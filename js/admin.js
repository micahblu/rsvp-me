/**
 * RSVP ME Admin functions
 *
 * @since: 1.9
 */
(function($){
	function rsvp_me_delete_event(id){
		var data = {
			action : 'rsvp_me_delete_event',
			id : id
		};
		
		$.datepicker.setDefaults({
		  showOn: "both",
		  buttonImageOnly: true,
		  buttonImage: "calendar.gif",
		  buttonText: "Calendar"
		});
		
		$(".date").datepicker();

		// since Wordpress 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
		$.post(ajaxurl, data, function(response) { 
			$("#eventrow_"+id).remove(); //remove deleted event row
			$("#rsvp_ajax_msg").html("Event successfully removed");
		});	
	}

	function toggle_rsvps(id){
		$("#event_rsvps_"+id).toggle();
	}
})(jQuery);