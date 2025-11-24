<?php
/**
 * Badge floating editing field.
 *
 * @package YITH\BulkProductEditing\Compatibility\BadgeManagement
 * @author  YITH <plugins@yithemes.com>
 * @since   2.0.0
 */

$input = array(
	'class'    => 'yith-wcbep-floating-editing-field__badges yith-post-search',
	'type'     => 'ajax-posts',
	'data'     => array(
		'post_type'   => class_exists( 'YITH_WCBM_Post_Types' ) ? YITH_WCBM_Post_Types::$badge : 'yith-wcbm-badge',
		'placeholder' => __( 'Search for a badge...', 'yith-woocommerce-bulk-product-editing' ),
	),
	'multiple' => true,
);

yith_plugin_fw_get_field( $input, true, false );
