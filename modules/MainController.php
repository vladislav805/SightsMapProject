<?

	use Method\APIException;
	use Method\APIMethod;
	use Method\Authorize\GetSession;
	use Method\User\GetById;
	use Model\AuthKey;
	use Model\Session;
	use Model\User;
	use tools\DatabaseConnection;

	class MainController extends Model\Controller {

		/** @var DatabaseConnection */
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
		 * @throws APIException
		 */
		public function __construct(PDO $pdo) {
			$this->mConnection = new DatabaseConnection($pdo);
		}

		/**
		 * Изменение авторизационного ключа для текущего запроса, если пользователь авторизован
		 * @param string $authKey
		 */
		public function setAuthKey($authKey) {
			$this->mAuthKey = $authKey;

			// TODO: если сессия потребуется, метод только тогда должен принудительно производить выборку
			// TODO: оставлено здесь для обратной совместимости на момент переписи
//			$this->initUserAuthSession();
		}



		/**
		 * @param string $sql
		 * @param int $type
		 * @return mixed
		 * @throws APIException
		 * @deprecated Использовать вместо этого makeRequest(): PDO
		 */
		public function query(string $sql, int $type) {
			return $this->mConnection->query($sql, $type);
		}

		/**
		 * Запрос к БД через PDO
		 * @param string $sql
		 * @return PDOStatement
		 */
		public function makeRequest(string $sql) {
			return $this->mConnection->getPdo()->prepare($sql);
		}

		/**
		 * Возвращает PDO
		 * @return PDO
		 */
		public function getDatabaseProvider() {
			return $this->mConnection->getPdo();
		}

		/**
		 * Вызов метода API
		 * @param APIMethod $method
		 * @return mixed
		 */
		public function perform(APIMethod $method) {
			return $method->call($this, $this->mConnection);
		}

		/**
		 * Проверка на то, есть ли у пользователя авторизация
		 * TODO: проверка на реальный токен, а не на любую строку
		 * @return boolean
		 */
		public function isAuthorized() {
			return $this->mAuthKey !== null;
		}

		/**
		 * Возвращает токен, который передал пользователь
		 * @return string
		 */
		public function getAuthKey() {
			return $this->mAuthKey;
		}

		/**
		 * Returns session
		 * @return Session
		 */
		public function getSession() {
			if ($this->mAuthKey && !$this->mSession) {
				$this->mSession = $this->perform(new GetSession(["authKey" => $this->mAuthKey]));
			}
			return $this->mSession;
		}

		/**
		 * @return User
		 */
		public function getUser() {
			if ($this->mAuthKey && !$this->mUser) {
				$this->mUser = $this->perform(new GetById(["userIds" => $this->getSession()->getUserId()]));
			}
			return $this->mUser;
		}

	}