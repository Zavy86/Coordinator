<?php
/* -------------------------------------------------------------------------- *\
|* -[ Accounts - Groups List ]----------------------------------------------- *|
\* -------------------------------------------------------------------------- */
$checkPermission="groups_view";
include("template.inc.php");
function content(){
 // get objects
 $company=api_accounts_company($_GET['company']);
 // show filters
 echo $GLOBALS['navigation']->filtersText();
 // check company
 if(!$company->id){
  // build warning dynamic list
  $warning_dl=new str_dl("br","dl-horizontal");
  $warning_dl->addElement(api_span(api_text("groups_list-dt-warning"),"text-error"),api_text("groups_list-dd-company"));
  // renderize warning dynamic list
  $warning_dl->render();
  return FALSE;
 }
 // build table
 $table=new str_table(api_text("groups_list-tr-unvalued"),TRUE);
 // build table header
 $table->addHeader("&nbsp;",NULL,"16");
 $table->addHeader(api_text("groups_list-th-group"),"nowarp");
 $table->addHeader(api_text("groups_list-th-description"),NULL,"100%");
 $table->addHeader(api_text("groups_list-th-members"),"nowarp text-center","32",NULL,2);
 $table->addHeader("&nbsp;",NULL,"16");
 // build group table row
 $groups=api_accounts_groups($company->id);
 groups_list_tr($groups->results,$table);
 // renderize table
 $table->render();
 // debug
 if($_SESSION["account"]->debug){
  pre_var_dump($groups->query,"print","query");
  pre_var_dump($groups->results,"print","group (recursively)");
 }
}
// build groups table rows recursively
function groups_list_tr($groups,&$table,$level=0){
 if(!is_array($groups)){return FALSE;}
 foreach($groups as $group){
  // make prefix
  $pre=NULL;
  for($i=0;$i<$level;$i++){$pre.="&nbsp;&nbsp;&nbsp;";}
  // make members list
  if($_GET['members']){
   $members_list=NULL;
   foreach($group->members as $member){
    $account=api_accounts_account($member);
    $account_level=$account->companies[$_GET['company']]->role->level;
    $member_name=$account->name;
    if($account_level>3){$member_name=api_tag("strong",$account->name);}
    $members_list.=", ".api_link("accounts_edit.php?idAccount=".$account->id,$member_name,NULL,NULL,FALSE,NULL,NULL,"_blank");
   }
  }
  // build table row
  $table->addRow();
  // build table fields
  $table->addField(api_link("groups_view.php?idGroup=".$group->id,api_icon('icon-search')));
  $table->addField($pre.$group->label,"nowarp");
  $table->addField($group->description." ".api_small(api_span(substr($members_list,2),"muted")));
  $table->addField(count($group->members),"nowarp text-center");
  $table->addField(api_link("groups_view.php?idGroup=".$group->id,api_icon('icon-user')));
  $table->addField(api_link("groups_edit.php?idGroup=".$group->id,api_icon('icon-edit')));
  //
  groups_list_tr($group->groups,$table,($level+1));
 }
 return TRUE;
}
?>