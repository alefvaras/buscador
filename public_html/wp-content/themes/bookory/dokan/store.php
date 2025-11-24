<?php
/**
 * The Template for displaying all single posts.
 *
 * @package dokan
 * @package dokan - 2014 1.0
 */

if (!defined('ABSPATH')) exit; // Exit if accessed directly

$store_user   = dokan()->vendor->get(get_query_var('author'));
$store_info   = $store_user->get_shop_info();
$map_location = $store_user->get_location();
$layout       = get_theme_mod('store_layout', 'left');

get_header('shop');

?>

<div class="dokan-store-wrap layout-<?php echo esc_attr($layout); ?>">
    <div id="primary" class="dokan-single-store content-area">
        <div id="dokan-content" class="store-page-wrap woocommerce" role="main">

            <?php dokan_get_template_part('store-header'); ?>

            <?php do_action('dokan_store_profile_frame_after', $store_user->data, $store_info); ?>

            <?php if (have_posts()) { ?>

                <div class="seller-items">
                    <div class="bookory-products-overflow">
                        <?php woocommerce_product_loop_start(); ?>

                        <?php while (have_posts()) : the_post(); ?>

                            <?php wc_get_template_part('content', 'product'); ?>

                        <?php endwhile; // end of the loop. ?>

                        <?php woocommerce_product_loop_end(); ?>

                    </div>
                </div>

                <?php dokan_content_nav('nav-below'); ?>

            <?php } else { ?>

                <p class="dokan-info"><?php esc_html_e('No products were found of this vendor!', 'bookory'); ?></p>

            <?php } ?>
        </div>

    </div><!-- .dokan-single-store -->

    <?php if ('right' === $layout || 'left' === $layout) { ?>
        <?php dokan_get_template_part('store', 'sidebar', array('store_user' => $store_user, 'store_info' => $store_info, 'map_location' => $map_location)); ?>
    <?php } ?>

</div><!-- .dokan-store-wrap -->

<?php get_footer('shop'); ?>
