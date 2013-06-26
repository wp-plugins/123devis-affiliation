<?php
	class sm_renderable {
		
		protected $data;
		protected $default_parameters = array();
		
		function __construct($data = array()){
			$this->data = $data;
		}
		
		function override_template($template_name, $str){
			$name = 'templates_' . $template_name;
			$this->$name = $str;
		}
		/*
		function get_array_val($name, $arrays, $default = null){
		}
		*/
		function set_parameter($name, $data){
			$this->default_parameters[$name] = $data;
		}
			
		function get_parameters(){
			return $this->default_parameters;
		}
		
		function get_parameter($name, $default=null){
			if (isset($this->default_parameters[$name])) return $this->default_parameters[$name];
			elseif (!is_null($default)) return $default;
			throw new Exception ("No parameter exists for \"$name\"");
		}
		
		function use_template($template_name, $data){
			$template = $this->data->get_parameter($template_name);

			foreach ($data as $data_name => $data_item){
				if (is_string($data_item) OR is_int($data_item)){
					$template = str_replace('[' . $data_name . ']', $data_item, $template);
				}
			}
			
			return $template;
		}
		
		function get_required_js(){
			return array();
		}
		/*
		function use_jquery(){
			return false;
		}
		
		function get_required_css(){
			return array();
		}
		*/

	}