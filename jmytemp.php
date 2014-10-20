<?php

require_once 'mydom.php';
			
class Template 
{
	private $doc;
	
	function __construct() {
		$this->doc = new Html();
	}
	
	function loadFile($template) {
		$this->doc->loadFile($template);
	}
			  
	function render(&$data = NULL, $id = "") {
		$root = $this->doc->getRootElement();
		$obj = NULL;
		if(isset($data)) {
			$obj = &$data;
		}
		else {
			$obj = array();
		}
		$node = NULL;
		if($id == "") {
			$node = $root;
		}
		else {
			$node = $this->doc->getElementById($id);
		}
		if(isset($node)) {
			$this->renderNode($node, $obj);
		}
	}
	
	function getHtml() {
		return $this->doc->save();
	}
	
	function saveData(&$data) {
		$html = $this->doc->getRootElement();
		$head = $html->getChild("head");
		if(!isset($head)) {
			return;
		}
		$script = $head->addChild();
		$script->setTag("script");
		$script->setAttribute("type", "text/javascript");
			
		$s = 'var data = {'.$this->dataToString($data).'};';
		$script->setInnerText($s);
	}
	
	//Private finctions
	
	private function dataToString(&$data) {
		$keys = array_keys($data);
		$asoc = FALSE;
		if($keys !== range(0, count($data) - 1)) {
			$asoc = TRUE;
		}
		$txt = "";
		foreach($keys as $key) {
			if($txt != "") {
				$txt.= ", ";
			}
			if($asoc) {
				$txt .= $key.": ";
			}
			if(is_array($data[$key])) {
				if(array_keys($data[$key]) !== range(0, count($data[$key]) - 1)) {
					$txt .= "{".$this->dataToString($data[$key])."}";
				}
				else {
					$txt .= "[".$this->dataToString($data[$key])."]";
				}
			}
			elseif(is_string($data[$key])) {
				$txt .= '"'.$data[$key].'"';
			}
			elseif(is_numeric($data[$key])) {
				$txt .= $data[$key];
			}
			elseif(is_bool($data[$key])) {
				if($data[$key]) {
					$txt .= "true";
				}
				else {
					$txt .= "false";
				}
			}
		}
		return $txt;
	}

	private function renderNode($node, &$obj, $loop = NULL)	{
		$children = $node->getChildren();
		foreach($children as $child)	{
			$data = $obj;
			$tp = $child->getType();
			if($tp == "DOMElement") {
				$this->renderChild($child, $data, $loop);	
			}
		}
	}
	
	private function renderChild($node, &$data, $loop = NULL) {	
		if($node->getAttribute("class") === "loop-cache") {
			return;
		}	
		$skip = $loop;	
		$commands = $node->getAttribute("data-temp");
		$coms = explode(";", $commands);
		foreach($coms as $command) {			
		$command = trim($command);
		//remove double spaces
		$args = explode(" ", $command);							
		switch($args[0]) {
			case "if":
				$val = $this->getData($args[1], $data);
				$com = NULL;
				if(isset($args[2])) {
					$com = $args[2];
				}
				if($val == $com && isset($com)) {
					$this->show($node);
				}
				else {
					$this->hide($node);
					return;
				}  
				break;	
			case "ifno":
				$val = $this->getData($args[1], $data);
				$com = NULL;
				if(isset($args[2])) {
					$com = $args[2];
				}
				if($val == $com && isset($com)) {
					$this->hide($node);
					return;
				}
				else {
					$this->show($node);
				}  
				break;	
			case "data":
				$file = file_get_contents($args[1]);
				$get = json_decode($file, true);
				if(json_last_error() == 0) {
					$data = array_replace_recursive($data, $get);
				}				
				break;	
			case "val":
				$val = $this->getData($args[1], $data);
				$node->setInnerHtml($val);
				break;	
			case "attr":
				$val = $this->getData($args[1], $data);
				$node->setAttribute($args[2], $val);
				break;
			case "include":
				$temp = trim($node->getInnerHtml());
				if(empty($temp)) {
					$temp = "";				
					$pth = $_SERVER["DOCUMENT_ROOT"];
					if(file_exists($pth.$args[1])) {
						$temp = file_get_contents($pth.$args[1]);
					}
					$node->setInnerHtml($temp);
				}				
				break; 
			case "insert":
				$temp = "";				
				$pth = $_SERVER["DOCUMENT_ROOT"];
				$val = $this->getData($args[1], $data);
				if(file_exists($pth.$val)) {
					$temp = file_get_contents($pth.$val);
				}
				$node->setInnerHtml($temp);		
				break;
			case "loop":
				$val = $this->getData($args[1], $data);
				$name = "";
				if(isset($args[2])) {
					$name = $args[2];
				}
				$temp ="";
				$text = "";
				if(!isset($skip)) {
					$skip = true;
					$temp = $node->getInnerHtml();
					$text = '<div class="loop-cache" style="display: none;">'.$temp."</div>";
				}
				else {
					$temp = $node->getInnerHtml();
					$text = "";
				}								
				$temp = $node->getInnerHtml();		
				if(!empty($val)) {
					for($i = 0; $i < count($val); $i++) {
						$item = new Element();
						$item->setInnerHtml($temp);
						if($name != "") {
							$this->replaceVar($item, $name, $args[1], $i);
						}
						$text .= $item->getInnerHtml();
					}
				}				
				$node->setInnerHtml($text);
				break;
			default:
				break;
			}
		}		
		$this->renderNode($node, $data, $skip);
	}
		
	private function hide($node) {
		$display = $node->getStyle("display");
		if($display == "none") {
			return;
		}
		$node->setStyle("display", "none");
		if($display != "") {
			$node->setAttribute("data-disp", $display);
		}
	}
	
	private function show($node) {
		$display = $node->getStyle("display");
		if($display != "none") {
			return;
		}
		$disp = $node->getAttribute("data-disp");
		if($disp == "") {
			$node->removeStyle("display");
		}
		else
		{
			$node->setStyle("display", $disp);
			$node->setAttribute("data-disp", "");
		}
	}
	
	private function getData($variable, $data) {
		$keys = explode(".", $variable);
		$v = $data;
		foreach($keys as $key) {
			if(!empty($v) && isset($v[$key])) {
				$v = $v[$key];
			}
			else {
				return "";
			}
		}
		return $v;
	}
	
	private function replaceVar($node, $name, $keys, $item) {
		$children = $node->getChildren();
		foreach($children as $child) {
			if($child->getType() == "DOMElement") {
				$s = $child->getAttribute("data-temp");
				$s = str_replace(" .".$name, " ".$keys.".".$item, $s);
				$child->setAttribute("data-temp", $s);
				$this->replaceVar($child, $name, $keys, $item);
			}
		}
	}
}

?>
