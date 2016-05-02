<?php
/**
 * QueryWall Firewall
 *
 * Firewall class for QueryWall.
 *
 * @package QueryWall
 * @since   1.0.1
 */

defined( 'ABSPATH' ) or die( 'You shall not pass!' );

if ( ! class_exists( 'QWall_Firewall' ) ):

class QWall_Firewall {

	/**
	 * Magic starts here.
	 *
	 * All custom functionality will be hooked into the "plugins_loaded" action.
	 *
	 * @since 1.0.7
	 * @return void
	 */
	public function __construct() {
		add_action( 'plugins_loaded', array( $this, 'plugins_loaded' ), 1 );
	}

	/**
	 * Conditionally hook into WordPress.
	 *
	 * @since 1.0.7
	 * @return void
	 */
	public function plugins_loaded() {

		if ( is_user_logged_in() && QWall_DIC::get( 'settings' )->get( 'settings', 'disable_loggedin_users' ) ) {
			return null;
		}

		self::analyze();
	}

	/**
	 * Analyze request
	 *
	 * @since 1.0.7
	 * @return void
	 */
	private static function analyze() {

		$attack_vectors = QWall_DIC::get( 'firewall_rules' )->get_attack_vectors();

		foreach ( $attack_vectors as $idx => $vector ) {

			$pattern = array_merge( $vector['default_pattern'], $vector['custom_pattern'] );

			if ( 'files' == $idx ) {
				// Analyze files variable
				self::analyze_files( $vector['name'], $pattern );
			} else {
				// Analyze server variable
				self::analyze_server( $vector['name'], $pattern );
			}
		}
	}

	/**
	 * Analyze given server information
	 *
	 * @param  string  $var Filter group
	 *
	 * @since 1.0.1
	 * @return void
	 */
	private static function analyze_server( $name, $pattern ) {

		if ( isset( $_SERVER[ $name ] ) && ! empty( $_SERVER[ $name ] ) && ! empty( $pattern ) && preg_match( '/' . implode( '|', $pattern )  . '/is', $_SERVER[ $name ], $matches ) ) {
			self::log( $name, urldecode( $matches[0] ), urldecode( $_SERVER[ $name ] ) );
			self::close();
		}
	}

	/**
	 * Analyze given files information
	 *
	 * @param  string  $var Filter group
	 *
	 * @since 1.0.2
	 * @return void
	 */
	private static function analyze_files( $name, $pattern ) {

		if ( isset( $_FILES ) && ! empty( $_FILES ) && ! empty( $pattern ) ) {
			foreach ( $_FILES as $file ) {
				$names = ( is_array( $file[ 'name' ] ) ? $file[ 'name' ] : array( $file[ 'name' ] ) );
				foreach( $names as $file_name ) {
					if ( preg_match( '/' . implode( '|', $pattern )  . '/is', $file_name, $matches ) ) {
						self::log( $name, $matches[0], $file_name );
						self::close();
					}
				}
			}
		}
	}

	/**
	 * You shall not pass!
	 *
	 * @since 1.0.1
	 * @return void
	 */
	private static function close() {

		$qwall_settings   = QWall_DIC::get( 'settings' );
		$redirect_url     = $qwall_settings->get( 'settings', 'redirect_url' );
		$http_status_code = $qwall_settings->get( 'settings', 'http_status_code' );
		$server_response  = $qwall_settings->get( 'settings', 'server_response' );

		if ( empty( $redirect_url ) ) {

			if( ! isset( $_SERVER['SERVER_PROTOCOL'] ) || empty( $_SERVER['SERVER_PROTOCOL'] ) ) {
				$_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.1';
			}

			$http_status_code_message = QWall_DIC::get( 'settings' )->get_http_status_codes( $http_status_code );

			header( $_SERVER['SERVER_PROTOCOL'] . ' ' . $http_status_code_message, true, $http_status_code);
			header( 'Connection: Close' );
		} else {
			header('Location: ' . $redirect_url, true, $http_status_code);
		}

		if( ! empty( $server_response ) ) {
			exit( $server_response );
		}

		exit;
	}

	/**
	 * Log request
	 *
	 * @param  string  $filter_group  Filter group
	 * @param  string  $filter_match  Filter match
	 * @param  string  $filter_input  Filter input
	 *
	 * @since 1.0.1
	 * @return void
	 */
	private static function log( $filter_group, $filter_match, $filter_input ) {
				
		global $wpdb;

		if( isset( $_SERVER['HTTP_USER_AGENT'] ) ) {
			$user_agent = $_SERVER['HTTP_USER_AGENT'];
		} else {
			$user_agent = '';
		}

		if ( QWall_DIC::get( 'settings' )->get( 'settings', 'anonymize_ip' ) ) {
			$ipv4 = long2ip( ip2long( $_SERVER['REMOTE_ADDR'] ) & 0xFFFFFF00 );
		} else {
			$ipv4 = $_SERVER['REMOTE_ADDR'];
		}

		$wpdb->insert(
			$wpdb->base_prefix . 'qwall_monitor',
			array(
				'date_time'     => current_time( 'mysql' ),
				'date_time_gmt' => current_time( 'mysql', 1 ),
				'ipv4'          => sprintf( '%u', ip2long( $ipv4 ) ),
				'agent'         => $user_agent,
				'filter_group'  => $filter_group,
				'filter_match'  => $filter_match,
				'filter_input'  => $filter_input
			)
		);
	}
}

QWall_DIC::set( 'firewall', new QWall_Firewall() );

endif;