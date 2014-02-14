<?php
/* -------------------------------------------------------------------------- *\
|* -[ LDAP Authentication ]-------------------------------------------------- *|
\* -------------------------------------------------------------------------- */
function ldap_authenticate($ldap_host,$ldap_dn,$ldap_domain,$username,$password,$ldap_userfield,$ldap_group=NULL){
 $return=FALSE;
 $ldap=ldap_connect($ldap_host);
 // verify user and password
 $bind=@ldap_bind($ldap,$username.$ldap_domain,$password);
 // check valid credentials
 if($bind){
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