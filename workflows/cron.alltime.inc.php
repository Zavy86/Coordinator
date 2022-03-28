<?php
/* -------------------------------------------------------------------------- *\
|* -[ Workflows - Cron all time ]-------------------------------------------- *|
\* -------------------------------------------------------------------------- */
require_once("../core/api.inc.php");
include_once("../workflows/config.inc.php");
if(api_basePath()<>$GLOBALS['dir']."cron"){api_die();}

/* -[ Acquire Mails ]-------------------------------------------------------- */

$log="CRON - MAIL RECEIVED FOR TICKET\n";

// check mail host
if($mail_host<>"mailserver"){
 // open imap connection
 $mailbox=imap_open("{".$mail_host.":143/novalidate-cert}INBOX",$mail_user,$mail_pass);
 // check connection
 if($mailbox){
  // get mails number
  $mail_total=imap_num_msg($mailbox);
  // cycle mails
  for($mail=1;$mail<=$mail_total;$mail++){
   $headers=imap_header($mailbox,$mail);
   $sender=addslashes($headers->from[0]->mailbox."@".$headers->from[0]->host);
   $subject=addslashes($headers->subject);
   $timestamp=date("Y-m-d H:i:s",strtotime($headers->date));
   $structure=imap_fetchstructure($mailbox,$mail);
   if(isset($structure->parts) && is_array($structure->parts) && isset($structure->parts[1])){
    $body=imap_fetchbody($mailbox,$mail,1.1);
    if(!$body){$body=imap_fetchbody($mailbox,$mail,1);}
    $part=$structure->parts[1];
    if($part->encoding==3){$body=imap_base64($body);}
     elseif($part->encoding==1){$body=imap_8bit($body);}
     else{$body=imap_qprint($body);}
   }
   if(!strlen($body)){$body=imap_body($mailbox,$mail);}
   $body=str_replace(array("=A0","=20"),"",$body);
   $message=utf8_encode(addslashes(trim(preg_replace('/(\r\n|\r|\n)+/',"\n",$body))));
   // check filters
   if(count($mail_filters)){
    if(!in_array($sender,$mail_filters)){
     // mark mail for deletion
     imap_delete($mailbox,$mail);
     // skip this mail
     continue;
    }
   }
   // save into database
   $query="INSERT INTO workflows_mails (timestamp,sender,subject,message)
    VALUES ('".$timestamp."','".$sender."','".$subject."','".$message."')";
   // execute query
   $GLOBALS['db']->execute($query);
   // check query result
   if($GLOBALS['db']->lastInsertedId()>0){
    // increment counter
    $count++;
    // mark mail for deletion
    imap_delete($mailbox,$mail);
   }
  }
  // delete mails
  imap_expunge($mailbox);
  // close imap connection
  imap_close($mailbox);
 }else{
  $log.="Connection failed..\n";
 }
}

// check counter
if($count){
 $log.="Number of mail imported: ".$count."\n";
 // log event
 api_log(API_LOG_NOTICE,"workflows","workflowsCron",
  "{logs_workflows_workflowsCron|".$log."|".$count."}",
  NULL,"workflows/workflows_mails_list.php");
}else{
 $log.="No mail to import..\n";
}

// show footer
if($g_submit<>"cron"){
 echo nl2br($log)."<br>";
}else{
 echo $log;
}
?>