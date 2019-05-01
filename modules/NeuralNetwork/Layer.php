<?

	namespace NeuralNetwork;

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
		 * Смещение
		 * @var double
		 */
		private $bias;

		/**
		 * Количество нейронов в этом слое
		 * @var int
		 */
		private $neuronsCount;

		/**
		 * Количество нейронов в предыдущем слое
		 * @var int
		 */
		private $prevNeuronCount;

		/**
		 * @param int $count
		 * @param int $prevCount
		 */
		public function __construct($count, $prevCount) {
			$this->neuronsCount = $count;
			$this->prevNeuronCount = $prevCount;
			$this->neurons = $this->createNeurons($count);
			$this->bias = 1; //randFloat() < .5 ? -1 : 1;
		}

		/**
		 * Создание $n нейронов в слое
		 * @param int $n
		 * @return Neuron[]
		 */
		private function createNeurons($n) {
			$s = [];
			for ($i = 0; $i < $n; ++$i) {
				$s[$i] = new Neuron($this->prevNeuronCount + 1);
			}
			return $s;
		}

		/**
		 *
		 * @return double[]
		 */
		public function giveSignals() {
			$signals = [];
			for ($i = 0; $i < $this->neuronsCount; ++$i) {
				$signals[$i] = $this->neurons[$i]->getActivationFunction();
			}
			return $signals;
		}

		/**
		 * @param double[] $signals
		 */
		public function acceptSignals($signals) {
			foreach ($this->neurons as $neuron) {
				$neuron->takeSignals($signals, $this->bias);
			}
		}

		/**
		 * @param double[] $errors
		 */
		public function acceptErrors($errors) {
			for ($i = 0; $i < $this->neuronsCount; ++$i) {
				$this->neurons[$i]->takeError($errors[$i]);
			}
		}

		/**
		 * @return double[]
		 */
		public function giveErrors() {
			$layErrs = [];
			for ($i = 0; $i < $this->prevNeuronCount; ++$i) {
				for ($j = 0; $j < $this->neuronsCount; ++$j) {
					$layErrs[] += $this->neurons[$j]->giveErrors()[$i];
				}
			}
			return $layErrs;
		}

		/**
		 * @param double $learnFactor
		 */
		public function fixWeights($learnFactor) {
			foreach ($this->neurons as $neuron) {
				$neuron->fixWeights($learnFactor);
			}
		}

		/**
		 * @return array
		 */
		public function jsonSerialize() {
			return [
				"neurons" => $this->neurons,
				"prevCount" => $this->prevNeuronCount,
				"bias" => $this->bias
			];
		}
	}