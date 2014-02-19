<?php
/* ------------------------------------------------------------------------- *\
|* -[ Dashboard - Template ]------------------------------------------------ *|
\* ------------------------------------------------------------------------- */
include("module.inc.php");
// include core api functions
include("../core/api.inc.php");
// if exist include module api functions
if(file_exists("api.inc.php")){include("api.inc.php");}
// show header
$html->header(api_text("module-title"),$module_name);
// build navigation tab
$nav=new str_navigation();
$nav->addTab(api_text("nav-notifications"),"notifications_list.php?s=1");
$nav->addTab(api_text("nav-archived-notifications"),"notifications_list.php?s=2");
if(api_baseName()=="notifications_send.php"){
 $nav->addTab(api_text("nav-send-notifications"),"notifications_send.php");
}
// show navigation tab
$nav->render();
// check permissions before displaying module
if($checkPermission==NULL){content();}else{if(api_checkPermission("dashboard",$checkPermission,TRUE)){content();}}
// show footer
$html->footer();
?>