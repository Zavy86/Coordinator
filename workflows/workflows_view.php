<?php
/* -------------------------------------------------------------------------- *\
|* -[ Workflows - View ]----------------------------------------------------- *|
\* -------------------------------------------------------------------------- */
$checkPermission="workflows_view";
require_once("template.inc.php");
function content(){
 // definitions
 $notes_modals_array=array();
 // get workflow object
 $workflow=api_workflows_workflow($_GET['id'],TRUE);
 if(!$workflow->id){echo api_text("workflowNotFound");return FALSE;}
 // get flow
 $flow=api_workflows_flow($workflow->idFlow,FALSE);
 // get selected ticket object
 $selected_ticket=api_workflows_ticket($_GET['idTicket'],TRUE);
 // acquire variables
 $g_act=$_GET['act'];
 // make account
 $account=api_account($workflow->addIdAccount);
 $account_role=api_accounts_role($account->companies[$account->mainCompany]->idRole)->name;
 $account_group=$account->companies[$account->mainCompany]->groups[$account->companies[$account->mainCompany]->mainGroup]->label;
 if(api_checkPermission("accounts","accounts_edit")){$account_link="../accounts/accounts_edit.php?idAccount=".$account->id;}else{$account_link="#";}
 $account_dd=api_link($account_link,$account->name,$account_role." &rarr; ".$account_group,NULL,TRUE,NULL,NULL,($account_link<>"#"?"_blank":"_self"));
 // build workflow description list
 $workflow_dl=new str_dl("br","dl-horizontal");
 $workflow_dl->addElement(api_text("view-dt-idWorkflow"),$workflow->number);
 $workflow_dl->addElement(api_text("view-dt-category"),api_workflows_categoryName($workflow->idCategory,TRUE,TRUE));
 $workflow_dl->addElement(api_text("view-dt-subject"),"<strong>".stripslashes($workflow->subject)."</strong>");
 $workflow_dl->addElement(api_text("view-dt-account"),$account_dd);
 $workflow_dl->addElement(api_text("view-dt-typology"),api_workflows_typology($workflow->typology));
 $workflow_dl->addElement(api_text("view-dt-priority"),api_workflows_priority($workflow->priority));
 $workflow_dl->addElement(api_text("view-dt-status"),api_workflows_status($workflow->status));
 $workflow_dl->addElement(api_text("view-dt-addDate"),api_timestampFormat($workflow->addDate,api_text("datetime")));
 if($workflow->endDate<>NULL){$workflow_dl->addElement(api_text("view-dt-endDate"),api_timestampFormat($workflow->endDate,api_text("datetime")));}
 $workflow_dl->addElement(api_text("view-dt-sla"),api_workflows_workflowSLA($workflow),NULL);
 // DameWare integration
 include('config.inc.php');
 if($dwrcc_enabled){$workflow->dwrcc=api_link("dwrcc://".$workflow->hostname,api_icon("icon-screenshot"),NULL,NULL,FALSE,api_text("view-dd-connect",$workflow->hostname));}
 // OCS inventory integration
 $host_modal=api_workflows_ocs($workflow->hostname);
 if($host_modal<>FALSE){
  $workflow->hostname=$host_modal->link($host_modal->hostname);
 }
 // build details description list
 $details_dl=new str_dl("br","dl-horizontal");
 $details_dl->addElement(api_text("view-dt-details"),nl2br(stripslashes($workflow->description)));
 if(strlen($workflow->note)>0){$details_dl->addElement(api_text("view-dt-note"),nl2br(stripslashes($workflow->note)));}
 $details_dl->addElement(api_text("view-dt-hostname"),$workflow->dwrcc." ".$workflow->hostname);
 $details_dl->addElement(api_text("view-dt-guide"),"<a href='#' onClick=\"window.prompt('".api_text("view-dd-guide")."','".addslashes($flow->guide)."');\">".$flow->guide."</a>",NULL);
 // build tickets table
 $tickets_table=new str_table(api_text("view-tr-unvalued"),TRUE);
 $tickets_table->addHeader("&nbsp;",NULL,"16");
 $tickets_table->addHeader(api_text("view-th-idTicket"),"nowarp");
 $tickets_table->addHeader("&nbsp;",NULL,"16");
 $tickets_table->addHeader("!","nowarp text-center");
 $tickets_table->addHeader(api_text("view-th-sla"),"nowarp text-center");
 $tickets_table->addHeader("&nbsp;",NULL,"16");
 $tickets_table->addHeader(api_text("view-th-subject"),NULL,"100%");
 $tickets_table->addHeader(api_text("view-th-assigned"),"nowarp text-right");
 $tickets_table->addHeader(api_text("view-th-group"),"nowarp text-center");
 $tickets_table->addHeader(api_text("view-th-update"),"nowarp");
 //if(can_operate $activity->typology<>3){
 $tickets_table->addHeader("&nbsp;",NULL,"16");
 //}
 // build tickets table rows
 foreach($workflow->tickets as $ticket){
  if($ticket->urged){$tickets_table->addRow("error");}
   elseif($ticket->id==$selected_ticket->id){$tickets_table->addRow("info");}
   else{$tickets_table->addRow();}
  // ticket typology
  switch($ticket->typology){
   case 1:$typology=api_icon("icon-tag",api_text("ticket-standard"));break;
   case 2:$typology=api_icon("icon-envelope",api_text("ticket-external-by",$ticket->mail));break;
   case 3:$typology=api_icon("icon-check",api_text("ticket-authorization-by",$ticket->mail));break;
  }
  // notes
  /*if(strlen($ticket->note)>0){
   $notes=" <a data-toggle='popover' data-placement='top' data-content=\"".stripslashes(str_replace("\n","| ",$ticket->note))."\">".api_icon("icon-comment")."</a>";
  }else{$notes=NULL;}*/
  $notes_modal=new str_modal("notes_".$ticket->id);
  $notes_modal->header(stripslashes($ticket->subject));
  $form_body=new str_form("submit.php?act=ticket_note&idWorkflow=".$workflow->id."&idTicket=".$ticket->id,"post","ticket_notes_".$ticket->id);
  $form_body->addField("textarea","note",api_account()->name."<br>".api_timestampFormat(api_now())."<br><br><input type='submit' class='btn btn-primary' value='Salva'>",NULL,"input-xlarge",api_text("view-ff-note-placeholder"),FALSE,5);
  $dl_body=new str_dl("br","dl-horizontal");
  foreach($ticket->notes as $note){
   if($note->addIdAccount==api_account()->id){$notes_del=api_link("submit.php?act=ticket_note_delete&idNote=".$note->id."&idWorkflow=".$workflow->id."&idTicket=".$ticket->id,api_icon("icon-trash"),api_text("view-ff-note-delete"),NULL,FALSE,api_text("view-ff-note-delete-confirm"))." ";}else{$notes_del=NULL;}
   $dl_body->addElement(api_account($note->addIdAccount)->name."<br>".api_timestampFormat($note->addDate),$notes_del.nl2br(stripslashes($note->note)));
  }
  if(!count($ticket->notes)){$dl_body->addElement("&nbsp;",api_text("view-dd-notesNull"));$note_count=NULL;}
   else{$note_count=" ".count($ticket->notes);}
  $notes_modal_body=$dl_body->render(FALSE);
  if(api_workflows_ticketProcessPermission($ticket)){$notes_modal_body=$form_body->render(FALSE).$notes_modal_body;}
  $notes_modal->body($notes_modal_body);
  $notes_modals_array[]=$notes_modal;
  // assigned
  if($ticket->status==1 || $ticket->status==3){$italic="<i>";$unitalic="</i>";}
  else{$italic=NULL;$unitalic=NULL;}
  // update
  if($ticket->status==4){$update=api_timestampFormat($ticket->endDate,api_text("datetime"));}
  else{$update=api_timestampFormat($ticket->updDate,api_text("datetime"));}
  // build tickets table fields
  $tickets_table->addField("<a href='workflows_view.php?id=".$workflow->id."&idTicket=".$ticket->id."'>".$typology."</a>","nowarp");
  $tickets_table->addField(str_pad($ticket->idWorkflow,5,"0",STR_PAD_LEFT)."-".str_pad($ticket->id,5,"0",STR_PAD_LEFT),"nowarp");
  // DA RIFARE MOLTO MELGIO-----------------------------------
  if($ticket->typology==3){
   $tickets_table->addField(api_workflows_status($ticket->status,TRUE,NULL,$ticket->approved),"nowarp text-center");
  }else{
   $tickets_table->addField(api_workflows_status($ticket->status,TRUE,$ticket->solved),"nowarp text-center");
  }
  $tickets_table->addField($ticket->priority,"nowarp text-center");
  $tickets_table->addField(api_workflows_ticketSLA($ticket),"nowarp text-center");
  $tickets_table->addField($notes_modal->link(api_icon("icon-comment",api_text("view-td-note"))).$note_count,"nowarp");
  $tickets_table->addField(stripslashes($ticket->subject));
  $tickets_table->addField($italic.(($ticket->idAssigned>0)?api_account($ticket->idAssigned)->firstname:"&nbsp;").$unitalic,"nowarp text-right");
  $tickets_table->addField(api_groupName($ticket->idGroup,TRUE,TRUE),"nowarp text-center");
  $tickets_table->addField($update,"nowarp");
  // check for process permission
  if($ticket->typology<>3 && api_workflows_ticketProcessPermission($ticket)){
   if($ticket->status==1){
    $action="<a href='submit.php?act=ticket_assign&idWorkflow=".$ticket->idWorkflow."&idTicket=".$ticket->id."' onClick='return confirm(\"".api_text("view-td-assign-confirm")."\")'>".api_icon("icon-eye-open",api_text("view-td-assign"))."</a>";
   }elseif($ticket->status>1 && $ticket->status<4){
    $action="<a href='workflows_view.php?id=".$ticket->idWorkflow."&idTicket=".$ticket->id."&act=editTicket'>".api_icon("icon-cog",api_text("view-td-process"))."</a>";
   }elseif($ticket->status==5){
    $action="<a href='workflows_view.php?id=".$ticket->idWorkflow."&idTicket=".$ticket->id."&act=editTicket'>".api_icon("icon-eye-close",api_text("view-td-unlock"))."</a>";
   }else{
    $action="<a href='workflows_view.php?id=".$ticket->idWorkflow."&idTicket=".$ticket->id."&act=editTicket'>".api_icon("icon-edit",api_text("view-td-reopen"))."</a>";
   }
  }else{
   $action="&nbsp;";
  }
  $tickets_table->addField($action,"nowarp text-center");
 }
 // edit selected ticket or add ticket modal window
 if($selected_ticket->id>0 && $g_act=="editTicket"){
  // build ticket edit modal window
  $ticket_modal=new str_modal("ticket_edit");
  $ticket_modal->header(stripslashes($selected_ticket->subject));
  $body_form=new str_form("submit.php?act=ticket_process&idWorkflow=".$workflow->id."&idTicket=".$selected_ticket->id,"post","ticket_edit");
  $body_form->addField("select","status",api_text("status"),NULL,"input-medium");
  $body_form->addFieldOption(1,api_text("status-opened"),($selected_ticket->status==1)?TRUE:FALSE);
  $body_form->addFieldOption(2,api_text("status-assigned"),($selected_ticket->status==2)?TRUE:FALSE);
  $body_form->addFieldOption(3,api_text("status-standby"),($selected_ticket->status==3)?TRUE:FALSE);
  $body_form->addFieldOption(41,api_text("status-closedExecuted"),($selected_ticket->status==4 && $selected_ticket->solved==1)?TRUE:FALSE);
  $body_form->addFieldOption(42,api_text("status-closedUnnecessary"),($selected_ticket->status==4 && $selected_ticket->solved==2)?TRUE:FALSE);
  $body_form->addFieldOption(40,api_text("status-closedUnexecuted"),($selected_ticket->status==4 && $selected_ticket->solved==0)?TRUE:FALSE);
  $body_form->addFieldOption(5,api_text("status-locked"),($selected_ticket->status==5)?TRUE:FALSE);
  $body_form->addField("hidden","idGroup",api_text("view-ff-idGroup"),$selected_ticket->idGroup,"input-large");
  $body_form->addField("hidden","idAssigned",api_text("view-ff-idAssigned"),$selected_ticket->idAssigned,"input-large");
  $body_form->addField("select","priority",api_text("priority"),NULL,"input-medium");
  $body_form->addFieldOption(1,api_text("priority-highest"),($selected_ticket->priority==1)?TRUE:FALSE);
  $body_form->addFieldOption(2,api_text("priority-high"),($selected_ticket->priority==2)?TRUE:FALSE);
  $body_form->addFieldOption(3,api_text("priority-medium"),($selected_ticket->priority==3)?TRUE:FALSE);
  $body_form->addFieldOption(4,api_text("priority-low"),($selected_ticket->priority==4)?TRUE:FALSE);
  $body_form->addFieldOption(5,api_text("priority-lowest"),($selected_ticket->priority==5)?TRUE:FALSE);
  /*$body_form->addField("select","difficulty",api_text("difficulty"),NULL,"input-medium");
  $body_form->addFieldOption(1,api_text("difficulty-low"),($selected_ticket->difficulty==1)?TRUE:FALSE);
  $body_form->addFieldOption(2,api_text("difficulty-medium"),($selected_ticket->difficulty==2)?TRUE:FALSE);
  $body_form->addFieldOption(3,api_text("difficulty-high"),($selected_ticket->difficulty==3)?TRUE:FALSE);*/
  if(strlen(api_account($ticket->addIdAccount)->account)){
   $body_form->addField("textarea","note",api_text("view-ff-message"),stripslashes($selected_ticket->note),"input-xlarge",NULL,FALSE,3);
   $body_form->addField("text","mail_cc",api_text("view-ff-mail_cc"),NULL,"input-xlarge",api_text("view-ff-mail_cc-placeholder"));
  }
  $body_form->addControl("submit",api_text("view-fc-submit"));
  $ticket_modal->body($body_form->render(FALSE));
 }elseif($g_act=="addTicket"){
  // build ticket add modal window
  $ticket_modal=new str_modal("ticket_add");
  $ticket_modal->header(api_text("view-ticketAdd"));
  $form_body=new str_form("submit.php?act=ticket_save&idWorkflow=".$workflow->id,"post","ticket_add");
  $form_body->addField("text","subject",api_text("view-ff-subject"),NULL,"input-xlarge",api_text("view-ff-subject-placeholder"));
  $form_body->addField("select","typology",api_text("view-ff-typology"),NULL,"input-large");
  $typologies=array(1=>"ticket-standard",2=>"ticket-external",3=>"ticket-authorization");
  foreach($typologies as $value=>$label){$form_body->addFieldOption($value,api_text($label));}
  $form_body->addField("text","mail",api_text("view-ff-mail"),NULL,"input-xlarge",api_text("view-ff-mail-placeholder"));
  $form_body->addField("hidden","idGroup",api_text("view-ff-group"),NULL,"input-large");
  $form_body->addField("hidden","idAssigned",api_text("view-ff-assigned"),NULL,"input-large");
  $form_body->addField("textarea","note",api_text("view-ff-note"),NULL,"input-xlarge");
  $form_body->addField("radio","priority",api_text("view-ff-priority"));
  $priority=array(1=>"priority-highest",2=>"priority-high",3=>"priority-medium",4=>"priority-low",5=>"priority-lowest");
  foreach($priority as $value=>$label){$form_body->addFieldOption($value,api_text($label),($value==3)?TRUE:FALSE);}
  $form_body->addField("radio","difficulty",api_text("view-ff-difficulty"));
  $difficulty=array(1=>"difficulty-low",2=>"difficulty-medium",3=>"difficulty-high");
  foreach($difficulty as $value=>$label){$form_body->addFieldOption($value,api_text($label),($value==2)?TRUE:FALSE);}
  $form_body->addField("text","slaAssignment",api_text("view-ff-slaAssignment"),NULL,"input-mini",NULL,FALSE,NULL,api_text("minutes"));
  $form_body->addField("text","slaClosure",api_text("view-ff-slaClosure"),NULL,"input-mini",NULL,FALSE,NULL,api_text("minutes"));
  $form_body->addControl("submit",api_text("view-fc-submit"));
  $ticket_modal->body($form_body->render(FALSE));
 }elseif($selected_ticket->id>0 && $g_act=="cloneTicket"){
  // build ticket edit modal window
  $ticket_modal=new str_modal("ticket_clone");
  $ticket_modal->header(stripslashes($selected_ticket->subject));
  $body_form=new str_form("submit.php?act=ticket_clone&idWorkflow=".$workflow->id."&idTicket=".$selected_ticket->id,"post","ticket_clone");
  $body_form->addField("text","subject",api_text("view-ff-subject"),$selected_ticket->subject,"input-xlarge");
  $body_form->addField("textarea","referents",api_text("view-ff-referents"),NULL,"input-xlarge",api_text("view-ff-referents-placeholder"),FALSE,10);
  $body_form->addControl("submit",api_text("view-fc-clone"));
  $ticket_modal->body($body_form->render(FALSE));
 }
 // open split
 $GLOBALS['html']->split_open();
 $GLOBALS['html']->split_span(6);
 // show workflow description list
 $workflow_dl->render();
 // split page
 $GLOBALS['html']->split_span(6);
 // show details description list
 $details_dl->render();
 // close split
 $GLOBALS['html']->split_close();
 // show tickets table
 $tickets_table->render();
 // show ticket modal windows
 if(is_object($ticket_modal)){$ticket_modal->render();}
 // show notes modal windows
 foreach($notes_modals_array as $notes_modal){$notes_modal->render();}
 // show host modal windows
 if(is_object($host_modal)){$host_modal->render();}
?>
<script type="text/javascript">
 $(document).ready(function(){
  <?php if($g_act=="addTicket"){echo "  $('#modal_ticket_add').modal('show');\n";} ?>
  <?php if($g_act=="editTicket"){echo "  $('#modal_ticket_edit').modal('show');\n";} ?>
  <?php if($g_act=="cloneTicket"){echo "  $('#modal_ticket_clone').modal('show');\n";} ?>
  // call action typology method change event
  $("form[name=ticket_add] #field_typology").trigger("change");
  // select2 idGroup
  $("form[name=ticket_edit] input[name=idGroup]").select2({
   placeholder:"<?php echo api_text("view-ff-idGroup-placeholder"); ?>",
   allowClear:true,
   ajax:{
    url:"../accounts/groups_json.inc.php",
    dataType:'json',
    data:function(term,page){return{q:term};},
    results:function(data,page){return{results:data};}
   },
   initSelection:function(element,callback){
    var id=$(element).val();
    if(id!==""){
     $.ajax("../accounts/groups_json.inc.php?q="+id,{
      dataType:"json"
     }).done(function(data){callback(data[0]);});
    }
   }
  });
  // select2 idAccountTo
  $("form[name=ticket_edit] input[name=idAssigned]").select2({
   placeholder:"<?php echo api_text("view-ff-idAssigned-placeholder"); ?>",
   minimumInputLength:2,
   allowClear:true,
   ajax:{
    url:"../accounts/accounts_json.inc.php",
    dataType:'json',
    data:function(term,page){return{q:term};},
    results:function(data,page){return{results:data};}
   },
   initSelection:function(element,callback){
    var id=$(element).val();
    if(id!==""){
     $.ajax("../accounts/accounts_json.inc.php?q="+id,{
      dataType:"json"
     }).done(function(data){callback(data[0]);});
    }
   }
  });
  // select2 idGroup
  $("form[name=ticket_add] input[name=idGroup]").select2({
   placeholder:"<?php echo api_text("view-ff-group-placeholder"); ?>",
   allowClear:true,
   ajax:{
    url:"../accounts/groups_json.inc.php",
    dataType:'json',
    data:function(term,page){return{q:term};},
    results:function(data,page){return{results:data};}
   },
   initSelection:function(element,callback){
    var id=$(element).val();
    if(id!==""){
     $.ajax("../accounts/groups_json.inc.php?q="+id,{
      dataType:"json"
     }).done(function(data){callback(data[0]);});
    }
   }
  });
  // select2 idAssigned
  $("form[name=ticket_add] input[name=idAssigned]").select2({
   placeholder:"<?php echo api_text("view-ff-assigned-placeholder"); ?>",
   minimumInputLength:2,
   allowClear:true,
   ajax:{
    url:"../accounts/accounts_json.inc.php",
    dataType:'json',
    data:function(term,page){return{q:term};},
    results:function(data,page){return{results:data};}
   },
   initSelection:function(element,callback){
    var id=$(element).val();
    if(id!==""){
     $.ajax("../accounts/accounts_json.inc.php?q="+id,{
      dataType:"json"
     }).done(function(data){callback(data[0]);});
    }
   }
  });
  // ticket_add validation
  $('form[name=ticket_add]').validate({
   ignore:null,
   rules:{
    subject:{required:true,minlength:3},
    slaAssigned:{number:true},
    slaClosure:{number:true}
   },
   submitHandler:function(form){form.submit();}
  });
 });
 // toggle action typology
 $("form[name=ticket_add] #field_typology").change(function(){
  if($(this).find("option:selected").val()==="1"){
   $("#field_mail").hide();
  }else{
   $("#field_mail").show();
  }
 });
</script>
<?php } ?>