<?php
/**
 * Bookory WooCommerce hooks
 *
 * @package bookory
 */

/**
 * Layout
 *
 * @see  bookory_before_content()
 * @see  bookory_after_content()
 * @see  woocommerce_breadcrumb()
 * @see  bookory_shop_messages()
 */

remove_action('woocommerce_before_main_content', 'woocommerce_breadcrumb', 20);
remove_action('woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10);
remove_action('woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10);
remove_action('woocommerce_sidebar', 'woocommerce_get_sidebar', 10);

add_action('woocommerce_before_main_content', 'bookory_before_content', 10);
add_action('woocommerce_after_main_content', 'bookory_after_content', 10);


//Position label onsale
remove_action('woocommerce_before_single_product_summary', 'woocommerce_show_product_sale_flash', 10);
add_action('bookory_single_product_video_360', 'woocommerce_show_product_sale_flash', 5);

//Wrapper content single
add_action('woocommerce_before_single_product_summary', 'bookory_woocommerce_single_content_wrapper_start', 0);
add_action('woocommerce_single_product_summary', 'bookory_woocommerce_single_content_wrapper_end', 99);

/**
 * Products
 *
 * @see bookory_upsell_display()
 * @see bookory_single_product_pagination()
 */

remove_action('woocommerce_product_thumbnails', 'woocommerce_show_product_thumbnails', 20);
remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_rating', 10);

add_action('woocommerce_product_thumbnails', 'woocommerce_show_product_thumbnails', 21);

add_action('woocommerce_single_product_summary', 'bookory_single_product_summary_top', 1);
add_action('woocommerce_single_product_summary', 'bookory_single_product_after_title', 6);
add_action('woocommerce_single_product_summary', 'bookory_woocommerce_time_sale', 25);
add_action('woocommerce_single_product_summary', 'bookory_woocommerce_deal_progress', 26);
add_action('woocommerce_single_product_summary', 'bookory_single_product_extra', 35);

add_filter('woosc_button_position_single', '__return_false');
add_filter('woosw_button_position_single', '__return_false');

add_action('woocommerce_share', 'bookory_social_share', 10);

add_action('woocommerce_after_add_to_cart_button', function (){

}, 30);

add_action('woocommerce_after_add_to_cart_button', 'bookory_wishlist_button', 31);

$product_single_style = bookory_get_theme_option('single_product_gallery_layout', 'horizontal');

add_theme_support('wc-product-gallery-lightbox');

if ($product_single_style === 'horizontal' || $product_single_style === 'vertical') {
    add_theme_support('wc-product-gallery-zoom');
    add_theme_support('wc-product-gallery-slider');
}

if ($product_single_style === 'gallery'){
    add_filter('woocommerce_single_product_image_thumbnail_html', 'bookory_woocommerce_single_product_image_thumbnail_html', 10, 2);
}

if ($product_single_style === 'sticky'){
    remove_action('woocommerce_after_single_product_summary', 'woocommerce_output_product_data_tabs', 10);
    add_action('woocommerce_single_product_summary', 'bookory_output_product_data_accordion', 70);
    add_filter('woocommerce_single_product_image_thumbnail_html', 'bookory_woocommerce_single_product_image_thumbnail_html', 10, 2);
}

add_action('bookory_single_product_video_360', 'bookory_single_product_video_360', 10);


/**
 * Cart fragment
 *
 * @see bookory_cart_link_fragment()
 */
if (defined('WC_VERSION') && version_compare(WC_VERSION, '2.3', '>=')) {
    add_filter('woocommerce_add_to_cart_fragments', 'bookory_cart_link_fragment');
} else {
    add_filter('add_to_cart_fragments', 'bookory_cart_link_fragment');
}

remove_action('woocommerce_cart_collaterals', 'woocommerce_cross_sell_display');
add_action('woocommerce_after_cart', 'woocommerce_cross_sell_display');

add_action('woocommerce_checkout_order_review', 'woocommerce_checkout_order_review_start', 5);
add_action('woocommerce_checkout_order_review', 'woocommerce_checkout_order_review_end', 15);

add_filter('woocommerce_get_script_data', function ($params, $handle) {
    if ($handle == "wc-add-to-cart") {
        $params['i18n_view_cart'] = '';
    }
    return $params;
}, 10, 2);

/*
 *
 * Layout Product
 *
 * */

add_filter('woosc_button_position_archive', '__return_false');
add_filter('woosq_button_position', '__return_false');
add_filter('woosw_button_position_archive', '__return_false');

function bookory_include_hooks_product_blocks() {

    remove_action('woocommerce_before_shop_loop_item_title', 'woocommerce_template_loop_product_thumbnail', 10);
    remove_action('woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_rating', 5);
    // Remove product content link
    remove_action('woocommerce_before_shop_loop_item', 'woocommerce_template_loop_product_link_open', 10);
    remove_action('woocommerce_after_shop_loop_item', 'woocommerce_template_loop_product_link_close', 5);
    remove_action('woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10);

}

if (isset($_GET['action']) && $_GET['action'] === 'elementor') {
    return;
}

bookory_include_hooks_product_blocks();

