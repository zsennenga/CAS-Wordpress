<?php
/**
 * ldap.php
 * 
 * Handles all interactions with the UC Davis LDAP Server
 * @author Zachary Ennenga
 */
require_once(ABSPATH . '/wp-admin/includes/plugin.php');
require_once(ABSPATH . WPINC . '/pluggable.php');
require_once(ABSPATH . 'wp-content/plugins/casPlugin/errors.php');
/**
 * @var Error object
 */
global $casError;
/**
 * ldapUsers
 * 
 * adds users via LDAP
 *
 */
class ldapUsers	{
	/**
	 * 
	 * @var LDAP Handle
	 */
	var $handle;
	/**
	 * 
	 * @var string
	 */
	var $base;
	/**
	 * 
	 * @var result set
	 */
	var $res;
	/**
	 * __construct
	 * 
	 * Sets the search base for all further queries
	 * 
	 * @param string $str
	 */
	function __construct($str)	{
		$this->base = $str;
	}
	/**
	 * 
	 * init
	 * LDAP Connection initializer
	 *
	 * @note I don't do this in the constructor such that I can branch based on success/error. Error supression is for friendlier errors later.
	 * @param string $str LDAP Server Host
	 * @return bool success or failure of the initialization of the ldap handle
	 */
	function init($str)	{
		$this->handle = @ldap_connect($str);
		if (!$this->handle)	{
			return false;
		}
		else	{
			if(@ldap_bind($this->handle))	{
				return true;
			}
			else	{
				return false;
			}
		}
	}
	/**
	 * 
	 * doList
	 * 
	 * Takes a list of uids/emails, and if they're found in ldap, adds them as wordpress users
	 * 
	 * @param string $list comma seperated list of who to add
	 * @param string $param controls either email or uid lookup
	 * @return boolean
	 */
	function doList($list,$param)	{
		//globalize error object
		global $casError;
		//break the list up
		$arr = explode(',',$list);
		//This is our "were there 0 errors" function. We can't just die; on an error since the whole "comma seperated" part is handled here.
		$retval = true;
		foreach($arr as $mem)	{
			//execture the search
			if ($mem == '' || $mem == null)	{
				$casError->message("Blank Entry Detected. Skipped.","warning");
				$retval = false;
			}
			else	{
				//Generic LDAp search parameters
				$this->search("$param=$mem");
				if($this->res)	{
					//half decent charset for a wordpress password
					//For security's sake we want to have a decent wordpress password, and since it is not (and should not) be in any way based on the CAS password
					//This is later hashed by wordpress
					$charset = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ!*&-@#$%^*;:_=+';
					$len = rand(5,10) + 10;
					$pass = "";
					//get the data
					$res = ldap_get_entries($this->handle,$this->res);
					for ($i = 0; $i < $len; $i++) {
						//generate a random 15-20 character password across a more or less complete set of characters.
						$pass .= $characters[rand(0, strlen($charset) - 1)];
					}
					if ($res['count'] == 0)	{
						$casError->message("$mem was not found. Make you spelled $mem correctly.","error");
						$retval = false;
					}
					else	{
						//add a user
						$res = wp_insert_user( array ('user_login' => $res[0]['uid'][0], 'user_pass' => $pass, 'user_email' => $res[0]['mail'][0], 'first_name' => $res[0]['givenname'][0], 'last_name' => $res[0]['sn'][0]));
						if (is_wp_error($res))	{
							$casError->message("Wordpress Error on $mem: " . $res->get_error_message(),"error");
							$retval = false;
						}
						else	{
							$casError->message("Successfully added $mem!","success");
						}
					}
				}
				else	{
					//Since LDAP is read only, and our prompt is behind the wp admin gate
					//I don't really care about malformed or "injected" queries. Just throw an error on invalid query and move on
					$casError->message("Invalid Search $mem. Make sure you are ONLY entering a comma seperated list of email addresses or usernames","error");
					$retval = false;
				}
			}
		}
		return $retval;
	}
	/**
	 * search
	 * 
	 * Perform an LDAP Query based on a predefined search base/query. Return only data useful for wordpress registrations.
	 * Only grabs info useful for wordpress registrations.
	 * 
	 * @param string $search
	 */
	function search($search)	{
		//I suppress errors on this such that I can handle them later with more friendly messages than the php ones.
		//I only grab the data relevant to WP. Userid, email, first and last name.
		$this->res = @ldap_search($this->handle,$this->base, $search, array('uid','mail','givenName','sn'));
	}
}
?>