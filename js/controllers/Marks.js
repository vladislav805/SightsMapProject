var Marks = {

	/**
	 * @var {SelectCheckable}
	 */
	mMarkList: null,

	/**
	 * @var {Mark[]}
	 */
	mMarkItems: null,

	/**
	 * @var {Bundle.<Mark>}
	 */
	mMarkBundle: null,

	/**
	 * Инициализация выбиралки категории
	 */
	init: function() {
		this.mMarkList = new SelectCheckable(g("mapOptionCategories"));
		this.mMarkList.setOnChecked(function() {
			Main.fire(EventCode.MAP_FILTER_UPDATED, {markIds: this.getSelected()});
		});

		this.mMarkBundle = new Bundle;
	},

	/**
	 * Получние категорий от API
	 */
	get: function() {
		API.marks.get().then(function(data) {
			return data.items.map(function(mark) {
				mark = new Mark(mark);
				Marks.mMarkBundle.set(mark.getId(), mark);
				return mark;
			});
		}).then(function(list) {
			return Marks.mListItems = list;
		}).then(Main.fire.bind(Main, EventCode.MARK_LIST_UPDATED));
	},

	/**
	 * @returns {Mark[]}
	 */
	getItems: function() {
		return this.mListItems;
	},

	/**
	 *
	 * @returns {Bundle.<Mark>}
	 */
	getBundle: function() {
		return this.mMarkBundle;
	},

	/**
	 * Очистка списка и построение списка по новым данным
	 * @param {Mark[]} items
	 */
	showMarks: function(items) {
		var ml = this.mMarkList;
		items.forEach(function(item) {
			var row = ml.add(item.getTitle(), item.getId(), true);
			row.getNode().style.setProperty("--colorItem", "#" + ColorUtils.getHEX(item.getColor()), "");
		}, this);
	}
};