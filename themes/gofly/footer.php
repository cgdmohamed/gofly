<?php
/**
 * Site footer: gold CTA card, brand + contact, link columns, badges, legal.
 *
 * @package gofly
 */

$gofly_contacts = array(
	array( 'call', __( 'واتساب', 'gofly' ), gofly_option( 'phone_display' ), gofly_whatsapp_url(), true ),
	array( 'mail', __( 'البريد الإلكتروني', 'gofly' ), gofly_option( 'email' ), 'mailto:' . gofly_option( 'email' ), false ),
	array( 'location_on', __( 'الموقع', 'gofly' ), gofly_option( 'address_full' ), '', false ),
	array( 'schedule', __( 'ساعات العمل', 'gofly' ), gofly_option( 'hours' ), '', false ),
);
$gofly_badges = gofly_option( 'badges', array() );
?>
</main>

<footer class="site-footer">
	<div class="footer-cta">
		<div>
			<div class="footer-cta__title"><?php echo esc_html( gofly_option( 'cta_title', __( 'جاهز لرحلتك القادمة؟', 'gofly' ) ) ); ?></div>
			<div class="footer-cta__text"><?php echo esc_html( gofly_option( 'cta_text', __( 'تواصل معنا الآن واحصل على عرض مخصص خلال دقائق.', 'gofly' ) ) ); ?></div>
		</div>
		<a class="btn btn--navy" href="<?php echo esc_url( gofly_whatsapp_url() ); ?>" target="_blank" rel="noopener"><?php echo gofly_brand_icon( 'whatsapp' ); // phpcs:ignore ?><?php esc_html_e( 'تواصل عبر واتساب', 'gofly' ); ?></a>
	</div>

	<div class="site-footer__inner">
		<div class="footer-top">
			<div class="footer-brand">
				<img src="<?php echo esc_url( gofly_logo_url( 'light' ) ); ?>" alt="<?php bloginfo( 'name' ); ?>" height="76">
				<p><?php echo esc_html( gofly_option( 'footer_about', get_bloginfo( 'description' ) ) ); ?></p>
				<div class="social"><?php gofly_social_links( 'social__link' ); ?></div>
			</div>
			<div class="footer-contact">
				<?php
				foreach ( $gofly_contacts as list( $icon, $label, $value, $url, $ltr ) ) :
					if ( ! $value ) {
						continue;
					}
					$value_html = $ltr ? '<span dir="ltr">' . esc_html( $value ) . '</span>' : esc_html( $value );
					?>
					<div class="footer-contact__item">
						<span class="icon-tile icon-tile--gold"><?php echo gofly_icon( $icon ); // phpcs:ignore ?></span>
						<span>
							<small><?php echo esc_html( $label ); ?></small>
							<?php if ( $url ) : ?>
								<a href="<?php echo esc_url( $url ); ?>"><?php echo $value_html; // phpcs:ignore ?></a>
							<?php else : ?>
								<b><?php echo $value_html; // phpcs:ignore ?></b>
							<?php endif; ?>
						</span>
					</div>
				<?php endforeach; ?>
			</div>
		</div>

		<div class="footer-links">
			<?php
			foreach ( array( 'footer_1', 'footer_2', 'footer_3' ) as $gofly_location ) :
				if ( ! has_nav_menu( $gofly_location ) ) {
					continue;
				}
				$gofly_menu = wp_get_nav_menu_object( get_nav_menu_locations()[ $gofly_location ] );
				?>
				<div class="footer-links__col">
					<b class="footer-links__title"><?php echo esc_html( $gofly_menu->name ); ?></b>
					<?php
					wp_nav_menu(
						array(
							'theme_location' => $gofly_location,
							'container'      => false,
							'menu_class'     => 'footer-links__list',
							'depth'          => 1,
							'link_before'    => gofly_icon( 'chevron_left' ),
						)
					);
					?>
				</div>
			<?php endforeach; ?>
		</div>

		<div class="footer-bottom">
			<?php if ( $gofly_badges ) : ?>
				<div class="footer-badges">
					<?php foreach ( $gofly_badges as $gofly_badge ) : ?>
						<span class="footer-badges__item"><?php echo gofly_image( $gofly_badge, 'medium', array( 'loading' => 'lazy' ) ); // phpcs:ignore ?></span>
					<?php endforeach; ?>
				</div>
			<?php else : ?>
				<div class="footer-badges">
					<span class="footer-badges__item"><img src="<?php echo esc_url( GOFLY_URI . '/assets/img/business-registration.svg' ); ?>" alt="<?php esc_attr_e( 'السجل التجاري', 'gofly' ); ?>" loading="lazy"></span>
					<span class="footer-badges__item"><img src="<?php echo esc_url( GOFLY_URI . '/assets/img/vat.png' ); ?>" alt="<?php esc_attr_e( 'ضريبة القيمة المضافة', 'gofly' ); ?>" loading="lazy"></span>
				</div>
			<?php endif; ?>
			<?php
			wp_nav_menu(
				array(
					'theme_location' => 'footer_legal',
					'container'      => false,
					'menu_class'     => 'footer-legal',
					'depth'          => 1,
					'fallback_cb'    => false,
				)
			);
			?>
		</div>

		<div class="footer-copy">
			<?php
			$gofly_copy = gofly_option( 'copyright', '© {year} ' . get_bloginfo( 'name' ) . '. ' . __( 'جميع الحقوق محفوظة.', 'gofly' ) );
			echo esc_html( str_replace( '{year}', wp_date( 'Y' ), $gofly_copy ) );
			?>
		</div>
	</div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
