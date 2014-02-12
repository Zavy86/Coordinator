<?php
/* -------------------------------------------------------------------------- *\
|* -[ Dashboard - Notifications Send ]--------------------------------------- *|
\* -------------------------------------------------------------------------- */
$checkPermission="notifications_send";
include("template.inc.php");
function content(){
 // form fields array
 $ff_array=array();
  $fo_array=array();
  $fo_array[]=api_formFieldOption(1,api_text("fo-notice"),TRUE);
  $fo_array[]=api_formFieldOption(2,api_text("fo-action"));
 $ff_array[]=api_formField("radio","typology",api_text("ff-typology"),NULL,"inline",NULL,$fo_array);
  $fo_array=array();
  $fo_array[]=api_formFieldOption(1,api_text("fo-user"),TRUE);
  $fo_array[]=api_formFieldOption(2,api_text("fo-group"));
  if(api_checkPermission("dashboard","notifications_send_all")){
   $fo_array[]=api_formFieldOption(3,api_text("fo-all"));
  }
 $ff_array[]=api_formField("radio","to",api_text("ff-recipient"),NULL,"inline",NULL,$fo_array);
 $ff_array[]=api_formField("hidden","idAccountTo",api_text("ff-user"),NULL,"input-xlarge");
 $ff_array[]=api_formField("hidden","idGroup",api_text("ff-group"),NULL,"input-xlarge");
 $ff_array[]=api_formField("text","subject",api_text("ff-subject"),NULL,"input-xxlarge",api_text("ff-subject-placeholder"));
 $ff_array[]=api_formField("textarea","message",api_text("ff-message"),NULL,"input-xxlarge",api_text("ff-message-placeholder"));
 $ff_array[]=api_formField("text","link",api_text("ff-link"),NULL,"input-xxlarge",api_text("ff-link-placeholder"));
 // form controls array
 $fc_array=array();
 $fc_array[]=api_formControl("submit",api_text("fc-submit"));
 $fc_array[]=api_formControl("button",api_text("fc-cancel"),NULL,"notification_list.php");
 // print form
 api_form($ff_array,$fc_array,"submit.php?act=notification_send","post","notification");
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
   placeholder:"<?php echo api_text("ff-user-placeholder"); ?>",
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
   placeholder:"Oppure seleziona un gruppo",
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