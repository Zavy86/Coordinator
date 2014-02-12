<?php
/* -------------------------------------------------------------------------- *\
|* -[ Accounts - Index ]----------------------------------------------------- *|
\* -------------------------------------------------------------------------- */
$checkPermission==NULL;
include("template.inc.php");
function content(){
 $account=$GLOBALS['db']->queryUniqueObject("SELECT * FROM accounts_accounts WHERE id='".$_SESSION['account']->id."'");
?>

<form class="form-horizontal" action="submit.php?act=account_customize" enctype='multipart/form-data' method="post">

 <div class="control-group">
  <label class="control-label" for="iName">Nome</label>
  <div class="controls"><input type="text" id="iAccount" class="input-large" name="name" placeholder="Nome" value="<?php echo $account->name;?>"></div>
 </div>

 <div class="control-group">
  <label class="control-label" for="iAccount">Account</label>
  <div class="controls"><input type="text" id="iAccount" class="input-xlarge" placeholder="Account" value="<?php echo $account->account;?>" readonly></div>
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
  <label class="control-label">Tipologia</label>
  <div class="controls">
   <select disabled>
    <option>
    <?php
     switch($account->typology){
      case 0:echo "Disabled (Disabilitato)";break;
      case 1:echo "Administrator (Amministratore)";break;
      case 2:echo "User (Utente)";break;
     }
    ?>
    </option>
   </select>
  </div>
 </div>

 <div class="control-group">
  <label class="control-label">Societ&agrave;</label>
  <div class="controls">
   <select disabled>
    <option>
    <?php
     $company=$GLOBALS['db']->queryUniqueObject("SELECT * FROM accounts_companies WHERE id='".$account->idCompany."'");
     if($company){echo $company->company." - ".$company->division;}
      else{echo "<td>Non assegnato</td>\n";}
    ?>
    </option>
   </select>
  </div>
 </div>

 <div class="control-group">
  <label class="control-label">Gruppi</label>
  <div class="controls">
  <?php
   $groups=$GLOBALS['db']->query("SELECT * FROM accounts_groups ORDER BY name ASC");
   while($group=$GLOBALS['db']->fetchNextObject($groups)){
    $grouprole=api_accountGrouprole($group->id);
    if($grouprole>0){
     echo $group->name." - ".api_grouproleName($grouprole,TRUE)."<br>\n";
    }
   }
  ?>
  </div>
 </div>

 <div class="control-group">
  <label class="control-label" for="iAvatar">Avatar</label>
  <div class="controls">
   <img src="<?php echo api_accountAvatar()."?".rand(0,999); ?>" class="img-polaroid" width="125">
  </div>
 </div>

 <div class="control-group">
  <div class="controls">
   <input type="file" id="iAvatar" name="avatar" style="display:none">
   <div class="input-append">
    <input type="text" id="iAvatarShow" class="input-large" placeholder="Carica un nuovo avatar" onclick="$('input[id=iAvatar]').click();" readonly>
    <a class="btn" onclick="$('input[id=iAvatar]').click();">Sfoglia</a>
   </div>
  </div>
 </div>

 <?php if(!api_getOption("ldap")){ ?>

 <div class="control-group">
  <label class="control-label" for="iPassword">Password</label>
  <div class="controls"><input type="password" id="iPassword" class="input-large" name="password" placeholder="Vuoi modificare la password?"></div>
 </div>

 <div class="control-group">
  <label class="control-label" for="iVerify">Conferma</label>
  <div class="controls"><input type="password" id="iConfirm" class="input-large" name="confirm" placeholder="Conferma la password scelta"></div>
 </div>

 <?php } ?>

 <div class="control-group">
  <div class="controls">
   <input type="submit" class="btn btn-primary" value="Aggiorna profilo">
  </div>
 </div>

</form>

<script type="text/javascript">
 $('input[id=iAvatar]').change(function(){
  $('#iAvatarShow').val($(this).val());
 });

 $(document).ready(function(){
  $('input[type="submit"]').attr('disabled','disabled');
  $('form').change(function(){
   $('input[type="submit"]').removeAttr('disabled');
  });
  // validation
  $('form').validate({
   rules:{
    name:{required:true,minlength:3},
    password:{minlength:6},
    confirm:{minlength:6,equalTo:"#iPassword"}
   },
   submitHandler:function(form){form.submit();}
  });
 });

</script>

<?php
}
?>