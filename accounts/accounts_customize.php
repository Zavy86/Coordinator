<?php
/* -------------------------------------------------------------------------- *\
|* -[ Accounts - Accounts Customize ]---------------------------------------- *|
\* -------------------------------------------------------------------------- */
include("template.inc.php");
function content(){
 // get objects
 $account=api_accounts_account();
 // build account dynamic list
 $account_dl=new str_dl("br","dl-horizontal");
 $account_dl->addElement(api_text("accounts_customize-ff-avatar"),api_image($account->avatar,"img-polaroid",125,NULL,TRUE));
 // build account form
 $account_form=new str_form("submit.php?act=account_customize&idAccount=".$account->id,"post","accounts_customize");
 $account_form->addField("file","avatar","&nbsp;",NULL,"input-large",api_text("accounts_customize-ff-avatar-placeholder"));
 $account_form->addField("text","name",api_text("accounts_customize-ff-name"),stripslashes($account->name),"input-large",api_text("accounts_customize-ff-name-placeholder"),FALSE,NULL,NULL,(api_checkPermission2("accounts","accounts_customize")?FALSE:TRUE));
 if(strlen($account->ldap)){
  $account_form->addField("text","account",api_text("accounts_customize-ff-mail"),stripslashes($account->account),"input-xlarge",api_text("accounts_customize-ff-account-placeholder"),FALSE,NULL,NULL,(api_checkPermission("accounts","accounts_customize")?FALSE:TRUE));
 }else{
  $account_form->addField("text","account",api_text("accounts_customize-ff-account"),stripslashes($account->account),"input-xlarge",api_text("accounts_customize-ff-account-placeholder"),FALSE,NULL,NULL,(api_checkPermission("accounts","accounts_customize")?FALSE:TRUE));
  $account_form->addField("password","password",api_text("accounts_customize-ff-password"),NULL,"input-large",api_text("accounts_customize-ff-password-placeholder"));
  $account_form->addField("password","confirm",api_text("accounts_customize-ff-confirm"),NULL,"input-large",api_text("accounts_customize-ff-confirm-placeholder"));
 }
 $account_form->addField("text","phone",api_text("accounts_customize-ff-phone"),$account->phone,"input-medium",api_text("accounts_customize-ff-phone-placeholder"));
 $account_form->addField("select","language",api_text("accounts_customize-ff-language"),NULL,"input-medium");
 foreach(api_language_availables() as $key=>$language){$account_form->addFieldOption($key,$language." (".$key.")",($key==$account->language?TRUE:FALSE));}
 // controls
 $account_form->addControl("submit",api_text("accounts_customize-fc-submit"));
 $account_form->addControl("button",api_text("accounts_customize-fc-cancel"),NULL,"../index.php");
 // build companies table
 $companies_table=new str_table(api_text("accounts_customize-companies-tr-unvalued"));
 // build companies table headers
 $companies_table->addHeader("&nbsp;",NULL,"16");
 $companies_table->addHeader(api_text("accounts_customize-companies-th-name"),NULL,"100%");
 $companies_table->addHeader(api_text("accounts_customize-companies-th-role"),"nowarp text-right");
 // build companies table fields
 foreach($account->companies as $company){
  // make main
  if($company->main){$main_td=api_icon('icon-star',api_text("accounts_customize-companies-td-main"));}
  else{$main_td=api_icon('icon-star-empty');}
  // build company row
  $companies_table->addRow();
  $companies_table->addField($main_td,"text-center");
  $companies_table->addField($company->name,"nowarp");
  $companies_table->addField($company->role->name,"nowarp text-right");
 }
 // companies groups
 $companies_groups_array=array();
 // cycle companies
 foreach($account->companies as $company){
  // build groups table
  $groups_table=new str_table(api_text("accounts_customize-groups-tr-unvalued"));
  // build companies table headers
  $groups_table->addHeader("&nbsp;",NULL,"16");
  $groups_table->addHeader(api_text("accounts_customize-groups-th-name",$company->name),NULL,"100%");
  // build companies table fields
  foreach($company->groups as $group){
   // make del and main
   if($group->main){$main_td=api_icon('icon-star',api_text("accounts_customize-groups-td-main"));}
   else{$main_td=api_icon('icon-star-empty');}
   // build company row
   $groups_table->addRow();
   $groups_table->addField($main_td,"text-center");
   $groups_table->addField($group->path.$group->label);
  }
  // add table to array
  $companies_groups_array[]=$groups_table;
 }
 // open split
 $GLOBALS['html']->split_open();
 $GLOBALS['html']->split_span(6);
 // renderize account dynamic list
 if(is_object($account_dl)){$account_dl->render();}
 // renderize account form
 if(is_object($account_form)){$account_form->render();}
 // split page
 $GLOBALS['html']->split_span(6);
 // renderize companies table
 if(is_object($companies_table)){$companies_table->render();}
 // cycle associated companies
 if(is_array($companies_groups_array)){
  foreach($companies_groups_array as $company_groups){
   // renderize groups table
   if(is_object($company_groups)){$company_groups->render();}
  }
 }
 // close split
 $GLOBALS['html']->split_close();
 // debug
 if($_SESSION["account"]->debug){pre_var_dump($account,"print","account");}
?>
<script type="text/javascript">
 $(document).ready(function(){
  // validation account
  $('form[name=accounts_customize]').validate({
   rules:{
<?php
 if(!strlen($account->ldap)){
  echo "    password:{minlength:6},\n";
  echo "    confirm:{minlength:6,equalTo:\"#accounts_customize_input_password\"},\n";
 }
?>
    name:{required:true,minlength:3}
   },
   submitHandler:function(form){form.submit();}
  });
 });
</script>
<?php } ?>