<?php

	namespace Method;

	class APIException extends \Exception implements \JsonSerializable {

		private $extra;

		public function __construct($code = 0, $extra = 0) {
			parent::__construct("error #" . $code, $code, null);

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