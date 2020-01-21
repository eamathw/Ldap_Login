<?php

error_reporting(E_ALL);
ini_set('display_errors',1);


function table_exist() {
/**
 * Checks table in SQL-database Piwigo for plugin ldap_login
 * Depending on $fix it can do for:
 *
 *		table_exist($fix=True) {
 *    	 -  Return an boolean for table existance and creates table if not exists
 *
 *		table_exist($fix=False) {
 *    	 - Return an boolean for table existance 
 *
 * @since 2.10.1
 *
 * @param boolean $fix
 * @return boolean 
 */
	$query = ('select 1 from `piwigo_ldap_login_config` LIMIT 1');
	$r = pwg_query($query);
	if(is_object($r) !== TRUE)
	{
	   //table not found..
 
	   return false;
	}
	else
	{
		return true;
		//I can find it...
	}
}


function ld_sql($action='get',$type=null,$data=null){
	
/**
 * Does actions on SQL-database Piwigo for plugin ldap_login
 * Depending on $action and $type it can do for:
 *
 *		ld_sql($action='get')
 *    	 - Return an associative array of a table values (key='...', value='...')
 *
 *		ld_sql($action='create',$type='create_table')
 *    	 - Creates table piwigo_ldap_login_config
 *
 *		ld_sql($action='create',$type='create_data','data')
 *    	 - Inserts data (array(array($k,$v),...)) in table
 *	 
 *		ld_sql($action='update','reset_openldap')
 *    	 - Updates values corresponding with OpenLDAP values (classes and attributes)
 *	 
 *		ld_sql($action='update','reset_ad')
 *    	 - Updates values corresponding with AD values (classes and attributes)	 
 *	 
 *		ld_sql($action='update','update_value','data')
 *    	 - Updates data from array($k1=>$v1,$k2=>$v2,...) in table
 *
 *		ld_sql($action='delete')
 *    	 - Deletes piwigo_ldap_login_config
 * 			 
 *
 * @since 2.10.1
 *
 * @param string $action
 * @param string $type
 * @param array $data
 * @return array $result
 */


	
	###
	### GET 
	###

	if ($action === 'get') {
		if(table_exist()){
			$query ='SELECT param,value FROM piwigo_ldap_login_config';
			$result = query2array($query,'param','value');
			return $result;
		}
	};

	###
	### CREATE 
	###	ENGINE = MyISAM CHARSET=utf8 COLLATE utf8_general_ci;

		
	if ($action == 'create'){
		if ($type == 'create_table') {
			$query="CREATE TABLE IF NOT EXISTS `piwigo_ldap_login_config` (`param` varchar(40) CHARACTER SET utf8 NOT NULL,`value` text CHARACTER SET utf8,`comment` varchar(255) CHARACTER SET utf8 DEFAULT NULL,UNIQUE KEY `param` (`param`)) ENGINE = MyISAM CHARSET=utf8 COLLATE utf8_general_ci;";
			pwg_query($query);
		
		}	
		if ($type == 'create_data') {
			if(isset($data)){
				foreach($data as $k => $v){
					$datas[]=array(
						'param' => $k,
						'value' => pwg_db_real_escape_string($v)
						);
				}
				mass_inserts('piwigo_ldap_login_config', array('param','value'), $datas);
			}
			
		}	
		
	###
	### Update 
	###	
	
	}		
	if ($action == 'update'){
		if(table_exist()){
			if ($type == 'reset_openldap') {
				$updates = array(
					array(
						'param' => 'ld_user_attr',
						'value' => 'cn'
						),			
					array(
						'param' => 'ld_user_class',
						'value' => 'inetOrgPerson'
						),			
					array(
						'param' => 'ld_group_class',
						'value' => 'groupOfNames'
						)
					);
				mass_updates(
					'piwigo_ldap_login_config',
					array(
					'primary' => array('param'),
					'update' => array('value')
					),
					$updates
				);			
			}
			if ($type == 'reset_ad') {
				$updates = array(
					array(
						'param' => 'ld_user_attr',
						'value' => 'sAMAccountname'
						),			
					array(
						'param' => 'ld_user_class',
						'value' => 'user'
						),			
					array(
						'param' => 'ld_group_class',
						'value' => 'group'
						)
					);
				mass_updates(
					'piwigo_ldap_login_config',
					array(
					'primary' => array('param'),
					'update' => array('value')
					),
					$updates
				);	
			}
			if ($type == 'update_value') {
				if (isset($data)) {
					foreach($data as $k => $v){
						$updates[]=array(
							'param' => $k,
							'value' => pwg_db_real_escape_string($v)
							);
					}
					mass_updates(
						'piwigo_ldap_login_config',
						array(
						'primary' => array('param'),
						'update' => array('value')
						),
						$updates
					);
				}				
			}			
			if ($type == 'clear_mail_address') {
				$query="update piwigo_users SET mail_address = null WHERE id > 2";
				pwg_query($query);				
			}
		}
	}

	###
	### DELETE 
	###	

	if ($action == 'delete'){
		if(table_exist()){
			if ($type=='delete_table') {
				$query="
				DROP TABLE `piwigo_ldap_login_config`;
				";
				pwg_query($query);
			}
		}
	}	
}

