<?php
/**
 * RSVP ME Pro Shortcodes
 *
 * @author: Micah Blu
 * @since: 1.1.0
 * @uses FooManChu
 */


/**
 * Shortcode function that displays an event rsvp
 * 
 * @param null
 * @return null
 */
function rsvpme_event_form( $atts ){
	
	extract( shortcode_atts( array(
		'id' => null
	), $atts ) );

	if(isset($id)){ 
		$event = get_rsvp_event_by_id($id);
		$template = file_get_contents(RSVP_ME_FILE_PATH . "/themes/default/event.fmc");
		$foomanchu->render($template, $rsvp_me);
	}
}

add_shortcode("rsvp_event", "rsvpme_event_form");
?>