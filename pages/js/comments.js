"use strict";

var Comments = {

	init: function() {
		onReady(() => {
			/** @var {HTMLElement[]|NodeListOf} forms */
			const forms = document.querySelectorAll(".comment-form");

			Array.from(forms).forEach(form => setFormListener(form, Comments.onCommentSubmit.bind(form, parseInt(form.dataset.sid))));
		});
	},

	/**
	 *
	 * @param {int} sightId
	 * @param {Event} event
	 * @returns {boolean}
	 */
	onCommentSubmit: function(sightId, event) {
		event.preventDefault();

		const params = shakeOutForm(this);

		if (!params.text.length) {
			alert("Пусто");
			return false;
		}

		API.comments.add(sightId, params.text).then(result => {
			const list = document.querySelector(".comments-items[data-sid='" + sightId + "']");
			Comments.__insertComment(list, result.comment, result.user);
			this.reset();
		}).catch(error => {
			console.error(error);
		});

		return false;
	},

	/**
	 * @param {Element} parent
	 * @param {Comment} comment
	 * @param {User} user
	 * @private
	 */
	__insertComment: function(parent, comment, user) {
		parent.insertAdjacentHTML("beforeend", [
			"<div class=\"comment-item\" data-comment-id=\"" + comment.commentId + "\">",
			"<div class=\"comment-author-photo\" style=\"background-image: url('" + user.photo.photo200 + "')\"></div>",
			"<div class=\"comment-content\">",
			"<h6 class=\"comment-author-name\">",
			"<a href=\"/user/" + user.login + "\">",
			user.firstName + " " + user.lastName,
			"</a></h6>",
			"<div class=\"comment-text\">" + comment.text + "</div>",
			"<div class=\"comment-footer\">",
			new Date(comment.date * 1000).format("%d/%m/%Y %H:%M"), // TODO: constant
			" | <span class=\"comment-action\" onclick=\"Comments.removeComment(this);\" data-cid=\"" + comment.commentId + "\">Удалить</span>",
			"</div>",
			"</div>",
			"</div>"
		].join(""));
	},

	/**
	 *
	 * @param {Element} node
	 */
	removeComment: function(node) {
		const commentId = parseInt(node.dataset.cid);
		API.comments.remove(commentId).then(result => {
			document.querySelector("[data-comment-id='" + commentId + "']").remove();
		}).catch(error => console.error(error));
	}

};