<?php
/* -------------------------------------------------------------------------- *\
|* -[ Accounts - Accounts JSON ]--------------------------------------------- *|
\* -------------------------------------------------------------------------- */
include("../core/api.inc.php");
// acquire variables
$search=$_GET['q'];
if(!$search){die();}
$count=0;
$answer=array();
// execute the query
$accounts=$GLOBALS['db']->query("SELECT id,name FROM accounts_accounts WHERE id='".$search."' OR name LIKE '%".$search."%' ORDER BY name ASC LIMIT 25");
while($account=$GLOBALS['db']->fetchNextObject($accounts)){
 $count++;
 $answer[] = array("id"=>$account->id,"text"=>$account->name);
}
if($count==25){
 // check if result is more of 25
 if($GLOBALS['db']->countOf("accounts_accounts","name LIKE '%".$search."%'")>25){
  $answer[]=array("id"=>"0","text"=>"[...]");
 }
}
if(!$count){
 // no results
 $answer[]=array("id"=>"0","text"=>"No Results Found..");
}
// encode the results
echo json_encode($answer);
?>