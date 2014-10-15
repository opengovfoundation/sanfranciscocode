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
	public $section_regex = '/^(?:\(CRC\) )?\[?(?:(SECTION|SEC.) )?(?P<number>(APPENDIX )?[0-9A-Z]+[0-9A-Za-z_\.\-]*\.?)\s*(?:–\s*)?(?P<catch_line>.*?)\.?\]?$/i';

	public $structure_regex = '/^(?:\(CBC\) )?(?P<type>APPENDIX|CHAPTER|DIVISION|PART|SECTION)?\s*(?P<number>[A-Za-z0-9\.]{1,3})(?:\s+–)?\s+(?P<name>.*?)$/i';

	public function pre_parse_chapter(&$chapter)
	{
		$title = trim($chapter->REFERENCE->TITLE[0]);

		// If this is the top-level index, just skip it for now.
		if($title == 'SAN FRANCISCO BUILDING INSPECTION COMMISSION CODES')
		{
			unset($chapter->LEVEL);
		}

		// If there's more than one title,this has a table of contents.
		if(count($chapter->REFERENCE->TITLE) > 1)
		{
			if(count($chapter->LEVEL->LEVEL) > 1)
			{
				$this->logger->message('Skipping first level.', 2);
				unset($chapter->LEVEL->LEVEL[0]);
			}
		}

		if(count($chapter->LEVEL->LEVEL))
		{
			/*
			 * Get the part of the building code from the title.
			 */
			$this->logger->message('Generating building code sections.', 2);

			if(preg_match('/^(([A-Z ]+) CODE) ?(.*)$/', $title, $matches))
			{
				$this->logger->message('BUILDING: ' . $matches[1], 1);

				$structure = new stdClass();
				$structure->name = ucwords(strtolower($matches[1]));
				$structure->label = 'Code';
				$structure->identifier = strtolower($matches[2]);
				$structure->order_by = $structure->identifier;

				$structure->level = count($this->structures) + 1;

				if(isset($matches[3]) && strlen(trim($matches[3])))
				{
					$structure->metadata = new stdClass();
					$structure->metadata->text = trim($matches[3]);
				}

				$this->create_structure($structure);

				$this->structures[] = $structure;
			}
		}
		else {
			print 'No content!';
		}

	}

	public function pre_parse_structure($level)
	{
		$structure_name = $this->clean_title(trim((string) $level->RECORD->HEADING));

		if($structure_name === 'ADMINISTRATIVE BULLETINS')
		{
			$this->logger->message('Handling Administrative Bulletins', 2);

			$structure = new stdClass();
			$structure->name = ucwords(strtolower($structure_name));
			$structure->identifier = 'bulletins';
			$structure->label = 'structure';
			$structure->order_by = '9999';
		}
		else
		{
			$structure = parent::pre_parse_structure($level);

			if($structure->label == 'Appendix')
			{
				$this->logger->message('Overriding Appendix', 2);

				$structure->identifier = 'Appendix ' . $structure->identifier;
			}
		}

		return $structure;
	}



	public function get_section_parts($section)
	{
		/*
		 * Parse the catch line and section number.
		 */
		$section_title = trim((string) $section->RECORD->HEADING);

		$this->logger->message('Title: ' . $section_title, 1);

		if (in_array($section_title, array(
			"PUBLISHER'S NOTE",
			"PUPLISHER'S NOTE" // [sic]
		)))
		{
			$section_parts = array(
				'number' => "Publishers Note",
				'catch_line' => "Publisher's Note"
			);
		}
		else
		{
			preg_match($this->section_regex, $section_title, $section_parts);
		}

		return $section_parts;
	}
}
