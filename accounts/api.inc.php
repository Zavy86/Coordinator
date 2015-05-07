<?php
/* -------------------------------------------------------------------------- *\
|* -[ Accounts - API ]------------------------------------------------------- *|
\* -------------------------------------------------------------------------- */

/**
 * Account object
 *
 * @param mixed $account account id or object
 * @return object account object
 */
function api_accounts_account($account=NULL){
 // get contact object
 if($account===NULL){$account=$_SESSION['account']->id;}
 if(is_numeric($account)){$account=$GLOBALS['db']->queryUniqueObject("SELECT * FROM accounts_accounts WHERE id='".$account."'");}
 if(!$account->id){return FALSE;}
 // check, clean and convert
 $account->account=stripslashes($account->account);
 unset($account->password);
 unset($account->secret);
 $account->name=stripslashes($account->name);
 $account->ldapUsername=stripslashes($account->ldapUsername);
 $account->typology=api_accounts_accountTypology($account);
 $account->language=stripslashes($account->language);
 $account->avatar=api_accounts_accountAvatar($account);
 $account->mail=$account->account;
 // make firstname
 if(strrpos($account->name," ")!==FALSE){$account->firstname=substr($account->name,0,strrpos($account->name," "));}
 // make shortname
 if(strrpos($account->name," ")!==FALSE){
  $account->shortname=substr($account->name,0,strrpos($account->name," "));
  $account->shortname.=substr($account->name,strrpos($account->name," "),2).".";
 }
 // make language ISO
 if($account->language<>"default"){$account->languageISO=substr($account->language,-2);}else{$account->languageISO="EN";}
 // get companies
 $account->companies=array();
 $companies=$GLOBALS['db']->query("SELECT accounts_companies.*,accounts_accounts_join_companies.idRole,accounts_accounts_join_companies.main FROM accounts_accounts_join_companies JOIN accounts_companies ON accounts_companies.id=accounts_accounts_join_companies.idCompany WHERE idAccount='".$account->id."' ORDER BY main DESC");
 while($company=api_accounts_company($GLOBALS['db']->fetchNextObject($companies),FALSE)){
  // check main
  if($company->main){$account->mainCompany=$company->id;}
  // get company role
  $company->role=api_accounts_role($company->idRole);
  // get groups
  $company->groups=array();
  $groups=$GLOBALS['db']->query("SELECT accounts_groups.*,accounts_accounts_join_groups.main FROM accounts_accounts_join_groups JOIN accounts_groups ON accounts_groups.id=accounts_accounts_join_groups.idGroup WHERE idAccount='".$account->id."' AND idCompany='".$company->id."' ORDER BY main DESC");
  while($group=api_accounts_group($GLOBALS['db']->fetchNextObject($groups),FALSE)){
   // check main
   if($group->main){$company->mainGroup=$group->id;}
  // store group object
   $company->groups[$group->id]=$group;
  }
  // store company object
  $account->companies[$company->id]=$company;
 }
 // return contact object
 return $account;
}

/**
 * Accounts Typology
 *
 * @param mixed $account account id or object
 * @return string textual typology
 */
function api_accounts_accountTypology($account){
 $typology=new stdClass();
 if($account->del){
  $typology->icon="icon-trash";
  $typology->description=api_text("account-typology-deleted");
 }else{
  if($account->enabled){
   if($account->superuser){
    $typology->icon="icon-fire";
    $typology->description=api_text("account-typology-superuser");
   }else{
    $typology->icon="icon-info-sign";
    $typology->description=api_text("account-typology-user");
   }
  }else{
   $typology->icon="icon-ban-circle";
   $typology->description=api_text("account-typology-disabled");
  }
 }
 return $typology;
}

/**
 * Accounts Avatar
 *
 * @param mixed $account account id or object
 * @return string avatar path
 */
function api_accounts_accountAvatar($account=NULL){
 if(is_numeric($account)||$account===NULL){$account=api_accounts_account($account);}
 if(!$account->id){return FALSE;}
 $avatar_path="uploads/uploads/accounts/avatar_".$account->id.".jpg";
 if(!file_exists("../".$avatar_path)){$avatar_path="uploads/uploads/accounts/avatar.jpg";}
 return $GLOBALS['dir'].$avatar_path;
}

/**
 * Accounts Status modal window
 *
 * @param mixed $account account id or object
 * @return object modal window object
 */
function api_accounts_accountStatusModal($account){
 if(is_numeric($account)){$account=api_accounts_account($account);}
 if(!$account->id){return FALSE;}
 $return=new str_modal("account_status_".$account->id);
 $return->header($account->name);
 // build status body dl
 $dl_body=new str_dl("br","dl-horizontal");
 $dl_body->addElement(api_text("api-account-dt-typology"),$account->typologyText);
 $dl_body->addElement(api_text("api-account-dt-add"),api_text("api-account-dd-add",array(api_account($account->addIdAccount)->name,api_timestampFormat($account->addDate,api_text("datetime")))));
 if($account->updIdAccount<>NULL){$dl_body->addElement(api_text("api-account-dt-upd"),api_text("api-account-dd-upd",array(api_account($account->updIdAccount)->name,api_timestampFormat($account->updDate,api_text("datetime")))));}
 if($account->accDate<>NULL){$dl_body->addElement(api_text("api-account-dt-acc"),api_timestampFormat($account->accDate,api_text("datetime")));}
 if($account->del){$dl_body->addElement("&nbsp;",api_icon("icon-trash")." ".api_text("api-account-dd-del"));}
 $return->body($dl_body->render(FALSE));
 return $return;
}

/**
 * Accounts
 *
 * @param string $search search query
 * @param boolean $pagination limit query by page
 * @param string $where additional conditions
 * @return object $results array of accounts objects, $pagination pagination object, $query executed query
 */
function api_accounts_accounts($search=NULL,$pagination=FALSE,$where=NULL){
 // definitions
 $return=new stdClass();
 $return->results=array();
 // generate query
 $query_table="accounts_accounts";
 // fields
 $query_fields="accounts_accounts.*";
 // join
 $query_join=" LEFT JOIN accounts_accounts_join_companies ON accounts_accounts_join_companies.idAccount=accounts_accounts.id";
 // group
 $query_group=" GROUP BY accounts_accounts.id";
 // where
 $query_where="( accounts_accounts.del='0' OR ".$GLOBALS['navigation']->filtersParameterQuery("del","0","accounts_accounts.del")." )";
 //$query_where.=" AND ".$GLOBALS['navigation']->filtersParameterQuery("typologies","1","accounts_accounts.typology");
 $query_where.=" AND ".$GLOBALS['navigation']->filtersParameterQuery("enabled","1","accounts_accounts.enabled");
 $query_where.=" AND ".$GLOBALS['navigation']->filtersParameterQuery("superuser","1","accounts_accounts.superuser");
 $query_where.=" AND ".$GLOBALS['navigation']->filtersParameterQuery("companies","1","accounts_accounts_join_companies.idCompany");

 // vincolo -------------------------------------------------------------------- verificare
 //$query_where.=" AND accounts_accounts_join_companies.idCompany='".api_company()->id."'";

 // search
 if(strlen($search)>0){
  $query_where.=" AND ( accounts_accounts.account LIKE '%".addslashes($search)."%'";
  $query_where.=" OR accounts_accounts.name LIKE '%".addslashes($search)."%'";
  $query_where.=" OR accounts_accounts.ldap LIKE '%".addslashes($search)."%' )";
 }
 // conditions
 if(strlen($where)>0){$query_where="( ".$query_where." ) AND ( ".$where." )";}
 // order
 $query_order=api_queryOrder("accounts_accounts.superuser DESC,accounts_accounts.del DESC,accounts_accounts.name ASC");
 // pagination
 if($pagination){
  $return->pagination=new str_pagination($query_table.$query_join,$query_where.$query_group,$GLOBALS['navigation']->filtersGet());
  // limit
  $query_limit=$return->pagination->queryLimit();
 }
 // build query
 $return->query="SELECT ".$query_fields." FROM ".$query_table.$query_join." WHERE ".$query_where.$query_group.$query_order.$query_limit;
 // execute query
 $results=$GLOBALS['db']->query($return->query);
 while($result=$GLOBALS['db']->fetchNextObject($results)){$return->results[$result->id]=api_accounts_account($result);}
 // return promotions objects
 return $return;
}


/**
 * Company object
 *
 * @param mixed $company company id or object
 * @param boolean $subObjects load also sub objects
 * @return object company object
 */
function api_accounts_company($company,$subObjects=TRUE){
 // get company object
 if(is_numeric($company)){$company=$GLOBALS['db']->queryUniqueObject("SELECT * FROM accounts_companies WHERE id='".$company."'");}
 if(!$company->id){return FALSE;}
 // check and convert
 $company->company=stripslashes($company->company);
 $company->name=stripslashes($company->name);
 // get members
 $company->members=array();
 $members=$GLOBALS['db']->query("SELECT * FROM accounts_accounts_join_companies WHERE idCompany='".$company->id."' ORDER BY idAccount ASC");
 while($member=$GLOBALS['db']->fetchNextObject($members)){
  $company->members[$member->idAccount]=$member->idAccount;
 }
 // load sub objects
 if($subObjects){
  // get groups
  $company->groups=api_accounts_groups($company->id)->results;
 }
 // return company object
 return $company;
}

/**
 * Companies
 *
 * @param string $search search query
 * @param boolean $pagination limit query by page
 * @param string $where additional conditions
 * @return object $results array of companies objects, $pagination pagination object, $query executed query
 */
function api_accounts_companies($search=NULL,$pagination=FALSE,$where=NULL){
 // definitions
 $return=new stdClass();
 $return->results=array();
 // generate query
 $query_table="accounts_companies";
 // fields
 $query_fields="accounts_companies.*,IF(accounts_accounts_join_companies.idAccount='".api_accounts_account()->id."','1','0') AS associated";
 // join
 $query_join=" LEFT JOIN accounts_accounts_join_companies ON accounts_accounts_join_companies.idCompany=accounts_companies.id";
 // group
 $query_group=" GROUP BY accounts_companies.id";
 // where
 $query_where="1";
 // filters
 if(is_object($GLOBALS['navigation'])){
  $query_where.=" AND ( accounts_companies.del='0' OR ".$GLOBALS['navigation']->filtersParameterQuery("del","0","accounts_companies.del")." )";
 }

 // if not superuser show only associated company  ---------------------------- check
 if(!api_accounts_account()->superuser){$query_where.=" AND accounts_accounts_join_companies.idAccount='".api_accounts_account()->id."'";}

 // search
 if(strlen($search)>0){
  $query_where.=" AND ( accounts_companies.company LIKE '%".addslashes($search)."%'";
  $query_where.=" OR accounts_companies.name LIKE '%".addslashes($search)."%'";
  $query_where.=" OR accounts_companies.fiscal_name LIKE '%".addslashes($search)."%' )";
 }
 // conditions
 if(strlen($where)>0){$query_where="( ".$query_where." ) AND ( ".$where." )";}
 // order
 $query_order=api_queryOrder("accounts_companies.name ASC, accounts_companies.company ASC");
 // pagination
 if($pagination){
  $return->pagination=new str_pagination($query_table.$query_join,$query_where.$query_group,$GLOBALS['navigation']->filtersGet());
  // limit
  $query_limit=$return->pagination->queryLimit();
 }
 // build query
 $return->query="SELECT ".$query_fields." FROM ".$query_table.$query_join." WHERE ".$query_where.$query_group.$query_order.$query_limit;
 // execute query
 $results=$GLOBALS['db']->query($return->query);
 while($result=$GLOBALS['db']->fetchNextObject($results)){$return->results[$result->id]=api_accounts_company($result);}
 // return companies objects
 return $return;
}


/**
 * Role object
 *
 * @param mixed $role role id or object
 * @return object role object
 */
function api_accounts_role($role){
 // get company object
 if(is_numeric($role)){$role=$GLOBALS['db']->queryUniqueObject("SELECT * FROM accounts_roles WHERE id='".$role."'");}
 if(!$role->id){return FALSE;}
 // check and convert
 $role->name=stripslashes($role->name);
 $role->description=stripslashes($role->description);
 // return role object
 return $role;
}

/**
 * Role Status modal window
 *
 * @param mixed $role account id or object
 * @return object modal window object
 */
function api_accounts_roleStatusModal($role){
 if(is_numeric($role)){$role=api_accounts_role($role);}
 if(!$role->id){return FALSE;}
 $return=new str_modal("account_status_".$role->id);
 $return->header($role->name);
 // build status body dl
 $dl_body=new str_dl("br","dl-horizontal");
 $dl_body->addElement(api_text("api-role-dt-add"),api_text("api-role-dd-add",array(api_account($role->addIdAccount)->name,api_timestampFormat($role->addDate,api_text("datetime")))));
 if($role->updIdAccount<>NULL){$dl_body->addElement(api_text("api-role-dt-upd"),api_text("api-role-dd-upd",array(api_account($role->updIdAccount)->name,api_timestampFormat($role->updDate,api_text("datetime")))));}
 $return->body($dl_body->render(FALSE));
 return $return;
}

/**
 * Roles
 *
 * @param string $search search query
 * @param boolean $pagination limit query by page
 * @param string $where additional conditions
 * @return object $results array of roles objects, $pagination pagination object, $query executed query
 */
function api_accounts_roles($search=NULL,$pagination=FALSE,$where=NULL){
 // definitions
 $return=new stdClass();
 $return->results=array();
 // generate query
 $query_table="accounts_roles";
 // fields
 $query_fields="accounts_roles.*";
 // where
 $query_where=" 1 ";
 // search
 if(strlen($search)>0){
  $query_where.=" AND ( accounts_roles.name LIKE '%".addslashes($search)."%'";
  $query_where.=" OR accounts_companies.description LIKE '%".addslashes($search)."%' )";
 }
 // conditions
 if(strlen($where)>0){$query_where="( ".$query_where." ) AND ( ".$where." )";}
 // order
 $query_order=api_queryOrder("accounts_roles.level ASC,accounts_roles.name ASC");
 // pagination
 if($pagination){
  $return->pagination=new str_pagination($query_table,$query_where,$GLOBALS['navigation']->filtersGet());
  // limit
  $query_limit=$return->pagination->queryLimit();
 }
 // build query
 $return->query="SELECT ".$query_fields." FROM ".$query_table." WHERE ".$query_where.$query_order.$query_limit;
 // execute query
 $results=$GLOBALS['db']->query($return->query);
 while($result=$GLOBALS['db']->fetchNextObject($results)){$return->results[$result->id]=api_accounts_role($result);}
 // return roles objects
 return $return;
}


/**
 * Group object
 *
 * @param mixed $group group id or object
 * @param boolean $subGroups load also sub groups
 * @return object group object
 */
function api_accounts_group($group,$subGroups=FALSE){
 // get group object
 if(is_numeric($group)){$group=$GLOBALS['db']->queryUniqueObject("SELECT * FROM accounts_groups WHERE id='".$group."'");}
 if(!$group->id){return FALSE;}
 // check and convert
 $group->name=stripslashes($group->name);
 $group->description=stripslashes($group->description);
 // make label
 $group->label=$group->name;
 if($group->description){$group->label.=" &minus; ".$group->description;}
 // get parent groups
 $group->path=NULL;
 $group->parents=array();
 $idParent=$group->idGroup;
 while($idParent<>NULL){
  $parent=$GLOBALS['db']->queryUniqueObject("SELECT * FROM accounts_groups WHERE id='".$idParent."'");
  if(!$parent->id){$idParent=NULL;continue;}
  $group->parents[]=$parent->id;
  $group->path=stripslashes($parent->name)." &rarr; ".$group->path;
  $idParent=$parent->idGroup;
 }
 // get members
 $group->members=array();
 $members=$GLOBALS['db']->query("SELECT accounts_accounts_join_groups.idAccount FROM accounts_accounts_join_groups JOIN accounts_accounts ON accounts_accounts.id=accounts_accounts_join_groups.idAccount JOIN accounts_accounts_join_companies ON accounts_accounts_join_companies.idAccount=accounts_accounts.id JOIN accounts_roles ON accounts_roles.id=accounts_accounts_join_companies.idRole WHERE accounts_accounts_join_groups.idGroup='".$group->id."' ORDER BY accounts_roles.level DESC,accounts_accounts.name ASC");
 while($member=$GLOBALS['db']->fetchNextObject($members)){
  $group->members[$member->idAccount]=$member->idAccount;
 }
 // load sub objects
 if($subGroups){
  // get groups
  $group->groups=array();
  $group->groups=api_accounts_groups($group->idCompany,$group->id)->results;
 }
 // return group object
 return $group;
}

/**
 * Groups
 *
 * @param integer $idCompany company id
 * @param integer $idGroup father group id
 * @param string $where additional conditions
 * @return object $results array of groups objects, $pagination pagination object, $query executed query
 */
function api_accounts_groups($idCompany,$idGroup=NULL,$where=NULL){
 if(!is_numeric($idCompany)){return FALSE;}
 if(!is_numeric($idGroup)&&$idGroup!==NULL){return FALSE;}
 // definitions
 $return=new stdClass();
 $return->results=array();
 // generate query
 $query_table="accounts_groups";
 // fields
 $query_fields="accounts_groups.*";
 // where
 $query_where="accounts_groups.idCompany='".$idCompany."'";
 if($idGroup===NULL){$query_where.=" AND ISNULL(idGroup)";}else{$query_where.=" AND accounts_groups.idGroup='".$idGroup."'";}
 // conditions
 if(strlen($where)>0){$query_where="( ".$query_where." ) AND ( ".$where." )";}
 // order
 $query_order=api_queryOrder("accounts_groups.name ASC");
 // build query
 $return->query="SELECT ".$query_fields." FROM ".$query_table." WHERE ".$query_where.$query_order;
 // execute query
 $results=$GLOBALS['db']->query($return->query);
 while($result=api_accounts_group($GLOBALS['db']->fetchNextObject($results),FALSE)){
  $result->groups=api_accounts_groups($result->idCompany,$result->id)->results;
  $return->results[$result->id]=$result;
 }
 // return promotions objects
 return $return;
}

?>