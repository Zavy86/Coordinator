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
 // include configuration file
 if(file_exists("config.inc.php")){
  include("config.inc.php");
 }else{
  echo api_text("server-configNotFound",$GLOBALS['dir']);
 }
 // loop servers
 if(count($stats_servers)>0){
  foreach($stats_servers as $index=>$server){
   // show charts
   echo "<div class='row-fluid'>\n";
   echo "<div class='span7'>\n";
   echo "<h4>".$server." - CPU</h4></center>\n";
   echo "<img id='imgCPU_".$index."' src='".$server."/cpu.inc.php'>\n";
   echo "</div><!-- /span7 -->\n";
   echo "<div class='span5'>\n";
   echo "<h4>RAM</h4>\n";
   echo "<img id='imgRAM_".$index."' src='".$server."/ram.inc.php'>\n";
   echo "</div><!-- /span5 -->\n";
   echo "</div><!-- /row-fluid -->\n";
   echo "<br>";
  }
 }
?>
<script type="text/javascript">
 $(document).ready(function(){
  // refresh charts every 2 sec
  setInterval(function(){
   <?php
    // loop servers
    if(count($stats_servers)>0){
     foreach($stats_servers as $index=>$server){
      echo "$('#imgCPU_".$index."').attr('src','".$server."/cpu.inc.php');\n";
      echo "$('#imgRAM_".$index."').attr('src','".$server."/ram.inc.php');\n";
     }
    }
   ?>
  },2000);
 });
</script>
<?php } ?>