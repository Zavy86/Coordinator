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
// load core language file
api_loadLocaleFile("../");
// build class
$html=new HTML();
$db=new DB($db_host,$db_user,$db_pass,$db_name);


/* -[ Check Debug ]---------------------------------------------------------- */
if($_SESSION['account']->debug){
 error_reporting(E_ALL & ~E_NOTICE);
 ini_set('display_errors',1);
}


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
/*if(api_baseName()<>"logs_list.php"&&$_SESSION['account']->administrator){
 if($GLOBALS['db']->countOf("logs_logs","new='1' AND typology>'1'")>0){
  $GLOBALS['alert']->alert="newLogs";
  $GLOBALS['alert']->class="alert-info";
 }
}*/


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
function api_loadLocaleFile($path=NULL,$language=NULL){
 if($path===NULL){$path=".";}
 if($language===NULL){$language=$_SESSION['language'];}
 if($language<>NULL && file_exists($path."/languages/".$language.".xml")){
  // load choised locale file
  $xml=simplexml_load_file($path."/languages/".$language.".xml");
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
 if(strlen($text)==0){
  $parameters_txt=NULL;
  if($parameters!==NULL){
   if(is_array($parameters)){
    $count=0;
    foreach($parameters as $parameter){
     $parameters_txt.="|".$parameter;
    }
   }
  }
  $text="{".$key.$parameters_txt."}";
  return $text;
 }
 // replace parameters
 if($parameters!==NULL){
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
   echo "window.setTimeout(function(){\$('#alert-message').alert('close');},10000);\n";
   echo "</script>\n";
  }
  // auto close if alert-info
  if($class=="alert-info"){
   echo "<script type=\"text/javascript\">\n";
   echo "window.setTimeout(function(){\$('#alert-message').alert('close');},20000);\n";
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


/* -[ Get user hostname ]---------------------------------------------------- */
function api_hostName(){
 return strtoupper(gethostbyaddr($_SERVER['REMOTE_ADDR']));
}


/* -[ Update Temp Token ]---------------------------------------------------- */
function api_updateTempToken(){
 $temp_token=md5(date("Y-m-d H:i:s"));
 $GLOBALS['db']->execute("UPDATE settings_settings SET value='".$temp_token."' WHERE code='temp_token'");
 return $temp_token;
}


/* -[ Send notification ]---------------------------------------------------- */
function api_notification_send($idAccount,$module,$action,$subject,$message,$link=NULL,$hash=NULL){
 if($idAccount==NULL || strlen($module)==0 || strlen($subject)==0 || strlen($message)==0){return FALSE;}
 // make random hash if not exist
 if($hash==NULL){$hash=md5(date('YdmHsi').api_randomString());}
 // build query
 $query="INSERT INTO logs_notifications
  (idAccount,timestamp,module,action,subject,message,link,hash,status) VALUES
  ('".$idAccount."','".date("Y-m-d H:i:s")."','".$module."','".$action."',
   '".addslashes($subject)."','".addslashes($message)."','".$link."','".$hash."','1')";
 // execute query
 $GLOBALS['db']->execute($query);
 // set id to last inserted id
 $q_id=$GLOBALS['db']->lastInsertedId();
 // return hash or false
 if($q_id>0){return $hash;}
 else{return FALSE;}
}

/* -[ Send notification to all accounts ]------------------------------------ */
function api_notification_all($module,$action,$subject,$message,$link=NULL){
 $hash=md5(date('YdmHsi').api_randomString());
 $accounts=$GLOBALS['db']->query("SELECT * FROM accounts_accounts");
 while($account=$GLOBALS['db']->fetchNextObject($accounts)){
  api_notification_send($account->id,$module,$action,$subject,$message,$link,$hash);
 }
 return $hash;
}

/* -[ Send notification to administrators ]---------------------------------- */
function api_notification_administrators($module,$action,$subject,$message,$link=NULL){
 $hash=md5(date('YdmHsi').api_randomString());
 $accounts=$GLOBALS['db']->query("SELECT * FROM accounts_accounts WHERE typology='1'");
 while($account=$GLOBALS['db']->fetchNextObject($accounts)){
  api_notification_send($account->id,$module,$action,$subject,$message,$link,$hash);
 }
 return $hash;
}

/* -[ Send notification to group members ]----------------------------------- */
function api_notification_group($idGroup,$idGrouprole,$module,$action,$subject,$message,$link=NULL){
 $hash=md5(date('YdmHsi').api_randomString());
 $groups="idGroup='".$idGroup."'";
 // check for subgroups
 $subgroups=$GLOBALS['db']->query("SELECT * FROM accounts_groups WHERE idGroup='".$idGroup."'");
 while($subgroup=$GLOBALS['db']->fetchNextObject($subgroups)){$groups.=" OR idGroup='".$subgroup->id."'";}
 // get accounts in groups
 $accounts=$GLOBALS['db']->query("SELECT distinct(idAccount) FROM accounts_groups_join_accounts WHERE (".$groups.") AND idGrouprole>='".$idGrouprole."'");
 while($account=$GLOBALS['db']->fetchNextObject($accounts)){
  api_notification_send($account->idAccount,$module,$action,$subject,$message,$link,$hash);
 }
 return $hash;
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
// @param $format : datetime format
function api_timestampFormat($timestamp,$format="Y-m-d H:i"){
 if($timestamp==NULL){return NULL;}
 $datetime=new DateTime($timestamp);
 $return=$datetime->format($format);
 // if language not default
 if($_SESSION['language']<>"default"){
  $days=array("Monday","Tuesday","Wednesday","Thursday","Friday","Saturday","Sunday");
  $locale_days=array(api_text("day-monday"),api_text("day-tuesday"),api_text("day-wednesday"),api_text("day-thursday"),api_text("day-friday"),api_text("day-saturday"),api_text("day-sunday"));
  $months=array("January","February","March","April","May","July","August","September","October","November","December");
  $locale_months=array(api_text("month-january"),api_text("month-february"),api_text("month-march"),api_text("month-april"),api_text("month-may"),api_text("month-june"),api_text("month-july"),api_text("month-august"),api_text("month-september"),api_text("month-october"),api_text("month-november"),api_text("month-december"));
  // replace days
  if(strpos($format,"l")!==FALSE){$return=str_replace($days,$locale_days,$return);}
  // replace three digit day
  if(strpos($format,"D")!==FALSE){
   array_walk($days,"api_timestampFormatThreeDigit");
   array_walk($locale_days,"api_timestampFormatThreeDigit");
   $return=str_replace($days,$locale_days,$return);
  }
  // replace month
  if(strpos($format,"F")!==FALSE){$return=str_replace($months,$locale_months,$return);}
  // replace three digit month
  if(strpos($format,"M")!==FALSE){
   array_walk($months,"api_timestampFormatThreeDigit");
   array_walk($locale_months,"api_timestampFormatThreeDigit");
   $return=str_replace($months,$locale_months,$return);
  }
 }
 return $return;
}

/* -[ Timestamp Format Three Digit ]----------------------------------------- */
// return three digit from string
function api_timestampFormatThreeDigit(&$string){
 $string=substr($string,0,3);
}

/* -[ Timestamp difference ]------------------------------------------------- */
// @param $timestamp_a : MySql timestamp from
// @param $timestamp_b : MySql timestamp to
// @param $format : Format to output: Seconds, mInutes, Hours, Days, Weeks, Months, Years
function api_timestampDifference($timestamp_a,$timestamp_b,$format="S"){
 if($timestamp_a==NULL || $timestamp_b==NULL){return NULL;}
 $seconds=strtotime($timestamp_b)-strtotime($timestamp_a);
 // format result
 switch(strtoupper($format)){
  case "I":$result=$seconds/60;break;
  case "H":$result=$seconds/3600;break;
  case "D":$result=$seconds/86400;break;
  case "W":$result=$seconds/604800;break;
  case "M":$result=$seconds/2592000;break;
  case "Y":$result=$seconds/31536000;break;
  default:$result=$seconds;
 }
 return number_format($result,2);
}

/* -[ Timestamp Difference Format ]------------------------------------------ */
// @integere $seconds : differences in seconds
function api_timestampDifferenceFormat($difference,$showSeconds=TRUE){
 if($difference==NULL){return FALSE;}
 $return=NULL;
 $days=intval(intval($difference)/(3600*24));
 if($days==1){$return.=$days." ".api_text("day").", ";}
 elseif($days>1){$return.=$days." ".api_text("days").", ";}
 $hours=(intval($difference)/3600)%24;
 if($hours==1){$return.=$hours." ".api_text("hour").", ";}
 elseif($hours>1){$return.=$hours." ".api_text("hours").", ";}
 $minutes=(intval($difference)/60)%60;
 if($minutes==1){$return.=$minutes." ".api_text("minute").", ";}
 elseif($minutes>1){$return.=$minutes." ".api_text("minutes").", ";}
 if($showSeconds || intval($difference)<60){
  $seconds=intval($difference)%60;
  if($seconds==1){$return.=$seconds." ".api_text("second").", ";}
  elseif($seconds>1){$return.=$seconds." ".api_text("seconds").", ";}
 }
 return substr($return,0,-2);
}

/* -[ Timestamp Dates form Week ]-------------------------------------------- */
// @param $week : week number (from 1 to 52)
// @param $year : year in YYYY format
// return  : Array of date from [0] and date to [1]
function api_timestampDateFromWeek($week,$year){
 $day=new DateTime();
 $day->setISODate($year,$week);
 $return[0]=$day->format('Y-m-d');
 $day->modify('+6 days');
 $return[1]=$day->format('Y-m-d');
 return $return;
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
// @string $to_mail : Recipient mail
// @string $message : Content of mail
// @string $subject : Subject of mail
// @booelan $html : Send mail in HTML format
// @string $from_mail : Sender mail
// @string $from_name : Sender name
function api_sendmail($to_mail,$message,$subject="",$html=FALSE,$from_mail="",$from_name=""){
 if($to_mail==NULL){return FALSE;}
 // headers
 $eol="\n";
 if($from_mail==""){$from_mail=api_getOption('owner_mail');}
 if($from_name==""){$from_name=api_getOption('owner_mail_from');}
 $headers= "MIME-Version: 1.0".$eol;
 $headers.="Content-type: text/plain; Charset=UTF-8".$eol;
 $headers.="From: ".$from_name." <".$from_mail.">".$eol;
 $headers.="Reply-To: ".$from_mail.$eol;
 $headers.="Return-Path: ".$from_mail.$eol;
 // subject
 if($subject==""){$subject="Coordinator - Communication";}
 // message
 $message.=$eol.$eol."--".$eol."Questo messaggio è stato generato automaticamente da Coordinator per conto di ".api_getOption('owner').", si prega di non rispondere.".$eol;
 // check HTML
 if($html){
  $mail_random_hash=md5(date('r',time()));
  $headers.="MIME-Version: 1.0".$eol;
  $headers.="Content-Type: multipart/alternative; boundary=\"PHP-alt-".$mail_random_hash."\"".$eol.$eol;
  $headers.="This is a multi-part message in MIME format".$eol;
  // message
  $mail_message=$eol."--PHP-alt-".$mail_random_hash.$eol.
   "Content-Type: text/plain; charset=utf-8".$eol.
   "Content-Transfer-Encoding: 7bit".$eol.$eol;
  $mail_message.=strip_tags($message).$eol;
  $mail_message.=$eol."--PHP-alt-".$mail_random_hash.$eol.
   "Content-Type: text/html; charset=utf-8".$eol.
   "Content-Transfer-Encoding: 7bit".$eol.$eol;
  $mail_message.=nl2br($message).$eol;
  $mail_message.=$eol."--PHP-alt-".$mail_random_hash."--".$eol;
  $message=$mail_message;
 }
 // check for asynchronous sendmail
 if(api_getOption("sendmail_asynchronous")){
  // insert mail into database
  $query="INSERT INTO logs_mails
   (`to`,`subject`,`message`,`headers`,`addDate`,`addIdAccount`) VALUES
   ('".$to_mail."','".addslashes($subject)."','".addslashes($message)."',
    '".addslashes($headers)."','".date('Y-m-d H:i:s')."','".api_accountId()."')";
  // execute query
  $GLOBALS['db']->execute($query);
  // set id to last inserted id
  $q_id=$GLOBALS['db']->lastInsertedId();
  if($q_id>0){return TRUE;}else{return FALSE;}
 }else{
  // sendmail
  return mail($to_mail,$subject,$message,$headers);
 }
}


/* -[ Check permissions ]---------------------------------------------------- */
// @param $module : Module to check
// @param $action : Action to check
// @param $idAccount : ID of the account
function api_checkPermission($module,$action,$alert=FALSE,$admin=TRUE,$idAccount=NULL){
 if($idAccount===0 || $idAccount==="0"){return NULL;}
 if($idAccount===NULL){$idAccount=$_SESSION['account']->id;}
  // if account is root return always true
 if($idAccount==1){return TRUE;}
 // if account typology is administrator return always true
 if($admin==TRUE && $idAccount==$_SESSION['account']->id && $_SESSION['account']->typology==1){return TRUE;}
 // retrieve the permission id
 $idPermission=$GLOBALS['db']->queryUniqueValue("SELECT id FROM settings_permissions WHERE module='".$module."' AND action='".$action."'");
 // get required groups
 $requiredgroups=$GLOBALS['db']->query("SELECT * FROM settings_permissions_join_accounts_groups WHERE idPermission='".$idPermission."'");
 while($required=$GLOBALS['db']->fetchNextObject($requiredgroups)){
  if($required->idGroup==0){
   $groups=$GLOBALS['db']->query("SELECT * FROM accounts_groups");
   while($group=$GLOBALS['db']->fetchNextObject($groups)){
    if(api_accountGrouprole($group->id,$idAccount)>=$required->idGrouprole){return TRUE;}
   }
  }else{
   if(api_accountGrouprole($required->idGroup,$idAccount)>=$required->idGrouprole){return TRUE;}
   // try in subgroups
   $subgroups=$GLOBALS['db']->query("SELECT * FROM accounts_groups WHERE idGroup='".$required->idGroup."'");
   while($subgroup=$GLOBALS['db']->fetchNextObject($subgroups)){
    if(api_accountGrouprole($subgroup->id,$idAccount)>=$required->idGrouprole){return TRUE;}
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


/* -[ Account id by session ]------------------------------------------------ */
function api_accountId(){
 return $_SESSION['account']->id;
}


/* -[ Account object by account id ]----------------------------------------- */
// @param $account_id : ID of the account
function api_account($account_id=NULL){
 if($account_id===0){return NULL;}
 if($account_id==NULL){$account_id=$_SESSION['account']->id;}
 $account=$GLOBALS['db']->queryUniqueObject("SELECT * FROM accounts_accounts WHERE id='".$account_id."'");
 if($account->id>0){
  return $account;
 }else{
  return "[Not found]";
 }
}


/* -[ Profile mail by account id ]------------------------------------------- */
// @param $account_id : ID of the account
function api_accountMail($account_id=NULL){
 if($account_id===0){return NULL;}
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
 if($account_id===0 || $account_id==="0"){return NULL;}
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


/* -[ Account Company object by account id ]--------------------------------- */
// @param $account_id : ID of the account
function api_accountCompany($account_id=NULL){
 if($account_id===0){return NULL;}
 if($account_id==NULL){$account_id=$_SESSION['account']->id;}
 $company=$GLOBALS['db']->queryUniqueObject("SELECT accounts_companies.* FROM accounts_companies JOIN accounts_accounts ON accounts_accounts.idCompany=accounts_companies.id WHERE accounts_accounts.id='".$account_id."'");
 if($company->id>0){
  return $company;
 }else{
  return FALSE;
 }
}


/* -[ Profile language by account id ]--------------------------------------- */
// @param $account_id : ID of the account
function api_accountLanguage($account_id=NULL){
 if($account_id===0){return NULL;}
 if($account_id==NULL){$account_id=$_SESSION['account']->id;}
 $language=$GLOBALS['db']->queryUniqueValue("SELECT language FROM accounts_accounts WHERE id='".$account_id."'");
 if($language<>NULL){
  return $language;
 }else{
  return "default";
 }
}


/* -[ Company by company id ]------------------------------------------------ */
// @param $idCompany : ID of the company
function api_company($idCompany){
 $company=$GLOBALS['db']->queryUniqueObject("SELECT * FROM accounts_companies WHERE id='".$idCompany."'");
 if($company->id>0){
  return $company;
 }else{
  return FALSE;
 }
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


/* -[ Account Main Group ]--------------------------------------------------- */
// @param $account_id : ID of the account
function api_accountMainGroup($account_id=NULL){
 if($account_id===0 || $account_id==="0"){return NULL;}
 if($account_id==NULL){$account_id=$_SESSION['account']->id;}
 $group=$GLOBALS['db']->queryUniqueObject("SELECT accounts_groups.*,accounts_groups_join_accounts.idGrouprole FROM accounts_groups_join_accounts JOIN accounts_groups ON accounts_groups_join_accounts.idGroup=accounts_groups.id WHERE accounts_groups_join_accounts.idAccount='".$account_id."' AND main='1'");
 if($group->id){
  return $group;
 }else{
  return FALSE;
 }
}


/* -[ Group name by group id ]----------------------------------------------- */
// @integer $idGroup : ID of the group
// @string $description : show group description
// @boolean $popup : show description in popup
function api_groupName($idGroup,$description=FALSE,$popup=FALSE){
 if(!$idGroup>0){return FALSE;}
 $group=$GLOBALS['db']->queryUniqueObject("SELECT * FROM accounts_groups WHERE id='".$idGroup."'");
 if($group->name<>NULL){
  $return=stripslashes($group->name);
  if($description && $group->description<>NULL){
   if($popup){
    $return="<a data-toggle='popover' data-placement='top' data-content='".stripslashes($group->description)."' style='color:#333333;'>".$return."</a>\n";
   }else{
    $return.=" (".stripslashes($group->description).")";
   }
  }
  return $return;
 }elseif($group->id){
  return "[ID ".$group->id."]";
 }else{
  return "[Not found]";
 }
}


/* -[ Group id by group name ]----------------------------------------------- */
// @param $groupName : Name of the group
function api_groupId($groupName){
 $group=$GLOBALS['db']->queryUniqueObject("SELECT * FROM accounts_groups WHERE name='".$groupName."'");
 if($group->id){
  return $group->id;
 }else{
  return FALSE;
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
function api_accountCompanyName($company_id=NULL){
 if($company_id){$company=$GLOBALS['db']->queryUniqueObject("SELECT * FROM accounts_companies WHERE id='".$company_id."'");}
 if($company->id>0){
  return stripslashes($company->company)." - ".stripslashes($company->division);
 }else{
  return "[Not found]";
 }
}


/* -[ Return if an account is member of a group or subgroup ]---------------- */
// @param $idGroup   : ID of the group
// @param $idAccount : ID of the account
// @param $subgroups : Check also in subgroups
function api_accountGroupMember($idGroup,$idAccount=NULL,$subgroups=TRUE){
 if($idAccount==NULL){$idAccount=$_SESSION['account']->id;}
 if($idGroup>0 && $idAccount>0){
  $grouprole=$GLOBALS['db']->queryUniqueValue("SELECT idGrouprole FROM accounts_groups_join_accounts WHERE idGroup='".$idGroup."' AND idAccount='".$idAccount."'");
  if($grouprole>0){return TRUE;}
  if($subgroups){
   $subgroups=$GLOBALS['db']->query("SELECT * FROM accounts_groups WHERE idGroup='".$idGroup."'");
   while($subgroup=$GLOBALS['db']->fetchNextObject($subgroups)){
    $grouprole=$GLOBALS['db']->queryUniqueValue("SELECT idGrouprole FROM accounts_groups_join_accounts WHERE idGroup='".$subgroup->id."' AND idAccount='".$idAccount."'");
    if($grouprole>0){return TRUE;}
   }
  }
 }
 return FALSE;
}


/* -[ Return the group role of an account ]---------------------------------- */
// @param $idGroup   : ID of the group
// @param $idAccount : ID of the account
// @param $subgroups : Check also in subgroups
function api_accountGrouprole($idGroup,$idAccount=NULL,$subgroups=FALSE){
 if($idAccount===0 || $idAccount==="0"){return NULL;}
 if($idAccount===NULL){$idAccount=$_SESSION['account']->id;}
 if(!$idGroup>0 && !$idAccount>0){return FALSE;}
 $grouprole=$GLOBALS['db']->queryUniqueValue("SELECT idGrouprole FROM accounts_groups_join_accounts WHERE idGroup='".$idGroup."' AND idAccount='".$idAccount."'");
 if($grouprole>0){
  return $grouprole;
 }else{
  $subgroups=$GLOBALS['db']->query("SELECT * FROM accounts_groups WHERE idGroup='".$idGroup."'");
  while($subgroup=$GLOBALS['db']->fetchNextObject($subgroups)){
   $grouprole=$GLOBALS['db']->queryUniqueValue("SELECT idGrouprole FROM accounts_groups_join_accounts WHERE idGroup='".$subgroup->id."' AND idAccount='".$idAccount."'");
   if($grouprole>0){return $grouprole;}
  }
 }
 return FALSE;
}


/* -[ Return account groups and grouproles ]--------------------------------- */
// @param $idAccount : ID of the account
// @param $supgroups : Check also in superiors groups
function api_accountGroups($idAccount=NULL,$supgroups=TRUE){
 if($idAccount===0 || $idAccount==="0"){return NULL;}
 if($idAccount===NULL){$idAccount=$_SESSION['account']->id;}
 $groups_array=array();
 if($idAccount>0){
  $groups=$GLOBALS['db']->query("SELECT * FROM accounts_groups_join_accounts JOIN accounts_groups ON accounts_groups_join_accounts.idGroup=accounts_groups.id WHERE idAccount='".$idAccount."' ORDER BY accounts_groups_join_accounts.idGroup ASC");
  while($group=$GLOBALS['db']->fetchNextObject($groups)){
   $group_obj=new stdClass();
   $group_obj->id=$group->id;
   $group_obj->name=$group->name;
   $group_obj->description=$group->description;
   $group_obj->grouprole=$group->idGrouprole;
   $group_obj->idGroup=$group->idGroup;
   $supgroup_obj->inherited=FALSE;
   $groups_array[$group->id]=$group_obj;
   if($supgroups && $group->idGroup>0){
    $supgroup=$GLOBALS['db']->queryUniqueObject("SELECT * FROM accounts_groups WHERE id='".$group->idGroup."'");
    $supgroup_obj=new stdClass();
    $supgroup_obj->id=$supgroup->id;
    $supgroup_obj->name=$supgroup->name;
    $supgroup_obj->description=$supgroup->description;
    $supgroup_obj->grouprole=$group->idGrouprole;
    $supgroup_obj->idGroup=0;
    $supgroup_obj->inherited=TRUE;
    if($groups_array[$supgroup->id]==NULL){$groups_array[$supgroup->id]=$supgroup_obj;}
   }
  }
  return $groups_array;
 }
 return FALSE;
}


/* -[ Check if account is in group ]----------------------------------------- */
// @param $idGroup   : ID of the group
// @param $idAccount : ID of the account
// @param $subgroups : Check also in subgroups
function api_checkAccountGroup($idGroup,$idAccount=NULL,$subgroups=FALSE){
 if($idAccount==NULL){$idAccount=$_SESSION['account']->id;}
 if(api_accountGrouprole($idGroup,$idAccount)>0){
  return TRUE;
 }else{
  if($subgroups){
   // try in subgroups
   $subgroups=$GLOBALS['db']->query("SELECT * FROM accounts_groups WHERE idGroup='".$idGroup."'");
   while($subgroup=$GLOBALS['db']->fetchNextObject($subgroups)){
    if(api_accountGrouprole($subgroup->id)>0){return TRUE;}
   }
  }
  return FALSE;
 }
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
function api_parse_csv_file($csv_file,$csv_delimiter=',',$csv_enclosure='"'){
 // definitions
 $csv_rows=array();
 $csv_error=FALSE;
 // open csv handle
 if(($handle=fopen($csv_file,"r"))!==FALSE){
  // get max lines
  $max_line_length=defined('MAX_LINE_LENGTH')?MAX_LINE_LENGTH:100000;
  // get haders
  $header=fgetcsv($handle,$max_line_length);
  // get rows
  while(($csv_row=fgetcsv($handle,$max_line_length,$csv_delimiter,$csv_enclosure))!==FALSE){
   if(count($csv_row)==count($header)){
    // add entry to row array
    $entry=array_combine($header,$csv_row);
    $csv_rows[]=$entry;
   }else{$csv_error=TRUE;}
  }
  // close handles
  fclose($handle);
 }else{$csv_error=TRUE;}
 // show error
 if($csv_error){echo "<p>There was an error parsing the file..<p>\n";return FALSE;}
 // return csv rows
 return $csv_rows;
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

/* -[ Include Dependent Module API ]----------------------------------------- */
// @string $module : module name to be included
function api_includeModule($module){
 if(!is_dir("../".$module."/")){die("Module ".$module." not found..");}
 // include module api
 if(file_exists("../".$module."/api.inc.php")){include("../".$module."/api.inc.php");}
 // load module language file
 api_loadLocaleFile("../".$module."/");
}

/* -[ Load Module API, Languages and Required Modules ]---------------------- */
// @array $modules_required : modules name to be included
function api_loadModule($modules_required=NULL){
 // include module api
 if(file_exists("api.inc.php")){include("api.inc.php");}
 // include required modules
 if($modules_required<>NULL){
  if(!is_array($modules_required)){$modules_required=array($modules_required);}
  foreach($modules_required as $module){
   api_includeModule($module);
  }
 }
 // load module language file
 api_loadLocaleFile("./");
}


/* -[ File Upload to Database ]---------------------------------------------- */
// @string $ :
//
// return $file : file object
// if file is duplicate and $uploadDuplicate are true return file->id with idDuplicate
// return -1 : error uploading file
// return -2 : file type doesn't match
function api_file_upload($input,$table="uploads_uploads",$name=NULL,$label=NULL,$description=NULL,$tags=NULL,$txtContent=FALSE,$types=NULL,$uploadDuplicate=TRUE){
 // check if a file are uploaded
 if(intval($input['size'])>0 && $input['error']==UPLOAD_ERR_OK){
  // get file from input field
  $file=new stdClass();
  $file->name=api_clearFileName($input['name']);
  $file->type=strtolower($input['type']);
  $file->size=intval($input['size']);
  $file->hash=md5_file($input['tmp_name']);
  $file->file=mysql_real_escape_string(file_get_contents($input['tmp_name']));
  // check metadata
  if($name<>NULL){$file->name=api_clearFileName($name);}
  if($label<>NULL){$file->label=$label;}else{$file->label=NULL;}
  if($description<>NULL){$file->description=$description;}else{$file->description=NULL;}
  if($tags<>NULL){$file->tags=$tags;}else{$file->tags=NULL;}
  // check file type
  if($types<>NULL){
   if(!is_array($types)){$types=array(strtolower($types));}else{$types=array_map('strtolower',$types);}
   if(!in_array($file->type,$types)){return -2;}
  }
  // import textual file content
  $file->txtContent=NULL;
  if($txtContent){
   switch($file->type){
    case "text/plain":
     $file->txtContent=$file->file;
     break;

   }
  }
  // check for duplicated files
  $file->duplicated=FALSE;
  $idDuplicate=$GLOBALS['db']->queryUniqueValue("SELECT id FROM ".$table." WHERE size='".$file->size."' AND hash='".$file->hash."'");
  if($idDuplicate>0){
   $file->duplicate=TRUE;
   $file->idDuplicate=$idDuplicate;
   // abort upload if false
   if(!$uploadDuplicate){
    $file->id=$idDuplicate;
    return $file;
   }
  }
  // upload file in database
  $query="INSERT INTO ".$table."
   (name,type,size,hash,file,label,description,tags,txtcontent,addDate,addIdAccount) VALUES
   ('".$file->name."','".$file->type."','".$file->size."','".$file->hash."','".$file->file."',
    '".$file->label."','".$file->description."','".$file->tags."','".$file->txtContent."',
    '".date("Y-m-d H:i:s")."','".$_SESSION['account']->id."')";
  $GLOBALS['db']->execute($query);
  // get file id
  $file->id=$GLOBALS['db']->lastInsertedId();
  // return metadata
  $return=$file;
 }else{
  // error uploading
  $return=-1;
 }
 return $return;
}


/* -[ File object from Database ]-------------------------------------------- */
// @integet $idFile : file id
// @string $table : database table name
// @string $name : file name if you want to rename
function api_file($idFile,$table="uploads_uploads"){
 // get file object
 $file=$GLOBALS['db']->queryUniqueObject("SELECT * FROM ".$table." WHERE id='".$idFile."'");
 if($file->id>0){
  return $file;
 }else{
  return FALSE;
 }
}


/* -[ File Download from Database ]------------------------------------------ */
// @integet $idFile : file id
// @string $table : database table name
// @string $name : file name if you want to rename
function api_file_download($idFile,$table="uploads_uploads",$name=NULL,$force=TRUE){
 // get file object
 $file=$GLOBALS['db']->queryUniqueObject("SELECT * FROM ".$table." WHERE id='".$idFile."'");
 if($file->id>0){
  if(strlen($name)>0){$file->name=$name;}
  header("Pragma: no-cache");
  header("Cache-Control: no-cache, must-revalidate");
  header('Content-Transfer-Encoding: binary');
  header("Content-length: ".strlen($file->file));
  header("Content-type: ".$file->type);
  if($force){$dispositions="attachment; ";}
  header("Content-Disposition: ".$dispositions."filename=".$file->name);
  ob_end_clean();
  echo $file->file;
 }else{
  echo "Error, file not found";
 }
}


/* -[ File Delete from Database ]-------------------------------------------- */
// @integet $idFile : file id
// @string $table : database table name
function api_file_delete($idFile,$table="uploads_uploads"){
 // get file object
 $file=$GLOBALS['db']->queryUniqueObject("SELECT * FROM ".$table." WHERE id='".$idFile."'");
 if($file->id>0){
  $GLOBALS['db']->execute("DELETE FROM ".$table." WHERE id='".$idFile."'");
  return TRUE;
 }else{
  return "[File not found]";
 }
}


/* -[ Link ]----------------------------------------------------------------- */
// @string $url : url to link
// @string $label : label for link
// @string $title : title for link
// @string $class : url css class
// @booelan $popup : show popup label
// @string $confirm : show confirm alert
// @string $style : manual styles tag
// @string $target : target window
function api_link($url,$label,$title=NULL,$class=NULL,$popup=FALSE,$confirm=NULL,$style=NULL,$target="_self"){
 if($url==NULL){return FALSE;}
 $return="<a href=\"".$url."\" class='".$class."' style=\"".$style."\"";
 if($popup){
  $return.=" data-toggle='popover' data-placement='top' data-content=\"".$title."\"";
 }elseif($title<>NULL){
  $return.=" title=\"".$title."\"";
 }
 if(strlen($confirm)>0){
  $return.=" onClick=\"return confirm('".addslashes($confirm)."')\"";
 }
 $return.=" target='".$target."'>".$label."</a>\n";
 return $return;
}


// @string $string : string in format {text_key|parameter1|parameter2|...|$parameterN}
function api_textParse($string){
 if(substr($string,0,1)<>"{"){return $string;}
 // definitions
 $text=new stdClass();
 // split string into key and parameters
 $explode=explode("|",substr($string,1,-1));
 // set text key
 $text->key=$explode[0];
 // remove text key from array
 unset($explode[0]);
 // set parameters array
 $text->parameters=$explode;
 // set text parsed
 $text->parsed=TRUE;
 // return text object
 return $text;
}


/* -[ LOG ]------------------------------------------------------------------ */
// Log an event
// &define typologies
define("API_LOG_NOTICE",1);
define("API_LOG_WARNING",2);
define("API_LOG_ERROR",3);
// @integer $typology : notification typology (use defined constant)
// @string $module : module name
// @string $action : module action
// @string $event : event to log
// @integer $key : item id or key
// @string $link : link to the event item
// @return : object with notification #subject and #message
function api_log($typology,$module,$action,$event,$key=NULL,$link=NULL){
 if($typology<1 || $typology>3 || $module==NULL || $action==NULL){return FALSE;}
 // definitions
 $log=new stdClass();
 // clean variables
 $event=addslashes($event);
 // log interpreter id if account is interpreted
 $idAccount=$_SESSION['account']->id;
 if($_SESSION['account']->interpreter){$idAccount=$_SESSION['account']->interpreter;}
 // build log query
 $query="INSERT INTO logs_logs
  (typology,timestamp,module,action,`key`,event,link,idAccount,ip) VALUES
  ('".$typology."','".date("Y-m-d H:i:s")."','".$module."','".$action."','".$key."',
   '".$event."','".$link."','".$idAccount."','".$_SERVER['REMOTE_ADDR']."')";
 // execute query
 $GLOBALS['db']->execute($query);
 // acquire log id
 $q_idLog=$GLOBALS['db']->lastInsertedId();
 // execute notification triggers
 $notifications=api_logNotificationTriggers($module,$action,$event,$key,$link);
 // build return object
 $log->id=$q_idLog;
 $log->notifications=$notifications;
 return $log;
}


/* -[ Log Notification Triggers ]-------------------------------------------- */
// @string $module : module name
// @string $action : module action
// @string $log : event to notificate
// @integer $id : item id
// @string $link : link to the event item
function api_logNotificationTriggers($module,$action,$event,$id,$link){
 if($module==NULL || $action==NULL){return FALSE;}
 // definitions
 $notifications_array=array();
 // retrieve trigger by module actions
 $triggers=$GLOBALS['db']->query("SELECT * FROM logs_triggers WHERE module='".$module."' AND action='".$action."'");
 while($trigger=$GLOBALS['db']->fetchNextObject($triggers)){
  // retrieve subscriptions by trigger
  $subscriptions=$GLOBALS['db']->query("SELECT * FROM logs_subscriptions WHERE `trigger`='".$trigger->trigger."'");
  while($subscription=$GLOBALS['db']->fetchNextObject($subscriptions)){
   // definitions
   $notification=new stdClass();
   $notification->idAccount=$subscription->idAccount;
   $notification->trigger=$subscription->trigger;
   $notification->link=$link;
   $notification->sent=FALSE;
   $notification->mail=FALSE;
   // check condition
   if($trigger->condition){
    $send=FALSE;
    // include logs conditions
    require_once("../".$module."/logs.inc.php");
    // call condition function
    if(call_user_func_array($trigger->condition,array($subscription->idAccount,$id))){$send=TRUE;}
   }else{
    $send=TRUE;
   }
   // send notification
   if($send){
    // samples
    // event: {logs_workflows_ticketCreated|00024-00028|parametro2}
    // trigger->name: logs-ticketDisponible
    // load recipient language file
    api_loadLocaleFile("../".$module."/",api_accountLanguage($subscription->idAccount));
    $notification->subject=api_text($trigger->trigger."-subject",api_textParse($event)->parameters);
    $notification->message=api_text($trigger->trigger."-message",api_textParse($event)->parameters);
    // if subscription mail is 2 archive the notification by default
    if($subscription->archived){$status=3;}else{$status=1;}
    // send and acquire notification hash
    $notification->hash=api_notification($subscription->idAccount,$module,$action,$notification->subject,$notification->message,$notification->link,NULL,$status);
    // send mail
    if($subscription->mail){
     $notification->mail=TRUE;
     if(substr($link,0,4)<>"http"){$mail_link="http://".$_SERVER['SERVER_NAME'].$GLOBALS['dir'].$link;}
     else{$mail_link=$link;}
     $mail_message=$notification->message."<br>\n"."Link: <a href='".$mail_link."'>".$mail_link."</a>";
     $notification->mail_sent=api_sendmail(api_accountMail($subscription->idAccount),$mail_message,$notification->subject,TRUE);
    }
    // build notifications array
    $notifications_array[]=$notification;
    // reload user language file
    api_loadLocaleFile("../".$module."/",api_accountLanguage());
   }
  }
 }
 // return notifications array
 return $notifications_array;
}


/* -[ Send notification ]---------------------------------------------------- */
// @integer $idAccount : account id of notification recipient
// @string $module : module name
// @string $action : module action
// @string $subject : log subject
// @string $message : log message
// @string $link : log link
// @string $hash : md5 log hash
// @integer $status : 1 received, 2 readed, 3 archived
function api_notification($idAccount,$module,$action,$subject,$message,$link=NULL,$hash=NULL,$status=1){
 if($idAccount<2 || $module==NULL || $subject==NULL || $message==NULL){return FALSE;}
 if($hash===NULL){$hash=md5(date('YdmHsi').api_randomString());}
 $query="INSERT INTO logs_notifications
  (hash,idAccount,timestamp,module,action,subject,message,link,status) VALUES
  ('".$hash."','".$idAccount."','".date("Y-m-d H:i:s")."','".$module."','".$action."',
   '".addslashes($subject)."','".addslashes($message)."','".$link."','".$status."')";
 $GLOBALS['db']->execute($query);
 if($GLOBALS['db']->lastInsertedId()>0){return $hash;}
 else{return FALSE;}
}

/* -[ Log History ]---------------------------------------------------- */
// @string $module : module name
// @integer $key : id of the object
function api_logHistory($module,$key,$only=NULL,$exclude=NULL){
 if($module==NULL || $key==NULL){return FALSE;}
 if($only!==NULL){if(!is_array($only)){$only=array($only);}}
 if($exclude!==NULL){if(!is_array($exclude)){$exclude=array($exclude);}}
 // definitions
 $history_array=array();
 // retrieve trigger by module actions
 $logs=$GLOBALS['db']->query("SELECT * FROM logs_logs WHERE module='".$module."' AND `key`='".$key."' ORDER BY timestamp DESC");
 while($event=$GLOBALS['db']->fetchNextObject($logs)){
  if($only!==NULL){
   if(in_array($event->action,$only)){$history_array[]=$event;}
  }elseif($exclude!==NULL){
   if(!in_array($event->action,$only)){$history_array[]=$event;}
  }else{
   $history_array[]=$event;
  }
 }
 return $history_array;
}

/* -[ Log History Parse ]---------------------------------------------------- */
// @string $module : module name
// @integer $key : id of the object
function api_logHistoryParse($timestamp,$account,$status_from=NULL,$status_to=NULL,$note=NULL){
 if(!$timestamp||!$account){return FALSE;}
 $return="<div id='history'>\n";
 $return.=" <div id='history_status'>\n";
 $return.="  <small>".api_timestampFormat($timestamp,api_text("datetime"))." - ".api_accountName($account)."</small><br>\n";
 if($status_from){$return.="  <strong><small>".$status_from."</small>";}
 if($status_to){$return.=" &rarr; <small>".$status_to."</small></strong>\n";}
 $return.=" </div>\n";
 if($note){$return.=" <div id='history_note'><small>".$note."</small></div>\n";}
 $return.="</div>\n";
 return $return;
}


/**
 * Return a Glyphicons icon html tag
 *
 * @param string $icon bootstrap icon glyphs
 * @param string $title title of icon
 * @param string $style manual styles tag
 */
function api_icon($icon,$title=NULL,$style=NULL){
 if($icon==NULL){return FALSE;}
 $return="<i class='".$icon."' title='".$title."' style='".$style."'></i>";
 return $return;
}

?>