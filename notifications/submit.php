<?php
/* -------------------------------------------------------------------------- *\
|* -[ Notifications - Submit ]----------------------------------------------- *|
\* -------------------------------------------------------------------------- */
include('../core/api.inc.php');
$act=$_GET['act'];
switch($act){
 // notifications
 case "notification_send":notification_send();break;
 case "notification_archive":notification_archive();break;
 case "notification_restore":notification_restore();break;
 case "notification_subscriptions":notification_subscriptions();break;
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
 $p_idAccountTo=$_POST['idAccountTo'];
 $p_typology=$_POST['typology'];
 $p_subject=addslashes($_POST['subject']);
 $p_message=addslashes($_POST['message']);
 $p_link=addslashes($_POST['link']);
 if(strlen($p_subject)>0 && strlen($p_message)>0){
  switch($p_to){
   case 1:api_notification_send($p_idAccountTo,$p_typology,"dashboard",$p_subject,$p_message,$p_link);break;
   case 2:api_notification_group($p_idGroup,0,$p_typology,"dashboard",$p_subject,$p_message,$p_link);break;
   case 3:api_notification_all($p_typology,"dashboard",$p_subject,$p_message,$p_link);break;
  }
  // redirect
  $alert="?alert=notificationSend&alert_class=alert-success";
  header("location: notifications_list.php".$alert);
 }else{
  // redirect
  $alert="?alert=notificationSendError&alert_class=alert-error";
  header("location: notifications_send.php".$alert);
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
  $query="UPDATE logs_notifications SET status='2' WHERE id='".$notification->id."'";
  // execute query
  $GLOBALS['db']->execute($query);
 }
 // redirect
 exit(header("location: notifications_list.php?s=1"));
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
 exit(header("location: notifications_list.php?s=2"));
}

/* -[ Notification Subscriptions ]------------------------------------------- */
function notification_subscriptions(){
 // remove all subscriptions
 $GLOBALS['db']->execute("DELETE FROM logs_subscriptions WHERE idAccount='".api_accountId()."'");
 // parse triggers
 $triggers=$GLOBALS['db']->query("SELECT * FROM logs_triggers ORDER BY module ASC");
 while($trigger=$GLOBALS['db']->fetchNextObject($triggers)){
  // get notification typology
  $notification=$_POST["notification_".$trigger->trigger];
  // subscribe trigger
  switch($notification){
   case 1:$query="INSERT INTO logs_subscriptions VALUES ('".api_accountId()."','".$trigger->trigger."','0')";break;
   case 2:$query="INSERT INTO logs_subscriptions VALUES ('".api_accountId()."','".$trigger->trigger."','1')";break;
   default:$query=NULL;
  }
  $GLOBALS['db']->execute($query);
 }
 // redirect
 exit(header("location: notifications_subscriptions.php"));
}