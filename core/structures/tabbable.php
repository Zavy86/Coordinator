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
 /** @var string $position Tabs position */
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
  $this->id="accordion_".rand(10000,99999);
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
  * @return boolean
  */
 function render(){
  // make position
  switch($this->position){
   case "right":$position_class="tabs-right";break;
   case "bottom":$position_class="tabs-below";break;
   case "left":$position_class="tabs-left";break;
   default:$position_class=NULL;break;
  }
  // open tabbable
  echo "<!-- tabbable -->\n";
  echo "<div class='tabbable ".$position_class." ".$this->class."'>\n";
  // renderize navigation and content
  if($this->position=="bottom"){
   $this->render_content();
   echo "<br>\n";
   $this->render_navigation();
  }else{
   $this->render_navigation();
   $this->render_content();
  }
  // close tabbable
  echo "</div><!-- /tabbable -->\n\n";
  return TRUE;
 }

 /**
  * Renderize tabbable navigation
  *
  * @return boolean
  */
 function render_navigation(){
  // open navigation
  echo " <!-- navigation-tabs -->\n";
  echo " <ul class='nav nav-tabs'>\n";
  // show tabs
  if(is_array($this->tabs_array)){
   // show field
   foreach($this->tabs_array as $key=>$tab){
    if(!is_object($tab)){continue;}
    echo "  <li class='";
    if($key==$this->selected){echo "active ";}
    if(!$tab->enabled){echo "disabled ";}
    echo $tab->class."'>";
    // check url
    if(!$tab->enabled){echo "<a href='#'";}
    else{echo "<a href='#tab".$key."' data-toggle='tab'";}
    // show label
    echo ">".$tab->label."</a>";
    echo "</li>\n";
   }
  }
  // close navigation
  echo " </ul><!-- /navigation-tabs -->\n\n";
  return TRUE;
 }

 /**
  * Renderize tabbable content
  *
  * @return boolean
  */
 function render_content(){
  // open content
  echo " <!-- content-tabs -->\n";
  echo " <div class='tab-content'>\n";
  // show content tabs
  if(is_array($this->tabs_array)){
   foreach($this->tabs_array as $key=>$tab){
    if(!is_object($tab)){continue;}
    echo "  <div class='tab-pane ";
    if($key==$this->selected){echo "active ";}
    echo $tab->class."' id='tab".$key."'>";
    echo $tab->content;
    echo "</div>\n";
   }
  }
  // close content
  echo " </div><!-- /content-tabs -->\n\n";
  return TRUE;
 }

}
?>
