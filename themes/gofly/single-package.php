<?php
/**
 * Single package: gallery, programme sections, sticky booking card and the
 * quote request modal (Fluent Forms).
 *
 * Every section is optional and only appears when its fields are filled.
 *
 * @package gofly
 */

get_header();

while ( have_posts() ) :
	the_post();
	$gofly_pkg  = gofly_package();
	$gofly_tabs = array_filter(
		array(
			'program'  => gofly_field( 'itinerary' ) ? array( 'route', __( 'البرنامج', 'gofly' ) ) : null,
			'hotels'   => gofly_field( 'hotels' ) ? array( 'hotel', __( 'الفنادق', 'gofly' ) ) : null,
			'cars'     => gofly_field( 'cars' ) ? array( 'directions_car', __( 'السيارات', 'gofly' ) ) : null,
			'policies' => gofly_field( 'policies' ) ? array( 'gavel', __( 'السياسات', 'gofly' ) ) : null,
		)
	);
	?>
	<?php if ( $gofly_tabs ) : ?>
		<nav class="sub-nav" aria-label="<?php esc_attr_e( 'أقسام البرنامج', 'gofly' ); ?>">
			<?php foreach ( $gofly_tabs as $gofly_id => list( $gofly_icon, $gofly_label ) ) : ?>
				<a href="#<?php echo esc_attr( $gofly_id ); ?>"><?php echo gofly_icon( $gofly_icon ); // phpcs:ignore ?><?php echo esc_html( $gofly_label ); ?></a>
			<?php endforeach; ?>
		</nav>
	<?php endif; ?>

	<header class="pkg-header">
		<?php echo gofly_breadcrumb(); // phpcs:ignore ?>
		<h1 class="pkg-header__title"><?php the_title(); ?> <span class="nowrap"><?php echo esc_html( gofly_duration( $gofly_pkg['days'], $gofly_pkg['nights'], true ) ); ?></span></h1>
		<?php if ( gofly_field( 'subtitle' ) ) : ?>
			<div class="pkg-header__sub"><?php echo esc_html( gofly_field( 'subtitle' ) ); ?></div>
		<?php endif; ?>
		<?php get_template_part( 'template-parts/package/gallery' ); ?>
	</header>

	<div class="pkg-layout">
		<div class="pkg-content">
			<?php
			foreach ( array( 'itinerary', 'includes', 'hotels', 'cars', 'rooms', 'info', 'restaurants', 'policies' ) as $gofly_part ) {
				get_template_part( 'template-parts/package/' . $gofly_part );
			}
			?>
		</div>
		<?php get_template_part( 'template-parts/package/booking-card', null, array( 'pkg' => $gofly_pkg ) ); ?>
	</div>

	<?php
	get_template_part( 'template-parts/package/mobile-bar', null, array( 'pkg' => $gofly_pkg ) );
	get_template_part( 'template-parts/package/quote-modal', null, array( 'pkg' => $gofly_pkg ) );
endwhile;

get_footer();
