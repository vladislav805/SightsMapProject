function PointListItem(point) {
	this.mPoint = point;
	this.mNode = ce("div",{"class": "listItem"}, [
		this.mNodeTitle = ce("div", {"class": "listItem-title"}, null),
		this.mNodeSubtitle = ce("div", {"class": "listItem-subtitle"}, null)
	]);

	this.mWasColor = null;

	this.init();
	this.update();
}

PointListItem.prototype = {

	init: function() {
		this.mNode.addEventListener("mouseenter", this.onChange.bind(this, true));
		this.mNode.addEventListener("mouseleave", this.onChange.bind(this, false));
		this.mNode.addEventListener("click", Map.findAndShowPoint.bind(Map, this.mPoint));
	},

	onChange: function(state) {
		var pm = this.mPoint.getPlacemark().getGeoObject();
		if (state) {
			this.mWasColor = pm.options.get("iconColor");
			pm.options.set({iconColor: "red", zIndex: 9999999});
		} else {
			pm.options.set({iconColor: this.mWasColor, zIndex: this.mPoint.getId()});
		}
	},

	update: function() {
		this.mNodeTitle.textContent = this.mPoint.title;
		this.mNodeSubtitle.textContent = this.mPoint.description.substr(0, 50).replacePlain();
		return this;
	},

	getNode: function() {
		return this.mNode;
	}
};