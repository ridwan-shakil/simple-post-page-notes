<?php
/**
 * Uninstall script for Simple Post Page Notes.
 *
 * @package SimplePostPageNotes
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Delete plugin options.
delete_option( 'sppn_excluded_post_types' );

// Optional: Delete all post meta.
$meta_keys = array( '_simple_post_page_note_title', '_simple_post_page_note_body' );
foreach ( $meta_keys as $meta_key ) {
	delete_metadata( 'post', 0, $meta_key, '', true );
}
