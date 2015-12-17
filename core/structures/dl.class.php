<?php
/**
 * Description List structure
 *
 * Long Description
 *
 * @package Coordinator\Structures
 * @author  Manuel Zavatta <manuel.zavatta@gmail.com>
 * @link    http://www.coordinator.it
 */

/**
 * Description List structure class
 *
 * @todo check phpdoc
 */
class str_dl{

 /** @var string $separator Default elements separator */
 protected $separator;
 /** @var string $class Description list css class */
 protected $class;
 /** @var string $elements_array Array of elements */
 protected $elements_array;

 /**
  * Description List class
  *
  * @param string $separator Default elements separator ( null | hr | br )
  * @param string $class Description list css class ( null | dl-horizontal )
  * @return boolean
  */
 public function __construct($separator=NULL,$class=NULL){
  if(!in_array(strtolower($separator),array(NULL,"hr","br"))){return FALSE;}
  $this->class=$class;
  $this->separator=$separator;
  $this->elements_array=array();
  return TRUE;
 }

 /**
  * Add Element
  *
  * @param string $label Label of the element
  * @param string $value Value of the element
  * @param string $separator Element separator ( null | default | hr | br )
  * @param string $class Element css class
  * @return boolean
  */
 public function addElement($label,$value,$separator="default",$class=NULL){
  if(!in_array(strtolower($separator),array(NULL,"default","hr","br"))){return FALSE;}
  if($separator=="default"){$separator=$this->separator;}
  if(!strlen($value)>0){$value="&nbsp;";}
  $element=new stdClass();
  $element->type="element";
  $element->label=$label;
  $element->value=$value;
  $element->separator=$separator;
  $element->class=$class;
  $this->elements_array[]=$element;
  return TRUE;
 }

 /**
  * Add Separator
  *
  * @param string $separator Separator ( null | default | hr | br )
  * @param string $class Separator css class
  * @return boolean
  */
 public function addSeparator($separator="default",$class=NULL){
  if(!in_array(strtolower($separator),array(NULL,"default","hr","br"))){return FALSE;}
  if($separator=="default"){$separator=$this->separator;}
  if(!strlen($value)>0){$value="&nbsp;";}
  $element=new stdClass();
  $element->type="separator";
  $element->label="&nbsp;";
  $element->value="&nbsp;";
  $element->separator=$separator;
  $element->class=$class;
  $this->elements_array[]=$element;
  return TRUE;
 }

 /**
  * Renderize description list object
  *
  * @param boolean $echo Echo HTML source code or return
  * @return void|string HTML source code
  */
 public function render($echo=TRUE){
  $return="\n<!-- dynamic-list -->\n";
  $return.="<dl class='".$this->class."'>\n";
  foreach($this->elements_array as $index=>$element){
   switch($element->type){
    case "element":
     $return.=" <dt>".$element->label."</dt><dd class='".$element->class."'>".$element->value."</dd>";
     if($element->separator<>NULL && $this->elements_array[$index+1]->type=="element"){$return.="<".$element->separator.">\n";}else{$return.="\n";}
     break;
    case "separator":
     if($element->separator<>NULL){$return.="<".$element->separator.">\n";}else{$return.="\n";}
     break;
   }
  }
  $return.="</dl><!-- /dynamic-list -->\n";
  if($echo){echo $return;return TRUE;}else{return $return;}
 }

}
?>