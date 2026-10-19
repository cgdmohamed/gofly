<?php
/**
 * Included / not included lists (one item per line in the admin).
 *
 * @package gofly
 */

$lists = array_filter(
	array(
		array( __( 'يشمل البرنامج', 'gofly' ), gofly_lines( gofly_field( 'includes' ) ), 'check', 'yes' ),
		array( __( 'لا يشمل', 'gofly' ), gofly_lines( gofly_field( 'excludes' ) ), 'close', 'no' ),
	),
	fn( $list ) => ! empty( $list[1] )
);
if ( ! $lists ) {
	return;
}
?>
<div class="grid grid--260 pkg-group">
	<?php foreach ( $lists as list( $title, $items, $icon, $mod ) ) : ?>
		<div class="card card--pad">
			<h3 class="card__title"><?php echo esc_html( $title ); ?></h3>
			<ul class="checklist checklist--<?php echo esc_attr( $mod ); ?>">
				<?php foreach ( $items as $item ) : ?>
					<li><span class="checklist__icon"><?php echo gofly_icon( $icon ); // phpcs:ignore ?></span><?php echo esc_html( $item ); ?></li>
				<?php endforeach; ?>
			</ul>
		</div>
	<?php endforeach; ?>
</div>
