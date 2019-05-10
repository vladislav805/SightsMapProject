<?

	namespace NeuralNetwork;

	use INeuralNetwork;
	use InvalidArgumentException;
	use JsonSerializable;

	/**
	 * Нейронная сеть, перцептрон
	 * @package NeuralNetwork
	 */
	class NeuralNetwork implements INeuralNetwork, JsonSerializable {

		/**
		 * Количество слоев
		 * @var int
		 */
		private $layersCount;

		/**
		 * Слои нейронной сети
		 * @var Layer[]
		 */
		private $layers;

		const NUMBER_OF_EPOCHS = 1000;

		const THRESHOLD = .05;

		/**
		 * Конструктор нейронной сети
		 * @param int[] $map "Карта" количества нейронов в слоях
		 */
		public function __construct($map) {
			if (sizeof($map) < 2) {
				throw new InvalidArgumentException("NeuralNetwork: network can't have less than two layers");
			}

			$this->layersCount = sizeOf($map);
			$this->initLayers($map);
		}

		/**
		 * Создание слоев в нейронной сети по заданной "карте"
		 * @param int[] $map "Карта" количества нейронов в слоях
		 */
		private function initLayers($map) {
			$this->layers = [];

			for ($i = 1; $i < sizeof($map); ++$i) {
				$this->layers[] = Layer::create($map[$i], $map[$i - 1]);
			}

			$this->layers[sizeof($this->layers) - 1]->setAsOutputLayer();
		}

		/**
		 * @param double[] $inputs
		 * @return double[]
		 */
		public function getAnswer($inputs) {
			if ($inputs === null) {
				throw new InvalidArgumentException("NeuralNetwork: inputs can't be null");
			}

			foreach ($this->layers as $layer) {
				$inputs = $layer->feedForward($inputs);
			}

			return $inputs;
		}

		public function trainNeuralNetwork($task, $ans, $opt = []) {
			return $this->trainNetwork($task, $ans, 0.9);
		}

		/**
		 * @param double[][] $inputs
		 * @param double[][] $correctAnswers
		 * @param double $alpha
		 */
		public function trainNetwork($inputs, $correctAnswers, $alpha) {
			if ($inputs === null) {
				throw new InvalidArgumentException("NeuralNetwork: inputs can't be null");
			}

			if ($correctAnswers === null) {
				throw new InvalidArgumentException("NeuralNetwork: correctAnswers can't be null");
			}

			if (sizeof($inputs) !== sizeof($correctAnswers)) {
				throw new InvalidArgumentException("NeuralNetwork: inputs and correctAnswers should be of the same size");
			}

			$max = sizeof($inputs) - 1;

			$grad = [];

			for ($i = 0; $i < self::NUMBER_OF_EPOCHS; ++$i) {
				$inputIndex = rand(0, $max);

				$networkOutput = $this->getAnswer($inputs[$inputIndex]);
				$correctAnswer = $correctAnswers[$inputIndex];

				/*if ($i % 1000 === 0) {
					printf("\nEpoch " . $i . ":\n");
					printf(join(",", $inputs[$inputIndex]) . " => " . join(",", $networkOutput) . ". Correct answer: " . join(",", $correctAnswer));
				}*/

				$grad[] = $this->getError($networkOutput, $correctAnswer);


				$this->backPropagate($networkOutput, $correctAnswer, $alpha);
			}

			return [
				"grad" => $grad
			];
		}

		/**
		 * @param double[] $networkAnswer
		 * @param double[] $correctAnswer
		 * @param double $alpha
		 */
		private function backPropagate($networkAnswer, $correctAnswer, $alpha) {
			$this->layers[sizeof($this->layers) - 1]->backPropagateOutputLayer($networkAnswer, $correctAnswer, $alpha);

			for ($i = sizeof($this->layers) - 2; $i >= 0; --$i) {
				$nextLayerWeights = $this->layers[$i + 1]->getWeights();
				$nextLayerSigmas = $this->layers[$i + 1]->getSigmas();

				$this->layers[$i]->backPropagate($nextLayerWeights, $nextLayerSigmas, $alpha);
			}

			foreach ($this->layers as $layer) {
				$layer->updateWeights();
			}
    	}

		/**
		 * @param double[] $networkAnswer
		 * @param double[] $correctAnswer
		 * @return double
		 */
    	private function getError($networkAnswer, $correctAnswer) {
			if (sizeof($correctAnswer) !== sizeof($networkAnswer)) {
				throw new InvalidArgumentException("NeuralNetwork: networkAnswer and correctAnswer should be of the same size");
			}

			$error = 0;

        	for ($i = 0, $l = sizeof($networkAnswer); $i < $l; ++$i) {
				$error += ($correctAnswer[$i] - $networkAnswer[$i]) * ($correctAnswer[$i] - $networkAnswer[$i]);
			}

        	return sqrt($error);
		}

		/**
		 * @param double[][] $inputs
		 * @param double[][] $correctAnswers
		 */
		public function testNetwork($inputs, $correctAnswers) {
			if ($inputs === null) {
				throw new InvalidArgumentException("NeuralNetwork: inputs can't be null");
			}

			if ($correctAnswers === null) {
				throw new InvalidArgumentException("NeuralNetwork: correctAnswers can't be null");
			}

			if (sizeof($inputs) !== sizeof($correctAnswers)) {
				throw new InvalidArgumentException("NeuralNetwork: inputs and correctAnswers should be of the same size");
			}

			printf("\n --------- TEST ---------\n");

			$totalInputsCounter = 0;
			$correctAnswersCounter = 0;


			for ($i = 0, $l = sizeof($inputs); $i < $l; ++$i) {
				$out = $this->getAnswer($inputs[$i]);


				printf("[%d] => [%s], expected [%s]\n", $i, join(",", $out), join(",", $correctAnswers[$i]));

				if (abs($out[0] - $correctAnswers[$i][0]) < self::THRESHOLD) {
					++$correctAnswersCounter;
				}

				++$totalInputsCounter;
			}

			$accuracy = $correctAnswersCounter / $totalInputsCounter;

			printf("\nAccuracy: %.5f\n", $accuracy);
		}

		/**
		 * Сериализация в JSON
		 * @return array
		 */
		public function jsonSerialize() {
			return [
				"version" => "1.0",
				"layers" => $this->layers
			];
		}
	}