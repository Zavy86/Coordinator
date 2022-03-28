<?php
/* -------------------------------------------------------------------------- *\
|* -[ Workflows - Add ]------------------------------------------------------ *|
\* -------------------------------------------------------------------------- */
$checkPermission="workflows_add";
require_once("template.inc.php");
function content(){
 // get workflow object
 $workflow=api_workflows_workflow($_GET['id'],FALSE);
 if(!$workflow->id){echo api_text("workflowNotFound");return FALSE;}
 // set idCategory
 if(isset($_GET['idCategory'])){$workflow->idCategory=$_GET['idCategory'];}
 // set idFlow and load Flow data
 if(isset($_GET['idFlow'])){
  $flow=api_workflows_flow($_GET['idFlow']);
  if($flow->id>0){
   $workflow->idFlow=$flow->id;
   $workflow->typology=$flow->typology;
   $workflow->subject=$flow->subject;
   $workflow->priority=$flow->priority;
  }
 }

 // build form
 $form=new str_form("submit.php?act=workflow_update&id=".$workflow->id,"post","workflows_edit");

 $form->addField("select","idCategory",api_text("edit-ff-category"));
 $categories=$GLOBALS['db']->query("SELECT * FROM workflows_categories WHERE idCategory='0' ORDER BY name ASC");
 while($category=$GLOBALS['db']->fetchNextObject($categories)){
  $form->addFieldOption($category->id,stripslashes($category->name),($category->id==$workflow->idCategory)?TRUE:FALSE);
  $subcategories=$GLOBALS['db']->query("SELECT * FROM workflows_categories WHERE idCategory='".$category->id."' ORDER BY name ASC");
  while($subcategory=$GLOBALS['db']->fetchNextObject($subcategories)){
   $form->addFieldOption($subcategory->id,"&minus; ".stripslashes($subcategory->name),($subcategory->id==$workflow->idCategory)?TRUE:FALSE);
   $subsubcategories=$GLOBALS['db']->query("SELECT * FROM workflows_categories WHERE idCategory='".$subcategory->id."' ORDER BY name ASC");
   while($subsubcategory=$GLOBALS['db']->fetchNextObject($subsubcategories)){
    $form->addFieldOption($subsubcategory->id,"&nbsp; &minus; ".stripslashes($subsubcategory->name),($subsubcategory->id==$workflow->idCategory)?TRUE:FALSE);
   }
  }
 }

 $form->addField("select","idFlow",api_text("edit-ff-flow"));
 $flows=$GLOBALS['db']->query("SELECT * FROM workflows_flows WHERE idCategory='".$workflow->idCategory."' ORDER BY subject ASC");
 $form->addFieldOption('',ucfirst(api_text("undefined")));
 while($flow=$GLOBALS['db']->fetchNextObject($flows)){
  $form->addFieldOption($flow->id,stripslashes($flow->subject),($flow->id==$workflow->idFlow)?TRUE:FALSE);
 }

 $form->addField("radio","typology",api_text("edit-ff-typology"));
 $form->addFieldOption(1,api_text("typology-request"),(1==$workflow->typology)?TRUE:FALSE);
 $form->addFieldOption(2,api_text("typology-incident"),(2==$workflow->typology)?TRUE:FALSE);
 $form->addField("text","subject",api_text("edit-ff-subject"),stripslashes($workflow->subject),"input-xxlarge");
 $form->addField("radio","priority",api_text("edit-ff-priority"));
 $form->addFieldOption(1,api_text("priority-highest"),(1==$workflow->priority)?TRUE:FALSE);
 $form->addFieldOption(2,api_text("priority-high"),(2==$workflow->priority)?TRUE:FALSE);
 $form->addFieldOption(3,api_text("priority-medium"),(3==$workflow->priority)?TRUE:FALSE);
 $form->addFieldOption(4,api_text("priority-low"),(4==$workflow->priority)?TRUE:FALSE);
 $form->addFieldOption(5,api_text("priority-lowest"),(5==$workflow->priority)?TRUE:FALSE);

 $form->addField("textarea","description",api_text("edit-ff-description"),stripslashes($workflow->description),"input-xxlarge",NULL,FALSE,10);

 $form->addField("textarea","note",api_text("edit-ff-note"),stripslashes($workflow->note),"input-xxlarge",NULL,FALSE,10);

 $form->addField("radio","tickets",api_text("edit-ff-tickets"));
 $form->addFieldOption(0,api_text("edit-fo-tickets-null"),TRUE);
 $form->addFieldOption(1,api_text("edit-fo-tickets-open"));


 // controls
 $form->addControl("submit",api_text("edit-fc-submit"));
 $form->addControl("button",api_text("edit-fc-cancel"),NULL,"workflows_view.php?id=".$workflow->id);
 // show form
 $form->render();

?>
<script type="text/javascript">
 $(document).ready(function(){
  // idCategory Change
  $("#workflows_edit_input_idCategory").change(function(){
   window.location.href="workflows_edit.php?id=<?php echo $workflow->id; ?>&idCategory="+this.value;
  });
  // idFlow Change
  $("#workflows_edit_input_idFlow").change(function(){
   window.location.href="workflows_edit.php?id=<?php echo $workflow->id; ?>&idCategory=<?php echo $workflow->idCategory; ?>&idFlow="+this.value;
  });
 });
</script>
<?php } ?>