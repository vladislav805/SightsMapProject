<?

	namespace tools;

	use Method\APIException;
	use mysqli;
	use mysqli_result;

	class DatabaseConnection {

		/** @var self */
		private static $sInstance;

		/** @var mysqli */
		private $mConnection;

		/**
		 * DatabaseController constructor.
		 * @param string $host
		 * @param string $user
		 * @param string $pass
		 * @param string $name
		 */
		public function __construct($host, $user, $pass, $name) {
			$this->mConnection = new mysqli($host, $user, $pass, $name);
			$this->mConnection->set_charset("utf8");
		}

		/**
		 * @param string $host
		 * @param string $user
		 * @param string $pass
		 * @param string $name
		 * @return DatabaseConnection
		 */
		public static function getInstance($host, $user, $pass, $name) {
			if (!self::$sInstance) {
				self::$sInstance = new self($host, $user, $pass, $name);
			}
			return self::$sInstance;
		}

		/**
		 * Make SQL query to database
		 * @param string $sql
		 * @param int	$resultType
		 * @return mixed
		 * @throws APIException
		 */
		public function query($sql, $resultType = DatabaseResultType::RAW) {
			$data = $this->mConnection->query($sql);

			if (!$data) {
				throw new APIException(ERROR_UNKNOWN_ERROR, ["msg" => "invalid data result at query: " . $sql]);
			}

			return $this->fetchResult($data, $resultType);
		}

		/**
		 * Destruct and close connection with mysqli server
		 */
		public function __destruct() {
			$this->mConnection->close();
		}

		/**
		 * Fetch result from SQL query to specified type
		 * @param mysqli_result $data
		 * @param int $resultType
		 * @return mixed
		 */
		private function fetchResult($data, $resultType) {
			/** @var mysqli_result $data */
			switch ($resultType) {

				case DatabaseResultType::ITEM:
					$a = $data->fetch_assoc();
					return $this->normalize($a);

				case DatabaseResultType::ITEMS:
					$result = [];
					while ($i = $data->fetch_assoc()) {
						$result[] = $this->normalize($i);
					};
					return $result;

				case DatabaseResultType::COUNT:
					return (int) $data->fetch_assoc()["COUNT(*)"];

				case DatabaseResultType::INSERTED_ID:
					return $this->mConnection->insert_id;

				case DatabaseResultType::AFFECTED_ROWS:
					return $this->mConnection->affected_rows;

				default:
					return $data;

			}
		}

		/**
		 * Change types of fields object a to original
		 * @param  mixed &$a Object from database
		 * @return object	 Modified object
		 */
		public static function normalize(&$a) {
			if (is_null($a)) {
				return null;
			}

			foreach ($a as $key => $value) {
				if (is_numeric($value)) {
					$a[$key] = +$value;
				}
			}

			return $a;
		}

		/**
		 * @return boolean
		 */
		public function hasError() {
			return $this->mConnection->errno > 0;
		}

	}

