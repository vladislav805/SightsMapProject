/**
 *
 * @param {{title: string|Node, content: string|Node, footer: string|Node=, closeByClickOutside: boolean=}} options
 * @constructor
 */
function Modal(options) {
	options = options || {};
	this.init();
	options.title && this.setTitle(options.title);
	options.content && this.setContent(options.content);
	options.footer && this.setFooter(options.footer);
	options.closeByClickOutside && this.__initEventsOutside();
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
		this.__getBody().appendChild(this.mNodeWrap);
	},

	__initEventsOutside: function() {
		this.mNodeWrap.addEventListener("click", event => event.target === self.mNodeWrap && self.hide(), false);
	},

	setTitle: function(title) {
		this.mNodeTitle.textContent = title;
		return this;
	},

	setContent: function(content) {
		if (content instanceof HTMLElement) {
			this.mNodeContent.innerHTML = "";
			this.mNodeContent.appendChild(content);
		} else {
			this.mNodeContent.innerHTML = content;
		}
		this.setPlain(!(content instanceof HTMLElement));
		return this;
	},

	setFooter: function(footer) {
		if (footer instanceof HTMLElement) {
			this.mNodeFooter.innerHTML = "";
			this.mNodeFooter.appendChild(footer);
		} else {
			this.mNodeFooter.innerHTML = footer;
		}
		return this;
	},

	setPlain: function(state) {
		this.mNodeContent.classList[state ? "add" : "remove"](Modal.CLASS_NAME_PLAIN);
		return this;
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
		this.__getBody().removeChild(this.mNodeWrap);
		this.mNodeWrap.remove && this.mNodeWrap.remove();
	},

	releaseAfter: function(time) {
		setTimeout(this.release.bind(this), time);
		return this;
	},

	__getBody: function() {
		return document.getElementsByTagName("body")[0];
	}

};

Modal.CLASS_NAME_OPENED = "modal-opened";
Modal.CLASS_NAME_PLAIN = "modal-content-plain";


function xConfirm(title, text, labelOk, labelCancel, onOk, onReject) {
	var content,
		footer,
		modal = new Modal({
			title: title,
			content: content = ce("form", null, [
				ce("div", {}, text)
			])
		});

	footer = ce("div", {"class": "modal-confirm-footer"}, [
		ce("input", {type: "button", value: labelCancel, onclick: modal.release.bind(modal)}),
		ce("input", {type: "submit", value: labelOk})
	]);

	content.addEventListener("submit", event => {
		event.preventDefault();
		modal.release();
		onOk && onOk();
		return false;
	});

	footer.firstElementChild.addEventListener("click", event => {
		onReject && onReject();
	});

	content.appendChild(footer);

	modal.show();
}