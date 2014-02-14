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
 // build table header
 $th_array=array(
  api_tableHeader("&nbsp;",NULL,"16"),
  api_tableHeader(api_text("accounts_list-th-name"),"nowarp",NULL,"name"),
  api_tableHeader(api_text("accounts_list-th-typology"),"nowarp",NULL,"typology"),
  api_tableHeader(api_text("accounts_list-th-company"),"nowarp",NULL,"idCompany"),
  api_tableHeader(api_text("accounts_list-th-account"),NULL,"100%","account"),
  api_tableHeader(api_text("accounts_list-th-lastAccess"),"nowarp",NULL,"lastLogin")
 );
 // build query
 $query_where="1";
 if($g_idCompany<>NULL){$query_where="idCompany='".$g_idCompany."'";}
 // order
 $query_order_field=$g_orderField;
 if(!$query_order_field){$query_order_field="idCompany ASC,typology ASC,name ASC,account";$g_orderMode=1;}
 if($g_orderMode==1){$query_order_mode=" ASC";}else{$query_order_mode=" DESC";}
 $query_order=" ORDER BY ".$query_order_field.$query_order_mode;
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
  // build table data
  $td_array=array();
  $td_array[]=api_tableField("<a href=\"accounts_edit.php?id=".$account->id."\">".api_icon('icon-edit')."</a>","nowarp");
  $td_array[]=api_tableField(stripslashes($account->name),"nowarp");
  $td_array[]=api_tableField($typology,"nowarp");
  $td_array[]=api_tableField($company,"nowarp");
  $td_array[]=api_tableField(stripslashes($account->account),"nowarp");
  $td_array[]=api_tableField(api_timestampFormat($account->lastLogin,TRUE),"nowarp");
  // build group table row
  $tr_array[]=api_tableRow($td_array);
 }
 // show table
 api_table($th_array,$tr_array,api_text("list-tr-unvalued"),TRUE);
}
?>