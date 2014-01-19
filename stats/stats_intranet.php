<?php
/* ------------------------------------------------------------------------- *\
|* -[ Settings - Settings Edit ]-------------------------------------------- *|
\* ------------------------------------------------------------------------- */
$checkPermission="stats_intranet";
include("template.inc.php");
function content(){

 $piwik=api_getOption("piwik_analytics");
 if($piwik<>""){
  $piwik_server=substr($piwik,0,strpos($piwik,":"));
  $piwik_siteid=substr($piwik,strpos($piwik,":",0)+1);
  $map_url="http://".$piwik_server."/index.php?module=API&method=VisitsSummary.get&idSite=".$piwik_siteid."&period=range&date=last7&format=xml&token_auth=37310aa5f6d8ad5b104e76387f0bc59e";
  $response_xml_data=file_get_contents($map_url);
  if($response_xml_data){
   $data = simplexml_load_string($response_xml_data);
   echo "<div id='piwik_stats'>Statistiche settimanali: Visite: ".$data->nb_visits." - Pagine visualizzate: ".$data->nb_actions." - Media pagine visualizzate per visita: ".$data->nb_actions_per_visit."</div>\n";
  }
 }

}