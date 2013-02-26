<?php
class Template extends jQuery { 
	private $data;
	
	public function __construct($view=false, $data=array()){		
		if($view){
			$this->load($view, $data);
		}else{
			parent::__construct();
		}
	}
	
	public function load($view, $data=array()){
		$view_file = config('viewpath').'/'."$view.php";		
		$this->data = $data;
		if(file_exists($view_file)){
			ob_start();
			extract($data);
			include($view_file);
			$html = ob_get_clean();
			parent::load($html);
		}else{
			die("Unable to load view: $view.");
		}
	}
	
	private function parse_variables($data){
		$query = '*[data-variables="true"]';
		
		$keys = array();
		$values = array();
		foreach($data as $key => $value){
			$keys[] = '{'.$key.'}';
			$values[] = $value;
		}
		
		if($nodes = $this->find($query)->result()){
			foreach($nodes as $node){
				if($attr = $node->attr('data-attribute')){
					$value = str_replace($keys, $values, $node->attr($attr));
					$node->attr($attr, $value);					
				}else{					
					$value = str_replace($keys, $values, $node->innerHTML());					
					$node->innerHTML($value);
				}
			}
		}
	}
	
	private function parse_nodes($key, $value){
		$query = '*[data-template="'.$key.'"]';
		if($nodes = $this->find($query)->result()){
			foreach($nodes as $node){
				if($attr = $node->attr('data-attribute')){
					$new_value = $value;
					$node->attr($attr, $new_value);
				}else{						
					$node->innerHTML($value);
				}
			}
		}
	}
	
	public function parse(){
		if($this->data){
			$this->parse_variables($this->data);
			foreach($this->data as $key => $value){			
				$this->parse_nodes($key, $value);
			}
		}
		
		return $this->document->saveHTML();		
	}		
}
?>