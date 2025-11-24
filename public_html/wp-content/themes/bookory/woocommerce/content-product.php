<?php
/**
 * The template for displaying product content within loops
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/content-product.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 9.4.0
 */

defined('ABSPATH') || exit;

global $product;

// Ensure visibility.
if (empty($product) || !$product->is_visible()) {
    return;
}
?>
<li <?php wc_product_class('product-style-default', $product); ?>>
    <?php do_action('woocommerce_before_shop_loop_item'); ?>
    <div class="product-block">
        <div class="product-transition">
            <?php
            do_action('woocommerce_before_shop_loop_item_title');
            bookory_template_loop_product_thumbnail();
            ?>
            <div class="group-action">
                <div class="shop-action vertical">
                    <?php
                    bookory_wishlist_button();
                    bookory_quickview_button();
                    //bookory_compare_button();
                    ?>
                    <div class="opal-add-to-cart-button">
                        <?php woocommerce_template_loop_add_to_cart(); ?>
                    </div>
                    <?php
                    do_action('bookory_woocommerce_product_loop_action');
                    ?>
                </div>
            </div>
            <?php
            woocommerce_template_loop_product_link_open();
            woocommerce_template_loop_product_link_close();
            ?>
        </div>
        <div class="product-caption">
            <?php
            do_action('woocommerce_shop_loop_item_title');
            woocommerce_template_loop_rating();
            bookory_wc_template_loop_product_author();
            do_action('woocommerce_after_shop_loop_item_title');
            ?>
        </div>
    </div>
    <?php do_action('woocommerce_after_shop_loop_item'); ?>
</li>
