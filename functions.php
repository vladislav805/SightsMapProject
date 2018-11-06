<?

	use Method\APIException;
	use Model\IController;
	use Model\IOwnerable;
	use Model\Point;
	use Model\User;

	require_once "config.php";
	require_once "modules/Method/Event/utils.php";

	/**
	 * Settings
	 */
	/** @deprecated  */
	define("ADMIN_ID_LIMIT", 100);
	define("DOMAIN", defined("PRODUCTION") && PRODUCTION ? "sights.vlad805.ru" : "192.168.1.111/sights");
	define("MODERATOR_NOTIFY_USER_ID", 1);

	define("UPLOAD_PHOTO_PROFILE_MIN_SIZE", 720);
	define("UPLOAD_PHOTO_POINT_MIN_SIZE", 1200);
	define("PHOTO_WATERMARK_MAX_SIDE_SIZE", 1400);
	define("PHOTO_WATERMARK_MAX_COMPRESSION", 98);
	define("PHOTO_WATERMARK_THUMB_SIDE_SIZE", 200);
	define("PHOTO_WATERMARK_THUMB_COMPRESSION", 50);
	define("PHOTO_WATERMARK_OFFSET_X", 10);
	define("PHOTO_WATERMARK_OFFSET_Y", 7);
	define("PHOTO_WATERMARK_FONT_SIZE", 12);
	define("PHOTO_WATERMARK_FONT_FACE", "assets/DroidSans.ttf");


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

	function truncate($text, $needLength = 120) {
		if (mb_strlen($text) < $needLength) {
			return $text;
		}

		$parts = preg_split("/([\s\n\r]+)/u", $text, null, PREG_SPLIT_DELIM_CAPTURE);
		$partsCount = sizeOf($parts);

		$length = 0;
		$last = 0;
		for (; $last < $partsCount; ++$last) {
			$length += mb_strlen($parts[$last]);
			if ($length > $needLength) {
				break;
			}
		}

		return implode(array_slice($parts, 0, $last)) . "…";
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
	 * @param IOwnerable $object
	 * @param int $errorId
	 * @return boolean
	 * @throws APIException
	 */
	function assertOwner(IController $cnt, IOwnerable $object, $errorId) {
		if ($cnt->getSession()->getUserId() !== $object->getOwnerId()) {
			$status = $cnt->getUser()->getStatus();
			if ($status !== User::STATE_ADMIN && $status !== User::STATE_MODERATOR) {
				throw new APIException($errorId, null, "Access denied: you have not access");
			}
		}
		return true;
	}

	function getGenderWord(\Model\User $user, string $male, string $female) {
		return $user->getSex() === 1 ? $female : $male;
	}

	/**
	 * @param int $n
	 * @param string $zero
	 * @param string[] $schemas
	 * @param string[] $pluralizes
	 * @return string
	 */
	function getSchemaByNumber($n, $zero, $schemas, $pluralizes) {
		if (!$n) {
			return $zero;
		}

		$schema = call_user_func_array("pluralize", array_merge([$n], $schemas));

		return sprintf($schema, $n, call_user_func_array("pluralize", array_merge([$n], $pluralizes)));
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
	 * @param double $lat
	 * @param double $lng
	 * @return boolean
	 */
	function isCoordinate($lat, $lng) {
		return inRange($lat, -90, 90) && inRange($lng, -180, 180);
	}

	function getHumanizeURLPlace(Point $point) {
		return "https://" . DOMAIN . "/place/" . $point->getId() . "-" . getTransliteratedNamePlace($point);
	}

	function getHumanizeDistanceString($distance) {
		if ($distance < 1) {
			return sprintf("%d м", $distance * 1e3);
		} else {
			return sprintf("%.2f км", $distance);
		}
	}

	function getTransliteratedNamePlace(Point $point) {
		return transliterate(mb_substr($point->getTitle(), 0, 50), TRANSLITERATE_TO_LAT);
	}

	define("TRANSLITERATE_TO_LAT", 0);
	define("TRANSLITERATE_TO_RUS", 1);
	function transliterate($text = null, $direction = TRANSLITERATE_TO_LAT) {
		$cyr = ['ж', 'ч', 'щ', 'ш', 'ю', 'а', 'б', 'в', 'г', 'д', 'е', 'з', 'и', 'й', 'к', 'л', 'м', 'н', 'о', 'п', 'р', 'с', 'т', 'у', 'ф', 'х', 'ц', 'ъ', 'ь', 'я', 	'Ж',  'Ч',  'Щ',   'Ш',  'Ю',  'А', 'Б', 'В', 'Г', 'Д', 'Е', 'З', 'И', 'Й', 'К', 'Л', 'М', 'Н', 'О', 'П', 'Р', 'С', 'Т', 'У', 'Ф', 'Х', 'Ц', 'Ъ', 'Ь', 'Я', 'ы', 'Ы', 'Ё', 'ё'];
		$lat = ['zh', 'ch', 'sht', 'sh', 'yu', 'a', 'b', 'v', 'g', 'd', 'e', 'z', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'r', 's', 't', 'u', 'f', 'h', 'c', 'y', 'x', 'q', 'Zh', 'Ch', 'Sht', 'Sh', 'Yu', 'A', 'B', 'V', 'G', 'D', 'E', 'Z', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'R', 'S', 'T', 'U', 'F', 'H', 'c', 'Y', 'X', 'Q', 'qi', 'Qi', 'Yo', 'yo'];
		$spec = [' ', '/', '\\', '=', '*', '!', "@", '#', '"', ',', '(', ')', '[', ']', '{', '}', '?', '«', '»'];

		return preg_replace("/(^-|-$)/im", "", preg_replace("/-{2,}/im", "-", str_replace(
			$direction === TRANSLITERATE_TO_RUS ? $lat : $cyr,
			$direction === TRANSLITERATE_TO_RUS ? $cyr : $lat,
			str_replace($spec, "-", $text)
		)));
	}

	/**
	 * @deprecated
	 */
	function makeOG($data) {
		$data["url"] = "https://" . DOMAIN . $_SERVER["REQUEST_URI"];
		foreach ($data as $key => $value) {
			$data[$key] = sprintf("<meta property=\"og:%s\" content=\"%s\" />", htmlspecialchars($key), htmlspecialchars($value));
		}
		return join("\n\t\t", array_values($data)) . "\n\t\t";
	}

	/**
	 * @deprecated
	 */
	function makeRibbonPoint($url) {
		return sprintf(" style=\"background: url('%s') no-repeat center center; background-size: cover;\"", $url);
	}

	/**
	 * Возвращает HEX представление десятичного числа, которое хранится в метках
	 * @param int $color Десятичное число
	 * @return string
	 */
	function getHexColor($color) {
		return str_pad(dechex($color), 6, "0", STR_PAD_LEFT);
	}

	function pluralize() {
		$args = func_get_args();
		$n = array_shift($args);

		if ($args < 2) {
			return "UNK";
		}

		return $args[(($n % 100 > 4 && $n % 100 < 20) ? 2 : [2, 0, 1, 1, 1, 2][($n % 10 < 5) ? $n % 10 : 5])];
	}

	function getRelativeDate($date) {
		$dateString = date("Y-m-d\TH:i:sP", $date);
		$date1 = new DateTime($dateString);
		$date2 = new DateTime("now");
		$d = $date1->diff($date2);

		if ($d->h > 6 || $d->days) {
			return date("d.m.Y H:i", $date);
		}

		if ($d->h) {
			$v = $d->h;
			$w = pluralize($v, "час", "часа", "часов");
		} elseif ($d->i) {
			$v = $d->i;
			$w = pluralize($v, "минуту", "минуты", "минут");
		} else {
			$v = $d->s;
			$w = pluralize($v, "секунду", "секунды", "секунд");
		}

		return sprintf("%d %s назад", $v, $w);
	}

	/**
	 * Make first letter in string $str upper case.
	 * @param $str
	 * @return string
	 */
	function upperCaseFirstLetter($str) {
		$firstLetter = mb_strtoupper(mb_substr($str, 0, 1));
		return $firstLetter . mb_substr($str, 1);
	}

	/**
	 * HTTP redirect to specify url
	 * @param $url
	 */
	function redirectTo($url) {
		header("Location: " . $url);
		exit;
	}

	function is_primitive($var) {
		return is_scalar($var) || is_null($var);
	}