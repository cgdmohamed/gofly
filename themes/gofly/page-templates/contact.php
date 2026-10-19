<?php
/**
 * Template Name: تواصل معنا
 *
 * Contact methods (Site Settings), a Fluent Form, location card and FAQ.
 *
 * @package gofly
 */

get_header();

while ( have_posts() ) :
	the_post();
	$gofly_methods = array_filter(
		array(
			array( 'chat', __( 'واتساب (الأسرع)', 'gofly' ), gofly_option( 'phone_display' ), gofly_whatsapp_url(), __( 'نرد خلال دقائق', 'gofly' ), true ),
			array( 'mail', __( 'البريد الإلكتروني', 'gofly' ), gofly_option( 'email' ), 'mailto:' . gofly_option( 'email' ), '', false ),
			array( 'schedule', __( 'ساعات العمل', 'gofly' ), gofly_option( 'hours' ), '', '', false ),
			array( 'location_on', __( 'العنوان', 'gofly' ), gofly_option( 'address_full' ), '', '', false ),
		),
		fn( $m ) => '' !== (string) $m[2]
	);
	$gofly_map = gofly_option( 'map_embed' );
	get_template_part( 'template-parts/components/page-hero' );
	?>
	<section class="section section--contact">
		<div class="contact-layout">
			<div class="contact-methods">
				<?php
				get_template_part(
					'template-parts/components/section-head',
					null,
					array(
						'eyebrow' => gofly_field( 'methods_eyebrow', false, __( 'طرق التواصل', 'gofly' ) ),
						'title'   => gofly_field( 'methods_title', false, __( 'نسعد بخدمتك في أي وقت', 'gofly' ) ),
					)
				);
				foreach ( $gofly_methods as list( $gofly_icon, $gofly_label, $gofly_value, $gofly_url, $gofly_extra, $gofly_ltr ) ) :
					$gofly_tag = $gofly_url ? 'a' : 'div';
					?>
					<<?php echo $gofly_tag; // phpcs:ignore ?> class="card contact-method"<?php echo $gofly_url ? ' href="' . esc_url( $gofly_url ) . '"' : ''; ?>>
						<span class="icon-tile"><?php echo gofly_icon( $gofly_icon ); // phpcs:ignore ?></span>
						<span class="contact-method__text">
							<small><?php echo esc_html( $gofly_label ); ?></small>
							<b<?php echo $gofly_ltr ? ' dir="ltr"' : ''; ?>><?php echo esc_html( $gofly_value ); ?></b>
							<?php if ( $gofly_extra ) : ?>
								<small><?php echo esc_html( $gofly_extra ); ?></small>
							<?php endif; ?>
						</span>
					</<?php echo $gofly_tag; // phpcs:ignore ?>>
				<?php endforeach; ?>
				<a class="btn btn--gold btn--block btn--lg" href="<?php echo esc_url( gofly_whatsapp_url() ); ?>" target="_blank" rel="noopener"><?php echo gofly_brand_icon( 'whatsapp' ); // phpcs:ignore ?><?php esc_html_e( 'ابدأ محادثة واتساب', 'gofly' ); ?></a>
			</div>

			<div class="card contact-form">
				<h2 class="contact-form__title"><?php echo esc_html( gofly_field( 'form_title', false, __( 'أرسل لنا رسالة', 'gofly' ) ) ); ?></h2>
				<p class="contact-form__text"><?php echo esc_html( gofly_field( 'form_text', false, __( 'املأ النموذج وسيتواصل معك أحد مستشاري السفر.', 'gofly' ) ) ); ?></p>
				<div class="form-skin"><?php echo gofly_form( gofly_field( 'form_id' ) ); // phpcs:ignore ?></div>
				<div class="contact-form__privacy"><?php echo gofly_icon( 'lock' ); // phpcs:ignore ?><?php esc_html_e( 'بياناتك محمية ولن تُشارك مع أي طرف.', 'gofly' ); ?></div>
			</div>
		</div>
	</section>

	<section class="section section--tight">
		<div class="card location">
			<div class="location__map">
				<?php if ( $gofly_map ) : ?>
					<iframe src="<?php echo esc_url( $gofly_map ); ?>" title="<?php esc_attr_e( 'خريطة الموقع', 'gofly' ); ?>" loading="lazy" referrerpolicy="no-referrer-when-downgrade" allowfullscreen></iframe>
				<?php else : ?>
					<span class="location__placeholder"><?php echo gofly_icon( 'location_on', 'ms--fill' ); // phpcs:ignore ?><?php esc_html_e( 'خريطة الموقع', 'gofly' ); ?></span>
				<?php endif; ?>
			</div>
			<div class="location__body">
				<div class="eyebrow"><?php esc_html_e( 'زرنا', 'gofly' ); ?></div>
				<h2 class="location__title"><?php echo esc_html( gofly_field( 'office_title', false, __( 'مكتبنا في الرياض', 'gofly' ) ) ); ?></h2>
				<p><?php echo esc_html( gofly_option( 'address_full' ) ); ?></p>
				<?php if ( gofly_option( 'map_link' ) ) : ?>
					<a class="link-icon" href="<?php echo esc_url( gofly_option( 'map_link' ) ); ?>" target="_blank" rel="noopener"><?php echo gofly_icon( 'directions' ); // phpcs:ignore ?><?php esc_html_e( 'احصل على الاتجاهات', 'gofly' ); ?></a>
				<?php endif; ?>
			</div>
		</div>
	</section>

	<?php
	get_template_part( 'template-parts/components/faq-section', null, array( 'layout' => 'stacked' ) );
endwhile;

get_footer();
