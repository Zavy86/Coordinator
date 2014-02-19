<?php
/* -------------------------------------------------------------------------- *\
|* -[ Logs - Template ]------------------------------------------------------ *|
\* -------------------------------------------------------------------------- */
include("module.inc.php");
// include core api functions
include("../core/api.inc.php");
// if exist include module api functions
if(file_exists("api.inc.php")){include("api.inc.php");}
// show header
$html->header(api_text("module-title"),$module_name);
// acquire variables
$g_interval=$_GET['i'];
if(!isset($g_interval)){$g_interval=7;}
$g_typology=$_GET['t'];
if(!isset($g_typology)){$g_typology=0;}
$g_module=$_GET['m'];
if(!isset($g_module)){$g_module=NULL;}
// build navigation tab
$nav=new str_navigation();
// switch typology label
switch($g_typology){
 case -1:$label=api_text("nav-warningsAndErrors");break;
 case 1:$label=api_text("nav-notices");break;
 case 2:$label=api_text("nav-warnings");break;
 case 3:$label=api_text("nav-errors");break;
 default:$label=api_text("nav-allEvents");break;
}
// build typology tab
$nav->addTab($label);
$nav->addSubTab(api_text("nav-allEvents"),"logs_list.php?t=0","&i=".$g_interval);
$nav->addSubTab(api_text("nav-warningsAndErrors"),"logs_list.php?t=-1","&i=".$g_interval);
$nav->addSubTab(api_text("nav-notices"),"logs_list.php?t=1","&i=".$g_interval);
$nav->addSubTab(api_text("nav-warnings"),"logs_list.php?t=2","&i=".$g_interval);
$nav->addSubTab(api_text("nav-errors"),"logs_list.php?t=3","&i=".$g_interval);
// build interval tab
$label=api_text("nav-lastDays",$g_interval);
$nav->addTab($label);
$nav->addSubTab(api_text("nav-lastDays",3),"logs_list.php?i=3","&t=".$g_typology);
$nav->addSubTab(api_text("nav-lastDays",7),"logs_list.php?i=7","&t=".$g_typology);
$nav->addSubTab(api_text("nav-lastDays",30),"logs_list.php?i=30","&t=".$g_typology);
$nav->addSubTab(api_text("nav-lastDays",90),"logs_list.php?i=90","&t=".$g_typology);
$nav->addSubTab(api_text("nav-lastDays",365),"logs_list.php?i=365","&t=".$g_typology);
// build modules tab
if($g_module<>NULL){$label=api_text("nav-module",strtoupper($g_module));}
 else{$label=api_text("nav-allModules");}
$nav->addTab($label);
$nav->addSubTab(api_text("nav-allModules"),"logs_list.php","?t=".$g_typology."&i=".$g_interval);
// get modules in the interval
$modules=$GLOBALS['db']->query("SELECT DISTINCT module FROM logs_logs WHERE timestamp BETWEEN CURDATE()- INTERVAL ".($g_interval-1)." DAY AND NOW() ORDER BY module ASC");
while($module=$GLOBALS['db']->fetchNextObject($modules)){
 $nav->addSubTab(strtoupper($module->module),"logs_list.php?m=".$module->module,"&t=".$g_typology."&i=".$g_interval);
}
// show navigation tab
$nav->render();
// check permissions before displaying module
if($checkPermission==NULL){content();}else{if(api_checkPermission("logs",$checkPermission,TRUE)){content();}}
// show footer
$html->footer();
?>