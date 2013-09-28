<?php
/**
 * The template for displaying a single Event page.
 *
 * @package WordPress
 * @subpackage RSVP ME
 * @since RSVP ME 1.9.0
 */

get_header(); ?>

	<div class="row">
		<div class="large-8 columns">

			<?php while ( have_posts() ) : the_post();  ?>		
				<?php
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
				$rsvp_me['id'] = get_the_id();
				$rsvp_me["title"] = get_the_title();
				$rsvp_me["description"] = get_the_content();
				$rsvp_me["featured_image"] = get_the_post_thumbnail(get_the_ID());

				foreach($fields as $field => $value){
					$rsvp_me[$field] = get_post_meta($post->ID, '_rsvp_me_event_' . $field, true); 
				}
				$rsvp_me["time"] = $rsvp_me["hour"] . ":" . $rsvp_me["minute"] . $rsvp_me["meridian"];

				buildTemplateFromValues(RSVP_ME_FILE_PATH . "/themes/default/event.html", $rsvp_me)
				?>
				
				<?php //comments_template( '', true ); ?>
			<?php endwhile; // end of the loop. ?>

		</div><!-- .large-8 .columns -->
	
		<div class="large-4 columns">
			<?php get_sidebar(); ?>
		</div><!-- .large-4 .columns -->
	</div><!-- .row -->
	
<?php get_footer(); ?>