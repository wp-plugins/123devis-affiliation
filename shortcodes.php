<?php
//[sm] shortcode
function sm_shortcode_func( $atts ){
	if (!isset($atts['action'])){
		throw new Exception("Action required in SM short tag");
	}

	wp_enqueue_style('sm_css', plugins_url('sm/ui/css/sm.css', __FILE__));

	$action = $atts['action'];

	$sm_display_defaults = get_option("sm_display_defaults");

	if (sm_no_api_calls_now()){
		return "";
	}

	switch($action){
		/*
		case 'sr_form' :
			if (!isset($atts['activity_id'])){
				throw new Exception("attribute 'activity_id' is required in SM short tag with action 'form'");
			}

			new sm_wp_log("Showing form \"{$atts['activity_id']}\" via shortcode with attributes");

			//$atts['mode'] = isset($atts['mode']) ? $atts['mode'] : 'basic';//default to basic
			$interview = $api->sr->activity->interview->get(array("activity"=>$atts['activity_id']));

			$interview->set_parameter("sm_display_defaults", $sm_display_defaults);

			if (!empty($_POST) AND $interview->submit($_POST)){
				return $interview->render_thanks();
			} else {
				return $interview->render($_POST);
			}

		break;

		case 'sp_form' :
			new sm_wp_log("Showing spa form via shortcode with attributes");

			$aff_track = get_option("sm_default_aff_str", "");

			$api = sm_api_factory();

			//$atts['mode'] = isset($atts['mode']) ? $atts['mode'] : 'basic';//default to basic
			$interview = $api->sp->interview->get(array());

			$interview->set_parameter("sm_display_defaults", $sm_display_defaults);

			$interview->set_affiliate_track_string($aff_track);

			sm_enqueue_required_js_for_forms();

			if ($interview->has_errors()){
				new sm_wp_log(array("type"=>"error", "message"=>"SP Interview has errors" . $interview->get_formatted_errors()));
				return __("Interview not available", "sm_translate");
			}

			return $interview->render_with_submit();
		break;
		*/
		case 'named_sr_form' :
		case 'named_sp_form' :

			if (!get_option( "sm_accept_spa", 0) AND $action == "named_sp_form"){
				new sm_wp_log("Trying to show shortcode for named sp form \"{$atts['form_name']}\" but option sm_accept_spa form not showing");
				return "";
			}

			new sm_wp_log("Showing named_form \"{$atts['form_name']}\" via shortcode");

			if (!isset($atts['form_name'])){
				throw new sm_exception_general("attribute 'form_name' is required in SM short tag with action 'named_form'");
			}

			$interview_params = array(
				"embedable_name" => $atts['form_name'],
				"type" => ($action == "named_sp_form" ? "sp" : "sr")
			);

			$interview = sm_make_interview_from_embeddable($interview_params);
			
			if (empty($interview)){
                new sm_wp_log(array("type"=>"error", "message"=>"Interview \"{$atts['form_name']}\" not found"));
				return __("Interview not available", "sm_translate");
			}
			
			if ($interview->has_errors()){
				new sm_wp_log(array("type"=>"error", "message"=>"Interview has errors" . $interview->get_formatted_errors()));
				return __("Interview not available", "sm_translate");
			}
			
			sm_enqueue_required_js_for_forms();
			
			if ($interview->get_parameter('view') == 'multiplude'){//multiplude requires bbq plugin to enable forward and backward functions
				sm_enqueue_required_js_for_forms(array(), array("jquery.bbq","multiplude_back_fix"));
			}

			return $interview->render_with_submit();
		break;

		case 'home_list' :
			$api = sm_api_factory();

			new sm_wp_log("Showing home_list via shortcode");
			//$atts['mode'] = isset($atts['mode']) ? $atts['mode'] : 'basic';//default to basic
			try {
				$home_list = $api->sr->category->list->get();
			} catch(Exception $e){
				return "";
			}
			$home_list->set_parameter("sm_display_defaults", $sm_display_defaults);

			return $home_list->render();
		break;

		case 'category_list' :
			$api = sm_api_factory();
			if (!isset($atts['category_id'])){
				throw new Exception("attribute 'category_id' is required in SM short tag with action 'category_list'");
			}

			new sm_wp_log("Showing category \"{$atts['category_id']}\" via shortcode");

			//$atts['mode'] = isset($atts['mode']) ? $atts['mode'] : 'basic';//default to basic
			try {
				$category_list = $api->sr->category->activities->get(array('category'=>$atts['category_id']));
			} catch(Exception $e){
				return "";
			}
			$category_list->set_parameter("sm_display_defaults", $sm_display_defaults);

			return $category_list->render();
		break;

		case 'search_box' :
			$api = sm_api_factory();

			new sm_wp_log("Showing search_box via shortcode");

			sm_enqueue_required_js_for_forms(array(), array("jquery","jquery.validate"));

			$search_box = $api->sr->activity->search->renderable();
			$search_box->set_parameter("sm_display_defaults", $sm_display_defaults);

			return $search_box->render();
		break;

		default :
            new sm_wp_log(array("type"=>"error", "message"=>"Invalid shortcode action \"$action\"!"));
			return "<!-- invalid sm shortcode -->";
            //throw new sm_exception_general("Invalid action \"$action\" in SM shortcode");
		break;
	}

	return;
}


