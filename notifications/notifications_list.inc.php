<?php
/* -------------------------------------------------------------------------- *\
|* -[ Notifications - Including List ]--------------------------------------- *|
\* -------------------------------------------------------------------------- */
 require_once("../core/api.inc.php");
 api_loadLocaleFile("../notifications/");
 // definitions
 $notifications_array=array();
 $modals_notifications_array=array();
 // notifications header
 echo "<li class='nav-header'>".api_text("list-li-notifications")."</li>\n";
 // check permission to send notifications
 if(api_checkPermission("notifications","notifications_send")){
  echo "<li><a href='../notifications/notifications_send.php'>".api_text("list-li-send")."</a></li>\n";
  echo "<li class='divider'></li>\n";
 }
 // query notifications
 $notifications=$GLOBALS['db']->query("SELECT * FROM notifications_notifications WHERE idAccountTo='".$_SESSION['account']->id."' AND status='1' LIMIT 0,10");
 while($notification=$GLOBALS['db']->fetchNextObject($notifications)){$notifications_array[]=$notification;}
 // show notifications subject
 foreach($notifications_array as $notification){
  // build modal
  $modal=new str_modal("notification_".$notification->id);
  // modal header
  $modal->header(stripslashes($notification->subject));
  // modal body
  $m_body="<p>Inviata da ".$account." il ".api_timestampFormat($notification->created,TRUE)."</p>\n";
  $m_body.="<p>".nl2br(stripslashes($notification->message))."</p>\n";
  if($notification->link<>NULL){
   if(substr($notification->link,0,7)<>"http://"){
    $url="http://".$_SERVER['SERVER_NAME'].$GLOBALS['dir'].$notification->link;
   }else{
    $url=$notification->link;
   }
   $m_body.="<p><a href='".$url."' target='_blank'>".$url."</a></p>\n";
  }
  if($status==2){$m_body.="<p>Archiviata da ".api_accountName($notification->idAccountArchived)." il ".api_timestampFormat($notification->archived,TRUE)."</p>";}
  $modal->body($m_body);
  // build modal footer
  $confirm=NULL;
  if($notification->status==1){
   $action="notification_archive";
   if($notification->typology==2){
    $confirm="onClick=\"return confirm('".api_text("list-m-confirm")."')\"";
    $button=api_text("list-m-archiveAsDone");
   }else{
    $button=api_text("list-m-archive");
   }
  }elseif($notification->status==2){
   $action="notification_restore";
   $button="Ripristina";
  }
  $m_footer="<a class='btn' href='../notifications/submit.php?act=".$action."&id=".$notification->idAction."'".$confirm.">".$button."</a>";
  $modal->footer($m_footer);
  $modals_notifications_array[]=$modal;
  // show notification
  echo "<li>".$modal->link(stripslashes($notification->subject))."</li>\n";
 }
 // show second divider
 if(count($notifications_array)>0){echo "<li class='divider'></li>\n";}
 echo "<li><a href='../notifications/notifications_list.php?s=1'>".api_text("list-li-showAll")."</a></li>\n";
?>
