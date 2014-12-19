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
 $navigation->addFilter("multiselect","typology",api_text("filter-typology"),array(1=>api_text("uploads-links-typology-public"),0=>api_text("uploads-links-typology-private")));
 // deleted
 $navigation->addFilter("checkbox","del","&nbsp;",array(1=>api_text("filter-del")));
}
// operations
if($g_idFile){
 $navigation->addTab(api_text("nav-operations"),NULL,NULL,"active");
 $navigation->addSubTab(api_text("nav-edit-file"),"uploads_files_edit.php?idFile=".$g_idFile,NULL,NULL,(api_checkPermission($module_name,"uploads_edit")?TRUE:FALSE));
 $navigation->addSubTab(api_text("nav-add-link"),"uploads_links_edit.php",NULL,NULL,(api_checkPermission($module_name,"links_edit")?TRUE:FALSE));
}else{
 $navigation->addTab(api_text("nav-operations"));
 if($g_idFolder){
  $navigation->addSubTab(api_text("nav-edit-folder"),"uploads_folders_edit.php?idFolder=".$g_idFolder,NULL,NULL,(api_checkPermission($module_name,"folders_edit")?TRUE:FALSE));
  $navigation->addSubTabDivider();
 }
 $navigation->addSubTab(api_text("nav-add-file"),"uploads_files_edit.php",NULL,NULL,(api_checkPermission($module_name,"uploads_edit")?TRUE:FALSE));
 $navigation->addSubTab(api_text("nav-add-folder"),"uploads_folders_edit.php",NULL,NULL,(api_checkPermission($module_name,"folders_edit")?TRUE:FALSE));
}
// show navigation
$navigation->render();
// check permissions before displaying module
if($checkPermission==NULL){content();}else{if(api_checkPermission($module_name,$checkPermission,TRUE)){content();}}
// print footer
$html->footer();
?>