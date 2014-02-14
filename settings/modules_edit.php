<?php
/* -------------------------------------------------------------------------- *\
|* -[ Settings - Modules Edit ]---------------------------------------------- *|
\* -------------------------------------------------------------------------- */
$checkPermission="modules_edit";
include("template.inc.php");
function content(){
 // acquire variables
 $g_id=$_GET['id'];
 if(!$g_id){$g_id=0;}
 // definitions
 $directory_array=array();
 $modules_array=array();
 // search modules
 $dir="../";
 if(is_dir($dir)){
  if($dh=opendir($dir)){
   while(($file=readdir($dh))!==false){
    if(is_dir($dir.$file)&&$file<>"."&&$file<>".."){
     if(file_exists($dir.$file."/module.inc.php")){
      $directory_array[]=$dir.$file."/";
     }
    }
   }
   closedir($dh);
  }
 }
 // sort alphabetically
 sort($directory_array);
 // loop modules directories
 foreach($directory_array as $module){
  $module_name=NULL;
  $module_version=NULL;
  $module_description=NULL;
  // include module informations
  include($module."module.inc.php");
  // build module object
  $module_obj=new stdClass();
  $module_obj->name=$module_name;
  $module_obj->version=$module_version;
  $module_obj->description=$module_description;
  // check for setup or update
  $module_db=$GLOBALS['db']->queryUniqueObject("SELECT * FROM settings_modules WHERE module='".$module_name."'");
  if($module_db->module){
   $module_obj->installed_version=$module_db->version;
   if($module_db->version<>$module_version){
    $module_obj->action="update";
   }else{
    $module_obj->action=NULL;
   }
  }else{
   $module_obj->action="setup";
  }
  $modules_array[]=$module_obj;
 }
 // build table header
 $th_array=array(
  api_tableHeader(api_text("modules-th-module"),"nowarp",NULL,"created"),
  api_tableHeader(api_text("modules-th-version"),"nowarp",NULL,"created"),
  api_tableHeader(api_text("modules-th-description"),NULL,"100%","created"),
  api_tableHeader("&nbsp;","nowarp",NULL,"created")
 );
 // loop modules
 foreach($modules_array as $module){
  // build table fields
  $td_array=array(
   api_tableField($module->name,"nowarp"),
   api_tableField($module->version,"nowarp"),
   api_tableField($module->description)
  );
  // switch actions
  switch($module->action){
   case "setup":
    $tr_class="success";
    $td="<a href='submit.php?act=module_setup&module=".$module->name."'>Setup</a>";
    break;
   case "update":
    $tr_class="warning";
    $td="<a href='submit.php?act=module_update&module=".$module->name."'>Update from ".$module_obj->installed_version."</a>";
    break;
   default:
    $tr_class=NULL;
    $td="&nbsp;";
  }
  $td_array[]=api_tableField($td,"nowarp");
  // build table row
  $tr_array[]=api_tableRow($td_array,$tr_class);
 }
 // show table
 api_Table($th_array,$tr_array,api_text("modules-tr-no-results"),TRUE);
}
?>