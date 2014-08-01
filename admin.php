<?php
/*
 * RSVP ME admin functions
 */
global $wpdb;

// Hooks 
add_action('admin_menu', 'rsvp_me_menu');
add_action('admin_init', 'rsvp_me_init');
add_action('admin_footer', 'rsvp_me_admin_footer');
add_action('wp_ajax_rsvp_me_update_settings', 'rsvp_me_update_settings');


function rsvp_me_init(){
	//check to see if we've just activated
	if(get_option('Activated_Plugin') == 'rsvp-me'){
		delete_option('Activated_Plugin');

		rsvp_me_welcome();

		add_action("shutdown", "rsvp_me_welcome");
	}
	rsvp_me_register_admin_scripts(); // register our scripts
}

function rsvp_me_welcome(){ 

	//wp_redirect(admin_url("admin.php?page=rsvp_me_settings"));
	?>
	
<?php }

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

	wp_enqueue_style("rsvp-admin-css", RSVP_ME_PLUGIN_URI . "/admin.css");
	wp_enqueue_style( 'wp-color-picker' );
	wp_enqueue_script( 'my-script-handle', plugins_url('js/admin.js', __FILE__ ), array( 'wp-color-picker' ), false, true );
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
	$top_menu_slug = "rsvp_events_settings";
	add_menu_page('RSVP ME', 'RSVP ME', 'manage_options', $top_menu_slug, 'rsvp_me_settings', plugins_url('rsvp-me/images/rsvp-me-wax.png'));
}

function rsvp_me_update_settings(){
	$msg = '';
	foreach($_POST as $field => $value){
		
		if($field != "action"){;
			// Try update, if fails, add option
			if(!update_option("_" . $field, $value)){
				add_option("_" . $field, $value);
			}
		}
	}
	echo json_encode(array("success" => true, "message" => $msg));
}	

/**
 * RSVP ME settings..
 * outputs html form for rsvp me settings 
 *
 * @since 1.9.0
 * @param null
 * @return null
 */
function rsvp_me_settings(){ ?>
	<div id="rsvp-me-admin">
	  <header id="rsvp-me-admin-header">
	  	<img src="<?php echo plugins_url(); ?>/rsvp-me/images/rsvp-me-logo-r.png" class="icon24 rsvp-me-logo" alt="RSVP ME" /> <h2>RSVP ME Settings</h2>
	  </header>

	  <br style="clear:both" />

		<?php $options = get_rsvp_me_options(); //die(print_r($options)); ?>
		<div class="tab-navigation">
	    <h2 class="nav-tab-wrapper">

			<?php for($i=0; $i < count($options); $i++) : ?>
				<a class="nav-tab <?php echo $i==0 ? 'nav-tab-active' : '' ?>" id="tab-<?php echo $i ?>"><?php echo $options[$i]['heading']; ?></a>
			<?php endfor; ?>
			</h2>
		</div><!-- .tab-navigation -->

		<div class="tab-contents">
			<?php for($i=0; $i < count($options); $i++) : ?>
	  		<?php $fields = $options[$i]['fields']; ?>
		  	<div class="tab-panel" id="tab-content-<?php echo $i ?>" <?php echo $i > 0 ? 'style="display:none"' : "" ?>>
			  	<div class="<?php echo $options[$i]['section'] == 'calendar' ? 'rsvp-me-cal-options' : ''?>">
		  		<?php
	  			foreach($fields as $field){

	  				switch($field["type"]){

	  					case "text" : ?>
	  						<p>
					  			<label for="rsvp_calendar_background"><?php echo $field["name"]; ?></label><br />
					  			<input type="text" name="<?php echo $field["id"] ?>" value="<?php echo $field['value']; ?>" />
					  		</p>
	  					<?php break;

	  					case "radio" : ?>
	  						<p>
					  			<label for="rsvp_calendar_background"><?php echo $field["name"]; ?></label><br />
					  			<input type="radio" name="<?php echo $field["id"] ?>" selected="<?php echo $field['value']; ?>" />
					  		</p>
	  					<?php break;

	  					case "color" : ?>
	  						<p>
					  			<label for="rsvp_calendar_background"><?php echo $field["name"]; ?></label><br />
					  			<input type="text" name="<?php echo $field["id"] ?>" value="<?php echo $field['value']; ?>" class="rsvp-me-color-field" data-default-color="<?php echo $field['value']; ?>" />
					  		</p>
	  					<?php break;
	  				}
	  			}
		  		?>
			  	</div>

			  	<?php if($options[$i]['section'] == 'response_labels') : ?>
			  		<p>* To disable maybe or decline response, just leave empty</p>
			  	<?php endif; ?>
			  	
			  	<?php if($options[$i]['section'] == 'calendar') : ?>
				  	<div class="rsvp-me-cal-sample">
				  		<div id='rsvp_me_calendar_widget'>
							<?php rsvp_me_draw_calendar(array(date("Y-m-d") => array()));  ?>
						</div><!-- #rsvp_me_calendar_widget -->
				  	</div>
				<?php endif; ?>
		  	</div><!-- .tab-panel -->
		  <?php endfor; ?>
		</div><!-- .tab-contents -->
		
		<br style="clear:both" />
		  	
	  <button class="button" id="rsvp-me-submit-settings">Update settings</button>

	  <div class="rsvp-me-alert-msg"></div>
		<p>Be sure to checkout my other great <a target="_blank" href="http://micahblu.com/products">Themes & Plugins</a></p> 		

	</div><!-- #rsvp-me-admin -->
<?php } ?>