<?php
/* -------------------------------------------------------------------------- *\
|* -[ Accounts - Groups Chained JSON ]--------------------------------------- *|
\* -------------------------------------------------------------------------- */
include("module.inc.php");
require_once("../core/api.inc.php");
api_loadModule();
// definitions
$results=array();
// acquire variables
$g_idCompany=$_GET['idCompany'];
if(!strlen($g_idCompany)){$g_idCompany=$_SESSION['company']->id;}
// add default results
$results[0]=array("0",ucfirst(api_text("api-group-main")));
// build options group
function tmp_api_recursive_groups_to_array($groups,&$array,$level=0){
 foreach($groups as $element){
  $pre=NULL;
  for($i=0;$i<$level;$i++){$pre.="&nbsp;&nbsp;&nbsp;";}
  $array[]=array($element->id,$pre.$element->label);
  tmp_api_recursive_groups_to_array($element->groups,$array,($level+1));
 }
}
// get groups
$groups=api_accounts_groups($g_idCompany); //,NULL,$query_where
tmp_api_recursive_groups_to_array($groups->results,$results);
// no results
if(count($results)==0){$results[0]=array('',ucfirst(api_text("noresults")));}
// encode the results
echo json_encode($results);
?>