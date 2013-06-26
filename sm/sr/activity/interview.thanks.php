<?php
	class sm_sr_activity_interview__thanks extends sm_renderable  {
		function render($interview){
			if ($this->data->get_api()->get_country() == "fr"){
				$s = '<div class="sm_interview sm_thanks"><a name="formname"></a>'.
					'<h2>Votre demande de devis a bien été enregistrée</h2>'.
					'<p>Nous recherchons actuellement les professionnels disponibles en mesure de répondre à votre demande de devis (jusqu\'à 5 au total).</p>'.
					'<p>Nous vous enverrons leurs coordonnées par email au fur et à mesure que nous les aurons identifiés.</p>'.
					'</div>';
			} else {
				$s = '<div class="sm_interview sm_thanks"><a name="formname"></a>'.
					'<h2>Your service request has been successfully received.</h2>'.
					'<p>We are currently looking for available service professionals from your area able to provide you with quotes (up to 4 max).</p>'.
					'<p>We will send you their contact details by email every time we find you a match.</p>'.
					'</div>';
				
			}
			
			if (!$this->data->get_parameter("ajax", "0")){
				$s .= "<script>\n";
				$s .= "jQuery(function(){\n";
				$s .= 	"setTimeout(function() {\n";
				$s .= 		"jQuery(\"body\").trigger(\"sr_submit.sm_eu\", ['";
				$trackid = $interview->get_submission_result()->get_track_id();
				$s .= 			$trackid;
				$s .=		"']);";
				$s .= 	"}, 40);\n";
				$s .= "});\n";
				$s .= "</script>";
			}
			$s .= $this->data->get_parameter("success_more_text_ty", "");
			
			return $s;
		}
	}