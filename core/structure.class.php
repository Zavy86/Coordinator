<?php

/* -[ Structure Classes ]---------------------------------------------------- */


/* -------------------------------------------------------------------------- *\
|* -[ Navigation ]----------------------------------------------------------- *|
\* -------------------------------------------------------------------------- */

class str_navigation{

 private $current_tab;

 protected $search;
 protected $get;
 protected $class;
 protected $nt_array;

 protected $filters;

 /* -[ Construct ]----------------------------------------------------------- */
 // @boolean $search : show search bar
 // @array $get : additional get parameters for search bar
 // @string $class : navigation css class
 public function __construct($search=FALSE,$get=NULL,$class=NULL){
  if($get<>NULL && !is_array($get)){$get=array($get);}
  $this->search=$search;
  $this->get=$get;
  $this->class=$class;
  $this->current_tab=-1;
  $this->nt_array=array();
  $this->filters=array();
  return TRUE;
 }

 /* -[ Add Tab ]------------------------------------------------------------- */
 // @string $label : label of the tab
 // @string $url : link url
 // @string $get : additional get parameters for link (&key=value)
 // @string $class : tab css class
 // @boolean $enabled : enable the navigation tab (true) or not
 // @string $target : target page _blank, _self, _parent, _top
 // @string $confirm : confirmation message to approve if not null
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

 /* -[ Add Sub Tab ]------------------------------------------------------------- */
 // @string $label : label of the tab
 // @string $url : link url
 // @string $get : additional get parameters for link (&key=value)
 // @string $class : sub tab css class
 // @boolean $enabled : enable the navigation tab (true) or not
 // @string $target : target page _blank, _self, _parent, _top
 // @string $confirm : confirmation message to approve if not null
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
  $nt->confirm=$confirm;
  if(!is_array($this->nt_array[$this->current_tab]->dropdown)){
   $this->nt_array[$this->current_tab]->dropdown=array();
  }
  $this->nt_array[$this->current_tab]->dropdown[]=$nt;
  return TRUE;
 }

 /* -[ Add SubTab Header ]--------------------------------------------------- */
 // @string $label : label of the tab
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

 /* -[ Add SubTab Divider ]-------------------------------------------------- */
 function addSubTabDivider(){
  $nt=new stdClass();
  $nt->typology="subtab-divider";
  if(!is_array($this->nt_array[$this->current_tab]->dropdown)){
   $this->nt_array[$this->current_tab]->dropdown=array();
  }
  $this->nt_array[$this->current_tab]->dropdown[]=$nt;
  return TRUE;
 }

 /* -[ Add Filter ]---------------------------------------------------------- */
 // @string $type : text, checkbox, radio, select, multiselect, range, date, datetime, daterange, datetimerange
 // @string $name : name of the filter input
 // @string $label : label of the filter
 // @array $options : array of options (value=>label)
 // @string $class : filter input css class
 // @string $placeholder : placeholder message
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

 /* -[ Filters Textual ]----------------------------------------------------- */
 // @string $unvalued : text to show if no filters
 function filtersText($unvalued=NULL){
  $text=NULL;
  foreach($this->filters as $filter){
   $value=NULL;
   // switch filter type
   switch($filter->type){
    // multiple filters have array results
    case "multiselect":
     $text_filter=NULL;
     if(count($filter->options)==count($_GET[$filter->name])){
      $value="Tutti";
     }else{
      if(is_array($_GET[$filter->name])){
       foreach($_GET[$filter->name] as $g_option){
        $text_filter.=", ".$filter->options[$g_option];
       }
      }
      $value=substr($text_filter,2);
     }
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
   if($value<>NULL){$text.=" <span class='label label-info'>".$filter->label." = ".$value."</span>";}
  }
  if($text<>NULL){
   $return="<p><span class='label'>".api_text("filters-filters").":</span> ".substr(str_replace("*","%",$text),1)."</p>\n";
  }else{
   if($unvalued<>NULL){$unvalued="<p><span class='label'>".api_text("filters-filters").":</span> <span class='label label-inverse'>".$unvalued."</span></p>\n";}
   $return=$unvalued;
  }
  return $return;
 }

 /* -[ Filters Get Format ]-------------------------------------------------- */
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
  return $return;
 }

 /* -[ Filters Query ]------------------------------------------------------- */
 // @string $unvalued : query to show if no filters
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

 /* -[ Filters Parameter Query ]--------------------------------------------- */
 // @string $parameter : parameter id
 // @string $unvalued : query to show if no filters
 // @string $field : rename query field
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

 /* -[ Render ]-------------------------------------------------------------- */
  function render(){
  // open navigation
  echo "<!-- navigation-tabs -->\n";
  echo "<ul class='nav nav-tabs ".$this->class."'>\n";
  // show filters
  if(count($this->filters)>0){
   // build filter form modal body
   $modal_filter_body=new str_form(api_baseName(),"get","filters");
   $modal_filter_body->addField("hidden","filtered",NULL,"1");
   foreach($this->filters as $filter){
    // filter with options
    if($filter->options<>NULL){
     $modal_filter_body->addField($filter->type,$filter->name,$filter->label,NULL,$filter->class,$filter->placeholder);
     foreach($filter->options as $value=>$label){
      $checked=FALSE;
      if(is_array($_GET[$filter->name])){
       foreach($_GET[$filter->name] as $g_option){
        if($g_option==$value){$checked=TRUE;}
       }
      }else{
       if($_GET[$filter->name]==$value){$checked=TRUE;}
      }
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
   $modal_filter_body->addControl("button",api_text("filters-reset"),NULL,api_baseName());
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
    if(strlen($nt->confirm)){echo " onClick=\"return confirm('".$nt->confirm."')\"";}
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
      if(strlen($ntd->confirm)){echo " onClick=\"return confirm('".$ntd->confirm."')\"";}
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
   // load filters
   foreach($this->filters as $filter){
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
   }
   echo "    <input type='text' name='q' class='input-large' placeholder='Ricerca' value='".$_GET['q']."'>\n";
   if($_GET['q']<>NULL){echo "    <a class='btn' href='".api_baseName()."?q=".$this->filtersGet()."'><i class='icon-remove-sign'></i></a>\n";}
   echo "    <button type='submit' name='nav-search-submit' class='btn'><i class='icon-search'></i></button>\n";
   echo "   </div>\n  </li>\n </form><!-- /search -->\n";
  }
  // close navigation
  echo "</ul><!-- /navigation-tabs -->\n\n";
  // filters scripts
  if(count($this->filters)>0){
   /*echo "<script type='text/javascript'>\n";
   echo " function selectToggle(index,selected){\n";
   echo "  $('#filters_input_'+index+' option').each(function(){ $(this).attr('selected',selected); });\n";
   echo " };\n";
   echo "</script>\n\n";*/
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
 // @string $table
 // @string $where
 // @string $get : url of the page
 // @integet $limit
 // @string $class : pagination css class
 // @string $class_ul : pagination ul css class
 // @string $class_li : pagination ul li css class
 // @string $class_li_active : pagination ul li of current page css class
 // @string $class_li_disabled : pagination ul li of disabled pages css class
 public function __construct($table=NULL,$where=NULL,$get=NULL,$limit=20,$class="pagination-small pagination-right",$class_ul="",$class_li="",$class_li_active="active",$class_li_disabled="disabled"){
  if($table==NULL || !is_int($limit)){return FALSE;}
  // acquire variables
  $g_limit=$_GET['l'];
  if($g_limit>0){$limit=$g_limit;}
  $g_page=$_GET['p'];
  if(!$g_page){$g_page=1;}
  // count total rows
  if($where<>NULL){
   $total=$GLOBALS['db']->countOf($table,$where);
  }else{
   $total=$GLOBALS['db']->countOfAll($table);
  }
  // build url
  $url=api_baseName()."?p={p}".$get;
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
  $start=($this->page-1)*$this->limit;
  return " LIMIT ".$start.",".$this->limit;
 }

 /* -[ Render ]------------------------------------------------------------- */
 function render(){
  if(!$this->total>0){return FALSE;}
  $adjacents="2";
  $prev=$this->page-1;
  $next=$this->page+1;
  $lastpage=ceil($this->total/$this->limit);
  $lpm1=$lastpage-1;
  if($lastpage>1){
   // open pavigation
   echo "<!-- pagination -->\n";
   echo "<div class='pagination ".$this->class."'>\n";
   echo " <ul class='".$this->class_ul."'>\n";
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
   echo " </ul>\n";
   echo "</div><!-- /pagination -->\n\n";
  }
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
 function addField($type,$name,$label=NULL,$value=NULL,$class=NULL,$placeholder=NULL,$disabled=FALSE,$rows=7,$append=NULL){
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
  $fc->confirm=$confirm;
  $fc->disabled=$disabled;
  $this->fc_array[]=$fc;
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

 /* -[ Add Split ]----------------------------------------------------------- */
 function addSplit(){
  if($this->splitted==3){return FALSE;}
  $this->splitted++;
  $this->current_field++;
  $ff=new stdClass();
  $ff->type="split";
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
  $return.="<form name='".$this->name."' action='".$this->action."' method='".$this->method."' class='".$this->class."' enctype='multipart/form-data'>\n\n";
  // open split
  if($this->splitted>0){
   //$GLOBALS['html']->split_open();
   //$GLOBALS['html']->split_span($span);
   $return.="<!-- row-fluid -->\n";
   $return.="<div class='row-fluid'>\n";
   $split_open=TRUE;
   $return.=" <div class='span".$span."'>\n\n";
  }
  // show field
  foreach($this->ff_array as $index=>$ff){
   $options=FALSE;
   // check for split
   if($ff->type=="split"){
    //$GLOBALS['html']->split_span($span);
    $return.="\n </div><!-- /span".$this->columns." -->\n";
    $return.=" <div class='span".$span."'>\n\n";
    continue;
   }
   // open group
   if($ff->type<>"separator" && $ff->label<>NULL && $this->controlGroup){
    $return.="<div id='field_".$ff->name."' class='control-group'>\n";
   }
   // show label
   if($ff->label<>NULL && $this->controlGroup){
    $return.=" <label class='control-label'>".$ff->label."</label>\n";
    $return.=" <div class='controls'>\n";
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
     $return.="  <input type='".$ff->type."' name='".$ff->name."' id='".$this->name."_input_".$index."' class='".$ff->class."' placeholder=\"".$ff->placeholder."\" value=\"".$ff->value."\"";
     if($ff->disabled){$return.=" disabled='disabled'";}
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
     $return.="  <select name='".$ff->name."' id='".$this->name."_input_".$index."' class='".$ff->class."'";
     if($ff->disabled){$return.=" disabled='disabled'";}
     $return.=">\n";
     break;
    // multiselect
    case "multiselect":
     $options=TRUE;
     // open multiselect
     $return.="  <select name='".$ff->name."[]' id='".$this->name."_input_".$index."' class='".$ff->class."' multiple='multiple'";
     if($ff->disabled){$return.=" disabled='disabled'";}
     $return.=">\n";
     break;
    // textarea
    case "textarea":
     $return.="  <textarea name='".$ff->name."' id='".$this->name."_input_".$index."' rows='".$ff->rows."' class='".$ff->class."' placeholder=\"".$ff->placeholder."\"";
     if($ff->disabled){$return.=" disabled='disabled'";}
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
     $return.="  <input type='text' name='".$ff->name."_from' id='".$this->name."_input_from_".$index."' class='input-small ".$ff->class."' placeholder=\"".$ff->placeholder[0]."\" value=\"".$ff->value[0]."\"";
     if($ff->disabled){$return.=" disabled='disabled'";}
     $return.="> &nbsp;\n";
     $return.="  <input type='text' name='".$ff->name."_to' id='".$this->name."_input_to_".$index."' class='input-small ".$ff->class."' placeholder=\"".$ff->placeholder[1]."\" value=\"".$ff->value[1]."\"";
     if($ff->disabled){$return.=" disabled='disabled'";}
     $return.=">\n";
     if(!$ff->label){$return.="\n";}
     break;
    // slider
    case "slider":
     if(!$ff->label){$return.="  ";}
     $return.="  <input type='text' name='".$ff->name."' id='".$this->name."_input_".$index."' value='".$ff->value."' data-slider-min='0' data-slider-max='100' data-slider-value='".$ff->value."' class='".$ff->class."'";
     if($ff->disabled){$return.=" disabled='disabled'";}
     $return.=">\n";
     if(!$ff->label){$return.="\n";}
     break;
    // date and datetime
    case "date":
    case "datetime":
     if($ff->type=="date"){$name="date";$format="yyyy-MM-dd";}
     if($ff->type=="datetime"){$name="datetime";$format="yyyy-MM-dd hh:mm";}
     $return.="  <div id='".$this->name."_".$name."_".$index."' class='input-append'>\n";
     $return.="   <input type='text' name='".$ff->name."' id='".$this->name."_input_".$index."' data-format='".$format."' readonly='readonly' class='".$ff->class."' placeholder=\"".$ff->placeholder."\" value=\"".$ff->value."\">\n";
     $return.="   <span class='add-on'><i data-time-icon='icon-time' data-date-icon='icon-calendar'></i></span>\n";
     $return.="  </div>\n";
     break;
    // daterange and datetimerange
    case "daterange":
    case "datetimerange":
     if($ff->type=="daterange"){$name="daterange";$format="yyyy-MM-dd";$size="input-small";}
     if($ff->type=="datetimerange"){$name="datetimerange";$format="yyyy-MM-dd hh:mm";$size="input-medium";}
     $return.="  <div id='".$this->name."_".$name."_from_".$index."' class='input-append'>\n";
     $return.="   <input type='text' name='".$ff->name."_from' id='".$this->name."_input_from_".$index."' data-format='".$format."' readonly='readonly' class='".$size." ".$ff->class."' placeholder=\"".$ff->placeholder[0]."\" value=\"".$ff->value[0]."\">\n";
     $return.="   <span class='add-on'><i data-time-icon='icon-time' data-date-icon='icon-calendar'></i></span>\n";
     $return.="  </div>\n&nbsp;\n";
     $return.="  <div id='".$this->name."_".$name."_to_".$index."' class='input-append'>\n";
     $return.="   <input type='text' name='".$ff->name."_to' id='".$this->name."_input_to_".$index."' data-format='".$format."' readonly='readonly' class='".$size." ".$ff->class."' placeholder=\"".$ff->placeholder[1]."\" value=\"".$ff->value[1]."\">\n";
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
   }
   // show options
   if($options){
    // check for array of options
    if(is_array($ff->options)){
     // show option
     foreach($ff->options as $fo){
      // show checkbox or radio option
      switch(strtolower($ff->type)){
       case "checkbox":
       case "radio":
        $return.="  <label class='".$ff->type." ".$ff->class."'>";
        $return.="<input type='".$ff->type."' name='".$ff->name."' value=\"".$fo->value."\"";
        if($fo->checked){$return.=" checked='checked'";}
        if($fo->disabled){$return.=" disabled='disabled'";}
        $return.=">".$fo->label."</label>\n";
        break;
       // show select or multiselect option
       case "select":
       case "multiselect":
        $return.="   <option value=\"".$fo->value."\"";
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
    $return.=" <a href='#' onClick='selectToggle(\"".$this->name."_input_".$index."\",true)'>".api_text("form-select-all")."</a>,";
    $return.=" <a href='#' onClick='selectToggle(\"".$this->name."_input_".$index."\",false)'>".api_text("form-select-none")."</a>\n";
   }
   // show and close append div
   if($ff->append<>NULL){
    if(substr($ff->append,0,1)<>"<"){
     $return.="  <span class='add-on'>".$ff->append."</span>\n";
    }else{
     $return.="  ".$ff->append."\n";
    }
    $return.=" </div><!-- /input-append -->\n";
   }
   // close controls
   if($ff->label<>NULL && $this->controlGroup){$return.=" </div><!-- /controls -->\n";}
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
     $return.="  $('#".$this->name."_input_".$index."').dblclick(function(){ $(this).val('') });\n";
    }
    // daterange and datetimerange
    if(strtolower($ff->type)=="daterange" || strtolower($ff->type)=="datetimerange"){
     if(strtolower($ff->type)=="daterange"){$name="daterange";$param="pickTime:false";}
     if(strtolower($ff->type)=="datetimerange"){$name="datetimerange";$param="pickSeconds:false";}
     $return.="  $('#".$this->name."_".$name."_from_".$index."').datetimepicker({ ".$param." });\n";
     $return.="  $('#".$this->name."_".$name."_to_".$index."').datetimepicker({ ".$param." });\n";
     $return.="  $('#".$this->name."_input_from_".$index."').dblclick(function(){ $(this).val('') });\n";
     $return.="  $('#".$this->name."_input_to_".$index."').dblclick(function(){ $(this).val('') });\n";
    }
    $return.=" });\n";
    $return.="</script>\n\n";
   }
   // slider script
   if(strtolower($ff->type)=="slider"){
    $return.="<script type='text/javascript'>\n";
    $return.=" $(document).ready(function(){\n";
    $return.="  $('input[id=".$this->name."_input_".$index."]').slider();\n";
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
  $return.=" function selectToggle(id,selected){\n";
  $return.="  $('#'+id+' option').each(function(){ $(this).attr('selected',selected); });\n";
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
    switch(strtolower($fc->type)){
     // submit
     case "submit":
      $return.="  <input type='submit' name='".$this->name."_submit' id='".$this->name."_control_".$index."' class='btn btn-primary ".$fc->class."' value='".$fc->label."'>\n";
      break;
     // reset
     case "reset":
      $return.="  <input type='reset' name='".$this->name."_reset' id='".$this->name."_control_".$index."' class='btn ".$fc->class."' value='".$fc->label."'>\n";
      break;
     // button, link
     case "button":
     case "link":
      $return.="  <a href='".$fc->url."' ";
      if(strtolower($fc->type)=="button"){$return.="class='btn ".$fc->class."'";}
       else{$return.="class='".$fc->class."'";}
      if(strlen($fc->confirm)){$return.=" onClick=\"return confirm('".$fc->confirm."')\"";}
      $return.=" id='".$this->name."_control_".$index."'>".$fc->label."</a>\n";
      break;
    }
   }
  }
  // close group
  if($this->controlGroup){$return.=" </div>\n</div>\n\n";}
  // close split
  if($this->splitted>0){
   //$GLOBALS['html']->split_close();
   if($split_open){
    $return.="\n </div><!-- /span".$span." -->\n";
    $split_open=FALSE;
    $return.="</div><!-- /row-fluid -->\n\n";
   }
  }
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
 function link($label,$class=NULL){
  if(strlen($label)==0){return FALSE;}
  return "<a href='#modal_".$this->id."' data-toggle='modal' class='".$class."' id='modal-link_".$this->id."'>".$label."</a>";
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
 protected $splitted;
 protected $elements_array;

 /* -[ Contruct ]------------------------------------------------------------ */
 // @string $separator : default elements separator null, hr, br
 // @string $class : dynamic list css class
 public function __construct($separator=NULL,$class=NULL){
  if(!in_array(strtolower($separator),array(NULL,"hr","br"))){return FALSE;}
  $this->class=$class;
  $this->separator=$separator;
  $this->splitted=0;
  $this->elements_array=array();
  return TRUE;
 }

 /* -[ Modal Window Footer ]------------------------------------------------- */
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

 /* -[ Render ]-------------------------------------------------------------- */
 // @boolean $echo : echo result (true) or return
 public function render($echo=TRUE){
  $return="\n<!-- dynamic-list -->\n";
  $return.="<dl class='".$this->class."'>\n";
  foreach($this->elements_array as $element){
   $return.=" <dt>".$element->label."</dt><dd>".$element->value."</dd>";
   if($element->separator<>NULL){$return.="<".$element->separator.">\n";}else{$return.="\n";}
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

?>