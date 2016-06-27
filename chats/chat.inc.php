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
 <script src="<?php echo $GLOBALS['dir']."core/jquery/jquery-1.11.1.min.js";?>" type="text/javascript"></script>
</head>
<body style="padding:10px 10px 0 10px;">
<?php
 echo "<div id='frame_chat' style='height:420px;overflow:auto;padding-right:5px;'>Loading..</div>\n";
 echo "<iframe src='../chats/chat_edit.inc.php?account=".$g_idAccount."' style='height:40px' width='99.6%' frameborder='0' scrolling='no'></iframe>\n";
?>
<script type="text/javascript">
 $(document).ready(function(){
  setInterval(function(){
   $("#frame_chat").load("../chats/chat_view.inc.php?account=<?php echo $g_idAccount;?>",function(){
    $('#frame_chat').scrollTop($('#frame_chat')[0].scrollHeight);
   });
  },2000);
 });
</script>
</body>
</html>