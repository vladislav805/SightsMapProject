(function(api) {
	api.admin = {
		setBan: function(userId, reason, comment) {
			return api.request("admin.setBan", {userId: userId, reason: reason, comment: comment || ""});
		},

		setUserJob: function(userId, status) {
			return api.request("admin.setUserJob", {userId: userId, status: status});
		}
	};
})(API);

const Admin = {

	initBanPage: function() {
		ge("__adminBanAdd").addEventListener("submit", function(e) {
			return Admin.banUser(e, this);
		});
	},

	initJobsPage: function() {
		ge("__adminJobSet").addEventListener("submit", function(e) {
			return Admin.setJob(e, this);
		});
	},

	banUser: function(event, form) {
		event && event.preventDefault();

		const data = shakeOutForm(form);

		API.admin.setBan(data.userId, data.reason, data.comment).then(res => {
			refreshCurrent();
		});

		return false;
	},

	unbanUser: function(node) {
		const userId = +node.dataset.uid;

		API.admin.setBan(userId, 0).then(() => {
			Array.from(document.querySelectorAll(".admin-banned-user-" + userId)).forEach(item => {
				item.parentNode.removeChild(item);
			});
		});
	},

	setJob: function(event, form) {
		event && event.preventDefault();

		const data = shakeOutForm(form);

		API.admin.setUserJob(data.userId, data.status).then(res => {
			refreshCurrent();
		});

		return false;
	},

	focusSetJob: function(node) {
		const form = ge("__adminJobSet");
		const opts = Array.from(form.elements["status"].options).map(node => node.value);

		form.parentNode.classList.add("spoiler--open");
		form.elements["userId"].value = node.dataset.userId;
		form.elements["status"].selectedIndex = opts.indexOf(node.dataset.userStatus);
	}
};