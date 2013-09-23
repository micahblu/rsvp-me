<?php
/**
 * The template for displaying Event Archive pages.
 *
 * @package WordPress
 * @subpackage RSVP ME
 * @since RSVP ME 0.9 
 */

get_header(); ?>

	<div class="large-8 columns" role="main">

	<?php if ( have_posts() ) : ?>
		<header class="archive-header">
			<h1 class="archive-title"><?php
				if ( is_day() ) :
					printf( __( 'Daily Archives: %s', 'rsvp-me' ), '<span>' . get_the_date() . '</span>' );
				elseif ( is_month() ) :
					printf( __( 'Monthly Archives: %s', 'rsvp-me' ), '<span>' . get_the_date( _x( 'F Y', 'monthly archives date format', 'rsvp-me' ) ) . '</span>' );
				elseif ( is_year() ) :
					printf( __( 'Yearly Archives: %s', 'rsvp-me' ), '<span>' . get_the_date( _x( 'Y', 'yearly archives date format', 'rsvp-me' ) ) . '</span>' );
				else :
					_e( 'Events', 'rsvp-me' );
				endif;
			?></h1>
		</header><!-- .archive-header -->

		<?php while ( have_posts() ) : the_post(); ?>
			<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
				<header class="entry-header">
					<h1 class="entry-title">
						<a href="<?php the_permalink(); ?>" title="<?php echo esc_attr( sprintf( __( 'Permalink to %s', 'rsvp-me' ), the_title_attribute( 'echo=0' ) ) ); ?>" rel="bookmark"><?php the_title(); ?></a>
					</h1>
				</header><!-- .entry-header -->
				<?php the_post_thumbnail('medium'); ?>
			
				<div class="entry-content">
					<p><?php the_content( __( 'Continue reading <span class="meta-nav">&rarr;</span>', 'rsvp-me' ) ); ?></p>
					<?php //wp_link_pages( array( 'before' => '<div class="page-links">' . __( 'Pages:', 'rsvp-me' ), 'after' => '</div>' ) ); ?>
				</div><!-- .entry-content -->

				<footer class="entry-meta">
				
				</footer><!-- .entry-meta -->
			</article><!-- #post -->

			<?php
			global $wp_query;

			$big = 999999999; // need an unlikely integer
			
			echo paginate_links( array(
				'base' => str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) ),
				'format' => '?paged=%#%',
				'current' => max( 1, get_query_var('paged') ),
				'total' => $wp_query->max_num_pages
			) );
		endwhile; ?>

	<?php else : ?>
		<h1>There are no Events</h1>
	<?php endif; ?>
	
	</div><!-- .large-8 .columns -->
	
	<div class="large-4 columns">
		<?php get_sidebar(); ?>
	</div><!-- .large-4 .columns -->

<?php get_footer(); ?>