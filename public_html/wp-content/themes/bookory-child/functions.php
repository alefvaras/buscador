<?php
if (!defined('ABSPATH')) exit;

// =====================================================
// ESTILOS
// =====================================================
function bookory_child_enqueue_styles() {
    $parent_style = 'bookory-style';
    $theme = wp_get_theme();
    
    wp_enqueue_style($parent_style, get_template_directory_uri() . '/style.css', [], $theme->parent()->get('Version'));
    wp_enqueue_style('bookory-child-style', get_stylesheet_uri(), [$parent_style], $theme->get('Version'));
    wp_enqueue_style('bookory-advanced-search', get_stylesheet_directory_uri() . '/assets/css/advanced-search.css', [], $theme->get('Version'));
}
add_action('wp_enqueue_scripts', 'bookory_child_enqueue_styles', 15);

// =====================================================
// SCRIPTS
// =====================================================
function bookory_child_enqueue_scripts() {
    $theme = wp_get_theme();
    
    wp_enqueue_script('bookory-advanced-search', get_stylesheet_directory_uri() . '/assets/js/advanced-search.js', ['jquery'], $theme->get('Version'), true);
    
    wp_localize_script('bookory-advanced-search', 'bookorySearch', [
        'ajaxurl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('bookory_search_nonce'),
        'homeurl' => home_url(),
        'strings' => [
            'searching' => __('Buscando...', 'bookory-child'),
            'no_results' => __('No se encontraron resultados', 'bookory-child'),
            'error' => __('Error en la búsqueda', 'bookory-child')
        ]
    ]);
}
add_action('wp_enqueue_scripts', 'bookory_child_enqueue_scripts', 20);

// =====================================================
// SETUP
// =====================================================
function bookory_child_setup() {
    load_child_theme_textdomain('bookory-child', get_stylesheet_directory() . '/languages');
}
add_action('after_setup_theme', 'bookory_child_setup', 11);

// =====================================================
// SOBRESCRIBIR BÚSQUEDA AJAX DEL TEMA PADRE
// =====================================================

/**
 * Remover los hooks del padre y agregar los nuestros
 * Usamos prioridad alta en 'wp_loaded' para asegurar que el padre ya registró todo
 */
function bookory_child_override_ajax_search() {
    // Remover la función del padre
    remove_action('wp_ajax_bookory_ajax_search_products', 'bookory_ajax_search_products', 10);
    remove_action('wp_ajax_nopriv_bookory_ajax_search_products', 'bookory_ajax_search_products', 10);
    
    // Agregar nuestra función mejorada
    add_action('wp_ajax_bookory_ajax_search_products', 'bookory_child_improved_search', 10);
    add_action('wp_ajax_nopriv_bookory_ajax_search_products', 'bookory_child_improved_search', 10);
}
add_action('wp_loaded', 'bookory_child_override_ajax_search', 99);

/**
 * Función de búsqueda mejorada con normalización avanzada
 * Busca por título normalizado (sin espacios), título normal y SKU
 * Versión 2.0 - Mejorada para casos como "DANDADAN 20" vs "Dan Da Dan 20"
 */
function bookory_child_improved_search() {
    global $wpdb;

    $search_keyword = isset($_REQUEST['query']) ? sanitize_text_field($_REQUEST['query']) : '';
    $product_cat = isset($_REQUEST['product_cat']) ? sanitize_text_field($_REQUEST['product_cat']) : '';

    if (empty($search_keyword) || strlen($search_keyword) < 2) {
        wp_send_json([]);
    }

    // LOG para depuración (comentar en producción)
    error_log("=== BOOKORY SEARCH DEBUG ===");
    error_log("Original query: " . $search_keyword);

    // Normalizar: quitar espacios, guiones, puntos y convertir a minúsculas
    $normalized_keyword = strtolower(str_replace([' ', '-', '_', '.', ','], '', $search_keyword));
    error_log("Normalized query: " . $normalized_keyword);

    $suggestions = [];

    // Query SQL mejorada con normalización completa
    $sql = "
        SELECT DISTINCT p.ID, p.post_title
        FROM {$wpdb->posts} p
        LEFT JOIN {$wpdb->postmeta} pm_sku ON p.ID = pm_sku.post_id AND pm_sku.meta_key = '_sku'
        LEFT JOIN {$wpdb->postmeta} pm_stock ON p.ID = pm_stock.post_id AND pm_stock.meta_key = '_stock_status'
    ";

    // Join para categoría si se especifica
    if (!empty($product_cat)) {
        $sql .= "
            INNER JOIN {$wpdb->term_relationships} tr ON p.ID = tr.object_id
            INNER JOIN {$wpdb->term_taxonomy} tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
            INNER JOIN {$wpdb->terms} t ON tt.term_id = t.term_id
        ";
    }

    $sql .= "
        WHERE p.post_type = 'product'
        AND p.post_status = 'publish'
        AND (
            LOWER(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(p.post_title, ' ', ''), '-', ''), '_', ''), '.', ''), ',', '')) LIKE %s
            OR LOWER(p.post_title) LIKE %s
            OR LOWER(pm_sku.meta_value) LIKE %s
        )
    ";

    $params = [
        '%' . $wpdb->esc_like($normalized_keyword) . '%',
        '%' . $wpdb->esc_like(strtolower($search_keyword)) . '%',
        '%' . $wpdb->esc_like($search_keyword) . '%'
    ];

    if (!empty($product_cat)) {
        $sql .= " AND tt.taxonomy = 'product_cat' AND t.slug = %s";
        $params[] = $product_cat;
    }

    // Ordenar por relevancia: primero coincidencias normalizadas al inicio, luego en stock
    $sql .= " ORDER BY
        CASE
            WHEN LOWER(REPLACE(REPLACE(REPLACE(REPLACE(p.post_title, ' ', ''), '-', ''), '_', ''), '.', '')) LIKE %s THEN 1
            WHEN LOWER(p.post_title) LIKE %s THEN 2
            WHEN LOWER(pm_sku.meta_value) LIKE %s THEN 3
            ELSE 4
        END,
        CASE WHEN pm_stock.meta_value = 'instock' THEN 1 ELSE 2 END,
        p.post_title ASC
        LIMIT 20";

    $params[] = $wpdb->esc_like($normalized_keyword) . '%';
    $params[] = $wpdb->esc_like(strtolower($search_keyword)) . '%';
    $params[] = '%' . $wpdb->esc_like($search_keyword) . '%';

    error_log("SQL params: " . print_r($params, true));

    $products = $wpdb->get_results($wpdb->prepare($sql, $params));

    error_log("Found products: " . ($products ? count($products) : 0));

    if (!empty($products)) {
        foreach ($products as $post) {
            $product = wc_get_product($post->ID);
            if (!$product) continue;

            error_log("Product found: ID=" . $post->ID . ", Title=" . $post->post_title);

            $product_image = wp_get_attachment_image_src(get_post_thumbnail_id($product->get_id()));

            $suggestions[] = [
                'id'    => $product->get_id(),
                'value' => strip_tags($product->get_title()),
                'url'   => $product->get_permalink(),
                'img'   => $product_image ? esc_url($product_image[0]) : '',
                'price' => $product->get_price_html(),
            ];
        }
    }

    if (empty($suggestions)) {
        error_log("No products found - returning empty result");
        $suggestions[] = [
            'id'    => -1,
            'value' => esc_html__('No results', 'bookory'),
            'url'   => '',
        ];
    }

    error_log("=== END SEARCH DEBUG ===");
    wp_send_json($suggestions);
}

// =====================================================
// ENTERPRISE SEARCH CLASS (para búsqueda en página completa)
// =====================================================
require_once get_stylesheet_directory() . '/inc/class-enterprise-search.php';

// =====================================================
// LIMPIAR CACHE
// =====================================================
function bookory_clear_search_cache() {
    if (class_exists('Bookory_Enterprise_Search')) {
        $search = Bookory_Enterprise_Search::get_instance();
        $search->clear_search_cache();
    }
}
add_action('save_post', 'bookory_clear_search_cache');
add_action('delete_post', 'bookory_clear_search_cache');
add_action('woocommerce_product_set_stock_status', 'bookory_clear_search_cache');