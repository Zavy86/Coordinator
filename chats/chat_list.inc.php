<?php
 //require_once("../core/api.inc.php");
 // initializations
 $path="../chats/chats/";
 $ul=array();
?>
<li class="nav-header">Messaggistica</li>
<li><a href="#modalNew" data-toggle="modal">Nuovo messaggio</a></li>
<?php
 // cycle all chats files
 if($handle=opendir($path)){
  while(FALSE!==($entry=readdir($handle))){
   // cycle chats files whith account id
   if(strpos($entry,str_pad($_SESSION['account']->id,11,"0",STR_PAD_LEFT))!==FALSE){
    $chats_unread=0;
    // load chat file
    $chat_xml=simplexml_load_file($path.$entry);
    foreach($chat_xml->message as $message){
     // count unread messages
     if(intval($message->account)<>$_SESSION['account']->id && intval($message->status)==1){$chats_unread++;}
    }
    // generate chat account
    $id=explode("-",substr($entry,0,-4));
    if(ltrim($id[0],"0")<>$_SESSION['account']->id){
     $chat_account=ltrim($id[0],"0");
    }else{
     $chat_account=ltrim($id[1],"0");
    }
    // add li to ul
    $li="<li><a rel=\"shadowbox;width=360;height=480;\" href='../chats/chat.inc.php?account=".$chat_account."' title='".api_accountName($chat_account)."'>";
    if($chats_unread>0){$li.="<b> ".api_accountName($chat_account)." (".$chats_unread.")</b>";}else{$li.=api_accountName($chat_account);}
    $li.="</a></li>\n";
    $ul[]=$li;
   }
  }
  closedir($handle);
 }
 // show ul
 if(count($ul)>0){
  echo "<li class='divider'></li>\n";
  foreach($ul as $li){
   echo $li;
  }
 }
?>