<?php
/* -------------------------------------------------------------------------- *\
|* -[ CRON ]----------------------------------------------------------------- *|
\* -------------------------------------------------------------------------- */
include("../core/api.inc.php");
// acquire variables
$g_submit=$_GET['submit'];
// check if submit from web form or cron
if($g_submit<>"cron"){$html->header("CRON");}
// initialization
$cron_daily_path=array();
$cron_weekly_path=array();
// search and include daily crons
if($handle_dir=opendir("../")){
 while(FALSE!==($entry_dir=readdir($handle_dir))){
  if($entry_dir<>"." && $entry_dir<>".." && is_dir("../".$entry_dir)){
   if($handle_cron=opendir("../".$entry_dir)){
    while(FALSE!==($entry_cron=readdir($handle_cron))){
     if($entry_cron=="cron.daily.inc.php"){$cron_daily_path[]="../".$entry_dir."/".$entry_cron;}
     if($entry_cron=="cron.weekly.inc.php"){$cron_weekly_path[]="../".$entry_dir."/".$entry_cron;}
    }
    closedir($handle_cron);
   }
  }
 }
 closedir($handle_dir);
}
// include daily cron
foreach($cron_daily_path as $daily_path){
 if(file_exists($daily_path)){include $daily_path;}
}
// include weekly cron on sunday
if(date("w")==0){
 foreach($cron_weekly_path as $weekly_path){
  if(file_exists($weekly_path)){include $weekly_path;}
 }
}
// check if submit from web form or cron
if($g_submit<>"cron"){$html->footer();}
?>