<?php
/**
 * Latest blog posts.
 *
 * @package gofly
 */

$ids = get_posts(
	array(
		'posts_per_page'      => 3,
		'ignore_sticky_posts' => true,
		'fields'              => 'ids',
	)
);
if ( ! $ids ) {
	return;
}
$blog = get_option( 'page_for_posts' );
?>
<section class="section">
	<?php
	get_template_part(
		'template-parts/components/section-head',
		null,
		array(
			'eyebrow' => gofly_field( 'blog_eyebrow' ),
			'title'   => gofly_field( 'blog_title' ),
			'link'    => $blog ? array( 'url' => get_permalink( $blog ), 'title' => __( 'كل المقالات', 'gofly' ) ) : array(),
		)
	);
	?>
	<div class="grid grid--260">
		<?php
		foreach ( $ids as $pid ) {
			get_template_part( 'template-parts/components/post-card', null, array( 'post_id' => $pid ) );
		}
		?>
	</div>
</section>
