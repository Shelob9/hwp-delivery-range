<?php
/**
 * Admin page
 *
 * @package   hwp_dr
 * @author    Josh Pollock <Josh@JoshPress.net>
 * @license   GPL-2.0+
 * @link      
 * @copyright 2014 Josh Pollock
 */

class hwp_dr_admin {

	function __construct() {
		add_action( 'admin_menu', array( $this, 'settings_page' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );

	}

	/**
	 * Add our settings page
	 *
	 * @since 0.0.1
	 */
	function settings_page() {
		add_options_page(
			__( 'Delivery Range', 'hwp_dr' ),
			__( 'Delivery Range', 'hwp_dr' ),
			'manage_options',
			'hwp_dr',
			array( $this, 'admin_html')
		);
	}

	/**
	 * Register settings
	 *
	 * @since 0.0.1
	 */
	function register_settings() {
		add_settings_section(
			'hwp_dr',
			__( 'What Page To Use For Output', 'jptb' ),
			array( $this, 'page_choice_section_html' ),
			'hwp_dr'
		);

		add_settings_field(
			'hwp_dr_page_id',
			__( 'Select a page', 'wp_dr' ),
			array( $this, 'page_choice_setting_html' ),
			'hwp_dr',
			'hwp_dr'
		);
		register_setting(
			'hwp_dr',
			'hwp_dr_page_id',
			array( $this, 'sanitize_page_id_cb' )
		);

	}

	/**
	 * Sanitize save of hwp_dr_page_id option
	 *
	 * Makes sure value being saved is a number and that it is the ID of a page.
	 *
	 * @return bool
	 *
	 * @since 0.0.1
	 */
	function sanitize_page_id_cb() {

		if ( isset( $_POST[ 'hwp_dr_page_id' ] ) ) {
			$input = (int) $_POST[ 'hwp_dr_page_id' ];
			if ( ! get_post_type( $input ) === 'page' ) {
				return false;
			}

		}

	}

	/**
	 * Option for choosing page
	 *
	 * @since 0.0.1
	 */
	function page_choice_setting_html( $option ) {
		$option = esc_attr( get_option( 'hwp_dr_page_id', 0 ) );
		$options = $this->list_pages();
		if ( $options ) {
			echo sprintf( '<select name="hwp_dr_page_id" id="hwp_dr_page_id" default="%">', $option);
			foreach ( $options as $value => $label ) {
				echo sprintf( '<option value="%1s">%2s</option>', $value, $label );
			}
			echo '</select>';
		}
		else {
			_e( 'You do not have any pages', 'hwp_dr' );
			return;
		}


	}

	/**
	 * @TODO need this?
	 */
	function page_choice_section_html() {
		//@todo
	}

	/**
	 * The HTML for the admin form
	 *
	 * @since 0.0.1
	 */
	function admin_html() {
		?>
		<div class="wrap">
			<h2><?php _e( 'HWP Delivery Range Settings', 'hwp' ); ?></h2>
			<Strong>
				<?php _e( '', 'hwp_dr' ); ?>
			</Strong>
			<form action="options.php" method="POST">
				<?php
				do_settings_sections( 'hwp_dr' );
				settings_fields( 'hwp_dr' );

				submit_button();
				?>
			</form>
		</div><!-- .wrap -->
	<?php
	}

	/**
	 * List all published pages as ID => post_title
	 *
	 * @return array
	 *
	 * @since 0.0.1
	 */
	private function list_pages() {
		$args = array(
			'sort_order' => 'ASC',
			'sort_column' => 'post_title',
			'hierarchical' => 0,
			'post_type' => 'page',
			'post_status' => 'publish'
		);
		$pages = get_pages( $args );
		foreach( $pages as $page ) {
			$page_list[$page->ID ] = $page->post_title;
		}

		if ( isset( $page_list ) && is_array( $page_list ) ) {
			return $page_list;

		}

	}

	/**
	 * Holds the instance of this class.
	 *
	 * @since  0.0.1
	 * @access private
	 * @var    object
	 */
	private static $instance;


	/**
	 * Returns the instance.
	 *
	 * @since  0.0.1
	 * @access public
	 * @return object
	 */
	public static function init() {

		if ( !self::$instance )
			self::$instance = new hwp_dr_admin();

		return self::$instance;
	}
	
} 
