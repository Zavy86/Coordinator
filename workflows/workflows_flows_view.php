<?php
/* -------------------------------------------------------------------------- *\
|* -[ Workflows - Flow View ]------------------------------------------------ *|
\* -------------------------------------------------------------------------- */
$checkPermission="workflows_admin";
require_once("template.inc.php");
function content(){
 // acquire variables
 $g_idField=$_GET['idField'];
 if(!$g_idField){$g_idField=0;}
 $g_idAction=$_GET['idAction'];
 if(!$g_idAction){$g_idAction=0;}
 $g_act=$_GET['act'];
 if(!$g_act){$g_act=NULL;}
 // get flow object
 $flow=api_workflows_flow($_GET['idFlow']);
 // get selected field object
 $selected_field=api_workflows_flowField($_GET['idField']);
 // get selected action object
 $selected_action=api_workflows_flowAction($_GET['idAction']);
 // build workflow description list
 $flow_dl=new str_dl("br","dl-horizontal");
 $flow_dl->addElement(api_text("flows_view-dt-category"),api_workflows_categoryName($flow->idCategory,TRUE,TRUE));
 $flow_dl->addElement(api_text("flows_view-dt-subject"),"<strong>".stripslashes($flow->subject)."</strong>");
 $flow_dl->addElement(api_text("flows_view-dt-typology"),api_workflows_typology($flow->typology));
 $flow_dl->addElement(api_text("flows_view-dt-priority"),api_workflows_priority($flow->priority));
 if($flow->pinned){$flow_dl->addElement("&nbsp;",api_text("flows_view-dd-pinned"));}
 $flow_dl->addElement(api_text("flows_view-dt-sla"),$flow->sla." ".api_text("minutes"),NULL);
 // build details description list
 $details_dl=new str_dl("br","dl-horizontal");
 $details_dl->addElement(api_text("flows_view-dt-description"),nl2br(stripslashes($flow->description)));
 $details_dl->addElement(api_text("flows_view-dt-advice"),nl2br(stripslashes($flow->advice)));
 $details_dl->addElement(api_text("view-dt-guide"),"<a href='#' onClick=\"window.prompt('".api_text("view-dd-guide")."','".addslashes($flow->guide)."');\">".$flow->guide."</a>",NULL);
 // build fields table
 $fields_table=new str_table(api_text("flows_view-fields-tr-unvalued"));
 // build fields table headers
 $fields_table->addHeader("&nbsp;",NULL,34);
 $fields_table->addHeader("&nbsp;",NULL,16);
 $fields_table->addHeader(api_text("flows_view-fields-th-label"),"nowarp");
 $fields_table->addHeader(api_text("flows_view-fields-th-typology"),"nowarp");
 $fields_table->addHeader(api_text("flows_view-fields-th-name"),"nowarp");
 $fields_table->addHeader(api_text("flows_view-fields-th-value"));
 $fields_table->addHeader(api_text("flows_view-fields-th-options"),"nowarp");
 $fields_table->addHeader("&nbsp;",NULL,"100%");
 $fields_table->addHeader("&nbsp;",NULL,16);
 // build fields table rows
 foreach($flow->fields as $field){
  $fields_table->addRow();
  // position
  if($field->position>1){$position="<a href='submit.php?act=flow_field_move_up&idFlow=".$flow->id."&idField=".$field->id."'>".api_icon("icon-arrow-up")."</a>";}
  if($field->position<count($flow->fields)){$position.="<a href='submit.php?act=flow_field_move_down&idFlow=".$flow->id."&idField=".$field->id."'>".api_icon("icon-arrow-down")."</a>";}
  // build fields table fields
  $fields_table->addField($position,"nowarp");
  $fields_table->addField(($field->required)?api_icon("icon-ok-circle"):"&nbsp;");
  $fields_table->addField(stripslashes($field->label),"nowarp");
  $fields_table->addField(stripslashes($field->typology),"nowarp");
  $fields_table->addField(stripslashes($field->name),"nowarp");
  $fields_table->addField(stripslashes($field->value),"nowarp");
  $fields_table->addField(stripslashes($field->options_method),"nowarp");
  if($field->options_method=="values"){
   $fields_table->addField("<small>".nl2br(stripslashes($field->options_values))."</small>");
  }elseif($field->options_method=="query"){
   $fields_table->addField("<small>".stripslashes($field->options_query)."</small>");
  }else{
   $fields_table->addField("&nbsp;");
  }
  $fields_table->addField("<a href='workflows_flows_view.php?idFlow=".$flow->id."&idField=".$field->id."&act=editField'>".api_icon("icon-edit")."</a>");
 }
 // build actions table
 $actions_table=new str_table(api_text("flows_view-actions-tr-unvalued"));
 // build actions table headers
 $actions_table->addHeader("&nbsp;",NULL,16);
 $actions_table->addHeader(api_text("flows_view-actions-th-condition"),"nowarp");
 $actions_table->addHeader("!","text-center",16);
 $actions_table->addHeader(api_text("flows_view-actions-th-sla"),"nowarp text-center");
 $actions_table->addHeader(api_text("flows_view-actions-th-subject"),NULL,"100%");
 $actions_table->addHeader(api_text("flows_view-actions-th-assigned"),"nowarp text-right");
 $actions_table->addHeader(api_text("flows_view-actions-th-group"),"nowarp text-center");
 $actions_table->addHeader("&nbsp;",NULL,16);
 // build fields table rows
 foreach($flow->actions as $action){
  $actions_table->addRow();
  // require idAction
  $require=NULL;
  if($action->requiredAction>0){
   $required_action_name=$GLOBALS['db']->queryUniqueValue("SELECT subject FROM workflows_actions WHERE id='".$action->requiredAction."'");
   $require="<a data-toggle='popover' data-placement='top' data-content=\"[#".$action->requiredAction."] ".stripslashes(str_replace("\n","| ",$required_action_name))."\">".api_icon("icon-lock")."</i></a> ";
  }
  // condition
  if($action->conditionedField>0){
   $conditioned_field=$GLOBALS['db']->queryUniqueObject("SELECT * FROM workflows_fields WHERE id='".$action->conditionedField."'");
   $condition=stripslashes($conditioned_field->name)."=".$action->conditionedValue;
  }else{
   $condition="-";
  }
  // build actions table fields
  $actions_table->addField(api_workflows_ticketTypology($action->typology,TRUE),"nowarp");
  $actions_table->addField(stripslashes($condition),"nowarp");
  $actions_table->addField(stripslashes($action->priority),"text-center");
  $actions_table->addField($action->slaAssignment."-".$action->slaClosure,"nowarp text-center");
  $actions_table->addField($require."[#".$action->id."] ".stripslashes($action->subject));
  $actions_table->addField(api_account($action->idAssigned)->name,"nowarp text-right");
  $actions_table->addField(api_groupName($action->idGroup,TRUE,TRUE),"nowarp text-center");
  $actions_table->addField("<a href='workflows_flows_view.php?idFlow=".$flow->id."&idAction=".$action->id."&act=editAction'>".api_icon("icon-edit")."</a>");
 }
 // build fields modal window
 $field_modal=new str_modal("field_edit");
 if($selected_field->id){
  if(strlen($selected_field->label)>0){$label=stripslashes($selected_field->label);}
  else{$label=stripslashes($selected_field->name);}
  $field_modal->header(api_text("flows_view-fields-mh-field",$label));
 }else{
  $field_modal->header(api_text("flows_view-fields-mh-fieldAdd"));
 }
 // build fields modal form
 $form_body=new str_form("submit.php?act=flow_field_save&idFlow=".$flow->id."&idField=".$selected_field->id,"post","field_edit");
 $form_body->addField("text","label",api_text("flows_view-fields-ff-label"),$selected_field->label,"input-large",api_text("flows_view-fields-ff-label-placeholder"));
 $form_body->addField("select","typology",api_text("flows_view-fields-ff-typology"),NULL,"input-medium");
 $typologies=array("text","checkbox","radio","select","multiselect","textarea","file","slider","range","date","datetime","daterange","datetimerange","password","hidden");
 foreach($typologies as $typology){$form_body->addFieldOption($typology,ucwords($typology),($typology==$selected_field->typology)?TRUE:FALSE);}
 $form_body->addField("text","name",api_text("flows_view-fields-ff-name"),$selected_field->name,"input-large",api_text("flows_view-fields-ff-name-placeholder"));
 $form_body->addField("text","value",api_text("flows_view-fields-ff-value"),$selected_field->value,"input-large",api_text("flows_view-fields-ff-value-placeholder"));
 $form_body->addField("select","class",api_text("flows_view-fields-ff-class"),NULL,"input-medium");
 $form_body->addFieldOption("",api_text("flows_view-fields-ff-classNull"));
 $classes=array("input-mini","input-small","input-medium","input-large","input-xlarge","input-xxlarge");
 foreach($classes as $class){$form_body->addFieldOption($class,$class,($class==$selected_field->class)?TRUE:FALSE);}
 $form_body->addField("text","placeholder",api_text("flows_view-fields-ff-placeholder"),$selected_field->placeholder,"input-xlarge",api_text("flows_view-fields-ff-placeholder-placeholder"));
 $form_body->addField("select","options_method",api_text("flows_view-fields-ff-optionsMethod"),NULL,"input-medium");
 $methods=array("none","values","query");
 foreach($methods as $method){$form_body->addFieldOption($method,ucwords($method),($selected_field->options_method==$method)?TRUE:FALSE);}
 $form_body->addField("textarea","options_values",api_text("flows_view-fields-ff-optionsValues"),stripslashes($selected_field->options_values),"input-xlarge",api_text("flows_view-fields-ff-optionsValues-placeholder"));
 $form_body->addField("text","options_query",api_text("flows_view-fields-ff-optionsQuery"),stripslashes($selected_field->options_query),"input-xlarge",api_text("flows_view-fields-ff-optionsQuery-placeholder"));
 $form_body->addField("checkbox","required","&nbsp;");
 $form_body->addFieldOption(1,api_text("flows_view-fields-ff-required"),($selected_field->required)?TRUE:FALSE);
 $form_body->addControl("submit",api_text("flows_view-fields-fc-submit"));
 if($selected_field->id){$form_body->addControl("button",api_text("flows_view-fields-fc-delete"),"btn-danger","submit.php?act=flow_field_delete&idFlow=".$flow->id."&idField=".$selected_field->id,api_text("flows_view-fields-fc-delete-confirm"));}
 $field_modal->body($form_body->render(FALSE));
 // build actions modal window
 $action_modal=new str_modal("action_edit");
 if($selected_action->id){
  if(strlen($selected_action->label)>0){$label=stripslashes($selected_action->label);}
  else{$label=stripslashes($selected_action->name);}
  $action_modal->header(api_text("flows_view-actions-mh-action",$label));
 }else{
  $action_modal->header(api_text("flows_view-actions-mh-actionAdd"));
 }
 // build fields modal form
 $form_body=new str_form("submit.php?act=flow_action_save&idFlow=".$flow->id."&idAction=".$selected_action->id,"post","action_edit");
 $form_body->addField("text","subject",api_text("flows_view-actions-ff-subject"),$selected_action->subject,"input-xlarge",api_text("flows_view-actions-ff-subject-placeholder"));
 $form_body->addField("select","typology",api_text("flows_view-actions-ff-typology"),NULL,"input-large");
 $typologies=array(1=>"ticket-standard",2=>"ticket-external",3=>"ticket-authorization");
 foreach($typologies as $value=>$label){$form_body->addFieldOption($value,api_text($label),($value==$selected_action->typology)?TRUE:FALSE);}
 $form_body->addField("text","mail",api_text("flows_view-actions-ff-mail"),stripslashes($selected_action->mail),"input-xlarge",api_text("flows_view-actions-ff-mail-placeholder"));
 $form_body->addField("select","requiredAction",api_text("flows_view-actions-ff-requiredAction"),NULL,"input-large");
 $form_body->addFieldOption('',api_text("flows_view-actions-ff-requiredAction-null"));
 $actions=$GLOBALS['db']->query("SELECT * FROM workflows_actions WHERE idFlow='".$flow->id."' ORDER BY requiredAction ASC,subject ASC");
 while($action=$GLOBALS['db']->fetchNextObject($actions)){
  if($action->id==$selected_action->id){continue;}
  $form_body->addFieldOption($action->id,"[".$action->id."] ".stripslashes($action->subject),($action->id==$selected_action->requiredAction)?TRUE:FALSE);
 }
 $form_body->addField("select","conditionedField",api_text("flows_view-actions-ff-conditionedField"),NULL,"input-large");
 $form_body->addFieldOption('',api_text("flows_view-actions-ff-conditionedField-null"));
 $fields=$GLOBALS['db']->query("SELECT * FROM workflows_fields WHERE idFlow='".$flow->id."'");
 while($field=$GLOBALS['db']->fetchNextObject($fields)){
  $form_body->addFieldOption($field->id,stripslashes($field->label)." (".stripslashes($field->name).")",($field->id==$selected_action->conditionedField)?TRUE:FALSE);
 }
 $form_body->addField("text","conditionedValue",api_text("flows_view-actions-ff-conditionedValue"),$selected_action->conditionedValue,"input-medium");
 $form_body->addField("hidden","idGroup",api_text("flows_view-actions-ff-group"),$selected_action->idGroup,"input-large");
 $form_body->addField("hidden","idAssigned",api_text("flows_view-actions-ff-assigned"),$selected_action->idAssigned,"input-large");
 $form_body->addField("radio","priority",api_text("flows_view-actions-ff-priority"));
 $priority=array(1=>"priority-highest",2=>"priority-high",3=>"priority-medium",4=>"priority-low",5=>"priority-lowest");
 foreach($priority as $value=>$label){$form_body->addFieldOption($value,api_text($label),($value==$selected_action->priority || (!$selected_action->id && $value==3))?TRUE:FALSE);}
 $form_body->addField("radio","difficulty",api_text("flows_view-actions-ff-difficulty"));
 $difficulty=array(1=>"difficulty-low",2=>"difficulty-medium",3=>"difficulty-high");
 foreach($difficulty as $value=>$label){$form_body->addFieldOption($value,api_text($label),($value==$selected_action->difficulty || (!$selected_action->id && $value==2))?TRUE:FALSE);}
 $form_body->addField("text","slaAssignment",api_text("flows_view-actions-ff-slaAssignment"),stripslashes($selected_action->slaAssignment),"input-mini",NULL,FALSE,NULL,api_text("minutes"));
 $form_body->addField("text","slaClosure",api_text("flows_view-actions-ff-slaClosure"),stripslashes($selected_action->slaClosure),"input-mini",NULL,FALSE,NULL,api_text("minutes"));
 $form_body->addControl("submit",api_text("flows_view-actions-fc-submit"));
 if($selected_action->id){$form_body->addControl("button",api_text("flows_view-actions-fc-delete"),"btn-danger","submit.php?act=flow_action_delete&idFlow=".$flow->id."&idAction=".$selected_action->id,api_text("flows_view-actions-fc-delete-confirm"));}
 $action_modal->body($form_body->render(FALSE));
 // show flow
 echo "<h5>".api_text("flows_view-flow")." - <a href='workflows_flows_edit.php?idFlow=".$flow->id."'>".api_text("flows_view-flow-edit")."</a> - <a href='workflows_add.php?idFlow=".$flow->id."' target='_blank'>".api_text("flows_view-flow-preview")."</a></h5>\n";
 // open split
 $GLOBALS['html']->split_open();
 $GLOBALS['html']->split_span(6);
 // show workflow description list
 $flow_dl->render();
 // split page
 $GLOBALS['html']->split_span(6);
 // show details description list
 $details_dl->render();
 // close split
 $GLOBALS['html']->split_close();
 // show fields table
 echo "<h5>".api_text("flows_view-fields")." - <a href='workflows_flows_view.php?idFlow=".$flow->id."&act=addField'>".api_text("flows_view-fields-add")."</a></h5>\n";
 $fields_table->render();
 // show actions table
 echo "<h5>".api_text("flows_view-actions")." - <a href='workflows_flows_view.php?idFlow=".$flow->id."&act=addAction'>".api_text("flows_view-actions-add")."</a></h5>\n";
 $actions_table->render();
 // show fields modal windows
 $field_modal->render();
 // show actions modal windows
 $action_modal->render();
?>
<script type="text/javascript">
 $(document).ready(function(){
<?php
 switch($g_act){
  case "addField":case "editField":echo "  $('#modal_field_edit').modal('show');\n";break;
  case "addAction":case "editAction":echo "  $('#modal_action_edit').modal('show');\n";break;
 }
?>
  // call field typology change event on page load
  $("#modal_field_edit #field_typology").trigger("change");
  // call field options method change event
  $("#modal_field_edit #field_options_method").trigger("change");
  // call action typology method change event
  $("#modal_action_edit #field_typology").trigger("change");
  // call action conditioned method change event
  $("#modal_action_edit #field_conditionedField").trigger("change");
  // select2 idGroup
  $("input[name=idGroup]").select2({
   placeholder:"<?php echo api_text("flows_view-actions-ff-group-placeholder"); ?>",
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
  $("input[name=idAssigned]").select2({
   placeholder:"<?php echo api_text("flows_view-actions-ff-assigned-placeholder"); ?>",
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
  // field_edit validation
  $('form[name=field_edit]').validate({
   rules:{
    label:{required:true},
    name:{required:true,minlength:3}
   },
   submitHandler:function(form){form.submit();}
  });
  // action_edit validation
  $('form[name=action_edit]').validate({
   ignore:null,
   rules:{
    subject:{required:true,minlength:3},
    idGroup:{required:true},
    slaAssigned:{number:true},
    slaClosure:{number:true}
   },
   submitHandler:function(form){form.submit();}
  });
 });
 // toggle field typology options
 $("#modal_field_edit #field_typology").change(function(){
  switch($(this).find("option:selected").val()){
   case "checkbox":
   case "radio":
   case "select":
   case "multiselect":
    $("#field_options_method").show();
    break;
   default:
    $("#field_options_method option[value=none]").attr("selected","selected");
    $("#field_options_method").hide();
    // call options method change event
    $("#modal_field_edit #field_options_method").trigger("change");
  }
 });
 // toggle field options method
 $("#modal_field_edit #field_options_method").change(function(){
  if($(this).find("option:selected").val()==="values"){
   $("#field_options_values").show();
   $("#field_options_query").hide();
  }else if($(this).find("option:selected").val()==="query"){
   $("#field_options_values").hide();
   $("#field_options_query").show();
  }else{
   $("#field_options_values").hide();
   $("#field_options_query").hide();
  }
 });
 // toggle action typology
 $("#modal_action_edit #field_typology").change(function(){
  if($(this).find("option:selected").val()==="1"){
   $("#field_mail").hide();
  }else{
   $("#field_mail").show();
  }
 });
 // toggle action condition
 $("#modal_action_edit #field_conditionedField").change(function(){
  if($(this).find("option:selected").val()>1){
   $("#field_conditionedValue").show();
  }else{
   $("#field_conditionedValue").hide();
  }
 });
</script>
<?php } ?>