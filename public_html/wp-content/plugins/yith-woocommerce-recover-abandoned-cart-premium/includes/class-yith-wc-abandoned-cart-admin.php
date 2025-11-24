<?php /*//phpcs:ignore WordPress.Files.FileName.InvalidClassFileName

/ **
 * This file belongs to the YIT Plugin Framework.
 *
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE (GPL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.txt
 *
 * @package YITH WooCommerce Recover Abandoned Cart
*/


use Automattic\WooCommerce\Utilities\OrderUtil;

if ( ! defined( 'ABSPATH' ) || ! defined( 'YITH_YWRAC_VERSION' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Implements admin features of YITH_WC_Recover_Abandoned_Cart_Admin
 *
 * @class   YITH_WC_Recover_Abandoned_Cart_Admin
 * @package YITH WooCommerce Recover Abandoned Cart
 * @since   1.0.0
 * @author  YITH <plugins@yithemes.com>
 */
if ( ! class_exists( 'YITH_WC_Recover_Abandoned_Cart_Admin' ) ) {
	/**
	 * Class YITH_WC_Recover_Abandoned_Cart_Admin
	 */
	class YITH_WC_Recover_Abandoned_Cart_Admin {

		/**
		 * Single instance of the class
		 *
		 * @var \YITH_WC_Dynamic_Pricing_Admin
		 */
		protected static $instance;

		/**
		 * Panel
		 *
		 * @var Panel
		 */
		protected $_panel; //phpcs:ignore

		/**
		 * Premium tab template file name
		 *
		 * @var string
		 */
		protected $_premium = 'premium.php'; //phpcs:ignore

		/**
		 * Panel page
		 *
		 * @var string
		 */
		protected $_panel_page = 'yith_woocommerce_recover_abandoned_cart'; //phpcs:ignore


		/**
		 * WP_List_Table
		 *
		 * @var WP_List_Table
		 */
		public $cpt_obj;

		/**
		 * WP_List_Table
		 *
		 * @var WP_List_Table
		 */
		public $cpt_obj_pending_orders;

		/**
		 * WP_List_Table
		 *
		 * @var WP_List_Table
		 */
		public $cpt_obj_emails;

		/**
		 * Pending orders
		 *
		 * @var bool
		 */
		public $pending_orders = false;

		/**
		 * Returns single instance of the class
		 *
		 * @return \YITH_WC_Recover_Abandoned_Cart_Admin
		 * @since 1.0.0
		 */
		public static function get_instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		/**
		 * Constructor
		 *
		 * Initialize plugin and registers actions and filters to be used
		 *
		 * @since  1.0.0
		 */
		public function __construct() {

			$this->check_version();

			if ( get_option( 'ywrac_pending_orders_enabled' ) === 'yes' ) {
				$this->pending_orders = true;
				add_action(
					'update_option_ywrac_pending_order_delete_config',
					array(
						$this,
						'update_woocommerce_hold_stock_minutes_option',
					)
				);
			}

			/* Load classes for table tabs */
			add_action( 'plugins_loaded', array( $this, 'include_tables_tabs' ), 25 );

			// custom styles and javascripts.
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_styles_scripts' ), 11 );

			$this->create_menu_items();

			// if the page is the editor of abandoned cart post type add the metabox.
			if ( ywrac_check_valid_admin_page( YITH_WC_Recover_Abandoned_Cart()->post_type_name ) ) {
				YITH_WC_RAC_Metaboxes();
			}

			add_action( 'plugins_loaded', array( $this, 'load_privacy_dpa' ), 20 );

			add_filter(
				'plugin_action_links_' . plugin_basename( YITH_YWRAC_DIR . '/' . basename( YITH_YWRAC_FILE ) ),
				array(
					$this,
					'action_links',
				)
			);
			add_filter( 'yith_show_plugin_row_meta', array( $this, 'plugin_row_meta' ), 10, 5 );

			// Metabox customizations.
			add_action( 'dbx_post_sidebar', array( $this, 'print_save_button_in_edit_page' ) );

			add_action( 'yith_ywrac_custom_number_field', array( $this, 'custom_number_field' ) );
			add_action( 'yith_ywrac_send_email_template', array( $this, 'send_email_template' ) );

			// reset reports action.
			add_action( 'wp_ajax_ywrac_reset_reports', array( $this, 'ajax_ywrac_reset_reports' ) );
			add_action( 'wp_ajax_nopriv_ywrac_reset_reports', array( $this, 'ajax_ywrac_reset_reports' ) );
			add_action( 'wp_ajax_email_template_toggle_enabled', array( $this, 'ajax_email_template_toggle_enabled' ) );
			add_action( 'init', 'yith_ywrac_check_sample_email_template_posts', 20 );
		}

		/**
		 * Load classes for table tabs.
		 */
		public function include_tables_tabs() {
			require_once YITH_YWRAC_INC . 'admin/class-wp-carts-list-table.php';
			require_once YITH_YWRAC_INC . 'admin/class-wp-pending-orders-list-table.php';
			require_once YITH_YWRAC_INC . 'admin/class-wp-emails-list-table.php';
			require_once YITH_YWRAC_INC . 'admin/class-wp-email-log-list-table.php';
			require_once YITH_YWRAC_INC . 'admin/class-wp-recovered-list-table.php';
		}

		/**
		 * Print save button in edit page.
		 *
		 * @param WP_Post $post The post.
		 */
		public function print_save_button_in_edit_page( $post ) {
			$post_type_name = YITH_WC_Recover_Abandoned_Cart_Email()->post_type_name;
			if ( ! ! $post && isset( $post->post_type ) && $post->post_type === $post_type_name ) {
				global $post_id;
				$is_updating      = ! ! $post_id;
				$save_text        = __( 'Save', 'yith-woocommerce-recover-abandoned-cart' );
				$post_type_object = get_post_type_object( $post_type_name );
				$single           = $post_type_object->labels->singular_name ?? '';

				if ( $single ) {
					$save_text = __( 'Save template', 'yith-woocommerce-recover-abandoned-cart' );
				}
				?>
				<div class="ywrac-post-type__actions yith-plugin-ui">
					<?php if ( $is_updating ) : ?>
						<button id="ywrac-post-type__save"
								class="yith-plugin-fw__button--primary yith-plugin-fw__button--xl"><?php echo esc_html( $save_text ); ?></button>
					<?php else : ?>
						<input id="ywrac-post-type__save" type="submit"
								class="yith-plugin-fw__button--primary yith-plugin-fw__button--xl" name="publish"
								value="<?php echo esc_html( $save_text ); ?>">
					<?php endif; ?>
				</div>
				<?php
			}
		}
		/**
		 * Load the class
		 */
		public function load_privacy_dpa() {
			if ( class_exists( 'YITH_Privacy_Plugin_Abstract' ) ) {
				require_once YITH_YWRAC_INC . 'class.yith-wc-abandoned-cart-privacy-dpa.php';
			}
		}


		/**
		 * Check the version of plugin
		 *
		 * @return void
		 * @since  1.1.0
		 */
		private function check_version() {
			$current_option_version = get_option( 'yit_ywrac_version', '0' );
			$forced                 = isset( $_GET['update_ywrac_options'] ) && 'forced' === sanitize_text_field( wp_unslash( $_GET['update_ywrac_options'] ) ); //phpcs:ignore

			if ( version_compare( $current_option_version, YITH_YWRAC_VERSION, '>=' ) && ! $forced ) {
				return;
			}

			// In the version 1.1.0 pending order statistics are added.
			if ( version_compare( YITH_YWRAC_VERSION, '1.1.0', '>=' ) ) {
				// email sent.
				if ( ! get_option( 'ywrac_email_sent_cart_counter' ) && '' !== get_option( 'ywrac_email_sent_counter' ) ) {
					add_option( 'ywrac_email_sent_cart_counter', get_option( 'ywrac_email_sent_counter' ) );
				}
				// click on email.
				if ( ! get_option( 'ywrac_email_cart_clicks_counter' ) && '' !== get_option( 'ywrac_email_clicks_counter' ) ) {
					add_option( 'ywrac_email_cart_clicks_counter', get_option( 'ywrac_email_clicks_counter' ) );
				}
				// recovered carts.
				if ( ! get_option( 'ywrac_total_recovered_carts' ) && '' !== get_option( 'ywrac_recovered_carts' ) ) {
					add_option( 'ywrac_total_recovered_carts', get_option( 'ywrac_recovered_carts' ) );
				}
				// total amounts.
				if ( ! get_option( 'ywrac_total_cart_amount' ) && '' !== get_option( 'ywrac_total_amount' ) ) {
					add_option( 'ywrac_total_cart_amount', get_option( 'ywrac_total_amount' ) );
				}
			}

			update_option( 'yit_ywrac_version', YITH_YWRAC_VERSION );
		}


		/**
		 * Init function check if the plugin is enabled
		 *
		 * @return void
		 * @since  1.0.0
		 */
		public function init() {
			if ( get_option( 'ywrac_enabled' ) !== 'yes' ) {
				return;
			}
		}

		/**
		 * Enqueue styles and scripts
		 *
		 * @access public
		 * @return void
		 * @since 1.0.0
		 */
		public function enqueue_styles_scripts() {
			wp_enqueue_style( 'yith_ywrac_backend', YITH_YWRAC_ASSETS_URL . '/css/backend.css', array(), YITH_YWRAC_VERSION );
			wp_enqueue_script( 'yith_ywrac_admin', YITH_YWRAC_ASSETS_URL . '/js/ywrac-admin' . YITH_YWRAC_SUFFIX . '.js', array( 'jquery' ), YITH_YWRAC_VERSION, true );
			if ( ! wp_script_is( 'selectWoo' ) ) {
				wp_enqueue_script( 'selectWoo' );
				wp_enqueue_script( 'wc-enhanced-select' );
			}
			wp_localize_script(
				'yith_ywrac_admin',
				'yith_ywrac_admin',
				array(
					'ajaxurl'            => admin_url( 'admin-ajax.php', is_ssl() ? 'https' : 'http' ),
					'send_email_nonce'   => wp_create_nonce( 'send-email' ),
					'sent_label'         => __( 'Email:', 'yith-woocommerce-recover-abandoned-cart' ),
					'sent_label_on'      => __( 'Sent on:', 'yith-woocommerce-recover-abandoned-cart' ),
					'sent_label_test'    => __( 'Email Sent!', 'yith-woocommerce-recover-abandoned-cart' ),
					'grab_email'         => get_option( 'ywrac_user_guest_enabled' ),
					'block_loader'       => YITH_YWRAC_ASSETS_URL . '/images/ajax-loader.gif',
					'reset_confirmation' => esc_html__( 'Are you sure you want to reset the reports?', 'yith-woocommerce-recover-abandoned-cart' ),
				)
			);
		}

		/**
		 * Create Menu Items
		 *
		 * Print admin menu items
		 *
		 * @since  1.0
		 */
		private function create_menu_items() {

			// Add a panel under YITH Plugins tab.
			add_action( 'admin_menu', array( $this, 'register_panel' ), 5 );
			add_action( 'yith_ywrac_carts', array( $this, 'carts_tab' ) );

			if ( $this->pending_orders ) {
				add_action( 'yith_ywrac_pending_orders', array( $this, 'pending_orders_tab' ) );
			}
			add_action( 'yith_ywrac_emails', array( $this, 'emails_tab' ) );
			add_action( 'yith_ywrac_recovered', array( $this, 'recovered_tab' ) );
			add_action( 'yith_ywrac_mailslog', array( $this, 'mailslog_tab' ) );
			add_action( 'yith_ywrac_reports', array( $this, 'reports_tab' ) );
		}

		/**
		 * Add a panel under YITH Plugins tab
		 *
		 * @return   void
		 * @since    1.0
		 * @use      /Yit_Plugin_Panel class
		 * @see      plugin-fw/lib/yit-plugin-panel.php
		 */
		public function register_panel() {

			if ( ! empty( $this->_panel ) ) {
				return;
			}

			$admin_tabs = array(
				'dashboard' => array(
					'title'       => esc_html__( 'Dashboard', 'yith-woocommerce-recover-abandoned-cart' ),
					'description' => esc_html__( 'A main overview of your recover cart strategies', 'yith-woocommerce-recover-abandoned-cart' ),
					'icon'        => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" /></svg>',
				),
				'general'   => array(
					'title'       => esc_html__( 'Settings', 'yith-woocommerce-recover-abandoned-cart' ),
					'icon'        => 'settings',
					'description' => esc_html__( 'Set the general behaviour of the plugin', 'yith-woocommerce-recover-abandoned-cart' ),
				),
				'email'     => array(
					'title' => esc_html__( 'Email', 'yith-woocommerce-recover-abandoned-cart' ),
					'icon'  => '<svg data-slot="icon" aria-hidden="true" fill="none" stroke-width="1.5" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M21.75 9v.906a2.25 2.25 0 0 1-1.183 1.981l-6.478 3.488M2.25 9v.906a2.25 2.25 0 0 0 1.183 1.981l6.478 3.488m8.839 2.51-4.66-2.51m0 0-1.023-.55a2.25 2.25 0 0 0-2.134 0l-1.022.55m0 0-4.661 2.51m16.5 1.615a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V8.844a2.25 2.25 0 0 1 1.183-1.981l7.5-4.039a2.25 2.25 0 0 1 2.134 0l7.5 4.039a2.25 2.25 0 0 1 1.183 1.98V19.5Z" stroke-linecap="round" stroke-linejoin="round"></path></svg>',
				),
			);

			if ( ! $this->pending_orders ) {
				unset( $admin_tabs['pending_orders'] );
			}

			$args = array(
				'create_menu_page' => true,
				'parent_slug'      => '',
				'plugin_slug'      => YITH_YWRAC_SLUG,
				'page_title'       => 'YITH WooCommerce Recover Abandoned Cart',
				'menu_title'       => 'Recover Abandoned Cart',
				'capability'       => 'manage_options',
				'parent'           => 'yith-woocommerce-recover-abandoned-cart',
				'parent_page'      => 'yith_plugin_panel',
				'page'             => $this->_panel_page,
				'admin-tabs'       => $admin_tabs,
				'class'            => yith_set_wrapper_class(),
				'options-path'     => YITH_YWRAC_DIR . '/plugin-options',
				'is_premium'       => true,
				'ui_version'       => 2,
				'help_tab'         => array(
					'hc_url'     => 'https://support.yithemes.com/hc/en-us/categories/4460793775761-YITH-WOOCOMMERCE-RECOVER-ABANDONED-CART',
					'main_video' => array(
						'desc' => _x( 'Check this video to learn how to configure the plugin:', 'Help tab - Video title', 'yith-woocommerce-recover-abandoned-cart' ),
						'url'  => array(
							'en' => 'https://www.youtube.com/embed/56k2uTRA7v8',
							'it' => 'https://www.youtube.com/embed/of4_9f1OYrE',
							'es' => 'https://www.youtube.com/embed/6hyNUGeMmXs',
						),
					),
				),
				'welcome_modals'   => array(
					'on_close' => function () {
						update_option( 'ywrac-welcome-modal', 'no' );
					},
					'modals'   => array(
						'welcome' => array(
							'type'        => 'welcome',
							'description' => __( 'The #1 tool to recover abandoned carts and pending orders', 'yith-woocommerce-recover-abandoned-cart' ),
							'show'        => false, //get_option( 'ywrac-welcome-modal', 'welcome' ) === 'welcome',
							'items'       => array(
								'documentation'  => array(),
								'how-to-video'   => array(
									'url' => array(
										'en' => 'https://www.youtube.com/watch?v=PLLkF00Yi5c',
										'it' => 'https://www.youtube.com/watch?v=rqKNScId3EM',
										'es' => 'https://www.youtube.com/watch?v=jLt6nMBP2hI',
									),
								),
								'feature'        => array(
									'title'       => __( 'Configure the general options of the plugin', 'yith-woocommerce-recover-abandoned-cart' ),
									'description' => __( 'to decide how to activate strategies to recover carts', 'yith-woocommerce-recover-abandoned-cart' ),
									'url'         => add_query_arg( array( 'page' => 'yith_woocommerce_recover_abandoned_cart' ), admin_url( 'admin.php' ) ),
								),
								'create-product' => array(
									'title'       => __( 'Create and customize the emails sent to your customer', 'yith-woocommerce-recover-abandoned-cart' ),
									'description' => __( '...and start your cart recovery strategy now!', 'yith-woocommerce-recover-abandoned-cart' ),
									'url'         => add_query_arg( array( 'post_type' => 'ywrac_email' ), admin_url( 'post-new.php' ) ),
								),
							),
						),
					),
				),
				'your_store_tools' => array(
					'items' => array(
						'wishlist'             => array(
							'name'           => 'YITH WooCommerce Wishlist',
							'icon_url'       => YITH_YWRAC_ASSETS_URL . '/images/plugins/wishlist.svg',
							'url'            => '//yithemes.com/themes/plugins/yith-woocommerce-wishlist/',
							'description'    => _x(
								'Allow your customers to create lists of products they want and share them with family and friends.',
								'[YOUR STORE TOOLS TAB] Description for plugin YITH WooCommerce Wishlist',
								'yith-woocommerce-recover-abandoned-cart'
							),
							'is_active'      => defined( 'YITH_WCWL_PREMIUM' ),
							'is_recommended' => true,
						),
						'gift-cards'           => array(
							'name'           => 'YITH WooCommerce Gift Cards',
							'icon_url'       => YITH_YWRAC_ASSETS_URL . '/images/plugins/gift-cards.svg',
							'url'            => '//yithemes.com/themes/plugins/yith-woocommerce-gift-cards/',
							'description'    => _x(
								'Sell gift cards in your shop to increase your earnings and attract new customers.',
								'[YOUR STORE TOOLS TAB] Description for plugin YITH WooCommerce Gift Cards',
								'yith-woocommerce-recover-abandoned-cart'
							),
							'is_active'      => defined( 'YITH_YWGC_PREMIUM' ),
							'is_recommended' => true,
						),
						'request-a-quote'      => array(
							'name'           => 'YITH WooCommerce Request a Quote',
							'icon_url'       => YITH_YWRAC_ASSETS_URL . '/images/plugins/request-a-quote.svg',
							'url'            => '//yithemes.com/themes/plugins/yith-woocommerce-request-a-quote/',
							'description'    => _x(
								'Hide prices and/or the "Add to cart" button and let your customers request a custom quote for every product.',
								'[YOUR STORE TOOLS TAB] Description for plugin YITH WooCommerce Request a Quote',
								'yith-woocommerce-recover-abandoned-cart'
							),
							'is_active'      => defined( 'YITH_YWRAQ_PREMIUM' ),
							'is_recommended' => false,
						),
						'ajax-product-filter'  => array(
							'name'           => 'YITH WooCommerce Ajax Product Filter',
							'icon_url'       => YITH_YWRAC_ASSETS_URL . '/images/plugins/ajax-product-filter.svg',
							'url'            => '//yithemes.com/themes/plugins/yith-woocommerce-ajax-product-filter/',
							'description'    => _x(
								'Help your customers to easily find the products they are looking for and improve the user experience of your shop.',
								'[YOUR STORE TOOLS TAB] Description for plugin YITH WooCommerce Ajax Product Filter',
								'yith-woocommerce-recover-abandoned-cart'
							),
							'is_active'      => defined( 'YITH_WCAN_PREMIUM' ),
							'is_recommended' => false,
						),
						'product-addons'       => array(
							'name'           => 'YITH WooCommerce Product Add-Ons & Extra Options',
							'icon_url'       => YITH_YWRAC_ASSETS_URL . '/images/plugins/product-add-ons.svg',
							'url'            => '//yithemes.com/themes/plugins/yith-woocommerce-product-add-ons/',
							'description'    => _x(
								'Add paid or free advanced options to your product pages using fields like radio buttons, checkboxes, drop-downs, custom text inputs, and more.',
								'[YOUR STORE TOOLS TAB] Description for plugin YITH WooCommerce Product Add-Ons',
								'yith-woocommerce-recover-abandoned-cart'
							),
							'is_active'      => defined( 'YITH_WAPO_PREMIUM' ),
							'is_recommended' => false,
						),
						'dynamic-pricing'      => array(
							'name'           => 'YITH WooCommerce Dynamic Pricing & Discounts',
							'icon_url'       => YITH_YWRAC_ASSETS_URL . '/images/plugins/dynamic-pricing-and-discounts.svg',
							'url'            => '//yithemes.com/themes/plugins/yith-woocommerce-dynamic-pricing-and-discounts/',
							'description'    => _x(
								'Increase conversions through dynamic discounts and price rules, and build powerful and targeted offers.',
								'[YOUR STORE TOOLS TAB] Description for plugin YITH WooCommerce Dynamic Pricing & Discounts',
								'yith-woocommerce-recover-abandoned-cart'
							),
							'is_active'      => defined( 'YITH_YWDPD_PREMIUM' ),
							'is_recommended' => false,
						),
						'customize-my-account' => array(
							'name'           => 'YITH WooCommerce Customize My Account Page',
							'icon_url'       => YITH_YWRAC_ASSETS_URL . '/images/plugins/customize-myaccount-page.svg',
							'url'            => '//yithemes.com/themes/plugins/yith-woocommerce-customize-my-account-page/',
							'description'    => _x(
								'Customize the My Account page of your customers by creating custom sections with promotions and ad-hoc content based on your needs.',
								'[YOUR STORE TOOLS TAB] Description for plugin YITH WooCommerce Customize My Account',
								'yith-woocommerce-recover-abandoned-cart'
							),
							'is_active'      => defined( 'YITH_WCMAP_PREMIUM' ),
							'is_recommended' => false,
						),
					),
				),
			);

			// enable shop manager to set Dynamic Pricing Options.
			if ( get_option( 'ywrac_enable_shop_manager' ) === 'yes' ) {
				add_filter(
					'option_page_capability_yit_' . $args['parent'] . '_options',
					array(
						$this,
						'change_capability',
					)
				);
				add_filter( 'yit_plugin_panel_menu_page_capability', array( $this, 'change_capability' ) );
				$args['capability'] = 'manage_woocommerce';
			}

			/* === Fixed: not updated theme  === */
			if ( ! class_exists( 'YIT_Plugin_Panel_WooCommerce' ) ) {
				require_once YITH_YWRAC_DIR . '/plugin-fw/lib/yit-plugin-panel-wc.php';
			}

			$this->_panel = new YIT_Plugin_Panel_WooCommerce( $args );

			// Custom tinymce button.
			add_action( 'admin_head', array( $this, 'tc_button' ) );
		}

		/**
		 * Modify the capability
		 *
		 * @return string
		 */
		public function change_capability() {
			return 'manage_woocommerce';
		}

		/**
		 * Add a new button to tinymce
		 *
		 * @return   void
		 * @since    1.0
		 */
		public function tc_button() {

			if ( ! current_user_can( 'edit_posts' ) && ! current_user_can( 'edit_pages' ) ) {
				return;
			}

			$post_type = '';
			$get       = $_GET; //phpcs:ignore
			if ( isset( $get['post'] ) ) {
				$post      = sanitize_text_field( wp_unslash( $get['post'] ) );
				$post_type = get_post_type( $post );
			} elseif ( isset( $get['post_type'] ) ) {
				$post_type = sanitize_text_field( wp_unslash( $get['post_type'] ) );
			}

			if ( YITH_WC_Recover_Abandoned_Cart_Email()->post_type_name !== $post_type ) {
				return;
			}

			if ( 'true' === get_user_option( 'rich_editing' ) ) {
				add_filter( 'mce_external_plugins', array( $this, 'add_tinymce_plugin' ) );
				add_filter( 'mce_buttons', array( $this, 'register_tc_button' ) );
				add_filter( 'mce_external_languages', array( $this, 'add_tc_button_lang' ) );
			}
		}

		/**
		 * Add plugin button to tinymce from filter mce_external_plugins
		 *
		 * @param array $plugin_array Plugin array.
		 *
		 * @return   array
		 * @since    1.0
		 */
		public function add_tinymce_plugin( $plugin_array ) {
			$plugin_array['tc_button'] = YITH_YWRAC_ASSETS_URL . '/js/tinymce/text-editor.js';

			return $plugin_array;
		}

		/**
		 * Register the custom button to tinymce from filter mce_buttons
		 *
		 * @param array $buttons Buttons.
		 *
		 * @return   array
		 * @since    1.0
		 */
		public function register_tc_button( $buttons ) {
			array_push( $buttons, 'tc_button' );

			return $buttons;
		}

		/**
		 * Add multilingual to mce button from filter mce_external_languages
		 *
		 * @param array $locales Locales.
		 *
		 * @return   array
		 * @since    1.0
		 */
		public function add_tc_button_lang( $locales ) {
			$locales ['tc_button'] = YITH_YWRAC_INC . 'admin/tinymce/tinymce-plugin-langs.php';

			return $locales;
		}

		/**
		 * Premium Tab Template
		 *
		 * Load the premium tab template on admin page
		 *
		 * @return   void
		 * @since    1.0
		 */
		public function premium_tab() {
			$premium_tab_template = YITH_YWRAC_TEMPLATE_PATH . '/admin/' . $this->_premium;
			if ( file_exists( $premium_tab_template ) ) {
				include_once $premium_tab_template;
			}
		}

		/**
		 * Carts Template
		 *
		 * Load the abandoned cart template on admin page
		 *
		 * @return   void
		 * @since    1.0
		 */
		public function carts_tab() {
			$this->cpt_obj = new YITH_YWRAC_Carts_List_Table();

			$carts_tab = YITH_YWRAC_TEMPLATE_PATH . '/admin/carts-tab.php';
			if ( file_exists( $carts_tab ) ) {
				include_once $carts_tab;
			}
		}

		/**
		 * Carts Template
		 *
		 * Load the abandoned cart template on admin page
		 *
		 * @return   void
		 * @since    1.0
		 */
		public function pending_orders_tab() {
			$this->cpt_obj_pending_orders = new YITH_YWRAC_Pending_Orders_List_Table();

			$pending_orders_tab = YITH_YWRAC_TEMPLATE_PATH . '/admin/pending_orders-tab.php';
			if ( file_exists( $pending_orders_tab ) ) {
				include_once $pending_orders_tab;
			}
		}

		/**
		 * Email Templates
		 *
		 * Load the email templates on admin page
		 *
		 * @return   void
		 * @since    1.0
		 */
		public function emails_tab() {
			$this->cpt_obj_emails = new YITH_YWRAC_Emails_List_Table();

			$emails_tab = YITH_YWRAC_TEMPLATE_PATH . '/admin/emails-tab.php';
			if ( file_exists( $emails_tab ) ) {
				include_once $emails_tab;
			}
		}

		/**
		 * Email Log Templates
		 *
		 * Load the email logs templates on admin page
		 *
		 * @return   void
		 * @since    1.0
		 */
		public function mailslog_tab() {
			$this->cpt_obj_mailslog = new YITH_YWRAC_Email_Log_List_Table();

			$mailslog_tab = YITH_YWRAC_TEMPLATE_PATH . '/admin/mailslog-tab.php';
			if ( file_exists( $mailslog_tab ) ) {
				include_once $mailslog_tab;
			}
		}

		/**
		 * Recovered Orders
		 *
		 * Load the order completed with recover cart email
		 *
		 * @return   void
		 * @since    1.0
		 */
		public function recovered_tab() {
			$this->cpt_obj_orders = new YITH_YWRAC_Recovered_List_Table();

			$recovered_tab = YITH_YWRAC_TEMPLATE_PATH . '/admin/recovered-tab.php';
			if ( file_exists( $recovered_tab ) ) {
				include_once $recovered_tab;
			}
		}

		/**
		 * Reports Plugin Panel
		 *
		 * Load the stats of plugin
		 *
		 * @return   void
		 * @since    1.0
		 */
		public function reports_tab() {

			// emails sent.
			// APPLY_FILTERS: ywrac_email_sent_counter | Emails sent report counter. | @param int $sent_counter .
			$email_sent_counter = apply_filters( 'ywrac_email_sent_counter', get_option( 'ywrac_email_sent_counter', 0 ) );
			// APPLY_FILTERS: ywrac_email_sent_cart_counter | Cart emails sent report counter. | @param int $sent_emails_cart_counter .
			$email_sent_cart_counter = apply_filters( 'ywrac_email_sent_cart_counter', get_option( 'ywrac_email_sent_cart_counter', 0 ) );
			// APPLY_FILTERS: ywrac_email_sent_order_counter | Order emails sent report counter. | @param int $sent_emails_order_counter .
			$email_sent_order_counter = apply_filters( 'ywrac_email_sent_order_counter', get_option( 'ywrac_email_sent_order_counter', 0 ) );

			// email clicks.
			// APPLY_FILTERS: ywrac_email_clicks_counter | Email clicks report counter. | @param int $email_clicks_counter .
			$email_clicks_counter = apply_filters( 'ywrac_email_clicks_counter', get_option( 'ywrac_email_clicks_counter', 0 ) );
			// APPLY_FILTERS: ywrac_email_cart_clicks_counter | Cart email clicks report counter. | @param int $email_cart_clicks_counter .
			$email_cart_clicks_counter = apply_filters( 'ywrac_email_cart_clicks_counter', get_option( 'ywrac_email_cart_clicks_counter', 0 ) );
			// APPLY_FILTERS: ywrac_email_order_clicks_counter | Order email clicks report counter. | @param int $email_order_clicks_counter .
			$email_order_clicks_counter = apply_filters( 'ywrac_email_order_clicks_counter', get_option( 'ywrac_email_order_clicks_counter', 0 ) );

			// abandoned carts and pending orders.
			// APPLY_FILTERS: ywrac_abandoned_carts_counter | Abandoned carts report counter. | @param int $abandoned_carts_counter .
			$abandoned_carts_counter = apply_filters( 'ywrac_abandoned_carts_counter', get_option( 'ywrac_abandoned_carts_counter', 0 ) );
			// APPLY_FILTERS: ywrac_total_pending_orders | Total pending orders report counter. | @param int $total_pending_orders .
			$total_pending_orders = apply_filters( 'ywrac_total_pending_orders', get_option( 'ywrac_total_pending_orders', 0 ) );
			$total_pending_orders = $total_pending_orders < 0 ? 0 : $total_pending_orders;
			// APPLY_FILTERS: ywrac_total_abandoned_carts | Total Abandoned carts report counter. | @param int $total_abandoned_carts .
			$total_abandoned_carts = apply_filters( 'ywrac_total_abandoned_carts', get_option( 'ywrac_total_abandoned_carts', 0 ) );

			// recovered carts.
			// APPLY_FILTERS: ywrac_recovered_carts | Recovered carts report counter. | @param int $recovered_carts .
			$recovered_carts = apply_filters( 'ywrac_recovered_carts', get_option( 'ywrac_recovered_carts', 0 ) );
			// APPLY_FILTERS: ywrac_total_recovered_pending_orders | Total recovered pending orders report counter. | @param int $total_recovered_pending_orders .
			$total_recovered_pending_orders = apply_filters( 'ywrac_total_recovered_pending_orders', get_option( 'ywrac_total_recovered_pending_orders', 0 ) );
			$total_recovered_pending_orders = $total_recovered_pending_orders < 0 ? 0 : $total_recovered_pending_orders;
			// APPLY_FILTERS: ywrac_total_recovered_carts | Total Recovered carts report counter. | @param int $total_recovered_carts .
			$total_recovered_carts = apply_filters( 'ywrac_total_recovered_carts', get_option( 'ywrac_total_recovered_carts', 0 ) );
			$total_recovered_carts = ! is_numeric( $total_recovered_carts ) ? 0 : $total_recovered_carts;

			// rate conversion email sent/number of recovered items.
			// APPLY_FILTERS: ywrac_rate_conversion | Conversion rate value. | @param string $rate_conversion .
			$rate_conversion = $email_sent_counter ? apply_filters( 'ywrac_rate_conversion', number_format( 100 * $recovered_carts / $email_sent_counter, 2, '.', '' ) ) : 0;
			// APPLY_FILTERS: ywrac_rate_cart_conversion | Cart conversion rate value. | @param string $rate_cart_conversion .
			$rate_cart_conversion = $email_sent_cart_counter ? apply_filters( 'ywrac_rate_cart_conversion', number_format( 100 * $total_recovered_carts / $email_sent_cart_counter, 2, '.', '' ) ) : 0;
			// APPLY_FILTERS: ywrac_rate_order_conversion | Order conversion rate value. | @param string $rate_order_conversion .
			$rate_order_conversion = $email_sent_order_counter ? apply_filters( 'ywrac_rate_order_conversion', number_format( 100 * $total_recovered_pending_orders / $email_sent_order_counter, 2, '.', '' ) ) : 0;

			if ( ! $email_sent_counter ) {
				$rate_conversion = apply_filters( 'ywrac_rate_conversion', 0 );
			}
			// APPLY_FILTERS: ywrac_total_amount | Total amount value (reports). | @param string $total_amount .
			$total_amount = apply_filters( 'ywrac_total_amount', get_option( 'ywrac_total_amount' ) );
			// APPLY_FILTERS: ywrac_total_cart_amount | Total cart amount value (reports). | @param string $total_cart_amount .
			$total_cart_amount = apply_filters( 'ywrac_total_cart_amount', get_option( 'ywrac_total_cart_amount' ) );
			// APPLY_FILTERS: ywrac_total_order_amount | Total order amount value (reports). | @param string $total_order_amount .
			$total_order_amount = apply_filters( 'ywrac_total_order_amount', get_option( 'ywrac_total_order_amount' ) );

			$reports_tab = YITH_YWRAC_TEMPLATE_PATH . '/admin/reports-tab.php';
			if ( file_exists( $reports_tab ) ) {
				include_once $reports_tab;

				if ( apply_filters( 'ywrac_show_reset_stats_btn', false ) ) {
					echo '<button class="ywrac-reset-reports button-primary">' . esc_html__( 'Reset', 'yith-woocommerce-recover-abandoned-cart' ) . '</button>';
					wp_nonce_field( 'ywrac_reset_reports_nonce', 'ywrac_reset_reports_nonce', false, true );
				}
			}
		}

		/**
		 * Reset Report
		 *
		 * @return   mixed Array
		 * @since    1.4.3
		 */
		public function ajax_ywrac_reset_reports() {
			check_ajax_referer( 'ywrac_reset_reports_nonce', 'nonce' );
			$result = array( 'reset' => true );

			update_option( 'ywrac_email_sent_counter', 0 );
			update_option( 'ywrac_email_sent_cart_counter', 0 );
			update_option( 'ywrac_email_sent_order_counter', 0 );
			update_option( 'ywrac_email_clicks_counter', 0 );
			update_option( 'ywrac_email_cart_clicks_counter', 0 );
			update_option( 'ywrac_email_order_clicks_counter', 0 );
			update_option( 'ywrac_abandoned_carts_counter', 0 );
			update_option( 'ywrac_total_pending_orders', 0 );
			update_option( 'ywrac_total_abandoned_carts', 0 );
			update_option( 'ywrac_recovered_carts', 0 );
			update_option( 'ywrac_total_recovered_pending_orders', 0 );
			update_option( 'ywrac_total_recovered_carts', 0 );
			update_option( 'ywrac_total_amount', 0 );
			update_option( 'ywrac_total_cart_amount', 0 );
			update_option( 'ywrac_total_order_amount', 0 );

			wp_send_json( $result );
		}

		/**
		 * Action Links
		 *
		 * Add the action links to plugin admin page
		 *
		 * @param array $links Links plugin array.
		 *
		 * @return   mixed Array
		 * @use      plugin_action_links_{$plugin_file_name}
		 * @since    1.0
		 */
		public function action_links( $links ) {
			$links = yith_add_action_links( $links, $this->_panel_page, true, YITH_YWRAC_SLUG );

			return $links;
		}

		/**
		 * Plugin_row_meta
		 *
		 * Add the action links to plugin admin page.
		 *
		 * @param array  $new_row_meta_args Plugin Meta New args.
		 * @param string $plugin_meta Plugin Meta.
		 * @param string $plugin_file Plugin file.
		 * @param array  $plugin_data Plugin data.
		 * @param string $status Status.
		 * @param string $init_file Init file.
		 *
		 * @return   Array
		 * @since    1.0
		 * @use      plugin_row_meta
		 */
		public function plugin_row_meta( $new_row_meta_args, $plugin_meta, $plugin_file, $plugin_data, $status, $init_file = 'YITH_YWRAC_INIT' ) {
			if ( defined( $init_file ) && constant( $init_file ) === $plugin_file ) {
				$new_row_meta_args['slug'] = YITH_YWRAC_SLUG;
			}

			if ( defined( $init_file ) && constant( $init_file ) === $plugin_file ) {
				$new_row_meta_args['is_premium'] = true;
			}

			return $new_row_meta_args;
		}

		/**
		 * Add a textarea with editor as type of plugin panel
		 *
		 * @param array $opt Option.
		 *
		 * @since    1.0
		 */
		public function additional_textarea( $opt ) {
			$opt['default'] = ( get_option( $opt['id'] ) ) ? get_option( $opt['id'] ) : $opt['default'];
			?>
			<tr valign="top">
				<th scope="row" class="titledesc">
					<label for="<?php echo esc_attr( $opt['id'] ); ?>"><?php echo esc_html( $opt['name'] ); ?></label>
				</th>
				<td class="forminp forminp-text">
					<?php wc_get_template( 'admin/panel/textarea-editor.php', array( 'args' => $opt ) ); ?>
				</td>
			</tr>
			<?php
		}

		/**
		 * Update the value of textarea in the plugin panel
		 *
		 * @param array $opt Options.
		 *
		 * @since    1.0
		 */
		public function update_additional_textarea( $opt ) {

			if ( isset( $_POST[ $opt['id'] ] ) ) {  //phpcs:ignore
				$post_id = sanitize_text_field( wp_unslash( $_POST[ $opt['id'] ] ) ); //phpcs:ignore
				update_option( $opt['id'], $post_id );
			}
		}

		/**
		 * Get the premium landing uri
		 *
		 * @return  string The premium landing link
		 * @since   1.0.0
		 */
		public function get_premium_landing_uri() {
			return defined( 'YITH_REFER_ID' ) ? $this->_premium_landing . '?refer_id=' . YITH_REFER_ID : $this->_premium_landing;
		}

		/**
		 * Return the private panel_page value
		 *
		 * @return  string The panel page name
		 * @since   1.0.0
		 */
		public function get_panel_page() {
			return $this->_panel_page;
		}

		/**
		 * Return the panel page uri
		 *
		 * @param string $tab Tab.
		 *
		 * @return string
		 */
		public function get_panel_page_uri( $tab = '' ) {
			$panel_uri = add_query_arg( 'page', $this->_panel_page, admin_url( 'admin.php' ) );
			if ( $tab ) {
				$panel_uri = add_query_arg( 'tab', $tab, $panel_uri );
			}

			return $panel_uri;
		}

		/**
		 * Change delete Pending Orders after ... option
		 */
		public function update_woocommerce_hold_stock_minutes_option() {

			$old_wc_value = get_option( '_ywrac_old_wc_value', '' );

			if ( empty( $old_wc_value ) ) {
				$old_wc_value = get_option( 'woocommerce_hold_stock_minutes' );
				update_option( '_ywrac_old_wc_value', $old_wc_value );
			}

			$pending_order_config = get_option( 'ywrac_pending_order_delete_config' );
			if ( empty( $pending_order_config ) ) {
				$value = get_option( 'ywrac_pending_orders_delete', 360 );
			} else {
				$delete_pending_order_time = empty( $pending_order_config['delete_pending_order_time'] ) ? 360 : $pending_order_config['delete_pending_order_time'];
				$delete_pending_order_type = empty( $pending_order_config['delete_pending_order_type'] ) ? 'hours' : $pending_order_config['delete_pending_order_type'];
				switch ( $delete_pending_order_type ) {
					case 'minutes':
						$value = intval( $delete_pending_order_time );
						break;
					case 'hours':
						$value = intval( $delete_pending_order_time ) * 60;
						break;
					case 'days':
						$value = ( intval( $delete_pending_order_time ) * 24 ) * 60;
						break;
				}
			}

			$value = apply_filters( 'woocommerce_admin_settings_sanitize_option_woocommerce_hold_stock_minutes', '', '', $value );

			update_option( 'woocommerce_hold_stock_minutes', $value );
		}

		/**
		 * Cancel pending order
		 */
		public function cancel_unpaid_orders() {
			global $wpdb;

			$duration             = 0;
			$pending_order_config = get_option( 'ywrac_pending_orders_delete_config' );
			if ( empty( $pending_order_config ) ) {
				$duration = get_option( 'ywrac_pending_orders_delete', 360 );
			} else {
				$delete_pending_order_time = empty( $pending_order_config['delete_pending_order_time'] ) ? 360 : $pending_order_config['delete_pending_order_time'];
				$delete_pending_order_type = empty( $pending_order_config['delete_pending_order_type'] ) ? 'hours' : $pending_order_config['delete_pending_order_type'];
				switch ( $delete_pending_order_type ) {
					case 'minutes':
						$duration = intval( $delete_pending_order_time );
						break;
					case 'hours':
						$duration = intval( $delete_pending_order_time ) * 60;
						break;
					case 'days':
						$duration = intval( $delete_pending_order_time ) * 24 * 60;
						break;
				}
			}

			if ( $duration < 1 ) {
				return;
			}

			$date = date( 'Y-m-d H:i:s', strtotime( '-' . absint( $duration * 60 ) . ' MINUTES', ywrac_get_timestamp() ) ); //phpcs:ignore

			if ( OrderUtil::custom_orders_table_usage_is_enabled() ) {
				$args          = array(
					'return'        => 'ids',
					'limit'         => -1,
					'status'        => array( 'wc-pending' ),
					'date_modified' => '<' . $date,
				);
				$unpaid_orders = wc_get_orders( $args );
			} else {
				$unpaid_orders = $wpdb->get_col( //phpcs:ignore
					$wpdb->prepare(
						"
				SELECT posts.ID
				FROM {$wpdb->posts} AS posts
				WHERE 	posts.post_type   IN ('" . implode( "','", wc_get_order_types() ) . "')
				AND 	posts.post_status = 'wc-pending'
				AND 	posts.post_modified < %s
			",
						$date
					)
				);
			}

			if ( $unpaid_orders ) {
				foreach ( $unpaid_orders as $unpaid_order ) {
					$order = wc_get_order( $unpaid_order );

					if ( apply_filters( 'woocommerce_cancel_unpaid_order', 'checkout' === $unpaid_order->get_meta( '_created_via' ), $order ) ) {
						$order->update_status( 'cancelled', __( 'Unpaid order cancelled - time limit reached.', 'yith-woocommerce-recover-abandoned-cart' ) );
					}
				}
			}

			wp_clear_scheduled_hook( 'woocommerce_cancel_unpaid_orders' );
			wp_schedule_single_event( time() + ( absint( $duration ) * 60 ), 'woocommerce_cancel_unpaid_orders' );
		}

		/**
		 * Toggle to enable the email template
		 */
		public function ajax_email_template_toggle_enabled() {

			$posted = $_REQUEST; //phpcs:ignore
			if ( ! empty( $posted['id'] ) && ! empty( $posted['enabled'] ) && ! empty( $posted['security'] ) && wp_verify_nonce( $posted['security'], 'email-template-status-toggle-enabled' ) ) {
				$email_template_id = absint( $posted['id'] );
				$enabled           = $posted['enabled'];
				$post              = get_post( $email_template_id );

				if ( $post ) {
					update_post_meta( $email_template_id, '_ywrac_email_active', $enabled );
					wp_send_json(
						array(
							'success'    => true,
							'new_status' => $enabled,
						)
					);
				} else {
					wp_send_json(
						array(
							// translators: placeholder email template id.
							'error' => sprintf( __( 'Error: Email template #%s not found', 'yith-woocommerce-recover-abandoned-cart' ), $email_template_id ),
						)
					);
				}
			}
		}

		/**
		 * Custom field for Email Template metabox.
		 *
		 * @param array $field Fields.
		 */
		public function custom_number_field( $field ) {
			$mode = ! ! $field['mode'] ? $field['mode'] : '';
			switch ( $mode ) {
				case 'minutes':
					$sidetext = esc_html__( 'minutes', 'yith-woocommerce-recover-abandoned-cart' );
					break;
				case 'hours':
					$sidetext = esc_html__( 'hours', 'yith-woocommerce-recover-abandoned-cart' );
					break;
				case 'days':
					$sidetext = esc_html__( 'days', 'yith-woocommerce-recover-abandoned-cart' );
			}

			$html  = "<div class='yith-plugin-fw-custom-number-container '>";
			$html .= "<input type='number' name='{$field['name']}' value='{$field['value']}' min='0' style='width: 90px; margin-right: 10px;' />";
			if ( $mode ) {
				$html .= "<span style='line-height: 38px;'>" . $sidetext . '</span>';
			}
			$html .= '</div>';
			echo $html; //phpcs:ignore
		}

		/**
		 * Custom field for test email sending button.
		 *
		 * @param array $field Fields.
		 */
		public function send_email_template( $field ) {
			$value = ! ! $field['value'] ? $field['value'] : $field['default'];
			$name  = ! ! $field['name'] ? $field['name'] : '';
			if ( isset( $_GET['post'] ) ) {  //phpcs:ignore
				$post = sanitize_text_field( wp_unslash( $_GET['post'] ) ); //phpcs:ignore
				$html  = '<div class="yith-plugin-fw-send-email-template-container">';
				$html .= "<input type='text' name='{$name}' id='_ywrac_email_to_send' value='{$value}' class='yith-plugin-fw-text-input' style='width: 200px; margin-right: 10px;'>";
				$html .= sprintf( '<a class="ywrac-button-sent-email button-primary button-large button" data-id="%d" href="#">%s</a>', $post, esc_html__( 'Send email', 'yith-woocommerce-recover-abandoned-cart' ) );
				$html .= '</div>';
			} else {
				$html  = '<div class="yith-plugin-fw-send-email-template-container">';
				$html .= '<span>' . esc_html__( 'Save the email template before sending a test email', 'yith-woocommerce-recover-abandoned-cart' ) . '</span>';
				$html .= '</div>';
			}

			echo $html; //phpcs:ignore
		}
	}
}

/**
 * Unique access to instance of YITH_WC_Recover_Abandoned_Cart_Admin class
 *
 * @return \YITH_WC_Recover_Abandoned_Cart_Admin
 */
function YITH_WC_Recover_Abandoned_Cart_Admin() { //phpcs:ignore
	return YITH_WC_Recover_Abandoned_Cart_Admin::get_instance();
}
