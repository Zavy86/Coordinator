<?php
/* -------------------------------------------------------------------------- *\
|* -[ Dashboard - Notifications List ]--------------------------------------- *|
\* -------------------------------------------------------------------------- */
// refresh dashboard every 5 min
header("Refresh:300;url=".$_SERVER["PHP_SELF"]);
include("template.inc.php");
function content(){
// acquire variables
$status=$_GET['s'];
if(!$status){$status=1;}
$g_page=$_GET['p'];
if(!$g_page){$g_page=1;}
$g_limit=$_GET['l'];
if(!isset($g_limit)){$g_limit=20;}
$colspan=3;
$count=0;
?>
<table class="table table-striped table-hover table-condensed">
 <thead>
  <tr>
   <th width='16'>&nbsp;</th>
   <th class='nowarp'>Ricevuta</th>
   <?php if($status==2){echo "<th class='nowarp'>Archiviata</th>\n";$colspan=4;} ?>
   <th width='100%'>Notifica</th>
  </tr>
 </thead>
 <tbody>
<?php
// generate query
$query_where="status='".$status."' AND idAccountTo='".$_SESSION['account']->id."'";
// pagination
if($g_limit>0){
 $recordsLimit=$g_limit;
 $recordsCount=$GLOBALS['db']->countOf("notifications_notifications",$query_where);
 $query_start=($g_page-1)*$recordsLimit;
 $query_limit=" LIMIT ".$query_start.",".$recordsLimit;
}
// query
$notifications=$GLOBALS['db']->query("SELECT * FROM notifications_notifications WHERE ".$query_where." ORDER BY archived DESC,created DESC".$query_limit);
while($notification=$GLOBALS['db']->fetchNextObject($notifications)){
 $count++;
 // show record
 echo "<tr>\n";
 switch($notification->typology){
  case 1:echo "<td><i class='icon-info-sign'></i></td>\n";break;
  case 2:echo "<td><i class='icon-ok-sign'></i></td>\n";break;
 }
 if($notification->idAccountFrom>0){$account=api_accountName($notification->idAccountFrom);}else{$account="Intranet";}
 echo "<td class='nowarp'>".api_timestampFormat($notification->created,TRUE)."</td>\n";
 //echo "<td class='nowarp'>".$account."</td>\n";
 if($status==2){
  echo "<td class='nowarp'>".api_timestampFormat($notification->archived,TRUE)."</td>\n";
  //echo "<td class='nowarp'>".api_accountName($notification->idAccountArchived)."</td>\n";
 }
 echo "<td><a href='#modal".$notification->id."' data-toggle='modal' id='read".$notification->id."'>".stripslashes($notification->subject)."</a></td>\n";
 echo "</tr>\n";
 // modal label
 echo "<div id='modal".$notification->id."' class='modal hide fade' role='dialog' aria-hidden='true'>\n";
 echo "<div class='modal-header'>\n";
 // modal header
 echo "<button type='button' class='close' data-dismiss='modal' aria-hidden='true'>&times;</button>\n";
 echo "<h4>".stripslashes($notification->subject)."</h4>";
 echo "</div>\n";
 echo "<div class='modal-body'>\n";
 // modal body
 echo "<p>Inviata da ".$account." il ".api_timestampFormat($notification->created,TRUE)."</p>\n";
 echo "<p>".nl2br(stripslashes($notification->message))."</p>\n";
 if($notification->link<>NULL){
  $url="http://".$_SERVER['SERVER_NAME'].$GLOBALS['dir'].$notification->link;
  echo "<p><a href='".$url."' target='_blank'>".$url."</a></p>\n";
 }
 if($status==2){echo "<p>Archiviata da ".api_accountName($notification->idAccountArchived)." il ".api_timestampFormat($notification->archived,TRUE)."</p>\n";}
 echo "</div>\n";
 echo "<div class='modal-footer'>\n";
 // modal footer
 $confirm=NULL;
 if($notification->status==1){
  $action="notification_archive";
  if($notification->typology==2){
   $confirm="onClick=\"return confirm('Archiviando questa notifica segnalerai di aver eseguito l\'azione richiesta. Confermi di aver eseguito l\'azione?')\"";
   $button="Archivia come eseguita";
  }else{
   $button="Archivia";
  }
 }elseif($notification->status==2){
  $action="notification_restore";
  $button="Ripristina";
 }
 echo "<a class='btn' href='submit.php?act=".$action."&id=".$notification->idAction."'".$confirm.">".$button."</a>\n";
 echo "</div>\n</div>\n";
}
if(!$count){echo "<tr><td colspan=".$colspan.">Non &egrave; presente nessuna nuova notifica..</td></tr>\n";}
?>
 </tbody>
</table>
<?php
// show the pagination div
api_pagination($recordsCount,$recordsLimit,$g_page,"notifications_list.php?s=".$status,"pagination pagination-small pagination-right");
}
?>