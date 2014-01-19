<?php
/* -------------------------------------------------------------------------- *\
|* -[ Stats - CPU Chart ]---------------------------------------------------- *|
\* -------------------------------------------------------------------------- */
session_start();
// pChart inclusion
require_once("../core/pchart/pData.class");
require_once("../core/pchart/pChart.class");
$out=array();
$matches=array();
exec('free -mo',$out);
preg_match_all('/\s+([0-9]+)/',$out[1],$matches);
list($ram_total,$ram_used,$ram_free,$ram_shared,$ram_buffers,$ram_cached)=$matches[1];
if($ram_free===NULL){$ram_free=1024;}
// chart dataset definition
$DataSet=new pData;
$DataSet->AddPoint(array($ram_used-$ram_cached,$ram_cached,$ram_free),"Serie1");
$DataSet->AddPoint(array("Usata","Cache","Libera"),"Serie2");
$DataSet->AddAllSeries();
$DataSet->SetAbsciseLabelSerie("Serie2");
// initialise the graph
$graph=new pChart(300,300);
$graph->setFixedScale(0,intval($ram_total/1000));
$graph->setColorPalette(0,100,100,100);
$graph->setColorPalette(1,150,150,150);
$graph->setColorPalette(2,200,200,200);
$graph->setFontProperties("../core/pchart/tahoma.ttf",8);
$graph->setGraphArea(10,10,290,290);
$graph->drawFilledRoundedRectangle(7,7,293,293,5,240,240,240);
$graph->drawRoundedRectangle(5,5,295,295,5,230,230,230);
$graph->drawGraphArea(255,255,255,TRUE);
// draw the line graph
$graph->drawFlatPieGraphWithShadow($DataSet->GetData(),$DataSet->GetDataDescription(),150,150,85,PIE_PERCENTAGE_LABEL,10);  
// finish the graph
//$graph->Render("charts/ram.png");
$graph->Stroke();
?>