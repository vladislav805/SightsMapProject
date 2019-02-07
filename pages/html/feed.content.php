<?
	/** @var $this \Pages\FeedPage */

	use Model\Event;
	use Model\IItem;
	use Model\Sight;
	use Model\User;

	function feed_event_item(User $handler, int $date, array $action, IItem $object, bool $isNew) {
		$html = <<<HTML
<div class="feed-item %11\$s">
	<a class="feed-left"><img src="%5\$s" alt="" /></a>
	<div class="feed-item-content">
		<p><a href="/user/%4\$s">%1\$s %2\$s</a> %7\$s <a href="/%10\$s/%9\$d">%8\$s</a></p>
		<p>%6\$s</p>	
	</div>
</div>
HTML;

		printf(
			$html,
			$handler->getFirstName(), // 1
			$handler->getLastName(), // 2
			$handler->getId(), // 3
			$handler->getLogin(), // 4

			// 5
			$object instanceof Sight && $object->getPhoto() && $object->getPhoto()->getId()
				? $object->getPhoto()->getUrlThumbnail()
				: $handler->getPhoto()->getUrlThumbnail(),

			getRelativeDate($date), // 6
			getGenderWord($handler, $action[0], $action[1]), // 7
			$object instanceof Sight // 8
				? $object->getTitle()
				: null,
			$object->getId(), // 9
			$object instanceof Sight ? "sight" : null, // 10
			$isNew ? " feed-item--new" : "" // 11
		);
	}

	/** @var $items Event[] */
	/** @var $sights Sight[] */
	/** @var $users \Model\User[] */
	/** @var $photos \Model\Photo[] */

	foreach ($items as $item) {
		$user = $users[$item->getActionUserId()];
		switch ($item->getType()) {
			case Event::EVENT_POINT_VERIFIED:
				feed_event_item($user, $item->getDate(), [
					"подтвердил Вашу достопримечательность",
					"подтвердила Вашу достопримечательность"
				], $sights[$item->getSubjectId()], $item->isNew());
				break;

			case Event::EVENT_POINT_COMMENT_ADD:
				feed_event_item($user, $item->getDate(), [
					"добавил комментарий на странице Вашей достопримечательности",
					"добавила комментарий на странице Вашей достопримечательности"
				], $sights[$item->getSubjectId()], $item->isNew());
				break;

			case Event::EVENT_POINT_ARCHIVED:
				feed_event_item($user, $item->getDate(), [
					"заархивировал Вашу достопримечательность",
					"заархивировала Вашу достопримечательность"
				], $sights[$item->getSubjectId()], $item->isNew());
				break;

			case Event::EVENT_POINT_RATING_UP:
				feed_event_item($user, $item->getDate(), [
					"повысил рейтинг Вашей достопримечательности",
					"повысила рейтинг Вашей достопримечательности"
				], $sights[$item->getSubjectId()], $item->isNew());
				break;

			case Event::EVENT_POINT_RATING_DOWN:
				feed_event_item($user, $item->getDate(), [
					"понизил рейтинг Вашей достопримечательности",
					"понизила рейтинг Вашей достопримечательности"
				], $sights[$item->getSubjectId()], $item->isNew());
				break;

			default:
				var_dump($item);
		}
	}

	if (!sizeOf($items)) {
		print "Ни одного события ещё не было..";
	}