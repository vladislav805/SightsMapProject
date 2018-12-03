<?

	use Method\Point\GetById as getPhotoById;
	use Method\Point\GetNearby;
	use Method\User\GetById as getUserById;
	use Method\User\SetTelegramId;
	use Telegram\Constant\ParseMode;
	use Telegram\Method\EditMessageText;
	use Telegram\Method\GetWebhookInfo;
	use Telegram\Method\SendMessage;
	use Telegram\Method\SendPhoto;
	use Telegram\Method\SetWebhook;
	use Telegram\Model\Keyboard\InlineKeyboard;
	use Telegram\Model\Keyboard\InlineKeyboardButton;
	use Telegram\Model\Object\Message;
	use Telegram\Model\Response\CallbackQuery;
	use Telegram\Model\WebhookInfo;
	use Telegram\Utils\Logger;
	use Telegram\Utils\WebhookInfoTable;

	/** @noinspection PhpUndefinedNamespaceInspection */
	/** @noinspection PhpUndefinedClassInspection */
	/** @noinspection PhpUndefinedMethodInspection */

	require_once "../autoload.php";
	require_once "../config.php";
	require_once "../functions.php";
	require_once "helper.php";


	ini_set("error_log", "./log.log");
	error_reporting(E_ALL);
	$db = new PDO(sprintf("mysql:host=%s;dbname=%s;charset=utf8", DB_HOST, DB_NAME), DB_USER, DB_PASS);

	$sm = new MainController($db);

	$tg = new Telegram\Client(TELEGRAM_BOT_SECRET);

	if (isset($_REQUEST["check"])) {
		WebhookInfoTable::outputTable(new WebhookInfo($tg->performSingleMethod(new GetWebhookInfo)));
		exit;
	}

	if (isset($_REQUEST["setup"])) {
		print json_encode($tg->performSingleMethod(new SetWebhook("https://" . DOMAIN_MAIN . "/bot/", 15)));
		exit;
	}

	define("TB_REGEXP_AUTH", "/^\/auth ([0-9A-Fa-f]+)$/imu");
	define("TB_REGEXP_PLACE", "/^\/place(\d+)$/imu");

	define("TB_PHRASE_START", "Привет, %s.\n\nОтправь мне свое местоположение и я отправлю тебе близлежащие достопримечательности. Или просто введи название -- я постараюсь найти.\n\nДля авторизации: /auth");
	define("TB_PHRASE_AUTH", "Перейдите по ссылке расположенной ниже, авторизуйтесь и скопируйте код со страницы.\nПосле этого напишите в ответ команду эту же команду и код через пробел, например:\n<code>/auth 012345</code>\n\nhttps://sights.vlad805.ru/user/telegram");


	$tg->setLogger(new Logger("events.log", Logger::LOG_MODE_MESSAGE | Logger::LOG_MODE_INCLUDE_RAW | Logger::LOG_MODE_API_RESULT));

	/** @noinspection PhpUnhandledExceptionInspection */
	$tg->onMessage(function(Telegram\Client $tg, Message $message) use ($sm) {

		$chatId = $message->getChatId();

		$sendReply = function($text) use ($chatId, $tg) {
			$tg->performHookMethod((new SendMessage($chatId, $text))->setParseMode(ParseMode::HTML));
			exit;
		};

		/** @var \Model\User $sUser */
		$sUser = $sm->setTelegramId($message->getFrom()->getId());

		if ($message->hasText()) {
			switch ($message->getText()) {
				case "/start":
					$str = sprintf(TB_PHRASE_START, $message->getFrom()->getFirstName());
					$sendReply($str);
					break;

				case "/auth":
					$sendReply(TB_PHRASE_AUTH);
					break;

				case "/profile":
					if (!$sUser) {
						$sendReply(TB_PHRASE_AUTH);
					}

					$sendReply(sprintf("<b>@%s</b>\n<i>%s</i>", $sUser->getLogin(), $sUser->getCity() ? $sUser->getCity()->getName() : ""));
					break;
			}

			// Authorize
			if (preg_match_all(TB_REGEXP_AUTH, $message->getText(), $result, PREG_SET_ORDER)) {
				$code = hexdec($result[0][1]);

				$key = mb_strtolower("telegramAuth" . $code);

				$redis = $sm->getRedis();

				if (!$redis->exists($key)) {
					$sendReply("Invalid code");
				}

				$result = json_decode($redis->get($key));

				/** @var \Model\User $user */
				$user = $sm->perform(new getUserById(["userIds" => $result->userId]));

				$sm->perform(new SetTelegramId(["userId" => $user->getId(), "telegramId" => $message->getFrom()->getId()]));

				$text = sprintf("Привет, %s!", $user->getLogin());

				$sendReply($text);
				exit;
			}

			/**
			 * Конкретное место
			 */
			if (preg_match_all(TB_REGEXP_PLACE, $message->getText(), $result, PREG_SET_ORDER)) {
				$pid = (int) $result[0][1];

				/** @var \Model\Point $place */
				$place = $sm->perform(new getPhotoById(["pointId" => $pid]));

				if (!$place) {
					return;
				}

				if ($place->getPhoto()) {
					$tg->performSingleMethod(new SendPhoto($chatId, $place->getPhoto()->getUrlOriginal()));
				}


				$str = [];
				$str[] = sprintf("<b>%s</b>\n%s\n%s",
					$place->getTitle(),
					$place->isVerified() ? "✅ Верифицированное место\n" : "",
					truncate($place->getDescription(), 200)
				);

				$kb = new InlineKeyboard;
				$btn = new InlineKeyboardButton("Перейти на сайт");
				$btn->setUrl("https://sights.vlad805.ru/place/" . $place->getId());
				$kb->addRow()->addButton($btn);

				if ($sUser) {
					$visit = $kb->addRow();

					/** @var InlineKeyboardButton[] $buttons */
					$buttons = [
						new InlineKeyboardButton("Не видел", "visit;" . $pid . ";0"),
						new InlineKeyboardButton("Видел", "visit;" . $pid . ";1"),
						new InlineKeyboardButton("Хочу", "visit;" . $pid . ";2")
					];

					foreach ($buttons as $i => $btn) {
						if ($i === $place->getVisitState()) {
							$btn->setText("🔵 " . $btn->getText());
						}
						$visit->addButton($btn);
					}
				}


				$reply = new SendMessage($chatId, join(PHP_EOL, $str));
				$reply->setParseMode(ParseMode::HTML);
				$reply->setDisableWebPagePreview(true);
				$reply->setReplyMarkup($kb);
				$tg->performHookMethod($reply);
				exit;
			}

			/**
			 * Поиск по словам
			 */

			$query = trim($message->getText());

			$d = generateMessageAndButtonsBySearchQuery($sm, $query);

			$reply = new SendMessage($chatId, $d["text"]);
			$reply->setReplyMarkup($d["keyboard"]);
			$reply->setDisableWebPagePreview(true);
			//$tg->performHookMethod($reply);
			$reply->setParseMode(ParseMode::HTML);
			$tg->performHookMethod($reply);
			exit;
		}

		/**
		 * Поиск по присланному пользователем местоположению в радиусе 2 км
		 */
		$location = null;
		if ($location = $message->getLocation()) {
			$dnb = generateMessageAndButtonsByNearby($sm, $location->getLatitude(), $location->getLongitude());
			$reply = new SendMessage($chatId, $dnb["text"]);
			$reply->setReplyMarkup($dnb["keyboard"]);
			$reply->setDisableWebPagePreview(true);
			$reply->setParseMode(ParseMode::HTML);
			$tg->performHookMethod($reply);
			exit;
		}


		$sendReply("unknown command");
	});

	/** @noinspection PhpUnhandledExceptionInspection */
	$tg->onCallbackQuery(function(Telegram\Client $tg, CallbackQuery $query) use ($sm) {
		if ($query->getData() === "1") {
			return;
		}

		$unpacked = explode("@", $query->getData());

		switch ($unpacked[0]) {

			/**
			 * Search
			 */
			case "s":
				list(, $offset, $q) = $unpacked;
				$update = generateMessageAndButtonsBySearchQuery($sm, $q, $offset);
				$request = new EditMessageText($query->getChatId(), $query->getMessageId(), $update["text"]);
				$request->setReplyMarkup($update["keyboard"]);
				$request->setParseMode(ParseMode::HTML);
				$request->setDisableWebPagePreview(true);
				$tg->performHookMethod($request);
				break;

			/**
			 * Nearby
			 */
			case "n":
				list(, $offset, $lat, $lng) = $unpacked;

				$update = generateMessageAndButtonsByNearby($sm, $lat, $lng, $offset);
				$request = new EditMessageText($query->getChatId(), $query->getMessageId(), $update["text"]);
				$request->setReplyMarkup($update["keyboard"]);
				$request->setParseMode(ParseMode::HTML);
				$request->setDisableWebPagePreview(true);
				$tg->performHookMethod($request);
				break;

		}
	});