<?php
/**
 * Plugin Name: WPTOC+
 * Plugin URI : https://github.com/psydox/WPTOC-Plus
 * Description: WPTOC+ is a fork of the original Table of Contents Plus plugin, maintained by Brian V. Rosario, that automatically creates a table of contents for long-form content.
 * Author:      Brian V. Rosario
 * Author URI:  https://brianrosario.com
 * Update URI:  https://github.com/psydox/WPTOC-Plus
 * Text Domain: table-of-contents-plus
 * Domain Path: /languages
 * Version:     2026.05.27.0131
 * License:     GPL2
 */

/**
WPTOC+ is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
any later version.

WPTOC+ is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with WPTOC+.
*/

/**
 * GPL licenced Oxygen icon used for the colour wheel
 * http://www.iconfinder.com/search/?q=iconset%3Aoxygen
 */

function wptoc_plus_clear_update_metadata() {
	$plugin_file = plugin_basename( __FILE__ );
	$transient   = get_site_transient( 'update_plugins' );

	if ( is_object( $transient ) ) {
		if ( isset( $transient->response[ $plugin_file ] ) ) {
			unset( $transient->response[ $plugin_file ] );
		}

		if ( isset( $transient->no_update[ $plugin_file ] ) ) {
			unset( $transient->no_update[ $plugin_file ] );
		}

		if ( isset( $transient->checked[ $plugin_file ] ) ) {
			unset( $transient->checked[ $plugin_file ] );
		}

		set_site_transient( 'update_plugins', $transient );
	}

	delete_site_transient( 'update_plugins' );
}

register_activation_hook( __FILE__, 'wptoc_plus_clear_update_metadata' );
register_deactivation_hook( __FILE__, 'wptoc_plus_clear_update_metadata' );

require_once __DIR__ . '/includes/init.php';
