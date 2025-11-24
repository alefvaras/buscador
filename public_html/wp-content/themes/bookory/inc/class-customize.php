<?php
if (!defined('ABSPATH')) {
    exit;
}
if (!class_exists('Bookory_Customize')) {

    class Bookory_Customize {


        public function __construct() {
            add_action('customize_register', array($this, 'customize_register'));
        }

        /**
         * @param $wp_customize WP_Customize_Manager
         */
        public function customize_register($wp_customize) {

            /**
             * Theme options.
             */
            require_once get_theme_file_path('inc/customize-control/editor.php');
            $this->init_bookory_blog($wp_customize);

            $this->init_bookory_social($wp_customize);

            if (bookory_is_woocommerce_activated()) {
                $this->init_woocommerce($wp_customize);
            }

            do_action('bookory_customize_register', $wp_customize);
        }


        /**
         * @param $wp_customize WP_Customize_Manager
         *
         * @return void
         */
        public function init_bookory_blog($wp_customize) {

            $wp_customize->add_section('bookory_blog_archive', array(
                'title' => esc_html__('Blog', 'bookory'),
            ));

            // =========================================
            // Select Style
            // =========================================

            $wp_customize->add_setting('bookory_options_blog_style', array(
                'type'              => 'option',
                'default'           => 'standard',
                'sanitize_callback' => 'sanitize_text_field',
            ));

            $wp_customize->add_control('bookory_options_blog_style', array(
                'section' => 'bookory_blog_archive',
                'label'   => esc_html__('Blog style', 'bookory'),
                'type'    => 'select',
                'choices' => array(
                    'standard' => esc_html__('Blog Standard', 'bookory'),
                    'style-1'  => esc_html__('Blog Grid', 'bookory'),
                ),
            ));

            $wp_customize->add_setting('bookory_options_blog_columns', array(
                'type'              => 'option',
                'default'           => 1,
                'sanitize_callback' => 'sanitize_text_field',
            ));

            $wp_customize->add_control('bookory_options_blog_columns', array(
                'section' => 'bookory_blog_archive',
                'label'   => esc_html__('Colunms', 'bookory'),
                'type'    => 'select',
                'choices' => array(
                    1 => esc_html__('1', 'bookory'),
                    2 => esc_html__('2', 'bookory'),
                    3 => esc_html__('3', 'bookory'),
                    4 => esc_html__('4', 'bookory'),
                ),
            ));
        }

        /**
         * @param $wp_customize WP_Customize_Manager
         *
         * @return void
         */
        public function init_bookory_social($wp_customize) {

            $wp_customize->add_section('bookory_social', array(
                'title' => esc_html__('Socials', 'bookory'),
            ));
            $wp_customize->add_setting('bookory_options_social_share', array(
                'type'              => 'option',
                'capability'        => 'edit_theme_options',
                'sanitize_callback' => 'sanitize_text_field',
            ));

            $wp_customize->add_control('bookory_options_social_share', array(
                'type'    => 'checkbox',
                'section' => 'bookory_social',
                'label'   => esc_html__('Show Social Share', 'bookory'),
            ));
            $wp_customize->add_setting('bookory_options_social_share_facebook', array(
                'type'              => 'option',
                'capability'        => 'edit_theme_options',
                'sanitize_callback' => 'sanitize_text_field',
            ));

            $wp_customize->add_control('bookory_options_social_share_facebook', array(
                'type'    => 'checkbox',
                'section' => 'bookory_social',
                'label'   => esc_html__('Share on Facebook', 'bookory'),
            ));
            $wp_customize->add_setting('bookory_options_social_share_twitter', array(
                'type'              => 'option',
                'capability'        => 'edit_theme_options',
                'sanitize_callback' => 'sanitize_text_field',
            ));

            $wp_customize->add_control('bookory_options_social_share_twitter', array(
                'type'    => 'checkbox',
                'section' => 'bookory_social',
                'label'   => esc_html__('Share on Twitter', 'bookory'),
            ));
            $wp_customize->add_setting('bookory_options_social_share_linkedin', array(
                'type'              => 'option',
                'capability'        => 'edit_theme_options',
                'sanitize_callback' => 'sanitize_text_field',
            ));

            $wp_customize->add_control('bookory_options_social_share_linkedin', array(
                'type'    => 'checkbox',
                'section' => 'bookory_social',
                'label'   => esc_html__('Share on Linkedin', 'bookory'),
            ));
            $wp_customize->add_setting('bookory_options_social_share_google-plus', array(
                'type'              => 'option',
                'capability'        => 'edit_theme_options',
                'sanitize_callback' => 'sanitize_text_field',
            ));

            $wp_customize->add_control('bookory_options_social_share_google-plus', array(
                'type'    => 'checkbox',
                'section' => 'bookory_social',
                'label'   => esc_html__('Share on Google+', 'bookory'),
            ));

            $wp_customize->add_setting('bookory_options_social_share_pinterest', array(
                'type'              => 'option',
                'capability'        => 'edit_theme_options',
                'sanitize_callback' => 'sanitize_text_field',
            ));

            $wp_customize->add_control('bookory_options_social_share_pinterest', array(
                'type'    => 'checkbox',
                'section' => 'bookory_social',
                'label'   => esc_html__('Share on Pinterest', 'bookory'),
            ));
            $wp_customize->add_setting('bookory_options_social_share_email', array(
                'type'              => 'option',
                'capability'        => 'edit_theme_options',
                'sanitize_callback' => 'sanitize_text_field',
            ));

            $wp_customize->add_control('bookory_options_social_share_email', array(
                'type'    => 'checkbox',
                'section' => 'bookory_social',
                'label'   => esc_html__('Share on Email', 'bookory'),
            ));
        }

        /**
         * @param $wp_customize WP_Customize_Manager
         *
         * @return void
         */
        public function init_woocommerce($wp_customize) {

            $wp_customize->add_panel('woocommerce', array(
                'title' => esc_html__('Woocommerce', 'bookory'),
            ));

            $wp_customize->add_section('bookory_woocommerce_archive', array(
                'title'      => esc_html__('Archive', 'bookory'),
                'capability' => 'edit_theme_options',
                'panel'      => 'woocommerce',
                'priority'   => 1,
            ));

            $wp_customize->add_setting('bookory_options_woocommerce_archive_layout', array(
                'type'              => 'option',
                'default'           => 'default',
                'sanitize_callback' => 'sanitize_text_field',
            ));

            $wp_customize->add_control('bookory_options_woocommerce_archive_layout', array(
                'section' => 'bookory_woocommerce_archive',
                'label'   => esc_html__('Layout Style', 'bookory'),
                'type'    => 'select',
                'choices' => array(
                    'default'   => esc_html__('Sidebar', 'bookory'),
                    'canvas'    => esc_html__('Canvas Filter', 'bookory'),
                    'dropdown'  => esc_html__('Dropdown Filter', 'bookory'),
                ),
            ));

            $wp_customize->add_setting('bookory_options_woocommerce_archive_sidebar', array(
                'type'              => 'option',
                'default'           => 'left',
                'sanitize_callback' => 'sanitize_text_field',
            ));

            $wp_customize->add_control('bookory_options_woocommerce_archive_sidebar', array(
                'section' => 'bookory_woocommerce_archive',
                'label'   => esc_html__('Sidebar Position', 'bookory'),
                'type'    => 'select',
                'choices' => array(
                    'left'  => esc_html__('Left', 'bookory'),
                    'right' => esc_html__('Right', 'bookory'),

                ),
            ));

            // =========================================
            // Single Product
            // =========================================

            $wp_customize->add_section('bookory_woocommerce_single', array(
                'title'      => esc_html__('Single Product', 'bookory'),
                'capability' => 'edit_theme_options',
                'panel'      => 'woocommerce',
            ));

            $wp_customize->add_setting('bookory_options_single_product_gallery_layout', array(
                'type'              => 'option',
                'default'           => 'horizontal',
                'transport'         => 'refresh',
                'sanitize_callback' => 'sanitize_text_field',
            ));
            $wp_customize->add_control('bookory_options_single_product_gallery_layout', array(
                'section' => 'bookory_woocommerce_single',
                'label'   => esc_html__('Style', 'bookory'),
                'type'    => 'select',
                'choices' => array(
                    'horizontal' => esc_html__('Horizontal', 'bookory'),
                    'vertical'   => esc_html__('Vertical', 'bookory'),
                    'gallery'    => esc_html__('Slider', 'bookory'),
                ),
            ));

            $wp_customize->add_setting('bookory_options_single_product_product_width', array(
                'type'              => 'option',
                'default'           => 'boxed',
                'sanitize_callback' => 'sanitize_text_field',
            ));

            $wp_customize->add_control('bookory_options_single_product_product_width', array(
                'section' => 'bookory_woocommerce_single',
                'label'   => esc_html__('Product Width', 'bookory'),
                'type'    => 'select',
                'choices' => array(
                    'boxed'  => esc_html__('Boxed', 'bookory'),
                    'full' => esc_html__('Full', 'bookory'),

                ),
            ));


            $wp_customize->add_setting('bookory_options_single_product_archive_sidebar', array(
                'type'              => 'option',
                'default'           => 'left',
                'sanitize_callback' => 'sanitize_text_field',
            ));
            $wp_customize->add_control('bookory_options_single_product_archive_sidebar', array(
                'section' => 'bookory_woocommerce_single',
                'label'   => esc_html__('Sidebar Position', 'bookory'),
                'type'    => 'select',
                'choices' => array(
                    'left'  => esc_html__('Left', 'bookory'),
                    'right' => esc_html__('Right', 'bookory'),

                ),
            ));


            // =========================================
            // Product
            // =========================================

            $wp_customize->add_section('bookory_woocommerce_product', array(
                'title'      => esc_html__('Product Block', 'bookory'),
                'capability' => 'edit_theme_options',
                'panel'      => 'woocommerce',
            ));

            $wp_customize->add_setting('bookory_options_wocommerce_block_style', array(
                'type'              => 'option',
                'default'           => '',
                'transport'         => 'refresh',
                'sanitize_callback' => 'sanitize_text_field',
            ));
            $wp_customize->add_control('bookory_options_wocommerce_block_style', array(
                'section' => 'bookory_woocommerce_product',
                'label'   => esc_html__('Style', 'bookory'),
                'type'    => 'select',
                'choices' => array(
                    ''  => esc_html__('Style 1', 'bookory'),
                ),
            ));

            $wp_customize->add_setting('bookory_options_woocommerce_product_hover', array(
                'type'              => 'option',
                'default'           => 'none',
                'transport'         => 'refresh',
                'sanitize_callback' => 'sanitize_text_field',
            ));
            $wp_customize->add_control('bookory_options_woocommerce_product_hover', array(
                'section' => 'bookory_woocommerce_product',
                'label'   => esc_html__('Animation Image Hover', 'bookory'),
                'type'    => 'select',
                'choices' => array(
                    'none'          => esc_html__('None', 'bookory'),
                    'bottom-to-top' => esc_html__('Bottom to Top', 'bookory'),
                    'top-to-bottom' => esc_html__('Top to Bottom', 'bookory'),
                    'right-to-left' => esc_html__('Right to Left', 'bookory'),
                    'left-to-right' => esc_html__('Left to Right', 'bookory'),
                    'swap'          => esc_html__('Swap', 'bookory'),
                    'fade'          => esc_html__('Fade', 'bookory'),
                    'zoom-in'       => esc_html__('Zoom In', 'bookory'),
                    'zoom-out'      => esc_html__('Zoom Out', 'bookory'),
                ),
            ));

            $wp_customize->add_setting('bookory_options_wocommerce_row_laptop', array(
                'type'              => 'option',
                'default'           => 3,
                'transport'         => 'postMessage',
                'sanitize_callback' => 'sanitize_text_field',
            ));

            $wp_customize->add_control('bookory_options_wocommerce_row_laptop', array(
                'section' => 'woocommerce_product_catalog',
                'label'   => esc_html__('Products per row Laptop', 'bookory'),
                'type'    => 'number',
            ));

            $wp_customize->add_setting('bookory_options_wocommerce_row_tablet', array(
                'type'              => 'option',
                'default'           => 2,
                'transport'         => 'postMessage',
                'sanitize_callback' => 'sanitize_text_field',
            ));

            $wp_customize->add_control('bookory_options_wocommerce_row_tablet', array(
                'section' => 'woocommerce_product_catalog',
                'label'   => esc_html__('Products per row tablet', 'bookory'),
                'type'    => 'number',
            ));

            $wp_customize->add_setting('bookory_options_wocommerce_row_mobile', array(
                'type'              => 'option',
                'default'           => 1,
                'transport'         => 'postMessage',
                'sanitize_callback' => 'sanitize_text_field',
            ));

            $wp_customize->add_control('bookory_options_wocommerce_row_mobile', array(
                'section' => 'woocommerce_product_catalog',
                'label'   => esc_html__('Products per row mobile', 'bookory'),
                'type'    => 'number',
            ));
        }
    }
}
return new Bookory_Customize();
