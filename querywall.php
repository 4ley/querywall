<?php
/*
 * Plugin Name: QueryWall: Plug'n Play Firewall
 * Plugin URI: https://wordpress.org/plugins/querywall/
 * Description: Autopilot protection for your WordPress against malicious URL requests. QueryWall analyzes queries automically to protect your site against malicious URL requests.
 * Tags: Tags: firewall, security, protect, block, antivirus, defender, malicious, request, query, blacklist, url, eval, base64, hack, attack, brute force, infection, injection, malware, prevention, protection, trojan, virus, xss, waf
 * Usage: No configuration needed, just activate it.
 * Version: 1.0.3
 * Author: 4ley
 * Author URI: https://github.com/4ley/querywall
 * Requires at least: 3.1
 * Tested up to: 4.3
 * Stable tag: trunk
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 */

defined( 'ABSPATH' ) or die( 'You shall not pass!' );

require_once( dirname( __FILE__ ) . '/core/class-qwall-core.php' );
QWall_Core::init( __FILE__ );
