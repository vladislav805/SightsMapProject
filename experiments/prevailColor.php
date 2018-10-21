<?

	error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);

	class PrevailColor {

		/** @var resource */
		private $image;

		/** @var int */
		private $accuracy = 5;

		/** @var float[] */
		private $histogram;

		/** @var string */
		private $colors;

		public function __construct($file) {
			if (is_string($file)) {
				$file = imageCreateFromJpeg($file);
			}
			$this->image = $file;
		}

		private function createHistogram() {
			$w = imagesx($this->image);
			$h = imagesy($this->image);

			$n = $w * $h;

			$this->histogram = [];
			$this->colors = [];

			$centerX = $w / 2;
			$centerY = $h / 2;

			$hhx = $centerX / 2;
			$hhy = $centerY / 2;

			//for ($i = 0; $i < $w; $i += $this->accuracy) {
			//	for ($j = 0; $j < $h; $j += $this->accuracy) {
			for ($i = $centerX - $hhx; $i < $centerX + $hhx; $i += $this->accuracy) {
				for ($j = $centerY - $hhy; $j < $centerY + $hhy; $j += $this->accuracy) {
					$rgb = imageColorAt($this->image, $i, $j);

					list($r, $g, $b) = $this->extract($rgb);

					$V = (int) round(($r + $g + $b) / 3);

					// add the point to the histogram
					$this->histogram[$V] += $V / $n;
					$this->colors[$V] = $this->rgb2hex($r, $g, $b);
				}
			}

		}

		/**
		 * @return int[][]
		 */
		public function getHistogram() {
			if (!$this->histogram && !$this->colors) {
				$this->createHistogram();
			}

			return [$this->histogram, $this->colors];
		}

		public function getExtremes() {
			list($histogram, $colors) = $this->getHistogram();

			arsort($histogram);

			$top = array_slice(array_keys($histogram), 0, 3);


			return array_map(function($histIndex) use ($histogram, $colors) {
				return [ $colors[$histIndex], $histogram[$histIndex] ];
			}, $top);
		}

		private function extract($rgb) {
			return [ ($rgb >> 16) & 0xFF, ($rgb >> 8) & 0xFF, $rgb & 0xFF ];
		}

		private function rgb2hex($r, $g, $b) {
			return sprintf("%02x%02x%02x", $r, $g, $b);
		}



	}

	$source_file = "../userdata/97d58351b813/8b3199010a0e/2636288c881a/c930690c0157.b.jpg";
	//$source_file = "../userdata/f3a95db328cb/8e5199252b23/4024ac0cff6d/8000ee8c7bf0.b.jpg";

	// histogram options
	$histMaxHeight = 350;
	$histBarWidth = 4;

	$pc = new PrevailColor($source_file);

	list($hist, $colors) = $pc->getHistogram();

?>
<div style="width: <?=(256*$histBarWidth);?>px; height: <?=$histMaxHeight;?>px; border: 1px solid; background: linear-gradient(to right, white, green); vertical-align: bottom; position:relative;">
<?
	$barHeight = -1;
	for ($i = 0; $i < 0xff; ++$i) {
		$barHeight = ($hist[$i] / .25) * $histMaxHeight;

		printf("<div style=\"position: absolute; bottom: 0; left: %dpx; display: inline-block; width: %dpx; height: %dpx; background: #%s;\"></div>", $i * $histBarWidth, $histBarWidth, $barHeight, $colors[$i]);
	}
?>
</div>
<?
	echo sprintf("<a href='%s'>photo</a>", $source_file);