jQuery.support.cors = true;

jQuery(function($){
	//setup the other text field behaviors
	$("div.sm_other_wrap.start_hide").hide();
	$(".sm_select_has_other").bind("change click", function(){
		var $this = $(this);
		var jSel = 'input[name="'+$this.attr("name") + '_manual_input"]';
		var show_it = Boolean($this.val().match(/other$/));
		$(jSel).parent().toggle(show_it);
	});

	$("form.fr #cus_cp_form").focus(function(){
		$(".sm_which_town, #sm_town_list", $(this).next("#cp_msg").addClass("cp_error")).show();
		$("#sm_chosen_town").hide();
	}).keyup(function(){	

		//$("#cp_msg").removeClass("cp_error").html("");
		var $this = $(this);
		if ($this.val().length < 5) return;
		if ($this.val() == $this.attr("data_lastval")) return;
		$this.attr("data_lastval", $this.val());
		
		var success_function = function(data, status, xhr) {
			//console.log(data);
			data = $.parseJSON( data );
			
			$("#cus_cas_cp_form").val(data.cas_cp);
			var $msgBox = $this.parent().find("#cp_msg");
					
			if ($msgBox.length == 0){
				$msgBox = $this.parent().append("<div id=\"cp_msg\"></div>").find("#cp_msg");
			}

			switch(data.cas_cp){
				case 1:
					//$("#cp_msg").addClass("cp_error").html("Code Not Found");
					$("#cus_id_ville_form").val("");
				break;
				case 2:
				case 3:
					//WEBSITE-3658 : API wording fixes
					$("#cp_msg").addClass("cp_error").html("<div class=\"sm_which_town\">Spécifiez la ville svp : </div><div id=\"sm_town_list\"></div><div id=\"sm_chosen_town\">Ville choisie : <span id=\"sm_chosen_town_name\"></span> <a href=\"\" id=\"sm_change_town_link\">(éditer)</a></div>");
					$("#sm_change_town_link").click(function(){
						$(".sm_which_town, #sm_town_list").show();
						$("#cp_msg").addClass("cp_error");
						$("#sm_chosen_town").hide();
						return false;
					});
		
					$.each(data.villes, function(i, e){
						e.libelle = e.libelle.replace(/<[^<]+>/g,"");
						var $link = $("<a href=\"\" class=\"sm_cp_link\">"+e.libelle+"</a>").click(function(){	
							$("#cus_id_ville_form").val(e['id']);
							$("#cp_msg").removeClass("cp_error");
							$("#sm_town_list").hide();
							$(".sm_which_town").hide();
							$("#sm_chosen_town").show();
							$("#sm_chosen_town_name").html(e.libelle);
							return false;
						});
						var $wrapped = $("<div class=\"sm_town_itm\"></div>").append($link);
						if (e['id'] == 0){
							$wrapped.addClass("sm_other_link");
						}
						$("#sm_town_list").append($wrapped);
					});
				break;
			}
		}
		var ajax_link = "http://www.123devis.com/formulaires/ajax_on_cp/" + $this.val();
		if ( window.XDomainRequest ) {
			var xdr = new XDomainRequest();  
			xdr.onprogress = function(){}									
			xdr.onload = function(){
				success_function( xdr.responseText );
			}
			xdr.open("get", ajax_link); 
			xdr.send();
		}else {
			$.ajax(ajax_link, {
				type: "GET",
				success: success_function,
				crossDomain: true,
				error: function (xhr, ajaxOptions, thrownError) {
					alert(xhr.status);
					alert(thrownError);
				  }

			});
		}
	});	
	
});
// Numeric only control handler
jQuery.fn.ForceNumericOnly = function()
{
	return this.each(function()
	{
		jQuery(this).keydown(function(e)
		{
			var key = e.charCode || e.keyCode || 0;
			// allow backspace, tab, delete, arrows, numbers and keypad numbers ONLY
			return (
				key == 8 || 
				key == 9 ||
				key == 46 ||
				(key >= 37 && key <= 40) ||
				(key >= 48 && key <= 57) ||
				(key >= 96 && key <= 105));
		});
	});
};