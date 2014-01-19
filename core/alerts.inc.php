<?php

/* -[ Alerts ]--------------------------------------------------------------- */
function api_alert(){
 if(isset($_GET['alert'])){
  $alert=$_GET['alert'];
  $class=$_GET['alert_class'];
 }elseif(isset($GLOBALS['alert'])){
  $alert=$GLOBALS['alert']->alert;
  $class=$GLOBALS['alert']->class;
 }
 switch($alert){
  // core
  case "newLogs":$alert="Sono presenti delle nuove notifiche nel Registro degli Eventi. <a href='".$GLOBALS['dir']."logs/logs_list.php'>Visualizza eventi</a>";break;
  case "changeBrowser":$alert="Il browser utilizzato sembra non supportare completamente il formato HTML5, si consiglia di utilizzare <a href='http://chrome.google.com' target='_blank'>Google Chrome</a> o <a href='http://www.apple.com/safari' target='_blank'>Apple Safari</a>.";break;
  case "submitFunctionNotFound":$alert="Attenzione, la funzione richiamata non è stata implementata, contattare l'amministratore per maggiori informazioni.";break;
  // accounts
  case "loginFailed":$alert="L'account o la password inseriti non sono stati riconosciuti";break;
  case "loginDisabled":$alert="L'account inserito è stato disabilitato, contatta l'amministratore di sistema per maggiori informazioni";break;
  case "accountCreated":$alert="Il nuovo account è stato creato correttamente, seleziona i gruppi a cui assegnarlo";break;
  case "accountEdited":$alert="L'account è stato modificato correttamente";break;
  case "accountCustomized":$alert="Il tuo account è stato modificato correttamente";break;
  case "accountPasswordChanged":$alert="La tua password è stata variata con successo";break;
  case "accountSwitched1":$alert="Da questo momento stai operando con i privilegi di amministratore, ogni porta ti sarà aperta.. :)";break;
  case "accountSwitched2":$alert="Da questo momento stai operando con i privilegi di un normalissimo utente.. :(";break;
  case "passwordRetrived":$alert="La tua richiesta di ripristino della password è stata inoltrata correttamente";break;
  case "passwordResetted":$alert="Il ripristino della tua password è avvenuto correttamente";break;
  case "groupSaved":$alert="Il gruppo è stato salvato correttamente";break;
  case "groupDeleted":$alert="Il gruppo è stato eliminato definitivamente";break;
  case "companySaved":$alert="La società è stata salvata correttamente";break;
  case "companyDeleted":$alert="La società è stata eliminata definitivamente";break;
  case "typologySaved":$alert="La tipologia è stata salvata correttamente";break;
  case "typologyDeleted":$alert="La tipologia è stata eliminata definitivamente";break;
  case "ldapCreated":$alert="Il nuovo account è stato creato correttamente, ora puoi eseguire l'accesso";break;
  case "ldapCreatedError":$alert="Si è verificato un errore durante la creazione dell'account";break;
  // dashboard
  case "notificationSend":$alert="La notifica è stata inoltrata correttamente";break;
  case "notificationSendError":$alert="Si è verificato un errore durante la compilazione della notifica";break;
  // settings
  case "accessDenied":$alert="<h4>ACCESSO NEGATO</h4>I permessi del tuo account non sono sufficienti per completare l'operazione desiderata";break;
  case "settingsSaved":$alert="Le impostazioni sono state salvate correttamente";break;
 }
 // include module alert if exist
 if(file_exists("alerts.php")){include("alerts.php");}
 // show the alert
 if(isset($alert)){
  echo "<div id=\"alert-message\" class=\"alert ".$class."\">\n";
  echo "<button type=\"button\" class=\"close\" data-dismiss=\"alert\">&times;</button>";
  echo "\n".$alert."\n";
  echo "</div>\n";
  // auto close if alert-success
  if($class=="alert-success"){
   echo "<script type=\"text/javascript\">\n";
   echo "window.setTimeout(function(){\$('#alert-message').alert('close');},5000);\n";
   echo "</script>\n";
  }
  // auto close if alert-info
  if($class=="alert-info"){
   echo "<script type=\"text/javascript\">\n";
   echo "window.setTimeout(function(){\$('#alert-message').alert('close');},10000);\n";
   echo "</script>\n";
  }
 }
}

?>