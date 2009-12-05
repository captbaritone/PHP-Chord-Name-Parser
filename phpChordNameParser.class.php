<?php
// TODO
// 		Generate chord name from intervals above root
//		What if more than one token combine to reduce an included tone to <= 0 ?
class chord {
	public $root;
	public $bass;
	public $intervalsAboveRoot;
	public $chordTones;
	public $errors;
	public $log;
	public $warnings;
	private $tokens;
	
	public function __construct(){
		// Define array of tokens that can be found in chord names
		// The token's array ($interval => $weight)
		// $weight adjusts how important that interval is to the chord (0: not included -- 1: required)
		$this->tokens = array(
			"maj7"	=> array(7 => -.25, 11 => 1),				
			"maj9"	=> array(0 => -.5, 7 => -.25, 11 => 1, 2 => 1),
			"M9"	=> array(0 => -.5, 7 => -.25, 11 => 1, 2 => 1),
			"Δ9"	=> array(0 => -.5, 7 => -.25, 11 => 1, 2 => 1),
			"maj11"	=> array(0 => -.5, 7 => -.25, 11 => 1, 2 => 1, 5 => 1),
			"M11"	=> array(0 => -.5, 7 => -.25, 11 => 1, 2 => 1, 5 => 1),
			"Δ11"	=> array(0 => -.5, 7 => -.25, 11 => 1, 2 => 1, 5 => 1),
			"maj13"	=> array(0 => -.5, 7 => -.25, 11 => 1, 2 => 1, 5 => 1, 9 => 1),
			"M13"	=> array(0 => -.5, 7 => -.25, 11 => 1, 2 => 1, 5 => 1, 9 => 1),
			"Δ13"	=> array(0 => -.5, 7 => -.25, 11 => 1, 2 => 1, 5 => 1, 9 => 1),
			"add2"	=> array(2 => 1),
			"add9"	=> array(2 => 1),
			"add11"	=> array(5 => 1),
			"add13"	=> array(9 => 1),
			"m7b5"	=> array(4 => -1, 7 => 1, 3 => 1, 6 => 1, 10 => 1),
			"minor"	=> array(4 => -1, 3 => 1),
			"mi"	=> array(4 => -1, 3 => 1),
			"min"	=> array(4 => -1, 3 => 1),
			"major" => array(),
			"m"		=> array(4 => -1, 3 => 1),
			"^6"	=> array(9 => 1),
			"6/9"	=> array(0 => -.5,9 => 1, 2 => 1),
			"6-9"	=> array(0 => -.5,9 => 1, 2 => 1),
			"69"	=> array(0 => -.5,9 => 1, 2 => 1),
			"M7"	=> array(11 => 1),
			"5"		=> array(4 => -1),
			"ind"	=> array(4 => -1),
			"6"		=> array(9 => 1),
			"7"		=> array(10 => 1),
			"9"		=> array(0 => -.5, 10 => 1, 2 => 1),
			"Δ7"	=> array(11 => 1),
			"Δ"		=> array(11 => 1),
			"11"	=> array(0 => -.5, 7 => -.25, 10 => 1, 2 => 1, 5 => 1),
			"13"	=> array(0 => -.5, 7 => -.25, 10 => 1, 2 => 1, 5 => 1, 9 => 1),
			"-9"	=> array(0 => -.5, 7 => -.25, 10 => 1, 1 => 1),
			"b9"	=> array(0 => -.5, 7 => -.25, 10 => 1, 1 => 1),
			"#9"	=> array(0 => -.5, 7 => -.25, 10 => 1, 3 => 1),
			"+5"	=> array(7 => -1, 8 => 1),
			"+"		=> array(7 => -1, 8 => 1),
			"#5"	=> array(7 => -1, 8 => 1),
			"aug5"	=> array(7 => -1, 8 => 1),
			"aug"	=> array(7 => -1, 8 => 1),
			"dim5"	=> array(4 => -1, 7 => -1, 3 => 1, 6 => 1),
			"dim7"	=> array(4 => -1, 7 => -1, 3 => 1, 6 => 1, 9 => 1),
			"dim"	=> array(4 => -1, 7 => -1, 3 => 1, 6 => 1, 9 => 1),
			"-5"	=> array(7 => -1, 6 => 1),
			"b5"	=> array(7 => -1, 6 => 1),
			"°"		=> array(4 => -1, 7 => -1, 3 => 1, 6 => 1, 9 => 1),
			"ø"		=> array(4 => -1, 7 => -1, 3 => 1, 6 => 1, 10 => 1),
			"sus4"	=> array(4 => -1, 5 => 1),
			"sus2"	=> array(4 => -1, 2 => 1),
			"sus9"	=> array(0 => -1, 2 => 1),
			"sus"	=> array(0 => -1, 2 => 1),
			"-"		=> array(4 => -1, 3 => 1),
			// Some characters which we can safely ignore
			/* 		Some of these are used as separators which is fine, because if we find them, 
					they successfully isolated the two tokens they were trying to separate. */
			" "		=> array(),
			"("		=> array(),
			")"		=> array(),
			"["		=> array(),
			"]"		=> array(),
			"/"		=> array(),
			"\\"	=> array(),
			"|"		=> array()
		);
		$this->noteLookup = array(
			"c"  => 0,
			"c#" => 1,
			"db" => 1,
			"d"  => 2,
			"d#" => 3,
			"eb" => 3,
			"e"  => 4,
			"fb" => 4,
			"e#" => 5,
			"f"  => 5,
			"f#" => 6,
			"gb" => 6,
			"g"  => 7,
			"g#" => 8,
			"ab" => 8,
			"a"  => 9,
			"a#" => 10,
			"bb" => 10,
			"b"  => 11,
			"cb" => 11,
			"b#" => 12
		);
	}
	
	
	public function fillFromString($string){
		// Star the timer to see how long it takes
		$start = (float) array_sum(explode(' ',microtime())); 
		
		$this->log("Trying to parse: '$string'");
		// If the string starts with a root name
		if(preg_match("/^[a-gA-G][b#]?/", $string, $rootArr)){
			// Record root
			$this->root = $this->tone(strtolower($rootArr[0]));
			// Trim root off of the beginging of string
			$string = substr($string, strlen($this->root));
			$this->log("Root is: '{$rootArr[0]}'");
		}else{
			$this->errors[] = "Unable to determine root";
			return false;
		}
	
		// Assume a major chord based on the root (0,4,7)
		$this->intervalsAboveRoot = array(0 => 1, 4 => 1, 7 => 1);
		
		// If the last token is a slash 
		if(preg_match("=/[a-gA-G][b#]?$=", $string, $slashArr)){
			// Record the new bass tone (trimming the slash)
			$this->bass = $this->tone(strtolower(substr($slashArr[0], 1)));
			// Trim slash off of the end of string
			$string = substr($string, 0, strlen($string) - strlen($slashArr[0]));
			$this->log("Slash bass is: '" . substr($slashArr[0], 1) . "'");
		}
		
		// Until there is no string left
		while(strlen($string) > 0){
			$foundToken = false;
			foreach($this->tokens as $token => $intervals){
				//if the start of $string matches a token 
				if(strpos($string, strval($token)) === 0){ // strval is needed to prevent the token from being interpreted as an integer (ie '-5')
					$this->log("Found token '$token'");
					$foundToken = true;
					// Adjust the scoring of the intervals
					foreach($intervals as $interval => $weight){
						$this->intervalsAboveRoot[$interval] += $weight;
						// If the weight for the interval is 0 or less, remove the interval
						if($this->intervalsAboveRoot[$interval] <= 0){
							unset($this->intervalsAboveRoot[$interval]);
						}
					}
					// Trim that token from string
					$string = substr($string, strlen($token));
					// Start at the top of the token list again (because the order is important)
					break; 
				}
			}
			if(!$foundToken){
				$errors[] = "Unable to parse chord";
				return false;
			}
		}
			
		$this->fillChordTones();
		return true;
	}
	
	public function getNotes(){
		foreach($this->intervalsAboveRoot as $interval => $weight){
			$notes[] = ($this->root + $interval) % 12;
		}
		array(sort($notes));
		$return = implode(", ", $notes);
		if(isset($this->bass)){
			$return .= " /{$this->bass}";
		}
		return $return;
	}
	
	private function fillChordTones(){
		// Calculate the actual chord tones from the intervals above the root
		foreach($this->intervalsAboveRoot as $interval => $weight){
			$tone = ($this->root + $interval) % 12;
			$this->chordTones[] = $tone;
		}
		// If there is a bass tone, add it to the chord tones
		if(isset($this->bass)){
			$this->chordTones[] = $this->root;
		}
	}
	
	// Returns an array of all the chord tones and their weights in the form $tone => $weight
	public function getChordToneWeights(){
		// Calculate the actual chord tones from the intervals above the root
		foreach($this->intervalsAboveRoot as $interval => $weight){
			$tone = ($this->root + $interval) % 12;
			$chordToneWeights[$tone] = $weight;
		}
		// If there is a bass tone, add it to the chord tones
		if(isset($this->bass)){
			$chordToneWeights[$this->root] = 1;
		}
		return $chordToneWeights;
	}
	private function tone($note){
		return $this->noteLookup[$note];
	}
	
	private function log($log){
		$this->log[] = $log;
	}
}
?>