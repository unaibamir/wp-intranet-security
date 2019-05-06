<?php
/**
 * Update Login template
 *
 * @package WP Intranet Security
 */

?>
<h2> <?php echo esc_html__( 'Update Temporary Login', WPIS_LANG ); ?></h2>
<form method="post">
	<table class="form-table wpis-form">
		<tr class="form-field form-required">
			<th scope="row" class="wpis-form-row">
				<label for="user_email"><?php echo esc_html__( 'Email', WPIS_LANG ); ?> </label>
			</th>
			<td>
				<label for="user_email"><?php echo esc_attr( $temporary_user_data['email'] ); ?></label>
			</td>
		</tr>

		<tr class="form-field form-required">
			<th scope="row" class="wpis-form-row">
				<label for="user_first_name"><?php echo esc_html__( 'First Name', WPIS_LANG ); ?> </label>
			</th>
			<td>
				<input name="wpis_data[user_first_name]" type="text" id="user_first_name" value="<?php echo esc_attr( $temporary_user_data['first_name'] ); ?>" aria-required="true" maxlength="60" class="wpis-form-input"/>
			</td>
		</tr>

		<tr class="form-field form-required">
			<th scope="row" class="wpis-form-row">
				<label for="user_last_name"><?php echo esc_html__( 'Last Name', WPIS_LANG ); ?> </label>
			</th>
			<td>
				<input name="wpis_data[user_last_name]" type="text" id="user_last_name" value="<?php echo esc_attr( $temporary_user_data['last_name'] ); ?>" aria-required="true" maxlength="60" class="wpis-form-input"/>
			</td>
		</tr>

		<?php if ( is_network_admin() ) { ?>
			<tr class="form-field form-required">
				<th scope="row" class="wpis-form-row">
					<label for="user_super_admin"><?php echo esc_html__( 'Super Admin', WPIS_LANG ); ?> </label>
				</th>
				<td>
					<input type="checkbox" id="user_super_admin" name="wpis_data[super_admin]">
					<?php echo esc_html__( 'Grant this user super admin privileges for the Network.', WPIS_LANG ); ?>
				</td>
			</tr>
		<?php } else { ?>
			<tr class="form-field">
				<th scope="row" class="wpis-form-row">
					<label for="adduser-role"><?php echo esc_html__( 'Role', WPIS_LANG ); ?></label>
				</th>
				<td>
					<select name="wpis_data[role]" id="user-role">
						<?php
						$role = $temporary_user_data['role'];
						Wp_Intranet_Security_Common::tlwp_dropdown_roles( $visible_roles, $role );
						?>
					</select>
				</td>
			</tr>
		<?php } ?>

		<tr class="form-field">
			<th scope="row" class="wpis-form-row">
				<label for="adduser-role"><?php echo esc_html__( 'Extend Expiry', WPIS_LANG ); ?></label>
			</th>
			<td>
				<span id="expiry-date-selection">
						<select name="wpis_data[expiry]" id="update-user-expiry-time">
							<?php Wp_Intranet_Security_Common::get_expiry_duration_html( 'week' ); ?>
						</select>
				</span>

				<span style="display:none;" id="update-custom-date-picker">
					<input type="date" id="datepicker" name="wpis_data[custom_date]" value="" class="update-custom-date-picker"/>
				</span>

			</td>
		</tr>

		<tr class="form-field">
			<th scope="row" class="wpis-form-row"><label for="adduser-role"></label></th>
			<td>
				<p class="submit">
					<input type="submit" class="button button-primary wpis-form-submit-button" value="<?php esc_html_e( 'Submit', WPIS_LANG ); ?>" class="button button-primary" id="generatetemporarylogin" name="generate_temporary_login"> <?php esc_html_e( 'or', WPIS_LANG ); ?>
					<span class="cancel-update-login-form" id="cancel-update-login-form"><?php esc_html_e( 'Cancel', WPIS_LANG ); ?></span>
				</p>
			</td>
		</tr>
		<input type="hidden" name="wpis_action" value="update"/>
		<input type="hidden" name="wpis_data[user_id]" value="<?php echo esc_attr( $user_id ); ?>"/>
		<?php wp_nonce_field( 'manage-temporary-login_' . $user_id, 'manage-temporary-login', true, true ); ?>
	</table>
</form>
