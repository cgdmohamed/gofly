<?php
/**
 * Template Name: احجز الآن (طيران وفنادق)
 *
 * Flight / hotel request widget (assets/js/booking.js). It suggests cities
 * from the free Open-Meteo geocoding API and sends the request to the
 * company WhatsApp number from Site Settings.
 *
 * @package gofly
 */

get_header();

while ( have_posts() ) :
	the_post();
	get_template_part( 'template-parts/components/page-hero', null, array( 'class' => 'page-hero--booking' ) );
	?>
	<section class="booking-section">
		<div id="bk-page" class="booking-widget">
			<noscript><p class="notice-box"><?php esc_html_e( 'فعّل JavaScript لاستخدام نموذج الحجز، أو تواصل معنا عبر واتساب.', 'gofly' ); ?></p></noscript>
		</div>
		<?php if ( get_the_content() ) : ?>
			<div class="entry-content booking-section__content"><?php the_content(); ?></div>
		<?php endif; ?>
	</section>
	<?php
endwhile;

get_footer();
