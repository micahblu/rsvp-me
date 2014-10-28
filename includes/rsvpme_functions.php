<?php
/**
 * RSVP ME functions 
 *
 * @author: Micah Blu
 * @since: 0.5
 */

function rsvp_me_install(){

	global $wpdb;
	
	$table_prefix = $wpdb->prefix . "rsvp_me_";

	require_once(ABSPATH . 'wp-admin/includes/upgrade.php'); //required for dbDelta()
	
	//respondents table
	if($wpdb->get_var("show tables like '" . $table_prefix . "respondents" . "'") != $table_prefix . "respondents" ) {
	
		$sql = "CREATE TABLE " . $table_prefix . "respondents (
			id INT NOT NULL AUTO_INCREMENT,
			event_id INT NOT NULL,
			fname varchar(255) NOT NULL,
			lname varchar(255) NOT NULL,
			email varchar(255) NOT NULL,
			response enum('accepted', 'declined', 'maybe') NOT NULL,
			msg mediumtext NULL,
			time_accepted DATETIME NOT NULL,
			UNIQUE KEY id (id),
			PRIMARY KEY (id)
		);";
				
		$affected = $wpdb->query($sql);
	}
	//set a temporary activeated_plugin option to be refereneced for after registration specific actions
	//add_option('Activated_Plugin', 'rsvp-me');
}

function get_rsvp_event_by_id($id){
	global $wpdb;
	//prepare an event array and return, start with basic event post info
	$eventpost = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "posts WHERE ID=$id", ARRAY_A);
	
	if(count($eventpost) < 1) return array("error" => "Empty result");

	$event = array();
	$event['id'] = $eventpost[0]['ID'];
	$event['title'] = $eventpost[0]['post_title'];
	$event['description'] = $eventpost[0]['post_content'];
	$event['link'] = $eventpost[0]['guid'];

	// now add our post meta data
	$meta = $wpdb->get_results("SELECT * FROM ". $wpdb->prefix . "postmeta WHERE post_id=" . $id, ARRAY_A);

	// /die(print_r($meta));
	$ns = "/_rsvp_me_event_/";
	$str = '';
	
	foreach($meta as $field => $value){
		//let's only match the meta key values with said namespace
		if(preg_match($ns, $value['meta_key'])){
			$event[preg_replace($ns, "" , $value['meta_key'])] = $value['meta_value'];
		}
	}

	// format our time
	if( isset($event["hour"]) && isset($event["minute"]) && isset($event["meridian"]) ){
		$event["time"] = $event["hour"] . ":" . $event["minute"] . $event["meridian"];
	}

	// add a ymd version of the date, which is used by the calendar widget
	if(isset($event["date"])){

		$date = explode("/", $event['date']); // MM/DD/YYYY

		// event matches.. we'll store by its y-m-d
		$event["date_ymd"] = $date[2] . "-" . $date[0] . "-" . $date[1]; // this event machtes!
	}

	$event["featured_image"] = get_the_post_thumbnail($id);
	$image_array = wp_get_attachment_image_src( get_post_thumbnail_id($id) );
	if(!empty($image_array)){
		$event["featured_image_src"] = $image_array[0];
	}

	// add custom event labels
	$event['accept_response'] = stripslashes(get_option("_rsvp_me_accept_response"));
	$event['maybe_response'] = stripslashes(get_option("_rsvp_me_maybe_response"));
	$event['decline_response'] = stripslashes(get_option("_rsvp_me_decline_response"));

	if($event['maybe_response'] != "") $event['showMaybeResponse'] = true;
	if($event['decline_response'] != "") $event['showDeclineResponse'] = true;
	
	return $event;
}

function rsvp_me_get_events($month, $year){
	global $wpdb;
	
	$events = array();
	//first grab our events
	$eventrows = $wpdb->get_results("SELECT ID, post_title FROM " . $wpdb->prefix . "posts 
																	 WHERE post_type='event'
																	AND post_status='publish'");

	// now our event meta.. specifically the date
	foreach($eventrows as $event){

		$cur_event = get_rsvp_event_by_id($event->ID);

		$ymd = explode("-", $cur_event["date_ymd"]); // YYYY-MM-DD

		if($ymd[0] == $year && ltrim($ymd[1]) == $month){
			// this event matches the incoming year/month.. add to events array with ymd as key
			$events[$cur_event['date_ymd']][] = $cur_event;
		}
	}
	return $events;
}

function rsvp_me_get_respondents($id){
	global $wpdb;
			
	$rows = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "rsvp_me_respondents
								WHERE event_id = '$id'", ARRAY_A);

	return $rows;
}

function rsvp_me_calendar_widget($options = array()){
	/* output calendar widget */
	$year = isset($_GET['year']) ? $_GET['year'] : date("Y"); //default to current year
	$month = isset($_GET['month']) ? $_GET['month'] : date("n"); //default to current month
	
	//we'll need to grab events for this year/month
	$events = rsvp_me_get_events($month, $year);
	
	?>
	<div id='rsvp_me_calendar_widget'>   
		<?php rsvp_me_draw_calendar($events, $month, $year); ?>
	</div><!-- #rsvp_me_calendar_widget -->
	<?php
}

function get_rsvp_me_options(){

	// Response Label options
	$response_options = array(
		'heading' => 'Response Labels',
		'section' => 'response_labels',
		'fields' =>	array(
			array(
				'name' => 'Show Maybe Response',
				'desc' => '',
				'id' => 'rsvp_me_show_maybe_response',
				'default' => 'checked',
				'type' => 'checkbox'
			),
		  array(
				'name' => 'Accept Response Label',
				'desc' => '',
				'id' => 'rsvp_me_accept_response',
				'default' => "I'm Definitely coming!",
				'type' => 'text'
			),
			array(
				'name' => 'Maybe Response Label',
				'desc' => '',
				'id' => 'rsvp_me_maybe_response',
				'default' => "I might come",
				'type' => 'text'
			),
			array(
				'name' => 'Decline Response Label',
				'desc' => '',
				'id' => 'rsvp_me_decline_response',
				'default' => "Sorry Can't make it",
				'type' => 'text'
			)
		)
	);

	// Calendar options
	$calendar_options = array(
		'heading' => 'Manage Calendar Styles',
		'section' => 'calendar',
		'fields' =>	array(
			array(
				'name' => 'Cell Background',
				'desc' => '',
				'id' => 'rsvp_me_table_cell_bg',
				'default' => '#ffffff',
				'type' => 'color'
			),
			array(
				'name' => 'Border Color',
				'desc' => '',
				'id' => 'rsvp_me_table_border_color',
				'default' => '#cccccc',
				'type' => 'color'
			),
			array(
				'name' => 'Font Color',
				'desc' => '',
				'id' => 'rsvp_me_table_cell_color',
				'default' => '#333333',
				'type' => 'color'
			),
			array(
				'name' => 'Event Day Background',
				'desc' => '',
				'id' => 'rsvp_me_table_event_bg',
				'default' => '#ffffcc',
				'type' => 'color'
			)
		)
	);

	$options[] = $response_options;
	$options[] = $calendar_options;

	// load values or set defaults
	for($i=0; $i < count($options); $i++){
		for($j = 0; $j < count($options[$i]['fields']); $j++){			
			$options[$i]['fields'][$j]['value'] = stripslashes(get_option("_" . $options[$i]['fields'][$j]['id']));
		}
	}
	return $options;
}

function get_rsvp_me_calendar_settings(){
	$options = get_rsvp_me_options();
	$nv_pairs = array(); //name value pairs
	for($i=0; $i < count($options); $i ++){
		if($options[$i]['section'] !== 'calendar') continue;

		foreach($options[$i]['fields'] as $key => $field){
			$nv_pairs[$field['id']] = $field['value'];
		}
	}
	return $nv_pairs;
}

function rsvp_me_calendar_styles(){ 
	$settings = get_rsvp_me_calendar_settings();
	?>
	<style>

		#rsvp_me_event_calendar{
			width: 100%;
		}

		#rsvp_calendar_head{
			text-align: center;
			width: 100%;
		}

		#rsvp_me_event_calendar .prev{
			float:left;
		}

		#rsvp_me_event_calendar .next{
			float:right;
		}

		#rsvp_me_event_calendar table{
			border-collapse: collapse;
		}

		#rsvp_me_event_calendar table tr{

		}

		#rsvp_me_event_calendar table tr th{
			text-align: center;
		}	

		#rsvp_me_event_calendar table tr td{
			padding: 5px;
			border-style: solid;
			border-width: 1px;
			border-color: <?php echo $settings["rsvp_me_table_border_color"]; ?>;
			text-align: center;
			color: <?php echo $settings["rsvp_me_table_cell_color"]; ?>;
			background-color: <?php echo $settings["rsvp_me_table_cell_bg"]; ?>;
		}

		#rsvp_me_event_calendar table tr td.event-day{
			background-color: <?php echo $settings["rsvp_me_table_event_bg"]; ?>;
		}

		#rsvp_me_event_calendar table tr td.calendar-today{
			background-color: #ffffcc;
		}
		</style>
<?php }

function rsvp_me_draw_calendar($events=NULL, $month=NULL, $year=NULL, $settings=NULL){
	/**
	 * Changelog:
	 * -added div wrapper
	 * -added months array and header
	 * -added settings array that can be passed to allow manipulation of basic calendar settings like classname & day headers
	 * -added defaults for month/date/settings
     *
 	 * Notes:
	 * $obj needed as wordpress passes the first parameter
	*/

	$year = $year ? $year : date("Y"); //default to current year
	$month = $month ? $month : date("n"); //default to current month
	
	if(!$settings){
		//set the default settings
		$settings = array(
			"class" => "rsvp_me_calendar",
	    	"dayhead" => "short"
	  	);
	}
	
	$months = array(1=>"January", 2=>"February", 3=>"March", 4=>"April", 5=>"May", 6=>"June", 
					7=>"July", 8=>"August", 9=>"September", 10=>"October", 11=>"November", 12=>"December");

	/* draw table */
	$calendar = '<div id="rsvp_me_event_calendar" class="' . $settings["class"] . '">';
	$calendar .= '<div id="rsvp_calendar_head"> <a class="prev" href="Javascript: rsvpMe.prevMonth()"> < </a> <strong id="rsvp_calendar_month">' . $months[$month] . ' ' . $year . '</strong> <a href="Javascript: rsvpMe.nextMonth()" class="next"> > </div></a>';
	$calendar .= '<table cellpadding="0" cellspacing="0">';

	/* table headings */
	$headings = array( "full" => array('Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'),
					   "medium" => array('Sun', 'Mon', 'Tue', 'Web', 'Thr', 'Fri', 'Sat'),
					   "short" => array('S', 'M', 'T', 'W', 'T', 'F', 'S') );
	
	$calendar.= '<tr class="calendar-row"><th class="calendar-day-head">'.implode('</th><th class="calendar-day-head">', $headings[$settings["dayhead"]]).'</th></tr>';

	/* days and weeks vars now ... */
	$running_day = date('w',mktime(0,0,0,$month,1,$year));
	$days_in_month = date('t',mktime(0,0,0,$month,1,$year));
	$days_in_this_week = 1;
	$day_counter = 0;
	$dates_array = array();
	
	$today = date("Y-m-d");

	/* row for week one */
	$calendar.= '<tr class="calendar-row">';

	/* print "blank" days until the first of the current week */
	for($x = 0; $x < $running_day; $x++):
		$calendar.= '<td class="calendar-day-np">&nbsp;</td>';
		$days_in_this_week++;
	endfor;

	/* keep going with days.... */
	for($list_day = 1; $list_day <= $days_in_month; $list_day++)
	{
		//assess current ymd
		$current_ymd = $year . "-" . ($month < 10 ? "0" . $month : $month) . "-" . ($list_day < 10 && strlen($list_day) < 2 ? "0" . $list_day : $list_day);
		
		$is_today = $today == $current_ymd ? true : false;
		
		// determine if there are is just one event for this day or multiple
		$hasMultipleEvents = isset($events[$current_ymd]) && count($events[$current_ymd]) > 1 ? true : false;
		
		//build array of id's/titles to be used by showMultipleEvents
		if($hasMultipleEvents){
			
			foreach($events[$current_ymd] as $field => $event){
				$json[] = array2JSON($event, array("featured_image"));
			}

			$td_action = 'onclick="rsvpMe.showMultipleEvents([' . implode(",", $json) . '])"';	
			$calendar .= '<td class="' . ($is_today ? 'calendar-today' : 'calendar-day') . ' ' . "multi-event-day" . '" ' . $td_action .'>';
			
		}else
		{	
			if(isset($events[$current_ymd][0])){

				//echo "<h1>" . print_r($events[$current_ymd],true). " is set!</h1>";

				$json = array2JSON($events[$current_ymd][0], array("featured_image"));	

				$td_action = isset($events[$current_ymd]) ? 'onclick="rsvpMe.showEvent(' . $json . ')"' : '';
			}
			$calendar.= '<td class="' . ($is_today ? 'calendar-today' : 'calendar-day') . ' ' . (isset($events[$current_ymd]) ? "event-day" : "") . '" ' . (isset($td_action) ? $td_action : '') .'>';
		}
		
		$td_action = ''; // empty action as default for next iteration

		/* add in the day number */
		$calendar.= '<div class="day-number">'.$list_day.'</div>';
		$calendar.= '</td>';
		
		if($running_day == 6)
		{
			
			$calendar.= '</tr>';
			
			if(($day_counter+1) != $days_in_month)
			{
				$calendar.= '<tr class="calendar-row">';
			}
			
			$running_day = -1;
			$days_in_this_week = 0;
		}
		
		$days_in_this_week++; $running_day++; $day_counter++;
		
	}

	/* finish the rest of the days in the week */
	if($days_in_this_week < 8):
		for($x = 1; $x <= (8 - $days_in_this_week); $x++):
			$calendar.= '<td class="calendar-day-np">&nbsp;</td>';
		endfor;
	endif;

	/* final row */
	$calendar.= '</tr>';

	/* end the table */
	$calendar.= '</table>';
	
	/* close wrapping div */
	$calendar .= '</div>'; 
	
	/* all done, return result */
	rsvp_me_calendar_styles();
	echo $calendar;
}

function select_state($default=NULL, $field_name='state'){
	
	$state_list = array(
		'AL'=>"Alabama",  
		'AK'=>"Alaska",  
		'AZ'=>"Arizona",  
		'AR'=>"Arkansas",  
		'CA'=>"California",  
		'CO'=>"Colorado",  
		'CT'=>"Connecticut",  
		'DE'=>"Delaware",  
		'DC'=>"District Of Columbia",  
		'FL'=>"Florida",  
		'GA'=>"Georgia",  
		'HI'=>"Hawaii",  
		'ID'=>"Idaho",  
		'IL'=>"Illinois",  
		'IN'=>"Indiana",  
		'IA'=>"Iowa",  
		'KS'=>"Kansas",  
		'KY'=>"Kentucky",  
		'LA'=>"Louisiana",  
		'ME'=>"Maine",  
		'MD'=>"Maryland",  
		'MA'=>"Massachusetts",  
		'MI'=>"Michigan",  
		'MN'=>"Minnesota",  
		'MS'=>"Mississippi",  
		'MO'=>"Missouri",  
		'MT'=>"Montana",
		'NE'=>"Nebraska",
		'NV'=>"Nevada",
		'NH'=>"New Hampshire",
		'NJ'=>"New Jersey",
		'NM'=>"New Mexico",
		'NY'=>"New York",
		'NC'=>"North Carolina",
		'ND'=>"North Dakota",
		'OH'=>"Ohio",  
		'OK'=>"Oklahoma",  
		'OR'=>"Oregon",  
		'PA'=>"Pennsylvania",  
		'RI'=>"Rhode Island",  
		'SC'=>"South Carolina",  
		'SD'=>"South Dakota",
		'TN'=>"Tennessee",  
		'TX'=>"Texas",  
		'UT'=>"Utah",  
		'VT'=>"Vermont",  
		'VA'=>"Virginia",  
		'WA'=>"Washington",  
		'WV'=>"West Virginia",  
		'WI'=>"Wisconsin",  
		'WY'=>"Wyoming");
		
	$select = "<select name='$field_name' class='req'>\n";
	$select .= "<option value=''>Select A State</option>\n";
	
	foreach($state_list as $value => $name){
		if(strtolower($default) == strtolower($value) || strtolower($default) == strtolower($name))
			$select .= "<option value='" . $value . "' selected='selected'>" . $name . "</option>\n";
		else
			$select .= "<option value='" . $value . "'>" . $name . "</option>\n";
	}
	$select .= "</select>\n";
	
	return $select;
}

function buildTemplateFromValues($templ, $values, $echo=true){
	if(!file_exists($templ) || empty($values)){
		return false;
	}

	ob_start();
	include $templ;
	$form = ob_get_contents();
	ob_end_clean();

	foreach($values as $field => $value){
		$form = str_replace("{:" . $field . "}", $value, $form);
	}
	if($echo) echo stripslashes($form);
	else return $form;
}

function array2JSON($inArray, $skipkeys=array()){
	
	if(!is_array($inArray)) return;

	foreach($inArray as $field => $value){
		//echo $field . " = " . $value . "<br />";
		if(!is_array($value) && !in_array($field, $skipkeys)) {
			$fields[] = $field . " : '" . addslashes($value) . "'";
		}
	}

	if(is_array($fields)) return "{" . implode(",", $fields) . "}";
	else return;
}


function prettyprint($array, $echo=true){
	$str = '';
	$str = preg_replace("/\n/", "<br />", print_r($array, true));
	$str = preg_replace("/\t/", "&nbsp;&nbsp;", $str);
	if($echo) echo $str;
	else return $str;
	return $str;
}
?>