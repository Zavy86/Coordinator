<?php
/* -------------------------------------------------------------------------- *\
|* -[ Dashboard - Notifications Send ]--------------------------------------- *|
\* -------------------------------------------------------------------------- */
$checkPermission="notifications_send";
include("template.inc.php");
function content(){
?>

<form class="form-horizontal" action="submit.php?act=notification_send" method="post">

 <div class="control-group">
  <label class="control-label">Tipologia</label>
  <div class="controls">
   <label class="radio inline">
    <input type="radio" name="typology" value="1" checked>
    Notifica
   </label>
   <label class="radio inline">
    <input type="radio" name="typology" value="2">
    Azione
   </label>
  </div>
 </div>
 
 <div class="control-group">
  <label class="control-label">Destinatario</label>
  <div class="controls">
   <label class="radio inline">
    <input type="radio" name="to" value="1" checked>
    Singolo utente
   </label>
   <label class="radio inline">
    <input type="radio" name="to" value="2">
    Gruppo di utenti
   </label>
   <?php if(api_checkPermission("dashboard","notifications_send_all")){ ?>
   <label class="radio inline">
    <input type="radio" name="to" value="3">
    Tutti gli utenti
   </label>
   <?php } ?>
  </div>
 </div>
 
 <div id="dSingle" class="control-group">
  <label class="control-label" for="iIdAccountTo">Utente</label>
  <div class="controls">
   <input type="hidden" id="iIdAccountTo" name="idAccountTo" class="input-xlarge">
  </div>
 </div>
 
 <div id="dGroup" class="control-group">
  <label class="control-label" for="iIdGroup">Gruppo</label>
  <div class="controls">
   <input type="hidden" id="iIdGroup" name="idGroup" class="input-xlarge">
  </div>
 </div>
 
 <div class="control-group">
  <label class="control-label" for="iSubject">Oggetto</label>
  <div class="controls"><input type="text" id="iSubject" class="input-xxlarge" name="subject" placeholder="Specifica un oggetto"></div>
 </div>
 
 <div class="control-group">
  <label class="control-label" for="iMessage">Messaggio</label>
  <div class="controls"><textarea id="iMessage" class="input-xxlarge" name="message" rows="7" placeholder="Scrivi un messaggio.."></textarea></div>
 </div>

 <div class="control-group">
  <label class="control-label" for="iLink">Link</label>
  <div class="controls"><input type="text" id="iLink" class="input-xxlarge" name="link" placeholder="Inserisci qui un eventuale link"></div>
 </div>

 <div class="control-group">
  <div class="controls">
   <input type="submit" class="btn btn-primary" value="Invia notifica">
   <a href="notifications_list.php" class="btn">Annulla</a>
  </div>
 </div>

</form>

<script type="text/javascript">
 $(document).ready(function(){
  $("#dGroup").hide();
  // change radio to
  $("input[name='to']").change(function(){
   if($("input[name='to']:checked").val()=='1'){
    $("#dSingle").show();
    $("#dGroup").hide();
   }else if($("input[name='to']:checked").val()=='2'){
    $("#dSingle").hide();
    $("#dGroup").show();
   }else{
    $("#dSingle").hide();
    $("#dGroup").hide();
   }
  });
  
  // select2 idAccountTo
  $("#iIdAccountTo").select2({
   placeholder:"Seleziona un destinatario",
   minimumInputLength:2,
   ajax:{
    url:"../accounts/accounts_json.inc.php",
    dataType:'json',
    data:function(term,page){return{q:term};},
    results:function(data,page){return{results:data};}
   }
  });
  // select2 idGroup
  $("#iIdGroup").select2({
   placeholder:"Oppure seleziona un gruppo",
   ajax:{
    url:"../accounts/groups_json.inc.php",
    dataType:'json',
    data:function(term,page){return{q:term};},
    results:function(data,page){return{results:data};}
   }
  });
  // validation
  $('form').validate({
   rules:{
    subject:{required:true,minlength:3},
    message:{required:true,minlength:3}
   },
   submitHandler:function(form){form.submit();}
  });
 });
</script>

<?php } ?>