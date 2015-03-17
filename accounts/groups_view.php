<?php
/* -------------------------------------------------------------------------- *\
|* -[ Accounts - Groups View ]----------------------------------------------- *|
\* -------------------------------------------------------------------------- */
$checkPermission="groups_view";
include("template.inc.php");
function content(){
 // get objects
 $company=api_accounts_company($_GET['idCompany']);
 //pre_var_dump($company);
 $group=api_accounts_group($_GET['idGroup']);
 // cycle company groups
 if($company->id){
  foreach($company->groups as $group){
   groups_view_showMembers($group);
  }
 }elseif($group->id){
  groups_view_showMembers($group);
 }
}
// show members recursively
function groups_view_showMembers($group,$level=0){
 // definitions
 $levels_array=array();
 // cycle members
 foreach($group->members as $member){
  $member=api_accounts_account($member);
  $role=$member->companies[$group->idCompany]->role;
  if(!$role->level){continue;} // <---------------------------------------------- verificare
  if(!is_array($levels_array[$role->level]->members)){
   $levels_array[$role->level]=api_accounts_role($role->id);
   $levels_array[$role->level]->members=array();
  }
  $levels_array[$role->level]->members[$member->id]=$member;
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
  $dl_roles->addElement($role->name." ^".$role->level,substr($members,2));
 }
 // manual tabulations
 for($i=0;$i<$level;$i++){
  if($i<5){$level_h=($i+2);}else{$level_h=6;}
  echo api_tag("span","&nbsp;&nbsp;&nbsp;&nbsp;","h".$level_h);
 }
 // show group name
 if($level<5){$level_h=($level+2);}else{$level_h=6;}
 echo api_tag("span",$group->path.$group->label,"h".$level_h);
 // renderize
 $dl_roles->render();
 // cycle subgroups
 foreach($group->groups as $subgroup){groups_view_showMembers($subgroup,($level+1));}
}
?>