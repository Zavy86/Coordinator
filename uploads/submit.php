<?php
/* -------------------------------------------------------------------------- *\
|* -[ Uploads - Submit ]----------------------------------------------------- *|
\* -------------------------------------------------------------------------- */
include('../core/api.inc.php');
include('api.inc.php');
$act=$_GET['act'];
switch($act){
 // folder
 case "folder_save":folder_save();break;
 case "folder_delete":folder_delete();break;
 // folder
 case "file_save":file_save();break;
 case "file_download":file_download();break;
 case "file_delete":file_delete("delete");break;
 case "file_undelete":file_delete("undelete");break;
 case "file_remove":file_delete("remove");break;
 // link
 case "link_save":link_save();break;
 case "link_delete":link_delete();break;
// default
default:
  $alert="?alert=submitFunctionNotFound&alert_class=alert-warning&act=".$act;
  exit(header("location: index.php".$alert));
}


/**
 * Folder Save
 */
function folder_save(){
 if(!api_checkPermission("uploads","folders_edit")){api_die("accessDenied");}
 // get objects
 $folder=api_uploads_folder($_GET['idFolder']);
 // aquire variables
 $p_idFolder=$_POST['idFolder'];
 $p_name=api_cleanString($_POST['name'],"/[^A-Za-zÀ-ÿ0-9- ]/");
 $p_description=api_cleanString($_POST['description'],"/[^A-Za-zÀ-ÿ0-9-.,' ]/");
 // build query
 if($folder->id){
  $query="UPDATE uploads_folders SET
   idFolder='".$p_idFolder."',
   name='".$p_name."',
   description='".$p_description."',
   updDate='".api_now()."',
   updIdAccount='".api_accountId()."'
   WHERE id='".$folder->id."'";
  // execute query
  $GLOBALS['db']->execute($query);
  // log event
  $log=api_log(API_LOG_NOTICE,"uploads","folderUpdated",
   "{logs_uploads_folderUpdated|".$folder->id."|".$p_name."|".$p_description."}",
   $folder->id,"uploads/uploads_list.php?idFolder=".$folder->id);
  // redirect
  $alert="&alert=folderUpdated&alert_class=alert-success&idLog=".$log->id;
  exit(header("location: uploads_list.php?idFolder=".$folder->id.$alert));
 }else{
  $query="INSERT INTO uploads_folders
   (idFolder,name,description,addDate,addIdAccount,updDate,updIdAccount) VALUES
   ('".$p_idFolder."','".$p_name."','".$p_description."',
    '".api_now()."','".api_accountId()."','".api_now()."','".api_accountId()."')";
  // execute query
  $GLOBALS['db']->execute($query);
  // set id to last inserted id
  $q_idFolder=$GLOBALS['db']->lastInsertedId();
  // log event
  $log=api_log(API_LOG_NOTICE,"uploads","folderCreated",
   "{logs_uploads_folderCreated|".$q_idFolder."|".$p_name."|".$p_description."}",
   $q_idFolder,"uploads/uploads_list.php?idFolder=".$q_idFolder);
  // redirect
  $alert="&alert=folderCreated&alert_class=alert-success&idLog=".$log->id;
  exit(header("location: uploads_list.php?idFolder=".$q_idFolder.$alert));
 }
 // redirect
 $alert="?alert=uploadError&alert_class=alert-error";
 exit(header("location: uploads_list.php".$alert));
}

/**
 * Folder Delete
 */
function folder_delete(){
 if(!api_checkPermission("uploads","folders_edit")){api_die("accessDenied");}
 // get objects
 $folder=api_uploads_folder($_GET['idFolder'],TRUE);
 // check folder
 if($folder->id){
  // check if folder is empty
  if(!$GLOBALS['db']->countOf("uploads_uploads","idFolder='".$folder->id."'")){
   // execute query
   $GLOBALS['db']->execute("DELETE FROM uploads_folders WHERE id='".$folder->id."'");
   // update folder size
   /* se si vuole implementare, fare il controllo nelle sotto cartelle anche nell'altra function
   if($folder->idFolder){
    $size=$GLOBALS['db']->queryUniqueValue("SELECT SUM(size) FROM `uploads_uploads` WHERE del='0' AND idFolder='".$folder->idFolder."'");
    $GLOBALS['db']->execute("UPDATE uploads_folders SET size='".$size."' WHERE id='".$folder->idFolder."'");
   }*/
   // log event
   $log=api_log(API_LOG_WARNING,"uploads","folderDeleted",
    "{logs_uploads_folderDeleted|".$folder->id."|".$folder->name."|".$folder->description."}",
    $folder->id,"uploads/uploads_list.php?idFolder=".$folder->idFolder);
   // alert
   $alert="&alert=folderDeleted&alert_class=alert-warning&idLog=".$log->id;
  }else{$alert="&alert=uploadError&alert_class=alert-error";}
 }else{$alert="&alert=uploadError&alert_class=alert-error";}
 // redirect
 exit(header("location: uploads_list.php?idFolder=".$folder->idFolder.$alert));
}


/**
 * File Save
 */
function file_save(){
 if(!api_checkPermission("uploads","files_edit")){api_die("accessDenied");}
 // get objects
 $file=api_uploads_file($_GET['idFile'],TRUE);
 // aquire variables
 $p_idFolder=$_POST['idFolder'];
 $p_label=api_cleanString($_POST['label'],"/[^A-Za-z0-9- ]/");
 $p_description=api_cleanString($_POST['description'],"/[^A-Za-zÀ-ÿ0-9-.,' ]/");
 $p_tags=api_cleanString(strtolower($_POST['tags']),"/[^a-z0-9-,]/");
 if($file->id){
  $query="UPDATE uploads_uploads SET
   idFolder='".$p_idFolder."',
   label='".addslashes($p_label)."',
   description='".addslashes($p_description)."',
   tags='".addslashes($p_tags)."',
   updDate='".api_now()."',
   updIdAccount='".api_accountId()."'
   WHERE id='".$file->id."'";
  // execute query
  $GLOBALS['db']->execute($query);
  // log event
  $log=api_log(API_LOG_NOTICE,"uploads","fileUpdated",
   "{logs_uploads_fileUpdated|".$file->id."|".$file->name."|".$p_label."}",
   $file->id,"uploads/uploads_files_view.php?idFile=".$file->id."&idFolder=".$p_idFolder);
  // redirect
  $alert="&alert=fileUpdated&alert_class=alert-success&idLog=".$log->id;
  exit(header("location: uploads_files_view.php?idFile=".$file->id."&idFolder=".$p_idFolder.$alert));
 }else{
  // call upload file api
  $result=api_file_upload($_FILES['file'],"uploads_uploads",NULL,$p_label,$p_description,$p_tags,TRUE,NULL,TRUE,"uploads",$file->id);
  // check result
  if($result->id){
   $GLOBALS['db']->execute("UPDATE uploads_uploads SET idFolder='".$p_idFolder."' WHERE id='".$result->id."'");
   // update folder size
   if($p_idFolder){
    $size=$GLOBALS['db']->queryUniqueValue("SELECT SUM(size) FROM `uploads_uploads` WHERE del='0' AND idFolder='".$p_idFolder."'");
    $GLOBALS['db']->execute("UPDATE uploads_folders SET size='".$size."' WHERE id='".$p_idFolder."'");
   }
   // check action
   if($result->id==$file->id){$action="fileUpdated";}else{$action="fileCreated";}
   // log event
   $log=api_log(API_LOG_NOTICE,"uploads",$action,
    "{logs_uploads_".$action."|".$result->id."|".$result->name."|".$result->label."}",
    $result->id,"uploads/uploads_files_view.php?idFile=".$result->id."&idFolder=".$p_idFolder);
   // redirect
   $alert="&alert=".$action."&alert_class=alert-success&idLog=".$log->id;
   exit(header("location: uploads_files_view.php?idFile=".$result->id."&idFolder=".$p_idFolder.$alert));
  }
 }
 // redirect
 $alert="?alert=uploadError&alert_class=alert-error";
 exit(header("location: uploads_list.php".$alert));
}

/**
 * File Download
 */
function file_download(){
 if(!api_checkPermission("uploads","uploads_view")){api_die("accessDenied");}
 // download file from database
 api_file_download($_GET['idFile'],"uploads_uploads",NULL,FALSE,"uploads");
}

/**
 * File Delete
 *
 * @param string $action delete | undelete | remove
 */
function file_delete($action){
 if(!api_checkPermission("uploads","files_edit")){api_die("accessDenied");}
 // get objects
 $file=api_uploads_file($_GET['idFile'],TRUE);
 // acquire variables
 $g_idFolder=$_GET['idFolder'];
 // check file
 if($file->id){
  // build contact query
  switch($action){
   case "delete":
    $query="UPDATE uploads_uploads SET del='1',updDate='".api_now()."',updIdAccount='".api_accountId()."' WHERE id='".$file->id."'";
    $log_action="fileDeleted";
    break;
   case "undelete":
    $query="UPDATE uploads_uploads SET del='0',updDate='".api_now()."',updIdAccount='".api_accountId()."' WHERE id='".$file->id."'";
    $log_action="fileUndeleted";
    break;
   case "remove":
    $query="DELETE FROM uploads_uploads WHERE id='".$file->id."'";
    @unlink("../uploads/uploads/uploads/".$file->id."-".$file->hash);
    $log_action="fileRemoved";
    break;
   default:$query=NULL;
  }
  // execute query
  if($query){
   $GLOBALS['db']->execute($query);
   // update folder size
   if($g_idFolder){
    $size=$GLOBALS['db']->queryUniqueValue("SELECT SUM(size) FROM `uploads_uploads` WHERE del='0' AND idFolder='".$g_idFolder."'");
    $GLOBALS['db']->execute("UPDATE uploads_folders SET size='".$size."' WHERE id='".$g_idFolder."'");
   }
   // log event
   $log=api_log(API_LOG_WARNING,"uploads",$log_action,
    "{logs_uploads_".$log_action."|".$file->id."|".$file->name."|".$file->label."}",
    $file->id,"uploads/uploads_list.php?idFile=".$file->id."&idFolder=".$file->idFolder);
   // alert
   $alert="&alert=".$log_action."&alert_class=alert-warning&idLog=".$log->id;
  }else{$alert="&alert=uploadError&alert_class=alert-error";}
 }else{$alert="&alert=uploadError&alert_class=alert-error";}
 // redirect
 exit(header("location: uploads_list.php?idFolder=".$file->idFolder.$alert));
}


/**
 * Link Save
 */
function link_save(){
 if(!api_checkPermission("uploads","files_edit")){api_die("accessDenied");}
 // get objects
 $link=api_uploads_link($_GET['idLink']);
 $file=api_uploads_file($_POST['idUpload']);
 // aquire variables
 $p_idUpload=$_POST['idUpload'];
 $p_public=$_POST['public'];
 $p_password=$_POST['password'];
 if($p_password==$link->password){$p_password=NULL;}
 if($p_password<>NULL){$p_password=md5($p_password);}
 // build query
 if($link->id){
  $query="UPDATE uploads_links SET
   idUpload='".$p_idUpload."',
   public='".$p_public."',";
  if($p_password){$query.="password='".$p_password."',";}
  $query.="updDate='".api_now()."',
   updIdAccount='".api_accountId()."'
   WHERE id='".$link->id."'";
  // execute query
  $GLOBALS['db']->execute($query);
  // log event
  $log=api_log(API_LOG_NOTICE,"uploads","linkUpdated",
   "{logs_uploads_linkUpdated|".$file->id."|".$link->id."|".$p_public."}",
   $file->id,"uploads/uploads_files_view.php?idFile=".$file->id."&idFolder=".$file->idFolder);
  // redirect
  $alert="&alert=linkUpdated&alert_class=alert-success&idLog=".$log->id;
  exit(header("location: uploads_files_view.php?idFile=".$file->id."&idFolder=".$file->idFolder.$alert));
 }else{
  $idLink=md5(api_now());
  $query="INSERT INTO uploads_links
   (id,idUpload,public,password,addDate,addIdAccount) VALUES
   ('".$idLink."','".$p_idUpload."','".$p_public."','".$p_password."',
    '".api_now()."','".api_accountId()."')";
  // execute query
  $GLOBALS['db']->execute($query);
  // log event
  $log=api_log(API_LOG_NOTICE,"uploads","linkCreated",
   "{logs_uploads_linkCreated|".$file->id."|".$idLink."|".$p_public."}",
   $file->id,"uploads/uploads_files_view.php?idFile=".$file->id."&idFolder=".$file->idFolder);
  // redirect
  $alert="&alert=linkCreated&alert_class=alert-success&idLog=".$log->id;
  exit(header("location: uploads_files_view.php?idFile=".$file->id."&idFolder=".$file->idFolder.$alert));
 }
 // redirect
 $alert="?alert=uploadError&alert_class=alert-error";
 exit(header("location: uploads_list.php".$alert));
}

/**
 * Link Delete
 */
function link_delete(){
 if(!api_checkPermission("uploads","files_edit")){api_die("accessDenied");}
 // get objects
 $link=api_uploads_link($_GET['idLink'],TRUE);
 $file=api_uploads_file($link->idUpload,TRUE);
 // check link
 if($link->id){
  // delete link
  $GLOBALS['db']->execute("DELETE FROM uploads_links WHERE id='".$link->id."'");
  // log event
  $log=api_log(API_LOG_WARNING,"uploads","linkDeleted",
   "{logs_uploads_linkDeleted|".$file->id."|".$link->id."|".$link->public."}",
   $file->id,"uploads/uploads_list.php?idFile=".$file->id."&idFolder=".$file->idFolder);
  // redirect
  $alert="&alert=linkDeleted&alert_class=alert-warning&idLog=".$log->id;
  exit(header("location: uploads_files_view.php?idFile=".$file->id."&idFolder=".$file->idFolder.$alert));
 }
 // redirect
 $alert="?alert=uploadError&alert_class=alert-error";
 exit(header("location: uploads_list.php".$alert));
}

?>