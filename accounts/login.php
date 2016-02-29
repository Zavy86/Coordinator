<?php
/* -------------------------------------------------------------------------- *\
|* -[ Accounts - Login ]----------------------------------------------------- *|
\* -------------------------------------------------------------------------- */
include("../core/api.inc.php");
api_loadModule();
// acquire variables
$g_language=$_GET['lang'];
$g_account=$_GET['account'];
// store selected language in cookie or load
if(isset($g_language)){setcookie("language",$g_language,time()+60*60*24*30);}else{$g_language=$_COOKIE['language'];}
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
// language custom field
$login_controls.="<select name='language' id='login_input_language' class='input-small' onchange=\"javascript:location.href='login.php?lang='+this.value;\">\n";
// default (load user default language
$login_controls.="<option value='' id='login_input_language_option_default'>".api_text("login-fo-language-default")."</option>\n";
// cycle available languages
foreach(api_language_availables() as $key=>$language){
 $login_controls.="<option value='".$key."' id='login_input_language_option_".$key."'";
 if($key==$g_language){$login_controls.="selected";}
 $login_controls.=">".$language."</option>\n";
}
$login_controls.="</select>\n";
// submit button
$login_controls.="<input type='submit' id='login_submit' class='btn btn-primary' value=\"".api_text("login-fc-submit")."\">\n";
// password retrieve
$login_controls.=api_tag("span","&nbsp;".api_link("password_retrieve.php?lang=".$g_language,api_text("login-fc-retrieve")));
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
  $("form[name='login']").submit(function(){$("#login_submit").attr("disabled","disabled");});
  $("form[name='login']").change(function(){$("#login_submit").removeAttr("disabled");});
 });
</script>
<?php
 // close html without footer
 echo "</div><!-- /container -->\n";
 echo "</body>\n</html>\n";
?>