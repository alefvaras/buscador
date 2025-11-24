<?php
get_header(); ?>

    <div id="primary" class="content">
        <main id="main" class="site-main" role="main">
            <div class="error-404 not-found">
                <div class="page-content">
                    <header class="page-header">
                        <h1 class="title"><?php esc_html_e('404','bookory');?></h1>
                        <h3 class="sub-title"><?php esc_html_e('OOPS! THAT PAGE CAN\'T BE FOUND.', 'bookory'); ?></h3>
                    </header><!-- .page-header -->
                    <div class="error-text">
                        <span><?php esc_html_e('It looks like nothing was found at this location. You can either go back to the last page or go to homepage.', 'bookory') ?></span>
                    </div>
                    <div class="error-btn">
                        <a href="<?php echo esc_url(home_url('/')); ?>" class="button-404">
                            <span class="button-text"><?php esc_html_e('Homepage', 'bookory'); ?><i class="bookory-icon-angle-right"></i></span>
                        </a>
                    </div>
                </div><!-- .page-content -->
            </div><!-- .error-404 -->
        </main><!-- #main -->
    </div><!-- #primary -->
<?php

get_footer();
