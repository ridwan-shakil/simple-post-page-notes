<?php
/**
 * Metabox handler for Simple Post Page Notes.
 *
 * @package SimplePostPageNotes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class SPPN_Metabox
 */
class SPPN_Metabox {

	/**
	 * Option name for excluded post types.
	 */
	const OPTION_EXCLUDED = 'sppn_excluded_post_types';

	/**
	 * Meta keys.
	 */
	const META_TITLE = '_simple_post_page_note_title';
	const META_BODY  = '_simple_post_page_note_body';

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'add_meta_boxes', array( $this, 'add_notes_meta_box' ) );
		add_action( 'save_post', array( $this, 'save_post_note' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
	}

	/**
	 * Add meta box to allowed post types.
	 *
	 * Uses public post types minus any excluded by settings.
	 */
	public function add_notes_meta_box(): void {
		$excluded = (array) get_option( self::OPTION_EXCLUDED, array() );

		/*
		 * Get all public post types and exclude any user-selected types.
		 * Using 'names' returns an array of slugs.
		 */
		$post_types = array_diff( get_post_types( array( 'public' => true ), 'names' ), $excluded );

		foreach ( (array) $post_types as $post_type ) {
			add_meta_box(
				'simple_post_page_notes_box',
				__( 'Simple Notes', 'simple-post-page-notes' ),
				array( $this, 'render_notes_meta_box' ),
				$post_type,
				'side',
				'default'
			);
		}
	}

	/**
	 * Render the meta box HTML.
	 *
	 * @param WP_Post $post Post object.
	 */
	public function render_notes_meta_box( $post ): void {
		wp_nonce_field( 'simple_post_page_notes_save', 'simple_post_page_notes_nonce' );

		$note_title = get_post_meta( $post->ID, self::META_TITLE, true );
		$note_body  = get_post_meta( $post->ID, self::META_BODY, true );

		echo '<p>' . esc_html__( 'Add a private note for this post. Visible only to administrators and editors.', 'simple-post-page-notes' ) . '</p>';

		printf(
			'<input type="text" id="simple_post_page_note_title" name="simple_post_page_note_title" placeholder="%s" value="%s" style="width:100%%; margin-bottom:6px;">',
			esc_attr__( 'Note title...', 'simple-post-page-notes' ),
			esc_attr( (string) $note_title )
		);

		printf(
			'<textarea id="simple_post_page_note_body" name="simple_post_page_note_body" placeholder="%s" style="width:100%%;height:100px;">%s</textarea>',
			esc_attr__( 'Note description...', 'simple-post-page-notes' ),
			esc_textarea( (string) $note_body )
		);
	}

	/**
	 * Save note meta when post is saved.
	 *
	 * @param int $post_id Post ID.
	 *
	 * @return void
	 */
	public function save_post_note( $post_id ): void {
		/* Security checks. */
		if ( isset( $_POST['simple_post_page_notes_nonce'] ) === false ) { // phpcs:ignore WordPress.Security.NonceVerification
			return;
		}

		if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['simple_post_page_notes_nonce'] ) ), 'simple_post_page_notes_save' ) ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			return;
		}

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
			return;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		if ( isset( $_POST['simple_post_page_note_title'] ) ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			$title = sanitize_text_field( wp_unslash( $_POST['simple_post_page_note_title'] ) );
			update_post_meta( $post_id, self::META_TITLE, $title );
		}

		if ( isset( $_POST['simple_post_page_note_body'] ) ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			$body = sanitize_textarea_field( wp_unslash( $_POST['simple_post_page_note_body'] ) );
			update_post_meta( $post_id, self::META_BODY, $body );
		}
	}

	/**
	 * Enqueue admin CSS.
	 *
	 * @param string $hook Current admin page hook.
	 */
	public function enqueue_admin_assets( $hook ): void {
		if ( in_array( $hook, array( 'post.php', 'post-new.php', 'edit.php' ), true ) ) {
			wp_enqueue_style(
				'simple-post-page-notes-css',
				SPPN_PLUGIN_URL . 'assets/post-page-notes.css',
				array(),
				SPPN_VERSION
			);
		}

		if ( 'settings_page_simple-post-page-notes' === $hook ) {
			wp_enqueue_style(
				'simple-post-page-notes-settings-style',
				SPPN_PLUGIN_URL . 'assets/post-page-notes-settings.css',
				array(),
				SPPN_VERSION
			);
		}
	}
}
