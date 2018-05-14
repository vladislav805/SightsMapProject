var Photos = {

	/**
	 * Возвращает DOM-элемент для плашки с информацией о метке на карте
	 * @param {Point} point
	 * @returns {HTMLElement}
	 */
	getWidget: function(point) {
		var head,
			loader,
			items = ce("div", {"class": "photos-items"}, [loader = getLoader()]);

		head = ce("div", {"class": "photos-widget-wrap"}, [
			ce("h3", null, [
				getIcon("E251"),
				"Фотографии"
			]),
			point.canModify && ce("div", {"class": "photos-add", onclick: Photos.onClickAdd.bind(Photos, items, point)}, [getIcon("e2c6")])
		]);

		this.request(point.getId()).then(function(data) {
			items.removeChild(loader);

			point.setPhotos(data);

			items.dataset.emptyLabel = "Нет фотографий :(";
			if (!data.length) {
				return;
			}

			data.forEach(function(photo) {
				items.appendChild(Photos.getItem(photo, {point: point}));
			});

			baguetteBox.run(".photos-items", {
				noScrollbars: true
			});
		}).catch(function(e) {
			items.removeChild(loader);

			items.dataset.emptyLabel = "Что-то пошло не так... Ошибка:\n\n" + JSON.stringify(e);
		});

		return ce("div", {"class": "photos-widget"}, [head, items]);
	},

	/**
	 * Создает и возвращает один DOM-элемент фотографии для списка
	 * @param {Photo} photo
	 * @param {{point: Point}} options
	 * @returns {Node|HTMLElement}
	 */
	getItem: function(photo, options) {
		options = options || {};
		var node = ce("a", {
			href: photo.get(Photo.size.ORIGINAL),
			"class": "photo-item",
			style: "background-image: url(" + photo.get(Photo.size.THUMBNAIL) + ")"
		});

		if (options.point && options.point.canModify) {
			var remove = getIcon("e872", "white");
			remove.addEventListener("click", function(event) {
				event.preventDefault();
				event.stopPropagation();
				event.cancelBubble = true;
				xConfirm("Подтверждение", "Вы уверены, что хотите открепить эту фотографию от места?", "Открепить", "Отмена", Photos.removePhotoFromPoint.bind(Photos, options.point, photo, node), null);
				return false;
			});
			node.appendChild(remove);
		}

		return node;
	},

	/**
	 *
	 * @param {Point} point
	 * @param {Photo} photo
	 * @param {HTMLElement} node
	 */
	removePhotoFromPoint: function(point, photo, node) {
		var items = point.getPhotos(),
			info = new Toast("Открепление...");

		info.open(5000);
		items.splice(items.indexOf(photo), 1);

		Photos.commitPhotoSet(point).then(function() {
			info.setText("Откреплено").open(1000);
			node.parentNode.removeChild(node);
			API.photos.remove(photo.getId());
		});
	},

	/**
	 * Отправляет текущие данные о прикрепленных фотографиях к метке
	 * @param {Point} point
	 * @returns {Promise.<boolean>}
	 */
	commitPhotoSet: function(point) {
		return API.points.setPhotos(point.getId(), point.getPhotos().map(function(p) { return p.getId() }).join(","));
	},

	/**
	 * Получение данных о фотографиях, прикрепленных к метке
	 * @param pointId
	 * @returns {Promise.<Photo[]>}
	 */
	request: function(pointId) {
		return API.photos.get(pointId).then(function(d) {
			return d.map(function(f) {
				return new Photo(f);
			});
		});
	},

	/**
	 *
	 * @param {HTMLElement} list
	 * @param {Point} point
	 */
	onClickAdd: function(list, point) {
		var onFileReceived = function() {
				/** @var {File} */
				var f = fileInput.files[0];

				if (!f || f && !~f.type.indexOf("image/")) {
					wrapper.textContent = "Файл не выбран или выбрано не изображение. Попробуйте еще раз.";
					return;
				}

				wrapper.textContent = "Загрузка...";

				API.photos.upload(API.photos.type.POINT, f).then(function(d) {
					d = new Photo(d);
					var items = point.getPhotos();
					items.splice(items.length - 1, 0, d);
					list.appendChild(Photos.getItem(d, {point: point}));
					Photos.commitPhotoSet(point);
					modal.release();
					try {
						baguetteBox.destroy();
					} catch (e) {
						console.log("baguetteBox crashed");
					} finally {
						baguetteBox.run(".photos-items", {
							noScrollbars: true
						});
					}

				}).catch(function(e) {
					modal.setTitle("Ошибка");
					wrapper.textContent = getErrorStringByCode(e.error);
				});
			},
			wrapper,
			fileInput = ce("input", {type: "file", accept: "image/*", "onchange": onFileReceived}),
			modal = new Modal({
				title: "Загрузите фотографию этого места",
				content: wrapper = ce("div", {"class": "photo-upload-wrap"}, [
					ce("p", {}, null, "Нажмите на область ниже или перенесите в нее файлы размером до 5МБ"),
					ce("div", {"class": "photo-upload-dropZone"}, [fileInput])
				])
			});

		modal.show();
	}


};