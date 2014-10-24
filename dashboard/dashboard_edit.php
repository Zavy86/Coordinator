<?php
/* -------------------------------------------------------------------------- *\
|* -[ Dashboard - Dashboard Edit ]------------------------------------------- *|
\* -------------------------------------------------------------------------- */
include("template.inc.php");
function content(){
 // acquire variables
 $g_id=$_GET['id'];
 if(!$g_id){$g_id=0;}
 $selected_widget=$GLOBALS['db']->queryUniqueObject("SELECT * FROM settings_dashboards WHERE id='".$g_id."' AND idAccount='".$_SESSION['account']->id."'");
 // retrive widgets list
 $widgets_array=array();
 if($handle_dir=opendir("../")){
  while(FALSE!==($entry_dir=readdir($handle_dir))){
   if($entry_dir<>"." && $entry_dir<>".." && is_dir("../".$entry_dir)){
    if($handle_widget=opendir("../".$entry_dir)){
     while(FALSE!==($entry_widget=readdir($handle_widget))){
      if($entry_widget=="widget.inc.php"){
       $module_title=NULL;
       if(file_exists("../".$entry_dir."/module.inc.php")){
        include("../".$entry_dir."/module.inc.php");
       }
       $widget=new stdClass;
       $widget->module=$entry_dir;
       $widget->path="../".$entry_dir."/".$entry_widget;
       if($module_title<>""){$widget->title=$module_title;}
        else{$widget->title=$widget->module;}
       $widgets_array[]=$widget;
      }
     }
     closedir($handle_widget);
    }
   }
  }
  closedir($handle_dir);
 }
 // build table
 $table=new str_table(api_text("edit-tr-unvalued"));
 // build table header
 $table->addHeader("&nbsp;",NULL,32);
 $table->addHeader(api_text("edit-th-span"),"nowarp");
 $table->addHeader(api_text("edit-th-title"),NULL,"100%");
 $table->addHeader(api_text("edit-th-module"),"nowarp");
 $table->addHeader(api_text("edit-th-parameters"),"nowarp");
 $table->addHeader(api_text("edit-th-refresh"),"nowarp");
 $table->addHeader("&nbsp;",NULL,16);
 // count widgets
 $totWidgets=$GLOBALS['db']->countOf("settings_dashboards","idAccount='".$_SESSION['account']->id."'");
 // build table rows
 $widgets=$GLOBALS['db']->query("SELECT * FROM settings_dashboards WHERE idAccount='".$_SESSION['account']->id."' ORDER BY position ASC");
 while($widget=$GLOBALS['db']->fetchNextObject($widgets)){
  $table->addRow();
  // position
  $position=NULL;
  if($widget->position>1){
   $position="<a href='submit.php?act=widget_move_up&id=".$widget->id."'><i class='icon-arrow-up'></i></a>";
  }
  if($widget->position>0 && $widget->position<$totWidgets){
   $position.="<a href='submit.php?act=widget_move_down&id=".$widget->id."'><i class='icon-arrow-down'></i></a>";
  }
  // refresh
  switch($widget->refresh){
   case 5000:$refresh=api_text("edit-fo-secs","5");break;
   case 10000:$refresh=api_text("edit-fo-secs","10");break;
   case 30000:$refresh=api_text("edit-fo-secs","30");break;
   case 60000:$refresh=api_text("edit-fo-min","1");break;
   case 120000:$refresh=api_text("edit-fo-mins","2");break;
   case 300000:$refresh=api_text("edit-fo-mins","5");break;
   case 600000:$refresh=api_text("edit-fo-mins","10");break;
   case 900000:$refresh=api_text("edit-fo-mins","15");break;
   case 1800000:$refresh=api_text("edit-fo-mins","30");break;
   case 3600000:$refresh=api_text("edit-fo-hour","1");break;
   case 7200000:$refresh=api_text("edit-fo-hours","2");break;
   default:$refresh=($widget->refresh/1000)." sec";
  }
  // build table fields
  $table->addField($position,"nowarp text-center");
  $table->addField($widget->span." / 12","nowarp text-center");
  $table->addField($widget->title);
  $table->addField($widget->module,"nowarp");
  $table->addField($widget->parameters,"nowarp");
  $table->addField($refresh,"nowarp text-right");
  $table->addField("<a href='dashboard_edit.php?id=".$widget->id."'>".api_icon("icon-edit")."</a>","nowarp");
 }
 // build form
 $form=new str_form("submit.php?act=widget_save&id=".$selected_widget->id,"post","dashboard_edit");
 if(!$selected_widget->id){
  $form->addField("select","module",api_text("edit-ff-widget"),NULL,"input-large");
  if(count($widgets_array)){
   $form->addFieldOption('',api_text("edit-fo-selectWidget"));
   foreach($widgets_array as $widget){
    $form->addFieldOption($widget->module,$widget->title);
   }
  }else{
   $form->addFieldOption('',api_text("edit-fo-noWidgets"));
  }
 }
 $form->addField("text","parameters",api_text("edit-ff-parameters"),$selected_widget->parameters,"input-large",api_text("edit-ff-parameters-placeholder"));
 $form->addField("select","span",api_text("edit-ff-span"),NULL,"input-small");
 for($i=3;$i<=9;$i++){
  $form->addFieldOption($i,$i." / 12",($selected_widget->span==$i)?TRUE:FALSE);
 }
 $form->addFieldOption(12,"12 / 12",($selected_widget->span==12)?TRUE:FALSE);
 $form->addField("select","refresh",api_text("edit-ff-refresh"),NULL,"input-medium");
 if($_SESSION['account']->typology==1){
  $form->addFieldOption(5000,api_text("edit-fo-secs","5"),($selected_widget->refresh==5000)?TRUE:FALSE);
  $form->addFieldOption(10000,api_text("edit-fo-secs","10"),($selected_widget->refresh==10000)?TRUE:FALSE);
  $form->addFieldOption(30000,api_text("edit-fo-secs","30"),($selected_widget->refresh==30000)?TRUE:FALSE);
 }
 $form->addFieldOption(60000,api_text("edit-fo-min","1"),($selected_widget->refresh==60000)?TRUE:FALSE);
 $form->addFieldOption(120000,api_text("edit-fo-mins","2"),($selected_widget->refresh==120000)?TRUE:FALSE);
 $form->addFieldOption(300000,api_text("edit-fo-mins","5"),($selected_widget->refresh==300000)?TRUE:FALSE);
 $form->addFieldOption(600000,api_text("edit-fo-mins","10"),($selected_widget->refresh==600000)?TRUE:FALSE);
 $form->addFieldOption(900000,api_text("edit-fo-mins","15"),($selected_widget->refresh==900000)?TRUE:FALSE);
 $form->addFieldOption(1800000,api_text("edit-fo-mins","30"),($selected_widget->refresh==1800000)?TRUE:FALSE);
 $form->addFieldOption(3600000,api_text("edit-fo-hour","1"),($selected_widget->refresh==3600000)?TRUE:FALSE);
 $form->addFieldOption(7200000,api_text("edit-fo-hours","2"),($selected_widget->refresh==7200000)?TRUE:FALSE);
 $form->addControl("submit",api_text("edit-fc-submit"));
 $form->addControl("button",api_text("edit-fc-cancel"),NULL,"dashboard_edit.php");
 if($selected_widget->id){
  $form->addControl("button",api_text("edit-fc-remove"),"btn-danger","submit.php?act=widget_remove&id=".$selected_widget->id,api_text("edit-fc-remove-confirm"));
 }
 // open split
 $GLOBALS['html']->split_open();
 $GLOBALS['html']->split_span(7);
 // show table
 $table->render();
 // split page
 $GLOBALS['html']->split_span(5);
 if($selected_widget->id>0){echo "<center><b>".api_text("edit-editWidget")."</b></center><br>\n";}
  else{echo "<center><b>".api_text("edit-addWidget")."</b></center><br>\n";}
 // show form
 $form->render();
 // close split
 $GLOBALS['html']->split_close();
?>
<script type="text/javascript">
 $(document).ready(function(){
  // validation
  $("form[name='dashboard_edit']").validate({
   rules:{
    module:{required:true}
   },
   submitHandler:function(form){form.submit();}
  });
 });
</script>
<?php } ?>