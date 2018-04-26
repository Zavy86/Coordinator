<?php
 /**
  * Table structure JSON
  *
  * Logn Description
  *
  * @package Coordinator\JSONS
  * @author  Manuel Zavatta <manuel.zavatta@gmail.com>
  * @link    http://www.coordinator.it
  */

 // include api
 require_once("../core/api.inc.php");

 // switch action
 switch($_GET['act']){
  case "row_move":$return=row_move();break;
  default:
   die("ERROR: The action ".$_GET['act']." was not found..");
 }

 // check and renderize dump
 if($_GET['dump']){pre_var_dump($return);}
 // ecnode and echo
 echo json_encode($return);

 /**
  * JSON Errors builder
  *
  * @param type $code
  * @param type $txt
  * @return error object
  */
 function json_errors($code,$txt){
  $error=new stdClass();
  $error->error_code=$code;
  $error->error_txt=$txt;
  return $error;
 }

 /**
  * Row Move
  */
 function row_move(){
  // definitions
  $return=new stdClass();
  $return->errors=array();
  $return->queries=array();
  $return->queries_executed=array();
  // acquire variables
  $return->table=$_GET['table'];
  $return->rowid=$_GET['rowid'];
  $return->position=$_GET['position'];
  $return->field=$_GET['field'];
  if(!$return->field){$return->field="position";}
  $return->grouping=$_GET['grouping'];
  // check parameters
  if(!strlen($return->table)){$return->errors[]=json_errors(101,"Table undefined");}
  if(!intval($return->rowid)>0){$return->errors[]=json_errors(102,"Row ID undefined");}
  if(!intval($return->position)>0){$return->errors[]=json_errors(103,"Position undefined");}
  // check for errors
  if(!count($return->errors)){
   // get row object
   $return->object=$GLOBALS['db']->queryUniqueObject("SELECT * FROM `".$return->table."` WHERE `id`='".$return->rowid."'");
   // check if row exist
   if($return->object->id<>$return->rowid){$return->errors[]=json_errors(201,"Row not found");}
   // make grouping query
   if(strlen($return->grouping)){
    if($return->object->{$return->grouping}){$return->grouping_query=" AND `".$return->grouping."`='".$return->object->{$return->grouping}."'";}
    else{$return->grouping_query=" AND `".$return->grouping."` IS NULL";}
   }
   // get max position available
   $return->position_max=$GLOBALS['db']->queryUniqueValue("SELECT `".$return->field."` FROM `".$return->table."` ORDER BY `".$return->field."` DESC");
   // check if position isn't greater than max position available
   if($return->position_max<$return->position){$return->errors[]=json_errors(202,"Position not available");}
   // check for direction
   if(!$return->object->{$return->field}){
    $return->action="new";
    $return->queries[]="UPDATE `".$return->table."` SET `".$return->field."`=`".$return->field."`+'1' WHERE `".$return->field."`>='".$return->position."' AND `".$return->field."`<>'0'".$return->grouping_query;
    $return->queries[]="UPDATE `".$return->table."` SET `".$return->field."`='".$return->position."' WHERE `id`='".$return->object->id."'";
   }elseif($return->position>$return->object->{$return->field}){
    $return->action="increment";
    $return->queries[]="UPDATE `".$return->table."` SET `".$return->field."`=`".$return->field."`-'1' WHERE `".$return->field."`>'".$return->object->{$return->field}."' AND `".$return->field."`<='".$return->position."' AND `".$return->field."`<>'0'".$return->grouping_query;
    $return->queries[]="UPDATE `".$return->table."` SET `".$return->field."`='".$return->position."' WHERE `id`='".$return->object->id."'";
   }else{
    $return->action="decrement";
    $return->queries[]="UPDATE `".$return->table."` SET `".$return->field."`=`".$return->field."`+'1' WHERE `".$return->field."`<'".$return->object->{$return->field}."' AND `".$return->field."`>='".$return->position."' AND `".$return->field."`<>'0'".$return->grouping_query;
    $return->queries[]="UPDATE `".$return->table."` SET `".$return->field."`='".$return->position."' WHERE `id`='".$return->object->id."'";
   }
   // check for errors
   if(!count($return->errors)){
    foreach($return->queries as $key=>$query){
     $result=new stdClass();
     $result->query=$query;
     $result->error=$GLOBALS['db']->executeAndReturnError($query);
     if(!strlen($result->error)){$result->executed=TRUE;}
     $return->queries[$key]=$result;
    }
   }
  }
  // return
  return $return;
 }
?>