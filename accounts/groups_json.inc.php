<?php
/* -------------------------------------------------------------------------- *\
|* -[ Accounts - Groups JSON ]----------------------------------------------- *|
\* -------------------------------------------------------------------------- */
include("../core/api.inc.php");
// acquire variables
$search=$_GET['q'];
$answer=array();
// execute the query
$groups=$GLOBALS['db']->query("SELECT * FROM accounts_groups WHERE id='".$search."' OR name LIKE '%".$search."%' OR description LIKE '%".$search."%' ORDER BY name ASC LIMIT 10");
while($group=$GLOBALS['db']->fetchNextObject($groups)){
 $group_name=stripslashes($group->name);
 if(strlen($group->description)>0){$group_name.=" (".stripslashes($group->description).")";}
 $answer[]=array("id"=>$group->id,"text"=>$group_name);
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