<header id="masthead" class="site-header header-1" role="banner">
    <div class="header-container">
        <div class="container header-main">
            <div class="header-left">
                <?php
                bookory_site_branding();
                if (bookory_is_woocommerce_activated()) {
                    ?>
                    <div class="site-header-cart header-cart-mobile">
                        <?php bookory_cart_link(); ?>
                    </div>
                    <?php
                }
                ?>
                <?php bookory_mobile_nav_button(); ?>
            </div>
            <div class="header-center">
                <?php bookory_primary_navigation(); ?>
            </div>
            <div class="header-right desktop-hide-down">
                <div class="header-group-action">
                    <?php
                    bookory_header_account();
                    if (bookory_is_woocommerce_activated()) {
                        bookory_header_wishlist();
                        bookory_header_cart();
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
</header><!-- #masthead -->
