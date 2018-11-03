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
	}

};