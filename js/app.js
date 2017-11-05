Main
	// Получили сессию
	.addListener(EventCode.SESSION_COMMITTED, Main.showCurrentUser.bind(Main))
	.addListener(EventCode.SESSION_COMMITTED, Map.init.bind(Map))

	// Убили сессию
	.addListener(EventCode.SESSION_CLOSED, Main.showCurrentUser.bind(Main))

	// Карта готова
	.addListener(EventCode.MAP_DONE, Marks.get.bind(Marks))
	.addListener(EventCode.MAP_DONE, Map.initFilters.bind(Marks))
	.addListener(EventCode.MAP_DONE, Map.requestPointsByBounds.bind(Map))

	// Обновлен список категорий
	.addListener(EventCode.MARK_LIST_UPDATED, Marks.showMarks.bind(Marks))

	.addListener(EventCode.MAP_FILTER_UPDATED, Map.event.onFilterUpdated.bind(Map))

	// Изменились координаты просматриваемой части карты
	.addListener(EventCode.MAP_BOUNDS_CHANGED, Map.setAddressByLocation.bind(Map))
	.addListener(EventCode.MAP_BOUNDS_CHANGED, Map.savePosition.bind(Map))
	.addListener(EventCode.MAP_BOUNDS_CHANGED, Map.requestPointsByBounds.bind(Map))

	// Кликнули на метку
	.addListener(EventCode.POINT_CLICK, Map.showPointInfo.bind(Map))

	.addListener(EventCode.POINT_HIGHLIGHT, Map.event.onHighlight.bind(Map))

	.addListener(EventCode.POINT_SHOW, Map.event.onShow.bind(Map))

	// Обновлен список меток на карте
	.addListener(EventCode.POINT_LIST_UPDATED, Points.showList.bind(Points))
	.addListener(EventCode.POINT_LIST_UPDATED, Map.showPoints.bind(Map))

	// Кликнули на карту, создаем новую метку
	.addListener(EventCode.POINT_CREATE, Map.event.onCreate.bind(Map))

	// Создалось новое место на карте
	.addListener(EventCode.POINT_CREATED, Map.event.onCreated.bind(Map))

	// Изменили место
	.addListener(EventCode.POINT_EDITED, Map.event.onEdited.bind(Map))

	// Хотим сдвинуть метку
	.addListener(EventCode.POINT_MOVE, Map.event.onMove.bind(Map))

	// Удалили метку
	.addListener(EventCode.POINT_REMOVED, Map.event.onRemove.bind(Map))
;


/**
 * Первичная инициализация
 * Расширение полифилами объекта даты и объекта с помощью SugarJS
 * Инициаилизация бокового меню
 */
window.addEventListener("DOMContentLoaded", function() {
	Sugar.Date.extend();
	Sugar.Object.extend();
	Sugar.String.extend();

	Marks.init();
	Aside.init();
	Points.init();
	drawHeight();
});

var drawHeight = function() {
	var content = document.documentElement.clientHeight - 64;
	g("content").style.maxHeight = content + "px";
};

/**
 * Вторичная инициализация
 * Подключение сессии, идентификация авторизованного пользователя
 */
window.addEventListener("load", function() {
	var authKey;

	if (authKey = get(Const.AUTH_KEY)) {
		storage.set(Const.AUTH_KEY, authKey);
		window.location.hash = "";
	}
	authKey = storage.get(Const.AUTH_KEY) || null;

	window.mSession = new Session(authKey);
	window.mSession.resolve().then(Main.setSession.bind(this, window.mSession));
});

window.addEventListener("resize", drawHeight);