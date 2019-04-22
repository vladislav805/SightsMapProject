<?
	namespace PerceptronNetwork;

	class NeuralNetwork implements \INeuralNetwork {

		const WEIGHTS_NOT_READ = 1;
		const WEIGHTS_NOT_WRITE = 2;

		/** @var int */
		private $count;

		/** @var Perceptron[] */
		private $perceptrons;

		/** @var string|null */
		private $weightsFile;

		/** @var int */
		private $wfMode = 0;

		public function __construct($count) {
			$this->count = $count;
			$this->createPerceptrons();
		}

		private function createPerceptrons() {
			$this->perceptrons = [];
			for ($i = 0; $i < $this->count; ++$i) {
				$this->perceptrons[] = new Perceptron($this->count);
			}
		}

		public function setWeightFile($path, $mode = 0) {
			$this->weightsFile = $path;
			$this->wfMode = $mode;

			$this->loadWeightsFromFile();
		}

		private function loadWeightsFromFile() {
			if (!$this->weightsFile || !file_exists($this->weightsFile) || ($this->wfMode & self::WEIGHTS_NOT_READ) > 0) {
				return;
			}

			$res = json_decode(file_get_contents($this->weightsFile), true);
			for ($i = 0, $l = sizeof($res); $i < $l; ++$i) {
				$this->perceptrons[$i]->setWeights($res[$i]);
			}
		}

		private function saveWeightsToFile() {
			if (!$this->weightsFile || ($this->wfMode & self::WEIGHTS_NOT_WRITE) > 0) {
				return;
			}

			$data = json_encode($this->perceptrons, JSON_UNESCAPED_UNICODE);
			file_put_contents($this->weightsFile, $data);
		}

		/**
		 * Подача нейронной сети обучающей выборки и правильных ответов к ней
		 * @param double[][]|int[][] $task Обучающая выборка
		 * @param double[][]|int[][] $answers Правильные ответы
		 * @param array $options Параметры для обучения
		 * @return array
		 */
		public function trainNeuralNetwork($task, $answers, $options = []) {
			$log = [];
			$l = 0;
			do {
				$errors = 0;

				$i = 0;
				foreach ($task as $item) {

					$j = 0;
					foreach ($this->perceptrons as $perceptron) {
						$perceptron->sinapes = $item;
						$perceptron->mul_weights();
						$perceptron->sum_muls();

						$answer = $perceptron->threshold();

						if ($i === $j && !$answer) {
							$perceptron->teach(1);
							++$errors;
							$log[] = sprintf("Error %d, must me 1 instead 0, learning...", $j);
						}
						if ($i !== $j && $answer) {
							$perceptron->teach(-1);
							++$errors;
							$log[] = sprintf("Error %d, must me 0 instead 1, learning...", $j);
						}
					}

					++$i;
				}

				if (++$l > 200) {
					$log[] = sprintf("Limit iterations");
					break;
				}
			} while ($errors > 0);

			$this->saveWeightsToFile();

			return [$errors, $l];
		}

		/**
		 * Получение ответа по заданным входным параметрам
		 * @param double[]|int[] $task Выборка
		 * @return double[]
		 */
		public function getAnswer($task) {
			$vec = [];
			foreach ($this->perceptrons as $perceptron) {
				$perceptron->sinapes = $task;
				$perceptron->mul_weights();
				$perceptron->sum_muls();

				//$vec[] = [$perceptron->sum, $perceptron->limit];
				$vec[] = $perceptron->threshold();
			}
			return $vec;
		}
	}