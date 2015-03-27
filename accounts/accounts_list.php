<?php
/* -------------------------------------------------------------------------- *\
|* -[ Accounts - Accounts List ]--------------------------------------------- *|
\* -------------------------------------------------------------------------- */
$checkPermission="accounts_view";
include("template.inc.php");
function content(){
 // definitions
 $accounts_status_modals_array=array();
 // acquire variables
 $g_search=$_GET['q'];
 // show filters
 echo $GLOBALS['navigation']->filtersText();
 // build table
 $table=new str_table(api_text("accounts_list-tr-unvalued"),TRUE);
 // build table header
 $table->addHeader("&nbsp;",NULL,"16");
 $table->addHeader(api_text("accounts_list-th-name"),"nowarp",NULL,"accounts_accounts.name");
 if(api_getOption("ldap")){
  $table->addHeader(api_text("accounts_list-th-mail"),"nowarp",NULL,"accounts_accounts.account");
 }else{
  $table->addHeader(api_text("accounts_list-th-account"),"nowarp",NULL,"accounts_accounts.account");
 }
 $table->addHeader(api_text("accounts_list-th-company"),"nowarp","100%");
 if(api_getOption("ldap")){$table->addHeader(api_text("accounts_list-th-ldap"),"nowarp text-right",NULL,"accounts_accounts.ldapUsername");}
 $table->addHeader(api_text("accounts_list-th-lastAccess"),"nowarp",NULL,"accounts_accounts.accDate");
 $table->addHeader("&nbsp;",NULL,"16");
 // get accounts
 $accounts=api_accounts_accounts($g_search,TRUE);
 foreach($accounts->results as $account){
  // make class
  if($account->id==$_GET['idAccount']){$tr_class="info";}else{$tr_class=NULL;}
  // build modal window
  $accounts_status_modals_array[]=api_accounts_accountStatusModal($account);
  // make companies
  $companies=NULL;
  foreach($account->companies as $company){$companies.=$company->name." (".$company->role->name.")"."<br>";}
  // build table row
  $table->addRow($tr_class);
  // build table fields
  $table->addField("<a href=\"accounts_edit.php?idAccount=".$account->id."\">".api_icon('icon-edit')."</a>","nowarp");
  $table->addField(stripslashes($account->name),"nowarp");
  $table->addField($account->account,"nowarp");
  $table->addField(substr($companies,0,-4),"nowarp");
  if(api_getOption("ldap")){$table->addField($account->ldap,"nowarp text-right");}
  $table->addField(api_timestampFormat($account->accDate,api_text("datetime")),"nowarp");
  $table->addField(end($accounts_status_modals_array)->link(api_icon($account->typology->icon,$account->typology->description)),"nowarp");
 }
 // show table
 $table->render();
 // renderize the pagination
 $accounts->pagination->render();
 // renderize status modal windows
 foreach($accounts_status_modals_array as $status_modal){$status_modal->render();}
 // debug
 if($_SESSION["account"]->debug){
  pre_var_dump($accounts->query,"print","query");
  pre_var_dump($accounts->results,"print","accounts");
 }
}
?>