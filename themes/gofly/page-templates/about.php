<?php
/**
 * Template Name: من نحن
 *
 * Company story, values grid and the stats block.
 *
 * @package gofly
 */

get_header();

while ( have_posts() ) :
	the_post();
	get_template_part( 'template-parts/components/page-hero' );
	$gofly_values = (array) gofly_field( 'values_items', false, array() );
	?>
	<section class="section section--roomy">
		<div class="split">
			<div class="split__media"><?php echo gofly_image( gofly_field( 'story_image' ), 'gofly-hero' ) ?: get_the_post_thumbnail( null, 'gofly-hero' ); // phpcs:ignore ?></div>
			<div class="split__body">
				<div class="eyebrow"><?php echo esc_html( gofly_field( 'story_eyebrow', false, __( 'قصتنا', 'gofly' ) ) ); ?></div>
				<h2 class="section-title"><?php echo esc_html( gofly_field( 'story_title' ) ); ?></h2>
				<div class="split__text"><?php echo wp_kses_post( gofly_field( 'story_text' ) ); ?></div>
				<?php echo gofly_link( gofly_field( 'story_button' ), 'btn btn--gold' ); // phpcs:ignore ?>
			</div>
		</div>
	</section>

	<?php if ( $gofly_values ) : ?>
		<section class="section section--tight">
			<?php
			get_template_part(
				'template-parts/components/section-head',
				null,
				array(
					'eyebrow' => gofly_field( 'values_eyebrow', false, __( 'قيمنا', 'gofly' ) ),
					'title'   => gofly_field( 'values_title', false, __( 'ما يميزنا', 'gofly' ) ),
				)
			);
			?>
			<div class="grid grid--240">
				<?php
				foreach ( $gofly_values as $gofly_value ) {
					get_template_part( 'template-parts/components/feature-card', null, array( 'variant' => 'value' ) + $gofly_value );
				}
				?>
			</div>
		</section>
	<?php endif; ?>

	<section class="section section--stats">
		<?php get_template_part( 'template-parts/components/stats', null, array( 'variant' => 'navy' ) ); ?>
	</section>

	<?php if ( get_the_content() ) : ?>
		<section class="section section--tight"><div class="entry-content"><?php the_content(); ?></div></section>
	<?php endif; ?>
	<?php
endwhile;

get_footer();
