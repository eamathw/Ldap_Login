<?php
if (!defined('PHPWG_ROOT_PATH')) die('Hacking attempt!');

global $template;
$template->set_filenames( array('plugin_admin_content' => dirname(__FILE__).'/configuration.tpl') );
$template->assign(
  array(
    'PLUGIN_ACTION' => get_root_url().'admin.php?page=plugin-Ldap_Login-configuration',
    'PLUGIN_CHECK' => get_root_url().'admin.php?page=plugin-Ldap_Login-configuration',
    ));

$me = new Ldap();
$me->load_config();
//$me = get_plugin_data($plugin_id);

// Save LDAP configuration when submitted
if (isset($_POST['save'])){

	$me->config['forgot_url'] 	 = $_POST['FORGOT_URL'];
	$me->config['host'] 	 = $_POST['HOST'];
	$me->config['basedn']    = $_POST['BASEDN'];
	$me->config['port']      = $_POST['PORT'];
	$me->config['ld_server']   = $_POST['LD_SERVER'];
	$me->config['ld_attr']   = $_POST['LD_ATTR'];
	$me->config['ld_group']	 = $_POST['LD_GROUP'];
	$me->config['ld_group_class']	 = $_POST['LD_GROUP_CLASS'];
	$me->config['ld_group_member_attrib']	 = $_POST['LD_GROUP_MEMBER_ATTRIB'];
	$me->config['ld_binddn'] = $_POST['LD_BINDDN'];
	$me->config['ld_bindpw'] = $_POST['LD_BINDPW'];

	if (isset($_POST['LDAP_DEBUG'])){
		$me->config['ldap_debug'] = True;
	} else {
		$me->config['ldap_debug'] = False;
	}

	if (!isset($_POST['LD_BINDDN']) && !isset($_POST['LD_BINDPW']) ){
		$me->config['ld_anonbind'] = True;
	} else {
		$me->config['ld_anonbind'] = False;
	}

	if (isset($_POST['LD_USE_SSL'])){
		$me->config['ld_use_ssl'] = True;
	} else {
		$me->config['ld_use_ssl'] = False;
	}
	
	$me->save_config();
}

// Checki LDAP configuration
$me->ldap_conn();
if (isset($_POST['check_ldap'])){
	//$me->write_log("[function]> admin");
	//$check = $me->ldap_name($_POST['USERNAME']);
	$username = $me->ldap_search_dn($_POST['USERNAME']);
	//$me->write_log("[admin]> bind=".$username);
	$error=$me->check_ldap();
	if($error==1 && $username) {
		if ($me->ldap_bind_as($username,$_POST['PASSWORD'])){
			if($me->check_ldap_group_membership($username,$_POST['USERNAME'])){
	                        $template->assign('LD_CHECK_LDAP','<p style="color:green;">Configuration LDAP OK : '.$username.'</p>');
			} else {
				$template->assign('LD_CHECK_LDAP','<p style="color:orange;">Credentials OK, Check GroupMembership for: '.$username.'</p>');
			}
                }
                else {
			$template->assign('LD_CHECK_LDAP','<p style="color:red;"> Binding OK, but check credentials on server '.$me->config['uri'].' for user '.$username.'</p>');
                }
	} elseif($error==1 && !$username){
		$template->assign('LD_CHECK_LDAP','<p style="color:red;">Error : Binding OK, but no valid DN found on server '.$me->config['uri'].' for user '.$_POST['USERNAME'].'</p>');
	} elseif($error && $username){
		$template->assign('LD_CHECK_LDAP','<p style="color:red;">Error : Binding OK, but check credentials on '.$me->config['uri'].' for user '.$_POST['USERNAME'].'</p>');
	} else {
		$template->assign('LD_CHECK_LDAP','<p style="color:red;">Error : '.$error.' for binding on server '.$me->config['uri'].' for user '.$_POST['USERNAME'].', check your binding!</p>');
	}
}

// And build up the form with the new values
$template->assign('FORGOT_URL', 	$me->config['forgot_url']);
$template->assign('LDAP_DEBUG', 	$me->config['ldap_debug']);
$template->assign('HOST', 	$me->config['host']);
$template->assign('BASEDN',	$me->config['basedn']); // racine !
$template->assign('PORT', 	$me->config['port']);
$template->assign('LD_SERVER',	$me->config['ld_server']);
$template->assign('LD_ATTR',	$me->config['ld_attr']);
$template->assign('LD_GROUP',	$me->config['ld_group']);
$template->assign('LD_GROUP_CLASS',	$me->config['ld_group_class']);
$template->assign('LD_GROUP_MEMBER_ATTRIB',	$me->config['ld_group_member_attrib']);
$template->assign('LD_USE_SSL',	$me->config['ld_use_ssl']);
$template->assign('LD_BINDPW',	$me->config['ld_bindpw']);
$template->assign('LD_BINDDN',	$me->config['ld_binddn']);

$template->assign_var_from_handle( 'ADMIN_CONTENT', 'plugin_admin_content');
?>
