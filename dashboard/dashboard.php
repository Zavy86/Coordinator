<?php
/* -------------------------------------------------------------------------- *\
|* -[ Dashboard - Dashboard ]------------------------------------------------ *|
\* -------------------------------------------------------------------------- */
include("template.inc.php");
function content(){
 // build dashboard
 $dashboard=new str_dashboard();
 // get all tiles
 $tiles=$GLOBALS['db']->query("SELECT * FROM `settings_dashboards` WHERE `idAccount`='".api_account()->id."' ORDER BY `order`");
 while($tile=$GLOBALS['db']->fetchNextObject($tiles)){
  // make url
  if($tile->module){$tile->url="../".$tile->module."/".$tile->url;}
  // make background
  if(file_exists("../uploads/uploads/dashboard/".$tile->id.".jpg")){$tile->background="../uploads/uploads/dashboard/".$tile->id.".jpg";}
  // add dashboard element
  $dashboard->addElement($tile->url,$tile->label,$tile->description,TRUE,$tile->size,$tile->icon,NULL,NULL,$tile->background,$tile->target);
 }
 // renderize dashboard
 $dashboard->render();
 // show unvalued dashboard message
 if(!$dashboard->elements_count){
  echo api_tag("p",api_text("dashboard-welcome",api_account()->name));
  echo api_tag("p",api_text("dashboard-unvalued"));
 }
 // debug
 if($_SESSION["account"]->debug){pre_var_dump($dashboard,"print","dashboard");}
}
?>