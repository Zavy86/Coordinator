<?php
/* ------------------------------------------------------------------------- *\
|* -[ Dashboard - Template ]------------------------------------------------ *|
\* ------------------------------------------------------------------------- */
include("../core/api.inc.php");
$html->header("Bacheca","dashboard");
// acquire variables
$status=$_GET['s'];
if(!$status){$status=1;}
?>

<div class="row-fluid">

 <!-- Navigation -->
 <ul class="nav nav-tabs">

  <?php
   echo "<li";if(api_baseName()=="notifications_list.php" && $status==1){echo " class='active'";}
   echo "><a href='notifications_list.php?s=1'";
   echo ">Notifiche</a></li>";
  ?>

  <?php
   echo "<li";if(api_baseName()=="notifications_list.php" && $status==2){echo " class='active'";}
   echo "><a href='notifications_list.php?s=2'";
   echo ">Archivio notifiche</a></li>";
  ?>

  <?php /*<li class="pull-right"><img src="<?php echo $GLOBALS['dir']; ?>/core/images/logos/logo.png" style="height:34px"></li>*/ ?>

  <?php $g_search=$_GET['s']; ?>

  <?php if(api_baseName()=="addressbook_list.php"){ ?>

  <form action="addressbook_list.php" method="get">
   <li class="pull-right search">
    <div class="input-append">
     <input type="text" name="s" class="input-large" placeholder="Search" value="<?php echo $g_search; ?>">
     <?php if($g_search<>NULL){ echo "<a class='btn' href='addressbook_list.php'><i class='icon-remove-sign'></i></a>"; } ?>
     <button type="submit" class="btn"><i class="icon-search"></i></button>
    </div>
   </li>
  </form>

  <?php } ?>

 </ul>

<?php if($checkPermission==NULL){content();}else{if(api_checkPermission("dashboard",$checkPermission,TRUE)){content();}} ?>

</div><!-- /row -->

<?php $html->footer(); ?>