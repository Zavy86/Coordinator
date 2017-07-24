<?php
/* -------------------------------------------------------------------------- *\
|* -[ Logs - Notifications List ]-------------------------------------------- *|
\* -------------------------------------------------------------------------- */
// refresh dashboard every 5 min
header("Refresh:300;url=".$_SERVER["PHP_SELF"]);
include("template.inc.php");
function content(){
 // acquire variables
 $g_status=$_GET['s'];
 if(!$g_status){$g_status=1;}
 $g_search=$_GET['q'];
 // definitions
 $modals_array=array();
 // build table
 $table=new str_table(api_text("notifications_list-tr-no-results"),TRUE,"&s=".$g_status);
 $table->addHeader("&nbsp;",NULL,"16",NULL);
 $table->addHeader(api_text("notifications_list-th-timestamp"),"nowarp",NULL,"timestamp");
 $table->addHeader(api_text("notifications_list-th-notification"),NULL,"100%","subject");
 // build query
 $query_where="status='".$g_status."' AND idAccount='".$_SESSION['account']->id."'";
 // build search query
 if(strlen($g_search)>0){
  $query_where.=" AND (";
  $query_where.=" module LIKE '%".$g_search."%'";
  $query_where.=" OR action LIKE '%".$g_search."%'";
  $query_where.=" OR subject LIKE '%".$g_search."%'";
  $query_where.=" OR message LIKE '%".$g_search."%'";
  if(strlen($g_search)==10){$query_where.=" OR timestamp LIKE '".$g_search."%'";}
  $query_where.=" )";
 }
 // pagination
 $pagination=new str_pagination("logs_notifications",$query_where,"&s=".$g_status);
 $query_limit=$pagination->queryLimit();
 // query order
 $query_order=api_queryOrder("timestamp DESC");
 // query
 $notifications=$GLOBALS['db']->query("SELECT * FROM logs_notifications WHERE ".$query_where.$query_order.$query_limit);
 while($notification=$GLOBALS['db']->fetchNextObject($notifications)){
  // build modal
  $modal=new str_modal($notification->id);
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
  $confirm=NULL;if($notification->status==1){
   $action="notification_archive";
   $button=api_text("notifications_list-m-archive");
  }elseif($notification->status==2){
   $action="notification_restore";
   $button="Ripristina";
  }
  $m_footer="<a class='btn' href='submit.php?act=".$action."&id=".$notification->id."'".$confirm.">".$button."</a>";
  $modal->footer($m_footer);
  $modals_array[]=$modal;
  // build table row
  $table->addRow();
  // build table fields
  $table->addField($modal->link(api_icon("icon-search")));
  $table->addField(api_timestampFormat($notification->timestamp,api_text("datetime")),"nowarp");
  $table->addField(stripslashes($notification->subject),"nowarp");
 }
 // show table
 $table->render();
 // show modal windows
 foreach($modals_array as $modal){$modal->render();}
 // show the pagination div
 $pagination->render();
}
?>