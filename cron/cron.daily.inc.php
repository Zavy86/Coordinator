<?php
/* -------------------------------------------------------------------------- *\
|* -[ Cron - Cron daily ]---------------------------------------------------- *|
\* -------------------------------------------------------------------------- */
require_once("../core/api.inc.php");
if(api_basePath()<>$GLOBALS['dir']."cron"){api_die();}


/* -[ Remove temp files ]---------------------------------------------------- */
// log of the operation
$log_level=1;
$log="CRON - XXX\n";
$log.="Number of MySQL query executed..\n";
//api_log($log_level,"cron",$log);
// show footer
if($g_submit<>"cron"){
 echo nl2br($log);
}else{
 echo $log;
}
?>