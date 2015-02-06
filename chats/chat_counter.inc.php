<?php
 // requires
 require_once("../core/api.inc.php");
 // initializations
 $chats_unread=0;
 $path="../chats/chats/";
 // cycle all chats files
 if($handle=opendir($path)){
  while(FALSE!==($entry=readdir($handle))){
   // cycle chats files whith account id
   if(strpos($entry,str_pad($_SESSION['account']->id,11,"0",STR_PAD_LEFT))!==FALSE){
    // load chat file
    $chat_xml=simplexml_load_file($path.$entry);
    foreach($chat_xml->message as $message){
     // count unread messages
     if(intval($message->account)<>$_SESSION['account']->id && intval($message->status)==1){$chats_unread++;}
    }
   }
  }
  closedir($handle);
 }
 if($chats_unread>0){echo "<b>".$chats_unread."</b> <i class='icon-envelope'></i>";}
  else{echo "<i class='icon-inbox'></i>";}
?>