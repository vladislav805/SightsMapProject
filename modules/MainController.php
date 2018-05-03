<?

	use Method\APIMethod;
	use Method\Authorize\GetSession;
	use Method\User\GetById;
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

			// TODO: если сессия потребуется, метод только тогда должен принудительно производить выборку
			// TODO: оставлено здесь для обратной совместимости на момент переписи
//			$this->initUserAuthSession();
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
		 * Вызов метода API
		 * @param APIMethod $method
		 * @return mixed
		 */
		public function perform(APIMethod $method) {
			return $method->call($this);
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