var Cities = {

	/**
	 * @var {City[]}
	 */
	mCityItems: null,

	/**
	 * @var {Bundle.<City>}
	 */
	mCityBundle: null,

	/**
	 * Получние категорий от API
	 */
	get: function() {
		Cities.mCityBundle = new Bundle;
		API.cities.get().then(function(data) {
			return data.items.map(function(city) {
				city = new City(city);
				Cities.mCityBundle.set(city.getId(), city);
				return city;
			});
		}).then(function(list) {
			return Cities.mCityItems = list;
		});
	},

	/**
	 * @returns {City[]}
	 */
	getItems: function() {
		return this.mCityItems;
	},

	/**
	 *
	 * @returns {Bundle.<City>}
	 */
	getBundle: function() {
		return this.mCityBundle;
	}
};