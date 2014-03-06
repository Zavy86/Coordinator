<?php
/* -------------------------------------------------------------------------- *\
|* -[ Dashboard - Submit ]--------------------------------------------------- *|
\* -------------------------------------------------------------------------- */
include('../core/api.inc.php');
$act=$_GET['act'];
switch($act){
 // widgets
 case "widget_save":widget_save();break;
 case "widget_move_up":widget_move("up");break;
 case "widget_move_down":widget_move("down");break;
 case "widget_remove":widget_remove();break;
 // default
 default:
  $alert="?alert=submitFunctionNotFound&alert_class=alert-warning&act=".$act;
  header("location: index.php".$alert);
}


/* -[ Widget Save  ]--------------------------------------------------------- */
function widget_save(){
 // acquire variables
 $g_id=$_GET['id'];
 if(!$g_id){$g_id=0;}
 $p_module=$_POST['module'];
 $p_parameters=addslashes($_POST['parameters']);
 $p_span=$_POST['span'];
 $p_refresh=$_POST['refresh'];
 if($g_id>0){
  $query="UPDATE settings_dashboards SET
   span='".$p_span."',
   parameters='".$p_parameters."',
   refresh='".$p_refresh."'
   WHERE id='".$g_id."'";
  // execute query
  $GLOBALS['db']->execute($query);
  // redirect
  $alert="?alert=widgetUpdated&alert_class=alert-success";
  header("location: dashboard_edit.php".$alert);
 }else{
  // calculate position
  $position=$GLOBALS['db']->countOf("settings_dashboards","idAccount='".$_SESSION['account']->id."'");
  $position++;
  // acquire module title
  if(file_exists("../".$p_module."/module.inc.php")){include("../".$p_module."/module.inc.php");}
  if($module_title<>""){$title=$module_title;}else{$title=$p_module;}
  $query="INSERT INTO settings_dashboards
   (idAccount,position,span,title,module,parameters,refresh) VALUES
   ('".$_SESSION['account']->id."','".$position."','".$p_span."','".$title."',
    '".$p_module."','".$p_parameters."','".$p_refresh."')";
  // execute query
  $GLOBALS['db']->execute($query);
  // redirect
  $alert="?alert=widgetCreated&alert_class=alert-success";
  header("location: dashboard_edit.php".$alert);
 }
}

/* -[ Widget Move ]---------------------------------------------------------- */
function widget_move($to){
 // acquire variables
 $g_id=$_GET['id'];
 if(!$g_id){$g_id=0;}
 if($g_id>0){
  $moved=FALSE;
  // get widget position
  $position=$GLOBALS['db']->queryUniqueValue("SELECT position FROM settings_dashboards WHERE id='".$g_id."'");
  // move widget
  switch($to){
   case "up":
    if($position>1){
     echo $GLOBALS['db']->execute("UPDATE settings_dashboards SET position=".$position." WHERE position='".($position-1)."' AND idAccount='".$_SESSION['account']->id."'");
     echo $GLOBALS['db']->execute("UPDATE settings_dashboards SET position=".($position-1)." WHERE id='".$g_id."'");
     $moved=TRUE;
    }
    break;
   case "down":
    $max_position=$GLOBALS['db']->countOf("settings_dashboards","idAccount='".$_SESSION['account']->id."'");
    if($position<$max_position){
     echo $GLOBALS['db']->execute("UPDATE settings_dashboards SET position=".$position." WHERE position='".($position+1)."' AND idAccount='".$_SESSION['account']->id."'");
     echo $GLOBALS['db']->execute("UPDATE settings_dashboards SET position=".($position+1)." WHERE id='".$g_id."'");
     $moved=TRUE;
    }
    break;
  }
  // alert and redirect
  if($moved){$alert="?alert=widgetMoved&alert_class=alert-success";}
   else{$alert="?alert=widgetError&alert_class=alert-error";}
  exit(header("location: dashboard_edit.php".$alert));
 }else{
  // redirect
  $alert="?alert=widgetError&alert_class=alert-error";
  exit(header("location: dashboard_edit.php".$alert));
 }
}

/* -[ Widget Remove ]-------------------------------------------------------- */
function widget_remove(){
 // acquire variables
 $g_id=$_GET['id'];
 if(!$g_id){$g_id=0;}
 if($g_id>0){
  // get widget position
  $position=$GLOBALS['db']->queryUniqueValue("SELECT position FROM settings_dashboards WHERE id='".$g_id."'");
  // remove widget
  echo $GLOBALS['db']->execute("DELETE FROM settings_dashboards WHERE id='".$g_id."'");
  // moves back widget located after
  echo $GLOBALS['db']->execute("UPDATE settings_dashboards SET position=position-1 WHERE position>'".$position."' AND idAccount='".$_SESSION['account']->id."'");
  // redirect
  $alert="?alert=widgetRemoved&alert_class=alert-warning";
  exit(header("location: dashboard_edit.php".$alert));
 }else{
  // redirect
  $alert="?alert=widgetError&alert_class=alert-error";
  exit(header("location: dashboard_edit.php".$alert));
 }
}