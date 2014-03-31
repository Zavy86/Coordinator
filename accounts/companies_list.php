<?php
/* -------------------------------------------------------------------------- *\
|* -[ Accounts - Companies List ]-------------------------------------------- *|
\* -------------------------------------------------------------------------- */
$checkPermission="companies_list";
include("template.inc.php");
function content(){
 // build table
 $table=new str_table(api_text("list-tr-unvalued"),TRUE);
 // build table header
 $table->addHeader("&nbsp;",NULL,"16");
 $table->addHeader(api_text("companies_list-th-company"),"nowarp",NULL,"company");
 $table->addHeader(api_text("companies_list-th-name"),NULL,NULL,"name");
 $table->addHeader(api_text("companies_list-th-fiscal_name"),"nowarp","100%","fiscal_name");
 $table->addHeader(api_text("companies_list-th-members"),"nowarp text-center","32",NULL,2);
 // query order
 $query_order=api_queryOrder("company ASC,division ASC");
 // execute query
 $companies=$GLOBALS['db']->query("SELECT * FROM accounts_companies".$query_order);
 while($company=$GLOBALS['db']->fetchNextObject($companies)){
  // count members
  $members_count=$GLOBALS['db']->countOf("accounts_accounts","idCompany='".$company->id."'");
  // make company name
  $name=stripslashes($company->company)." - ".stripslashes($company->division);
  // build group table row
  $table->addRow();
  // build table fields
  $table->addField("<a href=\"companies_edit.php?id=".$company->id."\">".api_icon('icon-edit')."</a>","nowarp");
  $table->addField($name,"nowarp");
  $table->addField(stripslashes($company->name),"nowarp");
  $table->addField(stripslashes($company->fiscal_name));
  $table->addField($members_count,"nowarp text-center");
  $table->addField("<a href=\"accounts_list.php?idCompany=".$company->id."\">".api_icon('icon-user')."</a>","nowarp text-center");
 }
 // show table
 $table->render();
}
?>