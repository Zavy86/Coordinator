<?php
/* ------------------------------------------------------------------------- *\
|* -[ Settings - Template ]------------------------------------------------- *|
\* ------------------------------------------------------------------------- */
include("../core/api.inc.php");
$html->header("Settings");
?>

<div class="row-fluid">

 <!-- Navigation -->
 <ul class="nav nav-tabs">

  <?php 
   echo "<li";if(api_baseName()=="settings_edit.php"){echo " class='active'";}
   if(!api_checkPermission("settings","settings_edit")){echo " class='disabled'><a href='#'";}
   else{echo "><a href='settings_edit.php'";}
   echo ">Impostazioni</a></li>";
  ?>
  
  <?php 
   echo "<li";if(api_baseName()=="permissions_edit.php"){echo " class='active'";}
   if(!api_checkPermission("settings","permissions_edit")){echo " class='disabled'><a href='#'";}
   else{echo "><a href='permissions_edit.php'";}
   echo ">Permessi</a></li>";
  ?>
  
 </ul>

<?php if($checkPermission==NULL){content();}else{if(api_checkPermission("settings",$checkPermission,TRUE)){content();}} ?>

</div><!-- /row -->

<?php $html->footer(); ?>