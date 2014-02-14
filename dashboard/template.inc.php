<?php
/* ------------------------------------------------------------------------- *\
|* -[ Dashboard - Template ]------------------------------------------------ *|
\* ------------------------------------------------------------------------- */
include("../core/api.inc.php");
// show header
$html->header("Bacheca","dashboard");
// build navigation tab
$nt_array=array();
$nt_array[]=api_navigationTab(api_text("notifications"),"notifications_list.php?s=1");
$nt_array[]=api_navigationTab(api_text("archived-notifications"),"notifications_list.php?s=2");
if(api_baseName()=="notifications_send.php"){
 $nt_array[]=api_navigationTab(api_text("send-notifications"),"notifications_send.php");
}
// show navigation tab
api_navigation($nt_array);
// check permissions before displaying module
if($checkPermission==NULL){content();}else{if(api_checkPermission("dashboard",$checkPermission,TRUE)){content();}}
// show footer
$html->footer();
?>