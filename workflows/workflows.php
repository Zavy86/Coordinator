<?php
/* -------------------------------------------------------------------------- *\
|* -[ Workflows - Tickets List ]--------------------------------------------- *|
\* -------------------------------------------------------------------------- */
header("refresh:300;");
$checkPermission="workflows_view";
require_once("template.inc.php");
function content(){
 // definitions
 $tables_array=array();
 $tickets_array=array();
 $tickets_modals_array=array();
 // acquire variables
 $g_workflows=$_GET['workflows'];
 // query where
 $query_where="status<='3'";
 // only assignable tickets
 $query_where.=" AND ( idAssigned='".$_SESSION['account']->id."'";
 foreach(api_account()->companies[api_company()->id]->groups as $group){$query_where.=" OR idGroup='".$group->id."'";}
 $query_where.=" )";
 // order tickets
 $query_order=api_queryOrder("addDate DESC");
 // acquire tickets
 $tickets=$GLOBALS['db']->query("SELECT * FROM workflows_tickets WHERE ".$query_where.$query_order);
 while($ticket=$GLOBALS['db']->fetchNextObject($tickets)){$tickets_array[]=api_workflows_ticket($ticket);}
 // build tickets table
 $tickets_table=new str_table(api_text("workflows-tr-ticketsUnvalued"),TRUE);
 $tickets_table->addHeader("&nbsp;",NULL,"16");
 $tickets_table->addHeader("#","nowarp text-center");
 $tickets_table->addHeader("!","nowarp text-center","16");
 $tickets_table->addHeader(api_text("workflows-th-timestamp"),"nowarp");
 //$tickets_table->addHeader(api_text("workflows-th-sla"),"nowarp text-center");
 $tickets_table->addHeader(api_text("workflows-th-account"),"nowarp");
 $tickets_table->addHeader(api_text("workflows-th-category"),"nowarp");
 $tickets_table->addHeader(api_text("workflows-th-subject"),NULL,"100%");
 // build tables
 $tables_array['opened']=$tickets_table;
 $tables_array['assigned']=clone $tickets_table;
 $tables_array['standby']=clone $tickets_table;
 // group in opened table
 $tickets_table->addHeader(api_text("workflows-th-group"),"nowarp text-center");
 // build tickets table rows
 foreach($tickets_array as $ticket){
  // definitions
  $table=NULL;
  if(!$ticket->idAssigned){$ticket->idAssigned=0;}
  // status
  if($ticket->status==1){$table="opened";}
  if($ticket->status==2&&$ticket->idAssigned==api_account()->id){$table="assigned";}
  if($ticket->status==3&&$ticket->idAssigned==api_account()->id){$table="standby";}
  // check table
  if(!$table){continue;}
  // details modal windows
  $details_modal=api_workflows_ticketDetailsModal($ticket);
  $tickets_modals_array[]=$details_modal;
  // check urged
  if($ticket->urged){$tr_class="error";}else{$tr_class=NULL;}
  // buil row
  $tables_array[$table]->addRow($tr_class);
  // build tickets table fields
  $tables_array[$table]->addField("<a href='workflows_view.php?id=".$ticket->idWorkflow."&idTicket=".$ticket->id."'>".api_icon("icon-search")."</a>","nowarp");
  $tables_array[$table]->addField($ticket->number,"nowarp");
  $tables_array[$table]->addField(api_workflows_ticketPriority($ticket->priority,TRUE),"nowarp text-center");
  $tables_array[$table]->addField(api_timestampFormat($ticket->addDate,api_text("datetime")),"nowarp");
  //$tables_array[$table]->addField(api_workflows_ticketSLA($ticket),"nowarp text-center");
  $tables_array[$table]->addField(api_workflows_referentName($ticket->idWorkflow),"nowarp");
  $tables_array[$table]->addField(api_workflows_categoryName($ticket->idCategory,TRUE,TRUE,TRUE),"nowarp");
  $tables_array[$table]->addField(stripslashes($ticket->subject));
  if($table=="opened"){$tables_array[$table]->addField(api_groupName($ticket->idGroup,TRUE,TRUE),"nowarp text-center");}
 }
 // build personal workflows table
 $workflows_table=new str_table(api_text("workflows-tr-workflowsUnvalued"),FALSE);
 $workflows_table->addHeader("&nbsp;",NULL,"16");
 $workflows_table->addHeader("#","nowarp text-center");
 $workflows_table->addHeader("!","nowarp text-center");
 $workflows_table->addHeader(api_text("workflows-th-timestamp"),"nowarp");
 $workflows_table->addHeader(api_text("workflows-th-category"),"nowarp");
 $workflows_table->addHeader(api_text("workflows-th-subject"),NULL,"100%");
 $workflows_table->addHeader(api_text("workflows-th-status"),"nowarp text-right","16");
 // query where
 if($g_workflows<>"all"){$query_where=" AND (status<='3' OR addDate>=(NOW()-INTERVAL 15 DAY))";}else{$query_where=NULL;}
 // build workflow table rows
 $workflows=$GLOBALS['db']->query("SELECT * FROM workflows_workflows WHERE addIdAccount='".api_account()->id."'".$query_where." ORDER BY addDate DESC");
 while($workflow=$GLOBALS['db']->fetchNextObject($workflows)){
  $workflows_table->addRow();
  // build workflows table fields
  $workflows_table->addField("<a href='workflows_view.php?id=".$workflow->id."'>".api_icon("icon-search")."</a>","nowarp");
  $workflows_table->addField(str_pad($workflow->id,5,"0",STR_PAD_LEFT),"nowarp");
  $workflows_table->addField(api_workflows_ticketPriority($workflow->priority,TRUE),"nowarp text-center");
  $workflows_table->addField(api_timestampFormat($workflow->addDate,api_text("datetime")),"nowarp");
  $workflows_table->addField(api_workflows_categoryName($workflow->idCategory,TRUE,TRUE,TRUE),"nowarp");
  $workflows_table->addField(stripslashes($workflow->subject));
  $workflows_table->addField(api_workflows_status($workflow->status),"nowarp text-right'");
 }
 // show opened tickets table
 if($tables_array["opened"]->count()){
  echo "<h5>".api_text("workflows-tickets-opened")."</h5>\n";
  $tables_array["opened"]->render();
 }
 // show assigned tickets table
 if($tables_array["assigned"]->count()){
  echo "<h5>".api_text("workflows-tickets-assigned")."</h5>\n";
  $tables_array["assigned"]->render();
 }
 // show standby tickets table
 if($tables_array["standby"]->count()){
  echo "<h5>".api_text("workflows-tickets-standby")."</h5>\n";
  $tables_array["standby"]->render();
 }
 // show workflows table
 if(is_object($workflows_table)){
  echo "<h5>".api_text("workflows-workflows")."</h5>\n";
  $workflows_table->render();
 }
 if($g_workflows<>"all"){echo "<a href='workflows.php?workflows=all'>".api_text("workflows-workflows-all")."</a>";}
  else{echo "<a href='workflows.php'>".api_text("workflows-workflows-recents")."</a>";}
}
?>