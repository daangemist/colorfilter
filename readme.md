Colorfilter
================

Checks if the image denoted by $file is matching the defined color. It does this by dividing the
image into small squares. For each square the average color is calculated. If the average color comes
close to the provided $red, $green and $blue colors, with an allowed difference of $fuzziness, the
image matches.

When an image matches, it is added with an <img> tag to the output file.

Usage
-----------------

Place the `filter.php` file inside a folder with _jpeg_ images. Configure the red, green and blue constants
to match the color you want to filter on. Adjust the fuzziness to allow the variation in the color. A larger
value means more variation.

A little tweaking might be required of the box size, fuzziness and the color to get the desired result.
