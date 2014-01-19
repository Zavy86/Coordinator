<?php
/* ------------------------------------------------------------------------- *\
|* -[ Accounts - Password Reset ]------------------------------------------- *|
\* ------------------------------------------------------------------------- */
include("../core/api.inc.php");
$html->header("Password reset",NULL,FALSE);
// acquire variables
$g_account=$_GET['account'];
$g_secret=$_GET['key'];
if($g_secret==NULL||$g_account==NULL){die("FATAL ERROR /!\\");}
// check account
$account=$GLOBALS['db']->queryUniqueObject("SELECT * FROM accounts_accounts WHERE account='".$g_account."' AND secret='".$g_secret."'");
if(!$account->id){die("FATAL ERROR /!\\");}
?>

<div class="row-fluid">

<form class="form-horizontal" action="submit.php?act=password_reset" method="post">

 <input type="hidden" id="iSecret" name="secret" value="<?php echo $g_secret;?>">

 <div class="control-group">
  <label class="control-label" for="iName">Account</label>
  <div class="controls"><input type="text" id="iAccount" class="input-xlarge" name="account" value="<?php echo $g_account;?>" readonly></div>
 </div>

 <div class="control-group">
  <label class="control-label" for="iName">Password</label>
  <div class="controls"><input type="password" id="iPassword" class="input-xlarge" name="password" placeholder="Scegli una password"></div>
 </div>

 <div class="control-group">
  <label class="control-label" for="iName">Conferma</label>
  <div class="controls"><input type="password" id="iConfirm" class="input-xlarge" name="confirm" placeholder="Conferma la password scelta"></div>
 </div>

 <div class="control-group">
  <div class="controls">
   <input type="submit" class="btn btn-primary" value="Salva">
  </div>
 </div>
 
</form>

<script type="text/javascript">
 $(document).ready(function(){
  // validation
  $('form').validate({
   rules:{
    password:{required:true,minlength:6},
    confirm:{required:true,minlength:6,equalTo:"#iPassword"}
   },
   submitHandler:function(form){form.submit();}
  });
 });
</script>

</div><!-- /row -->

<?php $html->footer(); ?>