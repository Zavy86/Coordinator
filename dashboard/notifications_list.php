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
 $g_orderField=$_GET['of'];
 $g_orderMode=$_GET['om'];
 $colspan=3;


 // build table header
 $th_array=array(
  api_tableHeader("&nbsp;",NULL,"16",NULL),
  api_tableHeader(api_text("th-reception-date"),"nowarp",NULL,"created")
 );
 if($status==2){
  $th_array[]=api_tableHeader(api_text("th-archived-date"),"nowarp",NULL,"archived");
  $colspan=4;
 }
 $th_array[]=api_tableHeader(api_text("th-notification"),NULL,"100%","subject");


 // build query
 $query_where="status='".$status."' AND idAccountTo='".$_SESSION['account']->id."'";


 // pagination
 if($g_limit>0){
  $recordsLimit=$g_limit;
  $recordsCount=$GLOBALS['db']->countOf("notifications_notifications",$query_where);
  $query_start=($g_page-1)*$recordsLimit;
  $query_limit=" LIMIT ".$query_start.",".$recordsLimit;
 }


 // order
 $query_order_field=$g_orderField;
 if(!$query_order_field){if($status==2){$query_order_field="archived";}else{$query_order_field="created";}}
 if($g_orderMode==1){$query_order_mode=" ASC";}else{$query_order_mode=" DESC";}
 $query_order=" ORDER BY ".$query_order_field.$query_order_mode;

 // query
 $notifications=$GLOBALS['db']->query("SELECT * FROM notifications_notifications WHERE ".$query_where.$query_order.$query_limit);
 while($notification=$GLOBALS['db']->fetchNextObject($notifications)){
  // build table data
  $td_array=array();
  switch($notification->typology){
   case 1:$td_array[]=api_tableField(api_icon("icon-info-sign"),NULL);break;
   case 2:$td_array[]=api_tableField(api_icon("icon-ok-sign"),NULL);break;
  }
  $td_array[]=api_tableField(api_timestampFormat($notification->created,TRUE),"nowarp");
  if($status==2){$td_array[]=api_tableField(api_timestampFormat($notification->archived,TRUE),"nowarp");}
  $td_array[]=api_tableField("<a href='#modal".$notification->id."' data-toggle='modal' id='read".$notification->id."'>".stripslashes($notification->subject)."</a>","nowarp");


  // build table row
  $tr_array[]=api_tableRow($td_array,NULL);


  // modal label
  if($notification->idAccountFrom>0){$account=api_accountName($notification->idAccountFrom);}else{$account="Intranet";}
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
   if(substr($notification->link,0,7)<>"http://"){
    $url="http://".$_SERVER['SERVER_NAME'].$GLOBALS['dir'].$notification->link;
   }else{
    $url=$notification->link;
   }
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

 // show table
 api_Table($th_array,$tr_array,api_text("tr-no-results"),TRUE,"&s=".$status);

 // show the pagination div
 api_pagination($recordsCount,$recordsLimit,$g_page,"notifications_list.php?s=".$status,"pagination pagination-small pagination-right");
}
?>