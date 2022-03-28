<?php
/* -------------------------------------------------------------------------- *\
|* -[ Workflows - Add ]------------------------------------------------------ *|
\* -------------------------------------------------------------------------- */
$checkPermission="workflows_add";
require_once("template.inc.php");
function content(){
 // acquire variables
 $g_category=$_GET['idCategory'];
 $g_mail=$_GET['mail'];
 // get flow object
 $flow=api_workflows_flow($_GET['idFlow'],TRUE);
 if($g_mail){$mail=$GLOBALS['db']->queryUniqueObject("SELECT * FROM workflows_mails WHERE id='".$g_mail."'");}
 // show workflow informations
 if($flow->id>0){
  echo "<h4>".stripslashes($flow->subject);
  if(strlen($flow->description)>0){echo " &rarr; <small class='muted'>".stripslashes(nl2br($flow->description))."</small>";}
  echo "</h4>\n";
  if(strlen($flow->advice)>0){echo "<p>".stripslashes(nl2br($flow->advice))."</p>\n";}
 }
 echo "<br>\n";
 // build form
 $form=new str_form("submit.php?act=workflow_save&idFlow=".$flow->id,"post","workflows_add");
 if(!$flow->id){
  // flow fields
  $form->addField("hidden","idMail",NULL,$mail->id);
  $form->addField("select","idCategory",api_text("add-ff-category"));
  $form->addFieldOption("",ucfirst(api_text("undefined")));
  $categories=$GLOBALS['db']->query("SELECT * FROM workflows_categories WHERE idCategory='0' ORDER BY name ASC");
  while($category=$GLOBALS['db']->fetchNextObject($categories)){
   $form->addFieldOption($category->id,stripslashes($category->name),($category->id==$g_category)?TRUE:FALSE);
   $subcategories=$GLOBALS['db']->query("SELECT * FROM workflows_categories WHERE idCategory='".$category->id."' ORDER BY name ASC");
   while($subcategory=$GLOBALS['db']->fetchNextObject($subcategories)){
    $form->addFieldOption($subcategory->id,"&minus; ".stripslashes($subcategory->name),($subcategory->id==$g_category)?TRUE:FALSE);
    $subsubcategories=$GLOBALS['db']->query("SELECT * FROM workflows_categories WHERE idCategory='".$subcategory->id."' ORDER BY name ASC");
    while($subsubcategory=$GLOBALS['db']->fetchNextObject($subsubcategories)){
     $form->addFieldOption($subsubcategory->id,"&nbsp; &minus; ".stripslashes($subsubcategory->name),($subsubcategory->id==$g_category)?TRUE:FALSE);
    }
   }
  }
  $form->addField("radio","typology",api_text("add-ff-typology"));
  $form->addFieldOption(1,api_text("typology-request"),TRUE);
  $form->addFieldOption(2,api_text("typology-incident"));
  $form->addField("text","subject",api_text("add-ff-subject"),str_replace(array("I: ","R: "),"",stripslashes($mail->subject)),"input-xxlarge");
  $form->addField("radio","priority",api_text("add-ff-priority"));
  //$form->addFieldOption(1,api_text("priority-highest"));
  $form->addFieldOption(2,api_text("priority-high"));
  $form->addFieldOption(3,api_text("priority-medium"),TRUE);
  $form->addFieldOption(4,api_text("priority-low"));
  $form->addFieldOption(5,api_text("priority-lowest"));
 }else{
  // hidden fields from
  $form->addField("hidden","idCategory",NULL,$flow->idCategory);
  $form->addField("hidden","typology",NULL,$flow->typology);
  $form->addField("hidden","subject",NULL,$flow->subject);
  $form->addField("hidden","priority",NULL,$flow->priority);
  // flow fields
  foreach($flow->fields as $field){
   // build filed
   $form->addField($field->typology,$field->name,stripslashes($field->label),api_workflows_replaceTagCodes($field->value),$field->class,$field->placeholder);
   $field_options=api_workflows_flowFieldOptions($field);
   if(is_array($field_options)){
    foreach($field_options as $option){
     $form->addFieldOption($option->value,$option->label,$option->selected);
    }
   }
  }
 }
 // defaults fields
 $referent=api_account()->name;
 if($mail->id){$referent=NULL;}
 $form->addField("text","referent",api_text("add-ff-referent"),$referent,"input-medium");
 $form->addField("text","phone",api_text("add-ff-phone"),api_account()->phone,"input-small");
 $form->addField("textarea","note",api_text("add-ff-note"),stripslashes($mail->message),"input-xxlarge");
 $form->addField("file","add_file",api_text("add-ff-file"),NULL,"input-xlarge");
 // controls
 $form->addControl("submit",api_text("add-fc-submit"));
 if($mail->id){$form->addControl("button",api_text("add-fc-cancel"),NULL,"workflows_mails_list.php");}
 else{$form->addControl("button",api_text("add-fc-cancel"),NULL,"workflows_search.php?idCategory=".$g_category);}
 // show form
 $form->render();
?>
<script type="text/javascript">
 $(document).ready(function(){
  // validation
  $("form[name='workflows_add']").validate({
   rules:{
    idCategory:{required:true},
<?php
 if(is_array($flow->fields)){
  foreach($flow->fields as $field){
   if($field->required){echo "    ".$field->name.":{required:true},\n";}
  }
 }
?>
    referent:{required:true},
    phone:{required:true}
   },
   submitHandler:function(form){form.submit();}
  });
  $("form[name='workflows_add']").submit(function(){$("#workflows_add_control_submit").attr("disabled","disabled");});
  $("form[name='workflows_add']").change(function(){$("#workflows_add_control_submit").removeAttr("disabled");});
 });
</script>
<?php } ?>