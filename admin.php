<?php
/*
 * RSVP ME Pro admin functions
 */
global $wpdb;

// Hooks 
add_action('admin_menu', 'rsvp_me_menu');
add_action('admin_init', 'rsvp_me_register_admin_scripts');
add_action('admin_footer', 'rsvp_me_admin_footer');
add_action('wp_ajax_rsvp_me_update_settings', 'rsvp_me_update_settings');

/**
 * Register/enqueue the admin specific scripts & styles
 *
 * @since: 0.5
 * @return void
 * @param null
 */
function rsvp_me_register_admin_scripts(){
	wp_enqueue_script('jquery');
	wp_enqueue_style("jquery-ui-css", RSVP_ME_PLUGIN_URI . "/js/jquery-ui.css");
	wp_enqueue_script("rsvp-admin", RSVP_ME_PLUGIN_URI . "/js/admin.js", "jquery", null, true);
}

function rsvp_me_admin_footer(){ ?>
	<script type="text/javascript" src="<?php echo RSVP_ME_PLUGIN_URI . "/js/jquery-ui.js" ?>"></script>

	<script type="text/javascript">

	(function($){
		
		$.datepicker.setDefaults({
			  showOn: "both",
			  buttonImageOnly: true,
			  buttonImage: "<?php echo RSVP_ME_PLUGIN_URI; ?>/images/calendar.png",
			  buttonText: "Calendar"
			});
		$(".datepicker").datepicker();
		
	})(jQuery);

	</script>
	<?php
}

/**
 * Register the Top Level menu
 *
 * @since: 0.5
 * @return void
 * @param null
 */
function rsvp_me_menu() {  
	$top_menu_slug = "rsvp_events_overview";
	add_menu_page('RSVP ME', 'RSVP ME', 'manage_options', $top_menu_slug, 'rsvp_me_settings', plugins_url('rsvp-me/images/red-pen.png'));
}

function rsvp_me_update_settings(){

}

function rsvp_me_settings(){ ?>
  <h2>RSVP ME Settings</h2>
  <p><strong>Events Page URL:</strong> <?php echo bloginfo("siteurl") ?>/events ( <em>Permalinks must be set</em> )</p>
  <p>
  	<input type="checkbox" name="rsvp_me_email_notify" /> <label for="rsvp_me_email_notify">Notify me when someone RSVPs</label>
  </p>
	<p>
  	<input type="checkbox" name="rsvpe_me_disable_css" /> <label for="rsvpe_me_disable_css">Disable default CSS?</label>
  </p>
<?php }

function rsvp_me_events_overview(){
	?>
	<div id='admin-wrapper'>
		<h1>RSVP ME Events Overview</h1>
			<?php
			global $wpdb;
			$sql = "SELECT * FROM " . $wpdb->prefix . "rsvp_me_events ORDER BY id DESC";
			$rows = $wpdb->get_results($sql);
			?>
      <span id='rsvp_ajax_msg'></span>
			<div id="admin-events-wrapper">	
				<table cellpadding="0" cellspacing="0">
					<tr>
						<th>Event title</th>
						<th>Venue</th>
						<th>Date & time</th>
	          <th>RSVPs</th>
						<th></th>
					</tr>
					<?php
					foreach($rows as $row){
					
						$rsvps = rsvp_me_get_respondents($row->id);					
						$rsvp_count = count($rsvps);
			
						echo "<tr id='eventrow_" . $row->id . "'>\n";
						echo "<td valign='top'>" . stripslashes($row->title) . "</td>\n";
						echo "<td valign='top'>" . stripslashes($row->venue) . "</td>\n";
						echo "<td valign='top'>" . date("F jS g:i a", strtotime($row->event_date_time)) . "</td>\n";
						echo "<td valign='top'><a href='Javascript: toggle_rsvps($row->id)'>" . $rsvp_count . ($rsvp_count > 1 ? " people rsvpd" : " person has rsvpd") . "!</a></td>\n";
						echo "<td><a href='?page=rsvp_me_edit_event&id=" . $row->id . "'>Edit</a> | ";
						echo "<a href='Javascript: rsvp_me_delete_event(" . $row->id . ")' onclick='confirm(\"Are you sure you want to permanently delete this event?\")'>Delete</a></td>\n";
						echo "</tr>\n";
						
						rsvp_me_build_event_rsvps($rsvps, $row->id);
						
						echo "<tr><td colspan='5'><div style='width:100%; height:2px; border-bottom: 1px solid #ccc'></div></td></tr>\n";
					}
					?>
				</table>
		</div>
	</div>
	<? 
}

function rsvp_me_build_event_rsvps($rsvps, $id){
	
	?>
	<tr class='event_rsvps' id='event_rsvps_<?= $id?>' style='display:none'>
        <td colspan='5'>
        <div>
        <table width='100%' cellpadding='5'>
        	<tr>
            	<th>Respondent</th>
                <th>Email</th>
                <th>Response</th>
                <th>Message</th>
                <th>Time of response</th>
            </tr>
        	<? $count = count($rsvps); for($i=0; $i < $count; $i++): ?>
                <tr>
                	<td><?= $rsvps[$i]['fname'] . " " . $rsvps[$i]['lname'] ?></td>
                    <td><?= $rsvps[$i]['email'] ?></td>
                    <td><?= $rsvps[$i]['response'] ?></td>
                    <td><?= $rsvps[$i]['msg'] ?></td>
                    <td><?= date("F jS g:i a", strtotime($rsvps[$i]['time_accepted'])) ?></td>
                </tr>
            <? endfor; ?>   
            
            <tr>
            	<td colspan="5">
                	<a href='Javascript: toggle_rsvps(<?= $id ?>)'>Close</a>
                </td>
            </tr>
        </table>
        </div>
        </td>
    </tr>
    <?
}

function rsvp_me_add_event(){
	
	global $wpdb;
	
	if($_POST){
		//declare post data vars
		foreach($_POST as $field => $value) ${$field} = $value;
		
		$date_time = $date . " " . ($meridian == "pm" ? ($hour + 12) : $hour) . ":" . $minute . ":00";			
		
		$wpdb->query( $wpdb->prepare( "
						INSERT INTO " . $wpdb->prefix . "rsvp_me_events
						( id, title, description, venue, address, city, state, zip, event_date_time )
						VALUES ( %d, %s, %s, %s, %s, %s, %s, %d, %s )", 
						array(NULL, $title, $description, $venue, $address, $city, $state, $zip, $date_time) 
						) 
					);												   
		
		echo "<h2>Event added successfully</h2>\n";
	}
	rsvp_me_event_form('add');
}

function rsvp_me_edit_event(){
	
	global $wpdb;
	
	$id = $wpdb->escape($_REQUEST['id']);
	
	if($_POST){
		//declare post data vars
		foreach($_POST as $field => $value) ${$field} = $value;			
		
		$date_time = $date . " " . ($meridian == "pm" ? ($hour += 12) : $hour) . ":" . $minute . ":00";			
		
		$wpdb->update( $wpdb->prefix . "rsvp_me_events", 
	  	array( 'title' => $title, 'description' => $description,
			  'venue' => $venue, 'address' => $address, 
			  'city' => $city, 'state' => $state,
			  'zip' => $zip, 'event_date_time' => $date_time ), 
	   	array( 'id' => $id )
	  );
		echo "<h2>Event edited successfully</h2>\n";
	}
	
	$sql = "SELECT * FROM " . $wpdb->prefix . "rsvp_me_events WHERE id='$id'";
	
	$eventdata = $wpdb->get_row($sql, 'ARRAY_A');
	
	rsvp_me_event_form('edit', $eventdata);

}

function rsvp_me_delete_event(){
	
	global $wpdb;
	$id = $wpdb->escape($_REQUEST['id']);
	$sql = "DELETE FROM " . $wpdb->prefix . "rsvp_me_events WHERE id='$id' LIMIT 1";
	$wpdb->query($sql);
	
	echo "<h2>Event successfully removed</h2>";
}
?>