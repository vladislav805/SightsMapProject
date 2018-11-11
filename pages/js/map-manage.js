window.ManageMap = (function() {

	var mainMap;
	var sightPlacemark;
	var sightInfo;

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
					console.log(res);
					if (confirm("На фотографии обнаружена геометка. Установить ее как место достопримечательности?")) {
						alert("Если Вы находились дальше 1-2 метров от места, пожалуйста, скорректируйте метку более точно вручную..\n\nСпасибо!");
						mainMap.setCenter([res.lat, res.lng], 18, {checkZoomRange: true});
						manager.setInitialPositionPlacemark(res.lat, res.lng);
					}
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
				alert("Метка не поставлена");
				return false;
			}

			var res = shakeOutForm(form);
			res.lat = coords[0];
			res.lng = coords[1];
			res.markIds = res["markId[]"].map(i => +i);
			delete res["markId[]"];

			var photoIds = Array.from(photoList.children).map(item => "photoId" in item.dataset ? +item.dataset.photoId : null);

			var si = sightInfo.sight;
			var siCity = si.city ? si.city.cityId : 0;

			if (
				res.title !== si.title ||
				res.description.replace(/\r/ig, "") !== si.description.replace(/\r/ig, "") ||
				res.cityId !== siCity
			) {
				console.log("Need update info");
			}

			if (res.lat !== si.lat || res.lng !== si.lng) {
				console.log("Need update position");
			}

			if (!Sugar.Array.isEqual(res.markIds, si.markIds)) {
				// res.markIds.join(",");
				console.log("Need update marks");
			}

			if (!Sugar.Array.isEqual(photoIds, sightInfo.photos.map(i => i.photoId))) {
				// check if new photos not uploaded
				console.log("Need update photos");
			}

			return false;
		});

		sightInfo.photos.forEach(photo => {
			photoList.appendChild(new SightPhoto(photo, true).getNode());
		});
		photoList.dataset.count = String(sightInfo.photos.length);
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

			drop.addEventListener("click", function() {
				wrap.parentNode.removeChild(wrap);
				this.mFile = null;
				image = null;
			});

			wrap.appendChild(image);
			wrap.appendChild(drop);

			this.mWrap = wrap;
			this.mImage = image;

			if (!this.mExists) {
				this.__makeThumbnail();
			} else {
				this.__setUrlThumbnail();
			}
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
		}
	};

	window.addEventListener("load", function() {
		initDropZone();

		var form = ge("__manageMapForm");
		initForm(form);
	});

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
			}
		});
	});

	var manager = {
		setInitialPositionPlacemark: function(lat, lng) {
			var c = [lat, lng];
			sightPlacemark.geometry.setCoordinates(c);
			if (!sightPlacemark.getMap()) {
				mainMap.geoObjects.add(sightPlacemark);
			}
			mainMap.setCenter(c, 18, {checkZoomRange: true});
		},

		setInitialData: function(info) {
			sightInfo = info;
		}
	};

	return manager;
})();