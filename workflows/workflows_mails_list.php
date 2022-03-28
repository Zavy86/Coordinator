<?php
/* -------------------------------------------------------------------------- *\
|* -[ Workflows - Mails List ]----------------------------------------------- *|
\* -------------------------------------------------------------------------- */
$checkPermission="workflows_mails";
require_once("template.inc.php");
function content(){
 // build tickets table
 $table=new str_table(api_text("mails-tr-unvalued"),TRUE);
 $table->addHeader("&nbsp;",NULL,"16");
 $table->addHeader(api_text("mails-th-timestamp"),"nowarp",NULL,"timestamp");
 $table->addHeader(api_text("mails-th-sender"),"nowarp",NULL,"sender");
 $table->addHeader(api_text("mails-th-subject"),NULL,"100%","subject");
 $table->addHeader("&nbsp;",NULL,"16");
 // build mails table rows
 $mails=$GLOBALS['db']->query("SELECT * FROM workflows_mails ORDER BY timestamp ASC");
 while($mail=$GLOBALS['db']->fetchNextObject($mails)){
  $table->addRow();
  $table->addField(api_link("workflows_add.php?mail=".$mail->id,api_icon("icon-search")));
  $table->addField(api_timestampFormat($mail->timestamp,api_text("datetime")),"nowarp");
  $table->addField(stripslashes($mail->sender),"nowarp");
  $table->addField(stripslashes($mail->subject));
  $table->addField(api_link("submit.php?act=mail_delete&idMail=".$mail->id,api_icon("icon-trash"),NULL,NULL,FALSE,api_text("mails-delete-confirm")));
 }
 // renderize table
 $table->render();
}
?>