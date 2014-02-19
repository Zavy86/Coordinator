<?php
/* -------------------------------------------------------------------------- *\
|* -[ Accounts - Template ]-------------------------------------------------- *|
\* -------------------------------------------------------------------------- */
include("../core/api.inc.php");
// show header
$html->header("Accounts",NULL);
// build navigation tab
$nav=new str_navigation();
$nav->addTab(api_text("nav-profile"),"index.php");
$nav->addTab(api_text("nav-accounts"),"accounts_list.php",NULL,NULL,(api_checkPermission("accounts","accounts_list"))?TRUE:FALSE);
$nav->addTab(api_text("nav-groups"),"groups_list.php",NULL,NULL,(api_checkPermission("accounts","groups_list"))?TRUE:FALSE);
$nav->addTab(api_text("nav-companies"),"companies_list.php",NULL,NULL,(api_checkPermission("accounts","accounts_list"))?TRUE:FALSE);
$nav->addTab(($_GET['id']>0)?api_text("nav-edit"):api_text("nav-add"));
$nav->addSubTab(api_text("nav-account"),"accounts_edit.php",NULL,NULL,(api_checkPermission("accounts","accounts_add"))?TRUE:FALSE);
$nav->addSubTab(api_text("nav-group"),"groups_edit.php",NULL,NULL,(api_checkPermission("accounts","groups_add"))?TRUE:FALSE);
$nav->addSubTab(api_text("nav-company"),"companies_edit.php",NULL,NULL,(api_checkPermission("accounts","companies_add"))?TRUE:FALSE);
// show navigation tab
$nav->render();
// check permissions before displaying module
if($checkPermission==NULL){content();}else{if(api_checkPermission("accounts",$checkPermission,TRUE)){content();}}
// show footer
$html->footer();
?>