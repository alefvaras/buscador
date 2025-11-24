<div class="column-item post-style-1">
    <div class="post-inner">
        <?php
        bookory_post_thumbnail('bookory-post-grid', false);
        ?>
        <div class="entry-content">
            <div class="entry-meta">
                <?php bookory_post_meta(); ?>
            </div>
            <?php the_title('<h3 class="entry-title"><a href="' . esc_url(get_permalink()) . '" rel="bookmark">', '</a></h3>'); ?>
            <?php bookory_meta_footer(); ?>
        </div>
    </div>
</div>
