<?php
/* ------------------------------------------------------------------------- *\
|* -[ Accounts - Login ]---------------------------------------------------- *|
\* ------------------------------------------------------------------------- */
include("../core/api.inc.php");
// reset current session
session_destroy();
session_start();
$html->header(NULL,NULL,FALSE);
?>

<div class="row-fluid">

<form class="form-horizontal" action="submit.php?act=account_login" method="post">

 <div class="control-group">
  <label class="control-label" for="iName">Account</label>
  <div class="controls"><input type="text" id="iAccount" class="input-xlarge" name="account" placeholder="Username" autofocus></div>
 </div>

 <div class="control-group">
  <label class="control-label" for="iName">Password</label>
  <div class="controls"><input type="password" id="iPassword" class="input-xlarge" name="password" placeholder="Password"></div>
 </div>

 <div class="control-group">
  <div class="controls">
   <input type="submit" class="btn btn-primary" value="Accedi">
   <!-- <a href="password_retrieve.php" class="btn">Ripristina password</a> -->
  </div>
 </div>
 
</form>

<script type="text/javascript">
 $(document).ready(function(){
  // validation
  $('form').validate({
   rules:{
    account:{required:true},
    password:{required:true}
   },
   submitHandler:function(form){form.submit();}
  });
 });
</script>

</div><!-- /row -->

<?php $html->footer(); ?>