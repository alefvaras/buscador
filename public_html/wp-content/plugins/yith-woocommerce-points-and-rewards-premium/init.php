<?php
/**
 * Plugin Name: YITH WooCommerce Points and Rewards Premium
 * Plugin URI: https://yithemes.com/themes/plugins/yith-woocommerce-points-and-rewards/
 * Description: With <code><strong>YITH WooCommerce Points and Rewards</strong></code> you can start a rewards program with points to gain your customers' loyalty. Your customers will be able to use their points to get discounts making it a perfect marketing strategy for your store. <a href="https://yithemes.com/" target="_blank">Get more plugins for your e-commerce shop on <strong>YITH</strong></a>.
 * Version: 4.18.0
 * Author: YITH
 * Author URI: https://yithemes.com/
 * Text Domain: yith-woocommerce-points-and-rewards
 * Domain Path: /languages/
 * WC requires at least: 9.78
 * WC tested up to: 10.0
 * Requires Plugins: woocommerce
 **/

/*
 * @package YITH WooCommerce Points and Rewards
 * @since   1.0.0
 * @author  YITH <plugins@yithemes.com>
 */

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'is_plugin_active' ) ) {
	require_once ABSPATH . 'wp-admin/includes/plugin.php';
}

! defined( 'YITH_YWPAR_DIR' ) && define( 'YITH_YWPAR_DIR', plugin_dir_path( __FILE__ ) );

if ( ! function_exists( 'yith_deactivate_plugins' ) ) {
	require_once 'plugin-fw/yit-deactive-plugin.php';
}
yith_deactivate_plugins( 'YITH_YWPAR_FREE_INIT', plugin_basename( __FILE__ ) );

// Plugin Framework Loader.
if ( file_exists( plugin_dir_path( __FILE__ ) . 'plugin-fw/init.php' ) ) {
	require_once plugin_dir_path( __FILE__ ) . 'plugin-fw/init.php';
}

// Define constants ________________________________________.
! defined( 'YITH_YWPAR_VERSION' ) && define( 'YITH_YWPAR_VERSION', '4.18.0' );
! defined( 'YITH_YWPAR_PREMIUM' ) && define( 'YITH_YWPAR_PREMIUM', plugin_basename( __FILE__ ) );
! defined( 'YITH_YWPAR_INIT' ) && define( 'YITH_YWPAR_INIT', plugin_basename( __FILE__ ) );
! defined( 'YITH_YWPAR_FILE' ) && define( 'YITH_YWPAR_FILE', __FILE__ );
! defined( 'YITH_YWPAR_URL' ) && define( 'YITH_YWPAR_URL', plugins_url( '/', __FILE__ ) );
! defined( 'YITH_YWPAR_ASSETS_URL' ) && define( 'YITH_YWPAR_ASSETS_URL', YITH_YWPAR_URL . 'assets' );
! defined( 'YITH_YWPAR_TEMPLATE_PATH' ) && define( 'YITH_YWPAR_TEMPLATE_PATH', YITH_YWPAR_DIR . 'templates' );
! defined( 'YITH_YWPAR_VIEWS_PATH' ) && define( 'YITH_YWPAR_VIEWS_PATH', YITH_YWPAR_DIR . 'views' );
! defined( 'YITH_YWPAR_INC' ) && define( 'YITH_YWPAR_INC', YITH_YWPAR_DIR . 'includes/' );
! defined( 'YITH_YWPAR_SLUG' ) && define( 'YITH_YWPAR_SLUG', 'yith-woocommerce-points-and-rewards' );
! defined( 'YITH_YWPAR_SECRET_KEY' ) && define( 'YITH_YWPAR_SECRET_KEY', '' );

if ( ! defined( 'YITH_YWPAR_SUFFIX' ) ) {
	$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
	define( 'YITH_YWPAR_SUFFIX', $suffix );
}

if ( ! function_exists( 'yith_ywpar_install_woocommerce_admin_notice' ) ) {
	/**
	 * Admin notice when WooCommerce isn't installed.
	 */
	function yith_ywpar_install_woocommerce_admin_notice() {
		?>
		<div class="error">
			<p><?php esc_html_e( 'YITH WooCommerce Points and Rewards is enabled but not effective. It requires WooCommerce in order to work.', 'yith-woocommerce-points-and-rewards' ); ?></p>
		</div>
		<?php
	}
}



if ( ! function_exists( 'yith_ywpar_premium_install' ) ) {
	/**
	 * Check if create the points db table.
	 */
	function yith_ywpar_premium_install() {
		// Woocommerce installation check _________________________.
		if ( ! function_exists( 'WC' ) ) {
			add_action( 'admin_notices', 'yith_ywpar_install_woocommerce_admin_notice' );
			return;
		}

		add_action( 'before_woocommerce_init', 'ywpar_add_support_hpos_system' );
		/**
		 * DO_ACTION: yith_ywpar_init
		 *
		 * hook on init the plugin.
		 */
		do_action( 'yith_ywpar_init' );

		// check for update table.
		require_once YITH_YWPAR_INC . 'functions-yith-wc-points-rewards-updates.php';
		yith_ywpar_update_db_check();

	}
}

add_action( 'plugins_loaded', 'yith_ywpar_premium_install', 11 );

if ( ! function_exists( 'ywpar_add_support_hpos_system' ) ) {
    function ywpar_add_support_hpos_system() {
	    if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
		    \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', YITH_YWPAR_INIT );
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'cart_checkout_blocks', YITH_YWPAR_INIT );
	    }
    }
}

if ( ! function_exists( 'yith_plugin_onboarding_registration_hook' ) ) {
	include_once 'plugin-upgrade/functions-yith-licence.php';
}
register_activation_hook( __FILE__, 'yith_plugin_onboarding_registration_hook' );


if ( ! function_exists( 'yith_ywpar_premium_constructor' ) ) {
	/**
	 * Start the game
	 */
	function yith_ywpar_premium_constructor() {
		// Load ywpar text domain ___________________________________.
		if ( function_exists( 'yith_plugin_fw_load_plugin_textdomain' ) ) {
			yith_plugin_fw_load_plugin_textdomain( 'yith-woocommerce-points-and-rewards', basename( dirname( __FILE__ ) ) . '/languages' );
		}


		if ( ! class_exists( 'WP_List_Table' ) ) {
			require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
		}
		require_once YITH_YWPAR_INC . 'class-yith-wc-points-rewards.php';

		yith_points();
	}
}
add_action( 'yith_ywpar_init', 'yith_ywpar_premium_constructor' );


if ( ! function_exists( 'ywpar_remove_cron_scheduled' ) ) {
	/**
	 * Remove CRON schedule when the plugin will be deactivated.
	 */
	function ywpar_remove_cron_scheduled() {
		wp_clear_scheduled_hook( 'ywpar_cron' );
		wp_clear_scheduled_hook( 'ywpar_cron_birthday' );
	}
}

/**
 * Remove Cron Scheduled Events
 */
register_deactivation_hook( __FILE__, 'ywpar_remove_cron_scheduled' );

/* WC checkout blocks integration */
include_once YITH_YWPAR_INC . 'blocks/checkout-blocks/class-ywpar-checkout-blocks.php';
YITH_WC_Checkout_Blocks();
