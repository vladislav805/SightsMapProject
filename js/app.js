Main
	// Получили сессию
    // Показываем кто такой есть текущий пользователь
    .addListener(EventCode.SESSION_COMMITTED, Main.showCurrentUser.bind(Main))
    // Инициируем карту
	.addListener(EventCode.SESSION_COMMITTED, Map.init.bind(Map))

    // Получили кто такой есть владелец просматриваемой карты
    // Загружаем места и метки пользователя
    .addListener(EventCode.MAP_DONE, Marks.get.bind(Marks))

    .addListener(EventCode.MAP_DONE, Map.requestPointsByBounds.bind(Map))

    // Загрузуили метки
    // Показываем их в списке в меню
    .addListener(EventCode.MARK_LIST_UPDATED, Marks.showMarks.bind(Marks))

    // Изменились координаты просматриваемой части карты
    // Изменили URL
	.addListener(EventCode.MAP_BOUNDS_CHANGED, Map.setAddressByLocation.bind(Map))
	// Запомнили для последующего захода
	.addListener(EventCode.MAP_BOUNDS_CHANGED, Map.savePosition.bind(Map))
	// Запросили метки с для указанного участка
	.addListener(EventCode.MAP_BOUNDS_CHANGED, Map.requestPointsByBounds.bind(Map))

	.addListener(EventCode.POINT_CLICK, Map.showPointInfo.bind(Map))



	// Загрузили места
	// Показываем места в списке в меню
    .addListener(EventCode.POINT_LIST_UPDATED, Points.showList.bind(Points))
	// Показываем места на карте
	.addListener(EventCode.POINT_LIST_UPDATED, Map.showPoints.bind(Map))


	// Собрались создать место на карте
	// Создаем балун на карте с формой
	.addListener(EventCode.POINT_CREATE, Map.event.onCreate.bind(Map))

	// Создали место на карте
    // API отработало, добавили точку на карту
//	.addListener(EventCode.POINT_CREATED, Map.handle.event.onPointCreated.bind(Map))

	// Изменили место
    // API отработало, обновляем информацию о месте и оповещаем пользователя об этом
//	.addListener(EventCode.POINT_EDITED, Map.handle.event.onPointEdited.bind(Map))

//	.addListener(EventCode.POINT_MOVED, Map.handle.event.onPointMoved.bind(Map))

	// Удалили место
    // API отработало, удаляем с карты (-> MAP_PLACEMARK_REMOVE), удаляем из списка
//	.addListener(EventCode.POINT_REMOVED, Map.handle.event.onPointRemoved.bind(Map))

	// Удаляем место на карте
	// Нашли и удалили с карты плейсмарк
//	.addListener(EventCode.MAP_PLACEMARK_REMOVE, Map.removePlacemark.bind(Map))
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