<?php
/*
 * Plugin Name: QueryWall: Plug'n Play Firewall
 * Plugin URI: https://wordpress.org/plugins/querywall/
 * Description: Autopilot protection for your WordPress against malicious URL requests. QueryWall analyzes queries automically to protect your site against malicious URL requests.
 * Tags: firewall, security, protect, block, antivirus, defender, malicious, request, query, blacklist, url, eval, base64, hack
 * Usage: No configuration needed, just activate it.
 * Version: 1.0.0
 * Author: 4ley
 * Author URI: https://github.com/4ley/querywall
 * Requires at least: 4.0
 * Tested up to: 4.3
 * Stable tag: trunk
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 */

defined( 'ABSPATH' ) or die( 'Hey! You! Use the frontdoor!' );

if ( ! class_exists('QueryWall') ):

class QueryWall {

	/**
	 * Query filters
	 *
	 * @static
	 * @access public
	 */
	private static $filters = array(
		'REQUEST_URI'     => array( 'eval\(', 'UNION.*SELECT', '\(null\)', 'base64_', '\/localhost', '\%2Flocalhost', '\/pingserver', '\/config\.', '\/wwwroot', '\/makefile', 'crossdomain\.', 'proc\/self\/environ', 'etc\/passwd', '\/https\:', '\/http\:', '\/ftp\:', '\/cgi\/', '\.cgi', '\.exe', '\.sql', '\.ini', '\.dll', '\.asp', '\.jsp', '\/\.bash', '\/\.git', '\/\.svn', '\/\.tar', ' ', '\<', '\>', '\/\=', '\.\.\.', '\+\+\+', '\:\/\/', '\/&&', '\/Nt\.', '\;Nt\.', '\=Nt\.', '\,Nt\.', '\.exec\(', '\)\.html\(', '\{x\.html\(', '\(function\(' ),
		'QUERY_STRING'    => array( '\.\.\/', '127\.0\.0\.1', 'localhost', 'loopback', '\%0A', '\%0D', '\%00', '\%2e\%2e', 'input_file', 'execute', 'mosconfig', 'path\=\.', 'mod\=\.', 'wp-config\.php' ),
		'HTTP_USER_AGENT' => array( 'binlar', 'casper', 'cmswor', 'diavol', 'dotbot', 'finder', 'flicky', 'nutch', 'planet', 'purebot', 'pycurl', 'skygrid', 'sucker', 'turnit', 'vikspi', 'zmeu' )
	);

	/**
	 * Magic starts here.
	 *
	 * @static
	 * @access public
	 */
	public static function init() {
		// Analyze request
		self::analyze('REQUEST_URI');
		self::analyze('QUERY_STRING');
		self::analyze('HTTP_USER_AGENT');
		// Setup hooks etc.
		self::setup();
	}

	/**
	 * Setup hooks etc.
	 *
	 * @static
	 * @access public
	 */
	public static function setup() {
		add_filter( 'plugin_row_meta', array( __CLASS__, 'rate' ), 10, 2 );
	}

	/**
	 * Analyze given server information.
	 *
	 * @static
	 * @access public
	 */
	private static function analyze( $var ) {
		if ( isset( $_SERVER[ $var ] ) && ! empty( $_SERVER[ $var ] ) && preg_match( '/' . implode( '|', self::$filters[ $var ] )  . '/i', $_SERVER[ $var ] ) ) {
			self::close();
		}
	}

	/**
	 * Exit wordpress when a badass queries server.
	 *
	 * @static
	 * @access public
	 */
	private static function close() {
		header('HTTP/1.1 403 Forbidden');
		header('Status: 403 Forbidden');
		header('Connection: Close');
		exit;
	}

	/**
	 * Add rating link to plugin page.
	 *
	 * @static
	 * @access public
	 */
	public static function rate( $links, $file ) {
		if ( plugin_basename( __FILE__ ) == $file ) {
			$url = 'http://wordpress.org/support/view/plugin-reviews/' . basename( dirname( __FILE__ ) ) . '?rate=5#postform';
			$links[] = '<a target="_blank" href="' . $url . '" title="Click here to rate and review this plugin on WordPress.org">Rate this plugin</a>';
		}
		return $links;
	}
}

QueryWall::init();

endif;