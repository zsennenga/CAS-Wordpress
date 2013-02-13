<?php 
/* Plugin Name: CAS Auth System
 * Description: UC Davis CAS auth system for Wordpress. Requires php_curl and php_ldap.
 * Version: 1.0
 * Author: Zachary Ennenga
 */ 
?>

<?php
/**
 * casLogin.php
 * 
 * Main plugin file for the Cas Auth System Plugin. Initializes all core classes, sets up defines and includes.
 */
/*
 * CAS and LDAP server constants/defines
 * 
 */
define('CAS_SERVER', "cas.ucdavis.edu");
define('CAS_PORT', 8443);
define('CAS_URL', "/cas/");
define('CAS_CERT', ABSPATH . "wp-content\plugins\casPlugin\cacert.crt");

define('LDAP_SERVER',"ldap.ucdavis.edu");
define('LDAP_SEARCH_PATH',"ou=People,dc=ucdavis,dc=edu");
/*
 * Add CSS wordpress hook
 */
add_action( 'admin_enqueue_scripts', 'enqueueCSS' );
/*
 * Global error setup
 */
require_once(ABSPATH . 'wp-content/plugins/casPlugin/errors.php');

global $casError;
$casError = new casError();

/*
 * Wordpress includes
 */
require_once(ABSPATH . '/wp-admin/includes/plugin.php');
require_once(ABSPATH . WPINC . '/pluggable.php');

//Code here based on http://10up.com/blog/2012/10/wordpress-plug-in-self-deactivation/ to deactivate plugin 
//if you don't have the right php modules and display a more friendly error than the php ones
if ( !extension_loaded("curl") || !extension_loaded("ldap")) {
	if(current_user_can('activate_plugins')) {
		add_action('admin_init', 'casDeactivate');
        add_action('admin_notices', 'casNotice');
		function casDeactivate() {
			deactivate_plugins(plugin_basename(__FILE__));
        }
        function casNotice() {
			global $casError;
			$casError->messageNow("You need to activate php_ldap and php_curl for the CAS Auth System to work. Plugin Deactivated.","error");
			if(isset($_GET['activate'])) unset($_GET['activate']);
		}
    }
}
else	{
	/*
	 * Plugin Includes
	 */
	include_once(ABSPATH . 'wp-content/plugins/casPlugin/CAS.php');
	include_once(ABSPATH . 'wp-content/plugins/casPlugin/casAuthenticate.php');
	include_once(ABSPATH . 'wp-content/plugins/casPlugin/optionsMenu.php');

	/*
	 * Initialize plugin core classes
	 */
	new casAuthenticate();
	new casOptions();
}
/**
 * enqueueCSS
 * 
 * Adds CSS to wordpress enqueue
 */
function enqueueCSS()	{	
	wp_enqueue_style('messages','/wp-content/plugins/casPlugin/style.css');
}
?>