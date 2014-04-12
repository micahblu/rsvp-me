/**
 * RSVP ME Admin functions
 *
 * @since: 1.9
 */
(function($){

	/** 
	 * Admin tabs
   * @since 1.9.6
   */

	$('.nav-tab').click(function(){

		$(".tab-panel").hide();
		$(".nav-tab").removeClass("nav-tab-active");
		$(this).addClass("nav-tab-active");

		$("#"+this.id.replace(/(\-[0-9]+)/, '-content$1')).show();

	});

	/**
	 * Setup our setting ajax submit 
	 * @since 1.9.2
	 */
	$("#rsvp-me-submit-settings").on('click', function(e){
		e.preventDefault();

		var data = { action : 'rsvp_me_update_settings' };

		$("#rsvp-me-admin input").each(function(index){
			if(this.type != "submit" && /rsvp_me(.*)/.test(this.name)){
				data[this.name] = this.value;
			}
		});

		//console.log(data);

		$.post(ajaxurl, data, function(data){
			console.log(data);
			if(data.slice(-1) == "0"){
				data = data.slice(0, -1);
			}
			var response = $.parseJSON(data);

			if(response.success){
				$(".rsvp-me-alert-msg").html("<p class='rsvp-me-alert-box success'>Setting updated!</p>");
			}else{
				$(".rsvp-me-alert-msg").html("<p class='rsvp-me-alert-box error'>Oops, there an error saving the settings</p>");
			}

			setTimeout("jQuery('.rsvp-me-alert-box').fadeOut()", 2000);
		});
	});

	/**
	 * Hook our WP Color Picker on color field inputs 
	 * @since 1.9.2
	 */
	var myOptions = {
	    // you can declare a default color here,
	    // or in the data-default-color attribute on the input
	    defaultColor: false,
	    // a callback to fire whenever the color changes to a valid color
	    change: function(event, ui){

	    	switch(event.target.name){
	    		case "rsvp_me_table_cell_bg" :
	    			$("#rsvp_me_event_calendar table tr td").css("background-color", event.target.value);
	    		break;
	    		
	    		case "rsvp_me_table_border_color" :
	    			$("#rsvp_me_event_calendar table tr td").css("border-color", event.target.value);
	    		break;

	    		case "rsvp_me_table_cell_color" :
	    			$("#rsvp_me_event_calendar table tr td").css("color", event.target.value);
	    		break;
	    	
	    		case "rsvp_me_table_event_bg" :
	    			$("#rsvp_me_event_calendar table tr td.event-day").css("background-color", event.target.value);
	    		break;
	    	}
	    },
	    // a callback to fire when the input is emptied or an invalid color
	    clear: function() {},
	    // hide the color picker controls on load
	    hide: true,
	    // show a group of common colors beneath the square
	    // or, supply an array of colors to customize further
	    palettes: true
	};
	 
	$('.rsvp-me-color-field').wpColorPicker(myOptions);

})(jQuery);
