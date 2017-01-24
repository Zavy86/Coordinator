<?php
/**
 * Dashboard structure
 *
 * Long Description
 *
 * @package Coordinator\Structures
 * @author  Manuel Zavatta <manuel.zavatta@gmail.com>
 * @link    http://www.coordinator.it
 */

/**
 * Dashboard structure class
 *
 * @todo check phpdoc
 */
class str_dashboard{

 /** @var string $class Dashbaord css class */
 protected $class;
 /** @var string $label Default container label */
 protected $label;
 /** @var string $description Default container description */
 protected $description;
 /** @var string $elements_count Elements counter */
 public $elements_count;
 /** @var string $elements_array Array of elements */
 protected $elements_array;

 /**
  * Dashboard class
  *
  * @param string $label Default container label
  * @param string $description Default container description
  * @param string $class Dashboard css class ( null | dl-horizontal )
  * @return boolean
  */
 public function __construct($label=NULL,$description=NULL,$class=NULL){
  $this->label=$label;
  $this->description=$description;
  $this->class=$class;
  $this->elements_array=array();
  return TRUE;
 }

 /**
  * Add Container
  *
  * @param string $label Label of the element
  * @param string $description Element description
  * @param string $class Element css class
  * @return boolean
  */
 public function addContainer($label,$description=NULL,$class=NULL){
  if(!strlen($label)){return FALSE;}
  $element=new stdClass();
  $element->type="container";
  $element->label=$label;
  $element->description=$description;
  $element->class=$class;
  $this->elements_array[]=$element;
  return TRUE;
 }

 /**
  * Add Element
  *
  * @param string $url Linked URL
  * @param string $label Label of the element
  * @param string $description Element description
  * @param string $enabled Element enabled
  * @param string $size Element size ( 1x1 | 2x1 | 3x1 | 4x1 | 5x1 | 6x1 )
  * @param string $icon Element bottom left icon
  * @param string $counter Element bottom right counter
  * @param string $counter_class Element bottom right counter css class
  * @param string $background Background image path
  * @param string $target Link target
  * @param string $class Element css class
  * @return boolean
  */
 public function addElement($url,$label,$description=NULL,$enabled=TRUE,$size="1x1",$icon=NULL,$counter=NULL,$counter_class=NULL,$background=NULL,$target=NULL,$class=NULL){
  if(!strlen($url)||!strlen($label)){return FALSE;}
  if(!in_array(strtolower($size),array("1x1","2x1","3x1","4x1","5x1","6x1"))){$size="1x1";}
  $element=new stdClass();
  $element->type="element";
  $element->url=$url;
  $element->label=$label;
  $element->description=$description;
  $element->enabled=$enabled;
  $element->size=strtolower($size);
  $element->icon=$icon;
  $element->counter=$counter;
  $element->counter_class=$counter_class;
  $element->background=$background;
  $element->target=$target;
  $element->class=$class;
  $this->elements_array[]=$element;
  $this->elements_count++;
  return TRUE;
 }

 /**
  * Renderize dashboard object
  *
  * @param boolean $echo Echo HTML source code or return
  * @return void|string HTML source code
  */
 public function render($echo=TRUE){
  // renderize dashboard
  $return="\n<!-- dashboard -->\n";
  $return.="<div class='dashboard ".$this->class."'>\n";
  // renderize default container
  $return.=" <!-- dashboard-container -->\n";
  $return.=" <div class='dashboard-container ".$this->class."'>\n";
  // check for label
  if($this->label){
   // renderize default container title
   $return.="  <div class='dashboard-container-title'>\n";
   $return.="   <div class='dashboard-container-label'>".$this->label."</div>\n";
   if($this->description){$return.="   <div class='dashboard-container-description'>".$this->description."</div>\n";}
   $return.="  </div>\n";
  }
  // cycle all elements
  foreach($this->elements_array as $element){
   switch($element->type){
    // dashboard container
    case "container":
     // close default container
     $return.=" </div><!-- /dashboard-container -->\n";
     // open new container
     $return.=" <!-- dashboard-container -->\n";
     $return.=" <div class='dashboard-container ".$element->class."'>\n";
     $return.="  <div class='dashboard-container-title'>\n";
     $return.="   <div class='dashboard-container-label'>".$element->label."</div>\n";
     if($element->description){$return.="   <div class='dashboard-container-description'>".$element->description."</div>\n";}
     $return.="  </div>\n";
     break;
    // dashboard element
    case "element":
     // check if tile is starred if not in dashboard
     if(api_baseModule()<>"dashboard"){
      $starred_tile_id=$GLOBALS['db']->queryUniqueValue("SELECT id FROM `settings_dashboards` WHERE `idAccount`='".api_account()->id."' AND `module`='".api_baseModule()."' AND `url`='".$element->url."'");
      // make starred link
      if($starred_tile_id>0){
       $starred_link=api_link("../dashboard/submit.php?act=tile_delete&idTile=".$starred_tile_id."&redirect=../".api_baseModule()."/".api_baseName(),api_icon("icon-star"),NULL,NULL,FALSE,api_text("dashboard-tile-remove"));
      }else{
       $element->module=api_baseModule();
       $starred_link=api_link("../dashboard/submit.php?act=tile_save&redirect=../".api_baseModule()."/".api_baseName()."&element=".urlencode(json_encode($element)),api_icon("icon-star-empty"),NULL,NULL,FALSE,api_text("dashboard-tile-add"));
      }
     }
     // make hyperlink reference
     if($element->target){
      $href="window.open('".($element->enabled?$element->url:"#")."','".$element->target."');";
     }else{
      $href="window.location='".($element->enabled?$element->url:"#")."';";
     }
     // make background css style
     if(file_exists($element->background)){
      $background_style=" style='background-image:url(\"".$element->background."?rand=".rand(0,999)."\")'";
      $background_class="dashboard-element-background-alpha";
     }else{
      $background_style=NULL;
      $background_class=NULL;
     }
     // renderize dashboard element
     $return.="  <!-- dashboard-element -->\n";
     $return.="  <div class='dashboard-element dashboard-element-size-".$element->size." ".(!$element->enabled?"dashboard-element-disabled":NULL)."' onclick=\"".$href."\"".$background_style.">\n";
     $return.="   <p class='dashboard-element-label ".$background_class."'>".$starred_link." ".$element->label."</p>\n";
     $return.="   <p class='dashboard-element-description ".$background_class."'>".$element->description."</p>\n";
     if($element->icon){$return.="   <span class='dashboard-element-icon'>".api_icon($element->icon)."</span>\n";}
     if($element->counter){$return.="   <span class='dashboard-element-counter ".$element->counter_class."'>".$element->counter."</span>\n";}
     $return.="  </div><!-- /dashboard-element -->\n";
     break;
   }
  }
  $return.=" </div><!-- /dashboard-container -->\n";
  $return.="</div><!-- /dashboard -->\n";
  // echo or return
  if($echo){echo $return;return TRUE;}else{return $return;}
 }

}

?>