<?php
/* -------------------------------------------------------------------------- *\
|* -[ Logs - Including Notification List ]----------------------------------- *|
\* -------------------------------------------------------------------------- */
 require_once("../core/api.inc.php");
 api_loadLocaleFile("../logs/");
 // definitions
 $notifications_array=array();
 $modals_notifications_array=array();
 // notifications header
 echo "<li class='nav-header'>".api_text("notifications_list-li-notifications")."</li>\n";
 // check permission to send notifications
 if(api_checkPermission("logs","notifications_send")){
  echo "<li><a href='../logs/logs_notifications_send.php'>".api_text("notifications_list-li-send")."</a></li>\n";
  echo "<li class='divider'></li>\n";
 }
 // query notifications
 $notifications=$GLOBALS['db']->query("SELECT * FROM logs_notifications WHERE idAccount='".$_SESSION['account']->id."' AND status='1' LIMIT 0,10");
 while($notification=$GLOBALS['db']->fetchNextObject($notifications)){$notifications_array[]=$notification;}
 // show notifications subject
 foreach($notifications_array as $notification){
  // build modal
  $modal=new str_modal("notification_".$notification->id);
  // modal header
  $modal->header(stripslashes($notification->subject));
  // modal body
  $m_body="<p>".api_text("notifications_list-m-timestamp",api_timestampFormat($notification->timestamp,api_text("datetime")))."</p>\n";
  $m_body.="<hr>\n<p>".nl2br(stripslashes($notification->message))."</p>\n";
  if($notification->link<>NULL){
   if(substr($notification->link,0,4)<>"http"){
    $url=($_SERVER["HTTPS"]?"https://":"http://").$_SERVER['SERVER_NAME'].$GLOBALS['dir'].$notification->link;
   }else{
    $url=$notification->link;
   }
   $m_body.="<hr>\n<p><a href='".$url."' target='_blank'>".$url."</a></p>\n";
  }
  $modal->body($m_body);
  // build modal footer
  $confirm=NULL;
  if($notification->status==1){
   $action="notification_archive";
   $button=api_text("notifications_list-m-archive");
  }elseif($notification->status==2){
   $action="notification_restore";
   $button="Ripristina";
  }
  $m_footer="<a class='btn' href='../logs/submit.php?act=".$action."&id=".$notification->id."'>".$button."</a>";
  $modal->footer($m_footer);
  $modals_notifications_array[]=$modal;
  // show notification
  echo "<li>".$modal->link(substr(stripslashes($notification->subject),0,100))."</li>\n";
 }
 // show second divider
 if(count($notifications_array)>0){echo "<li class='divider'></li>\n";}
 echo "<li><a href='../logs/logs_notifications_list.php?s=1'>".api_text("notifications_list-li-showAll")."</a></li>\n";
 if(count($notifications_array)>2){echo "<li><a href='../logs/submit.php?act=notification_archiveAll' onClick=\"return confirm('".api_text("notifications_list-li-archiveAll-confirm")."')\">".api_text("notifications_list-li-archiveAll")."</a></li>\n";}
 echo "<li><a href='../logs/logs_subscriptions.php'>".api_text("notifications_list-li-subscribe")."</a></li>\n";
?>
