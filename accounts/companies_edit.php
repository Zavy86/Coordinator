<?php
/* ------------------------------------------------------------------------- *\
|* -[ Accounts - Companies Edit ]------------------------------------------- *|
\* ------------------------------------------------------------------------- */
if($_GET['id']>0){$checkPermission="companies_edit";}else{$checkPermission="companies_add";}
include("template.inc.php");
function content(){
 $g_id=$_GET['id'];
 if(!isset($g_id)){$g_id=0;}
 $company=$GLOBALS['db']->queryUniqueObject("SELECT * FROM accounts_companies WHERE id='".$g_id."'");
?>

<form class="form-horizontal" action="submit.php?act=company_save&id=<?php echo $g_id;?>" method="post">

 <div class="control-group">
  <label class="control-label" for="iDescription">Societ&agrave;</label>
  <div class="controls"><input type="text" id="iCompany" class="input-large" name="company" placeholder="Societ&agrave;" value="<?php echo $company->company;?>"></div>
 </div>
    
 <div class="control-group">
  <label class="control-label" for="iName">Divisione</label>
  <div class="controls"><input type="text" id="iDivision" class="input-large" name="division" placeholder="Nome divisione" value="<?php echo $company->division;?>"></div>
 </div>
 
 <div class="control-group">
  <label class="control-label" for="iCompanyName">Ragione sociale</label>
  <div class="controls"><input type="text" id="iName" class="input-xlarge" name="name" placeholder="Ragione sociale" value="<?php echo $company->name;?>"></div>
 </div>
 
 <div class="control-group">
  <div class="controls">
   <input type="submit" class="btn btn-primary" value="Salva">
   <a href="groups_list.php" class="btn">Annulla</a>
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
    company:{required:true,minlength:3},
    division:{required:true,minlength:3},
    name:{required:true,minlength:3}
   },
   highlight:function(label){$(label).closest('.control-group').removeClass('success').addClass('error');},
   success:function(label){$(label).closest('.control-group').addClass('success');},
   submitHandler:function(form){form.submit();}
  });
 });
</script>

<?php
}
?>