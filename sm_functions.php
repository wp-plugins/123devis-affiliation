<?php
	function sm_api_factory(){
		$sm_creds = get_option("sm_creds");

		if (!empty($sm_creds['sm_aff_id']) AND !empty($sm_creds['sm_token'])){
			$api = new sm_api($sm_creds['sm_aff_id'], $sm_creds['sm_token']);
		} else {
			$api = new sm_api();
		}

		$api->set_api_url(get_option("sm_api_url"), get_option("sm_api_server"));

		$api->set_cache_mechanism(get_option("sm_api_cache_mechanism", "ETAG"));

		return $api;
	}

	function sm_val_in_arrays($key, $arrays, $default){
		foreach ($arrays as $array){
			if (!is_array($array)) continue;

			if (is_array($array) AND array_key_exists($key, $array)){
				return $array[$key];
			}
		}
		return $default;
	}

	function sm_head_embedeableforms_js_json(){
		global $wpdb;

		//only need this if editing a page
		if (!preg_match("/post(\-new)?\.php$/", $_SERVER['SCRIPT_NAME'])) return;

		$r = array("sr"=>array());
		if (get_option( "sm_accept_spa", 0)){
			$r["sp"] = array();
		}

		foreach ($r as $type => $nouse){
			$myforms = $wpdb->get_results( "SELECT embedable_name FROM {$wpdb->prefix}sm_{$type}_forms WHERE is_archived <> 1 ORDER BY embedable_name" );
			foreach ($myforms as $myform){
				$r[$type][] = $myform->embedable_name;
			}
		}

		print "<script> var sm_embedeable_names = " . json_encode($r) . "</script>";
		return;
	}

	function sm_init(){
		//remove_action("send_headers", array()
		wp_enqueue_script("jquery");
		if ( ! current_user_can('edit_posts') && ! current_user_can('edit_pages') )
			return;

		// Add only in Rich Editor mode
		if ( get_user_option('rich_editing') == 'true') {
			add_filter("mce_external_plugins", "sm_tinymce_plugin");
			add_filter('mce_buttons', 'sm_tinymce_button');
			add_action('admin_footer','sm_head_embedeableforms_js_json');
		}
	}

	// Load the TinyMCE plugin : editor_plugin.js (wp2.5)
	function sm_tinymce_plugin($plugin_array) {
	   $plugin_array['smtinymceplugin'] = plugins_url('ui/js/tinymce_plugin.js', __FILE__);
	   return $plugin_array;
	}

	function sm_tinymce_button($buttons) {
	   array_push($buttons, "separator", "smtinymceplugin");
	   return $buttons;
	}

	function sm_make_interview_from_embeddable($data){
		global $wpdb;

		$api = sm_api_factory();

		if (isset($data['kwid_override'])){
			$api_kwid = $data['kwid_override'];
			unset($data['kwid_override']);
		} else {
			$sm_creds = get_option("sm_creds");
			$api_kwid = $sm_creds['sm_kwids'][0];
		}

		$type = $data['type'];
		unset($data['type']);

		switch($type){
			case "sr":
				$sql = "SELECT id, embedable_name, activity_id, parameters, created, altered, tracking_label FROM {$wpdb->prefix}sm_sr_forms WHERE ";
				$ajax_submit_path = admin_url() . "admin-ajax.php?action=sm_ajax_sr_submit";
			break;
			case "sp":
				$sql = "SELECT id, embedable_name, parameters, created, altered, tracking_label FROM {$wpdb->prefix}sm_sp_forms WHERE ";
				$ajax_submit_path = admin_url() . "admin-ajax.php?action=sm_ajax_sp_submit";
			break;
		}

		//prep sql and conditions
		$conditions_keys = array();
		$conditions_vals = array();
		foreach ($data as $cond_key => $cond_val){
			$key = $cond_key;
			if (preg_match("/^0-9+$/", $cond_val)) $key .= "= %d ";
			elseif(is_numeric($cond_val)) $key .= "= %F ";
			else $key .= "= %s ";
			$condition_keys[] = $key;
			$condition_vals[] = $cond_val;
		}

		foreach($condition_keys as $ckey){
			$sql .= $ckey;
		}

		$sql = $wpdb->prepare($sql, $condition_vals);

		$myform = $wpdb->get_row($sql);

		if (empty($myform)){
			return "";
		}

		//found embeddable. load interview via api, process it with wp specific values and return it.
		$myform->parameters = json_decode($myform->parameters, 1);

		try{
			switch ($type){
				case 'sr':
					$interview = $api->sr->activity->interview->get(array("activity"=>$myform->activity_id));
				break;
				case 'sp':
					$interview = $api->sp->interview->get(array());
				break;
			}
		} catch(Exception $e){
			return "";
		}

		//if this records tracking string is empty, pull from default as set in form options
		if (empty($myform->tracking_label)){
			$myform->tracking_label = get_option("sm_default_aff_str", "default");
		}

		$interview->set_affiliate_data($api_kwid, $myform->tracking_label);

		//set the site wide defaults as set on the defaults page
		$sm_display_defaults = get_option("sm_display_defaults");
		$interview->set_parameter("sm_display_defaults", $sm_display_defaults);

		//determine text to add to thank you from defaults and form forms.
		$more_text_ty = get_option("sm_default_success_more_text");
		if (!empty($myform->parameters['success_more_text'])){
			$interview->set_parameter("success_more_text_ty", $myform->parameters['success_more_text']);
			unset($myform->parameters['success_more_text']);
		} elseif (!empty($more_text_ty)){
			$interview->set_parameter("success_more_text_ty", $more_text_ty);
		}

		//save parameters to interview obj
		foreach ($myform->parameters as $pkey=>$pval) {
			$interview->set_parameter($pkey, $pval);
		}

		//give the ajax path for submission
		$interview->set_parameter("ajax_submit_path", $ajax_submit_path);

		//give the embedable id so ajax submissions can find the right one
		$interview->set_parameter("sm_embeddable_id", $myform->id);

		return $interview;
	}

	function sm_no_api_calls_now(){
		if (! get_option("sm_deactivate_api_during_slow", 0)){
			return 0;
		}

		if (!empty($_POST)) return 0;

		$times = get_option("sm_api_timeout_spans", array());

		$localtz = date_default_timezone_get();

		date_default_timezone_set('Europe/Luxembourg');

		$lx_time_now = date("Hi");

		date_default_timezone_set($localtz);

		foreach ($times as $time){
			if ($lx_time_now > $time[0] AND $lx_time_now <= $time[1]) {
				return 1;
			}
		}

		return 0;
	}

	/*
	 *	javascript files to enqueue.
	 @	alternate array. define each as name => array($path, $dependencies)
	 @	requireds array. define as list of names
	 *	To pass a subset, call as  sm_enqueue_required_js_for_forms(array(), array("jquery","jquery-ui-core"))
	 *	To pass an alternate call as  sm_enqueue_required_js_for_forms(array("jquery-ui-core"=> array("//alternateurl", array()))
	 *"jquery-form"
	 */
	function sm_enqueue_required_js_for_forms($alternate= array(), $reqd = array("jquery","jquery-ui-core","jquery-ui-widget","jquery.form.wizard","jquery.validate","jquery.sm_forms")){
		$available = array(
			"jquery" => array(),
			"jquery-ui-core" => array(),
			"jquery-ui-widget" => array(),
			//"jquery-form" => array(),
			"jquery.form.wizard" => array(
				plugins_url('sm/ui/js/jquery.formwizard-3.0.5/js/jquery.form.wizard.js', __FILE__),
				array("jquery", "jquery-ui-core", "jquery-ui-widget", "jquery-form")
			),
			"jquery.validate" => array(
				"url" => "http://ajax.aspnetcdn.com/ajax/jquery.validate/1.9/jquery.validate.min.js",
				"dependencies" => array("jquery.form.wizard")
			),
			"jquery.bbq" => array(
				"url" => plugins_url('sm/ui/js/jquery.ba-bbq.min.js', __FILE__),
				"dependencies" => array("jquery", "jquery.form.wizard")
			),
			"jquery.sm_forms" => array(
				"url" => plugins_url('sm/ui/js/jquery.forms.js', __FILE__),
				"dependencies" => array("jquery")
			),
			"multiplude_back_fix" => array(
				"url" => plugins_url('sm/ui/js/multiplude_back_fix.js', __FILE__),
				"dependencies" => array("jquery.bbq")
			)
		);

		foreach($reqd as $req){
			if (array_key_exists($req, $alternate))
				call_user_func_array("wp_enqueue_script", array_merge(array($req), $alternate[$req]));
			elseif (array_key_exists($req, $available))
				call_user_func_array("wp_enqueue_script", array_merge(array($req), $available[$req]));
			else throw new sm_exception_general("missing $req for sm_enqueue_required_js_for_forms");
		}
	}

	function sm_show_messages($msgs){
		foreach(array("error", "updated") as $mtype){
			if (isset($msgs[$mtype])){
				print "<div class=\"$mtype\"><p>" . $msgs[$mtype] . "</p></div>";
			}
		}
	}

	function sm_get_plugin_version(){
		$path = plugin_dir_path(__FILE__) . basename(dirname(__FILE__)).'.php';
		$plugin_data = get_plugin_data($path);
		return $plugin_data['Version'];
	}

	function sm_sanitize_for_slug($str){
		$str = trim($str);
		//first clear accents
		$str = remove_accents($str);

		//next turn anything else into a _.
		$str = preg_replace("/[^a-zA-Z0-9\_]/", "_", $str);

		//this might give us sequential underscores so turn those into just 1
		$str = preg_replace("/[\_]+/", "_", $str);

		//lower case so its easier to write and matches with the shortcode string
		$str = strtolower($str);

		return $str;
	}