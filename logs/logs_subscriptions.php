<?php
/* -------------------------------------------------------------------------- *\
|* -[ Logs - Subscriptions ]------------------------------------------------- *|
\* -------------------------------------------------------------------------- */
// refresh dashboard every 5 min
header("Refresh:300;url=".$_SERVER["PHP_SELF"]);
include("template.inc.php");
function content(){
 // definitions
 $current_module=NULL;
 // build form
 $form=new str_form("submit.php?act=notification_subscriptions","POST","subscriptions");
 // query
 //$triggers=$GLOBALS['db']->query("SELECT * FROM logs_triggers ORDER BY module ASC");
 $triggers=$GLOBALS['db']->query("SELECT * FROM logs_triggers GROUP BY `trigger` ORDER BY module ASC,id ASC");
 while($trigger=$GLOBALS['db']->fetchNextObject($triggers)){
  if($trigger->module<>$current_module){
   if(!api_checkPermissionShowModule($trigger->module,FALSE)){continue;}
   $current_module=$trigger->module;
   // load module language file
   api_loadLocaleFile("../".$trigger->module."/");
   $form->addTitle(api_text("module-title"));
  }
  // get current subscription status
  $subscription=$GLOBALS['db']->queryUniqueObject("SELECT * FROM logs_subscriptions WHERE `trigger`='".$trigger->trigger."' AND idAccount='".api_accountId()."'");
  // make notification title
  if(api_text($trigger->trigger."-description")<>"{".$trigger->trigger."-description}"){
   $notification_title=api_text($trigger->trigger."-label")." ".api_link("#",api_icon("icon-comment"),api_text($trigger->trigger."-description"),NULL,TRUE);
  }else{
   $notification_title=api_text($trigger->trigger."-label");
  }
  // show subscription options
  $form->addField("radio","notification_".$trigger->trigger,$notification_title,NULL,"inline");
  $form->addFieldOption(0,api_text("subscriptions-fo-none"),TRUE);
  $form->addFieldOption(1,api_text("subscriptions-fo-notification"),($subscription->idAccount)?TRUE:FALSE);
  $form->addFieldOption(2,api_text("subscriptions-fo-mail"),($subscription->mail)?TRUE:FALSE);
  $form->addFieldOption(3,api_text("subscriptions-fo-archived"),($subscription->archived)?TRUE:FALSE);
 }
 $form->addControl("submit",api_text("subscriptions-fc-submit"));
 // show form
 $form->render();
}
?>