<?php
/**
 * Accordion structure
 *
 * Long Description
 *
 * @package Coordinator\Structures
 * @author  Manuel Zavatta <manuel.zavatta@gmail.com>
 * @link    http://www.coordinator.it
 */

/**
 * Accordion structure class
 *
 * @todo check phpdoc
 */
class str_accordion{

 /** @var integer $id Accordion ID */
 protected $id;
 /** @var string $class Accordion css class */
 protected $class;
 /** @var array $elements_array Array of flag well elements */
 protected $elements_array;

 /**
  * Accordion class
  *
  * @param string $class Accordion css class
  * @return boolean
  */
 public function __construct($class=NULL){
  $this->id="accordion_".rand(10000,99999);
  $this->class=$class;
  $this->elements_array=array();
  return TRUE;
 }

 /**
  * Add accordion element
  *
  * @param string $label Label of element
  * @param string $content Element content
  * @param boolean $open Open by default
  * @param string $class Element css class
  * @param string $subLabel Sublabel of element
  * @return boolean
  */
 public function addElement($label,$content,$open=FALSE,$class=NULL,$subLabel=NULL){
  if(!$content){$content="&nbsp;";}
  $element=new stdClass();
  $element->label=$label;
  $element->content=$content;
  $element->open=$open;
  $element->class=$class;
  $element->subLabel=$subLabel;
  $this->elements_array[]=$element;
  return TRUE;
 }

 /**
  * Renderize accordion object
  *
  * @param boolean $echo Echo HTML source code or return
  * @return void|string HTML source code
  */
 public function render($echo=TRUE){
  $return="\n<!-- accordion -->\n";
  $return.="<div class='accordion ".$this->class."' id='".$this->id."'>\n";
  foreach($this->elements_array as $index=>$element){
   if($element->open){$openClass="in ";}else{$openClass=NULL;}
   $return.=" <div class='accordion-group'>\n";
   $return.="  <div class='accordion-heading'>\n";
   $return.="   <a href='#accordion_collapse_".$index."' class='accordion-toggle' data-toggle='collapse' data-parent='".$this->id."'>".$element->label."</a>\n";
   if($element->subLabel){$return.=$element->subLabel;}
   $return.="  </div>\n";
   $return.="  <div id='accordion_collapse_".$index."' class='accordion-body collapse ".$openClass.$element->class."'>\n";
   $return.="   <div class='accordion-inner'>\n\n";
   $return.=$element->content;
   $return.="\n\n   </div>\n";
   $return.="  </div>\n";
   $return.=" </div>\n";
  }
  $return.="</div><!-- /accordion -->\n\n";
  if($echo){echo $return;return TRUE;}else{return $return;}
 }

}
?>