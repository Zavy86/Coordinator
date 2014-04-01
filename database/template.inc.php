<?php
/* -------------------------------------------------------------------------- *\
|* -[ Database - Template ]-------------------------------------------------- *|
\* -------------------------------------------------------------------------- */
include("module.inc.php");
include("../core/api.inc.php");
api_loadModule();
// show header
$html->header(api_text("module-title"),$module_name);
// acquire variables
$g_module=$_GET['module'];
// build navigation
global $navigation;
$navigation=new str_navigation(TRUE);
$navigation->addTab(api_text("nav-view"),"database_view.php?module=");
if($g_module){
 $navigation->addTab(api_text("nav-module",ucfirst($g_module)),"database_view.php?module=".$g_module);
}
// show navigation
$navigation->render();
// check permissions before displaying module
if($checkPermission==NULL){content();}else{if(api_checkPermission($module_name,$checkPermission,TRUE)){content();}}
// show footer
$html->footer();
?>