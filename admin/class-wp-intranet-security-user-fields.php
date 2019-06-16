<?php

/**
 * Main WP Intranet Security Admin Class
 *
 * Manage settings, Temporary Logins
 *
 * @since 1.0
 * @package WP Intranet Security
 */
class Wp_Intranet_Security_User {
	/**
	 * Plugin Name
	 *
	 * @var string $plugin_name
	 *
	 * @since 1.0
	 */
	private $plugin_name;

	/**
	 * Plugin Version
	 *
	 * @var string $version
	 *
	 * @since 1.0
	 */
	private $version;

	/**
	 * Initialize User Class
	 *
	 * @param string $plugin_name
	 * @param string $version
	 *
	 * @since 1.0
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name 		= $plugin_name;
		$this->version     		= $version;
		$this->temp_options 	= maybe_unserialize( get_option( 'tlwp_settings', array() ) );
	}

	
	public function extra_user_profile_fields( $user ) {

		$default_role 			= $user->roles[0];
		$default_expiry_time 	= isset( $this->temp_options['default_expiry_time'] ) ? $this->temp_options['default_expiry_time'] : 'week';
		$temp_login				= filter_var(get_user_meta( $user->ID, "_wpis_user", true ), FILTER_VALIDATE_BOOLEAN);
		$expire					= get_user_meta( $user->ID, "_wpis_expire", true );
		$expire_string 			= Wp_Intranet_Security_Common::time_elapsed_string( $expire );
		$temp_login 			= filter_var( get_user_meta( $user_id, "_wpis_user", true ), FILTER_VALIDATE_BOOLEAN);

		$wpis_status = '';
		if ( Wp_Intranet_Security_Common::is_login_expired( $user->ID ) ) {
			$wpis_status = 'Expired';
		}

		?>
		
		<h3><?php _e( "WP Intranet Security", "WPIS_LANG"); ?></h3>

	    <table class="form-table">

		<tr class="form-field">
			<th scope="row" class="wpis-form-row">
				<label for=""><?php echo esc_html__( 'Temporary Login Status', WPIS_LANG ); ?></label>
			</th>
			<td class="wpis-status-<?php echo strtolower( $wpis_status ); ?>">
				<?php _e( $wpis_status, WPIS_LANG); ?>
			</td>
		</tr>

	    <tr class="form-field">
			<th scope="row" class="wpis-form-row">
				<label for="new-user-expiry-time"><?php echo esc_html__( 'Change Expiry', WPIS_LANG ); ?></label>
			</th>
			<td>
				<span id="expiry-date-selection">
						<select name="wpis_data[expiry]" id="new-user-expiry-time">
							<?php Wp_Intranet_Security_Common::get_expiry_duration_html( $default_expiry_time ); ?>
						</select>
				</span>

				<span style="display:none;" id="new-custom-date-picker">
					<input type="date" id="datepicker" name="wpis_data[custom_date]" value="" class="new-custom-date-picker"/>
				</span>

			</td>
		</tr>
	    </table>
		<?php

	}


	public function save_module_user_profile_fields( $user_id ) {

		if ( empty( $user_id ) ) {
			return;
		}

		if ( !current_user_can( 'edit_user', $user_id ) ) { 
			return; 
		}

		extract($_POST);
		
		$expiry_option 	= ! empty( $_POST['wpis_data']['expiry'] ) ? $_POST['wpis_data']['expiry'] : 'day';
		$date          	= ! empty( $_POST['wpis_data']['custom_date'] ) ? $_POST['wpis_data']['custom_date'] : '';

		update_user_meta( $user_id, '_wpis_user', true );
		update_user_meta( $user_id, '_wpis_created', Wp_Intranet_Security_Common::get_current_gmt_timestamp() );
		update_user_meta( $user_id, '_wpis_expire', Wp_Intranet_Security_Common::get_user_expire_time( $expiry_option, $date ) );
		update_user_meta( $user_id, '_wpis_token', Wp_Intranet_Security_Common::generate_wpis_token( $user_id ) );

		update_user_meta( $user_id, 'show_welcome_panel', 0 );

	}


	public function wpis_user_list_columns( $columns = array() ) {
		
		if ( !isset( $columns['wpis_user'] ) ) {
			$columns['wpis_user'] = esc_html__('Temporary Login', 'learndash');
		}
		
	    return $columns;
	}

	public function wpis_list_column_content( $column_content = '', $column_name = '', $user_id = 0 ) {

		$temp_login 	= filter_var( get_user_meta( $user_id, "_wpis_user", true ), FILTER_VALIDATE_BOOLEAN);
		$expire         = get_user_meta( $user_id, '_wpis_expire', true );
		$wpis_status 	= 'Active';
		if ( Wp_Intranet_Security_Common::is_login_expired( $user_id ) ) {
			$wpis_status = 'Expired';
		}

		switch ( $column_name ) {
			case 'wpis_user' :
				if( $temp_login ) {
					$column_content = "<span class='wpis-status-" . strtolower( $wpis_status ) . "'> " . $wpis_status . " ";

					if ( ! empty( $expire ) ) {
						//$column_content .= Wp_Intranet_Security_Common::time_elapsed_string( $expire );
					}

					$column_content .= "</span>";
				}
				
			default:
		}

		return $column_content;

	}

}
