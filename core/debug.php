<?php if($_GET['refresh']){header("refresh:1;");} ?>
<style>
 body{margin:0;padding:2px;font-family:sans-serif}
 table{border-collapse:collapse;width:100%;}
 table tr th{font-size:7pt;font-weight:bold;text-align:left;padding:2px}
 table tr td{font-size:10pt;font-weight:normal;text-align:left;padding:3px;border:1px solid #ccc}
 .info{background-color:#D9EDF7;}
 .warning{background-color:#FCF8E3;}
 .error{background-color:#F2DEDE;}
 .nowarp{white-space:nowrap;}
</style>
<?php
include_once("api.inc.php");
// definitions
if(!is_array($_SESSION['debug'])){$_SESSION['debug']=array();}
// refresh
if($_GET['refresh']){$th_refresh=api_link("?refresh=0","DISABLE AUTO REFRESH");}
else{$th_refresh=api_link("?refresh=1","ENABLE AUTO REFRESH");}
// build table
$table=new str_table();
// build headers
$table->addHeader("TIMESTAMP","nowarp");
$table->addHeader("MODULE","nowarp");
$table->addHeader("EVENT - ".$th_refresh,NULL,"100%");
// build fields
foreach(array_reverse($_SESSION['debug']) as $event){
 switch($event->typology){
  case API_DEBUG_NOTICE:$class_tr=NULL;break;
  case API_DEBUG_WARNING:$class_tr="warning";break;
  case API_DEBUG_ERROR:$class_tr="error";break;
 }
 $table->addRow($class_tr);
 $table->addField(substr($event->timestamp,11),"nowarp");
 $table->addField(strtoupper($event->module),"nowarp");
 $table->addField($event->event);
}
// renderize table
$table->render();
?>