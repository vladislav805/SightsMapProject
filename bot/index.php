<?

	use Method\Sight\GetById;
	use Method\Sight\Search;
	use Method\Sight\SetVisitState;
	use Telegram\Client;
	use Telegram\Method\GetWebhookInfo;
	use Telegram\Method\SetWebhook;
	use Telegram\Model\Object\Message;
	use Telegram\Model\Object\MessageEntity;
	use Telegram\Model\Response\CallbackQuery;
	use Telegram\Model\WebhookInfo;
	use Telegram\Utils\Logger;
	use Telegram\Utils\WebhookInfoTable;

	/** @noinspection PhpUndefinedNamespaceInspection */
	/** @noinspection PhpUndefinedClassInspection */
	/** @noinspection PhpUndefinedMethodInspection */

	error_reporting(E_ALL);
	ini_set("error_log", "./log.log");

	require_once "../autoload.php";
	require_once "../config.php";
	require_once "../functions.php";
	require_once "TelegramBotReplies.php";
	require_once "helper.php";

	$db = new PDO(sprintf("mysql:host=%s;dbname=%s;charset=utf8", DB_HOST, DB_NAME), DB_USER, DB_PASS);

	$tg = new Telegram\Client(TELEGRAM_BOT_SECRET);

	$ctrl = new TelegramController($db);

	if (isset($_REQUEST["check"])) {
		WebhookInfoTable::outputTable(new WebhookInfo($tg->performSingleMethod(new GetWebhookInfo)));
		exit;
	}

	if (isset($_REQUEST["setup"])) {
		print json_encode($tg->performSingleMethod(new SetWebhook("https://" . DOMAIN_MAIN . "/bot/", 15)));
		exit;
	}

	define("TG_BOT_SIGHTS_ITEMS_PER_PAGE", 5);


	$tg->setLogger(new Logger("events.log", Logger::LOG_MODE_MESSAGE | Logger::LOG_MODE_INCLUDE_RAW | Logger::LOG_MODE_API_RESULT));



	/** @noinspection PhpUnhandledExceptionInspection */
	$tg->onMessage(function(Client $tg, Message $message) use ($ctrl) {

		// Инициализация
		$ctrl->init($tg, $message);

		// Шаблонизатор ответов
		$replier = $ctrl->getReplier();

		if ($message->isCommand() && $message->getTextEntity(0)->getType() === MessageEntity::TYPE_BOT_COMMAND) {
			switch ($message->getTextEntity(0)->getData()) {
				case "/start":
					$replier->sendStartMessage();
					break;

				case "/auth":
					if (preg_match("/\/auth ([A-Fa-f0-9]{6})/im", $message->getText())) {
						list(, $code) = explode(" ", $message->getText());

						$result = $ctrl->pairing($code);

						if (!$result) {
							return;
						}

						$replier->sendWelcomeProfileMessage($result);
					} else {
						$replier->sendAuthorizeForm();
					}
					break;

				case "/profile":
					if (!$ctrl->isAuthorized()) {
						$replier->sendAuthorizeForm();
					}

					$replier->sendProfileMessage($ctrl->getUser());

					//$sendReply(sprintf("<b>@%s</b>\n<i>%s</i>", $sUser->getLogin(), $sUser->getCity() ? $sUser->getCity()->getName() : ""));
					break;

				case "/new":
					if (!$ctrl->isAuthorized()) {
						$replier->sendAuthorizeForm();
					}

					$replier->attemptCreateSight();
					break;

				case "/redis":
					$replier->parrot($ctrl->getRedis()->keys("*"));
					break;

				case "/clearlog":
					unlink(__DIR__ . "/log.log");
					unlink(__DIR__ . "/events.log");
					break;

				// default с exit не указывать, поскольку /sightN иначе не сработает
			}
		}

		if ($message->getReplyToMessage()) {
			switch ($message->getReplyToMessage()->getText()) {
				case TelegramBotReplies::TEXT_NEW_SIGHT:
					$location = $message->getLocation();

					if (!$location) {
						$replier->parrot("no location");
					}

					$replier->createAfterLocation();
					break;

				case TelegramBotReplies::TEXT_NEW_SIGHT_ENTER_TITLE:
					$title = $message->getText();

					$replier->parrot($title);
					break;
			}
		}


		if ($message->hasText()) {


			/**
			 * Конкретное место
			 */
			if (preg_match_all("/^\/sight(\d+)$/imu", $message->getText(), $result, PREG_SET_ORDER)) {
				$sid = (int) $result[0][1];

				/** @var \Model\Sight $sight */
				$sight = $ctrl->perform(new GetById(["sightId" => $sid]));

				if (!$sight) {
					$replier->parrot("sight not found");
				}

				$replier->showSightPage($sight);
			}

			/**
			 * Поиск по словам
			 */

			$query = trim($message->getText());

			$result = $ctrl->perform(new Search([
				"query" => $query,
				"count" => TG_BOT_SIGHTS_ITEMS_PER_PAGE
			]));

			$replier->makeSearchPage($query, $result);
		}




		/**
		 * Поиск по присланному пользователем местоположению в радиусе 2 км
		 */
		$location = null;
		if ($location = $message->getLocation()) {
			$result = getNearbySights($ctrl, $location->getLatitude(), $location->getLongitude());

			$replier->makeNearbyPage($result, $location->getLatitude(), $location->getLongitude());
		}


	});

	/** @noinspection PhpUnhandledExceptionInspection */
	$tg->onCallbackQuery(function(Client $tg, CallbackQuery $query) use ($ctrl) {

		// Инициализация
		$ctrl->init($tg, $query);

		// Шаблонизатор ответов
		$replier = $ctrl->getReplier();

		if ($query->getData() === "1") {
			exit;
		}

		$unpacked = explode(";", $query->getData());

		switch ($unpacked[0]) {

			/**
			 * Search
			 */
			case "s":
				list(, $offset, $query) = $unpacked;
				$result = $ctrl->perform(new Search([
					"query" => $query,
					"count" => TG_BOT_SIGHTS_ITEMS_PER_PAGE,
					"offset" => (int) $offset
				]));

				$replier->makeSearchPage($query, $result, (int) $offset);
				break;

			/**
			 * Nearby
			 */
			case "n":
				list(, $offset, $lat, $lng) = $unpacked;

				$result = getNearbySights($ctrl, $lat, $lng, $offset);
				$replier->makeNearbyPage($result, $lat, $lng, $offset);

				/*$update = generateMessageAndButtonsByNearby($ctrl, $lat, $lng, $offset);
				$request = new EditMessageText($query->getChatId(), $query->getMessageId(), $update["text"]);
				$request->setReplyMarkup($update["keyboard"]);
				$request->setParseMode(ParseMode::HTML);
				$request->setDisableWebPagePreview(true);
				$tg->performHookMethod($request);*/
				break;

			/**
			 * Set visit state
			 */
			case "v":
				list(, $sightId, $state) = $unpacked;
				$result = $ctrl->perform(new SetVisitState(["sightId" => $sightId, "state" => $state]));

				if ($result) {
					$replier->onVisitStateChange((int) $sightId, (int) $state);
				}
				break;


		}
	});