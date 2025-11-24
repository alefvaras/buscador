<?php
/**
 * Plugin Options
 *
 * @since   1.0.0
 * @author  YITH <plugins@yithemes.com>
 * @package YITH WooCommerce Points and Rewards
 */

defined( 'ABSPATH' ) || exit;

$currency = get_woocommerce_currency();

$custom_tab = array(
	'points-standard' => array(


		'points_assignment_title' => array(
			'name' => esc_html__( 'Points assignments', 'yith-woocommerce-points-and-rewards' ),
			'type' => 'title',
			'id'   => 'points_assignment_title',
		),

		'enable_points_upon_sales' => array(
			'name'      => esc_html__( 'Assign points to users', 'yith-woocommerce-points-and-rewards' ),
			'desc'      => esc_html__(
				'Choose whether to award points to users automatically or manually',
				'yith-woocommerce-points-and-rewards'
			),
			'type'      => 'yith-field',
			'yith-type' => 'radio',
			'options'   => array(
				'yes' => esc_html__(
					'Automatically - Points will be assigned automatically for each purchase',
					'yith-woocommerce-points-and-rewards'
				),
				'no'  => esc_html__(
					'Manually - You can assign points manually in \'Customer Points\' tab',
					'yith-woocommerce-points-and-rewards'
				),
			),
			'default'   => 'yes',
			'id'        => 'ywpar_enable_points_upon_sales',
		),

		'user_role_enabled' => array(
			'name'              => esc_html__( 'Assign points to', 'yith-woocommerce-points-and-rewards' ),
			'desc'              => esc_html__(
				'Choose whether to assign points to all users or only to specified user roles',
				'yith-woocommerce-points-and-rewards'
			),
			'type'              => 'yith-field',
			'yith-type'         => 'radio',
			'id'                => 'ywpar_user_role_enabled_type',
			'options'           => apply_filters(
				'ywpar_user_role_type_options',
				array(
					'all'   => esc_html__( 'All users', 'yith-woocommerce-points-and-rewards' ),
					'roles' => esc_html__( 'Only specified user roles', 'yith-woocommerce-points-and-rewards' ),
				)
			),
			'default'           => 'all',
			'custom_attributes' => array(
				'data-deps'       => 'ywpar_enable_points_upon_sales',
				'data-deps_value' => 'yes',
			),
		),

		'user_roles_selected' => array(
			'name'              => esc_html__( 'Assign points to roles', 'yith-woocommerce-points-and-rewards' ),
			'desc'              => esc_html__(
				'Choose which user roles can collect points with their purchases',
				'yith-woocommerce-points-and-rewards'
			),
			'type'              => 'yith-field',
			'yith-type'         => 'select',
			'class'             => 'wc-enhanced-select',
			'css'               => 'min-width:300px',
			'multiple'          => true,
			'id'                => 'ywpar_user_role_enabled',
			'options'           => yith_ywpar_get_roles(),
			'deps'              => array(
				'id'    => 'ywpar_user_role_enabled_type',
				'value' => 'roles',
			),
			'custom_attributes' => array(
				'data-deps'       => 'ywpar_enable_points_upon_sales,ywpar_user_role_enabled_type',
				'data-deps_value' => 'yes,roles',
			),
		),

		'earn_points_conversion_rate' => array(
			'name'              => esc_html__( 'Default points assigned', 'yith-woocommerce-points-and-rewards' ),
			'desc'              => sprintf(
				                       esc_html__(
					                       'Set how many points per product will be earned based on the product value. You can override this value for specific products or users using the points rules.%sPlease, note: points are awarded on a product basis and not on the cart total',
					                       'yith-woocommerce-points-and-rewards'
				                       ),
				                       '<br>'
			                       ) . '<a name="earn-role-option"></a>',
			'yith-type'         => 'options-conversion-earning',
			'type'              => 'yith-field',
			'default'           => array(
				$currency => array(
					'points' => 1,
					'money'  => 10,
				),
			),
			'id'                => 'ywpar_earn_points_conversion_rate',
			'custom_attributes' => array(
				'data-deps'       => 'ywpar_enable_points_upon_sales',
				'data-deps_value' => 'yes',
			),
		),
		'earn_prices_calc'            => array(
			'name'              => esc_html__(
				'Calculate points considering:',
				'yith-woocommerce-points-and-rewards'
			),
			'desc'              => esc_html__(
				'Choose whether to calculate points considering unit prices or product subtotal.',
				'yith-woocommerce-points-and-rewards'
				) . '<br><br>' . esc_html__(
					'Example: a rule that gives 1 point for every €10 spent. If a customer adds 10 products priced at €8 each to their cart:',
					'yith-woocommerce-points-and-rewards'
				) . '<br>- ' . esc_html__(
					'Calculating points on the unit price won’t award any points.',
					'yith-woocommerce-points-and-rewards'
				) . '<br>- ' . esc_html__(
					'Calculating points on the subtotal will award 8 points.',
					'yith-woocommerce-points-and-rewards'
			),

			'type'              => 'yith-field',
			'yith-type'         => 'radio',
			'id'                => 'ywpar_earn_prices_calc',
			'options'           => array(
				'unit'     => esc_html__( 'Unit price', 'yith-woocommerce-points-and-rewards' ),
				'subtotal' => esc_html__( 'Product subtotal', 'yith-woocommerce-points-and-rewards' ),
			),
			'default'           => 'unit',
			'custom_attributes' => array(
				'data-deps'       => 'ywpar_enable_points_upon_sales',
				'data-deps_value' => 'yes',
			),
		),
		'earn_prices_tax'             => array(
			'name'              => esc_html__(
				'Calculate points considering product price with:',
				'yith-woocommerce-points-and-rewards'
			),
			'desc'              => esc_html__(
				'Choose whether to calculate points considering prices with taxes or without taxes',
				'yith-woocommerce-points-and-rewards'
			),
			'type'              => 'yith-field',
			'yith-type'         => 'radio',
			'id'                => 'ywpar_earn_prices_tax',
			'options'           => array(
				'incl' => esc_html__( 'taxes included', 'yith-woocommerce-points-and-rewards' ),
				'excl' => esc_html__( 'taxes excluded', 'yith-woocommerce-points-and-rewards' ),
			),
			'default'           => 'yes' === get_option( 'woocommerce_prices_include_tax' ) ? 'incl' : 'excl',
			'custom_attributes' => array(
				'data-deps'       => 'ywpar_enable_points_upon_sales',
				'data-deps_value' => 'yes',
			),
		),


		'assign_points_to_registered_guest' => array(
			'name'              => esc_html__(
				'Assign points to guests if the billing email is registered',
				'yith-woocommerce-points-and-rewards'
			),
			'desc'              => esc_html__(
				'Enable to assign points to guest users if the billing email matches a registered user account',
				'yith-woocommerce-points-and-rewards'
			),
			'yith-type'         => 'onoff',
			'type'              => 'yith-field',
			'id'                => 'ywpar_assign_points_to_registered_guest',
			'default'           => 'no',
			'custom_attributes' => array(
				'data-deps'       => 'ywpar_enable_points_upon_sales',
				'data-deps_value' => 'yes',
			),
		),

		'assign_older_orders_points_to_new_registered_user' => array(
			'name'              => esc_html__(
				'Assign points to newly registered users if the billing email is registered',
				'yith-woocommerce-points-and-rewards'
			),
			'desc'              => esc_html__(
				'Enable to assign points to newly registered users if they used the same billing email address for previous orders',
				'yith-woocommerce-points-and-rewards'
			),
			'yith-type'         => 'onoff',
			'type'              => 'yith-field',
			'id'                => 'ywpar_assign_older_orders_points_to_new_registered_user',
			'default'           => 'no',
			'custom_attributes' => array(
				'data-deps'       => 'ywpar_enable_points_upon_sales',
				'data-deps_value' => 'yes',
			),
		),

		'order_status_to_earn_points' => array(
			'name'              => esc_html__(
				'Assign points when the order has status',
				'yith-woocommerce-points-and-rewards'
			),
			'desc'              => esc_html__(
				'Choose based on which order status to assign points to users',
				'yith-woocommerce-points-and-rewards'
			),
			'type'              => 'yith-field',
			'yith-type'         => 'select',
			'class'             => 'wc-enhanced-select',
			'css'               => 'min-width:300px',
			'multiple'          => true,
			'id'                => 'ywpar_order_status_to_earn_points',
			'options'           => ywpar_get_order_status_to_earn_points(),
			'default'           => array(
				'woocommerce_order_status_completed',
				'woocommerce_payment_complete',
				'woocommerce_order_status_processing',
			),
			'custom_attributes' => array(
				'data-deps'       => 'ywpar_enable_points_upon_sales',
				'data-deps_value' => 'yes',
			),
		),


		'points_assignment_end' => array(
			'type' => 'sectionend',
			'id'   => 'points_assignment_end',
		),


		'points_removal_exclusion_title' => array(
			'name'  => esc_html__( 'Points removal and exclusion', 'yith-woocommerce-points-and-rewards' ),
			'type'  => 'title',
			'id'    => 'points_removal_exclusion_title',
			'class' => 'alessio',
			'deps'  => array(
				'id'    => 'ywpar_enable_points_upon_sales',
				'value' => 'yes',
				'type'  => 'hide',
			),
		),

		'exclude_product_on_sale' => array(
			'name'              => esc_html__(
				'Exclude on-sale products from points collection',
				'yith-woocommerce-points-and-rewards'
			),
			'desc'              => esc_html__(
				'If enabled, sale products will not assign points to your users',
				'yith-woocommerce-points-and-rewards'
			),
			'yith-type'         => 'onoff',
			'type'              => 'yith-field',
			'id'                => 'ywpar_exclude_product_on_sale',
			'default'           => 'no',
			'custom_attributes' => array(
				'data-deps'       => 'ywpar_enable_points_upon_sales',
				'data-deps_value' => 'yes',
			),
		),

		'remove_point_order_deleted' => array(
			'name'              => esc_html__(
				'Remove earned points if order is cancelled',
				'yith-woocommerce-points-and-rewards'
			),
			'desc'              => esc_html__(
				'Enable if you want to remove earned points when an order is cancelled',
				'yith-woocommerce-points-and-rewards'
			),
			'yith-type'         => 'onoff',
			'type'              => 'yith-field',
			'id'                => 'ywpar_remove_point_order_deleted',
			'default'           => 'yes',
			'custom_attributes' => array(
				'data-deps'       => 'ywpar_enable_points_upon_sales',
				'data-deps_value' => 'yes',
			),
		),

		'reassing_redeemed_points_refund_order' => array(
			'name'              => esc_html__(
				'Reassign points when an order is refunded',
				'yith-woocommerce-points-and-rewards'
			),
			'desc'              => esc_html__(
				'Enable if you want to reassign all the redeemed points to a customer when an order is refunded',
				'yith-woocommerce-points-and-rewards'
			),
			'yith-type'         => 'onoff',
			'type'              => 'yith-field',
			'id'                => 'ywpar_reassing_redeemed_points_refund_order',
			'default'           => 'no',
			'custom_attributes' => array(
				'data-deps'       => 'ywpar_enable_points_upon_sales',
				'data-deps_value' => 'yes',
			),
		),

		'remove_point_refund_order' => array(
			'name'              => esc_html__(
				'Remove earned points if order is refunded',
				'yith-woocommerce-points-and-rewards'
			),
			'desc'              => esc_html__(
				'Enable to remove points when applying a total or partial refund of the order',
				'yith-woocommerce-points-and-rewards'
			),
			'yith-type'         => 'onoff',
			'type'              => 'yith-field',
			'id'                => 'ywpar_remove_point_refund_order',
			'default'           => 'yes',
			'custom_attributes' => array(
				'data-deps'       => 'ywpar_enable_points_upon_sales',
				'data-deps_value' => 'yes',
			),
		),

		'remove_points_coupon' => array(
			'name'              => esc_html__(
				'Do not assign points to the full order amount if a coupon is used',
				'yith-woocommerce-points-and-rewards'
			),
			'desc'              => esc_html__(
				'Enable this option if you do not want users to earn points on a full order amount if they use a coupon. Instead, they will only earn points on the amount minus the coupon discount. For example, the order total is $30 minus a $10 coupon discount, so the user earns points only on a $20 order value',
				'yith-woocommerce-points-and-rewards'
			),
			'type'              => 'yith-field',
			'yith-type'         => 'onoff',
			'id'                => 'ywpar_remove_points_coupon',
			'default'           => 'yes',
			'custom_attributes' => array(
				'data-deps'       => 'ywpar_enable_points_upon_sales',
				'data-deps_value' => 'yes',
			),
		),

		'disable_point_earning_while_reedeming' => array(
			'name'              => esc_html__(
				'Do not assign points to orders in which the user is redeeming points',
				'yith-woocommerce-points-and-rewards'
			),
			'desc'              => esc_html__(
				'Enable to not assign points to orders in which the user redeems points',
				'yith-woocommerce-points-and-rewards'
			),
			'id'                => 'ywpar_disable_earning_while_reedeming',
			'type'              => 'yith-field',
			'yith-type'         => 'onoff',
			'default'           => 'no',
			'custom_attributes' => array(
				'data-deps'       => 'ywpar_enable_points_upon_sales',
				'data-deps_value' => 'yes',
			),
		),


		'points_removal_exclusion_end' => array(
			'type' => 'sectionend',
			'id'   => 'points_removal_exclusion_end',
		),


		'points_other_options' => array(
			'name' => esc_html__( 'Other options', 'yith-woocommerce-points-and-rewards' ),
			'type' => 'title',
			'id'   => 'points_other_options',
		),

		'enabled_shop_manager' => array(
			'name'      => esc_html__(
				'Allow shop manager to manage this plugin',
				'yith-woocommerce-points-and-rewards'
			),
			'desc'      => esc_html__(
				'Enable to allow your shop manager to access and manage the plugin settings',
				'yith-woocommerce-points-and-rewards'
			),
			'type'      => 'yith-field',
			'yith-type' => 'onoff',
			'default'   => 'no',
			'id'        => 'ywpar_enabled_shop_manager',
		),

		'round_points_down_up' => array(
			'name'              => esc_html__( 'Points rounding', 'yith-woocommerce-points-and-rewards' ),
			'desc'              => esc_html__(
				'Select how to round the points. For example, if points are 1.5 and Round Up is selected, points will be 2. If Round Down is selected, points will be 1',
				'yith-woocommerce-points-and-rewards'
			),
			'type'              => 'yith-field',
			'yith-type'         => 'radio',
			'id'                => 'ywpar_points_round_type',
			'options'           => array(
				'up'   => esc_html__( 'Round Up', 'yith-woocommerce-points-and-rewards' ),
				'down' => esc_html__( 'Round Down', 'yith-woocommerce-points-and-rewards' ),
			),
			'default'           => 'down',
			'custom_attributes' => array(
				'data-deps'       => 'ywpar_enable_points_upon_sales',
				'data-deps_value' => 'yes',
			),
		),

		'enable_expiration_point' => array(
			'name'              => esc_html__( 'Set an expiration time for points', 'yith-woocommerce-points-and-rewards' ),
			'desc'              => esc_html__(
				'Enable if you want to set an expiration time on points assigned to users',
				'yith-woocommerce-points-and-rewards'
			),
			'type'              => 'yith-field',
			'yith-type'         => 'onoff',
			'default'           => 'no',
			'id'                => 'ywpar_enable_expiration_point',
			'custom_attributes' => array(
				'data-deps'       => 'ywpar_enable_points_upon_sales',
				'data-deps_value' => 'yes',
			),
		),

		'days_before_expiration' => array(
			'name'              => esc_html__( 'Points will expire after', 'yith-woocommerce-points-and-rewards' ),
			'desc'              => esc_html__(
				'Set a default expiration on points earned in your shop',
				'yith-woocommerce-points-and-rewards'
			),
			'type'              => 'yith-field',
			'yith-type'         => 'options-expire',
			'default'           => array(
				'number' => 0,
				'time'   => 'days',
			),
			'id'                => 'ywpar_days_before_expiration',
			'deps'              => array(
				'id'    => 'enable_expiration_point',
				'value' => 'yes',
			),
			'custom_attributes' => array(
				'data-deps'       => 'ywpar_enable_points_upon_sales,ywpar_enable_expiration_point',
				'data-deps_value' => 'yes,yes',
			),
		),

		'points_other_options_end' => array(
			'type' => 'sectionend',
			'id'   => 'points_other_options_end',
		),

	),
);

return apply_filters( 'ywpar_points_settings', $custom_tab );
