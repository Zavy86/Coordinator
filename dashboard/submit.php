<?php
/* -------------------------------------------------------------------------- *\
|* -[ Dashboard - Submit ]--------------------------------------------------- *|
\* -------------------------------------------------------------------------- */
include('../core/api.inc.php');
$act=$_GET['act'];
switch($act){
 // tiles
 case "tile_save":tile_save();break;
 case "tile_delete":tile_delete();break;
 case "tile_background_delete":tile_background_delete();break;

 /* old */
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


/**
 * Tile salve
 */
function tile_save(){
 // acquire variables
 $g_idTile=$_GET['idTile'];
 $g_redirect=$_GET['redirect'];
 if(!$g_redirect){$g_redirect="dashboard_edit.php";}
 $element=json_decode($_GET['element']);
 // check for element
 if($element->url){
  $p_size=addslashes($element->size);
  $p_label=addslashes($element->label);
  $p_description=addslashes($element->description);
  $p_module=addslashes($element->module);
  $p_url=addslashes($element->url);
  $p_icon=addslashes($element->icon);
 }else{
  $p_size=addslashes($_REQUEST['size']);
  $p_label=addslashes($_REQUEST['label']);
  $p_description=addslashes($_REQUEST['description']);
  $p_module=addslashes($_REQUEST['module']);
  $p_url=addslashes($_REQUEST['url']);
  $p_target=addslashes($_REQUEST['target']);
  $p_icon=addslashes($_REQUEST['icon']);
 }
 // check for insert or update
 if($g_idTile){
  // build update query
  $query="UPDATE `settings_dashboards` SET
   `size`='".$p_size."',
   `label`='".$p_label."',
   `description`='".$p_description."',
   `module`='".$p_module."',
   `url`='".$p_url."',
   `target`='".$p_target."',
   `icon`='".$p_icon."'
   WHERE `id`='".$g_idTile."'";
  // execute query
  $GLOBALS['db']->execute($query);
 }else{
  // calculate position
  $position=$GLOBALS['db']->countOf("settings_dashboards","`idAccount`='".api_account()->id."'");
  // build insert query
  $query="INSERT INTO `settings_dashboards`
   (`idAccount`,`order`,`size`,`label`,`description`,`module`,`url`,`target`,`icon`) VALUES
   ('".api_account()->id."','".($position+1)."','".$p_size."','".$p_label."','".$p_description."',
    '".$p_module."','".$p_url."','".$p_target."','".$p_icon."')";
  // execute query
  $GLOBALS['db']->execute($query);
  // get last insert id
  $g_idTile=$GLOBALS['db']->lastInsertedId();
 }
 // upload background
 if(intval($_FILES['background']['size'])>0 && $_FILES['background']['error']==UPLOAD_ERR_OK){
  if(!is_dir("../uploads/uploads/dashboard")){mkdir("../uploads/uploads/dashboard",0777,TRUE);}
  if(file_exists("../uploads/uploads/dashboard/".$g_idTile.".jpg")){unlink("../uploads/uploads/dashboard/".$g_idTile.".jpg");}
  if(is_uploaded_file($_FILES['background']['tmp_name'])){move_uploaded_file($_FILES['background']['tmp_name'],"../uploads/uploads/dashboard/".$g_idTile.".jpg");}
 }
 // redirect
 exit(header("location: ".$g_redirect));
}

/**
 * Tile delete
 */
function tile_delete(){
 // acquire variables
 $g_idTile=$_GET['idTile'];
 $g_redirect=$_GET['redirect'];
 if(!$g_redirect){$g_redirect="dashboard_edit.php";}
 // get tile position
 $order=$GLOBALS['db']->queryUniqueValue("SELECT `order` FROM `settings_dashboards` WHERE `id`='".$g_idTile."'");
 if($order>0){
  // remove tile
  echo $GLOBALS['db']->execute("DELETE FROM `settings_dashboards` WHERE `id`='".$g_idTile."'");
  // moves back tiles located after
  echo $GLOBALS['db']->execute("UPDATE `settings_dashboards` SET `order`=`order`-1 WHERE `order`>'".$order."' AND `idAccount`='".api_account()->id."'");
  // delete background
  if(file_exists("../uploads/uploads/dashboard/".$g_idTile.".jpg")){unlink("../uploads/uploads/dashboard/".$g_idTile.".jpg");}
 }
 //redirect
 exit(header("location: ".$g_redirect));
}

/**
 * Tile background delete
 */
function tile_background_delete(){
 // acquire variables
 $g_idTile=$_GET['idTile'];
 // delete background
 if(file_exists("../uploads/uploads/dashboard/".$g_idTile.".jpg")){unlink("../uploads/uploads/dashboard/".$g_idTile.".jpg");}
 // alert and redirect
 $alert="?alert=tileBackgroundDeleted&alert_class=alert-warning";
 exit(header("location: dashboard_edit.php".$alert));
}


/* old */

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
  header("location: dashboard_edit_old.php".$alert);
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
  header("location: dashboard_edit_old.php".$alert);
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
  exit(header("location: dashboard_edit_old.php".$alert));
 }else{
  // redirect
  $alert="?alert=widgetError&alert_class=alert-error";
  exit(header("location: dashboard_edit_old.php".$alert));
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
  exit(header("location: dashboard_edit_old.php".$alert));
 }else{
  // redirect
  $alert="?alert=widgetError&alert_class=alert-error";
  exit(header("location: dashboard_edit_old.php".$alert));
 }
}
