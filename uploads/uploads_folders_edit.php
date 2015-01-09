<?php
/* -------------------------------------------------------------------------- *\
|* -[ Uploads - Folders Edit ]----------------------------------------------- *|
\* -------------------------------------------------------------------------- */
$checkPermission="folders_edit";
include("template.inc.php");
function content(){
 // get objects
 $folder=api_uploads_folder($_GET['idFolder'],TRUE);
 // acquire variables
 $g_idParentFolder=$_GET['idParentFolder'];
 if(!$folder->id){$folder->idFolder=$g_idParentFolder;}
 // build form
 $form=new str_form("submit.php?act=folder_save&idFolder=".$folder->id,"post","uploads_folders_edit");
 // folders
 $form->addField("select","idFolder",api_text("uploads_folders_edit-ff-idFolder"),NULL,"input-xlarge");
  $form->addFieldOption("","/Uploads");
  $folders=api_query_tree("idFolder",NULL,"name ASC");
  foreach($folders as $current){
   if($current->id==$folder->id){continue;}
   $current=api_uploads_folder($current,TRUE);
   $form->addFieldOption($current->id,$current->path,($current->id==$folder->idFolder?TRUE:FALSE));
  }
 // data
 $form->addField("text","name",api_text("uploads_folders_edit-ff-name"),$folder->name,"input-xlarge",api_text("uploads_folders_edit-ff-name-placeholder"));
 $form->addField("text","description",api_text("uploads_folders_edit-ff-description"),$folder->description,"input-xxlarge",api_text("uploads_folders_edit-ff-description-placeholder"));
 // controls
 $form->addControl("submit",api_text("uploads_folders_edit-fc-submit"));
 if($folder->id){
  $form->addControl("button",api_text("uploads_folders_edit-fc-cancel"),NULL,"uploads_list.php?idFolder=".$folder->id);
  if(!$GLOBALS['db']->countOf("uploads_uploads","idFolder='".$folder->id."'")){
   $form->addControl("button",api_text("uploads_folders_edit-fc-delete"),"btn-warning","submit.php?act=folder_delete&idFolder=".$folder->id,api_text("uploads_folders_edit-fc-delete-confirm"));
  }
 }else{
  $form->addControl("button",api_text("uploads_folders_edit-fc-cancel"),NULL,"uploads_list.php&idFolder=".$folder->id);
 }
 // show form
 $form->render();
}
?>