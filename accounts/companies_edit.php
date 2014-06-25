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
 // build form
 $form=new str_form("submit.php?act=company_save&id=".$company->id,"post","companies");
 $form->splitOpen();
 $form->addField("text","company",api_text("companies_edit-ff-company"),stripslashes($company->company),"input-medium",api_text("companies_edit-ff-company-placeholder"));
 $form->addField("text","division",api_text("companies_edit-ff-division"),stripslashes($company->division),"input-medium",api_text("companies_edit-ff-division-placeholder"));
 $form->addField("text","name",api_text("companies_edit-ff-name"),stripslashes($company->name),"input-xlarge",api_text("companies_edit-ff-name-placeholder"));
 $form->addSeparator();
 $form->addField("text","fiscal_name",api_text("companies_edit-ff-fiscal_name"),stripslashes($company->fiscal_name),"input-xlarge",api_text("companies_edit-ff-fiscal_name-placeholder"));
 $form->addField("text","fiscal_vat",api_text("companies_edit-ff-fiscal_vat"),stripslashes($company->fiscal_vat),"input-medium");
 $form->addField("text","fiscal_code",api_text("companies_edit-ff-fiscal_code"),stripslashes($company->fiscal_code),"input-medium");
 $form->addField("text","fiscal_rea",api_text("companies_edit-ff-fiscal_rea"),stripslashes($company->fiscal_rea),"input-medium");
 $form->addField("text","fiscal_capital",api_text("companies_edit-ff-fiscal_capital"),stripslashes($company->fiscal_capital),"input-small");
 $form->addField("text","fiscal_currency",api_text("companies_edit-ff-fiscal_currency"),stripslashes($company->fiscal_currency),"input-mini");
 $form->splitSpan();
 $form->addField("text","address_address",api_text("companies_edit-ff-address_address"),stripslashes($company->address_address),"input-xlarge");
 $form->addField("text","address_zip",api_text("companies_edit-ff-address_zip"),stripslashes($company->address_zip),"input-mini");
 $form->addField("text","address_city",api_text("companies_edit-ff-address_city"),stripslashes($company->address_city),"input-xlarge");
 $form->addField("text","address_district",api_text("companies_edit-ff-address_district"),stripslashes($company->address_district),"input-mini");
 $form->addField("text","address_country",api_text("companies_edit-ff-address_country"),stripslashes($company->address_country),"input-medium");
 $form->addSeparator();
 $form->addField("text","phone_office",api_text("companies_edit-ff-phone_office"),stripslashes($company->phone_office),"input-large");
 $form->addField("text","phone_mobile",api_text("companies_edit-ff-phone_mobile"),stripslashes($company->phone_mobile),"input-large");
 $form->addField("text","phone_fax",api_text("companies_edit-ff-phone_fax"),stripslashes($company->phone_fax),"input-large");
 $form->addField("text","mail",api_text("companies_edit-ff-mail"),stripslashes($company->mail),"input-large");
 $form->splitClose();
 $form->addControl("submit",api_text("companies_edit-fc-save"));
 $form->addControl("button",api_text("companies_edit-fc-cancel"),NULL,"companies_list.php");
 if($company->id>0 && api_checkPermission("accounts","companies_delete")){
  $form->addControl("button",api_text("companies_edit-fc-delete"),"btn-danger","submit.php?act=company_delete&id=".$company->id,api_text("companies_edit-fc-delete-confirm"));
 }
 // show form
 $form->render();
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
    name:{required:true,minlength:3},
    fiscal_capital:{digits:true},
    fiscal_currency:{maxlength:3}
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