<?php

/* -[ Alerts ]--------------------------------------------------------------- */
function api_alert2(){
 if(isset($_GET['alert'])){
  $alert=$_GET['alert'];
  $class=$_GET['alert_class'];
 }elseif(isset($GLOBALS['alert'])){
  $alert=$GLOBALS['alert']->alert;
  $class=$GLOBALS['alert']->class;
 }
 switch($alert){
  // core
  case "maintenance":$alert=api_getOption('maintenance_description');break;
  case "accessDenied":$alert="<h4>ACCESSO NEGATO</h4>I permessi del tuo account non sono sufficienti per completare l'operazione desiderata";break;
  case "newLogs":$alert="Sono presenti delle nuove notifiche nel Registro degli Eventi. <a href='".$GLOBALS['dir']."logs/logs_list.php'>Visualizza eventi</a>";break;
  case "changeBrowser":$alert="Il browser utilizzato sembra non supportare completamente il formato HTML5, si consiglia di utilizzare <a href='http://chrome.google.com' target='_blank'>Google Chrome</a> o <a href='http://www.apple.com/safari' target='_blank'>Apple Safari</a>.";break;
  case "submitFunctionNotFound":$alert="Attenzione, la funzione richiamata non è stata implementata, contattare l'amministratore per maggiori informazioni.";break;
  // dashboard
  case "notificationSend":$alert="La notifica è stata inoltrata correttamente";break;
  case "notificationSendError":$alert="Si è verificato un errore durante la compilazione della notifica";break;
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