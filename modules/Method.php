<?php

	abstract class Method {

		public function __construct($request) {
			if (is_array($request)) {
				foreach ($request as $key => $value) {
					if (property_exists($this, $key)) {
						$this->{$key} = safeString($value);
					}
				}
			} elseif (get_class($request) === "Params") {
				/** @var Params $request */
				$params = $request->getAll();
				foreach ($params as $key => $value) {
					if (property_exists($this, $key)) {
						$this->{$key} = $value;
					}
				}
			}
		}

	}