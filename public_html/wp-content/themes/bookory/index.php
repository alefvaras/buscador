<?php

get_header(); ?>

	<div id="primary" class="content-area">
		<main id="main" class="site-main" role="main">

		<?php
		if ( have_posts() ) :

			get_template_part( 'loop' );

		else :

			get_template_part( 'content', 'none' );

		endif;
		?>

		</main><!-- #main -->
	</div><!-- #primary -->

<?php

/**
 * Functions hooked in to bookory_sidebar action
 *
 * @see bookory_get_sidebar      - 10
 */
do_action( 'bookory_sidebar' );
get_footer();
