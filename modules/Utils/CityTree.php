<?

	namespace Utils;

	use Countable;
	use Model\City;

	/**
	 * @param City[] $cities
	 * @return array
	 */
	function generateCitiesTree($cities) {
		$output = [];
		$all = [];
		$dangling = [];

		foreach ($cities as $entry) {
			$id = $entry->getId();

			if (!$entry->getParentId()) {
				$all[$id] = $entry;
				$output[] = &$all[$id];
			} else {
				$dangling[$id] = $entry;
			}
		}

		while (sizeOf($dangling) > 0) {
			foreach ($dangling as $entry) {
				$id = $entry->getId();
				$pid = $entry->getParentId();

				if (isset($all[$pid])) {
					$all[$id] = $entry;
					$all[$pid]->addChild($all[$id]);
					unset($dangling[$entry->getId()]);
				}
			}
		}

		unset($all, $dangling);

		$options = [];
		$options[] = ["label" => "не выбран", "value" => 0, "selected" => true];
		foreach ($output as $item) {
			$options = array_merge($options, getCityOption($item));
		}

		unset($output);

		return $options;
	}

	/**
	 * @param \Model\City $item
	 * @param int $level
	 * @return array
	 */
	function getCityOption($item, $level = 0) {
		$items = [
			"label" => str_repeat(" ", $level) . $item->getName(),
			"value" => $item->getId()
		];

		if ((is_array($item) || $item instanceof Countable) && sizeOf($item->getChildren())) {
			$items = [$items];
			$children = $item->getChildren();
			foreach ($children as $child) {
				$cities = getCityOption($child, $level + 1);

				if (isAssoc($cities)) {
					$items[] = $cities;
				} else {
					$items = array_merge($items, $cities);
				}
			}
			usort($cities, function($a, $b) {
				return mb_strcasecmp(trim($a["label"]), trim($b["label"]));
			});
		} else {
			$items = [$items];
		}

		return $items;
	}