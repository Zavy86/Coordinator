<?php
/* -------------------------------------------------------------------------- *\
|* -[ Workflows - Flow Edit ]------------------------------------------------ *|
\* -------------------------------------------------------------------------- */
$checkPermission="workflows_admin";
require_once("template.inc.php");
function content(){
 // definitions
 $categories_array=array();
 // acquire variables
 $g_idCategory=$_GET['idCategory'];
 if(!$g_idCategory){$g_idCategory=0;}
 // get flow object
 $flow=api_workflows_flow($_GET['idFlow'],FALSE);
 // build categories array
 $categories=$GLOBALS['db']->query("SELECT * FROM workflows_categories WHERE idCategory='0' ORDER BY name ASC");
 while($category=$GLOBALS['db']->fetchNextObject($categories)){
  $category->level=1;
  $categories_array[]=$category;
  // subcategories
  $subcategories=$GLOBALS['db']->query("SELECT * FROM workflows_categories WHERE idCategory='".$category->id."' ORDER BY name ASC");
  while($subcategory=$GLOBALS['db']->fetchNextObject($subcategories)){
   $subcategory->level=2;
   $subcategory->name="&minus; ".$subcategory->name;
   $categories_array[]=$subcategory;
   // subsubcategories
   $subsubcategories=$GLOBALS['db']->query("SELECT * FROM workflows_categories WHERE idCategory='".$subcategory->id."' ORDER BY name ASC");
   while($subsubcategory=$GLOBALS['db']->fetchNextObject($subsubcategories)){
    $subsubcategory->level=3;
    $subsubcategory->name="&nbsp;&nbsp; &minus; ".$subsubcategory->name;
    $categories_array[]=$subsubcategory;
   }
  }
 }
 // build form
 $form=new str_form("submit.php?act=flow_save&idFlow=".$flow->id,"post","workflow_edit");
 $form->addField("select","idCategory",api_text("flows_edit-ff-category"));
 foreach($categories_array as $category){
  $form->addFieldOption($category->id,stripslashes($category->name),($flow->idCategory==$category->id)?TRUE:FALSE);
 }
 $form->addField("text","subject",api_text("flows_edit-ff-subject"),stripslashes($flow->subject),"input-xxlarge",api_text("flows_edit-ff-subject-placeholder"));
 $form->addField("text","description",api_text("flows_edit-ff-description"),stripslashes($flow->description),"input-xxlarge",api_text("flows_edit-ff-description-placeholder"));
 $form->addField("textarea","advice",api_text("flows_edit-ff-advice"),stripslashes($flow->advice),"input-xxlarge",api_text("flows_edit-ff-advice-placeholder"));
 $form->addField("text","sla",api_text("flows_edit-ff-sla"),stripslashes($flow->sla),"input-mini",NULL,FALSE,NULL,api_text("minutes"));
 $form->addField("text","guide",api_text("flows_edit-ff-guide"),$flow->guide,"input-xxlarge",api_text("flows_edit-ff-guide-placeholder"));
 $form->addField("radio","typology",api_text("flows_edit-ff-typology"));
 $form->addFieldOption(1,api_text("typology-request"),($flow->typology==1 || !$flow->id)?TRUE:FALSE);
 $form->addFieldOption(2,api_text("typology-incident"),($flow->typology==2)?TRUE:FALSE);
 $form->addField("checkbox","pinned","&nbsp;");
 $form->addFieldOption(1,api_text("flows_edit-fo-pinned"),($flow->pinned==1)?TRUE:FALSE);
 $form->addField("radio","priority",api_text("flows_edit-ff-priority"));
 $form->addFieldOption(1,api_text("priority-highest"),($flow->priority==1)?TRUE:FALSE);
 $form->addFieldOption(2,api_text("priority-high"),($flow->priority==2)?TRUE:FALSE);
 $form->addFieldOption(3,api_text("priority-medium"),($flow->priority==3 || !$flow->id)?TRUE:FALSE);
 $form->addFieldOption(4,api_text("priority-low"),($flow->priority==4)?TRUE:FALSE);
 $form->addFieldOption(5,api_text("priority-lowest"),($flow->priority==5)?TRUE:FALSE);
 $form->addControl("submit",api_text("flows_edit-fc-submit"));
 if($flow->id){$form->addControl("button",api_text("flows_edit-fc-cancel"),NULL,"workflows_flows_view.php?idFlow=".$flow->id);}
 else{$form->addControl("button",api_text("flows_edit-fc-cancel"),NULL,"workflows_flows_list.php?idCategory=".$g_idCategory);}
 // show form
 $form->render();
?>
<script type="text/javascript">
 $(document).ready(function(){
  // validation
  $('form').validate({
   rules:{
    subject:{required:true},
    sla:{required:true,number:true}
   },
   submitHandler:function(form){form.submit();}
  });
 });
</script>
<?php } ?>