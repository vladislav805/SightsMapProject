/** @type {{
 *     id: int,
 *     title: string,
 *     image: {src: string},
 *     text: string,
 *     backgroundColor: string=,
 *     previousId: int=,
 *     nextId: int=,
 *     textColor: string=,
 *     __nodes: object
 * }} Slide */
var Slide = {};

/**
 * @param {Slide[]} slides
 * @param {{
 *     next: {label: string},
 *     previous: {label: string},
 *     end: {label: string},
 *     animationDuration: int=,
 *     onBeforeSlideChange: function=,
 *     onAfterSlideChange: function=,
 *     onEnd: function=
 * }} options
 * @constructor
 */
function FullScreenTextSlider(slides, options) {
	this.__mSlides = slides;
	this.__mOptions = options || {};
	this.__init();
}

FullScreenTextSlider.prototype = {

	/** @var {Slide[]} __mSlides */
	__mSlides: null,

	/** @var {object} __mOptions */
	__mOptions: null,

	/** @var {Node} __mNodeWrap */
	__mNodeWrap: null,

	/** @var {Node} __mNodeArea */
	__mNodeArea: null,

	/** @var {{wrap: Node, previous: Node, next: Node}|null} */
	__mControlPanel: null,

	/** @var {int} __mCurrentIndex */
	__mCurrentIndex: 0,

	/** @var {*} __mSlideMap */
	__mSlideMap: null,

	__init: function () {
		this.__mNodeArea = ce("div", {"class": "slides-area"});
		this.__mControlPanel = this.__makeControlPanel();
		this.__mNodeWrap = ce("div", {"class": "slides"}, [this.__mNodeArea, this.__mControlPanel.wrap]);
		this.__mCurrentIndex = 0;
		this.__initialInsert();
		if (this.__mOptions) {
			if (this.__mOptions.animationDuration) {
				this.__mNodeArea.style.transitionDuration = this.__mOptions.animationDuration + "ms";
			}
		}
	},

	__initialInsert: function() {
		this.__mSlideMap = {};
		this.__mSlides.map(slide => {
			this.__mSlideMap[slide.id] = slide;
			slide.__nodes = this.__makeSlideNode(slide);
		});
	},

	/**
	 * @param {Slide} slide
	 * @private
	 */
	__makeSlideNode: function(slide) {
		const obj = {};

		obj.wrap = ce("div", {"class": "slide", "data-sid": slide.id}, [
			obj.title = ce("h3", null, null, slide.title),
			obj.image = (slide.image ? ce("img", {src: slide.image.src}) : null),
			obj.text = ce("div", null, null, slide.text)
		]);

		return obj;
	},

	__makeControlPanel: function() {
		const o = {};
		o.wrap = ce("div", {"class": "slides-ctl"}, [
			o.previous = ce("div", {"class": "slides-ctl-item slides-ctl--previous"}, null, this.__mOptions.previous.label),
			o.next = ce("div", {"class": "slides-ctl-item slides-ctl--next"}, null, this.__mOptions.next.label)
		]);

		o.previous.addEventListener("click", e => this.previous());
		o.next.addEventListener("click", e => this.next());

		return o;
	},

	__getRoot: function() {
		return this.__mNodeWrap;
	},

	/**
	 * @returns {int}
	 */
	getCurrentIndex: function() {
		return this.__mCurrentIndex;
	},

	/**
	 * @returns {int}
	 */
	getId: function() {
		return this.__mSlides[this.__mCurrentIndex].id;
	},

	/**
	 * @returns {int}
	 */
	getSlideCount: function() {
		return this.__mSlides.length;
	},

	/**
	 * @param {int} index
	 * @private
	 */
	__replace: function(index) {
		if (index === this.getSlideCount() || index === FullScreenTextSlider.SLIDE_ID_END) {
			return this.__onEnd();
		}

		if (index < 0 || index > this.getSlideCount()) {
			throw new RangeError("index=" + index);
		}

		const allow = this.__mOptions && this.__mOptions.onBeforeSlideChange ? this.__mOptions.onBeforeSlideChange(this.__mSlides[index].id) : true;

		if (!allow) {
			return;
		}

		this.__mCurrentIndex = index;
		this.__mNodeWrap.dataset.current = String(index);
		this.__mNodeWrap.style.backgroundColor = this.__mSlides[index].backgroundColor || "white";
		this.__mNodeWrap.style.color = this.__mSlides[index].textColor || "black";
		this.__mOptions && this.__mOptions.onAfterSlideChange && this.__mOptions.onAfterSlideChange(this.__mSlides[index].id);
		this.__showAndHideControlButtons();
	},

	__showAndHideControlButtons: function() {
		const i = this.getCurrentIndex();
		const cur = this.__mSlides[i];
		this.__mControlPanel.previous.style.visibility = i - 1 < 0 || ("previousId" in cur && cur.previousId === FullScreenTextSlider.SLIDE_ID_END) ? "hidden" : "visible";
		this.__mControlPanel.next.style.visibility = i >= this.getSlideCount() ? "hidden" : "visible";
		this.__mControlPanel.next.textContent = i + 1 === this.getSlideCount() || ("nextId" in cur && cur.nextId === FullScreenTextSlider.SLIDE_ID_END) ? this.__mOptions.end.label : this.__mOptions.next.label;
	},

	previous: function() {
		const cur = this.__mSlides[this.__mCurrentIndex];
		this.__replace("previousId" in cur ? cur.previousId : this.__mCurrentIndex - 1);
	},

	next: function() {
		const cur = this.__mSlides[this.__mCurrentIndex];
		this.__replace("nextId" in cur ? cur.nextId : this.__mCurrentIndex + 1);
	},

	/**
	 *
	 * @param {int} id
	 * @returns {int}
	 * @private
	 */
	__findIndexById: function(id) {
		let index = -1;
		for (let i = 0, l = this.__mSlides.length; i < l; ++i) {
			if (this.__mSlides[i].id === id) {
				index = i;
				break;
			}
		}
		return index;
	},

	go: function(id) {
		const index = this.__findIndexById(id);

		if (!~index) {
			throw new RangeError("slide with id " + id + " not found");
		}

		this.__replace(index);
	},

	/*update: function(slide, id) {
		const old = this.__mSlideMap[id];

		slide.id = id;
		this.__mSlideMap[id] = slide;

		slide.__nodes = this.__makeSlideNode(slide);

		this.__mNodeArea.replaceChild(slide.__nodes.wrap, old.__nodes.wrap);

		this.__replace(this.__mCurrentIndex);
	},*/

	__onEnd: function() {
		this.__mOptions && this.__mOptions.onEnd && this.__mOptions.onEnd(this);
	},

	close: function() {
		if (!this.__mNodeWrap.parentNode) {
			return;
		}

		this.__mNodeWrap.parentNode.removeChild(this.__mNodeWrap);
	},

	inject: function() {
		this.__injectSlides();
		this.__replace(0);
		document.getElementsByTagName("body")[0].appendChild(this.__getRoot());
	},

	__injectSlides: function() {
		this.__mSlides.forEach(slide => this.__mNodeArea.appendChild(slide.__nodes.wrap));
	},

	querySelector: function(selector, current) {
		return !current
			? this.__mNodeArea.querySelector(selector)
			: this.__mSlides[this.__mCurrentIndex].__nodes.wrap.querySelector(selector);
	}
};

FullScreenTextSlider.SLIDE_ID_END = -5;