<?php
/* -------------------------------------------------------------------------- *\
|* -[ Uploads - List ]------------------------------------------------------- *|
\* -------------------------------------------------------------------------- */
$checkPermission="uploads_view";
include("template.inc.php");
function content(){
 // definitions
 $uploads_status_modals_array=array();
 // acquire variables
 $g_search=$_GET['q'];
 $current_folder=api_uploads_folder($_GET['idFolder']);
 // show filters
 echo $GLOBALS['navigation']->filtersText();
 // build table
 $table=new str_table(api_text("uploads_list-tr-unvalued"),TRUE,$GLOBALS['navigation']->filtersGet()."&idFolder=".$current_folder->id);
 $table->addHeader("&nbsp;",NULL,"16");
 $table->addHeader(api_text("uploads_list-th-name"),"nowarp",NULL,"uploads_uploads.label");
 $table->addHeader(api_text("uploads_list-th-description"),NULL,"100%","uploads_uploads.description");
 $table->addHeader(api_text("uploads_list-th-size"),"nowarp text-right",NULL,"uploads_uploads.size");
 $table->addHeader(api_text("uploads_list-th-timestamp"),"nowarp text-center",NULL,"uploads_uploads.updDate");
 $table->addHeader("&nbsp;",NULL,"16");
 // previous folder
 if($current_folder->id){
  // build group row
  $table->addRow();
  // build table field
  $table->addField(api_link("uploads_list.php?idFolder=".$current_folder->idFolder,api_icon("icon-arrow-left")),"nowarp");
  $table->addField(api_text("uploads_list-td-back"),"nowarp");
  $table->addField("&nbsp;");
  $table->addField("&nbsp;");
  $table->addField("&nbsp;");
  $table->addField("&nbsp;");
 }
 // generate query
 //$query_where=$GLOBALS['navigation']->filtersQuery(1);
 $query_where="1";
 if(strlen($g_search)==0){
  // acquire folders
  if($current_folder->id){$query_where="idFolder='".$current_folder->id."'";}else{$query_where="idFolder IS NULL";}
  $folders=$GLOBALS['db']->query("SELECT * FROM uploads_folders WHERE ".$query_where);
  //while($contact=api_contacts_contact($GLOBALS['db']->fetchNextObject($contacts),TRUE)){
  while($folder=api_uploads_folder($GLOBALS['db']->fetchNextObject($folders),TRUE)){
   // build modal window
   $uploads_status_modals_array[]=api_uploads_folderStatusModal($folder);
   // make size
   if($folder->size==0){$size_td=NULL;}
    elseif($folder->size>1048576){$size_td=number_format(($folder->size/1048576),2,",",".")." MB";}
    elseif($folder->size>134217728){$size_td=number_format(($folder->size/134217728),2,",",".")." GB";}
    else{$size_td=number_format(($folder->size/1024),2,",",".")." KB";}
   // build group row
   $table->addRow();
   // build table field
   $table->addField(api_link("uploads_list.php?idFolder=".$folder->id,api_icon("icon-folder-open")),"nowarp");
   $table->addField($folder->name,"nowarp");
   $table->addField("&nbsp;");
   $table->addField($size_td,"nowarp text-right");
   $table->addField(api_timestampFormat($folder->updDate,api_text("datetime")),"nowarp text-right");
   $table->addField(end($uploads_status_modals_array)->link(api_icon("icon-info-sign")),"nowarp");
  }
 }
 // where
 $query_where.=" AND (uploads_uploads.del='0' OR ".$GLOBALS['navigation']->filtersParameterQuery("del","0","uploads_uploads.del").")";
 $query_where.=" AND ".$GLOBALS['navigation']->filtersParameterQuery("public","1","uploads_uploads.public");
 // search
 if(strlen($g_search)){
  $query_where.=" AND (uploads_uploads.name LIKE '%".api_cleanString($g_search,"/[^A-Za-z0-9-. ]/","FALSENULL")."%'"; //  <<<--- copiare da submit
  $query_where.=" OR uploads_uploads.label LIKE '%".api_cleanString($g_search,"/[^A-Za-zÀ-ÿ0-9-.' ]/","FALSENULL")."%'"; //  <<<--- copiare da submit
  $query_where.=" OR uploads_uploads.description LIKE '%".api_cleanString($g_search,"/[^A-Za-zÀ-ÿ0-9-.,' ]/","FALSENULL")."%'"; //  <<<--- copiare da submit
  $query_where.=" OR uploads_uploads.tags LIKE '%".api_cleanString($g_search,"/[^A-Za-z0-9-_ ]/","FALSENULL")."%'"; //  <<<--- copiare da submit
  $query_where.=" OR uploads_uploads.txtContent LIKE '%".api_cleanString($g_search,"/[^A-Za-zÀ-ÿ0-9-.,' ]/","FALSENULL")."%' )"; //  <<<--- copiare da submit
 }
 // order
 $query_order=api_queryOrder("uploads_uploads.label ASC");
 // fields
 $query_fields="id,idFolder,name,type,size,hash,label,description,tags,txtContent,addDate,addIdAccount,updDate,updIdAccount,del";
 // execute query
 $files=$GLOBALS['db']->query("SELECT ".$query_fields." FROM uploads_uploads WHERE ".$query_where.$query_order);
 // acquire files
 while($file=api_uploads_file($GLOBALS['db']->fetchNextObject($files),TRUE)){
  // make icon
  if($file->del){$icon_td="icon-trash";}else{$icon_td="icon-info-sign";}
  // make size
  if($file->size>1048576){$size_td=number_format(($file->size/1048576),2,",",".")." MB";}
   elseif($file->size>134217728){$size_td=number_format(($file->size/134217728),2,",",".")." GB";}
   else{$size_td=number_format(($file->size/1024),2,",",".")." KB";}
  // build modal window
  $uploads_status_modals_array[]=api_uploads_fileStatusModal($file);
  // build group table row
  $table->addRow();
  // build table fields
  $table->addField(api_link("uploads_files_view.php?idFile=".$file->id."&idFolder=".$file->idFolder,api_icon("icon-search")),"nowarp");
  $table->addField($file->label,"nowarp");
  $table->addField($file->description);
  $table->addField($size_td,"nowarp text-right");
  $table->addField(api_timestampFormat($file->updDate,api_text("datetime")),"nowarp text-right");
  $table->addField(end($uploads_status_modals_array)->link(api_icon($icon_td)),"nowarp");
 }
 // show folder path
 if(!$current_folder->path_html){$current_folder->path_html="/".api_link("../uploads/uploads/uploads_list.php?idFolder=","Uploads");}
 echo "<h4>".$current_folder->path_html."</h4>";
 // renderize table
 $table->render();
 // renderize status modal windows
 foreach($uploads_status_modals_array as $status_modal){
  $status_modal->render();
 }
}
?>