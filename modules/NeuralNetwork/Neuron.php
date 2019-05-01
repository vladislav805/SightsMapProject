<?

	namespace NeuralNetwork;

	use JsonSerializable;
	use RuntimeException;

	/**
	 * Нейрон
	 * @package NeuralNetwork
	 */
	class Neuron implements JsonSerializable {
		/** @var double */
		private $e;

		/**
		 * Веса нейрона
		 * @var double[]
		 */
		private $weights;

		/**
		 * Количество нейронов
		 * @var int
		 */
		private $count;

		/**
		 * Ошибка
		 * @var double
		 */
		private $error;

		/**
		 * Входные сигналы
		 * @var double[]
		 */
		private $signals;

		/**
		 * Смещение
		 * @var double
		 */
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

		/**
		 * Инициализация весов рандомными значениями
		 */
		private function initWeights() {
			for ($i = 0, $l = sizeOf($this->weights); $i < $l; ++$i) {
				$this->weights[$i] = randFloat() < 0.5
					? randFloat() * 0.1 + (15 / $this->count)
					: -randFloat() * 0.1 - (15 / $this->count);
			}
		}

		/**
		 * Прогонка сигналов через веса
		 * @param double[] $signals
		 * @param double $bias
		 */
		public function takeSignals($signals, $bias) {
			if (sizeOf($signals) + 1 !== $this->count) {
				throw new RuntimeException("getAnswer: arguments not equals by size");
			}
			$this->signals = $signals;
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
		public function getActivationFunction() {
			// Сигмоида
			return 1 / (1 + exp(-$this->e));

			// Гиперболический тангенс
			//return tanh($this->e);
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
		 * Корректировка весов
		 * @param double $learnFactor
		 */
		public function fixWeights($learnFactor) {
			for ($i = 0; $i < $this->count - 1; ++$i) {
				$this->weights[$i] += $this->signals[$i] * $learnFactor * $this->getActivationFunction() * (1 - $this->getActivationFunction()) * $this->error;
			}
			$this->weights[$this->count - 1] += $this->biasIn * $learnFactor * $this->getActivationFunction() * (1 - $this->getActivationFunction()) * $this->error;
		}

		/**
		 * Сериализация в JSON
		 * @return array
		 */
		public function jsonSerialize() {
			return [
				"weights" => $this->weights,
				"e" => $this->e,
				"error" => $this->error,
				"biasIn" => $this->biasIn
			];
		}
	}