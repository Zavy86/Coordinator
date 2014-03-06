<?php
/* -------------------------------------------------------------------------- *\
|* -[ Dashboard - Widgets ]-------------------------------------------------- *|
\* -------------------------------------------------------------------------- */
include("template.inc.php");
function content(){
 // query widgets
 $widgets_array=array();
 $widgets=$GLOBALS['db']->query("SELECT * FROM settings_dashboards WHERE idAccount='".$_SESSION['account']->id."' ORDER BY position ASC");
 while($widget=$GLOBALS['db']->fetchNextObject($widgets)){
  // check if widget exist
  if(file_exists("../".$widget->module."/widget.inc.php")){
   // parameters
   $widget->parameters_array=array();
   $parameters_array=explode("&",$widget->parameters);
   if(count($parameters_array)>0){
    foreach($parameters_array as $parameters){
     if($parameters[0]<>""){
      $parameter=explode("=",$parameters);
      $widget->parameters_array[$parameter[0]]=$parameter[1];
     }
    }
   }
   $widgets_array[]=$widget;
  }
 }
 // check for widgets
 if(!count($widgets_array)){
  echo "<h4>".api_text("dashboard-welcome")."</h4>\n";
  echo "<div class='well well-small well-white'>\n";
  echo api_text("dashboard-welcome-message",api_accountName());
  echo "</div>\n";
  return FALSE;
 }
 // split
 $split_open=FALSE;
 $split_span=0;
 foreach($widgets_array as $widget){
  // if split is open
  if($split_open){
   if($split_span+$widget->span>12){
    $span=12-$split_span;
    if($span>0){
     echo "\n <div class='span".$span."'>&nbsp;</div><!-- /span".$span." -->\n";
    }
    // close split
    $split_open=FALSE;
    $split_span=0;
    echo "\n</div><!-- /row-fluid -->\n";
   }
  }
  // if split is close
  if(!$split_open){
   // open split
   $split_open=TRUE;
   echo "<!-- row-fluid -->\n";
   echo "<div class='row-fluid'>\n";
  }
  // open widget span
  $split_span=$split_span+$widget->span;
  echo "\n <div class='span".$widget->span."' id='widget_".$widget->id."'>\n";
  // set hidden loader
  //echo "<center><img src='".$GLOBALS['dir']."core/images/gifs/loader.gif' id='widget_".$widget->id."_loader' style='display:none'></center>";
  // set get parameter to widgets parameters
  $_GET=$widget->parameters_array;
  // include widget
  include("../".$widget->module."/widget.inc.php");
  // close widget span
  echo "\n </div><!-- /span".$widget->span." -->\n";
 }

 if($split_open){
  $span=12-$split_span;
  if($span>0){echo "\n <div class='span".$span."'>&nbsp;</div><!-- /span".$span." -->\n";}
  // close split
  $split_open=FALSE;
  $split_span=0;
  echo "\n</div><!-- /row-fluid -->\n";
 }
?>
<script type="text/javascript">
 $(document).ready(function(){
  // refresh widgets\n";
<?php
 foreach($widgets_array as $widget){
  if($widget->refresh>0){
   echo "  var refreshWidget_".$widget->id."=setInterval(function(){\n";
   echo "   $('#widget_".$widget->id."').css('background','url(".$GLOBALS['dir']."core/images/gifs/loader.gif) no-repeat top center');\n";
   echo "   $('#widget_".$widget->id."').load('../".$widget->widget."/widget.inc.php?refresh=1".$widget->parameters."',function(){\n";
   echo "    $('#widget_".$widget->id."').css('background-image','none');\n";
   echo "});\n";
   echo "  },".$widget->refresh.");\n";
  }
 }
?>
 });
</script>
<?php } ?>