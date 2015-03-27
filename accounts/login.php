<?php
/* -------------------------------------------------------------------------- *\
|* -[ Accounts - Login ]----------------------------------------------------- *|
\* -------------------------------------------------------------------------- */
include("../core/api.inc.php");
api_loadModule();
// acquire variables
$g_language=$_GET['lang'];
$g_account=$_GET['account'];
// load language file
if(strlen($g_language)){
 api_loadLocaleFile("../",$g_language);
 api_loadLocaleFile("../accounts/",$g_language);
}
// reset current session
$s_url=$_SESSION['external_redirect'];
session_destroy();
session_start();
$_SESSION['external_redirect']=$s_url;
// open html
$html->header(NULL,NULL,FALSE);
// build login form
$form=new str_form("submit.php?act=account_login&lang=".$g_language,"post","login");
$form->addField("text","account",NULL,$g_account,NULL,api_text("login-ff-account-placeholder"));
$form->addField("password","password",NULL,NULL,NULL,api_text("login-ff-password-placeholder"));
$login_controls="<input type='submit' class='btn btn-primary' value=\"".api_text("login-fc-submit")."\">\n";
$login_controls.="<span>&nbsp;<a href='password_retrieve.php?lang=".$g_language."'>".api_text("login-fc-retrieve")."</a></span>\n";
$form->addCustomField(NULL,$login_controls);
// open login div
echo "<div class='login-form'>\n";
echo "<h3 style='text-align:center;'>".api_getOption('title')."</h3>\n";
// renderize login form
$form->render();
// close login div
echo "</div>\n";
?>
<script type="text/javascript">
 $(document).ready(function(){
<?php
 if(strlen($g_account)){echo "  $('input[name=password]').focus();\n";}
 else{echo "  $('input[name=account]').focus();\n";}
?>
  // validation
  $('form[name=login]').validate({
   rules:{
    account:{required:true},
    password:{required:true}
   },
   submitHandler:function(form){form.submit();}
  });
 });
</script>
<?php
 // close html without footer
 echo "</div><!-- /container -->\n";
 echo "</body>\n</html>\n";
?>