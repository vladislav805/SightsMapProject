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

		public function getAverageColor() {
			$frequency = 20;

			$width  = imageSx($this->image);
			$height = imageSy($this->image);
			$count  = round($frequency * ($width * $height) / 100);

			$sumR = 0;
			$sumG = 0;
			$sumB = 0;

			$squareR = 0;
			$squareG = 0;
			$squareB = 0;

			for ($i = 0; $i < $count; ++$i) {
				$x = rand(0, $width  - 1);
				$y = rand(0, $height - 1);

				$color = imageColorAt($this->image, $x, $y);
				$colR = ($color >> 16) & 0xFF;
				$colG = ($color >> 8)  & 0xFF;
				$colB = ($color)       & 0xFF;

				$sumR += $colR;
				$sumG += $colG;
				$sumB += $colB;

				$squareR += $colR * $colR;
				$squareG += $colG * $colG;
				$squareB += $colB * $colB;
			}

			$averR = intval(($sumR / $count) << 16);
			$averG = intval(($sumG / $count) <<  8);
			$averB = intval( $sumB / $count);
			$average = $averR + $averG + $averB;

			$dispR = ($squareR / $count - ($averR >> 16) * ($averR >> 16));
			$dispG = ($squareG / $count - ($averG >> 8) * ($averG >> 8));
			$dispB = ($squareB / $count - $averB * $averB);
			$dispersion = ($dispR + $dispG + $dispB) / 3;

			return [$average, $dispersion];
		}

		private function extract($rgb) {
			return [ ($rgb >> 16) & 0xFF, ($rgb >> 8) & 0xFF, $rgb & 0xFF ];
		}

		public static function rgb2hex($r, $g, $b) {
			return sprintf("%02x%02x%02x", $r, $g, $b);
		}



	}

	//$source_file = "../userdata/97d58351b813/8b3199010a0e/2636288c881a/c930690c0157.b.jpg";
	//$source_file = "../userdata/f3a95db328cb/8e5199252b23/4024ac0cff6d/8000ee8c7bf0.b.jpg";
	$source_file = "../userdata/4f1a5fd8fc0c/1c29bd7b6d88/d5f30412a67d/3cba2dd58280.b.jpg";

	// histogram options
	$histMaxHeight = 350;
	$histBarWidth = 4;

	$pc = new PrevailColor($source_file);

	if (false) {
		list($hist, $colors) = $pc->getHistogram();

		?>
		<div style="width: <?=(256 * $histBarWidth);?>px; height: <?=$histMaxHeight;?>px; border: 1px solid; background: linear-gradient(to right, white, green); vertical-align: bottom; position:relative;">
			<?
				$barHeight = -1;
				for ($i = 0; $i < 0xff; ++$i) {
					$barHeight = ($hist[$i] / .25) * $histMaxHeight;

					printf("<div style=\"position: absolute; bottom: 0; left: %dpx; display: inline-block; width: %dpx; height: %dpx; background: #%s;\"></div>", $i * $histBarWidth, $histBarWidth, $barHeight, $colors[$i]);
				}
			?>
		</div>
		<?

	} else {
		list($color, $dispersion) = $pc->getAverageColor();

		printf("<div style='width: 300px;height: 300px; background: #%06x;'></div>%d", $color, $dispersion);
	}

	echo sprintf("<a href='%s'>photo</a>", $source_file);