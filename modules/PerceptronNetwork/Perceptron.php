<?

	namespace PerceptronNetwork;

	use JsonSerializable;

	class Perceptron implements JsonSerializable {

		/** @var double[] */
		public $mul; //отмасштабированные сигналы

		/** @var double[] */
		public $weights; //коэфициенты связей

		/** @var double[] */
		public $sinapes; //синапсы

		/** @var int */
		public $inputCounts;

		/** @var double */
		public $sum; //сумма сигналов

		/** @var int */
		public $limit; //порог

		public function __construct($inputs) {
			$this->inputCounts = $inputs;
			$this->limit = 50;

			$this->sinapes = [];
			$this->weights = [];

			$this->initNetwork();
		}

		private function initNetwork() {
			for ($i = 0; $i < $this->inputCounts; ++$i) {
				$this->sinapes[$i] = randFloat();
				$this->weights[$i] = 0;
			}
		}

		public function setWeights($w) {
			$this->weights = $w;
		}

		public function mul_weights() {
			for ($x = 0; $x < $this->inputCounts; $x++) {
				$this->mul[$x] = $this->sinapes[$x] * $this->weights[$x];
			}
		}

		public function sum_muls() {
			$this->sum = 0;
			for ($x = 0; $x < $this->inputCounts; ++$x) {
				$this->sum += $this->mul[$x];
			}
		}

		public function threshold() {
			return $this->sum >= $this->limit;
		}

		public function teach($c) {
			for ($x = 0; $x < $this->inputCounts; ++$x) {
				$this->weights[$x] += $this->sinapes[$x] * $c;
			}
		}

		public function jsonSerialize() {
			return $this->weights;
		}
	}