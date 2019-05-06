<?php
/**
 * Admin Settings Template
 *
 * @package WP Intranet Security
 */

?>
<h2 class="nav-tab-wrapper">
    <?php if(! $is_temporary_login)  { ?>
        <a href="<?php echo esc_url( admin_url( 'options-general.php?page=wp-intranet-security&tab=ip-restricts' ) ); ?>" class="nav-tab <?php echo 'ip-restricts' === $active_tab ? 'nav-tab-active' : ''; ?>"><?php echo esc_html__( 'IP Restricts', WPIS_LANG ); ?></a>
        <a href="<?php echo esc_url( admin_url( 'options-general.php?page=wp-intranet-security&tab=white-list-users' ) ); ?>" class="nav-tab <?php echo 'white-list-users' === $active_tab ? 'nav-tab-active' : ''; ?>"><?php echo esc_html__( 'White List Users', WPIS_LANG ); ?></a>
        <a href="<?php echo esc_url( admin_url( 'options-general.php?page=wp-intranet-security&tab=home' ) ); ?>" class="nav-tab <?php echo 'home' === $active_tab ? 'nav-tab-active' : ''; ?>"><?php echo esc_html__( 'Temporary Logins', WPIS_LANG ); ?></a>
        <!-- <a href="<?php echo esc_url( admin_url( 'options-general.php?page=wp-intranet-security&tab=settings' ) ); ?>" class="nav-tab <?php echo 'settings' === $active_tab ? 'nav-tab-active' : ''; ?>"><?php echo esc_html__( 'Settings', WPIS_LANG ); ?></a> -->
	<?php } ?>
    <a href="<?php echo esc_url( admin_url( 'options-general.php?page=wp-intranet-security&tab=system-info' ) ); ?>" class="nav-tab <?php echo 'system-info' === $active_tab ? 'nav-tab-active' : ''; ?>"><?php echo esc_html__( 'System Info', WPIS_LANG ); ?></a>
</h2>

<?php if ( 'home' === $active_tab && !$is_temporary_login ) { ?>
    <div class="wrap wpis-settings-wrap" id="temporary-logins">
        <h2>
			<?php echo esc_html__( 'Temporary Logins', WPIS_LANG ); ?>
            <span class="page-title-action" id="add-new-wpis-form-button"><?php esc_html_e( 'Create New', WPIS_LANG ); ?></span>
        </h2>
        <div class="wpis-settings">
            <!-- Add New Form Start -->

            <div class="wrap new-wpis-form" id="new-wpis-form">
				<?php include WPIS_PLUGIN_DIR . '/templates/new-login.php'; ?>
            </div>

			<?php if ( $do_update ) { ?>

                <div class="wrap update-wpis-form" id="update-wpis-form">
					<?php include WPIS_PLUGIN_DIR . '/templates/update-login.php'; ?>
                </div>

			<?php } ?>

			<?php $wpis_generated_url = esc_url( $wpis_generated_url );
			if ( ! empty( $wpis_generated_url ) ) { ?>

                <div class="wrap generated-wpis-login-link" id="generated-wpis-login-link">
                    <p>
						<?php esc_attr_e( "Here's a temporary login link", WPIS_LANG ); ?>
                    </p>
                    <input id="wpis-click-to-copy-btn" type="text" class="wpis-wide-input" value="<?php echo esc_url( $wpis_generated_url ); ?>">
                    <button class="wpis-click-to-copy-btn" data-clipboard-action="copy" data-clipboard-target="#wpis-click-to-copy-btn"><?php echo esc_html__( 'Click To Copy', WPIS_LANG ); ?></button>
                    <span id="copied-text-message-wpis-click-to-copy-btn"></span>
                    <p>
						<?php
						esc_attr_e( 'User can directly login to WordPress admin panel without username and password by opening this link.', WPIS_LANG );
						if ( ! empty( $user_email ) ) {
							/* translators: %s: mailto link */
							echo __( sprintf( '<a href="%s">Email</a> temporary login link to user', $mailto_link ), WPIS_LANG ); //phpcs:ignore
						}
						?>
                    </p>

                </div>
			<?php } ?>
            <!-- Add New Form End -->

            <!-- List All Generated Logins Start -->
            <div class="wrap list-wpis-logins" id="list-wpis-logins">
				<?php load_template( WPIS_PLUGIN_DIR . '/templates/list-temporary-logins.php' ); ?>
            </div>
            <!-- List All Generated Logins End -->
        </div>
    </div>
<?php } elseif ( 'settings' === $active_tab && !$is_temporary_login) { ?>
    <div class="wrap list-wpis-logins" id="wpis-logins-settings">
		<?php include WPIS_PLUGIN_DIR . '/templates/temporary-logins-settings.php'; ?>
    </div>

<?php } elseif ( 'ip-restricts' === $active_tab && !$is_temporary_login) { ?>
    <div class="wrap list-wpis-logins" id="wpis-logins-settings">
        <?php include WPIS_PLUGIN_DIR . '/templates/temporary-logins-settings.php'; ?>
    </div>

<?php } elseif ( 'white-list-users' === $active_tab && !$is_temporary_login) { ?>
    <div class="wrap list-wpis-logins" id="wpis-logins-settings">
        <?php include WPIS_PLUGIN_DIR . '/templates/white-list-users.php'; ?>
    </div>

<?php } else {  ?>

    <div class="wrap tlwp-sytem-info" id="tlwp-system-info">
		<?php include WPIS_PLUGIN_DIR . '/templates/system-info.php'; ?>
    </div>
<?php }  ?>
