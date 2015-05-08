<?php
/* -------------------------------------------------------------------------- *\
|* -[ Logs - Template ]------------------------------------------------------ *|
\* -------------------------------------------------------------------------- */
include("module.inc.php");
include("../core/api.inc.php");
api_loadModule();
// show header
$html->header(api_text("module-title"),$module_name);
// build navigation tab
$navigation=new str_navigation((api_baseName()=="logs_list.php"||api_baseName()=="logs_notifications_list.php")?TRUE:FALSE,"s");
if(api_checkPermission($module_name,"logs_list")){
 $navigation->addTab(api_text("nav-logs"),"logs_list.php");
}
if(api_account()->administrator){
 $navigation->addTab(api_text("nav-mails"),"logs_mails_list.php");
}
$navigation->addTab(api_text("nav-notifications"),"logs_notifications_list.php?s=1");
$navigation->addTab(api_text("nav-archived-notifications"),"logs_notifications_list.php?s=3");
if(api_checkPermission($module_name,"notifications_send")){
 $navigation->addTab(api_text("nav-send-notifications"),"logs_notifications_send.php");
}
$navigation->addTab(api_text("nav-subscriptions"),"logs_subscriptions.php");
// filters
if(api_baseName()=="logs_list.php"){
 $navigation->addFilter("multiselect","typology",api_text("filter-typologies"),array(1=>api_text("filter-notices"),2=>api_text("filter-warnings"),3=>api_text("filter-errors")));
 $navigation->addFilter("daterange","timestamp",api_text("filter-date"));
 $modules_array=array();
 $modules=$GLOBALS['db']->query("SELECT DISTINCT(module) FROM logs_logs ORDER BY module ASC");
 while($module=$GLOBALS['db']->fetchNextObject($modules)){
  $modules_array[$module->module]=$module->module;
 }
 $navigation->addFilter("multiselect","module",api_text("filter-modules"),$modules_array,"input-xlarge");
 // if not filtered load default filters
 if($_GET['resetFilters']||($_GET['filtered']<>1 && $_SESSION['filters'][api_baseName()]['filtered']<>1)){
  $_GET['timestamp_from']=date("Y-m-d",strtotime("-7 days"));
  $_GET['timestamp_to']=date("Y-m-d",strtotime("+1 days"));
  $_GET['typology']=array(2,3);
 }
}
// show navigation tab
$navigation->render();
// check permissions before displaying module
if($checkPermission==NULL){content();}else{if(api_checkPermission($module_name,$checkPermission,TRUE)){content();}}
// show footer
$html->footer();
?>