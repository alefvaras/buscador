<?php

remove_action( 'woocommerce_product_tabs', 'dokan_set_more_from_seller_tab', 10 );

$show_vendor_info = bookory_is_dokan_activated() ? dokan_get_option( 'show_vendor_info', 'dokan_appearance', 'off' ): '';
if('on'=== $show_vendor_info){
    add_action('woocommerce_single_product_summary', 'bookory_add_vendor_info_on_product_single_page', 63 );
}

//style product single
$product_single_style = bookory_get_theme_option('single_product_gallery_layout', 'horizontal');
if ($product_single_style === 'sticky'){
    add_action('woocommerce_single_product_summary', 'bookory_dokan_sold_store', 15);
    remove_action('woocommerce_single_product_summary', 'bookory_add_vendor_info_on_product_single_page', 63 );
}

if ($product_single_style === 'sidebar'){
    add_action('woocommerce_single_product_summary', 'bookory_dokan_sold_store', 15);
    remove_action('woocommerce_single_product_summary', 'bookory_add_vendor_info_on_product_single_page', 63 );
}