<?php
define('BOX_WIDTH', 12);
define('BOX_HEIGHT', 16);
define('MIN_BOX_COUNT', 3);
define('COLOR_RED', 65);
define('COLOR_GREEN', 46);
define('COLOR_BLUE', 40);
define('FUZZINESS', 6);

$files = glob('*.jpg');
$output = date('YmdHis') . '.html';
$fp = fopen($output, 'w');

$matches = 0;
foreach( $files as $file ) {

	if( isImageMatching( $file, BOX_WIDTH, BOX_HEIGHT, MIN_BOX_COUNT, FUZZINESS, COLOR_RED, COLOR_GREEN, COLOR_BLUE) ) {
		logHtml('<img src="' . $file . '" />');
		$matches++;
	}
}

fclose($fp);

echo $matches . ' out of ' . count($files) . ' were approved.' . PHP_EOL;
die;

function logHtml( $html ) {
	global $fp;
	fputs($fp, $html . PHP_EOL);
}

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

function areColorsWithInFuzziness( $fuzziness, $checkColors, $averageColors) {
	foreach( $checkColors as $index => $value ) {
		if( abs($checkColors[$index] - $averageColors[$index]) > $fuzziness ) {
			return false;
		}
	}

	return true;
}