<?php

	namespace Method\NeuralNetwork;

	use Method\APIException;
	use Method\APIPrivateMethod;
	use Method\ErrorCode;
	use Model\IController;
	use NeuralNetwork\NeuralNetwork;

	/**
	 * Получение сети сети.
	 * @package Method\NeuralNetwork
	 */
	class CheckErrorNetwork extends APIPrivateMethod {

		use TGetNetworkWeightsFilePath;

		/**
		 * @param IController $main
		 * @return APIException|null
		 */
		public function resolve(IController $main) {
			/*if (file_exists($this->getNetworkWeightsFilePath($main))) {
				return null;
			}*/

			/** @var NeuralNetwork $network */
			$builder = new InitializeWeights([]);
			try {
				$builder->fetchUserData($main);
			} catch (APIException $e) {
				$accepted = [
					ErrorCode::NOT_ENOUGH_DATA_FOR_TRAINING
				];
				if (in_array($e->getCode(), $accepted)) {
					return $e;
				}
			}

			return null;
		}

	}