<?php
/* -------------------------------------------------------------------------- *\
|* -[ Accounts - Groups Edit ]----------------------------------------------- *|
\* -------------------------------------------------------------------------- */
$checkPermission="groups_edit";
include("template.inc.php");

// build options group DA FARE API <-------------------------------------------- todo
function options_group($groups,&$form,$level=0,$selected=NULL,$skip=NULL){
 if(!is_array($groups)){return FALSE;}
 foreach($groups as $group){
  if($group->id==$skip){continue;}
  $pre=NULL;
  for($i=0;$i<$level;$i++){$pre.="&nbsp;&nbsp;&nbsp;";}
  $form->addFieldOption($group->id,$pre.$group->label,($group->id==$selected?TRUE:FALSE));
  options_group($group->groups,$form,($level+1),$selected,$skip);
 }
 return TRUE;
}

function content(){
 // get objects
 $group=api_accounts_group($_GET['idGroup'],FALSE);
 $company=api_accounts_company($group->idCompany,FALSE);
 // build form
 $form=new str_form("submit.php?act=group_save&idGroup=".$group->id,"post","groups_edit");
 // company
 if($company->id){
  $form->addField("hidden","idCompany",NULL,$company->id);
 }else{
  $form->addField("select","idCompany",api_text("groups_edit-ff-company"),NULL,"input-large");
  $form->addFieldOption("",ucfirst(api_text("undefined")));
  foreach(api_accounts_companies()->results as $result){
   // <------------------------------------------------------------------------- todo check for administrable
   $form->addFieldOption($result->id,$result->name);
  }
 }
 // father
 $form->addField("select","idGroup",api_text("groups_edit-ff-group"),NULL,"input-medium");
 $form->addFieldOption(0,api_text("groups_edit-fo-main"));
 options_group(api_accounts_groups($group->idCompany)->results,$form,0,$group->idGroup,$group->id);
 // name
 $form->addField("text","name",api_text("groups_edit-ff-name"),stripslashes($group->name),"input-large",api_text("groups_edit-ff-name-placeholder"));
 // description
 $form->addField("text","description",api_text("groups_edit-ff-description"),stripslashes($group->description),"input-xlarge",api_text("groups_edit-ff-description-placeholder"));
 // controls
 $form->addControl("submit",api_text("groups_edit-fc-submit"));
 $form->addControl("button",api_text("groups_edit-fc-cancel"),NULL,"groups_list.php?idGroup=".$group->id);
 if($group->id>0){$form->addControl("button",api_text("groups_edit-fc-delete"),"btn-danger","submit.php?act=group_delete&idGroup=".$group->id,api_text("groups_edit-fc-delete-confirm"));}
 // renderize form
 $form->render();
 // debug
 if($_SESSION["account"]->debug){pre_var_dump($group,"print","group");}
?>
<script type="text/javascript">
 $(document).ready(function(){
  // chained father
  $("#groups_edit_input_idGroup").remoteChained({
   parents:"#groups_edit_input_idCompany",
   url:"../accounts/groups_chained_json.inc.php",
   loading:"Loading..",
   clear:true
  });
  // validation
  $('form[name=groups_edit]').validate({
   rules:{
    idCompany:{required:true},
    idGroup:{required:true},
    name:{required:true,minlength:2}
   },
   submitHandler:function(form){form.submit();}
  });
 });
</script>
<?php } ?>