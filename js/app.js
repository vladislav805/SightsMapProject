Main
	// Получили сессию
    .addListener(EventCode.SESSION_COMMITTED, Main.showCurrentUser.bind(Main))
	.addListener(EventCode.SESSION_COMMITTED, Map.init.bind(Map))

    // Убили сессию
    .addListener(EventCode.SESSION_CLOSED, Main.showCurrentUser.bind(Main))

    // Карта готова
    .addListener(EventCode.MAP_DONE, Marks.get.bind(Marks))
    .addListener(EventCode.MAP_DONE, Map.requestPointsByBounds.bind(Map))

    // Обновлен список категорий
    .addListener(EventCode.MARK_LIST_UPDATED, Marks.showMarks.bind(Marks))

    // Изменились координаты просматриваемой части карты
	.addListener(EventCode.MAP_BOUNDS_CHANGED, Map.setAddressByLocation.bind(Map))
	.addListener(EventCode.MAP_BOUNDS_CHANGED, Map.savePosition.bind(Map))
	.addListener(EventCode.MAP_BOUNDS_CHANGED, Map.requestPointsByBounds.bind(Map))

    // Кликнули на метку
	.addListener(EventCode.POINT_CLICK, Map.showPointInfo.bind(Map))

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
	Info.init();
	Points.init();
	drawHeight();
});

var drawHeight = function() {
	g("content").style.maxHeight = (document.documentElement.scrollHeight - 64) + "px";
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