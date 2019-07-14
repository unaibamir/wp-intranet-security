<?php
/**
 * Create New Temporary Login template
 *
 * @package WP Intranet Security
 */

?>

<?php
$users = Wp_Intranet_Security_Common::get_non_temporary_logins();
?>

<form method="post">
	<div class="wrap create-new-or-existing-user">
		<table class="form-table wpis-form">
			<tr>
				<th><label for="new-user-type"><?php _e("Select User Type", WPIS_LANG); ?></label></th>
				<td>
					<select name="wpis_data[user_type]" id="new-user-type">
						<option value=""><?php _e("Please Select", WPIS_LANG); ?></option>
						<option value="new_user"><?php _e("New Temporary User", WPIS_LANG); ?></option>
						<?php if( !empty( $users ) ) : ?>
							<option value="existing_user"><?php _e("Existing User", WPIS_LANG); ?></option>
						<?php endif; ?>
					</select>
				</td>
			</tr>
		</table>

		<div id="existing-user-wpis-form" style="display: none;">
			<?php if( !empty( $users ) ) : ?>
				<table class="form-table wpis-form">
					<tr>
						<th><label for="existing_user_id"><?php _e("Select User", WPIS_LANG); ?></label></th>
						<td>
							<select name="wpis_data[existing_user_id]" id="existing_user_id" class="select2-dropdown">
								<option value=""><?php _e("Please Select User", WPIS_LANG); ?></option>
								<?php foreach ($users as $user) {
									?>
									<option value="<?php echo $user->ID ?>">
										<?php echo $user->user_login; ?> - <?php echo $user->user_email; ?> (<?php echo $user->roles[0]; ?>)
									</option>
									<?php
								} 
								?>
							</select>
						</td>
					</tr>
					<tr class="form-field">
						<th scope="row" class="wpis-form-row">
							<label for="existing-user-expiry-time"><?php echo esc_html__( 'Change Expiry', WPIS_LANG ); ?></label>
						</th>
						<td>
							<span id="expiry-date-selection">
									<select name="wpis_data[existing_user_expiry]" id="existing-user-expiry-time">
										<?php Wp_Intranet_Security_Common::get_expiry_duration_html( $default_expiry_time ); ?>
									</select>
							</span>

							<span style="display:none;" id="new-custom-date-picker" class="new-custom-date-picker-container">
								<input type="date"  name="wpis_data[existing_custom_date]" value="" class="new-custom-date-picker"/>
							</span>

						</td>
					</tr>
					<tr class="form-field">
						<th scope="row" class="wpis-form-row"><label for="adduser-role"></label></th>
						<td>
							<p class="submit">
								<input type="submit" class="button button-primary wpis-form-submit-button" value="<?php esc_html_e( 'Submit', WPIS_LANG ); ?>" class="button button-primary" id="generatetemporarylogin" name="generate_temporary_login"> <?php esc_html_e( 'or', WPIS_LANG ); ?>
								<span class="cancel-new-login-form" id="cancel-new-login-form"><?php esc_html_e( 'Cancel', WPIS_LANG ); ?></span>
							</p>
						</td>
					</tr>
					<?php wp_nonce_field( 'wpis_generate_login_url', 'wpis-nonce', true, true ); ?>
				</table>
			<?php endif; ?>
		</div>
					
		<div id="new-temp-login" style="display: none;">
			<h2> <?php echo esc_html__( 'Create a new Temporary Login', WPIS_LANG ); ?></h2>
			
			<table class="form-table wpis-form">
				<tr class="form-field form-required">
					<th scope="row" class="wpis-form-row">
						<label for="user_email"><?php echo esc_html__( 'Email*', WPIS_LANG ); ?> </label>
					</th>
					<td>
						<input name="wpis_data[user_email]" type="text" id="user_email" value="" aria-required="true" maxlength="60" class="wpis-form-input"/>
					</td>
				</tr>

				<tr class="form-field form-required">
					<th scope="row" class="wpis-form-row">
						<label for="user_first_name"><?php echo esc_html__( 'First Name', WPIS_LANG ); ?> </label>
					</th>
					<td>
						<input name="wpis_data[user_first_name]" type="text" id="user_first_name" value="" aria-required="true" maxlength="60" class="wpis-form-input"/>
					</td>
				</tr>

				<tr class="form-field form-required">
					<th scope="row" class="wpis-form-row">
						<label for="user_last_name"><?php echo esc_html__( 'Last Name', WPIS_LANG ); ?> </label>
					</th>
					<td>
						<input name="wpis_data[user_last_name]" type="text" id="user_last_name" value="" aria-required="true" maxlength="60" class="wpis-form-input"/>
					</td>
				</tr>
				<tr class="form-field">
					<th scope="row" class="wpis-form-row">
						<label for="adduser-role"><?php echo esc_html__( 'Role', WPIS_LANG ); ?></label>
					</th>
					<td>
						<select name="wpis_data[role]" id="user-role">
							<?php Wp_Intranet_Security_Common::tlwp_dropdown_roles( $visible_roles, $default_role ); ?>
						</select>
					</td>
				</tr>

				<tr class="form-field">
					<th scope="row" class="wpis-form-row">
						<label for="adduser-role"><?php echo esc_html__( 'Expiry', WPIS_LANG ); ?></label>
					</th>
					<td>
						<span id="expiry-date-selection">
								<select name="wpis_data[expiry]" id="new-user-expiry-time">
									<?php Wp_Intranet_Security_Common::get_expiry_duration_html( $default_expiry_time ); ?>
								</select>
						</span>

						<span style="display:none;" id="new-custom-date-picker" class="new-custom-date-picker-container">
							<input type="date"  name="wpis_data[custom_date]" value="" class="new-custom-date-picker"/>
						</span>

					</td>
				</tr>

				<tr class="form-field">
					<th scope="row" class="wpis-form-row"><label for="adduser-role"></label></th>
					<td>
						<p class="submit">
							<input type="submit" class="button button-primary wpis-form-submit-button" value="<?php esc_html_e( 'Submit', WPIS_LANG ); ?>" class="button button-primary" id="generatetemporarylogin" name="generate_temporary_login"> <?php esc_html_e( 'or', WPIS_LANG ); ?>
							<span class="cancel-new-login-form" id="cancel-new-login-form"><?php esc_html_e( 'Cancel', WPIS_LANG ); ?></span>
						</p>
					</td>
				</tr>
				<?php wp_nonce_field( 'wpis_generate_login_url', 'wpis-nonce', true, true ); ?>
			</table>
		</div>
	</div>
</form>
