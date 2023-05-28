<?php

require_once realpath(__DIR__ . '/../vendor/autoload.php');
require_once realpath(__DIR__ . '/../class.ldap.php');


use Microsoft\Graph\Graph;
use Microsoft\Graph\Model;
use GuzzleHttp\Client;

use Firebase\JWT\JWK;
use Firebase\JWT\JWT;

$ldap = new Ldap();
$ldap->load_config();        
$ldap->write_log("[function]> azure_login");


define('TENANT_ID', $ldap->config['ld_azure_tenant_id']);
define('CLIENT_ID', $ldap->config['ld_azure_client_id']);
define('CLIENT_SECRET', $ldap->config['ld_azure_client_secret']);
define('REDIRECT_URI', $ldap->config['ld_azure_redirect_uri']);
$jwks_url = str_replace("{TENANT_ID}",$ldap->config['ld_azure_tenant_id'],$ldap->config['ld_azure_jwks_url']);

if (isset($_GET['code'])) {
    $client = new Client();
    $token_url = str_replace("{TENANT_ID}",$ldap->config['ld_azure_tenant_id'],$ldap->config['ld_azure_token_url']);
    $tokenResponse = $client->post($token_url, [
        'form_params' => [
            'code' => $_GET['code'],
            'grant_type' => 'authorization_code',
            'client_id' => CLIENT_ID,
            'client_secret' => CLIENT_SECRET,
            'redirect_uri' => REDIRECT_URI,
        ],
        // These options are needed to enable getting
        // the response body from a 4xx response
        'http_errors' => true,
        'curl' => [
            CURLOPT_FAILONERROR => false
        ]
    ]);

    if ($tokenResponse->getStatusCode() == 200) {
        // Return the access_token
        $responseBody = json_decode($tokenResponse->getBody()->getContents());
        $accessToken = $responseBody->access_token;
        $idToken = $responseBody->id_token;
        $user = new stdClass();;

        try {
            $azureKeys = json_decode(file_get_contents($jwks_url),true);
            $decodedIdToken = JWT::decode($idToken, JWK::parseKeySet($azureKeys,'RS256')); //works
            $user->claim=$decodedIdToken->groups ?? array();
            //$user->claim=$decodedIdToken[$ldap->config['ldap_azure_claim_name']] ?? array();
        } catch (\Exception $e) {
            echo 'Exception catched: ',  $e->getMessage(), "\n";
        }

        //should be replaced...
        $graph = new Graph;
        $graph->setAccessToken($accessToken);
        $graphUser = $graph->createRequest("GET", '/me?$select=' . $ldap->config['ld_azure_user_identifier'] . ',displayName')
            ->setReturnType(Model\User::class)
            ->execute();
        $user->userPrincipalname=$graphUser->getUserPrincipalName();
        $user->displayName=$graphUser->getDisplayName();
     //  echo("<pre>");print_r($user);echo("</pre>");


        $_SESSION['access_token'] = $accessToken;
        $_SESSION['userObject'] = $user;
        

		global $prefixeTable;
		// search user in piwigo database
		$query = 'SELECT '.$conf['user_fields']['id'].' AS id FROM '.USERS_TABLE.' WHERE '.$conf['user_fields']['username'].' = \''.pwg_db_real_escape_string($user->userPrincipalname).'\' ;';
		$row = pwg_db_fetch_assoc(pwg_query($query));
		$ldap->write_log("[azure_login]> user found in db:" . (!empty($row['id'])) );
		// if query is not empty, it means everything is ok and we can continue, auth is done !
		if (!empty($row['id'])) {
		//user exist
            if($ldap->config['ld_group_user_active'] == 1) {
                $status=false;
            } else {
                $status='normal';
            }
            if (in_array($ldap->config['ld_group_user'],  $user->claim)) {
                $status='normal';
            }
            if (($ldap->config['ld_group_admin_active'] == 1) && (in_array($ldap->config['ld_group_admin'],  $user->claim))) {
                $status='administrator';
            }
            if (($ldap->config['ld_group_webmaster_active'] == 1) && (in_array($ldap->config['ld_group_webmaster'],  $user->claim))) {
                $status='webmaster';
            }
            if($status == false){
                trigger_notify('login_failure', stripslashes($user->userPrincipalname));
                $ldap->write_log("[azure_login]> User does not have role / claim as user to login");
                return false;
            }                                
            $query = 'UPDATE `'.USER_INFOS_TABLE.'` SET `status` = "'. $status . '" WHERE `'.USER_INFOS_TABLE.'`.`user_id` = ' . $row['id'] . ';';
            pwg_query($query);        
			log_user($row['id'], False);
			trigger_notify('login_success', stripslashes($user->userPrincipalname));
			return true;
		} else {
		//user doest not (yet) exist
			$ldap->write_log("[azure_login]> User found in Azure but not in SQL");
			//this is where we check we are allowed to create new users upon that.
			if ($ldap->config['ld_allow_newusers']) {
				$ldap->write_log("[login]> Creating new user and store in SQL");
				$mail=null;
				if($ldap->config['ld_use_mail']){
					//retrieve LDAP e-mail address and create a new user
					$mail = $user->userPrincipalName;
				}
				$errors=[];
				$new_id = register_user($user->userPrincipalname,random_password(32),$user->userPrincipalname,true,$errors);
				if(count($errors) > 0) {
					foreach ($errors as &$e){
						$ldap->write_log("[azure_login]> ".$e, 'ERROR');
					}
					return false;
				}
                if($ldap->config['ld_group_user_active'] == 1) {
                    $status=false;
                } else {
                    $status='normal';
                }
                if (in_array($ldap->config['ld_group_user'],  $user->claim)) {
                    $status='normal';
                }
                if (($ldap->config['ld_group_admin_active'] == 1) && (in_array($ldap->config['ld_group_admin'],  $user->claim))) {
                    $status='administrator';
                }
                if (($ldap->config['ld_group_webmaster_active'] == 1) && (in_array($ldap->config['ld_group_webmaster'],  $user->claim))) {
                    $status='webmaster';
                }
                if($status == false){
                    trigger_notify('login_failure', stripslashes($user->userPrincipalname));
                    $ldap->write_log("[azure_login]> User does not have role / claim as user to login");
                    return false;
                }                              
                $query = 'UPDATE `'.USER_INFOS_TABLE.'` SET `status` = "'. $status . '" WHERE `'.USER_INFOS_TABLE.'`.`user_id` = ' . $new_id . ';';
				pwg_query($query);
                
				//Login user
				log_user($new_id, False);
				trigger_notify('login_success', stripslashes($user->userPrincipalname));

				//in case the e-mail address is empty, redirect to profile page
				if ($ldap->config['ld_allow_profile']) {
					redirect('profile.php');
				} else {
		//                    redirect('index.php');
				}
				return true;
			}
			//else : this is the normal behavior ! user is not created.
			else {
				trigger_notify('login_failure', stripslashes($user->userPrincipalname));
				$ldap->write_log("[azure_login]> Not allowed to create user (ld_allow_newusers=false)");
				return false;
			}
		}		


  	      
        
    } else if ($tokenResponse->getStatusCode() == 400) {
        // Check the error in the response body
        $responseBody = json_decode($tokenResponse->getBody()->getContents());
        if (isset($responseBody->error)) {
            $error = $responseBody->error;
            $error_description = $responseBody->error_description;
            // authorization_pending means we should keep polling
            if (strcmp($error, 'authorization_pending') != 0) {
                throw new Exception('Token endpoint returned: ' . $error . ' ' . $error_description, 100);
            }
        }
    }
} else {
//   $redirect = 'https://' . $_SERVER['HTTP_HOST'] . '/piwigo/index.php';
//    header('Location: ' . filter_var($redirect, FILTER_SANITIZE_URL));
}