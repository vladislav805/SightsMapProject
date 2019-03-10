<?

	namespace NeuralNetwork;

	use INeuralNetwork;
	use RuntimeException;

	class NeuralNetwork implements INeuralNetwork {

		/** @var int */
		private $layersCount;

		/** @var double[] */
		private $sensors;

		/** @var Layer[] */
		private $layers;

		public function __construct($n, $map) {
			$this->sensors = array_fill(0, $n, 0);
			$this->layersCount = sizeOf($map);
			$this->layers = [];
			$this->initLayers($map);
		}

		private function initLayers($map) {
			$this->layers[0] = new Layer($map[0], sizeOf($this->sensors));
			for ($i = 1; $i < $this->layersCount; ++$i) {
				$this->layers[$i] = new Layer($map[$i], $map[$i - 1]);
			}
		}

		/**
		 * @param double[] $task
		 * @return double[]
		 */
		public function getAnswer($task) {
			if (sizeOf($this->sensors) !== sizeOf($task)) {
				throw new RuntimeException("getAnswer: arguments not equals by size");
			}
			$this->sensors = $task;
			$this->layers[0]->acceptSignals($this->sensors);
			for ($i = 1; $i < $this->layersCount; ++$i) {
				$this->layers[$i]->acceptSignals($this->layers[$i - 1]->giveSignals());
			}
			return $this->layers[sizeOf($this->layers) - 1]->giveSignals();
		}

		/**
		 * @param double[][] $task
		 * @param double[][] $answers
		 * @param array $options
		 * @return array
		 */
		public function trainNeuralNetwork($task, $answers, $options = []) {
			$learnCoefficient = isset($options["learnCoefficient"]) ? $options["learnCoefficient"] : 0.8;
			$sureness = isset($options["sureness"]) ? $options["sureness"] : 0.1;

			// $bError = false;
			// $totalError = 0;
			if (sizeOf($task) !== sizeOf($answers)) {
				throw new RuntimeException("trainNeuralNetwork: arguments not equals by size");
			}

			$q = 0;
			$maxIterationsCount = 20000;
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
		 * @param double[] $expect
		 * @param double[] $real
		 * @return double[]
		 */
		private function getErrors($expect, $real) {
			if (sizeOf($expect) !== sizeOf($real)) {
				throw new RuntimeException("getAnswer: arguments not equals by size");
			}

			$errors = [];
			for ($i = 0, $l = sizeOf($expect); $i < $l; ++$i) {
				$errors[$i] = $expect[$i] - $real[$i];
			}

			return $errors;
		}

		/**
		 * @param double[] $error
		 * @return mixed
		 */
		private function getTotalError($error) {
			return array_sum(array_map("abs", $error));
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
		 * @param double[] $errors
		 */
		private function backPropagateErrors($errors) {
			$this->layers[$this->layersCount - 1]->acceptErrors($errors);
			for ($i = $this->layersCount - 1; $i > 0; --$i) {
				$this->layers[$i - 1]->acceptErrors($this->layers[$i]->giveErrors());
			}
		}

		/**
		 * @param double $learnCoef
		 */
		private function fixWeights($learnCoef) {
			for ($i = sizeOf($this->layers) - 1; $i >= 0; --$i) {
				$this->layers[$i]->fixWeights($learnCoef);
			}
		}

		private function backPropagateAndFix($errors, $learCoef) {
			$this->backPropagateErrors($errors);
			$this->fixWeights($learCoef);
		}
	}