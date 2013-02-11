<?php
/*
* Setup Error, Include wordpress stuff to allow for some errors and whatnot
*/
require_once(ABSPATH . '/wp-admin/includes/plugin.php');
require_once(ABSPATH . WPINC . '/pluggable.php');

global $casError;
/*
* Quickie container class for the options menu and the like. Wordpress hook is created at construction, executed whenever wordpress says to.
* the doError(); function in doMenu(); is 95% of the error reporting in this plugin. Most of it having to do with LDAP stuff.
*/
class casOptions	{
	/*
	* Exists so we add the correct wordpress hook at the start of the program. Also adds the error handling hook.
	* @return instance of casOptions
	*/
	function __construct() {
		if (current_user_can("edit_users"))	{ 
			add_submenu_page( 'users.php','CAS Options', 'Add CAS Users', 'manage_options', 'casUsers', 'casOptions::doMenu' );
		}
		add_action('admin_notices', 'casError::doError');
	}
	
	/*
	* Meat of the wordpress hook.
	* @return void
	*/
	function doMenu() {
		global $casError;
		if ( !current_user_can('edit_users') )  {
			wp_die(__( 'You do not have sufficient permissions to access this page.'));
		}
		echo '<div class="wrap">';
		echo '<h2>Add CAS Users</h2>';
		echo '<p>Enter a comma seperated list of either email addresses or Kerberos Usernames of the users your want to add.</p>';
		echo '<form method="post" name="createuser" id="createuser" action="admin.php?page=casUsers">';
		echo '<div class="casLabel"><label>Add By Email: </label></div>
			  <div class="casField"><input size="100" name="email" type="text" id="email" value=""></div>
			  <div class="casButton"><input type="submit" name="subemail" id="subemail" class="button button-primary" value="Add via Email Addresses"></div>
			  <br/><div class="clear"></div><br/>
			  <div class="casLabel"><label>Add By Username: </label></div>
			  <div class="casField"><input size="100"  name="uname" type="text" id="uname" value=""></div>
			  <div class="casButton"><input  type="submit" name="subuname" id="subuname" class="button button-primary" value="Add via Kerberos Name"></p></div>';
		echo '</form>';
		echo '</div>';
	}
}
?>