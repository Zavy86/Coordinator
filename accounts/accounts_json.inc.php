<?php
/* -------------------------------------------------------------------------- *\
|* -[ Accounts - Accounts JSON ]--------------------------------------------- *|
\* -------------------------------------------------------------------------- */
include("../core/api.inc.php");
// acquire variables
$search=$_GET['q'];
if(!$search){die();}
$answer=array();
// execute the query
$accounts=$GLOBALS['db']->query("SELECT id,name FROM accounts_accounts WHERE id='".$search."' OR name LIKE '%".$search."%' AND enabled='1' ORDER BY name ASC LIMIT 25");
while($account=$GLOBALS['db']->fetchNextObject($accounts)){
 $answer[]=array("id"=>$account->id,"text"=>$account->name);
}
// check if result is more of 25
if(count($answer)==25){
 if($GLOBALS['db']->countOf("accounts_accounts","id='".$search."' OR name LIKE '%".$search."%''")>25){
  $answer[]=array("id"=>"0","text"=>"[...]");
 }
}
// no results
if(count($answer)==0){$answer[]=array("id"=>"0","text"=>"No Results Found..");}
// encode the results
echo json_encode($answer);
?>