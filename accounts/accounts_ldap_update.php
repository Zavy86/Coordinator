<?php
/* -------------------------------------------------------------------------- *\
|* -[ Accounts - Account LDAP Update ]--------------------------------------- *|
\* -------------------------------------------------------------------------- */
include("../core/api.inc.php");
api_loadModule();
// acquire variables
$g_language=$_GET['lang'];
$g_username=$_GET['username'];
// load language file
if(strlen($g_language)){
 api_loadLocaleFile("../",$g_language);
 api_loadLocaleFile("../accounts/",$g_language);
}
// open html
$html->header(NULL,NULL,FALSE);
// build form
$form=new str_form("submit.php?act=account_ldap_update","post","accounts_ldap_update");
$form->addField("text","username",api_text("accounts_ldap_update-ff-username"),$g_username,"input-large",null,false,null,null,true);
$form->addField("password","password",api_text("accounts_ldap_update-ff-password"),null,"input-large");
$form->addField("password","password_new1",api_text("accounts_ldap_update-ff-password_new1"),null,"input-large");
$form->addField("password","password_new2",api_text("accounts_ldap_update-ff-password_new2"),null,"input-large");
$form->addControl("submit",api_text("accounts_ldap_update-fc-submit"));
$form->addControl("button",api_text("accounts_ldap_update-fc-cancel"),NULL,"login.php");
// show informations
echo api_tag("p",api_text("accounts_ldap_update-p-informations"))."<br>\n";
// renderize form
$form->render();
?>
<script type="text/javascript">
 $(document).ready(function(){
  // validation
  $('form[name=accounts_ldap_update]').validate({
   rules:{
    username:{required:true},
    password:{required:true},
    password_new1:{required:true,minlength:8},
    password_new2:{required:true,minlength:8,equalTo:"#accounts_ldap_update_input_password_new1"}
   },
   submitHandler:function(form){form.submit();}
  });
 });
</script>
<?php $html->footer(); ?>