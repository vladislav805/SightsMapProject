<?

	namespace NeuralNetwork;

	use INeuralNetwork;
	use JsonSerializable;
	use RuntimeException;

	class NeuralNetwork implements INeuralNetwork, JsonSerializable {

		/**
		 * Количество слоев
		 * @var int
		 */
		private $layersCount;

		/**
		 * Количество входов
		 * @var int
		 */
		private $inputsCount;

		/**
		 * Слои
		 * @var Layer[]
		 */
		private $layers;

		/**
		 * Конструктор нейронной сети
		 * @param int[] $map "Карта" количества нейронов в слоях
		 */
		public function __construct($map) {
			$this->layersCount = sizeOf($map);
			$this->inputsCount = $map[0];

			$this->initLayers($map);
		}

		/**
		 * Создание слоев в нейронной сети по заданной "карте"
		 * @param int[] $map "Карта" количества нейронов в слоях
		 */
		private function initLayers($map) {
			$this->layers = [
				new Layer($map[0], $this->inputsCount)
			];

			for ($i = 1; $i < $this->layersCount; ++$i) {
				$this->layers[$i] = new Layer($map[$i], $map[$i - 1]);
			}
		}

		/**
		 * Получение вектора ответа от нейронной сети по тестовому вектору
		 * @param double[] $task Тестовый вектор
		 * @return double[] Результат от нейронной сети
		 */
		public function getAnswer($task) {
			if (sizeof($task) !== $this->inputsCount) {
				throw new RuntimeException("getAnswer: arguments not equals by size");
			}

			$this->layers[0]->acceptSignals($task);
			for ($i = 1; $i < $this->layersCount; ++$i) {
				$this->layers[$i]->acceptSignals($this->layers[$i - 1]->giveSignals());
			}
			return $this->layers[sizeOf($this->layers) - 1]->giveSignals();
		}

		/**
		 * Обучение нейронной сети
		 * @param double[][] $task Обучающая выборка
		 * @param double[][] $answers Правильные ответы к обучающей выборке
		 * @param array $options
		 * @return array
		 */
		public function trainNeuralNetwork($task, $answers, $options = []) {
			$learnCoefficient = isset($options["learnCoefficient"]) ? $options["learnCoefficient"] : 0.8;
			$sureness = isset($options["threshold"]) ? $options["threshold"] : 0.1;

			if (sizeOf($task) !== sizeOf($answers)) {
				throw new RuntimeException("trainNeuralNetwork: arguments not equals by size");
			}

			$q = 0;
			$maxIterationsCount = 100;
			do {
				$totalError = 0;
				$bError = false;
				for ($i = 0, $l = sizeOf($task); $i < $l; ++$i) {
					$errors = $this->getErrors($answers[$i], $this->getAnswer($task[$i]));
					$totalError += $this->getTotalError($errors);
					if ($this->isError($sureness, $errors)) {
						$this->backPropagateAndFix($errors, $learnCoefficient);
						$bError = true;
					}
				}
				//printf("%d ; %.5f\n", $q, $totalError);
				if ($q >= $maxIterationsCount) {
					break;
				}
				++$q;
			} while ($bError);
			return [$totalError, $q];
		}

		/**
		 * Вычислить ошибку между полученным вектором и верным
		 * @param double[] $expect Ожидаемые ответы
		 * @param double[] $real Полученные ответы
		 * @return double[] Ошибка по каждому из входов
		 */
		private function getErrors($expect, $real) {
			if (sizeOf($expect) !== sizeOf($real)) {
				throw new RuntimeException("getAnswer: arguments not equals by size");
			}

			return array_map(function($valExp, $valReal) {
				return $valExp - $valReal;
			}, $expect, $real);
		}

		/**
		 * @param double[] $error
		 * @return mixed
		 */
		private function getTotalError($error) {
			return array_sum(array_map("abs", $error)) / 10;
		}

		/**
		 * @param double $sureness
		 * @param double[] $errors
		 * @return boolean
		 */
		private function isError($sureness, $errors) {
			$e = false;
			foreach ($errors as $error) {
				$e = $e || abs($error) > $sureness;
			}
			return $e;
		}

		/**
		 * Обучение обратным распространением ошибки
		 * @param double[] $errors
		 */
		private function backPropagateErrors($errors) {
			$this->layers[$this->layersCount - 1]->acceptErrors($errors);
			for ($i = $this->layersCount - 1; $i > 0; --$i) {
				$this->layers[$i - 1]->acceptErrors($this->layers[$i]->giveErrors());
			}
		}

		/**
		 * Корректировка весов
		 * @param double $learnFactor
		 */
		private function fixWeights($learnFactor) {
			//for ($i = sizeOf($this->layers) - 1; $i >= 0; --$i) {
			for ($i = 0, $l = sizeOf($this->layers) - 1; $i < $l; ++$i) {
				$this->layers[$i]->fixWeights($learnFactor);
			}
		}

		/**
		 * Запрос на обучение обратным распространением ошибки и корректировкой весов
		 * @param double[] $errors Ошибки
		 * @param $learnFactor
		 */
		private function backPropagateAndFix($errors, $learnFactor) {
			$this->backPropagateErrors($errors);
			$this->fixWeights($learnFactor);
		}

		/**
		 * @param string $path
		 * @return NeuralNetwork
		 */
		public static function load($path) {
			$json = json_decode(file_get_contents($path));

			$nn = new self([0]);

			$nn->layersCount = sizeof($json->layers);
			$nn->layers = [];

			foreach ($json->layers as $layer) {
				$nn->layers[] = Layer::load($layer);
			}

			$nn->inputsCount = $nn->layers[0]->getNeuronsCount();

			return $nn;
		}

		/**
		 * @param string $path
		 * @return boolean
		 */
		public function save($path) {
			$data = json_encode($this, JSON_UNESCAPED_UNICODE);

			file_put_contents($path, $data);

			return true;
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