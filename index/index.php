<?php
/* -------------------------------------------------------------------------- *\
|* -[ Index - Index ]-------------------------------------------------------- *|
\* -------------------------------------------------------------------------- */
include("template.inc.php");
function content(){
 // menu array
 global $menu_array;
 $menu_array=array();
 // dashboard menu
 $dashboard=new stdClass();
 $dashboard->url=$GLOBALS['dir']."dashboard/dashboard.php";
 $dashboard->icon=$GLOBALS['dir']."dashboard/icon.png";
 $dashboard->menu=api_text("index-dashboard");
 $menu_array[]=$dashboard;
 // index menu function
 function index_menus($idMenu){
  $menus=$GLOBALS['db']->query("SELECT * FROM settings_menus WHERE idMenu='".$idMenu."' ORDER BY position ASC");
  while($menu=$GLOBALS['db']->fetchNextObject($menus)){
   // get translated name
   $translation=$GLOBALS['db']->queryUniqueObject("SELECT * FROM settings_menus_languages WHERE idMenu='".$menu->id."' AND language='".$_SESSION['language']."'");
   if($translation->id){$menu->menu=$translation->name;}
   index_menus($menu->id);
   if(api_checkPermissionShowModule($menu->module)){
   if(!file_exists("../".$menu->module."/icon.png")){continue;}
    $menu->url=$GLOBALS['dir'].$menu->module."/".$menu->url;
    $menu->icon=$GLOBALS['dir'].$menu->module."/icon.png";
    $GLOBALS['menu_array'][]=$menu;
   }
  }
 }
 // get main menu
 index_menus(1);
 // link menu
 $links_menu=$GLOBALS['db']->query("SELECT * FROM settings_menus WHERE idMenu='2' ORDER BY position ASC");
 while($menu=$GLOBALS['db']->fetchNextObject($links_menu)){
  // get translated name
  $translation=$GLOBALS['db']->queryUniqueObject("SELECT * FROM settings_menus_languages WHERE idMenu='".$menu->id."' AND language='".$_SESSION['language']."'");
  if($translation->id){$menu->menu=$translation->name;}
  if(!api_account()->administrator){
   if($GLOBALS['db']->countOf("settings_menus_join_accounts_groups","idMenu='".$menu->id."'")>0){
    $enabled=FALSE;
    $groups=$GLOBALS['db']->query("SELECT * FROM settings_menus_join_accounts_groups WHERE idMenu='".$menu->id."'");
    while($group=$GLOBALS['db']->fetchNextObject($groups)){if(api_accountGroupMember($group->idGroup)>0){$enabled=TRUE;continue;}}
    if(!$enabled){continue;}
   }
  }
  if(strlen($menu->module)>0){$menu->url="../".$menu->module."/".$menu->url;}
  if(substr($menu->url,0,4)=="http"){$menu->external=TRUE;}
  $menu->icon=$GLOBALS['dir']."uploads/uploads/links/".$menu->id.".png";
  $menu_array[]=$menu;
 }
 // show links
 echo "<div class='row'>\n";
 foreach($menu_array as $menu){
  if($menu->external){$target=" target='_blank'";}else{$target=NULL;}
  echo "<div class='span2'><a href='".$menu->url."'".$target." class='btn btn-index'><img src='".$menu->icon."' class='img-responsive' style='width:100%;'><b>".stripslashes($menu->menu)."</b></a></div>\n";
 }
 echo "</div>\n";
}
?>