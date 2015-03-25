<?php
/* -------------------------------------------------------------------------- *\
|* -[ Accounts - Groups View ]----------------------------------------------- *|
\* -------------------------------------------------------------------------- */
$checkPermission="groups_view";
include("template.inc.php");
function content(){
 // get objects
 $company=api_accounts_company($_GET['idCompany']);
 $group=api_accounts_group($_GET['idGroup']);
 if($company->id){
  groups_tree_table($company->groups);
 }elseif($group->id){
  groups_tree_table(array($group),0,$group->idGroup);
 }
}
// show groups tree
function groups_tree_table(array $groups,$level=0,$father=0){
 // definitions
 $count=0;
 // limit sublevels
 if($level>2){return true;}
 // build tree table
 echo "<table class='tree'>\n";
 // if is set father
 if($father){
  $members_list=NULL;
  // get father group
  $father_obj=api_accounts_group($father);
  // cycle group members
  foreach($father_obj->members as $member){
   $account=api_accounts_account($member);
   $account_role=$account->companies[$father_obj->idCompany]->role;
   if($account_role->level>3){
    // make element
    $account->leaf_label=$account->name;
    if($account_role->level>=3){$account->leaf_label=mb_strtoupper($account->leaf_label,"UTF-8");}
    if($account_role->level>=4){$account->leaf_label=api_tag("strong",$account->leaf_label);}
    if($account_role->level>=5){$account->leaf_label=api_tag("u",$account->leaf_label);}
    if($account->companies[$father_obj->idCompany]->mainGroup<>$father_obj->id){$account->leaf_label=api_tag("i",$account->leaf_label);}
    $account->leaf_popup=$account->name." &rarr; ".$account_role->level." ".$account_role->name;
    $members_list.="<br>".api_link("accounts_edit.php?idAccount=".$account->id,$account->leaf_label,$account->leaf_popup,"hiddenlink",TRUE,NULL,NULL,"_blank");
   }
  }
  // show father box
  echo " <tr>\n";
  echo "  <td colspan='".count($groups)."'>";
  echo "   <div class='leaf'>";
  echo "<span class='group'>";
  echo api_link("groups_tree.php?idGroup=".$father_obj->id,$father_obj->name,$father_obj->description,NULL,TRUE);
  echo "</span><br>";
  echo "<span class='members'><hr>".substr($members_list,4)."</span></div>\n";
  echo "  </td>\n";
  echo " </tr>\n";
 }
 // if level or father show up branch
 if($level>0||$father){
  echo " <tr>\n  <td colspan='".count($groups)."'>\n";
  echo "   <table><tr><td class='width-50 right'>&nbsp;</td><td class='width-50'>&nbsp;</td></tr></table>\n";
  echo "  </td>\n </tr>\n";
 }
 // show branch leaf
 echo " <tr>\n";
 // cycle branch groups
 foreach($groups as $group){
  // definitions
  $members_list=NULL;
  $members_array=array();
  // increment counter
  $count++;
  // cycle group members
  foreach($group->members as $member){
   $account=api_accounts_account($member);
   $account_role=$account->companies[$group->idCompany]->role;
   // make element
   $account->leaf_label=$account->firstname;
   if(!strlen($account->leaf_label)){$account->leaf_label=$account->name;}
   if($account_role->level>=3){$account->leaf_label=mb_strtoupper($account->leaf_label,"UTF-8");}
   if($account_role->level>=4){$account->leaf_label=api_tag("strong",$account->leaf_label);}
   if($account_role->level>=5){$account->leaf_label=api_tag("u",$account->leaf_label);}
   if($account->companies[$group->idCompany]->mainGroup<>$group->id){$account->leaf_label=api_tag("i",$account->leaf_label);}
   $account->leaf_popup=$account->name." &rarr; ".$account_role->level." ".$account_role->name;
   // divide members by level
   if(!is_array($members_array[$account_role->level])){$members_array[$account_role->level]=array();}
   $members_array[$account_role->level][$account->id]=$account;
  }
  // cycle members level
  sort($members_array);
  foreach($members_array as $members){
   $level_members_list=NULL;
   // cycle level members
   foreach($members as $member){
    $level_members_list.="<br>".api_link("accounts_edit.php?idAccount=".$member->id,$member->leaf_label,$member->leaf_popup,"hiddenlink",TRUE,NULL,NULL,"_blank");
   }
   $members_list.="<hr>".substr($level_members_list,4);
  }
  // open leaf
  echo "  <td>\n";
  // show up branch
  if($count>1){$top_left=" top";}else{$top_left=NULL;}
  if($count<count($groups)){$top_right=" top";}else{$top_right=NULL;}
  if($level>0){echo "<table><tr><td class='width-50 right".$top_left."'>&nbsp;</td><td class='width-50".$top_right."'>&nbsp;</td></tr></table>";}
  // show leaf
  if($level==0){$div_class="active";}else{$div_class=NULL;}
  echo "   <div class='leaf ".$div_class."'>";
  echo "<span class='group'>";
  echo api_link("groups_tree.php?idGroup=".$group->id,$group->name,$group->description,NULL,TRUE);
  echo "</span><br>";
  echo "<span class='members'>".$members_list."</span></div>\n";
  // sub branchs
  if(count($group->groups)){
   groups_tree_table($group->groups,($level+1));
  }
  // close leaf
  echo "  </td>\n";
 }
 // close branch
 echo " </tr>\n";
 echo "</table>\n";
}
?>
<style type='text/css'>
 table.tree{width:100%;margin:0;padding:0;border-collapse:collapse;}
 table.tree table{width:100%;margin:0;padding:0;border-collapse:collapse;}
 table.tree td{margin:0;padding:0;vertical-align:top;text-align:center;}
 table.tree td.width-50{width:50%}
 table.tree td.right{border-right:1px solid #666666;}
 table.tree td.top{border-top:1px solid #666666;}
 div.leaf{display:inline-block;margin:0 2px 0 2px;padding:4px;background-color:#FAFAFA;border:1px solid #666666;}
 div.active{background-color:#F0F0F0;border:2px solid #666666;}
 div.leaf hr{height:1px;margin:0;padding:0;background-color:#BBBBBB;}
 div.leaf span.group{font-size:12px;font-weight:bolder;}
 div.leaf span.members{font-size:10px;white-space:nowrap;}
</style>