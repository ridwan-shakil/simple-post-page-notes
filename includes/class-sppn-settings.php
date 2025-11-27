<?php
/**
 * Settings page for Simple Post Page Notes.
 *
 * @package SimplePostPageNotes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class SPPN_Settings
 */
class SPPN_Settings {

	/**
	 * Option name to store excluded post types.
	 */
	const OPTION_EXCLUDED = 'sppn_excluded_post_types';

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_settings_page' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
	}

	/**
	 * Add settings submenu under Settings.
	 */
	public function add_settings_page(): void {
		add_options_page(
			__( 'Post Page Notes', 'simple-post-page-notes' ),
			__( 'Post Page Notes', 'simple-post-page-notes' ),
			'manage_options',
			'simple-post-page-notes',
			array( $this, 'render_settings_page' )
		);
	}

	/**
	 * Register plugin settings.
	 */
	public function register_settings(): void {
		register_setting(
			'sppn_settings_group',
			self::OPTION_EXCLUDED,
			array(
				'type'              => 'array',
				'sanitize_callback' => array( $this, 'sanitize_excluded_post_types' ),
				'default'           => array(),
			)
		);
	}

	/**
	 * Sanitization callback for excluded post types.
	 *
	 * @param mixed $input Input value.
	 *
	 * @return array
	 */
	public function sanitize_excluded_post_types( $input ): array {
		$post_types = get_post_types( array( 'public' => true ), 'names' );

		if ( ! is_array( $input ) ) {
			return array();
		}

		return array_values( array_intersect( $input, $post_types ) );
	}

	/**
	 * Render the settings page.
	 */
	public function render_settings_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$post_types = get_post_types( array( 'public' => true ), 'objects' );
		$excluded   = get_option( self::OPTION_EXCLUDED, array() );
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Simple Post Page Notes Settings', 'simple-post-page-notes' ); ?></h1>
			<form method="post" action="options.php">
				<?php settings_fields( 'sppn_settings_group' ); ?>
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><?php esc_html_e( 'Exclude Post Types', 'simple-post-page-notes' ); ?></th>
						<td>
							<?php foreach ( $post_types as $slug => $obj ) : ?>
								<label style="display:block;margin-bottom:6px;">
									<input type="checkbox" name="<?php echo esc_attr( self::OPTION_EXCLUDED ); ?>[]" value="<?php echo esc_attr( $slug ); ?>" <?php checked( in_array( $slug, (array) $excluded, true ) ); ?> />
									<?php echo esc_html( $obj->labels->singular_name ); ?> <code>(<?php echo esc_html( $slug ); ?>)</code>
								</label>
							<?php endforeach; ?>
							<p class="description"><?php esc_html_e( 'Select post types where you do NOT want to show notes.', 'simple-post-page-notes' ); ?></p>
						</td>
					</tr>
				</table>
				<?php submit_button(); ?>
			</form>
		</div>
		<?php
	}
}
