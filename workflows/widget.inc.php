<?php
/* -------------------------------------------------------------------------- *\
|* -[ Workflows - Widget ]--------------------------------------------------- *|
\* -------------------------------------------------------------------------- */
require_once("../core/api.inc.php");
// load module language file
api_loadLocaleFile("../workflows/");
// include module api
require_once("../workflows/api.inc.php");
// widget title and well
echo "<h4>".api_text("widget-title")."</h4>\n";
echo "<div class='well well-small well-white'>\n";
// acquire variables
$span=$_GET['span'];
// if span < 6 build small widget
if($span<6){
 // check personal workflows
 $personal_workflows=NULL;
 // opened
 $personal_workflows_opened=$GLOBALS['db']->countOf("workflows_workflows","addIdAccount='".api_account()->id."' AND status='1'");
 if($personal_workflows_opened){$personal_workflows.="<p>".api_workflows_status(1,TRUE)." ".api_text("widget-workflows-opened").": ".number_format($personal_workflows_opened,0,",",".")."</p>\n";}
 // assigned
 $personal_workflows_assigned=$GLOBALS['db']->countOf("workflows_workflows","addIdAccount='".api_account()->id."' AND status='2'");
 if($personal_workflows_assigned){$personal_workflows.="<p>".api_workflows_status(2,TRUE)." ".api_text("widget-workflows-assigned").": ".number_format($personal_workflows_assigned,0,",",".")."</p>\n";}
 // check pocessable tickets
 $processable_tickets=NULL;
 // opened
 $processable_tickets_where.="idAssigned='".api_account()->id."'";
 foreach(api_account()->companies[api_company()->id]->groups as $group){$processable_tickets_where.=" OR idGroup='".$group->id."'";}
 if(api_accountGroupMember(1)){$processable_tickets_where.=" OR idGroup='0'";}
 $processable_tickets_opened=$GLOBALS['db']->countOf("workflows_tickets","status='1' AND ( ".$processable_tickets_where." )");
 if($processable_tickets_opened){$processable_tickets.="<p>".api_workflows_status(1,TRUE)." ".api_text("widget-tickets-opened").": ".number_format($processable_tickets_opened,0,",",".")."</p>\n";}
 // assigned
 $processable_tickets_assigned=$GLOBALS['db']->countOf("workflows_tickets","status='2' AND idAssigned='".api_account()->id."'");
 if($processable_tickets_assigned){$processable_tickets.="<p>".api_workflows_status(2,TRUE)." ".api_text("widget-tickets-assigned").": ".number_format($processable_tickets_assigned,0,",",".")."</p>\n";}
 // stanbdy
 $processable_tickets_standby=$GLOBALS['db']->countOf("workflows_tickets","status='3' AND idAssigned='".api_account()->id."'");
 if($processable_tickets_standby){$processable_tickets.="<p>".api_workflows_status(3,TRUE)." ".api_text("widget-tickets-standby").": ".number_format($processable_tickets_standby,0,",",".")."</p>\n";}
 // closed
 $processable_tickets_closed=$GLOBALS['db']->countOf("workflows_tickets","status='4' AND idAssigned='".api_account()->id."'");
 if($processable_tickets_closed){$processable_tickets.="<p>".api_workflows_status(4,TRUE)." ".api_text("widget-tickets-closed").": ".number_format($processable_tickets_closed,0,",",".")."</p>\n";}
 // check for null
 if($processable_tickets==NULL){$processable_tickets="<p>".api_text("widget-tickets-null")."</p>\n";}
 // show personal workflows counters
 if($personal_workflows){echo "<h5>".api_text("widget-workflows")."</h5>\n".$personal_workflows;}
 // show processable tickets counters
 echo "<h5>".api_text("widget-tickets")."</h5>\n".$processable_tickets;
}else{
 // if span > 6 build large widget
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
 // query where
 if($g_workflows<>"all"){$query_where=" AND (status<='3' OR addDate>=(NOW()-INTERVAL 15 DAY))";}else{$query_where=NULL;}
 // build workflow table rows
 $workflows=$GLOBALS['db']->query("SELECT * FROM workflows_workflows WHERE addIdAccount='".api_account()->id."'".$query_where." ORDER BY addDate DESC");
 // if span < 8 build large widget
 if($span<8){
  // build tickets table
  $tickets_table=new str_table(api_text("workflows-tr-ticketsUnvalued"),TRUE);
  $tickets_table->addHeader("&nbsp;",NULL,"16");
  $tickets_table->addHeader("!","nowarp text-center","16");
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
   $tables_array[$table]->addField("<a href='../workflows/workflows_view.php?id=".$ticket->idWorkflow."&idTicket=".$ticket->id."' target='_blank'>".api_icon("icon-search")."</a>","nowarp");
   $tables_array[$table]->addField(api_workflows_ticketPriority($ticket->priority,TRUE),"nowarp text-center");
   $tables_array[$table]->addField(api_workflows_referentName($ticket->idWorkflow),"nowarp");
   $tables_array[$table]->addField(api_workflows_categoryName($ticket->idCategory,TRUE,TRUE,TRUE),"nowarp");
   $tables_array[$table]->addField(stripslashes($ticket->subject));
   if($table=="opened"){$tables_array[$table]->addField(api_groupName($ticket->idGroup,TRUE,TRUE),"nowarp text-center");}
  }
  // build personal workflows table
  $workflows_table=new str_table(api_text("workflows-tr-workflowsUnvalued"),FALSE);
  $workflows_table->addHeader("&nbsp;",NULL,"16");
  $workflows_table->addHeader("#","nowarp text-center");
  $workflows_table->addHeader(api_text("workflows-th-timestamp"),"nowarp");
  $workflows_table->addHeader(api_text("workflows-th-subject"),NULL,"100%");
  // cycle personal workflows
  while($workflow=$GLOBALS['db']->fetchNextObject($workflows)){
   $workflows_table->addRow();
   // build workflows table fields
   $workflows_table->addField("<a href='../workflows/workflows_view.php?id=".$workflow->id."' target='_blank'>".api_icon("icon-search")."</a>","nowarp");
   $workflows_table->addField(str_pad($workflow->id,5,"0",STR_PAD_LEFT),"nowarp");
   $workflows_table->addField(api_timestampFormat($workflow->addDate,api_text("datetime")),"nowarp");
   $workflows_table->addField(stripslashes($workflow->subject));
  }
 }else{
  // if span > 8 build large widget
  // build tickets table
  $tickets_table=new str_table(api_text("workflows-tr-ticketsUnvalued"),TRUE);
  $tickets_table->addHeader("&nbsp;",NULL,"16");
  $tickets_table->addHeader("#","nowarp text-center");
  $tickets_table->addHeader("!","nowarp text-center","16");
  $tickets_table->addHeader(api_text("workflows-th-timestamp"),"nowarp");
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
   $tables_array[$table]->addField("<a href='../workflows/workflows_view.php?id=".$ticket->idWorkflow."&idTicket=".$ticket->id."' target='_blank'>".api_icon("icon-search")."</a>","nowarp");
   $tables_array[$table]->addField($ticket->number,"nowarp");
   $tables_array[$table]->addField(api_workflows_ticketPriority($ticket->priority,TRUE),"nowarp text-center");
   $tables_array[$table]->addField(api_timestampFormat($ticket->addDate,api_text("datetime")),"nowarp");
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
  // cycle personal workflows
  while($workflow=$GLOBALS['db']->fetchNextObject($workflows)){
   $workflows_table->addRow();
   // build workflows table fields
   $workflows_table->addField("<a href='../workflows/workflows_view.php?id=".$workflow->id."' target='_blank'>".api_icon("icon-search")."</a>","nowarp");
   $workflows_table->addField(str_pad($workflow->id,5,"0",STR_PAD_LEFT),"nowarp");
   $workflows_table->addField(api_workflows_ticketPriority($workflow->priority,TRUE),"nowarp text-center");
   $workflows_table->addField(api_timestampFormat($workflow->addDate,api_text("datetime")),"nowarp");
   $workflows_table->addField(api_workflows_categoryName($workflow->idCategory,TRUE,TRUE,TRUE),"nowarp");
   $workflows_table->addField(stripslashes($workflow->subject));
   $workflows_table->addField(api_workflows_status($workflow->status),"nowarp text-right'");
  }
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
}
echo "<span class='pull-right'>\n";
echo "<a href='../workflows/index.php'>".api_text("widget-showAll")."</a>\n";
echo "</span>\n";
echo "<br>\n</div>\n";
?>