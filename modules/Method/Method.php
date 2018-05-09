<?php

	namespace Method;

	use Model\Params;

	abstract class Method {

		public function __construct($request) {
			if (is_array($request)) {
				foreach ($request as $key => $value) {
					$this->putParam($key, $value);
				}
			} elseif (is_object($request) && get_class($request) === "Model\\Params") {
				/** @var Params $request */
				$params = $request->getAll();
				foreach ($params as $key => $value) {
					$this->putParam($key, $value);
				}
			}
		}

		private function putParam($key, $value) {
			if (property_exists($this, $key)) {
				$this->{$key} = is_string($value) ? trim($value) : $value;
			}
		}

	}