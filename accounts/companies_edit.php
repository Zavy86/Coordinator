<?php
/* -------------------------------------------------------------------------- *\
|* -[ Accounts - Companies Edit ]-------------------------------------------- *|
\* -------------------------------------------------------------------------- */
if($_GET['id']>0){$checkPermission="companies_edit";}else{$checkPermission="companies_add";}
include("template.inc.php");
function content(){
 // acquire variables
 $g_id=$_GET['id'];
 if(!isset($g_id)){$g_id=0;}
 // get company object
 $company=$GLOBALS['db']->queryUniqueObject("SELECT * FROM accounts_companies WHERE id='".$g_id."'");
 // form fields array
 $ff_array=array();
 $ff_array[]=api_formField("text","company",api_text("companies_edit-ff-company"),stripslashes($company->company),"input-medium",api_text("companies_edit-ff-company-placeholder"));
 $ff_array[]=api_formField("text","division",api_text("companies_edit-ff-division"),stripslashes($company->division),"input-medium",api_text("companies_edit-ff-division-placeholder"));
 $ff_array[]=api_formField("text","name",api_text("companies_edit-ff-name"),stripslashes($company->name),"input-xlarge",api_text("companies_edit-ff-name-placeholder"));
 // form controls array
 $fc_array=array();
 $fc_array[]=api_formControl("submit",api_text("companies_edit-fc-save"));
 $fc_array[]=api_formControl("button",api_text("companies_edit-fc-cancel"),NULL,"companies_list.php");
 if($company->id>0 && api_checkPermission("accounts","companies_delete")){
  $fc_array[]=api_formControl("button",api_text("companies_edit-fc-delete"),"btn-danger","submit.php?act=company_delete&id=".$company->id,api_text("companies_edit-fc-delete-confirm"));
 }
 // print form
 api_form($ff_array,$fc_array,"submit.php?act=company_save&id=".$company->id,"post","companies");
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
    company:{required:true,minlength:3},
    division:{required:true,minlength:3},
    name:{required:true,minlength:3}
   },
   highlight:function(label){$(label).closest('.control-group').removeClass('success').addClass('error');},
   success:function(label){$(label).closest('.control-group').addClass('success');},
   submitHandler:function(form){form.submit();}
  });
 });
</script>
<?php
}
?>