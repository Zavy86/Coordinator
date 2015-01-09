<?php
/* -------------------------------------------------------------------------- *\
|* -[ Uploads - Download ]--------------------------------------------------- *|
\* -------------------------------------------------------------------------- */
include('../core/api.inc.php');
include('api.inc.php');
api_loadModule();
// definitions
$permission=FALSE;
// acquire variables
$g_password=$_GET['password'];
// acquire objects
$link=api_uploads_link($_GET['link']);
// check link
if($link->public){
 if($link->password){
  // password protected link
  if(md5($g_password)==$link->password){
   if($_GET['download']){$permission=TRUE;}
    else{download_link();}
  }else{
   request_password();
  }
 }else{
  // public link
  $permission=TRUE;
 }
}else{
 // private link
 if(api_checkPermission("uploads","uploads_view")){$permission=TRUE;}
}
// check permission
if($permission){
 // increment counter
 $GLOBALS['db']->execute("UPDATE uploads_links SET counter=counter+1 WHERE id='".$link->id."'");
 // download file from database
 api_file_download($link->idUpload,"uploads_uploads",NULL,FALSE,"uploads");
}else{
 echo api_text("uploadNotFound");
}
// request password form
function request_password(){
 $form=new str_form("download.php","get","download");
 $form->addField("hidden","link",NULL,$_GET['link']);
 $form->addField("password","password",api_text("download-ff-password"),NULL,"input-large");
 $form->addControl("submit",api_text("download-fc-submit"));
 $form->render();
 exit();
}
// download link
function download_link(){
 header("Refresh: 2; url=download.php?link=".$_GET['link']."&password=".$_GET['password']."&download=1");
 echo "<a href='download.php?link=".$_GET['link']."&password=".$_GET['password']."&download=1'>".api_text("download-download")."</a>";
 exit();
}
?>