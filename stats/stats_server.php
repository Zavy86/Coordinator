<?php
/* -------------------------------------------------------------------------- *\
|* -[ Stats - Server ]------------------------------------------------------- *|
\* -------------------------------------------------------------------------- */
$checkPermission="stats_server";
include("template.inc.php");
function content(){
 // reset values
 if($_GET['reset']==TRUE){
  unset($_SESSION['stats_cpu']);
  unset($_SESSION['stats_cpu_timestamp']);
 }
?>
<div id="res"></div>
<div class="row-fluid">
 <div class="span7">
 <h4><?php echo strtoupper($_SERVER['SERVER_NAME']);?> - CPU</h4>
 <img id="imgCPU" src="cpu.inc.php">
</div><!-- /span7 -->
<div class="span5">
 <h4><?php echo strtoupper($_SERVER['SERVER_NAME']);?> - RAM</h4>
 <img id="imgRAM" src="ram.inc.php">
</div><!-- /span5 -->
</div><!-- /row-fluid -->

<br>

<div class="row-fluid">
 <div class="span7">
 <h4>SIS-MYSQL - CPU</h4>
 <img id="imgCPU-sql" src="http://sis-mysql/scripts/stats/cpu.inc.php">
</div><!-- /span7 -->
<div class="span5">
 <h4>SIS-MYSQL - RAM</h4>
 <img id="imgRAM-sql" src="http://sis-mysql/scripts/stats/ram.inc.php">
</div><!-- /span5 -->
</div><!-- /row-fluid -->

<script type="text/javascript">
 $(document).ready(function(){
  // refresh charts every 2 sec
  setInterval(function(){
   $("#imgCPU" ).attr("src","cpu.inc.php");
   $("#imgRAM" ).attr("src","ram.inc.php");
   $("#imgCPU-sql" ).attr("src","http://sis-mysql/scripts/stats/cpu.inc.php");
   $("#imgRAM-sql" ).attr("src","http://sis-mysql/scripts/stats/ram.inc.php");
  },2000);
 });
</script>
<?php } ?>