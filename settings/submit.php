<?php
/* ------------------------------------------------------------------------- *\
|* -[ Settings - Submit ]--------------------------------------------------- *|
\* ------------------------------------------------------------------------- */
include('../core/api.inc.php'); // Include the core API function
$act=$_GET['act'];
switch($act){
 // settings
 case "settings_save":settings_save();break;
 // validations
 case "validations_toggle":validations_toggle();break;
 // permissions
 case "permissions_add":permissions_add();break;
 case "permissions_del":permissions_del();break;
 case "permissions_reset":permissions_reset();break;
 // default
 default:header("location: index.php");
}


/* -[ Settings Save ]-------------------------------------------------------- */
function settings_save(){
 if(!api_checkPermission("settings","settings_edit")){api_die();}
 // update settings
 if($_POST['owner']<>NULL){$GLOBALS['db']->execute("UPDATE settings_settings SET value='".addslashes($_POST['owner'])."' WHERE code='owner'");}
 if($_POST['owner_url']<>NULL){$GLOBALS['db']->execute("UPDATE settings_settings SET value='".addslashes($_POST['owner_url'])."' WHERE code='owner_url'");}
 if($_POST['owner_mail']<>NULL){$GLOBALS['db']->execute("UPDATE settings_settings SET value='".addslashes($_POST['owner_mail'])."' WHERE code='owner_mail'");}
 if($_POST['owner_mail_from']<>NULL){$GLOBALS['db']->execute("UPDATE settings_settings SET value='".addslashes($_POST['owner_mail_from'])."' WHERE code='owner_mail_from'");}
 if($_POST['title']<>NULL){$GLOBALS['db']->execute("UPDATE settings_settings SET value='".addslashes($_POST['title'])."' WHERE code='title'");}
 if(isset($_POST['google_analytics'])){$GLOBALS['db']->execute("UPDATE settings_settings SET value='".addslashes($_POST['google_analytics'])."' WHERE code='google_analytics'");}
 if(isset($_POST['piwik_analytics'])){$GLOBALS['db']->execute("UPDATE settings_settings SET value='".addslashes($_POST['piwik_analytics'])."' WHERE code='piwik_analytics'");}
 if($_POST['cron_token']<>NULL){$GLOBALS['db']->execute("UPDATE settings_settings SET value='".addslashes($_POST['cron_token'])."' WHERE code='cron_token'");}
 if($_POST['maintenance']=="on"){$GLOBALS['db']->execute("UPDATE settings_settings SET value='1' WHERE code='maintenance'");}else{$GLOBALS['db']->execute("UPDATE settings_settings SET value='0' WHERE code='maintenance'");}
 if($_POST['maintenance_description']<>NULL){$GLOBALS['db']->execute("UPDATE settings_settings SET value='".addslashes($_POST['maintenance_description'])."' WHERE code='maintenance_description'");}
 // redirect
 $alert="?alert=settingsSaved&alert_class=alert-success";
 header("location: settings_edit.php".$alert);
}


/* -[ Validations Toggle ]--------------------------------------------------- */
function validations_toggle(){
 if(!api_checkPermission("settings","validations_edit")){api_die("accessDenied");}
 // acquire variables
 $g_module=$_GET['module'];
 $g_idValidation=$_GET['idValidation'];
 $g_idGroup=$_GET['idGroup'];
 // check current status
 $check=$GLOBALS['db']->queryUniqueValue("SELECT idValidation FROM accounts_validations_links WHERE idValidation='".$g_idValidation."' AND idGroup='".$g_idGroup."'");
 // check lock status
 $locked=$GLOBALS['db']->queryUniqueValue("SELECT locked FROM accounts_validations WHERE id='".$g_idValidation."'");
 if($_SESSION['account']->id<>1 && $locked){api_die("accessDenied");}
 // build query
 if($check==$g_idValidation){
  // revoke permission
  $GLOBALS['db']->execute($query="DELETE FROM accounts_validations_links WHERE idValidation='".$g_idValidation."' AND idGroup='".$g_idGroup."'");
 }else{
  // enable permission
  $GLOBALS['db']->execute("INSERT INTO accounts_validations_links (idValidation,idGroup) VALUES ('".$g_idValidation."','".$g_idGroup."')");
 }
 // redirect
 header("location: validations_edit.php?module=".$g_module);
}


/* -[ Permissions Add Group and Grouprole ]---------------------------------- */
function permissions_add_group_grouprole($idPermission,$idGroup,$idGrouprole){
 if(!api_checkPermission("settings","permissions_edit")){api_die("accessDenied");}
 // check lock status
 $locked=$GLOBALS['db']->queryUniqueValue("SELECT locked FROM settings_permissions WHERE id='".$idPermission."'");
 if($_SESSION['account']->id<>1 && $locked){api_die("accessDenied");}
 // delete previous associations
 if($idGroup==0){
  $GLOBALS['db']->execute($query="DELETE FROM settings_permissions_join_accounts_groups WHERE idPermission='".$idPermission."'");
 }else{
  $GLOBALS['db']->execute($query="DELETE FROM settings_permissions_join_accounts_groups WHERE idPermission='".$idPermission."' AND (idGroup='0' OR idGroup='".$idGroup."')");
 }
 // add group permission
 $GLOBALS['db']->execute("INSERT INTO settings_permissions_join_accounts_groups (idPermission,idGroup,idGrouprole) VALUES ('".$idPermission."','".$idGroup."','".$idGrouprole."')");  
}

 
/* -[ Permissions Add ]------------------------------------------------------ */
function permissions_add(){
 if(!api_checkPermission("settings","permissions_edit")){api_die("accessDenied");}
 // acquire variables
 $p_module=$_POST['module'];
 $p_idPermission=$_POST['idPermission'];
 $p_idGroup=$_POST['idGroup'];
 $p_idGrouprole=$_POST['idGrouprole'];
 if($p_module<>"" && $p_idGrouprole>0){
  if($p_idPermission>0){
   permissions_add_group_grouprole($p_idPermission,$p_idGroup,$p_idGrouprole);
  }elseif($p_idPermission==-1){
   $permissions=$GLOBALS['db']->query("SELECT * FROM settings_permissions WHERE module='".$p_module."' ORDER BY action ASC");
   while($permission=$GLOBALS['db']->fetchNextObject($permissions)){
    if(!$permission->locked || $_SESSION['account']->id==1){
     permissions_add_group_grouprole($permission->id,$p_idGroup,$p_idGrouprole);
    }
   }
  }
 }
 // redirect
 header("location: permissions_edit.php?module=".$p_module);
}

/* -[ Permissions Delete ]--------------------------------------------------- */
function permissions_del(){
 if(!api_checkPermission("settings","permissions_edit")){api_die("accessDenied");}
 // acquire variables
 $g_module=$_GET['module'];
 $g_idPermission=$_GET['idPermission'];
 $g_idGroup=$_GET['idGroup'];
 if($g_module<>"" && $g_idPermission>0){
  // check lock status
  $locked=$GLOBALS['db']->queryUniqueValue("SELECT locked FROM settings_permissions WHERE id='".$g_idPermission."'");
  if($_SESSION['account']->id<>1 && $locked){api_die("accessDenied");}
  // delete permission
  $GLOBALS['db']->execute($query="DELETE FROM settings_permissions_join_accounts_groups WHERE idPermission='".$g_idPermission."' AND idGroup='".$g_idGroup."'");
 }
 // redirect
 header("location: permissions_edit.php?module=".$g_module);
}

/* -[ Permissions Reset ]---------------------------------------------------- */
function permissions_reset(){
 if(!api_checkPermission("settings","permissions_edit")){api_die("accessDenied");}
 // acquire variables
 $g_module=$_GET['module'];
 if($g_module<>""){
  // get unlocked permissions
  $permissions=$GLOBALS['db']->query("SELECT * FROM settings_permissions WHERE module='".$g_module."' AND locked='0'");
  while($permission=$GLOBALS['db']->fetchNextObject($permissions)){
   // delete permission
   $GLOBALS['db']->execute($query="DELETE FROM settings_permissions_join_accounts_groups WHERE idPermission='".$permission->id."'");
  }
 }
 // redirect
 header("location: permissions_edit.php?module=".$g_module);
}

?>