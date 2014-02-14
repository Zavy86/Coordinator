<?php
/* -------------------------------------------------------------------------- *\
|* -[ Accounts - Template ]-------------------------------------------------- *|
\* -------------------------------------------------------------------------- */
include("../core/api.inc.php");
// show header
$html->header("Accounts",NULL);
// build navigation tab
$nt_array=array();
$nt_array[]=api_navigationTab(api_text("nav-profile"),"index.php");
$nt_array[]=api_navigationTab(api_text("nav-accounts"),"accounts_list.php",NULL,NULL,NULL,(api_checkPermission("accounts","accounts_list"))?TRUE:FALSE);
$nt_array[]=api_navigationTab(api_text("nav-groups"),"groups_list.php",NULL,NULL,NULL,(api_checkPermission("accounts","groups_list"))?TRUE:FALSE);
$nt_array[]=api_navigationTab(api_text("nav-companies"),"companies_list.php",NULL,NULL,NULL,(api_checkPermission("accounts","accounts_list"))?TRUE:FALSE);


if(
 api_baseName()=="accounts_edit.php" ||
 api_baseName()=="groups_edit.php" ||
 api_baseName()=="companies_edit.php"
){$class="active";}else{$class=NULL;}

if($_GET['id']>0){$label=api_text("nav-edit");}else{$label=api_text("nav-add");}



$ntd_array=array();
$ntd_array[]=api_navigationTab(api_text("nav-account"),"accounts_edit.php",NULL,NULL,NULL,(api_checkPermission("accounts","accounts_add"))?TRUE:FALSE);
$ntd_array[]=api_navigationTab(api_text("nav-group"),"groups_edit.php",NULL,NULL,NULL,(api_checkPermission("accounts","groups_add"))?TRUE:FALSE);
$ntd_array[]=api_navigationTab(api_text("nav-company"),"companies_edit.php",NULL,NULL,NULL,(api_checkPermission("accounts","companies_add"))?TRUE:FALSE);

$nt_array[]=api_navigationTab($label,NULL,NULL,$class,$ntd_array);

// show navigation tab
api_navigation($nt_array);

// check permissions before displaying module
if($checkPermission==NULL){content();}else{if(api_checkPermission("accounts",$checkPermission,TRUE)){content();}}
// show footer
$html->footer();
?>