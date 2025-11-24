<?php
if (!defined('ABSPATH')) exit;

class Bookory_Enterprise_Search {
    private static $instance = null;
    private $cache_group = 'bookory_search';
    private $cache_time = 3600;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_action('wp_ajax_bookory_advanced_search', [$this, 'ajax_search']);
        add_action('wp_ajax_nopriv_bookory_advanced_search', [$this, 'ajax_search']);
        add_filter('posts_search', [$this, 'improve_search_query'], 10, 2);
        add_filter('posts_orderby', [$this, 'search_orderby'], 10, 2);
    }
    
    public function ajax_search() {
        check_ajax_referer('bookory_search_nonce', 'nonce');
        
        $query = sanitize_text_field($_POST['query'] ?? '');
        $category = sanitize_text_field($_POST['category'] ?? '');
        $post_type = sanitize_text_field($_POST['post_type'] ?? 'product');
        
        if (strlen($query) < 2) {
            wp_send_json_error(['message' => 'Query demasiado corto']);
        }
        
        $cache_key = md5($query . $category . $post_type);
        $cached = wp_cache_get($cache_key, $this->cache_group);
        
        if (false !== $cached) {
            wp_send_json_success($cached);
        }
        
        $results = $this->perform_search($query, $category, $post_type);
        
        wp_cache_set($cache_key, $results, $this->cache_group, $this->cache_time);
        wp_send_json_success($results);
    }
    
    private function perform_search($query, $category = '', $post_type = 'product') {
        global $wpdb;
        
        $results = [];
        $query_parts = $this->prepare_query($query);
        
        $args = [
            'post_type' => $post_type,
            'post_status' => 'publish',
            'posts_per_page' => 20,
            's' => $query,
            'orderby' => 'relevance',
            'meta_query' => [],
            'tax_query' => []
        ];
        
        if ($post_type === 'product') {
            $args['meta_query'][] = [
                'relation' => 'OR',
                [
                    'key' => '_stock_status',
                    'value' => 'instock',
                    'compare' => '='
                ],
                [
                    'key' => '_backorders',
                    'value' => 'yes',
                    'compare' => '='
                ]
            ];
            
            if (!empty($category)) {
                $args['tax_query'][] = [
                    'taxonomy' => 'product_cat',
                    'field' => 'slug',
                    'terms' => $category
                ];
            }
            
            $sku_products = $this->search_by_sku($query);
            if (!empty($sku_products)) {
                $args['post__in'] = array_merge($args['post__in'] ?? [], $sku_products);
            }
        }
        
        add_filter('posts_search', [$this, 'custom_search_query'], 10, 2);
        $search_query = new WP_Query($args);
        remove_filter('posts_search', [$this, 'custom_search_query']);
        
        if ($search_query->have_posts()) {
            while ($search_query->have_posts()) {
                $search_query->the_post();
                $results[] = $this->format_result(get_post(), $query);
            }
            wp_reset_postdata();
        }
        
        $results = $this->rank_results($results, $query);
        
        return [
            'results' => array_slice($results, 0, 10),
            'total' => count($results),
            'suggestions' => $this->get_suggestions($query, $results)
        ];
    }
    
    private function search_by_sku($query) {
        global $wpdb;
        
        $sku_results = $wpdb->get_col($wpdb->prepare("
            SELECT DISTINCT post_id 
            FROM {$wpdb->postmeta} 
            WHERE meta_key = '_sku' 
            AND meta_value LIKE %s
            LIMIT 20
        ", '%' . $wpdb->esc_like($query) . '%'));
        
        return $sku_results;
    }
    
    public function custom_search_query($search, $query) {
        global $wpdb;

        if (!$query->is_search() || empty($query->query_vars['s'])) {
            return $search;
        }

        $search_term = $query->query_vars['s'];
        $search_parts = explode(' ', $search_term);

        $search_conditions = [];

        foreach ($search_parts as $term) {
            $term = $wpdb->esc_like($term);
            // Normalizar el término eliminando espacios para la comparación normalizada
            $term_normalized = str_replace(' ', '', strtolower($term));
            $term_normalized_escaped = $wpdb->esc_like($term_normalized);

            $search_conditions[] = "
                ({$wpdb->posts}.post_title LIKE '%{$term}%')
                OR ({$wpdb->posts}.post_content LIKE '%{$term}%')
                OR ({$wpdb->posts}.post_excerpt LIKE '%{$term}%')
                OR (LOWER(REPLACE(REPLACE(REPLACE({$wpdb->posts}.post_title, ' ', ''), '-', ''), '.', '')) LIKE '%{$term_normalized_escaped}%')
                OR (LOWER(REPLACE(REPLACE(REPLACE({$wpdb->posts}.post_content, ' ', ''), '-', ''), '.', '')) LIKE '%{$term_normalized_escaped}%')
                OR EXISTS (
                    SELECT 1 FROM {$wpdb->postmeta}
                    WHERE {$wpdb->postmeta}.post_id = {$wpdb->posts}.ID
                    AND (
                        {$wpdb->postmeta}.meta_value LIKE '%{$term}%'
                        OR LOWER(REPLACE(REPLACE(REPLACE({$wpdb->postmeta}.meta_value, ' ', ''), '-', ''), '.', '')) LIKE '%{$term_normalized_escaped}%'
                    )
                )
                OR EXISTS (
                    SELECT 1 FROM {$wpdb->term_relationships} tr
                    INNER JOIN {$wpdb->term_taxonomy} tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
                    INNER JOIN {$wpdb->terms} t ON tt.term_id = t.term_id
                    WHERE tr.object_id = {$wpdb->posts}.ID
                    AND (
                        t.name LIKE '%{$term}%'
                        OR LOWER(REPLACE(REPLACE(REPLACE(t.name, ' ', ''), '-', ''), '.', '')) LIKE '%{$term_normalized_escaped}%'
                    )
                )
            ";
        }

        $search = ' AND (' . implode(' OR ', $search_conditions) . ')';

        return $search;
    }
    
    public function improve_search_query($search, $query) {
        if (!$query->is_search() || !is_main_query()) {
            return $search;
        }
        
        return $this->custom_search_query($search, $query);
    }
    
    public function search_orderby($orderby, $query) {
        if (!$query->is_search() || !is_main_query()) {
            return $orderby;
        }
        
        global $wpdb;
        $search_term = $query->query_vars['s'];
        
        return "
            CASE 
                WHEN {$wpdb->posts}.post_title LIKE '{$search_term}%' THEN 1
                WHEN {$wpdb->posts}.post_title LIKE '%{$search_term}%' THEN 2
                WHEN {$wpdb->posts}.post_content LIKE '%{$search_term}%' THEN 3
                ELSE 4
            END, {$wpdb->posts}.post_date DESC
        ";
    }
    
    private function format_result($post, $query) {
        $result = [
            'id' => $post->ID,
            'title' => $post->post_title,
            'url' => get_permalink($post->ID),
            'type' => $post->post_type,
            'relevance' => 0
        ];
        
        if ($post->post_type === 'product') {
            $product = wc_get_product($post->ID);
            if ($product) {
                $result['price'] = $product->get_price_html();
                $result['image'] = get_the_post_thumbnail_url($post->ID, 'thumbnail');
                $result['sku'] = $product->get_sku();
                $result['stock'] = $product->is_in_stock();
                $result['rating'] = $product->get_average_rating();
            }
        } else {
            $result['excerpt'] = wp_trim_words($post->post_content, 20);
            $result['image'] = get_the_post_thumbnail_url($post->ID, 'thumbnail');
        }
        
        return $result;
    }
    
    private function rank_results($results, $query) {
        $query_lower = strtolower($query);
        $query_parts = explode(' ', $query_lower);

        // Normalizar query eliminando espacios, guiones y puntos
        $query_normalized = str_replace([' ', '-', '.'], '', $query_lower);

        foreach ($results as &$result) {
            $title_lower = strtolower($result['title']);
            $title_normalized = str_replace([' ', '-', '.'], '', $title_lower);
            $score = 0;

            // Búsqueda exacta (comparación normal y normalizada)
            if (stripos($title_lower, $query_lower) === 0) {
                $score += 100;
            } elseif (stripos($title_normalized, $query_normalized) === 0) {
                $score += 95; // Casi tan relevante como coincidencia exacta
            } elseif (stripos($title_lower, $query_lower) !== false) {
                $score += 50;
            } elseif (stripos($title_normalized, $query_normalized) !== false) {
                $score += 45; // Coincidencia normalizada en el medio
            }

            // Búsqueda por partes
            foreach ($query_parts as $part) {
                $part_normalized = str_replace([' ', '-', '.'], '', $part);

                if (stripos($title_lower, $part) !== false) {
                    $score += 10;
                } elseif (stripos($title_normalized, $part_normalized) !== false) {
                    $score += 8; // Coincidencia normalizada de parte
                }
            }

            // Búsqueda en SKU (normalizada y no normalizada)
            if (isset($result['sku'])) {
                $sku_lower = strtolower($result['sku']);
                $sku_normalized = str_replace([' ', '-', '.'], '', $sku_lower);

                if (stripos($sku_lower, $query_lower) !== false) {
                    $score += 75;
                } elseif (stripos($sku_normalized, $query_normalized) !== false) {
                    $score += 70;
                }
            }

            // Bonus por disponibilidad
            if ($result['type'] === 'product' && isset($result['stock']) && $result['stock']) {
                $score += 5;
            }

            $result['relevance'] = $score;
        }

        usort($results, function($a, $b) {
            return $b['relevance'] - $a['relevance'];
        });

        return $results;
    }
    
    private function get_suggestions($query, $results) {
        if (count($results) > 0) {
            return [];
        }
        
        $suggestions = [];
        $similar = $this->get_similar_terms($query);
        
        foreach ($similar as $term) {
            $suggestions[] = [
                'term' => $term,
                'url' => home_url('/?s=' . urlencode($term))
            ];
        }
        
        return array_slice($suggestions, 0, 5);
    }
    
    private function get_similar_terms($query) {
        global $wpdb;
        
        $terms = $wpdb->get_col($wpdb->prepare("
            SELECT DISTINCT t.name
            FROM {$wpdb->terms} t
            INNER JOIN {$wpdb->term_taxonomy} tt ON t.term_id = tt.term_id
            WHERE tt.taxonomy IN ('product_cat', 'product_tag', 'category', 'post_tag')
            AND t.name LIKE %s
            LIMIT 10
        ", '%' . $wpdb->esc_like($query) . '%'));
        
        return $terms;
    }
    
    private function prepare_query($query) {
        $query = trim($query);
        $parts = preg_split('/\s+/', $query);
        return array_filter($parts);
    }
    
    public function clear_search_cache() {
        wp_cache_flush_group($this->cache_group);
    }
}

Bookory_Enterprise_Search::get_instance();