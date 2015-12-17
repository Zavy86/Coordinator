<?php
/**
 * Splitted structure
 *
 * Long Description
 *
 * @package Coordinator\Structures
 * @author  Manuel Zavatta <manuel.zavatta@gmail.com>
 * @link    http://www.coordinator.it
 */

/**
 * Splitted structure class
 *
 * @todo check phpdoc
 */
class str_splitted{

 /** @var string $class Accordion css class */
 protected $class;
 /** @var integer $columns Total number of columns used ( <=12 ) */
 protected $columns;
 /** @var array $spans_array Array of span objects */
 protected $spans_array;

 /**
  * Splitted class
  *
  * @param string $class Splitted css class
  * @return boolean
  */
 public function __construct($class=NULL){
  $this->class=$class;
  $this->columns=0;
  $this->spans_array=array();
  return TRUE;
 }


 /**
  * Add splitted span
  *
  * @param integer $columns Number of columns for span
  * @param string $content Span content
  * @return boolean
  */
 function addSpan($columns,$content){
  if(!$columns || !$content){return FALSE;}
  if($this->columns+$columns>12){return FALSE;}
  $this->columns+=$columns;
  $span=new stdClass();
  $span->content=$content;
  $span->columns=$columns;
  $this->spans_array[]=$span;
  return TRUE;
 }

 /**
  * Renderize splitted object
  *
  * @param boolean $echo Echo HTML source code or return
  * @return void|string HTML source code
  */
 public function render($echo=TRUE){
  $splitSpan=0;
  $return.="<!-- row-fluid -->\n";
  $return.="<div class='row-fluid'>\n";

  foreach($this->spans_array as $index=>$span){
   if($index){$return.="\n </div><!-- /span".$splitSpan." -->\n";}
   $splitSpan=$span->columns;
   $return.=" <div class='span".$span->columns."'>\n\n";
   $return.=$span->content;
  }

  $return.="\n </div><!-- /span".$splitSpan." -->\n";
  $return.="</div><!-- /row-fluid -->\n\n";

  if($echo){echo $return;return TRUE;}else{return $return;}
 }

}
?>