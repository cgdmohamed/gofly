<?php
/**
 * Fallback template for archives and search results.
 *
 * @package gofly
 */

get_header();

get_template_part(
	'template-parts/components/page-hero',
	null,
	array(
		'title' => is_search()
			/* translators: %s: search query */
			? sprintf( __( 'نتائج البحث عن: %s', 'gofly' ), get_search_query() )
			: wp_strip_all_tags( get_the_archive_title() ),
	)
);
?>
<section class="section section--roomy">
	<?php if ( have_posts() ) : ?>
		<div class="grid grid--300">
			<?php
			while ( have_posts() ) :
				the_post();
				'package' === get_post_type()
					? get_template_part( 'template-parts/components/package-card' )
					: get_template_part( 'template-parts/components/post-card' );
			endwhile;
			?>
		</div>
		<?php gofly_pagination(); ?>
	<?php else : ?>
		<?php get_template_part( 'template-parts/components/empty-state' ); ?>
	<?php endif; ?>
</section>
<?php
get_footer();
