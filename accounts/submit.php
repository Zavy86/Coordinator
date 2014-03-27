<?php
/* -------------------------------------------------------------------------- *\
|* -[ Accounts - Submit ]---------------------------------------------------- *|
\* -------------------------------------------------------------------------- */
include('../core/api.inc.php');
$act=$_GET['act'];
switch($act){
 // accounts
 case "account_login":account_login();break;
 case "account_logout":account_logout();break;
 case "account_save":account_save();break;
 case "account_customize":account_customize();break;
 case "account_grouprole_add":account_grouprole_add();break;
 case "account_grouprole_delete":account_grouprole_delete();break;
 case "account_delete":account_delete();break;
 // accounts switch
 case "account_switch_to_admin":account_switch(1);break;
 case "account_switch_to_user":account_switch(2);break;
 // debug
 case "account_debug_enable":account_debug(TRUE);break;
 case "account_debug_disable":account_debug(FALSE);break;
 // password
 case "password_retrieve":password_retrieve();break;
 case "password_reset":password_reset();break;
 // groups
 case "group_save":group_save();break;
 case "group_delete":group_delete();break;
 // companies
 case "company_save":company_save();break;
 case "company_delete":company_delete();break;
 // ldap
 case "ldap_account_create":ldap_account_create();break;
 // default
 default:
  $alert="?alert=submitFunctionNotFound&alert_class=alert-warning&act=".$act;
  header("location: index.php".$alert);
}


/* -[ Account Login ]-------------------------------------------------------- */
function account_login(){
 // acquire variables
 $p_account=addslashes($_POST['account']);
 $p_password=$_POST['password'];
 // [ LDAP START ]--------------------------------------------------------------
 // if account is not an email
 if(strpos($p_account,"@")==FALSE && $p_account<>"root" && api_getOption('ldap')){
  // try ldap authentication
  include('../config.inc.php');
  include('../core/ldap.inc.php');
  if(ldap_authenticate(api_getOption('ldap_host'),api_getOption('ldap_dn'),api_getOption('ldap_domain'),$p_account,$p_password,api_getOption('ldap_userfield'),api_getOption('ldap_group'))){
   $account=$GLOBALS['db']->queryUniqueObject("SELECT * FROM accounts_accounts WHERE ldapUsername='".$p_account."'");
   if($account->id){
    // account exist
    if($account->typology==0){
     // account disabled
     api_log(2,"accounts","LOGIN FAILED - ACCOUNT DISABLED\nThe account ".$p_account." is disabled");
     $alert="?alert=loginDisabled&alert_class=alert-warning";
     header("location: login.php".$alert);
    }else{
     // check maintenance
     if($account->typology<>1 && api_getOption("maintenance")){
      $alert="?alert=maintenance&alert_class=alert-warning";
      exit(header("location: login.php".$alert));
     }
     // account enabled
     session_destroy();
     session_start();
     $_SESSION['account']=$account;
     // update session language
     $_SESSION['language']=$account->language;
     if($account->typology==1){$_SESSION['account']->typology=2;$_SESSION['account']->administrator=TRUE;}
     // update lastLogin
     $GLOBALS['db']->execute("UPDATE accounts_accounts SET lastLogin='".date('Y-m-d H:i:s')."' WHERE id='".$account->id."'");
     // redirect
     header("location: ../index.php");
    }
   }else{
    // account does not exist
    header("location: request_account_ldap.php?account=".$p_account);
   }
  }else{
   // ldap authentication failed
   api_log(1,"accounts","LOGIN FAILED - WRONG PASSWORD\nWrong password inserted for account: ".$p_account);
   $alert="?alert=loginFailed&alert_class=alert-error";
   header("location: login.php".$alert);
  }
 }else{
 // [ LDAP END ]----------------------------------------------------------------
 // retrieve account
 $account=$GLOBALS['db']->queryUniqueObject("SELECT * FROM accounts_accounts WHERE account='".$p_account."' AND password='".md5($p_password)."' AND typology>'0'");
 if($account->id){
  // check maintenance
  if($account->typology<>1 && api_getOption("maintenance")){
   $alert="?alert=maintenance&alert_class=alert-warning";
   exit(header("location: login.php".$alert));
  }
  // open new session
  session_destroy();
  session_start();
  $_SESSION['account']=$account;
  // update session language
  $_SESSION['language']=$account->language;
  if($account->typology==1){$_SESSION['account']->typology=2;$_SESSION['account']->administrator=TRUE;}
  // update lastLogin
  $GLOBALS['db']->execute("UPDATE accounts_accounts SET lastLogin='".date('Y-m-d H:i:s')."' WHERE id='".$account->id."'");
  // redirect
  header("location: ../index.php");
 }else{
  // login failed
  $account=$GLOBALS['db']->queryUniqueObject("SELECT * FROM accounts_accounts WHERE account='".$p_account."'");
  if($account->id){
   if($account->typology==0){
    api_log(3,"accounts","LOGIN FAILED - ACCOUNT DISABLED\nThe account ".$p_account." is disabled");
    $alert="?alert=loginDisabled&alert_class=alert-warning";
   }else{
    api_log(2,"accounts","LOGIN FAILED - WRONG PASSWORD\nWrong password inserted for account: ".$p_account);
    $alert="?alert=loginFailed&alert_class=alert-error";
   }
  }else{
   api_log(3,"accounts","LOGIN FAILED - ACCOUNT DOES NOT EXIST\nThe account ".$p_account." does not exist");
   $alert="?alert=loginFailed&alert_class=alert-error";
  }
  header("location: login.php".$alert);
 }
 }
}

/* -[ Account Logout ]------------------------------------------------------- */
function account_logout(){
 session_destroy();
 session_start();
 header("location: index.php");
}

/* -[ Account Save ]--------------------------------------------------------- */
function account_save(){
 $g_id=$_GET['id'];
 if(!isset($g_id)){$g_id=0;}
 // acquire variables
 $p_name=addslashes($_POST['name']);
 $p_account=addslashes($_POST['account']);
 $p_typology=$_POST['typology'];
 $p_language=addslashes($_POST['language']);
 $p_idCompany=$_POST['idCompany'];
 // build query
 if($g_id>0){
  if(!api_checkPermission("accounts","accounts_edit")){api_die("accessDenied");}
  $query="UPDATE accounts_accounts SET
   name='".$p_name."',
   account='".$p_account."',
   typology='".$p_typology."',
   language='".$p_language."',
   idCompany='".$p_idCompany."'
   WHERE id='".$g_id."'";
  // execute query
  $GLOBALS['db']->execute($query);
  // Grouprole
  // acquire variables
  $p_idGroup=$_POST['idGroup'];
  if(!isset($p_idGroup)){$p_idGroup=0;}
  $p_idGrouprole=$_POST['idGrouprole'];
  if($p_idGroup>0){
   // build query
   if($g_id>0){
    if(!api_checkPermission("accounts","accounts_edit")){api_die("accessDenied");}
    $query="INSERT INTO accounts_groups_join_accounts
     (idGroup,idAccount,idGrouprole) VALUES
     ('".$p_idGroup."','".$g_id."','".$p_idGrouprole."')";
   }
   // execute query
   $GLOBALS['db']->execute($query);
   // redirect
   header("location: accounts_edit.php?id=".$g_id);
  }else{
   // redirect
   $alert="?alert=accountUpdated&alert_class=alert-success&alert_parameters=".$p_name;
   header("location: accounts_list.php".$alert);
  }
 }else{
  if(!api_checkPermission("accounts","accounts_add")){api_die("accessDenied");}
  $secret=api_randomString(32);
  $query="INSERT INTO accounts_accounts
   (account,password,secret,name,typology,idCompany) VALUES
   ('".$p_account."','".md5(api_randomString(10))."','".$secret."','".$p_name."','".$p_typology."','".$p_idCompany."')";
  // execute query
  $GLOBALS['db']->execute($query);
  $alert="&alert=accountCreated&alert_class=alert-success&alert_parameters=".$p_name;
  // set id to last inserted id
  $g_id=$GLOBALS['db']->lastInsertedId();
  // sendmail
  $message="Benvenuto/a ".$p_name.",\n";
  $message.=" è stato attivato un account su Coordinator a tuo nome.\n\n";
  $message.="Per confermare la tua iscrizione e scegliere una password usa il seguente indirizzo:\n\n";
  $message.="http://".$_SERVER['SERVER_NAME'].$GLOBALS['dir'];
  $message.="accounts/password_reset.php?account=".$p_account."&key=".$secret."\n\n";
  $message.="Ricorda che questo codice è utilizzabile solamente per il tuo primo accesso.";
  if($g_id>0){api_sendmail($p_account,$message,"Attivazione account Coordinator personale");}
  header("location: accounts_edit.php?id=".$g_id.$alert);
 }
}

/* -[ Account Customize ]---------------------------------------------------- */
function account_customize(){
 // acquire variables
 $p_name=addslashes($_POST['name']);
 $p_language=addslashes($_POST['language']);
 $p_password=$_POST['password'];
 $p_confirm=$_POST['confirm'];
 // update session language
 $_SESSION['language']=$p_language;
 // build query
 if(strlen($p_password)>6 && $p_password==$p_confirm){
  $query="UPDATE accounts_accounts SET
   name='".$p_name."',
   password='".md5($p_password)."'
   WHERE id='".$_SESSION['account']->id."'";
  // execute query
  $GLOBALS['db']->execute($query);
  $alert="?alert=accountPasswordChanged&alert_class=alert-success";
  // sendmail
  $message="Ciao ".$_SESSION['account']->name.",\n";
  $message.=" la modifica della tua password è avvenuta correttamente.";
  api_sendmail($_SESSION['account']->account,$message,"Notifica di variazione della password");
 }else{
  $query="UPDATE accounts_accounts SET
   name='".$p_name."',
   language='".$p_language."'
   WHERE id='".$_SESSION['account']->id."'";
  // execute query
  $GLOBALS['db']->execute($query);
  $alert="?alert=accountCustomized&alert_class=alert-success";
  // upload avatar
  if(isset($_FILES['avatar']['tmp_name']) && $_FILES['avatar']['tmp_name']<>'' && intval($_FILES['avatar']['size'])>0){
   if($_FILES['avatar']['error']==UPLOAD_ERR_OK){
    // scale avatar to 125x125 and convert it to jpg
    api_avatarResize($_FILES['avatar']['tmp_name'],"../uploads/accounts/avatar_".$_SESSION['account']->id.".jpg",125,125);
   }
  }
 }
 // redirect
 header("location: index.php".$alert);
}

/* -[ Account Grouprole Add ]--------------------------------------------------------- */
function account_grouprole_add(){
 $g_id=$_GET['id'];
 if(!isset($g_id)){$g_id=0;}
 // acquire variables
 $p_idGroup=$_POST['idGroup'];
 if(!isset($p_idGroup)){$p_idGroup=0;}
 $p_idGrouprole=$_POST['idGrouprole'];
 if(!isset($p_idGrouprole)){$p_idGrouprole=1;}
 $account=$GLOBALS['db']->queryUniqueObject("SELECT * FROM accounts_accounts WHERE id='".$g_id."'");
 if($account->id>0 && $p_idGroup>0 && $p_idGrouprole>0){
  if(!api_checkPermission("accounts","accounts_edit")){api_die("accessDenied");}
  $query="INSERT INTO accounts_groups_join_accounts
   (idGroup,idAccount,idGrouprole) VALUES
   ('".$p_idGroup."','".$g_id."','".$p_idGrouprole."')";
  // execute query
  $GLOBALS['db']->execute($query);
  // alert
  $alert="&alert=accountUpdated&alert_class=alert-success&alert_parameters=".$account->name;
 }
 header("location: accounts_edit.php?id=".$g_id.$alert);
}

/* -[ Account Grouprole Delete ]--------------------------------------------- */
function account_grouprole_delete(){
 if(!api_checkPermission("accounts","accounts_edit")){api_die("accessDenied");}
 $g_idAccount=$_GET['idAccount'];
 if(!isset($g_idAccount)){$g_idAccount=0;}
 $g_idGroup=$_GET['idGroup'];
 if(!isset($g_idGroup)){$g_idGroup=0;}
 $g_from=$_GET['from'];
 if($g_idAccount>0 && $g_idGroup>0){
  // delete grouprole account
  $GLOBALS['db']->execute("DELETE FROM accounts_groups_join_accounts WHERE idAccount='".$g_idAccount."' AND idGroup='".$g_idGroup."'");
 }
 // redirect
 if($g_from=="members"){header("location: groups_members.php?idGroup=".$g_idGroup);}
  else{header("location: accounts_edit.php?id=".$g_idAccount);}
}

/* -[ Account Delete ]--------------------------------------------------------- */
function account_delete(){
 if(!api_checkPermission("accounts","accounts_delete")){api_die("accessDenied");}
 $g_id=$_GET['id'];
 if(!isset($g_id)){$g_id=0;}
 $account=$GLOBALS['db']->queryUniqueObject("SELECT * FROM accounts_accounts WHERE id='".$g_id."'");
 if($account->id>0){
  die("Function disabled for security reason");
  // delete account
  //$GLOBALS['db']->execute("DELETE FROM accounts_accounts WHERE id='".$account->id."'");
  // delete groups
  //$GLOBALS['db']->execute("DELETE FROM accounts_groups_join_accounts WHERE idAccount='".$account->id."'");
 }
 // redirect
 $alert="?alert=accountDeleted&alert_class=alert&alert_parameters=".$account->name;
 header("location: accounts_list.php".$alert);
}

/* -[ Account administrators can switch to different typology ]-------------- */
function account_switch($typology){
 if($_SESSION['account']->administrator){
  $_SESSION['account']->typology=$typology;
 }
 // redirect
 $alert="?alert=accountSwitched".$typology."&alert_class=alert-success";
 header("location: index.php".$alert);
}

/* -[ Account debug toggle ]------------------------------------------------- */
function account_debug($enable){
 // enable or disable debug
 $_SESSION['account']->debug=$enable;
 // redirect
 $alert="?alert=accountDebug".$enable."&alert_class=alert-success";
 header("location: index.php".$alert);
}


/* -[ Password Retrieve ]---------------------------------------------------- */
function password_retrieve(){
 // acquire variables
 $p_account=$_POST['account'];
 $account=$GLOBALS['db']->queryUniqueObject("SELECT * FROM accounts_accounts WHERE id>'1' AND account='".$p_account."'");
 if(!$account->id){die("FATAL ERROR /!\\");}
 // build query
 if($account->id>1){
  $secret=api_randomString(32);
  $query="UPDATE accounts_accounts SET
   password='".md5(api_randomString(10))."',
   secret='".$secret."'
   WHERE id='".$account->id."'";
  // execute query
  $GLOBALS['db']->execute($query);
  $alert="?alert=passwordRetrived&alert_class=alert-success";
  // sendmail
  $message="Ciao ".$account->name.",\n";
  $message.=" abbiamo ricevuto la tua richiesta di ripristino della password.\n\n";
  $message.="Per procedere con la scelta di una nuova password usa il seguente indirizzo:\n\n";
  $message.="http://".$_SERVER['SERVER_NAME'].$GLOBALS['dir'];
  $message.="accounts/password_reset.php?account=".$account->account."&key=".$secret."\n\n";
  $message.="Ricorda che questo codice è utilizzabile solamente per questa specifica sessione.";
  api_sendmail($p_account,$message,"Richiesta di ripristino della password");
 }
 // redirect
 header("location: index.php".$alert);
}

/* -[ Password Reset ]------------------------------------------------------- */
function password_reset(){
 // acquire variables
 $p_secret=$_POST['secret'];
 $p_account=$_POST['account'];
 $p_password=$_POST['password'];
 $p_confirm=$_POST['confirm'];
 // check account
 if($p_secret==NULL||$p_account==NULL){die("FATAL ERROR /!\\");}
 if($p_password<>$p_confirm){die("FATAL ERROR /!\\");}
 $account=$GLOBALS['db']->queryUniqueObject("SELECT * FROM accounts_accounts WHERE account='".$p_account."' AND secret='".$p_secret."'");
 if(!$account->id){die("FATAL ERROR /!\\");}
 // build query
 if($account->id>0){
  $query="UPDATE accounts_accounts SET
   password='".md5($p_password)."',
   secret=NULL
   WHERE id='".$account->id."'";
  // execute query
  $GLOBALS['db']->execute($query);
  $alert="?alert=passwordResetted&alert_class=alert-success";
  // sendmail
  $message="Ciao ".$account->name.",\n";
  $message.=" il ripristino della tua password è avvenuto correttamente.";
  api_sendmail($p_account,$message,"Notifica di ripristino della password");
 }
 // redirect
 header("location: index.php".$alert);
}


/* -[ Group Save ]----------------------------------------------------------- */
function group_save(){
 $g_id=$_GET['id'];
 if(!isset($g_id)){$g_id=0;}
 // acquire variables
 $p_idGroup=addslashes($_POST['idGroup']);
 $p_name=addslashes($_POST['name']);
 $p_description=addslashes($_POST['description']);
 // build query
 if($g_id>0){
  if(!api_checkPermission("accounts","groups_edit")){api_die("accessDenied");}
  $query="UPDATE accounts_groups SET
   idGroup='".$p_idGroup."',
   name='".$p_name."',
   description='".$p_description."'
   WHERE id='".$g_id."'";
  $alert="?alert=groupUpdated&alert_class=alert-success&alert_parameters=".$p_name;
 }else{
  if(!api_checkPermission("accounts","groups_add")){api_die("accessDenied");}
  $query="INSERT INTO accounts_groups
   (idGroup,name,description) VALUES
   ('".$p_idGroup."','".$p_name."','".$p_description."')";
  $alert="?alert=groupCreated&alert_class=alert-success&alert_parameters=".$p_name;
 }
 // execute query
 $GLOBALS['db']->execute($query);
 // redirect
 header("location: groups_list.php".$alert);
}

/* -[ Group Delete ]--------------------------------------------------------- */
function group_delete(){
 if(!api_checkPermission("accounts","groups_delete")){api_die("accessDenied");}
 $g_id=$_GET['id'];
 if(!isset($g_id)){$g_id=0;}
 $group=$GLOBALS['db']->queryUniqueObject("SELECT * FROM accounts_groups WHERE id='".$g_id."'");
 if($group->id>0){
  // delete group
  $GLOBALS['db']->execute("DELETE FROM accounts_groups WHERE id='".$group->id."'");
  // delete group links
  $GLOBALS['db']->execute("DELETE FROM accounts_groups_join_accounts WHERE idGroup='".$group->id."'");
 }
 // redirect
 $alert="?alert=groupDeleted&alert_class=alert&alert_parameters=".$group->name;
 header("location: groups_list.php".$alert);
}


/* -[ Company Save ]--------------------------------------------------------- */
function company_save(){
 $g_id=$_GET['id'];
 if(!isset($g_id)){$g_id=0;}
 // acquire variables
 $p_company=addslashes($_POST['company']);
 $p_division=addslashes($_POST['division']);
 $p_name=addslashes($_POST['name']);
 // build query
 if($g_id>0){
  if(!api_checkPermission("accounts","companies_edit")){api_die("accessDenied");}
  $query="UPDATE accounts_companies SET
   company='".$p_company."',
   division='".$p_division."',
   name='".$p_name."'
   WHERE id='".$g_id."'";
  $alert="?alert=companyUpdated&alert_class=alert-success&alert_parameters=".$p_name;
 }else{
  if(!api_checkPermission("accounts","companies_add")){api_die("accessDenied");}
  $query="INSERT INTO accounts_companies
   (company,division,name) VALUES
   ('".$p_company."','".$p_division."','".$p_name."')";
  $alert="?alert=companyCreated&alert_class=alert-success&alert_parameters=".$p_name;
 }
 // execute query
 $GLOBALS['db']->execute($query);
 // redirect
 header("location: companies_list.php".$alert);
}

/* -[ Company Delete ]--------------------------------------------------------- */
function company_delete(){
 if(!api_checkPermission("accounts","companies_delete")){api_die("accessDenied");}
 $g_id=$_GET['id'];
 if(!isset($g_id)){$g_id=0;}
 $company=$GLOBALS['db']->queryUniqueObject("SELECT * FROM accounts_companies WHERE id='".$g_id."'");
 if($company->id>0){
  die("Function disabled for security reason");
  // count if accounts_divisions==0
  // count if accounts_accounts==0
  // delete company
  //$GLOBALS['db']->execute("DELETE FROM accounts_companies WHERE id='".$company->id."'");
 }
 // redirect
 $alert="?alert=companyDeleted&alert_class=alert&alert_parameters=".$company->name;
 header("location: companies_list.php".$alert);
}


/* -[ LDAP Account Create ]-------------------------------------------------- */
function ldap_account_create(){
 // acquire variables
 $p_ldapUsername=addslashes(strtolower($_POST['ldapUsername']));
 //$p_ldapPassword=addslashes($_POST['ldapPassword']);
 $p_account=addslashes(strtolower($_POST['account']));
 $p_firstname=addslashes($_POST['firstname']);
 $p_lastname=addslashes($_POST['lastname']);
 $p_language=$_POST['language'];
 $p_idCompany=$_POST['idCompany'];
 // set name
 $name=ucfirst(strtolower($p_lastname))." ".ucfirst(strtolower($p_firstname));
 // try ldap authentication
 //include('../config.inc.php');
 //include('../core/ldap.inc.php');
 //if(ldap_authenticate($ldap_host,$ldap_dn,$ldap_domain,$p_ldapUsername,$p_ldapPassword,$ldap_group)){
 // check if not exist
 if(!$GLOBALS['db']->countOf("accounts_accounts","account='".$p_account."'") &&
    !$GLOBALS['db']->countOf("accounts_accounts","ldapUsername='".$p_ldapUsername."'")){
  // build query
  $query="INSERT INTO accounts_accounts
   (account,password,name,typology,language,idCompany,ldapUsername) VALUES
   ('".$p_account."','".md5(api_randomString(10))."','".$name."','2','".$p_language."','".$p_idCompany."','".$p_ldapUsername."')";
  // execute query
  $GLOBALS['db']->execute($query);
  $idAccount=$GLOBALS['db']->lastInsertedId();
  if($idAccount>0){
   // log
   $log="Name: ".$name."\n";
   $log.="Account: ".$p_account."\n";
   $log.="Company: ".api_companyName($p_idCompany)."\n";
   $log.="LDAP: ".$p_ldapUsername;
   api_log(1,"accounts","LDAP ACCOUNT CREATED\n".$log);
   // sendmail
   $message="Salve ".$name.",\n";
   $message.=" è stato attivato un account su Coordinator a tuo nome.\n\n";
   $message.="Per eseguire l'accesso puoi usare il seguente indirizzo:\n\n";
   $message.="http://".$_SERVER['SERVER_NAME'].$GLOBALS['dir']."accounts/login.php\n\n";
   $message.="Inserendo il tuo account e la tua password di sistema.";
   api_sendmail($p_account,$message,"Attivazione account Coordinator personale");
   // notification
   $notification_subject="Benvenuto ".$name." su Coordinator";
   $notification_message="Benvenuto ".$name.", il tuo account è stato creato.\n";
   $notification_message.="A breve un amministratore provvederà ad assegnarti ai tuoi gruppi di competenza.";
   api_notification_send($idAccount,1,"accounts",$notification_subject,$notification_message,NULL,0);
   // notification to administrators
   $notification_subject="Nuovo account attivato - ".$name;
   $notification_message=$name." ha creato l'account ".$p_account." eseguendo l'accesso tramite l'utente ".$p_ldapUsername.".\n";
   $notification_message.="&Egrave; necessario assegnare i gruppi al suo account.";
   $notification_link="accounts/accounts_edit.php?id=".$idAccount;
   api_notification_administrators(2,"accounts",$notification_subject,$notification_message,$notification_link,0);
   $alert="?alert=ldapCreated&alert_class=alert-success";
   exit(header("location: login.php".$alert));
  }
 }
 $alert="?alert=ldapCreatedError&alert_class=alert-error";
 exit(header("location: login.php".$alert));
}
