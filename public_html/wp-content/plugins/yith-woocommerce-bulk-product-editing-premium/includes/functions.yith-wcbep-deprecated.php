<?php
/**
 * Deprecated Functions
 *
 * @author  YITH <plugins@yithemes.com>
 * @package YITH\BulkProductEditing
 */

defined( 'YITH_WCBEP' ) || exit(); // Exit if accessed directly.

if ( ! function_exists( 'yith_wcbep_get_template' ) ) {
	/**
	 * Print a template
	 *
	 * @param string $template The template.
	 * @param array  $args     Arguments.
	 *
	 * @deprecated 1.2.36
	 */
	function yith_wcbep_get_template( $template, $args = array() ) {
		wc_deprecated_function( 'yith_wcbep_get_template', '1.2.36' );
		extract( $args ); // phpcs:ignore WordPress.PHP.DontExtract.extract_extract
		include YITH_WCBEP_TEMPLATE_PATH . $template;
	}
}

if ( ! function_exists( 'yith_wcbep_strContains' ) ) {
	/**
	 * Check if string contains another string.
	 *
	 * @param string $haystack The haystack.
	 * @param string $needle   The needle.
	 *
	 * @return bool
	 * @deprecated 1.2.36
	 */
	function yith_wcbep_strContains( $haystack, $needle ) {
		wc_deprecated_function( 'yith_wcbep_strContains', '1.2.36' );

		return stripos( $haystack, $needle ) !== false;
	}
}

if ( ! function_exists( 'yith_wcbep_strStartsWith' ) ) {
	/**
	 * Check if string starts with another string.
	 *
	 * @param string $haystack The haystack.
	 * @param string $needle   The needle.
	 *
	 * @return bool
	 * @deprecated 1.2.36
	 */
	function yith_wcbep_strStartsWith( $haystack, $needle ) {
		wc_deprecated_function( 'yith_wcbep_strStartsWith', '1.2.36' );

		return '' === $needle || false !== stripos( $haystack, $needle, - strlen( $haystack ) );
	}
}

if ( ! function_exists( 'yith_wcbep_strEndsWith' ) ) {
	/**
	 * Check if string ends with another string.
	 *
	 * @param string $haystack The haystack.
	 * @param string $needle   The needle.
	 *
	 * @return bool
	 * @deprecated 1.2.36
	 */
	function yith_wcbep_strEndsWith( $haystack, $needle ) {
		wc_deprecated_function( 'yith_wcbep_strEndsWith', '1.2.36' );
		if ( '' === $needle ) {
			return true;
		} else {
			$temp = strlen( $haystack ) - strlen( $needle );

			return $temp >= 0 && stripos( $haystack, $needle, $temp ) !== false;
		}
	}
}
