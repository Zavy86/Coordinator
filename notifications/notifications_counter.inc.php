<?php
/* -------------------------------------------------------------------------- *\
|* -[ Notifications - Including Counter ]------------------------------------ *|
\* -------------------------------------------------------------------------- */
 require_once("../core/api.inc.php");
 // count unarchived notifications
 $notifications_unarchived=$GLOBALS['db']->countOf("logs_notifications","idAccount='".$_SESSION['account']->id."' AND status='1'");
 if($notifications_unarchived){echo "<b>".$notifications_unarchived."</b> <i class='icon-bell'></i>";}
  else{echo "<i class='icon-bell'></i>";}
?>