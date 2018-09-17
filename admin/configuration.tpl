<h2>{'Ldap_Login Plugin'|@translate}</h2>

<div id="configContent">

<p>{'All LDAP users can use their ldap password everywhere on piwigo if needed.'|@translate}</p>

<form method="post" action="{$PLUGIN_ACTION}" class="general">

	{if (!extension_loaded('ldap'))}
		<p style="color:red;">{'Warning: LDAP Extension missing.'|@translate}</p>
		<br />
	{/if}


	<fieldset class="mainConf">
	<legend>{'General settings'|@translate}</legend>
	
	<ul>
		<li>
			<label for="forgot_url">{'Url for password reset'|@translate}</label>
			<br>
			<input size="70" type="text" id="forgot_url" name="FORGOT_URL" value="{$FORGOT_URL}" />
		</li>
		<i>Enter your company directory password reset URL (https://mycompany.com/passreset.php)<br>If empty, it is the default piwigo "password.php"</i>

		<li>
			<label for="ldap_debug">
			{if $LDAP_DEBUG }
				<input type="checkbox" id="ldap_debug" name="LDAP_DEBUG" value="{$LDAP_DEBUG}" checked />
			{else}
				<input type="checkbox" id="ldap_debug" name="LDAP_DEBUG" value="{$LDAP_DEBUG}" />
			{/if}{'Debug LDAP using/var/log/Ldap_login.log'|@translate}</label>
		</li>
	</ul>

    </fieldset>
	
	<fieldset class="mainConf">
	<legend>{'Ldap server host connection'|@translate}</legend>
	
	<ul>
		<li>
			<label for="host">{'Ldap server host'|@translate}</label>
			<br>
			<input size="70" type="text" id="host" name="HOST" value="{$HOST}" />
		</li>
	
		<li>
			<label for="ld_use_ssl">
			{if $LD_USE_SSL }
				<input type="checkbox" id="ld_use_ssl" name="LD_USE_SSL" value="{$LD_USE_SSL}" checked />
			{else}
				<input type="checkbox" id="ld_use_ssl" name="LD_USE_SSL" value="{$LD_USE_SSL}" />
			{/if}
			{'Secure connexion'|@translate}</label>
		</li>
	
		<li>
			<label for="port">{'Ldap port'|@translate}</label>
			<br>
			<input type="text" id="port" name="PORT" value="{$PORT}" />
		</li>
	</ul>
	<i>{'If empty, localhost and standard protocol ports will be used in configuration.'|@translate}</i>
    </fieldset>
    
    <fieldset class="mainConf">
	<legend>{'Ldap attributes'|@translate}</legend>
	<ul>
		<li>
			<label for="ld_server">{'Server mode:'|@translate}</label><br>
				<select name="LD_SERVER" id="ld_server">
				  <option value="ad" 		{if 'ad' == {$LD_SERVER}}selected{/if}>Active Directory</option>
				  <option value="openldap"	{if 'openldap' == {$LD_SERVER}}selected{/if}>OpenLDAP</option>
				</select>
		</li>
		<i>{'If using MS AD, choose Active Directory, else choose OpenLDAP'|@translate}</i>		
		<li>
			<label for="basedn">{'Base DN'|@translate}</label>
			<br>
			<input size="70" type="text" id="basedn" name="BASEDN" value="{$BASEDN}" />
		</li>
		<br>
		<li>
			<label for="ld_attr">{'Attribute corresponding to the user name'|@translate}</label>
			<br>
			<input type="text" id="ld_attr" name="LD_ATTR" value="{$LD_ATTR}" />
		</li>
		<i>For AD it is often 'sAMAccountName'. For OpenLDAP, it is often 'userid'. Please check your directory details for the correct attribute.</i>
	</ul>
    </fieldset>
    <fieldset class="mainConf">
	<legend>{'Ldap Group attributes'|@translate}</legend>
	<ul>
		<li>
			<label for="groupdn">{'DN of group for membership-check and calculated CN (using RegEx)'|@translate}</label>
			<br>
			<input size="70" type="text" id="ld_group" name="LD_GROUP" value="{$LD_GROUP}" /><input disabled type="text" value='{$LD_GROUP|regex_replace:"/,[a-z]+.*/":""}' />
		</li>
		<li>
		<label for="groupdn_class">{'Class of group:'|@translate}</label>
		<br>
		<select name="LD_GROUP_CLASS" id="ld_group_class" >
		  <option value="group"  		{if 'group' == {$LD_GROUP_CLASS}}selected{/if}>group</option>
		  <option value="posixgroup" 	{if 'posixgroup' == {$LD_GROUP_CLASS}}selected{/if}>posixGroup</option>
		</select>
		</li>
		<i>{'Depending on server configuration the class may differ, choose accordingly. OpenLDAP: posixGroup. AD: group.'|@translate}</i>
		<br>
		<li>
		<label for="ld_group_member_attrib">{'Attribute for members in group:'|@translate}</label>
		<br>
		<select name="LD_GROUP_MEMBER_ATTRIB" id="ld_group_member_attrib" >
		  <option value="member"  		{if 'member' == {$LD_GROUP_MEMBER_ATTRIB}}selected{/if}>member</option>
		  <option value="memberUid" 	{if 'memberUid' == {$LD_GROUP_MEMBER_ATTRIB}}selected{/if}>memberUid</option>
		</select>
		</li>
		<i>{'Depending on server configuration the attribute may differ, choose accordingly. OpenLDAP: memberUid. AD:member.'|@translate}</i>
		
	</ul>
    </fieldset>
    
    <fieldset class="mainConf">
	<legend>{'Ldap connection credentials'|@translate}</legend>
	<ul>
		<li>
			<label for="ld_binddn">{'Bind DN, field in full ldap style'|@translate}</label>
			<br>
			<input size="70" type="text" id="ld_binddn" name="LD_BINDDN" value="{$LD_BINDDN}" />
		</li>
		
		<li>
			<label for="ld_bindpw">{'Bind password'|@translate}</label>
			<br>
			<input type="password" id="ld_bindpw" name="LD_BINDPW" />
		</li>
	</ul>
	<i>{'Let the fields blank if the ldap accept anonymous connections. '|@translate}</i>
</fieldset>
 
<p>
<input type="submit" value="{'Save'|@translate}" name="save" />
</p>
</form>

<form method="post" action="{$PLUGIN_CHECK}" class="general">
<fieldset class="mainConf">
<legend>{'Ldap_Login Test'|@translate}</legend>
<i>{'You must save the settings with the Save button just up there before testing here.'|@translate}</i>
	<ul>
		<li>
			<label for="username">{'Username'|@translate}</label>
			<br>
			<input type="text" id="username" name="USERNAME" value="{$USERNAME}" />
		</li>
		
		<li>
			<label for="ld_attr">{'Your password'|@translate}</label>
			<br>
			<input type="password" id="password" name="PASSWORD" value="{$PASSWORD}" />
		</li>
	</ul>
	
	{if (!empty($LD_CHECK_LDAP))}
 		{$LD_CHECK_LDAP}
	{/if}

</fieldset>
<p><input type="submit" value="{'Test Settings'|@translate}" name="check_ldap" /></p>

</form>
</div>
