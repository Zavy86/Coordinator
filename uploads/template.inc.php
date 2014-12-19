<?php
/* -------------------------------------------------------------------------- *\
|* -[ Contacts - Template ]-------------------------------------------------- *|
\* -------------------------------------------------------------------------- */
include("module.inc.php");
include("../core/api.inc.php");
api_loadModule();
// print header
$html->header(api_text("module-title"),$module_name);
// acquire variables
$g_idFile=$_GET['idFile'];
$g_idFolder=$_GET['idFolder'];
// build navigation tab
global $navigation;
$navigation=new str_navigation((api_baseName()=="uploads_list.php"?TRUE:FALSE));
// uploads list
$navigation->addTab(api_text("nav-list"),"uploads_list.php");
// uploads filters
if(api_baseName()=="uploads_list.php"){
 // typologies
 $navigation->addFilter("multiselect","typology",api_text("filter-typology"),array(1=>api_text("filter-typology-public"),2=>api_text("filter-typology-private")));
 // roles
 $filter_roles_array=array();
 $roles=$GLOBALS['db']->query("SELECT id,name FROM contacts_roles WHERE del='0' ORDER BY name ASC");
 while($role=api_contacts_role($GLOBALS['db']->fetchNextObject($roles))){
  $filter_roles_array[$role->id]=$role->name;
 }
 $navigation->addFilter("multiselect","role",api_text("filter-role"),$filter_roles_array);
 // deleted
 $navigation->addFilter("checkbox","del","&nbsp;",array(1=>api_text("filter-del")));
}
// operations
if($g_idFile){
 $navigation->addTab(api_text("nav-operations"),NULL,NULL,"active");
 $navigation->addSubTab(api_text("nav-edit-file"),"contacts_edit.php?idContact=".$g_idContact,NULL,NULL,(api_checkPermission($module_name,"contacts_edit")?TRUE:FALSE));
}else{
 $navigation->addTab(api_text("nav-operations"));
 if($g_idFolder){
  $navigation->addSubTab(api_text("nav-edit-folder"),"contacts_edit.php?idContact=".$g_idContact,NULL,NULL,(api_checkPermission($module_name,"contacts_edit")?TRUE:FALSE));
  $navigation->addSubTabDivider();
 }
 $navigation->addSubTab(api_text("nav-add-file"),"uploads_file_edit.php");
 $navigation->addSubTab(api_text("nav-add-folder"),"uploads_folder_edit.php");
}
// show navigation
$navigation->render();
// check permissions before displaying module
if($checkPermission==NULL){content();}else{if(api_checkPermission($module_name,$checkPermission,TRUE)){content();}}
// print footer
$html->footer();
?>