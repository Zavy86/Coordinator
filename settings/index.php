<?php
/* -[ Redirect ]------------------------------------------------------------- */
$alert=$_GET['alert'];
if(isset($alert)){$alert="?alert=".$alert;}
$act=$_GET['act'];
if(isset($act)){$act="&act=".$act;}
header("location: settings_edit.php".$alert.$act);
?>