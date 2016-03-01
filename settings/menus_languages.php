<?php
/* -------------------------------------------------------------------------- *\
|* -[ Settings - Menus Languages ]------------------------------------------- *|
\* -------------------------------------------------------------------------- */
$checkPermission="menus_edit";
include("template.inc.php");
function content(){
 // definitions
 $language_availables=api_language_availables();
 // acquire variables
 $g_id=$_GET['id'];
 if(!$g_id){$g_id=0;}
 $g_idMenu=$_GET['idMenu'];
 if(!$g_idMenu){$g_idMenu=0;}
 if($g_id>0){$menu=$GLOBALS['db']->queryUniqueObject("SELECT * FROM settings_menus WHERE id='".$g_id."'");}
 $g_idLanguage=$_GET['idLanguage'];
 if(!$g_idLanguage){$g_idLanguage=0;}
 if($g_idLanguage>0){$selected_language=$GLOBALS['db']->queryUniqueObject("SELECT * FROM settings_menus_languages WHERE id='".$g_idLanguage."'");}
 // check
 if($menu->id>1){
  // build table
  $table=new str_table(api_text("menus_languages-tr-unvalued"));
  // table headers
  $table->addHeader("&nbsp;",NULL,"16");
  $table->addHeader(api_text("menus_languages-th-language"),"nowarp");
  $table->addHeader(api_text("menus_languages-th-translation"),NULL,"100%");
  // execute query
  $query="SELECT * FROM settings_menus_languages WHERE idMenu='".$menu->id."' ORDER BY language ASC";
  $languages=$GLOBALS['db']->query($query);
  while($translation=$GLOBALS['db']->fetchNextObject($languages)){
   // build group table row
   $table->addRow();
   // build table fields
   $table->addField(api_link("menus_languages.php?id=".$g_id."&idMenu=".$g_idMenu."&idLanguage=".$translation->id,api_icon('icon-edit')),"text-center");
   $table->addField($language_availables[$translation->language]);
   $table->addField($translation->name);
   $table->addField("<a href='submit.php?act=menu_language_delete&id=".$g_id."&idMenu=".$g_idMenu."&idLanguage=".$translation->id."' onClick=\"return confirm('".api_text("menus_languages-td-delete-confirm")."');\">".api_icon('icon-trash')."</a>","text-center");
  }
  // build form
  $form=new str_form("submit.php?act=menu_language_save&id=".$menu->id."&idMenu=".$g_idMenu."&idLanguage=".$selected_language->id,"post","menus_languages");
  $form->addField("select","language",api_text("menus_languages-ff-language"),NULL,"input-large");
  $form->addFieldOption("",api_text("menus_languages-ff-language-select"));
  foreach($language_availables as $key=>$language){$form->addFieldOption($key,$language,($key==$selected_language->language?TRUE:FALSE));}
  $form->addField("text","name",api_text("menus_languages-ff-name"),$selected_language->name,"input-large");
  $form->addControl("submit",api_text("menus_languages-fc-submit"));
  $form->addControl("button",api_text("menus_languages-fc-cancel"),NULL,"menus_edit.php?id=".$menu->id."&idMenu=".$g_idMenu);
  // show table
  $table->render();
  // show form
  $form->render();
 }
?>
<script type="text/javascript">
 $(document).ready(function(){
  // validation
  $("form[name='menus_languages']").validate({
   rules:{
    language:{required:true},
    name:{required:true}
   },
   submitHandler:function(form){form.submit();}
  });
 });
</script>
<?php } ?>