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
	 * Query filters
	 *
	 * @since 1.0.1
	 * @return void
	 */
	private static $filters = array(
		'REQUEST_URI'     => array( 'eval\(', 'UNION.*SELECT', '\(null\)', 'base64_', '\/localhost', '\%2Flocalhost', '\/pingserver', '\/config\.', '\/wwwroot', '\/makefile', 'crossdomain\.', 'proc\/self\/environ', 'etc\/passwd', '\/https\:', '\/http\:', '\/ftp\:', '\/cgi\/', '\.cgi', '\.exe', '\.sql', '\.ini', '\.dll', '\.asp', '\.jsp', '\/\.bash', '\/\.git', '\/\.svn', '\/\.tar', ' ', '\<', '\>', '\/\=', '\.\.\.', '\+\+\+', '\:\/\/', '\/&&', '\/Nt\.', '\;Nt\.', '\=Nt\.', '\,Nt\.', '\.exec\(', '\)\.html\(', '\{x\.html\(', '\(function\(' ),
		'QUERY_STRING'    => array( '\.\.\/', '127\.0\.0\.1', 'localhost', 'loopback', '\%0A', '\%0D', '\%00', '\%2e\%2e', 'input_file', 'execute', 'mosconfig', 'path\=\.', 'mod\=\.', 'wp-config\.php' ),
		'HTTP_USER_AGENT' => array( 'binlar', 'casper', 'cmswor', 'diavol', 'dotbot', 'finder', 'flicky', 'nutch', 'planet', 'purebot', 'pycurl', 'skygrid', 'sucker', 'turnit', 'vikspi', 'zmeu' )
	);

	/**
	 * Magic starts here
	 *
	 * @since 1.0.1
	 * @return void
	 */
	public static function init() {

		// Analyze request
		self::analyze('REQUEST_URI');
		self::analyze('QUERY_STRING');
		self::analyze('HTTP_USER_AGENT');
	}

	/**
	 * Analyze given server information
	 *
	 * @param  string  $var Filter group
	 *
	 * @since 1.0.1
	 * @return void
	 */
	private static function analyze( $var ) {

		if ( isset( $_SERVER[ $var ] ) && ! empty( $_SERVER[ $var ] ) && preg_match( '/' . implode( '|', self::$filters[ $var ] )  . '/i', $_SERVER[ $var ], $matches ) ) {
			self::log( $var, urldecode( $matches[0] ), urldecode( $_SERVER[ $var ] ) );
			self::close();
		}
	}

	/**
	 * You shall not pass
	 *
	 * @since 1.0.1
	 * @return void
	 */
	private static function close() {

		header('HTTP/1.1 403 Forbidden');
		header('Status: 403 Forbidden');
		header('Connection: Close');
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

		$wpdb->insert(
			$wpdb->base_prefix . 'qwall_monitor',
			array(
				'date_time'     => current_time( 'mysql' ),
				'date_time_gmt' => current_time( 'mysql', 1 ),
				'ipv4'          => sprintf( '%u', ip2long( $_SERVER['REMOTE_ADDR'] ) ),
				'agent'         => $_SERVER['HTTP_USER_AGENT'],
				'filter_group'  => $filter_group,
				'filter_match'  => $filter_match,
				'filter_input'  => $filter_input
			)
		);
	}
}

endif;