<?php
/* -------------------------------------------------------------------------- *\
|* -[ Logs - Submit ]-------------------------------------------------------- *|
\* -------------------------------------------------------------------------- */
include('../core/api.inc.php');
$act=$_GET['act'];
switch($act){
 // notifications
 case "notification_send":notification_send();break;
 case "notification_archive":notification_archive();break;
 case "notification_archiveAll":notification_archiveAll();break;
 case "notification_restore":notification_restore();break;
 case "notification_subscriptions":notification_subscriptions();break;
 // mails
 case "mails_retry":mails_retry();break;
 case "mails_delete":mails_delete();break;
 // default
 default:
  $alert="?alert=submitFunctionNotFound&alert_class=alert-warning&act=".$act;
  header("location: index.php".$alert);
}


/* -[ Notification Send ]---------------------------------------------------- */
function notification_send(){
 // acquire variables
 $p_to=$_POST['to'];
 $p_idGroup=$_POST['idGroup'];
 $p_idAccount=$_POST['idAccount'];
 $p_typology=$_POST['typology'];
 $p_subject=addslashes($_POST['subject']);
 $p_message=addslashes($_POST['message']);
 $p_link=addslashes($_POST['link']);
 if(strlen($p_subject)>0 && strlen($p_message)>0){
  switch($p_to){
   case 1:api_notification_send($p_idAccount,"logs","logsNotification",$p_subject,$p_message,$p_link);break;
   case 2:api_notification_group($p_idGroup,0,"logs","logsNotification",$p_subject,$p_message,$p_link);break;
   case 3:api_notification_all("logs","logsNotification",$p_subject,$p_message,$p_link);break;
  }
  // redirect
  $alert="&alert=notificationSend&alert_class=alert-success";
  header("location: logs_notifications_list.php?s=1".$alert);
 }else{
  // redirect
  $alert="&alert=notificationSendError&alert_class=alert-error";
  header("location: logs_sendnotifications.php?s=1".$alert);
 }
}

/* -[ Notification Archive ]------------------------------------------------- */
function notification_archive(){
 $g_id=$_GET['id'];
 if(!isset($g_id)){$g_id=0;}
 // get notification
 $notification=$GLOBALS['db']->queryUniqueObject("SELECT * FROM logs_notifications WHERE id='".$g_id."'");
 // check if exist
 if($notification->id>0){
  // generate query
  $query="UPDATE logs_notifications SET status='3' WHERE id='".$notification->id."'";
  // execute query
  $GLOBALS['db']->execute($query);
 }
 // redirect
 exit(header("location: logs_notifications_list.php?s=1"));
}

/* -[ Notification Archive All ]--------------------------------------------- */
function notification_archiveAll(){
 // archive all user notifications
 $GLOBALS['db']->execute("UPDATE logs_notifications SET status='3' WHERE idAccount='".api_account()->id."'");
 // redirect
 exit(header("location: logs_notifications_list.php?s=3"));
}

/* -[ Notification Restore ]------------------------------------------------- */
function notification_restore(){
 $g_id=$_GET['id'];
 if(!isset($g_id)){$g_id=0;}
 // get notification
 $notification=$GLOBALS['db']->queryUniqueObject("SELECT * FROM logs_notifications WHERE id='".$g_id."'");
 // check if exist
 if($notification->id>0){
  // generate query
  $query="UPDATE logs_notifications SET status='1' WHERE id='".$notification->id."'";
  // execute query
  $GLOBALS['db']->execute($query);
 }
 // redirect
 exit(header("location: logs_notifications_list.php?s=3"));
}

/* -[ Notification Subscriptions ]------------------------------------------- */
function notification_subscriptions(){
 // remove all subscriptions
 $GLOBALS['db']->execute("DELETE FROM logs_subscriptions WHERE idAccount='".api_account()->id."'");
 // parse triggers
 $triggers=$GLOBALS['db']->query("SELECT * FROM logs_triggers GROUP BY `trigger` ORDER BY module ASC");
 while($trigger=$GLOBALS['db']->fetchNextObject($triggers)){
  // get notification typology
  $notification=$_POST["notification_".$trigger->trigger];
  // subscribe trigger
  switch($notification){
   case 1:$query="INSERT INTO logs_subscriptions VALUES ('".api_account()->id."','".$trigger->trigger."','0','0')";break;
   case 2:$query="INSERT INTO logs_subscriptions VALUES ('".api_account()->id."','".$trigger->trigger."','1','0')";break;
   case 3:$query="INSERT INTO logs_subscriptions VALUES ('".api_account()->id."','".$trigger->trigger."','1','1')";break;
   default:$query=NULL;
  }
  $GLOBALS['db']->execute($query);
 }
 // redirect
 $alert="?alert=notificationUpdated&alert_class=alert-success";
 exit(header("location: logs_subscriptions.php".$alert));
}

/* -[ Mails Retry ]---------------------------------------------------------- */
function mails_retry(){
 $g_id=$_GET['id'];
 if(!isset($g_id)){$g_id=0;}
 // get mail
 $mail=$GLOBALS['db']->queryUniqueObject("SELECT * FROM logs_mails WHERE id='".$g_id."'");
 // check if exist
 if($mail->id>0){
  // delete mail
  $GLOBALS['db']->execute("UPDATE logs_mails SET status='0',error='',sendDate='' WHERE id='".$mail->id."'");
 }
 // redirect
 exit(header("location: logs_mails_list.php"));
}

/* -[ Mails Delete ]--------------------------------------------------------- */
function mails_delete(){
 $g_id=$_GET['id'];
 if(!isset($g_id)){$g_id=0;}
 // get mail
 $mail=$GLOBALS['db']->queryUniqueObject("SELECT * FROM logs_mails WHERE id='".$g_id."'");
 // check if exist
 if($mail->id>0){
  // delete mail
  $GLOBALS['db']->execute("DELETE FROM logs_mails WHERE id='".$mail->id."'");
 }
 // redirect
 exit(header("location: logs_mails_list.php"));
}