<?php
/* -------------------------------------------------------------------------- *\
|* -[ Logs - Logs List ]----------------------------------------------------- *|
\* -------------------------------------------------------------------------- */
$checkPermission="logs_list";
include("template.inc.php");
function content(){
 // definitions
 $modals_array=array();
 // show filters
 echo $GLOBALS['navigation']->filtersText();
 // generate query
 $query_where=$GLOBALS['navigation']->filtersQuery("1");
 // pagination
 $pagination=new str_pagination("logs_logs",$query_where,$GLOBALS['navigation']->filtersGet());
 $query_limit=$pagination->queryLimit();
 // sorting
 $query_order=api_queryOrder("timestamp DESC");
 // build table
 $table=new str_table(api_text("list-tr-unvalued"),TRUE);
 $table->addHeader("&nbsp;",NULL,"16");
 $table->addHeader(api_text("list-th-timestamp"),"nowarp",NULL,"timestamp");
 $table->addHeader(api_text("list-th-module"),"nowarp",NULL,"module");
 $table->addHeader(api_text("list-th-action"),"nowarp",NULL,"action");
 $table->addHeader(api_text("list-th-log"),NULL,"100%","log");
 $table->addHeader(api_text("list-th-account"),"nowarp");
 // execute query
 $logs=$GLOBALS['db']->query("SELECT * FROM logs_logs WHERE ".$query_where.$query_order.$query_limit);
 while($log=$GLOBALS['db']->fetchNextObject($logs)){
  $tr_class=NULL;

  // load language file
  api_loadLocaleFile("../".$log->module."/");

  // switch typology
  switch($log->typology){
   case 1:$td_icon="icon-info-sign";break;
   case 2:$tr_class="warning";$td_icon="icon-warning-sign";break;
   case 3:$tr_class="error";$td_icon="icon-remove-sign";break;
  }

  // parse event
  $parsed=api_textParse($log->event);
  if($parsed->parsed){$log->event=api_text($parsed->key,$parsed->parameters);}

  // make subject
  if(($strpos=strpos($log->event,"\n"))==0){$strpos=100;}
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
  $table->addField($modal->link(api_icon($td_icon),"nowarp"));
  $table->addField(api_timestampFormat($log->timestamp,api_text("datetime")),"nowarp");
  $table->addField(strtoupper(stripslashes($log->module)));
  $table->addField(stripslashes($log->action));
  $table->addField($modal->link($log_subject));
  $table->addField(api_accountName($log->idAccount),"nowarp");
 }
 // show table
 $table->render();
 // show modals
 foreach($modals_array as $modal){$modal->render();}
 // show pagination
 $pagination->render();
}