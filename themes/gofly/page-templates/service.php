<?php
/**
 * Template Name: صفحة خدمة
 *
 * Landing page for a service (international licence, car hire, visas…):
 * intro, benefits, steps, required documents beside a request form, FAQ.
 * Every section hides itself when its fields are empty.
 *
 * @package gofly
 */

get_header();

while ( have_posts() ) :
	the_post();
	get_template_part( 'template-parts/components/page-hero' );

	$gofly_sections = array(
		'features' => 'value',
		'steps'    => 'step',
	);
	$gofly_docs     = gofly_lines( gofly_field( 'docs_items' ) );
	$gofly_form_id  = gofly_field( 'form_id' );
	?>
	<?php if ( gofly_field( 'intro_title' ) ) : ?>
		<section class="section section--roomy">
			<div class="split">
				<?php if ( gofly_field( 'intro_image' ) || has_post_thumbnail() ) : ?>
					<div class="split__media"><?php echo gofly_image( gofly_field( 'intro_image' ), 'gofly-hero' ) ?: get_the_post_thumbnail( null, 'gofly-hero' ); // phpcs:ignore ?></div>
				<?php endif; ?>
				<div class="split__body">
					<div class="eyebrow"><?php echo esc_html( gofly_field( 'intro_eyebrow' ) ); ?></div>
					<h2 class="section-title"><?php echo esc_html( gofly_field( 'intro_title' ) ); ?></h2>
					<div class="split__text"><?php echo wp_kses_post( gofly_field( 'intro_text' ) ); ?></div>
					<div class="btn-row">
						<a class="btn btn--gold btn--lg" href="#service-request"><?php echo gofly_icon( 'edit_note' ); // phpcs:ignore ?><?php echo esc_html( gofly_field( 'form_title', false, __( 'اطلب الخدمة الآن', 'gofly' ) ) ); ?></a>
						<a class="btn btn--outline btn--lg" href="<?php echo esc_url( gofly_whatsapp_url() ); ?>" target="_blank" rel="noopener"><?php echo gofly_brand_icon( 'whatsapp' ); // phpcs:ignore ?><?php esc_html_e( 'استفسر عبر واتساب', 'gofly' ); ?></a>
					</div>
				</div>
			</div>
		</section>
	<?php endif; ?>

	<?php
	foreach ( $gofly_sections as $gofly_key => $gofly_variant ) :
		$gofly_items = (array) gofly_field( $gofly_key . '_items', false, array() );
		if ( ! $gofly_items ) {
			continue;
		}
		?>
		<section class="section<?php echo 'steps' === $gofly_key ? ' section--soft' : ''; ?>">
			<?php
			get_template_part(
				'template-parts/components/section-head',
				null,
				array(
					'eyebrow' => gofly_field( $gofly_key . '_eyebrow' ),
					'title'   => gofly_field( $gofly_key . '_title' ),
				)
			);
			?>
			<div class="grid <?php echo 'steps' === $gofly_key ? 'grid--230' : 'grid--240'; ?>">
				<?php
				foreach ( $gofly_items as $gofly_i => $gofly_item ) {
					get_template_part(
						'template-parts/components/feature-card',
						null,
						array(
							'variant' => $gofly_variant,
							'number'  => 'step' === $gofly_variant ? $gofly_i + 1 : 0,
						) + $gofly_item
					);
				}
				?>
			</div>
		</section>
	<?php endforeach; ?>

	<section class="section" id="service-request">
		<div class="contact-layout">
			<?php if ( $gofly_docs ) : ?>
				<div class="card card--pad service-docs">
					<h2 class="card__title"><?php echo esc_html( gofly_field( 'docs_title', false, __( 'المستندات المطلوبة', 'gofly' ) ) ); ?></h2>
					<ul class="checklist checklist--yes">
						<?php foreach ( $gofly_docs as $gofly_doc ) : ?>
							<li><span class="checklist__icon"><?php echo gofly_icon( 'check' ); // phpcs:ignore ?></span><?php echo esc_html( $gofly_doc ); ?></li>
						<?php endforeach; ?>
					</ul>
					<?php if ( gofly_field( 'docs_note' ) ) : ?>
						<p class="service-docs__note"><?php echo gofly_icon( 'info' ); // phpcs:ignore ?><?php echo esc_html( gofly_field( 'docs_note' ) ); ?></p>
					<?php endif; ?>
				</div>
			<?php endif; ?>

			<div class="card contact-form">
				<h2 class="contact-form__title"><?php echo esc_html( gofly_field( 'form_title', false, __( 'اطلب الخدمة الآن', 'gofly' ) ) ); ?></h2>
				<p class="contact-form__text"><?php echo esc_html( gofly_field( 'form_text' ) ); ?></p>
				<?php if ( $gofly_form_id || current_user_can( 'edit_pages' ) ) : ?>
					<div class="form-skin"><?php echo gofly_form( $gofly_form_id ); // phpcs:ignore ?></div>
				<?php endif; ?>
				<?php if ( ! $gofly_form_id ) : ?>
					<a class="btn btn--gold btn--block btn--lg" href="<?php echo esc_url( gofly_whatsapp_url( get_the_title() ) ); ?>" target="_blank" rel="noopener"><?php echo gofly_brand_icon( 'whatsapp' ); // phpcs:ignore ?><?php esc_html_e( 'أرسل طلبك عبر واتساب', 'gofly' ); ?></a>
				<?php endif; ?>
				<div class="contact-form__privacy"><?php echo gofly_icon( 'lock' ); // phpcs:ignore ?><?php esc_html_e( 'بياناتك محمية ولن تُشارك مع أي طرف.', 'gofly' ); ?></div>
			</div>
		</div>
	</section>

	<?php get_template_part( 'template-parts/components/faq-section' ); ?>

	<?php if ( get_the_content() ) : ?>
		<section class="section section--tight"><div class="entry-content"><?php the_content(); ?></div></section>
	<?php endif; ?>
	<?php
endwhile;

get_footer();
