<?php
/**
 * Stats band.
 *
 * @package gofly
 */

if ( ! gofly_field( 'stats_show', false, true ) ) {
	return;
}
?>
<section class="section">
	<?php get_template_part( 'template-parts/components/stats', null, array( 'variant' => 'band' ) ); ?>
</section>
