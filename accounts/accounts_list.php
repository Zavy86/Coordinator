<?php
/* ------------------------------------------------------------------------- *\
|* -[ Accounts - Accounts List ]-------------------------------------------- *|
\* ------------------------------------------------------------------------- */
$checkPermission="accounts_list";
include("template.inc.php");
function content(){
?>

<table class="table table-striped table-hover">
 <thead>
  <tr>
   <th width="22">&nbsp;</th>
   <th width="10">#</th>
   <th>Nome</th>
   <th>Tipologia</th>
   <th>Societ&agrave;</th>
   <th>Account</th>
   <th>Ultimo accesso</th>
  </tr>
 </thead>
 <tbody>

<?php

$accounts=$GLOBALS['db']->query("SELECT * FROM accounts_accounts WHERE 1 ORDER BY idCompany ASC,typology ASC,name ASC,account ASC");
while($account=$GLOBALS['db']->fetchNextObject($accounts)){
 echo "<tr>\n";
 echo "<td><a href='accounts_edit.php?id=".$account->id."'><img src='".api_accountAvatar($account->id,"images/avatar.png")."' style='margin-top:-6px'></a></td>\n";
 echo "<td>".$account->id."</td>\n";
 echo "<td>".$account->name."</td>\n";
 echo "<td>";
 switch($account->typology){
  case 0:echo "Disabled";break;
  case 1:echo "Administrator";break;
  case 2:echo "User";break;
 }
 echo "</td>\n";
 $company=$GLOBALS['db']->queryUniqueObject("SELECT * FROM accounts_companies WHERE id='".$account->idCompany."'");
 if($company){echo "<td>".$company->company." - ".$company->division."</td>\n";}
  else{echo "<td><i>Non assegnato</i></td>\n";}
 echo "<td>".$account->account."</td>\n";
 echo "<td>".api_timestampFormat($account->lastLogin,TRUE)."</td>\n";
 echo "</tr>\n";
}

?>

 </tbody>
</table>

<?php
}
?>