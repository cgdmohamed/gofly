<?php
/**
 * Site header: top bar, logo, primary menu, CTA and the mobile menu panel.
 *
 * @package gofly
 */

$gofly_cta = gofly_option(
	'header_cta',
	array(
		'url'    => gofly_template_url( 'booking' ),
		'title'  => __( 'احجز الآن', 'gofly' ),
		'target' => '',
	)
);
?>
<!doctype html>
<html <?php language_attributes(); ?> class="no-js">
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<script>document.documentElement.classList.replace('no-js','js');</script>
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="skip-link screen-reader-text" href="#main"><?php esc_html_e( 'تخطي إلى المحتوى', 'gofly' ); ?></a>

<header class="site-header<?php echo is_front_page() ? ' site-header--overlay' : ''; ?>">
	<div class="topbar">
		<div class="topbar__info">
			<?php if ( gofly_option( 'phone_display' ) ) : ?>
				<a href="<?php echo esc_url( gofly_whatsapp_url() ); ?>"><?php echo gofly_brand_icon( 'whatsapp' ); // phpcs:ignore ?><span dir="ltr"><?php echo esc_html( gofly_option( 'phone_display' ) ); ?></span></a>
			<?php endif; ?>
			<?php if ( gofly_option( 'hours' ) ) : ?>
				<span><?php echo gofly_icon( 'schedule' ); // phpcs:ignore ?><?php echo esc_html( gofly_option( 'hours' ) ); ?></span>
			<?php endif; ?>
			<?php if ( gofly_option( 'address_short' ) ) : ?>
				<span><?php echo gofly_icon( 'location_on' ); // phpcs:ignore ?><?php echo esc_html( gofly_option( 'address_short' ) ); ?></span>
			<?php endif; ?>
		</div>
		<div class="topbar__social"><?php gofly_social_links( 'topbar__social-link' ); ?></div>
	</div>

	<div class="site-header__bar">
		<a class="site-logo" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
			<img src="<?php echo esc_url( gofly_logo_url( 'light' ) ); ?>" alt="<?php bloginfo( 'name' ); ?>" width="160" height="64">
		</a>

		<nav class="nav" aria-label="<?php esc_attr_e( 'القائمة الرئيسية', 'gofly' ); ?>">
			<?php
			wp_nav_menu(
				array(
					'theme_location' => 'primary',
					'container'      => false,
					'menu_class'     => 'nav__menu',
					'depth'          => 2,
					'walker'         => new Gofly_Nav_Walker(),
					'fallback_cb'    => false,
				)
			);
			?>
		</nav>

		<div class="site-header__actions">
			<a class="icon-btn icon-btn--outline site-header__search" href="<?php echo esc_url( get_post_type_archive_link( 'package' ) ); ?>" aria-label="<?php esc_attr_e( 'ابحث في الباقات', 'gofly' ); ?>"><?php echo gofly_icon( 'search' ); // phpcs:ignore ?></a>
			<?php echo gofly_link( $gofly_cta, 'btn btn--gold', 'flight_takeoff' ); // phpcs:ignore ?>
			<button class="icon-btn icon-btn--outline mnav-toggle" type="button" aria-controls="mnav" aria-expanded="false" aria-label="<?php esc_attr_e( 'القائمة', 'gofly' ); ?>"><?php echo gofly_icon( 'menu' ); // phpcs:ignore ?></button>
		</div>
	</div>
</header>

<div class="mnav" id="mnav" role="dialog" aria-modal="true" aria-label="<?php esc_attr_e( 'القائمة', 'gofly' ); ?>" aria-hidden="true">
	<div class="mnav__overlay" data-mnav-close></div>
	<div class="mnav__panel">
		<div class="mnav__head">
			<img src="<?php echo esc_url( gofly_logo_url( 'dark' ) ); ?>" alt="<?php bloginfo( 'name' ); ?>" height="52">
			<button class="icon-btn icon-btn--soft" type="button" data-mnav-close aria-label="<?php esc_attr_e( 'إغلاق', 'gofly' ); ?>"><?php echo gofly_icon( 'close' ); // phpcs:ignore ?></button>
		</div>
		<?php
		wp_nav_menu(
			array(
				'theme_location' => 'primary',
				'container'      => false,
				'menu_class'     => 'mnav__menu',
				'depth'          => 1,
				'walker'         => new Gofly_Nav_Walker(),
				'fallback_cb'    => false,
			)
		);
		echo gofly_link( $gofly_cta, 'btn btn--gold btn--block' ); // phpcs:ignore
		?>
	</div>
</div>

<main id="main" class="site-main">
