Ldap_Login is a piwigo plugin to allow people to connect and get access to a piwigo website with their Ldap credentials.

There is an option to allow creation of new users if they are granted access by ldap but don't exist in Piwigo DB.

Original work: stephane @ 22decembre.eu.


# Ldap_Login
LDAP authentication plugin for piwigo with user-group support

_Incompatible with other LDAP plugins, please deactivate the previous version before using this version. Also bear in mind that it can not be guarranteed that this plugin is safe. I try to keep security as high as possible but keep in mind that YOU are responsible for YOUR server._ 

## Example-Config for an M$-AD:

### LDAP server connection
Setting 						| Value 						| Description
------------ 					| ------------- 				| -------------
**LDAP server host**			| IP or hostname
**Secure connection (ldaps):**	| unchecked
**LDAP port:** 					|389 							| or another port 

### Ldap attributes
Setting 						| Value 						| Description
------------ 					| ------------- 				| -------------
**Server mode:**				| Active Directory or OpenLDAP 	|
**Base DN of LDAP server:**		| dc=company,dc=com  			|This should be the branch in your directory that covers both groups and users.
**Attribute corresponding to the user name:**|  userid 			|This can be sAMAccountName (M$) or just userid (OpenLDAP)

### Ldap Group attributes
Setting 						| Value 						| Description
------------ 					| ------------- 				| -------------
**DN of group for membership-check and calculated CN (using RegEx):**| cn=piwigo_users,ou=groups,dc=company,dc=com & cn=piwigo_users
**Class of group:** 			| posixGroup or group 			| Depending on server configuration the class may differ, choose accordingly 
**Attribute for members in group:**		| memberUid or member	|Depending on server configuration the attribute may differ, choose accordingly

### LDAP connection credentials
Setting 						| Value 						| Description
------------ 					| ------------- 				| -------------
**Bind DN in LDAP style (e.g.: cn=admin,dc=example,dc=com):**| cn=admin, ou=piwigo,ou=users,dc=company,dc=com
**Bind password:**				| verystronpassword123 			|Empty passwords wont work anymore due to bug

### LdapLogin Test
Setting 						| Value 						| Description
------------ 					| ------------- 				| -------------
**Your LDAP username:**			| any user
**Your LDAP password:** 		|verysafe password

