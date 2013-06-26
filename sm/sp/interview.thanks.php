<?php
	class sm_sp_interview__thanks extends sm_renderable  {
		function render(){
			if ($this->data->get_api()->get_country() == "fr"){
				$s = '<div class="sm_interview sm_thanks"><a name="formname"></a>'.
					'<h2>Votre demande de devis a bien été enregistrée</h2>'.
					'<p>L\'un de nos conseillers va maintenant traiter votre demande d\'inscription.</p>'.
					'<p>Il vous confirmera votre inscription sous 48 heures.</p>'.
					'</div>';
			} else {
				$s = '<div class="sm_interview sm_thanks"><a name="formname"></a>'.
					'<h2>Thank you</h2>'.
					'<p>We received your information and one of our account managers will contact you shortly to help you get started and receive leads.</p>'.
					'</div>';
			}
			
			$s .= $this->data->get_parameter("success_more_text_ty", "");
			
			return $s;
		}
	}