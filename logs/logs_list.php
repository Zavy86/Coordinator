<?php
/* -------------------------------------------------------------------------- *\
|* -[ Logs - Logs List ]----------------------------------------------------- *|
\* -------------------------------------------------------------------------- */
$checkPermission="logs_list";
include("template.inc.php");
function content(){
 // acquire variables
 $g_interval=$_GET['i'];
 if(!isset($g_interval)){$g_interval=7;}
 $g_typology=$_GET['t'];
 if(!isset($g_typology)){$g_typology=0;}
 $g_module=$_GET['m'];
 if(!isset($g_module)){$g_module=NULL;}
 // definitions
 $news_array=array();
 $modals_array=array();
 // generate query
 $query_where="timestamp BETWEEN CURDATE()- INTERVAL ".($g_interval-1)." DAY AND NOW()";
 if($g_typology>0&&$g_typology<4){$query_where.=" AND typology='".$g_typology."'";}
 if($g_typology==-1){$query_where.=" AND (typology='2' OR typology='3')";}
 if($g_module<>NULL){$query_where.=" AND module='".$g_module."'";}
 // pagination
 $pagination=new str_pagination("logs_logs",$query_where,"&i=".$g_interval."&t=".$g_typology."&m=".$g_module);
 $query_limit=$pagination->queryLimit();
 // sorting
 $query_order=api_queryOrder("timestamp DESC");
 // build table
 $table=new str_table(api_text("list-tr-unvalued"),TRUE,"&i=".$g_interval."&t=".$g_typology."&m=".$g_module);
 $table->addHeader("&nbsp;",NULL,"16");
 $table->addHeader(api_text("list-th-date"),"nowarp",NULL,"timestamp");
 $table->addHeader(api_text("list-th-module"),"nowarp",NULL,"module");
 $table->addHeader(api_text("list-th-log"),NULL,"100%","log");
 $table->addHeader(api_text("list-th-account"),"nowarp");
 // execute query
 $logs=$GLOBALS['db']->query("SELECT * FROM logs_logs WHERE ".$query_where.$query_order.$query_limit);
 while($log=$GLOBALS['db']->fetchNextObject($logs)){
  $tr_class=NULL;
  // check unread
  if($log->new){
   $news_array[]=$log->id;
   // set new status to false
   $GLOBALS['db']->execute("UPDATE logs_logs SET new='0' WHERE id='".$log->id."'");
  }
  // switch typology
  switch($log->typology){
   case 1:$td_icon="icon-info-sign";break;
   case 2:if($g_typology<1){$tr_class="warning";}$td_icon="icon-warning-sign";break;
   case 3:if($g_typology<1){$tr_class="error";}$td_icon="icon-remove-sign";break;
  }
  // make subject
  if(($strpos=strpos($log->event,"\n"))==0){$strpos=50;}
  $log_subject=str_ireplace("<br>"," ",substr($log->event,0,$strpos));
  if($log->new){$log_subject="<span class='unread'>".$log_subject."</span>";}
  // build modal window
  $modal=new str_modal($log->id);
  $modal->header(strip_tags($log_subject));
  $m_body="<dl>\n";
  $m_body.="<dt>Data</dt><dd>".api_timestampFormat($log->timestamp,api_text("timestamp"))."</dd><br>\n";
  if($log->idAccount>0){$m_body.="<dt>Account:</dt><dd>".api_accountName($log->idAccount)."</dd><br>\n";}
  $m_body.="<dt>Indirizzo IP</dt><dd>".$log->ip."</dd><br>\n"; // gethostbyaddr
  if(strlen($log->link)>0){$m_body.="<dt>Link:</dt><dd><a href='".$GLOBALS['dir'].$log->link."' target='_blank'>".$log->link."</a></dd><br>\n";}
  $m_body.="<dt>Log</dt><dd>".nl2br($log->event)."</dd>\n";
  $m_body.="</dl>";
  $modal->body($m_body);
  $modals_array[]=$modal;
  // build group table row
  $table->addRow($tr_class);
  // build table fields
  $table->addField(api_icon($td_icon),"nowarp");
  $table->addField(api_timestampFormat($log->timestamp,api_text("datetime")),"nowarp");
  $table->addField(strtoupper(stripslashes($log->module)));
  $table->addField($modal->link($log_subject));
  $table->addField(api_accountName($log->idAccount),"nowarp");
 }
 // show table
 $table->render();
 // show modals
 foreach($modals_array as $modal){$modal->render();}
 // show pagination
 $pagination->render();
 // set the javascript to set read new items
 if(count($news_array)>0){
  echo "<script language='javascript'>\n";
  foreach($news_array as $new){
   echo "$(\"#modal-link_".$new."\").click(function(){\n";
   echo " $(this).find(\"span\").removeClass('unread')\n";
   echo "});\n";
  }
  echo "</script>\n";
 }
}