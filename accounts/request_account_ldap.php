<?php
/* -------------------------------------------------------------------------- *\
|* -[ Accounts - Password Reset ]-------------------------------------------- *|
\* -------------------------------------------------------------------------- */
include("../core/api.inc.php");
$html->header("Account request",NULL,FALSE);
// acquire variables
$g_account=strtolower($_GET['account']);
// connect to ldap
$ldap=ldap_connect($ldap_host,3268);
if($ldap){
 // try to bind
 $bind=ldap_bind($ldap,"testsis".$ldap_domain,"Aosta123456");
 if($bind){
  // set options
  ldap_set_option($ldap,LDAP_OPT_REFERRALS,0);
  ldap_set_option($ldap,LDAP_OPT_PROTOCOL_VERSION,3);
  // setup filter to show only people
  $filter="samaccountname=".$g_account;
  $get=array("samaccountname","sn","givenname","mail");
  // query ldap
  $search=ldap_search($ldap,$ldap_dn,$filter,$get);
  $results=ldap_get_entries($ldap,$search);
  // check and get result
  if($g_account==strtolower($results[0]["samaccountname"][0])){
   $ldap_firsname=ucfirst(strtolower($results[0]["givenname"][0]));
   $ldap_lastname=ucfirst(strtolower($results[0]["sn"][0]));
   $ldap_mail=strtolower($results[0]["mail"][0]);
  }
 }
}
?>

<div class="row-fluid">

<form class="form-horizontal" action="submit.php?act=ldap_account_create" method="post">

 <p>Benvenuto, compila il seguente modulo per procedere alla creazione del tuo account:</p><br>

 <div class="control-group">
  <label class="control-label" for="iName">Account</label>
  <div class="controls"><input type="text" id="iLdapUsername" class="input-xlarge" name="ldapUsername" value="<?php echo $g_account;?>" <?php if($g_account<>NULL){echo "readonly";} ?>></div>
 </div>

 <?php /*
 <div class="control-group">
  <label class="control-label" for="iName">Password</label>
  <div class="controls"><input type="password" id="iLdapPassword" class="input-xlarge" name="ldapPassword" placeholder="Password attuale"></div>
 </div>
 */ ?>

 <div class="control-group">
  <label class="control-label" for="iName">Indirizzo e-mail</label>
  <div class="controls"><input type="text" id="iAccount" class="input-xlarge" name="account" placeholder="Indirizzo e-mail" value="<?php echo $ldap_mail;?>"></div>
 </div>

 <div class="control-group">
  <label class="control-label" for="iName">Cognome</label>
  <div class="controls"><input type="text" id="iLastName" class="input-xlarge" name="lastname" placeholder="Cognome" value="<?php echo $ldap_lastname;?>"></div>
 </div>

 <div class="control-group">
  <label class="control-label" for="iName">Nome</label>
  <div class="controls"><input type="text" id="iFirstName" class="input-xlarge" name="firstname" placeholder="Nome" value="<?php echo $ldap_firsname;?>"></div>
 </div>

 <div class="control-group">
  <label class="control-label">Lingua</label>
  <div class="controls">
   <select name="language">
    <option value="default">Default</option>
    <?php
     $dir="../languages/";
     if(is_dir($dir)){
      if($dh=opendir($dir)){
       while(($file=readdir($dh))!==false){
        if(substr($file,-4)==".xml" && $file<>"default.xml"){
         echo "<option value='".substr($file,0,-4)."'";
         if(substr($file,0,-4)==$account->language){echo " selected='selected'";}
         echo ">".substr($file,0,-4)."</option>\n";
        }
       }
       closedir($dh);
      }
     }
    ?>
   </select>
  </div>
 </div>

 <div class="control-group">
  <label class="control-label">Societ&agrave;</label>
  <div class="controls">
   <select name="idCompany">
    <option value=''>Nessuna</option>";
  <?php
   $companies=$GLOBALS['db']->query("SELECT * FROM accounts_companies ORDER BY company ASC,division ASC");
   while($company=$GLOBALS['db']->fetchNextObject($companies)){
    echo "<option value='".$company->id."'";
    if($account->idCompany==$company->id){echo " selected";}
    echo "> ".$company->company." - ".$company->division." &rarr; ".$company->name;
    echo "</option>\n";
   }
  ?>
   </select>
  </div>
 </div>

 <div class="control-group">
  <div class="controls">
   <input type="submit" class="btn btn-primary" value="Continua">
  </div>
 </div>

</form>

<script type="text/javascript">
 $(document).ready(function(){
  // validation
  $('form').validate({
   rules:{
    ldapUsername:{required:true},
    //ldapPassword:{required:true},
    account:{required:true,email:true},
    firstname:{required:true,minlength:2},
    lastname:{required:true,minlength:2}
   },
   submitHandler:function(form){form.submit();}
  });
 });
</script>

</div><!-- /row -->

<?php $html->footer(); ?>