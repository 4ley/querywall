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
	 * Enqueue actions to build the admin pages.
	 *
	 * Calls all the needed actions to build any given admin page.
	 *
	 * @since 1.0.1
	 * @return array
	 */
	public function build_admin() {

		global $plugin_file;

		add_menu_page(
			__( 'Firewall Request Monitor', 'querywall' ),
			__( 'QueryWall', 'querywall' ),
			'manage_options',
			'querywall',
			array( __CLASS__, 'render_page' ),
			'dashicons-shield'
		);
	}

	/**
	 * Displays firewall logs table
	 *
	 * @since 1.0.1
	 * @return array
	 */
	public function render_page() {

		require( dirname( __FILE__ ) . '/class-qwall-monitor.php' );

		$fw_monitor = new QWall_Monitor();
		$fw_monitor->prepare_items();
		?>
			<style type="text/css">
			    .wp-list-table .column-date_time { width: 10%; }
			    .wp-list-table .column-date_time span { cursor: help; border-bottom: 1px dotted #aaa; }
			    .wp-list-table .column-ipv4 { width: 10%; }
			    .wp-list-table .column-filter_group { width: 10%; }
			    .wp-list-table .column-filter_input { width: 70%; }
				.wp-list-table .column-filter_input strong {
					padding: 0 2px;
					color: #333;
					border-radius: 2px;
					background-color: #ffff8c;
				}
				.qwall-subtitle { margin: 5px 0 0; color: #666; }
			</style>
			<div class="wrap">
				<h2><?php echo get_admin_page_title(); ?></h2>
				<p class="qwall-subtitle">Blocked requests will be shown in the list below.</p>
				<?php $fw_monitor->display(); ?>
			</div>
		<?php
	}

	/**
	 * Add rating link to plugin page.
	 *
	 * @since 1.0.1
	 * @return array
	 */
	public static function rate( $links, $file ) {
		if ( strpos( $file, 'querywall.php' ) !== false ) {
			$wp_url = 'https://wordpress.org/support/view/plugin-reviews/querywall?rate=5#postform';
			$fb_url = 'https://www.facebook.com/QueryWall-Plugn-Play-Firewall-474820996034299/';
			$links[] = '<a target="_blank" href="' . $wp_url . '" title="Rate and review QueryWall on WordPress.org">Rate this plugin</a>';
			$links[] = '<a target="_blank" href="' . $fb_url . '" title="Visit QueryWall on Facebook" style="padding:0 5px;color:#fff;vertical-align:middle;border-radius:2px;background:#f5c140;">Visit on Facebook</a>';
		}
		return $links;
	}
}

endif;