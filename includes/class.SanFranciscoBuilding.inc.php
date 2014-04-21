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
	public $section_regex = '/^\[?(?:SECTION )?(?P<number>[0-9A-Z]+[0-9A-Za-z_\.\-]*\.?)\s*(?:–\s*)?(?P<catch_line>.*?)\.?\]?$/i';

	public $structure_regex = '/^(?P<type>CHAPTER|DIVISION|PART|SECTION)?\s*(?P<number>[A-Za-z0-9\.]+)(?:\s*–)?\s*(?P<name>.*?)$/i';

	public function pre_parse_chapter(&$chapter, &$structures)
	{
		/*
		 * Get the part of the building code from the title.
		 */
		$this->logger->message('Generating building code sections.', 2);
		if(preg_match('/^(([A-Z ]+) CODE) ?(.*)$/', $chapter->REFERENCE->TITLE[0], $matches))
		{
			$this->logger->message('BUILDING: ' . $matches[1], 1);

			$structure = new stdClass();
			$structure->name = ucwords(strtolower($matches[1]));
			$structure->label = 'Code';
			$structure->identifier = strtolower($matches[2]);
			$structure->order_by = $structure->identifier;

			$this->structure_depth++;
			$structure->level = $this->structure_depth;

			if(isset($matches[3]) && strlen(trim($matches[3])))
			{
				$structure->metadata = new stdClass();
				$structure->metadata->text = trim($matches[3]);
			}

			$structures[] = $structure;
		}

		$this->logger->message('Skipping first level.', 2);
		unset($chapter->LEVEL->LEVEL[0]);

	}
}
