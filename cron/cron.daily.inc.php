<?php
/* -------------------------------------------------------------------------- *\
|* -[ Cron - Cron daily ]---------------------------------------------------- *|
\* -------------------------------------------------------------------------- */
require_once("../core/api.inc.php");
if(api_basePath()<>$GLOBALS['dir']."cron"){api_die();}

/* -[ Delete Old Sended Mail ]----------------------------------------------- */
$log="CRON - DELETE OLD SENDED MAIL\n";
$count=$GLOBALS['db']->countOf("logs_mails","status='1' AND sendDate < DATE(NOW() - INTERVAL 1 MONTH)");
if($count){
 $mails=$GLOBALS['db']->query("SELECT * FROM logs_mails WHERE status='1' AND sendDate < DATE(NOW() - INTERVAL 1 MONTH)");
 while($mail=$GLOBALS['db']->fetchNextObject($mails)){
  $GLOBALS['db']->execute("DELETE FROM logs_mails WHERE id='".$mail->id."'");
 }
 $log.="Number of mail sended 1 month ago deleted: ".$count."\n";
 // log event
 api_log(API_LOG_NOTICE,"cron","cronSendmailDelete",
  "{logs_cron_sendmailDelete|".$count."}");
}else{
 $log.="No mail sended 1 month ago to be deleted..\n";
}
// show footer
if($g_submit<>"cron"){
 echo nl2br($log)."<br>";
}else{
 echo $log;
}

?>