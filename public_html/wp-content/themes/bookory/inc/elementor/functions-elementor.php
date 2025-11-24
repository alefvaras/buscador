<?php
if ( ! function_exists( 'bookory_elementor_get_render_attribute_string' ) ) {
	function bookory_elementor_get_render_attribute_string($element, $obj) {
		return $obj->get_render_attribute_string($element);
	}
}
if ( ! function_exists( 'bookory_elementor_parse_text_editor' ) ) {
	function bookory_elementor_parse_text_editor( $content, $obj ) {
		$content = apply_filters( 'widget_text', $content, $obj->get_settings() );

		$content = shortcode_unautop( $content );
		$content = do_shortcode( $content );
		$content = wptexturize( $content );

		if ( $GLOBALS['wp_embed'] instanceof \WP_Embed ) {
			$content = $GLOBALS['wp_embed']->autoembed( $content );
		}

		return $content;
	}
}

if ( ! function_exists( 'bookory_elementor_get_strftime' ) ) {
	function bookory_elementor_get_strftime( $instance, $obj ) {
		$string = '';
		if ( $instance['show_days'] ) {
			$string .= $obj->render_countdown_item( $instance, 'label_days', 'days', 'elementor-countdown-days' );
		}
		if ( $instance['show_hours'] ) {
			$string .= $obj->render_countdown_item( $instance, 'label_hours', 'hours', 'elementor-countdown-hours' );
		}
		if ( $instance['show_minutes'] ) {
			$string .= $obj->render_countdown_item( $instance, 'label_minutes', 'minutes', 'elementor-countdown-minutes' );
		}
		if ( $instance['show_seconds'] ) {
			$string .= $obj->render_countdown_item( $instance, 'label_seconds', 'seconds', 'elementor-countdown-seconds' );
		}

		return $string;
	}
}

if (!function_exists('bookory_elementor_breakpoints')) {
    function bookory_elementor_breakpoints() {

        $breakpoints = \Elementor\Plugin::$instance->breakpoints->get_breakpoints();
        $var ='';
        $check = bookory_is_woocommerce_activated();
        foreach (array_reverse($breakpoints) as $breakpoint) {
            if ($breakpoint->is_enabled()) {
                $var .='@media('.$breakpoint->get_direction().'-width:'.$breakpoint->get_value().'px){';
                $device_name = str_replace('_','-',$breakpoint->get_name());
                for ($i = 1; $i <= 8; $i++) {
                    $ratio = round((12/$i)/12*100,10);
                    $var .= 'body.theme-bookory [data-elementor-columns-'.$device_name.'="'.$i.'"] .column-item{flex: 0 0 '.$ratio.'%; max-width: '.$ratio.'%;}';
                    if($check){
                        $var .= '.woocommerce.columns-'.$device_name.'-'.$i.' ul.products li.product{flex: 0 0 '.$ratio.'%; max-width: '.$ratio.'%;}';
                    }
                }
                $var .='}';
            }
        }
        wp_add_inline_style('bookory-style', $var);
    }
}