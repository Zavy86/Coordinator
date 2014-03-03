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
      $module_core=FALSE;
      include($dir.$file."/module.inc.php");
      if(!$module_core){$directory_array[]=$dir.$file."/";}
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
 // build table
 $table=new str_table(api_text("modules-tr-unvalued"),TRUE);
 $table->addHeader(api_text("modules-th-module"),"nowarp");
 $table->addHeader(api_text("modules-th-version"),"nowarp");
 $table->addHeader(api_text("modules-th-description"),NULL,"100%");
 $table->addHeader("&nbsp;","nowarp",NULL);
 // loop modules
 foreach($modules_array as $module){
  // switch actions
  switch($module->action){
   case "setup":
    $tr_class="success";
    $td="<a href='submit.php?act=module_setup&module=".$module->name."'>".api_text("modules-td-setup")."</a>";
    $td.=" - <a href='submit.php?act=module_remove&module=".$module->name."' onClick=\"return confirm('".api_text("modules-td-remove-confirm")."');\">".api_text("modules-td-remove")."</a>";
    break;
   case "update":
    $tr_class="warning";
    $td="<a href='submit.php?act=module_update&module=".$module->name."'>".api_text("modules-td-update",$module_obj->installed_version)."</a>";
    break;
   default:
    $tr_class=NULL;
    $td="<a href='submit.php?act=module_uninstall&module=".$module->name."' onClick=\"return confirm('".api_text("modules-td-uninstall-confirm")."');\">".api_text("modules-td-uninstall")."</a>";
  }
  // build table row
  $table->addRow($tr_class);
  // build table fields
  $table->addField($module->name,"nowarp");
  $table->addField($module->version,"nowarp");
  $table->addField($module->description);
  $table->addField($td,"nowarp");
 }
 // show table
 $table->render();
}
?>