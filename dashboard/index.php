<?php
/* -[ Redirect ]------------------------------------------------------------- */
include("../core/api.inc.php");
$alert=$_GET['alert'];
if(isset($alert)){$alert="&alert=".$alert;}
$act=$_GET['act'];
if(isset($act)){$act="&act=".$act;}
header("location: dashboard.php".$alert.$act);
?>