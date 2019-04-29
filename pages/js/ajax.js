const MODULE_CSS = "css";
const MODULE_JS = "js";

const initAjax = () => {
	const links = document.querySelectorAll("a:not([data-ajax-inited]):not([data-noAjax])");

	Array.from(links).forEach(link => {
		link.addEventListener("click", evt => navigateTo(link.href, evt));
		link.dataset.ajaxInited = "1";
	});

	window.initSpoilers && initSpoilers();
};

/**
 *
 * @param {string} url
 * @param {Event|null} evt
 * @param {{silent: boolean=}=} options
 * @returns {boolean}
 */
const navigateTo = (url, evt, options) => {

	if (evt && url.indexOf("sights.vlad805") < 0) {
		return true;
	}

	options = options || {};
	evt && evt.preventDefault();
	let dstUrl;
	setLoadingOverlayVisibility(true);
	fetch(url + (~url.indexOf("?") ? "&" : "?") + "_ajax=1", {
		redirect: "follow",
		method: "POST",
		cache: "no-cache",
		credentials: "include",
		headers: {
			"X-Requested-With": "XMLHttpRequest"
		}
	})
		.then(r => {
			dstUrl = r.url;
			return r.json()
		})
		.then(res => showAjaxContent(res, {
			url: dstUrl.replace(/[?&]_ajax=1$/, ""),
			pushState: !options.silent
		}))
		.catch(e => console.error(e));
	return false;
};

const refreshCurrent = () => navigateTo(window.location.pathname + window.location.search, null, {silent: true});

/**
 *
 * @param {{
 *     internal: { title: string, styles: string[], scripts: string[], init: string=, bodyClass: string, notificationsCount: int },
 *     page: { content: string },
 *     ribbon: { image: string, content: string[]|string, block: string= }=,
 *     backLink: { url: string }=
 * }} content
 * @param {{
 *     url: string,
 *     pushState: boolean
 * }} options
 */
const showAjaxContent = (content, options) => {
	options = options || {};

	document.title = content.internal.title;

	document.body.className = content.page.bodyClass || "";

	if (options.pushState) {
		window.history.pushState(null, content.internal.title, options.url);
	}

	let hatPhoto = ge("hatPhoto");
	if (hatPhoto) {
		hatPhoto.parentNode.dataset.feedCount = String(content.internal.notificationsCount || 0);
	}

	const hasRibbon = "ribbon" in content;

	ge("head").classList[hasRibbon ? "add" : "remove"]("head--ribbon");

	const rootRibbon = ge("ribbon-main");

	rootRibbon.hidden = !hasRibbon;
	if (hasRibbon) {
		const innerRibbon = ge("ribbon-content");
		const ribbonImage = ge("ribbon-image");

		for (let i = rootRibbon.childElementCount - 1; i >= 0; --i) {
			let c = rootRibbon.children[i];
			if (c !== innerRibbon && c !== ribbonImage) {
				rootRibbon.removeChild(c);
			}
		}

		emptyNode(innerRibbon);


		if (content.ribbon.block) {
			rootRibbon.insertAdjacentHTML("afterbegin", content.ribbon.block);
		}

		if (Array.isArray(content.ribbon.content)) {
			content.ribbon.content.forEach((header, index) => innerRibbon.appendChild(ce("h" + (index + 1), null, null, header)));
		} else {
			innerRibbon.insertAdjacentHTML("beforeend", content.ribbon.content);
		}



		ribbonImage.hidden = !content.ribbon.image;

		if (content.ribbon.image) {
			ribbonImage.style.backgroundImage = "url(" + content.ribbon.image + ")";
		}
	}

	ge("head-back").href = content.backLink && content.backLink.url || "";

	document.querySelector(".page-content-inner").innerHTML = content.page.content;
	insertModules(content.internal.styles, MODULE_CSS);
	insertModules(content.internal.scripts, MODULE_JS, data => {
		console.log("===> onInit", content.internal.init);
		Function(content.internal.init).call(window);
	});

	setLoadingOverlayVisibility(false);
	window.scrollTo(0, 0);
	updateHeadRibbonBackgroundOpacity();
	initAjax();
};

const insertModules = (files, type, callback) => {
	let selector;
	let filter;
	let insert;
	let doneCount = 0;
	const checkAll = () => doneCount === files.length && callback && callback(files);
	const onLoadItem = () => ++doneCount && checkAll();
	const head = document.getElementsByTagName("head")[0];
	switch (type) {
		case MODULE_CSS:
			selector = "link[rel=stylesheet]";
			filter = link => link.getAttribute("href");
			insert = file => head.appendChild(ce("link", {rel: "stylesheet", href: file}));
			break;

		case MODULE_JS:
			selector = "script";
			filter = script => script.getAttribute("src");
			insert = file => head.appendChild(ce("script", {src: file, onload: onLoadItem}));
			break;
	}

	const exists = Array.from(document.querySelectorAll(selector)).map(filter);
	files = files.filter(file => !~exists.indexOf(file));
	files.forEach(insert);
	checkAll();
};

const setLoadingOverlayVisibility = state => {
	const html = document.getElementsByTagName("html")[0];
	html.classList[state ? "add" : "remove"]("state--loading");
	html.classList[!state ? "add" : "remove"]("state--loaded");
};

window.addEventListener("load", () => initAjax());
window.addEventListener("popstate", () => navigateTo(window.location.pathname + window.location.search, null, {silent: true}));