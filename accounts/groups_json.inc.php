<?php
/* -------------------------------------------------------------------------- *\
|* -[ Accounts - Groups JSON ]----------------------------------------------- *|
\* -------------------------------------------------------------------------- */
include("../core/api.inc.php");
// acquire variables
$g_search=$_GET['q'];
$answer=array();
// build query
if(is_numeric($g_search)){
 $query_where="id='".$g_search."'";
}else{
 $query_where="name LIKE '%".$g_search."%' OR description LIKE '%".$g_search."%'";
}
// execute the query
$groups=$GLOBALS['db']->query("SELECT * FROM accounts_groups WHERE ".$query_where." ORDER BY name ASC LIMIT 25");
while($group=$GLOBALS['db']->fetchNextObject($groups)){
 $group_name=stripslashes($group->name);
 if(strlen($group->description)>0){$group_name.=" (".stripslashes($group->description).")";}
 $answer[]=array("id"=>$group->id,"text"=>$group_name);
}
if(count($answer)==25){
 // check if result is more of 25
 if($GLOBALS['db']->countOf("accounts_groups","name LIKE '%".$g_search."%'")>10){
  $answer[]=array("id"=>"0","text"=>"[...]");
 }
}
// no results
if(count($answer)==0){$answer[]=array("id"=>"0","text"=>"No Results Found..");}
// encode the results
echo json_encode($answer);
?>