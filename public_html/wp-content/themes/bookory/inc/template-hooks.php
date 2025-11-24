<?php
/**
 * =================================================
 * Hook bookory_page
 * =================================================
 */
add_action('bookory_page', 'bookory_page_header', 10);
add_action('bookory_page', 'bookory_page_content', 20);

/**
 * =================================================
 * Hook bookory_single_post_top
 * =================================================
 */
add_action('bookory_single_post_top', 'bookory_post_thumbnail', 10);

/**
 * =================================================
 * Hook bookory_single_post
 * =================================================
 */
add_action('bookory_single_post', 'bookory_post_header', 20);
add_action('bookory_single_post', 'bookory_post_content', 30);

/**
 * =================================================
 * Hook bookory_single_post_bottom
 * =================================================
 */
add_action('bookory_single_post_bottom', 'bookory_post_taxonomy', 5);
add_action('bookory_single_post_bottom', 'bookory_post_nav', 10);
add_action('bookory_single_post_bottom', 'bookory_display_comments', 20);

/**
 * =================================================
 * Hook bookory_loop_post
 * =================================================
 */
add_action('bookory_loop_post', 'bookory_post_header', 15);
add_action('bookory_loop_post', 'bookory_post_content', 30);

/**
 * =================================================
 * Hook bookory_footer
 * =================================================
 */
add_action('bookory_footer', 'bookory_footer_default', 20);

/**
 * =================================================
 * Hook bookory_after_footer
 * =================================================
 */

/**
 * =================================================
 * Hook wp_footer
 * =================================================
 */
add_action('wp_footer', 'bookory_template_account_dropdown', 1);
add_action('wp_footer', 'bookory_mobile_nav', 1);

/**
 * =================================================
 * Hook wp_head
 * =================================================
 */
add_action('wp_head', 'bookory_pingback_header', 1);

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
add_action('bookory_sidebar', 'bookory_get_sidebar', 10);

/**
 * =================================================
 * Hook bookory_loop_after
 * =================================================
 */
add_action('bookory_loop_after', 'bookory_paging_nav', 10);

/**
 * =================================================
 * Hook bookory_page_after
 * =================================================
 */
add_action('bookory_page_after', 'bookory_display_comments', 10);

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

/**
 * =================================================
 * Hook bookory_woocommerce_shop_loop_item_title
 * =================================================
 */

/**
 * =================================================
 * Hook bookory_woocommerce_after_shop_loop_item_title
 * =================================================
 */

/**
 * =================================================
 * Hook bookory_woocommerce_after_shop_loop_item
 * =================================================
 */
