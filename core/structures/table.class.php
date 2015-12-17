<?php
/**
 * Table structure
 *
 * Long Description
 *
 * @package Coordinator\Structures
 * @author  Manuel Zavatta <manuel.zavatta@gmail.com>
 * @link    http://www.coordinator.it
 */

/**
 * Table structure class
 *
 * @todo check phpdoc
 */
class str_table{

 /** @var integer $current_row Current row index */
 private $current_row=0;
 /** @var string $unvalued Text to show if no results */
 protected $unvalued;
 /** @var boolean $sortable Show headers sortable link */
 protected $sortable;
 /** @var string $get Additional get parameters for sortable link */
 protected $get;
 /** @var string $class Table css class */
 protected $class;
 /** @var array $th_array Array of table headers */
 protected $th_array;
 /** @var array $tr_array Array of table rows */
 protected $tr_array;

 /**
  * Table class
  *
  * @param string $unvalued Text to show if no results
  * @param boolean $sortable Show headers sortable link
  * @param string $get Additional get parameters for sortable link in format &key=value
  * @param string $class Table css class
  * @return boolean
  */
 public function __construct($unvalued=NULL,$sortable=FALSE,$get=NULL,$class=NULL){
  $this->unvalued=$unvalued;
  $this->sortable=$sortable;
  $this->get=$get;
  $this->class=$class;
  $this->current_row=0;
  $this->th_array=array();
  $this->tr_array=array();
  return TRUE;
 }

 /**
  * Add table header
  *
  * @param string $name Column header name
  * @param string $class Column header css class
  * @param string $width Column header width
  * @param string $order Query field for order
  * @param integer $colspan Column span
  * @return boolean
  */
 public function addHeader($name,$class=NULL,$width=NULL,$order=NULL,$colspan=1){
  if(strlen($name)==0){return FALSE;}
  $th=new stdClass();
  $th->name=$name;
  $th->class=$class;
  $th->width=$width;
  $th->order=$order;
  $th->colspan=$colspan;
  $this->th_array[]=$th;
  return TRUE;
 }

 /**
  * Add table row
  *
  * @param string $class Row css class
  * @return boolean
  */
 public function addRow($class=NULL){
  $this->current_row++;
  $this->tr_array[$this->current_row]=new stdClass();
  $this->tr_array[$this->current_row]->class=$class;
  $this->tr_array[$this->current_row]->fields=array();
  return TRUE;
 }

 /**
  * Add table row
  *
  * @param string $content Field content
  * @param string $class Field css class
  * @param integer $colspan Column span
  * @return boolean
  */
 function addField($content,$class=NULL,$colspan=1){
  $td=new stdClass();
  $td->content=$content;
  $td->class=$class;
  $td->colspan=$colspan;
  $this->tr_array[$this->current_row]->fields[]=$td;
  return TRUE;
 }

 /**
  * Rows number
  *
  * @return integer Number of rows
  */
 public function count(){
  return $this->current_row;
 }

 /**
  * Renderize table object
  *
  * @param boolean $echo Echo HTML source code or return
  * @return void | string HTML source code
  */
 function render($echo=TRUE){
  // open table
  $return="<!-- table -->\n";
  $return.="<table class='table table-striped table-hover table-condensed ".$this->class."'>\n";
  // open head
  if(is_array($this->th_array)){
   $return.="<thead>\n <tr>\n";
   // show headers
   foreach($this->th_array as $th){
    $return.="  <th class='".$th->class."' width='".$th->width."' colspan='".$th->colspan."'>";
    if($this->sortable && $th->order<>NULL){
     // show order link
     if($th->order==$_GET['of']){if($_GET['om']==1){$order=0;}else{$order=1;}}else{$order=1;}
     // check order
     if($th->order==$_GET['of']){
      if($_GET['om']==0){$return.=api_icon("icon-circle-arrow-down",NULL,"margin-top:-0.5px;")."&nbsp;";}
      if($_GET['om']==1){$return.=api_icon("icon-circle-arrow-up",NULL,"margin-top:-0.5px;")."&nbsp;";}
     }
     $return.="<a href='".api_baseName()."?of=".$th->order."&om=".$order.$this->get."'>";
    }
    $return.=$th->name;
    if($this->sortable){$return.="</a>";}
    $return.="</th>\n";
   }
   $return.=" </tr>\n";
   // close head
   $return.="</thead>\n";
  }
  // open body
  $return.="<tbody>\n";
  if(is_array($this->tr_array)){
   foreach($this->tr_array as $tr){
    // show rows
    $return.=" <tr class='".$tr->class."'>\n";
    // show fields
    if(is_array($tr->fields)){
     foreach($tr->fields as $td){
      // show field
      $return.="  <td class='".$td->class."' colspan='".$td->colspan."'>".$td->content."</td>\n";
     }
    }
    $return.=" </tr>\n";
   }
  }
  // show no value text
  if(!count($this->tr_array) && $this->unvalued<>NULL){$return.="<tr><td colspan=".count($this->th_array).">".$this->unvalued."</td></tr>\n";}
  // close body
  $return.="</tbody>\n";
  // close table
  $return.="</table>\n<!-- /table -->\n\n";
  if($echo){echo $return;return TRUE;}else{return $return;}
 }

}
?>