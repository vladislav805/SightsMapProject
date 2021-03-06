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

	function ReportReason(d) {
		BaseModel.apply(this, arguments);
	}

	extendClass(BaseModel, Sight, {});
	extendClass(BaseModel, City, {});
	extendClass(City, StandaloneCity, {});
	extendClass(BaseModel, User, {});
	extendClass(BaseModel, Photo, {});
	extendClass(BaseModel, Mark, {});
	extendClass(BaseModel, Comment, {});
	extendClass(BaseModel, ReportReason, {});

	main.Sight = Sight;
	main.City = City;
	main.StandaloneCity = StandaloneCity;
	main.User = User;
	main.Photo = Photo;
	main.Mark = Mark;
	main.Comment = Comment;
	main.ReportReason = ReportReason;

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
			NOT_SET: "NOT_SET",
			FEMALE: "FEMALE",
			MALE: "MALE"
		},

		/**
		 *
		 * @param {int[]|int|string[]|string} userIds
		 * @returns {Promise.<User[]>}
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
		 * @param {object} opts
		 * @returns {Promise.<{result: boolean, userId: int, mail: boolean}>}
		 */
		create: function(opts) {
			return main.request("account.create", opts);
		},

		/**
		 *
		 * @param {object} opts
		 * @returns {Promise.<int>}
		 */
		editInfo: function(opts) {
			return main.request("account.editInfo", opts);
		},

		/**
		 *
		 * @param {string} oldPassword
		 * @param {string} newPassword
		 * @returns {Promise.<{authKey: string, authId: int, userId: int, date: int, }>}
		 */
		changePassword: function(oldPassword, newPassword) {
			return main.request("account.changePassword", {oldPassword, newPassword}).then(result => {
				main.session.setAuthKey(result.authKey);
				return result;
			});
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
		 * @param {int} photoId
		 * @returns {Promise.<boolean>}
		 */
		setProfilePhoto: function(photoId) {
			return main.request("account.setProfilePhoto", {photoId: photoId});
		},

		removeProfilePhoto: function() {
			return main.request("account.removeProfilePhoto", {});
		},

		/**
		 *
		 * @returns {Promise}
		 */
		logout: function() {
			return main.request("users.logout", {});
		}

	};

	main.sights = {

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
			return main.request("sights.get", { lat1: lat1, lng1: lng1, lat2: lat2, lng2: lng2 }).then(function(r) {
				return main.utils.parse(r.type === "sights"
					? {items: Sight, users: User}
					: {items: StandaloneCity}, r);
			});
		},

		/**
		 *
		 * @param {int} sightId
		 * @returns {Promise.<Sight>}
		 */
		getById: function(sightId) {
			return main.request("sights.getById", { sightId: sightId }).then(function(r) {
				return new Sight(r);
			});
		},

		/**
		 *
		 * @param {{title: string, description: string, lat: float, lng: float}} obj
		 * @returns {Promise.<Sight>}
		 */
		add: function(obj) {
			return main.request("sights.add", obj).then(function(r) {
				return new Sight(r);
			});
		},

		/**
		 *
		 * @param {int} sightId
		 * @param {{title: string, description: string, sightId: int?, cityId: int=}} obj
		 * @returns {Promise.<Sight>}
		 */
		edit: function(sightId, obj) {
			obj = obj || {};
			obj.sightId = sightId;
			return main.request("sights.edit", obj).then(r => new Sight(r));
		},

		/**
		 *
		 * @param {int} sightId
		 * @param {int} state
		 * @returns {Promise.<{change: boolean, state: {visited: int, desired: int}}>}
		 */
		setVisitState: function(sightId, state) {
			return main.request("sights.setVisitState", { sightId: sightId, state: +state });
		},

		/**
		 *
		 * @param {int} sightId
		 * @param {int[]|string} photoIds
		 * @returns {Promise.<boolean>}
		 */
		setPhotos: function(sightId, photoIds) {
			return main.request("sights.setPhotos", {sightId: sightId, photoIds: Array.isArray(photoIds) ? photoIds.join(",") : photoIds})
		},

		/**
		 *
		 * @param {int} sightId
		 * @param {int[]|string} markIds
		 * @returns {Promise.<boolean>}
		 */
		setMarks: function(sightId, markIds) {
			return main.request("sights.setMarks", {sightId: sightId, markIds: Array.isArray(markIds) ? markIds.join(",") : markIds});
		},

		/**
		 *
		 * @param {int} sightId
		 * @param {boolean} state
		 * @returns {Promise.<boolean>}
		 */
		setVerify: function(sightId, state) {
			return main.request("sights.setVerify", {sightId: sightId, state: +state});
		},

		/**
		 *
		 * @param {int} sightId
		 * @param {boolean} state
		 * @returns {Promise.<boolean>}
		 */
		setArchived: function(sightId, state) {
			return main.request("sights.setArchived", {sightId: sightId, state: +state});
		},

		/**
		 *
		 * @param {int} sightId
		 * @param {float} lat
		 * @param {float} lng
		 * @returns {Promise.<boolean>}
		 */
		move: function(sightId, lat, lng) {
			return main.request("sights.move", { sightId: sightId, lat: lat, lng: lng });
		},

		/**
		 *
		 * @param {int} sightId
		 * @returns {Promise.<boolean>}
		 */
		remove: function(sightId) {
			return main.request("sights.remove", { sightId: sightId });
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
			return main.request("sights.search", {
				query: query,
				count: count || 50,
				offset: offset || 0,
				order: main.sights.orderBy.RATING,
				cityId: cityId || ""
			});
		},

		/**
		 *
		 * @returns {Promise<Sight>}
		 */
		getRandomPlace: function() {
			return main.request("sights.getRandomPlace", {}).then(function(r) {
				return new Sight(r);
			});
		},

		/**
		 *
		 * @param {float} lat
		 * @param {float} lng
		 * @param {int} distance
		 * @param {int} count
		 * @returns {Promise<{count: int, items: API.Sight, distances: {sightId: int, distance: int}[]}>}
		 */
		getNearby: function(lat, lng, distance, count) {
			return main.request("sights.getNearby", { lat: lat, lng: lng, distance: distance, count: count }).then(r => {
				const items = main.utils.parse(Sight, r.items);
				const assocDistance = {};
				r.distances.forEach(item => assocDistance[item.sightId] = item.distance);
				return items.map(sight => {
					sight.distance = assocDistance[sight.sightId];
					return sight;
				});
			});
		},

		/**
		 *
		 * @param {int} sightId
		 * @param {int} photoId
		 * @returns {Promise<boolean>}
		 */
		suggestPhoto: function(sightId, photoId) {
			return main.request("sights.suggestPhoto", { sightId: sightId, photoId: photoId });
		},

		/**
		 *
		 * @param {int} sightId
		 * @param {int} photoId
		 * @returns {Promise<boolean>}
		 */
		approvePhoto: function(sightId, photoId) {
			return main.request("sights.approvePhoto", { sightId: sightId, photoId: photoId });
		},

		/**
		 *
		 * @param {int} sightId
		 * @param {int} photoId
		 * @returns {Promise<boolean>}
		 */
		declinePhoto: function(sightId, photoId) {
			return main.request("sights.declinePhoto", { sightId: sightId, photoId: photoId });
		},

		/**
		 * @returns {Promise<{text: string, items: API.ReportReason[]}>}
		 */
		getReportReasons: function() {
			return main.request("sights.getReportReasons");
		},

		/**
		 *
		 * @param {int} sightId
		 * @param {int} reasonId
		 * @param {string=} comment
		 * @returns {Promise<int>}
		 */
		report: function(sightId, reasonId, comment) {
			return main.request("sights.report", { sightId: sightId, reasonId: reasonId, comment: comment || "" });
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
			SIGHT: 1,
			PROFILE: 2,
			SIGHT_SUGGESTED: 3
		},

		UPLOAD_TYPE: {
			SIGHT: "sight",
			PROFILE: "profile",
			SIGHT_SUGGEST: "sight_suggest"
		},

		/**
		 *
		 * @param {int} sightId
		 * @returns {Promise.<{items: Photo[], users: User[]=}>}
		 */
		get: function(sightId) {
			return main.request("photos.get", {sightId: sightId}).then(function(r) {
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
		 * @param {string} type
		 * @param {File|Blob} file
		 * @returns {Promise<Photo>}
		 */
		upload: function(type, file) {
			return new Promise((resolve, reject) => {
				main.request("photos.getUploadUri", {type: type})
			        .then(/** @param {{ hash: string, uniqId: int, url: string}} target */ target => new Promise((resolve, reject) => {
				        var xhr = new XMLHttpRequest;
				        xhr.open("POST", target.url);
				        xhr.onreadystatechange = function() {
					        if (xhr.readyState !== 4) {
						        return;
					        }

					        try {
						        var result = JSON.parse(xhr.responseText);
						        !result.error ? resolve(target) : reject({xhr: xhr, error: result.error});
					        } catch (e) {
						        reject({xhr: xhr, error: false});
					        }
				        };
				        xhr.send(main.utils.makeFormData({
					        file: file
				        }));
			        }))
				    .then(/** @param {{ hash: string, uniqId: int }} upload */ upload => main.request("photos.save", {
					    hash: upload.hash,
					    qi: upload.uniqId
				    }))
				    .then(result =>
						resolve(main.utils.parse(Photo, result)[0])
					)
				    .catch(error => {
				    	console.error(error);
					    return reject(error);
				    })
			});
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
		 * @param {int} sightId
		 * @param {int=} count
		 * @param {int=} offset
		 * @returns {Promise.<{count: int, items: Comment[], users: User[]}>}
		 */
		get: function(sightId, count, offset) {
			count = count || 50;
			return main.request("comments.get", { sightId: sightId, offset: offset, count: count }).then(r => {
				return main.utils.parse({items: Comment, users: User}, r);
			});
		},

		/**
		 *
		 * @param {int} sightId
		 * @param {string} text
		 * @returns {Promise.<{comment: Comment, user: User}>}
		 */
		add: function(sightId, text) {
			return main.request("comments.add", { sightId: sightId, text: text }).then(r => ({comment: new Comment(r.comment), user: new User(r.user)}));
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
		 * @param {string=} extra
		 * @returns {Promise.<{count: int, items: City[]}>}
		 */
		get: function(extra) {
			return main.request("cities.get", {extra: extra || ""}).then(function(r) {
				return main.utils.parse({items: City}, r);
			});
		},

		/**
		 *
		 * @param {string} name
		 * @param {int|null} parentId
		 * @param {float|null} lat
		 * @param {float|null} lng
		 * @param {int|null} radius
		 * @param {string} description
		 * @returns {Promise.<City>}
		 */
		add: function(name, parentId, lat, lng, radius, description) {
			return main.request("cities.add", {
				name: name,
				parentId: parentId,
				lat: lat,
				lng: lng
			});
		},

		edit: function(cityId, args) {
			args.cityId = cityId;
			return main.request("cities.edit", args).then(function(res) {
				return new City(res);
			});
		},

		/**
		 *
		 * @param {int} cityId
		 * @returns {Promise.<boolean>}
		 */
		remove: function(cityId) {
			return main.request("cities.remove", {
				cityId: cityId
			});
		}


	};

	main.rating = {
		/**
		 *
		 * @param {int} sightId
		 * @param {int} rating
		 * @returns {Promise.<{change: boolean, rating: int}>}
		 */
		set: function(sightId, rating) {
			return main.request("rating.set", { sightId: sightId, rating: rating });
		}
	};

	main.neuralNetwork = {

		getParametersForRouting: function() {
			return main.request("neuralNetwork.getParametersForRouting");
		},

		/**
		 *
		 * @param {{count: int=, offset: int=}} params
		 * @returns {Promise.<{count: int, items: Sight[], error: float}>}
		 */
		getInterestedSights: function(params) {
			return main.request("neuralNetwork.getInterestedSights", params);
		}

	};

	main.error = {
		NO_PARAM: 0x01,
		UNKNOWN_METHOD: 0x04,
		UNKNOWN_ERROR: 0x05,
		INCORRECT_LOGIN_PASSWORD: 0x10,
		LOGIN_ALREADY_EXIST: 0x11,
		INCORRECT_LENGTH_PASSWORD: 0x12,
		INCORRECT_NAMES: 0x13,
		EMAIL_ALREADY_EXIST: 0x14,
		SESSION_NOT_FOUND: 0x1f,
		POINT_NOT_FOUND: 0x20,
		INVALID_COORDINATES: 0x21,
		MARK_NOT_FOUND: 0x30,
		INVALID_COLOR: 0x31,
		PHOTO_NOT_FOUND: 0x40,
		UPLOAD_FAILURE: 0x41,
		UPLOAD_INVALID_RESOLUTION: 0x42,
		COMMENT_NOT_FOUND: 0x50,
		RATING_INVALID: 0x60,
		COMPILE_ERROR: 0x90,
		RUNTIME_ERROR: 0x91,
		ACCESS_DENIED: 0x1e,
		FLOOD_CONTROL: 0x0f,
		ACCESS_FOR_METHOD_DENIED: 0xc0,
		ACCOUNT_NOT_ACTIVE: 0xc1
	};

	return main;
})();