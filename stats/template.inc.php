<?php
/* -------------------------------------------------------------------------- *\
|* -[ Stats - Template ]----------------------------------------------------- *|
\* -------------------------------------------------------------------------- */
include("module.inc.php");
// include core api functions
include("../core/api.inc.php");
// if exist include module api functions
if(file_exists("api.inc.php")){include("api.inc.php");}
// show header
$html->header(api_text("module-title"),$module_name);
// build navigation tab
$nav=new str_navigation();
$nav->addTab(api_text("nav-servers"),"stats_server.php");
// show navigation tab
$nav->render();
// check permissions before displaying module
if($checkPermission==NULL){content();}else{if(api_checkPermission($module_name,$checkPermission,TRUE)){content();}}
// show footer
$html->footer();
?>