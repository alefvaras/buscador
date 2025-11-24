<?php
/**
 * The Template for displaying product archives, including the main shop page which is a post type archive
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/archive-product.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 8.6.0
 */

defined('ABSPATH') || exit;

get_header('shop');
if (bookory_is_mas_woocommerce_brands_activated() && is_product_taxonomy() && is_tax($brand_taxonomy = Mas_WC_Brands()->get_brand_taxonomy()) && 0 === absint(get_query_var('paged'))) {
    $product_style = bookory_get_theme_option('wocommerce_block_style', 0) == 0 ? '' : bookory_get_theme_option('wocommerce_block_style', 0);

    /**
     * Hook: woocommerce_before_main_content.
     *
     * @hooked woocommerce_output_content_wrapper - 10 (outputs opening divs for the content)
     * @hooked woocommerce_breadcrumb - 20
     * @hooked WC_Structured_Data::generate_website_data() - 30
     */
    do_action('woocommerce_before_main_content');
    if (woocommerce_product_loop()) {
        ?>
        <header class="woocommerce-products-author-header">

            <?php
            $term = get_queried_object();
            echo mas_wcbr_get_brand_thumbnail_image($term);
            ?>
            <div class="author-header-caption">
                <h3 class="author-name"><?php woocommerce_page_title(true); ?></h3>
                <?php

                if ($term && !empty($term->description)) {
                    echo '<div class="term-description">' . wc_format_content(wp_kses_post($term->description)) . '</div>';
                }
                ?>
            </div>
        </header>
        <?php
        woocommerce_output_all_notices();

        ?>
        <div class="books-author-title"><?php echo sprintf('%1s %2s', esc_html__('Books By', 'bookory'), woocommerce_page_title(false)); ?></div>
        <div class="bookory-products-overflow">
            <?php

            wc_set_loop_prop('columns', 6);

            wc_set_loop_prop('product-class', 'bookory-products products');

            woocommerce_product_loop_start();

            if (wc_get_loop_prop('total')) {
                while (have_posts()) {
                    the_post();

                    /**
                     * Hook: woocommerce_shop_loop.
                     */
                    do_action('woocommerce_shop_loop');
                    wc_get_template_part('content-product', $product_style);

                }
            }

            woocommerce_product_loop_end();
            ?>
        </div>
        <?php

        /**
         * Hook: woocommerce_after_shop_loop.
         *
         * @hooked woocommerce_pagination - 10
         */
        do_action('woocommerce_after_shop_loop');
    } else {
        /**
         * Hook: woocommerce_no_products_found.
         *
         * @hooked wc_no_products_found - 10
         */
        do_action('woocommerce_no_products_found');
    }
    /**
     * Hook: woocommerce_after_main_content.
     *
     * @hooked woocommerce_output_content_wrapper_end - 10 (outputs closing divs for the content)
     */
    do_action('woocommerce_after_main_content');

} else {

    $product_style = bookory_get_theme_option('wocommerce_block_style', 0) == 0 ? '' : bookory_get_theme_option('wocommerce_block_style', 0);

    $layout = isset($_GET['layout']) ? $_GET['layout'] : apply_filters('bookory_shop_layout', 'grid');

    /**
     * Hook: woocommerce_before_main_content.
     *
     * @hooked woocommerce_output_content_wrapper - 10 (outputs opening divs for the content)
     * @hooked woocommerce_breadcrumb - 20
     * @hooked WC_Structured_Data::generate_website_data() - 30
     */
    do_action('woocommerce_before_main_content');
    if (woocommerce_product_loop()) {
        ?>
        <header class="woocommerce-products-header">
            <?php if (apply_filters('woocommerce_show_page_title', true)) : ?>
                <h1 class="woocommerce-products-header__title page-title"><?php woocommerce_page_title(); ?></h1>
            <?php endif; ?>

            <?php
            /**
             * Hook: woocommerce_archive_description.
             *
             * @hooked woocommerce_taxonomy_archive_description - 10
             * @hooked woocommerce_product_archive_description - 10
             */
            do_action('woocommerce_archive_description');
            ?>
        </header>
        <?php
        woocommerce_output_all_notices();
        ?>
        <div class="bookory-sorting">
            <?php
            /**
             * Hook: woocommerce_before_shop_loop.
             *
             */
            do_action('bookory_woocommerce_before_shop_loop');
            bookory_button_shop_canvas();
            bookory_button_shop_dropdown();
            bookory_button_grid_list_layout();
            woocommerce_catalog_ordering();
            bookory_products_per_page_select();
            if (bookory_get_theme_option('woocommerce_archive_layout') == 'dropdown') {
                bookory_render_woocommerce_shop_dropdown();
            }
            ?>
        </div>
        <div class="bookory-products-overflow">
            <?php
            $columns = wc_get_loop_prop('columns');
            wc_set_loop_prop('columns', $columns);
            if ($layout == 'list') {
                wc_set_loop_prop('product-class', 'bookory-products products-list');
            } else {
                wc_set_loop_prop('product-class', 'bookory-products products');
            }

            woocommerce_product_loop_start();

            if (wc_get_loop_prop('total')) {
                while (have_posts()) {
                    the_post();

                    /**
                     * Hook: woocommerce_shop_loop.
                     */
                    do_action('woocommerce_shop_loop');
                    if ($layout == 'list') {
                        wc_get_template_part('content', 'product-list');
                    } else {
                        wc_get_template_part('content-product', $product_style);
                    }
                }
            }

            woocommerce_product_loop_end();
            ?>
        </div>
        <?php

        /**
         * Hook: woocommerce_after_shop_loop.
         *
         * @hooked woocommerce_pagination - 10
         */
        do_action('woocommerce_after_shop_loop');
    } else {
        /**
         * Hook: woocommerce_no_products_found.
         *
         * @hooked wc_no_products_found - 10
         */
        do_action('woocommerce_no_products_found');
    }
    /**
     * Hook: woocommerce_after_main_content.
     *
     * @hooked woocommerce_output_content_wrapper_end - 10 (outputs closing divs for the content)
     */
    do_action('woocommerce_after_main_content');

    /**
     * Hook: woocommerce_sidebar.
     *
     * @hooked woocommerce_get_sidebar - 10
     */
    do_action('woocommerce_sidebar');
}

get_footer('shop');
