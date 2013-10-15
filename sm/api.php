<?php
	class sm_api {

		private $api_url = 'http://local-api.servicemagic.co.uk';
		private $api_version = "0.3";
		private $cache_mechanism = "ETAG";
		private $api_country;
		private $path = array();
		private $overrides = array();
		private $sm_settings;
		private $headers;

		function __construct($sm_aff_id = "0", $sm_token = "not stated"){
			$this->sm_settings = array(
				'sm_aff_id' => $sm_aff_id,
				'sm_token' => $sm_token
			);
			
			//setup the headers to transmit sm creds
			$this->headers = array(
				"user-agent" => "Servicemagic API Lib {$this->api_version}",
				"x-sm-token" => $this->sm_settings['sm_token']
			);
		}

        function get_api_version(){
            return  $this->api_version;
        }

		function set_api_url($api_url, $api_server){
			$this->api_url = $api_url;
			$this->api_server = $api_server;
		}

		function set_cache_mechanism($cache_mechanism){
			$this->cache_mechanism = $cache_mechanism;
		}
		
		function add_header($name, $val){
			$this->headers[$name] = $val;
		}
		
		function get_country(){
			return preg_replace("/^(dev-|local-)/", "", $this->api_server);
		}

		function get($a = array()){
			return $this->call("get", $a);
		}

		function post($a = array()){
			return $this->call("post", $a);
		}

		function delete($a = array()){
			return $this->call("delete", $a);
		}

		//shortcut to not make http request but use template hierarchy polymorphically
		function renderable($a = array()){
			return $this->call("renderable", $a);
		}

		private function call($mthd, $call_args){

			$from_cache = 0;
			//save the path to here for reeuse in rendering
			$renderable_class_name = "sm_" . implode("_", $this->path);

			//if $more contains any same path references need to add to array
			$more_path = array();
			foreach ($this->path as $spath){
				$more_path[] = $spath;
				if (array_key_exists($spath, $call_args)){
					$more_path[] = $call_args[$spath];
				}
			}

			//clear the path for object reeuse
			$this->path = array();

			//can be called for renderable so that we just give back the renderable object without traumatizing all the data variables
			if ($mthd == "renderable"){
				return new $renderable_class_name($call_args, $this->sm_settings, $this);
			}

			//determine the whole url
			$url = implode("/", array_merge(array($this->api_url, $this->api_server, $this->sm_settings['sm_aff_id'], $this->api_version),  $more_path));

			//clean for local dev urls since these have the info in the subdomain
			if (preg_match("/(123devis|magic\.co\.uk)/", $url)){
				$url = preg_replace("/(local|dev)-(uk|fr|de|it|es)\//", "", $url);
			}

			

			switch($mthd){
				case 'get' :
					//only get requests should ever be cached
					//make a cache name that is ok relative to wp multisite potential conflicts
					$cache_identifyer_str = implode("_", $more_path) . '__' . $this->sm_settings['sm_aff_id'] . '_' . $this->get_country();

					//now that we have name, check the cache for quicker response
					$cache = new sm_cacheing;
					$cached_api_data = $cache->retrieve($cache_identifyer_str);

					if (isset($cached_api_data['etag']) AND $this->cache_mechanism == "ETAG"){
						$this->headers[] = array("If-None-Match" => $cached_api_data['etag']);
					}

					if ($this->cache_mechanism == "Timeout" AND !empty($cached_api_data)){
						$api_data = $cached_api_data;
						$from_cache = 1;
					} else {
						$sm_http = $this->http_factory(array( "headers" => $this->prep_headers() ));

						//make the call
						$sm_http->get($url, $call_args);
						if ($sm_http->get_response_field("status_code") == "304"){
							$from_cache = 1;
							$api_data = $cached_api_data;
							unset($api_data['etag']);
						} else {
							$api_data_str = $sm_http->get_response_field("body");
							$api_data_str = str_replace("SMTP Error: Could not connect to SMTP host smtp-host\n", "", $api_data_str);
							$api_data = json_decode($api_data_str, 1);
						}
					}

				break;
				case 'post' :
					$sm_http = $this->http_factory(array( "headers" => $this->prep_headers() ));
					$sm_http->post($url, $call_args);
					$api_data_str = $sm_http->get_response_field("body");
					$api_data_str = str_replace("SMTP Error: Could not connect to SMTP host smtp-host\n", "", $api_data_str);
					$api_data = json_decode($api_data_str, 1);
				break;
				default :
					throw new Exception("Invalid http method. Please use get or post.");
				break;
			}

			//do something for misformatted or empty response
			if (empty($api_data)) {
				$error_data = array(
					"from_cache" => $from_cache,
					"url"=> $url
				);
				if (isset($api_data_str)){
					$error_data["api_data_str"] = $api_data_str;
				}
				//print_r($error_data);
				throw new sm_exception_httperror ("data failed json decode with string : "
					. substr($api_data_str, 0, 40)
					. (strlen($api_data_str) > 40 ? "..." : "."), $error_data);
			}

			//the api throws errors with the json key success = false (empty), do something in this case
			if (isset($api_data['success']) AND $api_data['success'] === FALSE) {
				$error_data = array(
					"from_cache" => $from_cache,
					"url"=> $url
				);
				if (isset($api_data_str)){
					$error_data["api_data_str"] = $api_data_str;
				}
				if (isset($call_args)){
					$error_data["call_args"] = $call_args;
				}
				
				throw new sm_exception_httperror ("api error", $error_data);
			}

			if ($mthd == "get" AND !isset($decoded_data['errors']) AND !$from_cache AND $sm_http->get_response_field("status_code") != "304"){
				//save etag for cacheing reeuse
				if ($etag = $sm_http->get_response_field("etag")){
					$api_data['etag'] = $etag;
				}
				$cache->save($cache_identifyer_str, $api_data);
				unset($api_data['etag']);
			}

			return new $renderable_class_name($api_data, $this->sm_settings, $this);
		}

		private function http_factory($data){
			$http_loader = new sm_http;

			$http = $http_loader->get_http_obj();

			$http->set_timeout("get", (getenv("SM_DEV") ? 20 : 4));
			$http->set_timeout("post", (getenv("SM_DEV") ? 40 : 12));

			foreach ($data['headers'] as $header){
				$http->add_http_header($header);
			}
			return $http;
		}
		
		private function prep_headers(){
			$headers = array();
			foreach ($this->headers as $header_key => $header_val){
				$headers[] = $header_key . ": " . $header_val;
			}
			return $headers;
		}
		
		//magic method used to build path url for api ex $api->account->validate->get();
		function __get($name){
			$this->path[] = strtolower($name);
			return $this;
		}
		
		

	}