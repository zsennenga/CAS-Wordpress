<?php
/*
* Error setup, includes for ldap. Includes some wordpress stuff to handle user registration.
*/
require_once(ABSPATH . '/wp-admin/includes/plugin.php');
require_once(ABSPATH . WPINC . '/pluggable.php');
require_once(ABSPATH . 'wp-content/plugins/casPlugin/errors.php');
global $casError;
/*
* General LDAP helper class. Adds users to wordpress.
*/
class ldapUsers	{
	var $handle;
	var $base;
	var $res;
	/*
	* Set the search base 
	*/
	function __construct($str)	{
		$this->base = $str;
	}
	/*
	* LDAP Connection initializer
	*
	* @param string $str LDAP Server Host
	* @return bool success or failure of the initialization of the ldap handle
	* @note I don't do this in the constructor such that I can branch based on success/error. Error supression is for friendlier errors later.
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
	//General function to add users via a comma seperated list of identifiers.
	function doList($list,$param)	{
		//breakup the list
		global $casError;
		$arr = explode(',',$list);
		//This is are "were there 0 errors" function. We can't just die; on an error since the whole "comma seperated" part is handled here.
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
	//Don't want to sorry about a seperate CSS file. Maybe I should just make one.
	//Search the LDAP server this class is bound to. $search is the ldap search to perform.
	function search($search)	{
		//I suppress errors on this such that I can handle them later with more friendly messages than the php ones.
		//I only grab the data relevant to WP. Userid, email, first and last name.
		$this->res = @ldap_search($this->handle,$this->base, $search, array('uid','mail','givenName','sn'));
	}
}
?>