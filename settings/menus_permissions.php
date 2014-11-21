<?php
/* -------------------------------------------------------------------------- *\
|* -[ Settings - Menus Edit ]------------------------------------------------ *|
\* -------------------------------------------------------------------------- */
$checkPermission="menus_edit";
include("template.inc.php");
function content(){
 // acquire variables
 $g_id=$_GET['id'];
 if(!$g_id){$g_id=0;}
 $g_idMenu=$_GET['idMenu'];
 if(!$g_idMenu){$g_idMenu=0;}
 if($g_id>0){$menu=$GLOBALS['db']->queryUniqueObject("SELECT * FROM settings_menus WHERE id='".$g_id."'");}
 // check
 if($menu->id>1){
  // build table
  $table=new str_table(api_text("menus_permissions-tr-unvalued"));
  // table headers
  $table->addHeader("&nbsp;",NULL,"16");
  $table->addHeader(api_text("menus_permissions-th-groups"),NULL,"100%");
  // execute query
  $query="SELECT accounts_groups.* FROM settings_menus_join_accounts_groups JOIN accounts_groups ON settings_menus_join_accounts_groups.idGroup=accounts_groups.id WHERE settings_menus_join_accounts_groups.idMenu='".$menu->id."' ORDER BY accounts_groups.name ASC";
  $groups=$GLOBALS['db']->query($query);
  while($group=$GLOBALS['db']->fetchNextObject($groups)){
   $name=$group->name;
   if($group->idGroup>0){$name=api_groupName($group->idGroup)."&minus;".$name;}
   if($group->description){$name.=" (".$group->description.")";}
   // build group table row
   $table->addRow();
   // build table fields
   $table->addField("<a href='submit.php?act=menu_permission_delete&id=".$g_id."&idMenu=".$g_idMenu."&idGroup=".$group->id."' onClick=\"return confirm('".api_text("menus_permissions-td-delete-confirm")."');\">".api_icon('icon-trash')."</a>","text-center");
   $table->addField($name);
  }
  // build form
  $form=new str_form("submit.php?act=menu_permission_add&id=".$menu->id."&idMenu=".$g_idMenu,"post","menus_permissions");
  $form->addField("select","idGroup",api_text("menus_permissions-ff-idGroup"),NULL,"input-xxlarge");
  $groups=$GLOBALS['db']->query("SELECT * FROM accounts_groups ORDER BY idGroup ASC,name ASC");
  while($group=$GLOBALS['db']->fetchNextObject($groups)){
   $name=$group->name;
   if($group->idGroup>0){$name=api_groupName($group->idGroup)."&minus;".$name;}
   if($group->description){$name.=" (".$group->description.")";}
   $form->addFieldOption($group->id,$name);
  }
  $form->addControl("submit",api_text("menus_permissions-fc-submit"));
  $form->addControl("button",api_text("menus_permissions-fc-cancel"),NULL,"menus_edit.php?id=".$menu->id."&idMenu=".$g_idMenu);
  // show table
  $table->render();
  // show form
  $form->render();
 }
}
?>