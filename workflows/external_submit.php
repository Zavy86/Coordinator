<?php
/* -------------------------------------------------------------------------- *\
|* -[ Workflows - External Submit ]------------------------------------------ *|
\* -------------------------------------------------------------------------- */
$dontCheckSession=TRUE;
require_once('../core/api.inc.php');
require_once('api.inc.php');
api_loadLocaleFile("./");
$act=$_GET['act'];
switch($act){
 // ticket
 case "ticket_external":ticket_external();break;
 case "ticket_authorize":ticket_authorize();break;
 // default
 default:
  $alert="?alert=submitFunctionNotFound&alert_class=alert-warning&act=".$act;
  header("location: index.php".$alert);
}


/* -[ Ticket External ]------------------------------------------------------ */
function ticket_external(){
 // acquire variables
 $g_idTicket=$_GET['idTicket'];
 if(!$g_idTicket){$g_idTicket=0;}
 $g_idWorkflow=$_GET['idWorkflow'];
 if(!$g_idWorkflow){$g_idWorkflow=0;}
 $g_hash=$_GET['hash'];
 if(!$g_hash){$g_hash=NULL;}
 $g_solved=$_GET['solved'];
 if(!$g_solved){$g_solved=0;}
 $ticket=$GLOBALS['db']->queryUniqueObject("SELECT * FROM workflows_tickets WHERE id='".$g_idTicket."' AND idWorkflow='".$g_idWorkflow."' AND typology='2'");
 // check
 if($ticket->id>0){
  // check hash
  if(strlen($g_hash)>0 && $g_hash===$ticket->hash){
   $query="UPDATE workflows_tickets SET
    hash='".md5(api_randomString(32))."',
    status='4',
    solved='".$g_solved."',
    idAssigned='".$_SESSION['account']->id."',
    updDate='".api_now()."',
    assDate='".api_now()."',
    endDate='".api_now()."'
    WHERE id='".$g_idTicket."'";
   // execute query
   $GLOBALS['db']->execute($query);
   if($g_solved>0){
    $locked_tickets=$GLOBALS['db']->query("SELECT * FROM workflows_tickets WHERE requiredTicket='".$g_idTicket."' AND status='5'");
    while($locked_ticket=$GLOBALS['db']->fetchNextObject($locked_tickets)){
     // send notification
     api_workflows_notifications($locked_ticket);
    }
    $GLOBALS['db']->execute("UPDATE workflows_tickets SET status='1',addDate='".api_now()."' WHERE requiredTicket='".$g_idTicket."' AND status='5'");
   }
   // check if all activities are completed
   if($GLOBALS['db']->countOf("workflows_tickets","idWorkflow='".$g_idWorkflow."' AND (status<'4' OR status='5')")==0){
    // close workflow
    $GLOBALS['db']->execute("UPDATE workflows_workflows SET status='4',endDate='".api_now()."' WHERE id='".$g_idWorkflow."'");
    // notification

    // -----!!!----- notifica che il workflow è chiuso

   }
   echo "OK, THANKS";
  }else{
   echo "ERROR, INVALID HASH";
  }
 }else{
  echo "ERROR, INVALID ACTIVITY";
 }
}

/* -[ Ticket Authorize ]----------------------------------------------------- */
function ticket_authorize(){
 // acquire variables
 $g_idTicket=$_GET['idTicket'];
 if(!$g_idTicket){$g_idTicket=0;}
 $g_idWorkflow=$_GET['idWorkflow'];
 if(!$g_idWorkflow){$g_idWorkflow=0;}
 $g_hash=$_GET['hash'];
 if(!$g_hash){$g_hash=NULL;}
 $g_authorization=$_GET['authorization'];
 if(!$g_authorization){$g_authorization=0;}
 $ticket=$GLOBALS['db']->queryUniqueObject("SELECT * FROM workflows_tickets WHERE id='".$g_idTicket."' AND idWorkflow='".$g_idWorkflow."' AND typology='3'");
 // check
 if($ticket->id>0){
  // check hash
  if(strlen($g_hash)>0 && $g_hash===$ticket->hash){
   $query="UPDATE workflows_tickets SET
    hash='".md5(api_randomString(32))."',
    status='4',
    solved='1',
    approved='".$g_authorization."',
    idAssigned='".$_SESSION['account']->id."',
    updDate='".api_now()."',
    assDate='".api_now()."',
    endDate='".api_now()."'
    WHERE id='".$g_idTicket."'";
   // execute query
   $GLOBALS['db']->execute($query);
   if($g_authorization){
    $locked_tickets=$GLOBALS['db']->query("SELECT * FROM workflows_tickets WHERE requiredTicket='".$g_idTicket."' AND status='5'");
    while($locked_ticket=$GLOBALS['db']->fetchNextObject($locked_tickets)){
     // send notification
     api_workflows_notifications($locked_ticket);
    }
    $GLOBALS['db']->execute("UPDATE workflows_tickets SET status='1',addDate='".api_now()."' WHERE requiredTicket='".$g_idTicket."' AND status='5'");

    // check if all activities are completed
    if($GLOBALS['db']->countOf("workflows_tickets","idWorkflow='".$g_idWorkflow."' AND (status<'4' OR status='5')")==0){
     // close workflow
     $GLOBALS['db']->execute("UPDATE workflows_workflows SET status='4',endDate='".api_now()."' WHERE id='".$g_idWorkflow."'");
     // notification

     // -----!!!----- notifica che il workflow è chiuso

    }

    echo "OK, AUTHORIZED";
   }else{

    // chiude tutti i ticket del workflow non ancora chiusi
    $GLOBALS['db']->execute("UPDATE workflows_tickets SET status='4',solved='2',endDate='".api_now()."' WHERE idWorkflow='".$g_idWorkflow."' AND status<>'4'");

    // chiude il workflow come non risolto inserendo nelle note non autorizzato
    $GLOBALS['db']->execute("UPDATE workflows_workflows SET status='4',endDate='".api_now()."' WHERE id='".$g_idWorkflow."'");

    echo "OK, NOT AUTHORIZED";
   }
  }else{
   echo "ERROR, INVALID HASH";
  }
 }else{
  echo "ERROR, INVALID ACTIVITY";
 }
}
