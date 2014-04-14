<?php
/* -------------------------------------------------------------------------- *\
|* -[ Notifications - Template ]--------------------------------------------- *|
\* -------------------------------------------------------------------------- */
include("module.inc.php");
include("../core/api.inc.php");
api_loadModule();
// show header
$html->header(api_text("module-title"),$module_name);
// build navigation tab
$nav=new str_navigation((api_baseName()=="notifications_list.php")?TRUE:FALSE,"s");
$nav->addTab(api_text("nav-notifications"),"notifications_list.php?s=1");
$nav->addTab(api_text("nav-archived-notifications"),"notifications_list.php?s=2");
if(api_checkPermission("notifications","notifications_send")){
 $nav->addTab(api_text("nav-send-notifications"),"notifications_send.php");
}
$nav->addTab(api_text("nav-subscriptions"),"notifications_subscriptions.php");
// show navigation tab
$nav->render();
// check permissions before displaying module
if($checkPermission==NULL){content();}else{if(api_checkPermission($module_name,$checkPermission,TRUE)){content();}}
// show footer
$html->footer();
?>