<?php
/* -------------------------------------------------------------------------- *\
|* -[ Uploads - API ]-------------------------------------------------------- *|
\* -------------------------------------------------------------------------- */

/**
 * File object
 *
 * @param mixed $file file id or object
 * @param booelan $del load also deleted objects if true
 * @return object file object
 */
function api_uploads_file($file,$del=FALSE){
 // get file object
 if(is_numeric($file)){$file=$GLOBALS['db']->queryUniqueObject("SELECT * FROM uploads_uploads WHERE id='".$file."'");}
 if(!$file->id){return FALSE;}
 if(!$del && $file->del){return FALSE;}
 // check and convert
 $file->name=stripslashes($file->name);
 $file->type=stripslashes($file->type);
 $file->hash=stripslashes($file->hash);
 $file->label=stripslashes($file->label);
 if(!$file->label){$file->label=substr($file->name,0,-4);}
 $file->description=stripslashes($file->description);
 $file->tags=stripslashes($file->tags);
 $file->txtContent=stripslashes($file->txtContent);
 // build links
 $file->links=array();
 $links=$GLOBALS['db']->query("SELECT * FROM uploads_links WHERE idUpload='".$file->id."'");
 while($link=$GLOBALS['db']->fetchNextObject($links)){$file->links[$link->id]=$link;}
 // return object
 return $file;
}

/**
 * File Status modal window
 *
 * @param mixed $file file id or object
 * @return object modal window object
 */
function api_uploads_fileStatusModal($file){
 if(is_numeric($file)){$file=$GLOBALS['db']->queryUniqueObject("SELECT * FROM uploads_uploads WHERE id='".$file."'");}
 if(!$file->id){return FALSE;}
 $return=new str_modal("upload_status_".$file->id);
 $return->header($file->name);
 // build status body dl
 $dl_body=new str_dl("br","dl-horizontal");
 $dl_body->addElement(api_text("api-uploads-dt-add"),api_text("api-uploads-dd-add",array(api_accountName($file->addIdAccount),api_timestampFormat($file->addDate,api_text("datetime")))));
 if($file->updIdAccount<>NULL){$dl_body->addElement(api_text("api-uploads-dt-upd"),api_text("api-uploads-dd-upd",array(api_accountName($file->updIdAccount),api_timestampFormat($file->updDate,api_text("datetime")))));}
 if($file->del){$dl_body->addElement("&nbsp;",api_icon("icon-trash")." ".api_text("api-uploads-dd-del"));}
 $return->body($dl_body->render(FALSE));
 return $return;
}


/**
 * Folder object
 *
 * @param mixed $folder folder id or object
 * @param booelan $del load also deleted objects if true
 * @return object folder object
 */
function api_uploads_folder($folder,$del=FALSE){
 // get file object
 if(is_numeric($folder)){$folder=$GLOBALS['db']->queryUniqueObject("SELECT * FROM uploads_folders WHERE id='".$folder."'");}
 if(!$folder->id){return FALSE;}
 if(!$del && $folder->del){return FALSE;}
 // check and convert
 $folder->name=stripslashes($folder->name);
 // make path
 $folder->path="/".$folder->name;
 $folder->path_html.="/".api_link("../uploads/uploads/uploads_list.php?idFolder=".$folder->id,$folder->name);
 $parent_folder=$folder;
 while($parent_folder->idFolder<>NULL){
  $parent_folder=$GLOBALS['db']->queryUniqueObject("SELECT * FROM uploads_folders WHERE id='".$parent_folder->idFolder."'");
  $folder->path="/".$parent_folder->name.$folder->path;
  $folder->path_html="/".api_link("../uploads/uploads/uploads_list.php?idFolder=".$parent_folder->id,$parent_folder->name).$folder->path_html;
 }
 $folder->path="/Uploads".$folder->path;
 $folder->path_html="/".api_link("../uploads/uploads/uploads_list.php?idFolder=","Uploads").$folder->path_html;
 return $folder;
}

/**
 * Folder Status modal window
 *
 * @param mixed $folder folder id or object
 * @return object modal window object
 */
function api_uploads_folderStatusModal($folder){
 if(is_numeric($folder)){$folder=$GLOBALS['db']->queryUniqueObject("SELECT * FROM uploads_folders WHERE id='".$folder."'");}
 if(!$folder->id){return FALSE;}
 $return=new str_modal("upload_folder_status_".$folder->id);
 $return->header($folder->name);
 // build status body dl
 $dl_body=new str_dl("br","dl-horizontal");
 $dl_body->addElement(api_text("api-uploads-dt-add"),api_text("api-uploads-dd-add",array(api_accountName($folder->addIdAccount),api_timestampFormat($folder->addDate,api_text("datetime")))));
 if($folder->updIdAccount<>NULL){$dl_body->addElement(api_text("api-uploads-dt-upd"),api_text("api-uploads-dd-upd",array(api_accountName($folder->updIdAccount),api_timestampFormat($folder->updDate,api_text("datetime")))));}
 if($folder->del){$dl_body->addElement("&nbsp;",api_icon("icon-trash")." ".api_text("api-uploads-dd-del"));}
 $return->body($dl_body->render(FALSE));
 return $return;
}

/**
 * Link Status modal window
 *
 * @param mixed $link link id or object
 * @return object modal window object
 */
function api_uploads_linkStatusModal($link){
 if(is_numeric($link)){$link=$GLOBALS['db']->queryUniqueObject("SELECT * FROM uploads_links WHERE id='".$link."'");}
 if(!$link->id){return FALSE;}
 $return=new str_modal("upload_link_status_".$link->id);
 $return->header($link->id);
 // build status body dl
 $dl_body=new str_dl("br","dl-horizontal");
 if($link->public){
  if($link->password){$typology_dd=api_text("uploads-links-typology-password");}
   else{$typology_dd=api_text("uploads-links-typology-public");}
 }else{$typology_dd=api_text("uploads-links-typology-private");}
 $dl_body->addElement(api_text("api-uploads-dt-typology"),$typology_dd);
 $dl_body->addElement(api_text("api-uploads-dt-counter"),api_text("api-uploads-dd-counter",$link->counter));
 $dl_body->addElement(api_text("api-uploads-dt-add"),api_text("api-uploads-dd-add",array(api_accountName($link->addIdAccount),api_timestampFormat($link->addDate,api_text("datetime")))));
 if($link->updIdAccount<>NULL){$dl_body->addElement(api_text("api-uploads-dt-upd"),api_text("api-uploads-dd-upd",array(api_accountName($link->updIdAccount),api_timestampFormat($link->updDate,api_text("datetime")))));}
 $return->body($dl_body->render(FALSE));
 return $return;
}

?>