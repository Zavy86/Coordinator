<?php
/* -------------------------------------------------------------------------- *\
|* -[ Accounts - Groups View ]----------------------------------------------- *|
\* -------------------------------------------------------------------------- */
$checkPermission="groups_view";
include("template.inc.php");
function content(){
 // get objects
 $group=api_accounts_group($_GET['idGroup']);
 // show members recursively
 groups_view_showMembers($group);
 // debug
 if($_SESSION["account"]->debug){pre_var_dump($group,"print","group (recursively)");}
}
// show members recursively
function groups_view_showMembers($group){
 // definitions
 $levels_array=array();
 // cycle members
 foreach($group->members as $member){
  $member=api_accounts_account($member);
  $level=$member->companies[$group->idCompany]->role->level;
  if(!$level){continue;} // <--------------------------------------------------- verificare
  if(!is_array($levels_array[$level]->members)){
   $levels_array[$level]=api_accounts_role($level);
   $levels_array[$level]->members=array();
  }
  $levels_array[$level]->members[$member->id]=$member;
 }
 // build roles dynamic list
 $dl_roles=new str_dl("br","dl-horizontal");
 // cycle levels
 foreach($levels_array as $role){
  // cycle members
  $members=NULL;
  foreach($role->members as $member){
   $member_name=$member->name;
   if($role->level>3){$member_name=api_tag("strong",$member->name);}
   $members.=", ".api_link("accounts_edit.php?idAccount=".$member->id,$member_name,NULL,NULL,FALSE,NULL,NULL,"_blank");
  }
  $dl_roles->addElement($role->name,substr($members,2));
 }
 // show company and group name
 echo api_tag("h5",$group->path.$group->label,"text-center");
 // renderize
 $dl_roles->render();
 // cycle subgroups
 foreach($group->groups as $subgroup){
  groups_view_showMembers($subgroup);
 }
}
?>