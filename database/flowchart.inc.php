<?php
/* -------------------------------------------------------------------------- *\
|* -[ Databae - Flow Chart ]------------------------------------------------- *|
\* -------------------------------------------------------------------------- */
session_start();
// build flowchart
include("../core/pchart2/class/pData.class.php");
include("../core/pchart2/class/pDraw.class.php");
include("../core/pchart2/class/pSpring.class.php");
include("../core/pchart2/class/pImage.class.php");

/* Create the pChart object */
$myPicture = new pImage(640,640);

/* Draw the background */
//$Settings = array("R"=>170, "G"=>183, "B"=>87, "Dash"=>1, "DashR"=>190, "DashG"=>203, "DashB"=>107);
//$myPicture->drawFilledRectangle(0,0,300,300,$Settings);

/* Overlay with a gradient */
//$Settings = array("StartR"=>219, "StartG"=>231, "StartB"=>139, "EndR"=>1, "EndG"=>138, "EndB"=>68, "Alpha"=>50);
//$myPicture->drawGradientArea(0,0,300,300,DIRECTION_VERTICAL,$Settings);
//$myPicture->drawGradientArea(0,0,300,20,DIRECTION_VERTICAL,array("StartR"=>0,"StartG"=>0,"StartB"=>0,"EndR"=>100,"EndG"=>100,"EndB"=>100,"Alpha"=>80));

/* Add a border to the picture */
//$myPicture->drawRectangle(0,0,639,639,array("R"=>0,"G"=>0,"B"=>0));

/* Set the graph area boundaries*/
$myPicture->setGraphArea(0,0,640,640);

/* Set the default font properties */
$myPicture->setFontProperties(array("FontName"=>"../core/pchart2/fonts/calibri.ttf","FontSize"=>10,"R"=>0,"G"=>0,"B"=>0));

/* Enable shadow computing */
//$myPicture->setShadow(TRUE,array("X"=>2,"Y"=>2,"Alpha"=>20));

/* Create the pSpring object */
$SpringChart = new pSpring();

/* Create some nodes */
//$SpringChart->addNode(0,array("Name"=>"accounts_accounts","Connections"=>"2","Shape"=>NODE_SHAPE_SQUARE,"FreeZone"=>80,"Size"=>10,"NodeType"=>NODE_TYPE_CENTRAL));

// set default node settings
$SpringChart->setNodeDefaults(array("FreeZone"=>120,"R"=>0,"G"=>136,"B"=>204,"Size"=>3));
$SpringChart->setLinkDefaults(array("R"=>125,"G"=>125,"B"=>125));

// build nodes
foreach($_SESSION['nodes'] as $node){
 $SpringChart->addNode($node->name,array("Name"=>$node->label,"Connections"=>$node->links));
}

/* Set the link properties */
//$SpringChart->linkProperties(0,2,array("R"=>255,"G"=>0,"B"=>0,"Ticks"=>2));

/* Draw the spring chart */
$Result = $SpringChart->drawSpring($myPicture);

/* Output the statistics */
// print_r($Result);

/* Render the picture (choose the best way) */
//$myPicture->autoOutput("example.spring.relations.png");

$myPicture->Stroke();
?>