<?

	use Model\ListCount;
	use Model\Sight;
	use Telegram\Client;
	use Telegram\Constant\ParseMode;
	use Telegram\Method\EditMessageReplyMarkup;
	use Telegram\Method\EditMessageText;
	use Telegram\Method\SendMessage;
	use Telegram\Method\SendPhoto;
	use Telegram\Model\Keyboard\ForceReply;
	use Telegram\Model\Keyboard\InlineKeyboard;
	use Telegram\Model\Keyboard\InlineKeyboardButton;
	use Telegram\Model\Object\Message;
	use Telegram\Model\Response\CallbackQuery;

	class TelegramBotReplies {

		/** @var Client */
		private $client;

		/** @var Message|CallbackQuery */
		private $message;

		/** @var \Model\IController */
		private $controller;

		const TEXT_NEW_SIGHT = "Отправь мне, пожалуйста, ее местоположение.\nПостарайся как можно точнее выбрать место.";
		const TEXT_NEW_SIGHT_ENTER_TITLE = "Теперь введи название достопримечательности";

		public function __construct(Client $client, $message, \Model\IController $ctrl) {
			$this->client = $client;
			$this->message = $message;
			$this->controller = $ctrl;
		}

		/**
		 * @return int
		 */
		private function toId() {
			return $this->message instanceof Message || $this->message instanceof CallbackQuery ? $this->message->getChatId() : 0;
		}

		/**
		 * Отправляет приветственное сообщение
		 */
		public function sendStartMessage() {
			if ($this->message instanceof CallbackQuery) {
				return;
			}

			$text = sprintf("Привет, %s.\n\nОтправь мне свое местоположение и я отправлю тебе близлежащие достопримечательности. Или просто введи название -- я постараюсь найти.\n\nЕсли хочешь авторизоваться, чтобы использовать весь функционал, введи /auth и следуй инструкциям.", $this->message->getFrom()->getFirstName());

			$this->client->performHookMethod(new SendMessage($this->toId(), $text));
		}

		/**
		 * Отправляет сообщение с авторизационной формой
		 */
		public function sendAuthorizeForm() {
			$text = "Хорошо. Пожалуйста, перейди по ссылке расположенной ниже, авторизуйся (если еще не авторизован) и скопируй код шестизначный со страницы.\n\nПосле этого напиши в ответ эту же команду и код через пробел, например:\n<code>/auth 012345</code>";

			$reply = new SendMessage($this->toId(), $text);
			$reply->setParseMode(ParseMode::HTML);
			$kb = new InlineKeyboard;
			$button = new InlineKeyboardButton("Открыть страницу");
			$button->setUrl("https://sights.vlad805.ru/userarea/telegram");
			$kb->addRow()->addButton($button);
			$reply->setReplyMarkup($kb);
			$this->client->performHookMethod($reply);
		}

		/**
		 * @param \Model\User $user
		 */
		public function sendWelcomeProfileMessage($user) {
			$text = sprintf('Привет, <b>%1$s %2$s</b>!', $user->getFirstName(), $user->getLastName());

			$reply = new SendMessage($this->toId(), $text);
			$reply->setParseMode(ParseMode::HTML);
			$this->client->performHookMethod($reply);
		}

		/**
		 * @param \Model\User $user
		 */
		public function sendProfileMessage($user) {
			$text = sprintf('<b>%1$s %2$s</b>' . PHP_EOL . '%3$s', $user->getFirstName(), $user->getLastName(), $user->getCity() ? $user->getCity()->getName() : "");

			$reply = new SendMessage($this->toId(), $text);
			$reply->setParseMode(ParseMode::HTML);
			$this->client->performHookMethod($reply);
		}

		public function showSightPage(Sight $sight) {
			$reply = null;

			$reply = $sight->getPhoto()
				? new SendPhoto($this->toId(), $sight->getPhoto()->getUrlOriginal())
				: new SendMessage($this->toId());

			$str = [];
			$str[] = sprintf("<b>%s</b>\n%s\n%s",
				$sight->getTitle(),
				$sight->isVerified() ? "✅ Верифицированное место\n" : "",
				truncate($sight->getDescription(), 200)
			);


			if ($this->controller->isAuthorized()) {
				$kb = $this->makeVisitStateKeyboard($sight->getId(), $sight->getVisitState());
				$reply->setReplyMarkup($kb);
			}

			$reply->setText(join(PHP_EOL, $str));
			$reply->setParseMode(ParseMode::HTML);

			$this->client->performHookMethod($reply);
		}

		/**
		 * @param int $sightId
		 * @param int $state
		 * @return InlineKeyboard
		 */
		private function makeVisitStateKeyboard($sightId, $state) {
			$kb = new InlineKeyboard;
			$btn = new InlineKeyboardButton("Открыть на сайте");
			$btn->setUrl("https://sights.vlad805.ru/sight/" . $sightId);

			$kb->addRow()->addButton($btn);

			$visit = $kb->addRow();

			/** @var InlineKeyboardButton[] $buttons */
			$buttons = [
				new InlineKeyboardButton("🚷", "v;" . $sightId . ";0"),
				new InlineKeyboardButton("☑️", "v;" . $sightId . ";1"),
				new InlineKeyboardButton("🚶‍♂️", "v;" . $sightId . ";2")
			];

			foreach ($buttons as $i => $btn) {
				if ($i === $state) {
					$btn->setText("[" . $btn->getText() . "]");
				}
				$visit->addButton($btn);
			}
			return $kb;
		}

		/**
		 * @param string $query
		 * @param ListCount $result
		 * @param int $offset
		 */
		public function makeSearchPage($query, $result, $offset = null) {
			$str = [];
			$kb = new Telegram\Model\Keyboard\InlineKeyboard;

			$count = $result->getCount();

			/** @var Sight[] $items */
			$items = $result->getItems();

			$cursorStart = $offset;
			$cursorEnd = $offset + sizeOf($items);

			$kr = $kb->addRow();

			if ($cursorStart) {
				$kr->addButton(new InlineKeyboardButton("◀️", packCallbackSearchQuery($query, $offset - TG_BOT_SIGHTS_ITEMS_PER_PAGE)));
			}

			$kr->addButton(new InlineKeyboardButton(sprintf("%d…%d / %d", $cursorStart, $cursorEnd, $count), "1"));

			if ($cursorEnd !== $count) {
				$kr->addButton(new InlineKeyboardButton("▶️️", packCallbackSearchQuery($query, $offset + TG_BOT_SIGHTS_ITEMS_PER_PAGE)));
			}

			$i = $cursorStart;
			foreach ($items as $item) {
				$str[] = sprintf("<b>%d</b>. /sight%d\n%s<b>%s</b>\n",
					$i + 1,
					$item->getId(),
					$item->getPhoto() ? "🖼 " : "",
					$item->getTitle()
				);
				$i++;
			}

			$text = join(PHP_EOL, $str);
			$msg = $offset === null
				? new SendMessage($this->toId(), $text)
				: new EditMessageText($this->toId(), $this->message->getMessage()->getId(), $text);
			$msg->setReplyMarkup($kb);
			$msg->setParseMode(ParseMode::HTML);
			$msg->setDisableWebPagePreview(true);
			$this->client->performHookMethod($msg);
		}

		/**
		 * @param array $result
		 * @param double $lat
		 * @param double $lng
		 * @param int|null $offset
		 */
		public function makeNearbyPage($result, $lat, $lng, $offset = null) {
			/** @var int $count */
			/** @var Sight[] $items */
			/** @var array $distances */
			list($count, $items, $distances) = $result;

			$str = [

			];

			$kb = new InlineKeyboard;

			$cursorStart = $offset;
			$cursorEnd = $offset + sizeOf($items);

			$kr = $kb->addRow();

			if ($cursorStart) {
				$kr->addButton(new InlineKeyboardButton("◀️", packCallbackNearby($lat, $lng, $offset - TG_BOT_SIGHTS_ITEMS_PER_PAGE)));
			}

			$kr->addButton(new InlineKeyboardButton(sprintf("%d…%d / %d", $cursorStart, $cursorEnd, $count), "1"));

			if ($cursorEnd !== $count) {
				$kr->addButton(new InlineKeyboardButton("▶️️", packCallbackNearby($lat, $lng, $offset + TG_BOT_SIGHTS_ITEMS_PER_PAGE)));
			}

			$mapArgs = [
				sprintf("%.6f,%.6f,ya_ru", $lng, $lat)
			];

			$i = $cursorStart;
			foreach ($items as $p) {
				$dist = $distances[$p->getId()];
				$distName = "м";

				if ($dist > 1000) {
					$dist /= 1000;
					$distName = "км";
				}

				$str[] = sprintf("%d. /sight%d (<i>%.1f %s</i>)\n%s%s<b>%s</b>\n",
					$i + 1,
					$p->getId(),
					$dist,
					$distName,
					$p->isVerified() ? "✅ " : ($p->isArchived() ? "🚫 " : ""),
					$p->getPhoto() ? "🖼 " : "",
					$p->getTitle()
				);
				$i++;

				$mapArgs[] = sprintf("%.6f,%.6f,pm2wtm%d", $p->getLng(), $p->getLat(), $i);
			}

			$url = "https://static-maps.yandex.ru/1.x/?l=map&size=600,400&pt=" . join("~", $mapArgs);
			array_unshift($str, sprintf("Найдено<a href=\"%s\">&#8204;</a> %d %s от этого места не дальше двух километров\n", $url, $count, pluralize($count, ["место", "места", "мест"])));


			$text = join(PHP_EOL, $str);
			$msg = $offset === null
				? new SendMessage($this->toId(), $text)
				: new EditMessageText($this->toId(), $this->message->getMessage()->getId(), $text);
			$msg->setReplyMarkup($kb);
			$msg->setParseMode(ParseMode::HTML);
			$this->client->performHookMethod($msg);
		}

		public function parrot($text) {
			$msg = new SendMessage($this->toId(), sprintf("<code>%s</code>", json_encode($text, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)));
			$msg->setParseMode(ParseMode::HTML);
			$this->client->performHookMethod($msg);
			exit;
		}

		public function onVisitStateChange($sightId, $state) {
			$kb = $this->makeVisitStateKeyboard($sightId, $state);
			$msg = new EditMessageReplyMarkup($this->toId(), $this->message->getMessage()->getId(), $kb);
			$this->client->performHookMethod($msg);
		}

		public function attemptCreateSight() {
			$msg = new SendMessage($this->toId(), self::TEXT_NEW_SIGHT);
			$msg->setReplyMarkup(new ForceReply());
			$this->client->performHookMethod($msg);
		}

		public function createAfterLocation() {
			$msg = new SendMessage($this->toId(), self::TEXT_NEW_SIGHT_ENTER_TITLE);
			$msg->setReplyMarkup(new ForceReply());
			$this->client->performHookMethod($msg);
		}

	}