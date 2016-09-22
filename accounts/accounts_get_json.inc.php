<?php
/* -------------------------------------------------------------------------- *\
|* -[ Accounts - Account Get JSON ]------------------------------------------ *|
\* -------------------------------------------------------------------------- */
include("../core/api.inc.php");
// acquire variables
$id=$_GET['id'];
if(!$id){die();}
$answer=array();
// execute the query
$account=$GLOBALS['db']->queryUniqueObject("SELECT id,account,name,phone,ldap FROM accounts_accounts WHERE id='".$id."'");
$answer[]=$account->id;
$answer[]=stripslashes($account->account);
$answer[]=stripslashes($account->name);
$answer[]=stripslashes($account->phone);
$answer[]=stripslashes($account->ldap);
// encode the results
echo json_encode($answer);
?>