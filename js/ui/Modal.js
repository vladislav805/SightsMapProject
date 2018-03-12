/**
 *
 * @param {{title: string|Node, content: string|Node, footer: string|Node=}} options
 * @constructor
 */
function Modal(options) {
	options = options || {};
	this.init();
	options.title && this.setTitle(options.title);
	options.content && this.setContent(options.content);
	options.footer && this.setFooter(options.footer);
}

Modal.prototype = {

	mNodeWrap: null,
	mNodeWindow: null,
	mNodeHeader: null,
	mNodeTitle: null,
	mNodeContent: null,
	mNodeFooter: null,

	init: function() {
		this.mNodeTitle = ce("div", {"class": "modal-title"});
		this.mNodeHeader = ce("div", {"class": "modal-head"}, [this.mNodeTitle]);
		this.mNodeContent = ce("div", {"class": "modal-content"});
		this.mNodeFooter = ce("div", {"class": "modal-footer"});
		this.mNodeWindow = ce("div", {"class": "modal-window"}, [this.mNodeHeader, this.mNodeContent, this.mNodeFooter]);
		this.mNodeWrap = ce("div", {"class": "modal-wrap"}, [this.mNodeWindow]);
		getBody().appendChild(this.mNodeWrap);
		this.initEvents();
	},

	initEvents: function() {
		var self = this;

		/*this.mNodeWrap.addEventListener("click", function(event) {
			event.target === self.mNodeWrap && self.hide();
		}, false);*/
	},

	setTitle: function(title) {
		this.mNodeTitle.textContent = title;
	},

	setContent: function(content) {
		if (content instanceof HTMLElement) {
			this.mNodeContent.innerHTML = "";
			this.mNodeContent.appendChild(content);
		} else {
			this.mNodeContent.innerHTML = content;
		}
		this.setPlain(!(content instanceof HTMLElement));
	},

	setFooter: function(footer) {
		if (footer instanceof HTMLElement) {
			this.mNodeFooter.innerHTML = "";
			this.mNodeFooter.appendChild(footer);
		} else {
			this.mNodeFooter.innerHTML = footer;
		}
	},

	setPlain: function(state) {
		this.mNodeContent.classList[state ? "add" : "remove"](Modal.CLASS_NAME_PLAIN);
	},

	show: function() {
		this.mNodeWrap.classList.add(Modal.CLASS_NAME_OPENED);
		return this;
	},

	hide: function() {
		this.mNodeWrap.classList.remove(Modal.CLASS_NAME_OPENED);
		return this;
	},

	release: function() {
		getBody().removeChild(this.mNodeWrap);
		this.mNodeWrap.remove && this.mNodeWrap.remove();
	}

};

Modal.CLASS_NAME_OPENED = "modal-opened";
Modal.CLASS_NAME_PLAIN = "modal-content-plain";