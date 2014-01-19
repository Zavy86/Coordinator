<?php
/* ------------------------------------------------------------------------- *\
|* -[ Accounts - Typologies Edit ]------------------------------------------ *|
\* ------------------------------------------------------------------------- */
if($_GET['id']>0){$checkPermission="typologies_edit";}else{$checkPermission="typologies_add";}
include("template.inc.php");
function content(){
 $g_id=$_GET['id'];
 if(!isset($g_id)){$g_id=0;}
 $typology=$GLOBALS['db']->queryUniqueObject("SELECT * FROM accounts_typologies WHERE id='".$g_id."'");
?>

<form class="form-horizontal" action="submit.php?act=typology_save&id=<?php echo $g_id;?>" method="post">
    
 <div class="control-group">
  <label class="control-label" for="iName">Tipologia</label>
  <div class="controls"><input type="text" id="iName" class="input-large" name="name" placeholder="Nome tipologia" value="<?php echo $typology->name;?>"<?php if($typology->id>0&&$typology->id<=4){echo "  readonly";} ?>></div>
 </div>

 <div class="control-group">
  <label class="control-label" for="iDescription">Descrizione</label>
  <div class="controls"><input type="text" id="iDescription" class="input-xlarge" name="description" placeholder="Descrizione della tipologia" value="<?php echo $typology->description;?>"></div>
 </div>
 
 <div class="control-group">
  <div class="controls">
   <input type="submit" class="btn btn-primary" value="Salva">
   <a href="typologies_list.php" class="btn">Annulla</a>
   <?php if($g_id>4){if(api_checkPermission("accounts","typologies_delete")){echo "<a href='submit.php?act=typology_delete&id=".$g_id."' onclick=\"return confirm('Eliminare definitivamente questa tipologia?')\" class='btn btn-danger'>Elimina</a>";}}?>
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
    name:{required:true,minlength:3}
   },
   submitHandler:function(form){form.submit();}
  });
 });
</script>

<?php
}
?>