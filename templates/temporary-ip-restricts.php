<?php
/**
 * Temporary Login settings template
 *
 * @package WP Intranet Security
 */
?>

<h2> <?php echo esc_html__( 'IP Restricts', WPIS_LANG ); ?></h2>
<form method="post">
	<table class="form-table">

		<tr class="form-field">
			<th scope="row" class="wpis-form-row">
				<label for="rsa-site-restrict"><?php echo esc_html__( 'Site Visibility', WPIS_LANG ); ?></label>
			</th>
			<td>
				<label class="switch">
					<input type="checkbox" id="rsa-site-restrict" name="tlwp_settings_data[rsa_options][site_restrict]" <?php checked( $rsa_options["site_restrict"], 1, true ); ?> value="1">
					<span class="slider round small"></span>
				</label>
				<p class="description"><?php echo esc_html__( 'Restrict site access to visitors who are logged in or allowed by IP address', WPIS_LANG ); ?></p>
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
				<label for="enable_emails"><?php echo esc_html__( 'Enable Emails', WPIS_LANG ); ?></label>
			</th>
			<td>
				<label class="switch">
					<?php $enable_emails = isset($rsa_options["enable_emails"]) ? $rsa_options["enable_emails"] : 0; ?>
					<input type="checkbox" id="enable_emails" name="tlwp_settings_data[rsa_options][enable_emails]" <?php checked( $enable_emails, 1, true ); ?> value="1">
					<span class="slider round small"></span>
				</label>
			</td>
		</tr>

		<tr class="form-field">
			<th scope="row" class="wpis-form-row">
				<label for="temp_mail_msg"><?php echo esc_html__( 'Temporary User Password Recovery Email Content', WPIS_LANG ); ?></label>
			</th>
			<td>
				<?php
				$message = !empty( $rsa_options["mail_msg"] ) ? $rsa_options["mail_msg"] : esc_html__( 'Access to this site is restricted.', WPIS_LANG );
				wp_editor(
					$message,
					'temp_mail_msg',
					array(
						'media_buttons' => true,
						'textarea_name' => 'tlwp_settings_data[rsa_options][mail_msg]',
						'textarea_rows' => 4,
						'tinymce'       => true,
					)
				);
				?>

				<p class="description">
					<?php echo esc_html__( "The following template tags are available for use in all of the email settings below.", WPIS_LANG ); ?>
				</p>
				<ul>
					<li><em>%username%</em> - <?php echo esc_html__( 'The user name of the member on the site', WPIS_LANG ); ?></li>
					<li><em>%firstname%</em> - <?php echo esc_html__( 'The first name of the member', WPIS_LANG ); ?></li>
					<li><em>%lastname%</em> - <?php echo esc_html__( 'The last name of the member', WPIS_LANG ); ?></li>
					<li><em>%displayname%</em> - <?php echo esc_html__( 'The display name of the member', WPIS_LANG ); ?></li>
					<li><em>%sitename%</em> - <?php echo esc_html__( 'The site name', WPIS_LANG ); ?></li>
					<li><em>%sitelink%</em> - <?php echo esc_html__( 'The site link', WPIS_LANG ); ?></li>
					<li><em>%recoverylink%</em> - <?php echo esc_html__( 'The recover link which will be sent to user', WPIS_LANG ); ?></li>
				</ul>
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
					<input type="hidden" name="tlwp_settings_data[rsa_options][head_code]" value="302">
					<input type="hidden" name="tab" value="ip-restricts">
					<input type="hidden" name="tlwp_settings_data[white_list_user_grpups][]" value="<?php echo $white_list_user_grpups; ?>">
				</p>
			</td>
		</tr>

		<?php wp_nonce_field( 'wpis_generate_login_url', 'wpis-nonce', true, true ); ?>		
		
	</table>
</form>
