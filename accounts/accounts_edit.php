<?php
/* -------------------------------------------------------------------------- *\
|* -[ Accounts - Accounts Edit ]--------------------------------------------- *|
\* -------------------------------------------------------------------------- */
$checkPermission="accounts_edit";
include("template.inc.php");
function content(){
 // acquire variables
 if(!isset($_GET['idAccount'])){$_GET['idAccount']=0;}
 // get objects
 $account=api_accounts_account($_GET['idAccount']);
 // build account dynamic list
 if($account->id>1){
  $account_dl=new str_dl("br","dl-horizontal");
  $account_dl->addElement(api_text("accounts_edit-ff-avatar"),api_image($account->avatar,"img-polaroid",125,NULL,TRUE));
 }
 // build account form
 $account_form=new str_form("submit.php?act=account_save&idAccount=".$account->id,"post","accounts_edit");
 // enabled
 if($account->id>1){
  $account_form->addField("checkbox","enabled","&nbsp;");
  $account_form->addFieldOption(1,api_text("accounts_edit-fo-enabled"),($account->enabled?TRUE:FALSE),($account->del?TRUE:FALSE));
 }
 // name
 $account_form->addField("text","name",api_text("accounts_edit-ff-name"),stripslashes($account->name),"input-large",api_text("accounts_edit-ff-name-placeholder"));
 // account
 if($account->id<>1){
  if(api_getOption("ldap")){
   $account_form->addField("text","account",api_text("accounts_edit-ff-mail"),stripslashes($account->account),"input-xlarge",api_text("accounts_edit-ff-account-placeholder"));
   $account_form->addField("text","ldap",api_text("accounts_edit-ff-ldap"),stripslashes($account->ldap),"input-large",api_text("accounts_edit-ff-ldap-placeholder"));
  }else{
   $account_form->addField("text","account",api_text("accounts_edit-ff-account"),stripslashes($account->account),"input-xlarge",api_text("accounts_edit-ff-account-placeholder"));
   $account_form->addField("hidden","ldap",NULL,stripslashes($account->ldap));
  }
 }
 // phone
 $account_form->addField("text","phone",api_text("accounts_edit-ff-phone"),$account->phone,"input-medium",api_text("accounts_edit-ff-phone-placeholder"));
 // language
 if($account->id<>1){
  $account_form->addField("select","language",api_text("accounts_edit-ff-language"),NULL,"input-medium");
  foreach(api_language_availables() as $key=>$language){$account_form->addFieldOption($key,$language." (".$key.")",($key==$account->language?TRUE:FALSE));}
 }
 // super user
 if($account->id>1 && api_checkPermission("accounts","accounts_manage")){
  $account_form->addField("checkbox","superuser","&nbsp;");
  $account_form->addFieldOption(1,api_text("accounts_edit-fo-superuser"),($account->superuser?TRUE:FALSE),($account->del?TRUE:FALSE));
 }else{
  $account_form->addField("hidden","superuser",NULL,$account->superuser);
 }
 // controls
 $account_form->addControl("submit",api_text("accounts_edit-fc-submit"));
 $account_form->addControl("button",api_text("accounts_edit-fc-cancel"),NULL,"accounts_list.php");
 if($account->id>0 && api_checkPermission("accounts","accounts_manage")){
  if($account->del){$account_form->addControl("button",api_text("accounts_edit-fc-undelete"),"btn-warning","submit.php?act=account_undelete&idAccount=".$account->id);}
  else{$account_form->addControl("button",api_text("accounts_edit-fc-delete"),"btn-danger","submit.php?act=account_delete&idAccount=".$account->id,api_text("accounts_edit-fc-delete-confirm"));}
 }
 if($account->id>1 && (($_SESSION['account']->administrator && $account->id<>api_accountId())||api_accountId()==1)){
  $account_form->addControl("button",api_text("accounts_edit-fc-interpret"),"btn-success","submit.php?act=account_interpret&idAccount=".$account->id,api_text("accounts_edit-fc-interpret-confirm"));
 }
 // companies
 if($account->id>1){
  // build companies table
  $companies_table=new str_table(api_text("accounts_edit-companies-tr-unvalued"));
  // build companies table headers
  $companies_table->addHeader("&nbsp;",NULL,"16");
  $companies_table->addHeader(api_text("accounts_edit-companies-th-name"),NULL,"100%");
  $companies_table->addHeader(api_text("accounts_edit-companies-th-role"),"nowarp text-right");
  $companies_table->addHeader("&nbsp;",NULL,"16");
  // build companies table fields
  foreach($account->companies as $company){
   // check permissions --------------------------------------------------------
   // make del and main
   $del_td=api_link("submit.php?act=account_company_remove&idAccount=".$account->id."&idCompany=".$company->id,api_icon('icon-trash',api_text("accounts_edit-companies-td-remove")),NULL,NULL,FALSE,api_text("accounts_edit-companies-td-remove-confirm"));
   if($company->main){
    $main_td=api_icon('icon-star',api_text("accounts_edit-companies-td-main"));
    if(count($account->companies)>1){$del_td="&nbsp;";}
   }else{
    $main_td=api_link("submit.php?act=account_company_mainize&idAccount=".$account->id."&idCompany=".$company->id,api_icon('icon-star-empty',api_text("accounts_edit-companies-td-mainize")));
   }
   // build company row
   $companies_table->addRow();
   $companies_table->addField($main_td,"text-center");
   $companies_table->addField($company->name,"nowarp");
   $companies_table->addField($company->role->name,"nowarp text-right");
   $companies_table->addField($del_td,"text-center");
  }
  // get associated companies
  $companies_array=array();
  $companies=api_accounts_companies();
  foreach($companies->results as $company){
   // skip not administrable and assigned companies ----------------------------
   //if(!api_accounts_account()->superuser){continue;}
   //if(array_key_exists($company->id,$account->companies)){continue;}
   $companies_array[]=$company;
  }
  if(count($companies_array)){
   // build companies form
   $companies_form=new str_form("submit.php?act=account_company_add&idAccount=".$account->id,"post","accounts_edit_companies");
   $companies_box="<select name='idCompany' class='input-xlarge'>\n";
   $companies_box.="<option value=''>".api_text("accounts_edit-companies-fo-add")."</option>\n";
   foreach($companies_array as $company){
    $companies_box.="<option value='".$company->id."'>".$company->name."</option>\n";
   }
   $companies_box.="</select>\n";
   $companies_box.="<select name='idRole' class='input-medium'>\n";
   foreach(api_accounts_roles()->results as $role){
    $label=$role->level." &minus; ".$role->name;
    if(strlen($role->description)){$label.=" (".$role->description.")";}
    $companies_box.="<option value='".$role->id."'>".$label."</option>\n";
   }
   $companies_box.="</select>\n";
   $companies_box.="<input type='submit' name='submit' class='btn' value='+'>\n";
   $companies_form->addCustomField(NULL,$companies_box);
  }
 }
 // companies groups
 if($account->id>1){
  $companies_groups_array=array();
  // build options group
  function options_group($array,$level=0){
   foreach($array as $group){
    $pre=NULL;
    for($i=0;$i<$level;$i++){$pre.="&nbsp;&nbsp;&nbsp;";}
    $return.="<option value='".$group->id."'>".$pre.$group->label."</option>\n";
    $return.=options_group($group->groups,($level+1));
   }
   return $return;
  }
  // cycle companies
  foreach($account->companies as $company){
   // biuld company groups object
   $company_groups=new stdClass();
   $company_groups->idCompany=$company->id;
   // build groups table
   $groups_table=new str_table(api_text("accounts_edit-groups-tr-unvalued"));
   // build companies table headers
   $groups_table->addHeader("&nbsp;",NULL,"16");
   $groups_table->addHeader(api_text("accounts_edit-groups-th-name",$company->name),NULL,"100%");
   $groups_table->addHeader("&nbsp;",NULL,"16");
   // build companies table fields
   foreach($company->groups as $group){
    // make del and main
    $del_td=api_link("submit.php?act=account_group_remove&idAccount=".$account->id."&idCompany=".$company->id."&idGroup=".$group->id,api_icon('icon-trash',api_text("accounts_edit-groups-td-remove")),NULL,NULL,FALSE,api_text("accounts_edit-groups-td-remove-confirm"));
    if($group->main){
     $main_td=api_icon('icon-star',api_text("accounts_edit-groups-td-main"));
     if(count($company->groups)>1){$del_td="&nbsp;";}
    }else{
     $main_td=api_link("submit.php?act=account_group_mainize&idAccount=".$account->id."&idCompany=".$company->id."&idGroup=".$group->id,api_icon('icon-star-empty',api_text("accounts_edit-groups-td-mainize")));
    }
    // build company row
    $groups_table->addRow();
    $groups_table->addField($main_td,"text-center");
    $groups_table->addField($group->path.$group->label);
    $groups_table->addField($del_td,"text-center");
   }
   // link table
   $company_groups->table=$groups_table;
   // get company groups
   $groups=api_accounts_groups($company->id);
   // build companies form
   $groups_form=new str_form("submit.php?act=account_group_add&idAccount=".$account->id."&idCompany=".$company->id,"post","accounts_edit_company".$company->id."_groups");
   $groups_box="<select name='idGroup' class='input-xlarge'>\n";
   $groups_box.="<option value=''>".api_text("accounts_edit-groups-fo-add")."</option>\n";
   $groups_box.=options_group($groups->results);
   $groups_box.="</select>\n";
   $groups_box.="<input type='submit' name='submit' class='btn' value='+'>\n";
   $groups_form->addCustomField(NULL,$groups_box);
   // link form
   $company_groups->form=$groups_form;
   // add element
   $companies_groups_array[]=$company_groups;
  }
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
 // renderize companies form
 if(is_object($companies_form)){$companies_form->render();}
 // cycle associated companies
 if(is_array($companies_groups_array)){
  foreach($companies_groups_array as $company_groups){
   // renderize groups table
   if(is_object($company_groups->table)){$company_groups->table->render();}
   // renderize groups form
   if(is_object($company_groups->form)){$company_groups->form->render();}
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
  $('form[name=accounts_edit]').validate({
   rules:{
    name:{required:true,minlength:3},
<?php
 if(api_getOption("ldap")){
  echo "    account:{email:true}\n";
 }else{
  echo "    account:{required:true,email:true}\n";
 }
?>
   },
   submitHandler:function(form){form.submit();}
  });
  // validation companies
  $('form[name=accounts_edit_companies]').validate({
   rules:{
    idCompany:{required:true}
   },
   submitHandler:function(form){form.submit();}
  });
<?php
  // cycle companies
  if(is_array($companies_groups_array)){
   foreach($companies_groups_array as $company_groups){
    echo "  // company ".$company_groups->idCompany." groups\n";
    echo "  $('form[name=accounts_edit_company".$company_groups->idCompany."_groups]').validate({rules:{idGroup:{required:true}},submitHandler:function(form){form.submit();}});\n";
   }
  }
?>
 });
</script>
<?php } ?>