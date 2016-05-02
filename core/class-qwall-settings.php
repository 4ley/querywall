<?php
/**
 * QueryWall Settings
 *
 * Settings class for QueryWall.
 *
 * @package QueryWall
 * @since   1.0.7
 */

defined( 'ABSPATH' ) or die( 'You shall not pass!' );

if ( ! class_exists( 'QWall_Settings' ) ):

class QWall_Settings {

	/**
	 * Option settings
	 *
	 * @since 1.0.7
	 */
	private $settings;

	/**
	 * Default option settings
	 *
	 * @since 1.0.7
	 */
	private $default_settings = array(
		'qwall_settings' => array(
			'disable_loggedin_users' => false,
			'anonymize_ip'           => false,
			'http_status_code'       => 403,
			'server_response'        => '<h1>403 Forbidden</h1>',

		),

	);

	/**
	 * HTTP status codes
	 *
	 * @since 1.0.7
	 */
	private $http_status_codes = array(
		301 => '301 Moved Permanently',
		302 => '302 Found',
		303 => '303 See Other',
		400 => '400 Bad Request',
		401 => '401 Unauthorized',
		403 => '403 Forbidden',
		404 => '404 Not Found',
		410 => '410 Gone',
		418 => '418 I’m a teapot',
		420 => '420 Policy Not Fulfilled',
		444 => '444 No Response',
		503 => '503 Service Unavailable',

	);

	/**
	 * Magic starts here.
	 *
	 * All custom functionality will be hooked into the "init" action.
	 *
	 * @since 1.0.7
	 * @return void
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'init' ), 40 );
	}

	/**
	 * Conditionally hook into WordPress.
	 *
	 * @since 1.0.7
	 * @return void
	 */
	public function init() {
		
		add_action( 'admin_init', array( $this, 'cb_admin_init' ) );
		add_action( 'admin_menu', array( $this, 'cb_admin_menu' ) );
	}

	/**
	 * Enqueue actions to build the admin menu.
	 *
	 * Calls all the needed actions to build the admin menu.
	 *
	 * @since 1.0.7
	 * @return void
	 */
	public function cb_admin_menu() {

		// add_submenu_page( $parent_slug, $page_title, $menu_title, $capability, $menu_slug, $function );
		add_submenu_page(
			'querywall',
			__( 'Settings', 'querywall' ),
			__( 'Settings', 'querywall' ),
			'manage_options',
			'querywall-settings',
			array( $this, 'display_settings_page' )
		);
	}

	/**
	 * Enqueue actions to build the settings page.
	 *
	 * Calls all the needed actions to build the settings page.
	 *
	 * @since 1.0.7
	 * @return void
	 */
	public function cb_admin_init() {

		// register_setting( $option_group, $option_name, $sanitize_callback );
		register_setting(
			'qwall_settings',
			'qwall_settings',
			array( $this, 'sanitize_settings' )
		);

		// add_settings_section( $id, $title, $callback, $page );
		add_settings_section(
			'qwall_settings',
			'',
			array( $this, 'display_general_section' ),
			'querywall-settings'
		);

		// add_settings_field( $id, $title, $callback, $page, $section, $args );
		add_settings_field(
			'disable_loggedin_users',
			'Logged in users',
			array( $this, 'display_settings_option_checkbox' ),
			'querywall-settings',
			'qwall_settings',
			array(
				'name'  => 'disable_loggedin_users',
				'value' => $this->get( 'settings', 'disable_loggedin_users' ),
				'label' => __( 'Disable for logged in users', 'querywall' ),

			)
		);

		add_settings_field(
			'anonymize_ip',
			'IP anonymization',
			array( $this, 'display_settings_option_checkbox' ),
			'querywall-settings',
			'qwall_settings',
			array(
				'name'  => 'anonymize_ip',
				'value' => $this->get( 'settings', 'anonymize_ip' ),
				'label' => __( 'Anonymize IP addresses (Sets the last octet of IPv4 to zero)', 'querywall' ),

			)
		);

		add_settings_field(
			'http_status_code',
			'Server status code',
			array( $this, 'display_settings_option_http_status_codes' ),
			'querywall-settings',
			'qwall_settings',
			array(
				'name'  => 'http_status_code',
				'value' => $this->get( 'settings', 'http_status_code' ),

			)
		);

		add_settings_field(
			'redirect_url',
			'Redirect URL',
			array( $this, 'display_settings_option_input' ),
			'querywall-settings',
			'qwall_settings',
			array(
				'name'  => 'redirect_url',
				'value' => $this->get( 'settings', 'redirect_url' ),
				'label' => __( 'e.g. https://www.example.org/ (You have to use a redirection status code starting with 3**)', 'querywall' ),

			)
		);

		add_settings_field(
			'server_response',
			'Server response',
			array( $this, 'display_settings_option_textarea' ),
			'querywall-settings',
			'qwall_settings',
			array(
				'name'  => 'server_response',
				'value' => $this->get( 'settings', 'server_response' ),
				'label' => __( 'You shall not pass! (Leave blank for an empty response)', 'querywall' ),

			)
		);
	}

	/**
	 * Displays settings page
	 *
	 * @since 1.0.7
	 * @return void
	 */
	public function display_settings_page() {
		?>
		<div class="wrap">
			<h1><?php _e( 'QueryWall Settings', 'querywall' ); ?></h1>
			<?php settings_errors(); ?>
			<form method="post" action="options.php">
				<?php
				settings_fields( 'qwall_settings' );
				do_settings_sections( 'querywall-settings' );
				submit_button();
				?>
			</form>
		</div>
		<?php
	}

	/**
	 * Displays general section
	 *
	 * @since 1.0.7
	 * @return void
	 */
	public function display_general_section() {
		?>
		<p><?php _e( 'Configure QueryWall to fit your needs.', 'querywall' ); ?></p>
		<?php
	}

	/**
	 * Displays settings option
	 *
	 * @since 1.0.7
	 * @return void
	 */
	public function display_settings_option_input( $args ) {
		?>
		<input class="regular-text" type="text" size="40" name="qwall_settings[<?php echo $args['name']; ?>]" value="<?php echo $args['value']; ?>">
		<p class="description"><?php echo $args['label']; ?></p>
		<?php
	}

	/**
	 * Displays settings option
	 *
	 * @since 1.0.7
	 * @return void
	 */
	public function display_settings_option_textarea( $args ) {
		?>
		<textarea rows="3" cols="50" name="qwall_settings[<?php echo $args['name']; ?>]"><?php echo $args['value']; ?></textarea>
		<p class="description"><?php echo $args['label']; ?></p>
		<?php
	}

	/**
	 * Displays settings option
	 *
	 * @since 1.0.7
	 * @return void
	 */
	public function display_settings_option_checkbox( $args ) {
		?>
		<input type="checkbox" id="<?php echo $args['name']; ?>" name="qwall_settings[<?php echo $args['name']; ?>]" value="true" <?php checked( true, $args['value'] ); ?>>
		<label class="inline-block" for="<?php echo $args['name']; ?>"><?php echo $args['label']; ?></label>
		<?php
	}

	/**
	 * Displays HTTP status code option
	 *
	 * @since 1.0.7
	 * @return void
	 */
	public function display_settings_option_http_status_codes( $args ) {

		$status_codes = $this->get_http_status_codes();
		?>
		<select name="qwall_settings[<?php echo $args['name']; ?>]">
			<?php foreach ( $status_codes as $code => $message ) { ?>
				<option value="<?php echo $code; ?>" <?php selected( $code, $args['value'] ); ?>><?php echo $message; ?></option>
			<?php } ?>
		</select>
		<?php
	}

	/**
	 * Available HTTP status codes.
	 *
	 * @since 1.0.7
	 * @return string|array
	 */
	public function get_http_status_codes( $code = null ) {

		$codes = apply_filters( 'querywall_http_status_codes', $this->http_status_codes );

		if ( null !== $code ) {

			if ( ! isset( $codes[ $code ] ) ) {
				return null;
			}

			return $codes[ $code ];
		}

		return $codes;
	}

	/**
	 * Validate options from the settings page.
	 *
	 * @since 1.0.7
	 * @return array
	 */
	public function sanitize_settings( $settings ) {

		$settings['anonymize_ip']           = isset( $settings['anonymize_ip'] );
		$settings['disable_loggedin_users'] = isset( $settings['disable_loggedin_users'] );

		if( isset( $settings['server_response'] ) ) {
			$settings['server_response'] = wp_kses( stripslashes_deep( $settings['server_response'] ), wp_kses_allowed_html( 'post' ) );
		}

		if( isset( $settings['redirect_url'] ) ) {
			$settings['redirect_url'] = esc_url( $settings['redirect_url'] );
		}

		if ( null === $this->get_http_status_codes( $settings['http_status_code'] ) ) {
			unset( $settings['http_status_code'] );
		}

		return $settings;
	}

	/*public function cb_display_general_settings() {
		
		$options = array(
			'title'             => 'Optionen',
			'settings_fields'   => 'qwall_general_settings_group',
			'settings_sections' => 'qwall-general-options',

		);

		echo $this->get_view( 'backend/settings', $options );
	}*/

	/**
	 * Get option settings.
	 *
	 * @since 1.0.7
	 * @return array
	 */	
	public function get( $namespace, $name = null, $default = '' ) {
		
		$namespace = 'qwall_' . $namespace;

		if ( ! isset( $this->settings[ $namespace ] ) ) {
			$this->settings[ $namespace ] = get_option( $namespace, $this->default_settings[ $namespace ] );
		}
		
		if ( null === $name ) {
			return $this->settings[ $namespace ];
		} else if ( isset( $this->settings[ $namespace ][ $name ] ) ) {
			return $this->settings[ $namespace ][ $name ];
		} else if ( isset( $this->default_settings[ $namespace ][ $name ] ) ) {
			return $this->default_settings[ $namespace ][ $name ];
		} else {
			return $default;
		}
	}

	/**
	 * Delete option settings.
	 *
	 * @since 1.0.7
	 * @return array
	 */	
	public function delete( $namespace ) {
		delete_option( 'qwall_' . $namespace );
	}
}

QWall_DIC::set( 'settings', new QWall_Settings() );

endif;