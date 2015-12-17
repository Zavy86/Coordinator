<?php
/**
 * Flag well structure
 *
 * Long Description
 *
 * @package Coordinator\Structures
 * @author  Manuel Zavatta <manuel.zavatta@gmail.com>
 * @link    http://www.coordinator.it
 */

/**
 * Flag well structure class
 *
 * @todo check phpdoc
 */
class str_flagwell{

 /** @var string $title Flag well title */
 protected $title;
 /** @var string $class Flag well css class */
 protected $class;
 /** @var string $class Flagwell content */
 protected $content;

 /**
  * Flag well class
  *
  * @param string $title Title of flag well
  * @param string $class Flag well css class
  * @return boolean
  */
 public function __construct($title,$class=NULL){
  if(strlen($title)==0){return FALSE;}
  $this->title=$title;
  $this->class=$class;
  return TRUE;
 }

 /**
  * Flag well content
  *
  * @param string $content Content of flag well
  * @return boolean
  */
  public function content($content){
  if(strlen($content)==0){return FALSE;}
  $this->content=$content;
  return TRUE;
 }

 /**
  * Renderize flag well object
  *
  * @param boolean $echo Echo HTML source code or return
  * @return void|string HTML source code
  */
 public function render($echo=TRUE){
  $return="\n<!-- flag-well -->\n";
  $return.="<div class='flag-well ".$this->class."'>\n";
  $return.=" <span class='title'>".$this->title."</span>\n";
  $return.=" <div class='flag-well-content'>\n".$this->content."\n </div>\n";
  $return.="</div><!-- /flag-well -->\n";
  if($echo){echo $return;return TRUE;}else{return $return;}
 }

}
?>