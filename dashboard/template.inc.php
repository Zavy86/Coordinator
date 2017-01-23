<?php
/* -------------------------------------------------------------------------- *\
|* -[ Dashboard - Template ]------------------------------------------------- *|
\* -------------------------------------------------------------------------- */
include("module.inc.php");
include("../core/api.inc.php");
api_loadModule();
// show header
$html->header(api_text("module-title"),$module_name);
// build navigation tab
global $navigation;
$navigation=new str_navigation();
// dashboard edit
$navigation->addTab(api_text("nav-dashboard-edit"),"dashboard_edit.php");
// show navigation
$navigation->render();
// check permissions before displaying module
if($checkPermission==NULL){content();}else{if(api_checkPermission($module_name,$checkPermission,TRUE)){content();}}
// show footer
$html->footer();
?>