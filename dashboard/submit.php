<?php
/* -------------------------------------------------------------------------- *\
|* -[ Dashboard - Submit ]--------------------------------------------------- *|
\* -------------------------------------------------------------------------- */
include('../core/api.inc.php');
$act=$_GET['act'];
switch($act){
 // dashboard
 case "dashboard_":dashboard_();break;
 // default
 default:
  $alert="?alert=submitFunctionNotFound&alert_class=alert-warning&act=".$act;
  header("location: index.php".$alert);
}


/* -[ Dashboard  ]---------------------------------------------------- */
function dashboard_(){
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
