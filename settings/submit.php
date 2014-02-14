<?php
/* ------------------------------------------------------------------------- *\
|* -[ Settings - Submit ]--------------------------------------------------- *|
\* ------------------------------------------------------------------------- */
include('../core/api.inc.php');
$act=$_GET['act'];
switch($act){
 // settings
 case "settings_save":settings_save();break;
 // validations
 case "validations_toggle":validations_toggle();break;
 // modules
 case "module_setup":module_setup();break;
 case "module_update":module_update();break;
 // permissions
 case "permissions_add":permissions_add();break;
 case "permissions_del":permissions_del();break;
 case "permissions_reset":permissions_reset();break;
 // menus
 case "menu_save":menu_save();break;
 case "menu_move_up":menu_move("up");break;
 case "menu_move_down":menu_move("down");break;
 case "menu_delete":menu_delete();break;
 // default
 default:header("location: index.php");
}


/* -[ Settings Save ]-------------------------------------------------------- */
function settings_save(){
 if(!api_checkPermission("settings","settings_edit")){api_die();}
 // owner
 if($_POST['owner']<>NULL){$GLOBALS['db']->execute("UPDATE settings_settings SET value='".addslashes($_POST['owner'])."' WHERE code='owner'");}
 if($_POST['owner_url']<>NULL){$GLOBALS['db']->execute("UPDATE settings_settings SET value='".addslashes($_POST['owner_url'])."' WHERE code='owner_url'");}
 if($_POST['owner_mail']<>NULL){$GLOBALS['db']->execute("UPDATE settings_settings SET value='".addslashes($_POST['owner_mail'])."' WHERE code='owner_mail'");}
 if($_POST['owner_mail_from']<>NULL){$GLOBALS['db']->execute("UPDATE settings_settings SET value='".addslashes($_POST['owner_mail_from'])."' WHERE code='owner_mail_from'");}
 // title and logo
 if($_POST['title']<>NULL){$GLOBALS['db']->execute("UPDATE settings_settings SET value='".addslashes($_POST['title'])."' WHERE code='title'");}
 if($_POST['show_logo']=="on"){$GLOBALS['db']->execute("UPDATE settings_settings SET value='1' WHERE code='show_logo'");}else{$GLOBALS['db']->execute("UPDATE settings_settings SET value='0' WHERE code='show_logo'");}
 // maintenance
 if($_POST['maintenance']=="on"){$GLOBALS['db']->execute("UPDATE settings_settings SET value='1' WHERE code='maintenance'");}else{$GLOBALS['db']->execute("UPDATE settings_settings SET value='0' WHERE code='maintenance'");}
 if($_POST['maintenance_description']<>NULL){$GLOBALS['db']->execute("UPDATE settings_settings SET value='".addslashes($_POST['maintenance_description'])."' WHERE code='maintenance_description'");}
 // tokens
 if($_POST['cron_token']<>NULL){$GLOBALS['db']->execute("UPDATE settings_settings SET value='".addslashes($_POST['cron_token'])."' WHERE code='cron_token'");}
 if(isset($_POST['google_analytics'])){$GLOBALS['db']->execute("UPDATE settings_settings SET value='".addslashes($_POST['google_analytics'])."' WHERE code='google_analytics'");}
 if(isset($_POST['piwik_analytics'])){$GLOBALS['db']->execute("UPDATE settings_settings SET value='".addslashes($_POST['piwik_analytics'])."' WHERE code='piwik_analytics'");}
 // ldap
 if($_POST['ldap']=="on"){$GLOBALS['db']->execute("UPDATE settings_settings SET value='1' WHERE code='ldap'");}else{$GLOBALS['db']->execute("UPDATE settings_settings SET value='0' WHERE code='ldap'");}
 if(isset($_POST['ldap_host'])){$GLOBALS['db']->execute("UPDATE settings_settings SET value='".addslashes($_POST['ldap_host'])."' WHERE code='ldap_host'");}
 if(isset($_POST['ldap_dn'])){$GLOBALS['db']->execute("UPDATE settings_settings SET value='".addslashes($_POST['ldap_dn'])."' WHERE code='ldap_dn'");}
 if(isset($_POST['ldap_domain'])){$GLOBALS['db']->execute("UPDATE settings_settings SET value='".addslashes($_POST['ldap_domain'])."' WHERE code='ldap_domain'");}
 if(isset($_POST['ldap_group'])){$GLOBALS['db']->execute("UPDATE settings_settings SET value='".addslashes($_POST['ldap_group'])."' WHERE code='ldap_group'");}
 // redirect
 $alert="?alert=settingSaved&alert_class=alert-success";
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


/* -[ Menu Save ]------------------------------------------------------------ */
function menu_save(){
 if(!api_checkPermission("settings","menu_edit")){api_die("accessDenied");}
 // acquire variables
 $g_id=$_GET['id'];
 if($g_id>0){$menu=$GLOBALS['db']->queryUniqueObject("SELECT * FROM settings_menus WHERE id='".$g_id."'");}
 $p_idMenu=$_POST['idMenu'];
 $p_menu=addslashes($_POST['menu']);
 $p_module=addslashes($_POST['module']);
 $p_url=addslashes($_POST['url']);
 // set position
 if($menu->id>0 && $menu->idMenu==$p_idMenu){
  // no change
  $position=$menu->position;
 }else{
  // set maximum position
  $position=$GLOBALS['db']->countOf("settings_menus","idMenu='".$p_idMenu."'");
  $position++;
  // if changed parent menu move back submenu located after
  if($p_idMenu<>$menu->idMenu){
   echo $GLOBALS['db']->execute("UPDATE settings_menus SET position=position-1 WHERE position>'".$menu->position."' AND idMenu='".$menu->idMenu."'");
  }
 }
 // build query
 if($menu->id>0){
  $query="UPDATE settings_menus SET
   idMenu='".$p_idMenu."',
   menu='".$p_menu."',
   module='".$p_module."',
   url='".$p_url."',
   position='".$position."'
   WHERE id='".$g_id."'";
  // execute query
  $GLOBALS['db']->execute($query);
  // redirect
  $alert="&alert=menuUpdated&alert_class=alert-success";
  header("location: menus_edit.php?idMenu=".$p_idMenu.$alert);
 }else{
  $query="INSERT INTO settings_menus
   (idMenu,menu,module,url,position) VALUES
   ('".$p_idMenu."','".$p_menu."','".$p_module."','".$p_url."','".$position."')";
  // execute query
  $GLOBALS['db']->execute($query);
  // set id to last inserted id
  $g_id=$GLOBALS['db']->lastInsertedId();
  // redirect
  $alert="&alert=menuCreated&alert_class=alert-success";
  header("location: menus_edit.php?idMenu=".$p_idMenu.$alert);
 }
}

/* -[ Menu Move ]------------------------------------------------------------ */
function menu_move($to){
 if(!api_checkPermission("settings","menu_edit")){api_die("accessDenied");}
 // acquire variables
 $g_id=$_GET['id'];
 if(!$g_id){$g_id=0;}
 $g_idMenu=$_GET['idMenu'];
 if(!$g_idMenu){$g_idMenu=0;}
 if($g_id>0 && $g_idMenu>0){
  $moved=FALSE;
  // get current position
  $position=$GLOBALS['db']->queryUniqueValue("SELECT position FROM settings_menus WHERE id='".$g_id."'");
  // move field
  switch($to){
   case "up":
    if($position>1){
     echo $GLOBALS['db']->execute("UPDATE settings_menus SET position=".$position." WHERE position='".($position-1)."' AND idMenu='".$g_idMenu."'");
     echo $GLOBALS['db']->execute("UPDATE settings_menus SET position=".($position-1)." WHERE id='".$g_id."'");
     $moved=TRUE;
    }
    break;
   case "down":
    $max_position=$GLOBALS['db']->countOf("settings_menus","idMenu='".$g_idMenu."'");
    if($position<$max_position){
     echo $GLOBALS['db']->execute("UPDATE settings_menus SET position=".$position." WHERE position='".($position+1)."' AND idMenu='".$g_idMenu."'");
     echo $GLOBALS['db']->execute("UPDATE settings_menus SET position=".($position+1)." WHERE id='".$g_id."'");
     $moved=TRUE;
    }
    break;
  }
  // alert and redirect
  if($moved){$alert="&alert=menuMoved&alert_class=alert-success";}
   else{$alert="&alert=settingError&alert_class=alert-error";}
  exit(header("location: menus_edit.php?idMenu=".$g_idMenu.$alert));
 }else{
  // redirect
  $alert="&alert=settingError&alert_class=alert-error";
  exit(header("location: menus_edit.php?idMenu=".$g_idMenu.$alert));
 }
}


/* -[ Menu Delete ]---------------------------------------------------------- */
function menu_delete(){
 if(!api_checkPermission("settings","menu_edit")){api_die("accessDenied");}
 // acquire variables
 $g_id=$_GET['id'];
 if(!$g_id){$g_id=0;}
 $g_idMenu=$_GET['idMenu'];
 if(!$g_idMenu){$g_idMenu=0;}
 if($g_id>0 && $g_idMenu>0){
  // get menu position
  $position=$GLOBALS['db']->queryUniqueValue("SELECT position FROM settings_menus WHERE id='".$g_id."'");
  // delete action
  echo $GLOBALS['db']->execute("DELETE FROM settings_menus WHERE id='".$g_id."'");
  // moves back fields located after
  echo $GLOBALS['db']->execute("UPDATE settings_menus SET position=position-1 WHERE position>'".$position."' AND idMenu='".$g_idMenu."'");
  // redirect
  $alert="&alert=menuDeleted&alert_class=alert-success";
  exit(header("location: menus_edit.php?idMenu=".$g_idMenu.$alert));
 }else{
  // redirect
  $alert="&alert=settingError&alert_class=alert-error";
  exit(header("location: menus_edit.php?idMenu=".$g_idMenu.$alert));
 }
}


/* -[ Module Setup ]--------------------------------------------------------- */
function module_setup(){
 if(!api_checkPermission("settings","modules_edit")){api_die("accessDenied");}
 // acquire variables
 $g_module=$_GET['module'];
 $module_path="../".$g_module."/";
 if(file_exists($module_path."module.inc.php")){
  // include module informations
  include($module_path."module.inc.php");
  // insert module into database
  $query="INSERT INTO settings_modules (module,version,title,description) VALUES
   ('".$module_name."','1.0.0','".$module_title."','".$module_description."')";
  $GLOBALS['db']->execute($query);
  // restore mysql dump
  if(file_exists($module_path."queries/setup.sql")){api_restoreMysqlDump($module_path."queries/setup.sql");}
  // redirect
  $alert="?alert=moduleSetup&alert_class=alert-success";
  exit(header("location: modules_edit.php".$alert));
 }else{
  // redirect
  $alert="?alert=settingError&alert_class=alert-error";
  exit(header("location: modules_edit.php".$alert));
 }
}


/* -[ Module Update ]-------------------------------------------------------- */
function module_update(){
 if(!api_checkPermission("settings","modules_edit")){api_die("accessDenied");}
 // acquire variables
 $g_module=$_GET['module'];
 $module_path="../".$g_module."/";
 if(file_exists($module_path."module.inc.php")){
  $infinite_loop=0;
  // include module informations
  include($module_path."module.inc.php");
  // get current installed version
  $current_version=$GLOBALS['db']->queryUniqueValue("SELECT version FROM settings_modules WHERE module='".$module_name."'");
  // check for update
  while($current_version<>$module_version){
   // execute update
   api_restoreMysqlDump($module_path."queries/update_".$current_version.".sql");
   $current_version=$GLOBALS['db']->queryUniqueValue("SELECT version FROM settings_modules WHERE module='".$module_name."'");
   // check for infinite loop
   $infinite_loop++;
   if($infinite_loop==9999999){
    $alert="?alert=moduleUpdateSqlError&alert_class=alert-error";
    exit(header("location: modules_edit.php".$alert));
   }
  }
  // redirect
  $alert="?alert=moduleUpdated&alert_class=alert-success";
  exit(header("location: modules_edit.php".$alert));
 }else{
  // redirect
  $alert="?alert=settingError&alert_class=alert-error";
  exit(header("location: modules_edit.php".$alert));
 }
}

?>