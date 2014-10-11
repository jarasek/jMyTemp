<?php

class Children implements Iterator
{
	private $position = 0;
	private $node = null;

	public function __construct(&$node) {
		$this->position = 0;
		$this->node = &$node;
	}

	function rewind() {
		$this->position = 0;
	}

	function current() {
		return new Element($this->node["children"][$this->position]);
	}

	function key() {
		return $this->position;
	}

	function next() {
		++$this->position;
	}

	function valid() {
		if(!isset($this->node)) {
			return FALSE;
		}
		if(!isset($this->node["children"])) {
			return FALSE;
		}
		return isset($this->node["children"][$this->position]);
	}
}

class Element
{
	private $node;
	
	function __construct(&$node = null) {
		if(isset($node)) {
			$this->node = &$node;
		}
		else {
			$this->node = array("tag" => "root", "children" => array());
		}
	}
	
	function getInnerHtml() {
		return $this->innerHtml($this->node);
	}
	
	function setInnerHtml($text) {
		$this->node["children"] = array();
		$index = 0;
		$this->parse($this->node, $text, $index);
	}
	
	function setInnerText($text) {
		$this->node["children"] = array();
		$this->node["children"][] = array("text" => $text);
	}
	
	function getChildren() {
		return new Children($this->node);
	}
	
	function getChild($tag) {
		if(!isset($this->node["children"])) {
			return NULL;
		}
		foreach($this->node["children"] as &$child) {
			if(isset($child["tag"])) {
				if($child["tag"] == $tag) {
					return new Element($child);
				}
			}
		}
		return NULL;
	}
	
	function addChild() {
		if(!isset($this->node["children"])) {
			$this->node["children"] = array();
		}
		$i = array_push($this->node["children"], array("tag" => "", "children" => array()));
		return new Element($this->node["children"][$i -1]);
	}
	
	function getTag() {
		if(!isset($this->node["tag"])) {
			return "";
		}
		return $this->node["tag"];
	}

	function setTag($name) {
		$this->node["tag"] = $name;
	}
	
	function setEnd() {
		$this->node["end"] = true;
	}
	
	function getAttribute($name) {
		if(!isset($this->node["attr"])) {
			return "";
		}
		if(!isset($this->node["attr"][$name])) {
			return "";
		}
		return $this->node["attr"][$name];
	}
	
	function setAttribute($name, $value) {
		if($value == "") {
			$this->removeAttribute($name);
		}	
		else {
			$this->node["attr"][$name] = $value;
		}
	}
	
	function removeAttribute($name) {
		unset($this->node["attr"][$name]);
		if(isset($this->node["attr"])) {
			if(count($this->node["attr"]) == 0) {
				unset($this->node["attr"]);
			}
		}
	}
			  
	function getStyle($name) {
		$style = $this->getAttribute("style");
		if($style != "") {
			$lines = explode(";", $style);
			foreach($lines as $line) {
				$attr = explode(":", $line);
				if(trim($attr[0]) == $name) {
					return trim($attr[1]);
				}
			}		
		}
		else {
			return "";
		}
	}
	
	function setStyle($name, $value) {
		if($value == "") {
			$this->removeStyle($name);
			return;
		}
		if(!isset($this->node["attr"])) {
			$this->node["attr"] = array("style" => $name.": ".$value.";");
			return;
		}
		if(!isset($this->node["attr"]["style"])) {
			$this->node["attr"]["style"] = $name.": ".$value.";";
			return;
		}		
		$style = trim($this->node["attr"]["style"]);
		if($this->getStyle($name) == "") {
			if(substr($style, strlen($style)-1, 1) != ";") {
				$style .= ";";
			}
			$this->node["attr"]["style"] = $style." ".$name.": ".$value.";";
		}
		else {
			$lines = explode(";", $style);
			$newstyle = "";
			foreach($lines as $line) {
				$attr = explode(":", $line);
				if(trim($attr[0]) == $name) {
					$attr[1] = $value;
				}
				if(trim($attr[0]) != "") {
					$newstyle .= " ".trim($attr[0]).": ".trim($attr[1]).";";
				}
			}
			$this->node["attr"]["style"] = trim($newstyle);
		}		
	}
	
	function removeStyle($name) {
		$style = $this->getStyle($name);
		if($style == "") {
			return;
		}
		else {
			$lines = explode(";", $style);
			$newstyle = "";
			foreach($lines as $line) {
				$attr = explode(":", $line);
				if(trim($attr[0]) != $name && trim($attr[0]) != "") {
					$newstyle = $newstyle.trim($attr[0]).": ".trim($attr[1]).";";
				}
			}
			if($newstyle != "") {
				$this->node["attr"]["style"] = $newstyle;
			}
			else {
				unset($this->node["attr"]["style"]);
				if(count($this->node["attr"]) == 0) {
					unset($this->node["attr"]);
				}
			}
		}	
	}
	
	function printElement() {
		$this->printNode($this->node, "");
	}
	
	//private functions
	
	private function printNode(&$node, $tab) {	
		foreach($node["children"] as &$child) {
			echo $tab;
			if(isset($child["tag"])) {
				echo $child["tag"];
			}
			if(isset($child["error"])) {
				echo " ".$child["error"];
			}
			if(isset($child["text"])) {
				echo " ".htmlspecialchars($child["text"]);
			}
			if(isset($child["attr"])) {
				$attrs = array_keys($child["attr"]);
				foreach($attrs as $attr) {
					echo " ".$attr.'="'.$child["attr"][$attr].'"';
				}
			}		
			if(isset($child["end"])) {
				if($child["end"]) {
					echo " /&gt;";
				}
			}
			echo "<br>\n";		
			if(isset($child["children"])) {
				$this->printNode($child, $tab."&nbsp;&nbsp;&nbsp;");
			}
		}
	}
	
	private function innerHtml(&$node) {
		$text = "";
		if(!isset($node)) {
			return "";
		}
		if(!isset($node["children"])) {
			return "";
		}
		foreach ($node["children"] as $child) {
			$end = TRUE;
			if(!isset($child["tag"])) {
				if(isset($child["text"])) {
					$text .= $child["text"]."\n";
					$end = FALSE;
				}
			}
			else {
				if($child["tag"] == "!--") {
					$text .= "<".$child["tag"]." ".$child["text"]." -->\n";
					$end = FALSE;
				}
				elseif($child["tag"] == "!DOCTYPE") {
					$text .= "<".$child["tag"]." ".$child["text"]." >\n";
					$end = FALSE;
				}
				else {
					$text .= "<".$child["tag"];
					if(isset($child["attr"])) {
						foreach(array_keys($child["attr"]) as $key) {
							$text .= " ".$key.'="'.$child["attr"][$key].'"';
						}
					}
					if(isset($child["end"])) {
						if($child["end"] == TRUE) {
							if(!isset($child["children"]) || count($child["children"]) == 0) {
								$text .= " />\n";
								$end = FALSE;
							}
						}
					}
					if($end) {
						$text .= ">\n";
					}
				}
			}
			if(isset($child["children"])) {
				$text .= $this->innerHtml($child);
			}
			if($end) {
				$text .= "</".$child["tag"].">\n";
			}
		}
		return $text;
	}
	
	private function parse(&$node, &$text, &$index) {
		$begin = $index;
		while($index < strlen($text)) {
			$pos = strpos($text, "<", $index);
			if($pos === false) {
				//no start tag
				$s = substr($text, $begin);
				$s = trim($s);
				if($s != "") {
					array_push($node["children"], array("text" => $s)); //save text to end of text
				}
				$index = strlen($text);
				return;
			}
			else {
				//start tag exists
				if($pos > $index) {
					//save text before tag
					$s = substr($text, $begin, $pos - $begin);
					$s = trim($s);
					if($s != "") {
						array_push($node["children"], array("text" => $s)); //save text before <
					}
					$begin = $pos;
				}
				$index = $pos;
				if($text[$index +1] == "/") {
					//end tag
					$pos = strpos($text, ">", $index);
					if($pos === false) {
					   //no closing of end tag
						$s = substr($text, $begin);
						array_push($node["children"], array("error" => 1, "text" => $s)); //error
						$index = strlen($text);
						return;
					}
					else {
						//end tag closed
						$s = substr($text, $index+2, $pos - $index -2);
						$index = $pos +1;
						if($s == $node["tag"]) {
							//start and end tag the same
							return;
						}
						//start and end tag different - ignored
					}
				}
				else {
					//comment
					if(substr($text, $index, 4) == "<!--") {
						$kpos = strpos($text, "-->", $index +4);
						//end of comment exists
						if($kpos !== false) {
							$s = trim(substr($text, $index +4, $kpos - $index -4));
							$com = array("tag" => "!--", "text" => $s);
							array_push($node["children"], $com); //save comment
							$index = $kpos + 3;
							$begin = $index;
							continue;
						}
						//no end of comment
						else {
							$s = trim(substr($text, $index +4));
							$com = array("tag" => "!--", "text" => $s, "error" => 2);
							array_push($node["children"], $com); //save comment to end of file
							$index = strlen($text);
							return;
						}
					}
					//doctype
					if(substr($text, $index, 9) == "<!DOCTYPE") {
						$kpos = strpos($text, ">", $index +9);
						//end of comment exists
						if($kpos !== false) {
							$s = trim(substr($text, $index +9, $kpos - $index -9));
							$com = array("tag" => "!DOCTYPE", "text" => $s);
							array_push($node["children"], $com); //save doctype
							$index = $kpos + 1;
							$begin = $index;
							continue;
						}
						//no end of doctype
						else {
							$s = trim(substr($text, $index +9));
							$com = array("tag" => "!DOCTYPE", "text" => $s, "error" => 4);
							array_push($node["children"], $com); //save doctype to end of file
							$index = strlen($text);
							return;
						}
					}
					//normal tag
					else {
						$this->parseTag($node, $text, $index);
						$begin = $index;
					}
				}
			}
		}		
	}

	private function parseTag(&$node, &$text, &$index) {
		$pos = strpos($text, ">", $index);
		if($pos === false) {
			$s = substr($text, $index);
			array_push($node["children"], array("error" => 3, "text" => $s)); //save error tag to end of file
			$index = strlen($text);
			return;
		} 
		else {
			$s = substr($text, $index +1, $pos - $index -1);
			$index = $pos +1;
			$s = trim($s);
			$args = array();
			$string = false;
			$delim = "";
			$k = 0;
			for($i = 0; $i < strlen($s); $i++) {
				$char = $s[$i];
				if($char == '"' || $char == "'") {
					if($string == false) {
						$delim = $char;
					}
				}
				if($char == $delim) {
					if($string == true) {
						$string = false;
					}
					else {
						$string = true;
					}
				}
				if($char == " " || $char == "\t" || $char == "\n"  || $char == "\r") {
					if($string == false) {
						if($i > $k) {
							$arc = substr($s, $k, $i - $k);						
							$done = false;
							if(count($args) > 0) {
								$last = $args[count($args) -1];
								if($last[strlen($last) -1] == "=") {
									$args[count($args) -1] .= $arc;
									$done = true;
								}								
								if($arc[0] == "=") {
									$args[count($args) -1] .= $arc;
									$done = true;
								}							
							}						
							if(!$done) {
								array_push($args, $arc);
							}
						}
						$k = $i +1;
					}
				}
			}
			$done = false;
			$arc = substr($s, $k);
			if(count($args) > 0) {
				$last = $args[count($args) -1];
				if($last[strlen($last) -1] == "=") {
					$args[count($args) -1] .= $arc;
					$done = true;
				}
				if($arc[0] == "=") {
					$args[count($args) -1] .= $arc;
					$done = true;
				}
			}
			if(!$done) {
				array_push($args, $arc);
			}
			
			if(count($args) > 0) {
				$arc = $args[count($args) -1];
				$char = $arc[strlen($arc) -1];
				if($char == "/" && $arc != "/") {
					$args[count($args) -1] = substr($arc, 0, strlen($arc) -1);
					array_push($args, "/");
					
				}
			}
			
			$end = false;
			$child = array("children" => array());
			for($i = 0; $i < count($args); $i++) {
				if($i == 0) {
					$child["tag"] = $args[0];
				}
				else {
					$s = trim($args[$i]);
					if($s != "") {
						if($s != "/") {
							$kpos = strpos($s, "=");
							$key = "";
							$attr = "";						
							if($kpos !== false) {
								$key = trim(substr($s, 0, $kpos));
								$attr = trim(substr($s, $kpos +1));
								if($attr[0] == '"' || $attr[0] == "'") {
									$child["attr"][$key] = substr($attr, 1, strlen($attr) -2);
								}
								else {
									$child["attr"][$key] = $attr;
								}
							}
						}
						else {
							$end = true;
							$child["end"] = true;
							unset($child["children"]);
						}
					}
				}
			}			
			$i = array_push($node["children"], $child);
			if($end == false) {
				//no closed tag
				if($child["tag"] == "script") {
					//script node
					$pos = strpos($text, "</script>", $index);
					if($pos !== null) {
						$s = trim(substr($text, $index, $pos - $index));
						if($s != "") {
							array_push($node["children"][$i -1]["children"], array("text" => $s));
						}
						$index = $pos +9;
					}
				}
				else {
					//no script tag
					$this->parse($node["children"][$i -1], $text, $index);
				}
			}
		}
	}
}

class Html
{
	var $root;
	
	function __construct() {
		$this->root = array("tag" => "root", "children" => array());
	}
	
	function getRootElement() {
		return new Element($this->root);
	}
	
	function loadFile($path) {
		$text = file_get_contents($path);
		$this->loadString($text);
	}
	
	function loadString($text) {
		$el = $this->getRootElement();
		$el->setInnerHtml($text);
	}
	
	function save() {
		$el = $this->getRootElement();
		return $el->getInnerHtml();
	}

	function getElementById($id) {
		return new Element($this->getChildById($this->root, $id));
	}
	
	//private functions
	
	private function &getChildById(&$node, $id) {
		foreach ($node["children"] as &$child) {
			if(isset($child["attr"])) {
				if(isset($child["attr"]["id"])) {
					if($child["attr"]["id"] == $id) {
						return $child;
					}
				}
			}
			if(isset($child["children"])) {
				$ret = &$this->getChildById($child, $id);
				if(isset($ret)) {
					return $ret;
				}
			}
		}
		$nil = NULL;
		return $nil;
	}
}

?>
