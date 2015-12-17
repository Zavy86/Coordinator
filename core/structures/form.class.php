<?php
/**
 * Form structure
 *
 * Long Description
 *
 * @package Coordinator\Structures
 * @author  Manuel Zavatta <manuel.zavatta@gmail.com>
 * @link    http://www.coordinator.it
 */

/**
 * Form structure class
 *
 * @todo check phpdoc
 */
class str_form{

 /** @var integer $current_field Current field index */
 private $current_field;
 /** @var string $action Form action url */
 protected $action;
 /** @var string $method Form method */
 protected $method;
 /** @var $name Form name */
 protected $name;
 /** @var string $class Form css class */
 protected $class;
 /** @var integer $splitted Number of page sections ( <=3 ) */
 protected $splitted;
 /** @var boolean $controlGroup Show control-group div */
 protected $controlGroup;
 /** @var array $ff_array Array of form field objects */
 protected $ff_array;
 /** @var array $fc_array Array of form control objects */
 protected $fc_array;

 /**
  * Form structure class
  *
  * @param string $action Form action url
  * @param string $method Form method ( get | post )
  * @param string $name Form name
  * @param string $class Form css class
  * @param boolean $controlGroup Show control-group div
  * @return boolean
  */
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

 /**
  * Add Field
  *
  * @param string $type Form field type ( hidden | text | password | checkbox | radio | select | multiselect | textarea | file | range | date | datetime | daterange | datetimerange )
  * @param string $name Name of the form field
  * @param string $label Label for the field
  * @param string $value Default value
  * @param string $class Field css class
  * @param string $placeholder Placeholder message
  * @param boolean $disabled Disable field
  * @param integer $rows Number of textarea rows
  * @param string $append Append text
  * @param boolean $readonly Readonly field
  * @return boolean
  */
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

 /**
  * Add Custom Field
  *
  * @param string $label Label for the field
  * @param string $source Source code of field
  * @return boolean
  */
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

 /**
  * Add Field Option
  *
  * @param string $value Option value
  * @param string $label Label for the field option
  * @param boolean $checked Checked or Selected field option
  * @param boolean $disabled Disable field option
  * @return boolean
  */
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

 /**
  * Add Form Control
  *
  * @param string $type Form field type ( submit | reset | button | link )
  * @param string $label Label for the control
  * @param string $class Control css class
  * @param string $url Link url
  * @param string $confirm Confirmation message to approve if not null
  * @param boolean $disabled Disable control
  * @return boolean
  */
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

 /**
  * Add Title
  *
  * @param string $title Title to show
  * @param string $class Title css class
  * @return boolean
  */
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

 /**
  * Add Separator
  *
  * @param string $tag Separator tag ( hr | br )
  * @param string $class Separator css class
  * @return boolean
  */
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

 /**
  * Open split
  *
  * @return boolean
  */
 function splitOpen(){
  $this->current_field++;
  $ff=new stdClass();
  $ff->type="splitOpen";
  $this->ff_array[$this->current_field]=$ff;
  return TRUE;
 }

 /**
  * Split span
  *
  * @return boolean
  */
 function splitSpan(){
  if($this->splitted==3){return FALSE;}
  $this->splitted++;
  $this->current_field++;
  $ff=new stdClass();
  $ff->type="splitSpan";
  $this->ff_array[$this->current_field]=$ff;
  return TRUE;
 }

 /**
  * Close Split
  *
  * @return boolean
  */
 function splitClose(){
  $this->current_field++;
  $ff=new stdClass();
  $ff->type="splitClose";
  $this->ff_array[$this->current_field]=$ff;
  return TRUE;
 }

 /**
  * Renderize form object
  *
  * @param boolean $echo Echo HTML source code or return
  * @return void|string HTML source code
  */
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
    // TODO: check for multiselect script if needed
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
  // close form
  $return.="</form><!-- /form-".$this->name." -->\n\n";
  if($echo){echo $return;return TRUE;}else{return $return;}
 }
}
?>