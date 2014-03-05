<?php
/* -------------------------------------------------------------------------- *\
|* -[ Notifications - Including List ]--------------------------------------- *|
\* -------------------------------------------------------------------------- */
 require_once("../core/api.inc.php");
 api_loadLocaleFile("../notifications/");
 // definitions
 $notifications_array=array();
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
  echo "<li><a href='#'>".$notification->subject."</a></li>";
 }
 // show second divider
 if(count($notifications_array)>0){echo "<li class='divider'></li>\n";}
 echo "<li><a href='../notifications/notifications_list.php?s=1'>".api_text("list-li-showAll")."</a></li>\n";
?>
