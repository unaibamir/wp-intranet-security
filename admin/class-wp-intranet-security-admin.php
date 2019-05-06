<?php

/**
 * Main WP Intranet Security Admin Class
 *
 * Manage settings, Temporary Logins
 *
 * @since 1.0
 * @package WP Intranet Security
 */
class Wp_Intranet_Security_Admin {
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
	 * Initialize Admin Class
	 *
	 * @param string $plugin_name
	 * @param string $version
	 *
	 * @since 1.0
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;
	}

	/**
	 * Enqueue CSS
	 *
	 * @since 1.0
	 */
	public function enqueue_styles() {

		wp_enqueue_style( 'select2css', plugin_dir_url( __FILE__ ) . 'css/select2.min.css', array(), $this->version, 'all' );

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/wp-intranet-security-admin.css', array( 'select2css' ), $this->version, 'all' );

		wp_enqueue_style( 'jquery-ui-css', 'https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/themes/smoothness/jquery-ui.css' );
	}

	/**
	 * Enqueue JS
	 *
	 * @since 1.0
	 */
	public function enqueue_scripts() {

		wp_enqueue_script( 'select2js', plugin_dir_url( __FILE__ ) . 'js/select2.min.js', array( 'jquery' ), $this->version, false );
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/wp-intranet-security-admin.js', array( 'jquery', 'select2js' ), $this->version, false );
		wp_enqueue_script( 'clipboardjs', plugin_dir_url( __FILE__ ) . 'js/clipboard.min.js', array( 'jquery' ), $this->version, false );

		wp_enqueue_script( 'jquery' );
		wp_enqueue_script( 'jquery-ui-core' );
		wp_enqueue_script( 'jquery-ui-datepicker' );

		$data = array(
			'admin_ajax_url' => admin_url( 'admin-ajax.php', 'relative' ),
		);

		wp_localize_script( $this->plugin_name, 'data', $data );
	}

	/**
	 * Add admin menu for 'Temporary Logins' inside users section
	 *
	 * @since 1.0
	 */
	public function admin_menu() {
		add_options_page(
			__( 'WP Intranet Security', WPIS_LANG ), __( 'WP Intranet Security', WPIS_LANG ), apply_filters( 'tempadmin_user_cap', 'manage_options' ), 'wp-intranet-security', array(
				__class__,
				'admin_settings',
			)
		);
	}

	/**
	 * Manage admin settings
	 *
	 * @return void
	 *
	 * @since 1.0
	 */
	public static function admin_settings() {

		$_template_file = WPIS_PLUGIN_DIR . '/templates/admin-settings.php';

		$is_temporary_login = false;
		$current_user_id    = get_current_user_id();
		if ( Wp_Intranet_Security_Common::is_valid_temporary_login( $current_user_id ) ) {
			$is_temporary_login = true;
		}

		$active_tab = ! empty( $_GET['tab'] ) ? $_GET['tab'] : ( $is_temporary_login ? 'system-info' : 'home' );

		if ( ! $is_temporary_login ) {
			$wpis_generated_url = ! empty( $_REQUEST['wpis_generated_url'] ) ? $_REQUEST['wpis_generated_url'] : '';
			$user_email          = ! empty( $_REQUEST['user_email'] ) ? sanitize_email( $_REQUEST['user_email'] ) : '';
			$tlwp_settings       = maybe_unserialize( get_option( 'tlwp_settings', array() ) );
			$action              = ! empty( $_GET['action'] ) ? $_GET['action'] : '';
			$user_id             = ! empty( $_GET['user_id'] ) ? $_GET['user_id'] : '';
			$do_update           = ( 'update' === $action ) ? 1 : 0;

			if ( ! empty( $user_id ) ) {
				$temporary_user_data = Wp_Intranet_Security_Common::get_temporary_logins_data( $user_id );
			}

			if ( ! empty( $wpis_generated_url ) ) {
				$mailto_link = Wp_Intranet_Security_Common::generate_mailto_link( $user_email, $wpis_generated_url );
			}

			$default_role        		= ( ! empty( $tlwp_settings ) && isset( $tlwp_settings['default_role'] ) ) ? $tlwp_settings['default_role'] : 'administrator';
			$default_expiry_time 		= ( ! empty( $tlwp_settings ) && isset( $tlwp_settings['default_expiry_time'] ) ) ? $tlwp_settings['default_expiry_time'] : 'week';
			$visible_roles       		= ( ! empty( $tlwp_settings ) && isset( $tlwp_settings['visible_roles'] ) ) ? $tlwp_settings['visible_roles'] : array();
			$white_list_user_grpups 	= ( ! empty( $tlwp_settings ) && isset( $tlwp_settings['white_list_user_grpups'] ) ) ? $tlwp_settings['white_list_user_grpups'] : array();
			$white_list_ld_user_groups  = ( ! empty( $tlwp_settings ) && isset( $tlwp_settings['white_list_ld_user_groups'] ) ) ? $tlwp_settings['white_list_ld_user_groups'] : array();
			$white_list_users  			= ( ! empty( $tlwp_settings ) && isset( $tlwp_settings['white_list_users'] ) ) ? $tlwp_settings['white_list_users'] : array();
		}

		include $_template_file;
	}

	/**
	 * Create a Temporary user
	 *
	 * @return void
	 *
	 * @since 1.0
	 */
	public function create_user() {

		if ( empty( $_POST['wpis_data'] ) || empty( $_POST['wpis-nonce'] ) || ( ! empty( $_POST['wpis_action'] ) && 'update' === $_POST['wpis_action'] ) ) {
			return;
		}

		$data   = $_POST['wpis_data'];
		$email  = $data['user_email'];
		$error  = true;
		$result = array(
			'status' => 'error',
		);

		$redirect_link = '';
		if ( false == Wp_Intranet_Security_Common::can_manage_wpis() ) {
			$result['message'] = 'unathorised_access';
		} elseif ( ! wp_verify_nonce( $_POST['wpis-nonce'], 'wpis_generate_login_url' ) ) {
			$result['message'] = 'nonce_failed';
		} elseif ( empty( $data['user_email'] ) ) {
			$result['message'] = 'empty_email';
		} elseif ( ! is_email( $email ) ) {
			$result['message'] = 'not_valid_email';
		} elseif ( ! empty( $data['user_email'] ) && email_exists( $data['user_email'] ) ) {
			$result['message'] = 'email_is_in_use';
		} else {
			$error = false;
		}

		if ( ! $error ) {
			$user = Wp_Intranet_Security_Common::create_new_user( $data );
			if ( isset( $user['error'] ) && $user['error'] === true ) {
				$result = array(
					'status'  => 'error',
					'message' => 'user_creation_failed',
				);
			} else {
				$result = array(
					'status'  => 'success',
					'message' => 'user_created',
				);

				$user_id       = isset( $user['user_id'] ) ? $user['user_id'] : 0;
				$redirect_link = Wp_Intranet_Security_Common::get_redirect_link( $result );
				$redirect_link = add_query_arg( 'wpis_generated_url', Wp_Intranet_Security_Common::get_login_url( $user_id ), $redirect_link );
				$redirect_link = add_query_arg( 'user_email', $email, $redirect_link );
			}
		}

		if ( empty( $redirect_link ) ) {
			$redirect_link = Wp_Intranet_Security_Common::get_redirect_link( $result );
		}

		wp_safe_redirect( $redirect_link, 302 );
		exit();
	}

	/**
	 * Manage settings
	 *
	 * @return Void
	 *
	 * @since 1.4.6
	 */
	public function update_tlwp_settings() {

		if ( empty( $_POST['tlwp_settings_data'] ) || empty( $_POST['wpis-nonce'] ) ) {
			return;
		}

		$data = $_POST['tlwp_settings_data'];

		$default_role        		= isset( $data['default_role'] ) ? $data['default_role'] : 'administrator';
		$default_expiry_time 		= isset( $data['default_expiry_time'] ) ? $data['default_expiry_time'] : 'week';
		$visible_roles       		= isset( $data['visible_roles'] ) ? $data['visible_roles'] : array();
		$white_list_user_grpups    	= isset( $data['white_list_user_grpups'] ) ? $data['white_list_user_grpups'] : array();
		$white_list_ld_user_groups	= isset( $data['white_list_ld_user_groups'] ) ? $data['white_list_ld_user_groups'] : array();
		$white_list_users			= isset( $data['white_list_users'] ) ? $data['white_list_users'] : array();
		$ip_restricted       		= isset( $data['ip_restricted'] ) ? $data['ip_restricted'] : array();


		if ( ! in_array( $default_role, $visible_roles ) ) {
			$visible_roles[] = $default_role;
		}

		$tlwp_settings = array(
			'default_role'        		=> $default_role,
			'default_expiry_time' 		=> $default_expiry_time,
			'visible_roles'       		=> $visible_roles,
			'white_list_user_grpups'    => $white_list_user_grpups,
			'white_list_ld_user_groups' => $white_list_ld_user_groups,
			'white_list_users' 			=> $white_list_users,
			'ip_restricted'		  		=> $ip_restricted
		);

		update_option( 'tlwp_settings', "");

		$update = update_option( 'tlwp_settings', maybe_serialize( $tlwp_settings ), true );

		$result = array();
		if ( $update ) {
			$result = array(
				'status'  => 'success',
				'message' => 'settings_updated',
				'tab'     => isset( $_REQUEST["tab"] ) ? $_REQUEST["tab"] : 'home',
			);
		}

		$redirect_link = Wp_Intranet_Security_Common::get_redirect_link( $result );

		wp_redirect( $redirect_link, 302 );
		exit();
	}

	/**
	 * Manage temporary logins
	 *
	 * @since 1.0
	 */
	public static function manage_temporary_login() {

		// Don't have wpis_action or user_id? Say Good Bye...
		if ( empty( $_REQUEST['wpis_action'] ) || empty( $_REQUEST['user_id'] ) ) {
			return;
		}

		$action = $_REQUEST['wpis_action'];

		// We support following actions
		$valid_actions = array(
			'disable',
			'enable',
			'delete',
			'update',
		);

		if ( ! in_array( $action, $valid_actions ) ) {
			return;
		}

		// Can manage Temporary Logins? If yes..go ahead
		if ( ( false === Wp_Intranet_Security_Common::can_manage_wpis() ) ) {
			return;
		}

		$error   = false;
		$user_id = absint( $_REQUEST['user_id'] );
		$nonce   = $_REQUEST['manage-temporary-login'];
		$result  = array();

		// Perform action only on the valid temporary login
		$is_valid_temporary_user = Wp_Intranet_Security_Common::is_valid_temporary_login( $user_id, false );

		if ( ! $is_valid_temporary_user ) {
			$result = array(
				'status'  => 'error',
				'message' => 'is_not_temporary_login',
			);
			$error  = true;
		} elseif ( ! wp_verify_nonce( $nonce, 'manage-temporary-login_' . $user_id ) ) {
			$result = array(
				'status'  => 'error',
				'message' => 'nonce_failed',
			);
			$error  = true;
		}

		if ( ! $error ) {
			if ( 'disable' === $action ) {
				$disable_login = Wp_Intranet_Security_Common::manage_login( absint( $user_id ), 'disable' );
				if ( $disable_login ) {
					$result = array(
						'status'  => 'success',
						'message' => 'login_disabled',
					);
				} else {
					$result = array(
						'status'  => 'error',
						'message' => 'default_error_message',
					);
				}
			} elseif ( 'enable' === $action ) {
				$enable_login = Wp_Intranet_Security_Common::manage_login( absint( $user_id ), 'enable' );

				if ( $enable_login ) {
					$result = array(
						'status'  => 'success',
						'message' => 'login_enabled',
					);
				} else {
					$result = array(
						'status'  => 'error',
						'message' => 'default_error_message',
					);
				}
			} elseif ( 'delete' === $action ) {
				$delete_user = wp_delete_user( $user_id, get_current_user_id() );

				// delete user from Multisite network too!
				if ( is_multisite() ) {

					// If it's a super admin, we can't directly delete user from network site.
					// We need to revoke super admin access first and then delete user
					if ( is_super_admin( $user_id ) ) {
						revoke_super_admin( $user_id );
					}
					$delete_user = wpmu_delete_user( $user_id );
				}

				if ( ! is_wp_error( $delete_user ) ) {
					$result = array(
						'status'  => 'success',
						'message' => 'user_deleted',
					);
				} else {
					$result = array(
						'status'  => 'error',
						'message' => 'default_error_message',
					);
				}
			} elseif ( 'update' === $action ) {

				$data = ! empty( $_POST['wpis_data'] ) ? $_POST['wpis_data'] : array();

				$user_id = ! empty( $data['user_id'] ) ? $data['user_id'] : 0;

				$update = Wp_Intranet_Security_Common::update_user( $user_id, $data );

				if ( $update ) {
					$result = array(
						'status'  => 'success',
						'message' => 'user_updated',
					);
				} else {
					$result = array(
						'status'  => 'error',
						'message' => 'default_error_message',
					);
				}
			} else {
				$result = array(
					'status'  => 'error',
					'message' => 'invalid_action',
				);
			}// End if().
		}// End if().

		$redirect_link = Wp_Intranet_Security_Common::get_redirect_link( $result );
		wp_redirect( $redirect_link, 302 );
		exit();
	}

	/**
	 * Display Success/ Error message
	 *
	 * @since 1.0
	 */
	public function display_admin_notices() {

		if ( empty( $_REQUEST['page'] ) || ( empty( $_REQUEST['page'] ) && 'wp-intranet-security' !== $_REQUEST['page'] ) || ! isset( $_REQUEST['wpis_message'] ) || ( ! isset( $_REQUEST['wpis_error'] ) && ! isset( $_REQUEST['wpis_success'] ) ) ) { // Input var okay.
			return;
		}

		$messages = array(
			'user_creation_failed'    => __( 'User creation failed', WPIS_LANG ),
			'unathorised_access'      => __( 'You do not have permission to create a temporary login', WPIS_LANG ),
			'email_is_in_use'         => __( 'Email is already in use', WPIS_LANG ),
			'empty_email'             => __( 'Please enter valid email address. Email field should not be empty', WPIS_LANG ),
			'not_valid_email'         => __( 'Please enter valid email address', WPIS_LANG ),
			'is_not_temporary_login'  => __( 'User you are trying to delete is not temporary', WPIS_LANG ),
			'nonce_failed'            => __( 'Nonce failed', WPIS_LANG ),
			'invalid_action'          => __( 'Invalid action', WPIS_LANG ),
			'default_error_message'   => __( 'Unknown error occured', WPIS_LANG ),
			'user_created'            => __( 'Login created successfully!', WPIS_LANG ),
			'user_updated'            => __( 'Login updated successfully!', WPIS_LANG ),
			'user_deleted'            => __( 'Login deleted successfully!', WPIS_LANG ),
			'login_disabled'          => __( 'Login disabled successfully!', WPIS_LANG ),
			'login_enabled'           => __( 'Login enabled successfully!', WPIS_LANG ),
			'settings_updated'        => __( 'Settings have been updated successfully', WPIS_LANG ),
			'default_success_message' => __( 'Success!', WPIS_LANG ),
		);

		$class   = $message = '';
		$error   = ! empty( $_REQUEST['wpis_error'] ) ? true : false; // Input var okay.
		$success = ! empty( $_REQUEST['wpis_success'] ) ? true : false; // Input var okay.
		if ( $error ) {
			$message_type = ! empty( $_REQUEST['wpis_message'] ) ? $_REQUEST['wpis_message'] : 'default_error_message';
			$message      = $messages[ $message_type ];
			$class        = 'error';
		} elseif ( $success ) {
			$message_type = ! empty( $_REQUEST['wpis_message'] ) ? $_REQUEST['wpis_message'] : 'default_success_message';
			$message      = $messages[ $message_type ];
			$class        = 'updated';
		}

		$class .= ' notice notice-succe is-dismissible';

		if ( ! empty( $message ) ) {
			$notice = '';
			$notice .= '<div id="notice" class="' . $class . '">';
			$notice .= '<p>' . esc_attr( $message ) . '</p>';
			$notice .= '</div>';

			echo $notice;
		}

		return;
	}

	/**
	 * Disable welcome notification for temporary user.
	 *
	 * @param int $blog_id
	 * @param int $user_id
	 * @param string $password
	 * @param string $title
	 * @param string $meta
	 *
	 * @return bool
	 */
	public function disable_welcome_notification( $blog_id, $user_id, $password, $title, $meta ) {

		if ( ! empty( $user_id ) ) {
			$check_expiry = false;
			if ( Wp_Intranet_Security_Common::is_valid_temporary_login( $user_id, $check_expiry ) ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Change the admin footer text on temporary login admin pages.
	 *
	 * @since  1.4.3
	 *
	 * @param  string $footer_text
	 *
	 * @return string
	 */
	public function admin_footer_text( $footer_text ) {

		$current_screen = get_current_screen();

		if ( isset( $current_screen->id ) && 'settings_page_wp-intranet-security' === $current_screen->id ) {

			$current_user_id    = get_current_user_id();
			$can_ask_for_review = true; //Wp_Intranet_Security_Common::can_ask_for_review( $current_user_id );

			// Change the footer text.
			if ( $can_ask_for_review ) {
				$footer_text = sprintf( __( 'If you like <strong>WP Intranet Security</strong> plugin, please leave us a %s rating. A huge thanks in advance!', WPIS_LANG ), '<a href="https://wordpress.org/support/plugin/temporary-login-without-password/reviews" target="_blank" class="tlwp-rating-link" data-rated="' . esc_attr__( 'Thank You :) ', WPIS_LANG ) . '">&#9733;&#9733;&#9733;&#9733;&#9733;</a>' );
			} else {
				$footer_text = sprintf( __( 'Thank you for using %s.', WPIS_LANG ), '<a href="https://wordpress.org/plugins/temporary-login-without-password/" target="_blank">WP Intranet Security</a>' );
			}
		}

		return $footer_text;
	}

	/**
	 * Triggered when clicking the rating footer.
	 */
	public static function tlwp_rated() {
		$current_user_id = get_current_user_id();
		update_user_meta( $current_user_id, 'tlwp_admin_footer_text_rated', 1 );
		update_user_meta( $current_user_id, 'tlwp_review_time', time() );
		update_user_meta( $current_user_id, 'tlwp_review_from', 'footer' );
		wp_die();
	}

	/**
	 * Triggered when clicking the rating link from header.
	 */
	public static function tlwp_reivew_header() {
		$current_user_id = get_current_user_id();
		update_user_meta( $current_user_id, 'tlwp_admin_header_text_rated', 1 );
		update_user_meta( $current_user_id, 'tlwp_review_time', time() );
		update_user_meta( $current_user_id, 'tlwp_review_from', 'header' );
		wp_die();
	}

	/**
	 * Prepare a HTML for the review
	 *
	 * @since 1.4.5
	 */
	public function tlwp_ask_user_for_review() {

		$current_user_id = get_current_user_id();

		$nobug = '';

		if ( isset( $_GET['tlwp_nobug'] ) ) { // Input var okay.
			$nobug = absint( esc_attr( wp_unslash( $_GET['tlwp_nobug'] ) ) );
		}

		if ( 1 === $nobug ) {
			update_user_meta( $current_user_id, 'tlwp_no_bug', 1 );
			update_user_meta( $current_user_id, 'tlwp_no_bug_time', time() );
		}

		$current_user_id    = get_current_user_id();
		$can_ask_for_review = Wp_Intranet_Security_Common::can_ask_for_review( $current_user_id );

		if ( $can_ask_for_review ) {

			$reviewurl = 'https://wordpress.org/support/plugin/temporary-login-without-password/reviews/';

			$current_page_url = "//" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

			$nobugurl = add_query_arg( 'tlwp_nobug', 1, $current_page_url );

			echo '<div class="notice notice-warning">';

			echo sprintf( __( '<p>You have been using <b>WP Intranet Security</b> plugin, do you like it? If so, please leave us a review with your feedback! <a href="%s" class="tlwp-rating-link-header" target="_blank" data-rated="' . esc_attr__( 'Thank You :) ', WPIS_LANG ) . '">Leave A Review</a> <a href="%s">No, Thanks</a></p>' ), esc_url( $reviewurl ), esc_url( $nobugurl ) );

			echo '</div>';
		}
	}

	/**
	 *
	 * Disable plugin deactivation link for the temporary user
	 *
	 * @param array $actions
	 * @param string $plugin_file
	 * @param array $plugin_data
	 * @param string $context
	 *
	 * @since 1.4.5
	 *
	 * @return mixed
	 */
	public function disable_plugin_deactivation( $actions, $plugin_file, $plugin_data, $context ) {

		$current_user_id = get_current_user_id();
		if ( Wp_Intranet_Security_Common::is_valid_temporary_login( $current_user_id ) && ( 'wp-intranet-security/wp-intranet-security.php' === $plugin_file ) ) {
			unset( $actions['deactivate'] );
			echo "<script> jQuery(document).ready(function() { jQuery('table.plugins tbody#the-list tr[data-slug=temporary-login-without-password] th.check-column input').attr('disabled', true); }); </script>";
		}

		return $actions;
	}

	/**
	 * Add settings link
	 *
	 * @param array $links
	 *
	 * @since 1.5.7
	 *
	 * @return array
	 */
	public function plugin_add_settings_link( $links ) {

		$settings_link = '<a href="options-general.php?page=wp-intranet-security&tab=ip-restricts">' . __( 'Settings' ) . '</a>';
		$links[]       = $settings_link;

		return $links;
	}

	/**
	 * Display admin bar when Temporary user logged in.
	 *
	 * @param WP_Admin_Bar $wp_admin_bar WP_Admin_Bar instance, passed by reference.
	 *
	 * @since 1.5.4
	 *
	 * @return bool
	 */
	public function tlwp_show_temporary_access_notice_in_admin_bar( $wp_admin_bar ) {

		$current_user_id = get_current_user_id();

		$is_valid_temporary_user = Wp_Intranet_Security_Common::is_valid_temporary_login( $current_user_id );

		if ( $is_valid_temporary_user ) {
			// Add the main site admin menu item.
			$wp_admin_bar->add_menu(
				array(
					'id'     => 'temporay-access-notice',
					'href'   => admin_url(),
					'parent' => 'top-secondary',
					'title'  => __( 'Temporary Access', WPIS_LANG ),
					'meta'   => array( 'class' => 'temporay-access-mode-active' ),
				)
			);
		}

		return true;

	}

}
