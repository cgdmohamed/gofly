<?php
/**
 * Template Name: الوجهات
 *
 * Grid of all destinations (Packages → Destinations), 12 per page.
 *
 * @package gofly
 */

get_header();

while ( have_posts() ) :
	the_post();
	get_template_part( 'template-parts/components/page-hero' );

	$gofly_per   = 12;
	$gofly_paged = max( 1, get_query_var( 'paged' ), get_query_var( 'page' ) );
	$gofly_total = (int) wp_count_terms( array( 'taxonomy' => 'destination', 'hide_empty' => false ) );
	$gofly_terms = get_terms(
		array(
			'taxonomy'   => 'destination',
			'hide_empty' => false,
			'number'     => $gofly_per,
			'offset'     => ( $gofly_paged - 1 ) * $gofly_per,
		)
	);
	?>
	<section class="section section--roomy">
		<?php if ( $gofly_terms && ! is_wp_error( $gofly_terms ) ) : ?>
			<div class="grid grid--250">
				<?php
				foreach ( $gofly_terms as $gofly_term ) {
					get_template_part( 'template-parts/components/destination-card', null, array( 'term' => $gofly_term ) );
				}
				?>
			</div>
			<?php gofly_pagination( (int) ceil( $gofly_total / $gofly_per ) ); ?>
		<?php else : ?>
			<?php get_template_part( 'template-parts/components/empty-state', null, array( 'title' => __( 'لا توجد وجهات بعد', 'gofly' ), 'text' => '' ) ); ?>
		<?php endif; ?>
	</section>
	<?php
endwhile;

get_footer();
