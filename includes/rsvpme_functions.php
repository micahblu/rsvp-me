<?php
/**
 * RSVP ME functions 
 * Author: Micah Blu
 * 
 * Meat & Potatos, most of the functions that make this work :)
 */

function rsvp_me_install(){

	global $wpdb;
	
	$table_prefix = $wpdb->prefix . "rsvp_me_";
	
	$tables = array(
		"settings" 		 => $table_prefix . "settings",
		"events"			 => $table_prefix . "events",
		"respondents"  => $table_prefix . "respondents"
	);
	
	//require_once(ABSPATH . 'wp-admin/includes/upgrade.php'); //required for dbDelta()
	
	//settings table
	if($wpdb->get_var("show tables like '" . $tables["settings"] ."'") != $tables["settings"] ) {
	
		$sql = "CREATE TABLE " . $tables["settings"] . " (
		id mediumint(9) NOT NULL AUTO_INCREMENT,
		version varchar(255) NOT NULL,
		license varchar(255) NOT NULL,
		UNIQUE KEY id (id)
		);";
		
		$wpdb->query($sql);
		
		$rows_affected = $wpdb->insert( $table_prefix . "settings", array( 'version' => RSVP_ME_VERSION, 'license' => 'free version' ) );
	}
	
	//events table
	if($wpdb->get_var("show tables like '" . $tables["events"] . "'") != $tables["events"] ) {
	
		$sql = "CREATE TABLE " . $tables["events"] . " (
		id mediumint(9) NOT NULL AUTO_INCREMENT,
		title varchar(255) NOT NULL,
		description text NOT NULL,
		venue varchar(255) NOT NULL,
		address varchar(255) NOT NULL,
		city varchar(255) NOT NULL,
		state varchar(3) NOT NULL,
		zip char(5) NOT NULL,
		event_date_time datetime NOT NULL,
		UNIQUE KEY id (id)
		);";
		
		$wpdb->query($sql);
		
	}

	//respondents table
	if($wpdb->get_var("show tables like '" . $tables["respondents"] . "'") != $tables["settings"] ) {
	
		$sql = "CREATE TABLE " . $tables["respondents"] . " (
		id mediumint(9) NOT NULL AUTO_INCREMENT,
		event_id mediumint(9) NOT NULL,
		fname varchar(255) NOT NULL,
		lname varchar(255) NOT NULL,
		email varchar(255) NOT NULL,
		response enum('accepted', 'declined', 'maybe') NOT NULL,
		msg mediumtext NULL,
		time_accepted datetime NOT NULL,
		UNIQUE KEY id (id)
		);";
				
		$wpdb->query($sql);
		
	}

}

function build_rsvp_form($event){

	?>
    <div id="rsvp_form_<?= $event['id'] ?>" class="rsvp-form" style='display:none'>
    	<h2 id="rsvpEventTitle"><?= stripslashes($event['title']) ?></h2>
        <p><?= stripslashes($event['description']) ?></p>
        
      	<br />
        <h3>Venue: <?= stripslashes($event['venue']) ?></h3>
    
        <?= $event['address'] . "<br />" . $event['city'] . ", " . $event['state'] . " " . $event['zip'] ?> 
        
        
        <br />
        
        <h3>Are you coming? Then RSVP below!</h3>
        <br />
        <form id="rsvp_form_<?= $event['id'] ?>" action="" method="" onsubmit="return rsvpMe.submitRsvp(<?= $event['id'] ?>)">
        
        <input type='hidden' name='event_id' value='<?= $event['id'] ?>' />
        
    	 <table cellpadding="5" cellspacing="0" border="0">
        
        	<tr>
            	<td>First name</td><td><input type='text'	 name='fname' value='' /></td>
           </tr>
           <tr>
           		<td>Last name</td><td><input type='text' name='lname' value='' /></td>
           </tr>
           <tr>
           		<td>Email</td><td><input type='text' name='email' value='' /></td>
           </tr>
           <tr>
           		<td colspan="2">
               
                  <input type='radio' name='response' value='accepted' /> I'm Definitely coming! <br />
                  <input type='radio' name='response' value='maybe' /> I might come. <br />
                  <input type='radio' name='response' value='declined' /> Sorry can't make it. <br />
                </td>
           </tr>
           <tr>
           		<td colspan="2">
                Want to send an additional message?<br />
                <textarea name='msg' style="width:300px; height:75px"></textarea>
                </td>
           </tr>
           <tr>
           	<td colspan="2"><span id='submit_cancel_<?= $event['id'] ?>'><input type='submit' name='submit' value='RSVP Me' /> or <a href="Javascript: rsvpMe.cancel()">Cancel</a></span></td>
           </tr>
        </table>
      	</form>
    </div>
    <?
}

function get_rsvp_by_id($id){
	global $wpdb;
		
	$event = $wpdb->get_results("SELECT *, DATE(event_date_time) AS ymd FROM " . $wpdb->prefix . "rsvp_me_events 
								   WHERE id='$id'", ARRAY_A);
	return $event[0];
}

function rsvp_me_get_events($month, $year){
	global $wpdb;
	
	$events = array(); //events array that will be returned with ymd date as key
	
	$rows = $wpdb->get_results("SELECT *, DATE(event_date_time) AS ymd FROM " . $wpdb->prefix . "rsvp_me_events 
								   WHERE MONTH(event_date_time) = '$month'
								   AND YEAR(event_date_time) = '$year'; ", ARRAY_A);
	foreach($rows as $row)
	{
	
		if(!isset($events[$row['ymd']]))
		{
			$events[$row['ymd']] = array();
		}
		// we use an array here to account for possibility of multiple events for this day
		// added 4-23-12 per recommendation by Vegard Kamben of Norway!
		array_push($events[$row['ymd']], $row);
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
	?>
	<div id='rsvp_me_calendar_widget'>   
		<? rsvp_me_draw_calendar(NULL) ?>
	</div><!-- #rsvp_me_calendar_widget -->
	<?
}

function rsvp_me_draw_calendar($obj, $month=NULL, $year=NULL, $settings=NULL){
	
	/* 	
	Changelog:
		-added div wrapper
		-added months array and header
		-added settings array that can be passed to allow manipulation of basic calendar settings like classname & day headers
		-added defaults for month/date/settings
	
	Notes:
	/$obj needed as wordpress passes the first parameter
	
	*/
	
	$year = $year ? $year : date("Y"); //default to current year
	$month = $month ? $month : date("n"); //default to current month
	
	//we'll need to grab events for this year/month
	$events = rsvp_me_get_events($month, $year);
	
	if(!$settings){
		//set the default settings
		$settings = array("class" => "rsvp_me_calendar",
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
		
		if($hasMultipleEvents)
		{
			//build array of id's/titles to be used by showMultipleEvents
			$jsObjects = array(); 
			foreach($events[$current_ymd] as $event)
			{
				$jsObjects[] = "{ id : " . $event["id"] . ", title : '" . $event["title"] . "' }";
			}
			$td_action = 'onclick="rsvpMe.showMultipleEvents([' . implode(",", $jsObjects) . '])"';	
			$calendar .= '<td class="' . ($is_today ? 'calendar-today' : 'calendar-day') . ' ' . "multi-event-day" . '" ' . $td_action .'>';
		}else
		{
			$td_action = isset($events[$current_ymd]) ? 'onclick="rsvpMe.showEvent(' . $events[$current_ymd][0]['id'] . ')"' : '';
			$calendar.= '<td class="' . ($is_today ? 'calendar-today' : 'calendar-day') . ' ' . (isset($events[$current_ymd]) ? "event-day" : "") . '" ' . $td_action .'>';
		}
		
		
		/** check for events !! 
		if(isset($events[$current_ymd]))
		{
			if($hasMultipleEvents)
			{
				foreach($events[$current_ymd] as $event)
				{
					build_rsvp_form($event);
				}
			}
			else
			{
				build_rsvp_form($events[$current_ymd][0]);
			}
		}**/
		//die();
		
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
	
	$select .= "</option>\n";
	
	return $select;
}

function rsvp_me_event_form($handle, $event=NULL){
	
	if($event){
		$timestamp = strtotime($event['event_date_time']);
		$date = date("Y-m-d", $timestamp);
		$hour = date("h", $timestamp);
		$minute = date("i", $timestamp); 
		$meridian = date("a", $timestamp);
	}	
	?>
	<div id='admin-wrapper'>
        <h1><?=ucfirst($handle)?> Event</h1>
	   
		<form action="" method="post" name="">
		
        <?php echo $handle=='edit' ? "<input type='hidden' name='id' value='" . $event["id"] ."' />\n" : "" ?>
        
		<div class='form-segments'>
		<p>What's the event?</p>
  
		<table cellpadding="10" cellpadding="5">
			<tr>
				<td>Event title</td><td><input type='text' id='text' name='title' value='<?php if(isset($event['title'])) echo stripslashes($event['title'])?>' /></td>
			</tr>
			<tr>
				<td>Event description</td>
				<td><textarea name='description' id='description' style='width:275px; height:75px'><?php if(isset($event['description'])) echo stripslashes($event['description'])?></textarea></td>
			</tr>
		</table>
		</div>
		
		<div class='form-segments'>
		<p>When does it occur?</p>
		<table cellpadding="10" cellpadding="5">
			<tr>
				<td>
				Date<br />
				<input type="text" onclick="cal.appendCalendar(this, '400', '300', '<?php echo PLUGIN_PATH ?>')" name="date" readonly="readonly" size='10' maxlength="10" value="<?php if(isset($date)) echo $date ?>" title="calfield" class='reqd' />
				</td>
			
				<td>
				Hour<br />
				<select name='hour'>
					<?
					for($i=1; $i < 13; $i++){
						$h = ($i < 10 ? "0" . $i : $i);
						echo "<option value='$h' " . ($hour == $h ? "selected='selected'" : "") . ">$h</option>\n";
					} 
					?>
				</select>
				
				</td>
				<td>
				Minute<br />
				<select name='minute'>
					<?
					for($i=0; $i < 61; $i++){
						$min = ($i < 10 ? "0" . $i : $i);
						echo "<option value='$min' " . ($minute == $min ? "selected='selected'" : "") . ">$min</option>\n";
					} 
					?>
				</select>
			  
				</td>
				<td>
				&nbsp;<br />
				<select name='meridian'>
			   
				  <option value='am' <?php echo ( isset($meridian) && $meridian == "am") ? "selected='selected'" : "" ?>>AM</option>
				  <option value='pm' <?php echo ( isset($meridian) && $meridian == "pm") ? "selected='selected'" : "" ?>>PM</option>
				
				 </select>
				</td>
			</tr>
			
		</table>
		</div>
		
		<div class='form-segments'>
		<p>Where's it at?</p>
        
		<table cellpadding="10" cellpadding="5">
			
			<tr>
				<td>Venue</td><td><input type='text' id='venue' name='venue' value='<?=stripslashes($event['venue'])?>' /></td>
			</tr>
			
			<tr>
				<td>Address</td><td><textarea name='address' id='address'><?=$event['address']?></textarea></td>
			</tr>
			
			<tr>
				<td>City</td><td><input type='text' name='city' id='city' value='<?=$event['city']?>' /></td>
			</tr>
			
			<tr>
				<td>State</td><td><?= select_state($event['state']) ?></td>
			</tr>
			
			<tr>
				<td>Zip</td><td><input type='text' name='zip' size='5' maxlength="5" value='<?=$event['zip']?>' /></td>
			</tr>    
			
			
		</table>
		</div>
		
	  
		<p><input type='submit' name='submit' value='Submit' /></p>
	   
		</form>
		
	</div>
	<? 
}
?>