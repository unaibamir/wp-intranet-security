<?php
/**
 * Temporary Login settings template
 *
 * @package WP Intranet Security
 */

?>
<h2> <?php echo esc_html__( 'White List Users', WPIS_LANG ); ?></h2>
<form method="post">
	<table class="form-table wpis-form">
		<tr class="form-field">
			<th scope="row" class="wpis-form-row">
				<label for="user-roles"><?php echo esc_html__( 'User Roles', WPIS_LANG ); ?></label>
				<p class="description"><?php echo esc_html__( 'select roles which for white list', WPIS_LANG ); ?></p>

			</th>
			<td>
				<select multiple name="white_list_settings[user_roles][]" id="user-roles" class="white-list-roles-dropdown select2-dropdown">
					<?php Wp_Intranet_Security_Common::tlwp_multi_select_dropdown_roles( $white_list_user_grpups ); ?>
				</select>
			</td>
		</tr>
		<?php if ( class_exists( 'SFWD_LMS' ) ) { ?>
		<tr class="form-field">
			<th scope="row" class="wpis-form-row">
				<label for="ld-user-groups"><?php echo esc_html__( 'LearnDash User Groups', WPIS_LANG ); ?></label>
			</th>
			<td>
				<select multiple name="white_list_settings[ld_user_groups][]" id="ld-user-groups" class="default-role-dropdown select2-dropdown">
					<?php
					foreach ( learndash_get_groups() as $key => $group ) {
						$selected = in_array($group->ID, $white_list_ld_user_groups) ? 'selected="selected"' : '';
						echo '<option value="'.$group->ID.'" '.$selected.'  >' .$group->post_title. '</option>';
					}
					?>
				</select>
			</td>
		</tr>
		<?php } ?>
        <tr class="form-field">
			<th scope="row" class="wpis-form-row">
				<label for="white-list-users"><?php echo esc_html__( 'Users', WPIS_LANG ); ?></label>
			</th>
			<td>
				<?php
				$users = get_users( array( 'fields' => array( 'ID', 'display_name' ) ) );
				?>
                <select multiple name="white_list_settings[users][]" id="white-list-users" class="select2-dropdown">
					<?php
					foreach ( $users as $key => $user ) {
						$selected = in_array($user->ID, $white_list_users) ? 'selected="selected"' : '';
						echo '<option value="'.$user->ID.'" '.$selected.'  >' .$user->display_name. '</option>';
					}
					?>
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
