<?php
/**
 * QueryWall Setup
 *
 * Plugin activation, deactivation, upgrade and uninstall
 *
 * @package QueryWall
 * @since   1.0.1
 */

defined( 'ABSPATH' ) or die( 'You shall not pass!' );

if ( ! class_exists( 'QWall_Setup' ) ):

class QWall_Setup {

	/**
	 * Plugin activate
	 *
	 * @since 1.0.1
	 * @return void
	 */
	public static function on_activate() {
		self::create_database_tables();
	}

	/**
	 * Plugin deactivate
	 *
	 * @since 1.0.1
	 * @return void
	 */
	public static function on_deactivate() {
	}

	/**
	 * Plugin uninstall
	 *
	 * @since 1.0.1
	 * @return void
	 */
	public static function on_uninstall() {

		self::remove_options();
		self::remove_database_tables();
		self::remove_scheduled_events();
	}

	/**
	 * Foreign plugin activated
	 *
	 * @since 1.0.1
	 * @return void
	 */
	public static function on_activated_plugin() {
		self::load_plugin_first();
	}

	/**
	 * Creates appropriate database tables
	 *
	 * Uses dbDelta to create database tables completely or if one is missing.
	 *
	 * @since 1.0.1
	 * @return void
	 */
	private static function create_database_tables() {

		global $wpdb;
		
		$charset_collate = '';
		
		if ( ! empty( $wpdb->charset ) ) {
			$charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
		}
		
		if ( ! empty( $wpdb->collate ) ) {
			$charset_collate .= " COLLATE $wpdb->collate";
		}

		$tables = "CREATE TABLE " . $wpdb->prefix . "qwall_monitor (
			id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			date_time DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
			date_time_gmt DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
			ipv4 INT UNSIGNED NOT NULL,
			agent VARCHAR(255) NOT NULL,
			filter_group VARCHAR(100) NOT NULL,
			filter_match VARCHAR(255) NOT NULL,
			filter_input TEXT NOT NULL,
			PRIMARY KEY (id),
			KEY date_time_gmt (date_time_gmt)
		) " . $charset_collate . ";";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $tables );
	}

	/**
	 * Remove database tables
	 *
	 * @since 1.0.1
	 * @return void
	 */
	private static function remove_database_tables() {

		global $wpdb;

		$wpdb->query( "DROP TABLE IF EXISTS " . $wpdb->base_prefix . "qwall_monitor;" );
	}

	/**
	 * Unschedule all events
	 *
	 * @since 1.0.5
	 * @return void
	 */
	private static function remove_scheduled_events() {

		QWall_Util::unschedule_event( 'qwall_purge_logs' );
	}

	/**
	 * Remove plugin options
	 *
	 * @since 1.0.7
	 * @return void
	 */
	private static function remove_options() {

		QWall_DIC::get( 'settings' )->delete( 'settings' );
	}

	/**
	 * Make sure plugin loads first
	 *
	 * @since 1.0.3
	 * @return void
	 */
	private static function load_plugin_first() {

		if ( $plugins = get_option( 'active_plugins' ) ) {
			$basename = plugin_basename( QWall_Core::$settings['plugin_file'] );
			if ( $key = array_search( $basename, $plugins ) ) {
				array_splice( $plugins, $key, 1 );
				array_unshift( $plugins, $basename );
				update_option( 'active_plugins', $plugins );
			}
		}
	}
}

endif;