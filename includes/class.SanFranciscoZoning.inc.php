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
	public $section_regex = '/^(?P<catch_line>.*?)$/i';

	public $structure_regex = '/^(?P<name>.*?)$/i';

	/*
	 * Most codes have a Table of Contents as the first LEVEL.
	 */
	public function pre_parse_chapter(&$chapter, &$structures)
	{
		$this->logger->message('Skipping first level.', 2);
		unset($chapter->LEVEL->LEVEL[0]);
	}

	/**
	 * Clean up XML into nice HTML.
	 */
	public function clean_text($xml)
	{

		var_dump($xml);
		$xml = preg_replace('/<LINK[^>]*filename="([0-9\-]+)\.pdf"[^>]*>(.*?)<\/LINK>/sm',
			'$2<br/><img src="/downloads/images/png/$1.png"><br/>
			<a href="/downloads/images/svg/$1.svg">View SVG</a>
			<a href="/downloads/images/pdf/$1.pdf">View PDF</a>', $xml);


		return parent::clean_text($xml);
	}

	public function parse_recurse($levels)
	{
		$this->logger->message('parse_recurse', 1);

		if(is_array($levels))
		{
			foreach($levels as $level) {
				$this->parse_recurse($level);
			}
		}
		else {
			$level = $levels;


			$title = (string) $level->RECORD->HEADING;

			/*
			 * Check to see if we have another layer of nesting
			 */
			if(isset($level->LEVEL))
			{
				/*
				 * If we have two levels deeper, this is a structure.
				 */
				if(count($level->xpath('./LEVEL/LEVEL')))
				{
					$structure = FALSE;

					$this->logger->message('STRUCTURE', 2);

					// If we have a structure heading, add it to the structures.
					if(count($level->xpath($this->structure_heading_xpath))) {
						$structure = $this->parse_structure( $level );

						if($structure) {
							$this->logger->message('Descending : ' . $structure->name, 2);

							$previous_structure = end($this->structures);

							if($previous_structure)
							{
								$structure->parent_id = $previous_structure->id;
							}

							$this->create_structure($structure);

							$this->structures[] = $structure;

						}
					}
					foreach($level->LEVEL as $sublevel)
					{
						// But recurse, either way.
						$this->parse_recurse($sublevel);
					}

					// If we had a structure heading, pop it from the structures.
					if($structure) {
						$this->logger->message('Ascending', 2);

						array_pop($this->structures);
					}
				}
				/*
				 * If we have one level deeper, this is a section.
				 */
				else
				{
					$this->logger->message('SECTION', 2);

					$new_section = $this->parse_section($level, $structures);

					if($new_section)
					{
						$this->sections[] = $new_section;
					}
					else {
						/*
						 * See if maybe we have a structure after all.
						 */
						// TODO
					}
				}
			}
			/*
			 * If we have no children, somehow we've gone too far!
			 */
			else
			{
				$this->logger->message('Empty', 1);
			}
		}

		$this->logger->message('Exit parse_recurse', 1);
	}

	public function parse_section($section)
	{
		var_dump('TEST');
		static $counter;
		if(empty($counter))
		{
			$counter = 1;
		}

		$code = new stdClass();

		$structure = end($this->structures);
		$code->structure_id = $structure->id;

		$section_parts = $this->get_section_parts($section);

		if(!isset($section_parts['catch_line']))
		{
			$this->logger->message('Could not get Section info from title, "' . (string) $section->RECORD->HEADING . '"', 5);
			return FALSE;
		}
		else
		{
			$code->section_number = $counter;
			$code->catch_line = $section_parts['catch_line'];
		}

		$code->section_number = $this->clean_identifier($code->section_number);
		$code->catch_line = $this->clean_identifier($code->catch_line);

		/*
		 * If this is an appendix, use the whole line as the title.
		 */
		if($section_parts['type'] === 'APPENDIX')
		{
			$code->catch_line = $section_parts[0];
		}
		$code->text = '';
		$code->history = '';
		$code->metadata = array(
			'repealed' => 'n',
			'notes' => ''
		);

		$code->order_by = $this->get_section_order_by($code);

		/*
		 * Get the paragraph text from the children RECORDs.
		 */

		$code->section = new stdClass();
		$i = 0;

		var_dump('SECTION-XML', $section->LEVEL->RECORD->asXML());

		foreach($section->LEVEL->RECORD as $record) {
			var_dump('II', $i++);
			$j = 0;
			foreach($record->PARA as $paragraph){
				$j++;
				var_dump('JJ', $j);

				$attributes = $paragraph->attributes();

				$type = '';

				if(isset($attributes['style-name']))
				{
					$type = (string) $attributes['style-name'];
				}

				switch($type)
				{
					case 'History' :
						$code->history .= $this->clean_text($paragraph->asXML());
						break;

					case 'Section-Deleted' :
						$code->catch_line = '[REPEALED]';
						$code->metadata['repealed'] = 'y';
						break;

					case 'EdNote' :
						$code->metadata['notes'] .= $this->clean_text($paragraph->asXML());
						break;

					default :
						$code->section->{$i} = new stdClass();

						$section_text = $this->clean_text($paragraph->asXML());

						$code->text .= $section_text . "\r\r";
						/*
						 * Get the section identifier if it exists.
						 */

						if(preg_match("/^<p>\s*\((?P<letter>[a-zA-Z0-9]{1,3})\) /", $section_text, $paragraph_id))
						{
							$code->section->{$i}->prefix = $paragraph_id['letter'];
							/*
							 * TODO: !IMPORTANT Deal with hierarchy.  This is just a hack.
							 */
							$code->section->{$i}->prefix_hierarchy = array($paragraph_id['letter']);

							/*
							 * Remove the section letter from the section.
							 */
							$section_text = str_replace($paragraph_id[0], '<p>', $section_text);
						}
						// TODO: Clean up tags in the paragraph.

						$code->section->{$i}->text = $section_text;

						$i++;
				}
			}
		}

		if(isset($code->catch_line) && strlen($code->catch_line))
		{
			$this->section_count++;

			$this->logger->message('Section Data: ' . print_r($code, TRUE), 1);

			$counter++;

			return $code;
		}
		else
		{
			$this->logger->message('Invalid section: ' . print_r($code, TRUE), 2);
			return FALSE;
		}
	}

	public function parse_structure($level)
	{
		static $counter;
		if(empty($counter))
		{
			$counter = 1;
		}

		$structure = $this->pre_parse_structure($level);

		if($structure)
		{
			/*
			 * Set the level.
			 */
			$structure->level = count($this->structures) + 1;
			$structure->edition_id = $this->edition_id;

			if(!isset($structure->identifier))
			{
				$this->logger->message('No identifier, so creating one for "'. $structure->name . '"', 3);

				$structure->identifier = $counter;

				if(strlen($structure->identifier) > 16)
				{
					$this->logger->message('Identifier is longer than 16 characters and will be truncated!', 3);
				}

			}

			if(!isset($structure->order_by))
			{
				$structure->order_by = $this->get_structure_order_by($structure);
			}

			/*
			 * Check to see if this structure has text of its own.
			 */
			if($paragraphs = $level->xpath('./LEVEL[@style-name="Normal Level"]/RECORD'))
			{
				foreach($paragraphs as $paragraph)
				{
					$attributes = $paragraph->PARA->attributes();

					$type = '';

					if(isset($attributes['style-name']))
					{
						$type = (string) $attributes['style-name'];
					}

					switch($type)
					{
						case 'History' :
						case 'Section-Deleted' :
							$structure->metadata->history .= $this->clean_text($paragraph->PARA->asXML());
							break;

						case 'EdNote' :
							$structure->metadata->notes .= $this->clean_text($paragraph->PARA->asXML());
							break;

						default :
							$table_children = $paragraph->PARA->xpath('./TABLE|SCROLL_TABLE');

							$para_text = $paragraph->PARA->asXML();

							if(!isset($structure->metadata->text))
							{
								$structure->metadata->text = '';
							}

							// Remove tables of contents.
							if($table_children && count($table_children))
							{
								$this->logger->message('Has tables.', 1);

								foreach($table_children as $child)
								{
									//var_dump(html_entities($para_text), html_entities($child->asXML()));
									$para_text = str_replace($child->asXML(), '', $para_text);
								}
							}

							$structure->metadata->text .= $this->clean_text($para_text);

							break;
					}
				}
			}

		}

		$this->logger->message('Structure Data: ' . print_r($structure, TRUE), 1);

		$structure = $this->post_parse_structure($level, $structure);
		$counter++;

		return $structure;
	}
}
