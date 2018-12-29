<?php

	namespace Method;

	use Model\Params;

	/**
	 * Абстрактный метод API
	 * @package Method
	 */
	abstract class Method {

		/**
		 * Конструктор принимает либо массив (например, $_REQUEST), либо
		 * объект \Model\Params.
		 * @param array|Params $request
		 */
		public function __construct($request) {
			if (is_array($request)) {
				$this->iterateOverParams($request);
			} elseif (is_object($request) && $request instanceof Params) {
				$this->iterateOverParams($request->getAll());
			}
		}

		/**
		 * Цикл обработки всех параметров
		 * @param array $params
		 */
		private function iterateOverParams($params) {
			foreach ($params as $key => $value) {
				$this->putParam($key, $value);
			}
		}

		/**
		 * Добавление информации в класс по ключу
		 * @param string $key
		 * @param mixed $value
		 */
		private function putParam($key, $value) {
			if (property_exists($this, $key)) {
				$this->{$key} = $this->handleParamValue($value);
			}
		}

		/**
		 * Обработка значения
		 * @param mixed $value
		 * @return mixed
		 */
		private function handleParamValue($value) {
			return is_string($value) ? str_replace("\r", "", trim($value)) : $value;
		}

	}