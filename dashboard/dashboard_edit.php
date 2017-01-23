<?php
/* -------------------------------------------------------------------------- *\
|* -[ Dashboard - Dashboard Edit ]------------------------------------------- *|
\* -------------------------------------------------------------------------- */
include("template.inc.php");
function content(){

 // get objects
 $selected_tile=$GLOBALS['db']->queryUniqueObject("SELECT * FROM `settings_dashboards` WHERE `id`='".$_GET['idTile']."'");

 // build table
 $table=new str_table(api_text("dashboard-edit-tr-unvalued"),FALSE,NULL,NULL,"settings_dashboards","order","idAccount");
 // build table header
 $table->addHeader("&nbsp;",NULL,32);
 $table->addHeader(api_text("dashboard-edit-th-size"),"nowarp");
 $table->addHeader(api_text("dashboard-edit-th-label"),NULL,"100%");
 $table->addHeader(api_text("dashboard-edit-th-module"),"nowarp text-right");
 $table->addHeader(api_text("dashboard-edit-th-url"),"nowarp");
 $table->addHeader("&nbsp;",NULL,16);
 // build table rows
 $tiles=$GLOBALS['db']->query("SELECT * FROM `settings_dashboards` WHERE `idAccount`='".api_account()->id."' ORDER BY `order`");
 while($tile=$GLOBALS['db']->fetchNextObject($tiles)){
  // buil table row
  $table->addRow(($selected_tile->id==$tile->id?"info":NULL));
  // build table fields
  $table->addFieldMovable($tile->id);
  $table->addField($tile->size,"nowarp text-center");
  $table->addField($tile->label);
  $table->addField($tile->module,"nowarp text-right");
  $table->addField($tile->url,"nowarp");
  $table->addField(api_link("dashboard_edit.php?idTile=".$tile->id,api_icon("icon-edit")),"nowarp");
 }









 // build form
 $form=new str_form("submit.php?act=tile_save&idTile=".$selected_tile->id,"post","dashboard_edit");

 $form->addField("select","size",api_text("dashboard-edit-ff-size"),NULL,"input-small");
 for($size_1=1;$size_1<=6;$size_1++){$form->addFieldOption($size_1."x1",$size_1."x1",($size_1==stripslashes($selected_tile->size)?TRUE:FALSE));}

 $form->addField("text","label",api_text("dashboard-edit-ff-label"),stripslashes($selected_tile->label),"input-large",api_text("dashboard-edit-ff-label-placeholder"));
 $form->addField("textarea","description",api_text("dashboard-edit-ff-description"),stripslashes($selected_tile->description),"input-large",api_text("dashboard-edit-ff-description-placeholder"),NULL,3);

 if(!$selected_tile->module){
  $form->addField("text","url",api_text("dashboard-edit-ff-url"),stripslashes($selected_tile->url),"input-large",api_text("dashboard-edit-ff-url-placeholder"));
 }else{
  $form->addField("hidden","module",NULL,stripslashes($selected_tile->module));
  $form->addField("hidden","url",NULL,stripslashes($selected_tile->url));
 }

 $form->addField("text","target",api_text("dashboard-edit-ff-target"),stripslashes($selected_tile->target),"input-medium",api_text("dashboard-edit-ff-target-placeholder"));

 $form->addField("text","icon",api_text("dashboard-edit-ff-icon"),stripslashes($selected_tile->icon),"input-medium",api_text("dashboard-edit-ff-icon-placeholder"));

 $form->addField("file","background",api_text("dashboard-edit-ff-background"));
 if(file_exists("../uploads/uploads/dashboard/".$selected_tile->id.".jpg")){
  $background_field="<div class='controls'>";
  $background_field.=api_link("submit.php?act=tile_background_delete&idTile=".$selected_tile->id,api_text("dashboard-edit-ff-background-delete"),NULL,NULL,FALSE,api_text("dashboard-edit-ff-background-delete-confirm"));
  $background_field.="<br><img src='../uploads/uploads/dashboard/".$selected_tile->id.".jpg' width='128' class='img-polaroid'>";
  $background_field.="</div>";
  $form->addCustomField("&nbsp;",$background_field);
 }

 $form->addControl("submit",api_text("dashboard-edit-fc-submit"));
 if($selected_tile->id){
  $form->addControl("button",api_text("dashboard-edit-fc-cancel"),NULL,"dashboard_edit.php");
  $form->addControl("button",api_text("dashboard-edit-fc-remove"),"btn-danger","submit.php?act=tile_delete&idTile=".$selected_tile->id,api_text("dashboard-edit-fc-remove-confirm"));
 }else{
  $form->addControl("button",api_text("dashboard-edit-fc-close"),NULL,"dashboard.php");
 }
 // open split
 $GLOBALS['html']->split_open();
 $GLOBALS['html']->split_span(7);
 // show table
 $table->render();
 // split page
 $GLOBALS['html']->split_span(5);

 if($selected_tile->id>0){echo "<center><b>".api_text("dashboard-edit-editTile")."</b></center><br>\n";}
  else{echo "<center><b>".api_text("dashboard-edit-addTile")."</b></center><br>\n";}

 // show form
 $form->render();
 // close split
 $GLOBALS['html']->split_close();
?>
<script type="text/javascript">
 $(document).ready(function(){

  $("#dashboard_edit_input_icon").iconpicker({inputSearch:false});

  // validation
  $("form[name='dashboard_edit']").validate({
   rules:{
    label:{required:true},
    url:{required:true}
   },
   submitHandler:function(form){form.submit();}
  });
 });
</script>
<?php } ?>