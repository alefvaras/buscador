<?php

if (!defined('ABSPATH')) {
    exit;
}

class Bookory_Elementor_Fix_Update {
    public $widgets
        = [
            'woocommerce-breadcrumb' => 'Bookory_Elementor_Breadcrumb'
        ];

    public function __construct() {
        if (version_compare(ELEMENTOR_VERSION, '1.8.0', '>=')) {
            add_action('elementor/init', [$this, 'on_elementor_init']);
        }
    }

    public function on_elementor_init() {
        add_filter('elementor/editor/localize_settings', function ($client_env) {
            $widgets = $this->widgets;

            foreach ($widgets as $key => $widget) {
                $client_env['initial_document']['widgets'][$key]['show_in_panel']  = true;
                $client_env['initial_document']['widgets'][$key]['hide_on_search'] = false;
                $item                                                              = array_search($key, array_column($client_env['promotionWidgets'], 'name'));
                unset($client_env['promotionWidgets'][$item]);
            }

            return $client_env;
        });

        add_action('elementor/widgets/register', function ($widgets_manager) {
            $widgets = $this->widgets;

            foreach ($widgets as $key => $widget) {
                $widgets_manager->unregister($key);
                $widgets_manager->register(new $widget());
            }

        }, 99);

    }
}

return new Bookory_Elementor_Fix_Update();
