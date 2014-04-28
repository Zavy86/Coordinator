<?php
/* -------------------------------------------------------------------------- *\
|* -[ Cron - Cron weekly ]--------------------------------------------------- *|
\* -------------------------------------------------------------------------- */
require_once("../core/api.inc.php");
if(api_basePath()<>$GLOBALS['dir']."cron"){api_die();}


/* -[ Remove temp files ]---------------------------------------------------- */
$log_level=1;
$log="CRON - CLEANER\n";
$count=0;
// remove all files in /tmp
$count=api_rm_recursive("../tmp",1);
// make directory and default index file
mkdir("../tmp");
file_put_contents("../tmp/index.php","<?php header(\"location: ../index.php\"); ?>");
// log operation
$log.="Number of file and folder removed: ".($count-2)."\n";
if($count-2>0){
 api_log($log_level,"cron","cron",$log);
}
// show footer
if($g_submit<>"cron"){
 echo nl2br($log);
}else{
 echo $log;
}

?>