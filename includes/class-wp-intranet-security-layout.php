<?php
/**
 * Plugin Layout Class
 *
 * @package WP Intranet Security
 */

/**
 * Manage Plugin Layout.
 *
 * Class Wp_Intranet_Security_Layout
 */
class Wp_Intranet_Security_Layout {

	/**
	 * Create footer headings.
	 *
	 * @since 1.0
	 *
	 * @return string
	 */
	public static function prepare_header_footer_row() {

		$row = '';

		$row .= '<th class="manage-column column-details" colspan="2">' . __( 'Users', WPIS_LANG ) . '</th>';
		$row .= '<th class="manage-column column-email">' . __( 'Role', WPIS_LANG ) . '</th>';
		$row .= '<th class="manage-column column-expired">' . __( 'Last Logged In', WPIS_LANG ) . '</th>';
		$row .= '<th class="manage-column column-expired">' . __( 'Expiry', WPIS_LANG ) . '</th>';
		$row .= '<th class="manage-column column-expired">' . __( 'Actions', WPIS_LANG ) . '</th>';

		return $row;
	}

	/**
	 * Prepare empty user row.
	 *
	 * @since 1.0
	 *
	 * @return string
	 */
	public static function prepare_empty_user_row() {

		$row = '';

		$row .= '<tr class="tempadmin-single-user-row tempadmin-empty-users-row standard">';
		$row .= '<td colspan="6">';
		$row .= '<span class="description">' . __( 'You have not created any temporary logins yet.', WPIS_LANG ) . '</span>';
		$row .= '</td>';
		$row .= '</tr>';

		return $row;
	}

	/**
	 * Prepare single user row
	 *
	 * @param WP_User|int $user WP_User object.
	 *
	 * @since 1.0
	 *
	 * @return string
	 */
	public static function prepare_single_user_row( $user = OBJECT ) {
		global $wpdb;
		if ( is_numeric( $user ) && ! is_object( $user ) ) {
			$user = get_user_by( 'id', $user );
		}

		$expire          = get_user_meta( $user->ID, '_wpis_expire', true ); // phpcs:ignore
		$last_login_time = get_user_meta( $user->ID, '_wpis_last_login', true ); // phpcs:ignore

		$last_login_str = __( 'Not yet logged in', WPIS_LANG );
		if ( ! empty( $last_login_time ) ) {
			$last_login_str = Wp_Intranet_Security_Common::time_elapsed_string( $last_login_time, true );
		}

		$wpis_status = 'Active';
		if ( Wp_Intranet_Security_Common::is_login_expired( $user->ID ) ) {
			$wpis_status = 'Expired';
		}

		if ( is_multisite() && is_super_admin( $user->ID ) ) {
			$user_role = __( 'Super Admin', WPIS_LANG );
		} else {
			$capabilities = $user->{$wpdb->prefix . 'capabilities'};
			$wp_roles     = new WP_Roles();
			$user_role    = '';
			foreach ( $wp_roles->role_names as $role => $name ) {
				if ( array_key_exists( $role, $capabilities ) ) {
					$user_role = $name;
				}
			}
		}

		$user_details = '<div><span>';
		if ( ( esc_attr( $user->first_name ) ) ) {
			$user_details .= '<span>' . esc_attr( $user->first_name ) . '</span>';
		}

		if ( ( esc_attr( $user->last_name ) ) ) {
			$user_details .= '<span> ' . esc_attr( $user->last_name ) . '</span>';
		}

		$user_details .= "  (<span class='wpis-user-login'>" . esc_attr( $user->user_login ) . ')</span><br />';

		if ( ( esc_attr( $user->user_email ) ) ) {
			$user_details .= '<span><b>' . esc_attr( $user->user_email ) . '</b></span> <br />';
		}

		$user_details .= '</span></div>';

		$row = '';

		$row .= '<tr id="single-user-' . absint( $user->ID ) . '" class="tempadmin-single-user-row">';
		$row .= '<td class="email column-details" colspan="2">' . $user_details . '</td>';
		$row .= '<td class="wpis-token column-role">' . esc_attr( $user_role ) . '</td>';
		$row .= '<td class="wpis-token column-last-login">' . esc_attr( $last_login_str ) . '</td>';

		$row .= '<td class="expired column-expired wpis-status-' . strtolower( $wpis_status ) . '">';
		if ( ! empty( $expire ) ) {
			$row .= Wp_Intranet_Security_Common::time_elapsed_string( $expire );
		}
		$row .= '</td>';
		$row .= '<td class="wpis-token column-email">' . self::prepare_row_actions( $user, $wpis_status ) . '</td>';
		$row .= '</tr>';

		return $row;
	}

	/**
	 * Prepare user actions row.
	 *
	 * @param WP_User $user WP_User object.
	 * @param string  $wpis_status Current wpis_status.
	 *
	 * @since 1.0
	 *
	 * @return string
	 */
	public static function prepare_row_actions( $user, $wpis_status ) {

		$is_active = ( 'active' === strtolower( $wpis_status ) ) ? true : false;
		$user_id   = $user->ID;
		$email     = $user->user_email;

		$delete_login_url     = Wp_Intranet_Security_Common::get_manage_login_url( $user_id, 'delete' );
		$update_login_url     = add_query_arg(
			array(
				'page'    => 'wp-intranet-security',
				'user_id' => $user_id,
				'action'  => 'update',
			), admin_url( 'options-general.php' )
		);
		$disable_login_url    = Wp_Intranet_Security_Common::get_manage_login_url( $user_id, 'disable' );
		$enable_login_url     = Wp_Intranet_Security_Common::get_manage_login_url( $user_id, 'enable' );
		$temporary_login_link = Wp_Intranet_Security_Common::get_login_url( $user_id );
		$mail_to_link         = Wp_Intranet_Security_Common::generate_mailto_link( $email, $temporary_login_link );

		$action_row = '<div class="actions">';

		if ( $is_active ) {
			$action_row .= "<span class='disable'><a title='" . __( 'Disable', WPIS_LANG ) . "' href='{$disable_login_url}'><span class='dashicons dashicons-unlock'></span></a></span>";
		} else {
			$action_row .= "<span class='enable'><a title='" . __( 'Reactivate for one day', WPIS_LANG ) . "' href='{$enable_login_url}'><span class='dashicons dashicons-lock'></a></span></span>";
		}

		$action_row .= "<span class='delete'><a title='" . __( 'Delete', WPIS_LANG ) . "' href='{$delete_login_url}'><span class='dashicons dashicons-no'></span></a></span>";
		$action_row .= "<span class='edit'><a title='" . __( 'Edit', WPIS_LANG ) . "' href='{$update_login_url}'><span class='dashicons dashicons-edit'></span></a></span>";

		// Shows these link only if temporary login active.
		if ( $is_active ) {
			$action_row .= "<span class='email'><a title='" . __( 'Email login link', WPIS_LANG ) . "' href='{$mail_to_link}'><span class='dashicons dashicons-email'></span></a></span>";
			$action_row .= "<span class='copy'><span id='text-{$user_id}' class='dashicons dashicons-admin-links wpis-copy-to-clipboard' title='" . __( 'Copy login link', WPIS_LANG ) . "' data-clipboard-text='{$temporary_login_link}'></span></span>";
			$action_row .= "<span id='copied-text-{$user_id}' class='copied-text-message'></span>";
		}

		$action_row .= '</div>';

		return $action_row;
	}

}
