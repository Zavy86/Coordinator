<?php

/* -[ Structure Classes ]---------------------------------------------------- */


/* -------------------------------------------------------------------------- *\
|* -[ Navigation ]----------------------------------------------------------- *|
\* -------------------------------------------------------------------------- */
class str_navigation{

 private $current_tab;

 protected $class;
 protected $nt_array;

 /* -[ Construct ]----------------------------------------------------------- */
 // @string $class : navigation css class
 public function __construct($class=NULL){
  $this->class=$class;
  $this->current_tab=-1;
  $this->nt_array=array();
  return TRUE;
 }

 /* -[ Add Tab ]------------------------------------------------------------- */
 // @string $label : label of the tab
 // @string $url : link url
 // @string $get : additional get parameters for link (&key=value)
 // @string $class : input css class
 // @boolean $enabled : enable the navigation tab (true) or not
 function addTab($label,$url=NULL,$get=NULL,$class=NULL,$enabled=TRUE){
  if(strlen($label)==0){return FALSE;}
  $nt=new stdClass();
  $nt->label=$label;
  $nt->url=$url;
  $nt->get=$get;
  $nt->class=$class;
  $nt->enabled=$enabled;
  $this->current_tab++;
  $this->nt_array[$this->current_tab]=$nt;
  return TRUE;
 }

 /* -[ Add Sub Tab ]------------------------------------------------------------- */
 // @string $label : label of the tab
 // @string $url : link url
 // @string $get : additional get parameters for link (&key=value)
 // @string $class : input css class
 // @boolean $enabled : enable the navigation tab (true) or not
 function addSubTab($label,$url=NULL,$get=NULL,$class=NULL,$enabled=TRUE){
  if(strlen($label)==0){return FALSE;}
  $nt=new stdClass();
  $nt->label=$label;
  $nt->url=$url;
  $nt->get=$get;
  $nt->class=$class;
  $nt->enabled=$enabled;
  if(!is_array($this->nt_array[$this->current_tab]->dropdown)){
   $this->nt_array[$this->current_tab]->dropdown=array();
  }
  $this->nt_array[$this->current_tab]->dropdown[]=$nt;
  return TRUE;
 }

 /* -[ Render ]-------------------------------------------------------------- */
  function render(){
  // open navigation
  echo "<!-- navigation-tabs -->\n";
  echo "<ul class='nav nav-tabs ".$this->class."'>\n";
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
     else{echo "<a href='".$nt->url.$nt->get."'";}
    // show label
    echo ">".$nt->label."</a>";
    // dropdown items
    if($dropdown){
     echo "\n  <ul class='dropdown-menu'>\n";
     foreach($nt->dropdown as $ntd){
      echo "   <li";
      if($ntd->enabled){
       echo "><a href='".$ntd->url.$ntd->get."'";
      }else{
       echo " class='disabled ".$ntd->class."'><a href='#'";
      }
      echo ">".$ntd->label."</a></li>\n";
     }
     echo "  </ul>\n ";
    }
    echo "</li>\n";
   }
  }
  // close navigation
  echo "</ul><!-- /navigation-tabs -->\n\n";
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
 public function render(){
  // open table
  echo "<!-- table -->\n";
  echo "<table class='table table-striped table-hover table-condensed ".$this->class."'>\n";
  // open head
  if(is_array($this->th_array)){
   echo "<thead>\n <tr>\n";
   // show headers
   foreach($this->th_array as $th){
    echo "  <th class='".$th->class."' width='".$th->width."' colspan='".$th->colspan."'>";
    if($this->sortable && $th->order<>NULL){
     // show order link
     if($th->order==$_GET['of']){if($_GET['om']==1){$order=0;}else{$order=1;}}else{$order=1;}
     // check order
     if($th->order==$_GET['of']){
      if($_GET['om']==0){echo api_icon("icon-circle-arrow-down","margin-top:-0.5px;")."&nbsp;";}
      if($_GET['om']==1){echo api_icon("icon-circle-arrow-up","margin-top:-0.5px;")."&nbsp;";}
     }
     echo "<a href='".api_baseName()."?of=".$th->order."&om=".$order.$this->get."'>";
    }
    echo $th->name;
    if($this->sortable){echo "</a>";}
    echo "</th>\n";
   }
   echo " </tr>\n";
   // close head
   echo "</thead>\n";
  }
  // open body
  echo "<tbody>\n";
  if(is_array($this->tr_array)){
   foreach($this->tr_array as $tr){
    // show rows
    echo " <tr class='".$tr->class."'>\n";
    // show fields
    if(is_array($tr->fields)){
     foreach($tr->fields as $td){
      // show field
      echo "  <td class='".$td->class."' colspan='".$td->colspan."'>".$td->content."</td>\n";
     }
    }
    echo " </tr>\n";
   }
  }
  // show no value text
  if(!count($this->tr_array) && $this->unvalued<>NULL){echo "<tr><td colspan=".count($this->th_array).">".$this->unvalued."</td></tr>\n";}
  // close body
  echo "</tbody>\n";
  // close table
  echo "</table>\n<!-- /table -->\n\n";
  return TRUE;
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

 protected $ff_array;
 protected $fc_array;

 /* -[ Construct ]----------------------------------------------------------- */
 // @string $action : form action url
 // @boolean $method : get, post
 // @string $name : form name
 // @string $class : form css class
 public function __construct($action,$method="get",$name="form",$class="form-horizontal"){
  if(strlen($action)==0 || !in_array(strtolower($method),array("get","post"))){return FALSE;}
  $this->action=$action;
  $this->method=$method;
  $this->name=$name;
  $this->class=$class;
  $this->splitted=0;
  $this->current_field=-1;
  $this->ff_array=array();
  $this->fc_array=array();
  return TRUE;
 }

 /* -[ Add Field ]----------------------------------------------------------- */
 // @string $type : hidden, text, password, checkbox, radio, select, textarea, file
 // @string $name : name of the form input (spaces not allowed)
 // @string $label : label for the field
 // @string $value : default value
 // @string $class : input css class
 // @string $placeholder : placeholder message
 // @boolean $disabled : disable input field (true) or not
 // @integer $rows : number of textarea rows
 function addField($type,$name,$label=NULL,$value=NULL,$class=NULL,$placeholder=NULL,$disabled=FALSE,$rows=7){
  if(!in_array(strtolower($type),array("hidden","text","password","checkbox","radio","select","textarea","file"))){return FALSE;}
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
  if(strlen($value)==0 || strlen($label)==0){return FALSE;}
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
 function render(){
  // check splits
  if($this->splitted>0){
   // calculate split
   $span=12/($this->splitted+1);
  }
  // open form
  echo "<!-- form-".$this->name." -->\n";
  echo "<form name='".$this->name."' action='".$this->action."' method='".$this->method."' class='".$this->class."'>\n\n";
  // open split
  if($this->splitted>0){
   $GLOBALS['html']->split_open();
   $GLOBALS['html']->split_span($span);
  }
  // show field
  foreach($this->ff_array as $index=>$ff){
   $options=FALSE;
   // check for split
   if($ff->type=="split"){$GLOBALS['html']->split_span($span);continue;}
   // open group
   if($ff->type<>"separator"){
    echo "<div id='field_".$ff->name."' class='control-group'>\n";
   }
   // show label
   if($ff->label<>NULL){
    echo " <label class='control-label'>".$ff->label."</label>\n";
    echo " <div class='controls'>\n";
   }
   // show input
   switch(strtolower($ff->type)){
    // hidden, text, password
    case "hidden":
    case "text":
    case "password":
     if(!$ff->label){echo "  ";}
     echo "  <input type='".$ff->type."' name='".$ff->name."' class='".$ff->class."' placeholder=\"".$ff->placeholder."\" value=\"".$ff->value."\"";
     if($ff->disabled){echo " disabled='disabled'";}
     echo ">\n";
     if(!$ff->label){echo "\n";}
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
     echo "  <select name='".$ff->name."' class='".$ff->class."'";
     if($ff->disabled){echo " disabled='disabled'";}
     echo ">\n";
     break;
    // textarea
    case "textarea":
     echo "  <textarea name='".$ff->name."' rows='".$ff->rows."' class='".$ff->class."' placeholder=\"".$ff->placeholder."\"";
     if($ff->disabled){echo " disabled='disabled'";}
     echo ">".$ff->value."</textarea>\n";
     break;
    // file
    case "file":
     if($ff->placeholder==NULL){$ff->placeholder="Select a file to upload";}
     echo "  <input type='file' id='file_".$index."' name='".$ff->name."' style='display:none'>\n";
     echo "  <div class='input-append'>\n";
     echo "   <input type='text' id='file_".$index."_show' class='".$ff->class."' placeholder=\"".$ff->placeholder."\" onDblClick=\"$('input[id=file_".$index."]').click();\" readonly>\n";
     echo "   <a class='btn' onClick=\"$('input[id=file_".$index."]').click();\">Sfoglia</a>\n";
     echo "  </div>\n";
     break;
    // separators
    case "separator":
     echo "<".$ff->tag." class='".$ff->class."'>\n\n";
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
        echo "  <label class='".$ff->type." ".$ff->class."'>";
        echo "<input type='".$ff->type."' name='".$ff->name."' value=\"".$fo->value."\"";
        if($fo->checked){echo " checked='checked'";}
        if($fo->disabled){echo " disabled='disabled'";}
        echo ">".$fo->label."</label>\n";
        break;
       // show select option
       case "select":
        echo "   <option value=\"".$fo->value."\"";
        if($fo->checked){echo " selected='selected'";}
        echo ">".$fo->label."</option>\n";
        break;
      }
     }
    }
   }
   // close select
   if(strtolower($ff->type)=="select"){echo "  </select>\n";}
   // close controls
   if($ff->label<>NULL){echo " </div>\n";}
   // close group
   if($ff->type<>"separator"){echo "</div>\n\n";}
   // file script
   if(strtolower($ff->type)=="file"){
    echo "<script type='text/javascript'>\n";
    echo " $('input[id=file_".$index."]').change(function(){\n";
    echo "  $('#file_".$index."_show').val($(this).val());\n";
    echo " });\n";
    echo "</script>\n\n";
   }
  }
  // show controls
  if(is_array($this->fc_array)){
   // open group
   echo "<div class='control-group'>\n";
   echo " <div class='controls'>\n";
   // show control
   foreach($this->fc_array as $fc){
    switch(strtolower($fc->type)){
     // submit
     case "submit":
      echo "  <input type='submit' name='submit' class='btn btn-primary ".$fc->class."' value='".$fc->label."'>\n";
      break;
     // reset
     case "reset":
      echo "  <input type='reset' name='submit' class='btn ".$fc->class."' value='".$fc->label."'>\n";
      break;
     // button, link
     case "button":
     case "link":
      echo "  <a href='".$fc->url."' ";
      if(strtolower($fc->type)=="button"){echo "class='btn ".$fc->class."'";}
       else{echo "class='".$fc->class."'";}
      if(strlen($fc->confirm)){echo " onClick=\"return confirm('".$fc->confirm."')\"";}
      echo ">".$fc->label."</a>\n";
      break;
    }
   }
  }
  // close group
  echo " </div>\n</div>\n\n";
  // close split
  if($this->splitted>0){$GLOBALS['html']->split_close();}
  // close form
  echo "</form><!-- /form-".$this->name." -->\n\n";
  return TRUE;
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
 function render(){
  if(!strlen($this->body)>0){return FALSE;}
  // open modal window
  echo "<!-- modal window ".$this->id." -->\n";
  echo "<div id='modal_".$this->id."' class='modal hide fade ".$this->class."' role='dialog' aria-hidden='true'>\n";
  // modal window header
  echo " <div class='modal-header'>\n";
  echo "  <button type='button' class='close' data-dismiss='modal' aria-hidden='true'>&times;</button>\n";
  if(strlen($this->header)>0){echo "  <h4>".$this->header."</h4>\n";}
  echo " </div>\n";
  // modal window body
  echo " <div class='modal-body'>\n".$this->body."\n </div>\n";
  // modal window footer
  if(strlen($this->footer)>0){echo " <div class='modal-footer'>\n".$this->footer."\n </div>\n";}
  // close modal window
  echo "</div><!-- /modal window -->\n\n";
  return TRUE;
 }

}






/* -------------------------------------------------------------------------- *\
|* -[ Dynamic List ]--------------------------------------------------------- *|
\* -------------------------------------------------------------------------- */
class str_dl{

 protected $class;
 protected $elements_array;

 public function __construct($class=NULL){
  $this->class=$class;
  $this->elements_array=array();
  return TRUE;
 }

 public function addElement($label,$value,$separator=NULL){
  $element=new stdClass();
  $element->label=$label;
  $element->value=$value;
  $element->separator=$separator;
  $this->elements_array[]=$element;
  return TRUE;
 }

 public function render(){
  echo "<dl class=".$this->dl_class.">\n";
  foreach($this->elements_array as $element){
   echo " <dt>".$element->label."</dt><dd>".$element->value."</dd>";
   echo $element->separator."\n";
  }
  echo "</dl>\n";
  return TRUE;
 }

}
?>