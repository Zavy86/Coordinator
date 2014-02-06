<?php
/* -------------------------------------------------------------------------- *\
|* -[ Helpdesk - Alerts ]---------------------------------------------------- *|
\* -------------------------------------------------------------------------- */
switch($alert){
 // settings
 case "settingSaved":$alert="Le impostazioni sono state salvate correttamente";break;
 case "settingError":$alert="Si &egrave; verificato un errore durante ";break;
 // menus
 case "menuCreated":$alert="Il menu &egrave; stato creato";break;
 case "menuUpdated":$alert="Il menu &egrave; stato aggiornato";break;
 case "menuMoved":$alert="Il menu &egrave; stato spostato";break;
 case "menuDeleted":$alert="Il menu &egrave; stato cancellato";break;
}
?>