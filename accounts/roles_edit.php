<?php
/* -------------------------------------------------------------------------- *\
|* -[ Accounts - Roles Edit ]------------------------------------------------ *|
\* -------------------------------------------------------------------------- */
$checkPermission="roles_edit";
include("template.inc.php");
function content(){
 // get objects
 $role=api_accounts_role($_GET['idRole']);
 // get highest level
 $highest_level=$GLOBALS['db']->queryUniqueValue("SELECT level FROM accounts_roles ORDER BY level DESC");
 // build form
 $form=new str_form("submit.php?act=role_save&idRole=".$role->id,"post","roles_edit");
 $form->addField("select","level",api_text("roles_edit-ff-level"),NULL,"input-small");
 $form->addFieldOption("",ucfirst(api_text("undefined")));
 for($i=1;$i<=($highest_level+1);$i++){$form->addFieldOption($i,$i,($role->level==$i?TRUE:FALSE));}
 $form->addField("text","name",api_text("roles_edit-ff-name"),$role->name,"input-large",api_text("roles_edit-ff-name-placeholder"));
 $form->addField("text","description",api_text("roles_edit-ff-description"),$role->description,"input-xlarge",api_text("roles_edit-ff-description-placeholder"));
 $form->addControl("submit",api_text("roles_edit-fc-submit"));
 $form->addControl("button",api_text("roles_edit-fc-cancel"),NULL,"roles_list.php?idRole=".$role->id);
 if($role->id){
  $disabled=$GLOBALS['db']->countOf("accounts_accounts_join_companies","idRole='".$role->id."'");
  $form->addControl("button",api_text("roles_edit-fc-delete"),"btn-danger","submit.php?act=role_delete&idRole=".$role->id,api_text("roles_edit-fc-delete-confirm"),$disabled);
 }
 // renderize form
 $form->render();
 // debug
 if($_SESSION["account"]->debug){pre_var_dump($role,"print","role");}
?>
<script type="text/javascript">
 $(document).ready(function(){
  // validation
  $('form[name=roles_edit]').validate({
   rules:{
    level:{required:true},
    name:{required:true,minlength:3}
   },
   submitHandler:function(form){form.submit();}
  });
 });
</script>
<?php } ?>