<?php
/**
 * Typogrify plugin for Craft CMS 3.x
 *
 * Typogrify prettifies your web typography by preventing ugly quotes and 'widows' and more
 *
 * @link      https://nystudio107.com/
 * @copyright Copyright (c) 2017 nystudio107
 */

use \PHP_Typography\Settings\Dash_Style;
use \PHP_Typography\Settings\Quote_Style;

/**
 * Typogrify config.php
 *
 * This file exists only as a template for the Typogrify settings.
 * It does nothing on its own.
 *
 * Don't edit this file, instead copy it to 'craft/config' as 'typogrify.php'
 * and make your changes there to override default settings.
 *
 * Once copied to 'craft/config', this file will be multi-environment aware as
 * well, so you can have different settings groups for each environment, just as
 * you do for 'general.php'
 */

return [
	"set_hyphenation" => false,
	
	"set_max_dewidow_pull" => 1000,

    // establishes maximum length of a widows that will be protected
    "set_max_dewidow_length" => 1000,

    // establishes the maximum number of words considered for dewidowing.
    "set_dewidow_word_number" => 1000,

    // establishes maximum length of pulled text to keep widows company
    "set_max_dewidow_pull" => 1000
];
