<?php
/* -------------------------------------------------------------------------- *\
|* -[ Accounts - Password Retrieve ]----------------------------------------- *|
\* -------------------------------------------------------------------------- */
include("../core/api.inc.php");
api_loadModule();
// acquire variables
$g_language=$_GET['lang'];
// load language file
if(strlen($g_language)){
 api_loadLocaleFile("../",$g_language);
 api_loadLocaleFile("../accounts/",$g_language);
}
// open html
$html->header("Password retrieve",NULL,FALSE);
// build warning description list
if(api_getOption("ldap")){
 $warning_dl=new str_dl("br","dl-horizontal");
 $warning_dl->addElement(api_span(api_text("password_retrieve-dt-ldap"),"text-error"),api_text("password_retrieve-dd-ldap",api_getOption("owner")));
}
// build password retrieve form
$form=new str_form("submit.php?act=password_retrieve&lang=".$g_language,"post","password_retrieve");
$form->addField("text","account",api_text("password_retrieve-ff-account"),NULL,"input-xlarge",api_text("password_retrieve-ff-account-placeholder"));
$form->addControl("submit",api_text("password_retrieve-fc-submit"));
$form->addControl("button",api_text("password_retrieve-fc-cancel"),NULL,"login.php?lang=".$g_language);
// renderize warning description list
if(is_object($warning_dl)){$warning_dl->render();}
// renderize password retrieve form
$form->render();
?>
<script type="text/javascript">
 $(document).ready(function(){
  // validation
  $('form[name=password_retrieve]').validate({
   rules:{
    account:{required:true,email:true}
   },
   submitHandler:function(form){form.submit();}
  });
 });
</script>
<?php $html->footer(); ?>