<?php
/* -------------------------------------------------------------------------- *\
|* -[ Accounts - Roles List ]------------------------------------------------ *|
\* -------------------------------------------------------------------------- */
$checkPermission="roles_view";
include("template.inc.php");
function content(){
 // definitions
 $roles_status_modals_array=array();
 // acquire variables
 $g_search=$_GET['q'];
 // build table
 $table=new str_table(api_text("roles_list-tr-unvalued"),TRUE);
 // build table header
 $table->addHeader("&nbsp;",NULL,"16");
 $table->addHeader(api_text("roles_list-th-level"),"nowarp text-center",NULL,"accounts_roles.level");
 $table->addHeader(api_text("roles_list-th-name"),"nowarp",NULL,"accounts_roles.name");
 $table->addHeader(api_text("roles_list-th-description"),NULL,"100%","accounts_roles.description");
 $table->addHeader("&nbsp;",NULL,"16");
 // get roles
 $roles=api_accounts_roles($g_search,TRUE);
 foreach($roles->results as $role){
  // build modal window
  $roles_status_modals_array[]=api_accounts_roleStatusModal($role);
  // make class
  if($role->id==$_GET['idRole']){$tr_class="info";}else{$tr_class=NULL;}
  // build group table row
  $table->addRow($tr_class);
  // build table fields
  $table->addField(api_link("roles_edit.php?idRole=".$role->id,api_icon('icon-edit')),"nowarp");
  $table->addField($role->level,"nowarp text-center");
  $table->addField($role->name,"nowarp");
  $table->addField($role->description);
  $table->addField(end($roles_status_modals_array)->link(api_icon("icon-info-sign")),"nowarp");
 }
 // renderize table
 $table->render();
 // renderize the pagination
 $roles->pagination->render();
 // renderize status modal windows
 foreach($roles_status_modals_array as $status_modal){$status_modal->render();}
 // debug
 if($_SESSION["account"]->debug){
  pre_var_dump($roles->query,"print","query");
  pre_var_dump($roles->results,"print","roles");
 }
}
?>