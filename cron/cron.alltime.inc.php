<?php
/* -------------------------------------------------------------------------- *\
|* -[ CIP - Cron All Time ]-------------------------------------------------- *|
\* -------------------------------------------------------------------------- */
require_once("../core/api.inc.php");
if(api_basePath()<>$GLOBALS['dir']."cron"){api_die();}

/* -[ Sendmail Asynchronous ]------------------------------------------------ */
$log="CRON - SENDMAIL\n";
$count=$GLOBALS['db']->countOf("logs_mails","status='0'");
$count_sended=0;
$count_failed=0;
if($count){
 $mails=$GLOBALS['db']->query("SELECT * FROM logs_mails WHERE status<>'1'");
 while($mail=$GLOBALS['db']->fetchNextObject($mails)){
  // send mail
  $sendmail=api_mailer_process($mail);
  // check
  if($sendmail){
   $count_sended++;
  }else{
   $count_failed++;
   $mail_error=$GLOBALS['db']->queryUniqueValue("SELECT error FROM logs_mails WHERE id='".$mail->id."'");
   // log event
   api_log(API_LOG_ERROR,"cron","cronSendmailFailed","{logs_cron_sendmailFailed|".$mail->to."|".$mail->subject."|".$mail_error."}",$mail->id);
  }
 }
 // log event
 $log.="Number of mail processed: ".$count."\n";
 $log.="Number of mail sended: ".$count_sended."\n";
 $log.="Number of mail failed: ".$count_failed."\n";
 if($count_failed>0){$typology=API_LOG_WARNING;}else{$typology=API_LOG_NOTICE;}
 api_log($typology,"cron","cronSendmail","{logs_cron_sendmail|".$count."|".$count_sended."|".$count_failed."}");
}else{
 $log.="There are no mails..\n";
}
// show footer
if($g_submit<>"cron"){
 echo nl2br($log)."<br>";
}else{
 echo $log;
}
?>