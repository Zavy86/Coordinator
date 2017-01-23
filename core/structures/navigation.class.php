<?php
/**
 * Navigation structure
 *
 * Long Description
 *
 * @package Coordinator\Structures
 * @author  Manuel Zavatta <manuel.zavatta@gmail.com>
 * @link    http://www.coordinator.it
 */

/**
 * Navigation structure class
 *
 * @todo check phpdoc
 */
class str_navigation{

 /** @var integer $current_tab Current tab index */
 private $current_tab;
 /** @var boolean $search show Search bar */
 protected $search;
 /** @var array $get Additional get parameters for search bar */
 protected $get;
 /** @var string $class Navigation css class */
 protected $class;
 /** @var array $nt_array Array of navigation tab objects */
 protected $nt_array;
 /** @var array $filters Array of filter objects */
 protected $filters;

 /**
  * Navigation class
  *
  * @param boolean $search Show search bar
  * @param array $get Additional get parameters for search bar
  * @param string $class Navigation css class
  * @return object Navigation object
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
  * @param string $label Label of the tab
  * @param string $url Link url
  * @param string $get Additional get parameters for link (&key=value)
  * @param string $class Tab css class
  * @param boolean $enabled Enable the navigation tab (true) or not
  * @param string $target Target page _blank, _self, _parent, _top
  * @param string $confirm Confirmation message to approve
  * @return boolean
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
  * @param string $label Label of the tab
  * @param string $url Link url
  * @param string $get Additional get parameters for link (&key=value)
  * @param string $class Sub tab css class
  * @param boolean $enabled Enable the navigation tab (true) or not
  * @param string $target Target page _blank, _self, _parent, _top
  * @param string $confirm Confirmation message to approve
  * @return boolean
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
  * @param string $label Label of the tab
  * @return boolean
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
  * @return boolean
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
  * @param string $type Form field type ( text | checkbox | radio | select | multiselect | range | date | datetime | daterange | datetimerange )
  * @param string $name Name of the filter input
  * @param string $label Label of the filter
  * @param array $options Array of options (value=>label)
  * @param string $class Filter input css class
  * @param string $placeholder Placeholder message
  * @return boolean
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
  * @param string $unvalued Text to return if no filters
  * @return boolean
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
  * @return string Url
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
  * @param string $unvalued Query to return if no filters
  * @return string Filters query or $unvalued value
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
     if($filter->type=="daterange"){$filter_name="SUBSTR(".$filter->name.",1,10)";}else{$filter_name=$filter->name;}
     if($_GET[$filter->name."_from"]<>NULL){$query_filter.=$filter_name.">='".$_GET[$filter->name."_from"]."'";}
     if($_GET[$filter->name."_from"]<>NULL && $_GET[$filter->name."_to"]<>NULL){$query_filter.=" AND ";}
     if($_GET[$filter->name."_to"]<>NULL){$query_filter.=$filter_name."<='".$_GET[$filter->name."_to"]."'";}
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
  * @param string $parameter Parameter id
  * @param string $unvalued Query to return if no filters
  * @param string $field Rename query field
  * @return string Filters query or $unvalued value
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
     if($filter->type=="daterange"){$field_name="SUBSTR(".$filter->name.",1,10)";}else{$field_name=$filter->name;}
    if($_GET[$filter->name."_from"]<>NULL){$query_filter.=$field_name.">='".$_GET[$filter->name."_from"]."'";}
    if($_GET[$filter->name."_from"]<>NULL && $_GET[$filter->name."_to"]<>NULL){$query_filter.=" AND ";}
    if($_GET[$filter->name."_to"]<>NULL){$query_filter.=$field_name."<='".$_GET[$filter->name."_to"]."'";}
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
  *
  * @return void
  */
 function render(){
  // open navigation
  echo "<!-- navigation-tabs -->\n";
  echo "<ul class='nav nav-tabs ".$this->class."'>\n";
  // show title
  echo " <li class='title'>".api_text("module-title")."</li>\n";
  // show dashboard if exist
  if(file_exists("dashboard.php")){echo " <li class='".(api_baseName()=="dashboard.php"?"active":NULL)."'><a href='dashboard.php' class='hiddenlink'>".api_icon("fa-th-large")."</a></li>\n";}
  // show filters
  if(count($this->filters)>0){
   // reset session filters
   if($_GET['resetFilters']){unset($_SESSION['filters'][api_baseModule()][api_baseName()]);}
   // store session filters
   if($_GET['filtered']){$_SESSION['filters'][api_baseModule()][api_baseName()]=$_GET;}
   // load session filters if exist
   if(isset($_SESSION['filters'][api_baseModule()][api_baseName()])){$_GET=array_merge($_SESSION['filters'][api_baseModule()][api_baseName()],$_GET);}
   // build filter form modal body
   $modal_filter_body=new str_form(api_baseName(),"get","filters");
   $modal_filter_body->addField("hidden","filtered",NULL,"1");
   foreach($this->filters as $filter){
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
  }
  // search bar
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
   echo "    <input type='text' id='structure_search' name='q' class='input-large' placeholder='".ucfirst(api_text("search"))."' value='".$_GET['q']."'>\n";
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
  // search script
  echo "<script type='text/javascript'>\n";
  echo "$(\"#structure_search\").click(function(){\$(this).select();});\n";
  echo "</script>\n\n";
  return TRUE;
 }

}
?>