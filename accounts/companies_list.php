<?php
/* ------------------------------------------------------------------------- *\
|* -[ Accounts - Companies List ]------------------------------------------- *|
\* ------------------------------------------------------------------------- */
$checkPermission="companies_list";
include("template.inc.php");
function content(){
 // build table header
 $th_array=array(
  api_tableHeader("&nbsp;",NULL,"16"),
  api_tableHeader(api_text("companies_list-th-company"),"nowarp"),
  api_tableHeader(api_text("companies_list-th-name"),NULL,"100%"),
  api_tableHeader(api_text("companies_list-th-members"),"nowarp text-center","32",NULL,2)
 );
 // execute query
 $companies=$GLOBALS['db']->query("SELECT * FROM accounts_companies ORDER BY company ASC,division ASC");
 while($company=$GLOBALS['db']->fetchNextObject($companies)){
  // count members
  $members_count=$GLOBALS['db']->countOf("accounts_accounts","idCompany='".$company->id."'");
  // make company name
  $name=stripslashes($company->company)." - ".stripslashes($company->division);
  // build table data
  $td_array=array();
  $td_array[]=api_tableField("<a href=\"companies_edit.php?id=".$company->id."\">".api_icon('icon-edit')."</a>","nowarp");
  $td_array[]=api_tableField($name,"nowarp");
  $td_array[]=api_tableField(stripslashes($company->name));
  $td_array[]=api_tableField($members_count,"nowarp text-center");
  $td_array[]=api_tableField("<a href=\"accounts_list.php?idCompany=".$company->id."\">".api_icon('icon-user')."</a>","nowarp text-center");
  // build group table row
  $tr_array[]=api_tableRow($td_array);
 }
 // show table
 api_table($th_array,$tr_array,api_text("list-tr-unvalued"));
}
?>