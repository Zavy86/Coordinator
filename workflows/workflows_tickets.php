<?php
/* -------------------------------------------------------------------------- *\
|* -[ Workflows - Tickets ]-------------------------------------------------- *|
\* -------------------------------------------------------------------------- */
$checkPermission="workflows_view";
require_once("template.inc.php");
function content(){
 // definitions
 $details_modals_array=array();
 // acquire variabled
 $g_search=$_GET['q'];
 // show filters
 echo $GLOBALS['navigation']->filtersText();
 // build tickets table
 $tickets_table=new str_table(api_text("flows-tr-ticketsUnvalued"),TRUE);
 $tickets_table->addHeader("&nbsp;",NULL,"16");
 $tickets_table->addHeader(api_text("flows-th-idTicket"),"nowarp",NULL,"id");
 $tickets_table->addHeader("&nbsp;",NULL,"16");
 $tickets_table->addHeader(api_text("flows-th-timestamp"),"nowarp",NULL,"addDate");
 //$tickets_table->addHeader(api_text("flows-th-sla"),"nowarp text-center",NULL);
 $tickets_table->addHeader("!","nowarp text-center",NULL,"priority");
 $tickets_table->addHeader(api_text("flows-th-account"),"nowarp");
 $tickets_table->addHeader(api_text("flows-th-category"),"nowarp");
 $tickets_table->addHeader(api_text("flows-th-subject"),NULL,"100%","subject");
 $tickets_table->addHeader(api_text("flows-th-assigned"),"nowarp");
 $tickets_table->addHeader(api_text("flows-th-group"),"nowarp text-center");
 $tickets_table->addHeader("&nbsp;",NULL,"16");
 // build query
 $query_table="workflows_tickets";
 // fields
 $query_fields="workflows_tickets.*";
 // where
 $query_where=" ( ".$GLOBALS['navigation']->filtersParameterQuery("status","1","workflows_tickets.status");
 $query_where.=" AND ".$GLOBALS['navigation']->filtersParameterQuery("idCategory","1","workflows_tickets.idCategory");
 $query_where.=" AND ".$GLOBALS['navigation']->filtersParameterQuery("addDate","1","workflows_tickets.addDate");
 $query_where.=" AND ".$GLOBALS['navigation']->filtersParameterQuery("addIdAccount","1","workflows_tickets.addIdAccount");
 $query_where.=" AND ".$GLOBALS['navigation']->filtersParameterQuery("idAssigned","1","workflows_tickets.idAssigned")." ) ";
 // search
 if(strlen($g_search)){
  // join
  $query_join=" LEFT JOIN workflows_workflows ON workflows_workflows.id=workflows_tickets.idWorkflow";
  $query_join.=" LEFT JOIN workflows_tickets_notes ON workflows_tickets_notes.idTicket=workflows_tickets.id";
  // where
  $query_where.=" AND ( workflows_tickets.subject LIKE '%".$g_search."%'";
  $query_where.=" OR workflows_workflows.subject LIKE '%".$g_search."%'";
  $query_where.=" OR workflows_workflows.description LIKE '%".$g_search."%'";
  $query_where.=" OR workflows_tickets_notes.note LIKE '%".$g_search."%' )";
 }
 // if not admin show only assignable tickets
 // correggere mettendo impostazione come permesso   <----------------------------------------
 if(!api_accountGroupMember(1)){
  // only assignable tickets
  $query_where.=" AND ( workflows_tickets.idAssigned='".api_account()->id."'";
  foreach(api_account()->companies[api_company()->id]->groups as $group){$query_where.=" OR idGroup='".$group->id."'";}
  $query_where.=" OR workflows_tickets.addIdAccount='".api_account()->id."' )";
 }
 // order tickets
 $query_order=api_queryOrder("addDate DESC");
 // pagination
 $pagination=new str_pagination($query_table.$query_join,$query_where,$GLOBALS['navigation']->filtersGet());
 $query_limit=$pagination->queryLimit();
 // acquire tickets
 $tickets=$GLOBALS['db']->query("SELECT ".$query_fields." FROM ".$query_table.$query_join." WHERE ".$query_where.$query_order.$query_limit);
 while($ticket=api_workflows_ticket($GLOBALS['db']->fetchNextObject($tickets),FALSE)){
  // definitions
  if(!$ticket->idAssigned){$ticket->idAssigned=0;}
  // details modal windows
  $details_modal=api_workflows_ticketDetailsModal($ticket);
  $details_modals_array[]=$details_modal;
  // check urged
  if($ticket->urged){$tr_class="error";}else{$tr_class=NULL;}
  // build row
  $tickets_table->addRow($tr_class);
  // build tickets table fields
  $tickets_table->addField("<a href='workflows_view.php?id=".$ticket->idWorkflow."&idTicket=".$ticket->id."'>".api_icon("icon-search")."</a>","nowarp");
  $tickets_table->addField(str_pad($ticket->idWorkflow,5,"0",STR_PAD_LEFT)."-".str_pad($ticket->id,5,"0",STR_PAD_LEFT),"nowarp");
  $tickets_table->addField(api_workflows_status($ticket->status,TRUE,$ticket->solved),"nowarp text-center");
  $tickets_table->addField(api_timestampFormat($ticket->addDate,api_text("datetime")),"nowarp");
  //$tickets_table->addField(api_workflows_ticketSLA($ticket),"nowarp text-center");
  $tickets_table->addField($ticket->priority,"nowarp text-center");
  $tickets_table->addField(api_workflows_referentName($ticket->idWorkflow),"nowarp");
  $tickets_table->addField(api_workflows_categoryName($ticket->idCategory),"nowarp");
  $tickets_table->addField(stripslashes($ticket->subject));
  $tickets_table->addField(api_account($ticket->idAssigned)->firstname,"nowarp text-right");
  $tickets_table->addField(api_groupName($ticket->idGroup,TRUE,TRUE),"nowarp text-center");
  $tickets_table->addField($details_modal->link(api_icon("icon-list")),"nowarp text-center");
 }
 // show tickets table
 if(is_object($tickets_table)){$tickets_table->render();}
 // show the pagination
 if(is_object($pagination)){$pagination->render();}
}
?>