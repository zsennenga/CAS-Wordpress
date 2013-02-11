<?php
/*
* Include option menu components
*/
require_once(ABSPATH . 'wp-content/plugins/casPlugin/ldap.php');
require_once(ABSPATH . 'wp-content/plugins/casPlugin/casOptions.php');
/*
* Setup Error var
*/
global $casError;

/*
* Validate and send registration requests
*/
if (isset($_POST['subemail']) || isset($_POST['subuname']))	{
	//Set up ldap instance
	$ldap = new ldapUsers(LDAP_SEARCH_PATH);
	//Bind ldap to server
	if (!$ldap->init(LDAP_SERVER))	{
		//log error
		$casError->message("Unable to establish LDAP connection. Please contact your system administrator","error");
	}
	else	{
		//if email submitted do email stuff
		if (isset($_POST['subemail'])) 	{
			$param = "mail";
			$list = $_POST['email'];
		}
		//Else uid stuff
		else {
			$param = "uid";
			$list = $_POST['uname'];
		}
		if ($list != "")	{
			//Handle the list. Expecting comma separations
			$res = $ldap->doList($list,$param);
			if ($res)	{
				$casError->message("All users added successfully","success");
			}
			else	{
				$casError->message("At least one error occured. Not all users were added.","warning");
			}
		}
		else	{
			$casError->message("Please enter at least one value before pressing submit","error");
		}
	}
}
?>
