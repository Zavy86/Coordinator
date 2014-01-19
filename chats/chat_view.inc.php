<?php
 include("../core/api.inc.php");
 // acquire variables
 $g_idAccount=$_GET['account'];
?>
<!DOCTYPE html>
<html lang="it">
<head>
 <meta charset="utf-8">
 <link href="<?php echo $GLOBALS['dir']."core/bootstrap/css/bootstrap.min.css";?>" rel="stylesheet">
 <link href="<?php echo $GLOBALS['dir']."core/template.css";?>" rel="stylesheet">
</head>
<body>
<div>
<?php
 // generate file path
 if($_SESSION['account']->id<$g_idAccount){
  $file_path="chats/".str_pad($_SESSION['account']->id,11,"0",STR_PAD_LEFT)."-".str_pad($g_idAccount,11,"0",STR_PAD_LEFT).".xml";
 }else{
  $file_path="chats/".str_pad($g_idAccount,11,"0",STR_PAD_LEFT)."-".str_pad($_SESSION['account']->id,11,"0",STR_PAD_LEFT).".xml";
 }
 // die if not exist
 if(!file_exists($file_path)){die();}
 // initialization
 $previous_from=NULL;
 $previous_timestamp=NULL;
 $last_id=NULL;
 $last_read=FALSE;
 // load chat file
 $chat_xml=simplexml_load_file($file_path);
 //stampa nick e password separati da un -
 foreach($chat_xml->message as $message){
  $last_id=intval($message->account);
  // change class
  if(intval($message->account)==$g_idAccount){
   echo "<div class='chat chat-to'>";
   // sign as read
   $message->status="2";
  }else{
   echo "<div class='chat chat-from'>";
   if($message->status==2){$last_read=TRUE;}else{$last_read=FALSE;}
  }// if previous from change
  if(intval($previous_from)<>intval($message->account)){
   $previous_from=$message->account;
   $previous_timestamp=NULL;
  }
  // if time between two message up to 5 min
  if(strtotime($message->timestamp)-strtotime($previous_timestamp)>300){
   echo "<div class='metadata'>".api_timestampFormat($message->timestamp,TRUE)."</div>";
  }
  $previous_timestamp=$message->timestamp;
  // show message
  echo "<div class='message'>".nl2br(stripslashes($message->text))."</div>\n";
 }
 if($last_id==$_SESSION['account']->id){
  if($last_read){
   echo "<div class='status'>Letto</div>\n";
  }else{
   echo "<div class='status'>Inviato</div>\n";
  }
 }
 // update chat file
 $handle=fopen("$file_path","w");
 fwrite($handle,$chat_xml->asXML());
 fclose($handle);
?>
</div>
</body>
</html>