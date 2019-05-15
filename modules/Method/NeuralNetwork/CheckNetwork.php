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
	class CheckNetwork extends APIPrivateMethod {

		use TGetNetworkWeightsFilePath;

		/**
		 * @param IController $main
		 * @return int
		 */
		public function resolve(IController $main) {
			$path = $this->getNetworkWeightsFilePath($main);

			if (file_exists($path)) {
				return 0;
			}

			/** @var NeuralNetwork $network */
			$builder = new InitializeWeights([]);
			try {
				$w = $builder->fetchUserData($main);
			} catch (APIException $e) {
				$accepted = [
					ErrorCode::NOT_ENOUGH_DATA_FOR_TRAINING
				];
				if (in_array($e->getCode(), $accepted)) {
					return $e->getCode();
				}
			}

			return 0;
		}

	}