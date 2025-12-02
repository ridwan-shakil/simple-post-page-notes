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
$sppn_meta_keys = array( '_simple_post_page_note_title', '_simple_post_page_note_body' );
foreach ( $sppn_meta_keys as $sppn_meta_key ) {
	delete_metadata( 'post', 0, $sppn_meta_key, '', true );
}
