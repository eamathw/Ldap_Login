<?php
/*
Plugin Name: Ldap_Login
Version: auto
Description: Allow piwigo authentication along an ldap
Plugin URI: https://github.com/VSLCatena/ldap_login
Author: Netcie
Author URI: https://github.com/VSLCatena/
*/
if (!defined('PHPWG_ROOT_PATH')) die('Hacking attempt!');

// +-----------------------------------------------------------------------+
// | Define plugin constants                                               |
// +-----------------------------------------------------------------------+
define('LDAP_LOGIN_ID',      basename(dirname(__FILE__)));
define('LDAP_LOGIN_PATH' ,   __DIR__ . '/');
define('LDAP_LOGIN_ADMIN',   get_root_url() . 'admin.php?page=plugin-' . LDAP_LOGIN_ID);
define('LDAP_LOGIN_VERSION', '1.2');

include_once(LDAP_LOGIN_PATH.'/class.ldap.php');

// +-----------------------------------------------------------------------+
// | Event handlers                                                        |
// +-----------------------------------------------------------------------+

add_event_handler('init', 'ld_init');

add_event_handler('try_log_user','login', 0, 4);

add_event_handler('get_admin_plugin_menu_links', array(&$ldap, 'ldap_admin_menu'));

// +-----------------------------------------------------------------------+
// | Admin menu loading                                                    |
// +-----------------------------------------------------------------------+

$ldap = new Ldap();
$ldap->load_config();
set_plugin_data($plugin['id'], $ldap);
unset($ldap);

// +-----------------------------------------------------------------------+
// | functions                                                             |
// +-----------------------------------------------------------------------+

function random_password( $length = 8 ) {
    $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()_-=+;:,.?";
    $password = substr( str_shuffle( $chars ), 0, $length );
    return $password;
}

function ld_init(){
	load_language('plugin.lang', LDAP_LOGIN_PATH);
	global $conf;
}

function fail($username) {
	trigger_action('login_failure', stripslashes($username));
	return false;
}

function update_user($username,$id) {
	$up = new Ldap();
	$up->load_config();
	$up->ldap_conn() or error_log("Unable to connect LDAP server : ".$up->getErrorString());

	// update user piwigo rights / access according to ldap. Only if it's webmaster / admin, so no normal !
	if($up->ldap_status($username) !='normal') {
		single_update(USER_INFOS_TABLE,array('status' => $up->ldap_status($username)),array('user_id' => $id));
	}

	// search groups
	$group_query = 'SELECT name, id FROM '.GROUPS_TABLE.';';
	
	$result = pwg_query($group_query);
	$inserts = array();
	while ($row = pwg_db_fetch_assoc($result))
	{
		if($up->user_membership($username, $up->ldap_group($row['name']))) {
			$inserts[] = array('user_id' => $id,'group_id' => $row['id']);
		}
	}

	if (count($inserts) > 0)
	{
		mass_inserts(USER_GROUP_TABLE, array('user_id', 'group_id'), $inserts,array('ignore'=>true));
	}
}

function login($success, $username, $password, $remember_me){

	global $conf;
	$allow_auth = False;
	
	$obj = new Ldap();
	$obj->load_config();
	$obj->ldap_conn() or error_log("Unable to connect LDAP server : ".$obj->getErrorString());
	
	// if there's a users group...
	if ($obj->config['users_group']) {
		// and the user is in
		if ($obj->user_membership($username,$obj->ldap_group($obj->config['users_group']))) {
			// it can continue
			$allow_auth = True;
		}
		else
		{	// otherwise it means the user is not allowed to enter !
			fail($username);
		}
	}
	else {
	// if there's no user group, we can continue.
	$allow_auth = True;
	}
	
	if ($allow_auth) {
		if ($obj->ldap_bind_as($username,$password)){ // bind with userdn
			// search user in piwigo database
			$query = '
				SELECT	'.$conf['user_fields']['id'].' AS id
				FROM '.USERS_TABLE.'
				WHERE	'.$conf['user_fields']['username'].' = \''.pwg_db_real_escape_string($username).'\';';
			$row = pwg_db_fetch_assoc(pwg_query($query));

			// if query is not empty, it means everything is ok and we can continue, auth is done !
			if (!empty($row['id'])) {
				update_user($username,$row['id']);
				
				log_user($row['id'], $remember_me);
				trigger_action('login_success', stripslashes($username));
				
				return True;
			}
  	
			// if query is empty but ldap auth is done we can create a piwigo user if it's said so !
			else {
				// this is where we check we are allowed to create new users upon that.
				if ($obj->config['allow_newusers']) {
			
					// we got the email address
					if ($obj->ldap_mail($username)) {
						$mail = $obj->ldap_mail($username);
					}
					else {
						$mail = NULL;
					}
			
					// we actually register the new user
					$new_id = register_user($username,random_password(8),$mail);
					update_user($username,$new_id);
                        
					// now we fetch again his id in the piwigo db, and we get them, as we just created him !
					log_user($new_id, False);
					
					trigger_action('login_success', stripslashes($username));
					
					redirect('profile.php');
					return true;
				}
				// else : this is the normal behavior ! user is not created.
				else { fail($username); }
			}
		}
		// ldap_bind_as was not successful
		else { fail($username); }
	}
	// user is not allowed to auth or auth is wrong !
	else { fail($username); }
}
?>