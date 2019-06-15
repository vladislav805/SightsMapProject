"use strict";

const SightPage = {

	init: function(sightId) {
		baguetteBox.run(".sight-photos-list", {
			noScrollbars: true,
			async: true,
			loop: true
		});

		SightPage.__initPhotoAuthorPanel(sightId);
	},

	setVisitState: function(node) {
		const toast = new Toast("Сохраняем...").show(60000);
		/** @var {{change: boolean, state: {visited: int, desired: int, notInterested: int}}} result */
		API.sights.setVisitState(+node.dataset.pid, +node.dataset.visitState).then(result => {
			const wrap = document.querySelector(".sight-visitState");
			wrap.dataset.visitState = node.dataset.visitState;

			toast.setText("Сохранено").show(3000);

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
		API.sights.setVerify(+node.dataset.pid, newState).then(result => {
			toast.setText("Сохранено").show(3000);
			node.dataset.nowState = String(+newState);
			node.disabled = false;
		});
	},

	archive: function(node) {
		const toast = new Toast("Сохранение...").show(60000);
		node.disabled = true;
		var newState = !+node.dataset.nowState;
		API.sights.setArchived(+node.dataset.pid, newState).then(result => {
			toast.setText("Сохранено").show(3000);
			node.dataset.nowState = String(+newState);
			node.disabled = false;
		});
	},

	remove: function(node) {
		xConfirm("Подтверждение", "Вы уверены, что хотите удалить эту достопримечательность?\nP.S. Если её более не существует, удалять ее не нужно, просто напишите об этом в комментариях или нажмите на «Жалоба» с причиной «более не существует» — модераторы примут изменение и на сайте останется память о месте.\n\nУдаление &ndash; действие безвозвратное.", "Да, удалить", "Нет, оставить", () => {
			const toast = new Toast("Удаление...").show(60000);
			API.sights.remove(+node.dataset.sid).then(() => {
				toast.setText("Удалено :(").show(3000);
			});
		});
	},

	report: async function(node) {
		const loader = ce("div", {"class": "round-loader round-loader--v50 round-loader--center"});
		const modal = new Modal({
			title: "Подать жалобу",
			content: loader
		});

		modal.show();

		const form = ce("form", null, []);

		const makeReport = () => {
			const params = shakeOutForm(form);


			API.sights.report(+node.dataset.sid, +params.reasonId, params.comment).then(onResult).catch(err => {
				modal.setContent(form).setFooter(footer);
				const t = new Toast("Что-то пошло не так. Кажется, Вы не указали причину.");
				t.show(3000);
			});
			modal.setContent(loader).setFooter(null);
		};

		const onResult = result => {
			modal.setTitle("Успешно отправлено!")
			     .setContent("Спасибо за бдительность.<br><br>О результатах мы оповестим Вас на странице События или по e-mail.").releaseAfter(5000);
		};

		const footer = ce("div", null, [
			ce("button",  {onclick: modal.release.bind(modal)}, null, "Отмена"),
			ce("button",  {onclick: makeReport}, null, "Готово")
		]);

		API.sights.getReportReasons().then(res => {
			/** @var {{text: string, items: API.ReportReason[]}} res */

			form.appendChild(ce("div", null, null, res.text));

			res.items.forEach(item => {
				form.appendChild(ce("label", {"class": "fi-checkbox"}, [
					ce("input", {type: "radio", name: "reasonId", value: item.reasonId}),
					ce("span", null, null, item.label)
				]));
			});

			form.insertAdjacentHTML("beforeend", "<div class=\"fi-wrap\"><textarea name=\"comment\" id=\"report_comment\"></textarea><label for=\"report_comment\">Комментарий (необязательно)</label></div>");

			modal.setContent(form).setFooter(footer);
		});
	},

	openDialogSuggestPhoto: function(sightId) {
		var content,
			footer,
			modal = new Modal({
				title: "Предложить фотографию",
				content: content = ce("form", null, [
					ce("div", {}, "Вы можете загрузить фотографию этой достопримечательности. После проверки автором места или администратором фотография будет добавлена в обший список. До этого фотография будет в разделе \"предложенные фотографии\" под основным блоком.")
				])
			});

		const submit = event => {
			event && event.preventDefault();

			const file = input.files[0];

			modal.setFooter("");

			API.photos.upload(API.photos.UPLOAD_TYPE.SIGHT_SUGGEST, file).then(photo => {
				return API.sights.suggestPhoto(sightId, photo.photoId);
			}).then(res => {
				new Toast("Фотография успешно загружена и предложена. Автор или администраторы в скором времени произведут проверку. Спасибо!").show(4000);
				modal.release();
				refreshCurrent();
			}).catch(function(e) {
				console.log(e);
				let err = e.toString();
				if ("error" in e) {
					err = "#" + e.error.errorId + ": " + e.error.message;
				}
				new Toast("Произошла ошибка!\n" + err).show(15000);
				modal.release();
			});

			return false;
		};

		const input = ce("input", {
			type: "file",
			accept: "image/*"
		});

		const dropZone = ce("div", {
			"class": "manage-photos-dropZone",
			"data-label-empty": "Нажмите здесь или бросьте сюда файл"
		}, [input]);

		input.addEventListener("change", event => {
			dropZone.previousSibling.textContent = "Загрузка";
			dropZone.hidden = true;
			submit();
		});

		content.appendChild(dropZone);

		footer = ce("div", {"class": "modal-confirm-footer"}, [
			ce("input", {type: "button", value: "Отмена", onclick: modal.release.bind(modal)}),
			//ce("input", {type: "submit", value: "Предложить"})
		]);

		content.addEventListener("submit", submit);

		content.appendChild(footer);

		modal.show();
	},

	__initPhotoAuthorPanel: function(sightId) {
		const photos = document.querySelectorAll(".sight-photoItem--suggested");

		const html = photoId => `<div class="sight-photoItem-managePanel">
	<div class="sight-photoItem-manageButton material-icons" onclick="SightPage.setSuggestedPhotoState(${sightId}, ${photoId}, 1, event, this)">done</div>
	<div class="sight-photoItem-manageButton material-icons" onclick="SightPage.setSuggestedPhotoState(${sightId}, ${photoId}, 0, event, this)">cancel</div>
</div>`;

		photos.forEach(photo => {
			photo.insertAdjacentHTML("beforeend", html(photo.dataset.photoId));
		});
	},

	/**
	 *
	 * @param {int} sightId
	 * @param {int} photoId
	 * @param {int} action
	 * @param {Event} event
	 * @param {Element} node
	 */
	setSuggestedPhotoState: function(sightId, photoId, action, event, node) {
		event.stopPropagation();
		event.preventDefault();

		//const itemPhoto = node.parentNode.parentNode;

		(action ? API.sights.approvePhoto : API.sights.declinePhoto)(sightId, photoId).then(res => {
			console.log(res);
			new Toast("Успешно").show(3000);

			refreshCurrent();
		});
	},

	showModalMap: function(element) {
		const lat = parseFloat(element.dataset.lat);
		const lng = parseFloat(element.dataset.lng);
		showModalMap(lat, lng, 17, {
			/**
			 *
			 * @param {ymaps.Map} yMap
			 */
			onReady: yMap => {
				const placemark = new ymaps.Placemark([lat, lng], {}, {
					iconContent: "islands#blueIcon",
					hasBalloon: false,
					hasHint: false,
					openBalloonOnClick: false,
					openHintOnHover: false
				});

				yMap.geoObjects.add(placemark);
			}
		});
	}

};