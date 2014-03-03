<?php
/* -------------------------------------------------------------------------- *\
|* -[ Accounts - Accounts Edit ]--------------------------------------------- *|
\* -------------------------------------------------------------------------- */
if($_GET['id']>0){$checkPermission="accounts_edit";}else{$checkPermission="accounts_add";}
include("template.inc.php");
function content(){
 // acquire variables
 $g_id=$_GET['id'];
 if(!isset($g_id)){$g_id=0;}
 // get account object
 $account=$GLOBALS['db']->queryUniqueObject("SELECT * FROM accounts_accounts WHERE id='".$g_id."'");
 // split window
 $GLOBALS['html']->split_open();
 $GLOBALS['html']->split_span(6);
 // build form
 $form=new str_form("submit.php?act=account_save&id=".$account->id,"post","accounts");
 $form->addField("text","name",api_text("accounts_edit-ff-name"),stripslashes($account->name),"input-large",api_text("accounts_edit-ff-name-placeholder"));
 // root checker
 if($account->id<>1){
  $form->addField("text","account",api_text("accounts_edit-ff-account"),stripslashes($account->account),"input-xlarge",api_text("accounts_edit-ff-account-placeholder"));
  $form->addField("select","typology",api_text("accounts_edit-ff-typology"),NULL,"input-medium");
  $form->addFieldOption(0,api_text("accounts_edit-fo-disabled"),($account->typology==0)?TRUE:FALSE);
  $form->addFieldOption(1,api_text("accounts_edit-fo-administrator"),($account->typology==1)?TRUE:FALSE);
  $form->addFieldOption(2,api_text("accounts_edit-fo-user"),($account->typology==2)?TRUE:FALSE);
  $form->addField("select","language",api_text("accounts_edit-ff-language"),NULL,"input-medium",NULL,$fo_array);
  $form->addFieldOption("default","Default");
  $dir="../languages/";
  if(is_dir($dir)){
   if($dh=opendir($dir)){
    while(($file=readdir($dh))!==false){
     if(substr($file,-4)==".xml" && $file<>"default.xml"){
      $language=substr($file,0,-4);
      $form->addFieldOption($language,$language,($language==$account->language)?TRUE:FALSE);
     }
    }
    closedir($dh);
   }
  }
  $form->addField("select","idCompany",api_text("accounts_edit-ff-company"),NULL,"input-large");
  $form->addFieldOption(0,api_text("accounts_edit-fo-CompanyNotAssigned"));
  $companies=$GLOBALS['db']->query("SELECT * FROM accounts_companies ORDER BY company ASC,division ASC");
  while($company=$GLOBALS['db']->fetchNextObject($companies)){
   $form->addFieldOption($company->id,$company->company." - ".$company->division,($company->id==$account->idCompany)?TRUE:FALSE);
  }
 }
 $form->addControl("submit",api_text("accounts_edit-fc-save"));
 $form->addControl("button",api_text("accounts_edit-fc-cancel"),NULL,"accounts_list.php");
 if($account->id>0 && api_checkPermission("accounts","accounts_delete")){
  $form->addControl("button",api_text("accounts_edit-fc-delete"),"btn-danger","submit.php?act=account_delete&id=".$account->id,api_text("accounts_edit-fc-delete-confirm"));
 }
 // show form
 $form->render();
 // split window
 $GLOBALS['html']->split_span(6);
 // if edit account show groups
 if($account->id>1){
  // build table
  $table=new str_table(api_text("accounts_edit-tr-unvalued"));
  // table headers
  $table->addHeader(api_text("accounts_edit-th-group"),NULL,"100%");
  $table->addHeader(api_text("accounts_edit-th-role"),"nowarp");
  $table->addHeader("&nbsp;",NULL,"16");
  // execute query
  $query="SELECT accounts_groups.*,accounts_groups_join_accounts.idGrouprole FROM accounts_groups_join_accounts JOIN accounts_groups ON accounts_groups_join_accounts.idGroup=accounts_groups.id WHERE accounts_groups_join_accounts.idAccount='".$account->id."'";
  $groups=$GLOBALS['db']->query($query);
  while($group=$GLOBALS['db']->fetchNextObject($groups)){
   $name=$group->name;
   if($group->idGroup>0){$name=api_groupName($group->idGroup)."&minus;".$name;}
   if($group->description){$name.=" (".$group->description.")";}
   // build group table row
   $table->addRow();
   // build table fields
   $table->addField($name);
   $table->addField(api_grouproleName($group->idGrouprole),"nowarp");
   $table->addField("<a href=\"submit.php?act=account_grouprole_delete&idAccount=".$account->id."&idGroup=".$group->id."\" onClick=\"return confirm('".api_text("accounts_edit-td-groupDelete-confirm")."');\">".api_icon('icon-trash')."</a>","text-center");
  }
  // show table
  $table->render();
  // add group form
 ?>
 <form class="form-horizontal" action="submit.php?act=account_grouprole_add&id=<?php echo $account->id;?>" method="post" name="accounts_groups">
 <select class="span5" name="idGroup">
  <option value="0">Aggiungi un gruppo</option>
  <?php
   $groups=$GLOBALS['db']->query("SELECT * FROM accounts_groups ORDER BY idGroup,name ASC");
   while($group=$GLOBALS['db']->fetchNextObject($groups)){
    if(!api_checkAccountGroup($group->id,$account->id)){
     $group_name=$group->name;
     if($group->idGroup>0){$group_name=api_groupName($group->idGroup)."&minus;".$group_name;}
     echo "<option value='".$group->id."'> ".$group_name;
     if($group->description){echo " (".$group->description.")";}
     echo "</option>\n";
    }
   }
  ?>
 </select>
 <select class="span5" name="idGrouprole">
  <?php
   $grouproles=$GLOBALS['db']->query("SELECT * FROM accounts_grouproles ORDER BY id ASC");
   while($grouprole=$GLOBALS['db']->fetchNextObject($grouproles)){
    echo "<option value='".$grouprole->id."'";
    if(api_accountGrouprole($group->id)==$grouprole->id){echo " selected";}
    echo "> ".$grouprole->name;
    if($grouprole->description){echo " (".$grouprole->description.")";}
    echo "</option>\n";
   }
  ?>
 </select>
 <button type="submit" name="account_grouprole_add" class="btn"><i class="icon-plus"></i></button>
<?php
 }
 $GLOBALS['html']->split_close();
?>
<script type="text/javascript">
 $(document).ready(function(){
  // validation
  $('form').validate({
   rules:{
    name:{required:true,minlength:3},
    account:{required:true,email:true}
   },
   submitHandler:function(form){form.submit();}
  });
 });
</script>
<?php } ?>