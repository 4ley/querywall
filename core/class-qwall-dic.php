<?php
/**
 * QueryWall Dependency Injection Container
 *
 * DIC class for QueryWall.
 *
 * @package QueryWall
 * @since   1.0.7
 */

defined( 'ABSPATH' ) or die( 'You shall not pass!' );

if ( ! class_exists( 'QWall_DIC' ) ):

class QWall_DIC {
	
	/**
	 * Objects
	 */
	private static $objects = array();

	/**
	 * Setter
	 * 
	 * @since 1.0.7
	 * @return void
	 */
	public static function set( $name, $value ) {

		if ( is_callable( $value ) ) {
			$value = $value();
		}
		
		self::$objects[ $name ] = $value;
	}

	/**
	 * Getter
	 * 
	 * @since 1.0.7
	 * @return object
	 */
	public static function get( $name ) {
		return self::$objects[ $name ];
	}
}

endif;