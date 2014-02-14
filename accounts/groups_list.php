<?php
/* -------------------------------------------------------------------------- *\
|* -[ Accounts - Groups List ]----------------------------------------------- *|
\* -------------------------------------------------------------------------- */
$checkPermission="groups_list";
include("template.inc.php");
function content(){
 // build table header
 $th_array=array(
  api_tableHeader("&nbsp;",NULL,"16"),
  api_tableHeader(api_text("groups_list-th-group"),"nowarp"),
  api_tableHeader(api_text("groups_list-th-description"),NULL,"100%"),
  api_tableHeader(api_text("groups_list-th-members"),"nowarp text-center","32",NULL,2)
 );
 // build a group table row
 function build_group_tr($group,$subgroup=FALSE){
  // count members and make member list
  $members=$GLOBALS['db']->query("SELECT * FROM accounts_groups_join_accounts WHERE idGroup='".$group->id."' ORDER BY idGrouprole DESC");
  while($member=$GLOBALS['db']->fetchNextObject($members)){
   $members_count++;
   $member_name=api_accountName($member->idAccount);
   if($member->idGrouprole>3){$member_name="<strong>".$member_name."</strong>";}
   $members_list.=$member_name.", ";
  }
  if(strlen($members_list)>0){$members_list="(".substr($members_list,0,-2).")";}
  // group or subgroup name
  if($subgroup){$name="&minus; ".stripslashes($group->name);}
   else{$name="<strong>".stripslashes($group->name)."</strong>";}
  // build table data
  $td_array=array();
  $td_array[]=api_tableField("<a href=\"groups_edit.php?id=".$group->id."\">".api_icon('icon-edit')."</a>","nowarp");
  $td_array[]=api_tableField($name,"nowarp");
  $td_array[]=api_tableField(stripslashes($group->description)." <small class='muted'>".$members_list."</small>");
  $td_array[]=api_tableField($members_count,"nowarp text-center");
  $td_array[]=api_tableField("<a href=\"groups_members.php?idGroup=".$group->id."\">".api_icon('icon-user')."</a>","nowarp text-center");
  // return table row
  return api_tableRow($td_array,NULL);
 }
 // execute query
 $groups=$GLOBALS['db']->query("SELECT * FROM accounts_groups WHERE idGroup='0' ORDER BY name ASC");
 while($group=$GLOBALS['db']->fetchNextObject($groups)){
  // build group table row
  $tr_array[]=build_group_tr($group);
  // subgroups
  $subgroups=$GLOBALS['db']->query("SELECT * FROM accounts_groups WHERE idGroup='".$group->id."' ORDER BY name ASC");
  while($subgroup=$GLOBALS['db']->fetchNextObject($subgroups)){
   // build subgroup table row
   $tr_array[]=build_group_tr($subgroup,TRUE);
  }
 }
 // show table
 api_table($th_array,$tr_array,api_text("groups_list-tr-unvalued"));
}
?>