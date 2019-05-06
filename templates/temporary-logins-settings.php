<?php
/**
 * Temporary Login settings template
 *
 * @package WP Intranet Security
 */

?>
<h2> <?php echo esc_html__( 'Temporary Login Settings', WPIS_LANG ); ?></h2>
<form method="post">
	<table class="form-table wpis-form">
		<tr class="form-field">
			<th scope="row" class="wpis-form-row">
				<label for="visible_roles"><?php echo esc_html__( 'Visible Roles', WPIS_LANG ); ?></label>
				<p class="description"><?php echo esc_html__( 'select roles from which you want to create a temporary login', WPIS_LANG ); ?></p>

			</th>
			<td>
				<select multiple name="tlwp_settings_data[visible_roles][]" id="visible-roles" class="visible-roles-dropdown">
					<?php Wp_Intranet_Security_Common::tlwp_multi_select_dropdown_roles( $visible_roles ); ?>
				</select>
			</td>
		</tr>
		<tr class="form-field">
			<th scope="row" class="wpis-form-row">
				<label for="adduser-role"><?php echo esc_html__( 'Default Role', WPIS_LANG ); ?></label>
			</th>
			<td>
				<select name="tlwp_settings_data[default_role]" id="default-role" class="default-role-dropdown">
					<?php wp_dropdown_roles( $default_role ); ?>
				</select>
			</td>
		</tr>
        <tr class="form-field">
			<th scope="row" class="wpis-form-row">
				<label for="adduser-role"><?php echo esc_html__( 'Default Expiry Time', WPIS_LANG ); ?></label>
			</th>
			<td>
                <select name="tlwp_settings_data[default_expiry_time]" id="default-expiry-time">
					<?php Wp_Intranet_Security_Common::get_expiry_duration_html( $default_expiry_time, array('custom_date') ); ?>
                </select>
			</td>
		</tr>

		<tr class="form-field">
			<th scope="row" class="wpis-form-row"><label for="temporary-login-settings"></label></th>
			<td>
				<p class="submit">
					<input type="submit" class="button button-primary wpis-form-submit-button" value="<?php esc_html_e( 'Submit', WPIS_LANG ); ?>" class="button button-primary" id="generatetemporarylogin" name="generate_temporary_login">
				</p>
			</td>
		</tr>

		<?php wp_nonce_field( 'wpis_generate_login_url', 'wpis-nonce', true, true ); ?>
	</table>
</form>
