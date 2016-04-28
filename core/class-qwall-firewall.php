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
		'REQUEST_URI'     => array( 'eval\(', 'UNION.*SELECT', 'GROUP_CONCAT', 'CONCAT\s*\(', '\(null\)', 'base64_', '\/localhost', '\%2Flocalhost', '\/pingserver', '\/config\.', '\/wwwroot', '\/makefile', 'crossdomain\.', 'proc\/self\/environ', 'etc\/passwd', '\/https\:', '\/http\:', '\/ftp\:', '\/cgi\/', '\.cgi', '\.exe', '\.sql', '\.ini', '\.dll', '\.asp', '\.jsp', '\/\.bash', '\/\.git', '\/\.svn', '\/\.tar', ' ', '\<', '\>', '\/\=', '\.\.\.', '\+\+\+', '\:\/\/', '\/&&', '\/Nt\.', '\;Nt\.', '\=Nt\.', '\,Nt\.', '\.exec\(', '\)\.html\(', '\{x\.html\(', '\(function\(' ),
		'QUERY_STRING'    => array( '\.\.\/', '127\.0\.0\.1', 'localhost', 'loopback', '\%0A', '\%0D', '\%00', '\%2e\%2e', 'input_file', 'execute', 'mosconfig', 'path\=\.', 'mod\=\.', 'wp-config\.php' ),
		'HTTP_USER_AGENT' => array( 'binlar', 'casper', 'cmswor', 'diavol', 'dotbot', 'finder', 'flicky', 'nutch', 'planet', 'purebot', 'pycurl', 'skygrid', 'sucker', 'turnit', 'vikspi', 'zmeu' ),
		'FILES'           => array( '\.dll$', '\.rb$', '\.py$', '\.exe$', '\.php[3-6]?$', '\.pl$', '\.perl$', '\.ph[34]$', '\.phl$', '\.phtml$', '\.phtm$' ),

	);

	/**
	 * Magic starts here.
	 *
	 * All custom functionality will be hooked into the "plugins_loaded" action.
	 *
	 * @since 1.0.7
	 * @return void
	 */
	public function __construct() {
		add_action( 'plugins_loaded', array( $this, 'plugins_loaded' ) );
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

		// Analyze server variable
		self::analyze_server( 'REQUEST_URI' );
		self::analyze_server( 'QUERY_STRING' );
		self::analyze_server( 'HTTP_USER_AGENT' );
		// Analyze files variable
		self::analyze_files( 'FILES' );
	}

	/**
	 * Analyze given server information
	 *
	 * @param  string  $var Filter group
	 *
	 * @since 1.0.1
	 * @return void
	 */
	private static function analyze_server( $var ) {

		if ( isset( $_SERVER[ $var ] ) && ! empty( $_SERVER[ $var ] ) && preg_match( '/' . implode( '|', self::$filters[ $var ] )  . '/i', $_SERVER[ $var ], $matches ) ) {
			self::log( $var, urldecode( $matches[0] ), urldecode( $_SERVER[ $var ] ) );
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
	private static function analyze_files( $var ) {

		if ( isset( $_FILES ) && ! empty( $_FILES ) ) {
			foreach ( $_FILES as $file ) {
				$names = ( is_array( $file[ 'name' ] ) ? $file[ 'name' ] : array( $file[ 'name' ] ) );
				foreach( $names as $name ) {
					if ( preg_match( '/' . implode( '|', self::$filters[ $var ] )  . '/i', $name, $matches ) ) {
						self::log( $var, $matches[0], $name );
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