<?php
/* -------------------------------------------------------------------------- *\
|* -[ Accounts - Template ]-------------------------------------------------- *|
\* -------------------------------------------------------------------------- */
include("module.inc.php");
include("../core/api.inc.php");
api_loadModule();
// show header
$html->header(api_text("module-title"),NULL);
// acquire variables
$g_idAccount=$_GET['idAccount'];
$g_idCompany=$_GET['idCompany'];
$g_idRole=$_GET['idRole'];
$g_idGroup=$_GET['idGroup'];
// build navigation tab
$navigation=new str_navigation((in_array(api_baseName(),array("accounts_list.php","companies_list.php"))?TRUE:FALSE));
// accounts filters
if(api_baseName()=="accounts_list.php"){
 // companies
 $companies_array=array();
 $companies=$GLOBALS['db']->query("SELECT * FROM accounts_companies ORDER BY name ASC");
 while($company=$GLOBALS['db']->fetchNextObject($companies)){
  $companies_array[$company->id]=$company->name;
 }
 $navigation->addFilter("multiselect","companies",api_text("filter-companies"),$companies_array);
 // enabled
 $navigation->addFilter("radio","enabled","&nbsp;",array(1=>api_text("filter-enabled"),0=>api_text("filter-disabled")));
 // deleted
 $navigation->addFilter("checkbox","del","&nbsp;",array(1=>api_text("filter-deleted")));
 // if not filtered load default filters
 if($_GET['resetFilters']||($_GET['filtered']<>1 && $_SESSION['filters'][api_baseModule()][api_baseName()]['filtered']<>1)){$_GET['enabled']=1;}
}
// groups filters
if(api_baseName()=="groups_list.php"){
 // companies
 $companies_array=array();
 $companies=$GLOBALS['db']->query("SELECT * FROM accounts_companies ORDER BY name ASC");
 while($company=$GLOBALS['db']->fetchNextObject($companies)){
  $companies_array[$company->id]=$company->name;
 }
 $navigation->addFilter("select","company",api_text("filter-company"),$companies_array);
 // show members
 $navigation->addFilter("checkbox","members","&nbsp;",array(1=>api_text("filter-members")));
 // if not filtered load default filters
 if($_GET['resetFilters']||($_GET['filtered']<>1 && $_SESSION['filters'][api_baseModule()][api_baseName()]['filtered']<>1)){$_GET['company']=$_SESSION['company']->id;}
}
$navigation->addTab(api_text("nav-profile"),"accounts_customize.php");
if(api_checkPermission("accounts","accounts_view")){$navigation->addTab(api_text("nav-accounts"),"accounts_list.php");}
if(api_checkPermission("accounts","groups_view")){$navigation->addTab(api_text("nav-groups"),"groups_list.php",NULL,(api_baseName()=="groups_view.php"?"active":NULL));}
if(api_checkPermission("accounts","companies_view")){$navigation->addTab(api_text("nav-companies"),"companies_list.php",NULL,(api_baseName()=="companies_view.php"?"active":NULL));}
if(api_checkPermission("accounts","roles_view") ||
   api_checkPermission("accounts","companies_edit") ||
   api_checkPermission("accounts","groups_edit") ||
   api_checkPermission("accounts","accounts_edit") ){
 $navigation->addTab(api_text("nav-administration"));
 $navigation->addSubTabHeader(api_text("nav-roles"));
 $navigation->addSubTab(api_text("nav-roles-list"),"roles_list.php",NULL,NULL,(api_checkPermission("accounts","roles_view")?TRUE:FALSE));
 $navigation->addSubTab(api_text("nav-roles-add"),"roles_edit.php",NULL,NULL,(api_checkPermission($module_name,"roles_edit")?TRUE:FALSE));
 $navigation->addSubTabHeader(api_text("nav-companies"));
 $navigation->addSubTab(api_text("nav-companies-add"),"companies_edit.php",NULL,NULL,(api_checkPermission($module_name,"companies_edit")?TRUE:FALSE));
 $navigation->addSubTabHeader(api_text("nav-groups"));
 $navigation->addSubTab(api_text("nav-groups-add"),"groups_edit.php",NULL,NULL,(api_checkPermission($module_name,"groups_edit")?TRUE:FALSE));
 $navigation->addSubTabHeader(api_text("nav-accounts"));
 $navigation->addSubTab(api_text("nav-accounts-add"),"accounts_edit.php",NULL,NULL,(api_checkPermission($module_name,"accounts_edit")?TRUE:FALSE));
}
// show navigation tab
$navigation->render();
// check permissions before displaying module
if($checkPermission==NULL){content();}else{if(api_checkPermission($module_name,$checkPermission,TRUE)){content();}}
// show footer
$html->footer();
?>