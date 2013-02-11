<?php
/**
 * casAuthenticate.php
 * 
 * File containing casAuthenticate class and associated functions
 * 
 */
require_once(ABSPATH . 'wp-content/plugins/casPlugin/errors.php');
/**
 * casAuthenticate
 * 
 * Handles all interactions with CAS
 * 
 * @author its-zach
 *
 */
class casAuthenticate	{
	/**
	 * 
	 * Adds wordpress filters/hooks. Generates CAS client instance. Sets up validation.
	 * 
	 */
	function __construct()	{
		add_filter('authenticate', 'casAuthenticate::Auth', 10, 3);
		add_action('wp_logout', 'casAuthenticate::Logout');
		add_action('wp_head', 'casAuthenticate::Error');
		phpCAS::client(CAS_VERSION_2_0,CAS_SERVER,CAS_PORT, CAS_URL);
		//cURL cacert.pem -> cacert.crt so that we can verify the server
		phpCAS::setCasServerCACert(CAS_CERT);
	}
	/**
	 * Auth
	 * 
	 * Functions that performs the actual CAS Auth
	 * 
	 */
	public function Auth() {
		//Do auth
		phpCAS::forceAuthentication();
		//Remove core wordpress auth hook
		remove_action('authenticate', 'wp_authenticate_username_password', 20);
		//Valid auth, valid user
		if(phpCAS::checkAuthentication() && phpCAS::GetUser())	{
			//get wordpress user for given CAS user
			$user_id = username_exists(phpCAS::GetUser());
			//Display error if no matching wordpress user
			if (!$user_id)	{
				//set the noacc get var on the main site url
				wp_redirect(site_url().'?noacc'	);
				return;
			}
			//Get wordpress data for user
			$userdata = get_userdata($user_id);
			//Set wordpress user to previously selected user
			$user = set_current_user($user_id,$username);
			//Set cookie
			wp_set_auth_cookie($user_id);
			//Do the login action in wordpress backend
			do_action('wp_login',$userdata->ID);
			//redirect user to the main site page.
			wp_redirect(site_url());
		}
	}
	/**
	 * Logout
	 * 
	 * Logout helper function
	 * 
	 */
	public function Logout()	{
		phpCAS::logoutWithUrl(site_url());
	}
	/**
	 * Error
	 * 
	 * Error helper function. Handles displaying error after a successful CAS auth, but failed wordpress auth.
	 * 
	 */
	public static function Error()	{
		if (isset($_GET['noacc']))	{
			casError::messageNow("You don't have a valid account for this site. Please contact the site administrator.","error");
		}
	}
}
?>