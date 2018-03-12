<?php

	namespace Method;

	class APIException extends \Exception implements \JsonSerializable {

		private $extra;

		/**
		 * APIException constructor.
		 * @param int $code
		 * @param int $extra
		 * @param string|boolean $message
		 */
		public function __construct($code = 0, $extra = 0, $message = false) {
			parent::__construct($message === false ? "error #" . $code : $message, $code, null);

			$this->extra = $extra;
		}

		public function jsonSerialize() {
			$d = [
				"errorId" => $this->getCode(),
				"message" => $this->getMessage()
			];

			if ($this->extra) {
				$d["extra"] = $this->extra;
			}

			return $d;
		}

	}