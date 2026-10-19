<?php
/**
 * Not found.
 *
 * @package gofly
 */

get_header();
get_template_part( 'template-parts/components/page-hero', null, array( 'title' => __( 'الصفحة غير موجودة', 'gofly' ), 'subtitle' => __( 'ربما نُقلت الصفحة أو حُذفت.', 'gofly' ) ) );
?>
<section class="section section--roomy">
	<?php
	get_template_part(
		'template-parts/components/empty-state',
		null,
		array(
			'icon'   => 'travel_explore',
			'title'  => __( 'لم نجد ما تبحث عنه', 'gofly' ),
			'text'   => __( 'تصفّح باقاتنا أو عد إلى الصفحة الرئيسية.', 'gofly' ),
			'button' => array(
				'url'   => get_post_type_archive_link( 'package' ),
				'title' => __( 'تصفح الباقات', 'gofly' ),
			),
		)
	);
	?>
</section>
<?php
get_footer();
