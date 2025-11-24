<?php

if (!defined('ABSPATH')) {
    exit;
}


class Bookory_Header_Footer_Elementor {
    public function __construct() {
        add_action('wp', [$this, 'hooks'], 99999);
    }

    public function hooks() {
        if (hfe_header_enabled()) {
            // Replace header.php template.
            remove_all_actions('get_header');
            add_action('get_header', [$this, 'override_header']);
        }

        if (hfe_footer_enabled() || hfe_is_before_footer_enabled()) {
            // Replace footer.php template.
            remove_all_actions('get_footer');
            add_action('get_footer', [$this, 'override_footer']);
        }
    }

    /**
     * Function for overriding the header in the elmentor way.
     *
     * @return void
     * @since 1.2.0
     *
     */
    public function override_header() {
        require get_theme_file_path('header.php');
        $templates   = [];
        $templates[] = 'header.php';
        // Avoid running wp_head hooks again.
        remove_all_actions('wp_head');
        ob_start();
        locate_template($templates, true);
        ob_get_clean();
    }

    /**
     * Function for overriding the footer in the elmentor way.
     *
     * @return void
     * @since 1.2.0
     *
     */
    public function override_footer() {
        require get_theme_file_path('footer.php');
        $templates   = [];
        $templates[] = 'footer.php';
        // Avoid running wp_footer hooks again.
        remove_all_actions('wp_footer');
        ob_start();
        locate_template($templates, true);
        ob_get_clean();
    }
}

new Bookory_Header_Footer_Elementor();
