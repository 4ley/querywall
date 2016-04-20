<?php
/**
 * QueryWall Core
 *
 * Core class for QueryWall.
 *
 * @package QueryWall
 * @since   1.0.1
 */

defined( 'ABSPATH' ) or die( 'You shall not pass!' );

if ( ! class_exists( 'QWall_Core' ) ):

class QWall_Core {

	/**
	 * Plugin settings.
	 */
	public static $settings;

	/**
	 * Magic starts here.
	 *
	 * @param  string  $plugin_file File path
	 *
	 * @since 1.0.1
	 * @return void
	 */
	public static function init( $plugin_file ) {

		$dirname = dirname( __FILE__ );

		self::$settings = array(
			'plugin_file' => $plugin_file,

		);

		require_once( $dirname . '/class-qwall-firewall.php' );
		QWall_Firewall::init();

		if ( is_admin() ) {
			self::admin_init();
		}
	}

	/**
	 * Admin magic starts here.
	 *
	 * @since 1.0.5
	 * @return void
	 */
	public static function admin_init() {

		$dirname = dirname( self::$settings['plugin_file'] );

		require_once( $dirname . '/core/class-qwall-util.php' );
		require_once( $dirname . '/core/class-qwall-setup.php' );
		require_once( $dirname . '/core/class-qwall-admin.php' );
		register_activation_hook( self::$settings['plugin_file'], array( 'QWall_Setup', 'on_activate' ) );
		register_deactivation_hook( self::$settings['plugin_file'], array( 'QWall_Setup', 'on_deactivate' ) );
		register_uninstall_hook( self::$settings['plugin_file'], array( 'QWall_Setup', 'on_uninstall' ) );
		add_action( 'activated_plugin', array( 'QWall_Setup', 'on_activated_plugin' ) );
		add_action( 'admin_menu', array( 'QWall_Admin', 'build_admin' ) );
		add_filter( 'plugin_row_meta', array( 'QWall_Admin', 'rate' ), 10, 2 );
		add_action( 'qwall_purge_logs', array( 'QWall_Admin', 'purge_logs' ) );

		if ( isset( $_POST['qwall_purge_logs_now'] ) ) {
			
			require_once( ABSPATH . 'wp-includes/pluggable.php' );
			
			if ( wp_verify_nonce( $_POST['qwall_purge_logs_nonce'], 'qwall_purge_logs' ) ) {
				
				do_action( 'qwall_purge_logs', ( int ) $_POST['qwall_purge_logs_older_than'] );
				add_action( 'admin_notices', array( 'QWall_Admin', 'render_admin_notice' ) );
			}
		}

		if ( isset( $_POST['qwall_purge_logs_daily'] ) || isset( $_POST['qwall_purge_logs_unschedule'] ) ) {
			
			require_once( ABSPATH . 'wp-includes/pluggable.php' );
			
			if ( wp_verify_nonce( $_POST['qwall_purge_logs_nonce'], 'qwall_purge_logs' ) ) {

				QWall_Util::unschedule_event( 'qwall_purge_logs' );

				if( isset( $_POST['qwall_purge_logs_daily'] ) ) {
					wp_schedule_event( current_time( 'timestamp' ), 'daily', 'qwall_purge_logs', ( int ) $_POST['qwall_purge_logs_older_than'] );
				}

				add_action( 'admin_notices', array( 'QWall_Admin', 'render_admin_notice' ) );
			}
		}
	}
}

endif;