<?php
/**
 * Upgrade Live Composer.
 *
 * @package LiveComposer
 */

// Prevent direct access to the file.
if ( ! defined( 'ABSPATH' ) ) {
	header( 'HTTP/1.0 403 Forbidden' );
	exit;
}

/**
 * Upgrade class
 */
final class DSLC_Upgrade {

	/**
	 * Init
	 */
	public static function init() {

		$curr_version = DS_LIVE_COMPOSER_VER;
		$versions_log = get_option( 'dslc_version', array() );

		// Make sure $versions_log is array.
		if ( $versions_log && ! is_array( $versions_log ) ) {

			$versions_log = array();
			$versions_log[] = get_option( 'dslc_version', array() );
		}

		/* if ( is_array( $versions_log ) && ! in_array( '1.3.10', $versions_log ) ) {
			self::update_1_3_10();
		} */

		if ( is_array( $versions_log ) && ! in_array( '1.3.16', $versions_log ) ) {
			self::update_1_3_16();
		}

		// Update versions update history with new version.
		if ( ! in_array( $curr_version, $versions_log ) ) {

			$versions_log[] = $curr_version;

			usort( $versions_log, 'version_compare' );

			// Updated to current version.
			update_option( 'dslc_version', $versions_log );
		}
	}

	/**
	 * Update ad bar with version 1.3.10.
	 */
	/* public static function update_1_3_10() {

		// Update upsell messages in the editing interface.
		$editor_messages = new LC_Editor_Messages();
		$editor_messages->delete_all_messages();
		$editor_messages->on_plugin_install();
	} */

	/**
	 * Update ad bar with version 1.3.16.
	 */
	public static function update_1_3_16() {

		// Update upsell messages in the editing interface.
		$editor_messages = new LC_Editor_Messages();
		$editor_messages->delete_all_messages();
		$editor_messages->on_plugin_install();
	}
}
