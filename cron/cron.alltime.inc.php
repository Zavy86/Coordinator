<?php
/* -------------------------------------------------------------------------- *\
|* -[ CIP - Cron All Time ]-------------------------------------------------- *|
\* -------------------------------------------------------------------------- */
require_once("../core/api.inc.php");
if(api_basePath()<>$GLOBALS['dir']."cron"){api_die();}

/* -[ Sendmail Asynchronous ]------------------------------------------------ */
$log="CRON - SENDMAIL\n";
$count=$GLOBALS['db']->countOf("sendmail_mails","status<>'1'");
$count_sended=0;
$count_failed=0;
if($count){
 $mails=$GLOBALS['db']->query("SELECT * FROM sendmail_mails WHERE status<>'1'");
 while($mail=$GLOBALS['db']->fetchNextObject($mails)){
  // send mail
  $sendmail=mail(stripslashes($mail->to),stripslashes($mail->subject),stripslashes($mail->message),stripslashes($mail->headers));
  if(!$sendmail){$error=error_get_last();}else{$error=NULL;}
  // check
  if($sendmail){
   $count_sended++;
   $GLOBALS['db']->execute("UPDATE sendmail_mails SET status='1',sendDate='".date("Y-m-d H:i:s")."' WHERE id='".$mail->id."'");
  }else{
   $count_failed++;
   if($mail->status==0){$status=2;}else{$status=$mail->status+1;}
   $GLOBALS['db']->execute("UPDATE sendmail_mails SET status='".$status."',sendDate='".date("Y-m-d H:i:s")."' WHERE id='".$mail->id."'");
   // log event
   api_log(API_LOG_ERROR,"cron","cronSendmailFailed",
    "{logs_cron_sendmailFailed|".$mail->to."|".$mail->subject."|".$error['type']."|".$error['message']."}",$mail->id);
  }
 }
 // log event
 $log.="Number of mail processed: ".$count."\n";
 $log.="Number of mail sended: ".$count_sended."\n";
 $log.="Number of mail failed: ".$count_failed."\n";
 if($count_failed>0){$typology=API_LOG_WARNING;}else{$typology=API_LOG_NOTICE;}
 api_log($typology,"cron","cronSendmail",
  "{logs_cron_sendmail|".$count."|".$count_sended."|".$count_failed."}");
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