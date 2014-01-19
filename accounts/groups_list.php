<?php
/* ------------------------------------------------------------------------- *\
|* -[ Accounts - Groups List ]---------------------------------------------- *|
\* ------------------------------------------------------------------------- */
$checkPermission="groups_list";
include("template.inc.php");
function content(){
?>
<table class="table table-striped table-hover table-condensed">
 <thead>
  <tr>
   <th class='nowarp' width='16'>&nbsp;</th>
   <th class='nowarp'>Gruppo</th>
   <th width='100%'>Descrizione</th>
   <th class='nowarp text-center' colspan='2'>Membri</th>
  </tr>
 </thead>
 <tbody>
<?php
// groups
$groups=$GLOBALS['db']->query("SELECT * FROM accounts_groups WHERE idGroup='0' ORDER BY name ASC");
while($group=$GLOBALS['db']->fetchNextObject($groups)){
 //$members_count=$GLOBALS['db']->countOf("accounts_groups_join_accounts","idGroup='".$group->id."'");
 $members_count=0;
 $members_list=NULL;
 $members=$GLOBALS['db']->query("SELECT * FROM accounts_groups_join_accounts WHERE idGroup='".$group->id."' ORDER BY idGrouprole DESC");
 while($member=$GLOBALS['db']->fetchNextObject($members)){
  $members_count++;
  $member_name=api_accountName($member->idAccount);
  if($member->idGrouprole>3){$member_name="<strong>".$member_name."</strong>";}
  $members_list.=$member_name.", ";
 }
 if(strlen($members_list)>0){$members_list="(".substr($members_list,0,-2).")";}
 // show groupo "<tr>\n";
 echo "<td class='nowarp'><a href=\"groups_edit.php?id=".$group->id."\"><i class='icon-search'></i></a></td>\n";
 echo "<td class='nowarp'><strong>".$group->name."</strong></td>\n";
 echo "<td>".$group->description." <small class='muted'>".$members_list."</small></td>\n";
 echo "<td class='nowarptext-center'>".$members_count."</td>\n";
 echo "<td class='nowarp text-center' width='16'><a href=\"groups_members.php?idGroup=".$group->id."\"><i class='icon-user'></i></a></td>\n";
 echo "</tr>\n";
 // subgroups
 $subgroups=$GLOBALS['db']->query("SELECT * FROM accounts_groups WHERE idGroup='".$group->id."' ORDER BY name ASC");
 while($subgroup=$GLOBALS['db']->fetchNextObject($subgroups)){
  //$members_count=$GLOBALS['db']->countOf("accounts_groups_join_accounts","idGroup='".$subgroup->id."'");
  $members_count=0;
  $members_list=NULL;
  $members=$GLOBALS['db']->query("SELECT * FROM accounts_groups_join_accounts WHERE idGroup='".$subgroup->id."' ORDER BY idGroupRole DESC");
  while($member=$GLOBALS['db']->fetchNextObject($members)){
   $members_count++;
   $member_name=api_accountName($member->idAccount);
   if($member->idGrouprole>3){$member_name="<strong>".$member_name."</strong>";}
   $members_list.=$member_name.", ";
  }
  if(strlen($members_list)>0){$members_list="(".substr($members_list,0,-2).")";}
  // show group
  echo "<tr>\n";
   echo "<td class='nowarp'><a href=\"groups_edit.php?id=".$subgroup->id."\"><i class='icon-search'></i></a></td>\n";
   echo "<td class='nowarp'>&minus; ".$subgroup->name."</td>\n";
   echo "<td>".$subgroup->description." <small class='muted'>".$members_list."</small></td>\n";
   echo "<td class='nowarp text-center'>".$members_count."</td>\n";
   echo "<td class='nowarp text-center' width='16'><a href=\"groups_members.php?idGroup=".$subgroup->id."\"><i class='icon-user'></i></a></td>\n";
  echo "</tr>\n";
 }
}
?>
 </tbody>
</table>
<?php } ?>