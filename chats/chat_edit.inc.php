<?php
 include("../core/api.inc.php");
 // acquire variables
 $g_idAccountTo=$_GET['account'];
?>
<!DOCTYPE html>
<html lang="it">
<head>
 <meta charset="utf-8">
 <link href="<?php echo $GLOBALS['dir']."core/bootstrap/css/bootstrap.min.css";?>" rel="stylesheet">
 <link href="<?php echo $GLOBALS['dir']."core/template.css";?>" rel="stylesheet">
</head>
<body>
 <form action='submit.php?act=chat_send&account=<?php echo $g_idAccountTo;?>' method='post'>
  <input type='text' name='message' style='width:325px;' placeholder='Scrivi un messaggio e premi invio' autofocus>
 </form>
</body>
</html>