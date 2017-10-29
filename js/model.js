/*
function Placemark(point) {

	this.mPoint = point;
	this.mObject = new ymaps.GeoObject({
		geometry: {
			type: "Point",
			coordinates: this.mPoint.getCoordinates()
		}
	}, {
		preset: "islands#icon"
	});

	this.mObject.events.add("balloonopen", function() {
		this.initBalloonActionButtons.call(this, "btnOnBllnOnMap" + this.mPoint.getId())
	}, this);
	this.mObject.events.add("mouseenter", function() { this.mPoint.getListItem().getNode().classList.add("listItem-hover") }, this);
	this.mObject.events.add("mouseleave", function() { this.mPoint.getListItem().getNode().classList.remove("listItem-hover"); }, this);

	this.setNormalProperties();
}

Placemark.prototype = {

	getGeoObject: function() {
		return this.mObject;
	},

	setNormalProperties: function () {
		this.mObject.properties.set({
			hintContent: this.mPoint.title.replaceHTML(),

			balloonContentHeader: this.getHeader(),
			balloonContentBody: this.getBody(),
			balloonContentFooter: this.getFooter(),

			accessCode: this.accessCode
		});

		this.initBalloonActionButtons("btnOnBllnOnMap" + this.mPoint.getId());


		this.mObject.options.set({
			iconColor: "#" + PlacemarkIcon.getHEX(0x1E98FF),
			zIndex: this.mPoint.getId()
		});

		if (!this.mPoint.isExists()) {
			var listener;
			this.mObject.events.add(["balloonclose"], listener = function() {

				// If after close balloon point is exists, than it was created
				if (this.mPoint.isExists()) {
					return this.mObject.events.remove(listener);
				}

				this.getGeoObject().getMap().geoObjects.remove(this.getGeoObject());
			}.bind(this));
		}

		this.mObject.events.remove("balloonclose", this.setNormalProperties, this);
	},

	getHeader: function() {
		return "<p class=\"balloon-plaintext\">" + this.mPoint.title.replaceHTML() + "<\/p>";
	},

	getBody: function() {
		return "<p class=\"balloon-plaintext\">" + this.mPoint.description.replaceHTML().replacePlain() + "<\/p>";
	},

	getFooter: function() {
		var p = this.mPoint, id = function(suffix) {
			return " id=\"btnOnBllnOnMap" + this.mPoint.getId() + suffix + "\"";
		}.bind(this);
		return [
			p.dateCreated ? "<p class=\"balloon-plaintext\">Создано " + p.dateCreated.format(Const.DEFAULT_FULL_DATE_FORMAT) + (p.isPublic ? "" : " (private)") + "<\/p>" : "",
			p.dateUpdated ? "<p class=\"balloon-plaintext\">Отредактировано " + p.dateUpdated.format(Const.DEFAULT_FULL_DATE_FORMAT) + "<\/p>" : "",
			"<p class=\"balloon-plaintext\">" + (p.isVisited ? "Посещено" : "Не посещено") + "<\/p>",
			p.mAuthor ? "<p class=\"balloon-plainText\">Author: " + p.mAuthor.getFullName() + "</p>" : "",
			"<div>",
			p.isPublic ? "<button" + id("link") + ">Поделиться<\/button>" : "",
			!p.isVisited ? "<button" + id("visit") + ">Отм. посещенным<\/button> " : "",
			p.canEdit ? "<button" + id("move") + ">Переместить<\/button>" : "",
			p.canEdit ? "<button" + id("edit") + ">Редактировать<\/button>" : "",
			p.canDelete ? "<button" + id("remove") + ">Удалить<\/button>" : "",
			"</div>"
		].join(" ");
	},

	/**
	 * Редактирование места

	edit: function() {
		this.mObject.properties.set({
			balloonContentHeader: this.getHeaderEditable() ,
			balloonContentBody: this.getBodyEditable(),
			balloonContentFooter: this.getFooterEditable(),

			pointId: this.mPoint.getId()
		});
		this.mObject.events.once("balloonclose", this.setNormalProperties, this);
		this.mObject.balloon.open().then(this.initBalloonEditForm.bind(this));
	},

	show: function() {
		return this.mObject.balloon.open();
	},

	initBalloonActionButtons: function(prefix) {
		var i = function(suffix) { return "#" + prefix + suffix; };
console.log("init balloon action buttons " + i);
		this.mObject.balloon.getOverlay().then(function(overlay) {
			var e = overlay.getBalloonElement(), b;

			e.querySelector(i("link")) && e.querySelector(i("link")).addEventListener("click", Map.handle.point.link.bind(Map, this.mPoint));
			e.querySelector(i("visit")) && (b = e.querySelector(i("visit"))).addEventListener("click", Map.handle.point.visit.bind(Map, this.mPoint, b));
			e.querySelector(i("edit")) && e.querySelector(i("edit")).addEventListener("click", Map.handle.point.edit.bind(Map, this.mPoint));
			e.querySelector(i("move")) && e.querySelector(i("move")).addEventListener("click", Map.handle.point.move.bind(Map, this.mPoint));
			e.querySelector(i("remove")) && e.querySelector(i("remove")).addEventListener("click", Map.handle.point.remove.bind(Map, this.mPoint));
		}.bind(this));
	},

	initBalloonEditForm: function() {
		this.mObject.balloon.getOverlay().then(function(overlay) {
			var form = overlay.getBalloonElement().querySelector("#editFormPlacemark" + this.mPoint.getId());
			form.addEventListener("submit", Map.handle.point.save.bind(form, this.mPoint));
		}.bind(this));
	},

	getHeaderEditable: function() {
		return this.mPoint.isExists() ? "Edit placemark &laquo;" + this.mPoint.title.replaceHTML() + "&raquo;" : "Create placemark";
	},

	getBodyEditable: function() {
		var p = this.mPoint;
		return [
			"<form id=\"editFormPlacemark" + this.mPoint.getId() + "\">",
			"<p class=\"balloon-edit-tips\">Title:<\/p>",
			"<input name=\"title\" value=\"" + p.title.replaceHTML() + "\" \/>",
			"<p class=\"balloon-edit-tips\">Description:<\/p>",
			"<textarea class=\"balloon-edit-textview\" name=\"description\">" + p.description.replaceHTML() + "<\/textarea>",
			"<label class=\"label-block\"><input type=\"checkbox\" name=\"isPublic\" checked value=\"1\" " + (p.isPublic ? "checked" : "") + " \/> public placemark (visible for everyone)<\/label>",
			"<p class=\"balloon-edit-tips\">Marks:<\/p>",
			"<select class='balloon-edit-select' name=\"markIds\" multiple size='5'>",
			Main.mMarks.map(function(mark) {

				return "<option value=\"" + mark.getId() + "\"" + (p.markIds && ~p.markIds.indexOf(mark.getId())) + ">" + mark.getTitle() + "</option>";
			}).join(""),
			"</select>",
			"<div><input type=\"submit\" value=\"Save\" \/><\/div>",
			"</form>"
		].join("")
	},

	getFooterEditable: function() {
		return "";
	},

	move: function() {
		return new Promise(function(resolve) {
			this.mObject.options.set({ draggable: true });
			this.mObject.balloon.close();

			this.mObject.events.once("dragend", function() {
				this.mObject.options.set({ draggable: false });
				resolve(this.mObject.geometry.getCoordinates());
			}.bind(this));
		}.bind(this));
	}
};

*/












var PlacemarkIcon = {

	mCache: {},

	mSchema: '<svg width="34" height="42" xmlns="http://www.w3.org/2000/svg"><defs><linearGradient x1="-4.452%" y1="87.587%" x2="92.777%" y2="7.839%" id="a" stop-color="#231F20"><stop offset="0%"/><stop stop-opacity="0" offset="100%"/></linearGradient></defs><g fill="none"><path d="M12.718 39.666c4.71-2.394 17.82-11.516 18.305-11.978.55-.52 1.06-1.06 1.49-1.62 3-4 1.122-7.62-4.43-8.03l-1.678-.16c-.96 3.428-3.223 7.603-6.433 12.33-1.47 2.167-3.043 4.3-4.615 6.31-.55.704-1.062 1.343-1.522 1.905l-.548.663c-.118.154-.27.322-.465.495l-.103.088zm0 0" opacity=".5" fill="url(#a)"/><path d="M0 13.5C0 6.044 6.044 0 13.5 0S27 6.044 27 13.5c0 .782-.067 1.557-.198 2.317l.012.166-.037.228c-.595 3.703-3.054 8.474-6.805 13.997-1.47 2.166-3.043 4.298-4.615 6.31-.55.703-1.062 1.342-1.522 1.904l-.548.664c-.118.154-.27.322-.465.495-.224.195-.464.36-.727.482-.406.186-.865.263-1.402.17l-.216-.048c-.862-.226-1.463-.88-1.66-1.68-.107-.434-.087-.814-.006-1.18l.05-.194 2.77-10.258C5.06 25.962 0 20.322 0 13.5zm0 0" fill-opacity=".8" fill="#fff"/><path d="M2 13.5C2 7.15 7.15 2 13.5 2S25 7.15 25 13.5c0 .767-.075 1.517-.22 2.243l.022.15C23.517 23.898 11.72 37.842 11.72 37.842s-.376.525-.734.41c-.368-.094-.205-.562-.205-.562l3.433-12.71c-.236.014-.474.022-.713.022C7.15 25 2 19.85 2 13.5zm0 0" fill="#%s"/><circle fill="#fff" cx="13.5" cy="13.5" r="8.5"/><circle fill="#%s" cx="13.5" cy="13.5" r="4.5"/></g></svg>',

	get: function(color) {
		return this.mCache[color] ? this.mCache[color] : this.create(color);
	},

	create: function(color) {
		return this.mCache[color] = URL.createObjectURL(new Blob([this.mSchema.replace(/%s/gi, this.getHEX(color))], {type: "image/svg+xml"}));
	},

	getHEX: function(color) {
		var hex = parseInt(color).toString(16);

		return "0".repeat((6 - hex.length).range(0, 6)) + hex;
	}

};