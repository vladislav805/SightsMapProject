"use strict";

const Sight = {

	setVisitState: function(node) {
		const toast = new Toast("Сохраняем...").show(60000);
		/** @var {{change: boolean, state: {visited: int, desired: int, notInterested: int}}} result */
		API.points.setVisitState(+node.dataset.pid, +node.dataset.visitState).then(result => {
			const wrap = document.querySelector(".sight-visitState");
			wrap.dataset.visitState = node.dataset.visitState;

			toast.setText("Сохрарено").show(3000);

			const counts = wrap.querySelectorAll("var");
			counts[1].textContent = String(result.state.visited);
			counts[2].textContent = String(result.state.desired);
			counts[3].textContent = String(result.state.notInterested);
		}).catch(function(error) {
			console.error(error);
		});
	},

	setRating: function(node, rating) {
		const isRated = parseInt(node.parentNode.dataset.user);

		if (isRated === rating) {
			rating = 0;
		}

		const toast = new Toast("Сохраняем Вашу оценку...").show(60000);
		/** @var {{change: boolean, rating: int}} result */
		API.rating.set(+node.dataset.pid, rating).then(result => {
			node.parentNode.dataset.user = String(rating);
			node.parentNode.querySelector("strong").textContent = String(result.rating);
			toast.setText("Спасибо! Ваша оценка учтена").show(3000);
		}).catch(function(error) {
			console.error(error);
		});
	},

	verify: function(node) {
		const toast = new Toast("Сохранение...").show(60000);
		node.disabled = true;
		var newState = !+node.dataset.nowState;
		API.points.setVerify(+node.dataset.pid, newState).then(result => {
			toast.setText("Сохранено").show(3000);
			node.dataset.nowState = String(+newState);
			node.disabled = false;
		});
	},

	archive: function(node) {
		const toast = new Toast("Сохранение...").show(60000);
		node.disabled = true;
		var newState = !+node.dataset.nowState;
		API.points.setArchived(+node.dataset.pid, newState).then(result => {
			toast.setText("Сохранено").show(3000);
			node.dataset.nowState = String(+newState);
			node.disabled = false;
		});
	},

	remove: function(node) {
		xConfirm("Подтверждение", "Вы уверены, что хотите удалить эту достопримечательность?\nP.S. Если её более не существует, удалять ее не нужно, просто напишите об этом в комментариях или нажмите на «Жалоба» с причиной «более не существует» — модераторы примут изменение и на сайте останется память о месте.\n\nУдаление &ndash; действие безвозвратное.", "Да, удалить", "Нет, оставить", () => {
			const toast = new Toast("Удаление...").show(60000);
			API.points.remove(+node.dataset.sid).then(() => {
				toast.setText("Удалено :(").show(3000);
			});
		});
	}
};