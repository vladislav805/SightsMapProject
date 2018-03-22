<?

	use Method\APIException;
	use Model\IController;
	use Model\Point;

	require_once "config.php";
	require_once "modules/Method/Event/utils.php";

	/**
	 * Settings
	 */
	define("ADMIN_ID_LIMIT", 100);
	define("DOMAIN", defined("PRODUCTION") && PRODUCTION ? "sights.vlad805.ru" : "192.168.1.111/sights");
	define("MODERATOR_NOTIFY_USER_ID", 1);

	/**
	 * Errors
	 */
	define("ERROR_NO_PARAM", 0x01);
	define("ERROR_UNKNOWN_METHOD", 0x04);

	define("ERROR_INCORRECT_LOGIN_PASSWORD", 0x10);
	define("ERROR_LOGIN_ALREADY_EXIST", 0x11);
	define("ERROR_EMAIL_ALREADY_EXIST", 0x14);
	define("ERROR_INCORRECT_LENGTH_PASSWORD", 0x12);
	define("ERROR_INCORRECT_NAMES", 0x13);
	define("ERROR_SESSION_NOT_FOUND", 0x1f);
	define("ERROR_ACCESS_DENIED", 0x1e);

	define("ERROR_POINT_NOT_FOUND", 0x20);
	define("ERROR_INVALID_COORDINATES", 0x21);

	define("ERROR_MARK_NOT_FOUND", 0x30);
	define("ERROR_INVALID_COLOR", 0x31);

	define("ERROR_PHOTO_NOT_FOUND", 0x40);
	define("ERROR_UPLOAD_FAILURE", 0x41);
	define("ERROR_UPLOAD_INVALID_SIZES", 0x42);

	define("ERROR_COMMENT_NOT_FOUND", 0x50);

	define("ERROR_UNKNOWN_ERROR", 0x05);
	define("ERROR_DATABASE_CONNECT", 0x07);
	define("ERROR_FLOOD_CONTROL", 0x0f);

	/**
	 * Getting parameter from query string
	 * @param string  $key Key
	 * @param string  $defaultValue
	 * @return string Value
	 */
	function get($key, $defaultValue = "") {
		return isset($_REQUEST[$key]) ? $_REQUEST[$key] : $defaultValue;
	}

	/**
	 * Trying get value in accessed $cases. If current value
	 * not in accessed, will returned first item of $cases
	 * @param  mixed   $val   Current value
	 * @param  mixed[] $cases Accessed values
	 * @return mixed		  Accessed value result
	 */
	function getDefaultValue($val, $cases) {
		return in_array($val, $cases) ? $val : $cases[0];
	}

	/**
	 * Output result in JSON format and stop execution
	 * @param  mixed $data Any data
	 * @param string $wrap
	 */
	function done($data, $wrap = "result") {
		if ($wrap) {
			$data = [$wrap => $data];
		};

		header("Content-type: application/json; charset=utf-8");
		print json_encode($data, JSON_UNESCAPED_UNICODE);
		exit;
	}

	function safeString(&$str) {
		return $str = addslashes($str);
	}


	/**
	 * @param mixed  $items
	 * @param string $cls
	 * @param mixed  $ctx
	 * @return mixed
	 */
	function parseItems($items, $cls, $ctx = null) {
		foreach ($items as $i => &$item) {
			$item = new $cls($item, $ctx);
		}

		/** @noinspection PhpIncompatibleReturnTypeInspection */
		return $items;
	}

	function truncate($text, $chars = 25) {
		return mb_strimwidth($text, 0, $chars, "...");
	}

	function str_split_unicode($str, $maxLength = 0) {
		if ($maxLength > 0) {
			$ret = [];
			$len = mb_strlen($str, "UTF-8");
			for ($i = 0; $i < $len; $i += $maxLength) {
				$ret[] = mb_substr($str, $i, $maxLength, "UTF-8");
			}
			return $ret;
		}
		return preg_split("//u", $str, -1, PREG_SPLIT_NO_EMPTY);
	}

	function removeDirectoryRecursively($dir) {
		$files = array_diff(scandir($dir), ['.', '..']);
		foreach ($files as $file) {
			is_dir($dir . "/" . $file) ? removeDirectoryRecursively($dir . "/" . $file) : unlink($dir . "/" . $file);
		}
		return rmdir($dir);
	}

	/**
	 * @param IController $cnt
	 * @param int $ownerId
	 * @param int $errorId
	 * @return boolean
	 * @throws APIException
	 */
	function assertOwner(IController $cnt, $ownerId, $errorId) {
		if ($cnt->getSession()->getUserId() !== $ownerId && $cnt->getSession()->getUserId() > ADMIN_ID_LIMIT) {
			throw new APIException($errorId);
		}
		return true;
	}

	/**
	 * Проверяет, находится ли число $val в промежутке между $min и $max
	 * @param number $val
	 * @param number $min
	 * @param number $max
	 * @return boolean
	 */
	function inRange($val, $min, $max) {
		return $min <= $val && $val <= $max;
	}

	/**
	 * Проверяет является ли число $x корректной координатой
	 * @param number $x
	 * @return boolean
	 */
	function isCoordinate($x) {
		return inRange($x, -180, 180);
	}

	function getHumanizeURLPlace(Point $point) {
		return "http://" . DOMAIN . "/place/" . $point->getId() . "-" . getTransliteratedNamePlace($point);
	}

	function getTransliteratedNamePlace(Point $point) {
		return transliterate(mb_substr($point->getTitle(), 0, 50), TRANSLITERATE_TO_LAT);
	}

	define("TRANSLITERATE_TO_LAT", 0);
	define("TRANSLITERATE_TO_RUS", 1);
	function transliterate($text = null, $direction = TRANSLITERATE_TO_LAT) {
		$cyr = ['ж', 'ч', 'щ', 'ш', 'ю', 'а', 'б', 'в', 'г', 'д', 'е', 'з', 'и', 'й', 'к', 'л', 'м', 'н', 'о', 'п', 'р', 'с', 'т', 'у', 'ф', 'х', 'ц', 'ъ', 'ь', 'я', 	'Ж',  'Ч',  'Щ',   'Ш',  'Ю',  'А', 'Б', 'В', 'Г', 'Д', 'Е', 'З', 'И', 'Й', 'К', 'Л', 'М', 'Н', 'О', 'П', 'Р', 'С', 'Т', 'У', 'Ф', 'Х', 'Ц', 'Ъ', 'Ь', 'Я', 'ы', 'Ы'];
		$lat = ['zh', 'ch', 'sht', 'sh', 'yu', 'a', 'b', 'v', 'g', 'd', 'e', 'z', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'r', 's', 't', 'u', 'f', 'h', 'c', 'y', 'x', 'q', 'Zh', 'Ch', 'Sht', 'Sh', 'Yu', 'A', 'B', 'V', 'G', 'D', 'E', 'Z', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'R', 'S', 'T', 'U', 'F', 'H', 'c', 'Y', 'X', 'Q', 'qi', 'Qi'];
		$spec = [' ', '/', '\\', '=', '*', '!', "@", '#'];

		return str_replace(
			$direction === TRANSLITERATE_TO_RUS ? $lat : $cyr,
			$direction === TRANSLITERATE_TO_RUS ? $cyr : $lat,
			str_replace($spec, "-", $text)
		);
	}