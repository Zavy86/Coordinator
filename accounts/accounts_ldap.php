<?php
/* -------------------------------------------------------------------------- *\
|* -[ Accounts - Account LDAP ]---------------------------------------------- *|
\* -------------------------------------------------------------------------- */
include("../core/api.inc.php");
api_loadModule();
// acquire variables
$g_language=$_GET['lang'];
$g_account=strtolower($_GET['account']);
// load language file
if(strlen($g_language)){
 api_loadLocaleFile("../",$g_language);
 api_loadLocaleFile("../accounts/",$g_language);
}
// open html
$html->header(NULL,NULL,FALSE);
// connect to ldap
$ldap=ldap_connect(api_getOption("ldap_host"),3268);
if($ldap){
 // try to bind
 $bind=ldap_bind($ldap,"testsis".api_getOption("ldap_domain"),"Aosta123456");
 if($bind){
  // set options
  ldap_set_option($ldap,LDAP_OPT_REFERRALS,0);
  ldap_set_option($ldap,LDAP_OPT_PROTOCOL_VERSION,3);
  // setup filter to show only people
  $filter=api_getOption("ldap_userfield")."=".$g_account;
  $get=array(api_getOption("ldap_userfield"),"samaccountname","sn","givenname","mail");
  // query ldap
  $search=ldap_search($ldap,$ldap_dn,$filter,$get);
  $results=ldap_get_entries($ldap,$search);
  // check and get result
  if($g_account==strtolower($results[0][strtolower(api_getOption("ldap_userfield"))][0])){
   $ldap_mail=strtolower($results[0]["mail"][0]);
   $ldap_firsname=ucfirst(strtolower($results[0]["sn"][0]));
   $ldap_lastname=ucfirst(strtolower($results[0]["givenname"][0]));
  }
 }
}
// build form
$form=new str_form("submit.php?act=account_ldap","post","accounts_ldap");
$form->addField("text","ldap",api_text("accounts_ldap-ff-ldap"),$g_account,"input-large",api_text("accounts_ldap-ff-ldap-placeholder"),FALSE,NULL,NULL,(strlen($g_account)?TRUE:FALSE));
$form->addField("text","account",api_text("accounts_ldap-ff-account"),$ldap_mail,"input-xlarge");
$form->addField("text","firstname",api_text("accounts_ldap-ff-firstname"),$ldap_firsname,"input-medium");
$form->addField("text","lastname",api_text("accounts_ldap-ff-lastname"),$ldap_lastname,"input-medium");
$form->addControl("submit",api_text("accounts_ldap-fc-submit"));
$form->addControl("button",api_text("accounts_ldap-fc-cancel"),NULL,"login.php");
$form->addField("select","language",api_text("accounts_ldap-ff-language"),NULL,"input-medium");
foreach(api_language_availables() as $key=>$language){$form->addFieldOption($key,$language." (".$key.")",($key==$account->language?TRUE:FALSE));}
// show informations
echo api_tag("p",api_text("accounts_ldap-p-informations"))."<br>\n";
// renderize form
$form->render();
?>
<script type="text/javascript">
 $(document).ready(function(){
  // validation
  $('form[name=accounts_ldap]').validate({
   rules:{
    ldap:{required:true},
    firstname:{required:true,minlength:2},
    lastname:{required:true,minlength:2}
    account:{email:true},
   },
   submitHandler:function(form){form.submit();}
  });
 });
</script>
<?php $html->footer(); ?>