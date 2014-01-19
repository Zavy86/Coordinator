<?php
/* ------------------------------------------------------------------------- *\
|* -[ Accounts - Groups Edit ]---------------------------------------------- *|
\* ------------------------------------------------------------------------- */
if($_GET['id']>0){$checkPermission="groups_edit";}else{$checkPermission="groups_add";}
include("template.inc.php");
function content(){
 $g_id=$_GET['id'];
 if(!isset($g_id)){$g_id=0;}
 $group=$GLOBALS['db']->queryUniqueObject("SELECT * FROM accounts_groups WHERE id='".$g_id."'");
?>

<form class="form-horizontal" action="submit.php?act=group_save&id=<?php echo $group->id;?>" method="post">
 
 <?php if($g_id==0 || $GLOBALS['db']->countOf("accounts_groups","idGroup='".$group->id."'")==0){ ?>
 
 <div class="control-group">
  <label class="control-label">Gruppo</label>
  <div class="controls">
   <select name="idGroup" class='input-medium'>
    <option value='0'>Principale</option>
    <?php
     $groups=$GLOBALS['db']->query("SELECT * FROM accounts_groups WHERE idGroup='0' AND id<>".$g_id." ORDER BY name ASC");
     while($r_group=$GLOBALS['db']->fetchNextObject($groups)){
      echo "<option value='".$r_group->id."'";
      if($group->idGroup==$r_group->id){echo " selected";}
      echo "> ".stripslashes($r_group->name);
      echo "</option>\n";
     }
    ?>
   </select>
  </div>
 </div>
 
 <?php } ?>
 
 <div class="control-group">
  <label class="control-label" for="iName">Nome Gruppo</label>
  <div class="controls"><input type="text" id="iName" class="input-large" name="name" placeholder="Nome gruppo" value="<?php echo $group->name;?>"></div>
 </div>

 <div class="control-group">
  <label class="control-label" for="iDescription">Descrizione</label>
  <div class="controls"><input type="text" id="iDescription" class="input-xlarge" name="description" placeholder="Descrizione del gruppo" value="<?php echo $group->description;?>"></div>
 </div>
 
 <div class="control-group">
  <div class="controls">
   <input type="submit" class="btn btn-primary" value="Salva">
   <a href="groups_list.php" class="btn">Annulla</a>
   <?php if($g_id){if(api_checkPermission("accounts","groups_delete")){echo "<a href='submit.php?act=group_delete&id=".$g_id."' onclick=\"return confirm('Eliminare definitivamente questo gruppo?')\" class='btn btn-danger'>Elimina</a>";}}?>
  </div>
 </div>
 
</form>

<script type="text/javascript">
 $(document).ready(function(){
  $('input[type="submit"]').attr('disabled','disabled');
  $('form').change(function(){
   $('input[type="submit"]').removeAttr('disabled');
  });
  // validation
  $('form').validate({
   rules:{
    name:{required:true,minlength:2}
   },
   submitHandler:function(form){form.submit();}
  });
 });
</script>

<?php
}
?>