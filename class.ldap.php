<?php


global $conf;
class Ldap {
	var $cnx;
	var $config;
	var $groups = array();
	var $warn_msg = array();
	var	$default_val = array(
		'ld_forgot_url' => 'password.php',
		'ld_debug_location' =>'./plugins/ldap_login/logs/',
		'ld_debug' => 1,
		'ld_debug_clearupdate' => 1,
		'ld_debug_level' => 'debug',
		'ld_host' => 'localhost',
		'ld_port' => '389',
		'ld_basedn' => 'ou=domain,dc=domain,dc=tld',
		'ld_user_class' => 'person',
		'ld_user_attr' => 'samaccountName',
		'ld_user_filter' => null,
		'ld_group_class' => 'group',
		'ld_group_filter' => null,
		'ld_group_attr' => 'name',
		'ld_group_desc' => 'description',
		'ld_group_basedn' => 'cn=groups,ou=domain,dc=domain,dc=tld',
		'ld_group_member_attr' => 'member',
		'ld_user_member_attr' => 'memberOf',
		'ld_group_webmaster' => 'cn=webmasters,cn=groups,ou=domain,dc=domain,dc=tld',
		'ld_group_admin' => 'cn=admins,cn=groups,ou=domain,dc=domain,dc=tld',
		'ld_group_user' => 'cn=users,cn=groups,ou=domain,dc=domain,dc=tld',
		'ld_binddn' => 'cn=service_account, ou=admins, ou=domain, dc=domain,dc=tld',
		'ld_bindpw' => null,
		'ld_anonbind' => 0,
		'ld_use_ssl' => 0,
		'ld_membership_user' => 0,
		'ld_group_user_active' => 1,
		'ld_group_admin_active' => 0,
		'ld_group_webmaster_active' => 0,
		'ld_sync_data' => null,
		'ld_allow_newusers' => 1,
		'ld_use_mail'=> 1,
		'ld_allow_profile' => 1,
		'ld_advertise_admin_new_ldapuser' => 0,
		'ld_send_password_by_mail_ldap' => 0
		);

    
    public function __construct($host, $port, $baseDn, $bindDn, $bindPassword, $userFilter, $attributes)
    {    
        global $ld_config,$ld_log;
        $this->config = $ld_config;
        $this->log= $ld_log;
        
        if(!extension_loaded('ldap')){
            $this->log->critical("[".basename(__FILE__)."/".__FUNCTION__."]> LDAP extension not loaded, see php_ldap module.");
        }
        
        $this->connection = @ldap_connect($host, $port) or throw new Exception();
        if(!$this->connection)
        {
            $this->log->critical("[".basename(__FILE__)."/".__FUNCTION__."]> Could not connect to LDAP server.");
        }
        $this->baseDn = $baseDn;
        $this->bindDn = $bindDn;
        $this->bindPassword = $bindPassword;
        $this->userFilter = $userFilter;
        $this->attributes = $attributes;

        ldap_set_option($this->connection, LDAP_OPT_PROTOCOL_VERSION, 3);
        ldap_set_option($this->connection, LDAP_OPT_REFERRALS, 0);
        
        $this->bind();
    }

    public function __destruct()
    {
        ldap_close($this->connection);
    }

    private function bind()
    {
        if (!ldap_bind($this->connection, $this->bindDn, $this->bindPassword)) {
            $this->log->critical("[".basename(__FILE__)."/".__FUNCTION__."]> Could not bind to LDAP server.");
            return false;
        }
    }
    
    
    public function authenticate($username, $password)
    {
        $userDn = $this->getUserDn($username);

        if (!$userDn) {
            return false;
        }

        if (ldap_bind($this->connection, $userDn, $password)) {
            return true;
        }

        return false;
    }

    public function getUserDn($username)
    {

        $searchResult = ldap_search($this->connection, $this->baseDn, $this->getUserFilter($username), $this->attributes);

        if($searchResult == false){
            $this->log->warning("[".basename(__FILE__)."/".__FUNCTION__."]> ".ldap_err2str(ldap_errno($this->connection)));
            return false;
        }
        $entries = ldap_get_entries($this->connection, $searchResult);
        if ($entries['count'] !== 1) {
            return false;
        }

        return $entries[0]['dn'];
    }

    public function getUserFilter($username)
    {
        return str_replace('%username%', $username, $this->userFilter);
    }
    
    public function isUserMemberOfGroup($user_dn, $group_dn) {
        $groupInfo = @ldap_read($this->connection, $group_dn, "(member=$user_dn)");
        if($groupInfo == false){
            $this->log->warning("[".basename(__FILE__)."/".__FUNCTION__."]> ".ldap_err2str(ldap_errno($this->connection)));
            return false;
        }
        $entries = ldap_get_entries($this->connection, $groupInfo);

        return $entries['count'] > 0;
    }

    public function getAttribute($user_dn,array $attr){
		$searchResult=@ldap_read($this->connection, $user_dn, "(objectclass=*)",$attr);
        if($searchResult == false){
            $this->log->warning("[".basename(__FILE__)."/".__FUNCTION__."]> ".ldap_err2str(ldap_errno($this->connection)));
            return false;
        }
		$entries = @ldap_get_entries($this->connection, $searchResult);
        if ($entries['count'] !== 1) {
            return false;
        }
        return $entries[0];
	}
    
}
?>
