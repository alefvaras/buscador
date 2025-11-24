<?php

if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('Bookory_Elementor')) :

    /**
     * The Bookory Elementor Integration class
     */
    class Bookory_Elementor {
        private $suffix = '';

        public function __construct() {
            $this->suffix = (defined('SCRIPT_DEBUG') && SCRIPT_DEBUG) ? '' : '.min';

            add_action('elementor/frontend/after_enqueue_scripts', [$this, 'register_auto_scripts_frontend']);
            add_action('elementor/init', array($this, 'add_category'));
            add_action('wp_enqueue_scripts', [$this, 'add_scripts'], 15);
            add_action('elementor/widgets/register', array($this, 'customs_widgets'));
            add_action('elementor/widgets/register', array($this, 'include_widgets'));
            add_action('elementor/frontend/after_enqueue_scripts', [$this, 'add_js']);

            // Custom Animation Scroll
            add_filter('elementor/controls/animations/additional_animations', [$this, 'add_animations_scroll']);
            add_filter('wp_enqueue_scripts', [$this, 'add_animations_scroll_style']);

            // Elementor Fix Noitice WooCommerce
            add_action('elementor/editor/before_enqueue_scripts', array($this, 'woocommerce_fix_notice'));

            // Backend
            add_action('elementor/editor/after_enqueue_styles', [$this, 'add_style_editor'], 99);

            // Add Icon Custom
            add_action('elementor/icons_manager/native', [$this, 'add_icons_native']);
            add_action('elementor/controls/register', [$this, 'add_icons']);

            // Add Breakpoints
            add_action('wp_enqueue_scripts', 'bookory_elementor_breakpoints', 9999);

            if (!bookory_is_elementor_pro_activated()) {
                require trailingslashit(get_template_directory()) . 'inc/elementor/custom-css.php';
                require trailingslashit(get_template_directory()) . 'inc/elementor/sticky-section.php';
                if (is_admin()) {
                    add_action('manage_elementor_library_posts_columns', [$this, 'admin_columns_headers']);
                    add_action('manage_elementor_library_posts_custom_column', [$this, 'admin_columns_content'], 10, 2);
                }
            }

            add_filter('elementor/fonts/additional_fonts', [$this, 'additional_fonts']);
            add_action('wp_enqueue_scripts', [$this, 'elementor_kit']);
        }

        public function elementor_kit() {
            $active_kit_id = Elementor\Plugin::$instance->kits_manager->get_active_id();
            Elementor\Plugin::$instance->kits_manager->frontend_before_enqueue_styles();
            $myvals = get_post_meta($active_kit_id, '_elementor_page_settings', true);
            if (!empty($myvals)) {
                $css = '';
                foreach ($myvals['system_colors'] as $key => $value) {
                    $css .= $value['color'] !== '' ? '--' . $value['_id'] . ':' . $value['color'] . ';' : '';
                }

                $var = "body{{$css}}";
                wp_add_inline_style('bookory-style', $var);
            }
        }

        public function additional_fonts($fonts) {
            $fonts["Sora"]     = 'googlefonts';
            return $fonts;
        }

        public function admin_columns_headers($defaults) {
            $defaults['shortcode'] = esc_html__('Shortcode', 'bookory');

            return $defaults;
        }

        public function admin_columns_content($column_name, $post_id) {
            if ('shortcode' === $column_name) {
                ob_start();
                ?>
                <input class="elementor-shortcode-input" type="text" readonly onfocus="this.select()" value="[hfe_template id='<?php echo esc_attr($post_id); ?>']"/>
                <?php
                ob_get_contents();
            }
        }

        public function add_js() {
            global $bookory_version;
            wp_enqueue_script('bookory-elementor-frontend', get_theme_file_uri('/assets/js/elementor-frontend.js'), [], $bookory_version);
        }

        public function add_style_editor() {
            global $bookory_version;
            wp_enqueue_style('bookory-elementor-editor-icon', get_theme_file_uri('/assets/css/admin/elementor/icons.css'), [], $bookory_version);
        }

        public function add_scripts() {
            global $bookory_version;
            $suffix = (defined('SCRIPT_DEBUG') && SCRIPT_DEBUG) ? '' : '.min';
            wp_enqueue_style('bookory-elementor', get_template_directory_uri() . '/assets/css/base/elementor.css', '', $bookory_version);
            wp_style_add_data('bookory-elementor', 'rtl', 'replace');

            // Add Scripts
            wp_register_script('tweenmax', get_theme_file_uri('/assets/js/vendor/TweenMax.min.js'), array('jquery'), '1.11.1');
            wp_register_script('parallaxmouse', get_theme_file_uri('/assets/js/vendor/jquery-parallax.js'), array('jquery'), $bookory_version);

            if (bookory_elementor_check_type('animated-bg-parallax')) {
                wp_enqueue_script('tweenmax');
                wp_enqueue_script('jquery-panr', get_theme_file_uri('/assets/js/vendor/jquery-panr' . $suffix . '.js'), array('jquery'), '0.0.1');
            }
        }


        public function register_auto_scripts_frontend() {
            global $bookory_version;
            wp_register_script('bookory-elementor-author-list', get_theme_file_uri('/assets/js/elementor/author-list.js'), array('jquery','elementor-frontend'), $bookory_version, true);
            wp_register_script('bookory-elementor-brand', get_theme_file_uri('/assets/js/elementor/brand.js'), array('jquery','elementor-frontend'), $bookory_version, true);
            wp_register_script('bookory-elementor-countdown', get_theme_file_uri('/assets/js/elementor/countdown.js'), array('jquery','elementor-frontend'), $bookory_version, true);
            wp_register_script('bookory-elementor-dokan-store', get_theme_file_uri('/assets/js/elementor/dokan-store.js'), array('jquery','elementor-frontend'), $bookory_version, true);
            wp_register_script('bookory-elementor-image-gallery', get_theme_file_uri('/assets/js/elementor/image-gallery.js'), array('jquery','elementor-frontend'), $bookory_version, true);
            wp_register_script('bookory-elementor-posts-grid', get_theme_file_uri('/assets/js/elementor/posts-grid.js'), array('jquery','elementor-frontend'), $bookory_version, true);
            wp_register_script('bookory-elementor-product-categories', get_theme_file_uri('/assets/js/elementor/product-categories.js'), array('jquery','elementor-frontend'), $bookory_version, true);
            wp_register_script('bookory-elementor-product-tab', get_theme_file_uri('/assets/js/elementor/product-tab.js'), array('jquery','elementor-frontend'), $bookory_version, true);
            wp_register_script('bookory-elementor-products', get_theme_file_uri('/assets/js/elementor/products.js'), array('jquery','elementor-frontend'), $bookory_version, true);
            wp_register_script('bookory-elementor-tabs', get_theme_file_uri('/assets/js/elementor/tabs.js'), array('jquery','elementor-frontend'), $bookory_version, true);
            wp_register_script('bookory-elementor-testimonial', get_theme_file_uri('/assets/js/elementor/testimonial.js'), array('jquery','elementor-frontend'), $bookory_version, true);
            wp_register_script('bookory-elementor-video', get_theme_file_uri('/assets/js/elementor/video.js'), array('jquery','elementor-frontend'), $bookory_version, true);
           
        }

        public function add_category() {
            Elementor\Plugin::instance()->elements_manager->add_category(
                'bookory-addons',
                array(
                    'title' => esc_html__('Bookory Addons', 'bookory'),
                    'icon'  => 'fa fa-plug',
                ),
                1);
        }

        public function add_animations_scroll_style() {
            global $bookory_version;
            $animations =[
                'opal-move-up'    => 'Move Up',
                'opal-move-down'  => 'Move Down',
                'opal-move-left'  => 'Move Left',
                'opal-move-right' => 'Move Right',
                'opal-flip'       => 'Flip',
                'opal-helix'      => 'Helix',
                'opal-scale-up'   => 'Scale',
                'opal-am-popup'   => 'Popup',
            ];
            foreach ($animations as $animation => $name) {
                wp_deregister_style('e-animation-' . $animation);
                wp_register_style('e-animation-' . $animation, get_theme_file_uri('/assets/css/animations/' . $animation . '.css'), [], $bookory_version);
            }
        }

        public function add_animations_scroll($animations) {
            $animations['Bookory Animation'] = [
                'opal-move-up'    => 'Move Up',
                'opal-move-down'  => 'Move Down',
                'opal-move-left'  => 'Move Left',
                'opal-move-right' => 'Move Right',
                'opal-flip'       => 'Flip',
                'opal-helix'      => 'Helix',
                'opal-scale-up'   => 'Scale',
                'opal-am-popup'   => 'Popup',
            ];

            return $animations;
        }

        public function customs_widgets() {
            $files = glob(get_theme_file_path('/inc/elementor/custom-widgets/*.php'));
            foreach ($files as $file) {
                if (file_exists($file)) {
                    require_once $file;
                }
            }
        }

        /**
         * @param $widgets_manager Elementor\Widgets_Manager
         */
        public function include_widgets($widgets_manager) {
            $files = glob(get_theme_file_path('/inc/elementor/widgets/*.php'));
            foreach ($files as $file) {
                if (file_exists($file)) {
                    require_once $file;
                }
            }
        }

        public function woocommerce_fix_notice() {
            if (bookory_is_woocommerce_activated()) {
                remove_action('woocommerce_cart_is_empty', 'woocommerce_output_all_notices', 5);
                remove_action('woocommerce_shortcode_before_product_cat_loop', 'woocommerce_output_all_notices', 10);
                remove_action('woocommerce_before_shop_loop', 'woocommerce_output_all_notices', 10);
                remove_action('woocommerce_before_single_product', 'woocommerce_output_all_notices', 10);
                remove_action('woocommerce_before_cart', 'woocommerce_output_all_notices', 10);
                remove_action('woocommerce_before_checkout_form', 'woocommerce_output_all_notices', 10);
                remove_action('woocommerce_account_content', 'woocommerce_output_all_notices', 10);
                remove_action('woocommerce_before_customer_login_form', 'woocommerce_output_all_notices', 10);
            }
        }

        public function add_icons( $manager ) {
            $new_icons = json_decode( '{"bookory-icon-account":"account","bookory-icon-adventure":"adventure","bookory-icon-america":"america","bookory-icon-angle-down":"angle-down","bookory-icon-angle-left":"angle-left","bookory-icon-angle-right":"angle-right","bookory-icon-angle-up":"angle-up","bookory-icon-arrow-left":"arrow-left","bookory-icon-arrow-right":"arrow-right","bookory-icon-art":"art","bookory-icon-author":"author","bookory-icon-biographies":"biographies","bookory-icon-book":"book","bookory-icon-calendar":"calendar","bookory-icon-cart":"cart","bookory-icon-category":"category","bookory-icon-check-square-solid":"check-square-solid","bookory-icon-checked":"checked","bookory-icon-chevron-double-left":"chevron-double-left","bookory-icon-chevron-double-right":"chevron-double-right","bookory-icon-child-book":"child-book","bookory-icon-classics":"classics","bookory-icon-clock":"clock","bookory-icon-compare":"compare","bookory-icon-contemporary":"contemporary","bookory-icon-discount-1":"discount-1","bookory-icon-discount":"discount","bookory-icon-education":"education","bookory-icon-expert":"expert","bookory-icon-eye":"eye","bookory-icon-facebook-f":"facebook-f","bookory-icon-family":"family","bookory-icon-fantasy":"fantasy","bookory-icon-fiction":"fiction","bookory-icon-filter-ul":"filter-ul","bookory-icon-free-delivery":"free-delivery","bookory-icon-genre":"genre","bookory-icon-gift-1":"gift-1","bookory-icon-google-plus-g":"google-plus-g","bookory-icon-heart-1":"heart-1","bookory-icon-historical":"historical","bookory-icon-horror":"horror","bookory-icon-left-arrow":"left-arrow","bookory-icon-linkedin-in":"linkedin-in","bookory-icon-list-ul":"list-ul","bookory-icon-long-arrow-down":"long-arrow-down","bookory-icon-long-arrow-left":"long-arrow-left","bookory-icon-long-arrow-right":"long-arrow-right","bookory-icon-long-arrow-up":"long-arrow-up","bookory-icon-map-marker-alt":"map-marker-alt","bookory-icon-pen":"pen","bookory-icon-philosophy":"philosophy","bookory-icon-phone-2":"phone-2","bookory-icon-phone":"phone","bookory-icon-play":"play","bookory-icon-popular":"popular","bookory-icon-quick-view":"quick-view","bookory-icon-quote":"quote","bookory-icon-regilion":"regilion","bookory-icon-returned-order":"returned-order","bookory-icon-right-arrow-cicrle":"right-arrow-cicrle","bookory-icon-right-arrow":"right-arrow","bookory-icon-romance":"romance","bookory-icon-search-1":"search-1","bookory-icon-search-all":"search-all","bookory-icon-secure-payment":"secure-payment","bookory-icon-shipping-truck":"shipping-truck","bookory-icon-shopping-bag":"shopping-bag","bookory-icon-sliders-v":"sliders-v","bookory-icon-sold":"sold","bookory-icon-star-bestseller":"star-bestseller","bookory-icon-store-2":"store-2","bookory-icon-twitte-1":"twitte-1","bookory-icon-360":"360","bookory-icon-bars":"bars","bookory-icon-caret-down":"caret-down","bookory-icon-caret-left":"caret-left","bookory-icon-caret-right":"caret-right","bookory-icon-caret-up":"caret-up","bookory-icon-cart-empty":"cart-empty","bookory-icon-check-square":"check-square","bookory-icon-circle":"circle","bookory-icon-cloud-download-alt":"cloud-download-alt","bookory-icon-comment":"comment","bookory-icon-comments":"comments","bookory-icon-contact":"contact","bookory-icon-credit-card":"credit-card","bookory-icon-dot-circle":"dot-circle","bookory-icon-edit":"edit","bookory-icon-envelope":"envelope","bookory-icon-expand-alt":"expand-alt","bookory-icon-external-link-alt":"external-link-alt","bookory-icon-file-alt":"file-alt","bookory-icon-file-archive":"file-archive","bookory-icon-filter":"filter","bookory-icon-folder-open":"folder-open","bookory-icon-folder":"folder","bookory-icon-frown":"frown","bookory-icon-gift":"gift","bookory-icon-grid":"grid","bookory-icon-grip-horizontal":"grip-horizontal","bookory-icon-heart-fill":"heart-fill","bookory-icon-heart":"heart","bookory-icon-history":"history","bookory-icon-home":"home","bookory-icon-info-circle":"info-circle","bookory-icon-instagram":"instagram","bookory-icon-level-up-alt":"level-up-alt","bookory-icon-list":"list","bookory-icon-map-marker-check":"map-marker-check","bookory-icon-meh":"meh","bookory-icon-minus-circle":"minus-circle","bookory-icon-minus":"minus","bookory-icon-mobile-android-alt":"mobile-android-alt","bookory-icon-money-bill":"money-bill","bookory-icon-pencil-alt":"pencil-alt","bookory-icon-plus-circle":"plus-circle","bookory-icon-plus":"plus","bookory-icon-random":"random","bookory-icon-reply-all":"reply-all","bookory-icon-reply":"reply","bookory-icon-search-plus":"search-plus","bookory-icon-search":"search","bookory-icon-shield-check":"shield-check","bookory-icon-shopping-basket":"shopping-basket","bookory-icon-shopping-cart":"shopping-cart","bookory-icon-sign-out-alt":"sign-out-alt","bookory-icon-smile":"smile","bookory-icon-spinner":"spinner","bookory-icon-square":"square","bookory-icon-star":"star","bookory-icon-store":"store","bookory-icon-sync":"sync","bookory-icon-tachometer-alt":"tachometer-alt","bookory-icon-th-large":"th-large","bookory-icon-th-list":"th-list","bookory-icon-thumbtack":"thumbtack","bookory-icon-ticket":"ticket","bookory-icon-times-circle":"times-circle","bookory-icon-times":"times","bookory-icon-trophy-alt":"trophy-alt","bookory-icon-truck":"truck","bookory-icon-user-headset":"user-headset","bookory-icon-user-shield":"user-shield","bookory-icon-user":"user","bookory-icon-video":"video","bookory-icon-adobe":"adobe","bookory-icon-amazon":"amazon","bookory-icon-android":"android","bookory-icon-angular":"angular","bookory-icon-apper":"apper","bookory-icon-apple":"apple","bookory-icon-atlassian":"atlassian","bookory-icon-behance":"behance","bookory-icon-bitbucket":"bitbucket","bookory-icon-bitcoin":"bitcoin","bookory-icon-bity":"bity","bookory-icon-bluetooth":"bluetooth","bookory-icon-btc":"btc","bookory-icon-centos":"centos","bookory-icon-chrome":"chrome","bookory-icon-codepen":"codepen","bookory-icon-cpanel":"cpanel","bookory-icon-discord":"discord","bookory-icon-dochub":"dochub","bookory-icon-docker":"docker","bookory-icon-dribbble":"dribbble","bookory-icon-dropbox":"dropbox","bookory-icon-drupal":"drupal","bookory-icon-ebay":"ebay","bookory-icon-facebook":"facebook","bookory-icon-figma":"figma","bookory-icon-firefox":"firefox","bookory-icon-google-plus":"google-plus","bookory-icon-google":"google","bookory-icon-grunt":"grunt","bookory-icon-gulp":"gulp","bookory-icon-html5":"html5","bookory-icon-joomla":"joomla","bookory-icon-link-brand":"link-brand","bookory-icon-linkedin":"linkedin","bookory-icon-mailchimp":"mailchimp","bookory-icon-opencart":"opencart","bookory-icon-paypal":"paypal","bookory-icon-pinterest-p":"pinterest-p","bookory-icon-reddit":"reddit","bookory-icon-skype":"skype","bookory-icon-slack":"slack","bookory-icon-snapchat":"snapchat","bookory-icon-spotify":"spotify","bookory-icon-trello":"trello","bookory-icon-twitter":"twitter","bookory-icon-vimeo":"vimeo","bookory-icon-whatsapp":"whatsapp","bookory-icon-wordpress":"wordpress","bookory-icon-yoast":"yoast","bookory-icon-youtube":"youtube"}', true );
			$icons     = $manager->get_control( 'icon' )->get_settings( 'options' );
			$new_icons = array_merge(
				$new_icons,
				$icons
			);
			// Then we set a new list of icons as the options of the icon control
			$manager->get_control( 'icon' )->set_settings( 'options', $new_icons ); 
        }

        public function add_icons_native($tabs) {
            global $bookory_version;
            $tabs['opal-custom'] = [
                'name'          => 'bookory-icon',
                'label'         => esc_html__('Bookory Icon', 'bookory'),
                'prefix'        => 'bookory-icon-',
                'displayPrefix' => 'bookory-icon-',
                'labelIcon'     => 'fab fa-font-awesome-alt',
                'ver'           => $bookory_version,
                'fetchJson'     => get_theme_file_uri('/inc/elementor/icons.json'),
                'native'        => true,
            ];

            return $tabs;
        }
    }

endif;

return new Bookory_Elementor();
