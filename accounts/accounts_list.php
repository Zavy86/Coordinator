<?php
/* -------------------------------------------------------------------------- *\
|* -[ Accounts - Accounts List ]--------------------------------------------- *|
\* -------------------------------------------------------------------------- */
$checkPermission="accounts_list";
include("template.inc.php");
function content(){
 // acquire variables
 $g_idCompany=$_GET['idCompany'];
 if(!isset($g_idCompany)){$g_idCompany=NULL;}
 $g_orderField=$_GET['of'];
 $g_orderMode=$_GET['om'];
 // build table
 $table=new str_table(api_text("list-tr-unvalued"),TRUE);
 // build table header
 $table->addHeader("&nbsp;",NULL,"16");
 $table->addHeader("&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;",NULL,"16");
 $table->addHeader(api_text("accounts_list-th-name"),"nowarp",NULL,"name");
 $table->addHeader(api_text("accounts_list-th-typology"),"nowarp",NULL,"typology");
 $table->addHeader(api_text("accounts_list-th-company"),"nowarp",NULL,"idCompany");
 $table->addHeader(api_text("accounts_list-th-account"),NULL,"100%","account");
 $table->addHeader(api_text("accounts_list-th-lastAccess"),"nowarp",NULL,"lastLogin");
 // build query
 $query_where="1";
 if($g_idCompany<>NULL){$query_where="idCompany='".$g_idCompany."'";}
 // query order
 $query_order=api_queryOrder("idCompany ASC,typology ASC,name ASC,account ASC");
 // execute query
 $accounts=$GLOBALS['db']->query("SELECT * FROM accounts_accounts WHERE ".$query_where.$query_order);
 while($account=$GLOBALS['db']->fetchNextObject($accounts)){
  // switch typology
  switch($account->typology){
   case 0:$typology="Disabled";break;
   case 1:$typology="Administrator";break;
   case 2:$typology="User";break;
  }
  // make company name
  if($account->idCompany>0){$company=api_companyName($account->idCompany);}else{$company="<i>".api_text("accounts_list-td-CompanyNotAssigned")."</i>";}
  // build table row
  $table->addRow();
  // build table fields
  $table->addField("<a href=\"accounts_edit.php?id=".$account->id."\">".api_icon('icon-edit')."</a>","nowarp");
  $table->addField("<img src='".api_accountAvatar($account->id)."?".rand(0,999)."' style='width:16px;height:16px;'>");
  $table->addField(stripslashes($account->name),"nowarp");
  $table->addField($typology,"nowarp");
  $table->addField($company,"nowarp");
  $table->addField(stripslashes($account->account),"nowarp");
  $table->addField(api_timestampFormat($account->lastLogin,api_text("datetime")),"nowarp");
 }
 // show table
 $table->render();
}
?>