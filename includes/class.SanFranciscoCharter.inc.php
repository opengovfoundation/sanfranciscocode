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
	/*
	 * Regexes.
	 */
	//                            | type of section                 |!temp!|    | section number                    (opt ' - section number')       |      | hyphen | catch line
	public $section_regex = '/^\[?((?P<type>SEC(TION|S\.|\.)|APPENDIX|ARTICLE)\s+)?(?P<number>[0-9A-Z]+[0-9A-Za-z_\.\-]*(.?\s-\s[0-9]+[0-9A-Za-z\.\-]*)?)\.?\s*(?:-\s*)?(?P<catch_line>.*?)\.?\]?$/i';
}
