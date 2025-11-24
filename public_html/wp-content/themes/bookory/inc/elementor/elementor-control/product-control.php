<?php

/**
 * Producta_Control control.
 *
 */
class Products_Control extends \Elementor\Control_Select2 {

    public function get_type() {
        return 'products';
    }

    public function enqueue() {
		global $bookory_version;
        wp_register_script('elementor-products-control', get_theme_file_uri('/inc/elementor/elementor-control/select2.js'), ['jquery'], $bookory_version, true);
        wp_enqueue_script('elementor-products-control');
    }
}
