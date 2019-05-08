<?php

/**
 * Class Wp_Intranet_Security_Common
 */
class Wp_Intranet_Security_Common {

	/**
	 * Create a ranadom username for the temporary user
	 *
	 * @param array $data
	 *
	 * @return string
	 */
	public static function create_username( $data ) {

		$first_name = isset( $data['user_first_name'] ) ? $data['user_first_name'] : '';
		$last_name  = isset( $data['user_last_name'] ) ? $data['user_last_name'] : '';
		$email      = isset( $data['user_email'] ) ? $data['user_email'] : '';

		$name = '';
		if ( ! empty( $first_name ) || ! empty( $last_name ) ) {
			$name = str_replace( array( '.', '+' ), '', trim( $first_name . $last_name ) );
		} else {
			if ( ! empty( $email ) ) {
				$explode = explode( '@', $email );
				$name    = str_replace( array( '.', '+' ), '', $explode[0] );
			}
		}

		if ( username_exists( $name ) ) {
			$name = $name . substr( uniqid( '', true ), - 6 );
		}

		return sanitize_user( $name, true );

	}

	/**
	 * Create a new user
	 *
	 * @param array $data
	 *
	 * @return array|int|WP_Error
	 */
	public static function create_new_user( $data ) {

		if ( false === Wp_Intranet_Security_Common::can_manage_wpis() ) {
			return 0;
		}

		$result = array(
			'error' => true
		);

		$expiry_option = ! empty( $data['expiry'] ) ? $data['expiry'] : 'day';
		$date          = ! empty( $data['custom_date'] ) ? $data['custom_date'] : '';

		$password   = Wp_Intranet_Security_Common::generate_password();
		$username   = Wp_Intranet_Security_Common::create_username( $data );
		$first_name = isset( $data['user_first_name'] ) ? sanitize_text_field( $data['user_first_name'] ) : '';
		$last_name  = isset( $data['user_last_name'] ) ? sanitize_text_field( $data['user_last_name'] ) : '';
		$email      = isset( $data['user_email'] ) ? sanitize_email( $data['user_email'] ) : '';
		$role       = ! empty( $data['role'] ) ? $data['role'] : 'subscriber';
		$user_args  = array(
			'first_name' => $first_name,
			'last_name'  => $last_name,
			'user_login' => $username,
			'user_pass'  => $password,
			'user_email' => sanitize_email( $email ),
			'role'       => $role,
		);

		$user_id = wp_insert_user( $user_args );

		if ( is_wp_error( $user_id ) ) {
			$code = $user_id->get_error_code();

			$result['errcode'] = $code;
			$result['message'] = $user_id->get_error_message( $code );

		} else {

			if ( is_multisite() && ! empty( $data['super_admin'] ) && 'on' === $data['super_admin'] ) {
				grant_super_admin( $user_id );
			}

			update_user_meta( $user_id, '_wpis_user', true );
			update_user_meta( $user_id, '_wpis_created', Wp_Intranet_Security_Common::get_current_gmt_timestamp() );
			update_user_meta( $user_id, '_wpis_expire', Wp_Intranet_Security_Common::get_user_expire_time( $expiry_option, $date ) );
			update_user_meta( $user_id, '_wpis_token', Wp_Intranet_Security_Common::generate_wpis_token( $user_id ) );

			update_user_meta( $user_id, 'show_welcome_panel', 0 );

			$result['error']   = false;
			$result['user_id'] = $user_id;

		}

		return $result;

	}

	/**
	 * update user
	 *
	 * @param array $data
	 *
	 * @return array|int|WP_Error
	 */
	public static function update_user( $user_id = 0, $data ) {

		if ( false === Wp_Intranet_Security_Common::can_manage_wpis() || ( 0 === $user_id ) ) {
			return 0;
		}

		$expiry_option = ! empty( $data['expiry'] ) ? $data['expiry'] : 'day';
		$date          = ! empty( $data['custom_date'] ) ? $data['custom_date'] : '';

		$first_name = isset( $data['user_first_name'] ) ? sanitize_text_field( $data['user_first_name'] ) : '';
		$last_name  = isset( $data['user_last_name'] ) ? sanitize_text_field( $data['user_last_name'] ) : '';
		$role       = ! empty( $data['role'] ) ? $data['role'] : 'subscriber';
		$user_args  = array(
			'first_name' => $first_name,
			'last_name'  => $last_name,
			'role'       => $role,
			'ID'         => $user_id //require for update_user
		);

		$user_id = wp_update_user( $user_args );

		if ( is_wp_error( $user_id ) ) {
			$code = $user_id->get_error_code();

			return array(
				'error'   => true,
				'errcode' => $code,
				'message' => $user_id->get_error_message( $code ),
			);
		}


		if ( is_multisite() && ! empty( $data['super_admin'] ) && 'on' === $data['super_admin'] ) {
			grant_super_admin( $user_id );
		}

		update_user_meta( $user_id, '_wpis_updated', Wp_Intranet_Security_Common::get_current_gmt_timestamp() );
		update_user_meta( $user_id, '_wpis_expire', Wp_Intranet_Security_Common::get_user_expire_time( $expiry_option, $date ) );

		return $user_id;

	}


	/**
	 * get the expiry duration
	 *
	 * @param string $key
	 *
	 * @since 1.0.0
	 *
	 * @updated: 1.5.11
	 *
	 * @return boolean|array
	 */
	public static function get_expiry_options() {

		$expiry_options = array(
			'hour'        => array( 'label' => __( 'One Hour', WPIS_LANG ), 'timestamp' => HOUR_IN_SECONDS, 'order' => 5 ),
			'3_hours'     => array( 'label' => __( 'Three Hours', WPIS_LANG ), 'timestamp' => HOUR_IN_SECONDS * 3, 'order' => 10 ),
			'day'         => array( 'label' => __( 'One Day', WPIS_LANG ), 'timestamp' => DAY_IN_SECONDS, 'order' => 15 ),
			'3_days'      => array( 'label' => __( 'Three Days', WPIS_LANG ), 'timestamp' => DAY_IN_SECONDS * 3, 'order' => 20 ),
			'week'        => array( 'label' => __( 'One Week', WPIS_LANG ), 'timestamp' => WEEK_IN_SECONDS, 'order' => 25 ),
			'month'       => array( 'label' => __( 'One Month', WPIS_LANG ), 'timestamp' => MONTH_IN_SECONDS, 'order' => 30 ),
			'custom_date' => array( 'label' => __( 'Custom Date', WPIS_LANG ), 'timestamp' => 0, 'order' => 35 )
		);

		// Now, one can add their own options.
		$expiry_options = apply_filters( 'tlwp_expiry_options', $expiry_options );

		// Get Order options to sort $expiry_options array by it's array
		foreach ( $expiry_options as $key => $options ) {
			$orders[ $key ] = ! empty( $options['order'] ) ? $options['order'] : 100;
		}

		// Sort $expiry_options array by it's order value
		array_multisort( $orders, SORT_ASC, $expiry_options );

		return $expiry_options;
	}

	/**
	 * Get Expire duration dropdown
	 *
	 * @param string $selected
	 *
	 * @update: 1.5.11
	 * @return string
	 */
	static function get_expiry_duration_html( $selected = '', $excluded = array() ) {

		$r = '';

		$expiry_options = self::get_expiry_options();

		if ( is_array( $expiry_options ) && count( $expiry_options ) > 0 ) {

			foreach ( $expiry_options as $key => $option ) {

				// We don't need to add option into dropdown if it's excluded
				if ( ! empty( $excluded ) && in_array( $key, $excluded ) ) {
					continue;
				}

				$label = ! empty( $option['label'] ) ? $option['label'] : '';

				$r .= "\n\t<option ";

				if ( $selected === $key ) {
					$r .= "selected='selected' ";
				}

				$r .= "value='" . esc_attr( $key ) . "'>$label</option>";

			}

		}

		echo $r;

	}

	/**
	 * Generate password for new user.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public static function generate_password() {
		return wp_generate_password( absint( 15 ), true, false );

	}

	/**
	 * Get the expiration time based on string
	 *
	 * @param string $expiry_option
	 * @param string $date
	 *
	 * @since 1.0.0
	 *
	 * @return false|float|int
	 */
	public static function get_user_expire_time( $expiry_option = 'day', $date = '' ) {

		$expiry_options = self::get_expiry_options();

		$expiry_option = in_array( $expiry_option, array_keys( $expiry_options ) ) ? $expiry_option : 'day';

		if ( 'custom_date' === $expiry_option ) {

			// For the custom_date option we need to simply expire login at particular date
			// So, we don't need to do addition in the current timestamp
			$current_timestamp = 0;
			$timestamp         = strtotime( $date );
		} else {

			// We need current gmt timestamp and from now we need to expire temporary login
			// after specified time. So, we need to add into current timestamp
			$current_timestamp = self::get_current_gmt_timestamp();
			$timestamp         = $expiry_options[ $expiry_option ]['timestamp'];
		}

		return $current_timestamp + floatval( $timestamp );

	}

	/**
	 * Get current GMT date time
	 *
	 * @since 1.0
	 *
	 * @return false|int
	 */
	public static function get_current_gmt_timestamp() {
		return strtotime( gmdate( 'Y-m-d H:i:s', time() ) );

	}

	/**
	 * Get Temporary Logins
	 *
	 * @since 1.0
	 *
	 * @param string $role
	 *
	 * @return array|bool
	 */
	public static function get_temporary_logins( $role = '' ) {

		$args = array(
			'fields'     => 'all',
			'meta_key'   => '_wpis_expire',
			'order'      => 'DESC',
			'orderby'    => 'meta_value',
			'meta_query' => array(
				0 => array(
					'key'   => '_wpis_user',
					'value' => 1,
				),
			),
		);

		if ( ! empty( $role ) ) {
			$args['role'] = $role;
		}

		$users = new WP_User_Query( $args );

		$users_data = $users->get_results();

		return $users_data;

	}

	/**
	 * Format time string
	 *
	 * @since 1.0
	 *
	 * @param int $stamp
	 * @param string $type
	 *
	 * @return false|string
	 */
	public static function format_date_display( $stamp = 0, $type = 'date_format' ) {

		$type_format = 'date_format';
		if ( 'date_format' === $type ) {
			$type_format = get_option( 'date_format' );
		} elseif ( 'time_format' === $type ) {
			$type_format = get_option( 'time_format' );
		}

		$timezone = get_option( 'timezone_string' );

		if ( empty( $timezone ) ) {
			return date( $type_format, $stamp );
		}

		$date = new DateTime( '@' . $stamp );

		$date->setTimezone( new DateTimeZone( $timezone ) );

		return $date->format( $type_format );

	}

	/**
	 * Get Redirection link
	 *
	 * @since 1.0
	 *
	 * @param array $result
	 *
	 * @return bool|string
	 */
	public static function get_redirect_link( $result = array() ) {

		if ( empty( $result ) ) {
			return false;
		}

		$base_url = menu_page_url( 'wp-intranet-security', false );

		if ( empty( $base_url ) ) {
			return false;
		}

		$query_string = '';
		if ( ! empty( $result['status'] ) ) {
			if ( 'success' === $result['status'] ) {
				$query_string .= '&wpis_success=1';
			} elseif ( 'error' === $result['status'] ) {
				$query_string .= '&wpis_error=1';
			}
		}

		if ( ! empty( $result['message'] ) ) {
			$query_string .= '&wpis_message=' . $result['message'];
		}

		if ( ! empty( $result['tab'] ) ) {
			$query_string .= '&tab=' . $result['tab'];
		}

		$redirect_link = $base_url . $query_string;

		return $redirect_link;

	}

	/**
	 * Can user have permission to manage temporary logins?
	 *
	 * @param int $user_id
	 *
	 * @return bool
	 */
	public static function can_manage_wpis( $user_id = 0 ) {

		if ( empty( $user_id ) ) {
			$user_id = get_current_user_id();
		}

		if ( empty( $user_id ) ) {
			return false;
		}

		// Don't give manage temporary users permission to temporary user
		$check = get_user_meta( $user_id, '_wpis_user', true );

		return ! empty( $check ) ? false : true;

	}

	/**
	 * Check if temporary login expired
	 *
	 * @since 1.0
	 *
	 * @param int $user_id
	 *
	 * @return bool
	 */
	public static function is_login_expired( $user_id = 0 ) {

		if ( empty( $user_id ) ) {
			$user_id = get_current_user_id();
		}

		if ( empty( $user_id ) ) {
			return false;
		}

		$expire = get_user_meta( $user_id, '_wpis_expire', true );

		return ! empty( $expire ) && self::get_current_gmt_timestamp() >= floatval( $expire ) ? true : false;

	}

	/**
	 * Generate Temporary Login Token
	 *
	 * @since 1.0
	 *
	 * @param $user_id
	 *
	 * @return string
	 */
	public static function generate_wpis_token( $user_id ) {
		$str = $user_id . time() . uniqid( '', true );

		return md5( $str );

	}

	/**
	 * Get valid temporary user based on token
	 *
	 * @since 1.0
	 *
	 * @param string $token
	 * @param string $fields
	 *
	 * @return array|bool
	 */
	public static function get_valid_user_based_on_wpis_token( $token = '', $fields = 'all' ) {
		if ( empty( $token ) ) {
			return false;
		}

		$args = array(
			'fields'     => $fields,
			'meta_key'   => '_wpis_expire',
			'order'      => 'DESC',
			'orderby'    => 'meta_value',
			'meta_query' => array(
				0 => array(
					'key'     => '_wpis_token',
					'value'   => sanitize_text_field( $token ),
					'compare' => '=',
				),
			),
		);

		$users = new WP_User_Query( $args );

		$users_data = $users->get_results();
		if ( empty( $users_data ) ) {
			return false;
		}

		foreach ( $users_data as $key => $user ) {
			$expire = get_user_meta( $user->ID, '_wpis_expire', true );
			if ( $expire <= self::get_current_gmt_timestamp() ) {
				unset( $users_data[ $key ] );
			}
		}

		return $users_data;

	}

	/**
	 * Checks whether user is valid temporary user
	 *
	 * @param int $user_id
	 * @param bool $check_expiry
	 *
	 * @return bool
	 */
	public static function is_valid_temporary_login( $user_id = 0, $check_expiry = true ) {

		if ( empty( $user_id ) ) {
			return false;
		}

		$check = get_user_meta( $user_id, '_wpis_user', true );

		if ( ! empty( $check ) && $check_expiry ) {
			$check = ! ( self::is_login_expired( $user_id ) );
		}

		return ! empty( $check ) ? true : false;

	}

	/**
	 * Get temporary login manage url
	 *
	 * @since 1.0
	 *
	 * @param $user_id
	 * @param string $action
	 *
	 * @return string
	 */
	public static function get_manage_login_url( $user_id, $action = '' ) {

		if ( empty( $user_id ) || empty( $action ) ) {
			return '';
		}

		$base_url = menu_page_url( 'wp-intranet-security', false );
		$args     = array();

		$valid_actions = array( 'disable', 'enable', 'delete', 'update' );
		if ( in_array( $action, $valid_actions ) ) {
			$args = array(
				'wpis_action' => $action,
				'user_id'      => $user_id,
			);
		}

		$manage_login_url = '';
		if ( ! empty( $args ) ) {
			$base_url         = add_query_arg( $args, trailingslashit( $base_url ) );
			$manage_login_url = wp_nonce_url( $base_url, 'manage-temporary-login_' . $user_id, 'manage-temporary-login' );
		}

		return $manage_login_url;

	}

	/**
	 * Get temporary login url
	 *
	 * @since 1.0
	 *
	 * @param $user_id
	 *
	 * @return string
	 */
	public static function get_login_url( $user_id ) {

		if ( empty( $user_id ) ) {
			return '';
		}

		$is_valid_temporary_login = self::is_valid_temporary_login( $user_id, false );
		if ( ! $is_valid_temporary_login ) {
			return '';
		}

		$wpis_token = get_user_meta( $user_id, '_wpis_token', true );
		if ( empty( $wpis_token ) ) {
			return '';
		}

		$login_url = add_query_arg( 'wpis_token', $wpis_token, trailingslashit( admin_url() ) );

		return $login_url;

	}

	/**
	 * Manage temporary logins
	 *
	 * @since 1.0
	 *
	 * @param int $user_id
	 * @param string $action
	 *
	 * @return bool
	 */
	public static function manage_login( $user_id = 0, $action = '' ) {

		if ( empty( $user_id ) || empty( $action ) ) {
			return false;
		}

		$is_valid_temporary_login = self::is_valid_temporary_login( $user_id, false );
		if ( ! $is_valid_temporary_login ) {
			return false;
		}

		$manage_login = false;
		if ( 'disable' === $action ) {
			$manage_login = update_user_meta( $user_id, '_wpis_expire', self::get_current_gmt_timestamp() );
		} elseif ( 'enable' === $action ) {
			$manage_login = update_user_meta( $user_id, '_wpis_expire', self::get_user_expire_time() );
		}

		if ( $manage_login ) {
			return true;
		}

		return false;

	}

	/**
	 * Get the redable time elapsed string
	 *
	 * @since 1.0
	 *
	 * @param int $time
	 * @param bool $ago
	 *
	 * @return string
	 */
	public static function time_elapsed_string( $time, $ago = false ) {

		if ( $ago ) {
			$etime = self::get_current_gmt_timestamp() - $time;
		} else {
			$etime = $time - self::get_current_gmt_timestamp();
		}

		if ( $etime < 1 ) {
			return __( 'Expired', WPIS_LANG );
		}

		$a = array(
			365 * 24 * 60 * 60 	=> 'year',
			30 * 24 * 60 * 60 	=> 'month',
			24 * 60 * 60 		=> 'day',
			60 * 60      		=> 'hour',
			60           		=> 'minute',
			1            		=> 'second',
		);

		$a_plural = array(
			'year'   => 'years',
			'month'  => 'months',
			'day'    => 'days',
			'hour'   => 'hours',
			'minute' => 'minutes',
			'second' => 'seconds',
		);

		foreach ( $a as $secs => $str ) {
			$d = $etime / $secs;

			if ( $d >= 1 ) {
				$r = round( $d );

				$time_string = ( $r > 1 ) ? $a_plural[ $str ] : $str;

				if ( $ago ) {
					return __( sprintf( '%d %s ago', $r, $time_string ), WPIS_LANG );
				} else {
					return __( sprintf( '%d %s remaining', $r, $time_string ), WPIS_LANG );
				}
			}
		}

		return __( 'Expired', WPIS_LANG );

	}

	/**
	 * Get all pages which needs to be blocked for temporary users
	 *
	 * @since 1.0
	 *
	 * @return array
	 */
	public static function get_blocked_pages() {
		$blocked_pages = array( 'user-new.php', 'user-edit.php', 'profile.php' );
		$blocked_pages = apply_filters( 'wpis_restricted_pages_for_temporary_users', $blocked_pages );

		return $blocked_pages;

	}

	/**
	 * Delete all temporary logins
	 *
	 * @since 1.0
	 */
	public static function delete_temporary_logins() {

		$temporary_logins = Wp_Intranet_Security_Common::get_temporary_logins();

		if ( count( $temporary_logins ) > 0 ) {
			foreach ( $temporary_logins as $user ) {
				if ( $user instanceof WP_User ) {
					wp_delete_user( $user->ID ); // Delete User
				}
			}
		}

	}

	/**
	 * Print out option html elements for multi role selectors.
	 *
	 * @since 1.5.2
	 *
	 * @param string $selected Slug for the role that should be already selected.
	 */
	public static function tlwp_multi_select_dropdown_roles( $selected_roles = array() ) {
		$r = '';

		$editable_roles = array_reverse( get_editable_roles() );

		foreach ( $editable_roles as $role => $details ) {
			$name = translate_user_role( $details['name'] );
			// preselect specified role
			if ( count( $selected_roles ) > 0 && in_array( $role, $selected_roles ) ) {
				$r .= "\n\t<option selected='selected' value='" . esc_attr( $role ) . "'>$name</option>";
			} else {
				$r .= "\n\t<option value='" . esc_attr( $role ) . "'>$name</option>";
			}
		}

		echo $r;
	}

	/**
	 * Get temporary_user details.
	 *
	 * @param int $user_id
	 *
	 * @since 1.5.3
	 *
	 * @return array
	 */
	public static function get_temporary_logins_data( $user_id = 0 ) {

		$user_data = array();
		if ( $user_id ) {

			$is_tlwp_user = get_user_meta( $user_id, '_wpis_user', true );

			if ( $is_tlwp_user ) {

				$temporary_user_info = get_userdata( $user_id );

				$email      = $temporary_user_info->user_email;
				$first_name = $temporary_user_info->first_name;
				$last_name  = $temporary_user_info->last_name;
				$role       = array_shift( $temporary_user_info->roles );

				$created_on  = get_user_meta( $user_id, '_wpis_created', true );
				$expire_on   = get_user_meta( $user_id, '_wpis_expire', true );
				$wpis_token = get_user_meta( $user_id, '_wpis_token', true );

				$user_data = array(
					'is_tlwp_user' => $is_tlwp_user,
					'email'        => $email,
					'first_name'   => $first_name,
					'last_name'    => $last_name,
					'created_on'   => $created_on,
					'expire_on'    => $expire_on,
					'wpis_token'  => $wpis_token,
					'role'         => $role
				);
			}

		}

		return $user_data;

	}

	/**
	 * Print out option html elements for role selectors.
	 *
	 * @since 1.5.2
	 *
	 * @param string $selected Slug for the role that should be already selected.
	 */
	public static function tlwp_dropdown_roles( $visible_roles = array(), $selected = '' ) {
		$r = '';

		$editable_roles = array_reverse( get_editable_roles() );
		$visible_roles = array();
		$visible_roles = ! empty( $visible_roles ) ? $visible_roles : array_keys( $editable_roles );

		/**
		 * NOTE: When edit tmeporary user - there may be a case where $selected role is not available in viisible roles
		 *  If so, add $selected role into $visible_roles array
		 */
		if ( ! in_array( $selected, $visible_roles ) ) {
			$visible_roles[] = $selected;
		}

		foreach ( $editable_roles as $role => $details ) {

			if ( in_array( $role, $visible_roles ) ) {
				$name = translate_user_role( $details['name'] );
				// preselect specified role
				if ( $selected == $role ) {
					$r .= "\n\t<option selected='selected' value='" . esc_attr( $role ) . "'>$name</option>";
				} else {
					$r .= "\n\t<option value='" . esc_attr( $role ) . "'>$name</option>";
				}
			}
		}

		echo $r;
	}

	/**
	 * Generate mailto link to send temporary login link directly into email
	 *
	 * @param $email
	 * @param $temporary_login_link
	 *
	 * @since 1.5.7
	 *
	 * @return string Generated mail to link
	 */
	public static function generate_mailto_link( $email, $temporary_login_link ) {

		$double_line_break  = '%0D%0A%0D%0A';    // as per RFC2368
		$mailto_greeting    = __( 'Hello,', WPIS_LANG );
		$mailto_instruction = __( 'Click the following link to log into the system:', WPIS_LANG );
		$mailto_subject     = __( 'Temporary Login Link', WPIS_LANG );
		$mailto_body        = $mailto_greeting . $double_line_break . $mailto_instruction . $double_line_break . $temporary_login_link . $double_line_break;

		$mailto_link = __( sprintf( "mailto:%s?subject=%s&body=%s", $email, $mailto_subject, $mailto_body ), WPIS_LANG );

		return $mailto_link;
	}

	/**
	 * Can we ask user for review?
	 *
	 * @param int $current_user_id
	 *
	 * @since  1.4.5
	 *
	 * @return bool
	 */
	public static function can_ask_for_review( $current_user_id ) {

		// Don't show 5 star review notice to temporary user
		if(!empty($current_user_id) && self::is_valid_temporary_login( $current_user_id )) {
			return false;
		}

		$tlwp_nobug         = get_user_meta( $current_user_id, 'tlwp_no_bug', true );
		$no_bug_days_before = 1;
		$tlwp_nobug_no_time = get_user_meta( $current_user_id, 'tlwp_no_bug_time', true );

		if ( ! empty( $tlwp_nobug_no_time ) && 0 !== $tlwp_nobug_no_time ) {
			$no_bug_time_diff   = time() - $tlwp_nobug_no_time;
			$no_bug_days_before = floor( $no_bug_time_diff / 86400 ); // 86400 seconds == 1 day
		}

		$tlwp_rated        = get_user_meta( $current_user_id, 'tlwp_admin_footer_text_rated', true );
		$tlwp_rated_header = get_user_meta( $current_user_id, 'tlwp_admin_header_text_rated', true );
		$temporary_logins  = self::get_temporary_logins();
		$total_logins      = count( $temporary_logins );

		// Is user fall in love with our plugin in 60 days after they said no for the review?
		// But, make sure we are asking user only after 60 days.
		// We are good people. Respect the user decision.
		if ( ( $tlwp_nobug && $no_bug_days_before < 60 ) || $tlwp_rated || $tlwp_rated_header || ( $total_logins < 1 ) ) {
			return false;
		}

		return true;
	}

}
