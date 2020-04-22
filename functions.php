<?

	use Method\APIException;
	use Model\IController;
	use Model\IGeoPoint;
	use Model\IOwnerable;
	use Model\Sight;
	use Model\User;
	use tools\PHPMailer\Exception;
	use tools\PHPMailer\PHPMailer;

	require_once "config.php";
	require_once "modules/Method/Event/utils.php";

	/**
	 * Settings
	 */
	define("DOMAIN_MAIN", "sights.vlad805.ru");
	define("DOMAIN_MEDIA", "ps-sights.velu.ga");

	define("UPLOAD_PHOTO_PROFILE_MIN_SIZE", 720);
	define("UPLOAD_PHOTO_SIGHT_MIN_SIZE", 1070);
	define("PHOTO_MAX_SIDE_SIZE", 1400);
	define("PHOTO_MAX_COMPRESSION", 98);
	define("PHOTO_THUMB_SIDE_SIZE", 200);
	define("PHOTO_THUMB_COMPRESSION", 50);
	define("PHOTO_WATERMARK_OFFSET_X", 10);
	define("PHOTO_WATERMARK_OFFSET_Y", 7);
	define("PHOTO_WATERMARK_FONT_SIZE", 12);
	define("PHOTO_WATERMARK_FONT_FACE", "assets/DroidSans.ttf");
	define("PHOTO_PREVAIL_COLOR_DELIMITER", ";");

	define("NEURAL_NETWORK_LOWER_LIMIT_FOR_START_TRAINING", 30);

	define("API_VERSION_MIN", 200);
	define("API_VERSION_MAX", 250);

	define("MINUTE", 60);
	define("HOUR", 60 * MINUTE);
	define("DAY", 24 * HOUR);
	define("WEEK", 7 * DAY);
	define("MONTH", 30 * DAY);
	define("YEAR", 365 * DAY);

	define("IS_AJAX", isset($_SERVER["HTTP_X_REQUESTED_WITH"]) && !empty($_SERVER["HTTP_X_REQUESTED_WITH"]) && mb_strToLower($_SERVER["HTTP_X_REQUESTED_WITH"]) === "xmlhttprequest");


	/**
	 * Getting parameter from query string
	 * @param string  $key Key
	 * @param string  $defaultValue
	 * @return string Value
	 */
	function get($key, $defaultValue = "") {
		return $_REQUEST[$key] ?? $defaultValue;
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
	 * @param mixed $data Any data
	 * @param string $wrap
	 * @param Redis|Credis_Client $redis
	 * @param string $redisKey
	 */
	function done($data, $wrap, $redis, $redisKey) {
		if ($wrap) {
			$data = [$wrap => $data];
		}

		header("Content-type: application/json; charset=utf-8");

		$json = json_encode($data, JSON_UNESCAPED_UNICODE);

		if ($redis) {
			$redis->set($redisKey, $json, ["EX" => IDEMPOTENCE_API_RESULT_TIMEOUT]);
		}

		print $json;
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

	function highlightURLs($text){
		return preg_replace_callback('![^=](((f|ht)tp(s)?://)[-a-zA-Zа-яА-Я()0-9@:%_+.~#?&;//=]+)!ium', function($res) {
			$url = $res[1];

			$info = parse_url($url);

			$attr = $info["host"] === DOMAIN_MAIN ? "" : " data-noAjax target=\"_blank\"";

			/** @noinspection HtmlUnknownAttribute */
			return sprintf(' <a href="%1$s" %2$s>%1$s</a> ', $url, $attr);
		}, $text);
	}

	function parsePseudoTags($input) {
		return preg_replace_callback('#\[(a|b|u|i)(=([^\]]+))]((?:[^[]|\[(?!/?\1])|(?R))+)\[/\1]#ium', function($input) {
			list(, $tag, , $equal, $inner) = $input;

			switch ($tag) {
				case "a":
					$info = parse_url($equal);
					$attr = $info["host"] === DOMAIN_MAIN ? "" : " data-noAjax target=\"_blank\"";

					/** @noinspection HtmlUnknownAttribute */
					return sprintf('<a href="%s" %s>%s</a>', $equal, $attr, $inner);

				case "b":
					return "<" . $tag . ">" . $inner . "</" . $tag . ">";
			}
			return "";
		}, $input);
	}

	/**
	 * @param string $text
	 * @return string
	 */
	function formatText($text) {
		return parsePseudoTags(
			nl2br(
				//htmlSpecialChars(
					highlightURLs(
						htmlSpecialChars($text, ENT_NOQUOTES)
					)
				//, ENT_QUOTES, "utf-8", true)
			,true)
		);
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

	function isTrustedUser(\Model\User $user) {
		return in_array($user->getStatus(), [\Model\User::STATE_MODERATOR, \Model\User::STATE_ADMIN]);
	}

	function getGenderWord(\Model\User $user, string $male, string $female) {
		return $user->getSex() === User::GENDER_FEMALE ? $female : $male;
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

		$schema = pluralize($n, $schemas);

		return sprintf($schema, $n, pluralize($n, $pluralizes));
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

	function toRange($val, $min, $max) {
		return min(max($val, $min), $max);
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

	/**
	 * Проверка на валидность строки на то, что это email
	 * @param  string  $email Строка
	 * @return boolean        true, если это email
	 */
	function isValidEmail($email) {
		return preg_match("/^([A-Za-z0-9.-]{2,64})@([A-Za-z0-9А-Яа-яЁё-]{2,64}\.){1,16}([a-z]{2,8})$/imu", $email);
	}

	function getHumanizeDistanceString($distance) {
		if ($distance < 1) {
			return sprintf("%d м", $distance * 1e3);
		} else {
			return sprintf("%.2f км", $distance);
		}
	}

	function getTransliteratedNamePlace(Sight $sight) {
		return transliterate(mb_substr($sight->getTitle(), 0, 50), TRANSLITERATE_TO_LAT);
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
	 * Возвращает HEX представление десятичного числа, которое хранится в метках
	 * @param int $color Десятичное число
	 * @return string
	 */
	function getHexColor($color) {
		return "#" . str_pad(dechex($color), 6, "0", STR_PAD_LEFT);
	}

	/**
	 * @param int $n
	 * @param string[] $args
	 * @return string
	 */
	function pluralize($n, $args) {
		if (sizeOf($args) !== 3) {
			return "UNK";
		}

		return $args[($n % 100 > 4 && $n % 100 < 20) ? 2 : [2, 0, 1, 1, 1, 2][($n % 10 < 5) ? $n % 10 : 5]];
	}

	/**
	 * @param $date
	 * @return false|string
	 */
	function getRelativeDate($date) {
		$dateString = date("Y-m-d\TH:i:sP", $date);

		$date1 = null;
		$date2 = null;

		try {
			$date1 = new DateTime($dateString);
			$date2 = new DateTime("now");
		} catch (Exception $e) {
			return null;
		}

		$d = $date1->diff($date2);

		if ($d->h > 6 || $d->days) {
			return date("d.m.Y H:i", $date);
		}

		if ($d->h) {
			$v = $d->h;
			$w = pluralize($v, ["час", "часа", "часов"]);
		} elseif ($d->i) {
			$v = $d->i;
			$w = pluralize($v, ["минуту", "минуты", "минут"]);
		} else {
			$v = $d->s;
			$w = pluralize($v, ["секунду", "секунды", "секунд"]);
		}

		return sprintf("%d %s %s", $v, $w, $d->invert ? "" : "назад");
	}

	/**
	 * HTTP redirect to specify url
	 * @param $url
	 */
	function redirectTo($url) {
		if (IS_AJAX) {
			$url .= (strpos($url, "?") === false ? "?" : "&") . "_ajax=1";
		}
		header("Location: " . $url);
		exit;
	}

	function mb_strcasecmp($str1, $str2, $encoding = null) {
		if (null === $encoding) { $encoding = mb_internal_encoding(); }
		return strcmp(mb_strtoupper($str1, $encoding), mb_strtoupper($str2, $encoding));
	}

	function isAssoc(array $arr) {
		if (sizeOf(array_filter(array_keys($arr), "is_string")) > 0) {
			return true;
		}
		if (array() === $arr) {
			return false;
		}
		return array_keys($arr) !== range(0, sizeOf($arr) - 1);
	}

	function reArrayFiles(&$files) {
		$result = [];
		$keys = array_keys($files);

		for ($i = 0, $l = sizeof($files["name"]); $i < $l; $i++) {
			foreach ($keys as $key) {
				$result[$i][$key] = $files[$key][$i];
			};
		};

		return array_filter($result, function($item) { return !$item["error"]; });
	}

	/**
	 * Returns double in range [0; 1]
	 * @return double
	 */
	function randFloat() {
		return (float) rand() / (float) getRandMax();
	}

	define("PREPARE_INTS", 1);
	define("PREPARE_STRINGS", 2);
	function prepareIds($ids, $type = PREPARE_STRINGS) {
		if (is_numeric($ids)) {
			return [$type === PREPARE_STRINGS ? (string) $ids : $ids];
		} elseif (is_string($ids)) {
			$isEmpty = function($v) {
				return $v !== "";
			};

			$ids = explode(",", $ids);

			if ($type === PREPARE_INTS) {
				return array_map("intval", $ids);
			}

			return array_values(array_filter(array_map("trim", $ids), $isEmpty));
		} elseif (is_null($ids)) {
			return [];
		} else {
			return array_values($ids);
		}
	}

	/**
	 * @param IGeoPoint $x
	 * @param IGeoPoint $y
	 * @return float
	 */
	function get_distance(IGeoPoint $x, IGeoPoint $y) {
		$rad_y_lat = deg2rad($y->getLat());
		$rad_x_lat = deg2rad($x->getLat());
		return (
			6371000 * acos(
				cos($rad_y_lat) * cos($rad_x_lat) * cos(deg2rad($x->getLng()) - deg2rad($y->getLng())) + sin($rad_y_lat) * sin($rad_x_lat)
			)
		);
	}

	function get_http_request_uri() {
		$args = get_http_query_wo_utm();
		return get_http_path() . (sizeof($args) > 0 ? "?" . http_build_query($args) : "");
	}

	function get_http_path() {
		return strtok($_SERVER["REQUEST_URI"], "?");
	}

	function get_http_query_wo_utm() {
		$restricted_get_params = [
			"utm_source",
			"utm_medium",
			"utm_content",
			"utm_term",
			"utm_campaign",
			"r",
			"action"
		];
		return array_filter($_GET, function($key) use ($restricted_get_params) {
			return !in_array($key, $restricted_get_params);
		}, ARRAY_FILTER_USE_KEY);
	}

	/**
	 * @param double|int $aValue
	 * @param double|int $aMin
	 * @param double|int $aMax
	 * @param double|int $bMin
	 * @param double|int $bMax
	 * @return double|int
	 */
	function get_relative_of_interval_value_from_interval($aValue, $aMin, $aMax, $bMin, $bMax) {
		// Длина отрезка A
		/*$aLength = $aMax - $aMin;

		// Положение значения на длине отрезка A от его начала
		$aValueRelative = $aValue - $aMin;

		// Положение значения на длине отрезка A в процентах
		$aValuePercent = $aValueRelative * 100 / $aLength;

		// Длина отрезка B
		$bLength = $bMax - $bMin;

		// Положение значения на длине отрезка B от его начала
		$bValueRelative = $bLength * $aValuePercent / 100;

		// Положение значения на отрезке B
		return $bMin + $bValueRelative;*/


		return $bMin + (($bMax - $bMin) * (($aValue - $aMin) * 100 / ($aMax - $aMin)) / 100);
	}

	function get_object_of_prefix($object, $prefix) {
		$res = [];
		$prefix_len = strlen($prefix);
		foreach ($object as $key => $value) {
			if (strpos($key, $prefix) === 0) {
				$str_len = strlen($key);
				$key = substr($key, $prefix_len, $str_len);
				$key = strtolower(substr($key, 0, 1)) . substr($key, 1, $str_len - $prefix_len);
				$res[$key] = $value;
			}
		}
		return $res;
	}

	static $__redis = null;

	function getRedis($host, $port, $db, $password, $timeout) {
		global $__redis;

		if (!$__redis) {
			try {
				$hasStock = class_exists("\\Redis");
			} catch (RuntimeException $e) {
				$hasStock = false;
			}

			if ($hasStock) {
				$__redis = new Redis();
				$__redis->auth($password);
				/** @noinspection PhpUnhandledExceptionInspection */
				$__redis->connect($host, $port, $timeout, null, 1);
				$__redis->select($db);
			} else {
				$__redis = new Credis_Client($host, $port, $timeout, '', $db, $password);
			}
		}

		return $__redis;
	}

	function check_recaptcha_v3($token, $secret) {
		$handle = curl_init("https://www.google.com/recaptcha/api/siteverify");
		curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($handle, CURLOPT_CONNECTTIMEOUT, 5);
		curl_setopt($handle, CURLOPT_TIMEOUT, 5);
		curl_setopt($handle, CURLOPT_POST, 1);

		curl_setopt($handle, CURLOPT_POSTFIELDS, [
			"secret" => $secret,
			"response" => $token
		]);

		$response = curl_exec($handle);
		curl_close($handle);

		$json = json_decode($response);

		return $json;
	}

	function send_mail($to, $title, $content) {
		$mail = new PHPMailer(true);

		try {
			// $mail->SMTPDebug = 2;
			$mail->isSMTP();
			$mail->Host = EMAIL_HOST;
			$mail->SMTPAuth = true;
			$mail->Username = EMAIL_LOGIN;
			$mail->Password = EMAIL_PASSWORD;
			$mail->SMTPSecure = EMAIL_SECURE;
			$mail->Port = EMAIL_PORT;
			$mail->CharSet = "utf-8";

			$mail->setFrom(EMAIL_LOGIN, "No reply");
			$mail->addAddress($to);
			// $mail->isHTML(true);
			$mail->Subject = $title;
			$mail->Body = $content;

			$mail->send();
		} catch (Exception $e) {
			echo $mail->ErrorInfo;
			exit;
		}
	}

	function send_mail_to_admin($title, $content) {
		send_mail(EMAIL_ADMIN, $title, $content);
	}
