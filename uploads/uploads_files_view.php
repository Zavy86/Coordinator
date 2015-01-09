<?php
/* -------------------------------------------------------------------------- *\
|* -[ Uploads - Files View ]------------------------------------------------- *|
\* -------------------------------------------------------------------------- */
$checkPermission="uploads_view";
include("template.inc.php");
function content(){
 // definitions
 $links_modal_array=array();
 // get objects
 $file=api_uploads_file($_GET['idFile'],TRUE);
 $folder=api_uploads_folder($file->idFolder);
 if(!$file->id){echo api_text("uploadNotFound");return FALSE;}
 // build status modal
 //$file_status_modal=api_uploads_fileStatusModal($file);
 // make links
 foreach($file->links as $link){
  $links_modal_array[]=api_uploads_linkStatusModal($link);
  if($link->public){
   if($link->password){$link_icon="icon-asterisk";$link_title=api_text("uploads-links-typology-password");}
    else{$link_icon="icon-globe";$link_title=api_text("uploads-links-typology-public");}
  }else{$link_icon="icon-lock";$link_title=api_text("uploads-links-typology-private");}
  $dd_links.="<br>".api_link("uploads_links_edit.php?idLink=".$link->id."&idFile=".$file->id,api_icon("icon-edit"));
  $dd_links.=" ".end($links_modal_array)->link(api_icon($link_icon,$link_title));
  $dd_links.=" <code>".api_link("../uploads/download.php?link=".$link->id,$link->id)."</code>";
  //$dd_links.=" ".api_link("#",api_icon("icon-share",api_text("uploads_files_view-dd-share")));
 }
 // build contact dynamic list
 $dl_file=new str_dl("br","dl-horizontal");
 $dl_file->addElement(api_text("uploads_files_view-dt-name"),$file->label);
 $dl_file->addElement(api_text("uploads_files_view-dt-file"),$folder->path_html." / ".api_link("submit.php?act=file_download&idFile=".$file->id,$file->name));
 if($dd_links){$dl_file->addElement(api_text("uploads_files_view-dt-links"),substr($dd_links,4));}
 if($file->tags){$dl_file->addElement(api_text("uploads_files_view-dt-tags"),$file->tags_html);}
 $dl_file->addElement(api_text("uploads_files_view-dt-size"),$file->size_formatted);
 if($file->description){$dl_file->addElement(api_text("uploads_files_view-dt-description"),$file->description);}
 /*if($file->del){$status_icon="icon-trash";}else{$status_icon="icon-info-sign";}
 if($file->updIdAccount){$status="uploads_files_view-dt-updIdAccount";$account_name=api_accountName($file->updIdAccount);}
  else{$status="uploads_files_view-dt-addIdAccount";$account_name=api_accountName($file->addIdAccount);}
 $dl_file->addElement(api_text($status),$account_name." &nbsp; ".$file_status_modal->link(api_icon($status_icon)));*/
 // renderize dl
 $dl_file->render();
 // renderize lnks modal window
 foreach($links_modal_array as $modal){if(is_object($modal)){$modal->render();}}
 // renderize status modal window
 //if(is_object($file_status_modal)){$file_status_modal->render();}
}
?>