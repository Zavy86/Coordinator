<?php
/* ------------------------------------------------------------------------- *\
|* -[ Accounts - Groups Members ]------------------------------------------- *|
\* ------------------------------------------------------------------------- */
$checkPermission="groups_list";
include("template.inc.php");
function content(){
 $g_idGroup=$_GET['idGroup'];
 if(!isset($g_idGroup)){$g_idGroup=0;}
 $group=$GLOBALS['db']->queryUniqueObject("SELECT * FROM accounts_groups WHERE id='".$g_idGroup."'"); 
?>

<h3><?php echo $group->name; ?></h3>

<table class="table table-striped table-hover">
 <thead>
  <tr>
   <th width="22">&nbsp;</th>
   <th width="10">#</th>
   <th>Nome</th>
   <th>Ruolo</th>
   <th>Societ&agrave;</th>
   <th>Account</th>
   <th width="22">&nbsp;</th>
  </tr>
 </thead>
 <tbody>

<?php

$accounts=$GLOBALS['db']->query("SELECT * FROM accounts_accounts WHERE 1 ORDER BY idCompany ASC,typology ASC,name ASC,account ASC");
while($account=$GLOBALS['db']->fetchNextObject($accounts)){
 $grouprole=api_accountGrouprole($g_idGroup,$account->id);
 if($grouprole>0){
  echo "<tr>\n";
  echo "<td><a href='accounts_edit.php?id=".$account->id."'><img src='".api_accountAvatar($account->id,"images/avatar.png")."' style='margin-top:-6px'></a></td>\n";
  echo "<td>".$account->id."</td>\n";
  echo "<td>".$account->name."</td>\n";
  echo "<td>".api_grouproleName($grouprole,TRUE)."</td>\n";
  if($account->idCompany>0){echo "<td>".api_companyName($account->idCompany)."</td>\n";}
   else{echo "<td><i>Non assegnato</i></td>\n";}
  echo "<td>".$account->account."</td>\n";
  echo "<td><a class='btn btn-mini' href='submit.php?act=account_grouprole_delete&idAccount=".$account->id."&idGroup=".$g_idGroup."&from=members' onclick=\"return confirm('Sei sicuro di voler rimuovere questo utente dal gruppo?');\"><i class='icon-trash'></i></a></td>\n";
  echo "</tr>\n";
 }
}

?>

 </tbody>
</table>

<?php
}
?>