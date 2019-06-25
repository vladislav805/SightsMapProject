<?

	use Method\APIException;
	use Method\APIMethod;
	use Method\Authorize\GetSession;
	use Method\User\GetByTelegramId;
	use Model\Session;
	use Model\User;

	class MainController extends Model\Controller {

		/** @var PDO */
		protected $mConnection;

		/** @var string */
		protected $mAuthKey = null;

		/** @var Session */
		protected $mSession;

		/** @var User */
		protected $mUser;

		/** @var Redis|Credis_Client */
		private $mRedis;

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

		public function setRedis($redis) {
			$this->mRedis = $redis;
		}

		/**
		 * Запрос к БД через PDO
		 * @param string $sql
		 * @return PDOStatement
		 */
		public final function makeRequest(string $sql) {
			return $this->mConnection->prepare($sql);
		}

		/**
		 * Возвращает PDO
		 * @return PDO
		 */
		public final function getDatabaseProvider() {
			return $this->mConnection;
		}

		/**
		 * @return Credis_Client|Redis
		 */
		public final function getRedis() {
			return $this->mRedis;
		}

		/**
		 * Вызов метода API
		 * @param APIMethod $method
		 * @return mixed
		 */
		public final function perform(APIMethod $method) {
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
			$user = $this->perform(new GetByTelegramId(["telegramId" => $telegramId]));

			if (!$user) {
				return false;
			}

			$this->mAuthKey = "telegramId" . $telegramId;
			$this->mSession = new Session(["authId" => 0, "authKey" => $this->mAuthKey, "userId" => $user->getId(), "date" => time()]);
			$this->mUser = $user;

			return $user;
		}

	}