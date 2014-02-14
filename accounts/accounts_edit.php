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

?>
<div class="row-fluid">
<div class="span6">
<?php
 // form fields array
 $ff_array=array();
 $ff_array[]=api_formField("text","name",api_text("accounts_edit-ff-name"),stripslashes($account->name),"input-large",api_text("accounts_edit-ff-name-placeholder"));
 // root checker
 if($account->id<>1){
  $ff_array[]=api_formField("text","account",api_text("accounts_edit-ff-account"),stripslashes($account->account),"input-xlarge",api_text("accounts_edit-ff-account-placeholder"));
   $fo_array=array(
    api_formFieldOption(0,api_text("accounts_edit-fo-disabled"),($account->typology==0)?TRUE:FALSE),
    api_formFieldOption(1,api_text("accounts_edit-fo-administrator"),($account->typology==1)?TRUE:FALSE),
    api_formFieldOption(2,api_text("accounts_edit-fo-user"),($account->typology==2)?TRUE:FALSE)
   );
  $ff_array[]=api_formField("select","typology",api_text("accounts_edit-ff-typology"),NULL,"input-medium",NULL,$fo_array);
   $fo_array=array();
   $fo_array[]=api_formFieldOption("default","Default");
   $dir="../languages/";
   if(is_dir($dir)){
    if($dh=opendir($dir)){
     while(($file=readdir($dh))!==false){
      if(substr($file,-4)==".xml" && $file<>"default.xml"){
       $language=substr($file,0,-4);
       $fo_array[]=api_formFieldOption($language,$language,($language==$account->language)?TRUE:FALSE);
      }
     }
     closedir($dh);
    }
   }
  $ff_array[]=api_formField("select","language",api_text("accounts_edit-ff-language"),NULL,"input-medium",NULL,$fo_array);
   $fo_array=array();
   $fo_array[]=api_formFieldOption(0,api_text("accounts_edit-fo-CompanyNotAssigned"));
   $companies=$GLOBALS['db']->query("SELECT * FROM accounts_companies ORDER BY company ASC,division ASC");
   while($company=$GLOBALS['db']->fetchNextObject($companies)){
    $fo_array[]=api_formFieldOption($company->id,$company->company." - ".$company->division,($company->id==$account->idCompany)?TRUE:FALSE);
   }
  $ff_array[]=api_formField("select","idCompany",api_text("accounts_edit-ff-company"),NULL,"input-large",NULL,$fo_array);
 }

 // form controls array
 $fc_array=array();
 $fc_array[]=api_formControl("submit",api_text("accounts_edit-fc-save"));
 $fc_array[]=api_formControl("button",api_text("accounts_edit-fc-cancel"),NULL,"accounts_list.php");
 if($account->id>0 && api_checkPermission("accounts","accounts_delete")){
  $fc_array[]=api_formControl("button",api_text("accounts_edit-fc-delete"),"btn-danger","submit.php?act=account_delete&id=".$account->id,api_text("accounts_edit-fc-delete-confirm"));
 }
 // print form
 api_form($ff_array,$fc_array,"submit.php?act=account_save&id=".$account->id,"post","accounts");
?>
</div><!-- /span6 -->
<div class="span6">
<?php
 // if edit account show groups
 if($account->id>1){
  // build table header
  $th_array=array(
   api_tableHeader(api_text("accounts_edit-th-group"),NULL,"100%"),
   api_tableHeader(api_text("accounts_edit-th-role"),"nowarp"),
   api_tableHeader("&nbsp;",NULL,"16")
  );
  // execute query
  $query="SELECT accounts_groups.*,accounts_groups_join_accounts.idGrouprole FROM accounts_groups_join_accounts JOIN accounts_groups ON accounts_groups_join_accounts.idGroup=accounts_groups.id WHERE accounts_groups_join_accounts.idAccount='".$account->id."'";
  $groups=$GLOBALS['db']->query($query);
  while($group=$GLOBALS['db']->fetchNextObject($groups)){
   $name=$group->name;
   if($group->description){$name.=" (".$group->description.")";}
   // build table data
   $td_array=array();
   $td_array[]=api_tableField($name);
   $td_array[]=api_tableField(api_grouproleName($group->idGrouprole),"nowarp");
   $td_array[]=api_tableField("<a href=\"submit.php?act=account_grouprole_delete&idAccount=".$account->id."&idGroup=".$group->id."\" onClick=\"return confirm('".api_text("accounts_edit-td-groupDelete-confirm")."');\">".api_icon('icon-trash')."</a>","text-center");
   // build group table row
   $tr_array[]=api_tableRow($td_array);
  }
  // show table
  api_table($th_array,$tr_array,api_text("list-tr-unvalued"));
  // add group
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
<?php } ?>
</div><!-- /span6 -->
</div><!-- /row-fluid -->
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