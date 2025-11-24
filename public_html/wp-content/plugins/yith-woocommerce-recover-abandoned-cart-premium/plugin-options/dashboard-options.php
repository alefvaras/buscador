<?php //phpcs:ignore WordPress.Files.FileName.InvalidClassFileName
/**
 * This file belongs to the YIT Plugin Framework.
 *
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE (GPL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.txt
 *
 * @package YITH WooCommerce Recover Abandoned Cart
 */


if ( ! defined( 'ABSPATH' ) || ! defined( 'YITH_YWRAC_VERSION' ) ) {
	exit; // Exit if accessed directly.
}

return array(
	'dashboard' => array(
		'dashboard-tabs' => array(
			'type'       => 'multi_tab',
			'nav-layout' => 'horizontal',
			'sub-tabs'   => array(
				'dashboard-reports' => array(
					'title'       => esc_html__( 'Dashboard', 'yith-woocommerce-recover-abandoned-cart' ),
					'description' => esc_html__( 'A main overview of your recover cart strategies', 'yith-woocommerce-recover-abandoned-cart' ),
				),
				'dashboard-carts'   => array(
					'title'       => esc_html__( 'Abandoned Carts', 'yith-woocommerce-recover-abandoned-cart' ),
					'icon'        => '<svg data-slot="icon" aria-hidden="true" fill="none" stroke-width="1.5" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 0 0-16.536-1.84M7.5 14.25 5.106 5.272M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z" stroke-linecap="round" stroke-linejoin="round"></path></svg>',
					'description' => esc_html__( 'Monitor your shop\'s abandoned carts and send recovery emails.', 'yith-woocommerce-recover-abandoned-cart' ),
				),
				'dashboard-pending_orders' => array(
					'title' => esc_html__( 'Pending Orders', 'yith-woocommerce-recover-abandoned-cart' ),
					'icon'  => '<svg data-slot="icon" aria-hidden="true" fill="none" stroke-width="1.5" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
					<path d="M8.625 12a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H8.25m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H12m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0h-.375M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" stroke-linecap="round" stroke-linejoin="round"></path>
				  </svg>',
					'description' => esc_html__( 'Monitor your shop\'s pending orders and send recovery emails.', 'yith-woocommerce-recover-abandoned-cart' ),
				),
				'dashboard-recovered'      => array(
					'title' => esc_html__( 'Recovered Carts', 'yith-woocommerce-recover-abandoned-cart' ),
					'icon'  => '<svg data-slot="icon" aria-hidden="true" fill="none" stroke-width="1.5" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
					<path d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" stroke-linecap="round" stroke-linejoin="round"></path>  </svg>',
					'description' => esc_html__( 'Here\'s a list of all carts that have been recovered. Yay!', 'yith-woocommerce-recover-abandoned-cart' ),
				),
			),
		),
	),
);
