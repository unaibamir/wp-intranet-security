<?php
/**
 * Activator Class
 *
 * @package WP Intranet Security
 */

/**
 * Class Wp_Intranet_Security_Activator
 *
 * @package WP Intranet Security
 */
class Wp_Intranet_Security_Activator {

	/**
	 * Activate Plugin.
	 *
	 * @since 1.0.0
	 */
	public static function activate() {
		/**
		 * Process
		 *
		 * - Get the previously added temporary logins data from temporary_logins_data option if available
		 * - Update user role for Temporary User if user exists into the system
		 * - Set temporary_logins_data option as empty
		 * - Set activation timestamp
		 * - Set plugin version
		 */

		$temporary_logins_data = get_option( 'temporary_logins_data', array() );

		if ( count( $temporary_logins_data ) > 0 ) {
			foreach ( $temporary_logins_data as $user_id => $user_role ) {
				wp_update_user( array(
					'ID'   => $user_id,
					'role' => $user_role,
				) );
			}
		}

		$add = 'yes';

		update_option( 'temporary_logins_data', array(), $add );
		update_option( 'tlwp_plugin_activation_time', time(), $add );
		update_option( 'tlwp_plugin_version', WPIS_PLUGIN_VERSION, $add );

	}

}
