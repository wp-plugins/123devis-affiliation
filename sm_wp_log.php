<?php
	class sm_wp_log {
		function __construct($data){
			global $wpdb;
			global $current_user;
			
			if (!is_array($data)){
				$data = array("message" => $data);
			}
			
			$more_data = array(
				'path' => $_SERVER['REQUEST_URI'], 
				'get_json' => json_encode($_GET),
				'post_json' => json_encode($_POST),
				'timest' => time(),
				'type' => "message",
				'user_name' => $current_user->user_login,
				'server_json' => json_encode($_SERVER)
			);
			
			$all_data = array_merge($more_data, $data);
			
			if (isset($all_data["message_more"]) AND !is_string($all_data["message_more"])){
				$all_data["message_more"] = json_encode($all_data["message_more"]);
			}

			$r = $wpdb->insert( 
				$wpdb->prefix . 'sm_log', 
				$all_data
			);
			
		}
	}