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

		/** @var boolean */
		protected $forceRebuildNetwork;

		/**
		 * Путь для сохранения данных о сети
		 * @var string
		 */
		private $mUserPath;

		/**
		 * @param IController $main
		 * @return NeuralNetwork
		 */
		public function resolve(IController $main) {
			$this->mUserPath = sprintf("%s/userdata/networks/%d.json", ROOT_PROJECT, $main->getUser()->getId());

			set_time_limit(60);

			if (!file_exists($this->mUserPath) || $this->forceRebuildNetwork) {
				/** @var NeuralNetwork $network */
				$builder = null;
				do {
					$builder = new InitializeWeights([]);
					$network = $main->perform($builder);
				} while ($builder->getError() > 0.85);
				//define("__NN_ERROR", $builder->getError());
			} else {
				$network = NeuralNetwork::load($this->mUserPath);
			}

			return $network;
		}

	}