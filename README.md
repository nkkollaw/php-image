# PHP TextOverlay

## What does it do?

Create a transparent PNG with text on it.

## Installation

Place the PHP file on your server and include it in your script or download via [composer](https://getcomposer.org/) `require: "nkkollaw/textoverlay": "dev-master"`.

# Usage

## Example

```php
$myOverlay = new \nkkollaw\TextOverlay(200, 50);

$myOverlay->text($text, array(
	// position
	'x' => 0,
	'y' => 0,
	'hoz_align' => 'left', // left|center|right
	'vert_align' => 'top', // top|middle|bottom
	
	// typography
	'font_file' => '/path/to/font.ttf',
	'font_size' => 20,
	'color' => array(0, 0, 0), // color in RGB format
	'autofit' => true, // shrink text to fit if wider than box
	
	// appearance
	'stroke_width' => 3,
	'stroke_color' => array(50, 50, 50),  // color in RGB format
	
	// appearance (other)
	'opacity' => 1,
	'angle' => 0,
	
	// etc.
	'debug' => false
));

// output image
$myOverlay->print();

// save image
$myOverlay->save('/path/to/image.png', false);

// save + output
$myOverlay->save('/path/to/image.png', true);
```

## Copyright

Copyright (c) 2015 Blake Kus [blakek.us](http://blakek.us)

This plugin is dual licenced under MIT and GPL Version 2 licences.

Permission is hereby granted, free of charge, to any person obtaining a copy of
this software and associated documentation files (the "Software"), to deal in
the Software without restriction, including without limitation the rights to
use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies
of the Software, and to permit persons to whom the Software is furnished to do
so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.