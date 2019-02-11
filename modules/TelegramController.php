<?

	use Method\APIException;
	use Method\User\GetByIds;
	use Method\User\SetTelegramId;
	use Model\Session;
	use Model\User;
	use Telegram\Client;
	use Telegram\Model\Object\Message;
	use Telegram\Model\Response\CallbackQuery;

	class TelegramController extends MainController {

		/** @var Message|CallbackQuery */
		private $mTelegramMessage;

		/** @var Client */
		private $mTelegramClient;

		/**
		 * Для работы главного контроллера требуется подключение к БД через PDO
		 * @param PDO $pdo
		 */
		public function __construct(PDO $pdo) {
			parent::__construct($pdo);
		}

		/**
		 * @param Client $client
		 * @param Message|CallbackQuery $message
		 */
		public function init(Client $client, $message) {
			$this->mTelegramClient = $client;
			$this->mTelegramMessage = $message;

			$redis = $this->getRedis();

			$userId = (int) $redis->get("tg" . $message->getChatId());
			if ($userId) {
				$this->mSession = new Session([
					"authId" => 0,
					"authKey" => "",
					"accessMask" => -1,
					"date" => time(),
					"userId" => $userId
				]);
				list($this->mUser) = $this->perform(new GetByIds([ "userIds" => [$userId], "extra" => "city,photo" ]));
			} else {
				$this->mUser = false;
			}
		}

		/**
		 * Проверка на то, есть ли у пользователя авторизация
		 * @return boolean
		 * @override
		 */
		public function isAuthorized() {
			if ($this->mUser === false) {
				return false;
			}

			try {
				return $this->getSession() !== null;
			} /** @noinspection PhpRedundantCatchClauseInspection */ catch (APIException $e) {
				return false;
			}
		}

		/**
		 * Возвращает токен, который передал пользователь
		 * @return string
		 * @override
		 */
		public function getAuthKey() {
			return null;
		}

		/**
		 * Returns session
		 * @return Session
		 * @override
		 */
		public function getSession() {
			return $this->mSession;
		}

		/**
		 * @return User
		 * @override
		 */
		public function getUser() {
			return $this->mUser;
		}

		/**
		 * @return TelegramBotReplies
		 */
		public function getReplier() {
			return new TelegramBotReplies($this->mTelegramClient, $this->mTelegramMessage, $this);
		}

		/**
		 * @param string $code
		 * @return boolean|User
		 */
		public function pairing($code) {
			$code = hexdec($code);

			$key = mb_strtolower("telegramAuth" . $code);

			$redis = $this->getRedis();

			if (!$redis->exists($key)) {
				return false;
			}

			$result = json_decode($redis->get($key));

			/** @var \Model\User $user */
			list($user) = $this->perform(new GetByIds([ "userIds" => [$result->userId] ]));

			$this->perform(new SetTelegramId([ "userId" => $user->getId(), "telegramId" => $this->mTelegramMessage->getChatId() ]));

			$redis->set("tg" . $this->mTelegramMessage->getChatId(), $user->getId());

			return $user;
		}

		/**
		 *
		 */
		public function unpairing() {
			$this->getRedis()->del("tg" . $this->mTelegramMessage->getChatId());
		}

	}