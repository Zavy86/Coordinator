<?php
/* ------------------------------------------------------------------------- *\
|* -[ Dashboard - Template ]------------------------------------------------ *|
\* ------------------------------------------------------------------------- */
include("../core/api.inc.php");
$html->header("Bacheca","dashboard");
// load module locale file
api_loadLocaleFile();
// acquire variables
$status=$_GET['s'];
if(!$status){$status=1;}
?>

 <!-- Navigation -->
 <ul class="nav nav-tabs">

  <?php
   echo "<li";if(api_baseName()=="notifications_list.php" && $status==1){echo " class='active'";}
   echo "><a href='notifications_list.php?s=1'";
   echo ">".api_text("notifications")."</a></li>";
  ?>

  <?php
   echo "<li";if(api_baseName()=="notifications_list.php" && $status==2){echo " class='active'";}
   echo "><a href='notifications_list.php?s=2'";
   echo ">".api_text("archived-notifications")."</a></li>";
  ?>

  <?php
   if(api_baseName()=="notifications_send.php"){
    echo "<li class='active'><a href='#'>".api_text("send-notifications")."</a></li>";
   }
  ?>

 </ul>

<?php if($checkPermission==NULL){content();}else{if(api_checkPermission("dashboard",$checkPermission,TRUE)){content();}} ?>

<?php $html->footer(); ?>