<?php
/*
Plugin Name: RSVP Me!
Plugin URI: http://www.micahblu.com/products/rsvp-me
Description: A Robust RSVP plugin 
Version: 1.9.2
Author: Micah Blu
Author URI: http://www.micahblu.com
License: GPL2
*/

define('RSVP_ME_VERSION', '1.9.2');

define('RSVP_ME_FILE_PATH', dirname(__FILE__));

define('RSVP_ME_DIR_NAME', basename(RSVP_ME_FILE_PATH));

$siteurl = get_option('siteurl'); 

define('RSVP_ME_PLUGIN_URI', $siteurl . '/wp-content/plugins/rsvp-me');


include (RSVP_ME_FILE_PATH . "/includes/rsvpme_functions.php");
include (RSVP_ME_FILE_PATH . "/includes/rsvpme_widget.php");
include (RSVP_ME_FILE_PATH . "/includes/rsvpme_shortcodes.php");
include (RSVP_ME_FILE_PATH . "/includes/rsvpme_events_post_type.php");

register_activation_hook( __FILE__, 'rsvp_me_install' );

/*
* Admin Specific Actions
*/
if( is_admin() ){
	/* cms scripts */
	include_once (RSVP_ME_FILE_PATH . "/admin.php");
}

/**
 * add default styles 
 */
function add_styles() {
	wp_register_style("rsvpMeStyles", RSVP_ME_PLUGIN_URI . "/rsvpme.css");
	wp_enqueue_style("rsvpMeStyles");
}

add_action('wp_print_styles', 'add_styles');

function rsvp_me_scripts(){

	wp_enqueue_script("jquery");
	wp_enqueue_script("jquery-ui", RSVP_ME_PLUGIN_URI . "/js/jquery-ui.js", "jquery", null, true);
	wp_enqueue_script("lightbox", RSVP_ME_PLUGIN_URI . "/js/jquery.lightbox_me.js", "jquery", null, true);
	
	/* rsvm me scripts */
	wp_register_script("rsvp-me", RSVP_ME_PLUGIN_URI . "/js/rsvp_me.js", null, null, true);
	wp_enqueue_script("rsvp-me");

}
add_action('wp_head', 'rsvp_me_scripts');	

function rsvp_me_footer(){ ?>
  <script type='text/javascript'>

		var plugin_path = "<?php echo RSVP_ME_PLUGIN_URI ?>";
		//var ajaxurl = "<?php echo str_replace(get_site_url(), "", admin_url('admin-ajax.php')); ?>";
		var ajaxurl = "<?php echo admin_url('admin-ajax.php'); ?>";
	</script>
	<div id="event_form_wrapper" style="display:none">
		<?php include RSVP_ME_FILE_PATH . "/themes/default/event-overlay.html"; ?>
	</div>

	<div id="single_event_overview_tmpl" style="display:none">
		<?php include RSVP_ME_FILE_PATH . "/themes/default/events.html"; ?>
	</div>
  <?php
}
add_action("wp_footer", "rsvp_me_footer", 99);

/**
 * Ajax functions
 */
function rsvp_me_event_data() {
	global $wpdb; // this is how you get access to the database

	$id = $_POST['id'];
	//rsvpme_event_form(array("id" => $id));
	echo json_encode(get_rsvp_event_by_id($id));
}

/* Front-side Ajax Methods */ 
function update_calendar(){
	$year = isset($_GET['year']) ? $_GET['year'] : date("Y"); //default to current year
	$month = isset($_GET['month']) ? $_GET['month'] : date("n"); //default to current month
	
	//we'll need to grab events for this year/month
	$events = rsvp_me_get_events($month, $year);
	rsvp_me_draw_calendar($events, $month, $year);
}

function submit_rsvp(){
	global $wpdb;
	
	foreach($_REQUEST as $field => $value){
		${$field} = $wpdb->escape(urldecode($value));
	}

	//first let's check to see if this user has already responded
	$row = $wpdb->get_row("SELECT email FROM " . $wpdb->prefix . "rsvp_me_respondents WHERE email='$email' AND event_id='$event_id'", ARRAY_N);

	if(count($row) > 0) {
		echo json_encode(array("error" => "duplicate"));
	}
	else{
		$affected = $wpdb->query("INSERT INTO " . $wpdb->prefix . "rsvp_me_respondents
					  VALUES(NULL, '$event_id', '$fname', '$lname', '$email', '$response', '$msg', NOW())");
					  
		if($affected > 0) echo json_encode(array("success" => true));
		else echo json_encode(array("error" => "There was an error adding your RSVP"));
	}
}

// event form ajax function
add_action('wp_ajax_rsvp_me_event_data', 'rsvp_me_event_data');
add_action('wp_ajax_nopriv_rsvp_me_event_data', 'rsvp_me_event_data');

// submit rsvp ajax function
add_action('wp_ajax_nopriv_submit_rsvp', 'submit_rsvp');
add_action('wp_ajax_submit_rsvp', 'submit_rsvp');

// update calendar ajax function
add_action('wp_ajax_nopriv_update_calendar', 'update_calendar');
add_action('wp_ajax_update_calendar', 'update_calendar');
?>
