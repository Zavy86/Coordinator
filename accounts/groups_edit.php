<?php
/* ------------------------------------------------------------------------- *\
|* -[ Accounts - Groups Edit ]---------------------------------------------- *|
\* ------------------------------------------------------------------------- */
if($_GET['id']>0){$checkPermission="groups_edit";}else{$checkPermission="groups_add";}
include("template.inc.php");
function content(){
 // acquire variables
 $g_id=$_GET['id'];
 if(!isset($g_id)){$g_id=0;}
 // get group object
 $group=$GLOBALS['db']->queryUniqueObject("SELECT * FROM accounts_groups WHERE id='".$g_id."'");
 // form fields array
 $ff_array=array();
  $fo_array=array();
  $fo_array[]=api_formFieldOption(0,api_text("groups_edit-fo-main"),TRUE);
  $groups=$GLOBALS['db']->query("SELECT * FROM accounts_groups WHERE idGroup='0' AND id<>".$g_id." ORDER BY name ASC");
  while($r_group=$GLOBALS['db']->fetchNextObject($groups)){
   if($group->idGroup==$r_group->id){$selected=TRUE;}else{$selected=FALSE;}
   $fo_array[]=api_formFieldOption($r_group->id,stripslashes($r_group->name),$selected);
  }
 $ff_array[]=api_formField("select","idGroup",api_text("groups_edit-ff-group"),NULL,"input-medium",NULL,$fo_array);
 $ff_array[]=api_formField("text","name",api_text("groups_edit-ff-name"),stripslashes($group->name),"input-large",api_text("groups_edit-ff-name-placeholder"));
 $ff_array[]=api_formField("text","description",api_text("groups_edit-ff-description"),stripslashes($group->description),"input-xlarge",api_text("groups_edit-ff-description-placeholder"));

 // form controls array
 $fc_array=array();
 $fc_array[]=api_formControl("submit",api_text("groups_edit-fc-save"));
 $fc_array[]=api_formControl("button",api_text("groups_edit-fc-cancel"),NULL,"groups_list.php");
 if($group->id>0 && api_checkPermission("accounts","groups_delete")){
  $fc_array[]=api_formControl("button",api_text("groups_edit-fc-delete"),"btn-danger","submit.php?act=group_delete&id=".$group->id,api_text("groups_edit-fc-delete-confirm"));
 }
 // print form
 api_form($ff_array,$fc_array,"submit.php?act=group_save&id=".$group->id,"post","groups");
?>
<script type="text/javascript">
 $(document).ready(function(){
  $('input[type="submit"]').attr('disabled','disabled');
  $('form').change(function(){
   $('input[type="submit"]').removeAttr('disabled');
  });
  // validation
  $('form').validate({
   rules:{
    name:{required:true,minlength:2}
   },
   submitHandler:function(form){form.submit();}
  });
 });
</script>
<?php
}
?>