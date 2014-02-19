<?php

/* -[ Global parameter ]----------------------------------------------------- */
session_start();
global $debug;                  // debug variable
global $html;                   // html structure resource
global $db;                     // database resource
global $dir;                    // base path resource
global $alert;                  // base path resource
global $locale;                 // array with translation
include("../config.inc.php");   // include the configuration file
include("html.class.php");      // include the html class
include("structure.class.php"); // include structure classes
include("db.class.php");        // include the database class
// load core translation file
api_loadLocaleFile("../");
// load module translation file
api_loadLocaleFile();
// build class
$html=new HTML();
$db=new DB($db_host,$db_user,$db_pass,$db_name);


/* -[ Check Session or Token ]----------------------------------------------- */
if($dontCheckSession<>TRUE){$dontCheckSession=FALSE;}
$g_submit=$_GET['submit'];
$g_token=$_GET['token'];
if($g_submit=="cron"){
 // check token
 if(!(strlen($g_token)==32 && $g_token==api_getOption("cron_token"))){
  api_log(3,"dashboard","TOKEN - WRONG CRON TOKEN\nFile: ".api_baseName());
  api_die();
 }
}else{
 // check session
 if(!isset($_SESSION['account'])){
  // redirect
  if(api_baseName()<>"login.php"
     && api_baseName()<>"password_retrieve.php"
     && api_baseName()<>"password_reset.php"
     && api_baseName()<>"request_account_ldap.php"
     && $dontCheckSession==FALSE){
   header("location: ".$GLOBALS['dir']."accounts/login.php");
  }
 }
}


/* -[ Check new logs ]------------------------------------------------------- */
if(api_baseName()<>"logs_list.php"&&$_SESSION['account']->administrator){
 if($GLOBALS['db']->countOf("logs_logs","new='1' AND typology>'1'")>0){
  $GLOBALS['alert']->alert="newLogs";
  $GLOBALS['alert']->class="alert-info";
 }
}


/* -[ Check browser ]-------------------------------------------------------- */
if((strpos($_SERVER['HTTP_USER_AGENT'],'Chrome')==false)
   &&(strpos($_SERVER['HTTP_USER_AGENT'],'Safari')==false)
   &&(strpos($_SERVER['HTTP_USER_AGENT'],'MSIE 10')==false)
   &&(strpos($_SERVER['HTTP_USER_AGENT'],'11')==false)){
 $GLOBALS['alert']->alert="changeBrowser";
 $GLOBALS['alert']->class="alert-error";
}


/* -[ Load Locale Files ]---------------------------------------------------- */
// @param $path : Path of locale if not default
function api_loadLocaleFile($path=NULL){
 if($path==NULL){$path=".";}
 if($_SESSION['language']<>NULL && file_exists($path."/languages/".strtolower($_SESSION['language']).".xml")){
  // load choised locale file
  $xml=simplexml_load_file($path."/languages/".strtolower($_SESSION['language']).".xml");
 }elseif(file_exists($path."/languages/default.xml")){
  // load deafult locale file
  $xml=simplexml_load_file($path."/languages/default.xml");
 }else{
  return FALSE;
 }
 if($xml<>NULL){
  if(!is_array($GLOBALS['locale'])){$GLOBALS['locale']=array();}
  foreach($xml->text as $text_xml){
   $key=(string)$text_xml['key'];
   $GLOBALS['locale'][$key]=(string)$text_xml;
  }
 }
 return TRUE;
}


/* -[ Text Translation ]----------------------------------------------------- */
// @param $key : Text key
// @param $parameters : String or array
function api_text($key,$parameters=NULL){
 // get text by key from locale array
 $text=$GLOBALS['locale'][$key];
 // if key not found
 if(strlen($text)==0){return "{".$key."}";}
 // replace parameters
 if($parameters<>NULL){
  if(is_array($parameters)){
   $count=0;
   foreach($parameters as $parameter){
    $text=str_replace("{".$count."}",$parameter,$text);
    $count++;
   }
  }else{
   $text=str_replace("{0}",$parameters,$text);
  }
 }
 return $text;
}


/* -[ Die ]------------------------------------------------------------------ */
function api_die($error=""){
 switch($error){
  case "accessDenied":$url="<a href='index.php?alert=accessDenied&alert_class=alert-error'>";break;
  default:$url="<a href='index.php'>";
 }
 $die="<html><head><style type='text/css'>body{background:black;color:green;}a{color:green;text-decoration:none;}a:hover{color:#00CC00;}strong{color:#00CC00;}</style></head>";
 $die.="<body><center><br><strong>SYSTEM FAILURE !</strong><br><br><br>";
 $die.="<em>\"Have you ever had a dream, Neo, that you were so sure was real?<br>";
 $die.="What if you were unable to wake from that dream? How would you<br>";
 $die.="know the difference between the dream world and the real world?\"</em>\n";
 $die.="<br><br><br>".$url."Continue &raquo;</a></center></body></html>\n";
 die($die);
}


/* -[ Alerts ]--------------------------------------------------------------- */
function api_alert(){
 if(isset($_GET['alert'])){
  $alert=$_GET['alert'];
  $class=$_GET['alert_class'];
  $parameters=$_GET['alert_parameters'];
 }elseif(isset($GLOBALS['alert'])){
  $alert=$GLOBALS['alert']->alert;
  $class=$GLOBALS['alert']->class;
  $parameters=$GLOBALS['alert']->parameters;
 }
 // show the alert
 if(isset($alert)){
  $alert=api_text($alert,$parameters);
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


/* -[ Base name path ]------------------------------------------------------- */
function api_baseName(){
 $path_parts=pathinfo($_SERVER['PHP_SELF']);
 return $path_parts['basename'];
}


/* -[ Base name of the module ]---------------------------------------------- */
function api_baseModule(){
 $path_parts=pathinfo($_SERVER['PHP_SELF']);
 return substr($path_parts['dirname'],strrpos($path_parts['dirname'],"/")+1);
}


/* -[ Base folder path ]----------------------------------------------------- */
function api_basePath(){
 $path_parts=pathinfo($_SERVER['PHP_SELF']);
 return $path_parts['dirname'];
}


/* -[ Get Option ]----------------------------------------------------------- */
// @param $code : Option code to return value
function api_getOption($code){
 $option=$GLOBALS['db']->queryUniqueObject("SELECT * FROM settings_settings WHERE code='".$code."'");
 if($option->value<>NULL){
  return $option->value;
 }else{
  return NULL;
 }
}


/* -[ LOG ]------------------------------------------------------------------ */
function api_log($typology,$module,$log,$link=NULL){
 if(($typology>0 && $typology<4) && strlen($module)>0 && strlen($log)>0){
  $query="INSERT INTO logs_logs (typology,timestamp,module,log,link,idAccount,ip)
   VALUES ('".$typology."','".date("Y-m-d H:i:s")."','".$module."','".$log."','".$link."','".$_SESSION['account']->id."','".$_SERVER['REMOTE_ADDR']."')";
  $GLOBALS['db']->execute($query);
  if($typology==3){
   $notification_subject="Si è verificato un errore nel modulo ".$module;
   $notification_message="Il registro degli eventi ha segnalato un errore grave.\n";
   $notification_message.="Si consiglia di verificare urgentemente.\n";
   $notification_link="logs/logs_list.php?i=7&t=-1";
   api_notification_administrators(2,"logs",$notification_subject,$notification_message,$notification_link,0);
  }
  return TRUE;
 }else{
  return FALSE;
 }
}


/* -[ Update Temp Token ]---------------------------------------------------- */
function api_updateTempToken(){
 $temp_token=md5(date("Y-m-d H:i:s"));
 $GLOBALS['db']->execute("UPDATE settings_settings SET value='".$temp_token."' WHERE code='temp_token'");
 return $temp_token;
}


/* -[ Send notification ]---------------------------------------------------- */
function api_notification_send($idAccountTo,$typology,$module,$subject,$message,$link=NULL,$idAccountFrom=NULL,$idAction=NULL){
 if($idAccountTo>1 && ($typology>0 && $typology<3) && strlen($module)>0 && strlen($subject)>0 && strlen($message)>0){
  if($idAccountFrom===NULL){$idAccountFrom=$_SESSION['account']->id;}
  if($idAction===NULL){$idAction=md5(date('YdmHsi').api_randomString());}
  $query="INSERT INTO notifications_notifications (idAction,idAccountTo,idAccountFrom,typology,module,subject,message,link,created,status)
   VALUES ('".$idAction."','".$idAccountTo."','".$idAccountFrom."','".$typology."','".$module."','".addslashes($subject)."','".addslashes($message)."','".$link."','".date("Y-m-d H:i:s")."','1')";
  $GLOBALS['db']->execute($query);
  return $idAction;
 }else{
  return FALSE;
 }
}


/* -[ Send notification to all accounts ]------------------------------------ */
function api_notification_all($typology,$module,$subject,$message,$link=NULL,$idAccountFrom=NULL){
 if($typology==2){$idAction=md5(date('YdmHsi').api_randomString());}
 $accounts=$GLOBALS['db']->query("SELECT * FROM accounts_accounts");
 while($account=$GLOBALS['db']->fetchNextObject($accounts)){
  api_notification_send($account->id,$typology,$module,$subject,$message,$link,$idAccountFrom,$idAction);
 }
 return $idAction;
}


/* -[ Send notification to administrators ]---------------------------------- */
function api_notification_administrators($typology,$module,$subject,$message,$link=NULL,$idAccountFrom=NULL){
 if($typology==2){$idAction=md5(date('YdmHsi').api_randomString());}
 $accounts=$GLOBALS['db']->query("SELECT * FROM accounts_accounts WHERE typology='1'");
 while($account=$GLOBALS['db']->fetchNextObject($accounts)){
  api_notification_send($account->id,$typology,$module,$subject,$message,$link,$idAccountFrom,$idAction);
 }
 return $idAction;
}


/* -[ Send notification to group members ]----------------------------------- */
function api_notification_group($idGroup,$idGrouprole,$typology,$module,$subject,$message,$link=NULL,$idAccountFrom=NULL){
 if($typology==2){$idAction=md5(date('YdmHsi').api_randomString());}
 $groups="idGroup='".$idGroup."'";
 // check for subgroups
 $subgroups=$GLOBALS['db']->query("SELECT * FROM accounts_groups WHERE idGroup='".$idGroup."'");
 while($subgroup=$GLOBALS['db']->fetchNextObject($subgroups)){$groups.=" OR idGroup='".$subgroup->id."'";}
 // get accounts in groups
 $accounts=$GLOBALS['db']->query("SELECT distinct(idAccount) FROM accounts_groups_join_accounts WHERE (".$groups.") AND idGrouprole>='".$idGrouprole."'");
 while($account=$GLOBALS['db']->fetchNextObject($accounts)){
  api_notification_send($account->idAccount,$typology,$module,$subject,$message,$link,$idAccountFrom,$idAction);
 }
 return $idAction;
}


/* -[ Show an icon for the file or the preview ]----------------------------- */
function api_file_icon($path,$filename,$size=128,$float=NULL){
 if($float<>NULL){
  if($float=="left"){$float_margin="right";}else{$float_margin="left";}
  $float="float:".$float.";margin-".$float_margin.":10px;";
 }
 if($size>31 && (substr($filename,-3)=="jpg" || substr($filename,-3)=="png" || substr($filename,-3)=="gif" || substr($filename,-4)=="jpeg")){
  return "<img src='".$GLOBALS['dir'].$path."/".$filename."' class='img-polaroid' style='height:".($size-10)."px;width:".($size-10)."px;".$float."'>";
 }elseif(file_exists("../core/images/files/".substr($filename,strrpos($filename,".")+1).".png")){
  return "<img src='../core/images/files/".substr($filename,strrpos($filename,".")+1).".png' style='height:".$size."px;width:".$size."px;".$float."'>";
 }else{
  return "<img src='../core/images/files/file.png' style='height:".$size."px;width:".$size."px;".$float."'>";
 }
}

/* -[ Show a folder icon ]--------------------------------------------------- */
function api_folder_icon($size=128,$float=NULL){
 if($float<>NULL){
  if($float=="left"){$float_margin="right";}else{$float_margin="left";}
  $float="float:".$float.";margin-".$float_margin.":10px;";
 }
 return "<img src='../core/images/files/folder.png' style='height:".$size."px;".$float."'>";
}


/* -[ Timestamp Format ]----------------------------------------------------- */
// @param $timestamp : MySql timestamp
// @param $time      : Return date and time
// @param $seconds   : Return date and time and seconds
function api_timestampFormat($timestamp,$time=FALSE,$seconds=FALSE){
 if($timestamp==NULL){return NULL;}
 $d=explode("-",$timestamp);
 $result=substr($d[2],0,2)."-".$d[1]."-".$d[0];
 if($time){$result.=" ".substr($d[2],3,5);}
 if($seconds){$result.=":".substr($d[2],9,2);}
 return $result;
}


/* -[ Timestamp difference ]------------------------------------------------- */
// @param $timestamp_a : MySql timestamp from
// @param $timestamp_b   : MySql timestamp to
// @param $format         : Format to output: Seconds, mInutes, Hours, Days, Weeks, Months, Years
function api_timestampDifference($timestamp_a,$timestamp_b,$format="S"){
 if($timestamp_a==NULL || $timestamp_b==NULL){return NULL;}
 $seconds=strtotime($timestamp_b)-strtotime($timestamp_a);
 // format result
 switch(strtoupper($format)){
  case "S":$result=$seconds;break;
  case "I":$result=$seconds/60;break;
  case "H":$result=$seconds/3600;break;
  case "D":$result=$seconds/86400;break;
  case "W":$result=$seconds/604800;break;
  case "M":$result=$seconds/2592000;break;
  case "Y":$result=$seconds/31536000;break;
 }
 return number_format($result,2);
}


/* -[ Random Generator ]----------------------------------------------------- */
// @param $size : Numbers of characters
function api_randomString($size=10){
 $characters="0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
 for($i=0;$i<$size;$i++){
  $random.=$characters[rand(0,strlen($characters))];
 }
 return $random;
}


/* -[ Sendmail ]------------------------------------------------------------- */
// @param $to_mail   : Recipient mail
// @param $message   : Content of mail
// @param $subject   : Subject of mail
// @param $from_mail : Sender mail
// @param $from_name : Sender name
function api_sendmail($to_mail,$message,$subject="",$from_mail="",$from_name=""){
 // headers
 if($from_mail==""){$from_mail=api_getOption('owner_mail');}
 if($from_name==""){$from_name=api_getOption('owner_mail_from');}
 $headers= "MIME-Version: 1.0\r\n";
 $headers.="Content-type: text/plain; Charset=UTF-8\r\n";
 $headers.="From: ".$from_name." <".$from_mail.">\r\n";
 $headers.="Reply-To: ".$from_mail."\r\n";
 $headers.="Return-Path: ".$from_mail."\r\n";
 // subject
 if($subject==""){$subject="Intranet - Comunicazione";}
 // message
 $message.="\n\n\n--\nQuesto messaggio è stato generato automaticamente da Coordinator per conto di Intranet, si prega di non rispondere.\n";
 // sendmail
 if($to_mail<>""){mail($to_mail,$subject,$message,$headers);}
}


/* -[ Check permissions ]---------------------------------------------------- */
// @param $module : Module to check
// @param $action : Action to check
function api_checkPermission($module,$action,$alert=FALSE){
 // if account is root return always true
 if($_SESSION['account']->id==1){return TRUE;}
 // if account typology is administrator return always true
 if($_SESSION['account']->typology==1){return TRUE;}
 // retrieve the permission id
 $idPermission=$GLOBALS['db']->queryUniqueValue("SELECT id FROM settings_permissions WHERE module='".$module."' AND action='".$action."'");
 // get required groups
 $requiredgroups=$GLOBALS['db']->query("SELECT * FROM settings_permissions_join_accounts_groups WHERE idPermission='".$idPermission."'");
 while($required=$GLOBALS['db']->fetchNextObject($requiredgroups)){
  if($required->idGroup==0){
   $groups=$GLOBALS['db']->query("SELECT * FROM accounts_groups");
   while($group=$GLOBALS['db']->fetchNextObject($groups)){
    if(api_accountGrouprole($group->id)>=$required->idGrouprole){return TRUE;}
   }
  }else{
   if(api_accountGrouprole($required->idGroup)>=$required->idGrouprole){return TRUE;}
   // try in subgroups
   $subgroups=$GLOBALS['db']->query("SELECT * FROM accounts_groups WHERE idGroup='".$required->idGroup."'");
   while($subgroup=$GLOBALS['db']->fetchNextObject($subgroups)){
    if(api_accountGrouprole($subgroup->id)>=$required->idGrouprole){return TRUE;}
   }
  }
 }
 if($alert){
  echo "<div id='alert-message' class='alert alert-error'>\n";
  echo "<button type='button' class='close' data-dismiss='alert'>&times;</button>\n";
  echo "<h4>ACCESSO NEGATO</h4>I permessi del tuo account non sono sufficienti per l'operazione selezionata\n";
  echo "<i>(Action: ".$action.")</i>\n"; // <- debug
  echo "</div>\n";
 }
 return FALSE;
}


/* -[ Check permissions to show module ]------------------------------------- */
// @param $module : Module to check
function api_checkPermissionShowModule($module,$admin=TRUE){
 // if account is root return always true
 if($_SESSION['account']->id==1 && $admin==TRUE){return TRUE;}
 // if account typology is administrator return always true
 if($_SESSION['account']->typology==1 && $admin==TRUE){return TRUE;}
 // retrieve the permissions list
 $permissions=$GLOBALS['db']->query("SELECT * FROM settings_permissions WHERE module='".$module."' ORDER BY id ASC");
 while($permission=$GLOBALS['db']->fetchNextObject($permissions)){
  // get required groups
  $requiredgroups=$GLOBALS['db']->query("SELECT * FROM settings_permissions_join_accounts_groups WHERE idPermission='".$permission->id."'");
  while($required=$GLOBALS['db']->fetchNextObject($requiredgroups)){
   if($required->idGroup==0){
    $groups=$GLOBALS['db']->query("SELECT * FROM accounts_groups ORDER BY id ASC");
    while($group=$GLOBALS['db']->fetchNextObject($groups)){
     if(api_accountGrouprole($group->id)>=$required->idGrouprole){return TRUE;}
    }
   }else{
    if(api_accountGrouprole($required->idGroup)>=$required->idGrouprole){return TRUE;}
    // try in subgroups
    $subgroups=$GLOBALS['db']->query("SELECT * FROM accounts_groups WHERE idGroup='".$required->idGroup."'");
    while($subgroup=$GLOBALS['db']->fetchNextObject($subgroups)){
     if(api_accountGrouprole($subgroup->id)>=$required->idGrouprole){return TRUE;}
    }
   }
  }
 }
 return FALSE;
}


/* -[ Profile mail by account id ]------------------------------------------- */
// @param $account_id : ID of the account
function api_accountMail($account_id=NULL){
 if($account_id==0){return NULL;};
 if($account_id==NULL){$account_id=$_SESSION['account']->id;}
 $account=$GLOBALS['db']->queryUniqueObject("SELECT * FROM accounts_accounts WHERE id='".$account_id."'");
 if($account->account<>NULL){
  return $account->account;
 }else{
  return "[Not found]";
 }
}


/* -[ Profile name by account id ]------------------------------------------- */
// @param $account_id : ID of the account
function api_accountName($account_id=NULL){
 if($account_id==0){return NULL;};
 if($account_id==NULL){$account_id=$_SESSION['account']->id;}
 $account=$GLOBALS['db']->queryUniqueObject("SELECT * FROM accounts_accounts WHERE id='".$account_id."'");
 if($account->name<>NULL){
  return $account->name;
 }elseif($account->id){
  return "[ID ".$account->id."]";
 }else{
  return "[Not found]";
 }
}


/* -[ Profile Name inverted by account id ]---------------------------------- */
// @param $account_id : ID of the account
function api_accountNameInverted($account_id=NULL){
 $accountName=api_accountName($account_id);
 if(strrpos($accountName," ")!==FALSE){
  $pos=strrpos($accountName," ");
  $name=substr($accountName,$pos);
  $name=$name." ".substr($accountName,0,$pos);
 }
 return $name;
}


/* -[ Profile firstname by account id ]-------------------------------------- */
// @param $account_id : ID of the account
function api_accountFirstname($account_id=NULL){
 $name=api_accountName($account_id);
 if(strrpos($name," ")!==FALSE){
  $name=substr($name,0,strrpos($name," "));
 }
 return $name;
}


/* -[ Company name by company id ]------------------------------------------- */
// @param $idCompany : ID of the company
function api_companyName($idCompany,$division=TRUE){
 $company=$GLOBALS['db']->queryUniqueObject("SELECT * FROM accounts_companies WHERE id='".$idCompany."'");
 if($company->name<>NULL){
  $return=$company->company;
  if($division && $company->division<>NULL){
   $return.=" - ".$company->division;
  }
  return $return;
 }elseif($company->id){
  return "[ID ".$company->id."]";
 }else{
  return "[Not found]";
 }
}


/* -[ Division name by company id ]------------------------------------------ */
// @param $idCompany : ID of the company
function api_divisionName($idCompany){
 $company=$GLOBALS['db']->queryUniqueObject("SELECT * FROM accounts_companies WHERE id='".$idCompany."'");
 if($company->division<>NULL){
  return $company->division;
 }elseif($company->id){
  return "[ID ".$company->id."]";
 }else{
  return "[Not found]";
 }
}


/* -[ Group name by group id ]----------------------------------------------- */
// @param $idGroup : ID of the group
function api_groupName($idGroup,$description=FALSE){
 $group=$GLOBALS['db']->queryUniqueObject("SELECT * FROM accounts_groups WHERE id='".$idGroup."'");
 if($group->name<>NULL){
  $return=$group->name;
  if($description && $group->description<>NULL){
   $return.=" (".$group->description.")";
  }
  return $return;
 }elseif($group->id){
  return "[ID ".$group->id."]";
 }else{
  return "[Not found]";
 }
}


/* -[ Grouprole name by grouprole id ]--------------------------------------- */
// @param $idGrouprole : ID of the grouprole
function api_grouproleName($idGrouprole,$description=FALSE){
 $grouprole=$GLOBALS['db']->queryUniqueObject("SELECT * FROM accounts_grouproles WHERE id='".$idGrouprole."'");
 if($grouprole->name<>NULL){
  $return=$grouprole->name;
  if($description && $grouprole->description<>NULL){
   $return.=" (".$grouprole->description.")";
  }
  return $return;
 }elseif($grouprole->id){
  return "[ID ".$grouprole->id."]";
 }else{
  return "[Not found]";
 }
}


/* -[ Grouprole name by grouprole id ]--------------------------------------- */
// @param $idGrouprole : ID of the grouprole
function api_grouproleDescription($idGrouprole){
 $grouprole=$GLOBALS['db']->queryUniqueObject("SELECT * FROM accounts_grouproles WHERE id='".$idGrouprole."'");
 if($grouprole->description<>NULL){
  return $grouprole->description;
 }elseif($grouprole->name<>NULL){
  return $grouprole->name;
 }elseif($grouprole->id){
  return "[ID ".$grouprole->id."]";
 }else{
  return "[Not found]";
 }
}


/* -[ Language extend ]------------------------------------------------------ */
// @param $language : Short language
function api_languageExtend($language){
 switch(strtoupper($language)){
  case "D":$language="Deutsch";break;
  case "E":$language="English";break;
  case "I":$language="Italiano";break;
  case "F":$language="Francais";break;
 }
 return $language;
}


/* -[ Sex by id ]------------------------------------------------------------ */
// @param $idSex : id of the sex
function api_sexName($idSex,$lang=""){
 switch(strtoupper($lang)){
  case "EN":
   switch($idSex){
    case 1:$sex="Male";break;
    case 2:$sex="Female";break;
    default:$sex="Undefined";
   }
   break;
  case "DE":
   switch($idSex){
    case 1:$sex="Mannlich";break;
    case 2:$sex="weiblich";break;
    default:$sex="Undefined";
   }
   break;
  case "FR":
   switch($idSex){
    case 1:$sex="Homme";break;
    case 2:$sex="Femme";break;
    default:$sex="Undefined";
   }
   break;
  default:
   switch($idSex){
    case 1:$sex="Maschile";break;
    case 2:$sex="Femminile";break;
    default:$sex="Indefinito";
   }
 }
 return $sex;
}


/* -[ Avatar by account id ]------------------------------------------------- */
// @param $idAccount : ID of the account
function api_accountAvatar($idAccount=NULL){
 if($idAccount==NULL){$idAccount=$_SESSION['account']->id;}
 if(file_exists("../uploads/accounts/avatar_".$idAccount.".jpg")){
  return "../uploads/accounts/avatar_".$idAccount.".jpg";
 }else{
  return $GLOBALS['dir']."uploads/accounts/avatar.jpg";
 }
}


/* -[ Company name by company id ]------------------------------------------- */
// @param $company_id : ID of the company
function api_accountCompany($company_id=NULL){
 if($company_id){$company=$GLOBALS['db']->queryUniqueObject("SELECT * FROM accounts_companies WHERE id='".$company_id."'");}
 if($company->id>0){
  return stripslashes($company->company)." - ".stripslashes($company->division);
 }else{
  return "[Not found]";
 }
}


/* -[ Return the group role of an account ]---------------------------------- */
// @param $idGroup   : ID of the group
// @param $idAccount : ID of the account
function api_accountGrouprole($idGroup,$idAccount=NULL){
 if($idAccount==NULL){$idAccount=$_SESSION['account']->id;}
 if($idGroup>0 && $idAccount>0){
  $grouprole=$GLOBALS['db']->queryUniqueValue("SELECT idGrouprole FROM accounts_groups_join_accounts WHERE idGroup='".$idGroup."' AND idAccount='".$idAccount."'");
 }
 if($grouprole>0){return $grouprole;}else{return FALSE;}
}


/* -[ Check if account is in group ]----------------------------------------- */
// @param $idGroup   : ID of the group
// @param $idAccount : ID of the account
function api_checkAccountGroup($idGroup,$idAccount=NULL){
 if($idAccount==NULL){$idAccount=$_SESSION['account']->id;}
 if(api_accountGrouprole($idGroup,$idAccount)>0){return TRUE;}else{return FALSE;}
}


/* -[ Check if account is in company ]--------------------------------------- */
// @param $idCompany : ID of the company
// @param $idAccount : ID of the account
function api_checkAccountCompany($idCompany,$idAccount=NULL){
 $check=$GLOBALS['db']->queryUniqueValue("SELECT id,idCompany FROM accounts_accounts WHERE id='".$idAccount."' AND idCompany='".$idCompany."'");
 if($idAccount>0 && $idAccount==$check){return TRUE;}else{return FALSE;}
}


/* -[ Generate the pagination div ]------------------------------------------ */
// @param $recordsCountCount : Total number of records
// @param $recordsLimit      : Number of records for page
// @param $currentPage       : Number of the current page
// @param $scriptUrl         : URL of the php page
// @param $class_div         : Class for pagination div
// @param $class_ul          : Class for pagination ul
// @param $class_li          : Class for pagination ul li
// @param $class_li_active   : Class for pagination ul li of current page
// @param $class_li_disabled : Class for pagination ul li of disabled pages
function api_pagination($recordsCount=0,$recordsLimit=10,$currentPage=1,$url="?",$class_div="pagination-small pagination-right",$class_ul="",$class_li="",$class_li_active="active",$class_li_disabled="disabled"){
 if($recordsCount>0){
  $adjacents="2";
  if(substr($url,-1)<>"?"&&substr($url,-1)<>"&"){
   if(strpos($url,"?")){$url.="&";}else{$url.="?";}
  }
  $prev=$currentPage-1;
  $next=$currentPage+1;
  $lastpage=ceil($recordsCount/$recordsLimit);
  $lpm1=$lastpage-1;
  if($lastpage>1){
   $pagination="<div class='pagination ".$class_div."'>\n";
   $pagination.="<ul class='".$class_ul."'>\n";
   if($currentPage>1){
    $pagination.= "<li class='".$class_li."'><a href='".$url."p=".$prev."'>&laquo;</a></li>";
   }else{
    $pagination.= "<li class='".$class_li_disabled."'><span>&laquo;</span></li>";
   }
   if($lastpage<7+($adjacents*2)){
    for($counter=1;$counter<=$lastpage;$counter++){
     if($counter==$currentPage){$pagination.= "<li class='".$class_li_active."'><span>".$counter."</span></li>";}
      else{$pagination.= "<li class='".$class_li."'><a href='".$url."p=".$counter."'>".$counter."</a></li>";}
    }
   }elseif($lastpage>5+($adjacents*2)){
    if($currentPage<2+($adjacents*2)){
     for($counter=1;$counter<4+($adjacents*2);$counter++){
      if($counter==$currentPage){$pagination.= "<li class='".$class_li_active."'><span>".$counter."</span></li>";}
       else{$pagination.= "<li class='".$class_li."'><a href='".$url."p=".$counter."'>".$counter."</a></li>";}
     }
     $pagination.="<li class='".$class_li_disabled."'><span>&hellip;</span></li>";
     $pagination.="<li><a href='".$url."p=".$lpm1."'>".$lpm1."</a></li>";
     $pagination.="<li><a href='".$url."p=".$lastpage."'>".$lastpage."</a></li>";
    }elseif($lastpage-($adjacents*2)>$currentPage&&$currentPage>($adjacents*2)){
     $pagination.="<li class='".$class_li."'><a href='".$url."p=1'>1</a></li>";
     $pagination.="<li class='".$class_li."'><a href='".$url."p=2'>2</a></li>";
     $pagination.="<li class='".$class_li_disabled."'><span>&hellip;</span></li>";
     for($counter=$currentPage-$adjacents;$counter<=$currentPage+$adjacents;$counter++){
      if($counter==$currentPage){$pagination.= "<li class='".$class_li_active."'><span>".$counter."</span></li>";}
       else{$pagination.= "<li class='".$class_li."'><a href='".$url."p=".$counter."'>".$counter."</a></li>";}
     }
     $pagination.= "<li class='".$class_li_disabled."'><span>&hellip;</span></li>";
     $pagination.= "<li class='".$class_li."'><a href='".$url."p=".$lpm1."'>".$lpm1."</a></li>";
     $pagination.= "<li class='".$class_li."'><a href='".$url."p=".$lastpage."'>".$lastpage."</a></li>";
    }else{
     $pagination.= "<li class='".$class_li."'><a href='".$url."p=1'>1</a></li>";
     $pagination.= "<li class='".$class_li."'><a href='".$url."p=2'>2</a></li>";
     $pagination.= "<li class='".$class_li_disabled."'><span>&hellip;</span></li>";
     for($counter=$lastpage-(2+($adjacents*2));$counter<=$lastpage;$counter++){
      if($counter==$currentPage){$pagination.= "<li class='".$class_li_active."'><span>".$counter."</span></li>";}
       else{$pagination.= "<li class='".$class_li."'><a href='".$url."p=".$counter."'>".$counter."</a></li>";}
     }
    }
   }
   if($currentPage<$counter-1){
    $pagination.= "<li class='".$class_li."'><a href='".$url."p=".$next."'>&raquo;</a></li>";
   }else{
    $pagination.= "<li class='".$class_li_disabled."'><span>&raquo;</span></li>";
   }
   $pagination.= "</ul>\n";
  }
  // show pagination
  echo $pagination;
 }
}


/* -[ Image Scale ]---------------------------------------------------------- */
// @param $source           : Image source
// @param $output           : Output path
// @param $max_width        : Desider max width
// @param $max_height       : Desired max height
// @param $watermark        : Add watermark
// @param $thumbnail        : Create thumbnail
// @param $thumbnail_width  : Thumbnail width
// @param $thumbnail_height : Thumbnail height
function api_imageScale($source,$output,$max_width,$max_height,$watermark=FALSE,$thumbnail=FALSE,$thumbnail_width=0,$thumbnail_height=0){
 list($width,$height,$image_type)=getimagesize($source);
 // file type
 switch($image_type){
  case 1:$src=imagecreatefromgif($source);break;
  case 2:$src=imagecreatefromjpeg($source);break;
  case 3:$src=imagecreatefrompng($source);break;
  default:return null;
 }
 // scale image
 $x_ratio=$max_width/$width;
 $y_ratio=$max_height/$height;
 if(($width<=$max_width)&&($height<=$max_height)){
  $tn_width=$width;
  $tn_height=$height;
 }elseif(($x_ratio*$height)<$max_height){
  $tn_height=ceil($x_ratio*$height);
  $tn_width=$max_width;
 }else{
  $tn_width=ceil($y_ratio*$width);
  $tn_height=$max_height;
 }
 $tmp=imagecreatetruecolor($tn_width,$tn_height);
 $white_bg=imagecolorallocate($tmp, 255, 255, 255);
 imagefilledrectangle($tmp,0,0,$tn_width,$tn_height,$white_bg);
 imagecopyresampled($tmp,$src,0,0,0,0,$tn_width,$tn_height,$width,$height);
 // watermark
 if($watermark){
  // check if watermark file exist
  if(file_exists("../../uploads/images/copyright.png")){
   $watermark = imagecreatefrompng("../../uploads/images/copyright.png");
   imagealphablending($tmp,true);
   $x=imagesx($tmp)-imagesx($watermark)-20;
   $y=imagesy($tmp)-imagesy($watermark)-20;
   imagecopy($tmp,$watermark,$x,$y,0,0,imagesx($watermark),imagesy($watermark));
  }
 }
 // save scaled image output to file
 imagejpeg($tmp,$output,100);
 // thumbnail
 if($thumbnail){
  $thumbnail_scale_factor=min($thumbnail_width/$width,$thumbnail_height/$height);
  $thumbnail_new_width=ceil($width*$thumbnail_scale_factor);
  $thumbnail_new_height=ceil($height*$thumbnail_scale_factor);
  $thumbnail_new_x=($thumbnail_width-$thumbnail_new_width)/2;
  $thumbnail_new_y=($thumbnail_height-$thumbnail_new_height)/2;
  $thumbnail_tmp=imagecreatetruecolor($thumbnail_width,$thumbnail_height);
  $white_bg=imagecolorallocate($thumbnail_tmp,255,255,255);
  imagefilledrectangle($thumbnail_tmp,0,0,$thumbnail_width,$thumbnail_height,$white_bg);
  imagecopyresampled($thumbnail_tmp,$src,$thumbnail_new_x,$thumbnail_new_y,0,0,$thumbnail_new_width,$thumbnail_new_height,$width,$height);
  $thumbnail_output=substr($output,0,-4)."_thumb.jpg";
  imagejpeg($thumbnail_tmp,$thumbnail_output,75);
 }
}


/* -[ Avatar Resize ]-------------------------------------------------------- */
// @param $source : Image source
// @param $output : Output path
// @param $width  : Desider width
// @param $height : Desired height
function api_avatarResize($source,$output,$width=125,$height=125){
 list($image_width,$image_height,$image_type)=getimagesize($source);
 // file type
 switch($image_type){
  case 1:$src=imagecreatefromgif($source);break;
  case 2:$src=imagecreatefromjpeg($source);break;
  case 3:$src=imagecreatefrompng($source);break;
  default:return null;
 }
 $scale_factor=min($width/$image_width,$height/$image_height);
 $new_width=ceil($image_width*$scale_factor);
 $new_height=ceil($image_height*$scale_factor);
 $new_x=($width-$new_width)/2;
 $new_y=($height-$new_height)/2;
 $tmp=imagecreatetruecolor($width,$height);
 $white_bg=imagecolorallocate($tmp,255,255,255);
 imagefilledrectangle($tmp,0,0,$width,$height,$white_bg);
 imagecopyresampled($tmp,$src,$new_x,$new_y,0,0,$new_width,$new_height,$image_width,$image_height);
 // save resized avatar output to file
 imagejpeg($tmp,$output,100);
}


/* -[ Parse CSV file ]------------------------------------------------------- */
// @param $csvfile : File CSV
function api_parse_csv_file($csvfile) {
 $csv=Array();
 $rowcount=0;
 if(($handle=fopen($csvfile,"r"))!==FALSE){
  $max_line_length=defined('MAX_LINE_LENGTH')?MAX_LINE_LENGTH:100000;
  $header=fgetcsv($handle,$max_line_length);
  $header_colcount=count($header);
  while(($row=fgetcsv($handle,$max_line_length))!==FALSE){
   $row_colcount=count($row);
   if($row_colcount==$header_colcount){
    $entry=array_combine($header,$row);
    $csv[]=$entry;
   }else{
    error_log("CSV Reader: Invalid number of columns at line ".($rowcount+2)." (row ".($rowcount+1)."). Expected: ".$header_colcount." Got: ".$row_colcount);
    return null;
   }
   $rowcount++;
  }
  fclose($handle);
 }else{
  echo "CSV Reader: Could not read CSV ".$csvfile;
  return null;
 }
 return $csv;
}


/* -[ Clear file name ]------------------------------------------------------ */
// @param $file_name : File name to clear
function api_clearFileName($file_name){
 $file_name=str_replace(" ","-",$file_name);
 $file_name=strtolower(preg_replace("/[^A-Za-z0-9-._]/", "",$file_name));
 return $file_name;
}


/* -[ List recursive directory ]------------------------------------------- */
// @param $dir : Path of directory to list
function api_ls_recursive($dir){
 $result=array();
 $handle=scandir($dir);
 $directory=array();
 // sort fodlers before files
 foreach($handle as $value){if(is_dir($dir."/".$value)){$directory[]=$value;}}
 foreach($handle as $value){if(is_file($dir."/".$value)){$directory[]=$value;}}
 foreach($directory as $value){
  if($value=='.'||$value=='..'){continue;} // skip
  if(is_file("$dir/$value")){$result[]="$dir/$value";continue;} // add file to array
  $result[]="$dir/$value"; // add directory to array
  if(count(scandir("$dir/$value"))==2){continue;} // skip if only . and ..
  foreach(api_ls_recursive("$dir/$value") as $value){ // recursive ls
   $result[]=$value;
  }
 }
 return $result;
}


/* -[ Remove recursive directory ]------------------------------------------- */
// @param $dir : Path of directory to delete
// @param $execute : If false print debug
function api_rm_recursive($dir,$execute=FALSE){
 $count=0;
 $handle=scandir($dir);
 $directory=array();
 // sort files before folders
 foreach($handle as $value){if(is_file($dir."/".$value)){$directory[]=$value;}}
 foreach($handle as $value){if(is_dir($dir."/".$value)){$directory[]=$value;}}
 // cycle
 foreach($directory as $value){
  // skip
  if($value=="."||$value==".."){continue;}
  // delete file
  if(is_file($dir."/".$value)){
   if($execute){unlink($dir."/".$value);}
    else{echo "unlink: ".$dir."/".$value."<br>";}
   $count++;
   continue;
  }
  // remove directory if empty
  if(count(scandir($dir."/".$value))==2){
   if($execute){rmdir($dir."/".$value);}
    else{echo "rmdir: ".$dir."/".$value."<br>";}
   $count++;
   continue;
  }
  // recursive rm
  $count+=api_rm_recursive($dir."/".$value,$execute);
 }
 // remove emptied directory
 if($execute){rmdir($dir);}
  else{echo "rmdir: ".$dir."<br>";}
 $count++;
 return $count;
}


/* -[ Extension Icon ]------------------------------------------------------- */
// @param $ext : File extension
function api_extensionIcon($ext){
 switch($ext){
  // images
  case "jpg":
  case "png":
  case "gif":
  case "jpeg":
   $return="<i class='icon-picture'></i>";
   break;
  // archives
  case "zip":
  case "rar":
  case "gzip":
   $return="<i class='icon-folder-close'></i>";
   break;
  // music
  case "wav":
  case "mp3":
   $return="<i class='icon-music'></i>";
   break;
  // pdf
  case "pdf":
   $return="<i class='icon-book'></i>";
   break;
  // documents
  case "doc":
  case "docx":
  case "odt":
  case "xls":
  case "xlsx":
  case "ods":
  case "ppt":
  case "pptx":
  case "odp":
   $return="<i class='icon-align-left'></i>";
   break;
  // scripts
  case "sql":
  case "bat":
  case "php":
  case "sh":
   $return="<i class='icon-cog'></i>";
   break;
  // default
  default:
   $return="<i class='icon-file'></i>";
 }
 return $return;
}


/* -[ Convert Measurements Unit ]-------------------------------------------- */
// @param $number : Number to be converted
// @param $unit_from : Measurement unit from (KG,T,LB | MM,IN)
// @param $unit_to : Measurement unit to (KG,T,LB | MM,IN)
// @param $decimals : Number of decimals
function api_convertUnit($number,$unit_from,$unit_to,$decimals=3){
 // number base format in the database (weight=kg | lenght=mm)
 $number_base=0;
 $number_return=0;
 // convert number from unit_from to number base
 switch(strtoupper($unit_from)){
  case "T":$number_base=$number*1000;break;
  case "LB":$number_base=$number/2.2046;break;
  case "IN":$number_base=$number/0.039370;break;
  default:$number_base=$number;
 }
 // convert number from number_base to unit_to
 switch(strtoupper($unit_to)){
  case "T":$number_return=$number_base/1000;break;
  case "LB":$number_return=$number_base*2.2046;break;
  case "IN":$number_return=$number_base*0.039370;break;
  default:$number_return=$number_base;
 }
 // set default decimals
 if($decimals===NULL){
  switch(strtoupper($unit_to)){
   case "T":$decimals=0;break;
   case "LB":$decimals=0;break;
   case "IN":$decimals=3;break;
   default:$decimals=3;
  }
 }
 // return converted number
 $number_return=number_format($number_return,$decimals,".","");
 if($decimals>0){
  $number_return=preg_replace('/(\.[0-9]+?)0*$/','$1',$number_return);
  $number_return=preg_replace('/\.0+$/','',$number_return);
 }
 return $number_return;
}


/* -[ Restore MySQL Dump ]----------------------------------------------------------------- */
// @string $file : mysql dump file path
function api_restoreMysqlDump($file){
 if(!file_exists($file)){return FALSE;}
 $query="";
 $lines=file($file);
 foreach($lines as $line){
  // skip comments
  if(substr($line,0,2)=="--" || $line==""){continue;}
  $query.=$line;
  // search for query end signal
  if(substr(trim($line),-1,1)==';'){
   // execute query
   $GLOBALS['db']->execute($query);
   $query="";
  }
 }
 return TRUE;
}



/* -[ Query Order ]---------------------------------------------------------- */
// @string $default : default order fields and methods
function api_queryOrder($default=NULL){
 // acquire variables
 $query_order_field=$_GET['of'];
 $query_order_mode=$_GET['om'];
 if(!$query_order_field){
  return " ORDER BY ".$default;
 }else{
  if($query_order_mode==1){$query_order_mode=" ASC";}else{$query_order_mode=" DESC";}
  return " ORDER BY ".$query_order_field.$query_order_mode;
 }
}








/* ---------------------------[ DOCUMENTATE ]-------------------------------- */



/* -[ Icon ]----------------------------------------------------------------- */
// @string $icon : bootstrap icon glyphs
// @string $style : manual styles tag
function api_icon($icon,$style=NULL){
 if($icon==NULL){return FALSE;}
 $return="<i class='".$icon."' style='".$style."'></i>";
 return $return;
}


/* -[ Navigation Tab ]------------------------------------------------------- */
// @string $label : label of the tab
// @string $url : link url
// @string $get : additional get parameters for link (&key=value)
// @string $class : input css class
// @array $dropdown : array of navigation tabs
// @boolean $enabled : enable the navigation tab (true) or not
function api_navigationTab($label,$url=NULL,$get=NULL,$class=NULL,$dropdown=NULL,$enabled=TRUE){
 if(strlen($label)==0){return FALSE;}
 $nt=new stdClass();
 $nt->label=$label;
 $nt->url=$url;
 $nt->get=$get;
 $nt->class=$class;
 $nt->dropdown=$dropdown;
 $nt->enabled=$enabled;
 return $nt;
}


/* -[ Navigation ]----------------------------------------------------------- */
// @array $nt_array : array of navigation tabs
// @string $class : navigation css class
function api_navigation($nt_array,$class=NULL){
 // open navigation
 echo "<!-- navigation tabs -->\n";
 echo "<ul class='nav nav-tabs ".$class."'>\n";
 // show tabs
 if(is_array($nt_array)){
  // show field
  foreach($nt_array as $nt){
   if(!is_object($nt)){continue;}
   // check dropdown menu
   if(is_array($nt->dropdown)){
    $dropdown=TRUE;
    $nt->label.=" <b class='caret'></b>";
   }else{$dropdown=FALSE;}
   // check active
   $active=FALSE;
   if(strlen($nt->url)==0){$nt->url="#";}
   echo " <li";
   if(substr($nt->url,0,(strpos($nt->url,"?")>0)?strpos($nt->url,"?"):strlen($nt->url))==api_baseName()){
    $active=TRUE;
    parse_str(parse_url($nt->url,PHP_URL_QUERY),$gets);
    if(count($gets)>0){
     foreach($gets as $key=>$value){
      if($_GET[$key]<>$value){$active=FALSE;}
     }
    }
   }
   // check active disabled and dropdown
   if($active){echo " class='active ".$nt->class."'><a href='#'";}
    elseif(!$nt->enabled){echo " class='disabled ".$nt->class."'><a href='#'";}
    elseif($dropdown){echo " class='dropdown ".$nt->class."'><a class='dropdown-toggle' data-toggle='dropdown' href='#'";}
    else{echo " class='".$nt->class."'><a href='".$nt->url.$nt->get."'";}
   // show label
   echo ">".$nt->label."</a>";
   // dropdown items
   if(is_array($nt->dropdown)){
    echo "\n  <ul class='dropdown-menu'>\n";
    foreach($nt->dropdown as $ntd){
     if(!is_object($ntd)){continue;}
     echo "   <li";
     if($ntd->enabled){
      echo "><a href='".$ntd->url.$ntd->get."'";
     }else{
      echo " class='disabled ".$ntd->class."'><a href='#'";
     }
     echo ">".$ntd->label."</a></li>\n";
    }
    echo "  </ul>\n ";
   }
   echo "</li>\n";
  }
 }
 // close navigation
 echo "</ul><!-- /navigation tabs -->\n\n";
}


/* -[ Table Header ]--------------------------------------------------------- */
// @string $name : column header name
// @string $class : column header css class
// @string $width : column header width
// @integer $colspan : column span
function api_tableHeader($name,$class=NULL,$width=NULL,$order=NULL,$colspan=1){
 if(strlen($name)==0){return FALSE;}
 $th=new stdClass();
 $th->name=$name;
 $th->class=$class;
 $th->width=$width;
 $th->order=$order;
 $th->colspan=$colspan;
 return $th;
}


/* -[ Table Field ]---------------------------------------------------------- */
// @string $content : field content
// @string $class : field css class
// @integer $colspan : column span
function api_tableField($content,$class=NULL,$colspan=1){
 if(strlen($content)==0){return FALSE;}
 $td=new stdClass();
 $td->content=$content;
 $td->class=$class;
 $td->colspan=$colspan;
 return $td;
}


/* -[ Table Row ]------------------------------------------------------------ */
// @array $fields : row field object
// @string $class : row css class
function api_tableRow($fields,$class=NULL){
 if(!is_array($fields)){return FALSE;}
 $tr=new stdClass();
 $tr->fields=$fields;
 $tr->class=$class;
 return $tr;
}


/* -[ Table ]---------------------------------------------------------------- */
// @array $th_array : array of column headers
// @array $tr_array : array of rows
// @string $unvalued : text to show if no results
// @boolean $sortable : show headers sortable link (true) or not
// @string $get : additional get parameters for sortable link (&key=value)
// @string $class : table css class
function api_table($th_array,$tr_array,$unvalued=NULL,$sortable=FALSE,$get=NULL,$class=NULL){
 $return=TRUE;
 // open table
 echo "<table class='table table-striped table-hover table-condensed ".$class."'>\n";
 // open head
 if(is_array($th_array)){
  echo "<thead>\n <tr>\n";
  // show headers
  foreach($th_array as $th){
   if(!is_object($th)){$return=-1;}
   echo "  <th class='".$th->class."' width='".$th->width."' colspan='".$th->colspan."'>";
   if($sortable && $th->order<>NULL){
    // show order link
    if($th->order==$_GET['of']){if($_GET['om']==1){$order=0;}else{$order=1;}}else{$order=1;}
    // check order
    if($th->order==$_GET['of']){
     if($_GET['om']==0){echo api_icon("icon-circle-arrow-down","margin-top:-0.5px;")."&nbsp;";}
     if($_GET['om']==1){echo api_icon("icon-circle-arrow-up","margin-top:-0.5px;")."&nbsp;";}
    }
    echo "<a href='".api_baseName()."?of=".$th->order."&om=".$order.$get."'>";
   }
   echo $th->name;
   if($sortable){echo "</a>";}
   echo "</th>\n";
  }
  echo " </tr>\n";
  // close head
  echo "</thead>\n";
 }
 // open body
 echo "<tbody>\n";
 if(is_array($tr_array)){
  foreach($tr_array as $tr){
   // show rows
   if(!is_object($tr)){$return=-2;}
   echo " <tr class='".$tr->class."'>\n";
   // show fields
   if(is_array($tr->fields)){
    foreach($tr->fields as $td){
     // show field
     if(!is_object($td)){$return=-3;}
     echo "  <td class='".$td->class."' colspan='".$td->colspan."'>".$td->content."</td>\n";
    }
   }
   echo " </tr>\n";
  }
 }
 // show no value text
 if(!count($tr_array) && $unvalued<>NULL){echo "<tr><td colspan=".count($th_array).">".$unvalued."</td></tr>\n";}
 // close body
 echo "</tbody>\n";
 // close table
 echo "</table>\n";
 return $return;
}


/* -[ Form Field ]----------------------------------------------------------- */
// @string $type : hidden, text, password, checkbox, radio, select, textarea, file
// @string $name : name of the form input (spaces not allowed)
// @string $label : label for the field
// @string $value : default value
// @string $class : input css class
// @string $placeholder : placeholder message
// @array $options : array of fields options
// @boolean $disabled : disable input field (true) or not
// @integer $rows : number of textarea rows
function api_formField($type,$name,$label=NULL,$value=NULL,$class=NULL,$placeholder=NULL,$options=NULL,$disabled=FALSE,$rows=7){
 if(strlen($type)==0 || strlen($name)==0){return FALSE;}
 $ff=new stdClass();
 $ff->type=$type;
 $ff->name=$name;
 $ff->label=$label;
 $ff->value=$value;
 $ff->class=$class;
 $ff->placeholder=$placeholder;
 $ff->options=$options;
 $ff->disabled=$disabled;
 $ff->rows=$rows;
 return $ff;
}


/* -[ Form Field Options ]--------------------------------------------------- */
// @string $value : option value
// @string $label : label for the field option
// @boolean $checked : checked or selected field option (true) or not
// @boolean $disabled : disable field option (true) or not
function api_formFieldOption($value,$label,$checked=FALSE,$disabled=FALSE){
 if(strlen($value)==0 || strlen($label)==0){return FALSE;}
 $fo=new stdClass();
 $fo->value=$value;
 $fo->label=$label;
 $fo->checked=$checked;
 $fo->disabled=$disabled;
 return $fo;
}


/* -[ Form Control ]--------------------------------------------------------- */
// @string $type : submit, reset, button, link
// @string $label : label for the control
// @string $class : input css class
// @string $url : link url
// @string $confirm : confirmation message to approve if not null
// @boolean $disabled : disable control (true) or not
function api_formControl($type,$label,$class=NULL,$url=NULL,$confirm=NULL,$disabled=FALSE){
 if(strlen($type)==0 || strlen($label)==0){return FALSE;}
 $fc=new stdClass();
 $fc->type=$type;
 $fc->label=$label;
 $fc->class=$class;
 $fc->url=$url;
 $fc->confirm=$confirm;
 $fc->disabled=$disabled;
 return $fc;
}


/* -[ Form ]----------------------------------------------------------------- */
// @array $ff_array : array of form fields
// @array $fc_array : array of form controls
// @string $action : form action url
// @boolean $method : get, post
// @string $name : form name
// @string $class : form css class
function api_form($ff_array,$fc_array,$action,$method="get",$name="form",$class="form-horizontal"){
 if(strlen($action)==0 || !(strtolower($method)=="get" || strtolower($method)=="post")){return FALSE;}
 // open form
 echo "<form name='".$name."' action='".$action."' method='".$method."' class='".$class."'>\n\n";
 // show fields
 if(is_array($ff_array)){
  // show field
  foreach($ff_array as $index=>$ff){
   if(!is_object($ff)){continue;}
   $options=FALSE;
   // open group
   echo "<div id='field_".$ff->name."' class='control-group'>\n";
   if($ff->label<>NULL){
    echo " <label class='control-label'>".$ff->label."</label>\n";
    echo " <div class='controls'>\n";
   }
   // show input
   switch(strtolower($ff->type)){
    // hidden, text, password
    case "hidden":
    case "text":
    case "password":
     if(!$ff->label){echo "  ";}
     echo "  <input type='".$ff->type."' name='".$ff->name."' class='".$ff->class."' placeholder=\"".$ff->placeholder."\" value=\"".$ff->value."\"";
     if($ff->disabled){echo " disabled='disabled'";}
     echo ">\n";
     if(!$ff->label){echo "\n";}
     break;
    // checkbox, radio
    case "checkbox":
    case "radio":
     $options=TRUE;
     break;
    // select
    case "select":
     $options=TRUE;
     // open select
     echo "  <select name='".$ff->name."' class='".$ff->class."'";
     if($ff->disabled){echo " disabled='disabled'";}
     echo ">\n";
     break;
    // textarea
    case "textarea":
     echo "  <textarea name='".$ff->name."' rows='".$ff->rows."' class='".$ff->class."' placeholder=\"".$ff->placeholder."\"";
     if($ff->disabled){echo " disabled='disabled'";}
     echo ">".$ff->value."</textarea>\n";
     break;
    // file
    case "file":
     if($ff->placeholder==NULL){$ff->placeholder="Select a file to upload";}
     echo "  <input type='file' id='file_".$index."' name='".$ff->name."' style='display:none'>\n";
     echo "  <div class='input-append'>\n";
     echo "   <input type='text' id='file_".$index."_show' class='".$ff->class."' placeholder=\"".$ff->placeholder."\" onDblClick=\"$('input[id=file_".$index."]').click();\" readonly>\n";
     echo "   <a class='btn' onClick=\"$('input[id=file_".$index."]').click();\">Sfoglia</a>\n";
     echo "  </div>\n";
     break;
   }
   // show options
   if($options){
    // if single option build array
    if(!is_array($ff->options)){if(is_object($ff->options)){$ff->options=array($ff->options);}}
    if(is_array($ff->options)){
     // show option
     foreach($ff->options as $fo){
      // show checkbox or radio option
      switch(strtolower($ff->type)){
       case "checkbox":
       case "radio":
        echo "  <label class='".$ff->type." ".$ff->class."'>";
        echo "<input type='".$ff->type."' name='".$ff->name."' value=\"".$fo->value."\"";
        if($fo->checked){echo " checked='checked'";}
        if($fo->disabled){echo " disabled='disabled'";}
        echo ">".$fo->label."</label>\n";
        break;
       // show select option
       case "select":
        echo "   <option value=\"".$fo->value."\"";
        if($fo->checked){echo " selected='selected'";}
        echo ">".$fo->label."</option>\n";
        break;
      }
     }
    }
   }
   // close select
   if(strtolower($ff->type)=="select"){echo "  </select>\n";}
   // close group
   if($ff->label<>NULL){echo " </div>\n";}
   echo "</div>\n\n";
   // file script
   if(strtolower($ff->type)=="file"){
    echo "<script type='text/javascript'>\n";
    echo " $('input[id=file_".$index."]').change(function(){\n";
    echo "  $('#file_".$index."_show').val($(this).val());\n";
    echo " });\n";
    echo "</script>\n\n";
   }
  }
  // show controls
  if(is_array($fc_array)){
   // open group
   echo "<div class='control-group'>\n";
   echo " <div class='controls'>\n";
   // show control
   foreach($fc_array as $fc){
    switch(strtolower($fc->type)){
     // submit
     case "submit":
      echo "  <input type='submit' name='submit' class='btn btn-primary ".$fc->class."' value='".$fc->label."'>\n";
      break;
     // reset
     case "reset":
      echo "  <input type='reset' name='submit' class='btn ".$fc->class."' value='".$fc->label."'>\n";
      break;
     // button, link
     case "button":
     case "link":
      echo "  <a href='".$fc->url."' ";
      if(strtolower($fc->type)=="button"){echo "class='btn ".$fc->class."'";}
       else{echo "class='".$fc->class."'";}
      if(strlen($fc->confirm)){echo " onClick=\"return confirm('".$fc->confirm."')\"";}
      echo ">".$fc->label."</a>\n";
      break;
    }
   }
  }
  // close group
  echo " </div>\n</div>\n\n";
  // close form
  echo "</form>\n";
 }
}


/* -[ Modal Window ]--------------------------------------------------------- */
// @string $id : id of the modal window
// @string $header : header of the modal window
// @string $body : body of the modal window
// @string $footer : footer of the modal window
// @string $class : modal window css class
function api_modal($id,$header,$body,$footer=NULL,$class=NULL){
 if(strlen($id)==0 || strlen($body)==0){return FALSE;}
 // open modal window
 echo "<!-- modal window ".$id." -->\n";
 echo "<div id='modal_".$id."' class='modal hide fade ".$class."' role='dialog' aria-hidden='true'>\n";
 // modal window header
 echo " <div class='modal-header'>\n";
 echo "  <button type='button' class='close' data-dismiss='modal' aria-hidden='true'>&times;</button>\n";
 if(strlen($header)>0){echo "  <h4>".$header."</h4>\n";}
 echo " </div>\n";
 // modal window body
 echo " <div class='modal-body'>\n".$body."\n </div>\n";
 // modal window footer
 if(strlen($footer)>0){echo " <div class='modal-footer'>\n".$footer."\n </div>\n";}
 // close modal window
 echo "</div><!-- /modal window -->\n\n";
}


/* -[ Modal Window Link ]---------------------------------------------------- */
// @string $id : id of the modal window
// @string $label : label of the link
// @string $class : modal link css class
function api_modalLink($id,$label,$class=NULL){
 if(strlen($id)==0 || strlen($label)==0){return FALSE;}
 return "<a href='#modal_".$id."' data-toggle='modal' class='".$class."' id='modal-link_".$id."'>".$label."</a>";
}

?>