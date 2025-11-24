<article id="post-<?php the_ID(); ?>" <?php post_class('article-default'); ?>>
    <div class="post-inner">
        <?php bookory_post_thumbnail('post-thumbnail',false); ?>
        <div class="post-content">
            <?php
            /**
             * Functions hooked in to bookory_loop_post action.
             *
             * @see bookory_post_header          - 15
             * @see bookory_post_content         - 30
             */
            do_action('bookory_loop_post');
            ?>
        </div>
    </div>
</article><!-- #post-## -->