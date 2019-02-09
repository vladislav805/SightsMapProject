const Feed = {
	readAll: function() {
		API.events.readAll().then(res => {
			new Toast("Успешно").show(2500);

			const news = document.querySelectorAll(".feed-item--new");
			Array.from(news).forEach(elem => elem.classList.remove("feed-item--new"));
		});
	}
};