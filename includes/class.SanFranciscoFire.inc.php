<?php

/**
 * San Francisco parser for State Decoded.
 * Extends AmericanLegal base classes.
 *
 * PHP version 5
 *
 * @license		http://www.gnu.org/licenses/gpl.html GPL 3
 * @version		0.8
 * @link		http://www.statedecoded.com/
 * @since		0.3
*/

/**
 * This class may be populated with custom functions.
 */

require 'class.AmericanLegal.inc.php';

class State extends AmericanLegalState {}

class Parser extends AmericanLegalParser
{
	//     Patterns:                         | section number                          |   | hyphen | catch line
	public $section_regex = '/^\[?(?:SECTION )?(?P<number>[0-9A-Z]+[0-9A-Za-z_\.\-]*\.?)\s*(?:–\s*)?(?P<catch_line>.*?)\.?\]?$/i';

	public $structure_regex = '/^(?P<type>CHAPTER|DIVISION|PART|SECTION)\s+(?P<number>[A-Za-z0-9\.]+)(?:\s*–)?\s*(?P<name>.*?)$/i';

}
