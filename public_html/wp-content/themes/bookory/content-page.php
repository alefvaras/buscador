<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	<?php
	/**
	 * Functions hooked in to bookory_page action
	 *
	 * @see bookory_page_header          - 10
	 * @see bookory_page_content         - 20
	 *
	 */
	do_action( 'bookory_page' );
	?>
</article><!-- #post-## -->
