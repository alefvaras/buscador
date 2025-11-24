<?php

if (!function_exists('bookory_before_content')) {
    /**
     * Before Content
     * Wraps all WooCommerce content in wrappers which match the theme markup
     *
     * @return  void
     * @since   1.0.0
     */
    function bookory_before_content() {
        echo <<<HTML
<div id="primary" class="content-area">
    <main id="main" class="site-main" role="main">
HTML;

    }
}


if (!function_exists('bookory_after_content')) {
    /**
     * After Content
     * Closes the wrapping divs
     *
     * @return  void
     * @since   1.0.0
     */
    function bookory_after_content() {
        echo <<<HTML
	</main><!-- #main -->
</div><!-- #primary -->
HTML;

        do_action('bookory_sidebar');
    }
}

if (!function_exists('bookory_cart_link_fragment')) {
    /**
     * Cart Fragments
     * Ensure cart contents update when products are added to the cart via AJAX
     *
     * @param array $fragments Fragments to refresh via AJAX.
     *
     * @return array            Fragments to refresh via AJAX
     */
    function bookory_cart_link_fragment($fragments) {
        ob_start();
        bookory_cart_link();
        $fragments['a.cart-contents'] = ob_get_clean();

        ob_start();

        return $fragments;
    }
}

if (!function_exists('bookory_cart_link')) {
    /**
     * Cart Link
     * Displayed a link to the cart including the number of items present and the cart total
     *
     * @return void
     * @since  1.0.0
     */
    function bookory_cart_link() {
        $cart = WC()->cart;
        ?>
        <a class="cart-contents" href="<?php echo esc_url(wc_get_cart_url()); ?>" title="<?php esc_attr_e('View your shopping cart', 'bookory'); ?>">
            <?php if ($cart): ?>
                <span class="count"><?php echo wp_kses_data(sprintf(_n('%d', '%d', WC()->cart->get_cart_contents_count(), 'bookory'), WC()->cart->get_cart_contents_count())); ?></span>
                <?php echo WC()->cart->get_cart_subtotal(); ?>
            <?php endif; ?>
        </a>
        <?php
    }
}

class Bookory_Custom_Walker_Category extends Walker_Category {

    public function start_el(&$output, $category, $depth = 0, $args = array(), $id = 0) {
        /** This filter is documented in wp-includes/category-template.php */
        $cat_name = apply_filters(
            'list_cats',
            esc_attr($category->name),
            $category
        );

        // Don't generate an element if the category name is empty.
        if (!$cat_name) {
            return;
        }

        $link = '<a class="pf-value" href="' . esc_url(get_term_link($category)) . '" data-val="' . esc_attr($category->slug) . '" data-title="' . esc_attr($category->name) . '" ';
        if ($args['use_desc_for_title'] && !empty($category->description)) {
            /**
             * Filters the category description for display.
             *
             * @param string $description Category description.
             * @param object $category Category object.
             *
             * @since 1.2.0
             *
             */
            $link .= 'title="' . esc_attr(strip_tags(apply_filters('category_description', $category->description, $category))) . '"';
        }

        $link .= '>';
        $link .= $cat_name . '</a>';

        if (!empty($args['feed_image']) || !empty($args['feed'])) {
            $link .= ' ';

            if (empty($args['feed_image'])) {
                $link .= '(';
            }

            $link .= '<a href="' . esc_url(get_term_feed_link($category->term_id, $category->taxonomy, $args['feed_type'])) . '"';

            if (empty($args['feed'])) {
                $alt = ' alt="' . sprintf(esc_html__('Feed for all posts filed under %s', 'bookory'), $cat_name) . '"';
            } else {
                $alt  = ' alt="' . $args['feed'] . '"';
                $name = $args['feed'];
                $link .= empty($args['title']) ? '' : $args['title'];
            }

            $link .= '>';

            if (empty($args['feed_image'])) {
                $link .= $name;
            } else {
                $link .= "<img src='" . $args['feed_image'] . "'$alt" . ' />';
            }
            $link .= '</a>';

            if (empty($args['feed_image'])) {
                $link .= ')';
            }
        }

        if (!empty($args['show_count'])) {
            $link .= ' (' . number_format_i18n($category->count) . ')';
        }
        if ('list' == $args['style']) {
            $output      .= "\t<li";
            $css_classes = array(
                'cat-item',
                'cat-item-' . $category->term_id,
            );

            if (!empty($args['current_category'])) {
                // 'current_category' can be an array, so we use `get_terms()`.
                $_current_terms = get_terms(
                    $category->taxonomy,
                    array(
                        'include'    => $args['current_category'],
                        'hide_empty' => false,
                    )
                );

                foreach ($_current_terms as $_current_term) {
                    if ($category->term_id == $_current_term->term_id) {
                        $css_classes[] = 'current-cat pf-active';
                    } elseif ($category->term_id == $_current_term->parent) {
                        $css_classes[] = 'current-cat-parent';
                    }
                    while ($_current_term->parent) {
                        if ($category->term_id == $_current_term->parent) {
                            $css_classes[] = 'current-cat-ancestor';
                            break;
                        }
                        $_current_term = get_term($_current_term->parent, $category->taxonomy);
                    }
                }
            }

            /**
             * Filters the list of CSS classes to include with each category in the list.
             *
             * @param array $css_classes An array of CSS classes to be applied to each list item.
             * @param object $category Category data object.
             * @param int $depth Depth of page, used for padding.
             * @param array $args An array of wp_list_categories() arguments.
             *
             * @since 4.2.0
             *
             * @see wp_list_categories()
             *
             */
            $css_classes = implode(' ', apply_filters('category_css_class', $css_classes, $category, $depth, $args));

            $output .= ' class="' . $css_classes . '"';
            $output .= ">$link\n";
        } elseif (isset($args['separator'])) {
            $output .= "\t$link" . $args['separator'] . "\n";
        } else {
            $output .= "\t$link<br />\n";
        }
    }
}

if (!function_exists('bookory_show_categories_dropdown')) {
    function bookory_show_categories_dropdown() {
        static $id = 0;
        $args  = array(
            'hide_empty' => 1,
            'parent'     => 0
        );
        $terms = get_terms('product_cat', $args);
        if (!empty($terms) && !is_wp_error($terms)) {
            ?>
            <div class="search-by-category input-dropdown">
                <div class="input-dropdown-inner bookory-scroll-content">
                    <!--                    <input type="hidden" name="product_cat" value="0">-->
                    <a href="#" data-val="0"><span><?php esc_html_e('All category', 'bookory'); ?></span></a>
                    <?php
                    $args_dropdown = array(
                        'id'               => 'product_cat' . $id++,
                        'show_count'       => 0,
                        'class'            => 'dropdown_product_cat_ajax',
                        'show_option_none' => esc_html__('All category', 'bookory'),
                    );
                    wc_product_dropdown_categories($args_dropdown);
                    ?>
                    <div class="list-wrapper bookory-scroll">
                        <ul class="bookory-scroll-content">
                            <li class="d-none">
                                <a href="#" data-val="0"><?php esc_html_e('All category', 'bookory'); ?></a></li>
                            <?php
                            if (!apply_filters('bookory_show_only_parent_categories_dropdown', false)) {
                                $args_list = array(
                                    'title_li'           => false,
                                    'taxonomy'           => 'product_cat',
                                    'use_desc_for_title' => false,
                                    'walker'             => new Bookory_Custom_Walker_Category(),
                                );
                                wp_list_categories($args_list);
                            } else {
                                foreach ($terms as $term) {
                                    ?>
                                    <li>
                                        <a href="#" data-val="<?php echo esc_attr($term->slug); ?>"><?php echo esc_attr($term->name); ?></a>
                                    </li>
                                    <?php
                                }
                            }
                            ?>
                        </ul>
                    </div>
                </div>
            </div>
            <?php
        }
    }
}

if (!function_exists('bookory_product_search')) {
    /**
     * Display Product Search
     *
     * @return void
     * @uses  bookory_is_woocommerce_activated() check if WooCommerce is activated
     * @since  1.0.0
     */
    function bookory_product_search($hide_search = false) {
        if (bookory_is_woocommerce_activated()) {
            static $index = 0;
            $index++;
            ?>
            <div class="site-search ajax-search">
                <div class="widget woocommerce widget_product_search">
                    <div class="ajax-search-result d-none"></div>
                    <form role="search" method="get" class="woocommerce-product-search" action="<?php echo esc_url(home_url('/')); ?>">
                        <label class="screen-reader-text" for="woocommerce-product-search-field-<?php echo isset($index) ? absint($index) : 0; ?>"><?php esc_html_e('Search for:', 'bookory'); ?></label>
                        <input type="search" id="woocommerce-product-search-field-<?php echo isset($index) ? absint($index) : 0; ?>" class="search-field" placeholder="<?php echo esc_attr__('Search products&hellip;', 'bookory'); ?>" autocomplete="off" value="<?php echo get_search_query(); ?>" name="s"/>
                        <button type="submit" value="<?php echo esc_attr_x('Search', 'submit button', 'bookory'); ?>"><?php echo esc_html_x('Search', 'submit button', 'bookory'); ?></button>
                        <input type="hidden" name="post_type" value="product"/>
                        <?php
                        if (!$hide_search) {
                            bookory_show_categories_dropdown();
                        }
                        ?>
                    </form>
                </div>
            </div>
            <?php
        }
    }
}

if (!function_exists('bookory_header_cart')) {
    /**
     * Display Header Cart
     *
     * @return void
     * @uses  bookory_is_woocommerce_activated() check if WooCommerce is activated
     * @since  1.0.0
     */
    function bookory_header_cart() {
        if (bookory_is_woocommerce_activated()) {
            if (!bookory_get_theme_option('show_header_cart', true)) {
                return;
            }
            ?>
            <div class="site-header-cart menu">
                <?php bookory_cart_link(); ?>
                <?php

                if (!apply_filters('woocommerce_widget_cart_is_hidden', is_cart() || is_checkout())) {

                    if (bookory_get_theme_option('header_cart_dropdown', 'side') == 'side') {
                        add_action('wp_footer', 'bookory_header_cart_side');
                    } else {
                        the_widget('WC_Widget_Cart', 'title=');
                    }
                }
                ?>
            </div>
            <?php
        }
    }
}

if (!function_exists('bookory_header_cart_side')) {
    function bookory_header_cart_side() {
        if (bookory_is_woocommerce_activated()) {
            ?>
            <div class="site-header-cart-side">
                <div class="cart-side-heading">
                    <span class="cart-side-title"><?php echo esc_html__('Shopping cart', 'bookory'); ?></span>
                    <a href="#" class="close-cart-side"><?php echo esc_html__('close', 'bookory') ?></a></div>
                <?php the_widget('WC_Widget_Cart', 'title='); ?>
            </div>
            <div class="cart-side-overlay"></div>
            <?php
        }
    }
}

if (!function_exists('bookory_upsell_display')) {
    /**
     * Upsells
     * Replace the default upsell function with our own which displays the correct number product columns
     *
     * @return  void
     * @since   1.0.0
     * @uses    woocommerce_upsell_display()
     */
    function bookory_upsell_display() {
        $columns = apply_filters('bookory_upsells_columns', 6);
        if (is_active_sidebar('sidebar-woocommerce-detail')) {
            $columns = 4;
        }
        woocommerce_upsell_display(-1, $columns);
    }
}

if (!function_exists('bookory_product_columns_wrapper')) {
    /**
     * Product columns wrapper
     *
     * @return  void
     * @since   2.2.0
     */
    function bookory_product_columns_wrapper() {
        $columns = bookory_loop_columns();
        echo '<div class="columns-' . absint($columns) . '">';
    }
}

if (!function_exists('bookory_loop_columns')) {
    /**
     * Default loop columns on product archives
     *
     * @return integer products per row
     * @since  1.0.0
     */
    function bookory_loop_columns() {
        $columns = 3; // 3 products per row

        if (function_exists('wc_get_default_products_per_row')) {
            $columns = wc_get_default_products_per_row();
        }

        return apply_filters('bookory_loop_columns', $columns);
    }
}

if (!function_exists('bookory_product_columns_wrapper_close')) {
    /**
     * Product columns wrapper close
     *
     * @return  void
     * @since   2.2.0
     */
    function bookory_product_columns_wrapper_close() {
        echo '</div>';
    }
}

if (!function_exists('bookory_shop_messages')) {
    /**
     * ThemeBase shop messages
     *
     * @since   1.4.4
     * @uses    bookory_do_shortcode
     */
    function bookory_shop_messages() {
        if (!is_checkout()) {
            echo bookory_do_shortcode('woocommerce_messages');
        }
    }
}

if (!function_exists('bookory_woocommerce_pagination')) {
    /**
     * ThemeBase WooCommerce Pagination
     * WooCommerce disables the product pagination inside the woocommerce_product_subcategories() function
     * but since ThemeBase adds pagination before that function is excuted we need a separate function to
     * determine whether or not to display the pagination.
     *
     * @since 1.4.4
     */
    function bookory_woocommerce_pagination() {
        if (woocommerce_products_will_display()) {
            woocommerce_pagination();
        }
    }
}


if (!function_exists('bookory_single_product_pagination')) {
    /**
     * Single Product Pagination
     *
     * @since 2.3.0
     */
    function bookory_single_product_pagination() {

        // Show only products in the same category?
        $in_same_term   = apply_filters('bookory_single_product_pagination_same_category', true);
        $excluded_terms = apply_filters('bookory_single_product_pagination_excluded_terms', '');
        $taxonomy       = apply_filters('bookory_single_product_pagination_taxonomy', 'product_cat');

        $previous_product = bookory_get_previous_product($in_same_term, $excluded_terms, $taxonomy);
        $next_product     = bookory_get_next_product($in_same_term, $excluded_terms, $taxonomy);

        if ((!$previous_product && !$next_product) || !is_product()) {
            return;
        }

        ?>
        <div class="bookory-product-pagination-wrap">
            <nav class="bookory-product-pagination" aria-label="<?php esc_attr_e('More products', 'bookory'); ?>">
                <?php if ($previous_product) : ?>
                    <a href="<?php echo esc_url($previous_product->get_permalink()); ?>" rel="prev">
                        <span class="pagination-prev "><i class="bookory-icon-left-arrow"></i><?php echo esc_html__('Prev', 'bookory'); ?></span>
                        <div class="product-item">
                            <?php echo sprintf('%s', $previous_product->get_image()); ?>
                            <div class="bookory-product-pagination-content">
                                <span class="bookory-product-pagination__title"><?php echo sprintf('%s', $previous_product->get_name()); ?></span>
                                <?php if ($price_html = $previous_product->get_price_html()) :
                                    printf('<span class="price">%s</span>', $price_html);
                                endif; ?>
                            </div>
                        </div>
                    </a>
                <?php endif; ?>

                <?php if ($next_product) : ?>
                    <a href="<?php echo esc_url($next_product->get_permalink()); ?>" rel="next">
                        <span class="pagination-next"><?php echo esc_html__('Next', 'bookory'); ?><i class="bookory-icon-right-arrow"></i></span>
                        <div class="product-item">
                            <?php echo sprintf('%s', $next_product->get_image()); ?>
                            <div class="bookory-product-pagination-content">
                                <span class="bookory-product-pagination__title"><?php echo sprintf('%s', $next_product->get_name()); ?></span>
                                <?php if ($price_html = $next_product->get_price_html()) :
                                    printf('<span class="price">%s</span>', $price_html);
                                endif; ?>
                            </div>
                        </div>
                    </a>
                <?php endif; ?>
            </nav><!-- .bookory-product-pagination -->
        </div>
        <?php

    }
}

if (!function_exists('bookory_sticky_single_add_to_cart')) {
    /**
     * Sticky Add to Cart
     *
     * @since 2.3.0
     */
    function bookory_sticky_single_add_to_cart() {
        global $product;

        if (!is_product() || get_option('woocommerce_coming_soon') == 'yes') {
            return;
        }

        $show = false;

        if ($product->is_purchasable() && $product->is_in_stock()) {
            $show = true;
        } else if ($product->is_type('external')) {
            $show = true;
        }

        if (!$show) {
            return;
        }

        $params = apply_filters(
            'bookory_sticky_add_to_cart_params', array(
                'trigger_class' => 'entry-summary',
            )
        );

        wp_localize_script('bookory-sticky-add-to-cart', 'bookory_sticky_add_to_cart_params', $params);
        ?>

        <section class="bookory-sticky-add-to-cart">
            <div class="col-full">
                <div class="bookory-sticky-add-to-cart__content">
                    <?php echo woocommerce_get_product_thumbnail(); ?>
                    <div class="bookory-sticky-add-to-cart__content-product-info">
						<span class="bookory-sticky-add-to-cart__content-title"><?php esc_attr_e('You\'re viewing:', 'bookory'); ?>
							<strong><?php the_title(); ?></strong></span>
                        <span class="bookory-sticky-add-to-cart__content-price"><?php echo sprintf('%s', $product->get_price_html()); ?></span>
                        <?php echo wc_get_rating_html($product->get_average_rating()); ?>
                    </div>
                    <?php
                    $class = implode(
                        ' ',
                        array_filter(
                            array(
                                'bookory-sticky-add-to-cart__content-button',
                                'button',
                                'alt',
                                'product_type_' . $product->get_type(),
                                $product->is_purchasable() && $product->is_in_stock() ? 'add_to_cart_button' : '',
                                $product->supports('ajax_add_to_cart') && $product->is_purchasable() && $product->is_in_stock() ? 'ajax_add_to_cart' : '',
                            )
                        )
                    );
                    woocommerce_template_loop_add_to_cart(['class' => $class]);
                    ?>
                </div>
            </div>
        </section><!-- .bookory-sticky-add-to-cart -->
        <?php
    }
}

if (!function_exists('bookory_woocommerce_product_list_bottom')) {
    function bookory_woocommerce_product_list_bottom() {
        ?>
        <div class="product-caption-bottom">
            <?php
            woocommerce_template_loop_add_to_cart();
            bookory_woocommerce_group_action();
            ?>
        </div>
        <?php
    }
}

if (!function_exists('bookory_woocommerce_product_list_add_to_cart')) {
    function bookory_woocommerce_product_list_add_to_cart() {
        ?>
        <div class="product-caption-bottom">
            <?php woocommerce_template_loop_add_to_cart(); ?>
        </div>
        <?php
    }
}

if (!function_exists('bookory_woocommerce_product_loop_unit')) {
    function bookory_woocommerce_product_loop_unit() {
        global $product;
        $unit = get_post_meta($product->get_id(), '_deal_unit', true);
        if (empty($unit)) {
            return;
        }
        ?>
        <div class="product-unit">
            <span class="title"><?php echo esc_html__('Unit:', 'bookory'); ?></span>
            <span class="value"><?php echo esc_html($unit); ?></span>
        </div>
        <?php
    }
}

if (!function_exists('bookory_add_quantity_field')) {
    function bookory_add_quantity_field() {
        global $product;

        if (!$product->is_sold_individually() && 'variable' != $product->get_type() && $product->is_in_stock()) {
            ?>
            <div class="product-input-quantity">
                <?php
                woocommerce_quantity_input(array('min_value' => 1, 'max_value' => $product->backorders_allowed() ? '' : $product->get_stock_quantity()));
                bookory_woocommerce_product_loop_unit();
                ?>
            </div>
            <?php
        }
    }
}

if (!function_exists('bookory_woocommerce_product_loop_action')) {
    function bookory_woocommerce_product_loop_action() {
        ?>
        <div class="group-action">
            <div class="shop-action">
                <?php do_action('bookory_woocommerce_product_loop_action'); ?>
            </div>
        </div>
        <?php
    }
}

if (!function_exists('bookory_stock_label')) {
    function bookory_stock_label() {
 
    }
}

if (!function_exists('bookory_single_product_summary_top')) {
    function bookory_single_product_summary_top() {
        ?>
        <div class="entry-summary-top">
            <?php
            bookory_stock_label();
            bookory_single_product_pagination();
            ?>
        </div>
        <?php
    }
}

if (!function_exists('bookory_single_product_after_title')) {
    function bookory_single_product_after_title() {
        global $product;
        ?>
        <div class="product_after_title">
            <?php
            bookory_woocommerce_single_author();
            woocommerce_template_single_rating();
            if (wc_product_sku_enabled() && ($product->get_sku() || $product->is_type('variable'))) :
                $sku = $product->get_sku() ? $product->get_sku() : esc_html__('N/A', 'bookory');
                ?>
                <span class="sku_wrapper"><?php esc_html_e('SKU:', 'bookory'); ?> <span class="sku"><?php printf('%s', $sku); ?></span></span>
            <?php endif; ?>
        </div>
        <?php
    }
}

if (!function_exists('bookory_product_label')) {
    function bookory_product_label() {
        global $product;

        $output = array();

        if ($product->is_on_sale()) {

            $percentage = '';

            if ($product->get_type() == 'variable') {

                $available_variations = $product->get_variation_prices();
                $max_percentage       = 0;

                foreach ($available_variations['regular_price'] as $key => $regular_price) {
                    $sale_price = $available_variations['sale_price'][$key];

                    if ($sale_price < $regular_price) {
                        $percentage = round((($regular_price - $sale_price) / $regular_price) * 100);

                        if ($percentage > $max_percentage) {
                            $max_percentage = $percentage;
                        }
                    }
                }

                $percentage = $max_percentage;
            } elseif (($product->get_type() == 'simple' || $product->get_type() == 'external')) {
                if (is_numeric($product->get_regular_price()) && is_numeric($product->get_sale_price())) {
                    $percentage = round((($product->get_regular_price() - $product->get_sale_price()) / $product->get_regular_price()) * 100);
                }
            }

            if ($percentage) {
                $output[] = '<span class="onsale">' . '-' . $percentage . '%' . '</span>';
            } else {
                $output[] = '<span class="onsale">' . esc_html__('Sale!', 'bookory') . '</span>';
            }
        }

        if ($output) {
            echo implode('', $output);
        }
    }
}
add_filter('woocommerce_sale_flash', 'bookory_product_label', 10);

if (!function_exists('bookory_woocommerce_get_product_label_new')) {
    function bookory_woocommerce_get_product_label_new() {
        global $product;
        $newness_days = 30;
        $created      = strtotime($product->get_date_created());
        if ((time() - (60 * 60 * 24 * $newness_days)) < $created) {
            echo '<span class="new-label">' . esc_html__('New!', 'bookory') . '</span>';
        }
    }
}


if (!function_exists('bookory_woocommerce_product_gallery_image')) {
    function bookory_woocommerce_product_gallery_image() {
        /**
         * @var $product WC_Product
         */
        global $product;
        $gallery = $product->get_gallery_image_ids();
        if (count($gallery) > 0) {
            $size = apply_filters('woocommerce_product_loop_size', 'woocommerce_thumbnail');
            echo '<div class="woocommerce-loop-product__gallery">';
            $url1    = wp_get_attachment_image_src($product->get_image_id(), $size);
            $srcset1 = wp_get_attachment_image_srcset($product->get_image_id(), $size);

            echo '<span class="gallery_item active" data-image="' . $url1[0] . '"  data-scrset="' . $srcset1 . '">' . $product->get_image('thumbnail') . '</span>';
            foreach ($gallery as $attachment_id) {
                $url    = wp_get_attachment_image_src($attachment_id, $size);
                $srcset = wp_get_attachment_image_srcset($attachment_id, $size);
                echo '<span class="gallery_item" data-image="' . $url[0] . '" data-scrset="' . $srcset . '">' . wp_get_attachment_image($attachment_id, 'thumbnail') . '</span>';
            }
            echo '</div>';
        }
    }
}

if (!function_exists('bookory_template_loop_product_thumbnail')) {
    function bookory_template_loop_product_thumbnail($size = 'woocommerce_thumbnail', $deprecated1 = 0, $deprecated2 = 0) {
        global $product;
        if (!$product) {
            return '';
        }
        $gallery    = $product->get_gallery_image_ids();
        $hover_skin = bookory_get_theme_option('woocommerce_product_hover', 'none');
        if ($hover_skin == 'none' || count($gallery) <= 0) {
            echo '<div class="product-image">' . $product->get_image('woocommerce_thumbnail') . '</div>';

            return '';
        }
        $image_featured = '<div class="product-image">' . $product->get_image('woocommerce_thumbnail') . '</div>';
        $image_featured .= '<div class="product-image second-image">' . wp_get_attachment_image($gallery[0], 'woocommerce_thumbnail') . '</div>';

        echo <<<HTML
<div class="product-img-wrap {$hover_skin}">
    <div class="inner">
        {$image_featured}
    </div>
</div>
HTML;
    }
}


if (!function_exists('bookory_woocommerce_single_product_image_thumbnail_html')) {
    function bookory_woocommerce_single_product_image_thumbnail_html($image, $attachment_id) {
        return wc_get_gallery_image_html($attachment_id, true);
    }
}

if (!function_exists('woocommerce_template_loop_product_title')) {

    /**
     * Show the product title in the product loop.
     */
    function woocommerce_template_loop_product_title() {
        echo '<h3 class="woocommerce-loop-product__title"><a href="' . esc_url_raw(get_the_permalink()) . '">' . get_the_title() . '</a></h3>';
    }
}

if (!function_exists('bookory_woocommerce_get_product_category')) {
    function bookory_woocommerce_get_product_category() {
        global $product;
        echo wc_get_product_category_list($product->get_id(), ', ', '<div class="posted-in">', '</div>');
    }
}

if (!function_exists('bookory_woocommerce_get_product_description')) {
    function bookory_woocommerce_get_product_description() {
        global $post;

        $short_description = apply_filters('woocommerce_short_description', $post->post_excerpt);

        if ($short_description) {
            ?>
            <div class="short-description">
                <?php echo sprintf('%s', $short_description); ?>
            </div>
            <?php
        }
    }
}

if (!function_exists('bookory_woocommerce_get_product_short_description')) {
    function bookory_woocommerce_get_product_short_description() {
        global $post;
        $short_description = wp_trim_words(apply_filters('woocommerce_short_description', $post->post_excerpt), 20);
        if ($short_description) {
            ?>
            <div class="short-description">
                <?php echo sprintf('%s', $short_description); ?>
            </div>
            <?php
        }
    }
}

if (!function_exists('bookory_header_wishlist')) {
    function bookory_header_wishlist() {
        if (function_exists('yith_wcwl_count_all_products')) {
            if (!bookory_get_theme_option('show_header_wishlist', true)) {
                return;
            }
            ?>
            <div class="site-header-wishlist">
                <a class="header-wishlist" href="<?php echo esc_url(get_permalink(get_option('yith_wcwl_wishlist_page_id'))); ?>">
                    <i class="bookory-icon-heart"></i>
                    <span class="count"><?php echo esc_html(yith_wcwl_count_all_products()); ?></span>
                </a>
            </div>
            <?php
        } elseif (function_exists('woosw_init')) {
            if (!bookory_get_theme_option('show_header_wishlist', true)) {
                return;
            }
            $key = WPCleverWoosw::get_key();

            ?>
            <div class="site-header-wishlist">
                <a class="header-wishlist" href="<?php echo esc_url(WPCleverWoosw::get_url($key, true)); ?>">
                    <i class="bookory-icon-heart-1"></i>
                    <span class="count"><?php echo esc_html(WPCleverWoosw::get_count($key)); ?></span>
                </a>
            </div>
            <?php
        }
    }
}

if (!function_exists('woosw_ajax_update_count') && function_exists('woosw_init')) {
    function woosw_ajax_update_count() {
        $key = WPCleverWoosw::get_key();

        wp_send_json(array(
            'text' => esc_html(_nx('Item', 'Items', WPCleverWoosw::get_count($key), 'items wishlist', 'bookory'))
        ));
    }

    add_action('wp_ajax_woosw_ajax_update_count', 'woosw_ajax_update_count');
    add_action('wp_ajax_nopriv_woosw_ajax_update_count', 'woosw_ajax_update_count');
}

if (!function_exists('bookory_button_grid_list_layout')) {
    function bookory_button_grid_list_layout() {
        ?>
        <div class="gridlist-toggle desktop-hide-down">
            <a href="<?php echo esc_url(add_query_arg('layout', 'grid')); ?>" id="grid" class="<?php echo isset($_GET['layout']) && $_GET['layout'] == 'list' ? '' : 'active'; ?>" title="<?php echo esc_html__('Grid View', 'bookory'); ?>"><i class="bookory-icon-th-large"></i></a>
            <a href="<?php echo esc_url(add_query_arg('layout', 'list')); ?>" id="list" class="<?php echo isset($_GET['layout']) && $_GET['layout'] == 'list' ? 'active' : ''; ?>" title="<?php echo esc_html__('List View', 'bookory'); ?>"><i class="bookory-icon-th-list"></i></a>
        </div>
        <?php
    }
}


if (!function_exists('bookory_woocommerce_list_get_rating')) {
    function bookory_woocommerce_list_show_rating() {
        global $product;
        echo wc_get_rating_html($product->get_average_rating());
    }
}

if (!function_exists('bookory_woocommerce_time_sale')) {
    function bookory_woocommerce_time_sale($is_not_single = false) {
        /**
         * @var $product WC_Product
         */
        global $product;

        if (!$product->is_on_sale()) {
            return;
        }

        $time_sale = get_post_meta($product->get_id(), '_sale_price_dates_to', true);
        if ($time_sale) {
            wp_enqueue_script('bookory-countdown');
            $time_sale += (get_option('gmt_offset') * HOUR_IN_SECONDS);
            $deal_text = $is_not_single ? esc_html__('End in:', 'bookory') : esc_html__('Hurry up! Sale ends in:', 'bookory');
            ?>
            <div class="time-sale">
                <div class="deal-text">
                    <span><?php printf('%s', $deal_text); ?></span>
                </div>
                <div class="bookory-countdown" data-countdown="true" data-date="<?php echo esc_html($time_sale); ?>">
                    <div class="countdown-item">
                        <span class="countdown-digits countdown-days"></span>
                        <span class="countdown-label"><?php echo esc_html__('Days', 'bookory') ?></span>
                    </div>
                    <div class="countdown-item">
                        <span class="countdown-digits countdown-hours"></span>
                        <span class="countdown-label"><?php echo esc_html__('Hrs', 'bookory') ?></span>
                    </div>
                    <div class="countdown-item">
                        <span class="countdown-digits countdown-minutes"></span>
                        <span class="countdown-label"><?php echo esc_html__('Mins', 'bookory') ?></span>
                    </div>
                    <div class="countdown-item">
                        <span class="countdown-digits countdown-seconds"></span>
                        <span class="countdown-label"><?php echo esc_html__('Secs', 'bookory') ?></span>
                    </div>
                </div>
            </div>
            <?php
        }
    }
}

if (!function_exists('bookory_woocommerce_deal_progress')) {
    function bookory_woocommerce_deal_progress() {
        global $product;

        $limit = get_post_meta($product->get_id(), '_deal_quantity', true);
        $sold  = intval(get_post_meta($product->get_id(), '_deal_sales_counts', true));
        if (empty($limit)) {
            return;
        }

        ?>

        <div class="deal-sold">
            <div class="deal-sold-text">
                <span><?php echo esc_html__('Already sold: ', 'bookory'); ?></span><span class="value"><?php echo esc_html(absint($sold)); ?>/<?php echo esc_html($limit); ?></span>
            </div>
            <div class="deal-progress">
                <div class="progress-bar">
                    <div class="progress-value" style="width: <?php echo trim($sold / $limit * 100) ?>%"></div>
                </div>
            </div>
        </div>

        <?php
    }
}

if (!function_exists('bookory_woocommerce_group_action')) {
    function bookory_woocommerce_group_action() {
        ?>
        <div class="group-action">
            <div class="shop-action">
                <?php
                bookory_wishlist_button();
                //bookory_quickview_button();
                bookory_compare_button();
                ?>
            </div>
        </div>
        <?php
    }
}

if (!function_exists('bookory_single_product_extra')) {
    function bookory_single_product_extra() {
        global $product;
        $product_extra = bookory_get_theme_option('single_product_content_meta', '');
        $product_extra = get_post_meta($product->get_id(), '_extra_info', true) !== '' ? get_post_meta($product->get_id(), '_extra_info', true) : $product_extra;
        if ($product_extra !== '') {
            echo '<div class="bookory-single-product-extra">' . html_entity_decode($product_extra) . '</div>';
        }
    }
}

if (!function_exists('bookory_button_shop_canvas')) {
    function bookory_button_shop_canvas() {
        if (is_active_sidebar('sidebar-woocommerce-shop')) { ?>
            <a href="#" class="filter-toggle" aria-expanded="false">
                <i class="bookory-icon-sliders-v"></i><span><?php esc_html_e('Filter', 'bookory'); ?></span></a>
            <?php
        }
    }
}

if (!function_exists('bookory_button_shop_dropdown')) {
    function bookory_button_shop_dropdown() {
        if (is_active_sidebar('sidebar-woocommerce-shop')) { ?>
            <a href="#" class="filter-toggle-dropdown" aria-expanded="false">
                <i class="bookory-icon-sliders-v"></i><span><?php esc_html_e('Filter', 'bookory'); ?></span></a>
            <?php
        }
    }
}

if (!function_exists('bookory_render_woocommerce_shop_canvas')) {
    function bookory_render_woocommerce_shop_canvas() {
        if (is_active_sidebar('sidebar-woocommerce-shop') && bookory_is_product_archive()) {
            ?>
            <div id="bookory-canvas-filter" class="bookory-canvas-filter">
                <span class="filter-close"><?php esc_html_e('HIDE FILTER', 'bookory'); ?></span>
                <div class="bookory-canvas-filter-wrap">
                    <?php if (bookory_get_theme_option('woocommerce_archive_layout') == 'canvas' || bookory_get_theme_option('woocommerce_archive_layout') == 'fullwidth') {
                        dynamic_sidebar('sidebar-woocommerce-shop');
                    }
                    ?>
                </div>
            </div>
            <div class="bookory-overlay-filter"></div>
            <?php
        }
    }
}
if (!function_exists('bookory_render_woocommerce_shop_dropdown')) {
    function bookory_render_woocommerce_shop_dropdown() {
        ?>
        <div id="bookory-dropdown-filter" class="bookory-dropdown-filter">
            <div class="bookory-dropdown-filter-wrap">
                <?php
                dynamic_sidebar('sidebar-woocommerce-shop');
                ?>
            </div>
        </div>
        <?php
    }
}

if (!function_exists('woocommerce_checkout_order_review_start')) {

    function woocommerce_checkout_order_review_start() {
        echo '<div class="checkout-review-order-table-wrapper">';
    }
}

if (!function_exists('woocommerce_checkout_order_review_end')) {

    function woocommerce_checkout_order_review_end() {
        echo '</div>';
    }
}

if (!function_exists('bookory_woocommerce_get_product_label_stock')) {
    function bookory_woocommerce_get_product_label_stock() {
        /**
         * @var $product WC_Product
         */
        global $product;
        if ($product->get_stock_status() == 'outofstock') {
            echo '<span class="stock-label">' . esc_html__('Out Of Stock', 'bookory') . '</span>';
        }
    }
}

if (!function_exists('bookory_woocommerce_single_content_wrapper_start')) {
    function bookory_woocommerce_single_content_wrapper_start() {
        echo '<div class="content-single-wrapper">';
    }
}

if (!function_exists('bookory_woocommerce_single_content_wrapper_end')) {
    function bookory_woocommerce_single_content_wrapper_end() {
        echo '</div>';
    }
}

if (!function_exists('bookory_woocommerce_single_brand')) {
    function bookory_woocommerce_single_brand() {
        $id = get_the_ID();

        $terms = get_the_terms($id, 'product_brand');

        if (is_wp_error($terms)) {
            return $terms;
        }

        if (empty($terms)) {
            return false;
        }

        $links = array();

        foreach ($terms as $term) {
            $link = get_term_link($term, 'product_brand');
            if (is_wp_error($link)) {
                return $link;
            }
            $links[] = '<a href="' . esc_url($link) . '" rel="tag">' . $term->name . '</a>';
        }
        echo '<div class="product-brand">' . esc_html__('Brands: ', 'bookory') . join('', $links) . '</div>';
    }
}

if (!function_exists('bookory_single_product_video_360')) {
    function bookory_single_product_video_360() {
        global $product;
        echo '<div class="product-video-360">';
        $images = get_post_meta($product->get_id(), '_product_360_image_gallery', true);
        $video  = get_post_meta($product->get_id(), '_video_select', true);

        if ($video && wc_is_valid_url($video)) {
            echo '<a class="product-video-360__btn btn-video" href="' . $video . '"><i class="bookory-icon-video"></i><span>' . esc_html__('Video', 'bookory') . '</span></a>';
        }

        if ($images) {
            $array      = explode(',', $images);
            $images_url = [];
            foreach ($array as $id) {
                $url          = wp_get_attachment_image_src($id, 'full');
                $images_url[] = $url[0];
            }

            echo '<a class="product-video-360__btn btn-360" href="#view-360"><i class="bookory-icon-360"></i><span>' . esc_html__('360 View', 'bookory') . '</span></a>';
            ?>
            <div id="view-360" class="view-360 zoom-anim-dialog mfp-hide">
                <div id="rotateimages" class="opal-loading" data-images="<?php echo implode(',', $images_url); ?>"></div>
                <div class="view-360-group">
                    <span class='view-360-button view-360-prev'><i class="bookory-icon-angle-left"></i></span>
                    <i class="bookory-icon-360 view-360-svg"></i>
                    <span class='view-360-button view-360-next'><i class="bookory-icon-angle-right"></i></span>
                </div>
            </div>
            <?php
        }

        echo '</div>';
    }
}

if (!function_exists('bookory_output_product_data_accordion')) {
    function bookory_output_product_data_accordion() {
        $product_tabs = apply_filters('woocommerce_product_tabs', array());
        if (!empty($product_tabs)) : ?>
            <div id="bookory-accordion-container" class="woocommerce-tabs wc-tabs-wrapper product-accordions">
                <?php $_count = 0; ?>
                <?php foreach ($product_tabs as $key => $tab) : ?>
                    <div class="accordion-item">
                        <div class="accordion-head <?php echo esc_attr($key); ?>_tab js-btn-accordion"
                             id="tab-title-<?php echo esc_attr($key); ?>">
                            <div class="accordion-title"><?php echo apply_filters('woocommerce_product_' . $key . '_tab_title', esc_html($tab['title']), $key); ?></div>
                        </div>
                        <div class="accordion-body js-card-body">
                            <?php call_user_func($tab['callback'], $key, $tab); ?>
                        </div>
                    </div>
                    <?php $_count++; ?>
                <?php endforeach; ?>
            </div>
        <?php endif;
    }
}

if (!function_exists('bookory_quickview_button')) {
    function bookory_quickview_button() {
        if (function_exists('woosq_init')) {
            echo do_shortcode('[woosq]');
        }
    }
}

if (!function_exists('bookory_compare_button')) {
    function bookory_compare_button() {
        if (function_exists('woosc_init')) {
            echo do_shortcode('[woosc]');
        }
    }
}

if (!function_exists('bookory_wishlist_button')) {
    function bookory_wishlist_button() {
        if (function_exists('woosw_init')) {
            echo do_shortcode('[woosw]');
        }
    }
}


if (!function_exists('bookory_quick_shop')) {
    function bookory_quick_shop($id = false) {
        if (isset($_GET['id'])) {
            $id = sanitize_text_field((int)$_GET['id']);
        }
        if (!$id || !bookory_is_woocommerce_activated()) {
            return;
        }

        global $post;

        $args = array('post__in' => array($id), 'post_type' => 'product');

        $quick_posts = get_posts($args);

        foreach ($quick_posts as $post) :
            setup_postdata($post);
            woocommerce_template_single_add_to_cart();
        endforeach;

        wp_reset_postdata();

        die();
    }

    add_action('wp_ajax_bookory_quick_shop', 'bookory_quick_shop');
    add_action('wp_ajax_nopriv_bookory_quick_shop', 'bookory_quick_shop');

}

if (!function_exists('bookory_quick_shop_wrapper')) {
    function bookory_quick_shop_wrapper() {
        global $product;
        ?>
        <div class="quick-shop-wrapper">
            <div class="quick-shop-close cross-button"></div>
            <div class="quick-shop-form">
            </div>
        </div>
        <?php
    }
}

function bookory_ajax_add_to_cart_handler() {
    WC_Form_Handler::add_to_cart_action();
    WC_AJAX::get_refreshed_fragments();
}

add_action('wc_ajax_bookory_add_to_cart', 'bookory_ajax_add_to_cart_handler');
add_action('wc_ajax_nopriv_bookory_add_to_cart', 'bookory_ajax_add_to_cart_handler');

// Remove WC Core add to cart handler to prevent double-add
remove_action('wp_loaded', array('WC_Form_Handler', 'add_to_cart_action'), 20);

function bookory_ajax_add_to_cart_add_fragments($fragments) {
    $all_notices  = WC()->session->get('wc_notices', array());
    $notice_types = apply_filters('woocommerce_notice_types', array('error', 'success', 'notice'));

    ob_start();
    foreach ($notice_types as $notice_type) {
        if (wc_notice_count($notice_type) > 0) {
            wc_get_template("notices/{$notice_type}.php", array(
                'notices' => array_filter($all_notices[$notice_type]),
            ));
        }
    }
    $fragments['notices_html'] = ob_get_clean();

    wc_clear_notices();

    return $fragments;
}

add_filter('woocommerce_add_to_cart_fragments', 'bookory_ajax_add_to_cart_add_fragments');


if (!function_exists('bookory_ajax_search_result')) {
    function bookory_ajax_search_result() {
        ?>
        <div class="ajax-search-result d-none">
        </div>
        <?php
    }
}
add_action('pre_get_product_search_form', 'bookory_ajax_search_result');

if (!function_exists('bookory_ajax_live_search_template')) {
    function bookory_ajax_live_search_template() {
        echo <<<HTML
        <script type="text/html" id="tmpl-ajax-live-search-template">
        <div class="product-item-search">
            <# if(data.url){ #>
            <a class="product-link" href="{{{data.url}}}" title="{{{data.title}}}">
            <# } #>
                <# if(data.img){#>
                <img src="{{{data.img}}}" alt="{{{data.title}}}">
                 <# } #>
                <div class="product-content">
                <h3 class="product-title">{{{data.title}}}</h3>
                <# if(data.price){ #>
                {{{data.price}}}
                 <# } #>
                </div>
                <# if(data.url){ #>
            </a>
            <# } #>
        </div>
        </script>
HTML;
    }
}
add_action('wp_footer', 'bookory_ajax_live_search_template');

if (!function_exists('bookory_single_product_review_template')) {
    function bookory_single_product_review_template() {
        global $product;

        if (comments_open()) {
            ?>
            <div class="single-product-reviews-wrap">
                <?php
                echo '<h3 class="review-title">' . esc_html__('Reviews', 'bookory') . '<sup class="count">' . $product->get_review_count() . '</sup></h3>';
                comments_template();
                ?>
            </div>
            <?php
        }
    }
}

if (!function_exists('bookory_single_product_tabs_template')) {
    function bookory_single_product_tabs_template() {
        $product_tabs = apply_filters('woocommerce_product_tabs', array());
        if (!empty($product_tabs)) : ?>
            <div class="bookory-woocommerce-tabs">
                <?php foreach ($product_tabs as $key => $product_tab) : ?>
                    <div class="umimi-woocommerce-tabs-panel">
                        <?php
                        if (isset($product_tab['callback'])) {
                            call_user_func($product_tab['callback'], $key, $product_tab);
                        }
                        ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif;
    }
}

if (!function_exists('bookory_woocommerce_render_color')) {

    function bookory_woocommerce_render_color() {
        /**
         * @var $product WC_Product_Variable
         */
        global $product;

        if (!function_exists('Woo_Variation_Swatches')) {
            return;
        }

        if ($product->is_type('variable')) {
            $attr_name           = 'pa_colors';
            $product_color_terms = wc_get_product_terms($product->get_id(), $attr_name, array('fields' => 'all'));
            $tax                 = wvs_get_wc_attribute_taxonomy($attr_name);
            $options             = $product->get_available_variations();

            if (!empty($product_color_terms)) {

                echo '<div class="product-color">';

                foreach ($product_color_terms as $term) {
                    $thumbnail = [];
                    foreach ($options as $option) {
                        foreach ($option['attributes'] as $_k => $_v) {
                            if ($_k === 'attribute_' . $attr_name && $_v === $term->slug) {
                                $thumbnail = $option['image'];
                                break;
                            }
                            if (count($thumbnail) > 0) {
                                break;
                            }
                        }
                    }

                    if (wvs_is_color_attribute($tax)) {
                        $color = sanitize_hex_color(wvs_get_product_attribute_color($term));
                        echo '<div class="item color-item" data-image="' . htmlspecialchars(wp_json_encode($thumbnail)) . '"  style="background-color:' . esc_attr($color) . '"><span class="screen-reader-text">' . esc_html($term->name) . '</span></div>';
                    } elseif (wvs_is_image_attribute($tax)) {
                        $attachment_id = absint(wvs_get_product_attribute_image($term));
                        $image_size    = woo_variation_swatches()->get_option('attribute_image_size');
                        $image         = wp_get_attachment_image_src($attachment_id, $image_size);

                        echo sprintf('<div class="item image-item" data-image="' . htmlspecialchars(wp_json_encode($thumbnail)) . '"><img aria-hidden="true" alt="%s" src="%s" width="%d" height="%d" /></div>', esc_attr($term->name), esc_url($image[0]), esc_attr($image[1]), esc_attr($image[2]));
                    }

                }

                echo '</div>';
            }

        }
    }
}
if (!function_exists('bookory_shop_page_link')) {
    function bookory_shop_page_link($keep_query = false, $taxonomy = '') {
        // Base Link decided by current page
        if (is_post_type_archive('product') || is_page(wc_get_page_id('shop')) || is_shop()) {
            $link = get_permalink(wc_get_page_id('shop'));
        } elseif (is_product_category()) {
            $link = get_term_link(get_query_var('product_cat'), 'product_cat');
        } elseif (is_product_tag()) {
            $link = get_term_link(get_query_var('product_tag'), 'product_tag');
        } else {
            $queried_object = get_queried_object();
            $link           = get_term_link($queried_object->slug, $queried_object->taxonomy);
        }

        if ($keep_query) {

            // Min/Max
            if (isset($_GET['min_price'])) {
                $link = add_query_arg('min_price', wc_clean($_GET['min_price']), $link);
            }

            if (isset($_GET['max_price'])) {
                $link = add_query_arg('max_price', wc_clean($_GET['max_price']), $link);
            }

            // Orderby
            if (isset($_GET['orderby'])) {
                $link = add_query_arg('orderby', wc_clean($_GET['orderby']), $link);
            }

            if (isset($_GET['woocommerce_catalog_columns'])) {
                $link = add_query_arg('woocommerce_catalog_columns', wc_clean($_GET['woocommerce_catalog_columns']), $link);
            }

            if (isset($_GET['woocommerce_archive_layout'])) {
                $link = add_query_arg('woocommerce_archive_layout', wc_clean($_GET['woocommerce_archive_layout']), $link);
            }

            if (isset($_GET['layout'])) {
                $link = add_query_arg('layout', wc_clean($_GET['layout']), $link);
            }

            if (isset($_GET['wocommerce_block_style'])) {
                $link = add_query_arg('wocommerce_block_style', wc_clean($_GET['wocommerce_block_style']), $link);
            }

            /**
             * Search Arg.
             * To support quote characters, first they are decoded from &quot; entities, then URL encoded.
             */
            if (get_search_query()) {
                $link = add_query_arg('s', rawurlencode(wp_specialchars_decode(get_search_query())), $link);
            }

            // Post Type Arg
            if (isset($_GET['post_type'])) {
                $link = add_query_arg('post_type', wc_clean($_GET['post_type']), $link);
            }

            // Min Rating Arg
            if (isset($_GET['min_rating'])) {
                $link = add_query_arg('min_rating', wc_clean($_GET['min_rating']), $link);
            }

            // All current filters
            if ($_chosen_attributes = WC_Query::get_layered_nav_chosen_attributes()) {
                foreach ($_chosen_attributes as $name => $data) {
                    if ($name === $taxonomy) {
                        continue;
                    }
                    $filter_name = sanitize_title(str_replace('pa_', '', $name));
                    if (!empty($data['terms'])) {
                        $link = add_query_arg('filter_' . $filter_name, implode(',', $data['terms']), $link);
                    }
                    if ('or' == $data['query_type']) {
                        $link = add_query_arg('query_type_' . $filter_name, 'or', $link);
                    }
                }
            }
        }

        if (is_string($link)) {
            return $link;
        } else {
            return '';
        }
    }
}

if (!function_exists('bookory_products_per_page_select')) {

    function bookory_products_per_page_select() {
        if ((wc_get_loop_prop('is_shortcode') || !wc_get_loop_prop('is_paginated') || !woocommerce_products_will_display())) return;

        $row          = wc_get_default_products_per_row();
        $max_col      = apply_filters('bookory_products_row_step_max', 6);
        $array_option = [];
        if ($max_col > 2) {
            for ($i = 2; $i <= $max_col; $i++) {
                $array_option[] = $row * $i;
            }
        } else {
            return;
        }

        $col = wc_get_default_product_rows_per_page();

        $products_per_page_options = apply_filters('bookory_products_per_page_options', $array_option);

        $current_variation = isset($_GET['per_page']) ? $_GET['per_page'] : $col * $row;
        ?>

        <div class="bookory-products-per-page">

            <label for="per_page" class="per-page-title"><?php esc_html_e('Show', 'bookory'); ?></label>
            <select name="per_page" id="per_page">
                <?php
                foreach ($products_per_page_options as $key => $value) :

                    ?>
                    <option value="<?php echo add_query_arg('per_page', $value, bookory_shop_page_link(true)); ?>" <?php echo esc_attr($current_variation == $value ? 'selected' : ''); ?>>
                        <?php echo esc_html($value); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <?php
    }
}

if (isset($_GET['per_page'])) {
    add_filter('loop_shop_per_page', 'bookory_loop_shop_per_page', 20);
}

function bookory_loop_shop_per_page($cols) {

    $cols = isset($_GET['per_page']) ? $_GET['per_page'] : $cols;

    return $cols;
}

if (!function_exists('bookory_get_book_author_taxonomy')) {
    function bookory_get_book_author_taxonomy() {
        return apply_filters('bookory_book_author_taxonomy', 'pa_book-author');
    }
}

if (!function_exists('bookory_wc_template_loop_product_author')) {
    function bookory_wc_template_loop_product_author() {
        $author_name = bookory_wc_get_product_author();

        if (!empty ($author_name)) :
            ?>
            <div class="woocommerce-loop-product__author"><?php echo wp_kses_post(str_replace('</a><a ', '</a>, <a ', $author_name)); ?></div><?php
        endif;
    }
}

if (!function_exists('bookory_wc_get_product_author')) {
    function bookory_wc_get_product_author($product_id = '') {
        global $product;

        $product_id = empty($product_id) ? $product->get_id() : $product_id;
        $author_tax = '';
        if (class_exists('Mas_WC_Brands')) {
            $author_tax = Mas_WC_Brands()->get_brand_taxonomy();
        } else {
            $author_tax = bookory_get_book_author_taxonomy();
        }
        $terms       = get_the_terms($product_id, $author_tax);
        $author_name = '';

        if ($terms && !is_wp_error($terms)) {
            foreach ($terms as $term) {
                $author_name .= '<a href="' . esc_url(get_term_link($term)) . '">' . $term->name . '</a>';
            }
        }

        return $author_name;
    }
}

if (!function_exists('bookory_woocommerce_single_author')) {
    function bookory_woocommerce_single_author() {
        $author_name = bookory_wc_get_product_author();
        if (!empty ($author_name)) : ?>
            <div class="product-brand">
                <?php echo esc_html__('Author: ', 'bookory') . wp_kses_post(str_replace('</a><a ', '</a>, <a ', $author_name)); ?>
            </div>
        <?php endif;
    }
}