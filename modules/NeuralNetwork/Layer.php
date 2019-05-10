<?

	namespace NeuralNetwork;

	use InvalidArgumentException;
	use JsonSerializable;

	/**
	 * Слой нейронной сети
	 * @package NeuralNetwork
	 */
	class Layer implements JsonSerializable {

		/**
		 * Массив из нейронов в этом слое
		 * @var Neuron[]
		 */
		private $neurons;

		/**
		 * @var double[]
		 */
		private $sigmas;

		/**
		 * @var boolean
		 */
		private $isOutputLayer;

		/**
		 * @param int $count
		 * @param int $connectionsPerNeuron
		 * @return Layer
		 */
		public static function create($count, $connectionsPerNeuron) {
			if ($count <= 0) {
				throw new InvalidArgumentException("Layer: should have 1 or more neurons");
			}

			$neurons = [];

			for ($i = 0; $i < $count; ++$i) {
				$neurons[] = Neuron::createEmpty($connectionsPerNeuron);
			}

			return new self($neurons);
		}

		/**
		 * @param Neuron[] $neurons
		 * @return Layer
		 */
		public static function restore($neurons) {
			return new self($neurons);
		}

		/**
		 * Конструктор слоя
		 * @param Neuron[] $neurons
		 */
		private function __construct($neurons) {
			$this->neurons = $neurons;
			$this->sigmas = [];
			$this->isOutputLayer = false;
		}

		/**
		 * @param double[] $inputs
		 * @return double[]
		 */
		public function feedForward($inputs) {
			if ($inputs === null) {
				throw new InvalidArgumentException("Layer: inputs can't be null");
			}

			$output = [];
			foreach ($this->neurons as $neuron) {
				$output[] = $neuron->feedForward($inputs);
			}

			return $output;

			/*return array_map(function(Neuron $neuron) use ($inputs) {
				return ;
			}, $this->neurons);*/
		}

		public function setAsOutputLayer() {
			$this->isOutputLayer = true;
		}

		public function setAsHiddenLayer() {
			$this->isOutputLayer = false;
		}

		/**
		 * @param double[] $networkAnswer
		 * @param double[] $correctAnswer
		 * @param double $alpha
		 */
		public function backPropagateOutputLayer($networkAnswer, $correctAnswer, $alpha) {
			if (!$this->isOutputLayer) {
				throw new InvalidArgumentException("Layer: layer is not an output layer");
			}

			if (sizeof($networkAnswer) !== sizeof($this->neurons)) {
				throw new InvalidArgumentException("Layer: networkAnswer should have the same size with layer");
			}

			for ($i = 0; $i < sizeof($this->neurons); ++$i) {
				$this->neurons[$i]->backPropagateSingle($networkAnswer[$i], $correctAnswer[$i], $alpha);
			}

			$this->sigmas = array_map(function(Neuron $neuron) {
				return $neuron->getSigma();
			}, $this->neurons);
		}

		/**
		 * @param double[][] $nextLayerWeights
		 * @param double[] $nextLayerSigmas
		 * @param double $alpha
		 */
		public function backPropagate($nextLayerWeights, $nextLayerSigmas, $alpha) {
			if ($nextLayerWeights === null) {
				throw new InvalidArgumentException("Layer: nextLayerWeights can't be null");
			}

			if ($nextLayerSigmas === null) {
				throw new InvalidArgumentException("Layer: nextLayerDeltas can't be null");
			}

			$neuronsOutgoingWeights = $this->getNeuronsOutgoingWeights($nextLayerWeights);


			for ($i = 0; $i < sizeof($this->neurons); ++$i) {
				$this->neurons[$i]->backPropagate($neuronsOutgoingWeights[$i], $nextLayerSigmas, $alpha);
			}

			$this->sigmas = array_map(function(Neuron $neuron) {
				return $neuron->getSigma();
			}, $this->neurons);
		}

		public function updateWeights() {
			foreach ($this->neurons as $neuron) {
				$neuron->updateWeights();
			}
		}

		/**
		 * @param double[][] $nextLayerWeights
		 * @return double[][]
		 */
		private function getNeuronsOutgoingWeights($nextLayerWeights) {
			$neuronsOutgoingWeights = [];

			for ($i = 0; $i < sizeof($this->neurons); ++$i) {
				$neuronOutgoingWeights = [];

				foreach ($nextLayerWeights as $weights) {
					$neuronOutgoingWeights[] = $weights[$i];
				}

				$neuronsOutgoingWeights[] = $neuronOutgoingWeights;
			}

			return $neuronsOutgoingWeights;
		}

		/**
		 * @return double[]
		 */
		public function getSigmas() {
			return $this->sigmas;
		}

		/**
		 * @return double[][]
		 */
		public function getWeights() {
			return array_map(function(Neuron $neuron) {
				return $neuron->getWeights();
			}, $this->neurons);
		}

		/**
		 * @return array
		 */
		public function jsonSerialize() {
			return [
				"neurons" => $this->neurons,
				"sigmas" => $this->sigmas
			];
		}
	}