<?php
/**
 * Pagination structure
 *
 * Long Description
 *
 * @package Coordinator\Structures
 * @author  Manuel Zavatta <manuel.zavatta@gmail.com>
 * @link    http://www.coordinator.it
 */

/**
 * Pagination structure class
 *
 * Long **Description** markdown format {@link http://example.com/my/bar}
 *
 * @todo check phpdoc
 */
class str_pagination{

 /** @var string $url Url of the page */
 protected $url;
 /** @var integer $current_tab Current tab index */
 protected $total;
 /** @var integer $limit Number of items for page */
 protected $limit;
 /** @var integer $page Current page */
 protected $page;
 /** @var string $class Pagination css class */
 protected $class;
 /** @var string $class_ul Pagination ul css class */
 protected $class_ul;
 /** @var string $class_li Pagination li css class */
 protected $class_li;
 /** @var string $class_li_active Pagination li of current page css class */
 protected $class_li_active;
 /** @var string $class_li_disabled Pagination li of disabled pages css class */
 protected $class_li_disabled;

 /**
  * Pagination class
  *
  * @param string $table Table name
  * @param string $where Query condition
  * @param string $get URL of the page
  * @param integer $limit Number of items for page
  * @param string $class Pagination css class
  * @param string $class_ul Pagination ul css class
  * @param string $class_li Pagination ul li css class
  * @param string $class_li_active Pagination ul li of current page css class
  * @param string $class_li_disabled Pagination ul li of disabled pages css class
  * @return object pagination object
  */
 public function __construct($table=NULL,$where=NULL,$get=NULL,$limit=20,$class="pagination-small pagination-right",$class_ul="",$class_li="",$class_li_active="active",$class_li_disabled="disabled"){
  if($table==NULL || !is_int($limit)){return FALSE;}
  // acquire variables
  if(isset($_GET['l'])){$limit=$_GET['l'];}
  $g_page=$_GET['p'];
  if(!$g_page){$g_page=1;}
  // count total rows
  if($where<>NULL){
   $total=0;
   $results=$GLOBALS['db']->query("SELECT * FROM ".$table." WHERE ".$where);
   while($result=$GLOBALS['db']->fetchNextObject($results)){$total++;}
  }else{
   $total=0;
   $results=$GLOBALS['db']->query("SELECT * FROM ".$table);
   while($result=$GLOBALS['db']->fetchNextObject($results)){$total++;}
  }
  // build url
  $url=api_baseName()."?p={p}".$get."&l=".$limit;
  // set variables
  $this->url=$url;
  $this->total=$total;
  $this->limit=$limit;
  $this->page=$g_page;
  $this->class=$class;
  $this->class_ul=$class_ul;
  $this->class_li=$class_li;
  $this->class_li_active=$class_li_active;
  $this->class_li_disabled=$class_li_disabled;
  return TRUE;
 }

 /**
  * Query limit
  *
  * @return string limit query
  */
 function queryLimit(){
  if($this->limit){
   $start=($this->page-1)*$this->limit;
   return " LIMIT ".$start.",".$this->limit;
  }else{
   return NULL;
  }
 }

 /**
  * Renderize pagination object
  *
  * @return void
  */
 function render(){
  if(!$this->total>0){return FALSE;}
  if($this->limit){
   $adjacents="2";
   $prev=$this->page-1;
   $next=$this->page+1;
   $lastpage=ceil($this->total/$this->limit);
   $lpm1=$lastpage-1;
  }
  // open pavigation
  echo "<!-- pagination -->\n";
  echo "<div class='pagination ".$this->class."'>\n";
  echo " <ul class='".$this->class_ul."'>\n";
  // pagination limit
  echo "  <li class='".$this->class_li_disabled."'><a href='#'>".ucfirst(api_text("show"))."</a></li>\n";
  $pagination_limit_array=array(20=>"20",100=>"100",250=>"250",0=>ucfirst(api_text("all")." (".number_format($this->total,0,",",".").")"));
  foreach($pagination_limit_array as $index=>$limit){
   if($index==$this->limit){$class=$this->class_li_active;}else{$class=$this->class_li;}
   echo "  <li class='".$class."'><a href='".str_replace("{p}",1,$this->url)."&l=".$index."'>".$limit."</a></li>\n";
  }
  // pages
  if($lastpage>1){
   echo "  <li class='null'><a href='#'>&nbsp;</a></li>\n";
   if($this->page>1){echo "  <li class='".$this->class_li."'><a href='".str_replace("{p}",$prev,$this->url)."'>&laquo;</a></li>\n";}
    else{echo "  <li class='".$this->class_li_disabled."'><span>&laquo;</span></li>\n";}
   if($lastpage<7+($adjacents*2)){
    for($counter=1;$counter<=$lastpage;$counter++){
     if($counter==$this->page){echo "  <li class='".$this->class_li_active."'><span>".$counter."</span></li>\n";}
      else{echo "  <li class='".$this->class_li."'><a href='".str_replace("{p}",$counter,$this->url)."'>".$counter."</a></li>\n";}
    }
   }elseif($lastpage>5+($adjacents*2)){
    if($this->page<2+($adjacents*2)){
     for($counter=1;$counter<4+($adjacents*2);$counter++){
      if($counter==$this->page){echo "  <li class='".$this->class_li_active."'><span>".$counter."</span></li>\n";}
       else{echo "  <li class='".$this->class_li."'><a href='".str_replace("{p}",$counter,$this->url)."'>".$counter."</a></li>\n";}
     }
     echo "  <li class='".$this->class_li_disabled."'><span>&hellip;</span></li>\n";
     echo "  <li><a href='".str_replace("{p}",$lpm1,$this->url)."'>".$lpm1."</a></li>\n";
     echo "  <li><a href='".str_replace("{p}",$lastpage,$this->url)."'>".$lastpage."</a></li>\n";
    }elseif($lastpage-($adjacents*2)>$this->page&&$this->page>($adjacents*2)){
     echo "  <li class='".$this->class_li."'><a href='".str_replace("{p}","1",$this->url)."'>1</a></li>\n";
     echo "  <li class='".$this->class_li."'><a href='".str_replace("{p}","2",$this->url)."'>2</a></li>\n";
     echo "  <li class='".$this->class_li_disabled."'><span>&hellip;</span></li>\n";
     for($counter=$this->page-$adjacents;$counter<=$this->page+$adjacents;$counter++){
      if($counter==$this->page){echo " <li class='".$this->class_li_active."'><span>".$counter."</span></li>\n";}
       else{echo "  <li class='".$this->class_li."'><a href='".str_replace("{p}",$counter,$this->url)."'>".$counter."</a></li>\n";}
     }
     echo "  <li class='".$this->class_li_disabled."'><span>&hellip;</span></li>\n";
     echo "  <li class='".$this->class_li."'><a href='".str_replace("{p}",$lpm1,$this->url)."'>".$lpm1."</a></li>\n";
     echo "  <li class='".$this->class_li."'><a href='".str_replace("{p}",$lastpage,$this->url)."'>".$lastpage."</a></li>\n";
    }else{
     echo "  <li class='".$this->class_li."'><a href='".str_replace("{p}","1",$this->url)."'>1</a></li>\n";
     echo "  <li class='".$this->class_li."'><a href='".str_replace("{p}","2",$this->url)."'>2</a></li>\n";
     echo "  <li class='".$this->class_li_disabled."'><span>&hellip;</span></li>\n";
     for($counter=$lastpage-(2+($adjacents*2));$counter<=$lastpage;$counter++){
      if($counter==$this->page){echo "  <li class='".$this->class_li_active."'><span>".$counter."</span></li>\n";}
       else{echo "  <li class='".$this->class_li."'><a href='".str_replace("{p}",$counter,$this->url)."'>".$counter."</a></li>\n";}
     }
    }
   }
   if($this->page<$counter-1){
    echo "  <li class='".$this->class_li."'><a href='".str_replace("{p}",$next,$this->url)."'>&raquo;</a></li>\n";
   }else{
    echo "  <li class='".$this->class_li_disabled."'><span>&raquo;</span></li>\n";
   }
  }
  echo " </ul>\n";
  echo "</div><!-- /pagination -->\n\n";
  return TRUE;
 }

}
?>