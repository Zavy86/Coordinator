<?php
/* -------------------------------------------------------------------------- *\
|* -[ Chats - Cron daily ]--------------------------------------------------- *|
\* -------------------------------------------------------------------------- */
require_once("../core/api.inc.php");
if(api_basePath()<>$GLOBALS['dir']."cron"){api_die();}


/* -[ Remove older chats ]--------------------------------------------------- */

// initialization
$wlog=FALSE;
$path="../chats/chats/";
// log of the operation
$log_level=1;
$log="CRON - CHATS - REMOVE OLDER MESSAGES\n";
// cycle all chats files
if($handle=opendir($path)){
 while(FALSE!==($entry=readdir($handle))){
  if(substr($entry,-4)==".xml"){
   // initialization
   $count=0;
   // load chat file
   $chat_xml=simplexml_load_file($path.$entry);
   // cycle al messages
   for($i=0;$i<count($chat_xml->message);$i++){
    // && intval($chat_xml->message[$i]->status)==2
    if(api_timestampDifference($chat_xml->message[$i]->timestamp,date("Y-m-d H:i:s"),"D")>10){
     $count++;
     $wlog=TRUE;
     // remove message
     unset($chat_xml->message[$i]);
     // decrement index
     $i--;
    }
   }
   // if removed all messages
   if(!count($chat_xml->message)>0){
    // delete chat file
    if(@unlink($path.$entry)){
     $log.="Removed all message from ".$entry."\n";
    }else{
     $log_level=2;
     $log.="Error while deleting file ".$entry."\n";
    }
   }else{
    if($count){
     // format xml
     $dom=new DOMDocument('1.0');
     $dom->preserveWhiteSpace=false;
     $dom->formatOutput=true;
     $dom->loadXML($chat_xml->asXML());
     // update chat file
     $file_handle=@fopen($path.$entry,"w");
     if($file_handle){
      $log.="Removed ".$count." messages from ".$entry."\n";
      fwrite($file_handle,$dom->saveXML());
      fclose($file_handle);
     }else{
      $log_level=2;
      $log.="Error while removing ".$count." message from ".$entry."\n";
     }
    }
   }
  }
 }
 closedir($handle);
}
// if delete generate log
if($wlog){api_log($log_level,"chats","chatsCron",$log);}else{$log.="Nothing to delete..\n";}
// show footer
if($g_submit<>"cron"){
 echo nl2br($log)."<br>";
}else{
 echo $log."\n";
}
?>