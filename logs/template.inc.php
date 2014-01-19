<?php
/* -------------------------------------------------------------------------- *\
|* -[ Logs - Template ]------------------------------------------------------ *|
\* -------------------------------------------------------------------------- */
include("../core/api.inc.php");
$html->header("Logs");
// acquire variables
$g_interval=$_GET['i'];
if(!isset($g_interval)){$g_interval=7;}
$g_typology=$_GET['t'];
if(!isset($g_typology)){$g_typology=0;}
$g_module=$_GET['m'];
if(!isset($g_module)){$g_module=NULL;}
?>

<div class="row-fluid">

 <!-- Navigation -->
 <ul class="nav nav-tabs">
  
  <li class="dropdown">
   <a class="dropdown-toggle" data-toggle="dropdown" href="#">
    <?php
     switch($g_typology){
      case -1:echo "Avvertimenti ed errori";break;
      case 1:echo "Notifiche";break;
      case 2:echo "Avvertimenti";break;
      case 3:echo "Errori";break;
      default:echo "Tutti gli eventi";
     }    
    ?>
    <b class="caret"></b>
   </a>
   <ul class="dropdown-menu">
    <li><a href="<?php echo "logs_list.php?i=".$g_interval."&t=0"; ?>">Tutti gli eventi</a></li>
    <li><a href="<?php echo "logs_list.php?i=".$g_interval."&t=-1"; ?>">Avvertimenti ed errori</a></li>
    <li><a href="<?php echo "logs_list.php?i=".$g_interval."&t=1"; ?>">Solo notifiche</a></li>
    <li><a href="<?php echo "logs_list.php?i=".$g_interval."&t=2"; ?>">Solo avvertimenti</a></li>
    <li><a href="<?php echo "logs_list.php?i=".$g_interval."&t=3"; ?>">Solo errori</a></li>
   </ul>
  </li>
  
  <li class="dropdown">
   <a class="dropdown-toggle" data-toggle="dropdown" href="#">
    <?php echo "Ultimi ".$g_interval." giorni"; ?>
    <b class="caret"></b>
   </a>
   <ul class="dropdown-menu">
    <li><a href="<?php echo "logs_list.php?i=3&t=".$g_typology; ?>">Ultimi 3 giorni</a></li>
    <li><a href="<?php echo "logs_list.php?i=7&t=".$g_typology; ?>">Ultimi 7 giorni</a></li>
    <li><a href="<?php echo "logs_list.php?i=30&t=".$g_typology; ?>">Ultimi 30 giorni</a></li>
    <li><a href="<?php echo "logs_list.php?i=90&t=".$g_typology; ?>">Ultimi 90 giorni</a></li>
    <li><a href="<?php echo "logs_list.php?i=365&t=".$g_typology; ?>">Ultimi 365 giorni</a></li>
   </ul>
  </li>
  
  <li class="dropdown">
   <a class="dropdown-toggle" data-toggle="dropdown" href="#">
    <?php
     if($g_module<>NULL){echo "Modulo ".strtoupper($g_module);}
      else{echo "Tutti i moduli";}
    ?>
    <b class="caret"></b>
   </a>
   <ul class="dropdown-menu">
    <?php
     $modules_array=array();
     $modules=$GLOBALS['db']->query("SELECT DISTINCT module FROM logs_logs WHERE timestamp BETWEEN CURDATE()- INTERVAL ".($g_interval-1)." DAY AND NOW() ORDER BY module ASC");
     echo "<li><a href='logs_list.php?i=".$g_interval."&t=".$g_typology."'>Tutti i moduli</a></li>";
     while($module=$GLOBALS['db']->fetchNextObject($modules)){$modules_array[]=$module;}
     foreach($modules_array as $module){
      echo "<li><a href='logs_list.php?i=".$g_interval."&t=".$g_typology."&m=".$module->module."'>Modulo ".strtoupper($module->module)."</a></li>";
     }
    ?>
   </ul>
  </li>
  
 </ul>

<?php if($checkPermission==NULL){content();}else{if(api_checkPermission("logs",$checkPermission,TRUE)){content();}} ?>

</div><!-- /row -->

<?php $html->footer(); ?>