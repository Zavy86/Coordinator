<?php
// include configuration file
include("config.inc.php");
// check for initial module
if(!strlen($initial_module)){$initial_module="index";}
// acquire variables
$alert=$_GET['alert'];
$act=$_GET['act'];
// check variables
if(isset($alert)){$alert="?alert=".$alert;}
if(isset($act)){$act="&act=".$act;}
// redirect
header("location: ".$initial_module."/index.php".$alert.$act);
?>