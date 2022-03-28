<?php
/* -------------------------------------------------------------------------- *\
|* -[ Workflows - Sendmail ]------------------------------------------------- *|
\* -------------------------------------------------------------------------- */
$checkPermission="workflows_view";
include("template.inc.php");
function content(){
 // get workflow object
 $workflow=api_workflows_workflow($_GET['idWorkflow'],TRUE);
 if(!$workflow->id){echo api_text("workflowNotFound");return FALSE;}
 // build form
 $form=new str_form("submit.php?act=workflow_sendmail&id=".$workflow->id,"post","workflow_sendmail");
 // fields
 $form->addField("text","to",api_text("sendmail-ff-to"),NULL,"input-xlarge",api_text("sendmail-ff-to-placeholder"));
 $form->addField("text","cc",api_text("sendmail-ff-cc"),NULL,"input-xxlarge",api_text("sendmail-ff-cc-placeholder"));
 $form->addField("text","subject",api_text("sendmail-ff-subject"),stripslashes($workflow->subject),"input-xxlarge");
 $message=stripslashes($workflow->description);
 if(strlen($workflow->note)){$message.="\n\n".api_text("sendmail-ff-note").": ".stripslashes($workflow->note);}
 $form->addField("textarea","message",api_text("sendmail-ff-message"),$message,"input-xxlarge",NULL,FALSE,16);
 // controls
 $form->addControl("submit",api_text("sendmail-fc-submit"),NULL,NULL,api_text("sendmail-fc-submit-confirm"));
 $form->addControl("button",api_text("sendmail-fc-cancel"),NULL,"workflows_view.php?id=".$workflow->id);
 // show form
 $form->render();
?>
<script type="text/javascript">
 $(document).ready(function(){
  // validation
  $('form').validate({
   rules:{
    to:{required:true,email:true}
   },
   submitHandler:function(form){form.submit();}
  });
 });
</script>

<?php } ?>