<?php
/* ------------------------------------------------------------------------- *\
|* -[ Settings - Template ]------------------------------------------------- *|
\* ------------------------------------------------------------------------- */
include("module.inc.php");
include("../core/api.inc.php");
api_loadModule();
// show header
$html->header(api_text("module-title"),$module_name);
// build navigation tab
$nav=new str_navigation();
$nav->addTab(api_text("nav-settings"),"settings_edit.php");
$nav->addTab(api_text("nav-modules"),"modules_edit.php");
$nav->addTab(api_text("nav-permissions"),"permissions_edit.php");
$nav->addTab(api_text("nav-menus"),"menus_edit.php");
// show navigation tab
$nav->render();
// check permissions before displaying module
if($checkPermission==NULL){content();}else{if(api_checkPermission($module_name,$checkPermission,TRUE)){content();}}
// show footer
$html->footer();
?>