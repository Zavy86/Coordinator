<?php
/* -------------------------------------------------------------------------- *\
|* -[ LDAP Authentication ]-------------------------------------------------- *|
\* -------------------------------------------------------------------------- */
function ldap_authenticate($ldap_host,$ldap_dn,$ldap_domain,$username,$password,$ldap_userfield,$ldap_group=NULL){
 /*
 error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_WARNING & ~E_DEPRECATED);
 ini_set('display_errors',1);
	pre_var_dump($username);
 $return=FALSE;
 */
 $extended_error=null;
 $ldap=ldap_connect($ldap_host);
 // verify user and password
 $bind=ldap_bind($ldap,$username.$ldap_domain,$password);
 // check for password expired
 ldap_get_option($ldap,0x0032,$extended_error);
 pre_var_dump($extended_error,"print","extended error");
 $error_number=explode(' ',explode(',',$extended_error)[2])[2];
 pre_var_dump($error_number,"print","error number");
 // redirect to ldap password update
 if($error_number=="532"){api_redirect("accounts_ldap_update.php?username=".$username."&lang=".$_GET['lang']);}
 // check valid credentials
 if($bind){
  pre_var_dump("bind ok");
  // return true or check group if not null
  if($ldap_group==NULL){
   $return=TRUE;
  }else{
   // set options
   ldap_set_option($ldap,LDAP_OPT_PROTOCOL_VERSION,3);
   ldap_set_option($ldap,LDAP_OPT_REFERRALS,0);
   // check presence in groups
   $filter="(".$ldap_userfield."=".$username.")"; //
   $attr=array("memberof");
   $result=ldap_search($ldap,$ldap_dn,$filter,$attr);
   $entries=ldap_get_entries($ldap, $result);
   foreach($entries[0]['memberof'] as $grps){
    if(strpos($grps,$ldap_group)){$return=TRUE;}
   }
  }
  // disconnect from active directory
  ldap_unbind($ldap);
 }
 return $return;
}
?>