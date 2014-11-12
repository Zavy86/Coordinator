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
 //case "validations_toggle":validations_toggle();break;
 // modules
 case "module_setup":module_setup();break;
 case "module_update":module_update();break;
 case "module_uninstall":module_uninstall();break;
 case "module_remove":module_remove();break;
 case "module_git_pull":module_git_pull();break;
 case "module_git_clone":module_git_clone();break;
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
 // mail
 if($_POST['owner_mail']<>NULL){$GLOBALS['db']->execute("UPDATE settings_settings SET value='".addslashes($_POST['owner_mail'])."' WHERE code='owner_mail'");}
 if($_POST['owner_mail_from']<>NULL){$GLOBALS['db']->execute("UPDATE settings_settings SET value='".addslashes($_POST['owner_mail_from'])."' WHERE code='owner_mail_from'");}
 if($_POST['sendmail_asynchronous']){$GLOBALS['db']->execute("UPDATE settings_settings SET value='1' WHERE code='sendmail_asynchronous'");}else{$GLOBALS['db']->execute("UPDATE settings_settings SET value='0' WHERE code='sendmail_asynchronous'");}
 // title and logo
 if($_POST['title']<>NULL){$GLOBALS['db']->execute("UPDATE settings_settings SET value='".addslashes($_POST['title'])."' WHERE code='title'");}
 if($_POST['show_logo']){$GLOBALS['db']->execute("UPDATE settings_settings SET value='1' WHERE code='show_logo'");}else{$GLOBALS['db']->execute("UPDATE settings_settings SET value='0' WHERE code='show_logo'");}
 // maintenance
 if($_POST['maintenance']){$GLOBALS['db']->execute("UPDATE settings_settings SET value='1' WHERE code='maintenance'");}else{$GLOBALS['db']->execute("UPDATE settings_settings SET value='0' WHERE code='maintenance'");}
 if($_POST['maintenance_description']<>NULL){$GLOBALS['db']->execute("UPDATE settings_settings SET value='".addslashes($_POST['maintenance_description'])."' WHERE code='maintenance_description'");}
 // tokens
 if($_POST['cron_token']<>NULL){$GLOBALS['db']->execute("UPDATE settings_settings SET value='".addslashes($_POST['cron_token'])."' WHERE code='cron_token'");}
 if(isset($_POST['google_analytics'])){$GLOBALS['db']->execute("UPDATE settings_settings SET value='".addslashes($_POST['google_analytics'])."' WHERE code='google_analytics'");}
 if(isset($_POST['piwik_analytics'])){$GLOBALS['db']->execute("UPDATE settings_settings SET value='".addslashes($_POST['piwik_analytics'])."' WHERE code='piwik_analytics'");}
 // ldap
 if($_POST['ldap']){$GLOBALS['db']->execute("UPDATE settings_settings SET value='1' WHERE code='ldap'");}else{$GLOBALS['db']->execute("UPDATE settings_settings SET value='0' WHERE code='ldap'");}
 if(isset($_POST['ldap_host'])){$GLOBALS['db']->execute("UPDATE settings_settings SET value='".addslashes($_POST['ldap_host'])."' WHERE code='ldap_host'");}
 if(isset($_POST['ldap_dn'])){$GLOBALS['db']->execute("UPDATE settings_settings SET value='".addslashes($_POST['ldap_dn'])."' WHERE code='ldap_dn'");}
 if(isset($_POST['ldap_domain'])){$GLOBALS['db']->execute("UPDATE settings_settings SET value='".addslashes($_POST['ldap_domain'])."' WHERE code='ldap_domain'");}
 if(isset($_POST['ldap_userfield'])){$GLOBALS['db']->execute("UPDATE settings_settings SET value='".addslashes($_POST['ldap_userfield'])."' WHERE code='ldap_userfield'");}
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
  if($module_create_menu){
   // get maximum position for main menu
   $position=$GLOBALS['db']->countOf("settings_menus","idMenu='1'");
   $position++;
   // insert module into main menu
   $query="INSERT INTO settings_menus
    (idMenu,menu,module,url,position) VALUES
    ('1','".$module_title."','".$module_name."','','".$position."')";
   // execute query
   $GLOBALS['db']->execute($query);
  }
  // execute setup queries by mysql dump
  if(file_exists($module_path."queries/setup.sql")){api_restoreMysqlDump($module_path."queries/setup.sql");}
  // log event
  $log=api_log(API_LOG_NOTICE,"settings","moduleInstalled",
   "{logs_settings_moduleInstalled|".$module_name."}",
   NULL,"settings/modules_edit.php");
  // redirect
  $alert="?alert=moduleSetup&alert_class=alert-success&idLog=".$log->id;
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
  // include module informations
  include($module_path."module.inc.php");
  // get initial version
  $initial_version=$GLOBALS['db']->queryUniqueValue("SELECT version FROM settings_modules WHERE module='".$module_name."'");
  $current_version=$initial_version;
  // explode versions
  $module_version_array=explode(".",$module_version);
  $current_version_array=explode(".",$current_version);
  // cycle untile version are syncronized
  while($current_version<>$module_version){
   // check major release
   if($current_version_array[0]<$module_version_array[0]){
    // execute dump from current minor release if exist for a maximum of 1000 minor releases
    if($current_version_array[1]<1000){
     api_restoreMysqlDump($module_path."queries/update_".$current_version_array[0].".".$current_version_array[1].".sql");
     // increment minor release
     $current_version_array[1]++;
    }else{
     // increment major release
     $current_version_array[0]++;
     // reset minor release
     $current_version_array[1]=0;
    }
   }elseif($current_version_array[0]==$module_version_array[0]){
    // if major release are syncronized check the minor release
    if($current_version_array[1]<$module_version_array[1]){
     // execute dump from current minor release
     api_restoreMysqlDump($module_path."queries/update_".$current_version_array[0].".".$current_version_array[1].".sql");
     // increment minor release
     $current_version_array[1]++;
    }
   }
   // if major and minor release are syncronized, syncronize the hotfix
   if($current_version_array[0]==$module_version_array[0] &&
      $current_version_array[1]==$module_version_array[1] &&
      $current_version_array[2]<>$module_version_array[2]){
    $current_version_array[2]=$module_version_array[2];
   }
   $current_version=$current_version_array[0].".".$current_version_array[1].".".$current_version_array[2];
  }
  // update version on database
  $GLOBALS['db']->execute("UPDATE `settings_modules` SET `version`='".$current_version."' WHERE `module`='".$module_name."';");
  // log event
  $log=api_log(API_LOG_NOTICE,"settings","moduleUpdated",
   "{logs_settings_moduleUpdated|".$module_name."|".$initial_version."|".$current_version."}",
   NULL,"settings/modules_edit.php");
  // redirect
  $alert="?alert=moduleUpdated&alert_class=alert-success&idLog=".$log->id;
  exit(header("location: modules_edit.php".$alert));
 }else{
  // redirect
  $alert="?alert=settingError&alert_class=alert-error";
  exit(header("location: modules_edit.php".$alert));
 }
}

/* -[ Module Uninstall ]--------------------------------------------------------- */
function module_uninstall(){
 if(!api_checkPermission("settings","modules_edit")){api_die("accessDenied");}
 // acquire variables
 $g_module=$_GET['module'];
 $module_path="../".$g_module."/";
 if(file_exists($module_path."module.inc.php")){
  // include module informations
  include($module_path."module.inc.php");
  if($module_create_menu){
   // delete module from logs triggers
   $GLOBALS['db']->execute("DELETE FROM logs_triggers WHERE module='".$module_name."'");
   // delete module from menus
   $GLOBALS['db']->execute("DELETE FROM settings_menus WHERE module='".$module_name."'");
  }
  // delete module from permissions
  $permissions=$GLOBALS['db']->query("SELECT * FROM settings_permissions WHERE module='".$module_name."'");
  while($permission=$GLOBALS['db']->fetchNextObject($permissions)){
   $GLOBALS['db']->execute("DELETE FROM settings_permissions_join_accounts_groups WHERE idPermission='".$permission->id."'");
  }
  $GLOBALS['db']->execute("DELETE FROM settings_permissions WHERE module='".$module_name."'");
  // delete module from dashboards
  $GLOBALS['db']->execute("DELETE FROM settings_dashboards WHERE module='".$module_name."'");
  // delete module from database
  $GLOBALS['db']->execute("DELETE FROM settings_modules WHERE module='".$module_name."'");
  // execute unistall queries by mysql dump
  if(file_exists($module_path."queries/uninstall.sql")){api_restoreMysqlDump($module_path."queries/uninstall.sql");}
  // log event
  $log=api_log(API_LOG_WARNING,"settings","moduleUninstalled",
   "{logs_settings_moduleUninstalled|".$module_name."}",
   NULL,"settings/modules_edit.php");
  // redirect
  $alert="?alert=moduleUninstalled&alert_class=alert-warning&idLog=".$log->id;
  exit(header("location: modules_edit.php".$alert));
 }else{
  // redirect
  $alert="?alert=settingError&alert_class=alert-error";
  exit(header("location: modules_edit.php".$alert));
 }
}

/* -[ Module Remove ]--------------------------------------------------------- */
function module_remove(){
 if(!api_checkPermission("settings","modules_edit")){api_die("accessDenied");}
 // acquire variables
 $g_module=$_GET['module'];
 $module_path="../".$g_module."/";
 if(file_exists($module_path."module.inc.php")){
  // include module informations
  include($module_path."module.inc.php");
  // delete module direcory
  api_rm_recursive(substr($module_path,0,-1),FALSE);
  // redirect
  $alert="?alert=moduleRemoved&alert_class=alert-warning";
  exit(header("location: modules_edit.php".$alert));
 }else{
  // redirect
  $alert="?alert=settingError&alert_class=alert-error";
  exit(header("location: modules_edit.php".$alert));
 }
}

/* -[ Module Git Pull ]------------------------------------------------------ */
function module_git_pull(){
 if(!api_checkPermission("settings","modules_edit")){api_die("accessDenied");}
 // definitions
 $modules_cloned=array();
 // check if coordinator is installed via git
 if(is_dir("../.git")){$modules_cloned[]="coordinator";}
 // check for modules cloned with git
 if($dh=opendir("../")){
  while(($entry=readdir($dh))!==false){
   if(is_dir("../".$entry) && $entry<>"." && $entry<>".."){
    if(is_dir("../".$entry."/.git")){$modules_cloned[]=$entry;}
   }
  }
 }
 // disabled for localhost and 127.0.0.1
 if($_SERVER['HTTP_HOST']<>"localhost" && $_SERVER['HTTP_HOST']<>"127.0.0.1"){
  $output.=exec('whoami')."@".exec('hostname').":".shell_exec("cd ".$GLOBALS['path'].$GLOBALS['dir']." ; pwd ; git stash clear ; git pull")."\n\n";
  foreach($modules_cloned as $module){
   $output.=exec('whoami')."@".exec('hostname').":".shell_exec("cd ".$GLOBALS['path'].$GLOBALS['dir'].$module." ; pwd ; git stash clear ; git pull")."\n\n";
  }
  // log event
  $log=api_log(API_LOG_NOTICE,"settings","moduleGitPull",
   "{logs_settings_moduleGitPull|".implode(", ",$modules_cloned)."|".$output."}",
   NULL,"settings/modules_edit.php");
  // alert
  $alert="?alert=gitpullSuccess&alert_class=alert-success&idLog=".$log->id;
 }else{
  // alert
  $alert="?alert=gitpullDisabled&alert_class=alert-error";
 }
 // redirect
 exit(header("location: modules_edit.php".$alert));
}

/* -[ Module Git Clone ]----------------------------------------------------- */
function module_git_clone(){
 if(!api_checkPermission("settings","modules_edit")){api_die("accessDenied");}
 // acquire variables
 $p_url=$_POST['url'];
 $p_dir=$_POST['dir'];
 $p_branch=$_POST['branch'];
 // execute shell commands
 $output.=exec('whoami')."@".exec('hostname').shell_exec("cd /var/www/".$GLOBALS['dir']." ; pwd ; git clone ".$p_url." ".$p_dir." ; cd ".$p_dir." ; pwd ; git checkout ".$p_branch);
 // log event
 $log=api_log(API_LOG_NOTICE,"settings","moduleGitClone",
  "{logs_settings_moduleGitClone|".$p_dir."|".$output."}",
  NULL,"settings/modules_edit.php");
 // redirect
 $alert="?alert=gitpullSuccess&alert_class=alert-success&idLog=".$log->id;
 exit(header("location: modules_edit.php".$alert));
}


/* -[ Permissions Add Group and Grouprole ]---------------------------------- */
function permissions_add_group_grouprole($idPermission,$idGroup,$idGrouprole){
 if(!api_checkPermission("settings","permissions_edit")){api_die("accessDenied");}
 // check lock status
 $locked=$GLOBALS['db']->queryUniqueValue("SELECT locked FROM settings_permissions WHERE id='".$idPermission."'");
 if($_SESSION['account']->id<>1 && $locked){api_die("accessDenied");}
 // delete previous associations
 if($idGroup==0){
  $GLOBALS['db']->execute($query="DELETE FROM settings_permissions_join_accounts_groups WHERE idPermission='".$idPermission."' AND (idGrouprole='".$idGrouprole."' OR idGroup='0')");
 }else{
  $GLOBALS['db']->execute($query="DELETE FROM settings_permissions_join_accounts_groups WHERE idPermission='".$idPermission."' AND idGrouprole='".$idGrouprole."' AND (idGroup='0' OR idGroup='".$idGroup."')");
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

?>