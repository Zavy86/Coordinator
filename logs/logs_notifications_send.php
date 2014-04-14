<?php
/* -------------------------------------------------------------------------- *\
|* -[ Logs - Send ]---------------------------------------------------------- *|
\* -------------------------------------------------------------------------- */
$checkPermission="notifications_send";
include("template.inc.php");
function content(){
 // build form
 $form=new str_form("submit.php?act=notification_send","post","notification_send");
 $form->addField("radio","typology",api_text("notifications_send-ff-typology"),NULL,"inline");
 $form->addFieldOption(1,api_text("notifications_send-fo-notice"),TRUE);
 $form->addFieldOption(2,api_text("notifications_send-fo-action"));
 $form->addField("radio","to",api_text("notifications_send-ff-recipient"),NULL,"inline");
 $form->addFieldOption(1,api_text("notifications_send-fo-user"),TRUE);
 $form->addFieldOption(2,api_text("notifications_send-fo-group"));
 if(api_checkPermission("logs","notifications_send_all")){
  $form->addFieldOption(3,api_text("notifications_send-fo-all"));
 }
 $form->addField("hidden","idAccountTo",api_text("notifications_send-ff-user"),NULL,"input-xlarge");
 $form->addField("hidden","idGroup",api_text("notifications_send-ff-group"),NULL,"input-xlarge");
 $form->addField("text","subject",api_text("notifications_send-ff-subject"),NULL,"input-xxlarge",api_text("notifications_send-ff-subject-placeholder"));
 $form->addField("textarea","message",api_text("notifications_send-ff-message"),NULL,"input-xxlarge",api_text("notifications_send-ff-message-placeholder"));
 $form->addField("text","link",api_text("notifications_send-ff-link"),NULL,"input-xxlarge",api_text("notifications_send-ff-link-placeholder"));
 $form->addControl("submit",api_text("notifications_send-fc-submit"));
 $form->addControl("button",api_text("notifications_send-fc-cancel"),NULL,"logs_notifications_list.php?s=1");
 // show form
 $form->render();
?>
<script type="text/javascript">
 $(document).ready(function(){
  $("#field_idGroup").hide();
  // change radio to
  $("input[name='to']").change(function(){
   if($("input[name='to']:checked").val()==='1'){
    $("#field_idAccountTo").show();
    $("#field_idGroup").hide();
   }else if($("input[name='to']:checked").val()==='2'){
    $("#field_idAccountTo").hide();
    $("#field_idGroup").show();
   }else{
    $("#field_idAccountTo").hide();
    $("#field_idGroup").hide();
   }
  });
  // select2 idAccountTo
  $("input[name='idAccountTo']").select2({
   placeholder:"<?php echo api_text("notifications_send-ff-user-placeholder"); ?>",
   minimumInputLength:2,
   ajax:{
    url:"../accounts/accounts_json.inc.php",
    dataType:'json',
    data:function(term,page){return{q:term};},
    results:function(data,page){return{results:data};}
   }
  });
  // select2 idGroup
  $("input[name='idGroup']").select2({
   placeholder:"<?php echo api_text("notifications_send-ff-group-placeholder"); ?>",
   ajax:{
    url:"../accounts/groups_json.inc.php",
    dataType:'json',
    data:function(term,page){return{q:term};},
    results:function(data,page){return{results:data};}
   }
  });
  // validation
  $('form').validate({
   rules:{
    subject:{required:true,minlength:3},
    message:{required:true,minlength:3}
   },
   submitHandler:function(form){form.submit();}
  });
 });
</script>
<?php } ?>