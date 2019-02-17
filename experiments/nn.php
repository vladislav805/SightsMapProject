<?
	function randFloat() {
		return (float) rand() / (float) getRandMax();
	}

	class NeuralNetwork {

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
		 * @param double $learnCoef
		 * @param double $sureness
		 */
		public function trainNeuralNetwork($task, $answers, $learnCoef, $sureness) {
			// $bError = false;
			// $totalError = 0;
			do {
				$totalError = 0;
				$bError = false;
				for ($i = 0, $l = sizeOf($task) - 1; $i < $l; ++$i) {
					$errors = $this->getErrors($answers[$i], $this->getAnswer($task[$i]));
					$totalError += $this->getTotalError($errors);
					if ($this->isError($sureness, $errors)) {
						$this->backpropagateAndFix($errors, $learnCoef);
						$bError = true;
					}
					//printf("%.5f\n", $totalError);
				}
			} while ($bError);
		}

		/**
		 * @param double[] $expect
		 * @param double[] $real
		 * @return double[]
		 */
		public function getErrors($expect, $real) {
			if (sizeOf($expect) !== sizeOf($real)) {
				throw new RuntimeException();
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
			for ($i = sizeOf($this->layers) - 1; $i > -1; --$i) {
				$this->layers[$i]->fixWeights($learnCoef);
			}
		}

		private function backpropagateAndFix($errors, $learCoef) {
			$this->backPropagateErrors($errors);
			$this->fixWeights($learCoef);
		}
	}

	class Layer {
		/** @var Neuron[] */
		private $neurons;

		/** @var double */
		private $bias;

		/** @var int */
		private $neuronsCount;

		/** @var int */
		private $prevNeuronCount;

		/**
		 * @param int $count
		 * @param int $prevCount
		 */
		public function __construct($count, $prevCount) {
			$this->neuronsCount = $count;
			$this->prevNeuronCount = $prevCount;
			$this->neurons = $this->createNeurons($count);
			$this->bias = randFloat() < .5 ? -1 : 1;
		}

		/**
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
		 * @return double[]
		 */
		public function giveSignals() {
			$signals = [];
			for ($i = 0; $i < $this->neuronsCount; ++$i) {
				$signals[$i] = $this->neurons[$i]->giveSigmSignal();
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
			$layErrs = array_fill(0, $this->prevNeuronCount, 0);
			for ($i = 0; $i < $this->prevNeuronCount; ++$i) {
				for ($j = 0; $j < $this->neuronsCount; ++$j) {
					$layErrs[$i] += $this->neurons[$j]->giveErrors()[$i];
				}
			}
			return $layErrs;
		}

		/**
		 * @param double $learnCoef
		 */
		public function fixWeights($learnCoef) {
			foreach ($this->neurons as $neuron) {
				$neuron->fixWeights($learnCoef);
			}
		}
	}

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
			$this->e = 0;
			$this->count = $count;
			$this->weights = array_fill(0, $count, 0);
			$this->error = 0;
			$this->initWeights();
		}

		private function initWeights() {
			for ($i = 0, $l = sizeOf($this->weights); $i < $l; ++$i) {
				$this->weights[$i] = randFloat() < 0.5
					? randFloat() * 0.3 + (15 / $this->count)
					: -randFloat() * 0.3 - (15 / $this->count);
			}
		}

		/**
		 * @param double[] $signals
		 * @param double $bias
		 */
		public function takeSignals($signals, $bias) {
			$this->sigmIn = $signals;
			$this->biasIn = $bias;
			$this->e = 0;

			for ($i = 0, $l = sizeOf($signals); $i < $l; ++$i) {
				$this->e += $signals[$i] * $this->weights[$i];
			}

			$this->e += $bias * $this->weights[$this->count - 1];
		}

		/**
		 * Сигмоида
		 * @return double
		 */
		public function giveSigmSignal() {
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
				$this->weights[$i] += $this->sigmIn[$i] * $learnCoef * $this->giveSigmSignal() * (1 - $this->giveSigmSignal()) * $this->error;
			}
			$this->weights[$this->count - 1] += $this->biasIn * $learnCoef * $this->giveSigmSignal() * (1 - $this->giveSigmSignal()) * $this->error;
		}
	}



	$nn = new NeuralNetwork(4, [3,1]);

	$task = [
		[1, 0, 0, 0],
		[1, 0, 0, 0],
		[0, 1 ,0, 0],
		[1, 1 ,0, 0],
		[0, 0 ,1, 0],
		[1, 0 ,1, 0],
		[0, 1 ,1, 0],
		[1, 1 ,1, 0],
		[0, 0 ,0, 1],
		[1, 1 ,0, 1],
		[0, 0 ,1, 1],
	];

	$answers = [
		[1],
		[0],
		[0],
		[0],
		[0],
		[1],
		[1],
		[1],
		[0],
		[0]
	];

	$nn->trainNeuralNetwork($task, $answers, 0.8, 0.5);

	$t1 = $nn->getAnswer([1, 0, 0, 1])[0];
	$t2 = $nn->getAnswer([0, 1, 0, 1])[0];

	printf("Expect: [1, 0]\nActual: [%.5f, %.5f]", $t1, $t2);



	print PHP_EOL;