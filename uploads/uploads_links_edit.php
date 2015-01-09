<?php
/* -------------------------------------------------------------------------- *\
|* -[ Uploads - Links Edit ]------------------------------------------------- *|
\* -------------------------------------------------------------------------- */
$checkPermission="files_edit";
include("template.inc.php");
function content(){
 // get objects
 $link=api_uploads_link($_GET['idLink']);
 if($link->idUpload){$file=api_uploads_file($link->idUpload,TRUE);}
  else{$file=api_uploads_file($_GET['idFile'],TRUE);}
 $g_idFolder=$_GET['idFolder'];
 if(!$g_idFolder){$g_idFolder=$file->idFolder;}
 // build form
 $form=new str_form("submit.php?act=link_save&idLink=".$link->id,"post","uploads_links_edit");
 // data
 $form->addField("hidden","idUpload",NULL,$file->id);
 $form->addField("radio","public",api_text("uploads_links_edit-ff-accessibility"));
 $form->addFieldOption(0,api_text("uploads_links_edit-ff-private"),($link->public==0?TRUE:FALSE));
 $form->addFieldOption(1,api_text("uploads_links_edit-ff-public"),($link->public==1?TRUE:FALSE));
 $form->addField("password","password",api_text("uploads_links_edit-ff-password"),$link->password,"input-large");
 // controls
 $form->addControl("submit",api_text("uploads_links_edit-fc-submit"));
 if($file->id){
  $form->addControl("button",api_text("uploads_links_edit-fc-cancel"),NULL,"uploads_files_view.php?idFile=".$file->id."&idFolder=".$g_idFolder);
  if($link->id){$form->addControl("button",api_text("uploads_links_edit-fc-delete"),"btn-warning","submit.php?act=link_delete&idLink=".$link->id,api_text("uploads_links_edit-fc-delete-confirm"));}
 }else{
  $form->addControl("button",api_text("uploads_links_edit-fc-cancel"),NULL,"uploads_list.php?idFolder=".$g_idFolder);
 }
 // show form
 $form->render();
?>
<script type="text/javascript">
 $(document).ready(function(){
  // toggle public password
  <?php if($link->public==0){echo "  $(\"#field_password\").hide();\n";} ?>
  $("input[name='public']").change(function(){
   if($(this).filter(':checked').val()==1){
    $("#field_password").show("medium");
   }else{
    $("#field_password").hide("medium");
   }
  });
 });
</script>
<?php } ?>