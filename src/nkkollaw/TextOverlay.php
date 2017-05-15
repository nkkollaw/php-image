<?php
/**
 * php-textoverlay— Text on transparent PNGs with PHP
 *
 * @author Forked from https://github.com/kus/php-image by Blake Kus
 * @copyright kus/php-image copyright © 2015 Blake Kus <blakekus@gmail.com>
 * @license MIT (http://www.opensource.org/licenses/mit-license.php)
 */

namespace nkkollaw;

class TextOverlay {
	/**
	 * Canvas resource
	 *
	 * @var resource
	 */
	protected $img;

	/**
	 * Canvas resource
	 *
	 * @var resource
	 */
	protected $imgCopy;

	/**
	 * PNG Compression level: from 0 (no compression) to 9.
	 *
	 * @var integer
	 */
	protected $compression = 0;

	/**
	 * Global font file
	 *
	 * @var String
	 */
	protected $fontFile;

	/**
	 * Global font size
	 *
	 * @var integer
	 */
	protected $fontSize = 12;

	/**
	 * Global text vertical alignment
	 *
	 * @var String
	 */
	protected $vertAlign = 'top';

	/**
	 * Global text horizontal alignment
	 *
	 * @var String
	 */
	protected $hozAlign = 'left';

	/**
	 * Global font colour
	 *
	 * @var array
	 */
	protected $color = array(255, 255, 255);

	/**
	 * Global text opacity
	 *
	 * @var float
	 */
	protected $textOpacity = 1;

	/**
	 * Global text angle
	 *
	 * @var integer
	 */
	protected $textAngle = 0;

	/**
	 * Global stroke width
	 *
	 * @var integer
	 */
	protected $strokeWidth = 0;

	/**
	 * Global stroke colour
	 *
	 * @var array
	 */
	protected $strokeColor = array(0, 0, 0);

	/**
	 * Canvas width
	 *
	 * @var integer
	 */
	protected $width;

	/**
	 * Canvas height
	 *
	 * @var integer
	 */
	protected $height;

	/**
	 * Default folder mode to be used if folder structure needs to be created
	 *
	 * @var String
	 */
	protected $folderMode = 0755;

	/**
	 * Initialise the image with a file path, or dimensions, or pass no dimensions and
	 * use setDimensionsFromImage to set dimensions from another image.
	 *
	 * @param string|integer $mixed (optional) file or width
	 * @param integer $height (optional)
	 * @return $this
	 */
	public function __construct($width, $height) {
		if (!extension_loaded('gd') && !extension_loaded('gd2')) {
			$this->handleError('GD is not loaded');

			return false;
		}

		if (!is_int($width) || !is_int($height)) {
			throw new \Exception('invalid dimensions');
		}
		if (!$width || !$height) {
			throw new \Exception('dimensions not specified');
		}

		return $this->initialiseCanvas($width, $height);
	}

	/**
	 * Intialise the canvas
	 *
	 * @param integer $width
	 * @param integer $height
	 * @return $this
	 */
	protected function initialiseCanvas($width, $height, $resource='img') {
		$this->width = $width;
		$this->height = $height;

		unset($this->$resource);
		$this->$resource = imagecreatetruecolor($this->width, $this->height);

		imagesavealpha($this->$resource, true); // Set the flag to save full alpha channel information
		imagealphablending($this->$resource, false); // Turn off transparency blending (temporarily)
		imagefilledrectangle($this->$resource, 0, 0, $this->width, $this->height, imagecolorallocatealpha($this->$resource, 0, 0, 0, 127)); // Completely fill the background with transparent color
		imagealphablending($this->$resource, true); // Restore transparency blending

		return $this;
	}

	/**
	 * After we update the image run this function
	 */
	protected function afterUpdate() {
		$this->shadowCopy();
	}

	/**
	 * Store a copy of the image to be used for clone
	 */
	protected function shadowCopy() {
		$this->initialiseCanvas($this->width, $this->height, 'imgCopy');
		imagecopy($this->imgCopy, $this->img, 0, 0, 0, 0, $this->width, $this->height);
	}

	/**
	 * Enable cloning of images in their current state
	 *
	 * $one = clone $image;
	 */
	public function __clone() {
		$this->initialiseCanvas($this->width, $this->height);
		imagecopy($this->img, $this->imgCopy, 0, 0, 0, 0, $this->width, $this->height);
	}

	/**
	 * Handle errors
	 *
	 * @param String $error
	 *
	 * @throws Exception
	 */
	protected function handleError($error) {
		throw new \Exception($error);
	}

	/**
	 * Prints the resulting image and cleans up.
	 */
	public function print() {
		header('Content-type: image/png');
		imagepng($this->img, null, $this->compression);
		$this->cleanup();
	}

	/**
	 * Cleanup
	 */
	public function cleanup() {
		imagedestroy($this->img);
	}

	/**
	 * Save the image
	 *
	 * @param String $path
	 * @param boolean $print
	 * @param boolean $destroy
	 * @return $this
	 */
	public function save($path, $print=false, $destroy=true) {
		if (!is_writable(dirname($path))) {
			if (!mkdir(dirname($path), $this->folderMode, true)) {
				$this->handleError(dirname($path) . ' is not writable and failed to create directory structure!');
			}
		}

		if (!is_writable(dirname($path))) {
			$this->handleError(dirname($path) . ' is not writable!');
		}

		imagepng($this->img, $path, $this->compression);

		if ($print) {
			$this->print();

			return;
		}

		if ($destroy) {
			$this->cleanup();
		} else {
			return $this;
		}
	}

	/**
	 * Save the image and return object to continue operations
	 *
	 * @param string $path
	 * @return $this
	 */
	public function snapshot($path) {
		return $this->save($path, false, false);
	}

	/**
	 * Save the image and prints it
	 *
	 * @param string $path
	 */
	public function printAndSave($path) {
		$this->save($path, true);
	}

	/**
	 * Draw a rectangle
	 *
	 * @param integer $x
	 * @param integer $y
	 * @param integer $width
	 * @param integer $height
	 * @param array $colour
	 * @param float $opacity
	 * @param boolean $outline
	 * @see http://www.php.net/manual/en/function.imagefilledrectangle.php
	 * @return $this
	 */
	public function rectangle($x=0, $y=0, $width=100, $height=50, $colour=array(0, 0, 0), $opacity=1.0, $outline=false) {
		if ($outline === true) {
			imagerectangle($this->img, $x, $y, $x + $width, $y + $height, imagecolorallocatealpha($this->img, $colour[0], $colour[1], $colour[2], (1 - $opacity) * 127));
		} else {
			imagefilledrectangle($this->img, $x, $y, $x + $width, $y + $height, imagecolorallocatealpha($this->img, $colour[0], $colour[1], $colour[2], (1 - $opacity) * 127));
		}
		$this->afterUpdate();
		return $this;
	}

	/**
	 * Draw text
	 *
	 * @param String $text
	 * @param array $options
	 * @see http://www.php.net/manual/en/function.imagettftext.php
	 * @return $this
	 */
	public function text($text, $options=array()) {
		// Unset null values so they inherit defaults
		foreach ($options as $k => $v) {
			if ($options[$k] === null) {
				unset($options[$k]);
			}
		}

		$defaults = array(
			// position
			'x' => 0,
			'y' => 0,
			'hoz_align' => $this->hozAlign,
			'vert_align' => $this->vertAlign,

			// typography
			'font_file' => $this->fontFile,
			'font_size' => $this->fontSize,
			'color' => $this->color,
			'autofit' => true,

			// dimensions
			'width' => $this->width,
			'height' => $this->height,

			// appearance
			'stroke_width' => $this->strokeWidth,
			'stroke_color' => $this->strokeColor,

			// appearance (other)
			'opacity' => $this->textOpacity,
			'angle' => $this->textAngle,

			// etc.
			'debug' => false
		);

		extract(array_merge($defaults, $options), EXTR_OVERWRITE);

		if ($font_file === null) {
			$this->handleError('No font file set!');
		}

		if (is_int($this->width) && $autofit) {
			$font_size = $this->fitToWidth($font_size, $angle, $font_file, $text, $this->width);
		}

		// Get Y offset as it 0 Y is the lower-left corner of the character
		$testbox = imagettfbbox($font_size, $angle, $font_file, $text);
		$offsety = abs($testbox[7]);
		$offsetx = 0;
		$actualWidth = abs($testbox[6] - $testbox[4]);
		$actualHeight = abs($testbox[1] - $testbox[7]);

		// If text box align text
		if (is_int($this->width) || is_int($height)) {
			if (!is_int($this->width)) {
				$this->width = $actualWidth;
			}
			if (!is_int($height)) {
				$height = $actualHeight;
			}

			if ($debug) {
				$this->rectangle($x, $y, $this->width, $height, array(0, 255, 255), 0.5);
			}

			switch ($hoz_align) {
				case 'center':
					$offsetx += (($this->width - $actualWidth) / 2);
					break;
				case 'right':
					$offsetx += ($this->width - $actualWidth);
					break;
			}

			switch ($vert_align) {
				case 'middle':
				case 'center':
					$offsety += (($height - $actualHeight) / 2);
					break;
				case 'bottom':
					$offsety += ($height - $actualHeight);
					break;
			}
		}

		// Draw stroke
		if ($stroke_width > 0) {
			$stroke_color = imagecolorallocatealpha($this->img, $stroke_color[0], $stroke_color[1], $stroke_color[2], (1 - $opacity) * 127);
			for ($sx = ($x-abs($stroke_width)); $sx <= ($x+abs($stroke_width)); $sx++) {
				for ($sy = ($y-abs($stroke_width)); $sy <= ($y+abs($stroke_width)); $sy++) {
					imagettftext($this->img, $font_size, $angle, $sx + $offsetx, $sy + $offsety, $stroke_color, $font_file, $text);
				}
			}
		}

		// Draw text
		imagettftext($this->img, $font_size, $angle, $x + $offsetx, $y + $offsety, imagecolorallocatealpha($this->img, $color[0], $color[1], $color[2], (1 - $opacity) * 127), $font_file, $text);
		$this->afterUpdate();

		return $this;
	}

	/**
	 * Reduce font size to fit to width
	 *
	 * @param integer $font_size
	 * @param integer $angle
	 * @param String $font_file
	 * @param String $text
	 * @param integer $width
	 * @return integer
	 */
	protected function fitToWidth($font_size, $angle, $font_file, $text, $width) {
		while ($font_size > 0) {
			$testbox = imagettfbbox($font_size, $angle, $font_file, $text);
			$actualWidth = abs($testbox[6] - $testbox[4]);
			if ($actualWidth <= $width) {
				return $font_size;
			} else {
				$font_size--;
			}
		}

		return $font_size;
	}

	/**
	 * Reduce font size to fit to width and height
	 *
	 * @param integer $font_size
	 * @param integer $angle
	 * @param String $font_file
	 * @param String $text
	 * @param integer $width
	 * @param integer $height
	 * @return integer
	 */
	protected function fitToBounds($font_size, $angle, $font_file, $text, $width, $height) {
		while ($font_size > 0) {
			$wrapped = $this->wrap($text, $width, $font_size, $angle, $font_file);
			$testbox = imagettfbbox($font_size, $angle, $font_file, $wrapped);
			$actualHeight = abs($testbox[1] - $testbox[7]);
			if ($actualHeight <= $height) {
				return $font_size;
			} else {
				$font_size--;
			}
		}

		return $font_size;
	}

	/**
	 * Draw multi-line text box and auto wrap text
	 *
	 * @param String $text
	 * @param array $options
	 * @return $this
	 */
	public function textBox($text, $options=array()) {
		$defaults = array(
			'font_size' => $this->fontSize,
			'color' => $this->color,
			'opacity' => $this->textOpacity,
			'x' => 0,
			'y' => 0,
			'width' => 100,
			'height' => null,
			'angle' => $this->textAngle,
			'stroke_width' => $this->strokeWidth,
			'stroke_color' => $this->strokeColor,
			'font_file' => $this->fontFile
		);
		extract(array_merge($defaults, $options), EXTR_OVERWRITE);
		if ($height) {
			$font_size = $this->fitTobounds($font_size, $angle, $font_file, $text, $width, $height);
		}
		return $this->text($this->wrap($text, $width, $font_size, $angle, $font_file), array('font_size' => $font_size, 'x' => $x, 'y' => $y, 'angle' => $angle, 'stroke_width' => $stroke_width, 'opacity' => $opacity, 'color' => $color, 'stroke_color' => $stroke_color, 'font_file' => $font_file));
	}

	/**
	 * Helper to wrap text
	 *
	 * @param String $text
	 * @param integer $width
	 * @param integer $font_size
	 * @param integer $angle
	 * @param String $font_file
	 * @return String
	 */
	protected function wrap($text, $width=100, $font_size=12, $angle=0, $font_file=null) {
		if ($font_file === null) {
			$font_file = $this->fontFile;
		}
		$ret = "";
		$arr = explode(' ', $text);
		foreach ($arr as $word) {
			$teststring = $ret . ' ' . $word;
			$testbox = imagettfbbox($font_size, $angle, $font_file, $teststring);
			if ($testbox[2] > $width) {
				$ret .= ($ret == "" ? "" : "\n") . $word;
			} else {
				$ret .= ($ret == "" ? "" : ' ') . $word;
			}
		}

		return $ret;
	}
}