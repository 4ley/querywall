<?php
/**
 * QueryWall Request Monitor List Table
 *
 * Firewall Log class for QueryWall.
 *
 * @package QueryWall
 * @since   1.0.1
 */

defined( 'ABSPATH' ) or die( 'You shall not pass!' );

if ( ! class_exists( 'QWall_Monitor_List_Table' ) ):

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class QWall_Monitor_List_Table extends WP_List_Table {

	function __construct() {

		parent::__construct(
			array(
				'plural'   => 'qwall_monitor_item',
				'singular' => 'qwall_monitor_items',
				'ajax'     => false
			)
		);
	}

	/**
	 * Define columns
	 *
	 * @since 1.0.1
	 * @return array list of column titles
	 */
	public function get_columns() {

		return array(
			'date_time'    => __( 'Time', 'querywall' ),
			'ipv4'         => __( 'IP', 'querywall' ),
			'filter_group' => __( 'Filter', 'querywall' ),
			'filter_input' => __( 'Request', 'querywall' )
		);
	}

	/**
	 * Define which columns are hidden
	 *
	 * @since 1.0.1
	 * @return array
	 */
	public function get_hidden_columns() {
		return array();
	}

	/**
	 * Define the sortable columns
	 *
	 * @since 1.0.1
	 * @return array
	 */
	public function get_sortable_columns() {

		return array(
			'date_time' => array( 'date_time_gmt', false ),

		);
	}

	/**
	 * Define what data to show on each column of the table
	 *
	 * @since 1.0.1
	 * @param  array  $item Item data
	 * @param  string $column_name Column name
	 *
	 * @return string
	 */
	public function column_default( $item, $column_name ) {

		switch( $column_name ) {
			case 'date_time':
				return '<span title="' . $item['date_time'] . '">' . human_time_diff( $item['time_stamp'], current_time( 'timestamp' ) ) . ' ago';
			case 'ipv4':

				$ipv4 = long2ip( $item[ $column_name ] );
				
				if ( substr( $ipv4, -2 ) == '.0' ) {
					$ipv4 = substr_replace( $ipv4, '***', -1 );
				}

				return $ipv4;

			case 'filter_input':
				return preg_replace( '/' . preg_quote( $item['filter_match'], '/' ) . '/i', '<strong>\\0</strong>', $item['filter_input'] );
			default:
				return $item[ $column_name ];
		}
	}

	/**
	 * Retrieves table data
	 *
	 * @since 1.0.1
	 * @param  integer $count
	 * @param  string  $order
	 * @param  string  $orderby
	 * @param  integer $offset
	 * @param  integer $limit
	 *
	 * @return string
	 */
	public function get_table_data( &$count, $order = 'desc', $orderby = 'date_time',  $offset = 0, $limit = 20 ) {

		global $wpdb;

		$extra_sql = "ORDER BY " . esc_sql( $orderby ) . " " . esc_sql( $order );

		$count = $wpdb->get_var( "SELECT COUNT(*) FROM `" . $wpdb->base_prefix . "qwall_monitor` " . $extra_sql . ";" );
		$items = $wpdb->get_results( "SELECT date_time, UNIX_TIMESTAMP(date_time) AS time_stamp, ipv4, filter_group, filter_match, filter_input FROM `" . $wpdb->base_prefix . "qwall_monitor` " . $extra_sql . " LIMIT " . absint( $offset ) . ", " . absint( $limit ) . ";", ARRAY_A );

		return $items;
	}

	/**
	 * Prepare data for display
	 *
	 * @since 1.0.1
	 * @return void
	 */
	public function prepare_items() {
 
		$columns  = $this->get_columns();
		$hidden   = $this->get_hidden_columns();
		$sortable = $this->get_sortable_columns();

		$this->_column_headers = array( $columns, $hidden, $sortable );

		$per_page = 20;

		$this->items = $this->get_table_data(
			$total_items,
			( ! empty( $_GET['order'] )   ? $_GET['order']   : 'desc' ),
			( ! empty( $_GET['orderby'] ) ? $_GET['orderby'] : 'date_time' ),
			( ( $this->get_pagenum() - 1 ) * $per_page ),
			$per_page
		);

		$this->set_pagination_args( array(
			'total_items' => $total_items,
			'per_page'    => $per_page,
			'total_pages' => ceil( $total_items / $per_page ),

		) );
	}
}

endif;