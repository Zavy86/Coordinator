<?php
/* -------------------------------------------------------------------------- *\
|* -[ Stats - CPU Chart ]---------------------------------------------------- *|
\* -------------------------------------------------------------------------- */
session_start();
// initialize values
if((strtotime(date('Y-m-d H:i:s'))-strtotime($_SESSION['stats_cpu_timestamp']))>10){
 for($i=0;$i<31;$i++){
  $_SESSION['stats_cpu'][$i]=0;
 }
}
// pChart inclusion
require_once("../core/pchart/pData.class");
require_once("../core/pchart/pChart.class");
// sets time value
$speed=1;
// sets variable with current CPU information and then turns it into an array seperating each word
$prevVal=shell_exec("cat /proc/stat");
$prevArr=explode(' ',trim($prevVal));
// gets some values from the array and stores them
$prevTotal=$prevArr[2]+$prevArr[3]+$prevArr[4]+$prevArr[5];
$prevIdle=$prevArr[5];
// wait a period of time until taking the readings again to compare with previous readings
usleep($speed*1000000);
// does the same as before
$val=shell_exec("cat /proc/stat");
$arr=explode(' ',trim($val));
// same as before.
$total=$arr[2]+$arr[3]+$arr[4]+$arr[5];
$idle=$arr[5];
// does some calculations now to work out what percentage of time the CPU has been in use over the given time period
$intervalTotal=intval($total-$prevTotal);
// does a few more calculations and outputs total CPU usage as an integer
if($intervalTotal>0){$cpu=intval(100*(($intervalTotal-($idle-$prevIdle))/$intervalTotal));}else{$cpu=0;}
$_SESSION['stats_cpu'][]=$cpu;
$_SESSION['stats_cpu']=array_splice($_SESSION['stats_cpu'],-31);
$_SESSION['stats_cpu_timestamp']=api_now();
// chart dataset definition
$dataSet=new pData;
$dataSet->AddPoint($_SESSION['stats_cpu'],"Serie1");
$dataSet->AddAllSeries();
$dataSet->SetAbsciseLabelSerie();
$dataSet->SetSerieName("CPU","Serie1");
// initialise the graph
$graph=new pChart(600,300);
$graph->setFixedScale(0,100);
$graph->createColorGradientPalette(0,0,0,100,100,100,10);
$graph->setFontProperties("../core/pchart/tahoma.ttf",8);
$graph->setGraphArea(40,20,580,270);
$graph->drawFilledRoundedRectangle(7,7,593,293,5,240,240,240);
$graph->drawRoundedRectangle(5,5,595,295,5,230,230,230);
$graph->drawGraphArea(255,255,255,TRUE);
$graph->drawScale($dataSet->GetData(),$dataSet->GetDataDescription(),SCALE_NORMAL,150,150,150,TRUE,0,2);
$graph->drawGrid(4,TRUE,230,230,230,50);
// draw the 0 line
$graph->setFontProperties("../core/pchart/tahoma.ttf",6);
$graph->drawTreshold(0,143,55,72,TRUE,TRUE);
// draw the line graph
$graph->drawLineGraph($dataSet->GetData(),$dataSet->GetDataDescription());
$graph->drawPlotGraph($dataSet->GetData(),$dataSet->GetDataDescription(),3,2,255,255,255);
// finish the graph
//$graph->Render("charts/cpu.png");
$graph->Stroke();
?>