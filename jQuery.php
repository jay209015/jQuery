<?php
/**
 * Converts an html document into a css searchable object
 * 
 * @property DOMDocument $document DOMDocument object
 * @property DOMXpath $xpath DOMXpath object 
 * @property array $nodes Array of jQueryNodes
 * @property DOMNodeList $results DOMNodeList object
 * @property string $last_xpath last generate xpath
 * @property object $CI Codeigniter
 */
class jQuery {
	public $document;
	public $xpath;
	public $nodes = array();
	public $results;
	public $last_xpath;
	public $CI;
	
	/**
	* Build jQuery object
	* 
	* @param mixed $source Can be a url, file or string of html	
	* @return jQuery
	*/
	public function __construct($source=false){
		if($source){
			$this->load($source);
		}
		return $this;
	}
	
	public function load($source){
		$this->document = new DOMDocument();		
		libxml_use_internal_errors(true);
		
		# Is source is a url
		if(filter_var($source, FILTER_VALIDATE_URL) !== FALSE){
			$source = file_get_contents($source);
			$this->document->recover = true;
			$this->document->strictErrorChecking = false;
			$this->document->loadHTML($source);
		# Is source a file
		}elseif(file_exists($source)){
			$this->document->loadHTMLFile($source);
		# Source must be pure html
		}else{
			$this->document->loadHTML($source);
		}
		
		$this->xpath = new DOMXpath($this->document);
		return $this;
	}
	
	/**
	* Converts a css string to xpath string
	* 
	* @param string $css String of css selectors
	* @return string
	*/
	public function css2xpath($css){
		$xpath = $css;
		
		# Attributes
		$xpath = str_replace('[', '[@', $xpath);
		
		# Nodes
		$xpath = preg_replace('/\s{1}+(?![\.#\s])([^\.#\s]*)/', "/$1", $xpath);
		
		# IDs
		$xpath = preg_replace('/(?:^|\s)+#([^\s\/]*)/', '*[@id="$1"]', $xpath);
		$xpath = preg_replace('/(?<!\s)#([^\s\/]*)/', '[@id="$1"]', $xpath);
		
		# Classes
		$xpath = preg_replace('/(?:^|\s+)\.([^\s\/]*)/', "*[contains(concat(' ', normalize-space(@class), ' '), ' $1 ')]", $xpath);
		$xpath = preg_replace('/(?<!\s)\.([^\s\/]*)/', "[contains(concat(' ', normalize-space(@class), ' '), ' $1 ')]", $xpath);
		
		# Final regexes
		$xpath = 'descendant-or-self::'.$xpath;
		$xpath = preg_replace('/(\/)/', '$1descendant::', $xpath);
		$xpath = preg_replace('/(?<!:)(\*)/', '/$1', $xpath);
		
		$this->last_xpath = $xpath;
		return $this->last_xpath;
	}
	
	/**
	* Searches the document for nodes matching css selector
	* 
	* @param string $css String of css selectors
	* @return jQuery
	*/
	public function find($css='html'){
		$xpath = $this->css2xpath(trim($css));
		
		$this->results = $this->xpath->query($xpath);		
		$this->node_array();
		return $this;
	}	
	
	/**
	* Get the results of a find
	* 
	* @return array
	*/
	public function result(){
		return $this->nodes;
	}
	
	/**
	* Get the first result of the set
	* 
	* @return array
	*/
	public function one(){
		return $this->nodes[0];
	}
	
	/**
	* Builds jQueryNodes from result of a find
	* 
	* @return jQuery
	*/
	private function node_array(){
		$this->nodes = array();
		if($this->results){
			foreach($this->results as $node){
				$this->nodes[] = new jQueryNode($node, $this->document);
			}
		}
		
		return $this;
	}
}
?>