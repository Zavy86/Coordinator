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
   echo "<li";if(api_baseName()=="modules_edit.php"){echo " class='active'";}
   if(!api_checkPermission("settings","modules_edit")){echo " class='disabled'><a href='#'";}
   else{echo "><a href='modules_edit.php'";}
   echo ">Moduli</a></li>";
  ?>

  <?php
   echo "<li";if(api_baseName()=="permissions_edit.php"){echo " class='active'";}
   if(!api_checkPermission("settings","permissions_edit")){echo " class='disabled'><a href='#'";}
   else{echo "><a href='permissions_edit.php'";}
   echo ">Permessi</a></li>";
  ?>

  <?php
   echo "<li";if(api_baseName()=="menus_edit.php"){echo " class='active'";}
   if(!api_checkPermission("settings","menus_edit")){echo " class='disabled'><a href='#'";}
   else{echo "><a href='menus_edit.php'";}
   echo ">Menu</a></li>";
  ?>

 </ul>

<?php if($checkPermission==NULL){content();}else{if(api_checkPermission("settings",$checkPermission,TRUE)){content();}} ?>

</div><!-- /row -->

<?php $html->footer(); ?>