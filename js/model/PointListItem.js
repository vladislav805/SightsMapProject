/**
 *
 * @param {Place} place
 * @constructor
 */
function PointListItem(place) {
	this.mPlace = place;
	this.mNode = ce("div",{"class": "listItem"}, [
		this.mNodeTitle = ce("div", {"class": "listItem-title"}, null),
		this.mNodeSubtitle = ce("div", {"class": "listItem-subtitle"}, null)
	]);

	this.mWasColor = null;
	this.mWasClusterColor = null;

	this.init();
	this.update();
}

PointListItem.prototype = {

	init: function() {
		this.mNode.addEventListener("mouseenter", this.onChangeMouseState.bind(this, true));
		this.mNode.addEventListener("mouseleave", this.onChangeMouseState.bind(this, false));
		this.mNode.addEventListener("click", this.showPointOnMap.bind(this));
	},

	onChangeMouseState: function(state) {
		Main.fire(EventCode.POINT_HIGHLIGHT, {place: this.mPlace, state: state});
	},

	showPointOnMap: function() {
		Main.fire(EventCode.POINT_SHOW, {place: this.mPlace});
	},

	update: function() {
		this.mNodeTitle.textContent = this.mPlace.getInfo().getTitle();
		this.mNodeSubtitle.textContent = this.mPlace.getInfo().getDescription().substr(0, 50);
		return this;
	},

	getNode: function() {
		return this.mNode;
	}
};