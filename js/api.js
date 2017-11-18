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
		}
	};

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
			xhr.open("POST", "//" + window.location.host + "/sights/api.php?method=" + method);
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
			return main.request("users.get", { userIds: Array.isArray(userIds) ? userIds.join(",") : userIds });
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

		/**
		 *
		 * @param {float} lat1
		 * @param {float} lng1
		 * @param {float} lat2
		 * @param {float} lng2
		 * @param {int[]?} markIds
		 * @param {boolean?} onlyVerified
		 * @param {int?} visitState
		 * @returns {Promise}
		 */
		get: function(lat1, lng1, lat2, lng2, markIds, onlyVerified, visitState) {
			return main.request("points.get", {
				lat1: lat1,
				lng1: lng1,
				lat2: lat2,
				lng2: lng2,
				markIds: markIds,
				visitState: visitState === null || visitState === undefined ? -1 : visitState,
				onlyVerified: onlyVerified || 0
			});
		},

		/**
		 *
		 * @param {{title: string, description: string, lat: float, lng: float, markIds: int[]=}} obj
		 * @returns {Promise}
		 */
		add: function(obj) {
			return main.request("points.add", obj);
		},

		/**
		 *
		 * @param {int} pointId
		 * @param {{title: string, description: string, pointId: int?}} obj
		 * @returns {Promise}
		 */
		edit: function(pointId, obj) {
			obj = obj || {};
			obj.pointId = pointId;
			return main.request("points.edit", obj);
		},

		/**
		 *
		 * @param {int} pointId
		 * @param {int} state
		 * @returns {Promise}
		 */
		setVisitState: function(pointId, state) {
			return main.request("points.setVisitState", { pointId: pointId, state: +state });
		},

		/**
		 *
		 * @param {int} pointId
		 * @param {int[]|string} photoIds
		 * @returns {Promise}
		 */
		setPhotos: function(pointId, photoIds) {
			return main.request("points.setPhotos", {pointId: pointId, photoIds: Array.isArray(photoIds) ? photoIds.join(",") : photoIds})
		},

		/**
		 *
		 * @param {int} pointId
		 * @param {int[]|string} markIds
		 * @returns {Promise}
		 */
		setMarks: function(pointId, markIds) {
			return main.request("points.setMarks", {pointId: pointId, markIds: Array.isArray(markIds) ? markIds.join(",") : markIds});
		},

		/**
		 *
		 * @param {int} pointId
		 * @param {float} lat
		 * @param {float} lng
		 * @returns {Promise}
		 */
		move: function(pointId, lat, lng) {
			return main.request("points.move", { pointId: pointId, lat: lat, lng: lng });
		},

		/**
		 *
		 * @param {int} pointId
		 * @returns {Promise}
		 */
		remove: function(pointId) {
			return main.request("points.remove", { pointId: pointId });
		}

	};

	main.marks = {

		/**
		 *
		 * @returns {Promise}
		 */
		get: function() {
			return main.request("marks.get");
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
		 * @returns {Promise}
		 */
		get: function(pointId) {
			return main.request("photos.get", {pointId: pointId});
		},

		/**
		 *
		 * @param {int} photoId
		 * @returns {Promise}
		 */
		getById: function(photoId) {
			return main.request("photos.getById", {photoId: photoId});
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
		 * @returns {Promise}
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
		 * @returns {Promise}
		 */
		get: function(pointId, count, offset) {
			count = count || 50;
			return main.request("comments.get", { pointId: pointId, offset: offset, count: count });
		},

		/**
		 *
		 * @param {int} pointId
		 * @param {string} text
		 * @returns {Promise}
		 */
		add: function(pointId, text) {
			return main.request("comments.add", { pointId: pointId, text: text });
		},

		/**
		 *
		 * @param {int} commentId
		 * @returns {Promise}
		 */
		remove: function(commentId) {
			return main.request("comments.remove", { commentId: commentId });
		}

	};

	main.events = {

		type: {
			POINT_VERIFIED: 1,
			PHOTO_SUGGESTED: 3,
			PHOTO_ACCEPTED: 4,
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

	return main;
})();