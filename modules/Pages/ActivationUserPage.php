<?

	namespace Pages;

	use Method\APIException;
	use Method\User\Activate;
	use Model\Params;

	class ActivationUserPage extends BasePage {

		protected function prepare($action) {
			$hash = get("hash");

			if (!$hash) {
				$this->error(408);
			}

			try {
				return $this->mController->perform(new Activate((new Params)->set("hash", $hash)));
			} catch (APIException $e) {
				return false;
			}
		}

		/**
		 * @param mixed $data
		 * @return string
		 */
		public function getBrowserTitle($data) {
			return "Подтверждение";
		}

		/**
		 * @param mixed $data
		 * @return string
		 */
		public function getPageTitle($data) {
			return $this->getBrowserTitle($data);
		}

		public function getContent($data) {
			if ($data) {
				print "Активация успешно пройдена. Теперь Вы можете авторизоваться и полноценно пользоваться сервисом. Спасибо!";
			} else {
				print "Что-то пошло не так. Такого активационного ключа нет.";
			}
		}
	}