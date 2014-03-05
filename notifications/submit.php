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
 $notification=$GLOBALS['db']->queryUniqueObject("SELECT * FROM notifications_notifications WHERE idAction='".$g_id."'");
 // check if exist
 if($notification->idAction==$g_id){
  // generate query
  $query="UPDATE notifications_notifications SET
   idAccountArchived='".$_SESSION['account']->id."',
   archived='".date('Y-m-d H:i:s')."',
   status='2'
   WHERE idAction='".$notification->idAction."'";
  // execute query
  $GLOBALS['db']->execute($query);
 }
 // redirect
 header("location: notifications_list.php?s=1");
}

/* -[ Notification Restore ]------------------------------------------------- */
function notification_restore(){
 $g_id=$_GET['id'];
 if(!isset($g_id)){$g_id=0;}
 // get notification
 $notification=$GLOBALS['db']->queryUniqueObject("SELECT * FROM notifications_notifications WHERE idAction='".$g_id."'");
 // check if exist
 if($notification->idAction==$g_id){
  // generate query
  $query="UPDATE notifications_notifications SET
   idAccountArchived=NULL,
   archived=NULL,
   status='1'
   WHERE idAction='".$notification->idAction."'";
  // execute query
  $GLOBALS['db']->execute($query);
 }
 // redirect
 header("location: notifications_list.php?s=2");
}
