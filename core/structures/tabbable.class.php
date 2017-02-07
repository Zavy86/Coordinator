<?php
/**
 * Tabbable structure
 *
 * Long Description
 *
 * @package Coordinator\Structures
 * @author  Manuel Zavatta <manuel.zavatta@gmail.com>
 * @link    http://www.coordinator.it
 */

/**
 * Tabbable structure class
 *
 * @todo check phpdoc
 */
class str_tabbable{
 /** @var integer $id Tabbable ID */
 protected $id;
 /** @var integer $current_tab Current tab index */
 private $current_tab;
 /** @var string $class Tabbable css class */
 protected $class;
 /** @var string $position Tabs navigation position */
 protected $position;
 /** @var integer $selected Selected tab index */
 protected $selected;
 /** @var array $tabs_array Array of tab elements */
 protected $tabs_array;

 /**
  * Tabbable class
  *
  * @param string $position Tabs position ( top | right | bottom | left )
  * @param string $class Tabbable css class
  * @return object tabbable object
  */
 public function __construct($position="top",$class=NULL){
  $this->id="tabbable_".rand(10000,99999);
  $this->class=$class;
  $this->position=$position;
  $this->selected=0;
  $this->current_tab=-1;
  $this->tabs_array=array();
 }

 /**
  * Add tab
  *
  * @param string $label Tab label
  * @param string $content Tab content
  * @param string $class Tab css class
  * @param boolean $enabled Enable the tab
  * @param boolean $selected Select tab by default
  * @return boolean
  */
 function addTab($label,$content,$class=NULL,$enabled=TRUE,$selected=FALSE){
  if(strlen($label)==0){return FALSE;}
  $tab=new stdClass();
  $tab->typology="tab";
  $tab->label=$label;
  $tab->content=$content;
  $tab->class=$class;
  $tab->enabled=$enabled;
  $this->current_tab++;
  if($selected){$this->selected=$this->current_tab;}
  $this->tabs_array[$this->current_tab]=$tab;
  return TRUE;
 }

 /**
  * Renderize tabbable object
  *
  * @param boolean $echo Echo HTML source code or return
  * @return void|string HTML source code
  */
 function render($echo=TRUE ){
  // make position
  switch($this->position){
   case "right":$position_class="tabs-right";break;
   case "bottom":$position_class="tabs-below";break;
   case "left":$position_class="tabs-left";break;
   default:$position_class=NULL;break;
  }
  // open tabbable
  $return="<!-- tabbable -->\n";
  $return.="<div class='tabbable ".$position_class." ".$this->class."'>\n";
  // renderize navigation and content
  if($this->position=="bottom"){
   $return.=$this->render_content($echo);
   $return.="<br>\n";
   $return.=$this->render_navigation($echo);
  }else{
   $return.=$this->render_navigation($echo);
   $return.=$this->render_content($echo);
  }
  // close tabbable
  $return.="</div><!-- /tabbable -->\n\n";
  if($echo){echo $return;return TRUE;}else{return $return;}
 }

 /**
  * Renderize tabbable navigation
  *
  * @param boolean $echo Echo HTML source code or return
  * @return void|string HTML source code
  */
 function render_navigation($echo=TRUE){
  //make position class
  if($this->position=="left"){$position_class=" text-right";}else{$position_class=NULL;}
  // open navigation
  $return=" <!-- navigation-tabs -->\n";
  $return.=" <ul class='nav nav-tabs".$position_class."'>\n";
  // show tabs
  if(is_array($this->tabs_array)){
   // show field
   foreach($this->tabs_array as $key=>$tab){
    if(!is_object($tab)){continue;}
    $return.="  <li class='";
    if($key==$this->selected){$return.="active ";}
    if(!$tab->enabled){$return.="disabled ";}
    $return.=$tab->class."'>";
    // check url
    if(!$tab->enabled){$return.="<a href='#'";}
    else{$return.="<a href='#tab".$key."' data-toggle='tab'";}
    // show label
    $return.=">".$tab->label."</a>";
    $return.="</li>\n";
   }
  }
  // close navigation
  $return.=" </ul><!-- /navigation-tabs -->\n\n";
  if($echo){echo $return;return TRUE;}else{return $return;}
 }

 /**
  * Renderize tabbable content
  *
  * @param boolean $echo Echo HTML source code or return
  * @return void|string HTML source code
  */
 function render_content($echo=TRUE){
  // open content
  $return=" <!-- content-tabs -->\n";
  $return.=" <div class='tab-content'>\n";
  // show content tabs
  if(is_array($this->tabs_array)){
   foreach($this->tabs_array as $key=>$tab){
    if(!is_object($tab)){continue;}
    $return.="  <div class='tab-pane ";
    if($key==$this->selected){$return.="active ";}
    $return.=$tab->class."' id='tab".$key."'>";
    $return.=$tab->content;
    $return.="</div>\n";
   }
  }
  // close content
  $return.=" </div><!-- /content-tabs -->\n\n";
  if($echo){echo $return;return TRUE;}else{return $return;}
 }

}
?>
