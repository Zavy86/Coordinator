<?php
/* ------------------------------------------------------------------------- *\
|* -[ Settings - Settings Edit ]-------------------------------------------- *|
\* ------------------------------------------------------------------------- */
$checkPermission="settings_edit";
include("template.inc.php");
function content(){
?>

<form class="form-horizontal" action="submit.php?act=settings_save" method="post">

 <div class="control-group">
  <label class="control-label" for="iOwner">Azienda</label>
  <div class="controls"><input type="text" id="iOwner" class="input-large" name="owner" placeholder="Nome dell'azienda" value="<?php echo api_getOption("owner");?>"></div>
 </div>

 <div class="control-group">
  <label class="control-label" for="iOwnerUrl">Sito web</label>
  <div class="controls"><input type="text" id="iOwnerUrl" class="input-xlarge" name="owner_url" placeholder="http://www.domain.tdl" value="<?php echo api_getOption("owner_url");?>"></div>
 </div>

 <div class="control-group">
  <label class="control-label" for="iOwnerMail">E-mail</label>
  <div class="controls"><input type="text" id="iOwnerMail" class="input-xlarge" name="owner_mail" placeholder="Indirizzo e-mail aziendale" value="<?php echo api_getOption("owner_mail");?>"></div>
 </div>
 
 <div class="control-group">
  <label class="control-label" for="iOwnerMail">Mittente</label>
  <div class="controls"><input type="text" id="iOwnerMailFrom" class="input-xlarge" name="owner_mail_from" placeholder="Nome visualizzato nel mittente delle e-mail" value="<?php echo api_getOption("owner_mail_from");?>"></div>
 </div>
 
 <hr>
 
 <div class="control-group">
  <label class="control-label" for="iTitle">Titolo visualizzato</label>
  <div class="controls"><input type="text" id="iTitle" class="input-small" name="title" placeholder="Coordinator" value="<?php echo api_getOption("title");?>"></div>
 </div>
 
 <div class="control-group">
  <label class="control-label" for="iGoogleAnalytics">Google Analytics</label>
  <div class="controls"><input type="text" id="iGoogleAnalytics" class="input-medium" name="google_analytics" placeholder="UA-XXXXXXXX-X" value="<?php echo api_getOption("google_analytics");?>"></div>
 </div>
 
 <div class="control-group">
  <label class="control-label" for="iGoogleAnalytics">Piwik Analytics</label>
  <div class="controls"><input type="text" id="iPiwikAnalytics" class="input-medium" name="piwik_analytics" placeholder="url:id" value="<?php echo api_getOption("piwik_analytics");?>"></div>
 </div>
 
 <div class="control-group">
  <label class="control-label" for="iCronToken">Cron Token</label>
  <div class="controls ">
   <div class="input-append">
    <input type="text" id="iCronToken" class="input-xlarge" name="cron_token" placeholder="" value="<?php echo api_getOption("cron_token");?>">
    <a class="btn" id="iCronTokenRandomize">Rigenera</a>
   </div>   
  </div>
 </div>
 

 
 <hr>

 <div class="control-group">
  <label class="control-label">Manutenzione</label>
  <div class="controls">
   <label class="checkbox"><input type="checkbox" name="maintenance";
   <?php if(api_getOption("maintenance")){echo " checked='checked'";}?>
   > Blocca il software per manutenzione</label>
  </div>
 </div>
 
 <div class="control-group">
  <label class="control-label" for="iMaintenanceDescription">Messaggio</label>
  <div class="controls"><input type="text" id="iMaintenanceDescription" class="input-xxlarge" name="maintenance_description" placeholder="Messaggio di avviso per manutenzione" value="<?php echo api_getOption("maintenance_description");?>"></div>
 </div>
  
 <div class="control-group">
  <div class="controls">
   <input type="submit" class="btn btn-primary" value="Salva">
   <input type="reset" class="btn" value="Annulla">
  </div>
 </div>
 
</form>

<script type="text/javascript">
 $(document).ready(function(){
  // validation
  $('form').validate({
   rules:{
    owner:{required:true,minlength:3},
    owner_url:{required:true,url:true},
    owner_mail:{required:true,email:true},
    owner_mail_from:{required:true,minlength:3},
    title:{required:true,minlength:3},
    maintenance_description:{required:true,minlength:3}
   },
   submitHandler:function(form){form.submit();}
  });
 });
 // randomize a new cron token
 $("#iCronTokenRandomize").click(function(){
  var randomToken=$.md5($.now());
  $("#iCronToken").val(randomToken);
 });
</script>

<?php } ?>