<?php
/* -------------------------------------------------------------------------- *\
|* -[ Chats - Submit ]------------------------------------------------------- *|
\* -------------------------------------------------------------------------- */
include('../core/api.inc.php'); // Include the core API function
$act=$_GET['act'];
switch($act){
 // chats
 case "chat_send":chat_send();break;
 // default
 default:header("location: index.php");
}


/* -[ Chat Send ]------------------------------------------------------------ */
function chat_send(){
 // acquire variables
 $g_idAccountTo=$_GET['account'];
 $p_message=addslashes($_POST['message']);
 if($g_idAccountTo>0 && strlen($p_message)>0){
  // generate file path
  if($_SESSION['account']->id<$g_idAccountTo){
   $file_path="chats/".str_pad($_SESSION['account']->id,11,"0",STR_PAD_LEFT)."-".str_pad($g_idAccountTo,11,"0",STR_PAD_LEFT).".xml";
  }else{
   $file_path="chats/".str_pad($g_idAccountTo,11,"0",STR_PAD_LEFT)."-".str_pad($_SESSION['account']->id,11,"0",STR_PAD_LEFT).".xml";
  }
  // load file if exist
  if(file_exists($file_path)){
   $chat_xml=simplexml_load_file($file_path);
  }else{
   $xmlstr="<?xml version=\"1.0\" encoding=\"utf-8\"?><chats></chats>";
   $chat_xml=new SimpleXMLElement($xmlstr);
  }
  // add new message
  $child=$chat_xml->addChild("message");
  $child->addChild("account",$_SESSION['account']->id);
  $child->addChild("timestamp",date("Y-m-d H:i:s"));
  $child->addChild("status","1");
  $child->addChild("text",$p_message);
  // format xml
  $dom=new DOMDocument('1.0');
  $dom->preserveWhiteSpace=false;
  $dom->formatOutput=true;
  $dom->loadXML($chat_xml->asXML());
  // update chat file
  $handle=fopen("$file_path","w");
  fwrite($handle,$dom->saveXML());
  fclose($handle);
 }
 // redirect
 header("location: chat_edit.inc.php?account=".$g_idAccountTo);
}