<?php
/**
 * =================================================
 * Hook bookory_page
 * =================================================
 */

/**
 * =================================================
 * Hook bookory_single_post_top
 * =================================================
 */

/**
 * =================================================
 * Hook bookory_single_post
 * =================================================
 */

/**
 * =================================================
 * Hook bookory_single_post_bottom
 * =================================================
 */

/**
 * =================================================
 * Hook bookory_loop_post
 * =================================================
 */

/**
 * =================================================
 * Hook bookory_footer
 * =================================================
 */

/**
 * =================================================
 * Hook bookory_after_footer
 * =================================================
 */
add_action('bookory_after_footer', 'bookory_sticky_single_add_to_cart', 999);

/**
 * =================================================
 * Hook wp_footer
 * =================================================
 */
add_action('wp_footer', 'bookory_render_woocommerce_shop_canvas', 1);

/**
 * =================================================
 * Hook wp_head
 * =================================================
 */

/**
 * =================================================
 * Hook bookory_before_header
 * =================================================
 */

/**
 * =================================================
 * Hook bookory_before_content
 * =================================================
 */

/**
 * =================================================
 * Hook bookory_content_top
 * =================================================
 */
add_action('bookory_content_top', 'bookory_shop_messages', 10);

/**
 * =================================================
 * Hook bookory_post_content_before
 * =================================================
 */

/**
 * =================================================
 * Hook bookory_post_content_after
 * =================================================
 */

/**
 * =================================================
 * Hook bookory_sidebar
 * =================================================
 */

/**
 * =================================================
 * Hook bookory_loop_after
 * =================================================
 */

/**
 * =================================================
 * Hook bookory_page_after
 * =================================================
 */

/**
 * =================================================
 * Hook bookory_woocommerce_before_shop_loop_item
 * =================================================
 */

/**
 * =================================================
 * Hook bookory_woocommerce_before_shop_loop_item_title
 * =================================================
 */
add_action('bookory_woocommerce_before_shop_loop_item_title', 'woocommerce_show_product_loop_sale_flash', 10);
add_action('bookory_woocommerce_before_shop_loop_item_title', 'woocommerce_template_loop_product_thumbnail', 15);

/**
 * =================================================
 * Hook bookory_woocommerce_shop_loop_item_title
 * =================================================
 */
add_action('bookory_woocommerce_shop_loop_item_title', 'woocommerce_template_loop_product_title', 5);
add_action('bookory_woocommerce_shop_loop_item_title', 'woocommerce_template_loop_rating', 10);

/**
 * =================================================
 * Hook bookory_woocommerce_after_shop_loop_item_title
 * =================================================
 */
add_action('bookory_woocommerce_after_shop_loop_item_title', 'bookory_wc_template_loop_product_author', 15);
add_action('bookory_woocommerce_after_shop_loop_item_title', 'bookory_woocommerce_get_product_description', 20);
add_action('bookory_woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_price', 25);
add_action('bookory_woocommerce_after_shop_loop_item_title', 'bookory_woocommerce_product_list_bottom', 30);

/**
 * =================================================
 * Hook bookory_woocommerce_after_shop_loop_item
 * =================================================
 */
