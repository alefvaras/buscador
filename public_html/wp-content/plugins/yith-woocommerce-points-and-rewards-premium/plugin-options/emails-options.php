<?php
/**
 * Email Options
 *
 * @since   1.0.0
 * @author  YITH <plugins@yithemes.com>
 * @package YITH WooCommerce Points and Rewards
 */

defined( 'ABSPATH' ) || exit;

return array(
	'emails' => array(
		'emails-tab' => array(
			'type'           => 'custom_tab',
			'action'         => 'ywpar_custom_emails_tab',
			'show_container' => true,
			'title'          => esc_html__( 'Emails', 'yith-woocommerce-points-and-rewards' ),
		),
	),
);