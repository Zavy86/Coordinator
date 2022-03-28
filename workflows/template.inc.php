<?php
/* -------------------------------------------------------------------------- *\
|* -[ Workflows - Template ]------------------------------------------------- *|
\* -------------------------------------------------------------------------- */
require_once("module.inc.php");
require_once("../core/api.inc.php");
api_loadModule();
// print header
$html->header(api_text("module-title"),$module_name);
// acquire variables
$g_id=$_GET['id'];
if(!$g_id){$g_id=0;}
$g_idWorkflow=$_GET['idWorkflow'];
if(!$g_idWorkflow){$g_idWorkflow=0;}
if($g_idWorkflow>0){$g_id=$g_idWorkflow;}
// get workflow object
$workflow=api_workflows_workflow($g_id,FALSE);
// get ticket object
$ticket=api_workflows_ticket($_GET['idTicket'],FALSE);
// build navigation
global $navigation;
$navigation=new str_navigation((api_baseName()=="workflows_tickets.php"||api_baseName()=="workflows_flows_list.php"||(api_baseName()=="workflows_search.php" && $_GET['idCategory']>0)?TRUE:FALSE),"idCategory");
// workflows
$navigation->addTab(api_text("nav-workflows"),"workflows.php");
$navigation->addTab(api_text("nav-history"),"workflows_tickets.php");
// operations
if($workflow->id){
 $navigation->addTab(api_text("nav-operations"),NULL,NULL,"active");
 if($workflow->status<4 && $workflow->addIdAccount==api_account()->id){$navigation->addSubTab(api_text("nav-urge"),"submit.php?act=workflow_urge&id=".$workflow->id);}
 if($workflow->status==1 && $workflow->addIdAccount==api_account()->id){$navigation->addSubTab(api_text("nav-close"),"submit.php?act=workflow_close&id=".$workflow->id,NULL,NULL,TRUE,"_self",api_text("nav-close-confirm"));}
 if(api_accountGroupMember(1)){$navigation->addSubTab(api_text("nav-edit"),"workflows_edit.php?id=".$workflow->id);}
 $navigation->addSubTab(api_text("nav-sendmail"),"workflows_sendmail.php?idWorkflow=".$workflow->id);
 $navigation->addSubTab(api_text("nav-addTicket"),"workflows_view.php?id=".$workflow->id."&act=addTicket");
 if(api_checkPermission("cr-authorizations","authorizations_edit") && file_exists("../cr-authorizations/index.php")){$navigation->addSubTab(api_text("nav-authorization"),"../cr-authorizations/authorizations_edit.php?idTicket=".$workflow->id);}
 if($ticket->id && api_workflows_ticketProcessPermission($ticket)){
  $navigation->addSubTabDivider();
  $navigation->addSubTabHeader(api_text("nav-ticket",$ticket->number));
  if($ticket->status==1){
   $navigation->addSubTab(api_text("nav-assign"),"submit.php?act=ticket_assign&idWorkflow=".$workflow->id."&idTicket=".$ticket->id);
  }elseif($ticket->status>1 && $ticket->status<4){
   $navigation->addSubTab(api_text("nav-process"),"workflows_view.php?id=".$workflow->id."&idTicket=".$ticket->id."&act=editTicket");
  }elseif($ticket->status==5){
   $navigation->addSubTab(api_text("nav-unlock"),"workflows_view.php?id=".$workflow->id."&idTicket=".$ticket->id."&act=editTicket");
  }else{
   $navigation->addSubTab(api_text("nav-reopen"),"workflows_view.php?id=".$workflow->id."&idTicket=".$ticket->id."&act=editTicket");
  }
  if($ticket->status<4 && $ticket->typology==1 && api_accountGroupMember(1)){
   $navigation->addSubTab(api_text("nav-clone"),"workflows_view.php?id=".$workflow->id."&idTicket=".$ticket->id."&act=cloneTicket");
  }
 }
}
// new workflow
if(api_baseName()=="workflows_add.php"){$class="active";}else{$class=NULL;}
$navigation->addTab(api_text("nav-open"),"../helpdesk/tickets_open.php",NULL,$class);
// selected
if(api_baseName()=="workflows_flows_list.php" ||
   api_baseName()=="workflows_flows_view.php" ||
   api_baseName()=="workflows_flows_edit.php" ||
   api_baseName()=="workflows_categories.php"){
 $class="active";
}else{
 $class=NULL;
}
// workflow by mail
if(api_checkPermission("workflows","workflows_mails")){
 if(api_baseName()<>"workflows_mails_list.php"){$mails=$GLOBALS['db']->countOfAll("workflows_mails");}
 if($mails){$mails="&nbsp;<span class='badge badge-warning'>".$mails."</span>";}else{$mails=" ";}
 $navigation->addTab(api_text("nav-ticket-mails",$mails),"workflows_mails_list.php");
}
// administration
if(api_checkPermission("workflows","workflows_admin")){
 $navigation->addTab(api_text("nav-administration"),NULL,NULL,$class);
 $navigation->addSubTab(api_text("nav-list"),"workflows_flows_list.php");
 $navigation->addSubTab(api_text("nav-add"),"workflows_flows_edit.php");
 $navigation->addSubTab(api_text("nav-categories"),"workflows_categories.php");
}
// filters
if(api_baseName()=="workflows_tickets.php"){
 // status filter
 $navigation->addFilter("multiselect","status",api_text("filter-status"),array(1=>api_text("filter-opened"),2=>api_text("filter-assigned"),3=>api_text("filter-standby"),4=>api_text("filter-closed"),5=>api_text("filter-locked")));
}
if(api_baseName()=="workflows_tickets.php" || api_baseName()=="workflows_flows_list.php"){
 // idCategory
 $categories_array=array();
 $categories=$GLOBALS['db']->query("SELECT * FROM workflows_categories WHERE idCategory='0' ORDER BY name ASC");
 while($category=$GLOBALS['db']->fetchNextObject($categories)){
  $categories_array[$category->id]=api_workflows_categoryName($category->id,TRUE);
  $subcategories=$GLOBALS['db']->query("SELECT * FROM workflows_categories WHERE idCategory='".$category->id."' ORDER BY name ASC");
  while($subcategory=$GLOBALS['db']->fetchNextObject($subcategories)){
   $categories_array[$subcategory->id]=api_workflows_categoryName($subcategory->id,TRUE);
   $subsubcategories=$GLOBALS['db']->query("SELECT * FROM workflows_categories WHERE idCategory='".$subcategory->id."' ORDER BY name ASC");
   while($subsubcategory=$GLOBALS['db']->fetchNextObject($subsubcategories)){
    $categories_array[$subsubcategory->id]=api_workflows_categoryName($subsubcategory->id,TRUE);
   }
  }
 }
 $navigation->addFilter("multiselect","idCategory",api_text("filter-category"),$categories_array,"input-xlarge");
 // date range
 $navigation->addFilter("daterange","addDate",api_text("filter-addDate"));
 // addIdAccount
 $accounts_array=array(""=>ucfirst(api_text("all")));
 $accounts=$GLOBALS['db']->query("SELECT * FROM accounts_accounts WHERE id>'1' ORDER BY name ASC");
 while($account=$GLOBALS['db']->fetchNextObject($accounts)){
  $accounts_array[$account->id]=api_account($account->id)->name;
 }
 $navigation->addFilter("select","addIdAccount",api_text("filter-addIdAccount"),$accounts_array,"input-xlarge");
 // idAssigned
 $navigation->addFilter("select","idAssigned",api_text("filter-idAssigned"),$accounts_array,"input-xlarge");
}
// show navigation
$navigation->render();
// check permissions before displaying module
if($checkPermission==NULL){content();}else{if(api_checkPermission($module_name,$checkPermission,TRUE)){content();}}
// print footer
$html->footer();
?>