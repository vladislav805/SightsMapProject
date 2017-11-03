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

		point: {

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