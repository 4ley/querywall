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
			require_once( $dirname . '/class-qwall-setup.php' );
			require_once( $dirname . '/class-qwall-admin.php' );
			register_activation_hook( $plugin_file, array( 'QWall_Setup', 'on_activate' ) );
			register_deactivation_hook( $plugin_file, array( 'QWall_Setup', 'on_deactivate' ) );
			register_uninstall_hook( $plugin_file, array( 'QWall_Setup', 'on_uninstall' ) );
			add_action( 'activated_plugin', array( 'QWall_Setup', 'on_activated_plugin' ) );
			add_action( 'admin_menu', array( 'QWall_Admin', 'build_admin' ) );
			add_filter( 'plugin_row_meta', array( 'QWall_Admin', 'rate' ), 10, 2 );
		}
	}
}

endif;