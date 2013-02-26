<?php
/**
 * Converts a node into a functional object
 * 
 * @property DOMDocument $document DOMDocument object
 * @property DOMNode $node DOMNode object
 * @property array $attributes Key value array of attributes
 * @property object $CI Codeigniter
 */
class jQueryNode {
	public $document;
	public $node;
	public $attributes = array();
	public $CI;
	
	/**
	* Build jQueryNode object
	* 
	* @param DOMNode $node Single DOMNode from jQuery->find()	
	* @return jQueryNode
	*/
	public function __construct($node, $document){
		$this->document = $document;
		$this->node = $node;
		$this->attributes = $this->get_attributes();
		
		return $this;
	}
	
	/**
	* Parse the current node and return an array of attributes
	* 	
	* @return array Key value array of attributes
	*/
	public function get_attributes(){
		$attributes = array();
		if($this->node->hasAttributes()){
			foreach($this->node->attributes as $attr){
				$attributes[$attr->nodeName] = $attr->nodeValue;
			}
		}
		return $attributes;
	}
	
	/**
	* Get the value for an attribute
	* 
	* @param string $key Attribute name	
	* @return string Attribute value
	*/
	public function attr($key, $value=false){
		if($value && isset($this->attributes[$key])){
			$this->node->setAttribute($key, $value);
		}else{
			return isset($this->attributes[$key])? $this->attributes[$key]: false;
		}
	}
	
	/**
	* Get the html inside the current node
	* 	
	* @return string inner html
	*/
	public function innerHTML($value=false){
		if($value){
			$newElement = $this->document->createElement($this->node->nodeName);			
			$this->appendHTML($newElement, $value);
			$this->node->parentNode->insertBefore($newElement, $this->node);
			$this->node->parentNode->removeChild($this->node);
			
			foreach($this->attributes as $key => $value){
				$newElement->setAttribute($key, $value);				
			}
			$this->node = $newElement;
		}
		return $this->node->nodeValue;
	}
	
	
	/**
	* Append raw html to the current node
	*
	* @return jQueryNode returns this
	*/
	function appendHTML(&$parent, $source) {
		$fragment = new DomDocument();
		$fragment->loadHTML('<htmlfragment>'.$source.'</htmlfragment>');
		$import = $fragment->getElementsByTagName('htmlfragment')->item(0);
		foreach ($import->childNodes as $child) {
			$importedNode = $this->document->importNode($child, true);
			$parent->appendChild($importedNode);
		}		
		
		return $this;
	}

	/**
	* Get the html value of the current node
	* 	
	* @return string html
	*/
	public function html(){
		return $this->document->saveXML($this->node);
	}
	
	/**
	* Searches the current node for nodes matching css selector
	* 
	* @param string $css String of css selectors
	* @return jQuery
	*/
	public function find($css='html'){
		$jQuery = new jQuery();
		$jQuery->load($this->html());
		return $jQuery->find($css);
	}
}
?>