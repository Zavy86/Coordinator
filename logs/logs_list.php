<?php
/* -------------------------------------------------------------------------- *\
|* -[ Logs - Logs List ]----------------------------------------------------- *|
\* -------------------------------------------------------------------------- */
$checkPermission="logs_list";
include("template.inc.php");
function content(){
?>
<table class="table table-striped table-hover table-condensed">
 <thead>
  <tr>
   <th width="16">&nbsp;</th>
   <th class='nowarp'>Data</th>
   <th>Modulo</th>
   <th width="100%">Log (Seleziona per maggiori dettagli)</th>
   <th class="nowarp">Account</th>
  </tr>
 </thead>
 <tbody>
<?php
// acquire variables
$g_interval=$_GET['i'];
if(!isset($g_interval)){$g_interval=7;}
$g_typology=$_GET['t'];
if(!isset($g_typology)){$g_typology=0;}
$g_module=$_GET['m'];
if(!isset($g_module)){$g_module=NULL;}
$g_page=$_GET['p'];
if(!$g_page){$g_page=1;}
$g_limit=$_GET['l'];
if(!isset($g_limit)){$g_limit=20;}
// generate query
$query_where="timestamp BETWEEN CURDATE()- INTERVAL ".($g_interval-1)." DAY AND NOW()";
if($g_typology>0&&$g_typology<4){$query_where.=" AND typology='".$g_typology."'";}
if($g_typology==-1){$query_where.=" AND (typology='2' OR typology='3')";}
if($g_module<>NULL){$query_where.=" AND module='".$g_module."'";}
// pagination
if($g_limit>0){
 $recordsLimit=$g_limit;
 $recordsCount=$GLOBALS['db']->countOf("logs_logs",$query_where);
 $query_start=($g_page-1)*$recordsLimit;
 $query_limit=" LIMIT ".$query_start.",".$recordsLimit;
}
// query
$logs_new_id=array();
$logs=$GLOBALS['db']->query("SELECT * FROM logs_logs WHERE ".$query_where." ORDER BY timestamp DESC".$query_limit);
while($log=$GLOBALS['db']->fetchNextObject($logs)){
 if($log->new){
  $logs_new_id[]=$log->id;
  // set new status to false
  $GLOBALS['db']->execute("UPDATE logs_logs SET new='0' WHERE id='".$log->id."'");
 }
 // show record
 switch($log->typology){
  case 1:echo "<tr><td><i class='icon-info-sign'></i></td>\n";break;
  case 2:echo "<tr";if($g_typology<1){echo " class='warning'";}echo "><td><i class='icon-warning-sign'></i></td>\n";break;
  case 3:echo "<tr";if($g_typology<1){echo " class='error'";}echo "><td><i class='icon-remove-circle'></i></td>\n";break;
 }
 echo "<td class='nowarp'>".api_timestampFormat($log->timestamp,TRUE)."</td>\n";
 echo "<td>".strtoupper($log->module)."</td>\n";
 if(($strpos=strpos($log->log,"\n"))==0){$strpos=50;}
 $log_subject=str_ireplace("<br>"," ",substr($log->log,0,$strpos));
 if($log->new){$log_subject="<span class='unread'>".$log_subject."</span>";}
 echo "<td><a href='#modal".$log->id."' data-toggle='modal' id='read".$log->id."'>".$log_subject."</a></td>\n";
 echo "<td class='nowarp'>".api_accountName($log->idAccount)."</td>\n";
 echo "</tr>\n";
 // modal label
 echo "<div id='modal".$log->id."' class='modal hide fade' role='dialog' aria-hidden='true'>\n";
 echo "<div class='modal-header'>\n";
 echo "<button type='button' class='close' data-dismiss='modal' aria-hidden='true'>&times;</button>\n";
 echo "<h4>".$log_subject."</h4>";
 echo "</div>\n";
 echo "<div class='modal-body'>\n";
 echo "<dl>\n";
 echo "<dt>Data</dt><dd>".api_timestampFormat($log->timestamp,TRUE,TRUE)."</dd><br>\n";
 //echo "<dt>Modulo</dt><dd>".strtoupper($log->module)."</dd><br>\n";
 if($log->idAccount>0){echo "<dt>Account:</dt><dd>".api_accountName($log->idAccount)."</dd><br>\n";}
 echo "<dt>Indirizzo IP</dt><dd>".$log->ip."</dd><br>\n"; // gethostbyaddr
 if(strlen($log->link)>0){echo "<dt>Link:</dt><dd><a href='".$GLOBALS['dir'].$log->link."' target='_blank'>".$log->link."</a></dd><br>\n";}
 echo "<dt>Log</dt><dd>".nl2br($log->log)."</dd>\n";
 echo "</dl>\n";
 echo "</div>\n</div>\n";
}
?>
 </tbody>
</table>
<?php
// show the pagination div
api_pagination($recordsCount,$recordsLimit,$g_page,"logs_list.php?i=".$g_interval."&t=".$g_typology."&m=".$g_module,"pagination pagination-small pagination-right");
// set the javascript to set read new items
?>
<script language="javascript">
<?php
foreach($logs_new_id as $log_new_id){
?>
$( "#read<?php echo $log_new_id; ?>" ).click(function(){
 $(this).find("span").removeClass('unread')
});
<?php
}
?>
</script>
<?php
}
?>