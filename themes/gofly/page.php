<?php
/**
 * Default page: title banner and block-editor content.
 *
 * @package gofly
 */

get_header();

while ( have_posts() ) :
	the_post();
	get_template_part( 'template-parts/components/page-hero' );
	?>
	<div class="section section--roomy">
		<div class="entry-content"><?php the_content(); ?></div>
	</div>
	<?php
endwhile;

get_footer();
