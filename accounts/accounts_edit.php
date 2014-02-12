<?php
/* ------------------------------------------------------------------------- *\
|* -[ Accounts - Accounts Edit ]-------------------------------------------- *|
\* ------------------------------------------------------------------------- */
if($_GET['id']>0){$checkPermission="accounts_edit";}else{$checkPermission="accounts_add";}
include("template.inc.php");
function content(){
 $g_id=$_GET['id'];
 if(!isset($g_id)){$g_id=0;}
 $account=$GLOBALS['db']->queryUniqueObject("SELECT * FROM accounts_accounts WHERE id='".$g_id."'");
?>

<form class="form-horizontal" action="submit.php?act=account_save&id=<?php echo $g_id;?>" method="post">

<div class="row-fluid">
<div class="span6">

 <div class="control-group">
  <label class="control-label" for="iName">Nome</label>
  <div class="controls"><input type="text" id="iName" class="input-large" name="name" placeholder="Nome" value="<?php echo $account->name;?>"></div>
 </div>

<?php if($account->id<>1){ ?>

 <div class="control-group">
  <label class="control-label" for="iAccount">Account</label>
  <div class="controls"><input type="text" id="iAccount" class="input-xlarge" name="account" placeholder="Indirizzo e-mail" value="<?php echo $account->account;?>"></div>
 </div>

 <div class="control-group">
  <label class="control-label">Tipologia</label>
  <div class="controls">
   <select name="typology">
    <option value='0'>Disabled (Disabilitato)</option>
    <option value='1'<?php if($account->typology==1){echo " selected";} ?>>Administrator (Amministratore)</option>
    <option value='2'<?php if($account->typology==2){echo " selected";} ?>>User (Utente)</option>
   </select>
  </div>
 </div>

 <div class="control-group">
  <label class="control-label">Lingua</label>
  <div class="controls">
   <select name="language">
    <option value="default">Default</option>
    <?php
     $dir="../languages/";
     if(is_dir($dir)){
      if($dh=opendir($dir)){
       while(($file=readdir($dh))!==false){
        if(substr($file,-4)==".xml" && $file<>"default.xml"){
         echo "<option value='".substr($file,0,-4)."'";
         if(substr($file,0,-4)==$account->language){echo " selected='selected'";}
         echo ">".substr($file,0,-4)."</option>\n";
        }
       }
       closedir($dh);
      }
     }
    ?>
   </select>
  </div>
 </div>

 <div class="control-group">
  <label class="control-label">Societ&agrave;</label>
  <div class="controls">
   <select name="idCompany">
    <option value='0'>Non assegnato</option>
  <?php
   $companies=$GLOBALS['db']->query("SELECT * FROM accounts_companies ORDER BY company ASC,division ASC");
   while($company=$GLOBALS['db']->fetchNextObject($companies)){
    echo "<option value='".$company->id."'";
    if($account->idCompany==$company->id){echo " selected";}
    echo "> ".$company->company." - ".$company->division;
    echo "</option>\n";
   }
  ?>
   </select>
  </div>
 </div>

<?php } /* root check */ ?>

 <div class="control-group">
  <div class="controls">
   <input type="submit" class="btn btn-primary" value="Salva">
   <a href="accounts_list.php" class="btn">Indietro</a>
  </div>
 </div>

</div><!-- /span6 -->
<div class="span6">

<?php if($account->id>1){ ?>

 <table class="table table-striped table-hover">
  <thead>
   <tr>
    <th>Gruppo</th>
    <th>Ruolo</th>
    <th width="22">&nbsp;</th>
   </tr>
  </thead>
  <tbody>
<?php
 $count=0;
 $groups=$GLOBALS['db']->query("SELECT * FROM accounts_groups ORDER BY name ASC");
 while($group=$GLOBALS['db']->fetchNextObject($groups)){
  if(api_checkAccountGroup($group->id,$account->id)){
   $count++;
   echo "<tr>\n";
   echo "<td>".$group->name;
   if($group->description){echo " (".$group->description.")";}
   echo "</td>\n";
   echo "<td>".api_grouproleName(api_accountGrouprole($group->id,$account->id),TRUE)."</td>\n";
   echo "<td><a class='btn btn-mini' href='submit.php?act=account_grouprole_delete&idAccount=".$g_id."&idGroup=".$group->id."' onclick=\"return confirm('Sei sicuro di voler eliminare questa associazione?');\"><i class='icon-trash'></i></a></td>\n";
   echo "</tr>\n";
  }
 }
 if($count==0){echo "<tr><td colspan='3'><i>Nessun gruppo definito..</i></td></tr>\n";}
?>
  </tbody>
 </table>

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

<?php } /* root check */ ?>

</div><!-- /span6 -->
</div><!-- /row-fluid -->

</form>

<script type="text/javascript">
 $(document).ready(function(){
  $('input[type="submit"]').attr('disabled','disabled');
  $('form').change(function(){
   $('input[type="submit"]').removeAttr('disabled');
  });
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