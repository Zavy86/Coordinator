<?php
/* ------------------------------------------------------------------------- *\
|* -[ Settings - Permissions Edit ]----------------------------------------- *|
\* ------------------------------------------------------------------------- */
$checkPermission="permissions_edit";
include("template.inc.php");
function content(){
 $g_module=$_GET['module'];
 // acquire modules array
 $modules_array=array();
 $modules=$GLOBALS['db']->query("SELECT DISTINCT module FROM settings_permissions ORDER BY module ASC");
 while($module=$GLOBALS['db']->fetchNextObject($modules)){$modules_array[]=$module;}
 // acquire groups array
 $groups_array=array((object)array("id"=>"0","name"=>"Tutti i gruppi","description"=>""));
 $groups=$GLOBALS['db']->query("SELECT * FROM accounts_groups ORDER BY idGroup,name ASC");
 while($group=$GLOBALS['db']->fetchNextObject($groups)){$groups_array[]=$group;}
 // acquire grouproles array
 $grouproles_array=array();
 //$grouproles_array=array((object)array("id"=>"0","name"=>"Tutti i ruoli","description"=>""));
 $grouproles=$GLOBALS['db']->query("SELECT * FROM accounts_grouproles ORDER BY id ASC");
 while($grouprole=$GLOBALS['db']->fetchNextObject($grouproles)){$grouproles_array[]=$grouprole;}
 // show modules
 echo "<div class='accordion' id='accordion'>";
 foreach($modules_array as $module){
  echo "<div class='accordion-group'>\n";
  echo "<div class='accordion-heading'>\n";
  echo "<a class='accordion-toggle' data-toggle='collapse' data-parent='#accordion' href='#collapse_".$module->module."'>\n";
  echo "<b>".strtoupper($module->module)."</b>\n";
  echo "</a>\n</div>\n";
  echo "<div id='collapse_".$module->module."' class='accordion-body collapse";
  if($g_module==$module->module){echo " in";}echo "'>\n";
  echo "<div class='accordion-inner'>\n";
  echo "<table class='table table-striped table-hover'>\n";
  echo "<thead>\n<tr>\n<th>Descrizione</th>\n";
  echo "<th>Gruppi e ruoli abilitati</th>\n";
  echo "</tr>\n</thead>\n<tbody>\n"; 
  $permission_unlocked_count=0;
  // acquire permissions array
  $permissions_array=array();
  $permissions=$GLOBALS['db']->query("SELECT * FROM settings_permissions WHERE module='".$module->module."' ORDER BY id ASC");
  while($permission=$GLOBALS['db']->fetchNextObject($permissions)){$permissions_array[]=$permission;}
  // show actions
  foreach($permissions_array as $permission){
   $enabled=FALSE;
   if(!$permission->locked || $_SESSION['account']->id==1){$permission_unlocked_count++;}
   echo "<tr>\n<td>";
   if($permission->locked && $_SESSION['account']->id<>1){echo "<i class='icon-lock'></i> ";}
   echo $permission->description."</td>\n<td>\n";
   // get required role by groups
   foreach($groups_array as $group){
    $requiredgrouprole=$GLOBALS['db']->queryUniqueValue("SELECT idGrouprole FROM settings_permissions_join_accounts_groups WHERE idPermission='".$permission->id."' AND idGroup='".$group->id."'");
    if($requiredgrouprole>0){
     $enabled=TRUE;
     echo "<div style=\"height:30px;\">";
     if(!$permission->locked || $_SESSION['account']->id==1){echo "<a class='btn btn-mini' href='submit.php?act=permissions_del&module=".$module->module."&idPermission=".$permission->id."&idGroup=".$group->id."' onclick=\"return confirm('Sei sicuro di voler rimuovere il permesso a questo gruppo?');\"><i class='icon-trash'></i></a> ";}
     if($group->idGroup==0){$group_name="<strong>".$group->name."</strong>";}
      else{$group_name=api_groupName($group->idGroup)."&minus;<strong>".$group->name."</strong>";}
     echo $group_name." &DoubleRightArrow; ".api_grouproleName($requiredgrouprole,TRUE)."</div>\n";
    }
   }
   if(!$enabled){echo "Azione permessa solo agli amministratori";}
   echo "</td>\n</tr>\n";
  }
  echo "</tbody>\n</table>\n";
  if($permission_unlocked_count){
?>
<form class="form-horizontal" action="submit.php?act=permissions_add" method="post">  
 <input type="hidden" name="module" value="<?php echo $module->module; ?>">
 <select name="idPermission">
  <option value="0">Seleziona un'azione</option>
  <option value="-1">Tutte le azioni disponibili</option>
<?php
 foreach($permissions_array as $permission){
  if(!$permission->locked || $_SESSION['account']->id==1){
   echo "<option value='".$permission->id."'> ".$permission->description."</option>\n";
  }
 }
?>
 </select>
 <select name="idGroup">
<?php
 foreach($groups_array as $group){
  $group_name=$group->name;
  if($group->idGroup>0){$group_name=api_groupName($group->idGroup)."&minus;".$group_name;}
  echo "<option value='".$group->id."'> ".$group_name;
  if($group->description){echo " (".$group->description.")";}
  echo "</option>\n";
 }
?>
 </select>
 <select name="idGrouprole">
<?php
 foreach($grouproles_array as $grouprole){
  echo "<option value='".$grouprole->id."'> ".$grouprole->name;
  if($grouprole->description){echo " (".$grouprole->description.")";}
  echo "</option>\n";
 }
?>
 </select>
 <button type="submit" class="btn"><i class="icon-plus"></i></button>
 <a href='<?php echo "submit.php?act=permissions_reset&module=".$module->module;?>' onclick="return confirm('Sei sicuro di voler resettare i permessi di questo modulo?');">Reset permessi</a>
</form>
<?php
  }
  echo "</div>\n</div>\n</div>\n";
 }
}
?>