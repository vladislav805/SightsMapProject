<?

	use Method\Authorize\GetSession;
	use Method\User\GetById;
	use Model\AuthKey;
	use Model\Session;
	use Model\User;
	use tools\DatabaseConnection;

	class MainController extends Controller {

		/** @var DatabaseConnection */
		private $mConnection;

		/** @var AuthKey */
		private $mAuthKey;

		/** @var Session */
		private $mSession;

		/** @var User */
		private $mUser;

		/**
		 *
		 */
		public function __construct() {
			$this->mConnection = DatabaseConnection::getInstance(DB_HOST, DB_USER, DB_PASS, DB_NAME);
		}

		/**
		 * @param string $authKey
		 */
		public function setAuthKey($authKey) {
			$this->mAuthKey = $authKey;
			$this->initUserAuthSession();
		}

		/**
		 * Initialization main controller
		 */
		private function initUserAuthSession() {
			try {
				$this->mSession = $this->perform(new GetSession(["authKey" => $this->mAuthKey]));
				$this->mUser = $this->perform(new GetById(["userIds" => $this->mSession->getUserId()]));
			} catch (APIException $e) {

			}
		}

		/**
		 * @param string $sql
		 * @param int $type
		 * @return mixed
		 */
		public function query(string $sql, int $type) {
			return $this->mConnection->query($sql, $type);
		}

		/**
		 * Call API method
		 * @param APIMethod $method
		 * @return mixed
		 */
		public function perform(APIMethod $method) {
			return $method->call($this, $this->mConnection);
		}

		/**
		 * Returns session
		 * @return Session
		 */
		public function getSession() {
			return $this->mSession;
		}

		/**
		 * @return User
		 */
		public function getUser() {
			return $this->mUser;
		}

	}