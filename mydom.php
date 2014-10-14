<?php

class Children implements Iterator
{
	private $position = 0;
	private $element = null;

	public function __construct($element) {
		$this->position = 0;
		$this->element = $element;
	}

	function rewind() {
		$this->position = 0;
	}

	function current() {
		return new Element($this->element->childNodes->item($this->position));
	}

	function key() {
		return $this->position;
	}

	function next() {
		++$this->position;
	}

	function valid() {
		if(!isset($this->element)) {
			return FALSE;
		}
		if($this->position < $this->element->childNodes->length) {
			return TRUE;
		}
		return FALSE;
	}
}

class Element
{
	private $element;
	
	function __construct($element = null) {
		if(isset($element)) {
			$this->element = $element;
		}
		else {
			$doc = new DOMDocument();
			$doc->loadHTML('<html><head><meta charset="UTF-8"></head><body><div>'
				.'</div></body></html>');	
			$this->element = $doc->getElementsByTagName("div")->item(0);
		}
	}
	
	function getType() {
		return get_class($this->element);
	}
			  
	function getInnerHtml() {
		$innerHTML = ""; 
		$children  = $this->element->childNodes;
		foreach ($children as $child) { 
			$innerHTML .= $this->element->ownerDocument->saveHTML($child);
		}
		return $innerHTML; 
	}
	
	function setInnerHtml($text) {
		while($this->element->childNodes->length){
			$this->element->removeChild($this->element->firstChild);
		}
		
		$doc = new DOMDocument();
		$doc->loadHTML('<html><head><meta charset="UTF-8"></head><body><div>'
			.$text.'</div></body></html>');	
		$root = $doc->getElementsByTagName("div")->item(0);
		
		$children  = $root->childNodes;
		foreach ($children as $child) { 
			$node = $this->element->ownerDocument->importNode($child, TRUE);
			$this->element->appendChild($node);
		}
	}
	
	function setInnerText($text) {
		$this->element->nodeValue = $text;
	}
	
	function getChildren() {
		return new Children($this->element);
	}
	
	function getChild($tag) {
		$list = $this->element->getElementsByTagName($tag);
		if($list->length >0) {
			return new Element($list->item(0));
		}
		return NULL;
	}
	
	function addChild() {
		$el = $this->element->ownerDocument->createElement("div");
		$this->element->appendChild($el);
		return new Element($el);
	}
	
	function getTag() {
		return $this->element->tagName;
	}

	function setTag($name) {
		$childnodes = array();
		foreach ($this->element->childNodes as $child) {
			$childnodes[] = $child;
		}
		
		$newnode = $this->element->ownerDocument->createElement($name);
		foreach ($childnodes as $child) {
			$child2 = $this->element->ownerDocument->importNode($child, true);
			$newnode->appendChild($child2);
		}
		
		$list = $this->element->attributes;
		foreach($list as $attr) {
			$attrName = $attr->nodeName;
			$attrValue = $attr->nodeValue;
			$newnode->setAttribute($attrName, $attrValue);
		}
		
		$this->element->parentNode->replaceChild($newnode, $this->element);
		$this->element = $newnode;
	}
	
	function setEnd() {
		//only for compatibility with previous version
	}
	
	function getAttribute($name) {
		return $this->element->getAttribute($name);
	}
	
	function setAttribute($name, $value) {
		$this->element->setAttribute($name, $value);
	}
	
	function removeAttribute($name) {
		$this->element->removeAttribute($name);
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
		
		$style = $this->element->getAttribute("style");
		if(empty($style)) {
			$this->element->setAttribute("style", $name.": ".$value.";");
			return;
		}		
		
		if($this->getStyle($name) == "") {
			if(substr($style, strlen($style)-1, 1) != ";") {
				$style .= ";";
			}
			$this->element->setAttribute("style", $style." ".$name.": ".$value.";");
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
			$this->element->setAttribute("style", trim($newstyle));
		}		
	}
	
	function removeStyle($name) {
		$style = $this->getStyle($name);
		if($style == "") {
			return;
		}
		else {
			$style = $this->element->getAttribute("style");
			$lines = explode(";", $style);
			$newstyle = "";
			foreach($lines as $line) {
				$attr = explode(":", $line);
				if(trim($attr[0]) != $name && trim($attr[0]) != "") {
					$newstyle = $newstyle.trim($attr[0]).": ".trim($attr[1]).";";
				}
			}
			if($newstyle != "") {
				$this->element->setAttribute("style", $newstyle);
			}
			else {
				$this->element->removeAttribute("style");
			}
		}	
	}
	
	function printElement() {
		//only for compatibilty
	}
}

class Html
{
	var $doc;
	
	function __construct() {
		$this->doc = new DOMDocument();
	}
	
	function getRootElement() {
		return new Element($this->doc->documentElement);
	}
	
	function loadFile($path) {
		@$this->doc->loadHTMLFile($path);
	}
	
	function loadString($text) {
		@$this->doc->loadHTML($text);
	}
	
	function save() {
		return $this->doc->saveHTML();
	}

	function getElementById($id) {
		return new Element($this->doc->getElementById($id));
	}
}

?>
