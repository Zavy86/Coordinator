<?php
/* -------------------------------------------------------------------------- *\
|* -[ Workflows - Categories Edit ]------------------------------------------ *|
\* -------------------------------------------------------------------------- */
$checkPermission="workflows_admin";
require_once("template.inc.php");
function content(){
 // definitions
 $categories_array=array();
 // get category object
 $selected_category=api_workflows_category($_GET['id']);
 if(!$selected_category->id){$selected_category->id=0;}
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
 // build table
 $table=new str_table(api_text("categories-tr-unvalued"));
 // build table headers
 $table->addHeader("&nbsp;",NULL,16);
 $table->addHeader(api_text("categories-th-category"),"nowarp");
 $table->addHeader(api_text("categories-th-description"),NULL,"100%");
 $table->addHeader("&nbsp;",NULL,16);
 // build table rows
 foreach($categories_array as $category){
  $table->addRow();
  // build table fields
  $table->addField("<a href='workflows_flows_list.php?filtered=1&idCategory[]=".$category->id."'>".api_icon("icon-search")."</a>");
  $table->addField(stripslashes($category->name),"nowarp");
  $table->addField("<small><i>".stripslashes($category->description)."</i><small>");
  $table->addField("<a href='workflows_categories.php?id=".$category->id."'>".api_icon("icon-edit")."</a>");
 }
 // build form
 $form=new str_form("submit.php?act=category_save&id=".$selected_category->id,"post","categories");
 $form->addField("select","idCategory",api_text("categories-ff-category"));
 $form->addFieldOption(0,api_text("categories-ff-category-default"));
 foreach($categories_array as $category){
  if($category->level<3){
   $form->addFieldOption($category->id,stripslashes($category->name),($selected_category->idCategory==$category->id)?TRUE:FALSE);
  }
 }
 $form->addField("text","name",api_text("categories-ff-name"),stripslashes($selected_category->name),"input-large",api_text("categories-ff-name-placeholder"));
 $form->addField("text","description",api_text("categories-ff-description"),stripslashes($selected_category->description),"input-xlarge",api_text("categories-ff-description-placeholder"));
 $form->addField("hidden","idGroup",api_text("categories-ff-group"),$selected_category->idGroup,"input-large");
 $form->addControl("submit",api_text("categories-fc-submit"));
 if($selected_category->id){$form->addControl("button",api_text("categories-fc-cancel"),NULL,"workflows_categories.php");}
 // open split
 $GLOBALS['html']->split_open();
 $GLOBALS['html']->split_span(7);
 // show table
 $table->render();
 // split page
 $GLOBALS['html']->split_span(5);
 if($selected_category->id){echo "<center><h5>".api_text("categories-update")."</h5></center><br>\n";}
 else{echo "<center><h5>".api_text("categories-create")."</h5></center><br>\n";}
 // show form
 $form->render();
 // close split
 $GLOBALS['html']->split_close();
?>
<script type="text/javascript">
 $(document).ready(function(){
  // submit toggle
  $('input[type="submit"]').attr('disabled','disabled');
  $('form').change(function(){
   $('input[type="submit"]').removeAttr('disabled');
  });
  // select2 idGroup
  $("input[name=idGroup]").select2({
   placeholder:"<?php echo api_text("categories-ff-group-placeholder");?>",
   allowClear:true,
   ajax:{
    url:"../accounts/groups_json.inc.php",
    dataType:'json',
    data:function(term,page){return{q:term};},
    results:function(data,page){return{results:data};}
   },
   initSelection:function(element,callback){
    var id=$(element).val();
    if(id!==""){
     $.ajax("../accounts/groups_json.inc.php?q="+id,{
      dataType:"json"
     }).done(function(data){callback(data[0]);});
    }
   }
  });
  // validation
  $('form').validate({
   ignore:null,
   rules:{
    name:{required:true,minlength:3},
    idGroup:{required:true}
   },
   submitHandler:function(form){form.submit();}
  });
 });
</script>
<?php } ?>