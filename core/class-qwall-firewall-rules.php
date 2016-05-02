<?php
/**
 * QueryWall Firewall Rules
 *
 * Firewall Rules class for QueryWall.
 *
 * @package QueryWall
 * @since   1.1.0
 */

defined( 'ABSPATH' ) or die( 'You shall not pass!' );

if ( ! class_exists( 'QWall_Firewall_Rules' ) ):

class QWall_Firewall_Rules {

	/**
	 * Attack vectors
	 *
	 * @since 1.1.0
	 */
	private $attack_vectors;

	/**
	 * Magic starts here.
	 *
	 * All custom functionality will be hooked into the "init" action.
	 *
	 * @since 1.1.0
	 * @return void
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'init' ), 35 );
	}

	/**
	 * Conditionally hook into WordPress.
	 *
	 * @since 1.1.0
	 * @return void
	 */
	public function init() {
		add_action( 'admin_menu', array( $this, 'cb_admin_menu' ) );
	}

	/**
	 * Enqueue actions to build the admin menu.
	 *
	 * Calls all the needed actions to build the admin menu.
	 *
	 * @since 1.1.0
	 * @return void
	 */
	public function cb_admin_menu() {

		// add_submenu_page( $parent_slug, $page_title, $menu_title, $capability, $menu_slug, $function );
		add_submenu_page(
			'querywall',
			__( 'Firewall Rules', 'querywall' ),
			__( 'Rules', 'querywall' ),
			'manage_options',
			'querywall-firewall-rules',
			array( $this, 'display_rules_page' )
		);
	}

	/**
	 * Displays firewall rules page
	 *
	 * @since 1.1.0
	 * @return void
	 */
	public function display_rules_page() {

		$attack_vectors = $this->get_attack_vectors();
		
		// set the internal pointer to its first element
		// so we can get the first key as a tab id
		reset( $attack_vectors );

		if ( ! isset( $_GET['vector'] ) || ! isset( $attack_vectors[ $_GET['vector'] ] ) ) {
			$_GET['vector'] = key( $attack_vectors );
		}
		?>
		<div class="wrap">
			<h1><?php echo get_admin_page_title(); ?></h1>
			<p><?php _e( 'Adjust the firewall rules to fit your security needs.', 'querywall' ); ?></p>

			<h2 class="nav-tab-wrapper">
				<?php
				foreach ( $attack_vectors as $idx => $vector ) {

					$css_class = '';
					
					if ( $_GET['vector'] == $idx ) {

						$current_vector_id = $idx;
						$current_vector    = $vector;
						$css_class         = 'nav-tab-active';
					}

					$tab_url = add_query_arg( array( 'vector' => $idx ) );
					echo '<a href="' . esc_url( $tab_url ) . '" title="' . esc_attr( $vector['name'] ) . '" class="nav-tab ' . $css_class . '">' . esc_html( $vector['name'] ) . '</a>';
				}
				?>
			</h2>

			<style type="text/css">
				#qwall.postbox .hndle { cursor: auto; }
				#qwall.postbox + p { margin: 5px 0 -20px; color: #666; }
				#qwall.postbox textarea { display: block; width: 100%; min-height: 200px; }
			</style>

			<div class="metabox-holder">
				<form method="post" action="">
					<div id="qwall" class="postbox">
						<h3 class="hndle"><?php echo $current_vector['name']; ?></h3>
						<div class="inside">
							<?php wp_nonce_field( 'qwall_av_rules', 'qwall_av_rules_nonce' ); ?>
							<input type="hidden" name="qwall_attack_vector" value="<?php echo $current_vector_id; ?>">
							<p>
							<?php
							$match_in = '$_SERVER[\'' . $current_vector['name'] . '\']';
							if ( 'files' == $current_vector_id ) {
								$match_in = '$_FILES';
							}
							printf( __( 'These rules will match all in <strong>%s</strong>. <i>better documentation will follow soon...</i>', 'querywall' ), $match_in );
							
							$default_pattern = implode( PHP_EOL, $current_vector['default_pattern'] );
							$custom_pattern  = str_replace( '##', PHP_EOL, base64_decode( get_option( 'qwall_avc_' . $current_vector_id ) ) );
							?>
							</p>
							<textarea disabled><?php echo $default_pattern; ?></textarea>
							<p><?php _e( 'Add your own rules below. One rule per line.<br><b>Note</b> that these patterns are regular expressions, as such you have to test your patterns thoroughly for best results.<br>Visit <a href="https://regex101.com/r/lN3qI6/1" target="_blank">regex101.com/r/lN3qI6/1</a> to see an example and to create your own rules. More on regular expression you\'ll find on <a href="https://en.wikipedia.org/wiki/Regular_expression" target="_blank">Wikipedia</a>.', 'querywall' ); ?></p>
							<textarea name="qwall_avc_rules"><?php echo $custom_pattern; ?></textarea>
						</div>
					</div>
					<input class="button button-primary" type="submit" value="<?php _e( 'Save rules', 'querywall' ); ?>">
				</form>
			</div>
		</div>
		<?php
	}

	/**
	 * Get attack vectors.
	 *
	 * @since 1.1.0
	 * @return string|array
	 */
	public function get_attack_vectors( $vector = null ) {

		if ( ! isset( $this->attack_vectors ) ) {

			$this->attack_vectors = array(
				'request_uri' => array(
					'name'            => 'REQUEST_URI',
					'default_pattern' => array( 'eval\(', 'UNION(.*)SELECT', 'GROUP_CONCAT', 'CONCAT\s*\(', '\(null\)', 'base64_', '\/localhost', '\%2Flocalhost', '\/pingserver', '\/config\.', '\/wwwroot', '\/makefile', 'crossdomain\.', 'proc\/self\/environ', 'etc\/passwd', '\/https\:', '\/http\:', '\/ftp\:', '\/cgi\/', '\.cgi', '\.exe', '\.sql', '\.ini', '\.dll', '\.asp', '\.jsp', '\/\.bash', '\/\.git', '\/\.svn', '\/\.tar', ' ', '\<', '\>', '\/\=', '\.\.\.', '\+\+\+', '\/&&', '\/Nt\.', '\;Nt\.', '\=Nt\.', '\,Nt\.', '\.exec\(', '\)\.html\(', '\{x\.html\(', '\(function\(', '\.php\([0-9]+\)', '(benchmark|sleep)(\s|%20)*\(' ),

				),
				'query_string' => array(
					'name'            => 'QUERY_STRING',
					'default_pattern' => array( '\.\.\/', '127\.0\.0\.1', 'localhost', 'loopback', '\%0A', '\%0D', '\%00', '\%2e\%2e', 'input_file', 'execute', 'mosconfig', 'path\=\.', 'mod\=\.', 'wp-config\.php' ),

				),
				'files' => array(
					'name'            => 'FILES',
					'default_pattern' => array( '\.dll$', '\.rb$', '\.py$', '\.exe$', '\.php[3-6]?$', '\.pl$', '\.perl$', '\.ph[34]$', '\.phl$', '\.phtml$', '\.phtm$' ),

				),
				'http_user_agent' => array(
					'name'            => 'HTTP_USER_AGENT',
					'default_pattern' => array( 'acapbot', 'binlar', 'casper', 'cmswor', 'diavol', 'dotbot', 'finder', 'flicky', 'morfeus', 'nutch', 'planet', 'purebot', 'pycurl', 'semalt', 'skygrid', 'snoopy', 'sucker', 'turnit', 'vikspi', 'zmeu' ),

				),
				'http_referer' => array(
					'name'            => 'HTTP_REFERER',
					'default_pattern' => array(),

				),
				'http_cookie' => array(
					'name'            => 'HTTP_COOKIE',
					'default_pattern' => array(),

				),
				'remote_addr' => array(
					'name'            => 'REMOTE_ADDR',
					'default_pattern' => array(),

				),

			);

			foreach( $this->attack_vectors as $idx => $v ) {

				$option         = get_option( 'qwall_avc_' . $idx );
				$custom_pattern = array();

				if ( $option && ! empty( $option ) ) {

					$option = base64_decode( $option );
					
					if ( $option && ! empty( $option ) ) {
						$custom_pattern = explode( '##', $option );
					}
				}

				$this->attack_vectors[ $idx ]['custom_pattern'] = $custom_pattern;
			}
		}

		if ( null !== $vector ) {

			if ( ! isset( $this->attack_vectors[ $vector ] ) ) {
				return null;
			}

			return $this->attack_vectors[ $vector ];
		}

		return $this->attack_vectors;
	}
}

QWall_DIC::set( 'firewall_rules', new QWall_Firewall_Rules() );

endif;