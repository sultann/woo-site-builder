<?php
/**
 * Plugin Name: Woo Site Builder Beta
 * Plugin URI:  http://pluginever.com
 * Description: The Plugin add a site builder to the site.
 * Version:     0.1.0
 * Author:      PluginEver
 * Author URI:  http://pluginever.com
 * Donate link: http://pluginever.com
 * License:     GPLv2+
 * Text Domain: woo_site_builder
 * Domain Path: /languages
 */

/**
 * Copyright (c) 2017 PluginEver (email : support@pluginever.com)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2 or, at
 * your discretion, any later version, as published by the Free
 * Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

// don't call the file directly
if ( !defined( 'ABSPATH' ) ) exit;
/**
 * Main initiation class
 */
class Woo_Site_Builder {

	public $version = '1.0.0';
	
	/**
	 * Sets up our plugin
	 * @since  0.1.0
	 */
	public function __construct() {

		register_activation_hook( __FILE__, array( $this, 'activate' ) );
		register_deactivation_hook( __FILE__, array( $this, 'deactivate' ) );
		add_action( 'admin_init', array( $this, 'admin_hooks' ) );
		add_action( 'init', [ $this, 'localization_setup' ] );


		$this->define_constants();
		$this->includes();
	}

	/**
	 * Activate the plugin
	 */
	function activate() {
		if (!in_array( 'woocommerce/woocommerce.php',apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ){

			deactivate_plugins( plugin_basename( __FILE__ ) );
		}

		// Make sure any rewrite functionality has been loaded
		flush_rewrite_rules();
	}

	/**
	 * Deactivate the plugin
	 * Uninstall routines should be in uninstall.php
	 */
	function deactivate() {

	}

	/**
	 * Initialize plugin for localization
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function localization_setup() {
		$locale = apply_filters( 'plugin_locale', get_locale(), 'woo_site_builder' );
		load_textdomain( 'woo_site_builder', WP_LANG_DIR . '/woo_site_builder/woo_site_builder-' . $locale . '.mo' );
		load_plugin_textdomain( 'woo_site_builder', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}



	/**
	 * Hooks for the Admin
	 * @since  0.1.0
	 * @return null
	 */
	public function admin_hooks() {

	}



	/**
	 * Include a file from the includes directory
	 * @since  0.1.0
	 * @param  string $filename Name of the file to be included
	 */
	public static function includes() {

		require WSB_INCLUDES.'/functions.php';
		require WSB_INCLUDES.'/builder.php';


	}


	/**
	 * Define Add-on constants
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	private function define_constants() {
		define( 'WSB_VERSION', $this->version );
		define( 'WSB_FILE', __FILE__ );
		define( 'WSB_PATH', dirname( WSB_FILE ) );
		define( 'WSB_INCLUDES', WSB_PATH . '/includes' );
		define( 'WSB_URL', plugins_url( '', WSB_FILE ) );
		define( 'WSB_ASSETS', WSB_URL . '/assets' );
		define( 'WSB_VIEWS', WSB_PATH . '/views' );
		define( 'WSB_TEMPLATES_DIR', WSB_PATH . '/templates' );
	}



	/**
	 * Display an error message if WP ERP is not active
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function admin_notice($type='error', $message) {
		printf(
			'%s'. __( $message, 'woo_site_builder' ) . '%s',
			'<div class="message '.$type.'"><p>',
			'</p></div>'
		);
	}





}

// init our class
$GLOBALS['Woo_Site_Builder'] = new Woo_Site_Builder();

/**
 * Grab the $Woo_Site_Builder object and return it
 */
function woo_site_builder() {
	global $Woo_Site_Builder;
	return $Woo_Site_Builder;
}