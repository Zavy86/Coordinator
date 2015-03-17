<?php
/* -------------------------------------------------------------------------- *\
|* -[ Accounts - Companies List ]-------------------------------------------- *|
\* -------------------------------------------------------------------------- */
$checkPermission="companies_list";
include("template.inc.php");
function content(){
 // definitions
 $companies_status_modals_array=array();
 // acquire variables
 $g_search=$_GET['q'];
 // build table
 $table=new str_table(api_text("companies_list-tr-unvalued"),TRUE);
 // build table header
 $table->addHeader("&nbsp;",NULL,"16");
 $table->addHeader(api_text("companies_list-th-company"),"nowarp",NULL,"company");
 $table->addHeader(api_text("companies_list-th-name"),NULL,NULL,"name");
 $table->addHeader(api_text("companies_list-th-fiscal_name"),"nowarp","100%","fiscal_name");
 $table->addHeader(api_text("companies_list-th-members"),"nowarp text-right");
 $table->addHeader("&nbsp;",NULL,"16");
 // get companies
 $companies=api_accounts_companies($g_search,TRUE);
 foreach($companies->results as $company){
  // make company name
  $name=stripslashes($company->company)." - ".stripslashes($company->division);
  // make class
  if($company->id==$_GET['idCompany']){$tr_class="info";}else{$tr_class=NULL;}
  // build group table row
  $table->addRow($tr_class);
  // build table fields
  $table->addField(api_link("companies_view.php?idCompany=".$company->id,api_icon('icon-search')),"nowarp");
  $table->addField($name,"nowarp");
  $table->addField(stripslashes($company->name),"nowarp");
  $table->addField(stripslashes($company->fiscal_name));
  $table->addField(count($company->members),"nowarp text-right");
  $table->addField(api_link("companies_edit.php?id=".$company->id,api_icon('icon-edit')),"nowarp");
 }
 // renderize table
 $table->render();
 // renderize the pagination
 $companies->pagination->render();
 // renderize status modal windows
 foreach($companies_status_modals_array as $status_modal){$status_modal->render();}
 // debug
 if($_SESSION["account"]->debug){
  pre_var_dump($companies->query,"print","query");
  pre_var_dump($companies->results,"print","companies");
 }
}
?>