<?php
/* -------------------------------------------------------------------------- *\
|* -[ Workflows - Logs and Conditions ]-------------------------------------- *|
\* -------------------------------------------------------------------------- */
require_once("api.inc.php");

/* -[ Workflow Owner ]---------------------------------------------------- */
// @integer $idAccount : account id
// @integer $idWorkflow : workflow id
function workflows_conditions_workflowOwner($idAccount,$idWorkflow){
 $workflow=api_workflows_workflow($idWorkflow);
 if($workflow->addIdAccount==$idAccount){return TRUE;}else{return FALSE;}
}

/* -[ Ticket Owner ]---------------------------------------------------- */
// @integer $idAccount : account id
// @integer $idTicket : ticket id
function workflows_conditions_ticketOwner($idAccount,$idTicket){
 $ticket=api_workflows_ticket($idTicket);
 if($ticket->addIdAccount==$idAccount){return TRUE;}else{return FALSE;}
}

/* -[ Ticket Processable ]---------------------------------------------------- */
// @integer $idAccount : account id
// @integer $idTicket : ticket id
function workflows_conditions_ticketProcessable($idAccount,$idTicket){
 return api_workflows_ticketProcessPermission($idTicket,$idAccount,FALSE);
}


?>