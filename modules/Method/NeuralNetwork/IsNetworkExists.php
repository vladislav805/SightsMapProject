<?php

	namespace Method\NeuralNetwork;

	use Method\APIException;
	use Method\APIPrivateMethod;
	use Model\IController;

	/**
	 * Проверка на то, что сеть существует
	 * @package Method\NeuralNetwork
	 */
	class IsNetworkExists extends APIPrivateMethod {

		use TGetNetworkWeightsFilePath;

		/**
		 * @param IController $main
		 * @return APIException|null
		 */
		public function resolve(IController $main) {
			return file_exists($this->getNetworkWeightsFilePath($main));
		}

	}