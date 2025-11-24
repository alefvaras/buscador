<?php
/**
 * Plugin Name: YITH WooCommerce Bulk Product Editing Premium
 * Plugin URI: https://yithemes.com/themes/plugins/yith-woocommerce-bulk-product-editing/
 * Description: <code><strong>YITH WooCommerce Bulk Product Editing</strong></code> allows you to edit multiple products at the same time. You can easily filter products and edit all fields you need in a massive, simple and fast way. <a href="https://yithemes.com/" target="_blank">Get more plugins for your e-commerce shop on <strong>YITH</strong></a>.
 * Version: 3.21.0
 * Author: YITH
 * Author URI: https://yithemes.com/
 * Text Domain: yith-woocommerce-bulk-product-editing
 * Domain Path: /languages/
 * WC requires at least: 10.0
 * WC tested up to: 10.2
 * Requires Plugins: woocommerce
 *
 * @author  YITH <plugins@yithemes.com>
 * @package YITH\BulkProductEditing
 * @version 3.21.0
 */

defined( 'ABSPATH' ) || exit(); // Exit if accessed directly.

if ( ! function_exists( 'is_plugin_active' ) ) {
	require_once ABSPATH . 'wp-admin/includes/plugin.php';
}

if ( ! function_exists( 'yit_deactive_free_version' ) ) {
	require_once 'plugin-fw/yit-deactive-plugin.php';
}
yit_deactive_free_version( 'YITH_WCBEP_FREE_INIT', plugin_basename( __FILE__ ) );

! defined( 'YITH_WCBEP_VERSION' ) && define( 'YITH_WCBEP_VERSION', '3.21.0' );

! defined( 'YITH_WCBEP_PREMIUM' ) && define( 'YITH_WCBEP_PREMIUM', '1' );

! defined( 'YITH_WCBEP_INIT' ) && define( 'YITH_WCBEP_INIT', plugin_basename( __FILE__ ) );

! defined( 'YITH_WCBEP' ) && define( 'YITH_WCBEP', true );

! defined( 'YITH_WCBEP_FILE' ) && define( 'YITH_WCBEP_FILE', __FILE__ );

! defined( 'YITH_WCBEP_URL' ) && define( 'YITH_WCBEP_URL', plugin_dir_url( __FILE__ ) );

! defined( 'YITH_WCBEP_DIR' ) && define( 'YITH_WCBEP_DIR', plugin_dir_path( __FILE__ ) );

! defined( 'YITH_WCBEP_TEMPLATE_PATH' ) && define( 'YITH_WCBEP_TEMPLATE_PATH', YITH_WCBEP_DIR . 'templates/' );

! defined( 'YITH_WCBEP_VIEWS_PATH' ) && define( 'YITH_WCBEP_VIEWS_PATH', YITH_WCBEP_DIR . 'views/' );

! defined( 'YITH_WCBEP_ASSETS_URL' ) && define( 'YITH_WCBEP_ASSETS_URL', YITH_WCBEP_URL . 'assets/' );

! defined( 'YITH_WCBEP_ASSETS_PATH' ) && define( 'YITH_WCBEP_ASSETS_PATH', YITH_WCBEP_DIR . 'assets/' );

! defined( 'YITH_WCBEP_INCLUDES_PATH' ) && define( 'YITH_WCBEP_INCLUDES_PATH', YITH_WCBEP_DIR . 'includes/' );

! defined( 'YITH_WCBEP_COMPATIBILITY_PATH' ) && define( 'YITH_WCBEP_COMPATIBILITY_PATH', YITH_WCBEP_INCLUDES_PATH . 'compatibility/' );

! defined( 'YITH_WCBEP_SLUG' ) && define( 'YITH_WCBEP_SLUG', 'yith-woocommerce-bulk-product-editing' );

! defined( 'YITH_WCBEP_SECRET_KEY' ) && define( 'YITH_WCBEP_SECRET_KEY', '' );

! defined( 'YITH_WCBEP_PLUGIN_NAME' ) && define( 'YITH_WCBEP_PLUGIN_NAME', 'YITH WooCommerce Bulk Product Editing' );

/**
 * Print admin notice if WooCommerce is not enabled
 */
function yith_wcbep_pr_install_woocommerce_admin_notice() {
	?>
	<div class="error">
		<p>
			<?php
			// translators: %s is the plugin name.
			echo esc_html( sprintf( __( '%s is enabled but not effective. It requires WooCommerce in order to work.', 'yith-woocommerce-bulk-product-editing' ), YITH_WCBEP_PLUGIN_NAME ) );
			?>
		</p>
	</div>
	<?php
}

if ( ! function_exists( 'yith_plugin_registration_hook' ) ) {
	require_once 'plugin-fw/yit-plugin-registration-hook.php';
}
register_activation_hook( __FILE__, 'yith_plugin_registration_hook' );

if ( ! function_exists( 'yith_plugin_onboarding_registration_hook' ) ) {
	include_once 'plugin-upgrade/functions-yith-licence.php';
}
register_activation_hook( __FILE__, 'yith_plugin_onboarding_registration_hook' );

/**
 * Plugin init
 */
function yith_wcbep_pr_init() {

	if ( function_exists( 'yith_plugin_fw_load_plugin_textdomain' ) ) {
		yith_plugin_fw_load_plugin_textdomain( 'yith-woocommerce-bulk-product-editing', dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}

	require_once __DIR__ . '/includes/class.yith-wcbep-list-table.php';
	require_once __DIR__ . '/includes/class-yith-wcbep-table-views.php';
	require_once __DIR__ . '/includes/class-yith-wcbep-bulk-actions.php';
	require_once __DIR__ . '/includes/class.yith-wcbep-custom-fields-manager.php';
	require_once __DIR__ . '/includes/class.yith-wcbep-custom-taxonomies-manager.php';
	require_once __DIR__ . '/includes/compatibility/class.yith-wcbep-compatibility.php';
	require_once __DIR__ . '/includes/functions.yith-wcbep-deprecated.php';
	require_once __DIR__ . '/includes/class-yith-wcbep-install.php';
	require_once __DIR__ . '/includes/class.yith-wcbep-admin.php';
	require_once __DIR__ . '/includes/functions.yith-wcbep.php';
	require_once __DIR__ . '/includes/class.yith-wcbep.php';

	// Let's start the game!
	yith_wcbep();
}

add_action( 'yith_wcbep_pr_init', 'yith_wcbep_pr_init' );

/**
 * Install
 */
function yith_wcbep_pr_install() {
	if ( ! function_exists( 'WC' ) ) {
		add_action( 'admin_notices', 'yith_wcbep_pr_install_woocommerce_admin_notice' );
	} else {
		do_action( 'yith_wcbep_pr_init' );
	}
}

add_action( 'plugins_loaded', 'yith_wcbep_pr_install', 11 );

// Plugin Framework Loader.
if ( file_exists( plugin_dir_path( __FILE__ ) . 'plugin-fw/init.php' ) ) {
	require_once plugin_dir_path( __FILE__ ) . 'plugin-fw/init.php';
}
