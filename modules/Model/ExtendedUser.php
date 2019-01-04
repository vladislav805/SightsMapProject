<?

	namespace Model;

	class ExtendedUser extends User {

		/** @var int */
		private $telegramId;

		/** @var int */
		private $vkId;

		public function __construct(array $u) {
			parent::__construct($u);

			isset($u["telegramId"]) && ($this->telegramId = (int) $u["telegramId"]);
			isset($u["vkId"]) && ($this->vkId = (int) $u["vkId"]);
		}

		/**
		 * @return int
		 */
		public function getTelegramId() {
			return $this->telegramId;
		}

		/**
		 * @return int
		 */
		public function getVkId() {
			return $this->vkId;
		}

		public function jsonSerialize() {
			return array_merge(parent::jsonSerialize(), [
				"status" => $this->getStatus(),
				"email" => $this->getEmail(),
				"telegramId" => $this->getTelegramId(),
				"vkId" => $this->getVkId()
			]);
		}

	}