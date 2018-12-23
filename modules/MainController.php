<?

	use Method\APIException;
	use Method\APIMethod;
	use Method\Authorize\GetSession;
	use Method\User\GetByTelegramId;
	use Model\Params;
	use Model\Session;
	use Model\User;

	class MainController extends Model\Controller {

		/** @var PDO */
		private $mConnection;

		/** @var string */
		private $mAuthKey = null;

		/** @var Session */
		private $mSession;

		/** @var User */
		private $mUser;

		/**
		 * Для работы главного контроллера требуется подключение к БД через PDO
		 * @param PDO $pdo
		 */
		public function __construct(PDO $pdo) {
			$this->mConnection = $pdo;
		}

		/**
		 * Изменение авторизационного ключа для текущего запроса, если пользователь авторизован
		 * @param string $authKey
		 */
		public function setAuthKey($authKey) {
			$this->mAuthKey = $authKey;
		}

		/**
		 * Запрос к БД через PDO
		 * @param string $sql
		 * @return PDOStatement
		 */
		public function makeRequest(string $sql) {
			return $this->mConnection->prepare($sql);
		}

		/**
		 * Возвращает PDO
		 * @return PDO
		 */
		public function getDatabaseProvider() {
			return $this->mConnection;
		}

		/**
		 * @return Credis_Client|Redis
		 */
		public function getRedis() {
			try {
				$hasStock = class_exists("\\Redis");
			} catch (RuntimeException $e) {
				$hasStock = false;
			}
			if ($hasStock) {
				$redis = new \Redis();
				$redis->auth(REDIS_PASSWORD);
				$redis->connect(REDIS_HOST, REDIS_PORT, REDIS_TIMEOUT, null, 1);
			} else {
				$redis = new \Credis_Client(REDIS_HOST, REDIS_PORT, REDIS_TIMEOUT, '', REDIS_DB, REDIS_PASSWORD);
			}
			return $redis;
		}

		/**
		 * Вызов метода API
		 * @param APIMethod $method
		 * @return mixed
		 */
		public function perform(APIMethod $method) {
			return $method->call($this);
		}

		/**
		 * Проверка на то, есть ли у пользователя авторизация
		 * @return boolean
		 */
		public function isAuthorized() {
			if ($this->mAuthKey === null) {
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
		 */
		public function getAuthKey() {
			return $this->mAuthKey;
		}

		private function fetchUserInfo() {
			if ($this->mAuthKey && !$this->mSession && !$this->mUser) {
				list($this->mSession, $this->mUser) = $this->perform(new GetSession(["authKey" => $this->mAuthKey]));
			}
		}

		/**
		 * Returns session
		 * @return Session
		 */
		public function getSession() {
			$this->fetchUserInfo();
			return $this->mSession;
		}

		/**
		 * @return User
		 */
		public function getUser() {
			$this->fetchUserInfo();
			return $this->mUser;
		}

		public function setTelegramId($telegramId) {
			$user = $this->perform(new GetByTelegramId((new Params)->set("telegramId", $telegramId)));

			if (!$user) {
				return false;
			}

			$this->mAuthKey = "telegramId" . $telegramId;
			$this->mSession = new Session(["authId" => 0, "authKey" => $this->mAuthKey, "userId" => $user->getId(), "accessMask" => 0, "date" => time()]);
			$this->mUser = $user;

			return $user;
		}

	}