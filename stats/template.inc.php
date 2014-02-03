<?php
/* -------------------------------------------------------------------------- *\
|* -[ Stats - Template ]----------------------------------------------------- *|
\* -------------------------------------------------------------------------- */
include("../core/api.inc.php");
$html->header("Statistiche");
?>

<div class="row-fluid">

 <!-- Navigation -->
 <ul class="nav nav-tabs">

  <?php
   echo "<li";if(api_baseName()=="stats_server.php"){echo " class='active'";}
   if(!api_checkPermission("stats","stats_server")){echo " class='disabled'><a href='#'";}
   else{echo "><a href='stats_server.php'";}
   echo ">Server</a></li>";
  ?>

 </ul>

<?php if($checkPermission==NULL){content();}else{if(api_checkPermission("stats",$checkPermission,TRUE)){content();}} ?>

</div><!-- /row -->

<?php $html->footer(); ?>