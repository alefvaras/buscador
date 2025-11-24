<?php

defined('ABSPATH') || exit;

global $product;

// Ensure visibility.
if (empty($product) || !$product->is_visible()) {
    return;
}
?>
<li <?php wc_product_class('product', $product); ?>>
    <div class="product-block-list product-block-list-7">
        <div class="left">.</div>
        <div class="right">
            <?php woocommerce_template_loop_product_title(); ?>
            <?php bookory_wc_template_loop_product_author(); ?>
        </div>
    </div>
</li>
