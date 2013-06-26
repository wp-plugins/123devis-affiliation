<?php
	class sm_exception_general extends Exception {
		function __construct($msg, $more = array()){
			
			new sm_wp_log(array(
				'type' => "error",
				'message' => $msg,
				'message_more' => json_encode($more)
			));
			
			parent::__construct($msg);
		}
	}