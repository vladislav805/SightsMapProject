/**
 * Сессия
 * По умолчанию: не инициализированная
 * @param {String} authKey
 * @constructor
 */
function Session(authKey) {
	this.mAuthKey = authKey;
	this.mUser = new User({__: true, userId: -Number.MAX_SAFE_INTEGER});
	this.mState = Session.STATE_UNKNOWN;
}

/**
 * Сессия известна
 * @type {number}
 */
Session.STATE_OK = 0;

/**
 * Сессии нет
 * @type {number}
 */
Session.STATE_NONE = 1;

/**
 * Неопределенное состояние
 * @type {number}
 */
Session.STATE_UNKNOWN = 2;

Session.prototype = {

	/**
	 * Возвращает идентификатор пользователя - владельца сессии
	 * @returns {number}
	 */
	getId: function() {
		return this.mUser ? this.mUser.getId() : 0;
	},

	/**
	 * Возвращает авторизационный ключ
	 * @returns {String}
	 */
	getAuthKey: function() {
		return this.mAuthKey;
	},

	/**
	 * Возвращает состояние сессии
	 * @returns {number}
	 */
	getState: function() {
		return this.mState;
	},

	/**
	 * Возвращает true, если пользователь авторизован
	 * @returns {boolean}
	 */
	isAuthorized: function() {
		return this.getState() === Session.STATE_OK;
	},

	/**
	 * Получение информации о владельце сессии
	 * @returns {Promise}
	 */
	resolve: function() {
		return new Promise(function(resolve, reject) {
			if (!this.mAuthKey) {
				this.mState = Session.STATE_NONE;
				resolve(null);
				return;
			}

			API.request("users.get", { authKey: this.mAuthKey }).then(function(t) {
				var user = t[0];
				if (user) {
					this.mUser = new User(user);
					this.mState = Session.STATE_OK;
					resolve(this);
				} else {
					this.mState = Session.STATE_NONE;
					reject();
				}
			}.bind(this));
		}.bind(this));
	},

	/**
	 *
	 * @returns {User|null}
	 */
	getUser: function() {
		return this.mUser ? this.mUser : null;
	}

};