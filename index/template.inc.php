<?php
/* -------------------------------------------------------------------------- *\
|* -[ Index - Template ]----------------------------------------------------- *|
\* -------------------------------------------------------------------------- */
include("module.inc.php");
include("../core/api.inc.php");
api_loadModule();
// show header
$html->header(NULL,$module_name);
// check permissions before displaying module
if($checkPermission==NULL){content();}else{if(api_checkPermission($module_name,$checkPermission,TRUE)){content();}}
// show footer
$html->footer();
?>