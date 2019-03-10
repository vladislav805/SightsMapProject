<?

	namespace NeuralNetwork;

	use RuntimeException;

	class Neuron {
		/** @var double */
		private $e;

		/** @var double[] */
		private $weights;

		/** @var int */
		private $count;

		/** @var double */
		private $error;

		/** @var double[] */
		private $sigmIn;

		/** @var double */
		private $biasIn;

		/**
		 * @param int $count
		 */
		public function __construct($count) {
			$this->e = 0.0;
			$this->count = $count;
			$this->weights = array_fill(0, $count, 0);
			$this->error = 0;
			$this->initWeights();
		}

		private function initWeights() {
			for ($i = 0, $l = sizeOf($this->weights); $i < $l; ++$i) {
				$this->weights[$i] = randFloat() < 0.5
					? randFloat() * 0.1 + (15 / $this->count)
					: -randFloat() * 0.1 - (15 / $this->count);
			}
		}

		/**
		 * @param double[] $signals
		 * @param double $bias
		 */
		public function takeSignals($signals, $bias) {
			if (sizeOf($signals) + 1 !== $this->count) {
				throw new RuntimeException("getAnswer: arguments not equals by size");
			}
			$this->sigmIn = $signals;
			$this->biasIn = $bias;
			$this->e = 0.0;

			for ($i = 0, $l = sizeOf($signals); $i < $l; ++$i) {
				$this->e += $signals[$i] * $this->weights[$i];
			}

			$this->e += $bias * $this->weights[$this->count - 1];
		}

		/**
		 * Сигмоида
		 * @return double
		 */
		public function giveSigmoidSignal() {
			return 1 / (1 + exp(-$this->e));
		}

		/**
		 * @param double $error
		 */
		public function takeError($error) {
			$this->error = $error;
		}

		/**
		 * @return double[]
		 */
		public function giveErrors() {
			$errors = [];
			for ($i = 0; $i < $this->count - 1; ++$i) {
				$errors[$i] = $this->error * $this->weights[$i];
			}
			return $errors;
		}

		/**
		 * @param double $learnCoef
		 */
		public function fixWeights($learnCoef) {
			for ($i = 0; $i < $this->count - 1; ++$i) {
				$this->weights[$i] += $this->sigmIn[$i] * $learnCoef * $this->giveSigmoidSignal() * (1 - $this->giveSigmoidSignal()) * $this->error;
			}
			$this->weights[$this->count - 1] += $this->biasIn * $learnCoef * $this->giveSigmoidSignal() * (1 - $this->giveSigmoidSignal()) * $this->error;
		}
	}