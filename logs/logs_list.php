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
 $g_page=$_GET['p'];
 if(!$g_page){$g_page=1;}
 $g_limit=$_GET['l'];
 if(!isset($g_limit)){$g_limit=20;}
 // generate query
 $query_where="timestamp BETWEEN CURDATE()- INTERVAL ".($g_interval-1)." DAY AND NOW()";
 if($g_typology>0&&$g_typology<4){$query_where.=" AND typology='".$g_typology."'";}
 if($g_typology==-1){$query_where.=" AND (typology='2' OR typology='3')";}
 if($g_module<>NULL){$query_where.=" AND module='".$g_module."'";}
 // pagination
 if($g_limit>0){
  $recordsLimit=$g_limit;
  $recordsCount=$GLOBALS['db']->countOf("logs_logs",$query_where);
  $query_start=($g_page-1)*$recordsLimit;
  $query_limit=" LIMIT ".$query_start.",".$recordsLimit;
 }
 // build table header
 $th_array=array(
  api_tableHeader("&nbsp;",NULL,"16"),
  api_tableHeader(api_text("list-th-date"),"nowarp"),
  api_tableHeader(api_text("list-th-module"),"nowarp"),
  api_tableHeader(api_text("list-th-log"),NULL,"100%"),
  api_tableHeader(api_text("list-th-account"),"nowarp"),
 );
 // new logs array
 $logs_new_id=array();
 // execute query
 $logs=$GLOBALS['db']->query("SELECT * FROM logs_logs WHERE ".$query_where." ORDER BY timestamp DESC".$query_limit);
 while($log=$GLOBALS['db']->fetchNextObject($logs)){
  $tr_class=NULL;
  // check unread
  if($log->new){
   $logs_new_id[]=$log->id;
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
  if(($strpos=strpos($log->log,"\n"))==0){$strpos=50;}
  $log_subject=str_ireplace("<br>"," ",substr($log->log,0,$strpos));
  if($log->new){$log_subject="<span class='unread'>".$log_subject."</span>";}
  // build table data
  $td_array=array();
  $td_array[]=api_tableField(api_icon($td_icon),"nowarp");
  $td_array[]=api_tableField(api_timestampFormat($log->timestamp,TRUE),"nowarp");
  $td_array[]=api_tableField(strtoupper(stripslashes($log->module)));
  $td_array[]=api_tableField(api_modalLink($log->id,$log_subject));
  $td_array[]=api_tableField(api_accountName($log->idAccount),"nowarp");
  // build group table row
  $tr_array[]=api_tableRow($td_array,$tr_class);
  // build modal window
  $m_body="<dl>\n";
  $m_body.="<dt>Data</dt><dd>".api_timestampFormat($log->timestamp,TRUE,TRUE)."</dd><br>\n";
  if($log->idAccount>0){$m_body.="<dt>Account:</dt><dd>".api_accountName($log->idAccount)."</dd><br>\n";}
  $m_body.="<dt>Indirizzo IP</dt><dd>".$log->ip."</dd><br>\n"; // gethostbyaddr
  if(strlen($log->link)>0){$m_body.="<dt>Link:</dt><dd><a href='".$GLOBALS['dir'].$log->link."' target='_blank'>".$log->link."</a></dd><br>\n";}
  $m_body.="<dt>Log</dt><dd>".nl2br($log->log)."</dd>\n";
  $m_body.="</dl>";
  // show modal window
  api_modal($log->id,strip_tags($log_subject),$m_body);
 }
 // show table
 api_table($th_array,$tr_array,api_text("groups_list-tr-unvalued"));
 // show the pagination div
 api_pagination($recordsCount,$recordsLimit,$g_page,"logs_list.php?i=".$g_interval."&t=".$g_typology."&m=".$g_module,"pagination pagination-small pagination-right");
 // set the javascript to set read new items
 if(count($logs_new_id)>0){
  echo "<script language='javascript'>\n";
  foreach($logs_new_id as $log_new_id){
   echo "$(\"#modal-link_".$log_new_id."\").click(function(){\n";
   echo " $(this).find(\"span\").removeClass('unread')\n";
   echo "});\n";
  }
  echo "</script>\n";
 }
}