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
 /** @var string $id Table id randomly generated */
 protected $id;
 /** @var string $unvalued Text to show if no results */
 protected $unvalued;
 /** @var boolean $sortable Show headers sortable link */
 protected $sortable;
 /** @var boolean $checkboxes Selectable rows */
 protected $checkboxes;
 /** @var string $get Additional get parameters for sortable link */
 protected $get;
 /** @var string $class Table css class */
 protected $class;
 /** @var boolean $movable Movable rows */
 protected $movable;
 /** @var string $move_table Table name for move function */
 protected $move_table;
 /** @var string $position_field Field name for position */
 protected $position_field;
 /** @var string $grouping_field Field name for grouping position */
 protected $grouping_field;
 /** @var array $th_array Array of table headers */
 protected $th_array;
 /** @var array $tr_array Array of table rows */
 protected $tr_array;
 /** @var array $checkboxes_actions Array of checkboxes actions */
 protected $checkboxes_actions;

 /**
  * Table class
  *
  * @param string $unvalued Text to show if no results
  * @param boolean $sortable Show headers sortable link
  * @param string $get Additional get parameters for sortable link in format &key=value
  * @param string $class Table css class
  * @param string $move_table Table name for move function
  * @param string $position_field Field name for position
  * @param string $grouping_field Field name for grouping position
  * @return boolean
  */
 public function __construct($unvalued=NULL,$sortable=FALSE,$get=NULL,$class=NULL,$move_table=NULL,$position_field="order",$grouping_field=NULL){
  $this->id="table_".rand(1000,9999);
  $this->unvalued=$unvalued;
  $this->sortable=$sortable;
  $this->get=$get;
  $this->class=$class;
  if(strlen($move_table)){
   $this->movable=TRUE;
   $this->move_table=$move_table;
   $this->position_field=$position_field;
   $this->grouping_field=$grouping_field;
   $this->class="sorted_table ".$this->class;
  }
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
  * Add table checkbox header
  *
  * @param string $class Column header css class
  * @param string $width Column header width
  * @param integer $colspan Column span
  * @return boolean
  */
 function addHeaderCheckbox($class=NULL,$width=NULL,$colspan=1){
  $th=new stdClass();
  $th->name="<input type='checkbox' name='table_rows_select' id='".$this->id."_select_rows' title='".api_text("table-row-select-all")."'>";
  $th->class=$class;
  $th->width=$width;
  $th->order=NULL;
  $th->colspan=$colspan;
  $this->th_array[]=$th;
  $this->checkboxes=TRUE;
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
  * Add table field
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
  * Add table movable field
  *
  * @param string $id row id to move
  * @param string $class Field css class
  * @param integer $colspan Column span
  * @return boolean
  */
 function addFieldMovable($id,$class=NULL,$colspan=1){
  $td=new stdClass();
  $td->content="<i class='icon-resize-vertical' title='".api_text("table-row-move")."' rowid='".$id."'></i>";
  $td->class=$class;
  $td->colspan=$colspan;
  $this->tr_array[$this->current_row]->fields[]=$td;
  return TRUE;
 }

 /**
  * Add table checkbox field
  *
  * @param string $id row id to check
  * @param string $class Field css class
  * @param integer $colspan Column span
  * @return boolean
  */
 function addFieldCheckbox($id,$class=NULL,$colspan=1){
  $td=new stdClass();
  $td->content="<input type='checkbox' name='table_rows[]' id='".$this->id."_row_".$id."' title='".api_text("table-row-select")."' value='".$id."'>";
  $td->class=$class;
  $td->colspan=$colspan;
  $this->tr_array[$this->current_row]->fields[]=$td;
  $this->checkboxes=TRUE;
  return TRUE;
 }

 /**
  * Add checkboxes action
  *
  * @param string $act action id (alphanumeric)
  * @param string $url link url to call
  * @return boolean
  */
 function addCheckboxesAction($act,$url){
  $action=new stdClass();
  $action->act=$act;
  $action->url=$url;
  $this->checkboxes_actions[$act]=$action;
  return TRUE;
 }

 /**
  * Get checkboxes action link ID
  *
  * @param string $act action id to get
  * @return string
  */
 function getCheckboxesActionLinkId($act){
  return $this->id."_action_".$this->checkboxes_actions[$act]->act;
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
  * @return void|string HTML source code
  */
 function render($echo=TRUE){
  // open table
  $return="<!-- table -->\n";
  $return.="<div class='table-responsive'>\n";
  $return.="<table id='".$this->id."' class='table table-striped table-hover table-condensed ".$this->class."'>\n";
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
  if(!count($this->tr_array) && $this->unvalued<>NULL){
   $colspan_count=0;
   foreach($this->th_array as $th){$colspan_count+=$th->colspan;}
   $return.="<tr><td colspan=".$colspan_count.">".$this->unvalued."</td></tr>\n";
  }
  // close body
  $return.="</tbody>\n";
  // close table
  $return.="</table>\n";
  $return.="</div>\n<!-- /table -->\n\n";
  // movable table jquery
  if($this->movable){
   $return.="<!-- table-move-script -->
<script type=\"text/javascript\">
 $(document).ready(function(){
  // sortable rows
  $('#".$this->id."').sortable({
    containerSelector:'table',
    itemPath:'> tbody',
    itemSelector:'tr',
    handle:'i.icon-resize-vertical',
    placeholder:'<tr class=\placeholder\/>',
    onDrop:function(\$item,container,_super,event){
     rowid=$(\$item).find('i.icon-resize-vertical')[0]['attributes']['rowid']['value'];
     position=\$item[0]['rowIndex'];
     console.log('id: '+rowid+' moved in position: '+position);
     console.log(\$item);
     // ajax request container
     var request;
     // abort any pending request
     if(request){request.abort();}
     // execute ajax post
     request=$.ajax({
      url:'../jsons/str_table.json.inc.php?act=row_move&table=".$this->move_table."&rowid='+rowid+'&position='+position+'&field=".$this->position_field."&grouping=".$this->grouping_field."',
      type:'post',
      dataType:'html'
     });
     // log ajax post success
     request.done(function(response,textStatus,jqXHR){
      console.log('AJAX Success');
     });
     // log ajax post error
     request.fail(function(xhr,textStatus,thrownError){
      console.error('AJAX Error: '+textStatus,thrownError);
     });
     // execute visual move effect
     _super(\$item, container);
    }
  });
});
</script>\n<!-- /table-move-script -->\n\n";
  }
  // movable table jquery
  if($this->checkboxes){
   $return.="<!-- table-checkboxes-script -->
<script type=\"text/javascript\">
 $(document).ready(function(){
  // select all checkboxes
  $('#".$this->id."_select_rows').change(function(){
   $('input[name=\"table_rows[]\"]').not(this).prop('checked',this.checked);
  });\n";
   // build action dynamic forms
   foreach($this->checkboxes_actions as $action){
    $return.="  // action_".$action->act."
  $('#".$this->id."_action_".$action->act."').click(function(event){
   event.preventDefault();
   var newForm=jQuery('<form>',{
    'action':'".$action->url."',
    'method':'post'
   });
   // get checked rows
   var checked=false;
   $('input[name=\"table_rows[]\"]:checked').each(function(){
    checked=true;
    newForm.append(jQuery('<input>',{
     'name':'table_rows[]',
     'value':$(this).val(),
     'type':'hidden'
    }));
   });
   $('body').append(newForm);
   // submit form
   if(checked){newForm.submit();}
  });\n";
   }
   $return.="  // check if at least one checkboxes is checked
  $('input[name=\"table_rows[]\"],input[name=\"table_rows_select\"]').change(function(){
   var checked=false;
   $('input[name=\"table_rows[]\"]:checked').each(function(){checked=true;});
   if(checked){\n";
   foreach($this->checkboxes_actions as $action){
    $return.="    $('#".$this->id."_action_".$action->act."').removeClass('disabled');\n";
   }
   $return.="   }else{\n";
   foreach($this->checkboxes_actions as $action){
    $return.="    $('#".$this->id."_action_".$action->act."').addClass('disabled');\n";
   }
   $return.="   }
  });
  // disable all checkbox actions buttons\n";
   foreach($this->checkboxes_actions as $action){
    $return.="  $('#".$this->id."_action_".$action->act."').addClass('disabled');\n";
   }
   $return.=" });
</script>\n<!-- /table-checkboxes-script -->\n\n";
  }
  if($echo){echo $return;return TRUE;}else{return $return;}
 }

}
?>