<?

	namespace NeuralNetwork;

	use InvalidArgumentException;
	use JsonSerializable;

	/**
	 * Нейрон
	 * @package NeuralNetwork
	 */
	class Neuron implements JsonSerializable {

		/**
		 * Веса связей нейрона
		 * @var double[]
		 */
		private $weights;

		/**
		 * Входные сигналы
		 * @var double[]
		 */
		private $input;

		/**
		 * Выходные сигналы
		 * @var double
		 */
		private $output;

		/**
		 * Входной сигнал нейрона смещения
		 * @var double
		 */
		private $bias;

		/**
		 * @var double[]
		 */
		private $delta;

		/**
		 * @var double
		 */
		private $sigma;

		/**
		 * Создание пустого нейрона
		 * @param int $count Количество входов
		 * @return Neuron
		 */
		public static function createEmpty($count) {
			$weights = self::initWeights($count);
			return new self(
				$weights,
				randFloat() - .5
			);
		}

		/**
		 * Создание нейрона с весами
		 * @param double[] $weights
		 * @param double[]|null $bias
		 * @return Neuron
		 */
		public static function createWithWeight($weights, $bias = null) {
			return new self(
				$weights,
				$bias === null ? randFloat() - .5 : $bias
			);
		}

		/**
		 * Создание вектора случайных весов
		 * @param int $n Количество входов
		 * @return double[]
		 */
		private static function initWeights($n) {
			$weights = array_fill(0, $n, 0);
			for ($i = 0; $i < $n; ++$i) {
				$weights[$i] = randFloat() - .5;
			}
			return $weights;
		}

		/**
		 * Конструктор нейрона
		 * @param double[] $weights Веса
		 * @param double $bias Смещение
		 */
		private function __construct($weights, $bias) {
			$this->weights = $weights;
			$this->bias = $bias;
			$this->output = -1;

			$this->sigma = 0;
			$this->delta = [];
		}

		/**
		 * @return double[]
		 */
		public function getWeights() {
			return $this->weights;
		}

		/**
		 * @param double[] $inputs
		 * @return double
		 */
		public function feedForward($inputs) {
			if ($inputs === null) {
				throw new InvalidArgumentException("Neuron: inputs can't be null");
			}

			if (sizeof($inputs) !== sizeof($this->weights)) {
				throw new InvalidArgumentException("Neuron::feedForward(): input[] and weights[] are not equals by size");
			}

			$this->input = $inputs;

			$sum = $this->sum($inputs);
			return $this->sigmoid($sum);
		}

		/**
		 * @param double[] $inputs
		 * @return double
		 */
		private function sum($inputs) {
			$sum = 0;

			for ($i = 0; $i < sizeof($inputs); ++$i) {
				$sum += $inputs[$i] * $this->weights[$i];
			}

			$sum += $this->bias;


			return $sum;
		}

		/**
		 * @param $sum
		 * @return double
		 */
		private function sigmoid($sum) {
			return 1. / (1. + exp(-$sum));
		}

		/**
		 * @return double
		 */
		private function derivative() {
			return $this->output * (1. - $this->output);
		}

		/**
		 * @param double[] $outgoingWeights
		 * @param double[] $nextLayerSigmas
		 * @param double $alpha
		 */
		public function backPropagate($outgoingWeights, $nextLayerSigmas, $alpha) {
			if ($outgoingWeights === null) {
				throw new InvalidArgumentException("Neuron: outgoingWeights can't be null");
			}

			if ($nextLayerSigmas === null) {
				throw new InvalidArgumentException("Neuron: nextLayerSigmas can't be null");
			}

			if (sizeof($outgoingWeights) !== sizeof($nextLayerSigmas)) {
				throw new InvalidArgumentException("Neuron: outgoingWeights and nextLayerSigmas should be of the same size");
			}

			$totalError = 0;

			for ($i = 0; $i < sizeof($outgoingWeights); ++$i) {
				$totalError += $outgoingWeights[$i] * $nextLayerSigmas[$i];
			}

			$this->update($totalError, $alpha);
		}

		/**
		 * @param double $totalError
		 * @param double $alpha
		 */
		private function update($totalError, $alpha) {
			$this->sigma = $totalError * $this->derivative();

			$this->delta = [];

			foreach ($this->input as $anInput) {
				$this->delta[] = $alpha * $this->sigma * $anInput;
			}

			$this->bias = $alpha * $this->sigma;
		}

		/**
		 * @param double $neuronAnswer
		 * @param double $correctAnswer
		 * @param double $alpha
		 */
		public function backPropagateSingle($neuronAnswer, $correctAnswer, $alpha) {
			$error = $correctAnswer - $neuronAnswer;

			$this->update($error, $alpha);
		}

		public function updateWeights() {
			for ($i = 0; $i < sizeof($this->weights); ++$i) {
				$oldWeight = $this->weights[$i];
				$newWeight = $oldWeight + $this->delta[$i];

				$this->weights[$i] = $newWeight;
			}
		}

		/**
		 * @return float
		 */
		public function getSigma() {
			return $this->sigma;
		}

		/**
		 * Сериализация в JSON
		 * @return array
		 */
		public function jsonSerialize() {
			return [
				"weights" => $this->weights,
				"bias" => $this->bias,
				"sigma" => $this->sigma
			];
		}
	}