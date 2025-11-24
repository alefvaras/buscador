<?php
/**
 * Dokan Widget Content Product Template
 *
 * @since   2.4
 *
 * @package dokan
 */

$img_kses = apply_filters(
    'dokan_product_image_attributes', [
        'img' => [
            'alt'         => [],
            'class'       => [],
            'height'      => [],
            'src'         => [],
            'width'       => [],
            'srcset'      => [],
            'data-srcset' => [],
            'data-src'    => [],
        ],
    ]
);

?>

<?php if ($r->have_posts()) : ?>
    <ul class="dokan-bestselling-product-widget product_list_widget">
        <?php
        while ($r->have_posts()) :
            $r->the_post();
            global $product;
            ?>
            <li class="product">
                <div class="product-list-inner">
                    <a href="<?php echo esc_url(get_permalink(dokan_get_prop($product, 'id'))); ?>" title="<?php echo esc_attr($product->get_title()); ?>">
                        <?php echo wp_kses($product->get_image(), $img_kses); ?>
                    </a>
                    <div class="product-content">
                        <a class="product-title" href="<?php echo esc_url($product->get_permalink()); ?>"><span><?php echo esc_html($product->get_title()); ?></span></a>
                        <?php
                        if (!empty($show_rating)) {
                            echo wc_get_rating_html($product->get_average_rating());
                        }
                        bookory_wc_template_loop_product_author();
                        woocommerce_template_loop_price();
                        ?>
                    </div>
                </div>
            </li>
        <?php endwhile; ?>
    </ul>
<?php else : ?>
    <p><?php esc_html_e('No products found', 'bookory'); ?></p>
<?php endif; ?>
