<?
	/** @noinspection PhpUnusedParameterInspection */

	namespace Pages;

	class TelegramAuthPage extends BasePage {

		const TIME_EXPIRED = 5 * MINUTE;

		/**
		 * @param $action
		 * @return mixed
		 */
		protected function prepare($action) {

			$nowTime = time();

			$userId = $this->mController->getSession()->getUserId();

			$partTime = (int) rand(0x100000, 0xffffff) + pow(2, $userId);

			$redis = $this->mController->getRedis();

			$key = mb_strtolower("telegramAuth" . $partTime);

			$redis->set($key, json_encode([
				"userId" => $userId
			], JSON_UNESCAPED_UNICODE), self::TIME_EXPIRED);

			return [
				"code" => strToUpper(dechex($partTime)),
				"expiredIn" => $nowTime + self::TIME_EXPIRED
			];
		}

		/**
		 * @param mixed $data
		 * @return string
		 */
		public function getBrowserTitle($data) {
			return "Авторизация для Telegram";
		}

		/**
		 * @param mixed $data
		 * @return string
		 */
		public function getPageTitle($data) {
			return $this->getBrowserTitle($data);
		}

		public function getContent($data) {
			require_once self::$ROOT_DOC_DIR . "telegram.auth.content.php";
		}

	}