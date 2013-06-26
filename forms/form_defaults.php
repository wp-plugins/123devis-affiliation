<!-- WEBSITE-3658 : API wording fixes -->
<div class="wrap">
<h2><?php print __( 'Form Options', 'sm_translate' ); ?></h2>

<?php sm_show_messages($messages);?>

<form method="post" action="" class="sm_appearance">
	<input type="hidden" name="_nonce" value="<?php print $nounce;?>">
	
	<!--WEBSITE-3684 : API Split the tab "option forms"-->
	<?php if(current_user_can('sm_api_manage_forms')){ ?>
	
	<h3><?php _e( 'Module Settings', 'sm_translate' ); ?></h3>
	
	
	
	<?php if(current_user_can('sm_api_manage_options')){ ?>
	
	<div class="sm_form_item">
		<label class="sm_inherit_pointer" for="parent"><?php _e("Temporarily disable API connections when slow", "sm_translate");?></label><br />
		<input 
			id="sm_deactivate_api_during_slow"
			type="checkbox" 
			class="sm_checkbox" 
			name="sm_deactivate_api_during_slow" 
			value="1" <?php if (sm_val_in_arrays('sm_deactivate_api_during_slow', $form_data_list, $sm_deactivate_api_during_slow)) print "checked=\"checked\"";?>> 
		<label for="sm_deactivate_api_during_slow"><?php _e("Yes","sm_translate");?></label>
	</div>
	
	<div class="sm_form_item">
		<label class="sm_inherit_pointer" for="parent"><?php _e("Clear all traces of plugin on deactivation", "sm_translate");?></label><br />
		<input 
			id="sm_clear_all_trace_on_deactivation"
			type="checkbox" 
			class="sm_checkbox" 
			name="sm_clear_all_trace_on_deactivation" 
			value="1" 
			<?php if (sm_val_in_arrays('sm_clear_all_trace_on_deactivation',  $form_data_list, $sm_clear_all_trace_on_deactivation)) print "checked=\"checked\"";?>> 
			<label for="sm_clear_all_trace_on_deactivation"><?php _e("Yes","sm_translate");?></label>
		<div class="sm_hint">
			<?php 
				_e('Check here if you wish all traces of the plugin to be removed when you deactivate the plugin.',"sm_translate");
			?>
		</div>
	</div>

	<div class="sm_form_item">
		<label class="sm_inherit_pointer"><?php _e("API cache mechanism", "sm_translate");?></label><br />
		<p>
			<input type="radio" class="sm_checkbox" name="sm_api_cache_mechanism" id="etag_cache" value="ETAG" <?php if (sm_val_in_arrays('sm_api_cache_mechanism',  $form_data_list, $sm_api_cache_mechanism) == "ETAG") print "checked=\"checked\"";?>> <b>ETAG :</b>
			<label for="etag_cache"><?php _e("The plugin will make a request to servicemagic.eu servers to see if the cached data is current.  This option while marginally slower ensures the data is always current.","sm_translate");?></label>
		</p>
		<p>
			<input type="radio" class="sm_checkbox" name="sm_api_cache_mechanism" id="tiemout_cache" value="Timeout" <?php if (sm_val_in_arrays('sm_api_cache_mechanism',  $form_data_list, $sm_api_cache_mechanism) == "Timeout") print "checked=\"checked\"";?>> <b>Trusted Cache :</b>
			<label for="tiemout_cache"><?php _e("The plugin will use cached data if it exists.  It is possible that the user might  stale data and therefore see a code issue while submitting an interview.","sm_translate");?></label>
		</p>
	</div>
	
	<?php } ?>
	
	<div class="sm_form_item">
		<label for="sm_default_success_more_text"><?php _e("Tracking pixel HTML", "sm_translate");?></label><br />
		<textarea id="sm_default_success_more_text" class="sm_textarea" name="sm_default_success_more_text"><?php print sm_val_in_arrays('sm_default_success_more_text', $form_data_list, $sm_default_success_more_text);?></textarea>
		<div class="sm_hint"><?php _e("Typically used with adwords pixels, this parameter allows you to append html to the success/thanks messaging on submission(javascript will not work here). Leave blank to not use this feature.","sm_translate")?></div>
	</div>
	
	
	
	<h3><?php print __( 'Manage Form Appearance', 'sm_translate' ); ?></h3>
	<p><?php print __( 'Customize the form\'s appearance', 'sm_translate' ); ?></p>
	<div class="sm_form_item">
		<label for="sm_default_aff_str"><?php _e("Your tracking code", "sm_translate");?></label><br />
		<input id="sm_default_aff_str" type="text" class="sm_textbox" name="sm_default_aff_str" value="<?php print sm_val_in_arrays('sm_default_aff_str',  $form_data_list, $sm_default_aff_str);?>"  >
		<div class="sm_hint"><?php _e("This parameter allows you to track the results of default forms independently from other forms.","sm_translate")?></div>
	</div>
	
	<div class="sm_form_item">
		<label for="sm_font_size"><?php _e("Font size", "sm_translate")?></label><br />
		<select class="sm_select" id="sm_font_size" name="sm_font_size">
			<option value=""><?php _e("Default", "sm_translate")?></option>
			<?php 
				foreach(range(8, 20) as $size){
					print "<option value=\"$size\" ";
					if (sm_val_in_arrays('sm_font_size', array_merge( $form_data_list, $sm_display_defaults), "") == $size) print "selected";
					print ">$size</option>\n";
				} 
				
			?>
		</select>
	</div>
	
	<div class="sm_form_item">
		<label for="sm_font_color"><?php _e("Font color", "sm_translate")?></label><br />
		<input type="text" class="sm_txt_wide" id="sm_font_color" name="sm_font_color" value="<?php print sm_val_in_arrays('sm_font_color', array($_POST, $sm_display_defaults), "");?>"  >
		<a href="" class="color_picker_reset" data_target="sm_font_color"><?php _e("Reset default", "sm_translate")?></a>
	</div>
	
	<div class="sm_form_item">
		<label for="sm_bg_color"><?php _e("Section background", "sm_translate")?></label><br />
		<input type="text" class="sm_txt_wide" id="sm_bg_color" name="sm_bg_color" value="<?php print sm_val_in_arrays('sm_bg_color', array($_POST, $sm_display_defaults), "");?>"  >
		<a href="" class="color_picker_reset" data_target="sm_bg_color"><?php _e("Reset default", "sm_translate")?></a>
	</div>
	
	<div class="sm_form_item"><br />
		<input type="submit" class="sm_submit button-primary" value="<?php _e("Save", "sm_translate")?>">
	</div>
	
	<?php } ?>
	
</form>

<script>
	var $j = jQuery.noConflict();
	$j(function(){
		$j("#sm_font_color, #sm_bg_color").miniColors({initColor:""});
		$j(".color_picker_reset").click(function(evt){
			var t = $j(this).attr("data_target");
			$j('input[name="'+t+'"]').miniColors("value","").blur();
			evt.preventDefault();			
		});
	});
</script>
</div>