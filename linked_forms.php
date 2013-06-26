<?php
	function sm_linked_forms_content($posts) {

		global $wp_query;
		global $wp;

        if (strtolower($wp->request) == "sm/interview" AND isset($_REQUEST['id_activity'])){
			remove_filter('the_content', 'wpautop');

			if (sm_no_api_calls_now()){
				return $posts;
			}

			if (isset($_COOKIE['KWID_COOKIE'])){
				$dflt_kwid = $_COOKIE['KWID_COOKIE'];
			} else {
				$sm_creds = get_option("sm_creds");
				$dflt_kwid = $sm_creds['sm_kwids'][0];
			}

			$dflt_aff_str = get_option("sm_default_aff_str");
			$display_defaults = get_option("sm_display_defaults");

			$post = new stdClass;
			$post->post_name = "sm_interview";

			$api = sm_api_factory();

			try{
				$interview = $api->sr->activity->interview->get(array("activity"=>$_REQUEST["id_activity"]));
			} catch(Exception $e){
				new sm_wp_log(array("type"=>"error", "message"=>"Error loading interview in linked forms", "message_more"=>$e->getMessage()));
				return $posts;
			}

			new sm_wp_log("Showing linked form \"{$interview->get_id()}\" {$interview->get_title()}");

			$interview->set_affiliate_data($dflt_kwid, $dflt_aff_str);

			if ($interview->has_errors()){
				new sm_wp_log(array("type"=>"error", "message"=>"Error rendering interview", "message_more"=>$interview->get_errors()));
				$post->post_content = "There was a problem. Please try again later.";
				return array($post);
			}

			$interview->set_parameter("view", "2page");

			$interview->set_parameter("sm_display_defaults", $display_defaults);

			$post->post_title = "Tell us about your " . $interview->get_title() . " project";
			$post->post_content = $interview->render_with_submit();

			wp_enqueue_style('sm_css', plugins_url('sm/ui/css/sm.css', __FILE__));

			sm_enqueue_required_js_for_forms();

			$post->post_author = "";
			$post->post_parent = "";
			$post->post_type = "page";
			$post->ID = -1;
			$post->post_status = 'static';
			$post->comment_status = 'closed';
			$post->ping_status = "closed";
			$post->comment_count = 0;
		   	$post->post_date = "";//current_time('mysql');
			$post->post_date_gmt = "";//current_time('mysql', 1);

            $posts = array($post);

            $wp_query->is_page = true;
            $wp_query->is_singular = true;
            $wp_query->is_home = false;
            $wp_query->is_archive = false;
            $wp_query->is_category = false;
            unset($wp_query->query["error"]);
            $wp_query->query_vars["error"]="";
            $wp_query->is_404=false;

        }
        return $posts;
	}