var Sight = {

	setVisitState: function(node) {
		/** @var {{change: boolean, state: {visited: int, desired: int}}} result */
		API.points.setVisitState(+node.dataset.pid, +node.dataset.visitState).then(function(result) {
			var wrap = node.parentNode;
			wrap.dataset.visitState = node.dataset.visitState;

			var counts = wrap.querySelectorAll("var");
			counts[1].textContent = String(result.state.visited);
			counts[2].textContent = String(result.state.desired);
		}).catch(function(error) {
			console.error(error);
		});
	},

	setRating: function(node, rating) {
		/** @var {{change: boolean, rating: int}} result */
		API.rating.set(+node.dataset.pid, rating).then(function(result) {
			var wrap = node.parentNode;
			wrap.querySelector("strong").textContent = String(result.rating);
		}).catch(function(error) {
			console.error(error);
		});
	},

	verify: function(node) {
		node.disabled = true;
		node.innerHTML = "Подтверждение ... ";
		var newState = !+node.dataset.nowState;
		API.points.setVerify(+node.dataset.pid, newState).then(function(result) {
			node.innerHTML = "Подтверждение = ";
			node.dataset.nowState = String(+newState);
			node.disabled = false;
		});
	},

	archive: function(node) {
		node.disabled = true;
		node.innerHTML = "Архивирование ... ";
		var newState = !+node.dataset.nowState;
		API.points.setArchived(+node.dataset.pid, newState).then(function(result) {
			node.innerHTML = "Архивирование = ";
			node.dataset.nowState = String(+newState);
			node.disabled = false;
		});
	}

};