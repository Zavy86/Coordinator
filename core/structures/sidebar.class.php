<?php
/**
 * Sidebar structure
 *
 * Long Description
 *
 * @package Coordinator\Structures
 * @author  Manuel Zavatta <manuel.zavatta@gmail.com>
 * @link    http://www.coordinator.it
 */

/**
 * Sidebar structure class
 *
 * @todo check phpdoc
 */
class str_sidebar{

 /** @var integer $id Sidebar ID */
 protected $id;
 /** @var integer $current_li Current item index */
 private $current_li;
 /** @var string $class Tabbable css class */
 protected $class;
 /** @var string $position Sidebar position */
 protected $position;
 /** @var array $li_array Array of sidebar items */
 protected $li_array;

 /**
  * Sidebar class
  *
  * @param string $class sidebar css class ( nav-list | nav-pills | nav-tabs )
  * @return boolean
  */
 public function __construct($class="nav-list"){
  $this->id="sidebar_".rand(10000,99999);
  $this->class=$class;
  $this->position=$position;
  $this->current_li=-1;
  $this->li_array=array();
 }

 /**
  * Add sidebar item
  *
  * @param string $label Label of item
  * @param string $url Link url
  * @param string $class Element css class
  * @param boolean $enabled Enable the sidebar item
  * @param string $target Target page ( _blank | _self | _parent | _top )
  * @return boolean
  */
 function addItem($label,$url=NULL,$class=NULL,$enabled=TRUE,$target="_self"){
  if(strlen($label)==0){return FALSE;}
  $li=new stdClass();
  $li->typology="item";
  $li->label=$label;
  $li->url=$url;
  $li->class=$class;
  $li->enabled=$enabled;
  $li->target=$target;
  $this->current_tab++;
  $this->li_array[$this->current_tab]=$li;
  return TRUE;
 }

 /**
  * Add sidebar header
  *
  * @param string $label Label of item
  * @return boolean
  */
 function addHeader($label){
  if(strlen($label)==0){return FALSE;}
  $li=new stdClass();
  $li->typology="header";
  $li->label=$label;
  $this->current_tab++;
  $this->li_array[$this->current_tab]=$li;
  return TRUE;
 }

 /**
  * Add sidebar divider
  *
  * @return boolean
  */
 function addDivider(){
  $li=new stdClass();
  $li->typology="divider";
  $this->current_tab++;
  $this->li_array[$this->current_tab]=$li;
  return TRUE;
 }

 /**
  * Renderize sidebar object
  *
  * @param boolean $echo Echo HTML source code or return
  * @return void|string HTML source code
  */
 function render($echo=TRUE){
  // open sidebar
  $return="<!-- sidebar -->\n";
  $return.="<ul class='nav nav-stacked ".$this->class."'>\n";
  // show tabs
  if(is_array($this->li_array)){
   // show field
   foreach($this->li_array as $li){
    if(!is_object($li)){continue;}
    // header
    if($li->typology=="header"){$return.="<li class='nav-header'>".$li->label."</li>\n";continue;}
    // divider
    if($li->typology=="divider"){$return.="<li class='divider'></li>\n";continue;}
    // item
    $return.=" <li";
    $active=FALSE;
    if(substr($li->url,0,(strpos($li->url,"?")>0)?strpos($li->url,"?"):strlen($li->url))==api_baseName()){
     $active=TRUE;
     parse_str(parse_url($li->url,PHP_URL_QUERY),$gets);
     if(count($gets)>0){
      foreach($gets as $key=>$value){
       if($_GET[$key]<>$value){$active=FALSE;}
      }
     }
    }
    // check active and disabled
    $return.=" class='";
    if($active){$return.="active ";}
    if(!$li->enabled){$return.="disabled ";}
    $return.=$li->class."'>";
    // check url
    if($active || !$li->enabled){$return.="<a href='#'";}
    else{$return.="<a href='".$li->url.$li->get."' target='".$li->target."'";}
    // show label
    $return.=">".$li->label."</a>";
    $return.="</li>\n";
   }
  }
  // close tabbable
  $return.="</ul><!-- /sidebar -->\n\n";
  if($echo){echo $return;return TRUE;}else{return $return;}
 }

}
?>