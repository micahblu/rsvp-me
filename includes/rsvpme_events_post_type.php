<?php

add_action("after_theme_setup", "rsvp_me_theme_support");

function rsvp_me_theme_support(){
	add_theme_support('post-thumbnails',  array('event'));
}

if( ! function_exists( 'event_create_post_type' ) ) :

	function event_create_post_type() {
		$labels = array(
			'name' => __( 'RSVP Events' ),
			'singular_name' => __( 'RSVP Event' ),
			'add_new' => __( 'Add event' ),
			'all_items' => __( 'All events' ),
			'add_new_item' => __( 'Add event' ),
			'edit_item' => __( 'Edit event' ),
			'new_item' => __( 'New event' ),
			'view_item' => __( 'View event' ),
			'search_items' => __( 'Search events' ),
			'not_found' => __( 'No events found' ),
			'not_found_in_trash' => __( 'No events found in trash' ),
			'parent_item_colon' => __( 'Parent event' )
			//'menu_name' => default to 'name'
		);
		$args = array(
			'labels' => $labels,
			'public' => true,
			'has_archive' => true,
			'publicly_queryable' => true,
			'query_var' => true,
			'rewrite' => array("slug" => "events"),
			'capability_type' => 'post',
			'hierarchical' => false,
			'supports' => array(
				'title',
				'editor',
				//'excerpt',
				'thumbnail',
				//'author',
				//'trackbacks',
				//'custom-fields',
				//'comments',
				//'revisions',
				//'page-attributes', // (menu order, hierarchical must be true to show Parent option)
				//'post-formats',
			),
			//'taxonomies' => array( 'category', 'post_tag' ), // add default post categories and tags
			//'taxonomies' => array( 'category' ), // add default post categories and tags
			'menu_position' => 99,
			'register_meta_box_cb' => 'event_add_post_type_metabox'
		);
		register_post_type( 'event', $args );  

		flush_rewrite_rules();
 		
		register_taxonomy( 'event_category', // register custom taxonomy - event category
			'event',
			array( 'hierarchical' => true,
				'label' => __( 'Event categories' )
			)
		);
		register_taxonomy( 'event_tag', // register custom taxonomy - event tag
			'event',
			array( 'hierarchical' => false,
				'label' => __( 'Event tags' )
			)
		);
	}
	add_action( 'init', 'event_create_post_type' );
 
	function event_add_post_type_metabox() { // add the meta box
		add_meta_box( 'event_metabox', 'Event Details', 'event_metabox', 'event', 'normal', 'high' );
		add_meta_box( 'respondent_metabox', 'Respondents', 'respondent_metabox', 'event', 'normal', 'high' );
	}
 
	function event_metabox() {
		global $post;
		// Noncename needed to verify where the data originated
		echo '<input type="hidden" name="event_post_noncename" id="event_post_noncename" value="' . wp_create_nonce( plugin_basename(__FILE__) ) . '" />';
 
		$fields = array(
			'event_venue_name' => '', 
			'event_date' => '', 
			'event_hour' => '', 
			'event_minute' => '', 
			'event_meridian' => '', 
			'event_address' => '', 
			'event_state' => '', 
			'event_city' => '', 
			'event_zip' => '');

		foreach($fields as $field => $value){
			$rsvp_me[$field] = get_post_meta($post->ID, '_rsvp_me_' . $field, true); 
		}
		$rsvp
		// Echo out the field
		?>
		
		<style>
			.ui-datepicker-trigger{
				float:right;
			}
			.rsvp-me-form-group{
				width: auto;
				clear:both;
			}
			.rsvp-me-form-group:last-child{
				
			}

			#rsvp_me_table{
				width: 100%;
			}

			#rsvp_me_table tr td{
				padding: 0px 15px;
			}
		</style>
		<table id="rsvp_me_table">
			<tr>
				<td valign="top">
					<div class="rsvp-me-form-group">
					 	<h2>Where:</h2>
					 	<p>
					 		<label>Venue name</label><br>
							<input type="text" class="widefat" name="rsvp_me_event_venue_name" value="<?php echo $rsvp_me['event_venue_name']; ?>">
						</p>
						<p>
							<label for="rsvp_me_event_address">Address</label><br />
							<textarea class="widefat" name='rsvp_me_event_address' id='address'><?php echo $rsvp_me['event_address']; ?></textarea>
						</p>

						<p>
							<label for="rsvp_me_event_city">City</label><br />
							<input class="widefat" type='text' name='rsvp_me_event_city' value='<?php echo $rsvp_me['event_city']; ?>' />
						</p>

						<p>
							<label for="rsvp_me_event_state">State</label><br />
							<?php echo select_state($rsvp_me['event_state'], 'rsvp_me_event_state'); ?>
						</p>
						
						<p>
							<label for="rsvp_me_event_zip">Zip</label><br />
							<input type='text' name='rsvp_me_event_zip' size='5' maxlength="5" value='<?php echo $rsvp_me['event_zip']; ?>' /></td>
						</p>   
					</div>
				</td>
				<td valign="top">
				<div class="rsvp-me-form-group">
				<h2>When:</h2>
				<p style="width:intrinsic">
					<label for="rsvp_me_event_date">Date</label><br />
				<input type="text" class="datepicker" name="rsvp_me_event_date" value="<?php echo $rsvp_me['event_date']; ?>" />
				</p>

				<label>Time</label><br />
				<p style="display:inline-block">
				<select name='rsvp_me_event_hour'>
					<?php for($i=1; $i < 13; $i++){
						$h = ($i < 10 ? "0" . $i : $i);
						echo "<option value='$h' " . ($rsvp_me['event_hour'] == $h ? "selected='selected'" : "") . ">$h</option>\n";
					} ?>
				</select>
				</p>

				<p style="display:inline-block">
				<select name='rsvp_me_event_minute'>
					<?php for($i=0; $i < 61; $i++){
						$min = ($i < 10 ? "0" . $i : $i);
						echo "<option value='$min' " . ($rsvp_me['event_minute'] == $min ? "selected='selected'" : "") . ">$min</option>\n";
					} ?>
				</select>
				</p>

				<p style="display:inline-block">
				<select name='rsvp_me_event_meridian'>
				  <option value='am' <?php echo ( isset($rsvp_me['event_meridian']) && $rsvp_me['event_meridian'] == "am") ? "selected='selected'" : "" ?>>AM</option>
				  <option value='pm' <?php echo ( isset($rsvp_me['event_meridian']) && $rsvp_me['event_meridian'] == "pm") ? "selected='selected'" : "" ?>>PM</option>
				 </select>
				</p>
				</div> 
				</td>
			</tr>
		</table>
	<?php
	}
 
 
	function event_post_save_meta( $post_id, $post ) { // save the data
		// verify this came from the our screen and with proper authorization,
		// because save_post can be triggered at other times
	
		if(count($_POST) > 0){ // make sure we have $_POST variant_set(variant, value)

			//die(nl2br(print_r($_POST,true)));

			if( isset($_POST['menu-settings-column-nonce']) && !wp_verify_nonce( $_POST['menu-settings-column-nonce'], plugin_basename(__FILE__) ) ) {
				return $post->ID;
			}

			if( isset($_POST['event_post_noncename']) && !wp_verify_nonce( $_POST['event_post_noncename'], plugin_basename(__FILE__) ) ) {
				return $post->ID;
			}
 		
			// is the user allowed to edit the post or page?
			if( ! current_user_can( 'edit_post', $post->ID )){
				return $post->ID;
			}
			// ok, we're authenticated: we need to find and save the data
			// we'll put it into an array to make it easier to loop though
	 
	 		if(isset($_POST['event_post_noncename'])){
				$event_post_meta['_rsvp_me_event_venue_name'] 	= $_POST['rsvp_me_event_venue_name'];
				$event_post_meta['_rsvp_me_event_date'] 				= $_POST['rsvp_me_event_date'];
				$event_post_meta['_rsvp_me_event_hour'] 				= $_POST['rsvp_me_event_hour'];
				$event_post_meta['_rsvp_me_event_minute'] 			= $_POST['rsvp_me_event_minute'];
				$event_post_meta['_rsvp_me_event_meridian'] 		= $_POST['rsvp_me_event_meridian'];
				$event_post_meta['_rsvp_me_event_address'] 			= $_POST['rsvp_me_event_address'];
				$event_post_meta['_rsvp_me_event_city'] 				= $_POST['rsvp_me_event_city'];
				$event_post_meta['_rsvp_me_event_state'] 				= $_POST['rsvp_me_event_state'];
				$event_post_meta['_rsvp_me_event_zip'] 					= $_POST['rsvp_me_event_zip'];
		 
				// add values as custom fields
				foreach( $event_post_meta as $key => $value ) { // cycle through the $event_post_meta array
					// if( $post->post_type == 'revision' ) return; // don't store custom data twice
					$value = implode(',', (array)$value); // if $value is an array, make it a CSV (unlikely)
					if( get_post_meta( $post->ID, $key, FALSE ) ) { // if the custom field already has a value
						update_post_meta($post->ID, $key, $value);
					} else { // if the custom field doesn't have a value
						add_post_meta( $post->ID, $key, $value );
					}
					if( !$value ) { // delete if blank
						delete_post_meta( $post->ID, $key );
					}
				} // if isset($_POST['event_post_noncename'])
			}
		}
	}
	add_action( 'save_post', 'event_post_save_meta', 1, 2 ); // save the custom fields
endif; // end of function_exists()

function respondent_metabox(){
	global $post;
	$rsvps = rsvp_me_get_respondents($post->ID);					
	$count = count($rsvps);

	if($count < 1){
		echo "<h2>No RSVP's yet</h2>\n";
	}else{ ?> 
	<style>
	#rsvp_me_respondent_table{
		width: 100%;
	}
	#rsvp_me_respondent_table tr th{
		text-align: left;
	}
	</style>
	<table id="rsvp_me_respondent_table" cellpadding="10">
		<thead>
			<tr>
				<th>Respondent</th>
				<th>Email</th>
				<th>Message</th>
				<th>Response</th>
				<th>Responded on</th>
			</tr>
		</thead>
		<tbody>
		<?php foreach($rsvps as $field => $respondent) : ?>
			<tr>
				<td><?php echo $respondent['fname'] . " " . $respondent['lname']; ?></td>
				<td><a href="mailto:<?php echo $respondent['email']; ?>"><?php echo $respondent['email']; ?></a></td>
				<td><?php echo urldecode($respondent['msg']); ?></td>
				<td><?php echo $respondent['response']; ?></td>
				<td><?php echo ltrim( date("F jS Y h:ia", strtotime($respondent['time_accepted'])), '0') ?></td>
			</tr>
		<?php endforeach; ?>
		</tbody>
	</table>
	<?php 
	}
}

/**
 * Designate our custome archive and single Event page templates
 */
//add_filter( 'template_include', 'my_plugin_templates' );
function my_plugin_templates( $template ) {
    $post_types = array( 'event' );

    if ( is_post_type_archive( $post_types ) && ! file_exists( get_stylesheet_directory() . '/archive-event.php' ) )
        $template = RSVP_ME_FILE_PATH . '/archive-event.php';
    if ( is_singular( $post_types ) && ! file_exists( get_stylesheet_directory() . '/single-event.php' ) )
        $template = RSVP_ME_FILE_PATH . '/single-event.php';

    return $template;
}

function rsvp_me_event_page($content){
	global $post;
	global $foomanchu;

	if($post->post_type != "event" || !is_single($post) ) return $content;

	$fields = array(
		'venue_name' => '', 
		'date' => '', 
		'hour' => '', 
		'minute' => '', 
		'meridian' => '', 
		'address' => '', 
		'state' => '', 
		'city' => '', 
		'zip' => ''
	);

	//prepare are values array for the template engine
	$event['id'] = get_the_id();
	$event["title"] = get_the_title();
	$event["description"] = $content;
	$event["featured_image"] = get_the_post_thumbnail(get_the_ID());

	foreach($fields as $field => $value){
		$event[$field] = get_post_meta($post->ID, '_rsvp_me_event_' . $field, true); 
	}
	$event["time"] = $event["hour"] . ":" . $event["minute"] . $event["meridian"];

	$event['accept_response'] = stripslashes(get_option("_rsvp_me_accept_response"));
	$event['maybe_response'] = stripslashes(get_option("_rsvp_me_maybe_response"));
	$event['decline_response'] = stripslashes(get_option("_rsvp_me_decline_response"));

	if($event['maybe_response'] != "") $event['showMaybeResponse'] = true;
	if($event['decline_response'] != "") $event['showDeclineResponse'] = true;

	//$content = buildTemplateFromValues(RSVP_ME_FILE_PATH . "/themes/default/event.html", $rsvp_me, false);
	$template = file_get_contents(RSVP_ME_FILE_PATH . "/themes/default/event.fmc");
	$content = $foomanchu->render($template, $event, false);

	return $content;
}

add_filter("the_content", "rsvp_me_event_page");
//add_filter("the_title", "rsvp_me_event_title");

/**
 * Add custom columns to our Events Custom Post Type
 */
// ONLY MOVIE CUSTOM TYPE POSTS  
add_filter('manage_event_posts_columns', 'rsvp_me_columns_head', 10);  
add_action('manage_event_posts_custom_column', 'rsvp_me_columns_content', 10, 2);  
  
// CREATE TWO FUNCTIONS TO HANDLE THE COLUMN  
function rsvp_me_columns_head($defaults) {  
	$defaults['venue'] = 'Venue';
	$defaults['event_date'] = 'Event Date';
  $defaults['respondents'] = 'Respondents';
  $defaults['id'] = 'ID';
  return $defaults;  
}

function rsvp_me_columns_content($column_name, $post_ID) {  

  switch($column_name){ 
  	case 'respondents' :
  		$rsvps = rsvp_me_get_respondents($post_ID);	
			echo count($rsvps);
			break;

		case 'venue' :
			$event = get_rsvp_event_by_id($post_ID);
			echo (isset($event["venue_name"]) ? $event["venue_name"] : "");
			break;
  
		case 'event_date' :
			$event = get_rsvp_event_by_id($post_ID);
			echo (isset($event["date"]) ? $event["date"] : "");
			break;

		case 'id' :
			$event = get_rsvp_event_by_id($post_ID);
			echo $post_ID;
			break;
  }
}
?>