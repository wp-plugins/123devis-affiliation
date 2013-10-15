﻿<?php
	class sm_sr_activity_interview__basic extends sm_baseinterview  {
		
		function render(){
			$defaults = $this->data->get_parameter("defaults", array());
			$ajax_submit_path = $this->data->get_parameter("ajax_submit_path", "");
			$embeddable_id = $this->data->get_parameter("sm_embeddable_id", "");
			$lang = $this->data->get_api()->get_country();
			$translation = $this->data->get_parameter("translate");
			$s = "<div class=\"sm_interview\">\n";
			if ($params = $this->data->get_parameter("sm_display_defaults", false)){
				$s .= "<style>";
				if ($params["sm_font_color"]) $s .= '.sm_interview {color:'.$params["sm_font_color"]."}\n";
				if ($params["sm_font_size"]) $s .= '  .sm_interview, .sm_interview input, .sm_interview select, .sm_interview textarea {font-size:'.$params["sm_font_size"]."px !important;}\n";
				if ($params["sm_bg_color"]) $s .= '.sm_interview {background-color:'.$params["sm_bg_color"]."}\n";
				$s .= "</style>";
			}
			$s .= "<a name=\"formname\"></a>";
			
			$s .= "<div class=\"form\">\n";
			
			$s .= "<form method=\"post\" id=\"sm_basic_form\"  action=\"#formname\" class=\"sm_form ";
			
			$s .= $lang;
			$s .= "\">\n";
			if ($above = $this->data->get_parameter("text_above_questions", "")){
				$s .= "<div class=\"sm_above_q\">$above</div>";
			}
			$s .= "<input type=\"hidden\" name=\"activity_id\" value=\"" . $this->data->get_data("id") . "\">\n";
			
			if ($embeddable_id){
				$s .= "<input type=\"hidden\" name=\"embeddable_id\" value=\"" . $embeddable_id . "\">\n";
			}
			
			if ($this->data->get_validator()->has_errors()){
				//print $this->data->get_validator()->get_formatted_errors();
			}
						
			foreach($this->data->get_data("questions") as $qid => $qdata){
				if ($qdata['type'] == 'hidden'){
					$hidden_obj = new sm_sr_activity_interview_hidden($this->data);	
					$s .= $hidden_obj->render($qdata, $_POST);
				} else {
				
					//required fields settings affect here
					if ($this->data->get_parameter("only_required_fields", 0) AND (!isset($qdata['required']) OR !$qdata['required'])){
						continue;
					}
					
					//default fields settings remove fields from presentation here
					if (isset($defaults[$qdata['name']])){
						continue;
					}
					
					$s .= "<div class=\"sm_item\" id=\"{$qdata['name']}_wrap\">\n";
					$s .= "<label class=\"sm_label\" for=\"". $qdata['name'] ."_form\">\n";
					
					$s .= $qdata['label'];
					if (isset($qdata['required'])){
						$s .= "<span class=\"sm_required\">*</span>\n";
					}
					$s .= "</label>\n";
					
					if ($qdata['type']== ""){
						print "missing type";
						print_r($qdata);
					}
					
					$form_obj_name = "sm_sr_activity_interview_" . $qdata['type'];
					
					$form_obj = new $form_obj_name($this->data);
					$s .= $form_obj->render($qdata, $_POST);
					
					if ($this->data->get_validator()->item_has_error($qdata['name'])){
						$s .= "<label for=\"{$qdata['name']}_form\"  class=\"error\">";
						$s .= $this->data->get_validator()->get_item_first_error($qdata['name']);
						$s .= "</label>\n"; 
					}
					
					$s .= "</div>\n";
				}
			}
			
			if (!$submit_string = $this->data->get_parameter('submit_string', false)){
				$submit_string = $translation->trans("Get Quotes");
			}			
			
			$submit_string = htmlspecialchars($submit_string);
			
			$s .= "<div class=\"sm_form_controls\">";
			$s .= "<input type=\"submit\" class=\"sm_submit\" value=\"" . $submit_string . "\">\n";
			$s .= "</div>";

			$s .= "<div class=\"sm_required_declaration\">";
			$s .= $translation->trans("* required fields");
			$s .= "</div>";
			
			$s .= "</form>\n";
			$s .= "</div>\n";
			$s .= "</div>\n";
			
			$json_messages = $this->setup_jquery_validate_messages($this->data->get_data("questions"));
			
			$s .= "<script type=\"text/javascript\">\n".
				'jQuery(function($){'."\n".	
						'$("#cus_primary_phone_form,#cus_alternate_phone_form").ForceNumericOnly();'."\n".
						'$.validator.addMethod("pattern", function(value, element, param) {'."\n".
						//" console.log('param', param, value);\n".]
						" 	param = param.replace(/^\/|\/([\/gi])?$/g, '');\n".
						" 	var reg = new RegExp(param);\n".
						//" 	console.log(reg);\n".
						"	return this.optional(element) || reg.test(value);\n".
						"}, \"Invalid format.\");\n".
						'$.validator.addMethod("re_match_one", function(value, element, param) {'."\n".
						//"	console.log('param', param, value);\n".
						"	for (var re_i in param) { \n".
						"		/^\/(.*)\/([gi])?$/g.exec(param[re_i]);\n".
						" 		var this_re = new RegExp(RegExp.$1, RegExp.$2);\n".
						//"		console.log(this_re);\n".
						"		if (this_re.test(value)) return true;\n".
						"	}\n".
						"	return this.optional(element);\n".
						"}, \"Invalid format.\");\n".
					
						"$(\"#sm_basic_form\").validate({\n";
						if ($ajax_submit_path) {
							$s .= "	submitHandler: function(form){\n".
							"		var \$submit_btn = $(\".sm_submit\", form);\n".
							"		\$submit_btn.attr(\"disabled\", \"disabled\").attr(\"value\", \$submit_btn.attr(\"value\") + \"...\");\n".
							"		$.ajax({\n".
							"			\"type\":\"POST\",\n".
							"			\"url\":\"" . $ajax_submit_path . "\",\n".
							"			\"data\":$(form).serialize(),\n".
							"			\"success\" : function(data,status){\n".
							"				if (typeof(data.message) == 'string') {\n".
							"					\$(form).html(data.message);\n".
							"					form.scrollIntoView(false);\n".
							"					\$(\"body\").triggerHandler(\"sr_submit.sm_eu\", [data.track_id]);\n".
							"				} else if(typeof(data.errors) == \"object\") {\n".
							"					var s = \"Please fix these errors\";\n".
							"					for (var ei in data.errors){\n".
							"						s += data.errors[ei].join(\"\\n\");\n".
							"					}\n".
							"					alert(s);\n".
							"				} else {\n".
							"					alert(\"We've experienced an error. We do apologize. Please check back soon.\");\n".
							"				}\n".
							"				\$submit_btn.removeAttr(\"disabled\").attr(\"value\", \$submit_btn.attr(\"value\").replace(\"...\",\"\"));\n".
							"				return false;\n".
							"			},\n".
							"			\"error\": function(){\n".
							"				alert(\"We've experienced an error. We do apologize. Please check back soon.\");\n".
							"				\$submit_btn.removeAttr(\"disabled\").attr(\"value\", \$submit_btn.attr(\"value\").replace(\"...\",\"\"));\n".
							"			},\n".
							"			\"dataType\": \"json\"\n".
							"		});\n".
							"	},	\n";
						//"},\n";
						}
						$s .= "		rules : " . json_encode($json_messages['rules']) . ", \n" .
						"			messages : " . json_encode($json_messages['messages']) . ",\n".
						"			errorPlacement: function(error, element) { \n".
						"			if ( element.is(\":radio,:checkbox\") ){\n".
						" 				//console.log(element.parent());\n".
						"				error.insertAfter( element.parent().parent() );\n".
						"			} else \n".
						"				error.insertAfter( element);\n".
						"			}\n".
						"});\n";
		
						$jsbehavior_obj = new sm_jsbehaviors();	
						$s .= $jsbehavior_obj->render($this->data->get_data("questions"), "#sm_basic_form");
				
				$s .= "});\n";
			//	");\n";
				
			//$s .= "});\n";
			$s .= "</script>\n";
			
			return $s;
		}
		
	}