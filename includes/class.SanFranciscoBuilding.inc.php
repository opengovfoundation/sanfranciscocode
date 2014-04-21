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
	public function pre_parse_chapter(&$chapter, &$structures)
	{
		/*
		 * Get the part of the building code from the title.
		 */
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

	}
}
