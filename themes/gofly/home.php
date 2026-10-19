<?php
/**
 * Blog index (the page set as "Posts page"). The first post on page one is
 * shown as the featured article; sticky posts come first automatically.
 *
 * @package gofly
 */

get_header();
get_template_part( 'template-parts/components/page-hero', null, array( 'title' => get_the_title( get_option( 'page_for_posts' ) ) ) );
?>
<section class="section section--roomy">
	<?php if ( have_posts() ) : ?>
		<?php
		if ( ! is_paged() ) :
			the_post();
			$gofly_cat = get_the_category();
			?>
			<a class="card featured-post" href="<?php the_permalink(); ?>">
				<div class="featured-post__media media-zoom"><?php the_post_thumbnail( 'gofly-hero' ); ?></div>
				<div class="featured-post__body">
					<span class="eyebrow"><?php esc_html_e( 'مقال مميز', 'gofly' ); ?><?php echo $gofly_cat ? ' · ' . esc_html( $gofly_cat[0]->name ) : ''; ?></span>
					<h2 class="featured-post__title"><?php the_title(); ?></h2>
					<p class="featured-post__excerpt"><?php echo esc_html( get_the_excerpt() ); ?></p>
					<span class="post-card__more"><?php esc_html_e( 'اقرأ المقال', 'gofly' ); ?><?php echo gofly_icon( 'arrow_back' ); // phpcs:ignore ?></span>
				</div>
			</a>
		<?php endif; ?>

		<?php if ( have_posts() ) : ?>
			<?php
			get_template_part(
				'template-parts/components/section-head',
				null,
				array(
					'eyebrow' => __( 'أحدث المقالات', 'gofly' ),
					'title'   => __( 'نصائح قبل السفر', 'gofly' ),
				)
			);
			?>
			<div class="grid grid--300">
				<?php
				while ( have_posts() ) :
					the_post();
					get_template_part( 'template-parts/components/post-card' );
				endwhile;
				?>
			</div>
		<?php endif; ?>
		<?php gofly_pagination(); ?>
	<?php else : ?>
		<?php get_template_part( 'template-parts/components/empty-state', null, array( 'title' => __( 'لا توجد مقالات بعد', 'gofly' ), 'text' => '' ) ); ?>
	<?php endif; ?>
</section>
<?php
get_footer();
