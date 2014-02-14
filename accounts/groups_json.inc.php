<?php
/* -------------------------------------------------------------------------- *\
|* -[ Accounts - Groups JSON ]----------------------------------------------- *|
\* -------------------------------------------------------------------------- */
include("../core/api.inc.php");
// acquire variables
$search=$_GET['q'];
$answer=array();
// execute the query
$groups=$GLOBALS['db']->query("SELECT id,name FROM accounts_groups WHERE name LIKE '%".$search."%' ORDER BY name ASC LIMIT 10");
while($group=$GLOBALS['db']->fetchNextObject($groups)){
 $answer[] = array("id"=>$group->id,"text"=>$group->name);
}
if(count($answer)==25){
 // check if result is more of 10
 if($GLOBALS['db']->countOf("accounts_groups","name LIKE '%".$search."%'")>10){
  $answer[]=array("id"=>"0","text"=>"[...]");
 }
}
// no results
if(count($answer)==0){$answer[]=array("id"=>"0","text"=>"No Results Found..");}
// encode the results
echo json_encode($answer);
?>