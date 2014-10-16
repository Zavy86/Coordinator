<?php
/* -------------------------------------------------------------------------- *\
|* -[ Accounts - Login ]----------------------------------------------------- *|
\* -------------------------------------------------------------------------- */
include("../core/api.inc.php");
// reset current session
$s_url=$_SESSION['external_redirect'];
session_destroy();
session_start();
$_SESSION['external_redirect']=$s_url;
$html->header(NULL,NULL,FALSE);
?>
<div class="login-form">
 <h3>Accesso al sistema</h3>
 <form class="form-horizontal" action="submit.php?act=account_login" method="post">
  <input type="text" id="iAccount" class="input-xlarge" name="account" placeholder="Account" autofocus>
  <input type="password" id="iPassword" class="input-xlarge" name="password" placeholder="Password"><br>
  <input type="submit" class="btn btn-primary" value="Accedi">
  <?php
   if(!api_getOption("ldap")){
    echo "<span>&nbsp;<a href='password_retrieve.php'>Non riesci ad accedere?</a></span>\n";
   }
   ?>
 </form>
</div>
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
</div><!-- /container -->
</body>
</html>
<?php //$html->footer(); ?>