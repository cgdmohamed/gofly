<?php
/**
 * Home page. Every section is edited on the page set as "Homepage"
 * (Settings → Reading) and hides itself when left empty.
 *
 * @package gofly
 */

get_header();

$gofly_sections = array( 'hero', 'partners', 'services', 'destinations', 'packages', 'steps', 'stats', 'gallery', 'instagram', 'testimonials', 'blog' );
foreach ( $gofly_sections as $gofly_section ) {
	get_template_part( 'template-parts/home/' . $gofly_section );
}
get_template_part( 'template-parts/components/faq-section', null, array( 'layout' => 'split' ) );

get_footer();
