<?php
/**
 * Plugin Name: Test AJAX Intercept
 * Description: Intercepta TODAS las peticiones AJAX
 * Version: 1.0
 */

// Interceptar CUALQUIER petición AJAX
add_action('admin_init', function() {
    if (defined('DOING_AJAX') && DOING_AJAX) {
        $action = isset($_REQUEST['action']) ? $_REQUEST['action'] : 'NO_ACTION';
        $log_file = WP_CONTENT_DIR . '/ajax-debug.log';
        $time = date('Y-m-d H:i:s');
        $data = json_encode($_REQUEST);
        file_put_contents($log_file, "[$time] Action: $action | Data: $data\n", FILE_APPEND);
    }
});

// También en init por si acaso
add_action('init', function() {
    if (defined('DOING_AJAX') && DOING_AJAX) {
        $action = isset($_REQUEST['action']) ? $_REQUEST['action'] : 'NO_ACTION';
        $log_file = WP_CONTENT_DIR . '/ajax-debug.log';
        $time = date('Y-m-d H:i:s');
        file_put_contents($log_file, "[$time] [INIT] Action detectada: $action\n", FILE_APPEND);
    }
});