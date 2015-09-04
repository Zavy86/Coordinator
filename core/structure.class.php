<?php

/* -[ Structure Classes ]---------------------------------------------------- */


/* -------------------------------------------------------------------------- *\
|* -[ Navigation ]----------------------------------------------------------- *|
\* -------------------------------------------------------------------------- */

/**
 * Navigation structure
 */
class str_navigation{

 private $current_tab;

 protected $search;
 protected $get;
 protected $class;
 protected $nt_array;

 protected $filters;

 /**
 * Navigation class
 *
 * @param boolean $search show search bar
 * @param array $get additional get parameters for search bar
 * @param string $class navigation css class
 * @return object navigation object
 */
 public function __construct($search=FALSE,$get=NULL,$class=NULL){
  if($get<>NULL && !is_array($get)){$get=array($get);}
  $this->search=$search;
  $this->get=$get;
  $this->class=$class;
  $this->current_tab=-1;
  $this->nt_array=array();
  $this->filters=array();
 }

 /**
 * Add navigation tab
 *
 * @param string $label label of the tab
 * @param string $url link url
 * @param string $get additional get parameters for link (&key=value)
 * @param string $class tab css class
 * @param boolean $enabled enable the navigation tab (true) or not
 * @param string $target target page _blank, _self, _parent, _top
 * @param string $confirm confirmation message to approve
 * @return true|false
 */
 function addTab($label,$url=NULL,$get=NULL,$class=NULL,$enabled=TRUE,$target="_self",$confirm=NULL){
  if(strlen($label)==0){return FALSE;}
  $nt=new stdClass();
  $nt->typology="tab";
  $nt->label=$label;
  $nt->url=$url;
  $nt->get=$get;
  $nt->class=$class;
  $nt->enabled=$enabled;
  $nt->target=$target;
  $nt->confirm=$confirm;
  $this->current_tab++;
  $this->nt_array[$this->current_tab]=$nt;
  return TRUE;
 }

 /**
 * Add navigation sub tab
 *
 * @param string $label label of the tab
 * @param string $url link url
 * @param string $get additional get parameters for link (&key=value)
 * @param string $class sub tab css class
 * @param boolean $enabled enable the navigation tab (true) or not
 * @param string $target target page _blank, _self, _parent, _top
 * @param string $confirm confirmation message to approve
 * @return true|false
 */
 function addSubTab($label,$url=NULL,$get=NULL,$class=NULL,$enabled=TRUE,$target="_self",$confirm=NULL){
  if(strlen($label)==0){return FALSE;}
  $nt=new stdClass();
  $nt->typology="subtab";
  $nt->label=$label;
  $nt->url=$url;
  $nt->get=$get;
  $nt->class=$class;
  $nt->enabled=$enabled;
  $nt->target=$target;
  $nt->confirm=addslashes($confirm);
  if(!is_array($this->nt_array[$this->current_tab]->dropdown)){
   $this->nt_array[$this->current_tab]->dropdown=array();
  }
  $this->nt_array[$this->current_tab]->dropdown[]=$nt;
  return TRUE;
 }

 /**
 * Add navigation sub tab header
 *
 * @param string $label label of the tab
 * @return true|false
 */
 function addSubTabHeader($label){
  if(strlen($label)==0){return FALSE;}
  $nt=new stdClass();
  $nt->typology="subtab-header";
  $nt->label=$label;
  if(!is_array($this->nt_array[$this->current_tab]->dropdown)){
   $this->nt_array[$this->current_tab]->dropdown=array();
  }
  $this->nt_array[$this->current_tab]->dropdown[]=$nt;
  return TRUE;
 }

 /**
 * Add navigation sub tab divider
 *
 * @return true|false
 */
 function addSubTabDivider(){
  $nt=new stdClass();
  $nt->typology="subtab-divider";
  if(!is_array($this->nt_array[$this->current_tab]->dropdown)){
   $this->nt_array[$this->current_tab]->dropdown=array();
  }
  $this->nt_array[$this->current_tab]->dropdown[]=$nt;
  return TRUE;
 }

 /**
 * Add navigation filter
 *
 * @param string $type text, checkbox, radio, select, multiselect, range, date, datetime, daterange, datetimerange
 * @param string $name name of the filter input
 * @param string $label label of the filter
 * @param array $options array of options (value=>label)
 * @param string $class filter input css class
 * @param string $placeholder placeholder message
 * @return true|false
 */
 function addFilter($type,$name,$label,$options=NULL,$class=NULL,$placeholder=NULL){
  if(!in_array(strtolower($type),array("text","checkbox","radio","select","multiselect","range","date","datetime","daterange","datetimerange"))){return FALSE;}
  if(strlen($name)==0){return FALSE;}
  if($options<>NULL && !is_array($options)){$options=array($options);}
  $f=new stdClass();
  $f->type=$type;
  $f->name=$name;
  $f->label=$label;
  $f->options=$options;
  $f->class=$class;
  $f->placeholder=$placeholder;
  $this->filters[]=$f;
  return TRUE;
 }

 /**
 * Retrieve navigation filters in textual format
 *
 * @param string $unvalued : text to return if no filters
 * @return string|$unvalued
 */
 function filtersText($unvalued=NULL){
  $text=NULL;
  foreach($this->filters as $filter){
   $value=NULL;
   // switch filter type
   switch($filter->type){
    // multiple filters have array results
    case "multiselect":
     $text_filter=NULL;
     /*if(count($filter->options)==count($_GET[$filter->name])){
      $value="Tutti";
     }else{*/
      if(is_array($_GET[$filter->name])){
       foreach($_GET[$filter->name] as $g_option){
        $text_filter.=", ".$filter->options[$g_option];
       }
      }
      $value=substr($text_filter,2);
     //}
     break;
    // checkbox and radio have text value
    case "checkbox":
    case "radio":
     if($_GET[$filter->name]<>NULL){$value=$filter->options[$_GET[$filter->name]];}
     break;
    // select value is in array
    case "select":
     if($_GET[$filter->name]<>NULL){$value=$filter->options[$_GET[$filter->name]];}
     break;
    // range values
    case "range":
    case "daterange":
    case "datetimerange":
     if($_GET[$filter->name."_from"]<>NULL){$value=api_text("form-range-from")." ".$_GET[$filter->name."_from"]." ";}
     if($_GET[$filter->name."_to"]<>NULL){$value.=api_text("form-range-to")." ".$_GET[$filter->name."_to"];}
     break;
    default:
     if($_GET[$filter->name]<>NULL){$value=$_GET[$filter->name];}
   }
   if($value<>NULL){
    $text.=" <span class='label label-info'>";
    if($filter->label<>NULL && strtolower($filter->label)<>"&nbsp;"){$text.=$filter->label." = ";}
    $text.=$value."</span>";
   }
  }
  if($text<>NULL){
   $return="<p><a href='".api_baseName()."?filtered=1' style='color:#ffffff;text-decoration:none;'><span class='label'>&times; ".api_text("filters-filters").":</span></a> ".substr(str_replace("*","%",$text),1)."</p>\n";
  }else{
   if($unvalued<>NULL){$unvalued="<p><a href='".api_baseName()."?filtered=1' style='color:#ffffff;text-decoration:none;'><span class='label'>&times; ".api_text("filters-filters").":</span></a> <span class='label label-inverse'>".$unvalued."</span></p>\n";}
   $return=$unvalued;
  }
  return $return;
 }

 /**
 * Retrieve navigation filters in url parameters format
 *
 * @return string &filtered=1&q=search
 */
 function filtersGet(){
  $return=NULL;
  foreach($this->filters as $filter){
   $value=NULL;
   // switch filter type
   switch($filter->type){
    // multiple filters have array results
    case "multiselect":
     if(is_array($_GET[$filter->name])){
      foreach($_GET[$filter->name] as $g_option){
       $value.="&".$filter->name."[]=".$g_option;
      }
     }
     break;
    // range values
    case "range":
    case "daterange":
    case "datetimerange":
     if($_GET[$filter->name."_from"]<>NULL){$value="&".$filter->name."_from=".$_GET[$filter->name."_from"];}
     if($_GET[$filter->name."_to"]<>NULL){$value.="&".$filter->name."_to=".$_GET[$filter->name."_to"];}
     break;
    default:
     if($_GET[$filter->name]<>NULL){$value="&".$filter->name."=".$_GET[$filter->name];}
   }
   if($value<>NULL){$return.=$value;}
  }
  return "&filtered=1".$return."&q=".$_GET['q'];
 }

 /**
 * Retrieve navigation filters query
 *
 * @string $unvalued query to return if no filters
 * @return string|$unvalued
 */
 function filtersQuery($unvalued="0"){
  $query=NULL;
  foreach($this->filters as $filter){
   $query_filter=NULL;
   // switch filter type
   switch($filter->type){
    // multiple filters have array results
    case "multiselect":
     $multi_filter=NULL;
     if(is_array($_GET[$filter->name])){
      foreach($_GET[$filter->name] as $g_option){
       $multi_filter.=" OR ".$filter->name."='".$g_option."'";
      }
     }
     if($multi_filter<>NULL){$query_filter="(".substr($multi_filter,4).")";}
     break;
    // range values
    case "range":
    case "daterange":
    case "datetimerange":
     $query_filter="(";
     if($_GET[$filter->name."_from"]<>NULL){$query_filter.=$filter->name.">='".$_GET[$filter->name."_from"]."'";}
     if($query_filter<>"("){$query_filter.=" AND ";}
     if($_GET[$filter->name."_to"]<>NULL){$query_filter.=$filter->name."<='".$_GET[$filter->name."_to"]."'";}
     $query_filter.=")";
     if($query_filter=="()"){$query_filter=NULL;}
     break;
    // text filters use like
    case "text":
     if($_GET[$filter->name]<>NULL){
      $query_filter=$filter->name." LIKE '".$_GET[$filter->name]."'";
     }
     break;
    default:
     if($_GET[$filter->name]<>NULL){
      $query_filter=$filter->name."='".$_GET[$filter->name]."'";
     }
   }
   // make filter query
   if($query_filter<>NULL){$query.=" AND ".$query_filter;}
  }
  // build complete query
  if($query<>NULL){$return="(".substr(str_replace("*","%",$query),5).")";}else{$return=$unvalued;}
  return $return;
 }

 /**
 * Retrieve navigation filters parameter query
 *
 * @param string $parameter parameter id
 * @param string $unvalued query to return if no filters
 * @param string $field rename query field
 * @return string|$unvalued
 */
 function filtersParameterQuery($parameter,$unvalued="0",$field=NULL){
  foreach($this->filters as $filter_tmp){
   if($filter_tmp->name==$parameter){
    $filter=$filter_tmp;
    break;
   }
  }
  if($field==NULL){$field=$filter->name;}
  $query_filter=NULL;
  // switch filter type
  switch($filter->type){
   // multiple filters have array results
   case "multiselect":
    $multi_filter=NULL;
    if(is_array($_GET[$filter->name])){
     foreach($_GET[$filter->name] as $g_option){
      $multi_filter.=" OR ".$field."='".$g_option."'";
     }
    }
    if($multi_filter<>NULL){$query_filter="(".substr($multi_filter,4).")";}
    break;
   // range values
   case "range":
   case "daterange":
   case "datetimerange":
    $query_filter="(";
    if($_GET[$filter->name."_from"]<>NULL){$query_filter.=$field.">='".$_GET[$filter->name."_from"]."'";}
    if($query_filter<>"("){$query_filter.=" AND ";}
    if($_GET[$filter->name."_to"]<>NULL){$query_filter.=$field."<='".$_GET[$filter->name."_to"]."'";}
    $query_filter.=")";
    if($query_filter=="()"){$query_filter=NULL;}
    break;
   // text filters use like
   case "text":
    if($_GET[$filter->name]<>NULL){
     $query_filter=$field." LIKE '".$_GET[$filter->name]."'";
    }
    break;
   default:
    if($_GET[$filter->name]<>NULL){
     $query_filter=$field."='".$_GET[$filter->name]."'";
    }
  }
  // build complete query
  if($query_filter<>NULL){$return=str_replace("*","%",$query_filter);}else{$return=$unvalued;}
  return $return;
 }

 /**
 * Renderize navigation object
 */
 function render(){
  // open navigation
  echo "<!-- navigation-tabs -->\n";
  echo "<ul class='nav nav-tabs ".$this->class."'>\n";
  // show filters
  if(count($this->filters)>0){
   // reset session filters
   if($_GET['resetFilters']){unset($_SESSION['filters'][api_baseName()]);}
   // store session filters
   if($_GET['filtered']){$_SESSION['filters'][api_baseName()]=$_GET;}
   // load session filters if exist
   if(isset($_SESSION['filters'][api_baseName()])){$_GET=array_merge($_SESSION['filters'][api_baseName()],$_GET);}
   // build filter form modal body
   $modal_filter_body=new str_form(api_baseName(),"get","filters");
   $modal_filter_body->addField("hidden","filtered",NULL,"1");
   //pre_var_dump($_GET);
   foreach($this->filters as $filter){
    //pre_var_dump($filter,"print","filter");
    //pre_var_dump($_GET[$filter->name],"print","value");
    // filter with options
    if($filter->options<>NULL){$modal_filter_body->addField($filter->type,$filter->name,$filter->label,NULL,$filter->class,$filter->placeholder);
     foreach($filter->options as $value=>$label){
      $checked=FALSE;
      if(is_array($_GET[$filter->name])){
       foreach($_GET[$filter->name] as $g_option){
        if($g_option==$value){$checked=TRUE;}
       }
      }else{
       if($_GET[$filter->name]==$value){$checked=TRUE;}
      }
      //pre_var_dump($value." -> ".$label." -> ".$checked,"print","option");
      $modal_filter_body->addFieldOption($value,$label,$checked);
     }
    }else{
     // range filter
     if($filter->type=="range" || $filter->type=="daterange" || $filter->type=="datetimerange"){
      $modal_filter_body->addField($filter->type,$filter->name,$filter->label,array(str_replace("*","%",$_GET[$filter->name."_from"]),str_replace("*","%",$_GET[$filter->name."_to"])),$filter->class,$filter->placeholder);
     }else{
      // standard filter
      $modal_filter_body->addField($filter->type,$filter->name,$filter->label,str_replace("*","%",$_GET[$filter->name]),$filter->class,$filter->placeholder);
     }
    }
   }
   $modal_filter_body->addControl("submit",api_text("filters-apply"));
   $modal_filter_body->addControl("button",api_text("filters-reset"),NULL,api_baseName()."?resetFilters=1");
   // build filter modal window
   $modal_filter=new str_modal("filters");
   $modal_filter->header(api_text("filters-filters"));
   $modal_filter->body($modal_filter_body->render(FALSE));
   // show link
   echo "\n <!-- filters -->\n";
   echo " <li class='filters'>\n";
   echo "  ".$modal_filter->link(api_icon("icon-filter"))."\n";
   echo " </li><!-- /filters -->\n\n";
  }
  // show tabs
  if(is_array($this->nt_array)){
   // show field
   foreach($this->nt_array as $nt){
    if(!is_object($nt)){continue;}
    // check dropdown menu
    if(is_array($nt->dropdown)){
     $dropdown=TRUE;
     $nt->label.=" <b class='caret'></b>";
    }else{$dropdown=FALSE;}
    // check active
    $active=FALSE;
    if(strlen($nt->url)==0){$nt->url="#";}
    echo " <li";
    if(substr($nt->url,0,(strpos($nt->url,"?")>0)?strpos($nt->url,"?"):strlen($nt->url))==api_baseName()){
     $active=TRUE;
     parse_str(parse_url($nt->url,PHP_URL_QUERY),$gets);
     if(count($gets)>0){
      foreach($gets as $key=>$value){
       if($_GET[$key]<>$value){$active=FALSE;}
      }
     }
    }
    // check dropdown active
    if($dropdown){
     foreach($nt->dropdown as $ntd){
      if(substr($ntd->url,0,(strpos($ntd->url,"?")>0)?strpos($ntd->url,"?"):strlen($ntd->url))==api_baseName()){
       $active=TRUE;
       break;
      }
     }
    }
    // check active disabled and dropdown class
    echo " class='";
    if($active){echo "active ";}
    if($dropdown){echo "dropdown ";}
    if(!$nt->enabled){echo "disabled ";}
    echo $nt->class."'>";
    // check url
    if($dropdown){echo "<a class='dropdown-toggle' data-toggle='dropdown' href='#'";}
     elseif($active || !$nt->enabled){echo "<a href='#'";}
     else{echo "<a href='".$nt->url.$nt->get."' target='".$nt->target."'";}
    if(strlen($nt->confirm)&&$nt->enabled){echo " onClick=\"return confirm('".$nt->confirm."')\"";}
    // show label
    echo ">".$nt->label."</a>";
    // dropdown items
    if($dropdown){
     echo "\n  <ul class='dropdown-menu'>\n";
     foreach($nt->dropdown as $ntd){
      // header
      if($ntd->typology=="subtab-header"){echo "<li class='nav-header'>".$ntd->label."</li>\n";continue;}
      // divider
      if($ntd->typology=="subtab-divider"){echo "<li class='divider'></li>\n";continue;}
      // subtab
      echo "   <li";
      if($ntd->enabled){
       echo "><a href='".$ntd->url.$ntd->get."' target='".$ntd->target."'";
      }else{
       echo " class='disabled ".$ntd->class."'><a href='#'";
      }
      if(strlen($ntd->confirm)&&$ntd->enabled){echo " onClick=\"return confirm('".$ntd->confirm."')\"";}
      echo ">".$ntd->label."</a></li>\n";
     }
     echo "  </ul>\n ";
    }
    echo "</li>\n";
   }
  }// search bar
  if($this->search){
   echo " <!-- search -->\n";
   echo " <form action='".api_baseName()."' method='get' name='nav-search'>\n";
   echo "  <li class='search pull-right'>\n";
   echo "   <input type='hidden' name='filtered' value='1'>\n";
   // get params
   if(count($this->get)>0){
    $gets=NULL;
    foreach($this->get as $get){
     $gets="&".$get."=".$_GET[$get];
     echo "   <input type='hidden' name='".$get."' value='".$_GET[$get]."'>\n";
    }
    $gets=substr($gets,1);
   }
   // show input
   echo "   <div class='input-append'>\n";
   //       <<<<<<<<<<<<<<------------------------------------------------------ FILTRI DISATTIVATI DALLA RICERCA
   // load filters
   /*foreach($this->filters as $filter){
    // switch filter type
    switch($filter->type){
     // multiple filters have array results
     case "multiselect":
      if(is_array($_GET[$filter->name])){
       foreach($_GET[$filter->name] as $g_option){
        echo "    <input type='hidden' name='".$filter->name."[]' value='".$g_option."'>\n";
       }
      }
      break;
     // range values
     case "range":
     case "daterange":
     case "datetimerange":
      if($_GET[$filter->name."_from"]<>NULL){echo "    <input type='hidden' name='".$filter->name."_from' value='".$_GET[$filter->name."_from"]."'>\n";}
      if($_GET[$filter->name."_to"]<>NULL){echo "    <input type='hidden' name='".$filter->name."_to' value='".$_GET[$filter->name."_to"]."'>\n";}
      break;
     default:
      if($_GET[$filter->name]<>NULL){echo "    <input type='hidden' name='".$filter->name."' value='".$_GET[$filter->name]."'>\n";}
    }
   }*/
   echo "    <input type='text' name='q' class='input-large' placeholder='".ucfirst(api_text("search"))."' value='".$_GET['q']."'>\n";
   if($_GET['q']<>NULL){echo "    <a class='btn' href='".api_baseName()."?nav-search-submit=reset".$this->filtersGet()."&q='><i class='icon-remove-sign'></i></a>\n";}
   echo "    <button type='submit' name='nav-search-submit' class='btn'><i class='icon-search'></i></button>\n";
   echo "   </div>\n  </li>\n </form><!-- /search -->\n";
  }
  // close navigation
  echo "</ul><!-- /navigation-tabs -->\n\n";
  // filters scripts
  if(count($this->filters)>0){
   $modal_filter->render();
  }
  return TRUE;
 }

}


/* -------------------------------------------------------------------------- *\
|* -[ Pagination ]----------------------------------------------------------- *|
\* -------------------------------------------------------------------------- */

class str_pagination{

 protected $url;
 protected $total;
 protected $limit;
 protected $page;
 protected $class;
 protected $class_ul;
 protected $class_li;
 protected $class_li_active;
 protected $class_li_disabled;

 /* -[ Construct ]----------------------------------------------------------- */
 // @string $table : the table name
 // @string $where : the query condition
 // @string $get : url of the page
 // @integet $limit : number of items for page
 // @string $class : pagination css class
 // @string $class_ul : pagination ul css class
 // @string $class_li : pagination ul li css class
 // @string $class_li_active : pagination ul li of current page css class
 // @string $class_li_disabled : pagination ul li of disabled pages css class
 public function __construct($table=NULL,$where=NULL,$get=NULL,$limit=20,$class="pagination-small pagination-right",$class_ul="",$class_li="",$class_li_active="active",$class_li_disabled="disabled"){
  if($table==NULL || !is_int($limit)){return FALSE;}
  // acquire variables
  if(isset($_GET['l'])){$limit=$_GET['l'];}
  $g_page=$_GET['p'];
  if(!$g_page){$g_page=1;}
  // count total rows
  if($where<>NULL){
   //$total=$GLOBALS['db']->countOf($table,$where);
   $total=0;
   $results=$GLOBALS['db']->query("SELECT * FROM ".$table." WHERE ".$where);
   while($result=$GLOBALS['db']->fetchNextObject($results)){$total++;}
  }else{
   //$total=$GLOBALS['db']->countOfAll($table);
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

 /* -[ Query Limit ]--------------------------------------------------------- */
 function queryLimit(){
  if($this->limit){
   $start=($this->page-1)*$this->limit;
   return " LIMIT ".$start.",".$this->limit;
  }else{
   return NULL;
  }
 }

 /* -[ Render ]------------------------------------------------------------- */
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


/* -------------------------------------------------------------------------- *\
|* -[ Table ]---------------------------------------------------------------- *|
\* -------------------------------------------------------------------------- */

class str_table{

 private $current_row=0;

 protected $unvalued;
 protected $sortable;
 protected $get;
 protected $class;
 protected $th_array;
 protected $tr_array;

 /* -[ Construct ]----------------------------------------------------------- */
 // @string $unvalued : text to show if no results
 // @boolean $sortable : show headers sortable link (true) or not
 // @string $get : additional get parameters for sortable link (&key=value)
 // @string $class : table css class
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

 /* -[ Add Header ]---------------------------------------------------------- */
 // @string $name : column header name
 // @string $class : column header css class
 // @string $width : column header width
 // @integer $colspan : column span
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

 /* -[ Add Row ]------------------------------------------------------------- */
 // @string $class : row css class
 public function addRow($class=NULL){
  $this->current_row++;
  $this->tr_array[$this->current_row]=new stdClass();
  $this->tr_array[$this->current_row]->class=$class;
  $this->tr_array[$this->current_row]->fields=array();
  return TRUE;
 }

 /* -[ Add Field ]----------------------------------------------------------- */
 // @string $content : field content
 // @string $class : field css class
 // @integer $colspan : column span
 function addField($content,$class=NULL,$colspan=1){
  $td=new stdClass();
  $td->content=$content;
  $td->class=$class;
  $td->colspan=$colspan;
  $this->tr_array[$this->current_row]->fields[]=$td;
  return TRUE;
 }

 /* -[ Count ]--------------------------------------------------------------- */
 public function count(){
  return $this->current_row;
 }

 /* -[ Render ]-------------------------------------------------------------- */
 // @boolean $echo : echo result (true) or return
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


/* -------------------------------------------------------------------------- *\
|* -[ Form ]----------------------------------------------------------------- *|
\* -------------------------------------------------------------------------- */

class str_form{

 private $current_field;

 protected $action;
 protected $method;
 protected $name;
 protected $class;
 protected $splitted;
 protected $controlGroup;

 protected $ff_array;
 protected $fc_array;

 /* -[ Construct ]----------------------------------------------------------- */
 // @string $action : form action url
 // @boolean $method : get, post
 // @string $name : form name
 // @string $class : form css class
 // @boolean $controlGroup : show control-group
 public function __construct($action,$method="get",$name="form",$class="form-horizontal",$controlGroup=TRUE){
  if(strlen($action)==0 || !in_array(strtolower($method),array("get","post"))){return FALSE;}
  $this->action=$action;
  $this->method=$method;
  $this->name=$name;
  $this->class=$class;
  $this->splitted=0;
  $this->controlGroup=$controlGroup;
  $this->current_field=-1;
  $this->ff_array=array();
  $this->fc_array=array();
  return TRUE;
 }

 /* -[ Add Field ]----------------------------------------------------------- */
 // @string $type : hidden, text, password, checkbox, radio, select, multiselect, textarea, file, range, date, datetime, daterange, datetimerange
 // @string $name : name of the form input (spaces not allowed)
 // @string $label : label for the field
 // @string $value : default value
 // @string $class : input css class
 // @string $placeholder : placeholder message
 // @boolean $disabled : disable input field (true) or not
 // @integer $rows : number of textarea rows
 // @string $append : append text
 // @boolean $readonly : readonly input field (true) or not
 function addField($type,$name,$label=NULL,$value=NULL,$class=NULL,$placeholder=NULL,$disabled=FALSE,$rows=7,$append=NULL,$readonly=FALSE){
  if(!in_array(strtolower($type),array("hidden","text","password","checkbox","radio","select","multiselect","textarea","file","slider","range","date","datetime","daterange","datetimerange"))){return FALSE;}
  if(strlen($name)==0){return FALSE;}
  $this->current_field++;
  $ff=new stdClass();
  $ff->type=$type;
  $ff->name=$name;
  $ff->label=$label;
  $ff->value=$value;
  $ff->class=$class;
  $ff->placeholder=$placeholder;
  $ff->disabled=$disabled;
  $ff->rows=$rows;
  $ff->append=$append;
  $ff->options=NULL;
  $ff->readonly=$readonly;
  if($type=="range" || $type=="daterange"){
   if($placeholder<>NULL && !is_array($placeholder)){$ff->placeholder=array($placeholder);}
   if($ff->placeholder[0]==NULL){$ff->placeholder[0]=api_text("form-range-from");}
   if($ff->placeholder[1]==NULL){$ff->placeholder[1]=api_text("form-range-to");}
   if(!is_array($value)){$ff->value=array($value);}
  }
  $this->ff_array[$this->current_field]=$ff;
  return TRUE;
 }

 /* -[ Add Custom Field ]---------------------------------------------------- */
 // @string $label : label for the field
 // @string $source : source code of controls
 function addCustomField($label=NULL,$source=NULL){
  if(strlen($source)==0){return FALSE;}
  $this->current_field++;
  $ff=new stdClass();
  $ff->type="custom";
  $ff->label=$label;
  $ff->source=$source;
  $ff->options=NULL;
  $this->ff_array[$this->current_field]=$ff;
  return TRUE;
 }

 /* -[ Add Field Options ]--------------------------------------------------- */
 // @string $value : option value
 // @string $label : label for the field option
 // @boolean $checked : checked or selected field option (true) or not
 // @boolean $disabled : disable field option (true) or not
 function addFieldOption($value,$label,$checked=FALSE,$disabled=FALSE){
  if(strlen($label)==0){return FALSE;}
  $fo=new stdClass();
  $fo->value=$value;
  $fo->label=$label;
  $fo->checked=$checked;
  $fo->disabled=$disabled;
  if(!is_array($this->ff_array[$this->current_field]->options)){$this->ff_array[$this->current_field]->options=array();}
  $this->ff_array[$this->current_field]->options[]=$fo;
  return TRUE;
 }

 /* -[ Form Control ]-------------------------------------------------------- */
 // @string $type : submit, reset, button, link
 // @string $label : label for the control
 // @string $class : input css class
 // @string $url : link url
 // @string $confirm : confirmation message to approve if not null
 // @boolean $disabled : disable control (true) or not
 function addControl($type,$label,$class=NULL,$url=NULL,$confirm=NULL,$disabled=FALSE){
  if(strlen($type)==0 || strlen($label)==0){return FALSE;}
  $fc=new stdClass();
  $fc->type=$type;
  $fc->label=$label;
  $fc->class=$class;
  $fc->url=$url;
  $fc->confirm=addslashes($confirm);
  $fc->disabled=$disabled;
  $this->fc_array[]=$fc;
  return TRUE;
 }

 /* -[ Add Title ]----------------------------------------------------------- */
 // @string $title : title to show
 // @string $class : separator css class
 function addTitle($title,$class=NULL){
  if($title==NULL){return FALSE;}
  $this->current_field++;
  $ff=new stdClass();
  $ff->type="title";
  $ff->title=$title;
  $ff->class=$class;
  $ff->options=NULL;
  $this->ff_array[$this->current_field]=$ff;
  return TRUE;
 }

 /* -[ Add Separator ]------------------------------------------------------- */
 // @string $tag : hr, br
 // @string $class : separator css class
 function addSeparator($tag="hr",$class=NULL){
  if(!in_array(strtolower($tag),array("hr","br"))){return FALSE;}
  $this->current_field++;
  $ff=new stdClass();
  $ff->type="separator";
  $ff->tag=$tag;
  $ff->class=$class;
  $ff->options=NULL;
  $this->ff_array[$this->current_field]=$ff;
  return TRUE;
 }

 /* -[ Open Split ]---------------------------------------------------------- */
 function splitOpen(){
  $this->current_field++;
  $ff=new stdClass();
  $ff->type="splitOpen";
  $this->ff_array[$this->current_field]=$ff;
  return TRUE;
 }

 /* -[ Add Split ]----------------------------------------------------------- */
 function splitSpan(){
  if($this->splitted==3){return FALSE;}
  $this->splitted++;
  $this->current_field++;
  $ff=new stdClass();
  $ff->type="splitSpan";
  $this->ff_array[$this->current_field]=$ff;
  return TRUE;
 }

 /* -[ Close Split ]--------------------------------------------------------- */
 function splitClose(){
  $this->current_field++;
  $ff=new stdClass();
  $ff->type="splitClose";
  $this->ff_array[$this->current_field]=$ff;
  return TRUE;
 }

 /* -[ Render ]-------------------------------------------------------------- */
 // @boolean $echo : echo result (true) or return
 function render($echo=TRUE){
  $return=NULL;
  // check splits
  $split_open=FALSE;
  if($this->splitted>0){
   // calculate split
   $span=12/($this->splitted+1);
  }
  // open form
  $return.="<!-- form-".$this->name." -->\n";
  $return.="<form action='".$this->action."' method='".$this->method."' name='".$this->name."' id='form_".$this->name."' class='".$this->class."' enctype='multipart/form-data'>\n\n";
  /*
  // open split
  if($this->splitted>0){
   //$GLOBALS['html']->split_open();
   //$GLOBALS['html']->split_span($span);
   $return.="<!-- row-fluid -->\n";
   $return.="<div class='row-fluid'>\n";
   $split_open=TRUE;
   $return.=" <div class='span".$span."'>\n\n";
  }*/
  // show field
  foreach($this->ff_array as $index=>$ff){
   $options=FALSE;
   // check for splitOpen
   if($ff->type=="splitOpen"){
    $split_open=TRUE;
    $return.="<!-- row-fluid -->\n";
    $return.="<div class='row-fluid'>\n";
    $return.=" <div class='span".$span."'>\n\n";
   }
   // check for splitSpan
   if($ff->type=="splitSpan"){
    //$GLOBALS['html']->split_span($span);
    $return.=" </div><!-- /span".$this->columns." -->\n";
    $return.=" <div class='span".$span."'>\n\n";
    continue;
   }
   // check for splitClose
   if($ff->type=="splitClose"){
    //$GLOBALS['html']->split_close();
    if($split_open){
     $split_open=FALSE;
     $return.=" </div><!-- /span".$span." -->\n";
     $return.="</div><!-- /row-fluid -->\n\n";
    }
    continue;
   }
   // open group
   if($ff->type<>"separator" && $ff->type<>"title" && $ff->label<>NULL && $this->controlGroup){
    $return.="<div id='field_".$ff->name."' class='control-group'>\n";
   }
   // show label
   if($ff->label<>NULL && $this->controlGroup){
    $return.=" <label class='control-label'>".$ff->label."</label>\n";
    if($ff->type<>"custom"){$return.=" <div class='controls'>\n";}
   }
   // open append div
   if($ff->append<>NULL){$return.="  <div class='input-append'>\n";}
   // show input
   switch(strtolower($ff->type)){
    // hidden, text, password
    case "hidden":
    case "text":
    case "password":
     if(!$ff->label){$return.="  ";}
     $return.="  <input type='".$ff->type."' name='".$ff->name."' id='".$this->name."_input_".$ff->name."' class='".$ff->class."' placeholder=\"".$ff->placeholder."\" value=\"".$ff->value."\"";
     if($ff->disabled){$return.=" disabled='disabled'";}
     if($ff->readonly){$return.=" readonly='readonly'";}
     $return.=">\n";
     if(!$ff->label){$return.="\n";}
     break;
    // checkbox, radio
    case "checkbox":
    case "radio":
     $options=TRUE;
     break;
    // select
    case "select":
     $options=TRUE;
     // open select
     $return.="  <select name='".$ff->name."' id='".$this->name."_input_".$ff->name."' class='".$ff->class."'";
     if($ff->disabled){$return.=" disabled='disabled'";}
     if($ff->readonly){$return.=" readonly='readonly'";}
     $return.=">\n";
     break;
    // multiselect
    case "multiselect":
     $options=TRUE;
     // open multiselect
     $return.="  <select name='".$ff->name."[]' id='".$this->name."_input_".$ff->name."' class='".$ff->class."' multiple='multiple'";
     if($ff->disabled){$return.=" disabled='disabled'";}
     if($ff->readonly){$return.=" readonly='readonly'";}
     $return.=">\n";
     break;
    // textarea
    case "textarea":
     $return.="  <textarea name='".$ff->name."' id='".$this->name."_input_".$ff->name."' rows='".$ff->rows."' class='".$ff->class."' placeholder=\"".$ff->placeholder."\"";
     if($ff->disabled){$return.=" disabled='disabled'";}
     if($ff->readonly){$return.=" readonly='readonly'";}
     $return.=">".$ff->value."</textarea>\n";
     break;
    // file
    case "file":
     if($ff->placeholder==NULL){$ff->placeholder="Select a file to upload";}
     $return.="  <input type='file' id='file_".$index."' name='".$ff->name."' style='display:none'>\n";
     $return.="  <div class='input-append'>\n";
     $return.="   <input type='text' id='file_".$index."_show' class='".$ff->class."' placeholder=\"".$ff->placeholder."\" onDblClick=\"$('input[id=file_".$index."]').click();\" readonly>\n";
     $return.="   <a class='btn' onClick=\"$('input[id=file_".$index."]').click();\">Sfoglia</a>\n";
     $return.="  </div>\n";
     break;
    // range
    case "range":
     if(!$ff->label){$return.="  ";}
     $return.="  <input type='text' name='".$ff->name."_from' id='".$this->name."_input_".$ff->name."'_from class='input-small ".$ff->class."' placeholder=\"".$ff->placeholder[0]."\" value=\"".$ff->value[0]."\"";
     if($ff->disabled){$return.=" disabled='disabled'";}
     if($ff->readonly){$return.=" readonly='readonly'";}
     $return.="> &nbsp;\n";
     $return.="  <input type='text' name='".$ff->name."_to' id='".$this->name."_input_".$ff->name."'_to class='input-small ".$ff->class."' placeholder=\"".$ff->placeholder[1]."\" value=\"".$ff->value[1]."\"";
     if($ff->disabled){$return.=" disabled='disabled'";}
     if($ff->readonly){$return.=" readonly='readonly'";}
     $return.=">\n";
     if(!$ff->label){$return.="\n";}
     break;
    // slider
    case "slider":
     if(!$ff->label){$return.="  ";}
     $return.="  <input type='text' name='".$ff->name."' id='".$this->name."_input_".$ff->name."' value='".$ff->value."' data-slider-min='0' data-slider-max='100' data-slider-value='".$ff->value."' class='".$ff->class."'";
     if($ff->disabled){$return.=" disabled='disabled'";}
     if($ff->readonly){$return.=" readonly='readonly'";}
     $return.=">\n";
     if(!$ff->label){$return.="\n";}
     break;
    // date and datetime
    case "date":
    case "datetime":
     if($ff->type=="date"){$name="date";$format="yyyy-MM-dd";}
     if($ff->type=="datetime"){$name="datetime";$format="yyyy-MM-dd hh:mm";}
     $return.="  <div id='".$this->name."_".$name."_".$index."' class='input-append'>\n";
     $return.="   <input type='text' name='".$ff->name."' id='".$this->name."_input_".$ff->name."' data-format='".$format."' readonly='readonly' class='".$ff->class."' placeholder=\"".$ff->placeholder."\" value=\"".$ff->value."\">\n";
     $return.="   <span class='add-on'><i data-time-icon='icon-time' data-date-icon='icon-calendar'></i></span>\n";
     $return.="  </div>\n";
     break;
    // daterange and datetimerange
    case "daterange":
    case "datetimerange":
     if($ff->type=="daterange"){$name="daterange";$format="yyyy-MM-dd";$size="input-small";}
     if($ff->type=="datetimerange"){$name="datetimerange";$format="yyyy-MM-dd hh:mm";$size="input-medium";}
     $return.="  <div id='".$this->name."_".$name."_from_".$index."' class='input-append'>\n";
     $return.="   <input type='text' name='".$ff->name."_from' id='".$this->name."_input_".$ff->name."_from' data-format='".$format."' readonly='readonly' class='".$size." ".$ff->class."' placeholder=\"".$ff->placeholder[0]."\" value=\"".$ff->value[0]."\">\n";
     $return.="   <span class='add-on'><i data-time-icon='icon-time' data-date-icon='icon-calendar'></i></span>\n";
     $return.="  </div>\n&nbsp;\n";
     $return.="  <div id='".$this->name."_".$name."_to_".$index."' class='input-append'>\n";
     $return.="   <input type='text' name='".$ff->name."_to' id='".$this->name."_input_".$ff->name."_to' data-format='".$format."' readonly='readonly' class='".$size." ".$ff->class."' placeholder=\"".$ff->placeholder[1]."\" value=\"".$ff->value[1]."\">\n";
     $return.="   <span class='add-on'><i data-time-icon='icon-time' data-date-icon='icon-calendar'></i></span>\n";
     $return.="  </div>\n";
     break;
    // custom
    case "custom":
     $return.=$ff->source;
     break;
    // separators
    case "separator":
     $return.="<".$ff->tag." class='".$ff->class."'>\n\n";
     break;
    // title
    case "title":
     $return.="<h5 class='".$ff->class."'>".$ff->title."</h5>\n\n";
     break;
   }
   // show options
   if($options){
    // check for array of options
    if(is_array($ff->options)){
     // show option
     foreach($ff->options as $index=>$fo){
      switch(strtolower($ff->type)){
       // show checkbox option
       case "checkbox":
        $return.="  <label class='".$ff->type." ".$ff->class."'>";
        $return.="<input type='".$ff->type."' name='".$ff->name."' id='".$this->name."_input_".$ff->name."_option_".$fo->value."' value=\"".$fo->value."\"";
        if($fo->checked){$return.=" checked='checked'";}
        if($fo->disabled){$return.=" disabled='disabled'";}
        $return.=">".$fo->label."</label>\n";
        break;
       // show radio option
       case "radio":
        $return.="  <label class='".$ff->type." ".$ff->class."'>";
        $return.="<input type='".$ff->type."' name='".$ff->name."' id='".$this->name."_input_".$ff->name."_option_".$fo->value."' value=\"".$fo->value."\"";
        if($fo->checked){$return.=" checked='checked'";}
        if($fo->disabled){$return.=" disabled='disabled'";}
        $return.=">".$fo->label."</label>\n";
        break;
       // show select or multiselect option
       case "select":
       case "multiselect":
        $return.="   <option value=\"".$fo->value."\" id='".$this->name."_input_".$ff->name."_option_".$fo->value."'";
        if($fo->checked){$return.=" selected='selected'";}
        $return.=">".$fo->label."</option>\n";
        break;
      }
     }
    }
   }
   // close select
   if(strtolower($ff->type)=="select"){$return.="  </select>\n";}
   // close multiselect
   if(strtolower($ff->type)=="multiselect"){
    $return.="  </select>\n";
    $return.="  <br>\n ".api_text("form-select");
    $return.=" <a href='#' onClick='".$this->name."_selectToggle(\"".$this->name."_input_".$ff->name."\",true)'>".api_text("form-select-all")."</a>,";
    $return.=" <a href='#' onClick='".$this->name."_selectToggle(\"".$this->name."_input_".$ff->name."\",false)'>".api_text("form-select-none")."</a>\n";
   }
   // show and close append div
   if($ff->append<>NULL){
    if(substr($ff->append,0,1)<>"<" && strpos($ff->append,"input")===FALSE){
     $return.="  <span class='add-on'>".$ff->append."</span>\n";
    }else{
     $return.="  ".$ff->append."\n";
    }
    $return.=" </div><!-- /input-append -->\n";
   }
   // close controls
   if($ff->label<>NULL && $this->controlGroup && $ff->type<>"custom"){$return.=" </div><!-- /controls -->\n";}
   // close group
   if($ff->type<>"separator" && $ff->label<>NULL && $this->controlGroup){$return.="</div><!-- /control-group -->\n\n";}
   // file script
   if(strtolower($ff->type)=="file"){
    $return.="<script type='text/javascript'>\n";
    $return.=" $('input[id=file_".$index."]').change(function(){\n";
    $return.="  $('#file_".$index."_show').val($(this).val());\n";
    $return.=" });\n";
    $return.="</script>\n\n";
   }
   // date, datetime, daterange and datetimerange script
   if(strtolower($ff->type)=="date" || strtolower($ff->type)=="datetime" || strtolower($ff->type)=="daterange" || strtolower($ff->type)=="datetimerange"){
    $return.="<script type='text/javascript'>\n";
    $return.=" $(document).ready(function(){\n";
    // date and datetime
    if(strtolower($ff->type)=="date" || strtolower($ff->type)=="datetime"){
     if(strtolower($ff->type)=="date"){$name="date";$param="pickTime:false";}
     if(strtolower($ff->type)=="datetime"){$name="datetime";$param="pickSeconds:false";}
     $return.="  $('#".$this->name."_".$name."_".$index."').datetimepicker({ ".$param." });\n";
     $return.="  $('#".$this->name."_input_".$ff->name."').dblclick(function(){ $(this).val('') });\n";
    }
    // daterange and datetimerange
    if(strtolower($ff->type)=="daterange" || strtolower($ff->type)=="datetimerange"){
     if(strtolower($ff->type)=="daterange"){$name="daterange";$param="pickTime:false";}
     if(strtolower($ff->type)=="datetimerange"){$name="datetimerange";$param="pickSeconds:false";}
     $return.="  $('#".$this->name."_".$name."_from_".$index."').datetimepicker({ ".$param." });\n";
     $return.="  $('#".$this->name."_".$name."_to_".$index."').datetimepicker({ ".$param." });\n";
     $return.="  $('#".$this->name."_input_".$ff->name."_from').dblclick(function(){ $(this).val('') });\n";
     $return.="  $('#".$this->name."_input_".$ff->name."_to').dblclick(function(){ $(this).val('') });\n";
    }
    $return.=" });\n";
    $return.="</script>\n\n";
   }
   // slider script
   if(strtolower($ff->type)=="slider"){
    $return.="<script type='text/javascript'>\n";
    $return.=" $(document).ready(function(){\n";
    $return.="  $('input[id=".$this->name."_input_".$ff->name."]').slider();\n";
    $return.=" });\n";
    $return.="</script>\n\n";
   }
   // multiselect scripts
   if(strtolower($ff->type)=="multiselect"){
    //------
   }
  }
  // multiselect scripts
  $return.="<script type='text/javascript'>\n";
  $return.=" function ".$this->name."_selectToggle(name,selected){\n";
  $return.="  $('#'+name+' option').each(function(){ $(this).attr('selected',selected); });\n";
  $return.=" };\n";
  $return.="</script>\n\n";
  // show controls
  if(is_array($this->fc_array)){
   if($this->controlGroup){
    // open group
    $return.="<div class='control-group'>\n";
    $return.=" <div class='controls'>\n";
   }
   // show control
   foreach($this->fc_array as $index=>$fc){
    // check disabled
    if($fc->disabled){$disabled=" disabled='disabled'";$fc->url="#";$fc->confirm=NULL;}else{$disabled=NULL;}
    // check confirm
    if(strlen($fc->confirm)){$confirm=" onClick=\"return confirm('".$fc->confirm."')\"";}else{$confirm=NULL;}
    // switch typology
    switch(strtolower($fc->type)){
     // submit
     case "submit":
      $return.="  <input type='submit' name='".$this->name."_submit'";
      $return.=" id='".$this->name."_control_submit' class='btn btn-primary ".$fc->class."'";
      $return.=$disabled.$confirm." value=\"".$fc->label."\">\n";
      break;
     // reset
     case "reset":
      $return.="  <input type='reset' name='".$this->name."_reset'";
      $return.=" id='".$this->name."_control_reset' class='btn ".$fc->class."'";
      $return.=$disabled.$confirm." value=\"".$fc->label."\">\n";
      break;
     // button, link
     case "button":
     case "link":
      $return.="  <a href='".$fc->url."' ";
      if(strtolower($fc->type)=="button"){$return.="class='btn ".$fc->class."'";}
       else{$return.="class='".$fc->class."'";}
      $return.=" id='".$this->name."_control_".$index."'".$disabled.$confirm.">".$fc->label."</a>\n";
      break;
    }
   }
  }
  // close group
  if($this->controlGroup){$return.=" </div>\n</div>\n\n";}
  // close split
  /*if($this->splitted>0){
   //$GLOBALS['html']->split_close();
   if($split_open){
    $return.="\n </div><!-- /span".$span." -->\n";
    $split_open=FALSE;
    $return.="</div><!-- /row-fluid -->\n\n";
   }
  }*/
  // close form
  $return.="</form><!-- /form-".$this->name." -->\n\n";
  if($echo){echo $return;return TRUE;}else{return $return;}
 }
}


/* -------------------------------------------------------------------------- *\
|* -[ Modal window ]--------------------------------------------------------- *|
\* -------------------------------------------------------------------------- */

class str_modal{

 protected $id;
 protected $header;
 protected $body;
 protected $footer;
 protected $class;

 /* -[ Contruct ]------------------------------------------------------------ */
 // @string $id : id of the modal window
 // @string $header : header of the modal window
 // @string $body : body of the modal window
 // @string $footer : footer of the modal window
 // @string $class : modal window css class
 public function __construct($id,$class=NULL){
  if(strlen($id)==0){return FALSE;}
  $this->id=$id;
  $this->class=$class;
  return TRUE;
 }

 /* -[ Modal Window Link ]--------------------------------------------------- */
 // @string $label : label of the link
 // @string $class : modal link css class
 // @string $style : style css
 function link($label,$class=NULL,$style=NULL){
  if(strlen($label)==0){return FALSE;}
  return "<a href='#modal_".$this->id."' data-toggle='modal' class='".$class."' id='modal-link_".$this->id."' style='".$style."'>".$label."</a>";
 }

 /* -[ Modal Window Header ]------------------------------------------------- */
 // @string $label : label of the header
 function header($label){
  if(strlen($label)==0){return FALSE;}
  $this->header=$label;
  return TRUE;
 }

 /* -[ Modal Window Body ]--------------------------------------------------- */
 // @string $content : content of the body
 function body($content){
  if(strlen($content)==0){return FALSE;}
  $this->body=$content;
  return TRUE;
 }

 /* -[ Modal Window Footer ]------------------------------------------------- */
 // @string $content : content of the footer
 function footer($content){
  if(strlen($content)==0){return FALSE;}
  $this->footer=$content;
  return TRUE;
 }

 /* -[ Render ]-------------------------------------------------------------- */
 // @boolean $echo : echo result (true) or return
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


/* -------------------------------------------------------------------------- *\
|* -[ Dynamic List ]--------------------------------------------------------- *|
\* -------------------------------------------------------------------------- */

class str_dl{

 protected $class;
 protected $separator;
 protected $elements_array;

 /* -[ Contruct ]------------------------------------------------------------ */
 // @string $separator : default elements separator null, hr, br
 // @string $class : dynamic list css class
 public function __construct($separator=NULL,$class=NULL){
  if(!in_array(strtolower($separator),array(NULL,"hr","br"))){return FALSE;}
  $this->class=$class;
  $this->separator=$separator;
  $this->elements_array=array();
  return TRUE;
 }

 /* -[ Add Element ]--------------------------------------------------------- */
 // @string $label : label of the dynamic list
 // @string $label : value of the dynamic list
 // @string $separator : null, hr, br
 // @string $class : element css class
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

 /* -[ Add Separator ]------------------------------------------------------- */
 // @string $separator : null, hr, br
 // @string $class : element css class
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

 /* -[ Render ]-------------------------------------------------------------- */
 // @boolean $echo : echo result (true) or return
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


/* -------------------------------------------------------------------------- *\
|* -[ Flag Well ]------------------------------------------------------------ *|
\* -------------------------------------------------------------------------- */

class str_flagwell{

 protected $title;
 protected $class;
 protected $content;

 /* -[ Contruct ]------------------------------------------------------------ */
 // @string $label : label for the flag well
 // @string $class : flag well css class
 public function __construct($title,$class=NULL){
  if(strlen($title)==0){return FALSE;}
  $this->title=$title;
  $this->class=$class;
  return TRUE;
 }

 /* -[ Flag Well Content ]--------------------------------------------------- */
 // @string $content : content of the well
 public function content($content){
  if(strlen($content)==0){return FALSE;}
  $this->content=$content;
  return TRUE;
 }

 /* -[ Render ]-------------------------------------------------------------- */
 // @boolean $echo : echo result (true) or return
 public function render($echo=TRUE){
  $return="\n<!-- flag-well -->\n";
  $return.="<div class='flag-well ".$this->class."'>\n";
  $return.=" <span class='title'>".$this->title."</span>\n";
  $return.=" <div class='flag-well-content'>\n".$this->content."\n </div>\n";
  $return.="</div><!-- /flag-well -->\n";
  if($echo){echo $return;return TRUE;}else{return $return;}
 }

}


/* -------------------------------------------------------------------------- *\
|* -[ Accordion ]-------------------------------------------------------- *|
\* -------------------------------------------------------------------------- */

class str_accordion{

 protected $id;
 protected $class;
 protected $elements_array;

 /* -[ Contruct ]------------------------------------------------------------ */
 // @string $separator : default elements separator null, hr, br
 // @string $class : dynamic list css class
 public function __construct($class=NULL){
  $this->id="accordion_".rand(10000,99999);
  $this->class=$class;
  $this->elements_array=array();
  return TRUE;
 }

 /* -[ Add Element ]--------------------------------------------------------- */
 // @string $label : label of the dynamic list
 // @string $content : value of the dynamic list
 // @string $open : default open if true, close if false
 // @string $class : element css class
 public function addElement($label,$content,$open=FALSE,$class=NULL,$subLabel=NULL){
  if(!$content){$content="&nbsp;";}
  $element=new stdClass();
  $element->label=$label;
  $element->subLabel=$subLabel;
  $element->content=$content;
  $element->open=$open;
  $element->class=$class;
  $this->elements_array[]=$element;
  return TRUE;
 }

 /* -[ Render ]-------------------------------------------------------------- */
 // @boolean $echo : echo result (true) or return
 public function render($echo=TRUE){

  $return="\n<!-- accordion -->\n";
  $return.="<div class='accordion ".$this->class."' id='".$this->id."'>\n";

  foreach($this->elements_array as $index=>$element){

   if($element->open){$openClass="in ";}else{$openClass=NULL;}

   $return.=" <div class='accordion-group'>\n";
   $return.="  <div class='accordion-heading'>\n";
   $return.="   <a href='#accordion_collapse_".$index."' class='accordion-toggle' data-toggle='collapse' data-parent='".$this->id."'>".$element->label."</a>\n";

   //if($element->subLabel){$return.="<p class='accordion-toggle'>".$element->subLabel."</p>\n";}
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


/* -------------------------------------------------------------------------- *\
|* -[ Page Splitted ]-------------------------------------------------------- *|
\* -------------------------------------------------------------------------- */

class str_splitted{

 protected $class;
 protected $columns;
 protected $spans_array;

 /* -[ Contruct ]------------------------------------------------------------ */
  // @string $class : dynamic list css class
 public function __construct($class=NULL){
  $this->class=$class;
  $this->columns=0;
  $this->spans_array=array();
  return TRUE;
 }

 /* -[ Add Split ]----------------------------------------------------------- */
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

 /* -[ Render ]-------------------------------------------------------------- */
 // @boolean $echo : echo result (true) or return
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

/* -------------------------------------------------------------------------- *\
|* -[ Tabbable ]------------------------------------------------------------- *|
\* -------------------------------------------------------------------------- */

/**
 * Tabbable structure
 */
class str_tabbable{

 private $current_tab;

 protected $class;
 protected $position;
 protected $selected;
 protected $nt_array;

 /**
 * Tabbable class
 *
 * @param string $position tabbable position (top, right, bottom, left)
 * @param string $class tabbable css class
 * @return object tabbable object
 */
 public function __construct($position="top",$class=NULL){
  $this->class=$class;
  $this->position=$position;
  $this->selected=0;
  $this->current_tab=-1;
  $this->nt_array=array();
 }

 /**
 * Add tab
 *
 * @param string $label label of the tab
 * @param string $content content of the tab
 * @param string $class tab css class
 * @param boolean $enabled enable the tab (true) or not
 * @param boolean $selected tab selected (true) or not
 * @return true|false
 */
 function addTab($label,$content,$class=NULL,$enabled=TRUE,$selected=FALSE){
  if(strlen($label)==0){return FALSE;}
  $nt=new stdClass();
  $nt->typology="tab";
  $nt->label=$label;
  $nt->content=$content;
  $nt->class=$class;
  $nt->enabled=$enabled;
  $this->current_tab++;
  if($selected){$this->selected=$this->current_tab;}
  $this->nt_array[$this->current_tab]=$nt;
  return TRUE;
 }

 /**
 * Renderize tabbable object
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
 * Renderize navigation
 */
 function render_navigation(){
  // open navigation
  echo " <!-- navigation-tabs -->\n";
  echo " <ul class='nav nav-tabs'>\n";
  // show tabs
  if(is_array($this->nt_array)){
   // show field
   foreach($this->nt_array as $key=>$nt){
    if(!is_object($nt)){continue;}
    echo "  <li class='";
    if($key==$this->selected){echo "active ";}
    if(!$nt->enabled){echo "disabled ";}
    echo $nt->class."'>";
    // check url
    if(!$nt->enabled){echo "<a href='#'";}
    else{echo "<a href='#tab".$key."' data-toggle='tab'";}
    // show label
    echo ">".$nt->label."</a>";
    echo "</li>\n";
   }
  }
  // close navigation
  echo " </ul><!-- /navigation-tabs -->\n\n";
  return TRUE;
 }

 /**
 * Renderize content
 */
 function render_content(){
  // open content
  echo " <!-- content-tabs -->\n";
  echo " <div class='tab-content'>\n";
  // show content tabs
  if(is_array($this->nt_array)){
   foreach($this->nt_array as $key=>$nt){
    if(!is_object($nt)){continue;}
    echo "  <div class='tab-pane ";
    if($key==$this->selected){echo "active ";}
    echo $nt->class."' id='tab".$key."'>";
    echo $nt->content;
    echo "</div>\n";
   }
  }
  // close content
  echo " </div><!-- /content-tabs -->\n\n";
  return TRUE;
 }

}


/* -------------------------------------------------------------------------- *\
|* -[ Sidebar ]-------------------------------------------------------------- *|
\* -------------------------------------------------------------------------- */

/**
 * Sidebar structure
 */
class str_sidebar{

 private $current_li;

 protected $class;
 protected $position;
 protected $li_array;

 /**
 * Sidebar class
 *
 * @param string $class sidebar css class (nav-list, nav-pills, nav-tabs)
 * @return object sidebar object
 */
 public function __construct($class="nav-list"){
  $this->class=$class;
  $this->position=$position;
  $this->current_li=-1;
  $this->li_array=array();
 }

 /**
 * Add item
 *
 * @param string $label label of the item
 * @param string $url link url
 * @param string $class item css class
 * @param boolean $enabled enable the navigation tab (true) or not
 * @param string $target target page _blank, _self, _parent, _top
 * @return true|false
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
 * Add header
 *
 * @param string $label label of the tab
 * @return true|false
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
 * Add divider
 *
 * @return true|false
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
 * @param boolean $echo echo result (true) or return
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