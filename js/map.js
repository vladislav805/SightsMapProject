/*
var ___Map = {

	___mMap: null,
	___mPoints: null,

	___init: function() {
	},

	___initMap: function() {


		var myButton = new ymaps.control.Button({
			data: {
				content: "<b>Button</b>"
			},
			options: {
				selectOnClick: false
			}
		});
		myButton.events.add("click", function() { alert('Щёлк'); });
		this.mMap.controls.add(myButton, {
			float: "none",
			position: {bottom: 10, left: 10}
		});

	},


	lastOpenedBalloon: null,


	___showPoint: function(point) {
		var zoom = Math.max(this.mMap.getZoom(), 15);
		this.mMap.setCenter(point.getCoordinates(), zoom, { duration: 400, useMapMargin: true });
	},







	___handle: {

		map: {
			createPlacemark: function(args) {
				return; // TODO
				var point = new Point({
						ownerId: Main.getSession().getId(),
						pointId: -1,
						lat: args.coords[0],
						lng: args.coords[1]
					}),
					place = point.getGeoObject();
				this.mPoints.add(place);
				point.getPlacemark().edit();
			}

		},

		point: {

			move: function(point) {
				var toast = null;
				point.getPlacemark().move().then(function(coords) {
					toast = new Toast("Moving...").open(60000);
					return vlad805.api.online.points.move(point.getId(), coords[0], coords[1]);
				}).then(function(result) {
					point.lat = result.lat;
					point.lng = result.lng;

					toast.setText("Successfully moved!").open(800);
					Main.fire(EventCode.POINT_MOVED, {
						point: point
					});
				});
			},

			visit: function(point, button) {
				if (button.classList.contains(Const.CLASS_NAME_BUTTON_DISABLED)) {
					return;
				}

				var toast = new Toast("Visiting...").open(60000);

				point.isVisit = 1;

				button.classList.add(Const.CLASS_NAME_BUTTON_DISABLED);
				vlad805.api.online.points.visit(point.getId(), true).then(function(result) {
					point.dateVisit = new Date(result.dateVisit * 1000);
					point.isVisited = result.isVisited;

					Main.fire(Const.POINT_EDITED, {
						point: point,
						data: {
							dateVisit: new Date(result.dateVisit * 1000),
							isVisited: result.isVisited
						},
						result: result
					});
					toast.setText("Successfully marked as visited!").open(800);
					button.parentNode.removeChild(button);
				});

			},

			link: function(point) {
				var success = copy2clipboard(point.getLink());

				new Toast(success ? "Link copied to clipboard!" : "Oops, something going wrong...").open(1000);
			},

			edit: function(point) {
				point.getPlacemark().edit();
			},

			save: function(point, event) {
				event.preventDefault();

				var isExists = point.isExists(),
					toast = new Toast("Saving...").open(60000);

				point.title = this.title.value.trim();
				point.description = this.description.value.trim();
				point.isPublic = this.isPublic.checked;

				if (getSelectedValuesInSelect(this.markIds).map(toInt)  )

				(!isExists
					? vlad805.api.online.points.add(point.getSingle())
					: vlad805.api.online.points.edit(point.getId(), point.getSingle())
				).then(function(result) {
					if (isExists) {
						point.dateUpdated = new Date(result.dateUpdated * 1000);
					} else {
						point.pointId = result.pointId;
						point.dateCreated = new Date(result.dateCreated * 1000);
						point.canVisit = true;
						point.canEdit = true;
						point.canDelete = true;
					}

					toast.setText("Successfully saved!").open(800);
					point.notify();

					if (isExists) {
						Main.fire(Const.POINT_EDITED, {
							point: point
						});
					} else {
						Main.fire(Const.POINT_CREATED, {
							point: point
						});
					}
				}.bind(this)).catch(function(err)  { console.error(err) });

				return false;
			},

			remove: function(point) {
				var confirmed = function() {
						toast.setButtons([]).setText("Removing...");
						vlad805.api.online.points.remove(point.getId()).then(function(result) {
							if (result) {
								toast.setText("Successfully removed!").open(1000);
								Main.fire(Const.POINT_REMOVED, {point: point});
							}
						});
					},
					rejected = function() {
						toast.close();
					},
					toast = new Toast("Are you sure, that you want remove this placemark?", {
						buttons: [
							{ label: "Yes", onclick: confirmed },
							{ label: "No", onclick: rejected }
						]
					}).open(15000);
			}

		},

		___event: {

			onPointCreated: function(args) {
				PageSide.addItem(args.point, true);
			},

			onPointEdited: function(args) {
				// normal callback in Map.handle.map.save -> Promise.then
				if (!args.point) {
					return;
				}

				args.point.getPlacemark().setNormalProperties();
			},

			onPointMoved: function(args) {
				// normal callback in Map.handle.map.save -> Promise.then
				if (!args.point) {
					return;
				}

				args.point.getPlacemark().setNormalProperties();

			},

			onPointRemoved: function(args) {
			}

		}

	}
};
*/