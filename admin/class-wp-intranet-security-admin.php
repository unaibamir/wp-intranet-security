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
	 * Plugin options.
	 *
	 * @var array $rsa_options The plugin options.
	 */
	private static $rsa_options;


	/**
	 * Plugin options.
	 *
	 * @var array $rsa_options The plugin options.
	 */
	private $temp_options;

	/**
	 * Initialize Admin Class
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

		$min    = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '';
		$folder = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : 'src/';

		wp_enqueue_script(
			'rsa-settings',
			plugin_dir_url( __FILE__ ) . 'js/' . $folder . 'settings' . $min . '.js',
			array( 'jquery-effects-shake' ),
			$this->version,
			true
		);

		$data = array(
			'admin_ajax_url' => admin_url( 'admin-ajax.php', 'relative' ),
		);

		wp_localize_script( $this->plugin_name, 'data', $data );


		$js_path = plugin_dir_url( __FILE__ ) . 'js/admin.min.js';

		$min    = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
		$folder = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? 'src/' : '';

		wp_enqueue_script(
			'rsa-admin',
			plugin_dir_url( __FILE__ ) . 'js/' . $folder . 'admin' . $min . '.js',
			array( 'jquery' ),
			$this->version,
			true
		);

		wp_localize_script(
			'rsa-admin',
			'rsaAdmin',
			array(
				'nonce' => wp_create_nonce( 'rsa_admin_nonce' ),
			)
		);
	}

	/**
	 * Add admin menu for 'Temporary Logins' inside users section
	 *
	 * @since 1.0
	 */
	public function admin_menu() {
		add_options_page(
			__( 'WP Intranet Security', WPIS_LANG ), __( 'WP Intranet Security', WPIS_LANG ), apply_filters( 'wpis_tempadmin_user_cap', 'manage_options' ), 'wp-intranet-security', array(
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

		$active_tab = ! empty( $_GET['tab'] ) ? $_GET['tab'] : ( $is_temporary_login ? 'system-info' : 'ip-restricts' );

		if ( ! $is_temporary_login ) {
			$wpis_generated_url  = ! empty( $_REQUEST['wpis_generated_url'] ) ? $_REQUEST['wpis_generated_url'] : '';
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

			$default_role        		= isset( $tlwp_settings['default_role'] ) ? $tlwp_settings['default_role'] : 'administrator';
			$default_expiry_time 		= isset( $tlwp_settings['default_expiry_time'] ) ? $tlwp_settings['default_expiry_time'] : 'week';
			$visible_roles       		= isset( $tlwp_settings['visible_roles'] ) ? $tlwp_settings['visible_roles'] : array();
			$rsa_options  				= isset( $tlwp_settings['rsa_options'] ) ? $tlwp_settings['rsa_options'] : array();

			$client_ip_address 			= self::get_client_ip_address();
			$config_ips 				= self::get_config_ips();
			$blog_public 				= get_option( 'blog_public' );


			$white_list_settings       	= get_option( 'white_list_settings', array() );
			$white_list_user_grpups 	= ( isset( $white_list_settings['user_roles'] ) ) ? $white_list_settings['user_roles'] : array();
			$white_list_ld_user_groups  = ( isset( $white_list_settings['ld_user_groups'] ) ) ? $white_list_settings['ld_user_groups'] : array();
			$white_list_users  			= ( isset( $white_list_settings['users'] ) ) ? $white_list_settings['users'] : array();
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
			'tab'  	 => 'home',
		);

		$redirect_link = '';

		if( !empty($data["user_type"]) && $data["user_type"] == "new_user" ) {
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
						'status'  	=> 'error',
						'message' 	=> 'user_creation_failed',
						'tab'  		=> 'home',
					);
				} else {
					$result = array(
						'tab'  		=> 'home',
						'status'  	=> 'success',
						'message' 	=> 'user_created',
					);

					$user_id       = isset( $user['user_id'] ) ? $user['user_id'] : 0;
					$redirect_link = Wp_Intranet_Security_Common::get_redirect_link( $result );
					$redirect_link = add_query_arg( 'wpis_generated_url', Wp_Intranet_Security_Common::get_login_url( $user_id ), $redirect_link );
					$redirect_link = add_query_arg( 'user_email', $email, $redirect_link );
				}
			}
		}

		if( !empty($data["user_type"]) && $data["user_type"] == "existing_user" ) {
			
			if ( false == Wp_Intranet_Security_Common::can_manage_wpis() ) {
				$result['message'] = 'unathorised_access';
			} elseif ( ! wp_verify_nonce( $_POST['wpis-nonce'], 'wpis_generate_login_url' ) ) {
				$result['message'] = 'nonce_failed';
			} elseif ( empty( $data['existing_user_id'] ) ) {
				$result['message'] = 'no_user_selected';
			} elseif ( empty( $data['existing_user_expiry'] ) ) {
				$result['message'] = 'no_existing_expiry';
			} else {
				$error = false;
			}

			if ( ! $error ) {

				$expiry_option 	= ! empty( $_POST['wpis_data']['existing_user_expiry'] ) ? $_POST['wpis_data']['existing_user_expiry'] : 'day';
				$date          	= ! empty( $_POST['wpis_data']['existing_custom_date'] ) ? $_POST['wpis_data']['existing_custom_date'] : '';

				$user_id 	 	= $data['existing_user_id'];

				update_user_meta( $user_id, '_wpis_user', true );
				update_user_meta( $user_id, '_wpis_created', Wp_Intranet_Security_Common::get_current_gmt_timestamp() );
				update_user_meta( $user_id, '_wpis_expire', Wp_Intranet_Security_Common::get_user_expire_time( $expiry_option, $date ) );
				update_user_meta( $user_id, '_wpis_token', Wp_Intranet_Security_Common::generate_wpis_token( $user_id ) );

				update_user_meta( $user_id, 'show_welcome_panel', 0 );

				$result = array(
					'tab'  		=> 'home',
					'status'  	=> 'success',
					'message' 	=> 'user_created',
				);

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

		$tlwp_settings 		= maybe_unserialize( get_option( 'tlwp_settings', array() ) );

		$rsa_options 		= !empty( $_POST["tlwp_settings_data"]["rsa_options"] ) ? self::sanitize_options( $_POST["tlwp_settings_data"]["rsa_options"] ) : $tlwp_settings["rsa_options"];

		/*$white_list_user_grpups 	= $_POST["tlwp_settings_data"]["white_list_user_grpups"];
		$white_list_users		 	= $_POST["tlwp_settings_data"]["white_list_users"];*/

		$_POST["tlwp_settings_data"]["rsa_options"]  			= $rsa_options;
		/*$_POST['tlwp_settings_data']["white_list_user_grpups"]  = $white_list_user_grpups;
		$_POST['tlwp_settings_data']["white_list_users"]  		= $white_list_users;

		if ( class_exists( 'SFWD_LMS' ) ) {
			$white_list_ld_user_groups = $_POST["tlwp_settings_data"]["white_list_ld_user_groups"];
			$_POST['tlwp_settings_data']["white_list_ld_user_groups"]  = $white_list_ld_user_groups;
		}*/

		$data 						= $_POST['tlwp_settings_data'];

		$default_role        		= isset( $data['default_role'] ) ? $data['default_role'] : 'administrator';
		$default_expiry_time 		= isset( $data['default_expiry_time'] ) ? $data['default_expiry_time'] : 'week';
		$visible_roles       		= isset( $data['visible_roles'] ) ? $data['visible_roles'] : array();
		/*$white_list_user_grpups    	= isset( $data['white_list_user_grpups'] ) ? $data['white_list_user_grpups'] : array();
		$white_list_ld_user_groups	= isset( $data['white_list_ld_user_groups'] ) ? $data['white_list_ld_user_groups'] : array();
		$white_list_users			= isset( $data['white_list_users'] ) ? $data['white_list_users'] : array();*/
		$ip_restricted       		= isset( $data['ip_restricted'] ) ? $data['ip_restricted'] : array();
		$rsa_options       			= isset( $data["rsa_options"] ) ? $data["rsa_options"] : array();


		if ( ! in_array( $default_role, $visible_roles ) ) {
			$visible_roles[] = $default_role;
		}

		$tlwp_settings = array(
			'default_role'        		=> $default_role,
			'default_expiry_time' 		=> $default_expiry_time,
			'visible_roles'       		=> $visible_roles,
			/*'white_list_user_grpups'    => $white_list_user_grpups,
			'white_list_ld_user_groups' => $white_list_ld_user_groups,
			'white_list_users' 			=> $white_list_users,*/
			'rsa_options'		  		=> $rsa_options
		);

		update_option( 'tlwp_settings', "");
		update_option( 'blog_public', $_POST["tlwp_settings_data"]["blog_public"]);

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


	public function update_white_list_settings() {

		if ( empty( $_POST['wpis-nonce-white'] ) ) {
			return;
		}

		$data = !empty( $_POST['white_list_settings'] ) ? $_POST['white_list_settings'] : array();
		
		update_option( 'white_list_settings', ''); // save way
		$update = update_option( 'white_list_settings', $data );

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
				'status'  	=> 'error',
				'message' 	=> 'is_not_temporary_login',
				'tab'  		=> 'home',
			);
			$error  = true;
		} elseif ( ! wp_verify_nonce( $nonce, 'manage-temporary-login_' . $user_id ) ) {
			$result = array(
				'status'  	=> 'error',
				'message' 	=> 'nonce_failed',
				'tab'  		=> 'home',
			);
			$error  = true;
		}

		if ( ! $error ) {
			if ( 'disable' === $action ) {
				$disable_login = Wp_Intranet_Security_Common::manage_login( absint( $user_id ), 'disable' );
				if ( $disable_login ) {
					$result = array(
						'status'  	=> 'success',
						'message' 	=> 'login_disabled',
						'tab'  		=> 'home',
					);
				} else {
					$result = array(
						'status'  	=> 'error',
						'message' 	=> 'default_error_message',
						'tab'  		=> 'home',
					);
				}
			} elseif ( 'enable' === $action ) {
				$enable_login = Wp_Intranet_Security_Common::manage_login( absint( $user_id ), 'enable' );

				if ( $enable_login ) {
					$result = array(
						'status'  => 'success',
						'message' => 'login_enabled',
						'tab'  		=> 'home',
					);
				} else {
					$result = array(
						'status'  => 'error',
						'message' => 'default_error_message',
						'tab'  		=> 'home',
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
						'tab'  		=> 'home',
					);
				} else {
					$result = array(
						'status'  => 'error',
						'message' => 'default_error_message',
						'tab'  		=> 'home',
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
						'tab'	  => 'home'
					);
				} else {
					$result = array(
						'status'  => 'error',
						'message' => 'default_error_message',
						'tab'  		=> 'home',
					);
				}
			} else {
				$result = array(
					'status'  => 'error',
					'message' => 'invalid_action',
					'tab'  		=> 'home',
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
			'no_user_selected'        => __( 'Please select user.', WPIS_LANG ),
			'no_existing_expiry'      => __( 'Please select user expiry time.', WPIS_LANG ),
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

		$settings_link = '<a href="'. admin_url( "options-reading.php" ) .'">' . __( 'Site Visibility' ) . '</a>';
		$settings_link .= ' | <a href="'. admin_url( "options-general.php?page=wp-intranet-security" ) .'">' . __( 'Settings' ) . '</a>';
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


	/**
	 * Retrieve the visitor ip address, even it is behind a proxy.
	 *
	 * @return string
	 */
	public static function get_client_ip_address() {
		$ip      = '';
		$headers = array(
			'HTTP_CLIENT_IP',
			'HTTP_X_FORWARDED_FOR',
			'HTTP_X_FORWARDED',
			'HTTP_X_CLUSTER_CLIENT_IP',
			'HTTP_FORWARDED_FOR',
			'HTTP_FORWARDED',
			'REMOTE_ADDR',
		);
		foreach ( $headers as $key ) {

			if ( ! isset( $_SERVER[ $key ] ) ) {
				continue;
			}

			foreach ( explode(
				',',
				sanitize_text_field( wp_unslash( $_SERVER[ $key ] ) )
			) as $ip ) {
				$ip = trim( $ip ); // just to be safe.

				if ( filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE ) !== false ) {
					return $ip;
				}
			}
		}

		return $ip;
	}


	/**
	 * Gets an array of valid IP addresses from constant.
	 *
	 * @return array
	 */
	public static function get_config_ips() {
		if ( ! defined( 'RSA_IP_WHITELIST' ) || ! RSA_IP_WHITELIST ) {
			return array();
		}

		if ( ! is_string( RSA_IP_WHITELIST ) ) {
			return array();
		}

		// Filter out valid IPs from configured ones.
		$raw_ips   = explode( '|', RSA_IP_WHITELIST );
		$valid_ips = array();
		foreach ( $raw_ips as $ip ) {
			$trimmed = trim( $ip );
			if ( self::is_ip( $trimmed ) ) {
				$valid_ips[] = $trimmed;
			}
		}
		return $valid_ips;
	}

	/**
	 * Validate IP address entry on demand (AJAX).
	 *
	 * @codeCoverageIgnore
	 */
	public function ajax_rsa_ip_check() {
		if ( ! check_ajax_referer( 'rsa_admin_nonce', 'nonce', false ) ) {
			wp_send_json_error();
			exit;
		}

		if ( empty( $_POST['ip_address'] ) || ! self::is_ip( stripslashes( sanitize_text_field( wp_unslash( $_POST['ip_address'] ) ) ) ) ) {
			die( '1' );
		}
		die;
	}

	/**
	 * Is it a valid IP address? v4/v6 with subnet range.
	 *
	 * @param string $ip_address IP Address to check.
	 *
	 * @return bool True if its a valid IP address.
	 */
	public static function is_ip( $ip_address ) {
		// very basic validation of ranges.
		if ( strpos( $ip_address, '/' ) ) {
			$ip_parts = explode( '/', $ip_address );
			if ( empty( $ip_parts[1] ) || ! is_numeric( $ip_parts[1] ) || strlen( $ip_parts[1] ) > 3 ) {
				return false;
			}
			$ip_address = $ip_parts[0];
		}

		// confirm IP part is a valid IPv6 or IPv4 IP.
		if ( empty( $ip_address ) || ! self::inet_pton( stripslashes( $ip_address ) ) ) {
			return false;
		}

		return true;
	}


	/**
	 * Sanitize RSA options.
	 *
	 * @param array $input The options to sanitize.
	 *
	 * @return array Sanitized input
	 */
	public static function sanitize_options( $input ) {
		$new_input['approach'] = (int) $input['approach'];
		if ( $new_input['approach'] < 1 || $new_input['approach'] > 4 ) {
			$new_input['approach'] = 1;
		}

		global $allowedtags;
		$new_input['message'] = wp_kses( $input['message'], $allowedtags );

		$new_input['redirect_path'] = empty( $input['redirect_path'] ) ? 0 : 1;
		$new_input['head_code']     = in_array( (int) $input['head_code'], array( 301, 302, 307 ), true ) ? (int) $input['head_code'] : 302;
		$new_input['redirect_url']  = empty( $input['redirect_url'] ) ? '' : esc_url_raw( $input['redirect_url'], array( 'http', 'https' ) );
		$new_input['page']          = empty( $input['page'] ) ? 0 : (int) $input['page'];

		$new_input['allowed'] = array();
		if ( ! empty( $input['allowed'] ) && is_array( $input['allowed'] ) ) {
			foreach ( $input['allowed'] as $ip_address ) {
				if ( self::is_ip( $ip_address ) ) {
					$new_input['allowed'][] = $ip_address;
				}
			}
		}
		$new_input['comment'] = array();
		if ( ! empty( $input['comment'] ) && is_array( $input['comment'] ) ) {
			foreach ( $input['comment'] as $comment ) {
				if ( is_scalar( $comment ) && !empty( $comment ) ) {
					$new_input['comment'][] = sanitize_text_field( $comment );
				}
			}
		}

		return $new_input;
	}


	/**
	 * Add a new choice to the privacy selector.
	 */
	public static function blog_privacy_selector() {
		global $wp;
		$is_restricted = ( 2 === (int) get_option( 'blog_public' ) );
		$is_restricted = apply_filters( 'wpis_restricted_site_access_is_restricted', $is_restricted, $wp );
		?>
		<p>
			<input id="blog-restricted" type="radio" name="blog_public" value="2" <?php checked( $is_restricted ); ?> />
			<label for="blog-restricted"><?php esc_html_e( 'Restrict site access to visitors who are logged in or allowed by IP address', 'restricted-site-access' ); ?></label>
		</p>
		<?php
	}


	/**
	 * Redirects restricted requests.
	 *
	 * @param array $wp WordPress request.
	 * @codeCoverageIgnore
	 */
	public static function restrict_access( $wp ) {

		$results = self::restrict_access_check( $wp );

		if ( is_array( $results ) && ! empty( $results ) ) {

			// Don't redirect during unit tests.
			if ( ! empty( $results['url'] ) && ! defined( 'WP_TESTS_DOMAIN' ) ) {
				wp_redirect( $results['url'], $results['code'] ); // phpcs:ignore WordPress.Security.SafeRedirect.wp_redirect_wp_redirect
				die();
			}

			// Don't die during unit tests.
			if ( ! empty( $results['die_message'] ) && ! defined( 'WP_TESTS_DOMAIN' ) ) {
				wp_die( wp_kses_post( $results['die_message'] ), esc_html( $results['die_title'] ), array( 'response' => esc_html( $results['die_code'] ) ) );
			}
		}
	}


	/**
	 * Determine whether page should be restricted at point of request.
	 *
	 * @param array $wp WordPress The main WP request.
	 * @return array              List of URL and code, otherwise empty.
	 */
	public static function restrict_access_check( $wp ) {

		$can_access 		= false;
		$tlwp_settings     	= maybe_unserialize( get_option( 'tlwp_settings', array() ) );
		self::$rsa_options 	= $tlwp_settings["rsa_options"];
		$is_restricted     	= self::is_restricted();

		// Check to see if it's _not_ restricted.
		if ( apply_filters( 'wpis_restricted_site_access_is_restricted', $is_restricted, $wp ) === false ) {
			return;
		}

		$allowed_ips = self::get_config_ips();
		if ( !empty( self::$rsa_options['allowed'] ) && is_array( self::$rsa_options['allowed'] ) ) {
			$allowed_ips = array_merge( $allowed_ips, self::$rsa_options['allowed'] );
		}

		// check for the allow list, if its empty block everything.
		if ( count( $allowed_ips ) > 0 ) {
			$remote_ip = self::get_client_ip_address();

			// iterate through the allow list.
			foreach ( $allowed_ips as $line ) {
				if ( self::ip_in_range( $remote_ip, $line ) ) {

					/**
					 * Fires when an ip address match occurs.
					 *
					 * Enables adding session_start() to the IP check, ensuring Varnish type cache will
					 * not cache the request. Passes the matched line; previous to 6.1.0 this action passed
					 * the matched ip and mask.
					 *
					 * @since 6.0.2
					 *
					 * @param string $remote_ip The remote IP address being checked.
					 * @param string $line      The matched masked IP address.
					 */
					do_action( 'wpis_restrict_site_access_ip_match', $remote_ip, $line );
					$can_access = true;
				}
			}
		}

		if( $is_restricted ) {
			$can_access 	= false;
		}

		if( $can_access ) {
			return;
		}



		$rsa_restrict_approach = apply_filters( 'wpis_restricted_site_access_approach', self::$rsa_options['approach'] );
		do_action( 'wpis_restrict_site_access_handling', $rsa_restrict_approach, $wp ); // allow users to hook handling.
		
		switch ( $rsa_restrict_approach ) {
			case 4: // Show them a page.
				if ( ! empty( self::$rsa_options['page'] ) ) {
					$page = get_post( self::$rsa_options['page'] );

					// If the selected page isn't found or isn't published, fall back to default values.
					if ( ! $page || 'publish' !== $page->post_status ) {
						self::$rsa_options['head_code']    = 302;
						$current_path                      = empty( $_SERVER['REQUEST_URI'] ) ? home_url() : sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) );
						self::$rsa_options['redirect_url'] = wp_login_url( $current_path );
						break;
					}

					// Are we already on the selected page?
					$on_selected_page = false;
					if ( isset( $wp->query_vars['page_id'] ) && absint( $wp->query_vars['page_id'] ) === $page->ID ) {
						$on_selected_page = true;
					}

					if ( ! $on_selected_page && ( isset( $wp->query_vars['pagename'] ) && $wp->query_vars['pagename'] === $page->post_name ) ) {
						$on_selected_page = true;
					}

					// There's a separate unpleasant conditional to match the page on front because of the way query vars are (not) filled at this point.
					if ( $on_selected_page || ( empty( $wp->query_vars ) && 'page' === get_option( 'show_on_front' ) && (int) get_option( 'page_on_front' ) === (int) self::$rsa_options['page'] ) ) {
						return;
					}

					self::$rsa_options['redirect_url'] = get_permalink( $page->ID );
					break;
				}
				// Fall thru to case 3 if case 2 not handled.
			case 3:
				$message  = esc_html( self::$rsa_options['message'] );
				$message  = apply_filters( 'wpis_restricted_site_access_message', $message, $wp );

				return array(
					'die_message' => $message,
					'die_title'   => esc_html( get_bloginfo( 'name' ) ) . ' - Site Access Restricted',
					'die_code'    => 403,
				);
			case 2:
				if ( ! empty( self::$rsa_options['redirect_url'] ) ) {
					if ( ! empty( self::$rsa_options['redirect_path'] ) ) {
						self::$rsa_options['redirect_url'] = untrailingslashit( self::$rsa_options['redirect_url'] ) . sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) );
					}
					break;
				}
				// No break, fall thru to default.
			default:
				self::$rsa_options['head_code']    = 302;
				$current_path                      = empty( $_SERVER['REQUEST_URI'] ) ? home_url() : sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) );
				self::$rsa_options['redirect_url'] = wp_login_url( $current_path );
		}

		$redirect_url  = apply_filters( 'wpis_restricted_site_access_redirect_url', self::$rsa_options['redirect_url'], $wp );
		$redirect_code = apply_filters( 'wpis_restricted_site_access_head', self::$rsa_options['head_code'], $wp );

		return array(
			'url'  => $redirect_url,
			'code' => $redirect_code,
		);
	}


	/**
	 * Determine if site should be restricted
	 */
	protected static function is_restricted() {

		$blog_public = get_option( 'blog_public', 2 );

		$user_check = self::user_can_access();

		$checks = is_admin() || $user_check || 2 !== (int) $blog_public || ( defined( 'WP_INSTALLING' ) && isset( $_GET['key'] ) );

		return ! $checks;
	}


	/**
	 * Check if current user has access.
	 *
	 * Can be short-circuited using the `restricted_site_access_user_can_access` filter
	 * to return a value other than null (boolean recommended).
	 *
	 * @return bool Whether the user has access
	 */
	protected static function user_can_access() {

		/**
		 * Filters whether the user can access the site before any other checks.
		 *
		 * Returning a non-null value will short-circuit the function
		 * and return that value instead.
		 *
		 * @param null|bool $access Whether the user can access the site.
		 */
		$access = apply_filters( 'wpis_restricted_site_access_user_can_access', null );

		if ( null !== $access ) {
			return $access;
		}

		if ( !is_user_logged_in() ) {
			return;
		}

		$can_access 			= false;
		$user 					= wp_get_current_user();
		$user_id 				= $user->ID;
		$user_role 				= $user->roles;
		$group_ids 				= class_exists( 'SFWD_LMS' ) ? learndash_get_users_group_ids( $user_id ) : array() ;
		$white_list_settings 	= get_option( 'white_list_settings', array() );
		
		if ( is_user_logged_in() && self::is_not_temp_admin() ) {
			$can_access = true;
		} elseif( is_user_logged_in() && !self::is_not_temp_admin() ) {
			if( !empty($white_list_settings["users"]) && in_array($user_id, $white_list_settings["users"]) ) {
				$can_access = true;
			}
			else if( !empty($white_list_settings["user_roles"]) && !empty( array_intersect($white_list_settings["user_roles"], $user_role) ) ) {
				$can_access = true;
			}
			else if( !empty($white_list_settings["ld_user_groups"]) && !empty( array_intersect($white_list_settings["ld_user_groups"], $group_ids) ) ) {
				$can_access = true;
			}
		} else {
			$can_access = false;
		}

		return apply_filters( "wpis_user_can_access", $can_access, $user );
	}


	/**
	 * Inet_pton is not included in PHP < 5.3 on Windows (WP requires PHP 5.2).
	 *
	 * @param string $ip IP Address.
	 *
	 * @return array|string
	 *
	 * @codeCoverageIgnore
	 */
	public static function inet_pton( $ip ) {
		if ( strpos( $ip, '.' ) !== false ) {
			// ipv4.
			$ip = pack( 'N', ip2long( $ip ) );
		} elseif ( strpos( $ip, ':' ) !== false ) {
			// ipv6.
			$ip  = explode( ':', $ip );
			$res = str_pad( '', ( 4 * ( 8 - count( $ip ) ) ), '0000', STR_PAD_LEFT );
			foreach ( $ip as $seg ) {
				$res .= str_pad( $seg, 4, '0', STR_PAD_LEFT );
			}
			$ip = pack( 'H' . strlen( $res ), $res );
		}
		return $ip;
	}


	/**
	 * Check if a given ip is in a network.
	 * Source: https://gist.github.com/tott/7684443
	 *
	 * @param  string $ip    IP to check in IPV4 format eg. 127.0.0.1.
	 * @param  string $range IP/CIDR netmask eg. 127.0.0.0/24, also 127.0.0.1 is accepted and /32 assumed.
	 * @return boolean true if the ip is in this range / false if not.
	 */
	public static function ip_in_range( $ip, $range ) {
		if ( strpos( $range, '/' ) === false ) {
			$range .= '/32';
		}
		// $range is in IP/CIDR format eg 127.0.0.1/24
		list( $range, $netmask ) = explode( '/', $range, 2 );
		$range_decimal           = ip2long( $range );
		$ip_decimal              = ip2long( $ip );
		$wildcard_decimal        = pow( 2, ( 32 - $netmask ) ) - 1;
		$netmask_decimal         = ~ $wildcard_decimal;
		return ( ( $ip_decimal & $netmask_decimal ) === ( $range_decimal & $netmask_decimal ) );
	}


	public static function is_not_temp_admin() {

		$current_user = wp_get_current_user();
		$temp_login = get_user_meta( $current_user->ID, "_wpis_user", true );

		if( $current_user->exists() && user_can($current_user, "manage_options") && empty( $temp_login ) ) {
			return true;
		}

		return false;
	}

}
