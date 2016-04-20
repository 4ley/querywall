<?php
/**
 * QueryWall Util
 *
 * Util class for QueryWall.
 *
 * @package QueryWall
 * @since   1.0.5
 */

defined( 'ABSPATH' ) or die( 'You shall not pass!' );

if ( ! class_exists( 'QWall_Util' ) ):

class QWall_Util {

	/**
	 * Unschedule an event by hook name
	 *
	 * @see https://core.trac.wordpress.org/ticket/18997#comment:23
	 * @since 1.0.5
	 * @return void
	 */
	public static function unschedule_event( $hook ) {

		$crons = _get_cron_array();

		if ( empty( $crons ) ) {
			return;
		}

		foreach( $crons as $timestamp => $cron ) {

			if ( ! empty( $cron[ $hook ] ) )  {
				unset( $crons[ $timestamp ][ $hook ] );
			}

			if ( empty( $crons[ $timestamp ] ) ) {
				unset( $crons[ $timestamp ] );
			}
		}

		_set_cron_array( $crons );
	}
}

endif;