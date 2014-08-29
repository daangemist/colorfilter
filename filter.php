<?php
/**
 * Defines the width of the box to divide the image in.
*/
define('BOX_WIDTH', 12);

/**
 * Defines the height of the box to divide the image in.
*/
define('BOX_HEIGHT', 16);

/**
 * The minimum number of boxes that need to match to the average color for the image to be selected.
*/
define('MIN_BOX_COUNT', 3);

/**
 * The amount of red for the color to match the average color of a box against.
*/
define('COLOR_RED', 65);

/**
 * The amount of green for the color to match the average color of a box against.
*/
define('COLOR_GREEN', 46);

/**
 * The amount of blue for the color to match the average color of a box against.
*/
define('COLOR_BLUE', 40);

/**
 * The allowed distance that the average box color may differ from the defined color.
*/
define('FUZZINESS', 6);

$files = glob('*.jpg');
$output = date('YmdHis') . '.html';
$fp = fopen($output, 'w');

$matches = 0;
foreach ($files as $file) {

	if (isImageMatching( $file, BOX_WIDTH, BOX_HEIGHT, MIN_BOX_COUNT, FUZZINESS, COLOR_RED, COLOR_GREEN, COLOR_BLUE)) {
		logHtml('<img src="' . $file . '" />');
		$matches++;
	}
}

fclose($fp);

echo $matches . ' out of ' . count($files) . ' were approved.' . PHP_EOL;
die;

/**
 * Writes a HTML string to the output file.
*/
function logHtml($html) {
	global $fp;
	fputs($fp, $html . PHP_EOL);
}

/**
 * Checks if the image denoted by $file is matching the defined color. It does this by dividing the
 * image into small squares. For each square the average color is calculated. If the average color comes
 * close to the provided $red, $green and $blue colors, with an allowed difference of $fuzziness, the
 * image matches.
 * @param string $file The file to check
 * @param int $boxWidth
 * @param int $boxHeight
 * @param int $minBoxCount
 * @param int $fuzziness
 * @param int $red
 * @param int $green
 * @param int $blue
 * @return boolean
*/
function isImageMatching( $file, $boxWidth, $boxHeight, $minBoxCount, $fuzziness, $red, $green, $blue) {
	$im = imagecreatefromjpeg($file);
	if( !$im ) {
		echo 'Unable to open image file ' . $file . PHP_EOL;
		return false;
	}
	$size = getimagesize($file);
	$width = $size[0];
	$height = $size[1];

	$horizontalBoxCount = ceil($width / $boxWidth);
	$verticalBoxCount = ceil($height/$boxHeight);

	$matchingBoxCount = 0;
	for( $iterV = 1; $iterV <= $verticalBoxCount; $iterV++ ) {
		for( $iterH = 1; $iterH <= $horizontalBoxCount; $iterH++) {
			$top = ($iterV-1) * $boxHeight;
			$left = ($iterH-1) * $boxWidth;
			$averageColors = getAverageColorsForBox($im, $left, $top, $boxWidth, $boxHeight, $width, $height);
			if( areColorsWithInFuzziness( $fuzziness, array($red,$green,$blue), $averageColors) ) {
				$matchingBoxCount++;
			}
		}
	}
	imagedestroy($im);
	return $matchingBoxCount >= $minBoxCount;
}

/**
 * Calculate the average color for a square inside an image.
 * @param resource $im
 * @param int $left
 * @param int $top
 * @param int $width
 * @param int $height
 * @param int $maxWidth
 * @param int $maxHeight
 * @return array With three indexes, 0 for the average amount of red in the pixels read, 1 for the average green and 2 for the average blue.
*/
function getAverageColorsForBox( $im, $left, $top, $width, $height, $maxWidth, $maxHeight ) {
	$redTotal = 0;
	$greenTotal = 0;
	$blueTotal = 0;
	$pixelsRead = 0;

	for( $iterV = $top; $iterV < $top + $height; $iterV++) {
		if( $iterV >= $maxHeight ) {
			break;
		}

		for( $iterH = $left; $iterH < $left + $width; $iterH++ ) {
			if( $iterH >= $maxWidth ) {
				break;
			}

			//from: http://php.net/manual/en/function.imagecolorat.php
			$rgb = imagecolorat($im, $iterH, $iterV);
			$redTotal += ($rgb >> 16) & 0xFF;
			$greenTotal += ($rgb >> 8) & 0xFF;
			$blueTotal += $rgb & 0xFF;
			$pixelsRead++;
		}
	}

	return array(
		floor($redTotal / $pixelsRead),
		floor($greenTotal / $pixelsRead),
		floor($blueTotal / $pixelsRead)
	);
}

/**
 * Checks if a color matches a different color, within a range of fuzziness.
 * @return boolean
*/
function areColorsWithInFuzziness( $fuzziness, $checkColors, $averageColors) {
	foreach( $checkColors as $index => $value ) {
		if( abs($checkColors[$index] - $averageColors[$index]) > $fuzziness ) {
			return false;
		}
	}

	return true;
}