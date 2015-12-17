<?php
/**
 * Modal structure
 *
 * Long Description
 *
 * @package Coordinator\Structures
 * @author  Manuel Zavatta <manuel.zavatta@gmail.com>
 * @link    http://www.coordinator.it
 */

/**
 * Modal window structure class
 *
 * @todo check phpdoc
 */
class str_modal{

 /** @var string $id Modal window id */
 protected $id;
 /** @var string $header Modal window header */
 protected $header;
 /** @var string $class Modal window body content */
 protected $body;
 /** @var string $class Modal window footer */
 protected $footer;
 /** @var string $class Modal window css class */
 protected $class;

 /**
  * Modal window class
  *
  * @param string $id Modal window ID
  * @param string $class Modal window css class
  * @return boolean
  */
 public function __construct($id,$class=NULL){
  if(strlen($id)==0){return FALSE;}
  $this->id=$id;
  $this->class=$class;
  return TRUE;
 }

 /**
  * Modal window link
  *
  * @param string $label Label for the link
  * @param string $class Link css class
  * @param string $style Link style tags
  * @return string Modal windows link
  */
 function link($label,$class=NULL,$style=NULL){
  if(strlen($label)==0){return FALSE;}
  return "<a href='#modal_".$this->id."' data-toggle='modal' class='".$class."' id='modal-link_".$this->id."' style='".$style."'>".$label."</a>";
 }

 /**
  * Modal window header
  *
  * @param string $content Content of the modal windows header
  * @return boolean
  */
 function header($content){
  if(strlen($content)==0){return FALSE;}
  $this->header=$content;
  return TRUE;
 }

 /**
  * Modal window body
  *
  * @param string $content Content of the modal windows body
  * @return boolean
  */
 function body($content){
  if(strlen($content)==0){return FALSE;}
  $this->body=$content;
  return TRUE;
 }

 /**
  * Modal window footer
  *
  * @param string $content Content of the modal windows footer
  * @return boolean
  */
 function footer($content){
  if(strlen($content)==0){return FALSE;}
  $this->footer=$content;
  return TRUE;
 }

 /**
  * Renderize modal window object
  *
  * @param boolean $echo Echo HTML source code or return
  * @return void | string HTML source code
  */
 function render($echo=TRUE){
  if(!strlen($this->body)>0){return FALSE;}
  // open modal window
  $return="<!-- modal window ".$this->id." -->\n";
  $return.="<div id='modal_".$this->id."' class='modal hide fade ".$this->class."' role='dialog' aria-hidden='true'>\n";
  // modal window header
  $return.=" <div class='modal-header'>\n";
  $return.="  <button type='button' class='close' data-dismiss='modal' aria-hidden='true'>&times;</button>\n";
  if(strlen($this->header)>0){$return.="  <h4>".$this->header."</h4>\n";}
  $return.=" </div>\n";
  // modal window body
  $return.=" <div class='modal-body'>\n".$this->body."\n </div>\n";
  // modal window footer
  if(strlen($this->footer)>0){$return.=" <div class='modal-footer'>\n".$this->footer."\n </div>\n";}
  // close modal window
  $return.="</div><!-- /modal window ".$this->id." -->\n\n";
  if($echo){echo $return;return TRUE;}else{return $return;}
 }

}
?>