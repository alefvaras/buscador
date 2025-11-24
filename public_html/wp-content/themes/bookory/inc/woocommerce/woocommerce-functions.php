<?php
/**
 * Checks if the current page is a product archive
 *
 * @return boolean
 */
function bookory_is_product_archive() {
    if (is_shop() || is_product_taxonomy() || is_product_category() || is_product_tag()) {
        return true;
    } else {
        return false;
    }
}

/**
 * @param $product WC_Product
 */
function bookory_product_get_image($product) {
    return $product->get_image();
}

/**
 * @param $product WC_Product
 */
function bookory_product_get_price_html($product) {
    return $product->get_price_html();
}

/**
 * Retrieves the previous product.
 *
 * @param bool $in_same_term Optional. Whether post should be in a same taxonomy term. Default false.
 * @param array|string $excluded_terms Optional. Comma-separated list of excluded term IDs. Default empty.
 * @param string $taxonomy Optional. Taxonomy, if $in_same_term is true. Default 'product_cat'.
 * @return WC_Product|false Product object if successful. False if no valid product is found.
 * @since 2.4.3
 *
 */
function bookory_get_previous_product($in_same_term = false, $excluded_terms = '', $taxonomy = 'product_cat') {
    $product = new Bookory_WooCommerce_Adjacent_Products($in_same_term, $excluded_terms, $taxonomy, true);
    return $product->get_product();
}

/**
 * Retrieves the next product.
 *
 * @param bool $in_same_term Optional. Whether post should be in a same taxonomy term. Default false.
 * @param array|string $excluded_terms Optional. Comma-separated list of excluded term IDs. Default empty.
 * @param string $taxonomy Optional. Taxonomy, if $in_same_term is true. Default 'product_cat'.
 * @return WC_Product|false Product object if successful. False if no valid product is found.
 * @since 2.4.3
 *
 */
function bookory_get_next_product($in_same_term = false, $excluded_terms = '', $taxonomy = 'product_cat') {
    $product = new Bookory_WooCommerce_Adjacent_Products($in_same_term, $excluded_terms, $taxonomy);
    return $product->get_product();
}


function bookory_is_woocommerce_extension_activated($extension = 'WC_Bookings') {
    if ($extension == 'YITH_WCQV') {
        return class_exists($extension) && class_exists('YITH_WCQV_Frontend') ? true : false;
    }

    return class_exists($extension) ? true : false;
}

function bookory_woocommerce_pagination_args($args) {
    $args['prev_text'] = '<i class="bookory-icon bookory-icon-angle-left"></i><span>' . esc_html__('Previous', 'bookory') . '</span>';
    $args['next_text'] = '<span>' . esc_html__('Next', 'bookory') . '</span><i class="bookory-icon bookory-icon-angle-right"></i>';
    return $args;
}

add_filter('woocommerce_pagination_args', 'bookory_woocommerce_pagination_args', 10, 1);


function bookory_unsupported_theme_remove_review_tab($tabs) {
    unset($tabs['reviews']);
    return $tabs;
}

/**
 * Check if a product is a deal
 *
 * @param int|object $product
 *
 * @return bool
 */
function bookory_woocommerce_is_deal_product($product) {
    $product = is_numeric($product) ? wc_get_product($product) : $product;

    // It must be a sale product first
    if (!$product->is_on_sale()) {
        return false;
    }

    if (!$product->is_in_stock()) {
        return false;
    }

    // Only support product type "simple" and "external"
    if (!$product->is_type('simple') && !$product->is_type('external')) {
        return false;
    }

    $deal_quantity = get_post_meta($product->get_id(), '_deal_quantity', true);

    if ($deal_quantity > 0) {
        return true;
    }

    return false;
}

function woocommerce_template_loop_rating() {
    global $product;
    if (!wc_review_ratings_enabled()) {
        return;
    }
    if ($rating_html = wc_get_rating_html($product->get_average_rating())) {
        echo apply_filters('bookory_woocommerce_rating_html', '<div class="count-review">' . $rating_html . '<span>' . number_format_i18n($product->get_review_count()) . '</span></div>');
    } else {
        echo '<div class="count-review"><div class="star-rating"></div><span>' . number_format_i18n($product->get_review_count()) . '</span></div>';
    }
}

if (!function_exists('bookory_check_quantity_product')) {
    function bookory_check_quantity_product() {
        global $product;
        $quantity = get_post_meta($product->get_id(), '_sold_individually', true);
        if ($quantity == 'yes' || $product->get_stock_status() == 'outofstock' || $product->is_type('variable') || $product->is_type('grouped')) {
            return true;
        } else {
            return false;
        }
    }
}

if (!function_exists('bookory_ajax_search_products')) {
    function bookory_ajax_search_products() {
        global $woocommerce;
        $search_keyword = $_REQUEST['query'];

        $ordering_args = $woocommerce->query->get_catalog_ordering_args('date', 'desc');
        $suggestions   = array();

        $args = array(
            's'                   => apply_filters('bookory_ajax_search_products_search_query', $search_keyword),
            'post_type'           => 'product',
            'post_status'         => 'publish',
            'ignore_sticky_posts' => 1,
            'orderby'             => $ordering_args['orderby'],
            'order'               => $ordering_args['order'],
            'posts_per_page'      => apply_filters('bookory_ajax_search_products_posts_per_page', 8),

        );

        $args['tax_query']['relation'] = 'AND';

        if (!empty($_REQUEST['product_cat'])) {
            $args['tax_query'][] = array(
                'taxonomy' => 'product_cat',
                'field'    => 'slug',
                'terms'    => strip_tags($_REQUEST['product_cat']),
                'operator' => 'IN'
            );
        }

        $products = get_posts($args);

        if (!empty($products)) {
            foreach ($products as $post) {
                $product       = wc_get_product($post);
                $product_image = wp_get_attachment_image_src(get_post_thumbnail_id($product->get_id()));

                $suggestions[] = apply_filters('bookory_suggestion', array(
                    'id'    => $product->get_id(),
                    'value' => strip_tags($product->get_title()),
                    'url'   => $product->get_permalink(),
                    'img'   => esc_url($product_image[0]),
                    'price' => $product->get_price_html(),
                ), $product);
            }
        } else {
            $suggestions[] = array(
                'id'    => -1,
                'value' => esc_html__('No results', 'bookory'),
                'url'   => '',
            );
        }
        wp_reset_postdata();

        echo json_encode($suggestions);
        die();
    }
}

add_action('wp_ajax_bookory_ajax_search_products', 'bookory_ajax_search_products');
add_action('wp_ajax_nopriv_bookory_ajax_search_products', 'bookory_ajax_search_products');

if (bookory_is_mas_woocommerce_brands_activated()) {
    if (!function_exists('bookory_mas_product_authors_list_terms_clauses')) {
        function bookory_mas_product_authors_list_terms_clauses($clauses, $taxonomies, $args) {
            if (!in_array(Mas_WC_Brands()->get_brand_taxonomy(), $taxonomies)) {
                return $clauses;
            }

            global $wpdb;

            if (!isset($args['first_letter'])) {
                return $clauses;
            }

            $clauses['where'] .= ' AND ' . $wpdb->prepare("t.name LIKE %s", $wpdb->esc_like($args['first_letter']) . '%');

            return $clauses;
        }
    }

    add_filter('terms_clauses', 'bookory_mas_product_authors_list_terms_clauses', 10, 3);

    if (!function_exists('bookory_mas_product_authors_list_query_vars')) {
        function bookory_mas_product_authors_list_query_vars($qvars) {
            $qvars[] = 'first_letter';
            return $qvars;
        }
    }

    add_filter('query_vars', 'bookory_mas_product_authors_list_query_vars');
}

if ( ! function_exists( 'wvs_get_wc_attribute_taxonomy' ) ){
    function wvs_get_wc_attribute_taxonomy( $attribute_name ) {

        $transient_name = sprintf( 'wvs_attribute_taxonomy_%s', $attribute_name );

        $cache = new Woo_Variation_Swatches_Cache( $transient_name, 'wvs_attribute_taxonomy' );

        if ( isset( $_GET['wvs_clear_transient'] ) ) {
            $cache->delete_transient();
        }

        if ( false === ( $attribute_taxonomy = $cache->get_transient() ) ) {

            global $wpdb;

            $attribute_name = str_replace( 'pa_', '', wc_sanitize_taxonomy_name( $attribute_name ) );

            $attribute_taxonomy = $wpdb->get_row( "SELECT * FROM " . $wpdb->prefix . "woocommerce_attribute_taxonomies WHERE attribute_name='{$attribute_name}'" );

            $cache->set_transient( $attribute_taxonomy );
        }

        return apply_filters( 'wvs_get_wc_attribute_taxonomy', $attribute_taxonomy, $attribute_name );
    }
}

if ( ! function_exists( 'wvs_taxonomy_meta_fields' ) ){
    function wvs_taxonomy_meta_fields( $field_id = false ) {

        $fields = array();

        $fields['color'] = array(
            array(
                'label' => esc_html__( 'Color', 'bookory' ), // <label>
                'desc'  => esc_html__( 'Choose a color', 'bookory' ), // description
                'id'    => 'product_attribute_color', // name of field
                'type'  => 'color'
            )
        );

        $fields['image'] = array(
            array(
                'label' => esc_html__( 'Image', 'bookory' ), // <label>
                'desc'  => esc_html__( 'Choose an Image', 'bookory' ), // description
                'id'    => 'product_attribute_image', // name of field
                'type'  => 'image'
            )
        );

        $fields = apply_filters( 'wvs_product_taxonomy_meta_fields', $fields );

        if ( $field_id ) {
            return isset( $fields[ $field_id ] ) ? $fields[ $field_id ] : array();
        }

        return $fields;

    }
}