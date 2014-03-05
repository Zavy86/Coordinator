<?php
/* -------------------------------------------------------------------------- *\
|* -[ Notifications - Widget ]----------------------------------------------- *|
\* -------------------------------------------------------------------------- */
require_once("../core/api.inc.php");
api_loadLocaleFile("../notifications/");
// title
echo "<h4>".api_text("widget-title")."</h4>\n";
echo "<div class='well well-small well-white'>\n";
// acquire variables
$status=$_GET['s'];
if(!$status){$status=1;}
// definitions
$modals_array=array();
// build table
$table=new str_table(api_text("list-tr-no-results"),TRUE,"&s=".$status);
$table->addHeader("&nbsp;",NULL,"16",NULL);
$table->addHeader(api_text("list-th-reception-date"),"nowarp",NULL,"created");
if($status==2){$table->addHeader(api_text("list-th-archived-date"),"nowarp",NULL,"archived");}
$table->addHeader(api_text("list-th-notification"),NULL,"100%","subject");
// build query
$query_where="status='".$status."' AND idAccountTo='".$_SESSION['account']->id."'";
$query_limit=" LIMIT 0,10";
// query order
if($status==2){$query_order=api_queryOrder("archived DESC");}else{$query_order=api_queryOrder("created ASC");}
// query
$notifications=$GLOBALS['db']->query("SELECT * FROM notifications_notifications WHERE ".$query_where.$query_order.$query_limit);
while($notification=$GLOBALS['db']->fetchNextObject($notifications)){
 // build modal
 $modal=new str_modal($notification->id);
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
   //Archiviando questa notifica segnalerai di aver eseguito l\'azione richiesta. Confermi di aver eseguito l\'azione?
   $button=api_text("list-m-archiveAsDone");
  }else{
   $button=api_text("list-m-archive");
  }
 }elseif($notification->status==2){
  $action="notification_restore";
  $button="Ripristina";
 }
 $m_footer="<a class='btn' href='submit.php?act=".$action."&id=".$notification->idAction."'".$confirm.">".$button."</a>";
 $modal->footer($m_footer);
 $modals_array[]=$modal;
 // build table row
 $table->addRow();
 // build table fields
 switch($notification->typology){
  case 1:$table->addField(api_icon("icon-info-sign"),NULL);break;
  case 2:$table->addField(api_icon("icon-ok-sign"),NULL);break;
 }
 $table->addField(substr($notification->created,0,16),"nowarp");
 if($status==2){$table->addField(substr($notification->archived,0,16),"nowarp");}
 $table->addField($modal->link(stripslashes($notification->subject)),"nowarp");
}
// show table
$table->render();
// show modal windows
foreach($modals_array as $modal){$modal->render();}
echo "<span class='pull-right'><a href='../notifications/notifications_list.php?s=1'>".api_text("list-li-showAll")."</a></span>\n";
echo "<br>\n</div>\n";
?>