<?php

	namespace Method\NeuralNetwork;

	use Method\APIPrivateMethod;
	use Model\IController;
	use Model\INotReturnablePublicAPI;
	use NeuralNetwork\NeuralNetwork;

	/**
	 * Получение сети сети.
	 * @package Method\NeuralNetwork
	 */
	class LoadNetwork extends APIPrivateMethod implements INotReturnablePublicAPI {

		use TGetNetworkWeightsFilePath;

		/** @var boolean */
		protected $forceRebuildNetwork;

		/**
		 * @param IController $main
		 * @return NeuralNetwork
		 */
		public function resolve(IController $main) {
			$path = $this->getNetworkWeightsFilePath($main);;

			set_time_limit(60);

			if (!file_exists($path) || $this->forceRebuildNetwork) {
				/** @var NeuralNetwork $network */
				$builder = null;
				$tries = 3;
				do {
					$builder = new InitializeWeights([]);
					$network = $main->perform($builder);
					if (!--$tries) {
						break;
					}
				} while ($builder->getError() > 0.85);
				//define("__NN_ERROR", $builder->getError());
			} else {
				$network = NeuralNetwork::load($path);
			}

			return $network;
		}

	}