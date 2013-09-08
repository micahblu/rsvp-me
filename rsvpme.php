<?php
/*
Plugin Name: RSVP Me
Plugin URI: http://www.micahblu.com/
Description: Event Calendar that allows users to RSVP to the selected event.
Version: 1.0.1
Author: Micah Blu
Author URI: http://www.micahblu.com
License: GPL2
*/

define('RSVP_ME_VERSION', '1.0.1');

define('RSVP_ME_FILE_PATH', dirname(__FILE__));

define('RSVP_ME_DIR_NAME', basename(RSVP_ME_FILE_PATH));

$siteurl = get_option('siteurl'); 

define('PLUGIN_PATH', $siteurl . '/wp-content/plugins/rsvp-me');

include_once (RSVP_ME_FILE_PATH . "/includes/rsvpme_functions.php");

register_activation_hook( __FILE__, 'rsvp_me_install' );

//create a sidebar widget for the event calendar
wp_register_sidebar_widget('rsvp_me_calendar', 'RSVP ME Calendar', 'rsvp_me_calendar_widget', array( 'option' => 'value' ) );

/* Append needed elements and includes to the header */
add_action('wp_print_styles', 'add_styles');

function add_styles() {

	wp_register_style("rsvpMeStyles", PLUGIN_PATH . "/rsvpme.css");
	
	wp_enqueue_style("rsvpMeStyles");

}

function rsvp_init_header(){

	wp_enqueue_script("jquery");

	wp_enqueue_script("thickbox");

	wp_enqueue_style("thickbox");
	
	/* rsvm me scripts */
	
	wp_register_script("rsvpMe", PLUGIN_PATH . "/js/rsvp_me.js");
	wp_enqueue_script("rsvpMe");
	
	wp_register_script("rsvpMeAjax", PLUGIN_PATH . "/js/ajax.js");
	wp_enqueue_script("rsvpMeAjax");
	
	wp_register_script("rsvpMeCookie", PLUGIN_PATH . "/js/Cookie.js");
	wp_enqueue_script("rsvpMeCookie");
}

	
function add_to_header(){
	//add neccessarry scripts & styles
	?>
    <script type='text/javascript'>
		
		var $ = jQuery;
		
		var plugin_path = "<?= PLUGIN_PATH ?>";
		
		var ajaxurl = "<?= admin_url('admin-ajax.php'); ?>";
		
		var rsvpCookie; //put our cookie var in the main scope
		
		$(document).ready(function(){
			
			//init our cookie
			rsvpCookie = new Cookie("visitordata");
			
		});
		
	</script>
    <?
}


/*
* Admin Specific Actions
*/
if( is_admin() ){
	/* cms scripts */
	include_once (RSVP_ME_FILE_PATH . "/admin.php");
	
	/*  !! we must include the wp_ajax actions for the front end here!!   */
	add_action('wp_ajax_nopriv_submit_rsvp', 'submit_rsvp');
	
	add_action('wp_ajax_submit_rsvp', 'submit_rsvp');
	
	add_action('wp_ajax_nopriv_update_calendar', 'update_calendar');
	
	add_action('wp_ajax_update_calendar', 'update_calendar');
	
}else{
	
	/* web visitor scripts */
	add_action('wp_head', 'add_to_header');	
	
	add_action('init', 'rsvp_init_header');
	
}

/* Front-side Ajax Methods */
 
function update_calendar(){

	rsvp_me_draw_calendar(NULL, $_GET['month'], $_GET['year']);
	echo "|"; //place the bar to separate our response from wordpress's

}

function submit_rsvp(){
	
	global $wpdb;
	
	foreach($_REQUEST as $field => $value){
		${$field} = $wpdb->escape(urldecode($value));
	}
	
	//first let's check to see if this user has already responded
	$row = $wpdb->get_row("SELECT email FROM " . $wpdb->prefix . "rsvp_me_respondents WHERE email='$email' AND event_id='$event_id'", ARRAY_N);

	if(count($row) > 0) {
		echo "error=duplicate|";
	}
	else{
		$wpdb->query("INSERT INTO " . $wpdb->prefix . "rsvp_me_respondents
					  VALUES(NULL, '$event_id', '$fname', '$lname', '$email', '$response', '$msg', NOW())");
		
		echo "success|";
	}
	
	//return true;	
}
?>
