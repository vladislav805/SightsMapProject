<?
	/** @var $this \Pages\FeedPage */

	use Model\Event;
	use Model\Photo;
	use Model\Sight;
	use Model\User;

	/*
	 * photo
	 * handler
	 * date
	 * action
	 * object
	 * isNew
	 */
	/**
	 * @param array $options
	 */
	function makeEventItem($options) {
		$html = '<div class="feed-item %1$s">
	<a class="feed-left"><img src="%2$s" alt="" /></a>
	<div class="feed-item-content">
		<p>%3$s %4$s %5$s</p>
		<p>%6$s</p>	
	</div>
</div>';
		$photo = isset($options["photo"]) ? $options["photo"] : null;
		$date = getRelativeDate($options["date"]);
		$action = null;

		$object = $options["object"];

		$handler = $options["handler"];
		if ($handler instanceof User) {
			$act = $options["action"];
			array_unshift($act, $handler);
			$action = call_user_func_array("getGenderWord", $act);

			if (!$photo && $handler->getPhoto()) {
				$photo = $handler->getPhoto()->getUrlThumbnail();
			}

			$handler = sprintf('<a href="/user/%1$s">%2$s %3$s</a>', $handler->getLogin(), htmlSpecialChars($handler->getFirstName()), htmlSpecialChars($handler->getLastName()));
		} else {
			$action = $action[0];
		}


		if (!is_string($handler)) {
			return;
		}

		if ($options["object"] instanceof Sight) {
			$object = sprintf('<a href="/sight/%1$d">%2$s</a>', $options["object"]->getId(), $options["object"]->getTitle());
		}

		if ($options["object"] === null) {
			return;
		}

		printf(
			$html,
			$options["isNew"] ? " feed-item--new" : "", // 1
			$photo, // 2
			$handler, // 3
			$action, // 4
			$object, // 5
			$date // 6
		);
	}
?>
	<h3>Последние события</h3>
	<p><input type="button" value="Отметить все просмотренным" onclick="Feed.readAll();" /></p>
<?
	/** @var $items Event[] */
	/** @var $sights Sight[] */
	/** @var $users User[] */
	/** @var $photos Photo[] */

	foreach ($items as $item) {
		$user = $users[$item->getActionUserId()];
		switch ($item->getType()) {
			case Event::EVENT_SIGHT_VERIFIED:
				if (!isset($sights[$item->getSubjectId()])) {
					continue;
				}
				makeEventItem([
					"photo" => null,
					"handler" => $user,
					"date" => $item->getDate(),
					"action" => [
						"подтвердил Вашу достопримечательность",
						"подтвердила Вашу достопримечательность"
					],
					"object" => $sights[$item->getSubjectId()],
					"isNew" => $item->isNew()
				]);
				break;

			case Event::EVENT_SIGHT_COMMENT_ADD:
				makeEventItem([
					"photo" => null,
					"handler" => $user,
					"date" => $item->getDate(),
					"action" => [
						"добавил комментарий на странице Вашей достопримечательности",
						"добавила комментарий на странице Вашей достопримечательности"
					],
					"object" => $sights[$item->getSubjectId()],
					"isNew" => $item->isNew()
				]);
				break;

			case Event::EVENT_SIGHT_ARCHIVED:
				if (!isset($sights[$item->getSubjectId()])) {
					continue;
				}
				makeEventItem([
					"photo" => null,
					"handler" => $user,
					"date" => $item->getDate(),
					"action" => [
						"заархивировал Вашу достопримечательность",
						"заархивировала Вашу достопримечательность"
					],
					"object" => $sights[$item->getSubjectId()],
					"isNew" => $item->isNew()
				]);
				break;
/*
			case Event::EVENT_SIGHT_RATING_UP:
				makeEventItem([
					"photo" => null,
					"handler" => $user,
					"date" => $item->getDate(),
					"action" => [
						"повысил рейтинг Вашей достопримечательности",
						"повысила рейтинг Вашей достопримечательности"
					],
					"object" => $sights[$item->getSubjectId()],
					"isNew" => $item->isNew()
				]);
				break;

			case Event::EVENT_SIGHT_RATING_DOWN:
				makeEventItem([
					"photo" => null,
					"handler" => $user,
					"date" => $item->getDate(),
					"action" => [
						"понизил рейтинг Вашей достопримечательности",
						"понизила рейтинг Вашей достопримечательности"
					],
					"object" => $sights[$item->getSubjectId()],
					"isNew" => $item->isNew()
				]);
				break;
*/
			case Event::EVENT_SIGHT_REMOVED:
				if (!isset($sights[$item->getSubjectId()])) {
					continue;
				}
				makeEventItem([
					"photo" => null,
					"handler" => $user,
					"date" => $item->getDate(),
					"action" => [
						"удалил Вашу достопримечательность",
						"удалила Вашу достопримечательность"
					],
					"object" => $item->getExtraText(),
					"isNew" => $item->isNew()
				]);
				break;

			case Event::EVENT_SIGHT_APPROVED_PHOTO:
				if (!isset($sights[$item->getSubjectId()])) {
					continue;
				}
				makeEventItem([
					"photo" => null,
					"handler" => $user,
					"date" => $item->getDate(),
					"action" => [
						"подтвердил и добавил предложенную Вами фотографию к достопримечательности",
						"подтвердила и добавила предложенную Вами фотографию к достопримечательности"
					],
					"object" => $sights[$item->getSubjectId()],
					"isNew" => $item->isNew()
				]);
				break;

			case Event::EVENT_SIGHT_SUGGESTED_PHOTO:
				if (!isset($sights[$item->getSubjectId()])) {
					continue;
				}
				makeEventItem([
					"photo" => null,
					"handler" => $user,
					"date" => $item->getDate(),
					"action" => [
						"предложил фотографию к Вашей достопримечательности",
						"предложила фотографию к Вашей достопримечательности"
					],
					"object" => $sights[$item->getSubjectId()],
					"isNew" => $item->isNew()
				]);
				break;

			default:
		//		var_dump($item);
		}
	}

	if (!sizeOf($items)) {
		print "Ни одного события ещё не было..";
	}