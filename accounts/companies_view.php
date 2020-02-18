<?php
/* -------------------------------------------------------------------------- *\
|* -[ Accounts - Groups View ]----------------------------------------------- *|
\* -------------------------------------------------------------------------- */
$checkPermission="groups_view";
include("template.inc.php");
function content(){
 // definitions
 $levels_array=array();
 // get objects
 $company=api_accounts_company($_GET['idCompany']);
 // check objects
 if(!$company->id){echo api_text("companyNotFound");return FALSE;}

 // build members description list
 $company_dl=new str_dl("br","dl-horizontal");

 $company_dl->addElement(api_text("companies_view-name"),$company->name);

 // members
 foreach($company->members as $member){
  $member=api_accounts_account($member);
  $role=$member->companies[$company->id]->role;
  if(!$role->level){continue;} // <---------------------------------------------- verificare
  if(!is_array($levels_array[$role->level]->members)){
   $levels_array[$role->level]=api_accounts_role($role->id);
   $levels_array[$role->level]->members=array();
  }
  $levels_array[$role->level]->members[$member->id]=$member;
 }
 // build members description list
 $roles_dl=new str_dl("br","dl-horizontal");
 // cycle levels
 foreach(array_reverse($levels_array,TRUE) as $role){
  // cycle members
  $members=NULL;
  foreach($role->members as $member){
   $member_name=$member->name;
   if($role->level>3){$member_name=api_tag("strong",$member->name);}
   $members.=", ".api_link("accounts_edit.php?idAccount=".$member->id,$member_name,NULL,NULL,FALSE,NULL,NULL,"_blank");
  }
  $roles_dl->addElement($role->name." ^".$role->level,substr($members,2));
 }
 // renderize
 $company_dl->render();
 // renderize
 $roles_dl->render();
}
?>