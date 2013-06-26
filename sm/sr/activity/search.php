<?php
	class sm_sr_activity_search extends sm_toprenderable  {
		
		function found_results(){
			return !empty($this->data["search_results"]);
		}
		
		function get_results(){
			return $this->data["search_results"];
		}
		
	}