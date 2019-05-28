/**
 * @type {{getTitle: (function(): string), getButtons: (function(): *), getDecorType: (function(): number), handleItem: (function(*): {level: (int), id: int, title: string}), doAsyncAction: (function(): Promise)}}
 */
const SMART_CONFIGURATION_CITIES = {

	__cache: null,

	getTitle: function() {
		return "Выбор города";
	},

	getButtons: function() {
		throw new Error("Not implemented");
	},

	doAsyncAction: function() {
		return this.__cache
			? Promise.resolve(this.__cache)
			: API.cities.get().then(res => this.__cache = this.__sortTree(res.items));
	},

	/**
	 *
	 * @param {API.City[]} items
	 * @returns {object[]}
	 * @private
	 */
	__sortTree: function(items) {
		const addChild = (root, city) => {
			if (!("child" in root)) {
				root.child = [];
			}
			root.child.push(city);
		};

		const output = [];
		const all = {};

		/** @var {API.City[]} dangling */
		const dangling = [];

		items.forEach(/** @param {API.City} city */ city => {
			const id = city.cityId;
			if (!city.parentId) {
				all[id] = city;
				output.push(city);
			} else {
				dangling.push(city);
			}
		});

		let d = 1000;

		while (dangling.length) {

			dangling.forEach((dang, i) => {
				const id = dang.cityId;
				const pid = dang.parentId;

				if (pid in all) {
					all[id] = dang;
					addChild(all[pid], dang);
					dangling.splice(i, 1);
				}
			});

			if (!d) {
				throw new Error("limit iterations");
			}
		}

		const result = [];
		const add2Result = (city, level) => {
			city.level = level;
			result.push(city);
			if (city.child) {
				city.child.forEach(c => add2Result(c, level + 1));
			}
		};

		output.forEach(c => add2Result(c, 0));

		return result;
	},

	getDecorType: function() {
		return SMART_MODAL_DECOR_LIST | SMART_MODAL_DECOR_RADIO_LIST | SMART_MODAL_DECOR_SEARCH;
	},

	handleItem: function(item) {
		return {id: item.cityId, title: item.name, subtitle: item.name4child, level: item.level, object: item};
	}
};

/**
 * @type {{getTitle: (function(): string), getButtons: (function(): *), getDecorType: (function(): number), handleItem: (function(*): {level: number, id: int, title: *}), doAsyncAction: (function(): Promise)}}
 */
const SMART_CONFIGURATION_MARKS = {

	__cache: null,

	getTitle: function() {
		return "Выбор метки";
	},

	getButtons: function() {
		throw new Error("Not implemented");
	},

	doAsyncAction: function() {
		return this.__cache
			? Promise.resolve(this.__cache)
			: API.marks.get().then(res => this.__cache = res.items);
	},

	getDecorType: function() {
		return SMART_MODAL_DECOR_LIST | SMART_MODAL_DECOR_CHECKABLE_LIST | SMART_MODAL_DECOR_SEARCH;
	},

	handleItem: function(item) {
		return {id: item.markId, title: item.title, level: 0, object: item};
	}
};

const SMART_MODAL_DECOR_LIST = 1;
const SMART_MODAL_DECOR_CHECKABLE_LIST = 2;
const SMART_MODAL_DECOR_RADIO_LIST = 4;
const SMART_MODAL_DECOR_SEARCH = 8;

const __smartModalContentList = (content) => {
	emptyNode(content);
	content.classList.add("list");
};

/**
 * Переопределение конфигурации "умного" модального окна
 * @param {{getTitle: function, getButtons: function, getDecorType: function, handleItem: function, doAsyncAction: function}} configuration Базовая конфигурация
 * @param {{getTitle: function=, getButtons: function, handleItem: function=}} overrides Переопределения пользователя
 * @return {{getTitle: function, getButtons: function, getDecorType: function, handleItem: function, doAsyncAction: function}}
 */
function smartModalsExtendConfiguration(configuration, overrides) {
	const config = clone(configuration);

	for (let key in overrides) {
		if (overrides.hasOwnProperty(key)) {
			config[key] = overrides[key];
		}
	}

	return config;
}

/**
 * Функция для создания DOM-элементов одного элемента списка
 * @param {{id: int|string, title: string, subtitle: string=, checked: boolean, level: int=}} item
 * @param multiple
 * @param checked
 * @returns {Node|HTMLElement}
 * @private
 */
const __smartModalContentListItem = (item, multiple, checked) => {
	const wrap = ce("label", {"class": "fi-checkbox"});
	let checkbox = ce("input", {type: multiple ? "checkbox" : "radio", name: "item", value: item.id});
	wrap.appendChild(checkbox);
	wrap.appendChild(ce("span", null, null, item.title));
	if (item.subtitle) {
		wrap.appendChild(ce("div", {"class": "sm-subtitle"}, null, item.subtitle));
	}

	if (item.level) {
		wrap.style.marginLeft = (item.level * 30) + "px";
	}

	if (item.checked || checked) {
		checkbox.checked = true;
	}

	return wrap;
};

/**
 * Создание формы для быстрого поиска по списку
 * @param {{title: string, id: int, node: Element}[]} list
 * @returns {Node|HTMLElement}
 * @private
 */
const __smartModalContentSearch = (list) => {
	const input = ce("input", {
		type: "search",
		"onkeydown": event => setTimeout(handle.bind(null, event), 10)
	});

	const handle = event => {
		const str = input.value.toLowerCase();
		list.forEach(item => {
			item.node.hidden = item.title.toLowerCase().indexOf(str) < 0;
		});
	};

	return input;
};

const __smartModalGetSelectedItems = (type, list, items) => {
	const checked = Array.from(list.querySelectorAll(":checked")).map(input => Number(input.value));

	const assoc = {};
	items.forEach(item => assoc[item.id] = item);

	const result = checked.map(id => assoc[id] && assoc[id].object);


	if ((type & SMART_MODAL_DECOR_CHECKABLE_LIST) !== 0) {
		return result;
	}
	if ((type & SMART_MODAL_DECOR_RADIO_LIST) !== 0) {
		return result.length ? result[0] : null;
	}
};

/**
 *
 * @param {{getTitle: function, getButtons: function, doAsyncAction: function, getDecorType: function, handleItem: function}} configuration
 * @param {{selected: int|int[]=}=} options
 */
const showSmartModal = (configuration, options) => {
	options = options || {};
	const content = ce("div", null, [ce("div", {"class": "round-loader round-loader--v50 round-loader--center"})]);
	const modal = new Modal({
		title: configuration.getTitle(),
		content: content
	});

	modal.show();

	configuration.doAsyncAction().then(result => {
		const decorType = configuration.getDecorType();
		const cnt = [];

		if ((decorType & SMART_MODAL_DECOR_LIST) !== 0) {
			const items = result.map(item => configuration.handleItem(item));
console.log(items)
			const callbacks = {
				getData: function() {
					return __smartModalGetSelectedItems(configuration.getDecorType(), content, items);
				}
			};

			modal.setFooter(configuration.getButtons().map(button => {
				return ce("button", {
					onclick: (button.onClick || function() {}).bind(null, button.name, callbacks, modal)
				}, null, button.label)
			}).reduce((root, button) => {
				root.appendChild(button);
				return root;
			}, ce("div")));

			__smartModalContentList(content);

			if ((decorType & SMART_MODAL_DECOR_RADIO_LIST) !== 0) {
				const s = "selected" in options ? (Array.isArray(options.selected) ? options.selected : [options.selected]) : [];
				items.forEach(item => {
					return cnt.push(item.node = __smartModalContentListItem(item, false, ~s.indexOf(item.id)));
				});
			}

			if ((decorType & SMART_MODAL_DECOR_CHECKABLE_LIST) !== 0) {
				const s = "selected" in options ? (Array.isArray(options.selected) ? options.selected : [options.selected]) : [];
				items.forEach(item => {
					return cnt.push(item.node = __smartModalContentListItem(item, true, ~s.indexOf(item.id)));
				});
			}

			if ((decorType & SMART_MODAL_DECOR_SEARCH) !== 0) {
				cnt.unshift(__smartModalContentSearch(items));
			}
		}
		cnt.forEach(c => content.appendChild(c));
	}).catch(e => console.error(e));
};


