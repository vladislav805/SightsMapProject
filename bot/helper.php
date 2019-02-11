<?
	/** @noinspection PhpUndefinedMethodInspection */

	use Method\Sight\GetNearby;
	use Model\IController;

	/** @noinspection PhpUndefinedClassInspection */
	/** @noinspection PhpUndefinedNamespaceInspection */

	/**
	 * @param string $query
	 * @param int $offset
	 * @return string
	 */
	function packCallbackSearchQuery($query, $offset) {
		return sprintf("s;%d;%s", $offset, $query);
	}

	/**
	 * @param IController $ctrl
	 * @param double $lat
	 * @param double $lng
	 * @param int $offset
	 * @return array
	 */
	function getNearbySights($ctrl, $lat, $lng, $offset = 0) {
		/** @var \Model\ListCount $result */
		$result = $ctrl->perform(new GetNearby([
			"lat" => $lat,
			"lng" => $lng,
			"distance" => 2000,
			"count" => 30
		]));

		$distances = [];
		$distArr = $result->getCustomData("distances");
		foreach ($distArr as $item) {
			$distances[$item["sightId"]] = $item["distance"];
		}

		$items = $result->getItems();
		$items = array_splice($items, $offset, TG_BOT_SIGHTS_ITEMS_PER_PAGE);

		return [$result->getCount(), $items, $distances];
	}

	/**
	 * @param double $lat
	 * @param double $lng
	 * @param int $offset
	 * @return string
	 */
	function packCallbackNearby($lat, $lng, $offset) {
		return sprintf("n;%d;%.8f;%.8f", $offset, $lat, $lng);
	}