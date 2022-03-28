<?php
/* -------------------------------------------------------------------------- *\
|* -[ Workflows - Submit ]--------------------------------------------------- *|
\* -------------------------------------------------------------------------- */
require_once('../core/api.inc.php');
require_once('api.inc.php');
api_loadLocaleFile("./");
$act=$_GET['act'];
switch($act){
 // workflows
 case "workflow_save":workflow_save();break;
 case "workflow_urge":workflow_urge();break;
 case "workflow_close":workflow_close();break;
 case "workflow_update":workflow_update();break;
 case "workflow_sendmail":workflow_sendmail();break;
 // tickets
 case "ticket_save":ticket_save();break;
 case "ticket_assign":ticket_assign();break;
 case "ticket_process":ticket_process();break;
 case "ticket_clone":ticket_clone();break;
 case "ticket_note":ticket_note();break;
 case "ticket_note_delete":ticket_note_delete();break;
 // mails
 case "mail_delete":mail_delete();break;
 // categories
 case "category_save":category_save();break;
 // flows
 case "flow_save":flow_save();break;
 case "flow_field_save":flow_field_save();break;
 case "flow_field_move_up":flow_field_move("up");break;
 case "flow_field_move_down":flow_field_move("down");break;
 case "flow_field_delete":flow_field_delete();break;
 case "flow_action_save":flow_action_save();break;
 case "flow_action_delete":flow_action_delete();break;
 // attachments
 case "attachments_download":attachments_download();break;
 // default
 default:
  $alert="?alert=submitFunctionNotFound&alert_class=alert-warning&act=".$act;
  exit(header("location: index.php".$alert));
}


/* -[ Workflow Save ]--------------------------------------------------------- */
function workflow_save(){
 if(!api_checkPermission("workflows","workflows_add")){api_die("accessDenied");}
 // get object
 $flow=api_workflows_flow($_GET['idFlow'],FALSE);
 // acquire variables
 $p_idMail=$_POST['idMail'];
 $p_idCategory=$_POST['idCategory'];
 $p_typology=$_POST['typology'];
 $p_subject=addslashes($_POST['subject']);
 $p_priority=$_POST['priority'];
 // assign flow variables
 if($flow->id){
  $idFlow=$flow->id;
  $sla=$flow->sla;
 }else{
  $idFlow='';
  $sla=480;
 }
 // build query
 $query="INSERT INTO workflows_workflows
  (idCategory,idFlow,typology,subject,priority,sla,status,addDate,addIdAccount) VALUES
  ('".$p_idCategory."','".$idFlow."','".$p_typology."','".$p_subject."','".$p_priority."',
   '".$sla."','1','".api_now()."','".$_SESSION['account']->id."')";
 // execute query
 $GLOBALS['db']->execute($query);
 // set id to last inserted id
 $q_idWorkflow=$GLOBALS['db']->lastInsertedId();
 // delete mail if exist
 if($p_idMail){$GLOBALS['db']->execute("DELETE FROM workflows_mails WHERE id='".$p_idMail."'");}
 // alert
 $alert="?alert=workflowCreated&alert_class=alert-success";
 // get fields
 if(!workflow_get_fields($q_idWorkflow,$flow->id)){$alert="?alert=workflowError&alert_class=alert-error";}
 // process actions
 if(!workflow_process_actions($q_idWorkflow,$flow->id)){$alert="?alert=workflowError&alert_class=alert-error";}
 // redirect
 if($p_idMail && $q_idWorkflow){
  exit(header("location: workflows_view.php".$alert."&id=".$q_idWorkflow));
 }else{
  exit(header("location: workflows.php".$alert));
 }
}

/* -[ Workflow Urge ]-------------------------------------------------------- */
function workflow_urge(){
 // get workflow object
 $workflow=api_workflows_workflow($_GET['id'],TRUE);
 // check
 if($workflow->id>0){
  // execute query
  $tickets=$GLOBALS['db']->query("SELECT * FROM workflows_tickets WHERE idWorkflow='".$workflow->id."' AND status<'4'");
  while($ticket=api_workflows_ticket($GLOBALS['db']->fetchNextObject($tickets))){
   api_log(API_LOG_NOTICE,"workflows","ticketUrged",
    "{logs_workflows_ticketUrged|".$ticket->number."|".$ticket->subject."|".$workflow->description."\n\nNote: ".$workflow->note."}",
    $ticket->id,"workflows/workflows_view.php?id=".$workflow->id."&idTicket=".$ticket->id);
   // send notification
   api_workflows_notifications($ticket->id);
  }
  $GLOBALS['db']->execute("UPDATE workflows_tickets SET urged='1' WHERE idWorkflow='".$workflow->id."' AND status<'4'");
  // alert
  $alert="&alert=workflowUrged&alert_class=alert-success";
 }else{
  $alert="&alert=workflowError&alert_class=alert-error";
 }
 // redirect
 exit(header("location: workflows_view.php?id=".$workflow->id.$alert));
}

/* -[ Workflow Close ]------------------------------------------------------- */
function workflow_close(){
 // get workflow object
 $workflow=api_workflows_workflow($_GET['id'],TRUE);
 // check
 if($workflow->id>0){
  // execute query
  $tickets=$GLOBALS['db']->query("SELECT * FROM workflows_tickets WHERE idWorkflow='".$workflow->id."' AND status<'4'");
  while($ticket=api_workflows_ticket($GLOBALS['db']->fetchNextObject($tickets))){
   api_log(API_LOG_NOTICE,"workflows","ticketClosed",
    "{logs_workflows_ticketClosed|".$ticket->number."|".$ticket->subject."|".$workflow->description."\n\nNote: ".$workflow->note."}",
    $ticket->id,"workflows/workflows_view.php?id=".$workflow->id."&idTicket=".$ticket->id);
   // send notification
   api_workflows_notifications($ticket->id);
  }
  // close tickets
  $GLOBALS['db']->execute("UPDATE workflows_tickets SET status='4',solved='2',idGroup='".api_company()->mainGroup."',idAssigned='".api_account()->id."',assDate='".api_now()."',updDate='".api_now()."',endDate='".api_now()."' WHERE idWorkflow='".$workflow->id."' AND status<'4'");
  // close workflow
  $GLOBALS['db']->execute("UPDATE workflows_workflows SET status='4',endDate='".api_now()."' WHERE id='".$workflow->id."'");
  // log event
  api_log(API_LOG_NOTICE,"workflows","workflowClosed",
   "{logs_workflows_workflowClosed|".$workflow->number."|".$workflow->subject."}",
   $workflow->id,"workflows/workflows_view.php?id=".$workflow->id);
  // alert
  $alert="?alert=workflowClosed&alert_class=alert-success";
 }else{
  $alert="?alert=workflowError&alert_class=alert-error";
 }
 // redirect
 exit(header("location: workflows.php".$alert));
}

/* -[ Workflow Update ]------------------------------------------------------ */
function workflow_update(){
 //if(!api_checkPermission("workflows","workflows_add")){api_die("accessDenied");}
 // get workflow object
 $workflow=api_workflows_workflow($_GET['id'],TRUE);
 // acquire variables
 $p_idCategory=$_POST['idCategory'];
 $p_idFlow=$_POST['idFlow'];
 $p_typology=$_POST['typology'];
 $p_subject=addslashes($_POST['subject']);
 $p_priority=$_POST['priority'];
 $p_description=addslashes($_POST['description']);
 $p_note=addslashes($_POST['note']);
 $p_tickets=$_POST['tickets'];
 // assign flow variables
 if($workflow->id>0){
  // build query
  $query="UPDATE workflows_workflows SET
   idCategory='".$p_idCategory."',
   idFlow='".$p_idFlow."',
   typology='".$p_typology."',
   subject='".$p_subject."',
   priority='".$p_priority."',
   description='".$p_description."',
   note='".$p_note."'
   WHERE id='".$workflow->id."'";
  // execute query
  $GLOBALS['db']->execute($query);
  // alert
  $alert="&alert=workflowUpdated&alert_class=alert-success";
  // if need to open a new tickets
  if($p_tickets==1){
   // close all opened tickets
   foreach($workflow->tickets as $ticket){
    $GLOBALS['db']->execute("UPDATE workflows_tickets SET status='4',solved='2',endDate='".api_now()."' WHERE status<>'4' AND id='".$ticket->id."'");
   }
   // open standard ticket
   $hash=md5(api_randomString(32));
   $hostname=api_workflows_hostName();
   // get group id by selected category
   $idGroup=api_workflows_categoryGroup($p_idCategory);
   // build query
   $query="INSERT INTO workflows_tickets
    (idWorkflow,idCategory,typology,hash,subject,idGroup,difficulty,priority,
     slaAssignment,slaClosure,status,solved,approved,hostname,addDate,addIdAccount) VALUES
    ('".$workflow->id."','".$p_idCategory."','1','".$hash."','".$p_subject."',
     '".$idGroup."','2','".$p_priority."','0','480','1','0','0','".$hostname."',
     '".api_now()."','".$workflow->addIdAccount."')";
   // execute query
   $GLOBALS['db']->execute($query);
   // set id to last inserted id
   $q_idTicket=$GLOBALS['db']->lastInsertedId();
   // send notification
   api_workflows_notifications($q_idTicket);
  }else{
   // update category
   $GLOBALS['db']->execute("UPDATE workflows_tickets SET idCategory='".$p_idCategory."' WHERE idWorkflow='".$workflow->id."'");
  }
 }else{
  $alert="&alert=workflowError&alert_class=alert-error";
 }
 // redirect
 exit(header("location: workflows_view.php?id=".$workflow->id.$alert));
}

/* -[ Workflow Sendmail ]---------------------------------------------------- */
function workflow_sendmail(){
 // get objects
 $workflow=api_workflows_workflow($_GET['id'],TRUE);
 if(!$workflow->id){echo api_text("workflowNotFound");return FALSE;}
 // acquire variables
 $p_to=$_POST['to'];
 $p_cc=$_POST['cc'];
 $p_subject=$_POST['subject'];
 $p_message=$_POST['message'];
 // sendmail
 $sendmail=api_mailer($p_to,$p_message,$p_subject,FALSE,NULL,NULL,$p_cc);
 if($sendmail){
  $alert="&alert=sendmailSuccess&alert_class=alert-success";
 }else{
  $alert="&alert=sendmailError&alert_class=alert-error";
 }
 // redirect
 header("location: workflows_view.php?id=".$workflow->id.$alert);
}

/* -[ Workflow Get Field ]----------------------------------------------------- */
function workflow_get_fields($idWorkflow,$idFlow=0){
 if(!api_checkPermission("workflows","workflows_add")){api_die("accessDenied");}
 if(!$idWorkflow){return FALSE;}
 // definitions
 $description=NULL;
 // acquire varaibles
 $p_referent=addslashes($_POST['referent']);
 $p_phone=addslashes($_POST['phone']);
 $p_note=addslashes($_POST['note']);
 $p_file=$_FILES['add_file'];
 // get flow fields
 if($idFlow>0){
  $fields=$GLOBALS['db']->query("SELECT * FROM workflows_fields WHERE idFlow='".$idFlow."' ORDER BY position ASC");
  while($field=$GLOBALS['db']->fetchNextObject($fields)){
   // field name to show in description
   if(strlen($field->label)>0){$field->nameShow=$field->label;}
   else{$field->nameShow=$field->name;}
   // --- da rifare magari in un api
   $value=NULL;
   // prepare options array
   $field->options=api_workflows_flowFieldOptions($field);
   // acquire field values by typology
   switch($field->typology){
    // multiselect have array values
    case "multiselect":
     $values=NULL;
     if(is_array($_POST[$field->name])){
      foreach($_POST[$field->name] as $g_option){
       $values.=", ".$field->options[$g_option]->label;
      }
     }
     $value=substr($values,2);
     break;
    // checkbox have array text values
    case "checkbox":
     $values=NULL;
     if(substr($field->name,-2)=="[]"){$field->name=substr($field->name,0,-2);}
     if(!is_array($_POST[$field->name])){$_POST[$field->name]=array($_POST[$field->name]);}
     foreach($_POST[$field->name] as $g_option){
      $values.=", ".$field->options[$g_option]->label;
     }
     $value=substr($values,2);
     break;
    // radio have text value
    case "radio":
     if($_POST[$field->name]<>NULL){$value=$field->options[$_POST[$field->name]]->label;}
     break;
    // select value is in array
    case "select":
     if($_POST[$field->name]<>NULL){$value=$field->options[$_POST[$field->name]]->label;}
     break;
    // range values
    case "range":
    case "daterange":
    case "datetimerange":
     if($_POST[$field->name."_from"]<>NULL){$value=api_text("form-range-from")." ".$_POST[$field->name."_from"]." ";}
     if($_POST[$field->name."_to"]<>NULL){$value.=api_text("form-range-to")." ".$_POST[$field->name."_to"];}
     break;
    case "file":
     $file=api_file_upload($_FILES[$field->name],"workflows_attachments",NULL,NULL,NULL,NULL,FALSE,NULL,TRUE,"workflows");
     if($file->id){
      $value=addslashes("<a href='submit.php?act=attachments_download&id=".$file->id."'>".$file->name."</a>");
     }
     break;
    default:
     $value=addslashes($_POST[$field->name]);
   }
   // ---
   $description.=$field->nameShow.": ".$value."\n\n";
  }
 }
 // acquire variables
 $description.=api_text("add-ff-referent").": ".$p_referent."\n\n";
 $description.=api_text("add-ff-phone").": ".$p_phone;

 $file=api_file_upload($p_file,"workflows_attachments",NULL,NULL,NULL,NULL,FALSE,NULL,TRUE,"workflows");
 if($file->id){$description.="\n\n".api_text("add-ff-file").": ".addslashes("<a href='http://".$_SERVER['SERVER_NAME'].$GLOBALS['dir']."/workflows/submit.php?act=attachments_download&id=".$file->id."'>".$file->name."</a>");}

 // execute query
 $GLOBALS['db']->execute("UPDATE workflows_workflows SET description='".addslashes($description)."',note='".addslashes($p_note)."' WHERE id='".$idWorkflow."'");
 // log event
 $workflow=api_workflows_workflow($idWorkflow);
 api_log(API_LOG_NOTICE,"workflows","workflowCreated",
  "{logs_workflows_workflowCreated|".$workflow->number."|".$workflow->subject."|".$workflow->description."\n\nNote: ".$workflow->note."}",
  $idWorkflow,"workflows/workflows_view.php?id=".$workflow->id);
 return TRUE;
}

/* -[ Workflow Process Actions ]----------------------------------------------- */
function workflow_process_actions($idWorkflow,$idFlow=0){
 if(!api_checkPermission("workflows","workflows_add")){api_die("accessDenied");}
 if(!$idWorkflow){return FALSE;}
 // check flow
 if($idFlow>0){
  // get flow actions
  $actions=$GLOBALS['db']->query("SELECT * FROM workflows_actions WHERE idFlow='".$idFlow."' ORDER BY requiredAction ASC,subject ASC");
  while($action=$GLOBALS['db']->fetchNextObject($actions)){
   $execute=TRUE;
   if($action->conditionedField>0){
    $execute=FALSE;
    $value=NULL;
    // check condition
    $field=$GLOBALS['db']->queryUniqueObject("SELECT * FROM workflows_fields WHERE id='".$action->conditionedField."'");
    if($field->id>0){
     $value=addslashes($_POST[$field->name]);
     if($value==$action->conditionedValue){$execute=TRUE;}
    }
   }
   // execute
   if($execute){
    // assign variables values
    $p_idCategory=$_POST['idCategory'];
    $requiredAction=$action->id;
    $typology=$action->typology;
    if($typology<>1){$hash=md5(api_randomString(32));}else{$hash=NULL;}
    $mail=addslashes(api_workflows_replaceTagCodes($action->mail));
    $subject=addslashes(api_workflows_replaceTagCodes($action->subject));
    $idGroup=$action->idGroup;
    $idAssigned=$action->idAssigned;
    $difficulty=$action->difficulty;
    $priority=$action->priority;
    $slaAssignment=$action->slaAssignment;
    $slaClosure=$action->slaClosure;
    $hostname=api_workflows_hostName();
    if($action->requiredAction>0){
     $status=5;
     $requiredTicket=$GLOBALS['db']->queryUniqueValue("SELECT id FROM workflows_tickets WHERE idWorkflow='".$idWorkflow."' AND requiredAction='".$action->requiredAction."'");
    }else{
     $status=1;
     $requiredTicket=0;
    }
    // build query
    $query="INSERT INTO workflows_tickets
     (idWorkflow,idCategory,requiredTicket,requiredAction,typology,hash,mail,subject,idGroup,idAssigned,
      difficulty,priority,slaAssignment,slaClosure,status,solved,approved,hostname,addDate,addIdAccount) VALUES
     ('".$idWorkflow."','".$p_idCategory."','".$requiredTicket."','".$requiredAction."','".$typology."','".$hash."',
      '".$mail."','".$subject."','".$idGroup."','".$idAssigned."','".$difficulty."','".$priority."',
      '".$slaAssignment."','".$slaClosure."','".$status."','0','0','".$hostname."','".api_now()."',
      '".$_SESSION['account']->id."')";
    // execute query
    $GLOBALS['db']->execute($query);
    // set id to last inserted id
    $q_idTicket=$GLOBALS['db']->lastInsertedId();
    // if ticket is not locked
    if($status<>5){
     // log event
     $workflow=api_workflows_workflow($idWorkflow);
     $ticket=api_workflows_ticket($q_idTicket);
     api_log(API_LOG_NOTICE,"workflows","ticketCreated",
      "{logs_workflows_ticketCreated|".$ticket->number."|".$ticket->subject."|".$workflow->description."\n\nNote: ".$workflow->note."}",
      $ticket->id,"workflows/workflows_view.php?id=".$workflow->id."&idTicket=".$ticket->id);
     // send notification
     api_workflows_notifications($q_idTicket);
    }
   }
  }
  // return
  return TRUE;
 }else{
  // open standard ticket
  $p_idCategory=$_POST['idCategory'];
  $hash=md5(api_randomString(32));
  $p_subject=addslashes($_POST['subject']);
  $p_priority=$_POST['priority'];
  $hostname=api_workflows_hostName();
  // get group id by selected category
  $idGroup=api_workflows_categoryGroup($p_idCategory);
  // build query
  $query="INSERT INTO workflows_tickets
   (idWorkflow,idCategory,typology,hash,subject,idGroup,difficulty,priority,
    slaAssignment,slaClosure,status,solved,approved,hostname,addDate,addIdAccount) VALUES
   ('".$idWorkflow."','".$p_idCategory."','1','".$hash."','".$p_subject."',
    '".$idGroup."','2','".$p_priority."','0','480','1','0','0','".$hostname."',
    '".api_now()."','".$_SESSION['account']->id."')";
  // execute query
  $GLOBALS['db']->execute($query);
  // set id to last inserted id
  $q_idTicket=$GLOBALS['db']->lastInsertedId();
  // log event
  $workflow=api_workflows_workflow($idWorkflow);
  $ticket=api_workflows_ticket($q_idTicket);
  api_log(API_LOG_NOTICE,"workflows","ticketCreated",
   "{logs_workflows_ticketCreated|".$ticket->number."|".$ticket->subject."|".$workflow->description."\n\nNote: ".$workflow->note."}",
   $ticket->id,"workflows/workflows_view.php?id=".$workflow->id."&idTicket=".$ticket->id);
  // send notification
  api_workflows_notifications($q_idTicket);
  // return
  return TRUE;
 }
 return FALSE;
}


/* -[ Ticket Save ]---------------------------------------------------------- */
function ticket_save(){
 if(!api_checkPermission("workflows","workflows_add")){api_die("accessDenied");}
 // get workflow object
 $workflow=api_workflows_workflow($_GET['idWorkflow']);
 if(!$workflow->id){
  // redirect
  $alert="?alert=workflowError&alert_class=alert-error";
  exit(header("location: workflows.php".$alert));
 }
 // build and acquire variables
 $p_idCategory=$workflow->idCategory;
 $p_typology=$_POST['typology'];
 if($p_typology<>1){$hash=md5(api_randomString(32));}else{$hash=NULL;}
 $p_mail=addslashes($_POST['mail']);
 $p_subject=addslashes($_POST['subject']);
 $p_note=addslashes($_POST['note']);
 $p_idGroup=$_POST['idGroup'];
 $p_idAssigned=$_POST['idAssigned'];
 $p_difficulty=$_POST['difficulty'];
 $p_priority=$_POST['priority'];
 $slaAssignment=$_POST['slaAssignment'];
 $slaClosure=$_POST['slaClosure'];
 $hostname=api_workflows_hostName();
 // build query
 $query="INSERT INTO workflows_tickets
  (idWorkflow,idCategory,typology,hash,mail,subject,idGroup,idAssigned,difficulty,priority,
   slaAssignment,slaClosure,status,solved,approved,hostname,addDate,addIdAccount) VALUES
  ('".$workflow->id."','".$p_idCategory."','".$p_typology."','".$hash."','".$p_mail."',
   '".$p_subject."','".$p_idGroup."','".$p_idAssigned."','".$p_difficulty."',
   '".$p_priority."','".$slaAssignment."','".$slaClosure."','1','0','0','".$hostname."',
   '".api_now()."','".$_SESSION['account']->id."')";
 // execute query
 $GLOBALS['db']->execute($query);
 // set id to last inserted id
 $q_idTicket=$GLOBALS['db']->lastInsertedId();
 // log event
 $ticket=api_workflows_ticket($q_idTicket);
 api_log(API_LOG_NOTICE,"workflows","ticketCreated",
  "{logs_workflows_ticketCreated|".$ticket->number."|".$ticket->subject."|".$workflow->description."\n\nNote: ".$p_note."}",
  $ticket->id,"workflows/workflows_view.php?id=".$workflow->id."&idTicket=".$ticket->id);
 // send notification
 api_workflows_notifications($q_idTicket);
 // check and save note
 if(strlen($p_note)>0){
  $query="INSERT INTO workflows_tickets_notes
   (idTicket,note,addDate,addIdAccount) VALUES
   ('".$ticket->id."','".$p_note."','".api_now()."','".$_SESSION['account']->id."')";
  // execute query
  $GLOBALS['db']->execute($query);
 }
 // redirect
 $alert="&alert=ticketCreated&alert_class=alert-success";
 exit(header("location: workflows_view.php?id=".$workflow->id.$alert));
}

/* -[ Ticket Assign ]-------------------------------------------------------- */
function ticket_assign(){
 if(!api_workflows_ticketProcessPermission($_GET['idTicket'])){api_die("accessDenied");}
 // acquire variables
 $g_idWorkflow=$_GET['idWorkflow'];
 if(!$g_idWorkflow){$g_idWorkflow=0;}
 $workflow=api_workflows_workflow($g_idWorkflow);
 $g_idTicket=$_GET['idTicket'];
 if(!$g_idTicket){$g_idTicket=0;}
 $ticket=api_workflows_ticket($g_idTicket);
 // check id
 if($workflow->id>0 && $ticket->id>0){
  if($ticket->status<>2){
   // execute queries
   $GLOBALS['db']->execute("UPDATE workflows_tickets SET status='2',urged='0',idAssigned='".$_SESSION['account']->id."',assDate='".api_now()."',updDate='".api_now()."' WHERE id='".$g_idTicket."'");
   // log event
   api_log(API_LOG_NOTICE,"workflows","ticketAssigned",
    "{logs_workflows_ticketAssigned|".$ticket->number."|".$ticket->subject."|".$workflow->description."\n\nNote: ".$workflow->note."}",
    $ticket->id,"workflows/workflows_view.php?id=".$workflow->id."&idTicket=".$ticket->id);
   if($workflow->status<>2){
    $GLOBALS['db']->execute("UPDATE workflows_workflows SET status='2' WHERE id='".$g_idWorkflow."'");
    // log event
    api_log(API_LOG_NOTICE,"workflows","workflowAssigned",
     "{logs_workflows_workflowAssigned|".$workflow->number."|".$workflow->subject."|".api_account()->name."}",
     $g_idWorkflow,"workflows/workflows_view.php?id=".$workflow->id);
   }
   // change group
   if(api_company()->mainGroup!==NULL){$GLOBALS['db']->execute("UPDATE workflows_tickets SET idGroup='".api_company()->mainGroup."' WHERE id='".$g_idTicket."'");}
   // alert
   $alert="&alert=ticketAssigned&alert_class=alert-success";
  }else{$alert="&alert=ticketErrorAssigned&alert_class=alert-error";}
 }else{$alert="&alert=ticketError&alert_class=alert-error";}
 // redirect
 exit(header("location: workflows_view.php?id=".$g_idWorkflow."&idTicket=".$g_idTicket.$alert));
}

/* -[ Ticket Process ]------------------------------------------------------- */
function ticket_process(){
 if(!api_workflows_ticketProcessPermission($_GET['idTicket'])){api_die("accessDenied");}
 // acquire variables
 $g_idWorkflow=$_GET['idWorkflow'];
 if(!$g_idWorkflow){$g_idWorkflow=0;}
 $workflow=api_workflows_workflow($g_idWorkflow);
 $g_idTicket=$_GET['idTicket'];
 if(!$g_idTicket){$g_idTicket=0;}
 $ticket=api_workflows_ticket($g_idTicket);
 $p_status=$_POST['status'];
 $p_idGroup=$_POST['idGroup'];
 $p_idAssigned=$_POST['idAssigned'];
 $p_priority=$_POST['priority'];
 $p_difficulty=$_POST['difficulty'];
 $p_note=addslashes($_POST['note']);
 $p_mail_cc_array=explode(",",str_replace(array(";","  "," "),array(",","",""),addslashes($_POST['mail_cc'])));
 $v_mail_cc=implode(",",$p_mail_cc_array);
 // switch status
 switch($p_status){
  case 1: // opened
   $update_date=",updDate=NULL,endDate=NULL";
   $solved=0;
   break;
  case 40: // unsolved
   $p_status=4;
   $solved=0;
   $solved_txt=strtolower(api_text("solved-unexecuted"));
   break;
  case 41: // solved
   $p_status=4;
   $solved=1;
   $solved_txt=strtolower(api_text("solved-executed"));
   break;
  case 42: // unnecessary
   $p_status=4;
   $solved=2;
   $solved_txt=strtolower(api_text("solved-unnecessary"));
   break;
  default:
   $solved=0;
 }
 // if closed set endDate
 if($p_status==4){$update_date=",endDate='".api_now()."'";}
 // if change assigned account reset status
 if($p_status==2 && $p_idAssigned<>$_SESSION['account']->id){$p_status=1;}
 // check
 if($workflow->id>0 && $ticket->id>0){
  $query="UPDATE workflows_tickets SET
   status='".$p_status."',
   idGroup='".$p_idGroup."',
   idAssigned='".$p_idAssigned."',
   priority='".$p_priority."',
   difficulty='".$p_difficulty."',
   solved='".$solved."'
   ".$update_date."
   WHERE id='".$g_idTicket."'";
  // execute query
  $GLOBALS['db']->execute($query);
  // save note
  if(strlen($p_note)>0){
   // build query
   $query="INSERT INTO workflows_tickets_notes
    (idTicket,note,addDate,addIdAccount) VALUES
    ('".$g_idTicket."','→ ".$p_note."','".api_now()."','".$_SESSION['account']->id."')";
   // execute query
   $GLOBALS['db']->execute($query);
   // send message to user if mail is setted
   $user=api_account($ticket->addIdAccount);
   if(strlen($user->account)>4){
    $subject="Ticket ".$ticket->number." - ".$ticket->subject;
    $message=$p_note."\n\nLink: http://".$_SERVER['SERVER_NAME'].$GLOBALS['dir']."workflows/workflows_view.php?id=".$workflow->id."&idTicket=".$ticket->id;
    api_mailer($user->account,$message,$subject,FALSE,api_account()->mail,api_account()->name,$v_mail_cc);
   }
  }
  // unlock locked tickets
  if($p_status==4){
   $locked_tickets=$GLOBALS['db']->query("SELECT * FROM workflows_tickets WHERE requiredTicket='".$g_idTicket."' AND status='5'");
   while($locked_ticket=$GLOBALS['db']->fetchNextObject($locked_tickets)){
    // send notification
    api_workflows_notifications($locked_ticket);
    $unlocked_ticket=api_workflows_ticket($locked_ticket->id);
    // log event
    api_log(API_LOG_NOTICE,"workflows","ticketUnlocked",
     "{logs_workflows_ticketUnlocked|".$unlocked_ticket->number."|".$unlocked_ticket->subject."|".$workflow->description."\n\nNote: ".$workflow->note."}",
     $unlocked_ticket->id,"workflows/workflows_view.php?id=".$workflow->id."&idTicket=".$unlocked_ticket->id);
   }
   $GLOBALS['db']->execute("UPDATE workflows_tickets SET status='1',addDate='".api_now()."' WHERE requiredTicket='".$g_idTicket."' AND status='5'");
  }
  // switch status for logs and notifications
  $alert="&alert=ticketUpdated&alert_class=alert-success";
  switch($p_status){
   // opened
   case 1:
    break;
   // assigned
   case 2:
    break;
   // standby
   case 3:
    // log event
    api_log(API_LOG_NOTICE,"workflows","ticketStandby",
     "{logs_workflows_ticketStandby|".$ticket->number."|".$ticket->subject."|".api_account()->name."|".$p_note."}",
     $g_idTicket,"workflows/workflows_view.php?id=".$g_idWorkflow."&idTicket=".$g_idTicket);
    break;
   // closed
   case 4:
    $alert="&alert=ticketClosed&alert_class=alert-success";
    // log event
    api_log(API_LOG_NOTICE,"workflows","ticketClosed",
     "{logs_workflows_ticketClosed|".$ticket->number."|".$ticket->subject."|".$solved_txt."|".api_account()->name."|".$p_note."}",
     $g_idTicket,"workflows/workflows_view.php?id=".$g_idWorkflow."&idTicket=".$g_idTicket);
   break;
   // locked
   case 5:
    break;
  }
  // check if all activities are completed
  if($GLOBALS['db']->countOf("workflows_tickets","idWorkflow='".$g_idWorkflow."' AND (status<'4' OR status='5')")==0){
   // close workflow
   $GLOBALS['db']->execute("UPDATE workflows_workflows SET status='4',endDate='".api_now()."' WHERE id='".$g_idWorkflow."'");
   // eventualmente disabilitarlo in caso il ticket sia unico del workflow
   // log event
   api_log(API_LOG_NOTICE,"workflows","workflowClosed",
    "{logs_workflows_workflowClosed|".$workflow->number."|".$workflow->subject."}",
    $g_idWorkflow,"workflows/workflows_view.php?id=".$g_idWorkflow);
   // redirect
   $alert="?alert=workflowClosed&alert_class=alert-success";
   exit(header("location: workflows.php".$alert));
  }
 }else{
  // alert
  $alert="&alert=ticketError&alert_class=alert-error";
 }
 // redirect
 exit(header("location: workflows_view.php?id=".$g_idWorkflow."&idTicket=".$g_idTicket.$alert));
}

/* -[ Ticket Clone ]--------------------------------------------------------- */
function ticket_clone(){
 // acquire variables
 $ticket=api_workflows_ticket($_GET['idTicket']);
 $p_subject=addslashes($_POST['subject']);
 $p_referents=addslashes($_POST['referents']);
 $referents_array=explode("\n",$p_referents);
 // check
 if($ticket->id){
  foreach($referents_array as $key=>$referent){
   if($key>0 && strlen($referent)<3){continue;}
   $subject=str_replace("  "," ",trim($p_subject." ".$referent));
   // build query
   $query="INSERT INTO workflows_tickets
    (idWorkflow,idCategory,typology,subject,idGroup,difficulty,priority,status,
     solved,approved,slaAssignment,slaClosure,hostname,addDate,addIdAccount) VALUES
    ('".$ticket->idWorkflow."','".$ticket->idCategory."','1','".$subject."','".$ticket->idGroup."',
     '".$ticket->difficulty."','".$ticket->priority."','1','0','0','".$ticket->slaAssignment."',
     '".$ticket->slaClosure."','".$ticket->hostname."','".api_now()."','".api_account()->id."')";
   // execute query
   $GLOBALS['db']->execute($query);
   // set id to last inserted id
   $q_idTicket=$GLOBALS['db']->lastInsertedId();
   // log event
   $clonedTicket=api_workflows_ticket($q_idTicket);
   api_log(API_LOG_NOTICE,"workflows","ticketCreated",
    "{logs_workflows_ticketCreated|".$clonedTicket->number."|".$clonedTicket->subject."|".$clonedTicket->description."}",
    $clonedTicket->id,"workflows/workflows_view.php?id=".$clonedTicket->idWorkflow."&idTicket=".$clonedTicket->id);
   // send notification
   api_workflows_notifications($q_idTicket);
  }
 }else{$alert="&alert=ticketError&alert_class=alert-error";}
 // redirect
 exit(header("location: workflows_view.php?id=".$ticket->idWorkflow.$alert));
}

/* -[ Ticket Note ]---------------------------------------------------------- */
function ticket_note(){
 // get objects
 $workflow=api_workflows_workflow($_GET['idWorkflow'],FALSE);
 $ticket=api_workflows_ticket($_GET['idTicket'],FALSE);
 // check id
 if(!$workflow->id){
  // redirect
  $alert="?alert=workflowError&alert_class=alert-error";
  exit(header("location: workflows.php".$alert));
 }
 if(!$ticket->id){
  // redirect
  $alert="&alert=workflowError&alert_class=alert-error";
  exit(header("location: workflows_view.php?id=".$workflow->id.$alert));
 }
 // build and acquire variables
 $p_note=addslashes($_POST['note']);
 // build query
 $query="INSERT INTO workflows_tickets_notes
  (idTicket,note,addDate,addIdAccount) VALUES
  ('".$ticket->id."','".$p_note."','".api_now()."','".$_SESSION['account']->id."')";
 // execute query
 $GLOBALS['db']->execute($query);
 // set id to last inserted id
 $q_idNote=$GLOBALS['db']->lastInsertedId();
 // log and notifications
 api_log(API_LOG_NOTICE,"workflows","ticketNote",
  "{logs_workflows_ticketNote|".$ticket->number."|".$ticket->subject."|".$q_idNote."|".$p_note."|".api_account()->name."}",
  $ticket->id,"workflows/workflows_view.php?id=".$workflow->id."&idTicket=".$ticket->id);
 // redirect
 $alert="&alert=noteCreated&alert_class=alert-success";
 exit(header("location: workflows_view.php?id=".$workflow->id.$alert));
}

/* -[ Ticket Note Delete ]--------------------------------------------------- */
function ticket_note_delete(){
 // acquire variables
 $g_idWorkflow=$_GET['idWorkflow'];
 $g_idTicket=$_GET['idTicket'];
 $g_idNote=$_GET['idNote'];
 // check id
 if($g_idNote){
  $GLOBALS['db']->execute("DELETE FROM workflows_tickets_notes WHERE id='".$g_idNote."'");
  // redirect
  $alert="&alert=noteDeleted&alert_class=alert-warning";
 }else{
  $alert="&alert=noteError&alert_class=alert-error";
 }
 // redirect
 exit(header("location: workflows_view.php?id=".$g_idWorkflow."&idTicket=".$g_idTicket.$alert));
}

/* -[ Mail Delete ]---------------------------------------------------------- */
function mail_delete(){
 if(!api_checkPermission("workflows","workflows_admin")){api_die("accessDenied");}
 // get workflow object
 $g_idMail=$_GET['idMail'];
 // check id
 if($g_idMail){
  $GLOBALS['db']->execute("DELETE FROM workflows_mails WHERE id='".$g_idMail."'");
  // redirect
  $alert="?alert=mailDeleted&alert_class=alert-warning";
 }else{
  $alert="?alert=mailError&alert_class=alert-error";
 }
 // redirect
 exit(header("location: workflows_mails_list.php".$alert));
}


/* -[ Category Save ]-------------------------------------------------------- */
function category_save(){
 if(!api_checkPermission("workflows","workflows_admin")){api_die("accessDenied");}
 // acquire variables
 $g_id=$_GET['id'];
 if(!$g_id){$g_id=0;}
 $p_idCategory=$_POST['idCategory'];
 $p_name=addslashes($_POST['name']);
 $p_description=addslashes($_POST['description']);
 $p_idGroup=addslashes($_POST['idGroup']);
 // check
 if($p_idGroup>0){
  // build query
  if($g_id>0 && $p_idGroup>0){
   $query="UPDATE workflows_categories SET
    idCategory='".$p_idCategory."',
    name='".$p_name."',
    description='".$p_description."',
    idGroup='".$p_idGroup."',
    updDate='".api_now()."',
    updIdAccount='".$_SESSION['account']->id."'
    WHERE id='".$g_id."'";
   // execute query
   $GLOBALS['db']->execute($query);
   // log and notifications
   api_log(API_LOG_NOTICE,"workflows","categoryUpdated",
    "{logs_workflows_categoryUpdated|".$p_name."}",
    $g_id,"workflows/workflows_categories.php?id=".$g_id);
   // redirect
   $alert="?alert=categoryUpdated&alert_class=alert-success";
   exit(header("location: workflows_categories.php".$alert));
  }else{
   $query="INSERT INTO workflows_categories
    (idCategory,name,description,idGroup,addDate,addIdAccount) VALUES
    ('".$p_idCategory."','".$p_name."','".$p_description."','".$p_idGroup."',
     '".api_now()."','".$_SESSION['account']->id."')";
   // execute query
   $GLOBALS['db']->execute($query);
   // set id to last inserted id
   $q_idCategory=$GLOBALS['db']->lastInsertedId();
   // log and notifications
   api_log(API_LOG_NOTICE,"workflows","categoryCreated",
    "{logs_workflows_categoryCreated|".$p_name."}",
    $q_idCategory,"workflows/workflows_categories.php?id=".$q_idCategory);
   // redirect
   $alert="?alert=categoryCreated&alert_class=alert-success";
   exit(header("location: workflows_categories.php".$alert));
  }
 }else{
  // redirect
  $alert="?alert=categoryError&alert_class=alert-error";
  exit(header("location: workflows_categories.php".$alert));
 }
}


/* -[ Flow Save ]------------------------------------------------------------ */
function flow_save(){
 if(!api_checkPermission("workflows","workflows_admin")){api_die("accessDenied");}
 // acquire variables
 $g_idFlow=$_GET['idFlow'];
 if(!$g_idFlow){$g_idFlow=0;}
 $p_idCategory=$_POST['idCategory'];
 $p_typology=$_POST['typology'];
 $p_pinned=$_POST['pinned'];
 $p_subject=addslashes($_POST['subject']);
 $p_description=addslashes($_POST['description']);
 $p_advice=addslashes($_POST['advice']);
 $p_priority=$_POST['priority'];
 $p_sla=$_POST['sla'];
 $p_guide=addslashes($_POST['guide']);
 // convert
 if(!$p_pinned){$p_pinned=0;}
 // build query
 if($g_idFlow>0){
  $query="UPDATE workflows_flows SET
   idCategory='".$p_idCategory."',
   typology='".$p_typology."',
   pinned='".$p_pinned."',
   subject='".$p_subject."',
   description='".$p_description."',
   advice='".$p_advice."',
   priority='".$p_priority."',
   sla='".$p_sla."',
   guide='".$p_guide."',
   updDate='".api_now()."',
   updIdAccount='".$_SESSION['account']->id."'
   WHERE id='".$g_idFlow."'";
  // execute query
  $GLOBALS['db']->execute($query);

   // log and notifications
   api_log(API_LOG_NOTICE,"workflows","flowUpdated",
    "{logs_workflows_flowUpdated|".$p_subject."}",
    $g_idFlow,"workflows/workflows_flows_view.php?idFlow=".$g_idFlow);

  // redirect
  $alert="&alert=flowUpdated&alert_class=alert-success";
  exit(header("location: workflows_flows_view.php?idFlow=".$g_idFlow.$alert));
 }else{
  $query="INSERT INTO workflows_flows
   (idCategory,typology,pinned,subject,description,advice,priority,sla,guide,
    addDate,addIdAccount) VALUES
   ('".$p_idCategory."','".$p_typology."','".$p_pinned."','".$p_subject."',
    '".$p_description."','".$p_advice."','".$p_priority."','".$p_sla."',
    '".$p_guide."','".api_now()."','".$_SESSION['account']->id."')";
  // execute query
  $GLOBALS['db']->execute($query);
  // set id to last inserted id
  $q_idFlow=$GLOBALS['db']->lastInsertedId();
   // log and notifications
   api_log(API_LOG_NOTICE,"workflows","flowCreated",
    "{logs_workflows_flowCreated|".$p_subject."}",
    $q_idFlow,"workflows/workflows_flows_view.php?idFlow=".$q_idFlow);
  // redirect
  $alert="&alert=flowCreated&alert_class=alert-success";
  exit(header("location: workflows_flows_view.php?idFlow=".$q_idFlow.$alert));
 }
}

/* -[ Flow Field Save ]------------------------------------------------------ */
function flow_field_save(){
 if(!api_checkPermission("workflows","workflows_admin")){api_die("accessDenied");}
 // acquire variables
 $g_idFlow=$_GET['idFlow'];
 if(!$g_idFlow){$g_idFlow=0;}
 $flow=api_workflows_flow($g_idFlow);
 $g_idField=$_GET['idField'];
 if(!$g_idField){$g_idField=0;}
 $p_typology=$_POST['typology'];
 $p_name=api_workflows_clearFieldName(addslashes($_POST['name']));
 $p_label=addslashes($_POST['label']);
 $p_value=addslashes($_POST['value']);
 $p_class=addslashes($_POST['class']);
 $p_placeholder=addslashes($_POST['placeholder']);
 $p_required=$_POST['required'];
 $p_options_method=addslashes($_POST['options_method']);
 $p_options_values=addslashes($_POST['options_values']);
 $p_options_query=addslashes($_POST['options_query']);
 // convert fields
 if($p_options_method=="none"){$p_options_method="";$p_options_values="";$p_options_query="";}
 if($p_options_method=="values"){$p_options_query="";}
 if($p_options_method=="query"){$p_options_values="";}
  if(!$p_required){$p_required=0;}
 // check flow
 if(!$g_idFlow>0){
  $alert="?alert=flowError&alert_class=alert-error";
  exit(header("location: workflows_flow_list.php".$alert));
 }
 // check for insert or update
 if($g_idField>0){
  // build query
  $query="UPDATE workflows_fields SET
   idFlow='".$g_idFlow."',
   typology='".$p_typology."',
   name='".$p_name."',
   label='".$p_label."',
   value='".$p_value."',
   class='".$p_class."',
   placeholder='".$p_placeholder."',
   options_method='".$p_options_method."',
   options_values='".$p_options_values."',
   options_query='".$p_options_query."',
   required='".$p_required."'
   WHERE id='".$g_idField."'";
  // execute query
  $GLOBALS['db']->execute($query);
  // log and notifications
  api_log(API_LOG_NOTICE,"workflows","fieldUpdated",
   "{logs_workflows_fieldUpdated|".$flow->subject."|".$p_label."}",
   $g_idField,"workflows/workflows_flows_view.php?id=".$g_idFlow);
  // redirect
  $alert="&alert=fieldUpdated&alert_class=alert-success";
  exit(header("location: workflows_flows_view.php?idFlow=".$g_idFlow.$alert));
 }else{
  // get number of fields for position
  $position=$GLOBALS['db']->countOf("workflows_fields","idFlow='".$g_idFlow."'");
  $position++;
  // build query
  $query="INSERT INTO workflows_fields
   (idFlow,typology,name,label,value,class,placeholder,options_method,options_values,
    options_query,required,position) VALUES
   ('".$g_idFlow."','".$p_typology."','".$p_name."','".$p_label."','".$p_value."',
    '".$p_class."','".$p_placeholder."','".$p_options_method."','".$p_options_values."',
    '".$p_options_query."','".$p_required."','".$position."')";
  // execute query
  $GLOBALS['db']->execute($query);
  // set id to last inserted id
  $q_idField=$GLOBALS['db']->lastInsertedId();

   // log and notifications
   api_log(API_LOG_NOTICE,"workflows","fieldCreated",
    "{logs_workflows_fieldCreated|".$flow->subject."|".$p_label."}",
    $q_idField,"workflows/workflows_flows_view.php?idFlow=".$g_idFlow);

  // redirect
  $alert="&alert=fieldCreated&alert_class=alert-success";
  exit(header("location: workflows_flows_view.php?idFlow=".$g_idFlow.$alert));
 }
}

/* -[ Flow Field Move ]------------------------------------------------------ */
function flow_field_move($to){
 if(!api_checkPermission("workflows","workflows_admin")){api_die("accessDenied");}
 // acquire variables
 $g_idFlow=$_GET['idFlow'];
 if(!$g_idFlow){$g_idFlow=0;}
 $flow=api_workflows_flow($g_idFlow);
 $g_idField=$_GET['idField'];
 if(!$g_idField){$g_idField=0;}
 $field=$GLOBALS['db']->queryUniqueObject("SELECT * FROM workflows_fields WHERE id='".$g_idField."'");
 // check flow
 if(!$g_idFlow>0){
  $alert="?alert=flowError&alert_class=alert-error";
  exit(header("location: workflows_flow_list.php".$alert));
 }
 if($g_idField>0){
  $moved=FALSE;
  // get current position
  $position=$GLOBALS['db']->queryUniqueValue("SELECT position FROM workflows_fields WHERE id='".$g_idField."'");
  // move field
  switch($to){
   case "up":
    if($position>1){
     echo $GLOBALS['db']->execute("UPDATE workflows_fields SET position=".$position." WHERE position='".($position-1)."' AND idFlow='".$g_idFlow."'");
     echo $GLOBALS['db']->execute("UPDATE workflows_fields SET position=".($position-1)." WHERE id='".$g_idField."'");
     $moved=TRUE;
    }
    break;
   case "down":
    $max_position=$GLOBALS['db']->countOf("workflows_fields","idFlow='".$g_idFlow."'");
    if($position<$max_position){
     echo $GLOBALS['db']->execute("UPDATE workflows_fields SET position=".$position." WHERE position='".($position+1)."' AND idFlow='".$g_idFlow."'");
     echo $GLOBALS['db']->execute("UPDATE workflows_fields SET position=".($position+1)." WHERE id='".$g_idField."'");
     $moved=TRUE;
    }
    break;
  }
  // alert and redirect
  if($moved){

   // log and notifications
   api_log(API_LOG_NOTICE,"workflows","fieldMoved",
    "{logs_workflows_fieldMoved|".$flow->subject."|".$field->label."}",
    $g_idField,"workflows/workflows_flows_view.php?id=".$g_idFlow);

   $alert="&alert=fieldMoved&alert_class=alert-success";
  }
  else{
   $alert="&alert=flowError&alert_class=alert-error";
  }
  exit(header("location: workflows_flows_view.php?idFlow=".$g_idFlow.$alert));
 }else{
  // redirect
  $alert="&alert=flowError&alert_class=alert-error";
  exit(header("location: workflows_flows_view.php?idFlow=".$g_idFlow.$alert));
 }
}

/* -[ Flow Field Delete ]---------------------------------------------------- */
function flow_field_delete(){
 if(!api_checkPermission("workflows","workflows_admin")){api_die("accessDenied");}
 // acquire variables
 $g_idFlow=$_GET['idFlow'];
 if(!$g_idFlow){$g_idFlow=0;}
 $flow=api_workflows_flow($g_idFlow);
 $g_idField=$_GET['idField'];
 if(!$g_idField){$g_idField=0;}
 $field=$GLOBALS['db']->queryUniqueObject("SELECT * FROM workflows_fields WHERE id='".$g_idField."'");
 // check flow
 if(!$g_idFlow>0){
  $alert="?alert=flowError&alert_class=alert-error";
  exit(header("location: workflows_flow_list.php".$alert));
 }
 // check
 if($g_idField>0){
  // delete field
  echo $GLOBALS['db']->execute("DELETE FROM workflows_fields WHERE id='".$g_idField."'");
  // moves back fields located after
  echo $GLOBALS['db']->execute("UPDATE workflows_fields SET position=position-1 WHERE position>'".$field->position."' AND idFlow='".$g_idFlow."'");
  // log and notifications
  api_log(API_LOG_WARNING,"workflows","fieldDeleted",
   "{logs_workflows_fieldDeleted|".$flow->subject."|".$field->label."}",
   $g_idField,"workflows/workflows_flows_view.php?id=".$g_idFlow);
  // redirect
  $alert="&alert=fieldDeleted&alert_class=alert-warning";
  exit(header("location: workflows_flows_view.php?idFlow=".$g_idFlow.$alert));
 }else{
  // redirect
  $alert="&alert=flowError&alert_class=alert-error";
  exit(header("location: workflows_flows_view.php?idFlow=".$g_idFlow.$alert));
 }
}

/* -[ Workflow Action Save ]-------------------------------------------------- */
function flow_action_save(){
 if(!api_checkPermission("workflows","workflows_admin")){api_die("accessDenied");}
 // acquire variables
 $g_idFlow=$_GET['idFlow'];
 if(!$g_idFlow){$g_idFlow=0;}
 $flow=api_workflows_flow($g_idFlow);
 $g_idAction=$_GET['idAction'];
 if(!$g_idAction){$g_idAction=0;}
 $p_typology=$_POST['typology'];
 $p_requiredAction=$_POST['requiredAction'];
 $p_conditionedField=$_POST['conditionedField'];
 $p_conditionedValue=addslashes($_POST['conditionedValue']);
 $p_subject=addslashes($_POST['subject']);
 $p_idGroup=$_POST['idGroup'];
 $p_idAssigned=$_POST['idAssigned'];
 if(!$p_idAssigned){$p_idAssigned=0;}
 $p_mail=addslashes($_POST['mail']);
 $p_difficulty=$_POST['difficulty'];
 $p_priority=$_POST['priority'];
 $p_slaAssignment=$_POST['slaAssignment'];
 $p_slaClosure=$_POST['slaClosure'];
 // convert fields
 if($p_typology==1){$p_mail="";}
 if($p_conditionedField==0){$p_conditionedValue="";}
 // check flow
 if(!$g_idFlow>0){
  $alert="?alert=flowError&alert_class=alert-error";
  exit(header("location: workflows_flow_list.php".$alert));
 }
 // build query
 if($g_idAction>0){
  $query="UPDATE workflows_actions SET
   idFlow='".$g_idFlow."',
   typology='".$p_typology."',
   requiredAction='".$p_requiredAction."',
   conditionedField='".$p_conditionedField."',
   conditionedValue='".$p_conditionedValue."',
   subject='".$p_subject."',
   idGroup='".$p_idGroup."',
   idAssigned='".$p_idAssigned."',
   mail='".$p_mail."',
   difficulty='".$p_difficulty."',
   priority='".$p_priority."',
   slaAssignment='".$p_slaAssignment."',
   slaClosure='".$p_slaClosure."'
   WHERE id='".$g_idAction."'";
  // execute query
  $GLOBALS['db']->execute($query);
  // log and notifications
  api_log(API_LOG_NOTICE,"workflows","actionUpdated",
   "{logs_workflows_actionUpdated|".$flow->subject."|".$p_subject."}",
   $g_idAction,"workflows/workflows_flows_view.php?id=".$g_idFlow);
  // redirect
  $alert="&alert=actionUpdated&alert_class=alert-success";
  exit(header("location: workflows_flows_view.php?idFlow=".$g_idFlow.$alert));
 }else{
  $query="INSERT INTO workflows_actions
   (idFlow,typology,requiredAction,conditionedField,conditionedvalue,subject,
    idGroup,idAssigned,mail,difficulty,priority,slaAssignment,slaClosure) VALUES
   ('".$g_idFlow."','".$p_typology."','".$p_requiredAction."','".$p_conditionedField."',
    '".$p_conditionedValue."','".$p_subject."','".$p_idGroup."','".$p_idAssigned."',
    '".$p_mail."','".$p_difficulty."','".$p_priority."','".$p_slaAssignment."',
    '".$p_slaClosure."')";
  // execute query
  $GLOBALS['db']->execute($query);
  // set id to last inserted id
  $q_idAction=$GLOBALS['db']->lastInsertedId();
   // log and notifications
   api_log(API_LOG_NOTICE,"workflows","actionCreated",
    "{logs_workflows_actionCreated|".$flow->subject."|".$p_subject."}",
    $q_idAction,"workflows/workflows_flows_view.php?idFlow=".$g_idFlow);
  // redirect
  $alert="&alert=actionCreated&alert_class=alert-success";
  exit(header("location: workflows_flows_view.php?idFlow=".$g_idFlow.$alert));
 }
}

/* -[ Flow Action Delete ]--------------------------------------------------- */
function flow_action_delete(){
 if(!api_checkPermission("workflows","workflows_admin")){api_die("accessDenied");}
 // acquire variables
 $g_idFlow=$_GET['idFlow'];
 if(!$g_idFlow){$g_idFlow=0;}
 $flow=api_workflows_flow($g_idFlow);
 $g_idAction=$_GET['idAction'];
 if(!$g_idAction){$g_idAction=0;}
 $action=$GLOBALS['db']->queryUniqueObject("SELECT * FROM workflows_actions WHERE id='".$g_idAction."'");
 // check flow
 if(!$g_idFlow>0){
  $alert="?alert=flowError&alert_class=alert-error";
  exit(header("location: workflows_flow_list.php".$alert));
 }
 if($g_idAction>0){
  // check if action is required
  if($GLOBALS['db']->countOf("workflows_actions","requiredAction='".$g_idAction."'")>0){
   // redirect
   $alert="&alert=actionRequired&alert_class=alert-error";
   exit(header("location: workflows_flows_view.php?idFlow=".$g_idFlow.$alert));
  }else{
   // delete action
   $GLOBALS['db']->execute("DELETE FROM workflows_actions WHERE id='".$g_idAction."'");
   // log and notifications
   api_log(API_LOG_WARNING,"workflows","actionDeleted",
    "{logs_workflows_actionDeleted|".$flow->subject."|".$action->subject."}",
    $g_idAction,"workflows/workflows_flows_view.php?id=".$g_idFlow);
   // redirect
   $alert="&alert=actionDeleted&alert_class=alert-warning";
   exit(header("location: workflows_flows_view.php?idFlow=".$g_idFlow.$alert));
  }
 }else{
  // redirect
  $alert="&alert=flowError&alert_class=alert-error";
  exit(header("location: workflows_flows_view.php?idFlow=".$g_idFlow.$alert));
 }
}

/* -[ Attachments Download ]-------------------------------------------- */
function attachments_download(){
 // acquire variables
 $g_id=$_GET['id'];
 if(!$g_id){$g_id=0;}
 // download file from database
 api_file_download($g_id,"workflows_attachments",NULL,FALSE,"workflows");
}