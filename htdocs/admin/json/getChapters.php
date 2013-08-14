<?php
	$txt = file_get_contents('AdminCode.TXT');
	$ret = preg_match_all('@CHAPTER\s([0-9A-Za-z]+):\s*\n(.*)\n@', $txt, $matches);
	
	$chapters = array();
	foreach($matches[1] as $index => $value){
		$chapters[$value] = $matches[2][$index];
	}
	
	foreach(glob('*.json') as $filename){
		$raw = file_get_contents($filename);
		$json = json_decode($raw);
		$parent = $json->heading->title;
		$json->chapter = array(
			'identifier'	=> $parent,
			'text'			=> $chapters[$parent]
		);
		
		file_put_contents($filename, json_encode($json));
	}
	
	




