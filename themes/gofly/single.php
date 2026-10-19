<?php
/**
 * Single blog post: article, table of contents, package promo, related posts.
 *
 * @package gofly
 */

get_header();

while ( have_posts() ) :
	the_post();
	$gofly_cat   = get_the_category();
	$gofly_toc   = gofly_post_toc();
	$gofly_promo = gofly_field( 'related_package' );
	if ( ! $gofly_promo ) {
		$gofly_latest = get_posts( array( 'post_type' => 'package', 'posts_per_page' => 1, 'fields' => 'ids' ) );
		$gofly_promo  = $gofly_latest ? $gofly_latest[0] : 0;
	}
	$gofly_promo = $gofly_promo instanceof WP_Post ? $gofly_promo->ID : (int) $gofly_promo;
	$gofly_share = array(
		'whatsapp'  => 'https://wa.me/?text=' . rawurlencode( get_the_title() . ' ' . get_permalink() ),
		'x-twitter' => 'https://twitter.com/intent/tweet?url=' . rawurlencode( get_permalink() ) . '&text=' . rawurlencode( get_the_title() ),
		'facebook-f' => 'https://www.facebook.com/sharer/sharer.php?u=' . rawurlencode( get_permalink() ),
		'telegram'  => 'https://t.me/share/url?url=' . rawurlencode( get_permalink() ) . '&text=' . rawurlencode( get_the_title() ),
	);
	?>
	<section class="page-hero page-hero--post">
		<?php echo gofly_breadcrumb(); // phpcs:ignore ?>
		<?php if ( $gofly_cat ) : ?>
			<a class="chip chip--gold" href="<?php echo esc_url( get_category_link( $gofly_cat[0] ) ); ?>"><?php echo esc_html( $gofly_cat[0]->name ); ?></a>
		<?php endif; ?>
		<h1 class="page-hero__title"><?php the_title(); ?></h1>
		<div class="post-meta">
			<span><?php echo gofly_icon( 'person' ); // phpcs:ignore ?><?php the_author(); ?></span>
			<span><?php echo gofly_icon( 'calendar_today' ); // phpcs:ignore ?><?php echo esc_html( get_the_date() ); ?></span>
			<span><?php echo gofly_icon( 'schedule' ); // phpcs:ignore ?><?php echo esc_html( sprintf( /* translators: %d: minutes */ _n( '%d دقيقة قراءة', '%d دقائق قراءة', gofly_reading_time(), 'gofly' ), gofly_reading_time() ) ); ?></span>
		</div>
	</section>

	<section class="section section--roomy">
		<div class="post-layout">
			<article class="post-article">
				<?php if ( has_post_thumbnail() ) : ?>
					<div class="post-article__cover"><?php the_post_thumbnail( 'gofly-hero' ); ?></div>
				<?php endif; ?>
				<div class="entry-content"><?php the_content(); ?></div>

				<div class="post-tags">
					<?php
					$gofly_tags = get_the_tags();
					if ( $gofly_tags ) :
						?>
						<b><?php esc_html_e( 'الوسوم:', 'gofly' ); ?></b>
						<?php foreach ( $gofly_tags as $gofly_tag ) : ?>
							<a class="chip" href="<?php echo esc_url( get_tag_link( $gofly_tag ) ); ?>"><?php echo esc_html( $gofly_tag->name ); ?></a>
						<?php endforeach; ?>
					<?php endif; ?>
					<span class="post-tags__spacer"></span>
					<b><?php esc_html_e( 'شارك:', 'gofly' ); ?></b>
					<?php foreach ( $gofly_share as $gofly_network => $gofly_url ) : ?>
						<a class="icon-btn icon-btn--soft" href="<?php echo esc_url( $gofly_url ); ?>" target="_blank" rel="noopener" aria-label="<?php echo esc_attr( $gofly_network ); ?>"><?php echo gofly_brand_icon( $gofly_network ); // phpcs:ignore ?></a>
					<?php endforeach; ?>
				</div>

				<div class="author-box">
					<img src="<?php echo esc_url( gofly_logo_url( 'dark' ) ); ?>" alt="" width="84" height="84">
					<div>
						<b><?php the_author(); ?></b>
						<p><?php echo esc_html( get_the_author_meta( 'description' ) ? get_the_author_meta( 'description' ) : __( 'مستشارو سفر يكتبون من واقع تجربة الرحلات التي ننظمها.', 'gofly' ) ); ?></p>
					</div>
				</div>
			</article>

			<aside class="post-aside">
				<?php if ( $gofly_toc ) : ?>
					<div class="card toc">
						<b class="toc__title"><?php echo gofly_icon( 'list', 'is-gold' ); // phpcs:ignore ?><?php esc_html_e( 'محتوى المقال', 'gofly' ); ?></b>
						<?php foreach ( $gofly_toc as $gofly_i => $gofly_heading ) : ?>
							<a class="toc__link" href="#<?php echo esc_attr( $gofly_heading['id'] ); ?>"><span class="toc__num"><?php echo (int) $gofly_i + 1; ?></span><?php echo esc_html( $gofly_heading['title'] ); ?></a>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>

				<?php
				if ( $gofly_promo ) :
					$gofly_pkg = gofly_package( $gofly_promo );
					?>
					<a class="promo-card" href="<?php echo esc_url( get_permalink( $gofly_promo ) ); ?>">
						<div class="promo-card__media media-zoom"><?php echo get_the_post_thumbnail( $gofly_promo, 'gofly-card', array( 'loading' => 'lazy' ) ); ?></div>
						<div class="promo-card__body">
							<div class="promo-card__eyebrow"><?php esc_html_e( 'باقة جاهزة', 'gofly' ); ?></div>
							<b class="promo-card__title"><?php echo esc_html( get_the_title( $gofly_promo ) ); ?></b>
							<?php if ( $gofly_pkg['price'] ) : ?>
								<div class="promo-card__price"><?php esc_html_e( 'ابتداءً من', 'gofly' ); ?> <?php echo gofly_price( $gofly_pkg['price'] ); // phpcs:ignore ?> <?php esc_html_e( 'للفرد', 'gofly' ); ?></div>
							<?php endif; ?>
							<span class="btn btn--gold btn--sm"><?php esc_html_e( 'شاهد البرنامج', 'gofly' ); ?> <?php echo gofly_icon( 'arrow_back' ); // phpcs:ignore ?></span>
						</div>
					</a>
				<?php endif; ?>
			</aside>
		</div>
	</section>

	<?php
	$gofly_related = get_posts(
		array(
			'posts_per_page' => 3,
			'post__not_in'   => array( get_the_ID() ),
			'category__in'   => wp_list_pluck( $gofly_cat, 'term_id' ),
			'fields'         => 'ids',
		)
	);
	if ( $gofly_related ) :
		$gofly_blog = get_option( 'page_for_posts' );
		?>
		<section class="section section--related">
			<?php
			get_template_part(
				'template-parts/components/section-head',
				null,
				array(
					'eyebrow' => __( 'قد يعجبك', 'gofly' ),
					'title'   => __( 'مقالات ذات صلة', 'gofly' ),
					'link'    => $gofly_blog ? array( 'url' => get_permalink( $gofly_blog ), 'title' => __( 'كل المقالات', 'gofly' ) ) : array(),
				)
			);
			?>
			<div class="grid grid--260">
				<?php
				foreach ( $gofly_related as $gofly_pid ) {
					get_template_part( 'template-parts/components/post-card', null, array( 'post_id' => $gofly_pid, 'excerpt' => false ) );
				}
				?>
			</div>
		</section>
	<?php endif; ?>
	<?php
endwhile;

get_footer();
