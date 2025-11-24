<?php
/**
 * Class YITH_WCBEP_Install
 * Installation related functions and actions.
 *
 * @author  YITH <plugins@yithemes.com>
 * @package YITH\BulkEditing\Classes
 * @since   2.0.0
 */

if ( ! class_exists( 'YITH_WCBEP_Install' ) ) {
	/**
	 * YITH_WCBEP_Install class.
	 *
	 * @since 2.0
	 */
	class YITH_WCBEP_Install {

		/**
		 * The updates to fire.
		 *
		 * @var array
		 */
		protected $db_updates = array(
			'2.3.0' => array(
				'yith_wcbep_update_230_table_views_conditions',
			),
		);

		/**
		 * Callbacks to be fired soon, instead of being scheduled.
		 *
		 * @var callable[]
		 */
		private $soon_callbacks = array(
			'yith_wcbep_update_230_table_views_conditions',
		);

		/**
		 * The version option.
		 */
		const VERSION_OPTION = 'yith_woocommerce_bulk_version';

		/**
		 * The version option.
		 */
		const DB_VERSION_OPTION = 'yith_wcbep_db_version';

		/**
		 * The update scheduled option.
		 */
		const DB_UPDATE_SCHEDULED_OPTION = 'yith_wcbep_db_update_scheduled_for';

		/**
		 * The update scheduled option.
		 */
		const UPDATE_CALLBACK_HOOK = 'yith_wcbep_run_update_callback';

		/**
		 * The update scheduled option.
		 */
		const UPDATE_CALLBACK_GROUP = 'yith-wcbep-db-updates';

		/**
		 * Single instance of the class
		 *
		 * @var YITH_WCBEP_Install
		 */
		protected static $instance;

		/**
		 * Singleton implementation
		 *
		 * @return YITH_WCBEP_Install
		 */
		public static function get_instance() {
			$self = __CLASS__ . ( class_exists( __CLASS__ . '_Premium' ) ? '_Premium' : '' );

			return ! is_null( $self::$instance ) ? $self::$instance : $self::$instance = new $self();
		}

		/**
		 * YITH_WCBEP_Install constructor.
		 */
		protected function __construct() {
			add_action( 'init', array( $this, 'check_version' ), 5 );
			add_action( 'yith_wcbep_run_update_callback', array( $this, 'run_update_callback' ) );
		}

		/**
		 * Check the plugin version and run the updater is required.
		 * This check is done on all requests and runs if the versions do not match.
		 */
		public function check_version() {
			if ( ! defined( 'IFRAME_REQUEST' ) && version_compare( get_option( static::VERSION_OPTION, '1.0.0' ), YITH_WCBEP_VERSION, '<' ) ) {
				$this->install();
				do_action( 'yith_wcbep_updated' );
			}
		}

		/**
		 * Get list of DB update callbacks.
		 *
		 * @return array
		 */
		public function get_db_update_callbacks() {
			return $this->db_updates;
		}

		/**
		 * Install WC.
		 */
		public function install() {
			// Check if we are not already running this routine.
			if ( 'yes' === get_transient( 'yith_wcbep_installing' ) ) {
				return;
			}

			set_transient( 'yith_wcbep_installing', 'yes', MINUTE_IN_SECONDS * 10 );
			if ( ! defined( 'YITH_WCBEP_INSTALLING' ) ) {
				define( 'YITH_WCBEP_INSTALLING', true );
			}

			$this->update_version();
			$this->maybe_update_db_version();

			delete_transient( 'yith_wcbep_installing' );

			do_action( 'yith_wcbep_installed' );
		}

		/**
		 * Update version to current.
		 */
		protected function update_version() {
			delete_option( static::VERSION_OPTION );
			add_option( static::VERSION_OPTION, YITH_WCBEP_VERSION );
		}

		/**
		 * The DB needs to be updated?
		 *
		 * @return bool
		 */
		public function needs_db_update() {
			$current_db_version = get_option( static::DB_VERSION_OPTION, '1.0.0' );

			return ! is_null( $current_db_version ) && version_compare( $current_db_version, $this->get_greatest_db_version_in_updates(), '<' );
		}

		/**
		 * Update DB version to current.
		 *
		 * @param string|null $version New DB version or null.
		 */
		public static function update_db_version( $version = null ) {
			delete_option( static::DB_VERSION_OPTION );
			add_option( static::DB_VERSION_OPTION, is_null( $version ) ? YITH_WCBEP_VERSION : $version );

			// Delete "update scheduled" option to allow future update scheduling.
			delete_option( static::DB_UPDATE_SCHEDULED_OPTION );
		}

		/**
		 * Maybe update db
		 */
		protected function maybe_update_db_version() {
			if ( $this->needs_db_update() ) {
				$this->update();
			}
		}

		/**
		 * Push all needed DB updates to the queue for processing.
		 */
		protected function update() {
			$current_db_version   = get_option( static::DB_VERSION_OPTION, '1.0.0' );
			$greatest_version     = $this->get_greatest_db_version_in_updates();
			$is_already_scheduled = get_option( static::DB_UPDATE_SCHEDULED_OPTION, '' ) === $greatest_version;

			if ( ! $is_already_scheduled ) {
				foreach ( $this->get_db_update_callbacks() as $version => $update_callbacks ) {
					if ( version_compare( $current_db_version, $version, '<' ) ) {
						$loop = 0;

						foreach ( $update_callbacks as $update_callback ) {
							if ( $this->is_soon_callback( $update_callback ) ) {
								$this->run_update_callback( $update_callback );
							} else {
								$time = time() + $loop;
								WC()->queue()->schedule_single( $time, static::UPDATE_CALLBACK_HOOK, compact( 'update_callback' ), static::UPDATE_CALLBACK_GROUP );
								$loop++;
							}
						}
					}
				}
				update_option( static::DB_UPDATE_SCHEDULED_OPTION, $greatest_version );
			}
		}

		/**
		 * Run an update callback when triggered by ActionScheduler.
		 *
		 * @param string $callback Callback name.
		 */
		public function run_update_callback( $callback ) {
			include_once YITH_WCBEP_INCLUDES_PATH . '/functions.yith-wcbep-update.php';

			if ( is_callable( $callback ) ) {
				static::run_update_callback_start( $callback );
				$result = (bool) call_user_func( $callback );
				static::run_update_callback_end( $callback, $result );
			}
		}

		/**
		 * Triggered when a callback will run.
		 *
		 * @param string $callback Callback name.
		 */
		protected function run_update_callback_start( $callback ) {
			if ( ! defined( 'YITH_WCBEP_UPDATING' ) ) {
				define( 'YITH_WCBEP_UPDATING', true );
			}
		}

		/**
		 * Triggered when a callback has run.
		 *
		 * @param string $callback Callback name.
		 * @param bool   $result   Return value from callback. Non-false need to run again.
		 */
		protected function run_update_callback_end( $callback, $result ) {
			if ( $result ) {
				$update_callback = array(
					'update_callback' => $callback,
				);
				WC()->queue()->add( static::UPDATE_CALLBACK_HOOK, $update_callback, static::UPDATE_CALLBACK_GROUP );
			}
		}

		/**
		 * Retrieve the major version in update callbacks.
		 *
		 * @return string
		 */
		public function get_greatest_db_version_in_updates() {
			$update_versions = array_keys( $this->get_db_update_callbacks() );
			usort( $update_versions, 'version_compare' );

			return end( $update_versions );
		}

		/**
		 * Return true if the callback needs to be fired soon, instead of being scheduled.
		 *
		 * @param string $callback The callback name.
		 *
		 * @return bool
		 */
		private function is_soon_callback( $callback ) {
			return in_array( $callback, $this->soon_callbacks, true );
		}
	}
}

/**
 * Return the Install Class Instance
 *
 * @return YITH_WCBEP_Install
 */
function yith_wcbep_install_class() {
	return YITH_WCBEP_Install::get_instance();
}
