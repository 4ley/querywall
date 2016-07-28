<?php
/*
 * Plugin Name: QueryWall: Plug'n Play Firewall
 * Plugin URI: https://wordpress.org/plugins/querywall/
 * Description: Autopilot protection for your WordPress against malicious URL requests. QueryWall analyzes queries automically to protect your site against malicious URL requests.
 * Tags: firewall, security, protect, block, antivirus, defender, malicious, request, query, blacklist, url, eval, base64, hack, attack, brute force, infection, injection, malware, botnet, backdoor, web application firewall, xss, website security, wordpress security, secure, prevention, protection, trojan, virus, xss, waf, security audit, querywall, bbq, block bad queries, ninjafirewall, wordfence, bulletproof security, ithemes security, better wp security, sucuri, vaultpress, simple firewall
 * Usage: No configuration needed, just activate it.
 * Version: 1.1.0
 * Author: 4ley
 * Author URI: https://github.com/4ley/querywall
 * Requires at least: 3.1
 * Tested up to: 4.6
 * Stable tag: trunk
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 */

defined( 'ABSPATH' ) or die( 'You shall not pass!' );

require_once( dirname( __FILE__ ) . '/core/class-qwall-core.php' );
QWall_Core::init( __FILE__ );
