<?php
/**
 * RSVP ME Pro Shortcodes
 *
 * @author: Micah Blu
 * @since: 1.1.0
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
		ob_start();
		include RSVP_ME_FILE_PATH . "/themes/default/event.html";
		$form = ob_get_contents();
		ob_end_clean();

		foreach($event as $field => $value){
			$form = str_replace("{:" . $field . "}", $value, $form);
		}
		echo "<h2>" . $event['title'] . "</h2>\n";
		echo $event['featured_image'];
		echo stripslashes($form);
	}
}

add_shortcode("rsvp_event", "rsvpme_event_form");
?>