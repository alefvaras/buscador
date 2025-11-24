<?php
if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('Bookory_WooCommerce')) :

    /**
     * The Bookory WooCommerce Integration class
     */
    class Bookory_WooCommerce {

        public $list_shortcodes;

        private $prefix = 'remove';

        /**
         * Setup class.
         *
         * @since 1.0
         */
        public function __construct() {
            $this->list_shortcodes = array(
                'recent_products',
                'sale_products',
                'best_selling_products',
                'top_rated_products',
                'featured_products',
                'related_products',
                'product_category',
                'products',
            );
            $this->init_shortcodes();

            add_action('after_setup_theme', array($this, 'setup'));
            add_filter('body_class', array($this, 'woocommerce_body_class'));
            add_action('widgets_init', array($this, 'widgets_init'));
            add_filter('bookory_theme_sidebar', array($this, 'set_sidebar'), 20);
            add_action('wp_enqueue_scripts', array($this, 'woocommerce_scripts'), 20);
            add_filter('woocommerce_enqueue_styles', '__return_empty_array');
            add_filter('woocommerce_output_related_products_args', array($this, 'related_products_args'));
            add_filter('woocommerce_product_thumbnails_columns', array($this, 'thumbnail_columns'));

            if (defined('WC_VERSION') && version_compare(WC_VERSION, '3.3', '<')) {
                add_filter('loop_shop_per_page', array($this, 'products_per_page'));
            }

            // Remove Shop Title
            add_filter('woocommerce_show_page_title', '__return_false');

            add_filter('bookory_register_nav_menus', [$this, 'add_location_menu']);
            add_filter('wp_nav_menu_items', [$this, 'add_extra_item_to_nav_menu'], 10, 2);

            add_filter('woocommerce_single_product_image_gallery_classes', function ($wrapper_classes) {
                $wrapper_classes[] = 'woocommerce-product-gallery-' . bookory_get_theme_option('single_product_gallery_layout', 'horizontal');

                return $wrapper_classes;
            });

            add_action('woocommerce_grouped_product_list_before_label', array(
                $this,
                'grouped_product_column_image'
            ), 10, 1);

            // Elementor Admin
            add_action('admin_action_elementor', array($this, 'register_elementor_wc_hook'), 1);
            add_filter('woocommerce_cross_sells_columns', array($this, 'woocommerce_cross_sells_columns'));

        }

        public function woocommerce_cross_sells_columns() {
            return wc_get_default_products_per_row();
        }

        public function register_elementor_wc_hook() {
            wc()->frontend_includes();
            require_once get_theme_file_path('inc/woocommerce/woocommerce-template-hooks.php');
            require_once get_theme_file_path('inc/woocommerce/template-hooks.php');
            bookory_include_hooks_product_blocks();
        }

        public function add_extra_item_to_nav_menu($items, $args) {
            if ($args->theme_location == 'my-account') {
                $items .= '<li><a href="' . esc_url(wp_logout_url(home_url())) . '">' . esc_html__('Logout', 'bookory') . '</a></li>';
            }

            return $items;
        }

        public function add_location_menu($locations) {
            $locations['my-account'] = esc_html__('My Account', 'bookory');

            return $locations;
        }

        /**
         * Sets up theme defaults and registers support for various WooCommerce features.
         *
         * Note that this function is hooked into the after_setup_theme hook, which
         * runs before the init hook. The init hook is too late for some features, such
         * as indicating support for post thumbnails.
         *
         * @return void
         * @since 2.4.0
         */
        public function setup() {
            add_theme_support(
                'woocommerce', apply_filters(
                    'bookory_woocommerce_args', array(
                        'product_grid' => array(
                            'default_columns' => 3,
                            'default_rows'    => 4,
                            'min_columns'     => 1,
                            'max_columns'     => 6,
                            'min_rows'        => 1,
                        ),
                    )
                )
            );


            /**
             * Add 'bookory_woocommerce_setup' action.
             *
             * @since  2.4.0
             */
            do_action('bookory_woocommerce_setup');
        }


        public function action_woocommerce_before_template_part($template_name, $template_path, $located, $args) {
            $product_style = bookory_get_theme_option('wocommerce_block_style', 0);
            if ($product_style != 0 && ($template_name == 'single-product/up-sells.php' || $template_name == 'single-product/related.php' || $template_name == 'cart/cross-sells.php')) {
                $template_custom = 'content-product-' . $product_style . '.php';
                add_filter('wc_get_template_part', function ($template, $slug, $name) use ($template_custom) {
                    if ($slug == 'content' && $name == 'product') {
                        return get_theme_file_path('woocommerce/' . $template_custom);
                    } else {
                        return $template;
                    }
                }, 10, 3);
            }
        }

        public function action_woocommerce_after_template_part($template_name, $template_path, $located, $args) {
            $product_style = bookory_get_theme_option('wocommerce_block_style', 0);
            if ($product_style != 0 && ($template_name == 'single-product/up-sells.php' || $template_name == 'single-product/related.php' || $template_name == 'cart/cross-sells.php')) {
                add_filter('wc_get_template_part', function ($template, $slug, $name) {
                    if ($slug == 'content' && $name == 'product') {
                        return get_theme_file_path('woocommerce/content-product.php');
                    } else {
                        return $template;
                    }
                }, 10, 3);
            }
        }

        private function init_shortcodes() {
            foreach ($this->list_shortcodes as $shortcode) {
                add_filter('shortcode_atts_' . $shortcode, array($this, 'set_shortcode_attributes'), 10, 3);

                add_action('woocommerce_shortcode_before_' . $shortcode . '_loop', array(
                    $this,
                    'shortcode_loop_start'
                ));
                add_action('woocommerce_shortcode_after_' . $shortcode . '_loop', array(
                    $this,
                    'shortcode_loop_end'
                ));
            }
        }

        public function shortcode_loop_end($atts = array()) {

            if (isset($atts['style'])) {
                if ($atts['style'] !== '') {

                    add_filter('wc_get_template_part', function ($template, $slug, $name) {
                        if ($slug == 'content' && $name == 'product') {
                            return get_theme_file_path('woocommerce/content-product.php');
                        } else {
                            return $template;
                        }
                    }, 10, 3);
                }
            }

            if (isset($atts['product_layout']) && $atts['product_layout'] === 'carousel') {
                echo '</div>';
            }
        }

        public function shortcode_loop_start($atts = array()) {
            if (isset($atts['style'])) {
                if ($atts['style'] !== '') {
                    $template_custom = 'content-product-' . $atts['style'] . '.php';

                    add_filter('wc_get_template_part', function ($template, $slug, $name) use ($template_custom) {
                        if ($slug == 'content' && $name == 'product') {
                            return get_theme_file_path('woocommerce/' . $template_custom);
                        } else {
                            return $template;
                        }
                    }, 10, 3);
                }
            }

            if (isset($atts['product_layout']) && $atts['product_layout'] === 'carousel') {
                echo '<div class="woocommerce-carousel" data-settings=\'' . $atts['carousel_settings'] . '\'>';
            }
        }

        public function set_shortcode_attributes($out, $pairs, $atts) {
            $out = wp_parse_args($atts, $out);

            return $out;
        }


        /**
         * Assign styles to individual theme mod.
         *
         * @return void
         * @since 2.1.0
         * @deprecated 2.3.1
         */
        public function set_bookory_style_theme_mods() {
            if (function_exists('wc_deprecated_function')) {
                wc_deprecated_function(__FUNCTION__, '2.3.1');
            } else {
                _deprecated_function(__FUNCTION__, '2.3.1');
            }
        }

        /**
         * Add WooCommerce specific classes to the body tag
         *
         * @param array $classes css classes applied to the body tag.
         *
         * @return array $classes modified to include 'woocommerce-active' class
         */
        public function woocommerce_body_class($classes) {
            $classes[] = 'woocommerce-active';

            // Remove `no-wc-breadcrumb` body class.
            $key = array_search('no-wc-breadcrumb', $classes, true);

            if (false !== $key) {
                unset($classes[$key]);
            }

            $style         = bookory_get_theme_option('wocommerce_block_style', 1);
            $layout        = bookory_get_theme_option('woocommerce_archive_layout', 'default');
            $sidebar       = bookory_get_theme_option('woocommerce_archive_sidebar', 'left');
            $sidebarsingle = bookory_get_theme_option('single_product_archive_sidebar', 'left');
            $productwidth  = bookory_get_theme_option('single_product_product_width', 'boxed');

            $classes[] = 'product-block-style-' . $style;

            if (bookory_is_product_archive()) {
                $classes[] = 'bookory-archive-product';

                if (is_active_sidebar('sidebar-woocommerce-shop')) {
                    if ($layout == 'default' && $sidebar == 'left') {
                        $classes[] = 'bookory-sidebar-left';
                    }

                    if ($layout == 'canvas') {
                        $classes[] = 'bookory-full-width-content shop_filter_canvas';
                    }

                    if ($layout == 'dropdown') {
                        $classes[] = 'bookory-full-width-content shop_filter_dropdown';
                    }
                } else {
                    $classes[] = 'bookory-full-width-content';
                }

                if (bookory_is_mas_woocommerce_brands_activated() && is_product_taxonomy() && is_tax($brand_taxonomy = Mas_WC_Brands()->get_brand_taxonomy())) {
                    $classes   = array_diff($classes, array(
                        'bookory-sidebar-left',
                        'bookory-sidebar-right',
                        'shop_filter_canvas',
                        'shop_filter_dropdown'
                    ));
                    $classes[] = 'bookory-full-width-content';
                }

            }

            if (bookory_is_product_archive() || is_cart() || is_checkout() || is_product()) {
                if (bookory_is_mas_woocommerce_brands_activated() && is_product_taxonomy() && is_tax($brand_taxonomy = Mas_WC_Brands()->get_brand_taxonomy())) {
                    $classes[] = 'bookory-product-laptop-4';
                    $classes[] = 'bookory-product-tablet-3';
                    $classes[] = 'bookory-product-mobile-2';
                } else {
                    if ($laptop = bookory_get_theme_option('wocommerce_row_laptop', 3)) {
                        $classes[] = 'bookory-product-laptop-' . $laptop;
                    }

                    if ($tablet = bookory_get_theme_option('wocommerce_row_tablet', 2)) {
                        $classes[] = 'bookory-product-tablet-' . $tablet;
                    }

                    if ($mobile = bookory_get_theme_option('wocommerce_row_mobile', 2)) {
                        $classes[] = 'bookory-product-mobile-' . $mobile;
                    }
                }

            }

            if (is_product()) {
                $classes[] = 'bookory-full-width-content';
                $classes[] = 'bookory-single-product-width-' . $productwidth;
                $classes[] = 'single-product-' . bookory_get_theme_option('single_product_gallery_layout', 'horizontal');

                if (is_active_sidebar('sidebar-woocommerce-detail')) {
                    if ($sidebarsingle == 'left') {
                        $classes[] = 'bookory-sidebar-left';
                    }
                    if ($sidebarsingle == 'right') {
                        $classes[] = 'bookory-sidebar-right';
                    }
                    $classes = array_diff($classes, array(
                        'bookory-full-width-content',
                    ));
                }
            }


            return $classes;
        }

        /**
         * WooCommerce specific scripts & stylesheets
         *
         * @since 1.0.0
         */
        public function woocommerce_scripts() {
            global $bookory_version;

            $suffix = (defined('SCRIPT_DEBUG') && SCRIPT_DEBUG) ? '' : '.min';
            wp_enqueue_style('bookory-woocommerce-style', get_template_directory_uri() . '/assets/css/woocommerce/woocommerce.css', array(), $bookory_version);
            wp_style_add_data('bookory-woocommerce-style', 'rtl', 'replace');

            // QuickView
            wp_dequeue_style('yith-quick-view');

            wp_register_script('bookory-header-cart', get_template_directory_uri() . '/assets/js/woocommerce/header-cart' . $suffix . '.js', array(), $bookory_version, true);
            wp_enqueue_script('bookory-header-cart');

            if (defined('WC_VERSION') && version_compare(WC_VERSION, '3.3', '<')) {
                wp_enqueue_style('bookory-woocommerce-legacy', get_template_directory_uri() . '/assets/css/woocommerce/woocommerce-legacy.css', array(), $bookory_version);
                wp_style_add_data('bookory-woocommerce-legacy', 'rtl', 'replace');
            }

            if (is_shop() || is_product() || is_product_taxonomy() || bookory_elementor_check_type('bookory-products')) {
                wp_enqueue_script('tooltipster');
                wp_enqueue_style('tooltipster');
            }

            wp_enqueue_script('bookory-products-ajax-search', get_template_directory_uri() . '/assets/js/woocommerce/product-ajax-search' . $suffix . '.js', array(
                'jquery',
                'tooltipster'
            ), $bookory_version, true);
            wp_enqueue_script('bookory-products', get_template_directory_uri() . '/assets/js/woocommerce/main' . $suffix . '.js', array(
                'jquery'
            ), $bookory_version, true);

            wp_enqueue_script('bookory-input-quantity', get_template_directory_uri() . '/assets/js/woocommerce/quantity' . $suffix . '.js', array('jquery'), $bookory_version, true);

            if (is_active_sidebar('sidebar-woocommerce-shop')) {
                wp_enqueue_script('bookory-off-canvas', get_template_directory_uri() . '/assets/js/woocommerce/off-canvas' . $suffix . '.js', array(), $bookory_version, true);
            }
            wp_enqueue_script('bookory-cart-canvas', get_template_directory_uri() . '/assets/js/woocommerce/cart-canvas' . $suffix . '.js', array(), $bookory_version, true);

            if (is_product() || is_cart()) {
                wp_enqueue_script('bookory-sticky-add-to-cart', get_template_directory_uri() . '/assets/js/sticky-add-to-cart' . $suffix . '.js', array(), $bookory_version, true);
                wp_enqueue_script('bookory-countdown');

                wp_enqueue_style('magnific-popup');
                wp_enqueue_script('magnific-popup');
                wp_enqueue_script('spritespin');

                wp_enqueue_script('slick');
                wp_enqueue_script('sticky-kit');
                wp_enqueue_script('bookory-single-product', get_template_directory_uri() . '/assets/js/woocommerce/single' . $suffix . '.js', array(
                    'jquery',
                    'slick',
                    'sticky-kit'
                ), $bookory_version, true);
            }


        }

        /**
         * Related Products Args
         *
         * @param array $args related products args.
         *
         * @return  array $args related products args
         * @since 1.0.0
         */
        public function related_products_args($args) {
            $product_items = 6;

            if (is_active_sidebar('sidebar-woocommerce-detail')) {
                $product_items = 4;
            }

            $args = apply_filters(
                'bookory_related_products_args', array(
                    'posts_per_page' => $product_items,
                    'columns'        => $product_items,
                )
            );

            return $args;
        }

        /**
         * Product gallery thumbnail columns
         *
         * @return integer number of columns
         * @since  1.0.0
         */
        public function thumbnail_columns() {
            $columns = bookory_get_theme_option('single_product_gallery_column', 3);

            if (bookory_get_theme_option('single_product_gallery_layout', 'horizontal') == 'vertical') {
                $columns = 1;
            }

            return intval(apply_filters('bookory_product_thumbnail_columns', $columns));
        }

        /**
         * Products per page
         *
         * @return integer number of products
         * @since  1.0.0
         */
        public function products_per_page() {
            return intval(apply_filters('bookory_products_per_page', 12));
        }

        /**
         * Query WooCommerce Extension Activation.
         *
         * @param string $extension Extension class name.
         *
         * @return boolean
         */
        public function is_woocommerce_extension_activated($extension = 'WC_Bookings') {
            return class_exists($extension) ? true : false;
        }

        public function widgets_init() {
            register_sidebar(array(
                'name'          => esc_html__('WooCommerce Shop', 'bookory'),
                'id'            => 'sidebar-woocommerce-shop',
                'description'   => esc_html__('Add widgets here to appear in your sidebar on blog posts and archive pages.', 'bookory'),
                'before_widget' => '<div id="%1$s" class="widget %2$s">',
                'after_widget'  => '</div>',
                'before_title'  => '<span class="gamma widget-title">',
                'after_title'   => '</span>',
            ));

            register_sidebar(array(
                'name'          => esc_html__('WooCommerce Detail', 'bookory'),
                'id'            => 'sidebar-woocommerce-detail',
                'description'   => esc_html__('Add widgets here to appear in your sidebar on blog posts and archive pages.', 'bookory'),
                'before_widget' => '<div id="%1$s" class="widget %2$s">',
                'after_widget'  => '</div>',
                'before_title'  => '<span class="gamma widget-title">',
                'after_title'   => '</span>',
            ));
        }

        public function set_sidebar($name) {
            $layout = bookory_get_theme_option('woocommerce_archive_layout', 'default');
            if (bookory_is_product_archive()) {
                if (is_active_sidebar('sidebar-woocommerce-shop') && $layout == 'default') {
                    $name = 'sidebar-woocommerce-shop';
                } else {
                    $name = '';
                }
                if (bookory_is_mas_woocommerce_brands_activated() && is_product_taxonomy() && is_tax($brand_taxonomy = Mas_WC_Brands()->get_brand_taxonomy())) {
                    $name = '';
                }

            }
            if (is_product()) {
                if (is_active_sidebar('sidebar-woocommerce-detail')) {
                    $name = 'sidebar-woocommerce-detail';
                } else {
                    $name = '';
                }
            }
            return $name;
        }

        public function grouped_product_column_image($grouped_product_child) {
            echo '<td class="woocommerce-grouped-product-image">' . $grouped_product_child->get_image('thumbnail') . '</td>';
        }

    }

endif;

return new Bookory_WooCommerce();
