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
 if($g_id>0){$selectedMenu=$GLOBALS['db']->queryUniqueObject("SELECT * FROM settings_menus WHERE id='".$g_id."'");}
 if($g_idMenu>0){$parentMenu=$GLOBALS['db']->queryUniqueObject("SELECT * FROM settings_menus WHERE id='".$g_idMenu."'");}
 // split page
 $GLOBALS['html']->split_open();
 $GLOBALS['html']->split_span(6);
 if($parentMenu-id>0){echo "<h5><a href='menus_edit.php?idMenu=".$parentMenu->idMenu."'>&laquo;</a> ".stripslashes($parentMenu->menu)."</h5>\n";}
 // build table
 $table=new str_table(api_text("menus-tr-unvalued"));
 $table->addHeader("&nbsp;",NULL,"16");
 $table->addHeader("&nbsp;",NULL,"16");
 $table->addHeader(api_text("menus-th-menu"),"nowarp");
 $table->addHeader(api_text("menus-th-module"),NULL,"100%");
 $table->addHeader("&nbsp;",NULL,"16");

 // get total menu entry
 $totMenus=$GLOBALS['db']->countOf("settings_menus","idMenu='".$g_idMenu."'");
 // execute query
 $menus=$GLOBALS['db']->query("SELECT * FROM settings_menus WHERE idMenu='".$g_idMenu."' ORDER BY position ASC,id ASC");
 while($menu=$GLOBALS['db']->fetchNextObject($menus)){
  // count items
  $count=$GLOBALS['db']->countOf("settings_menus","idMenu='".$menu->id."'");
  // position
  $position=NULL;
  if($menu->position>1){
   $position="<a href='submit.php?act=menu_move_up&idMenu=".$menu->idMenu."&id=".$menu->id."'><i class='icon-arrow-up'></i></a>";
  }
  if($menu->position>0 && $menu->position<$totMenus){
   $position.="<a href='submit.php?act=menu_move_down&idMenu=".$menu->idMenu."&id=".$menu->id."'><i class='icon-arrow-down'></i></a>";
  }
  // url
  if(strlen($menu->url)>0){$url="/".stripslashes($menu->url);}else{$url=NULL;}
  // icon
  $icon=api_icon('icon-minus');
  if($count>0){$icon="<a href='menus_edit.php?idMenu=".$menu->id."'>".api_icon('icon-search')."</a>";}
  // build table row
  $table->addRow();
  // build table fields
  $table->addField($icon,"nowarp");
  $table->addField($position,"nowarp text-center");
  $table->addField(stripslashes($menu->menu),"nowarp");
  $table->addField(stripslashes($menu->module).$url);
  $table->addField("<a href='menus_edit.php?idMenu=".$menu->idMenu."&id=".$menu->id."'>".api_icon('icon-edit')."</i></a>","nowarp");
 }
 // show table
 $table->render();
 // split page
 $GLOBALS['html']->split_span(6);
 // edit form
 if($selectedMenu->id>0){echo "<center><h5>Modifica menu</h5></center><br>\n\n";}
  else{echo "<center><h5>Aggiungi menu</h5></center><br>\n\n";}
 // build form
 $form=new str_form("submit.php?act=menu_save&id=".$selectedMenu->id,"post","menus");
 $form->addField("select","idMenu",api_text("menus-ff-parent"));
 $menus=$GLOBALS['db']->query("SELECT * FROM settings_menus WHERE id<>'".$selectedMenu->id."' ORDER BY idMenu ASC,position ASC,id ASC");
 while($menu=$GLOBALS['db']->fetchNextObject($menus)){
  if($menu->idMenu==0 || $menu->idMenu==1){
   $selected=FALSE;
   if($selectedMenu->idMenu>0){
    if($menu->id==$selectedMenu->idMenu){$selected=TRUE;}
   }else{
    if($menu->id==$g_idMenu){$selected=TRUE;}
   }
   $form->addFieldOption($menu->id,stripslashes($menu->menu),$selected);
  }
 }
 $form->addField("text","menu",api_text("menus-ff-menu"),stripslashes($selectedMenu->menu),NULL,api_text("menus-ff-menu-placeholder"));
 $form->addField("text","module",api_text("menus-ff-module"),stripslashes($selectedMenu->module),NULL,api_text("menus-ff-module-placeholder"));
 $form->addField("text","url",api_text("menus-ff-url"),stripslashes($selectedMenu->url),NULL,api_text("menus-ff-url-placeholder"));
 $form->addField("file","file",api_text("menus-ff-file"));
 if(file_exists("../uploads/uploads/links/".$selectedMenu->id.".png")){
  $form->addCustomField("&nbsp;","<div class='controls'><img src='../uploads/uploads/links/".$selectedMenu->id.".png' width='128' height='128' class='img-polaroid'></div>\n");
 }
 $form->addControl("submit",api_text("menus-fc-submit"));
 if($selectedMenu->id>0){
  if($GLOBALS['db']->countOf("settings_menus","idMenu='".$selectedMenu->id."'")==0){
   $form->addControl("button",api_text("menus-fc-delete"),"btn-danger","submit.php?act=menu_delete&idMenu=".$selectedMenu->idMenu."&id=".$selectedMenu->id,api_text("menus-fc-delete-confirm"));
  }
  if($selectedMenu->idMenu==2){
   $form->addControl("button",api_text("menus-fc-permissions"),"btn-warning","menus_permissions.php?id=".$selectedMenu->id."&idMenu=".$selectedMenu->idMenu);
  }
  $form->addControl("button",api_text("menus-fc-languages"),"btn-success","menus_languages.php?id=".$selectedMenu->id."&idMenu=".$selectedMenu->idMenu);
  $form->addControl("button",api_text("menus-fc-cancel"),NULL,"menus_edit.php?idMenu=".$selectedMenu->idMenu);
 }
 // show form
 $form->render();
 // split page
 $GLOBALS['html']->split_span(6);
 $GLOBALS['html']->split_close();
}
?>