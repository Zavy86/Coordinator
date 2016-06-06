<?php
/* -------------------------------------------------------------------------- *\
|* -[ Accounts - Submit ]---------------------------------------------------- *|
\* -------------------------------------------------------------------------- */
include('../core/api.inc.php');
api_loadModule();
$act=$_GET['act'];
switch($act){
 // accounts
 case "account_login":account_login();break;
 case "account_logout":account_logout();break;
 case "account_save":account_save();break;
 case "account_save_ldap":account_save_ldap();break;
 case "account_customize":account_customize();break;
 case "account_delete":account_delete("delete");break;
 case "account_undelete":account_delete("undelete");break;
 case "account_interpret":account_interpret();break;
 case "account_interpret_stop":account_interpret_stop();break;
 case "account_switch_to_admin":account_switch(1);break;
 case "account_switch_to_user":account_switch(0);break;
 case "account_debug_enable":account_debug(TRUE);break;
 case "account_debug_disable":account_debug(FALSE);break;
 case "account_company_add":account_company_add();break;
 case "account_company_mainize":account_company_mainize();break;
 case "account_company_remove":account_company_remove();break;
 case "account_group_add":account_group_add();break;
 case "account_group_mainize":account_group_mainize();break;
 case "account_group_remove":account_group_remove();break;
 // password
 case "password_retrieve":password_retrieve();break;
 case "password_reset":password_reset();break;
 // roles
 case "role_save":role_save();break;
 case "role_delete":role_delete();break;
 // companies
 case "company_save":company_save();break;   // <-- todo
 case "company_delete":company_delete();break;   // <-- todo
 // groups
 case "group_save":group_save();break;
 case "group_delete":group_delete();break;
 // default
 default:
  $alert="?alert=submitFunctionNotFound&alert_class=alert-warning&act=".$act;
  exit(header("location: index.php".$alert));
}


/**
 * Account Login
 */
function account_login(){
 // acquire variables
 $g_language=$_GET['language'];
 $p_language=$_POST['language'];
 $p_account=addslashes($_POST['account']);
 $p_password=$_POST['password'];
 $s_url=$_SESSION['external_redirect'];
 // if account is not an email
 if(strpos($p_account,"@")==FALSE && $p_account<>"root" && api_getOption('ldap')){
  $authentication_method="ldap";
  // try ldap authentication
  include('../config.inc.php');
  include('../core/ldap.inc.php');
  // try cache authentication
  //  ^------------------------------------------------------- to be implemented
  if(ldap_authenticate(api_getOption('ldap_host'),api_getOption('ldap_dn'),api_getOption('ldap_domain'),$p_account,$p_password,api_getOption('ldap_userfield'),api_getOption('ldap_group'))){
   $account=$GLOBALS['db']->queryUniqueObject("SELECT * FROM accounts_accounts WHERE ldap='".$p_account."'");
   if(!$account->id){
    // authentication successful but account does not exist
    $_SESSION['newldap_account']=$p_account;
    $_SESSION['newldap_password']=$p_password;
    exit(header("location: accounts_ldap.php?lang=".$g_language));
   }
  }
 }else{
  $authentication_method="standard";
  // try standard authentication
  $account=$GLOBALS['db']->queryUniqueObject("SELECT * FROM accounts_accounts WHERE account='".$p_account."' AND password='".md5($p_password)."'");
 }
 // check account
 if($account->id){
  // login successful
  switch($authentication_method){
   case "standard":
    $account->login=$account->account;
    break;
   case "ldap":
    $account->login=$account->ldap;
    if(api_getOption("ldap_cache_pwd")){$password_update_query=",password='".md5($p_password)."'";}
    break;
  }
  // check enable
  if($account->enabled){
   // check maintenance
   if(!$account->superuser && api_getOption("maintenance")){
    $alert="&alert=maintenance&alert_class=alert-warning";
    exit(header("location: login.php?lang=".$account->language."&account=".$account->login.$alert));
   }else{
    session_destroy();
    session_start();
    // get account object
    $_SESSION['account']=api_accounts_account($account->id);
    // update session language
    $_SESSION['language']=$account->language;
    // use choised language and set cookies
    if(strlen($p_language)){
     $_SESSION['language']=$p_language;
     setcookie("language",$p_language,time()+(60*60*24*30));
    }else{
     setcookie("language",$account->language,time()+(60*60*24*30));
    }
    // update session company
    $_SESSION['company']=api_account()->companies[api_account()->mainCompany];
    // enable administrator only for root (superuser can switch after login)
    if($account->id==1){$_SESSION['account']->administrator=TRUE;}
    else{$_SESSION['account']->administrator=FALSE;}
    // load account permissions
    $_SESSION['permissions']=api_loadAccountPermission();
    // update last access and ldap password if set
    $GLOBALS['db']->execute("UPDATE accounts_accounts SET accDate='".api_now()."'".$password_update_query." WHERE id='".$account->id."'");
    // log event
    api_log(API_LOG_NOTICE,"accounts","loginSuccess",
     "{logs_accounts_loginSuccess|".$account->login."|".$account->name."}",
     $account->id,"accounts/accounts_edit.php?idAccount=".$account->id);
    // redirect
    if($s_url){exit(header("location: ".$s_url));}else{exit(header("location: ../index.php"));}
   }
  }else{
   // login disabled
   api_log(API_LOG_WARNING,"accounts","loginDisabled",
    "{logs_accounts_loginDisabled|".$account->login."|".$account->name."}",
    $account->id,"accounts/accounts_edit.php?idAccount=".$account->id);
   $alert="&alert=loginDisabled&alert_class=alert-warning&alert_parameters=".$account->login;
   exit(header("location: login.php?lang=".$account->language."&account=".$account->login.$alert));
  }
 }else{
  // login failed
  switch($authentication_method){
   case "standard":
    $account=$GLOBALS['db']->queryUniqueObject("SELECT * FROM accounts_accounts WHERE account='".$p_account."' AND ( ISNULL(ldap) OR ldap='' )");
    $account->login=$account->account;
    break;
   case "ldap":
    $account=$GLOBALS['db']->queryUniqueObject("SELECT * FROM accounts_accounts WHERE ldap='".$p_account."'");
    $account->login=$account->ldap;
    break;
  }
  // logs
  if($account->id){
   api_log(API_LOG_NOTICE,"accounts","loginFailed",
    "{logs_accounts_loginFailed|".$account->account."|".$account->name."}",
    $account->id,"accounts/accounts_edit.php?idAccount=".$account->id);
  }else{
   api_log(API_LOG_WARNING,"accounts","loginError",
    "{logs_accounts_loginError|".$p_account."}");
  }
  // redirect
  $alert="&alert=loginFailed&alert_class=alert-warning";
  exit(header("location: login.php?lang=".$account->language."&account=".$account->login.$alert));
 }
}

/**
 * Account Logout
 */
function account_logout(){
 // destroy session
 session_destroy();
 session_start();
 // redirect
 exit(header("location: ../index.php"));
}

/**
 * Account Save
 */
function account_save(){
 if(!api_checkPermission("accounts","accounts_edit")){api_die("accessDenied");}
 // get objects
 $account=api_accounts_account($_GET['idAccount']);
 // acquire variables
 $p_name=addslashes($_POST['name']);
 $p_account=addslashes($_POST['account']);
 $p_ldap=addslashes($_POST['ldap']);
 $p_phone=$_POST['phone'];
 $p_language=addslashes($_POST['language']);
 $p_enabled=$_POST['enabled'];
 $p_superuser=$_POST['superuser'];
 // custom account fields
 $custom_fields=array();
 if(is_array($GLOBALS['custom_fields']['accounts'])){
  foreach($GLOBALS['custom_fields']['accounts'] as $field){
   if(isset($_POST[$field])){$custom_fields[$field]=addslashes($_POST[$field]);}
  }
 }
 // check duplicates
 if(($p_account<>$account->account)||(strlen($p_ldap)&&$p_ldap<>$account->ldap)){
  if($GLOBALS['db']->countOf("accounts_accounts","id<>'".$account->id."' AND account LIKE '".$p_account."'") ||
     $GLOBALS['db']->countOf("accounts_accounts","id<>'".$account->id."' AND ldap LIKE '".$p_ldap."'") ){
   // redirect without save
   $alert="&alert=accountDuplicate&alert_class=alert-error";
   exit(header("location: accounts_edit.php?idAccount=".$account->id.$alert));
  }
 }
 // build query
 if($account->id){
  $query="UPDATE accounts_accounts SET
   account='".$p_account."',
   name='".$p_name."',
   ldap='".$p_ldap."',
   phone='".$p_phone."',
   language='".$p_language."',";
  foreach($custom_fields as $field=>$value){$query.=$field."='".$value."',";}
  $query.="enabled='".$p_enabled."',
   superuser='".$p_superuser."',
   updDate='".api_now()."',
   updIdAccount='".api_account()->id."'
   WHERE id='".$account->id."'";
  // execute query
  $GLOBALS['db']->execute($query);
  // log event
  $log=api_log(API_LOG_NOTICE,"accounts","accountUpdated",
   "{logs_accounts_accountUpdated|".$p_name."|".$p_account."|".$p_ldap."}",
   $account->id,"accounts/accounts_edit.php?idAccount=".$account->id);
  // if account was disabled remove notifications
  if(!$p_enabled){
   $GLOBALS['db']->execute("DELETE FROM logs_subscriptions WHERE idAccount='".$account->id."'");
   $GLOBALS['db']->execute("UPDATE logs_notifications SET status='3' WHERE idAccount='".$account->id."'");
  }
  // redirect
  $alert="&alert=accountUpdated&alert_class=alert-success&alert_parameters=".$p_name."&idLog=".$log->id;
  exit(header("location: accounts_edit.php?idAccount=".$account->id.$alert));
 }else{
  // make secret random string
  if(!api_getOption("ldap")||!strlen($p_ldap)){$secret=api_randomString(32);}
  // build query
  $query="INSERT INTO accounts_accounts (account,password,secret,name,ldap,phone,language,addDate,addIdAccount";
  foreach($custom_fields as $field=>$value){$query.=",".$field;}
  $query.=") VALUES ('".$p_account."','".md5(api_randomString(10))."','".$secret."','".$p_name."',
    '".$p_ldap."','".$p_phone."','".$p_language."','".api_now()."','".api_account()->id."'";
  foreach($custom_fields as $field=>$value){$query.=",'".$value."'";}
  $query.=")";
  // execute query
  $GLOBALS['db']->execute($query);
  // set id to last inserted id
  $q_idAccount=$GLOBALS['db']->lastInsertedId();
  // log event
  $log=api_log(API_LOG_NOTICE,"accounts","accountCreated",
   "{logs_accounts_accountCreated|".$p_name."|".$p_account."|".$p_ldap."}",
   $q_idAccount,"accounts/accounts_edit.php?idAccount=".$q_idAccount);
  // load user language file for mail
  api_loadLocaleFile("../accounts/",$p_language);
  // sendmail
  if(api_getOption("ldap") && strlen($p_ldap)){
   $access_link="http://".$_SERVER['SERVER_NAME'].$GLOBALS['dir']."accounts/login.php?lang=".$p_language."&account=".$p_ldap;
   $mail_content=api_text("mails_accounts_accountCreated-message-ldap",array($p_name,$p_ldap,$access_link,api_getOption("owner")));}
  else{
   $activation_link="http://".$_SERVER['SERVER_NAME'].$GLOBALS['dir']."accounts/password_reset.php?account=".$p_account."&key=".$secret;
   $mail_content=api_text("mails_accounts_accountCreated-message",array($p_name,$activation_link,api_getOption("owner")));
  }
  if($q_idAccount>0){api_mailer($p_account,$mail_content,api_text("mails_accounts_accountCreated-subject"));}
  // restore language
  api_loadLocaleFile("../accounts/");
  // redirect
  $alert="&alert=accountCreated&alert_class=alert-success&alert_parameters=".$p_name."&idLog=".$log->id;
  exit(header("location: accounts_edit.php?idAccount=".$q_idAccount.$alert));
 }
}

/**
 * Account Save from LDAP
 */
function account_save_ldap(){
 // acquire variables
 $p_ldap=addslashes(strtolower($_POST['ldap']));
 $p_account=addslashes(strtolower($_POST['account']));
 $p_firstname=addslashes($_POST['firstname']);
 $p_lastname=addslashes($_POST['lastname']);
 $p_phone=$_POST['phone'];
 $p_language=$_POST['language'];
 // set name
 $f_name=ucwords(strtolower($p_lastname))." ".ucwords(strtolower($p_firstname));
 // check if not exist
 if(strlen($p_ldap)>0 && !$GLOBALS['db']->countOf("accounts_accounts","ldap='".$p_ldap."'")){
  // build query
  $query="INSERT INTO accounts_accounts
   (account,password,name,phone,ldap,language,addDate,addIdAccount) VALUES
   ('".$p_account."','".md5(api_randomString(10))."','".$f_name."','".$p_phone."',
    '".$p_ldap."','".$p_language."','".api_now()."','1')";
  // execute query
  $GLOBALS['db']->execute($query);
  // retrieve last inserted id
  $q_idAccount=$GLOBALS['db']->lastInsertedId();
  // log event
  api_log(API_LOG_NOTICE,"accounts","accountLDAP",
   "{logs_accounts_accountLDAP|".$f_name."|".$p_account."|".$p_ldap."}",
   $q_idAccount,"accounts/accounts_edit.php?idAccount=".$q_idAccount);
  // if is set account mail
  if(strlen($p_account)>0){
   // load user language file for mail
   api_loadLocaleFile("../accounts/",$p_language);
   $access_link="http://".$_SERVER['SERVER_NAME'].$GLOBALS['dir']."accounts/login.php?lang=".$p_language."&account=".$p_ldap;
   // sendmail
   api_mailer($p_account,api_text("mails_accounts_accountLDAP-message",array($f_name,$p_ldap,$access_link,api_getOption("owner"))),api_text("mails_accounts_accountLDAP-subject"));
   // restore language
   api_loadLocaleFile("../accounts/");
  }
  // check for workflow module
  if(file_exists("../workflows/api.inc.php")){
   require_once("../workflows/api.inc.php");
   if(function_exists(api_workflows_workflowAdd)){
    $w_subject="New Coordinator account activated for ".$f_name;
    $w_description="New Coordinator account activated to be enabled.";
    // create workflow ticket
    api_workflows_workflowAdd($w_subject,$w_description,$f_name,$p_phone,NULL,3,$q_idAccount);
   }
  }
  $alert="&alert=accountLDAP&alert_class=alert-success";
  exit(header("location: login.php?lang=".$p_language."&account=".$p_ldap.$alert));
 }else{
  $alert="?alert=accountLDAPError&alert_class=alert-error";
  exit(header("location: login.php".$alert));
 }
}

/**
 * Account Customize
 */
function account_customize(){
 // get objects
 $account=api_accounts_account();
 // check objects
 if(!$account->id){echo api_text("accountNotFound");return FALSE;}
 // acquire variables
 $p_account=addslashes($_POST['account']);
 $p_password=$_POST['password'];
 $p_confirm=$_POST['confirm'];
 $p_name=addslashes($_POST['name']);
 $p_phone=addslashes($_POST['phone']);
 $p_language=addslashes($_POST['language']);
 // update session language
 $_SESSION['language']=$p_language;
 // check duplicates
 if($p_account<>$account->account){
  if($GLOBALS['db']->countOf("accounts_accounts","id<>'".$account->id."' AND account LIKE '".$p_account."'")){
   // redirect without save
   $alert="?alert=accountDuplicate&alert_class=alert-error";
   exit(header("location: accounts_customize.php".$alert));
  }
 }
 // check for password change
 if(strlen($p_password)){
  if(strlen($p_password)>6 && $p_password==$p_confirm){
   $password_query="password='".md5($p_password)."',";
  }else{
   // redirect without save
   $alert="?alert=accountPasswordError&alert_class=alert-error";
   exit(header("location: accounts_customize.php".$alert));
  }
 }
 // build query
 $query="UPDATE accounts_accounts SET
  account='".$p_account."',
  ".$password_query."
  name='".$p_name."',
  phone='".$p_phone."',
  language='".$p_language."'
  WHERE id='".$account->id."'";
 // execute query
 $GLOBALS['db']->execute($query);
 // alert
 $alert="?alert=accountCustomized&alert_class=alert-success&idLog=".$log->id;

 // log event
 $log=api_log(API_LOG_NOTICE,"accounts","accountCustomized",
  "{logs_accounts_accountCustomized|".$p_name."|".$p_account."|".$account->ldap."}",
  $account->id,"accounts/accounts_edit.php?idAccount=".$account->id);

 // notice for account change to old mail address      ---------------
 if($p_account<>$account->account){
  $alert="?alert=accountChanged&alert_class=alert-success&idLog=".$log->id;
  api_mailer($account->account,api_text("mails_accounts_accountChanged-message",array($p_name,$p_account,api_getOption("owner"))),api_text("mails_accounts_accountChanged-subject"));
 }

  // notice for password change
 if(strlen($password_query)){
  $alert="?alert=passwordChanged&alert_class=alert-success&idLog=".$log->id;
  api_mailer($account->account,api_text("mails_accounts_passwordChanged-message",array($p_name,api_getOption("owner"))),api_text("mails_accounts_passwordChanged-subject"));
 }

 // upload avatar       --------------
 if(isset($_FILES['avatar']['tmp_name']) && $_FILES['avatar']['tmp_name']<>'' && intval($_FILES['avatar']['size'])>0){
  if($_FILES['avatar']['error']==UPLOAD_ERR_OK){
   // scale avatar to 125x125 and convert it to jpg
   api_avatarResize($_FILES['avatar']['tmp_name'],"../uploads/uploads/accounts/avatar_".$account->id.".jpg",125,125);
  }
 }

 // update account object
 $_SESSION['account']=api_accounts_account($account->id);

 // redirect
 exit(header("location: accounts_customize.php".$alert));
}

/**
 * Account Delete
 *
 * @param string $action delete | undelete
 */
function account_delete($action){
 if(!api_checkPermission("accounts","accounts_manage")){api_die("accessDenied");}
 // get objects
 $account=api_accounts_account($_GET['idAccount']);
 // check objects
 if(!$account->id){echo api_text("accountNotFound");return FALSE;}
 // build query
 switch($action){
  case "delete":
   $query="UPDATE accounts_accounts SET enabled='0',del='1',updDate='".api_now()."',updIdAccount='".api_account()->id."' WHERE id='".$account->id."'";
   $log_action="accountDeleted";
   $redirect="accounts_list.php";
   break;
  case "undelete":
   $query="UPDATE accounts_accounts SET del='0',updDate='".api_now()."',updIdAccount='".api_account()->id."' WHERE id='".$account->id."'";
   $log_action="accountUndeleted";
   $redirect="accounts_edit.php";
   break;
  default:$query=NULL;
 }
 // execute query
 if($query){
  $GLOBALS['db']->execute($query);
  // log event
  $log=api_log(API_LOG_WARNING,"accounts",$log_action,
   "{logs_accounts_".$log_action."|".$account->id."|".$account->name."}",
   $account->id,"accounts/accounts_edit.php?idAccount=".$account->id);
  // alert
  $alert="&alert=".$log_action."&alert_class=alert-warning&alert_parameters=".$account->name."&idLog=".$log->id;
 }
 // redirect
 exit(header("location: ".$redirect."?idAccount=".$account->id.$alert));
}

/* -[ Account Interpret ]---------------------------------------------------- */
function account_interpret(){
 // check for admins
 if(!api_account()->administrator){api_die("accessDenied");}
 $interpreter=api_account()->id;
 // reset session
 session_destroy();
 session_start();
 // acquire variables
 $g_idAccount=$_GET['idAccount'];
 // get object
 $account=api_accounts_account($g_idAccount);
 // set interpreter account
 $account->interpreter=$interpreter;
 // set session to account
 $_SESSION['account']=$account;
 // update session language
 $_SESSION['language']=api_account()->language;
 // update session company
 $_SESSION['company']=api_account()->companies[api_account()->mainCompany];
 // update account permissions
 $_SESSION['permissions']=api_loadAccountPermission();
 // log
 $log=api_log(API_LOG_WARNING,"accounts","accountInterpreted",
  "{logs_accounts_accountInterpreted|".$interpreter."|".api_account($interpreter)->name."|".api_account()->id."|".api_account()->name."}");
 // redirect
 $alert="?alert=accountInterpreted&alert_class=alert-success&alert_parameters=".api_account()->name."&idLog=".$log->id;
 exit(header("location: accounts_customize.php".$alert));
}

/* -[ Account Interpretation Stop ]------------------------------------------ */
function account_interpret_stop(){
 // check for interpretation
 if(!api_account()->interpreter){api_die("accessDenied");}
 $interpreted=api_account()->id;
 // get object
 $account=api_accounts_account(api_account()->interpreter);
 // reset session
 session_destroy();
 session_start();
 $_SESSION['account']=$account;
 $_SESSION['account']->administrator=TRUE;
 // update session language
 $_SESSION['language']=api_account()->language;
 // update session company
 $_SESSION['company']=api_account()->companies[api_account()->mainCompany];
 // update account permissions
 $_SESSION['permissions']=api_loadAccountPermission();
 // log
 $log=api_log(API_LOG_NOTICE,"accounts","accountInterpretedStop",
  "{logs_accounts_accountInterpretedStop|".api_account()->id."|".api_account()->name."|".$interpreted."|".api_account($interpreted)->name."}");
 // redirect
 $alert="?alert=accountInterpretedStop&alert_class=alert-success&alert_parameters=".api_account($interpreted)->name."&idLog=".$log->id;
 exit(header("location: accounts_customize.php".$alert));
}

/**
 * Account Switch
 *
 * @param boolean $typology 0 user, 1 administrator
 */
function account_switch($typology){
 // check if user is super user
 if(api_account()->superuser){
  // make to text
  if($typology){$to="Admin";}else{$to="User";}
  // switch session administrator flag
  $_SESSION['account']->administrator=$typology;
  // log event
  $log=api_log(API_LOG_NOTICE,"accounts","accountSwitchTo".$to,
   "{logs_accounts_accountSwitchTo".$to."|".api_account()->id."|".api_account()->name."}");
  // alert
  $alert="?alert=accountSwitchTo".$to."&alert_class=alert-success&alert_parameters=".api_account()->name."&idLog=".$log->id;
 }
 // redirect
 exit(header("location: index.php".$alert));
}

/**
 * Account Debug
 */
function account_debug($enable){
 // toggle debug
 $_SESSION['account']->debug=$enable;
 // redirect
 $alert="?alert=accountDebug".$enable."&alert_class=alert-success";
 exit(header("location: index.php".$alert));
}

/**
 * Account Company Add
 */
function account_company_add(){
 if(!api_checkPermission("accounts","accounts_edit")){api_die("accessDenied");}
 // get objects
 $account=api_accounts_account($_GET['idAccount']);
 $company=api_accounts_company($_POST['idCompany']);
 $role=api_accounts_role($_POST['idRole']);
 // check objects
 if(!$account->id){echo api_text("accountNotFound");return FALSE;}
 if(!$company->id){echo api_text("companyNotFound");return FALSE;}
 // check for duplicates
 if($GLOBALS['db']->countOf("accounts_accounts_join_companies","idAccount='".$account->id."' AND idCompany='".$company->id."'")){
  // store main value
  $f_main=$GLOBALS['db']->queryUniqueValue("SELECT main FROM accounts_accounts_join_companies WHERE idAccount='".$account->id."' AND idCompany='".$company->id."'");
  // remove duplicates
  $GLOBALS['db']->execute("DELETE FROM accounts_accounts_join_companies WHERE idAccount='".$account->id."' AND idCompany='".$company->id."'");
 }else{
  // check if is first company and mainize
  if(!$GLOBALS['db']->countOf("accounts_accounts_join_companies","idAccount='".$account->id."' AND main='1'")){$f_main=1;}else{$f_main=0;}
 }
 // add company
 $query="INSERT INTO accounts_accounts_join_companies
  (idAccount,idCompany,idRole,main) VALUES
  ('".$account->id."','".$company->id."','".$role->id."','".$f_main."')";
 // execute query
 $GLOBALS['db']->execute($query);
 // log event
 $log=api_log(API_LOG_NOTICE,"accounts","accountCompanyAdded",
  "{logs_accounts_accountCompanyAdded|".$account->name."|".$company->id."|".$company->name."|".$role->id."|".$role->level."|".$role->name."}");
 // redirect
 $alert="&alert=accountUpdated&alert_class=alert-success&alert_parameters=".$account->name."&idLog=".$log->id;
 exit(header("location: accounts_edit.php?idAccount=".$account->id.$alert));
}

/**
 * Account Company Mainize
 */
function account_company_mainize(){
 if(!api_checkPermission("accounts","accounts_edit")){api_die("accessDenied");}
 // get objects
 $account=api_accounts_account($_GET['idAccount']);
 $company=api_accounts_company($_GET['idCompany']);
 // check objects
 if(!$account->id){echo api_text("accountNotFound");return FALSE;}
 if(!$company->id){echo api_text("companyNotFound");return FALSE;}
 // remove old main if exist
 $GLOBALS['db']->execute("UPDATE accounts_accounts_join_companies SET main='0' WHERE idAccount='".$account->id."'");
 // make company main
 $GLOBALS['db']->execute("UPDATE accounts_accounts_join_companies SET main='1' WHERE idAccount='".$account->id."' AND idCompany='".$company->id."'");
 // log event
 $log=api_log(API_LOG_NOTICE,"accounts","accountCompanyMainize",
  "{logs_accounts_accountCompanyMainize|".$account->name."|".$company->id."|".$company->name."}");
 // redirect
 $alert="&alert=accountUpdated&alert_class=alert-success&alert_parameters=".$account->name."&idLog=".$log->id;
 exit(header("location: accounts_edit.php?idAccount=".$account->id.$alert));
}

/**
 * Account Company Remove
 */
function account_company_remove(){
 if(!api_checkPermission("accounts","accounts_edit")){api_die("accessDenied");}
 // get objects
 $account=api_accounts_account($_GET['idAccount']);
 $company=api_accounts_company($_GET['idCompany']);
 // check objects
 if(!$account->id){echo api_text("accountNotFound");return FALSE;}
 if(!$company->id){echo api_text("companyNotFound");return FALSE;}
 // remove company
 $GLOBALS['db']->execute("DELETE FROM accounts_accounts_join_companies WHERE idAccount='".$account->id."' AND idCompany='".$company->id."'");
 // remove company assigned groups
 $GLOBALS['db']->execute("DELETE accounts_accounts_join_groups.* FROM accounts_accounts_join_groups JOIN accounts_groups ON accounts_groups.id=accounts_accounts_join_groups.idGroup WHERE accounts_accounts_join_groups.idAccount='".$account->id."' AND accounts_groups.idCompany='".$company->id."'");
 // log event
 $log=api_log(API_LOG_NOTICE,"accounts","accountCompanyRemoved",
  "{logs_accounts_accountCompanyRemoved|".$account->name."|".$company->id."|".$company->name."}");
 // redirect
 $alert="&alert=accountUpdated&alert_class=alert-success&alert_parameters=".$account->name."&idLog=".$log->id;
 exit(header("location: accounts_edit.php?idAccount=".$account->id.$alert));
}


/**
 * Account Group Add
 */
function account_group_add(){
 if(!api_checkPermission("accounts","accounts_edit")){api_die("accessDenied");}
 // get objects
 $account=api_accounts_account($_GET['idAccount']);
 $company=api_accounts_company($_GET['idCompany']);
 $group=api_accounts_group($_POST['idGroup']);
 // check objects
 if(!$account->id){echo api_text("accountNotFound");return FALSE;}
 if(!$company->id){echo api_text("companyNotFound");return FALSE;}
 if(!$group->id){echo api_text("groupNotFound");return FALSE;}
 // check if is first group and mainize
 if(!$GLOBALS['db']->countOf("accounts_accounts_join_groups JOIN accounts_groups ON accounts_groups.id=accounts_accounts_join_groups.idGroup","idAccount='".$account->id."' AND idCompany='".$company->id."' AND main='1'")){$f_main="1";}else{$f_main="0";}
 // check for duplicates
 if(!$GLOBALS['db']->countOf("accounts_accounts_join_groups","idAccount='".$account->id."' AND idGroup='".$group->id."'")){
  // add group
  $GLOBALS['db']->execute("INSERT INTO accounts_accounts_join_groups (idAccount,idGroup,main) VALUES ('".$account->id."','".$group->id."','".$f_main."')");
  // log event
  $log=api_log(API_LOG_NOTICE,"accounts","accountGroupAdded",
  "{logs_accounts_accountGroupAdded|".$account->name."|".$company->id."|".$company->name."|".$group->id."|".$group->name."}");
 }
 // redirect
 $alert="&alert=accountUpdated&alert_class=alert-success&alert_parameters=".$account->name."&idLog=".$log->id;
 exit(header("location: accounts_edit.php?idAccount=".$account->id.$alert));
}

/**
 * Account Group Mainize
 */
function account_group_mainize(){
 if(!api_checkPermission("accounts","accounts_edit")){api_die("accessDenied");}
 // get objects
 $account=api_accounts_account($_GET['idAccount']);
 $company=api_accounts_company($_GET['idCompany']);
 $group=api_accounts_group($_GET['idGroup']);
 // check objects
 if(!$account->id){echo api_text("accountNotFound");return FALSE;}
 if(!$company->id){echo api_text("companyNotFound");return FALSE;}
 if(!$group->id){echo api_text("groupNotFound");return FALSE;}
 // remove old main if exist
 $GLOBALS['db']->execute("UPDATE accounts_accounts_join_groups JOIN accounts_groups ON accounts_groups.id=accounts_accounts_join_groups.idGroup SET main='0' WHERE idAccount='".$account->id."' AND idCompany='".$company->id."'");
 // make group main
 $GLOBALS['db']->execute("UPDATE accounts_accounts_join_groups SET main='1' WHERE idAccount='".$account->id."' AND idGroup='".$group->id."'");
 // log event
 $log=api_log(API_LOG_NOTICE,"accounts","accountGroupMainize",
  "{logs_accounts_accountGroupMainize|".$account->name."|".$company->id."|".$company->name."|".$group->id."|".$group->name."}");
 // redirect
 $alert="&alert=accountUpdated&alert_class=alert-success&alert_parameters=".$account->name."&idLog=".$log->id;
 exit(header("location: accounts_edit.php?idAccount=".$account->id.$alert));
}

/**
 * Account Group Remove
 */
function account_group_remove(){
 if(!api_checkPermission("accounts","accounts_edit")){api_die("accessDenied");}
 // get objects
 $account=api_accounts_account($_GET['idAccount']);
 $company=api_accounts_company($_GET['idCompany']);
 $group=api_accounts_group($_GET['idGroup']);
 // check objects
 if(!$account->id){echo api_text("accountNotFound");return FALSE;}
 if(!$company->id){echo api_text("companyNotFound");return FALSE;}
 if(!$group->id){echo api_text("groupNotFound");return FALSE;}
 // remove group
 $GLOBALS['db']->execute("DELETE FROM accounts_accounts_join_groups WHERE idAccount='".$account->id."' AND idGroup='".$group->id."'");
 // log event
 $log=api_log(API_LOG_NOTICE,"accounts","accountGroupRemoved",
  "{logs_accounts_accountGroupRemoved|".$account->name."|".$company->id."|".$company->name."|".$group->id."|".$group->name."}");
 // redirect
 $alert="&alert=accountUpdated&alert_class=alert-success&alert_parameters=".$account->name."&idLog=".$log->id;
 exit(header("location: accounts_edit.php?idAccount=".$account->id.$alert));
}


/**
 * Password Retrieve
 */
function password_retrieve(){
 // acquire variables
 $g_language=$_GET['language'];
 $p_account=$_POST['account'];
 // check account
 if(!strlen($p_account)){echo api_text("accountError");return FALSE;}
 $account=api_accounts_account($GLOBALS['db']->queryUniqueValue("SELECT id FROM accounts_accounts WHERE account='".$p_account."' AND ( ISNULL(ldap) OR ldap='')"));
 if(!$account->id){echo api_text("accountError");return FALSE;}
 // build query
 $secret=api_randomString(32);
 $query="UPDATE accounts_accounts SET
  password='".md5(api_randomString(10))."',
  secret='".$secret."'
  WHERE id='".$account->id."'";
 // execute query
 $GLOBALS['db']->execute($query);
 // log event
 $log=api_log(API_LOG_NOTICE,"accounts","accountPasswordRetrieve",
  "{logs_accounts_accountPasswordRetrieve|".$account->account."|".$account->name."}",
  $account->id,"accounts/accounts_edit.php?idAccount=".$account->id);
 // load user language file for mail
 api_loadLocaleFile("../accounts/",$account->language);
 // sendmail
 $activation_link="http://".$_SERVER['SERVER_NAME'].$GLOBALS['dir']."accounts/password_reset.php?account=".$account->account."&key=".$secret;
 api_mailer($p_account,api_text("mails_accounts_passwordRetrieve-message",array($account->name,$activation_link)),api_text("mails_accounts_passwordRetrieve-subject"));
 // redirect
 $alert="&alert=passwordRetrived&alert_class=alert-success&idLog=".$log->id;
 exit(header("location: login.php?lang=".$g_language.$alert));
}

/**
 * Password Reset
 */
function password_reset(){
 // acquire variables
 $p_account=$_POST['account'];
 $p_secret=$_POST['secret'];
 $p_password=$_POST['password'];
 $p_confirm=$_POST['confirm'];
 // check account
 if(!strlen($p_secret)||!strlen($p_account)){echo api_text("accountError");return FALSE;}
 if($p_password<>$p_confirm){echo api_text("accountError");return FALSE;}
 $account=api_accounts_account($GLOBALS['db']->queryUniqueValue("SELECT id FROM accounts_accounts WHERE account='".$p_account."' AND secret='".$p_secret."' AND ( ISNULL(ldap) OR ldap='')"));
 if(!$account->id){echo api_text("accountError");return FALSE;}
 // build query
 $query="UPDATE accounts_accounts SET
  password='".md5($p_password)."',
  secret=NULL
  WHERE id='".$account->id."'";
 // execute query
 $GLOBALS['db']->execute($query);
 // log event
 $log=api_log(API_LOG_NOTICE,"accounts","accountPasswordResetted",
  "{logs_accounts_accountPasswordResetted|".$account->account."|".$account->name."}",
  $account->id,"accounts/accounts_edit.php?idAccount=".$account->id);
 // load user language file for mail
 api_loadLocaleFile("../accounts/",$account->language);
 // sendmail
 api_mailer($p_account,api_text("mails_accounts_passwordReset-message",$account->name),api_text("mails_accounts_passwordReset-subject"));
 // redirect
 $alert="&alert=passwordResetted&alert_class=alert-success&idLog=".$log->id;
 exit(header("location: login.php?lang=".$account->language.$alert));
}


/**
 * Role Save
 */
function role_save(){
 if(!api_checkPermission("accounts","roles_edit")){api_die("accessDenied");}
 // get objects
 $role=api_accounts_role($_GET['idRole']);
 // acquire variables
 $p_level=$_POST['level'];
 $p_name=addslashes($_POST['name']);
 $p_description=addslashes($_POST['description']);
 // build query
 if($role->id){
  $query="UPDATE accounts_roles SET
   level='".$p_level."',
   name='".$p_name."',
   description='".$p_description."',
   updDate='".api_now()."',
   updIdAccount='".api_account()->id."'
   WHERE id='".$role->id."'";
  // execute query
  $GLOBALS['db']->execute($query);
  // log event
  $log=api_log(API_LOG_NOTICE,"accounts","roleUpdated",
   "{logs_accounts_roleUpdated|".$p_level."|".$p_name."|".$p_description."}",
   $role->id,"accounts/roles_edit.php?idRole=".$role->id);
  // redirect
  $alert="&alert=roleUpdated&alert_class=alert-success&alert_parameters=".$p_name."&idLog=".$log->id;
  exit(header("location: roles_list.php?idRole=".$role->id.$alert));
 }else{
  $query="INSERT INTO accounts_roles
   (level,name,description,addDate,addIdAccount) VALUES
   ('".$p_level."','".$p_name."','".$p_description."','".api_now()."','".api_account()->id."')";
  // execute query
  $GLOBALS['db']->execute($query);
  // get last inserted id
  $q_idRole=$GLOBALS['db']->lastInsertedId();
  // log event
  $log=api_log(API_LOG_NOTICE,"accounts","roleCreated",
   "{logs_accounts_roleCreated|".$p_level."|".$p_name."|".$p_description."}",
   $q_idRole,"accounts/roles_edit.php?idRole=".$q_idRole);
  // redirect
  $alert="&alert=roleCreated&alert_class=alert-success&alert_parameters=".$p_name."&idLog=".$log->id;
  exit(header("location: roles_list.php?idRole=".$q_idRole.$alert));
 }
}

/**
 * Role Delete
 */
function role_delete(){
 if(!api_checkPermission("accounts","roles_edit")){api_die("accessDenied");}
 // get objects
 $role=api_accounts_role($_GET['idRole']);
 if(!$role->id){echo api_text("roleNotFound");return FALSE;}
 // delete role
 $GLOBALS['db']->execute("DELETE FROM accounts_roles WHERE id='".$role->id."'");
  // log event
  $log=api_log(API_LOG_NOTICE,"accounts","roleDeleted",
   "{logs_accounts_roleDeleted|".$role->level."|".$role->name."|".$role->description."}");
  // redirect
  $alert="?alert=roleCreated&alert_class=alert-success&alert_parameters=".$role->name."&idLog=".$log->id;
  exit(header("location: roles_list.php".$alert));
}


/* -[ Company Save ]--------------------------------------------------------- */
function company_save(){
 $g_id=$_GET['id'];
 if(!isset($g_id)){$g_id=0;}
 // acquire variables
 $p_company=addslashes($_POST['company']);
 $p_division=addslashes($_POST['division']);
 $p_name=addslashes($_POST['name']);
 $p_fiscal_name=addslashes($_POST['fiscal_name']);
 $p_fiscal_vat=addslashes($_POST['fiscal_vat']);
 $p_fiscal_code=addslashes($_POST['fiscal_code']);
 $p_fiscal_rea=addslashes($_POST['fiscal_rea']);
 $p_fiscal_capital=addslashes($_POST['fiscal_capital']);
 $p_fiscal_currency=addslashes($_POST['fiscal_currency']);
 $p_address_address=addslashes($_POST['address_address']);
 $p_address_zip=addslashes($_POST['address_zip']);
 $p_address_city=addslashes($_POST['address_city']);
 $p_address_district=addslashes($_POST['address_district']);
 $p_address_country=addslashes($_POST['address_country']);
 $p_phone_office=addslashes($_POST['phone_office']);
 $p_phone_mobile=addslashes($_POST['phone_mobile']);
 $p_phone_fax=addslashes($_POST['phone_fax']);
 $p_mail=addslashes($_POST['mail']);
 // build query
 if($g_id>0){
  if(!api_checkPermission("accounts","companies_edit")){api_die("accessDenied");}
  $query="UPDATE accounts_companies SET
   company='".$p_company."',
   division='".$p_division."',
   name='".$p_name."',
   fiscal_name='".$p_fiscal_name."',
   fiscal_vat='".$p_fiscal_vat."',
   fiscal_code='".$p_fiscal_code."',
   fiscal_rea='".$p_fiscal_rea."',
   fiscal_capital='".$p_fiscal_capital."',
   fiscal_currency='".$p_fiscal_currency."',
   address_address='".$p_address_address."',
   address_zip='".$p_address_zip."',
   address_city='".$p_address_city."',
   address_district='".$p_address_district."',
   address_country='".$p_address_country."',
   phone_office='".$p_phone_office."',
   phone_mobile='".$p_phone_mobile."',
   phone_fax='".$p_phone_fax."',
   mail='".$p_mail."'
   WHERE id='".$g_id."'";
  // log event
  $log=api_log(API_LOG_NOTICE,"accounts","companyUpdated",
   "{logs_accounts_companyUpdated|".$p_company."|".$p_division."|".$p_name."}");
  // alert
  $alert="?alert=companyUpdated&alert_class=alert-success&alert_parameters=".$p_name."&idLog=".$log->id;
 }else{
  if(!api_checkPermission("accounts","companies_add")){api_die("accessDenied");}
  $query="INSERT INTO accounts_companies
   (company,division,name,fiscal_name,fiscal_vat,fiscal_code,fiscal_rea,fiscal_capital,
    fiscal_currency,address_address,address_zip,address_city,address_district,address_country,
    phone_office,phone_mobile,phone_fax,mail) VALUES
   ('".$p_company."','".$p_division."','".$p_name."','".$p_fiscal_name."','".$p_fiscal_vat."',
    '".$p_fiscal_code."','".$p_fiscal_rea."','".$p_fiscal_capital."','".$p_fiscal_currency."',
    '".$p_address_address."','".$p_address_zip."','".$p_address_city."','".$p_address_district."',
    '".$p_address_country."','".$p_phone_office."','".$p_phone_mobile."','".$p_phone_fax."',
    '".$p_mail."')";
  // log event
  $log=api_log(API_LOG_NOTICE,"accounts","companyCreated",
   "{logs_accounts_companyCreated|".$p_company."|".$p_division."|".$p_name."}");
  // alert
  $alert="?alert=companyCreated&alert_class=alert-success&alert_parameters=".$p_name."&idLog=".$log->id;
 }
 // execute query
 $GLOBALS['db']->execute($query);
 // redirect
 exit(header("location: companies_list.php".$alert));
}

/* -[ Company Delete ]--------------------------------------------------------- */
function company_delete(){
 if(!api_checkPermission("accounts","companies_delete")){api_die("accessDenied");}
 $g_id=$_GET['id'];
 if(!isset($g_id)){$g_id=0;}
 $company=$GLOBALS['db']->queryUniqueObject("SELECT * FROM accounts_companies WHERE id='".$g_id."'");
 if($company->id>0){
  die("Function disabled for security reason");
  /*
  // count if accounts_divisions==0
  // count if accounts_accounts==0
  // delete company
  $GLOBALS['db']->execute("DELETE FROM accounts_companies WHERE id='".$company->id."'");
  // log event
  $log=api_log(API_LOG_WARNING,"accounts","companyDeleted",
   "{logs_accounts_companyDeleted|".$company->company."|".$company->division."|".$company->name."}");
  */
 }
 // redirect
 $alert="?alert=companyDeleted&alert_class=alert&alert_parameters=".$company->name."&idLog=".$log->id;
 exit(header("location: companies_list.php".$alert));
}


/**
 * Group Save
 */
function group_save(){
 if(!api_checkPermission("accounts","groups_edit")){api_die("accessDenied");}
 // get objects
 $group=api_accounts_group($_GET['idGroup']);
 $company=api_accounts_company($_POST['idCompany']);
 if(!$company->id){echo api_text("companyNotFound");return FALSE;}
 // acquire variables
 $p_idGroup=$_POST['idGroup'];
 $p_name=addslashes($_POST['name']);
 $p_description=addslashes($_POST['description']);
 // checks
 if($p_idGroup==0){$p_idGroup=NULL;}
 // check duplicates name
 if($GLOBALS['db']->countOf("accounts_groups","id<>'".$group->id."' AND idCompany='".$company->id."' AND name LIKE '".$p_name."'")){
  // redirect without save
  $alert="&alert=groupDuplicate&alert_class=alert-error&alert_parameters=".$p_name."&idLog=".$log->id;
  exit(header("location: groups_edit.php?idGroup=".$group->id.$alert));
 }
 // build query
 if($group->id){
  $query="UPDATE accounts_groups SET
   idCompany='".$company->id."',
   idGroup='".$p_idGroup."',
   name='".$p_name."',
   description='".$p_description."',
   updDate='".api_now()."',
   updIdAccount='".api_account()->id."'
   WHERE id='".$group->id."'";
  // execute query
  $GLOBALS['db']->execute($query);
  // log event
  $log=api_log(API_LOG_NOTICE,"accounts","groupUpdated",
   "{logs_accounts_groupUpdated|".$company->id."|".$company->name."|".$p_name."|".$p_description."}",
   $group->id,"accounts/groups_edit.php?idRole=".$group->id);
  // redirect
  $alert="&alert=groupUpdated&alert_class=alert-success&alert_parameters=".$p_name."&idLog=".$log->id;
  exit(header("location: groups_list.php?idGroup=".$group->id.$alert));
 }else{
  $query="INSERT INTO accounts_groups
   (idCompany,idGroup,name,description,addDate,addIdAccount) VALUES
   ('".$company->id."','".$p_idGroup."','".$p_name."','".$p_description."',
    '".api_now()."','".api_account()->id."')";
  // execute query
  $GLOBALS['db']->execute($query);
  // get last inserted id
  $q_idGroup=$GLOBALS['db']->lastInsertedId();
  // log event
  $log=api_log(API_LOG_NOTICE,"accounts","groupCreated",
   "{logs_accounts_groupCreated|".$company->id."|".$company->name."|".$p_name."|".$p_description."}",
   $q_idGroup,"accounts/groups_edit.php?idGroup=".$q_idGroup);
  // redirect
  $alert="&alert=groupCreated&alert_class=alert-success&alert_parameters=".$p_name."&idLog=".$log->id;
  exit(header("location: groups_list.php?idGroup=".$q_idGroup.$alert));
 }
}

/**
 * Group Delete
 */
function group_delete(){
 if(!api_checkPermission("accounts","groups_edit")){api_die("accessDenied");}
 // get objects
 $group=api_accounts_group($_GET['idGroup']);
 $company=api_accounts_company($group->idCompany);
 if(!$group->id){echo api_text("groupNotFound");return FALSE;}
 if(!$company->id){echo api_text("companyNotFound");return FALSE;}
 // delete group
 $GLOBALS['db']->execute("DELETE FROM accounts_groups WHERE id='".$group->id."'");
 // log event
 $log=api_log(API_LOG_WARNING,"accounts","groupDeleted",
  "{logs_accounts_groupDeleted|".$company->id."|".$company->name."|".$group->name."|".$group->description."}");
 // redirect
 $alert="?alert=groupDeleted&alert_class=alert-warning&alert_parameters=".$group->name."&idLog=".$log->id;
 exit(header("location: groups_list.php".$alert));
}
