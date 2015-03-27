<?php
/* -------------------------------------------------------------------------- *\
|* -[ Accounts - Password Reset ]-------------------------------------------- *|
\* -------------------------------------------------------------------------- */
include("../core/api.inc.php");
api_loadModule();
$html->header("Password reset",NULL,FALSE);
// acquire variables
$g_account=$_GET['account'];
$g_secret=$_GET['key'];
if(!strlen($g_secret)||!strlen($g_account)){echo api_text("accountError");return FALSE;}
// check account
$account=api_accounts_account($GLOBALS['db']->queryUniqueValue("SELECT id FROM accounts_accounts WHERE account='".$g_account."' AND secret='".$g_secret."' AND ( ISNULL(ldap) OR ldap='')"));
if(!$account->id){echo api_text("accountError");return FALSE;}
// load user language file for account
api_loadLocaleFile("../accounts/",$account->language);
// build account dynamic list
$account_dl=new str_dl("br","dl-horizontal");
$account_dl->addElement(api_text("password_reset-dt-name"),$account->name);
$account_dl->addElement(api_text("password_reset-dt-account"),$account->account);
// renderize password reset form
$form=new str_form("submit.php?act=password_reset","post","password_reset");
$form->addField("hidden","account",NULL,$account->account);
$form->addField("hidden","secret",NULL,$g_secret);
$form->addField("password","password",api_text("password_reset-ff-password"),NULL,"input-large",api_text("password_reset-ff-password-placeholder"));
$form->addField("password","confirm",api_text("password_reset-ff-confirm"),NULL,"input-large",api_text("password_reset-ff-confirm-placeholder"));
$form->addControl("submit",api_text("password_reset-fc-submit"));
$form->addControl("button",api_text("password_reset-fc-cancel"),NULL,"login.php");
// renderize account dynamic list
$account_dl->render();
// renderize password reset form
$form->render();
?>
<script type="text/javascript">
 $(document).ready(function(){
  // validation
  $('form[name=password_reset]').validate({
   rules:{
    password:{required:true,minlength:6},
    confirm:{required:true,minlength:6,equalTo:"#password_reset_input_password"}
   },
   submitHandler:function(form){form.submit();}
  });
 });
</script>
<?php $html->footer(); ?>