<?php
/**
 * Main class file
 *
 * @package WP Intranet Security
 */

/**
 * Class Wp_Intranet_Security
 *
 * @package WP Intranet Security
 */
class Wp_Intranet_Security {

	/**
	 * Loader.
	 *
	 * @var string $loader Loader.
	 */
	protected $loader;

	/**
	 * Plugin Name.
	 *
	 * @var string $plugin_name Plugin Name.
	 */
	protected $plugin_name;

	/**
	 * Plugin Version
	 *
	 * @var string $version Plugin Version.
	 */
	protected $version;

	/**
	 * Wp_Intranet_Security constructor.
	 */
	public function __construct() {

		$this->plugin_name = WPIS_LANG;
		$this->version     = WPIS_PLUGIN_VERSION;

		$this->load_dependencies();
		$this->define_admin_hooks();
		$this->define_public_hooks();
	}

	/**
	 * Load dependencies.
	 *
	 * @since 1.0.0
	 */
	private function load_dependencies() {

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wp-intranet-security-loader.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-wp-intranet-security-admin.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-wp-intranet-security-public.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wp-intranet-security-common.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wp-intranet-security-layout.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-wp-intranet-security-user-fields.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wpis-system-info.php';

		$this->loader = new Wp_Intranet_Security_Loader();
	}

	/**
	 * Define Admin Hooks.
	 *
	 * @since   1.0.0
	 */
	private function define_admin_hooks() {

		$plugin_admin 	= new Wp_Intranet_Security_Admin( $this->get_plugin_name(), $this->get_version() );
		$plugin_user 	= new Wp_Intranet_Security_User( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

		$this->loader->add_action( 'admin_menu', $plugin_admin, 'admin_menu' );
		$this->loader->add_action( 'network_admin_menu', $plugin_admin, 'admin_menu' );
		$this->loader->add_action( 'admin_init', $plugin_admin, 'create_user' );
		$this->loader->add_action( 'admin_init', $plugin_admin, 'update_tlwp_settings' );
		$this->loader->add_action( 'admin_init', $plugin_admin, 'update_white_list_settings' );
		$this->loader->add_action( 'admin_init', $plugin_admin, 'manage_temporary_login' );
		$this->loader->add_action( 'admin_notices', $plugin_admin, 'display_admin_notices' );
		$this->loader->add_action( 'wp_ajax_rsa_ip_check', $plugin_admin, 'ajax_rsa_ip_check' );
		$this->loader->add_action( 'blog_privacy_selector', $plugin_admin, 'blog_privacy_selector' );
		$this->loader->add_action( 'parse_request', $plugin_admin, 'restrict_access' );
		//$this->loader->add_action( 'show_user_profile', $plugin_user, 'extra_user_profile_fields', 9 );
		$this->loader->add_action( 'edit_user_profile', $plugin_user, 'extra_user_profile_fields', 9 );
		//$this->loader->add_action( 'personal_options_update', $plugin_user, 'save_module_user_profile_fields', 9 );
		$this->loader->add_action( 'edit_user_profile_update', $plugin_user, 'save_module_user_profile_fields', 9 );
		$this->loader->add_action( 'manage_users_columns', $plugin_user, 'wpis_user_list_columns', 10 );
		$this->loader->add_action( 'manage_users_custom_column', $plugin_user, 'wpis_list_column_content', 10, 3 );

		$this->loader->add_filter( 'plugin_action_links', $plugin_admin, 'disable_plugin_deactivation', 10, 4 );
		$this->loader->add_filter( 'plugin_action_links_' . WPIS_PLUGIN_BASE_NAME, $plugin_admin, 'plugin_add_settings_link', 10, 4 );
	}

	/**
	 * Defind Admin hooks.
	 *
	 * @since 1.0.0
	 */
	private function define_public_hooks() {

		$plugin_public = new Wp_Intranet_Security_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'init', $plugin_public, 'init_wpis' );
		$this->loader->add_filter( 'wp_authenticate_user', $plugin_public, 'disable_temporary_user_login', 10, 2 );
		$this->loader->add_filter( 'allow_password_reset', $plugin_public, 'disable_password_reset', 10, 2 );
	}

	/**
	 * Start Loading.
	 *
	 * @since 1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * Get Plugin Name.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * Get Loader Class.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Get Plugin Version
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function get_version() {
		return $this->version;
	}

}
