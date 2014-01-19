<?php
/* -------------------------------------------------------------------------- *\
|* -[ Accounts - Accounts JSON ]--------------------------------------------- *|
\* -------------------------------------------------------------------------- */
include("../core/api.inc.php");
// acquire variables
$search=$_GET['q'];
$count=0;
$answer=array();
// execute the query
$groups=$GLOBALS['db']->query("SELECT id,name FROM accounts_groups WHERE name LIKE '%".$search."%' ORDER BY name ASC LIMIT 10");
while($group=$GLOBALS['db']->fetchNextObject($groups)){
 $count++;
 $answer[] = array("id"=>$group->id,"text"=>$group->name);
}
if($count==25){
 // check if result is more of 10
 if($GLOBALS['db']->countOf("accounts_groups","name LIKE '%".$search."%'")>10){
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