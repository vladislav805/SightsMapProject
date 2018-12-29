window.ManageMap = (function() {

	let mainMap;
	let sightPlacemark;
	let sightInfo;
	let photoSortable;

	function initDropZone() {
		var dropArea = document.getElementById("__photo-drop-zone"),
			input = dropArea.firstElementChild;

		var highlight = e => {
			dropArea.style.borderColor = "red";
			dropArea.classList.add("highlight");
		};

		var unhighlight = e => {
			dropArea.style.borderColor = "var(--primaryBackgroundColor)";
			dropArea.classList.remove("highlight");
		};

		["dragenter", "dragover", "dragleave", "drop"].forEach(eventName => {
			dropArea.addEventListener(eventName, e => {
				e.preventDefault();
				e.stopPropagation();
			}, false);
		});

		["dragenter", "dragover"].forEach(eventName => {
			dropArea.addEventListener(eventName, highlight, false);
		});

		["dragleave", "drop"].forEach(eventName => {
			dropArea.addEventListener(eventName, unhighlight, false);
		});

		var onDroppedFiles = function(files) {
			handleFiles(input, files).then(res => {
				if (res) {
					xConfirm("Изменить геометку?", "На фотографии обнаружена геометка, поставленная устройством на основе GPS. Установить ее как место достопримечательности?\n\nУчтите, что устройство не всегда ставит метку достаточно точно. Пожалуйста, проверьте и исправьте, при необходимости, чтобы метка на карте стояла как можно точнее к реальному местоположению места.", "Да", "Не нужно", () => {
						mainMap.setCenter([res.lat, res.lng], 18, {checkZoomRange: true});
						manager.setInitialPositionPlacemark(res.lat, res.lng, 18);
					});
				}
			});
		};

		input.addEventListener("change", e => onDroppedFiles(input.files));

		dropArea.addEventListener("drop", e => {
			var dt = e.dataTransfer;
			var files = dt.files;

			onDroppedFiles(files);
		}, false);
	}

	function initForm(form) {
		var photoList = document.querySelector(".manage-photos-list");
		form.addEventListener("submit", e => {
			e.preventDefault();

			var coords = sightPlacemark.geometry.getCoordinates();

			if (coords[0] === 0 && coords[1] === 0) {
				new Toast("Метка не поставлена").show(3000);
				return false;
			}

			var res = shakeOutForm(form);
			res.lat = coords[0];
			res.lng = coords[1];
			res.markIds = res["markId[]"].map(i => +i);
			delete res["markId[]"];

			/** @var {API.Sight} si */
			var si = sightInfo.sight;

			const modal = new Modal({
				title: "Пожалуйста, подождите",
				content: "Сохранение информации",
				closeByClickOutside: false
			});

			modal.setPlain(true);
			modal.show();

			manager.saveInfo(res).then(/** @param {API.Sight} sight */ sight => {
				if (!sightInfo.sight) {
					sightInfo.sight = si = sight;
					window.history.replaceState(null, "Редактирование места", "/sight/" + si.sightId + "/edit");
				}
				modal.setContent("Информация сохранена");
				if (res.lat !== si.lat || res.lng !== si.lng) {
					modal.setContent("Изменение положения...");
					console.log("Need update position");
					return API.points.move(si.sightId, res.lat, res.lng);
				}
				return true;
			}).then(result => {
				if (!Sugar.Array.isEqual(res.markIds, si.markIds)) {
					modal.setContent("Изменение списка меток...");
					console.log("Need update marks", res.markIds, si.markIds);
					return API.points.setMarks(si.sightId, res.markIds);
				}
				return true;
			}).then(result => {
				const nodes = Array.from(photoList.children).filter(node => node.sightPhoto);
				const oldPhotoIds = sightInfo.photos.map(i => i.photoId);
				const newPhotoIds = nodes.map(item => "photoId" in item.dataset ? +item.dataset.photoId : null);

				if (
					~newPhotoIds.indexOf(null) || // if has not uploaded photos
					!Sugar.Array.isEqual(oldPhotoIds, newPhotoIds) // if arrays not equals
				) {
					modal.setContent("Изменение списка фотографий...");
					console.log("Need update photos");
					return handleAllPhotos(nodes, modal).then(photos => {
						modal.setContent("Изменение списка фотографий...");
						sightInfo.photos = photos;
						return API.points.setPhotos(si.sightId, photos.filter(i => i).map(p => p.photoId));
					}).then(result => {
						return true;
					});
				}
				return true;
			}).then(() => modal.setContent("Успешно сохранено").releaseAfter(2500)).catch(error => {
				modal.setPlain(false);
				modal.setTitle("Произошла ошибка");
				modal.setContent(JSON.stringify(error));
			});

			return false;
		});
	}

	function showPhotoList(photos) {
		const photoList = document.querySelector(".manage-photos-list");
		photos.forEach(photo => {
			photoList.appendChild(new SightPhoto(photo, true).getNode());
		});
		photoList.dataset.count = String(photos.length);
		makeSortable();
	}

	function makeSortable() {
		photoSortable && photoSortable.destroy();
		photoSortable = Sortable.create(document.querySelector(".manage-photos-list"), {animation: 150});
	}

	function handleFiles(input, files) {
		return new Promise(resolve => {
			var list = input.parentNode.previousElementSibling;
			var count = list.childElementCount;
			var coord = null;

			var i = 0;

			var fetchPhoto = (file) => {
				var up = new SightPhoto(file);

				list.appendChild(up.getNode());
				list.dataset.count++;

				if (!count && !coord) {
					up.getExif().then(result => {
						coord = result;
						resolve(result);
					});
				}

				if (files[i = i + 1]) {
					fetchPhoto(files[i]);
				} else {
					makeSortable();
				}
			};

			fetchPhoto(files[i]);
		});
	}

	function SightPhoto(file, exists) {
		this.mExists = exists;
		this.mFile = file;
		this.__createNodes();
	}

	SightPhoto.prototype = {
		__createNodes: function() {
			var wrap = document.createElement("div");
			var image = document.createElement("img");
			var drop = document.createElement("div");

			wrap.classList.add("manage-photo-item");
			drop.classList.add("manage-photo-drop");

			drop.textContent = "cancel";

			drop.addEventListener("click", () => this.__showConfirmRemove());

			wrap.appendChild(image);
			wrap.appendChild(drop);

			wrap.sightPhoto = this;

			this.mWrap = wrap;
			this.mImage = image;

			if (!this.mExists) {
				this.__makeThumbnail();
			} else {
				this.__setUrlThumbnail();
			}
		},

		__showConfirmRemove: function() {
			xConfirm("Подтверждение", "Вы уверены, что хотите открепить эту фотографию от этого места?", "Да", "Нет", () => {
				this.mWrap.parentNode.removeChild(this.mWrap);
				this.mFile = null;
				this.mImage = null;
			})
		},

		__makeThumbnail: function() {
			var reader = new FileReader;

			/** @param {{target: {result: string}}} e */
			reader.onload = e => {
				this.mImage.src = e.target.result;
				this.mImage.title = this.mFile.name;
			};

			reader.readAsDataURL(this.mFile);
		},

		__setUrlThumbnail: function() {
			this.mImage.src = this.mFile.photo200;
			this.mWrap.dataset.photoId = this.mFile.photoId;
			this.mWrap.dataset.uploaded = this.mFile.date;
		},

		getFile: function() {
			return this.mFile;
		},

		/**
		 * @returns {HTMLElement}
		 */
		getNode: function() {
			return this.mWrap;
		},

		getExif: function() {
			return new Promise((resolve => {
				const fetchData = () => EXIF.getData(this.mImage, () => {
					const rawLat = EXIF.getTag(this.mImage, "GPSLatitude");
					const rawLng = EXIF.getTag(this.mImage, "GPSLongitude");

					if (rawLat && rawLng) {
						var c = {
							lat: this.__toGpsCoordinates(rawLat, EXIF.getTag(this.mImage, "GPSLatitudeRef")),
							lng: this.__toGpsCoordinates(rawLng, EXIF.getTag(this.mImage, "GPSLongitudeRef"))
						};
						resolve(c);
					}
				});

				if (this.mImage.complete && this.mImage.naturalHeight !== 0) {
					fetchData();
				} else {
					this.mImage.addEventListener("load", fetchData);
				}
			}));
		},

		__toGpsCoordinates: function(arr, direction) {
			var degrees = arr.length > 0 ? arr[0] : 0;
			var minutes = arr.length > 1 ? arr[1] : 0;
			var seconds = arr.length > 2 ? arr[2] : 0;

			var dd = degrees + minutes / 60 + seconds / (60 * 60);

			if (direction === "S" || direction === "W") {
				return dd * -1;
			}
			return dd;
		},

		upload: function() {
			return API.photos.upload(API.photos.UPLOAD_TYPE.SIGHT, this.getFile()).then(photo => {
				this.mWrap.dataset.photoId = photo.photoId;
				this.mWrap.dataset.uploaded = photo.date;
				return photo;
			});
		}
	};

	/**
	 * @param {array} photos
	 * @param {Modal} modal
	 * @returns {Promise<API.Photo[]>}
	 */
	function handleAllPhotos(photos, modal) {
		return new Promise(resolve => {
			var photoIds = photos.map(node => "photoId" in node.dataset ? node.sightPhoto.getFile() : null);

			// Если нет незагруженных фото, сразу возвращаем массив из идентификаторов
			if (!~photoIds.indexOf(null)) {
				resolve(photoIds);
				return;
			}

			const promises = [];
			let N = 1;

			modal.setTitle("Загрузка фотографий...");

			const addPromise = index => {
				return () => {
					modal.setContent("Фотография " + N + ", осталось " + promises.length);
					return photos[index].sightPhoto.upload().then(photo => {
						console.log("uploaded", photo, index, photoIds);
						photoIds[index] = photo;
						return photo;
					}).catch(e => {
						let error = e.error;
						new Toast("Ошибка #" + error.errorId + "\n\n" + error.message).show(3000);
						return null
					});
				};
			};

			for (let i = 0, l = photoIds.length; i < l; ++i) {
				if (photoIds[i] === null) {
					promises.push(addPromise(i));
				}
			}

			const fire = () => {
				const promise = promises.shift();

				if (promises.length) {
					return promise().then(() => fire());
				} else {
					return promise().then(() => resolve(photoIds));
				}
			};

			fire();
		});
	}

	let listSuggestionsNode;
	let listSuggestionsCollection;

	function initSuggestionsList() {
		listSuggestionsNode = ge("manage-suggestions");
		listSuggestionsCollection = new ymaps.GeoObjectCollection();

		mainMap.geoObjects.add(listSuggestionsCollection);
	}

	function showNewSuggestions(lat, lng) {
		setOpacity(listSuggestionsNode, true);

		API.points.getNearby(lat, lng, 300, 3).then(res => {
			listSuggestionsNode.parentNode.hidden = !res.length;
			emptyNode(listSuggestionsNode);
			setOpacity(listSuggestionsNode, false);
			listSuggestionsCollection.removeAll();

			res.forEach(sight => {
				listSuggestionsCollection.add(new ymaps.Placemark([sight.lat, sight.lng], {
					hintContent: sight.title
				}, {
					preset: "islands#grayCircleDotIcon"
				}));

				listSuggestionsNode.appendChild(getSuggestionNode(sight));
			});
		});
	}

	function getSuggestionNode(sight) {
		let km = sight.distance > 1000;
		const unit = km ? "км" : "м";
		const k = km ? (sight.distance / 1000).toFixed(1) : sight.distance;
		return ce("a", {"class": "suggestion-item"}, [
			ce("h5", null, null, sight.title),
			ce("span", {"class": "suggestion-item-distance"}, null, k + " " + unit)
		]);
	}

	var manager = {
		init: () => {
			ymaps.ready(function() {
				new BaseMap(ge("manage-map"), null, {
					updateAddressOnChange: false,

					/**
					 * @param {ymaps.Map} yMap
					 */
					onMapReady: function(yMap) {
						mainMap = yMap;

						yMap.events.add("click", e => {
							var c = e.get("coords");
							manager.setInitialPositionPlacemark(c[0], c[1]);
						});

						sightPlacemark = new ymaps.Placemark([0, 0], {}, {
							draggable: true,
							hasBalloon: false,
							hasHint: false
						});

						sightPlacemark.events.add("dragend", evt => {
							const c = sightPlacemark.geometry.getCoordinates();
							showNewSuggestions(c[0], c[1]);
						});

						initSuggestionsList();
					}
				});
			});

			initDropZone();
			initForm(ge("__manageMapForm"));

			return true;
		},
		setInitialPositionPlacemark: function(lat, lng, z) {
			var c = [lat, lng];
			sightPlacemark.geometry.setCoordinates(c);
			if (!sightPlacemark.getMap()) {
				mainMap.geoObjects.add(sightPlacemark);
			}
			z && mainMap.setCenter(c, Math.max(z, mainMap.getZoom()), {checkZoomRange: true});
			showNewSuggestions(lat, lng);
		},

		setInitialData: function(info) {
			sightInfo = info;

			if (sightInfo.sight && sightInfo.sight.lat && sightInfo.sight.lng) {
				ymaps.ready(() => this.setInitialPositionPlacemark(sightInfo.sight.lat, sightInfo.sight.lng, 18));
			}

			sightInfo.photos && showPhotoList(sightInfo.photos);
		},

		/**
		 *
		 * @param {{title: string, description: string, cityId: int=}} res
		 * @returns {Promise<Sight>}
		 */
		saveInfo: function(res) {
			return sightInfo.sight && sightInfo.sight.sightId ? API.points.edit(sightInfo.sight.sightId, res) : API.points.add(res);
		}
	};

	return manager;
})();