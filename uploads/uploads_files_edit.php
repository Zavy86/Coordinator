<?php
/* -------------------------------------------------------------------------- *\
|* -[ Uploads - Files Edit ]------------------------------------------------- *|
\* -------------------------------------------------------------------------- */
include("template.inc.php");
function content(){
 // get objects
 $file=api_uploads_file($_GET['idFile'],TRUE);
 $g_idFolder=$_GET['idFolder'];
 if(!$g_idFolder){$g_idFolder=$file->idFolder;}
 // build form
 $form=new str_form("submit.php?act=uploads_file_save&idFile=".$file->id,"post","contacts_attachments");
 // folders
 $form->addField("select","idFolder",api_text("uploads_files_edit-ff-idFolder"),NULL,"input-xlarge");
  $form->addFieldOption("","/Uploads");
  $folders=api_query_tree("idFolder",NULL,"name ASC");
  foreach($folders as $folder){
   $folder=api_uploads_folder($folder,TRUE);
   $form->addFieldOption($folder->id,$folder->path,($folder->id==$g_idFolder?TRUE:FALSE));
  }
 // file
 if($file->id){$file_placeholder=api_text("uploads_files_edit-ff-file-placeholder-replace");}
  else{$file_placeholder=api_text("uploads_files_edit-ff-file-placeholder");}
 $form->addField("file","file",api_text("uploads_files_edit-ff-file"),NULL,"input-xlarge",$file_placeholder);
 // data
 $form->addField("text","label",api_text("uploads_files_edit-ff-label"),$file->label,"input-xlarge",api_text("uploads_files_edit-ff-label-placeholder"));
 $form->addField("text","description",api_text("uploads_files_edit-ff-description"),$file->description,"input-xxlarge",api_text("uploads_files_edit-ff-description-placeholder"));
 $form->addField("text","tags",api_text("uploads_files_edit-ff-tags"),$file->tags,"input-xxlarge",api_text("uploads_files_edit-ff-tags-placeholder"));
 // controls
 $form->addControl("submit",api_text("uploads_files_edit-fc-submit"));
 if($file->id){
  $form->addControl("button",api_text("uploads_files_edit-fc-cancel"),NULL,"uploads_files_view.php?idFile=".$file->id."&idFolder=".$g_idFolder);
  if($file->del){
   $form->addControl("button",api_text("uploads_files_edit-fc-undelete"),"btn-warning","submit.php?act=file_undelete&idFile=".$file->id."&idFolder=".$g_idFolder);
   $form->addControl("button",api_text("uploads_files_edit-fc-remove"),"btn-danger","submit.php?act=file_remove&idFile=".$file->id."&idFolder=".$g_idFolder,api_text("uploads_files_edit-fc-remove-confirm"));
  }
  else{$form->addControl("button",api_text("uploads_files_edit-fc-delete"),"btn-warning","submit.php?act=file_delete&idFile=".$file->id."&idFolder=".$g_idFolder,api_text("uploads_files_edit-fc-delete-confirm"));}
 }else{
  $form->addControl("button",api_text("uploads_files_edit-fc-cancel"),NULL,"uploads_list.php?idFolder=".$g_idFolder);
 }
 // show form
 $form->render();
}
?>