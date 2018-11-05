var API = (function() {
	var main = {};

	main.utils = {
		makeFormData: function(params) {
			var fd = new FormData(),
				fx = (fd.set ? fd.set : fd.append).bind(fd);

			for (var key in params) {
				if (params.hasOwnProperty(key) && params[key] !== null) {
					var val = params[key];
					Array.isArray(val) && (val = val.join(","));
					fx(key, params[key]);
				}
			}

			return fd;
		},

		parse: function(Class, data) {
			if (typeof Class !== "function" && data.length === undefined) {

				for (var key in Class) {
					if (Class.hasOwnProperty(key) && key in data) {
						data[key] = main.utils.parse(Class[key], data[key]);
					}
				}

				return data;
			}

			return data.map(function(item) {
				return new Class(item);
			});
		}
	};

	function extendClass(SuperClass, SubClass, methods) {
		SubClass.prototype = Object.create(SuperClass.prototype);

		SubClass.prototype.constructor = SubClass;

		if (!methods) {
			return;
		}

		for (var name in methods) {
			if (methods.hasOwnProperty(name)) {
				SubClass.prototype[name] = methods[name];
			}
		}
	}


	function BaseModel(d) {
		for (var key in d) {
			if (d.hasOwnProperty(key)) {
				this[key] = d[key];
			}
		}
	}

	function Sight(d) {
		BaseModel.apply(this, arguments);

		this.city && (this.city = new City(d.city));
	}

	function City(d) {
		BaseModel.apply(this, arguments);
	}

	function StandaloneCity(d) {
		BaseModel.apply(this, arguments)
	}

	function User(d) {
		BaseModel.apply(this, arguments);

		d.photo && (d.photo = new Photo(d.photo));
		d.city && (d.city = new City(d.city));
	}

	function Photo(d) {
		BaseModel.apply(this, arguments);
	}

	function Mark(d) {
		BaseModel.apply(this, arguments);
	}

	function Comment(d) {
		BaseModel.apply(this, arguments);
	}

	extendClass(BaseModel, Sight, {});
	extendClass(BaseModel, City, {});
	extendClass(City, StandaloneCity, {});
	extendClass(BaseModel, User, {});
	extendClass(BaseModel, Photo, {});
	extendClass(BaseModel, Mark, {});
	extendClass(BaseModel, Comment, {});

	main.Sight = Sight;
	main.City = City;
	main.StandaloneCity = StandaloneCity;
	main.User = User;
	main.Photo = Photo;
	main.Mark = Mark;
	main.Comment = Comment;

	/**
	 * Make request to API
	 * @param {string} method
	 * @param {object=} params
	 * @returns {Promise}
	 */
	main.request = function(method, params) {

		params = params || {};

		if (mSessionAuthKey) {
			params.authKey = mSessionAuthKey;
		}

		return new Promise(function(resolve, reject) {
			var xhr = new XMLHttpRequest;
			xhr.open("POST", "//" + window.location.hostname + "/api.php?method=" + method);
			xhr.onreadystatechange = function() {
				if (xhr.readyState !== 4) {
					return;
				}

				try {
					var result = JSON.parse(xhr.responseText);
					!result.error ? resolve(result.result) : reject({xhr: xhr, error: result.error});
				} catch (e) {
					reject({xhr: xhr, error: false});
				}
			};
			xhr.send(main.utils.makeFormData(params));
		});
	};

	var mSessionAuthKey = null;

	main.session = {

		/**
		 *
		 * @param {string|null} ak
		 * @returns {main.session}
		 */
		setAuthKey: function(ak) {
			mSessionAuthKey = ak;
			return main.session;
		}

	};

	main.users = {

		sex: {
			FEMALE: 1,
			MALE: 2
		},

		/**
		 *
		 * @param {int[]|int|string[]|string} userIds
		 * @returns {Promise}
		 */
		get: function(userIds) {
			return main.request("users.get", { userIds: Array.isArray(userIds) ? userIds.join(",") : userIds }).then(function(r) {
				return main.utils.parse(User, r);
			});
		}

	};

	main.account = {

		/**
		 *
		 * @param {string} login
		 * @param {string} password
		 * @returns {Promise}
		 */
		getAuthKey: function(login, password) {
			return main.request("users.getAuthKey", { login: login, password: password });
		},

		/**
		 *
		 * @param {string} firstName
		 * @param {string} lastName
		 * @param {string} login
		 * @param {string} password
		 * @param {int} sex
		 * @returns {Promise}
		 */
		create: function(firstName, lastName, login, password, sex) {
			return main.request("account.create", {firstName: firstName, lastName: lastName, login: login, password: password, sex: sex});
		},

		/**
		 *
		 * @param {boolean} status
		 * @returns {Promise}
		 */
		setStatus: function(status) {
			return main.request("account.setStatus", {status: +status});
		},

		/**
		 *
		 * @returns {Promise}
		 */
		logout: function() {
			return main.request("users.logout", {});
		}

	};

	main.points = {

		visitState: {
			NOT_VISITED: 0,
			VISITED: 1,
			DESIRED: 2
		},

		orderBy: {
			DATE_CREATE_ASC: 1,
			DATE_CREATE_DESC: -1,
			DATE_UPDATE_ASC: 2,
			DATE_UPDATE_DESC: -2,
			RATING: 3
		},

		/**
		 *
		 * @param {float} lat1
		 * @param {float} lng1
		 * @param {float} lat2
		 * @param {float} lng2
		 * @returns {Promise.<{count: int, items: Sight[]|City[], users: User[]}>}
		 */
		get: function(lat1, lng1, lat2, lng2) {
			return main.request("points.get", { lat1: lat1, lng1: lng1, lat2: lat2, lng2: lng2 }).then(function(r) {
				return main.utils.parse(r.type === "sights"
					? {items: Sight, users: User}
					: {items: StandaloneCity}, r);
			});
		},

		/**
		 *
		 * @param {int} pointId
		 * @returns {Promise.<Sight>}
		 */
		getById: function(pointId) {
			return main.request("points.getById", { pointId: pointId }).then(function(r) {
				return new Sight(r);
			});
		},

		/**
		 *
		 * @param {{title: string, description: string, lat: float, lng: float}} obj
		 * @returns {Promise.<Sight>}
		 */
		add: function(obj) {
			return main.request("points.add", obj).then(function(r) {
				return new Sight(r);
			});
		},

		/**
		 *
		 * @param {int} pointId
		 * @param {{title: string, description: string, pointId: int?}} obj
		 * @returns {Promise.<Sight>}
		 */
		edit: function(pointId, obj) {
			obj = obj || {};
			obj.pointId = pointId;
			return main.request("points.edit", obj).then(function(r) {
				return new Sight(r);
			});
		},

		/**
		 *
		 * @param {int} pointId
		 * @param {int} state
		 * @returns {Promise.<{change: boolean, state: {visited: int, desired: int}}>}
		 */
		setVisitState: function(pointId, state) {
			return main.request("points.setVisitState", { pointId: pointId, state: +state });
		},

		/**
		 *
		 * @param {int} pointId
		 * @param {int[]|string} photoIds
		 * @returns {Promise.<boolean>}
		 */
		setPhotos: function(pointId, photoIds) {
			return main.request("points.setPhotos", {pointId: pointId, photoIds: Array.isArray(photoIds) ? photoIds.join(",") : photoIds})
		},

		/**
		 *
		 * @param {int} pointId
		 * @param {int[]|string} markIds
		 * @returns {Promise.<boolean>}
		 */
		setMarks: function(pointId, markIds) {
			return main.request("points.setMarks", {pointId: pointId, markIds: Array.isArray(markIds) ? markIds.join(",") : markIds});
		},

		/**
		 *
		 * @param {int} pointId
		 * @param {boolean} state
		 * @returns {Promise.<boolean>}
		 */
		setVerify: function(pointId, state) {
			return main.request("points.setVerify", {pointId: pointId, state: +state});
		},

		/**
		 *
		 * @param {int} pointId
		 * @param {boolean} state
		 * @returns {Promise.<boolean>}
		 */
		setArchived: function(pointId, state) {
			return main.request("points.setArchived", {pointId: pointId, state: +state});
		},

		/**
		 *
		 * @param {int} pointId
		 * @param {float} lat
		 * @param {float} lng
		 * @returns {Promise.<boolean>}
		 */
		move: function(pointId, lat, lng) {
			return main.request("points.move", { pointId: pointId, lat: lat, lng: lng });
		},

		/**
		 *
		 * @param {int} pointId
		 * @returns {Promise.<boolean>}
		 */
		remove: function(pointId) {
			return main.request("points.remove", { pointId: pointId });
		},

		/**
		 *
		 * @param {string} query
		 * @param {int=} count
		 * @param {int=} offset
		 * @param {int=} order
		 * @param {int=} cityId
		 * @returns {Promise.<{count: int, items: Sight[]}>}
		 */
		search: function(query, count, offset, order, cityId) {
			return main.request("points.search", {
				query: query,
				count: count || 50,
				offset: offset || 0,
				order: main.points.orderBy.RATING,
				cityId: cityId || ""
			});
		},

		/**
		 *
		 * @returns {Promise.<Sight>}
		 */
		getRandomPlace: function() {
			return main.request("points.getRandomPlace", {}).then(function(r) {
				return new Sight(r);
			});
		}

	};

	main.marks = {

		/**
		 *
		 * @returns {Promise.<{count: int, items: Mark[]}>}
		 */
		get: function() {
			return main.request("marks.get").then(function(r) {
				return main.utils.parse({items: Mark}, r);
			});
		},

		/**
		 *
		 * @param {string} title
		 * @param {int} color
		 * @returns {Promise}
		 */
		add: function(title, color) {
			return main.request("marks.add", { title: title, color: color });
		},

		/**
		 *
		 * @param {int} markId
		 * @param {string} title
		 * @param {int} color
		 * @returns {Promise}
		 */
		edit: function(markId, title, color) {
			return main.request("marks.edit", { markId: markId, title: title, color: color });
		},

		/**
		 *
		 * @param {int} markId
		 * @returns {Promise}
		 */
		remove: function(markId) {
			return main.request("marks.remove", { markId: markId });
		}

	};

	main.photos = {

		type: {
			POINT: 1,
			PROFILE: 2
		},

		/**
		 *
		 * @param {int} pointId
		 * @returns {Promise.<{items: Photo[], users: User[]=}>}
		 */
		get: function(pointId) {
			return main.request("photos.get", {pointId: pointId}).then(function(r) {
				return main.utils.parse({items: Photo, users: User}, r);
			});
		},

		/**
		 *
		 * @param {int} photoId
		 * @returns {Promise.<Photo>}
		 */
		getById: function(photoId) {
			return main.request("photos.getById", {photoId: photoId}).then(function(r) {
				return main.utils.parse(Photo, r);
			});
		},

		/**
		 *
		 * @param {int} type
		 * @param {File|Blob} file
		 * @returns {Promise}
		 */
		upload: function(type, file) {
			return main.request("photos.upload", {type: type, file: file});
		},

		/**
		 *
		 * @param {int} photoId
		 * @returns {Promise.<boolean>}
		 */
		remove: function(photoId) {
			return main.request("photos.remove", {photoId: photoId});
		}

	};

	main.comments = {

		/**
		 *
		 * @param {int} pointId
		 * @param {int=} count
		 * @param {int=} offset
		 * @returns {Promise.<{count: int, items: Comment[], users: User[]}>}
		 */
		get: function(pointId, count, offset) {
			count = count || 50;
			return main.request("comments.get", { pointId: pointId, offset: offset, count: count }).then(function(r) {
				return main.utils.parse({items: Comment, users: User}, r);
			});
		},

		/**
		 *
		 * @param {int} pointId
		 * @param {string} text
		 * @returns {Promise.<Comment>}
		 */
		add: function(pointId, text) {
			return main.request("comments.add", { pointId: pointId, text: text }).then(function(r) {
				return new Comment(r);
			});
		},

		/**
		 *
		 * @param {int} commentId
		 * @returns {Promise.<boolean>}
		 */
		remove: function(commentId) {
			return main.request("comments.remove", { commentId: commentId });
		}

	};

	main.events = {

		type: {
			POINT_VERIFIED: 1,
			PHOTO_SUGGESTED: 3,
			PHOTO_ADDED: 4,
			PHOTO_DECLINED: 5,
			PHOTO_REMOVED: 6,
			POINT_MARKS_EDITED: 7,
			POINT_COMMENT_ADD: 8,
			POINT_REPORT: 9,
			POINT_COMMENT_REPORT: 10,
			POINT_NEW_UNVERIFIED: 11
		},

		/**
		 *
		 * @returns {Promise}
		 */
		get: function() {
			return main.request("events.get");
		},

		/**
		 *
		 * @returns {Promise}
		 */
		readAll: function() {
			return main.request("events.readAll");
		}

	};

	main.cities = {

		/**
		 *
		 * @returns {Promise.<{count: int, items: City[]}>}
		 */
		get: function() {
			return main.request("cities.get").then(function(r) {
				return main.utils.parse({items: City}, r);
			});
		}

	};

	main.rating = {
		/**
		 *
		 * @param {int} pointId
		 * @param {int} rating
		 * @returns {Promise.<{change: boolean, rating: int}>}
		 */
		set: function(pointId, rating) {
			return main.request("rating.set", { pointId: pointId, rating: rating });
		}
	};

	return main;
})();