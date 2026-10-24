<?php
/**
 * Blog post card: cover, category · date, title, excerpt, "read more".
 *
 * @package gofly
 *
 * @var array $args {
 *     @type int  $post_id Post ID.
 *     @type bool $excerpt Show the excerpt and "read more".
 * }
 */

$args = wp_parse_args(
	$args,
	array(
		'post_id' => get_the_ID(),
		'excerpt' => true,
	)
);
$pid  = $args['post_id'];
$cat  = get_the_category( $pid );
?>
<a class="post-card" href="<?php echo esc_url( get_permalink( $pid ) ); ?>">
	<div class="post-card__media media-zoom"><?php echo gofly_card_image( $pid ); // phpcs:ignore ?></div>
	<div class="post-card__meta">
		<?php echo $cat ? esc_html( $cat[0]->name ) . ' · ' : ''; ?><?php echo esc_html( get_the_date( 'j F', $pid ) ); ?>
	</div>
	<h3 class="post-card__title"><?php echo esc_html( get_the_title( $pid ) ); ?></h3>
	<?php if ( $args['excerpt'] ) : ?>
		<p class="post-card__excerpt"><?php echo esc_html( get_the_excerpt( $pid ) ); ?></p>
		<span class="post-card__more"><?php esc_html_e( 'اقرأ المزيد', 'gofly' ); ?><?php echo gofly_icon( 'arrow_back' ); // phpcs:ignore ?></span>
	<?php endif; ?>
</a>
