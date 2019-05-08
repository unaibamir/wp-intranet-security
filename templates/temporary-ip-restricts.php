<?php
/**
 * Temporary Login settings template
 *
 * @package WP Intranet Security
 */
?>

<h2> <?php echo esc_html__( 'IP Restricts', WPIS_LANG ); ?></h2>
<form method="post">
	<table class="form-table" id="rsa_handle_fields">

		<tr class="form-field">
			<th scope="row" class="wpis-form-row">
				<label for="redirect"><?php echo esc_html__( 'Site Visibility', WPIS_LANG ); ?></label>
			</th>
			<td>
				<a href="<?php echo admin_url( "options-reading.php" ); ?>"><?php echo esc_html__( 'Click here to change the website\'s visibility', WPIS_LANG ); ?></a>
			</td>
		</tr>
		
		<tr class="form-field">
			<th scope="row" class="wpis-form-row">
				<label for="visible_roles"><?php echo esc_html__( 'Handle restricted visitors', WPIS_LANG ); ?></label>
			</th>
			<td>
				<fieldset id="rsa_handle_fields">
					<input id="rsa-send-to-login" name="tlwp_settings_data[rsa_options][approach]" type="radio" <?php checked( $rsa_options["approach"], 1, true ); ?> value="1"  />
					<label for="rsa-send-to-login"><?php esc_html_e( 'Send them to the WordPress login screen', WPIS_LANG ); ?></label>
					<br />
					<input id="rsa-redirect-visitor" name="tlwp_settings_data[rsa_options][approach]" type="radio" <?php checked( $rsa_options["approach"], 2, true ); ?> value="2"  />
					<label for="rsa-redirect-visitor"><?php esc_html_e( 'Redirect them to a specified web address', WPIS_LANG ); ?></label>
					<br />
					<input id="rsa-display-message" name="tlwp_settings_data[rsa_options][approach]" type="radio" <?php checked( $rsa_options["approach"], 3, true ); ?> value="3"  />
					<label for="rsa-display-message"><?php esc_html_e( 'Show them a simple message', WPIS_LANG ); ?></label>

					<?php if ( ! is_network_admin() ) : ?>
						<br />
						<input id="rsa-unblocked-page" name="tlwp_settings_data[rsa_options][approach]" type="radio" <?php checked( $rsa_options["approach"], 4, true ); ?> value="4"  />
						<label for="rsa-unblocked-page"><?php esc_html_e( 'Show them a page', WPIS_LANG ); ?></label>
					<?php endif; ?>
				</fieldset>
			</td>
		</tr>

		<tr class="form-field">
			<th scope="row" class="wpis-form-row">
				<label for="redirect"><?php echo esc_html__( 'Redirect web address', WPIS_LANG ); ?></label>
			</th>
			<td>
				<input type="text" name="tlwp_settings_data[rsa_options][redirect_url]" id="redirect" class="rsa_redirect_field regular-text wpis-form-input" value="<?php echo $rsa_options["redirect_url"]; ?>" />
			</td>
		</tr>

		<tr class="form-field">
			<th scope="row" class="wpis-form-row">
				<label for="redirect_path"><?php echo esc_html__( 'Redirect to same path', WPIS_LANG ); ?></label>
			</th>
			<td>
				<fieldset><legend class="screen-reader-text"><span><?php echo esc_html__( 'Redirect to same path', WPIS_LANG ); ?></span></legend>
					<label for="redirect_path">
						<input type="checkbox" name="tlwp_settings_data[rsa_options][redirect_path]" value="1" id="redirect_path" class="rsa_redirect_field" <?php checked( $rsa_options["redirect_path"], 1, true ); ?> />
						<?php esc_html_e( 'Send restricted visitor to same path (relative URL) at the new web address', WPIS_LANG ); ?></label>
				</fieldset>
			</td>
		</tr>

		<tr class="form-field">
			<th scope="row" class="wpis-form-row">
				<label for="redirect_code"><?php echo esc_html__( 'Redirection status code', WPIS_LANG ); ?></label>
			</th>
			<td>
				<select name="tlwp_settings_data[rsa_options][head_code]" id="redirect_code" class="rsa_redirect_field">
					<option value="301" <?php  selected( $rsa_options["head_code"], 301, true ) ?>><?php esc_html_e( '301 Permanent', WPIS_LANG ); ?></option>
					<option value="302" <?php  selected( $rsa_options["head_code"], 302, true ) ?>><?php esc_html_e( '302 Undefined', WPIS_LANG ); ?></option>
					<option value="307" <?php  selected( $rsa_options["head_code"], 307, true ) ?>><?php esc_html_e( '307 Temporary', WPIS_LANG ); ?></option>
				</select>
			</td>
		</tr>

		<tr class="form-field">
			<th scope="row" class="wpis-form-row">
				<label for="rsa_message"><?php echo esc_html__( '', WPIS_LANG ); ?></label>
			</th>
			<td>
				<?php

				$message = !empty( $rsa_options["message"] ) ? $rsa_options["message"] : esc_html__( 'Access to this site is restricted.', WPIS_LANG );

				wp_editor(
					$message,
					'rsa_message',
					array(
						'media_buttons' => false,
						'textarea_name' => 'tlwp_settings_data[rsa_options][message]',
						'textarea_rows' => 4,
						'tinymce'       => false,
					)
				);
		?>
			</td>
		</tr>

		<tr class="form-field">
			<th scope="row" class="wpis-form-row">
				<label for="rsa_page"><?php echo esc_html__( 'Restricted notice page', WPIS_LANG ); ?></label>
			</th>
			<td>
				<?php
				$selected = !empty( $rsa_options["page"] ) ? $rsa_options["page"] : 0;
				wp_dropdown_pages(
					array(
						'selected'         => esc_html( $selected ),
						'show_option_none' => esc_html__( 'Select a page', WPIS_LANG ),
						'name'             => 'tlwp_settings_data[rsa_options][page]',
						'id'               => 'rsa_page',
					)
				);
				?>
			</td>
		</tr>


		<tr class="form-field">
			<th scope="row" class="wpis-form-row">
				<label for=""><?php echo esc_html__( 'Unrestricted IP addresses', WPIS_LANG ); ?></label>
			</th>
			<td>
				<div class="hide-if-no-js">
					<div id="ip_list">
						<div id="ip_list_empty" style="display: none;"><input type="text" name="tlwp_settings_data[rsa_options][allowed][]" class="ip code code-field" value="" readonly="true" size="20" /> <input type="text" name="tlwp_settings_data[rsa_options][comment][]" value="" class="comment code-field" size="20" /> <a href="#remove" class="remove_btn"><?php echo esc_html( _x( 'Remove', 'remove IP address action', WPIS_LANG ) ); ?></a></div>
					<?php
					$ips      = (array) $rsa_options['allowed'];
					$comments = isset( $rsa_options['comment'] ) ? (array) $rsa_options['comment'] : array();
					foreach ( $ips as $key => $ip ) {
						if ( ! empty( $ip ) ) {
							echo '<div><input type="text" name="tlwp_settings_data[rsa_options][allowed][]" value="' . esc_attr( $ip ) . '" class="ip code code-field" readonly="true" size="20" /> <input type="text" name="tlwp_settings_data[rsa_options][comment][]" value="' . ( isset( $comments[ $key ] ) ? esc_attr( wp_unslash( $comments[ $key ] ) ) : '' ) . '" size="20" class="code-field" /> <a href="#remove" class="remove_btn">' . esc_html_x( 'Remove', 'remove IP address action', WPIS_LANG ) . '</a></div>';
						}
					}
					?>
					</div>
					<div>
						<input type="text" name="newip" id="newip" class="ip code code-field" placeholder="<?php esc_attr_e( 'IP Address or Range' ); ?>" size="20" />
						<input type="text" name="newipcomment" class="code-field" id="newipcomment" placeholder="<?php esc_attr_e( 'Identify this entry' ); ?>" size="20" /> <input class="button" type="button" id="addip" value="<?php esc_attr_e( 'Add' ); ?>" />
						<p class="description"><label for="newip"><?php esc_html_e( 'Enter a single IP address or a range using a subnet prefix', WPIS_LANG ); ?></label></p>
						<?php if ( ! empty( $_SERVER['REMOTE_ADDR'] ) ) : ?>
							<input class="button" type="button" id="rsa_myip" value="<?php esc_attr_e( 'Add My Current IP Address', WPIS_LANG ); ?>" style="margin-top: 5px;" data-myip="<?php echo esc_attr( $client_ip_address ); ?>" /><br />
						<?php endif; ?>
					</div>

					<?php
					$config_ips = $config_ips;
					if ( ! empty( $config_ips ) ) :
						?>
						<div class="config_ips" style="margin-top: 10px;">
							<h4>
								<?php esc_html_e( 'Unrestricted IP addresses set by code configuration', WPIS_LANG ); ?>
							</h4>
							<ul class="ul-disc">
								<?php
								foreach ( $config_ips as $ip ) {
									echo '<li><code>' . esc_attr( $ip ) . '</code></li>';
								}
								?>
							</ul>
						</div>
					<?php endif; ?>
				</div>
				<p class="hide-if-js"><strong><?php esc_html_e( 'To manage IP addresses, you must use a JavaScript enabled browser.', WPIS_LANG ); ?></strong></p>
			</td>
		</tr>


		<!--  -->

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
