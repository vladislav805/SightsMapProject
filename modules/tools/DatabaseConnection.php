<?

	namespace tools;

	use Method\APIException;
	use PDO;

	/**
	 * Class DatabaseConnection
	 * BACK COMPATIBILITY
	 * @package tools
	 * @deprecated
	 */
	class DatabaseConnection {

		/** @var PDO */
		private $mConnection;

		/**
		 * DatabaseController constructor.
		 * @param string $host
		 * @param string $user
		 * @param string $pass
		 * @param string $name
		 * @throws APIException
		 */
		public function __construct($pdo) {
			$this->mConnection = $pdo;

			if ($this->mConnection->errorCode()) {
				throw new APIException(ERROR_DATABASE_CONNECT, ["data" => $this->mConnection->errorInfo()]);
			}
		}

		/**
		 * @return PDO
		 */
		public function getPdo() {
			return $this->mConnection;
		}


		/**
		 * Make SQL query to database
		 * @param string $sql
		 * @param int	$resultType
		 * @return mixed
		 * @throws APIException
		 * @deprecated
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
			$this->mConnection = null;
		}

		/**
		 * Fetch result from SQL query to specified type
		 * @param \PDOStatement $data
		 * @param int $resultType
		 * @return mixed
		 */
		private function fetchResult($data, $resultType) {
			switch ($resultType) {

				case DatabaseResultType::ITEM:

					$a = $data->fetch(PDO::FETCH_ASSOC); // $data->fetch_assoc();
					return $this->normalize($a);

				case DatabaseResultType::ITEMS:
					$result = $data->fetchAll(PDO::FETCH_ASSOC);
					//while ($i = $data->fetch_assoc()) {
					//	$result[] = $this->normalize($i);
					//};
					foreach ($result as &$item) {
						$item = $this->normalize($item);
					}
					return $result;

				case DatabaseResultType::COUNT:
					return (int) $data->fetch(PDO::FETCH_ASSOC)["COUNT(*)"];

				case DatabaseResultType::INSERTED_ID:
					return $this->mConnection->lastInsertId();

				case DatabaseResultType::AFFECTED_ROWS:
					return $data->rowCount();

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
			return $this->mConnection->errorCode() > 0;
		}

	}

