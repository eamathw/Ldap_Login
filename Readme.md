Ldap_Login is a piwigo plugin to allow people to connect and get access to a piwigo website with their Ldap credentials.
There is an option to allow creation of new users if they are granted access by ldap but don't exist in Piwigo DB.

Original work: stephane @ 22decembre.eu.


# Ldap_Login
LDAP authentication plugin for piwigo with user-group support

_Incompatible with other LDAP plugins, please deactivate the previous version before using this version. Also bear in mind that it can not be guarranteed that this plugin is safe. I try to keep security as high as possible but keep in mind that YOU are responsible for YOUR server._ 


## Default config:
Most settings can be changed when the plugin activates and you visit configuration page.

Param 						| Default Value 						| Comment
------------ 					| ------------- 				| -------------
**ld_forgot_url**					| password.php 					| custom password forget link
**ld_debug_location**				| ./plugins/Ldap_login/logs/ 	| log location 
**ld_debug**						|  1 							| enable/disable loggin
**ld_debug_clearupdate**			|  1 							| clear debug log after update
**ld_debug_level**				|  debug 						| default debug level (currently only 'debug')
**ld_host**						|  localhost 					| hostname 
**ld_port**						|  389 | port of ldap (can be empty or 636)
**ld_basedn**						|  ou=base,dc=example,dc=com 	| your base directory DN
**ld_user_class**					|  person 						| your user class [inetOrgPerson, person, user]
**ld_user_attr**					|  sAMAccountName 				| login attribute [cn,sAMAccounName]
**ld_user_filter**				|  null							| additional user filter
**ld_group_class**				|  group 						| class of group [group, groupOfNames]
**ld_group_filter**				|  null 						| additional group filter
**ld_group_attr**					|  name 						| Not in use (reserved)
**ld_group_desc**					|  description 					| Not in use (reserved)
**ld_group_basedn**				|  cn=groups,dc=domain,dc=tld 	| Base of your Piwigo Groups for Group sync
**ld_group_member_attr**			|  member 						| Attribute for user in group
**ld_user_member_attr**			|  memberOf 					| Attribute for group in user
**ld_group_webmaster**			|  cn=piwigo_webmasters,cn=groups,dc=domain,dc=tld 				| Group containing webmasters
**ld_group_admin**				|  cn=piwigo_admins,cn=groups,dc=domain,dc=tld 					| Group containing Admins
**ld_group_user**					|  cn=piwigo_users,cn=groups,dc=domain,dc=tld 					| Group containing Users
**ld_binddn**						|  cn=service_account, ou=Users, ou=base, dc=domain,dc=tld 		| Your (service)account for binding
**ld_bindpw**						|  null 						| The password
**ld_anonbind**					|  0 							| if binddn / bindpw empty then this is true, else false. This enables anonymous binding
**ld_use_ssl**					|  0 							| Untested! Usage of LDAPS
**ld_membership_user**			|  0 							| Should group be tested on user (memberOf in user = 1) or user be tested on group (member in group = 0). Recommended to keep on 0
**ld_group_user_active**			|  1 							| Is there a group restriction on all users?
**ld_group_admin_active**			|  0 							| Are admins automatically queried using LDAP?
**ld_group_webmaster_active**		|  0 							| Are webmaster automatically queried using LDAP?
**ld_sync_data**					|  null 						| Magic atrribute containing data of new Group Management system
**ld_allow_newusers**				|  1 							| Are new users allowed to be created using LDAP when logging in
**ld_allow_profile**				|  1 							| Redirect to user profile?
**ld_advertise_admin_new_ldapuser**		|  0 					| Should admin get mail if new users registrates?
**ld_send_password_by_mail_ldap**			|  0					| Should users get a mail with info when registrating?
