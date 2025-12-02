<?php
/**
 * Columns handler for Simple Post Page Notes.
 *
 * @package SimplePostPageNotes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class SPPN_Columns
 */
class SPPN_Columns {

	/**
	 * Meta key used for sorting and preview.
	 */
	const META_TITLE = '_simple_post_page_note_title';

	/**
	 * Option name for excluded post types (shared with metabox class).
	 */
	const OPTION_EXCLUDED = 'sppn_excluded_post_types';

	/**
	 * Constructor.
	 */
	public function __construct() {
		// Register columns for public post types during init in the main bootstrap.
		add_action( 'init', array( $this, 'register_columns' ) );
	}

	/**
	 * Register columns and hooks for each public post type (except excluded ones).
	 */
	public function register_columns(): void {
		$excluded   = (array) get_option( self::OPTION_EXCLUDED, array() );
		$post_types = array_diff( get_post_types( array( 'public' => true ), 'names' ), $excluded );

		foreach ( (array) $post_types as $post_type ) {
			add_filter( "manage_{$post_type}_posts_columns", array( $this, 'add_notes_column' ) );
			add_action( "manage_{$post_type}_posts_custom_column", array( $this, 'render_notes_column' ), 10, 2 );
			add_filter( "manage_edit-{$post_type}_sortable_columns", array( $this, 'make_note_column_sortable' ) );
		}

		add_action( 'pre_get_posts', array( $this, 'sort_notes_column_query' ) );
	}

	/**
	 * Add the Note Preview column after title.
	 *
	 * @param array $columns Existing columns.
	 *
	 * @return array Modified columns array.
	 */
	public function add_notes_column( $columns ) {
		$new_columns = array();

		foreach ( $columns as $key => $label ) {
			$new_columns[ $key ] = $label;

			if ( 'title' === $key ) {
				$new_columns['simple_post_page_note'] = __( 'Note Preview', 'simple-post-page-notes' );
			}
		}

		return $new_columns;
	}

	/**
	 * Render the note preview column.
	 *
	 * @param string $column Column name.
	 * @param int    $post_id Post ID.
	 */
	public function render_notes_column( $column, $post_id ): void {
		if ( 'simple_post_page_note' !== $column ) {
			return;
		}

		$title = get_post_meta( $post_id, self::META_TITLE, true );

		if ( empty( $title ) ) {
			echo '<span class="sppn-empty-note">' . esc_html__( '— No Note —', 'simple-post-page-notes' ) . '</span>'; // phpcs:ignore WordPress.Security.EscapeOutput
			return;
		}

		$preview = wp_html_excerpt( (string) $title, 30, '…' );
		printf( '<span class="sppn-note-preview" title="%1$s">%2$s</span>', esc_attr( (string) $title ), esc_html( $preview ) );
	}

	/**
	 * Make the note column sortable.
	 *
	 * @param array $columns Sortable columns.
	 *
	 * @return array
	 */
	public function make_note_column_sortable( $columns ) {
		$columns['simple_post_page_note'] = 'simple_post_page_note';

		return $columns;
	}

	/**
	 * Modify the query when ordering by our column.
	 *
	 * @param WP_Query $query Query instance.
	 */
	public function sort_notes_column_query( $query ): void {
		if ( ! is_admin() || ! $query->is_main_query() ) {
			return;
		}

		$orderby = $query->get( 'orderby' );

		if ( 'simple_post_page_note' === $orderby ) {
			$query->set( 'meta_key', self::META_TITLE );
			$query->set( 'orderby', 'meta_value' );
		}
	}
}
