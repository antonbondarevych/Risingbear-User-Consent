<?php
if ( ! defined( 'ABSPATH' ) ) exit();
/*
	Plugin Name: Risingbear User Consent
	Description: Additional User Consent plugin for Beautiful and responsive cookie consent plugin
	Author: RisingBear Company
	Author URI: https://risingbear.no/
	Version: 0.03
	License: GPL2
 */

 if ( !in_array( 'beautiful-and-responsive-cookie-consent/nsc_bar-cookie-consent.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
	add_action( 'admin_notices', 'rb_uc_beautiful_and_responsive_cookie_deactivated', 10 );
	return;
 }

define('RB_UC_VERSION', '0.03');
define('RB_UC_PLUGIN_DIR', dirname(__FILE__));
define('RB_UC_PLUGIN_URL', plugin_dir_url( __FILE__ ));
define('RB_UC_PLUGIN_BASENAME_FILE', plugin_basename( __FILE__ ) );
define('RB_UC_PLUGIN_BASENAME_DIR', plugin_basename( __DIR__ ) );

require_once( RB_UC_PLUGIN_DIR . '/class/class-update-plugin.php' );
require_once( RB_UC_PLUGIN_DIR . '/class/class-consent-handler.php' );
require_once( RB_UC_PLUGIN_DIR . '/class/class-admin-pages.php' );
require_once( RB_UC_PLUGIN_DIR . '/class/class-forms-hooks-init.php' );


function rb_uc_beautiful_and_responsive_cookie_deactivated() {
	echo '<div class="error"><p><strong>Risingbear User Consent</strong> requires <strong>Beautiful and responsive cookie consent</strong> to be installed and active.</p></div>';
}


if (is_admin()) {
    add_filter("plugin_action_links_" . RB_UC_PLUGIN_BASENAME_FILE, 'rb_uc_plugin_settings_link');

	function rb_uc_plugin_settings_link($links) { 
		$settings_link = '<a href="options-general.php?page=tracking-code">Settings</a>'; 
		array_unshift( $links, $settings_link ); 
		return $links; 
	}
}