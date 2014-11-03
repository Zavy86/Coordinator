<?php
/* -------------------------------------------------------------------------- *\
|* -[ Accounts - Password Retrieve ]----------------------------------------- *|
\* -------------------------------------------------------------------------- */
include("../core/api.inc.php");
$html->header("Password retrieve",NULL,FALSE);
?>

<div class="row-fluid">

<form class="form-horizontal" action="submit.php?act=password_retrieve" method="post">

 <div class="control-group">
  <label class="control-label" for="iName">Account</label>
  <div class="controls"><input type="text" id="iAccount" class="input-xlarge" name="account" placeholder="Insert your e-mail address"></div>
 </div>

 <div class="control-group">
  <div class="controls">
   <input type="submit" class="btn btn-warning" value="Reset password">
  </div>
 </div>

</form>

<script type="text/javascript">
 $(document).ready(function(){
  // validation
  $('form').validate({
   rules:{
    account:{required:true,email:true}
   },
   submitHandler:function(form){form.submit();}
  });
 });
</script>

</div><!-- /row -->

<?php $html->footer(); ?>