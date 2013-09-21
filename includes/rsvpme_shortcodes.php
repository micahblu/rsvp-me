<?php
/**
 * RSVP ME Pro Shortcodes
 *
 * @author: Micah Blu
 * @since: v1.1.0
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

	if(isset($id)){ $event = get_rsvp_by_id($id); ?>

		<div id="rsvp_form_<?php echo $id ?>" class="rsvp-form">
    	<h2 id="rsvpEventTitle"><?php echo stripslashes($event['title']) ?></h2>
        <p><?php echo stripslashes($event['description']) ?></p>
        <h3>Venue: <?php echo stripslashes($event['venue']) ?></h3>
        <?php echo $event['address'] . "<br />" . $event['city'] . ", " . $event['state'] . " " . $event['zip'] ?> 
       
        <h3>Are you coming? Then RSVP below!</h3>
        
        <form id="rsvp_form_<?php echo $event['id'] ?>" action="" method="" onsubmit="return rsvpMe.submitRsvp(<?php echo $event['id'] ?>)"> 
       		<input type='hidden' name='event_id' value='<?php echo $event['id'] ?>' />
					<table cellpadding="5" cellspacing="0" border="0">
						<tr>
							<td>First name</td><td><input type='text'	 name='fname' value='' /></td>
						</tr>
						<tr>
							<td>Last name</td><td><input type='text' name='lname' value='' /></td>
						</tr>
						<tr>
							<td>Email</td>
							<td><input type='text' name='email' value='' /></td>
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
							<td colspan="2">
								<span id='submit_cancel_<?php echo $event['id'] ?>'>
								<input type='submit' name='submit' value='RSVP Me' /> or <a href="Javascript: rsvpMe.cancel()">Cancel</a></span>
							</td>
						</tr>
					</table>
      	</form>
    </div>
    <?
	}
}

add_shortcode("rsvp_event", "rsvpme_event_form");
?>