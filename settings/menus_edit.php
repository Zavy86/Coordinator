<?php
/* ------------------------------------------------------------------------- *\
|* -[ Settings - Menus Edit ]----------------------------------------------- *|
\* ------------------------------------------------------------------------- */
$checkPermission="menus_edit";
include("template.inc.php");
function content(){
 // acquire variables
 $g_id=$_GET['id'];
 if(!$g_id){$g_id=0;}
 $g_idMenu=$_GET['idMenu'];
 if(!$g_idMenu){$g_idMenu=0;}
 if($g_id>0){$selectedMenu=$GLOBALS['db']->queryUniqueObject("SELECT * FROM settings_menus WHERE id='".$g_id."'");}
 if($g_idMenu>0){$parentMenu=$GLOBALS['db']->queryUniqueObject("SELECT * FROM settings_menus WHERE id='".$g_idMenu."'");}
?>
<div class="row-fluid">
<div class="span6">
<?php
 if($parentMenu-id>0){echo "<h5><a href='menus_edit.php?idMenu=".$parentMenu->idMenu."'>&laquo;</a> ".stripslashes($parentMenu->menu)."</h5>\n";}
?>
<table class="table table-striped table-hover table-condensed">
 <thead>
  <tr>
   <th width='16'>&nbsp;</th>
   <th width='32'>&nbsp;</th>
   <th class='nowarp'>Menu</th>
   <th width='100%'>Modulo</th>
   <th width='16'>&nbsp;</th>
  </tr>
 </thead>
 <tbody>
<?php
 // get total menu entry
 $totMenus=$GLOBALS['db']->countOf("settings_menus","idMenu='".$g_idMenu."'");
 //
 $menus=$GLOBALS['db']->query("SELECT * FROM settings_menus WHERE idMenu='".$g_idMenu."' ORDER BY position ASC,id ASC");
 while($menu=$GLOBALS['db']->fetchNextObject($menus)){
  // count items
  $count=$GLOBALS['db']->countOf("settings_menus","idMenu='".$menu->id."'");
  // position
  $position=NULL;
  if($menu->position>1){
   $position="<a href='submit.php?act=menu_move_up&idMenu=".$menu->idMenu."&id=".$menu->id."'><i class='icon-arrow-up'></i></a>";
  }
  if($menu->position<$totMenus){
   $position.="<a href='submit.php?act=menu_move_down&idMenu=".$menu->idMenu."&id=".$menu->id."'><i class='icon-arrow-down'></i></a>";
  }
  // show record
  echo "<tr>\n";
  if($count>0){
   echo "<td class='nowarp'><a href='menus_edit.php?idMenu=".$menu->id."'><i class='icon-plus'></i></a></td>\n";
  }else{
   echo "<td class='nowarp'><i class='icon-minus'></i></td>\n";
  }
  if(strlen($menu->url)>0){$url="/".stripslashes($menu->url);}else{$url=NULL;}
  echo "<td class='nowarp text-center'>".$position.$menu->position."</td>\n";
  echo "<td class='nowarp'>".stripslashes($menu->menu)."</td>\n";
  echo "<td class='nowarp'>".stripslashes($menu->module).$url."</td>\n";
  echo "<td class='nowarp'><a href='menus_edit.php?idMenu=".$menu->idMenu."&id=".$menu->id."'><i class='icon-edit'></i></a></td>\n";
  echo "</tr>\n";
 }
?>
 </tbody>
</table>

</div><!-- /span6 -->
<div class="span6">

<?php
 if($selectedMenu->id>0){echo "<center><h5>Modifica menu</h5></center><br>\n";}
  else{echo "<center><h5>Aggiungi menu</h5></center><br>\n";}
?>

<form class="form-horizontal" action="<?php echo "submit.php?act=menu_save&id=".$selectedMenu->id;?>" method="post">

 <div class="control-group">
  <label class="control-label">Categoria padre</label>
  <div class="controls">
   <select name="idMenu">
    <?php
     $menus=$GLOBALS['db']->query("SELECT * FROM settings_menus WHERE id<>'".$selectedMenu->id."' ORDER BY idMenu ASC,position ASC,id ASC");
     while($menu=$GLOBALS['db']->fetchNextObject($menus)){
      if($menu->idMenu==0 || $menu->idMenu==1){
       echo "<option value='".$menu->id."'";
       if($selectedMenu->idMenu>0){
        if($menu->id==$selectedMenu->idMenu){echo " selected";}
       }else{
        if($menu->id==$g_idMenu){echo " selected";}
       }
       echo ">".stripslashes($menu->menu);
       echo "</option>\n";
      }
     }
    ?>
   </select>
  </div>
 </div>

 <div class="control-group">
  <label class="control-label" for="menu">Nome</label>
  <div class="controls"><input type="text" id="menu" class="input-large" name="menu" value="<?php echo stripslashes($selectedMenu->menu);?>"></div>
 </div>

 <div class="control-group">
  <label class="control-label" for="module">Modulo</label>
  <div class="controls"><input type="text" id="module" class="input-large" name="module" value="<?php echo stripslashes($selectedMenu->module);?>"></div>
 </div>

 <div class="control-group">
  <label class="control-label" for="url">URL</label>
  <div class="controls"><input type="text" id="url" class="input-xlarge" name="url" value="<?php echo stripslashes($selectedMenu->url);?>"></div>
 </div>

 <div class="control-group">
  <div class="controls">
   <input type='submit' class='btn btn-primary' name='submit' value='Salva menu'>
   <?php
   if($selectedMenu->id>0){
    $count=$GLOBALS['db']->countOf("settings_menus","idMenu='".$selectedMenu->id."'");
    if($count==0){
     echo "<a href='submit.php?act=menu_delete&idMenu=".$selectedMenu->idMenu."&id=".$selectedMenu->id."' class='btn btn-danger' onClick=\"return confirm('Sei sicuro di voler eliminare questa voce di menu?')\">Elimina</a>\n";
    }
    echo "<a href='menus_edit.php?idMenu=".$selectedMenu->idMenu."' class='btn'>Annulla</a>\n";
   }
   ?>
  </div>
 </div>

</form>

</div><!-- /span6 -->
</div><!-- /row-fluid -->
<?php } ?>