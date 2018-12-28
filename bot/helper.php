<?
	/** @noinspection PhpUndefinedMethodInspection */

	use Model\IController;

	/** @noinspection PhpUndefinedClassInspection */
	/** @noinspection PhpUndefinedNamespaceInspection */

	/**
	 * @param IController $sm
	 * @param string $query
	 * @param int $offset
	 * @return array
	 */
	function generateMessageAndButtonsBySearchQuery($sm, $query, $offset = 0) {
		$PER_PAGE = 5;

		$result = $sm->perform(new Method\Sight\Search(["query" => $query, "count" => $PER_PAGE, "offset" => $offset]));

		$str = [];
		$kb = new Telegram\Model\Keyboard\InlineKeyboard;

		$count = $result->getCount();
		$items = $result->getItems();

		$cursorStart = $offset;
		$cursorEnd = $offset + sizeOf($items);

		$kr = $kb->addRow();

		if ($cursorStart) {
			$kr->addButton(new \Telegram\Model\Keyboard\InlineKeyboardButton("◀️", packCallbackSearchQuery($query, $offset - $PER_PAGE)));
		}

		$kr->addButton(new \Telegram\Model\Keyboard\InlineKeyboardButton(sprintf("%d…%d / %d", $cursorStart, $cursorEnd, $count), "1"));

		if ($cursorEnd !== $count) {
			$kr->addButton(new \Telegram\Model\Keyboard\InlineKeyboardButton("▶️️", packCallbackSearchQuery($query, $offset + $PER_PAGE)));
		}

		$i = $cursorStart;
		foreach ($items as $item) {
			$str[] = sprintf("<b>%d</b>. /place%d\n%s<b>%s</b>\n",
				$i + 1,
				$item->getId(),
				$item->getPhoto() ? "🖼 " : "",
				$item->getTitle()
			);
			$i++;
		}

		return [
			"text" => join(PHP_EOL, $str),
			"keyboard" => $kb
		];
	}

	/**
	 * @param string $query
	 * @param int $offset
	 * @return string
	 */
	function packCallbackSearchQuery($query, $offset) {
		return sprintf("s@%d@%s", $offset, $query);
	}

	/**
	 * @param IController $sm
	 * @param double $lat
	 * @param double $lng
	 * @param int $offset
	 * @return array
	 */
	function generateMessageAndButtonsByNearby($sm, $lat, $lng, $offset = 0) {
		$PER_PAGE = 5;

		$result = $sm->perform(new Method\Sight\GetNearby([
			"lat" => $lat,
			"lng" => $lng,
			"distance" => 2,
			"count" => 20
		]));

		$distances = [];
		$distArr = $result->getCustomData("distances");
		foreach ($distArr as $item) {
			$distances[$item["sightId"]] = $item["distance"];
		}

		$count = $result->getCount();
		/** @var \Model\Sight[] $items */
		$items = $result->getItems();

		$str = [];
		$str[] = sprintf("Найдено %d %s от Вас в 2км\n", $count, pluralize($count, ["место", "места", "мест"]));

		$items = array_splice($items, $offset, $PER_PAGE);

		$kb = new Telegram\Model\Keyboard\InlineKeyboard;

		$cursorStart = $offset;
		$cursorEnd = $offset + sizeOf($items);

		$kr = $kb->addRow();

		if ($cursorStart) {
			$kr->addButton(new \Telegram\Model\Keyboard\InlineKeyboardButton("◀️", packCallbackNearby($lat, $lng, $offset - $PER_PAGE)));
		}

		$kr->addButton(new \Telegram\Model\Keyboard\InlineKeyboardButton(sprintf("%d…%d / %d", $cursorStart, $cursorEnd, $count), "1"));

		if ($cursorEnd !== $count) {
			$kr->addButton(new \Telegram\Model\Keyboard\InlineKeyboardButton("▶️️", packCallbackNearby($lat, $lng, $offset + $PER_PAGE)));
		}

		$i = $cursorStart;
		foreach ($items as $p) {
			$dist = $distances[$p->getId()];
			$distName = "км";

			if ($dist < 1) {
				$dist *= 1000;
				$distName = "м";
			}

			$str[] = sprintf("%d. /place%d (<i>%.1f %s</i>)\n%s%s<b>%s</b>\n",
				$i + 1,
				$p->getId(),
				$dist,
				$distName,
				$p->isVerified() ? "✅ " : ($p->isArchived() ? "🚫 " : ""),
				$p->getPhoto() ? "🖼 " : "",
				$p->getTitle()
			);
			$i++;
		}

		return [
			"text" => join(PHP_EOL, $str),
			"keyboard" => $kb
		];
	}

	/**
	 * @param double $lat
	 * @param double $lng
	 * @param int $offset
	 * @return string
	 */
	function packCallbackNearby($lat, $lng, $offset) {
		return sprintf("n@%d@%.8f@%.8f", $offset, $lat, $lng);
	}