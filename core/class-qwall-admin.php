<?php
/**
 * QueryWall Admin
 *
 * Admin class for QueryWall.
 *
 * @package QueryWall
 * @since   1.0.1
 */

defined( 'ABSPATH' ) or die( 'You shall not pass!' );

if ( ! class_exists( 'QWall_Admin' ) ):

class QWall_Admin {

	/**
	 * Magic starts here.
	 *
	 * All custom functionality will be hooked into the "init" action.
	 *
	 * @since 1.0.7
	 * @return void
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'init' ), 30 );
	}

	/**
	 * Conditionally hook into WordPress.
	 *
	 * @since 1.0.7
	 * @return void
	 */
	public function init() {

		add_action( 'admin_menu', array( $this, 'cb_admin_menu' ) );
		add_filter( 'plugin_row_meta', array( $this, 'cb_plugin_meta' ), 10, 2 );
	}

	/**
	 * Enqueue actions to build the admin menu.
	 *
	 * Calls all the needed actions to build the admin menu.
	 *
	 * @since 1.0.1
	 * @return void
	 */
	public function cb_admin_menu() {

		// add_menu_page( $page_title, $menu_title, $capability, $menu_slug, $function, $icon_url, $position );
		add_menu_page(
			__( 'Firewall Request Monitor', 'querywall' ),
			__( 'QueryWall', 'querywall' ),
			'manage_options',
			'querywall',
			'',
			'dashicons-shield'
		);
	}

	/**
	 * Add rating link to plugin page.
	 *
	 * @since 1.0.7
	 * @return array
	 */
	public function cb_plugin_meta( $links, $file ) {

		if ( strpos( $file, 'querywall.php' ) !== false ) {
			// style="padding:0 2px;color:#fff;vertical-align:middle;border-radius:2px;background:#00b9eb;"
			$links[] = '<a target="_blank" href="https://wordpress.org/support/view/plugin-reviews/querywall?rate=5#postform" title="Rate and review QueryWall on WordPress.org">Rate on WordPress.org</a>';
			$links[] = '<a target="_blank" href="https://github.com/4ley/querywall" title="Contribute to QueryWall on GitHub">Contribute on GitHub</a>';
			$links[] = '<a target="_blank" href="https://www.facebook.com/querywall" title="Visit QueryWall on Facebook">Visit on Facebook</a>';
		}

		return $links;
	}
}

QWall_DIC::set( 'admin', new QWall_Admin() );

endif;