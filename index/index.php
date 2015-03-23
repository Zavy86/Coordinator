<?php
/* -------------------------------------------------------------------------- *\
|* -[ Index - Index ]-------------------------------------------------------- *|
\* -------------------------------------------------------------------------- */
include("template.inc.php");
function content(){
 // menu array
 $menu_array=array();
 // dashboard menu
 $dashboard=new stdClass();
 $dashboard->url=$GLOBALS['dir']."dashboard/dashboard.php";
 $dashboard->icon=$GLOBALS['dir']."dashboard/icon.png";
 $dashboard->menu=api_text("index-dashboard");
 $menu_array[]=$dashboard;
 // main menu
 $main_menu=$GLOBALS['db']->query("SELECT * FROM settings_menus WHERE idMenu='1' ORDER BY position ASC");
 while($menu=$GLOBALS['db']->fetchNextObject($main_menu)){
  if(api_account()->administrator || api_checkPermissionShowModule($menu->module,FALSE)){
   if(!file_exists("../".$menu->module."/icon.png")){continue;}
   $menu->url=$GLOBALS['dir'].$menu->module."/".$menu->url;
   $menu->icon=$GLOBALS['dir'].$menu->module."/icon.png";
   $menu_array[]=$menu;
  }
 }
 // link menu
 $links_menu=$GLOBALS['db']->query("SELECT * FROM settings_menus WHERE idMenu='2' ORDER BY position ASC");
 while($menu=$GLOBALS['db']->fetchNextObject($links_menu)){
  if(!api_account()->administrator){
   if($GLOBALS['db']->countOf("settings_menus_join_accounts_groups","idMenu='".$menu->id."'")>0){
    $enabled=FALSE;
    $groups=$GLOBALS['db']->query("SELECT * FROM settings_menus_join_accounts_groups WHERE idMenu='".$menu->id."'");
    while($group=$GLOBALS['db']->fetchNextObject($groups)){if(api_accountGroupMember($group->idGroup)>0){$enabled=TRUE;continue;}}
    if(!$enabled){continue;}
   }
  }
  if(strlen($menu->module)>0){$menu->url="../".$menu->module."/".$menu->url;}
  if(substr($menu->url,0,7)=="http://"){$menu->external=TRUE;}
  $menu->icon=$GLOBALS['dir']."uploads/uploads/links/".$menu->id.".png";
  $menu_array[]=$menu;
 }
 // show links
 $count=0;
 foreach($menu_array as $menu){
  $count++;
  if($count==1){echo "<div class='row'>\n";}
  if($menu->external){$target=" target='_blank'";}else{$target=NULL;}
  echo "<div class='span2'><a href='".$menu->url."'".$target." class='btn btn-index'><img src='".$menu->icon."'><b>".stripslashes($menu->menu)."</b></a></div>\n";
  if($count==6){echo "</div>\n<br>\n";$count=0;}
 }
 if($count<>0){echo "</div>\n";}
}
?>