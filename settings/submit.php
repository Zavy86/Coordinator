<?php
/* ------------------------------------------------------------------------- *\
|* -[ Settings - Submit ]--------------------------------------------------- *|
\* ------------------------------------------------------------------------- */
include('../core/api.inc.php');
$act=$_GET['act'];
switch($act){
 // settings
 case "settings_save":settings_save();break; // -- to check
 // modules
 case "module_setup":module_setup();break; // -- to check
 case "module_update":module_update();break; // -- to check
 case "module_uninstall":module_uninstall();break; // -- to check
 case "module_remove":module_remove();break; // -- to check
 case "module_git_pull":module_git_pull();break; // -- to check
 case "module_git_clone":module_git_clone();break; // -- to check
 // permissions
 case "permission_group_add":permission_group_add();break;
 case "permission_group_remove":permission_group_remove();break;
 case "permission_group_reset":permission_group_reset();break;
 // menus
 case "menu_save":menu_save();break;  // -- to check
 case "menu_move_up":menu_move("up");break; // -- to check
 case "menu_move_down":menu_move("down");break; // -- to check
 case "menu_delete":menu_delete();break; // -- to check
 case "menu_permission_add":menu_permission_add();break; // -- to check
 case "menu_permission_delete":menu_permission_delete();break; // -- to check
 case "menu_language_save":menu_language_save();break;  // -- to check
 case "menu_language_delete":menu_language_delete();break;  // -- to check
 // default
 default:
  $alert="?alert=submitFunctionNotFound&alert_class=alert-warning&act=".$act;
  exit(header("location: index.php".$alert));
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
 // smtp
 if($_POST['smtp']){$GLOBALS['db']->execute("UPDATE settings_settings SET value='1' WHERE code='smtp'");}else{$GLOBALS['db']->execute("UPDATE settings_settings SET value='0' WHERE code='smtp'");}
 if(isset($_POST['smtp_host'])){$GLOBALS['db']->execute("UPDATE settings_settings SET value='".addslashes($_POST['smtp_host'])."' WHERE code='smtp_host'");}
 if(isset($_POST['smtp_username'])){$GLOBALS['db']->execute("UPDATE settings_settings SET value='".addslashes($_POST['smtp_username'])."' WHERE code='smtp_username'");}
 if(isset($_POST['smtp_password'])){$GLOBALS['db']->execute("UPDATE settings_settings SET value='".addslashes($_POST['smtp_password'])."' WHERE code='smtp_password'");}
 if(isset($_POST['smtp_secure'])){$GLOBALS['db']->execute("UPDATE settings_settings SET value='".addslashes($_POST['smtp_secure'])."' WHERE code='smtp_secure'");}
 // redirect
 $alert="?alert=settingSaved&alert_class=alert-success";
 header("location: settings_edit.php".$alert);
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
 // acquire variables
 $selected_modules=$_POST['table_rows'];
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
  foreach($modules_cloned as $module){
   if($module=="coordinator"){$module=NULL;}else{if(!in_array($module,$selected_modules)){continue;}}
   $output.=exec('whoami')."@".exec('hostname').":".shell_exec("cd ".$GLOBALS['path'].$GLOBALS['dir'].$module." ; pwd ; git stash ; git stash clear ; git pull")."\n\n";
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
 $output.=exec('whoami')."@".exec('hostname').shell_exec("cd ".$GLOBALS['path'].$GLOBALS['dir']." ; pwd ; git clone ".$p_url." ".$p_dir." ; cd ".$p_dir." ; pwd ; git checkout ".$p_branch);
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


/**
 * Permission Group Add
 */
function permission_group_add(){
 if(!api_checkPermission("settings","permissions_edit")){api_die("accessDenied");}
 // definitions
 $permissions_array=array();
 // get objects
 $company=api_accounts_company($_GET['idCompany']);
 // acquire variables
 $g_module=$_GET['module'];
 $g_idPermission=$_POST['idPermission'];
 $g_idGroup=$_POST['idGroup'];
 $g_level=$_POST['level'];
 // get permissions
 if($g_idPermission==0){
  $permissions=$GLOBALS['db']->query("SELECT * FROM settings_permissions WHERE module='".$g_module."'");
  while($permission=$GLOBALS['db']->fetchNextObject($permissions)){
   if($permission->locked&&api_accounts_account()->id>1){continue;}
   $permissions_array[]=$permission;
  }
 }else{
  $permissions_array[]=$GLOBALS['db']->queryUniqueObject("SELECT * FROM settings_permissions WHERE id='".$g_idPermission."'");
 }
 // get group
 if($g_idGroup<>0){$group=api_accounts_group($g_idGroup,FALSE);}else{$group=new stdClass();}
 // check objects
 if(!$company->id){echo api_text("companyNotFound");return FALSE;}
 if($g_idGroup<>0&&!$group->id){echo api_text("groupNotFound");return FALSE;}
 if(!count($permissions_array)){echo api_text("permissionNotFound");return FALSE;}
 // make query where, query fields and group 0
 if($g_idGroup==0){
  $query_where=" AND idCompany='".$company->id."'";
  $query_where.=" AND ISNULL(idGroup)";
  $f_company=$company->id;
  $f_group=NULL;
  $group->id=0;
  $group->name="ALL";
  $group->description="All company users";
 }else{
  $query_where=" AND ISNULL(idCompany)";
  $query_where.=" AND idGroup='".$group->id."'";
  $f_company=NULL;
  $f_group=$group->id;
 }
 // make action alert
 if(count($permissions_array)>1){$action_alert=$g_module."_all";}
 else{$action_alert=$permissions_array[0]->action;}
 // cycle permissions
 foreach($permissions_array as $permission){
  // remove previous associations
  $GLOBALS['db']->execute("DELETE FROM settings_permissions_join_accounts_groups WHERE idPermission='".$permission->id."'".$query_where);
  // build query
  $query="INSERT INTO settings_permissions_join_accounts_groups
   (idPermission,idCompany,idGroup,level) VALUES
   ('".$permission->id."','".$f_company."','".$f_group."','".$g_level."')";
  // execute query
  $GLOBALS['db']->execute($query);
  // log event
  $log=api_log(API_LOG_NOTICE,"settings","permissionAdded",
   "{logs_settings_permissionAdded|".$permission->id."|".$permission->module."|".$permission->action."|".$permission->description."|".$company->id."|".$company->name."|".$group->id."|".$group->name."|".$group->description."}",
   $permission->id,"settings/permissions_edit.php?module=".$permission->module."&idCompany=".$company->id."&action=".$permission->action);
 }
 // redirect
 $alert="&alert=permissionAdded&alert_class=alert-success&alert_parameters[]=".$action_alert."&alert_parameters[]=".$group->name."&idLog=".$log->id;
 exit(header("location: permissions_edit.php?module=".$permission->module."&idCompany=".$company->id.$alert));
}

/**
 * Permission Group Remove
 */
function permission_group_remove(){
 if(!api_checkPermission("settings","permissions_edit")){api_die("accessDenied");}
 // get objects
 $permission=$GLOBALS['db']->queryUniqueObject("SELECT * FROM settings_permissions WHERE id='".$_GET['idPermission']."'");
 $company=api_accounts_company($_GET['idCompany']);
 // acquire variables
 $g_idGroup=$_GET['idGroup'];
 if($g_idGroup<>0){$group=api_accounts_group($g_idGroup,FALSE);}else{$group=new stdClass();}
 // check objects
 if(!$permission->id){echo api_text("permissionNotFound");return FALSE;}
 if($permission->locked&&api_accounts_account()->id>1){echo api_text("permissionLocked");return FALSE;}
 if(!$company->id){echo api_text("companyNotFound");return FALSE;}
 if($g_idGroup<>0&&!$group->id){echo api_text("groupNotFound");return FALSE;}
 // make query where and group 0
 if($g_idGroup==0){
  $query_where=" AND idCompany='".$company->id."'";
  $query_where.=" AND ISNULL(idGroup)";
  $group->id=0;
  $group->name="ALL";
  $group->description="All company users";
 }else{
  $query_where=" AND ISNULL(idCompany)";
  $query_where.=" AND idGroup='".$group->id."'";
 }
 // build query
 $query="DELETE FROM settings_permissions_join_accounts_groups WHERE idPermission='".$permission->id."'".$query_where;
 // execute query
 $GLOBALS['db']->execute($query);
 // log event
 $log=api_log(API_LOG_WARNING,"settings","permissionRemoved",
  "{logs_settings_permissionRemoved|".$permission->id."|".$permission->module."|".$permission->action."|".$permission->description."|".$company->id."|".$company->name."|".$group->id."|".$group->name."|".$group->description."}",
  $permission->id,"settings/permissions_edit.php?module=".$permission->module."&idCompany=".$company->id."&action=".$permission->action);
 // redirect
 $alert="&alert=permissionRemoved&alert_class=alert-warning&alert_parameters[]=".$permission->action."&alert_parameters[]=".$group->name."&idLog=".$log->id;
 exit(header("location: permissions_edit.php?module=".$permission->module."&idCompany=".$company->id.$alert));
}

/**
 * Permission Group Reset
 */
function permission_group_reset(){
 if(!api_checkPermission("settings","permissions_edit")){api_die("accessDenied");}
 // get objects
 $company=api_accounts_company($_GET['idCompany']);
 // check objects
 if(!$company->id){echo api_text("companyNotFound");return FALSE;}
 // acquire variables
 $g_module=$_GET['module'];
 // remove all group for all actions
 $GLOBALS['db']->query("DELETE settings_permissions_join_accounts_groups.* FROM settings_permissions_join_accounts_groups JOIN settings_permissions ON settings_permissions.id=settings_permissions_join_accounts_groups.idPermission LEFT JOIN accounts_groups ON accounts_groups.id=settings_permissions_join_accounts_groups.idGroup WHERE settings_permissions.module='".$g_module."' AND ( settings_permissions_join_accounts_groups.idCompany='".$company->id."' OR accounts_groups.idCompany='".$company->id."' )");
 // log event
 $log=api_log(API_LOG_WARNING,"settings","permissionResetted",
  "{logs_settings_permissionResetted|".$g_module."|".$company->id."|".$company->name."}",
  $g_module,"settings/permissions_edit.php?module=".$g_module."&idCompany=".$company->id);
 // redirect
 $alert="&alert=permissionResetted&alert_class=alert-warning&alert_parameters[]=".$g_module."&idLog=".$log->id;
 exit(header("location: permissions_edit.php?module=".$g_module."&idCompany=".$company->id.$alert));
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
  if($menu->idMenu<>NULL && $p_idMenu<>$menu->idMenu){
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
  // alert
  $alert="&alert=menuUpdated&alert_class=alert-success";
 }else{
  $query="INSERT INTO settings_menus
   (idMenu,menu,module,url,position) VALUES
   ('".$p_idMenu."','".$p_menu."','".$p_module."','".$p_url."','".$position."')";
  // execute query
  $GLOBALS['db']->execute($query);
  // set id to last inserted id
  $g_id=$GLOBALS['db']->lastInsertedId();
  // alert
  $alert="&alert=menuCreated&alert_class=alert-success";
 }
 // upload image
 if(intval($_FILES['file']['size'])>0 && $_FILES['file']['error']==UPLOAD_ERR_OK){
  if(!is_dir("../uploads/uploads/links")){mkdir("../uploads/uploads/links",0777,TRUE);}
  if(file_exists("../uploads/uploads/links/".$g_id.".png")){unlink("../uploads/uploads/links/".$g_id.".png");}
  if(is_uploaded_file($_FILES['file']['tmp_name'])){move_uploaded_file($_FILES['file']['tmp_name'],"../uploads/uploads/links/".$g_id.".png");}
 }
 // redirect
 exit(header("location: menus_edit.php?idMenu=".$p_idMenu.$alert)); //."&id=".$g_id
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
  // delete icon
  if(file_exists("../uploads/uploads/links/".$g_id.".png")){unlink("../uploads/uploads/links/".$g_id.".png");}
  // redirect
  $alert="&alert=menuDeleted&alert_class=alert-success";
  exit(header("location: menus_edit.php?idMenu=".$g_idMenu.$alert));
 }else{
  // redirect
  $alert="&alert=settingError&alert_class=alert-error";
  exit(header("location: menus_edit.php?idMenu=".$g_idMenu.$alert));
 }
}

/* -[ Menu Permission Add ]-------------------------------------------------- */
function menu_permission_add(){
 if(!api_checkPermission("settings","menu_edit")){api_die("accessDenied");}
 // acquire variables
 $g_id=$_GET['id'];
 $g_idMenu=$_GET['idMenu'];
 $p_idGroup=$_POST['idGroup'];
 // check
 if($g_id>0 && $p_idGroup>0){
  $GLOBALS['db']->execute("DELETE FROM settings_menus_join_accounts_groups WHERE idMenu='".$g_id."' AND idGroup='".$p_idGroup."'");
  $GLOBALS['db']->execute("INSERT INTO settings_menus_join_accounts_groups (idMenu,idGroup) VALUES ('".$g_id."','".$p_idGroup."')");
 }
 // redirect
 header("location: menus_permissions.php?id=".$g_id."&idMenu=".$g_idMenu);
}

/* -[ Menu Permission Delete ]----------------------------------------------- */
function menu_permission_delete(){
 if(!api_checkPermission("settings","menu_edit")){api_die("accessDenied");}
 // acquire variables
 $g_id=$_GET['id'];
 $g_idMenu=$_GET['idMenu'];
 $g_idGroup=$_GET['idGroup'];
 // check
 if($g_id>0 && $g_idGroup>0){
  $GLOBALS['db']->execute("DELETE FROM settings_menus_join_accounts_groups WHERE idMenu='".$g_id."' AND idGroup='".$g_idGroup."'");
 }
 // redirect
 header("location: menus_permissions.php?id=".$g_id."&idMenu=".$g_idMenu);
}


/* -[ Menu Language Save ]--------------------------------------------------- */
function menu_language_save(){
 if(!api_checkPermission("settings","menu_edit")){api_die("accessDenied");}
 // acquire variables
 $g_id=$_GET['id'];
 if($g_id>0){$menu=$GLOBALS['db']->queryUniqueObject("SELECT * FROM settings_menus WHERE id='".$g_id."'");}
 $g_idLanguage=$_GET['idLanguage'];
 if($g_idLanguage>0){$translation=$GLOBALS['db']->queryUniqueObject("SELECT * FROM settings_menus_languages WHERE id='".$g_idLanguage."'");}
 $p_language=$_POST['language'];
 $p_name=addslashes($_POST['name']);
 // build query
 if($translation->id>0){
  $query="UPDATE settings_menus_languages SET
   language='".$p_language."',
   name='".$p_name."'
   WHERE id='".$g_idLanguage."'";
  // execute query
  $GLOBALS['db']->execute($query);
  // alert
  $alert="&alert=languageUpdated&alert_class=alert-success";
 }else{
  $query="INSERT INTO settings_menus_languages
   (idMenu,language,name) VALUES
   ('".$menu->id."','".$p_language."','".$p_name."')";
  // execute query
  $GLOBALS['db']->execute($query);
  // alert
  $alert="&alert=languageCreated&alert_class=alert-success";
 }
 // redirect
 exit(header("location: menus_languages.php?id=".$g_id."&idMenu=".$menu->idMenu));
}

/* -[ Menu Language Delete ]------------------------------------------------- */
function menu_language_delete(){
 if(!api_checkPermission("settings","menu_edit")){api_die("accessDenied");}
 // acquire variables
 $g_id=$_GET['id'];
 $g_idMenu=$_GET['idMenu'];
 $g_idLanguage=$_GET['idLanguage'];
 // check
 if($g_id>0 && $g_idLanguage>0){
  $GLOBALS['db']->execute("DELETE FROM settings_menus_languages WHERE id='".$g_idLanguage."'");
 }
 // redirect
 exit(header("location: menus_languages.php?id=".$g_id."&idMenu=".$g_idMenu));
}

?>