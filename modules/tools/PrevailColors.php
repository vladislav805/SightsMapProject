<?

	namespace tools;

	use RuntimeException;

	/**
	 * This class can be used to get the most common colors in an image.
	 * It needs one parameter:
	 * 	$image - the filename of the image you want to process.
	 * Optional parameters:
	 *
	 *	$count - how many colors should be returned. 0 means all. default=20
	 *	$reduce_brightness - reduce (not eliminate) brightness variants? default=true
	 *	$reduce_gradients - reduce (not eliminate) gradient variants? default=true
	 *	$delta - the amount of gap when quantizing color values.
	 *		Lower values mean more accurate colors. default=16
	 *
	 * Author: 	Csongor Zalatnai
	 *
	 * Modified By: Kepler Gelotte - Added the gradient and brightness variation
	 * 	reduction routines. Kudos to Csongor for an excellent class. The original
	 * 	version can be found at:
	 *
	 *	http://www.phpclasses.org/browse/package/3370.html
	 *
	 */

	class PrevailColors {
		private $PREVIEW_WIDTH = 150;
		private $PREVIEW_HEIGHT = 150;

		private $image;

		public function __construct($image) {
			$this->image = $image;

			/*if (!is_readable($this->image)) {
				throw new RuntimeException("Image " . $this->image . " does not exist or is unreadable");
			}*/
		}

		/**
		 * Returns the colors of the image in an array, ordered in descending order, where the keys are the colors, and the values are the count of the color.
		 *
		 * @param int $count
		 * @param bool $reduceBrightness
		 * @param bool $reduceGradients
		 * @param int $delta
		 * @return array
		 */
		public function getColors($count = 20, $reduceBrightness = true, $reduceGradients = true, $delta = 16) {
			if ($delta > 2) {
				$halfDelta = $delta / 2 - 1;
			} else {
				$halfDelta = 0;
			}

			// WE HAVE TO RESIZE THE IMAGE, BECAUSE WE ONLY NEED THE MOST SIGNIFICANT COLORS.
			list($oWidth, $oHeight, $type) = getImageSize($this->image);
			$scale = 1;

			if ($oWidth > 0) {
				$scale = min($this->PREVIEW_WIDTH / $oWidth, $this->PREVIEW_HEIGHT / $oHeight);
			}

			if ($scale < 1) {
				$width = floor($scale * $oWidth);
				$height = floor($scale * $oHeight);
			} else {
				$width = $oWidth;
				$height = $oHeight;
			}

			$image_resized = imageCreateTrueColor($width, $height);

			switch ($type) {
				case IMAGETYPE_GIF:
					$image_orig = imageCreateFromGif($this->image);
					break;

				case IMAGETYPE_JPEG:
					$image_orig = imageCreateFromJpeg($this->image);
					break;

				case IMAGETYPE_PNG:
					$image_orig = imageCreateFromPng($this->image);
					break;

				default:
					throw new RuntimeException("Unknown image type");
			}

			// WE NEED NEAREST NEIGHBOR RESIZING, BECAUSE IT DOESN'T ALTER THE COLORS
			imageCopyResampled($image_resized, $image_orig, 0, 0, 0, 0, $width, $height, $oWidth, $oHeight);

			$im = $image_resized;
			$imgWidth = imageSx($im);
			$imgHeight = imageSy($im);

			$pixelCount = 0;

			$hexArray = [];

			for ($y = 0; $y < $imgHeight; $y++) {
				for ($x = 0; $x < $imgWidth; $x++) {
					$pixelCount++;
					$index = imageColorAt($im, $x, $y);
					$colors = imageColorsForIndex($im, $index);

					// ROUND THE COLORS, TO REDUCE THE NUMBER OF DUPLICATE COLORS
					if ($delta > 1) {
						foreach ($colors as $k => $v) {
							$colors[$k] = min((int) ((($colors[$k]) + $halfDelta) / $delta) * $delta, 0xff);
						}
					}

					$hex = $this->getHexByArrayRGB($colors);

					if (!isset($hexArray[$hex])) {
						$hexArray[$hex] = 1;
					} else {
						$hexArray[$hex]++;
					}
				}
			}

			// Reduce gradient colors
			if ($reduceGradients) {
				arSort($hexArray, SORT_NUMERIC);

				$gradients = [];
				foreach ($hexArray as $hex => $num) {
					if (!isset($gradients[$hex])) {
						$newHEX = $this->findAdjacent($hex, $gradients, $delta);
						$gradients[$hex] = $newHEX;
					} else {
						$newHEX = $gradients[$hex];
					}

					if ($hex != $newHEX) {
						$hexArray[$hex] = 0;
						$hexArray[$newHEX] += $num;
					}
				}
			}

			// Reduce brightness variations
			if ($reduceBrightness) {
				arsort($hexArray, SORT_NUMERIC);

				$brightness = [];
				foreach ($hexArray as $hex => $num) {
					if (!isset($brightness[$hex])) {
						$newHEX = $this->normalize($hex, $brightness, $delta);
						$brightness[$hex] = $newHEX;
					} else {
						$newHEX = $brightness[$hex];
					}

					if ($hex !== $newHEX) {
						$hexArray[$hex] = 0;
						$hexArray[$newHEX] += $num;
					}
				}
			}

			arsort($hexArray, SORT_NUMERIC);

			// convert counts to percentages
			foreach ($hexArray as $key => $value) {
				$hexArray[(string) $key] = (float) $value / $pixelCount;
			}

			return $count ?  array_slice($hexArray, 0, $count, true) : $hexArray;
		}

		private function normalize($hex, $hexArray, $delta) {
			$lowest = 255;
			$highest = 0;
			$colors['red'] = hexdec(substr($hex, 0, 2));
			$colors['green'] = hexdec(substr($hex, 2, 2));
			$colors['blue'] = hexdec(substr($hex, 4, 2));

			foreach ($colors as $value) {
				if ($lowest > $value) {
					$lowest = $value;
				}
				if ($highest < $value) {
					$highest = $value;
				}
			}

			// Do not normalize white, black, or shades of grey unless low delta
			if ($lowest === $highest) {
				if ($delta <= 32) {
					if ($lowest === 0 || $highest >= (255 - $delta)) {
						return $hex;
					}
				} else {
					return $hex;
				}
			}

			for (; $highest < 256; $lowest += $delta, $highest += $delta) {
				$c = $colors;
				foreach ($c as $k => $v) {
					$c[$k] = $v - $lowest;
				}
				$new_hex = $this->getHexByArrayRGB($c);

				if (isset($hexArray[$new_hex])) {
					// same color, different brightness - use it instead
					return $new_hex;
				}
			}

			return $hex;
		}

		private function findAdjacent($hex, $gradients, $delta) {
			$red = hexdec(substr($hex, 0, 2));
			$green = hexdec(substr($hex, 2, 2));
			$blue = hexdec(substr($hex, 4, 2));

			if ($red > $delta) {
				$new_hex = substr("0" . dechex($red - $delta), -2) . substr("0" . dechex($green), -2) . substr("0" . dechex($blue), -2);
				if (isset($gradients[$new_hex])) {
					return $gradients[$new_hex];
				}
			}
			if ($green > $delta) {
				$new_hex = substr("0" . dechex($red), -2) . substr("0" . dechex($green - $delta), -2) . substr("0" . dechex($blue), -2);
				if (isset($gradients[$new_hex])) {
					return $gradients[$new_hex];
				}
			}
			if ($blue > $delta) {
				$new_hex = substr("0" . dechex($red), -2) . substr("0" . dechex($green), -2) . substr("0" . dechex($blue - $delta), -2);
				if (isset($gradients[$new_hex])) {
					return $gradients[$new_hex];
				}
			}

			if ($red < (255 - $delta)) {
				$new_hex = substr("0" . dechex($red + $delta), -2) . substr("0" . dechex($green), -2) . substr("0" . dechex($blue), -2);
				if (isset($gradients[$new_hex])) {
					return $gradients[$new_hex];
				}
			}
			if ($green < (255 - $delta)) {
				$new_hex = substr("0" . dechex($red), -2) . substr("0" . dechex($green + $delta), -2) . substr("0" . dechex($blue), -2);
				if (isset($gradients[$new_hex])) {
					return $gradients[$new_hex];
				}
			}
			if ($blue < (255 - $delta)) {
				$new_hex = substr("0" . dechex($red), -2) . substr("0" . dechex($green), -2) . substr("0" . dechex($blue + $delta), -2);
				if (isset($gradients[$new_hex])) {
					return $gradients[$new_hex];
				}
			}

			return $hex;
		}

		private function getHexByArrayRGB($colors) {
			return substr("0" . dechex($colors['red']), -2) .
				substr("0" . dechex($colors['green']), -2) .
				substr("0" . dechex($colors['blue']), -2);
		}
	}