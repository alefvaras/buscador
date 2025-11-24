<?php
/**
 * Plugin Options
 *
 * @since   1.0.0
 * @author  YITH <plugins@yithemes.com>
 * @package YITH WooCommerce Points and Rewards
 */

defined( 'ABSPATH' ) || exit;

return array(
	'customers' => array(
		'customers-tab' => array(
			'type'           => 'custom_tab',
			'action'         => 'yith_ywpar_customers',
			'show_container' => true,
			'title'          => esc_html__( 'Customer Points', 'yith-woocommerce-points-and-rewards' ),
			'description'    => esc_html__( 'Monitor and manage all points collected from your customers.', 'yith-woocommerce-points-and-rewards' ),
		),
	),
);
