<?php
/**
 * Coordinator API
 *
 * Long Description
 *
 * @package      Coordinator\API
 * @author       Manuel Zavatta <manuel.zavatta@gmail.com>
 */

/* -[ Global parameter ]----------------------------------------------------- */
session_start();
global $debug;                     // debug variable
global $html;                      // html structure resource
global $db;                        // database resource
global $path;                      // base path of web root directory
global $dir;                       // directory of web application
global $alert;                     // alerts global variable
global $locale;                    // array with translation
global $initial_module;            // initial module to load
global $custom_fields;             // custom fields
global $script_timer;              // script timer
$script_timer=api_getmicrotime();
include_once("../config.inc.php"); // include the configuration file
include_once("html.class.php");    // include the html class
include_once("db.class.php");      // include the database class
// include structures
include_once("structures/accordion.class.php");
include_once("structures/dashboard.class.php");
include_once("structures/dl.class.php");
include_once("structures/flagwell.class.php");
include_once("structures/form.class.php");
include_once("structures/modal.class.php");
include_once("structures/navigation.class.php");
include_once("structures/pagination.class.php");
include_once("structures/sidebar.class.php");
include_once("structures/splitted.class.php");
include_once("structures/tabbable.class.php");
include_once("structures/table.class.php");
// include core api+
include_once("../accounts/api.inc.php");
// check for initial module
if(!strlen($initial_module)){$initial_module="index";}
// load core language file
api_loadLocaleFile("../");
// build class
$html=new HTML();
$db=new DB($db_host,$db_user,$db_pass,$db_name);


/* -[ Check Debug ]---------------------------------------------------------- */
if($_SESSION['account']->debug){
 error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_WARNING & ~E_DEPRECATED);
 ini_set('display_errors',1);
}else{
 error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_WARNING & ~E_DEPRECATED);
 ini_set('display_errors',0);
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
     && api_baseName()<>"accounts_ldap.php"
     && api_baseName()<>"accounts_ldap_update.php"
     && api_baseName()<>"password_retrieve.php"
     && api_baseName()<>"password_reset.php"
     && api_baseName()<>"submit.php"
     && api_baseName()<>"download.php"
     && $dontCheckSession==FALSE){
   // save url to session if not in this skip array
   $url_skip=array($GLOBALS['dir']."accounts/submit.php?act=account_login",
    $GLOBALS['dir']."accounts/index.php",
    $GLOBALS['dir']."chats/chat.inc.php",
    $GLOBALS['dir']."chats/chat_list.inc.php",
    $GLOBALS['dir']."chats/chat_edit.inc.php",
    $GLOBALS['dir']."chats/chat_counter.inc.php",
    $GLOBALS['dir']."logs/logs_notifications_list.inc.php",
    $GLOBALS['dir']."logs/logs_notifications_counter.inc.php");
   if(!in_array($_SERVER['REQUEST_URI'],$url_skip)){
    $_SESSION['external_redirect']=($_SERVER["HTTPS"]?"https://":"http://").$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
   }
   // redirect to login page
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


/* -[ Check Maintenance ]---------------------------------------------------- */
if(api_getOption("maintenance") && !api_account()->superuser &&
  ( api_baseName()<>"login.php" && api_baseName()<>"submit.php") ){
 $alert="&alert=maintenance&alert_class=alert-warning";
 exit(header("location: ../accounts/login.php?lang=".api_account()->language."&account=".api_account()->login.$alert));
}


/* -[ Check browser ]-------------------------------------------------------- */
if((strpos($_SERVER['HTTP_USER_AGENT'],'Chrome')==false)
   &&(strpos($_SERVER['HTTP_USER_AGENT'],'Safari')==false)
   &&(strpos($_SERVER['HTTP_USER_AGENT'],'Firefox')==false)
   &&(strpos($_SERVER['HTTP_USER_AGENT'],'MSIE 10')==false)
   &&(strpos($_SERVER['HTTP_USER_AGENT'],'11')==false)){
 $GLOBALS['alert']->alert="changeBrowser";
 $GLOBALS['alert']->class="alert-error";
}


/**
 * Deprecated function alert
 *
 * @param string $function fuction name
 * @param string $new new fuction name
 */
function api_deprecatedAlert($function,$new=NULL){
 if(strlen($new)){$new="<BR>Please use ".$new;}
 echo api_alert_box("The function ".$function."() is deprecated".$new,"DEPRECATED","alert-error");
 return FALSE;
}

/**
 * Redirect function
 *
 * @param string $location location url
 */
function api_redirect($location){
 if($_SESSION["account"]->debug){die(api_link($location,$location));}
 exit(header("location: ".$location));
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


/**
* Text Translation
* @param string $key text key
* @param string|array $parameters parameters to include into text
* @return string text translated
*/
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

/* -[ Boolean Text ]--------------------------------------------------------- */
// @param $boolean : boolean value
function api_text_boolean($boolean){
 if($boolean){return api_text("yes");}else{return api_text("no");}
}


/* -[ Die ]------------------------------------------------------------------ */
function api_die($error=""){
 $url="<a href='index.php?alert=".$error."&alert_class=alert-error'>";
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
  $idLog=$_GET['idLog'];
 }elseif(isset($GLOBALS['alert'])){
  $alert=$GLOBALS['alert']->alert;
  $class=$GLOBALS['alert']->class;
  $parameters=$GLOBALS['alert']->parameters;
 }
 // show the alert
 if(isset($alert)){
  $alert=api_text($alert,$parameters);
  if($idLog){$alert="<a href='../logs/logs_list.php?filtered=1&typology%5B%5D=1&typology%5B%5D=2&typology%5B%5D=3&idLog=".$idLog."' target='_blank'>".api_icon("icon-book")."</a> &nbsp;".$alert;}
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


/**
* Base name path
* @return string file name of script
*/
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
function api_hostName($ipaddr=NULL){
 if($ipaddr===NULL){
  if($_SERVER["HTTP_X_FORWARDED_FOR"]<>""){
   $proxy=$_SERVER["REMOTE_ADDR"];
   $host=@gethostbyaddr($_SERVER["HTTP_X_FORWARDED_FOR"]);
  }else{
   $host=@gethostbyaddr($_SERVER["REMOTE_ADDR"]);
  }
 }else{
  $host=@gethostbyaddr($ipaddr);
 }
 return strtoupper($host);
}


/* -[ Update Temp Token ]---------------------------------------------------- */
function api_updateTempToken(){
 $temp_token=md5(api_now());
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
  ('".$idAccount."','".api_now()."','".$module."','".$action."',
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


/**
* Timestamp Format
* @param string $timestamp MySql datetime
* @param string $format datetime format ( php date format or language key )
* @param string $language language conversion
* @return string formatted date time
*/
function api_timestampFormat($timestamp,$format="Y-m-d H:i:s",$language=NULL){
 if($timestamp==NULL){return NULL;}
 if($timestamp=="0000-00-00 00:00:00"){return NULL;}
 if(!strlen($language)){$language=$_SESSION['language'];}
 $datetime=new DateTime($timestamp);
 $return=$datetime->format($format);
 // if language not default
 if($language<>"default"){
  if($language<>$_SESSION['language']){api_loadLocaleFile("../",$language);}
  $days=array("Monday","Tuesday","Wednesday","Thursday","Friday","Saturday","Sunday");
  $locale_days=array(api_text("day-monday"),api_text("day-tuesday"),api_text("day-wednesday"),api_text("day-thursday"),api_text("day-friday"),api_text("day-saturday"),api_text("day-sunday"));
  $months=array("January","February","March","April","May","June","July","August","September","October","November","December");
  $locale_months=array(api_text("month-january"),api_text("month-february"),api_text("month-march"),api_text("month-april"),api_text("month-may"),api_text("month-june"),api_text("month-july"),api_text("month-august"),api_text("month-september"),api_text("month-october"),api_text("month-november"),api_text("month-december"));
  if($language<>$_SESSION['language']){api_loadLocaleFile();}
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

/* -[ Timestamp Format Seconds to Time ]------------------------------------- */
function api_timestampFormatSecondsToTime($seconds,$showSeconds=true){
 if(!$seconds){return false;}
 $time=null;
 $h=floor($seconds/3600);
 $m=floor($seconds/60%60);
 $s=floor($seconds%60);
 if($h){$time.=sprintf("%02d:",$h);}
 if($h || $m){$time.=sprintf("%02d:",$m);}
 if($showSeconds || $seconds<60){$time.=sprintf("%02d:",$s);}
 $return=substr($time,0,-1);
 return $return;
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
 return number_format($result,2,".","");
}

/**
* Timestamp Difference From
* @param string $from start date
* @param string $difference difference in textual form ("+1 day","-1 month,..)
* @param string $format timestamp format
* @return string formatted timestamp difference
*/
function api_timestampDifferenceFrom($from,$difference,$format="Y-m-d H:i:s"){
 $date = new DateTime($from);
 $date->modify($difference);
 return $date->format($format);
}

/* -[ Timestamp Difference Format ]------------------------------------------ */
// @integer $seconds : differences in seconds
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

/**
 *
 * @param type $date_from
 * @param type $date_to
 * @param array $working_days array of working days number 1-7
 * @param array $working_hours array of starting and ending working hours 0-23
 * @return int
 */
function api_timestampDifferenceWorkingSeconds($date_from,$date_to,$working_days=array(1,2,3,4,5),$working_hours=array(8.5,17.5)) {
 // convert date in unix time
 $date1=strtotime($date_from);
 $date2=strtotime($date_to);
 // check for equals
 if($date1==$date2){return 0;}
 // check for first date
 if($date1<$date2){
  $sign=1;
 }else{
  // invert dates
  $tmp_date=$date1;
  $date1=$date2;
  $date2=$tmp_date;
  $sign=-1;
 }
 // check working days and hours arrays
 if(!count($working_days)){$working_days = array(1,2,3,4,5);} // from monday to friday
 if(count($working_hours)<>2){$working_hours = array(8.5, 17.5);} // from 08:30 to 17:30
 // definitions
 $days=0;
 $seconds=0;
 $current_date = $date1;
 $beg_h=floor($working_hours[0]);
 $beg_m=($working_hours[0]*60)%60;
 $end_h=floor($working_hours[1]);
 $end_m=($working_hours[1]*60)%60;
 /** @todo sistemare function */
 // setup the very next first working timestamp
 if (!in_array(date('w',$current_date) , $working_days)) {
  // the current day is not a working day
  // the current timestamp is set at the begining of the working day
  $current_date = mktime( $beg_h, $beg_m, 0, date('n',$current_date), date('j',$current_date), date('Y',$current_date) );
  // search for the next working day
  while ( !in_array(date('w',$current_date) , $working_days) ) {
   $current_date += 24*3600; // next day
  }
  //pre_var_dump("000 next first working timestamp ".date("Y-m-d H:i:s",$current_date)." - ".$current_date);
 } else {
  // check if the current timestamp is inside working hours
  $date0 = mktime( $beg_h, $beg_m, 0, date('n',$current_date), date('j',$current_date), date('Y',$current_date) );
  //pre_var_dump("001 date0 ".date("Y-m-d H:i:s",$date0)." - ".$date0);
  // it's before working hours, let's update it
  if ($current_date<$date0){
   $current_date = $date0;
   //pre_var_dump("002 it's before ".date("Y-m-d H:i:s",$current_date)." - ".$current_date);
  }
  $date3 = mktime( $end_h, $end_m, 59, date('n',$current_date), date('j',$current_date), date('Y',$current_date) );
  //pre_var_dump("003 date3 ".date("Y-m-d H:i:s",$date3)." - ".$date3);
  if ($current_date>$date3) {
   // outch ! it's after working hours, let's find the next working day
   $current_date += 24*3600; // the day after
   // and set timestamp as the begining of the working day
   $current_date = mktime( $beg_h, $beg_m, 0, date('n',$current_date), date('j',$current_date), date('Y',$current_date) );
   while ( !in_array(date('w',$current_date) , $working_days) ) {
    $current_date += 24*3600; // next day
   }
   //pre_var_dump("004 it's after ".date("Y-m-d H:i:s",$current_date)." - ".$current_date);
  }
 }
 // so, $current_date is now the first working timestamp available...
 //pre_var_dump("!005 first time stamp available ".date("Y-m-d H:i:s",$current_date)." - ".$current_date);
 // if first time stamp available is major of date2 return 0
 if($current_date>$date2){
  //pre_var_dump("005-major first time stamp available ".date("Y-m-d H:i:s",$current_date)." major of end date (date2) - return seconds: 0");
  return 0;   /** @todo perche??? */
 }
 // if first time stamp available is the same day of date2
 if(date("Y-m-d",$current_date)==date("Y-m-d",$date2)){
  // calculate only seconds (no days)
  // if date2 is major of end of working day set date2 to end of working day
  $date3 = mktime( $end_h, $end_m, 59, date('n',$date2), date('j',$date2), date('Y',$date2) );
  if ($date2>$date3) {
   $date2=$date3;
   //pre_var_dump("005-major-end end date (date2) major end of working day set date2 to: ".date("Y-m-d H:i:s",$date2));
  }else{
   //pre_var_dump("005-minor-end date2 to: ".date("Y-m-d H:i:s",$date2));
  }
  $seconds=$date2-$current_date+1;
  //pre_var_dump("005-same first time stamp available ".date("Y-m-d H:i:s",$current_date)." same day of end date (date2) ".date("Y-m-d H:i:s",$date2)." - return seconds: ".$seconds);
  return $seconds;
 }
 // calculate the number of seconds from current timestamp to the end of the working day
 $date0 = mktime( $end_h, $end_m, 59, date('n',$current_date), date('j',$current_date), date('Y',$current_date) );
 //pre_var_dump("005-2 fine primo giorno lavorativo (date0) ".date("Y-m-d H:i:s",$date0));
 // check if date2 major end of first working day
 if($date2>$date0){
  // add seconds from current date to end hour of current date
  $seconds = $date0-$current_date+1;
  //pre_var_dump("006-min data fine (date2) ".date("Y-m-d H:i:s",$date2)." maggiore di fine primo giorno lavorativo (date0) ".date("Y-m-d H:i:s",$date0)." secondi attuali: ".$seconds);
 }else{
  //pre_var_dump("006-mag data fine (date2) ".date("Y-m-d H:i:s",$date2)." minore di fine primo giorno lavorativo (date0) ".date("Y-m-d H:i:s",$date0)." secondi attuali: ".$seconds);
 }
 // calculate the number of days from the current day to the end day
 $date3 = mktime( $beg_h, $beg_m, 0, date('n',$date2), date('j',$date2), date('Y',$date2) );
 while ( $current_date < $date3 ) {
  $current_date += 24*3600; // next day
  if (in_array(date('w',$current_date) , $working_days) ) {$days++;} // it's a working day
 }
 if ($days>0) {$days--;} //because we've allready count the first day (in $seconds)
 //pre_var_dump("007 da first timestamp available (vedi sopra) all'ultimo giorno richiesto ".date("Y-m-d H:i:s",$date3)." - giorni: ".$days." in secondi: ".($days*32400));
 //pre_var_dump("008 current_date attuale ".date("Y-m-d H:i:s",$current_date));
 // check if end's timestamp is inside working hours
 $date0 = mktime( $beg_h, $beg_m, 0, date('n',$date2), date('j',$date2), date('Y',$date2) );
 //pre_var_dump("009 controlla se data finale (date2) ".date("Y-m-d H:i:s",$date2)." è minore di inizio ultimo giorno (date0) ".date("Y-m-d H:i:s",$date0));
 if ($date2<$date0) {
  // it's before, so nothing more !
 //pre_var_dump("010-1 data fine (date2) ".date("Y-m-d H:i:s",$date2)." minore di inizio ultimo giorno (date0) ".date("Y-m-d H:i:s",$date0)." secondi da aggiungere 0 - giorni tot: ".$days." - secondi tot: ".$seconds);
 } else {
  // is it after ?
  $date3 = mktime( $end_h, $end_m, 59, date('n',$date2), date('j',$date2), date('Y',$date2) );
  //pre_var_dump("010-2 controlla se data fine (date2) ".date("Y-m-d H:i:s",$date2)." è maggiore di data finale ultimo giorno (date3) ".date("Y-m-d H:i:s",$date3));
  if ($date2>$date3) {
   //pre_var_dump("010-3 modifica (date2) uguale a (date3)".date("Y-m-d H:i:s",$date3));
   $date2=$date3;
  }
  // calculate the number of seconds from current timestamp to the final timestamp
  $tmp = $date2-$date0+1;
  //pre_var_dump("011 tmp ".$tmp);
  $seconds += $tmp;
  //pre_var_dump("012 data fine (date2) ".date("Y-m-d H:i:s",$date2)." maggiore di inizio ultimo giorno (date0) ".date("Y-m-d H:i:s",$date0)." secondi da aggiungere ".$tmp." - secondi tot: ".$seconds);
 }
 // add working days calculated in seconds
 $seconds += 3600*($working_hours[1]-$working_hours[0])*$days;
 $seconds=$sign * $seconds; // to get hours
 //pre_var_dump("013 secondi totali ".$seconds." - ore totali: ".($seconds/3600)." - giorni totali: ".($seconds/3600/9));
 return $seconds;
}

/**
 * Date Years
 *
 * @param string $years_from start year
 * @param string $years_to end year, if null current year
 * @return array $years_array array of years
 */
function api_date_years($years_from,$years_to=NULL){
 $years_array=array();
 if(!$years_to){$years_to=date("Y");}
 for($year=$years_to;$year>=$years_from;$year--){$years_array[$year]=$year;}
 return $years_array;
}

/**
 * Date Months
 *
 * @param string $month_from start month, if null january
 * @param string $month_to end month, if null december
 * @return array $months_array array of months
 */
function api_date_months($month_from=NULL,$month_to=NULL){
 $months_array=array();
 if(!$month_from){$month_from=1;}
 if(!$month_to){$month_to=12;}
 $locale_months=array(1=>api_text("month-january"),2=>api_text("month-february"),3=>api_text("month-march"),4=>api_text("month-april"),5=>api_text("month-may"),6=>api_text("month-june"),7=>api_text("month-july"),8=>api_text("month-august"),9=>api_text("month-september"),10=>api_text("month-october"),11=>api_text("month-november"),12=>api_text("month-december"));
 for($month=$month_from;$month<=$month_to;$month++){$months_array[$month]=$locale_months[$month];}
 return $months_array;
}

/**
 * Date Days
 *
 * @param string $day_from start day, if null monday (0 to 6)
 * @param string $day_to end day, if null sunday (1 to 7)
 * @return array Array of days
 */
function api_date_days($day_from=1,$day_to=7){
 // check parameters
 if($day_from<0||$day_from>6){$day_from=1;}
 if($day_to<1||$day_to>7){$day_to=7;}
 // make days array
 $days_array=array(
  0=>api_text("day-sunday"),
  1=>api_text("day-monday"),
  2=>api_text("day-tuesday"),
  3=>api_text("day-wednesday"),
  4=>api_text("day-thursday"),
  5=>api_text("day-friday"),
  6=>api_text("day-saturday"),
  7=>api_text("day-sunday")
 );
 // make return array
 $return_array=array();
 for($i=$day_from;$i<=$day_to;$i++){$return_array[$i]=$days_array[$i];}
 // return
 return $return_array;
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


/* -[ Group name by group id ]------------------------ verificare ----------- */
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


/* -[ Group id by group name ]------------------- verificare ---------------- */
// @param $groupName : Name of the group
// @param $idCompany : Company id or company in use
function api_groupId($groupName,$idCompany=NULL){
 if($idCompany===NULL){$idCompany=api_company()->id;}
 $group=$GLOBALS['db']->queryUniqueObject("SELECT * FROM accounts_groups WHERE idCompany='".$idCompany."' AND name='".$groupName."'");
 if($group->id){
  return $group->id;
 }else{
  return FALSE;
 }
}


/* -[ Return the group role of an account ]-------------- verificare -------- */
// @param $idGroup   : ID of the group   // è stata aggiornata giusto per funzionare ma è da sistemare
// @param $idAccount : ID of the account
// @param $subgroups : Check also in subgroups
function api_accountGrouprole($idGroup,$idAccount=NULL,$subGroups=FALSE){
 $group=api_accounts_group($idGroup,$subGroups);
 $account=api_account($idAccount);
 if(!$group->id||!$account->id){return FALSE;}
 // check if group is in array account company groups
 if(array_key_exists($group->id,$account->companies[$group->idCompany]->groups)){
  return $account->companies[$group->idCompany]->role->level;
 }
 if($subGroups){
  // retrieve subgroups
  $subgroups_array=array();
  $subgroups=api_accounts_groups($group->idCompany,$group->id);
  api_walkGroupsRecursively($subgroups->results,$subgroups_array);
  // check subgroups
  foreach($subgroups_array as $subgroup){
   // check if subgroup is in array account company groups
   if(array_key_exists($subgroup,$account->companies[$group->idCompany]->groups)){
    return $account->companies[$group->idCompany]->role->level;
   }
  }
 }
 return false;
}


/* -[ Check if account is in group ]------------------- vertificare --------- */
// @param $idGroup   : ID of the group
// @param $idAccount : ID of the account
// @param $subgroups : Check also in subgroups
function api_checkAccountGroup($idGroup,$idAccount=NULL,$subgroups=FALSE){
 if(api_accountGrouprole($idGroup,$idAccount,$subgroups)>0){return TRUE;}
 return FALSE;
}


/* -[ Generate the pagination div ]-------------------- verificare ---------- */
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
  if(file_exists("../../uploads/uploads/images/copyright.png")){
   $watermark = imagecreatefrompng("../../uploads/uploads/images/copyright.png");
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
function api_parse_csv_file($csv_file,$csv_delimiter=',',$csv_enclosure='"',$header=TRUE){
 // definitions
 $csv_rows=array();
 $csv_error=FALSE;
 // open csv handle
 if(($handle=fopen($csv_file,"r"))!==FALSE){
  // get max lines
  $max_line_length=defined('MAX_LINE_LENGTH')?MAX_LINE_LENGTH:100000;
  // get haders
  if($header){$headers=fgetcsv($handle,$max_line_length,$csv_delimiter,$csv_enclosure);}
  // get rows
  while(($csv_row=fgetcsv($handle,$max_line_length,$csv_delimiter,$csv_enclosure))!==FALSE){
   if($header==TRUE && count($csv_row)<>count($headers)){$csv_error=TRUE;continue;}
   // add entry to row array
   if($header){$csv_row=array_combine($headers,$csv_row);}
   $csv_rows[]=$csv_row;
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
 $file_name=str_replace("---","-",$file_name);
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


/**
* Query Order
* @param string $default default order fields and methods
* @return string order by query
*/
function api_queryOrder($default=NULL){
 // acquire variables
 $query_order_field=$_GET['of'];
 $query_order_mode=$_GET['om'];
 if(!$query_order_field){
  return " ORDER BY ".$default;
 }else{
  if($query_order_mode==1){$query_order_mode=" ASC";}else{$query_order_mode=" DESC";}
  $query_order_field=str_replace(","," ".$query_order_mode.",",$query_order_field);
  return " ORDER BY ".$query_order_field.$query_order_mode;
 }
}

/* -[ Include Dependent Module API ]----------------------------------------- */
// @string $module : module name to be included
function api_includeModule($module){
 if(!is_dir("../".$module."/")){die("Module ".$module." not found..");}
 // include module api
 if(file_exists("../".$module."/api.inc.php")){include_once("../".$module."/api.inc.php");}
 // load module language file
 api_loadLocaleFile("../".$module."/");
}

/**
* Load Module API, Languages and Required Modules
* @param array $modules_required modules name to be included
*/
function api_loadModule($modules_required=NULL){
 // include module api
 if(file_exists("api.inc.php")){include_once("api.inc.php");}
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
function api_file_upload($input,$table="uploads_uploads",$name=NULL,$label=NULL,$description=NULL,$tags=NULL,$txtContent=FALSE,$types=NULL,$uploadDuplicate=TRUE,$path=NULL){
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
  if($name<>NULL){$file->name=substr(api_clearFileName($name),-200);}
  if($label<>NULL){$file->label=api_cleanString($label,"/[^A-Za-z0-9-_ ]/");}else{$file->label=pathinfo($file->name,PATHINFO_FILENAME);}
  if($description<>NULL){$file->description=api_cleanString($description,"/[^A-Za-zÀ-ÿ0-9-.,' ]/");}else{$file->description=NULL;}
  if($tags<>NULL){$file->tags=api_cleanString(strtolower($tags),"/[^a-z0-9-,]/");}else{$file->tags=NULL;}
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
  // check for upload path or upload to database
  if(strlen($path)>1){
   $file->file=NULL;
   if(substr($path,-1)<>"/"){$path=$path."/";}
   if(!is_dir("../uploads/uploads/".$path)){mkdir("../uploads/uploads/".$path,0777,TRUE);}
   if(file_exists("../uploads/uploads/".$path."upload.tmp")){unlink("../uploads/uploads/".$path."upload.tmp");}
   if(is_uploaded_file($input['tmp_name'])){if(!move_uploaded_file($input['tmp_name'],"../uploads/uploads/".$path."upload.tmp")){$return=-1;}}else{$return=-1;}
  }
  // build query
  $query="INSERT INTO ".$table."
   (name,type,size,hash,file,label,description,tags,txtContent,addDate,addIdAccount,
    updDate,updIdAccount) VALUES
   ('".addslashes($file->name)."','".$file->type."','".$file->size."','".$file->hash."',
    '".addslashes($file->file)."','".addslashes($file->label)."','".addslashes($file->description)."',
    '".addslashes($file->tags)."','".addslashes($file->txtContent)."','".api_now()."',
    '".$_SESSION['account']->id."','".api_now()."','".$_SESSION['account']->id."')";
  // execute query
  $GLOBALS['db']->execute($query);
  // get file id
  $file->id=$GLOBALS['db']->lastInsertedId();
  // rename file into file system
  if($path<>NULL){
   if(file_exists("../uploads/uploads/".$path."upload.tmp")){rename("../uploads/uploads/".$path."upload.tmp","../uploads/uploads/".$path.$file->id."-".$file->hash);}
  }
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
function api_file_download($idFile,$table="uploads_uploads",$name=NULL,$force=TRUE,$path=NULL){
 // get file object
 $file=$GLOBALS['db']->queryUniqueObject("SELECT * FROM ".$table." WHERE id='".$idFile."'");
 if($file->id>0){
  if(strlen($name)>0){$file->name=$name;}
  // check for upload path or upload to database
  if(strlen($path)>1){
   if(substr($path,-1)<>"/"){$path=$path."/";}
   if(file_exists("../uploads/uploads/".$path.$file->id."-".$file->hash)){
    //header("location: ../uploads/uploads/".$path.$file->id.$file->name);
    header("Pragma: no-cache");
    header("Cache-Control: no-cache, must-revalidate");
    header("Content-Description: File Transfer");
    header("Content-Type: application/octet-stream");
    header("Content-Length: ".filesize("../uploads/uploads/".$path.$file->id."-".$file->hash));
    if($force){$dispositions="attachment; ";}
    header("Content-Disposition: ".$dispositions."filename=".$file->name);
    ob_end_clean();
    readfile("../uploads/uploads/".$path.$file->id."-".$file->hash);
   }else{
    echo "Error, file not found";
   }
  }else{
   header("Pragma: no-cache");
   header("Cache-Control: no-cache, must-revalidate");
   header('Content-Transfer-Encoding: binary');
   header("Content-Length: ".strlen($file->file));
   header("Content-Type: ".$file->type);
   if($force){$dispositions="attachment; ";}
   header("Content-Disposition: ".$dispositions."filename=".$file->name);
   ob_end_clean();
   echo $file->file;
  }
 }else{
  echo "Error, file not found";
 }
}


/* -[ File Delete from Database ]-------------------------------------------- */
// @integet $idFile : file id
// @string $table : database table name

function api_file_delete($idFile,$table="uploads_uploads",$path=NULL){
 // get file object
 $file=$GLOBALS['db']->queryUniqueObject("SELECT * FROM ".$table." WHERE id='".$idFile."'");
 if($file->id>0){
  $GLOBALS['db']->execute("DELETE FROM ".$table." WHERE id='".$idFile."'");
  if(strlen($path)>1){
   $file->file=NULL;
   if(substr($path,-1)<>"/"){$path=$path."/";}
   if(file_exists("../uploads/uploads/".$path.$file->id."-".$file->hash)){unlink("../uploads/uploads/".$path.$file->id."-".$file->hash);}
  }
  return TRUE;
 }else{
  return "[File not found]";
 }
}

/**
* Link
* @param string $url url to link
* @param string $label label for link
* @param string $title title for link
* @param string $class link css class
* @param booelan $popup show popup title
* @param string $confirm show confirm alert box
* @param string $style manual styles tag
* @param string $target target window
* @param string $id link id or random created
* @return string link
*/
function api_link($url,$label,$title=NULL,$class=NULL,$popup=FALSE,$confirm=NULL,$style=NULL,$target="_self",$id=NULL){
 if($url==NULL){return FALSE;}
 if($id==NULL){$id="link_".rand(111,999);}
 $return="<a id=\"".$id."\" href=\"".$url."\" class='".$class."' style=\"".$style."\"";
 if($popup){
  $return.=" data-toggle='popover' data-placement='top' data-content=\"".$title."\"";
 }elseif($title<>NULL){
  $return.=" title=\"".$title."\"";
 }
 if(strlen($confirm)>0){
  $return.=" onClick=\"return confirm('".addslashes($confirm)."')\"";
 }
 $return.=" target='".$target."'>".$label."</a>";
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


// &define typologies
define("API_LOG_NOTICE",1);
define("API_LOG_WARNING",2);
define("API_LOG_ERROR",3);

/**
* Log an event
*
* @param integer $typology notification typology (use defined constant)
* @param string $module module name
* @param string $action module action
* @param string $event event to log
* @param integer $key item id or key
* @param string $link link to the event item
* @return object notification object
*/
function api_log($typology,$module,$action,$event,$key=NULL,$link=NULL){
 if($typology<1 || $typology>3 || $module==NULL || $action==NULL){return FALSE;}
 // definitions
 $log=new stdClass();
 // clean variables
 $event=addslashes($event);
 // log interpreter id if account is interpreted
 $idAccount=api_account()->id;
 if($_SESSION['account']->interpreter){$idAccount=$_SESSION['account']->interpreter;}
 // build log query
 $query="INSERT INTO logs_logs
  (typology,timestamp,module,action,`key`,event,link,idAccount,ip) VALUES
  ('".$typology."','".api_now()."','".$module."','".$action."','".$key."',
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
    api_loadLocaleFile("../".$module."/",api_account($subscription->idAccount)->language);
    $notification->subject=api_text($trigger->trigger."-subject",api_textParse($event)->parameters);
    $notification->message=api_text($trigger->trigger."-message",api_textParse($event)->parameters);
    // if subscription mail is 2 archive the notification by default
    if($subscription->archived){$status=3;}else{$status=1;}
    // send and acquire notification hash
    $notification->hash=api_notification($subscription->idAccount,$module,$action,$notification->subject,$notification->message,$notification->link,NULL,$status);
    // send mail
    if($subscription->mail){
     $notification->mail=TRUE;
     if(substr($link,0,4)<>"http"){$mail_link=($_SERVER["HTTPS"]?"https://":"http://").$_SERVER['SERVER_NAME'].$GLOBALS['dir'].$link;}
     else{$mail_link=$link;}
     $mail_message=$notification->message."<br>\n"."Link: <a href='".$mail_link."'>".$mail_link."</a>";
     if(api_account()->id>1){
      $mail_from=api_account()->mail;
      $mail_sender=api_account()->name;
     }
     $notification->mail_sent=api_mailer(api_account($subscription->idAccount)->mail,stripslashes($mail_message),stripslashes($notification->subject),TRUE,$mail_from,$mail_sender);
    }
    // build notifications array
    $notifications_array[]=$notification;
    // reload user language file
    api_loadLocaleFile("../".$module."/",api_account()->language);
   }
  }
 }
 // return notifications array
 return $notifications_array;
}




/* --------------------------  N U O V E    A P I  -------------------------- */


/**
 * Get Micro Time
 *
 * @return float intial seconds
 */
function api_getmicrotime(){
 list($usec,$sec)=explode(" ",microtime());
 $seconds=((float)$usec+(float)$sec);
 return $seconds;
}


/**
 * Send notification
 *
 * @param integer $idAccount account id of notification recipient
 * @param string $module module name
 * @param string $action module action
 * @param string $subject log subject
 * @param string $message log message
 * @param string $link log link
 * @param string $hash md5 log hash
 * @param integer $status 1 received, 2 readed, 3 archived
 * @return mixed log hash or FALSE
 */
function api_notification($idAccount,$module,$action,$subject,$message,$link=NULL,$hash=NULL,$status=1){
 if($idAccount<2 || $module==NULL || $subject==NULL || $message==NULL){return FALSE;}
 if($hash===NULL){$hash=md5(date('YdmHsi').api_randomString());}
 $query="INSERT INTO logs_notifications
  (hash,idAccount,timestamp,module,action,subject,message,link,status) VALUES
  ('".$hash."','".$idAccount."','".api_now()."','".$module."','".$action."',
   '".addslashes($subject)."','".addslashes($message)."','".$link."','".$status."')";
 $GLOBALS['db']->execute($query);
 if($GLOBALS['db']->lastInsertedId()>0){return $hash;}
 else{return FALSE;}
}

/**
 * Log History
 *
 * @param string $module module name
 * @param integer $key id of the object
 * @param array $only action to include (null for all)
 * @param array $exclude action to exclude (null for all)
 * @return array array of history events
 */
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
   if(!in_array($event->action,$exclude)){$history_array[]=$event;}
  }else{
   $history_array[]=$event;
  }
 }
 return $history_array;
}

/**
 * Log History Parse
 *
 * @param string $timestamp event date and time
 * @param integer $account event account id
 * @param string $status_from event status from
 * @param string $status_to event status to
 * @param string $note event note
 * @return string parsed history event
 */
function api_logHistoryParse($timestamp,$account,$status_from=NULL,$status_to=NULL,$note=NULL){
 if(!$timestamp||!$account){return FALSE;}
 $return="<div id='history'>\n";
 $return.=" <div id='history_status'>\n";
 $return.="  <small>".api_timestampFormat($timestamp,api_text("datetime"))." - ".api_account($account)->name."</small><br>\n";
 if($status_from){$return.="  <strong><small>".$status_from."</small></strong>";}
 if($status_to){$return.="<strong> &rarr; <small>".$status_to."</small></strong>\n";}
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
 * @return string Glyphicons icon html tag
 */
function api_icon($icon,$title=NULL,$style=NULL){
 if($icon==NULL){return FALSE;}
 if(substr($icon,0,2)=="fa"){$icon="fa ".$icon;}
 $return="<i class='".$icon."' title='".$title."' style='".$style."' aria-hidden='true'></i>";
 return $return;
}


/**
 * Connect to a Web Service with a WSDL file
 *
 * @param string $wsdl Web Service Description Language file
 * @param string $username Web Service username
 * @param string $password Web Service password
 * @return object
 */
function api_webservice_wsdl($wsdl,$username=NULL,$password=NULL){
 if(!file_exists("../core/nusoap/wsdl/".$wsdl)){echo "ERROR WSDL: ".$wsdl." not found";return FALSE;}
 // initialize webservice
 require_once("../core/nusoap/nusoap.php");
 //$nusoap_client=new nusoap_client("../core/nusoap/wsdl/".$wsdl,TRUE);
 $nusoap_client=new nusoap_client("../core/nusoap/wsdl/".$wsdl,TRUE,NULL,NULL,NULL,NULL,0,3600); /* timeout 1 ora */
 if($username<>NULL){$nusoap_client->setCredentials($username,$password);}
 // return nusoap client
 return $nusoap_client;
}


/**
 * Show a variable dump into a pre tag
 *
 * @param string $variable variable to dump
 * @param string $echo typology of echo :  print | dump
 * @param string $label dump label
 * @return print variable dump into a pre tag
 */
function pre_var_dump($variable,$echo="print",$label=NULL){
 echo "<pre>";
 if($label<>NULL){echo "<strong>".$label."</strong><br>";}
 switch($echo){
  case "print":print_r($variable);break;
  default:var_dump($variable);
 }
 echo "</pre>";
}

/**
 * Upper Case Names
 *
 * @param string $string name string
 * @param array $delimiters delimiter characters
 * @return string upper cased name
 */
 function api_ucnames($string,$delimiters=array('-','\'',',')){
  $return=ucwords(strtolower($string));
  foreach($delimiters as $delimiter){
   if(strpos($return,$delimiter)!==false){
    $return=implode($delimiter,array_map('ucfirst',explode($delimiter,$return)));
   }
  }
  return $return;
 }

/**
 * Format a Phone Number
 *
 * @param string $phone phone number
 * @param string $separator separator character
 * @return string formatted phone number
 */
function api_phoneFormat($phone,$separator=" "){
 // check region and set offset
 if(substr($phone,0,1)=="+"){$region=substr($phone,0,3);$offset=3;}else{$offset=0;}
 // switch region
 switch($region){
  case "+00":
   $typology="INT";
  case "+39":
   if(substr($phone,$offset,1)=="0"){$typology="IT";}
   if(substr($phone,$offset,1)=="3"){$typology="IT-mobile";}
   break;
  case "+41":
   $typology="CH";
   break;
 }
 // switch typology
 switch($typology){
  case "INT":
   $phone=substr($phone,$offset,3).$separator.substr($phone,$offset+3,3).$separator.substr($phone,$offset+6,3).$separator.substr($phone,$offset+9);
   $region=NULL;
   break;
  case "IT":
   if(strlen($phone)>($offset+8)){$number_cut=3;}else{$number_cut=2;}
   $phone=substr($phone,$offset,2).$separator.substr($phone,$offset+2,2).$separator.substr($phone,$offset+4,$number_cut).$separator.substr($phone,$offset+4+$number_cut);
   break;
  case "IT-mobile":
   $phone=substr($phone,$offset,3).$separator.substr($phone,$offset+3,3).$separator.substr($phone,$offset+6);
   break;
  case "CH":
   $phone=substr($phone,$offset,2).$separator.substr($phone,$offset+2,3).$separator.substr($phone,$offset+5,2).$separator.substr($phone,$offset+7);
   break;
  default:
   $phone=substr($phone,$offset,2).$separator.substr($phone,$offset+2,2).$separator.substr($phone,$offset+4,3).$separator.substr($phone,$offset+7);
 }
 return trim($region.$separator.$phone,$separator);
}


/**
 * Clean a string
 *
 * @param string $string string to clean
 * @param string $pattern pattern to clean
 * @return string cleaned string
 */
function api_cleanString($string,$pattern="/[^A-Za-zÀ-ÿ0-9-._' ]/",$null=NULL){
 if(!$string){return NULL;}
 $string=preg_replace("!\s+!"," ",$string);
 $string=preg_replace($pattern,"",$string);
 if(!strlen($string)){$string=$null;}
 return $string;
}


/**
 * Clean a number
 *
 * @param string $number number to clean
 * @param string $pattern pattern to clean
 * @return string cleaned number
 */
function api_cleanNumber($number,$pattern="/[^0-9.]/",$null=NULL){
 if(!$number){return NULL;}
 $number=preg_replace($pattern,"",$number);
 if(!strlen($number)){$number=$null;}
 return $number;
}


/**
 * Current date and time
 *
 * @return string current date and time
 */
function api_now(){
 return date("Y-m-d H:i:s");
}


/**
 * Convert tabs to spaces
 *
 * @return string text with space instead of tabs
 */
function api_tab2space($text,$tab=4,$nbsp=FALSE){
 $lines=explode("\n",$text);
 foreach($lines as $line){
  while(($t=mb_strpos($line,"\t"))!==FALSE){
   if($t){$preTab=mb_substr($line,0,$t);}else{$preTab='';}
   $line=$preTab.str_repeat($nbsp?chr(7):' ',$tab-(mb_strlen($preTab)%$tab)).mb_substr($line,$t+1);
  }
  if($nbsp){$line=str_replace($nbsp?chr(7):' ', '&nbsp;',rtrim($line));}
  $return.=rtrim($line)."\n";
 }
 return substr($return,0,-1);
}


/**
 * Query tree
 *
 * @param string $parent parent field
 * @param string $root root field value
 * @param string $order query order
 * @param string $id id field
 * @return array tree of rows
 */
function api_query_tree($parent,$root=NULL,$order=NULL,$id="id"){
 $return=array();
 if($root===NULL){$query_where=$parent." IS NULL";}
  else{$query_where=$parent."='".$root."'";}
 if($order){$query_order=" ORDER BY ".$order;}
 $rows=$GLOBALS['db']->query("SELECT * FROM uploads_folders WHERE ".$query_where.$query_order);
 while($row=$GLOBALS['db']->fetchNextObject($rows)){
  $return[]=$row;
  $return=array_merge($return,api_query_tree($parent,$row->$id,$order));
 }
 return $return;
}


/**
 * Alert box
 *
 * @param string $message alert box content
 * @param string $title alert box title
 * @param string $class alert box css class
 * @return mixed content html source or false
 */
function api_alert_box($message,$title=NULL,$class=NULL){
 if(!strlen($message)){return FALSE;}
 $html="<!-- alert-box -->\n";
 $html.="<div id='alert-message' class='alert ".$class."'>\n";
 $html.=" <button type='button' class='close' data-dismiss='alert'>&times;</button>\n";
 if(strlen($title)){$html.=" <h4>".$title."</h4>\n";}
 $html.=" <span>".$message."<span>\n</div>\n";
 $html.="<!-- /alert-box -->\n\n";
 return $html;
}


/**
 * Tag
 *
 * @param string $tag html tag
 * @param string $text tag content
 * @param string $class span css class
 * @return string content into a tag
 */
function api_tag($tag,$text,$class=NULL){
 if(!strlen($text)){return FALSE;}
 if(!strlen($tag)){return $text;}
 if(strlen($class)){$class=" class='".$class."'";}
 $html="<".$tag.$class.">".$text."</".$tag.">";
 return $html;
}


/**
 * Span
 *
 * @param string $text span content
 * @param string $class span css class
 * @return string content into a span
 */
function api_span($text,$class=NULL){
 if(!strlen($text)){return FALSE;}
 if(strlen($class)){$class=" class='".$class."'";}
 $span="<span".$class.">".$text."</span>";
 return $span;
}


/**
 * Small
 *
 * @param string $text small content
 * @param string $class span css class
 * @return string content into a span
 */
function api_small($text,$class=NULL){
 if(!strlen($text)){return FALSE;}
 if(strlen($class)){$class=" class='".$class."'";}
 $small="<small".$class.">".$text."</small>";
 return $small;
}


/**
 * Sound
 *
 * @param string $sound sound name or path of mp3 file
 * Available sound: alarm,
 * @return boolean FALSE if sound file was not found
 */
function api_sound($sound="alarm"){
 if(substr($sound,-3)<>".mp3"){$sound="../core/sounds/".$sound.".mp3";}
 if(!file_exists($sound)){return FALSE;}
 echo "<!-- sound audio -->\n";
 echo "<audio id='audio_alarm' src='".$sound."' preload='auto'></audio>\n";
 echo "<script type='text/javascript'>\n";
 echo " $(document).ready(function(){\n  document.getElementById('audio_alarm').play();\n });\n";
 echo "</script>\n";
 echo "<!-- /sound audio -->\n";
 return TRUE;
}


/**
 * Image
 *
 * @param string $image image path
 * @param string $class image css class
 * @param string $width image width
 * @param string $height image height
 * @param string $refresh add random string to refresh
 * @return string image tag
 */
function api_image($image,$class=NULL,$width=NULL,$height=NULL,$refresh=FALSE,$style=NULL){
 if($refresh){$refresh="?".rand(1000,9999);}
 $image_tag="<img src='".$image.$refresh."' class='".$class."' width='".$width."' height='".$height."' style=\"".$style."\">";
 return $image_tag;
}


/**
 * Debug
 *
 * @param string $module event generator module
 * @param string $event event description
 * @param integer $typology event typology
 * @return mixed debug event object or FALSE
 */
define("API_DEBUG_NOTICE",1);
define("API_DEBUG_WARNING",2);
define("API_DEBUG_ERROR",3);
function api_debug_event($module,$event,$typology=1){
 if(!strlen($module)||!strlen($event)){return FALSE;}
 if(!is_array($_SESSION['debug'])){$_SESSION['debug']=array();}
 // build debug event object
 $debug=new stdClass();
 $debug->timestamp=api_now();
 $debug->module=$module;
 $debug->event=$event;
 $debug->typology=$typology;
 switch($typology){
  case 1:$debug->typologyText="Notice";break;
  case 2:$debug->typologyText="Warning";break;
  case 3:$debug->typologyText="Error";break;
 }
 // save debug event
 $_SESSION['debug'][]=$debug;
 // return
 return $debug;
}


/**
 * Available languages
 *
 * @param string $path core o module path
 * @return mixed language objects array or false
 */
function api_language_availables($path="../"){
 $dir=$path."languages/";
 if(!is_dir($dir)){return FALSE;}
 $languages_array=array();
 if($dh=opendir($dir)){
  while(($file=readdir($dh))!==false){
   /*if($file=="default.xml"){
    $languages_array["default"]="Default";
   }else*/if(substr($file,-4)==".xml"){
    $language=substr($file,0,-4);
    // load locale for language
    api_loadLocaleFile("../",$language);
    $languages_array[$language]=api_text("language");
   }
  }
  closedir($dh);
 }
 // restore locale
 api_loadLocaleFile("../");
 // return
 return $languages_array;
}

/**
 * Mailer
 *
 * @param string $to recipient A mails comma separated
 * @param string $message mail content
 * @param string $subject mail subject
 * @param booelan $html send mail in HTML format
 * @param string $from_mail sender mail
 * @param string $from_name sender name
 * @param string $cc recipient CC mails comma separed
 * @param string $bcc recipient BCC mails comma separed
 * @param string $attachments attachment paths comma separed
 * @return boolean
 */
function api_mailer($to,$message,$subject="",$html=FALSE,$from_mail="",$from_name="",$cc="",$bcc="",$attachments=""){
 // checks and cleans
 $f_to=addslashes(str_replace(",",";",strtolower(api_cleanString($to,$pattern="/[^A-Za-z0-9-.,;_@]/"))));
 $f_message=addslashes($message);
 $f_subject=addslashes($subject);
 $f_from=addslashes($from_mail);
 $f_sender=addslashes($from_name);
 $f_cc=addslashes(str_replace(",",";",strtolower(api_cleanString($cc,$pattern="/[^A-Za-z0-9-.,;_@]/"))));
 $f_bcc=addslashes(str_replace(",",";",strtolower(api_cleanString($bcc,$pattern="/[^A-Za-z0-9-.,;_@]/"))));
 $f_attachments=addslashes(str_replace(",",";",$attachments));
 if($html){$f_html="1";}else{$f_html="0";}
 if(!strlen($to)){return FALSE;}
 // insert mail into database
 $query="INSERT INTO logs_mails
  (`to`,`cc`,`bcc`,`from`,`sender`,`subject`,`message`,`attachments`,`html`,
   `addDate`,`addIdAccount`) VALUES
  ('".$f_to."','".$f_cc."','".$f_bcc."','".$f_from."','".$f_sender."','".$f_subject."',
   '".$f_message."','".$f_attachments."','".$f_html."','".api_now()."','".api_account()->id."')";
 // execute query
 $GLOBALS['db']->execute($query);
 // set id to last inserted id
 $q_idMail=$GLOBALS['db']->lastInsertedId();
 // check if insert
 if($q_idMail>0){$return=TRUE;}else{$return=FALSE;}
 // check if asynchronous sendmail is false and send
 if(!api_getOption("sendmail_asynchronous")){$return=api_mailer_process($q_idMail);}
 // return
 return $return;
}

/**
 * Mailer Process
 *
 * @param mixed $mail mail id or object
 * @return boolean
 */
function api_mailer_process($mail){
 // get object
 if(is_numeric($mail)){$mail=$GLOBALS['db']->queryUniqueObject("SELECT * FROM logs_mails WHERE id='".$mail."'");}
 if(!isset($mail->id)){return FALSE;}
 // definitions
 $to_array=explode(";",$mail->to);
 $cc_array=explode(";",$mail->cc);
 $bcc_array=explode(";",$mail->bcc);
 $attachments_array=explode(";",$mail->attachments);
 // requrie php mailer class
 require_once("../core/phpmailer/PHPMailerAutoload.php");
 // build php mailer
 $mailer=new PHPMailer;
 $mailer->CharSet="UTF-8";
 // check for stmp or relay
 if(api_getOption("smtp")){
  $mailer->isSMTP();
  // host
  $mailer->Host=api_getOption("smtp_host");
  // authentication
  if(strlen(api_getOption("smtp_username"))>0){
   $mailer->SMTPAuth=TRUE;
   $mailer->Username=api_getOption("smtp_username");
   $mailer->Password=api_getOption("smtp_password");
  }else{
   $mailer->SMTPAuth=FALSE;
  }
  // secure
  switch(strtolower(api_getOption("smtp_secure"))){
   case "tls":$mailer->SMTPSecure="tls";break;
   case "ssl":$mailer->SMTPSecure="ssl";break;
   default:
    $mailer->SMTPSecure=NULL;
    $mailer->SMTPAutoTLS=FALSE;
  }
 }
 // sender
 if(!strlen($mail->from)){$mail->from=api_getOption('owner_mail');}
 if(!strlen($mail->sender)){$mail->sender=api_getOption('owner_mail_from');}
 $mailer->From=api_getOption('owner_mail');
 $mailer->FromName=stripslashes($mail->sender);
 $mailer->addReplyTo(stripslashes($mail->from));
 // receivers
 if(count($to_array)){foreach($to_array as $to){$mailer->addAddress(trim(stripslashes($to)));}}
 if(count($cc_array)){foreach($cc_array as $cc){$mailer->addCC(trim(stripslashes($cc)));}}
 if(count($bcc_array)){foreach($bcc_array as $bcc){$mailer->addBCC(trim(stripslashes($bcc)));}}
 if(count($attachments_array)){foreach($attachments_array as $attachment){$mailer->addAttachment(trim(stripslashes($attachment)));}}
 // subject
 $mailer->Subject=stripslashes($mail->subject);
 // message
 $mail->message.="\n\n--\nThis message was automatically generated by Coordinator for company ".api_getOption("owner").", please do not respond.";
 if($mail->html){
  $mailer->isHTML(TRUE);
  $mailer->Body=stripslashes(nl2br($mail->message));
  $mailer->AltBody=strip_tags(str_replace("<br>","\n",stripslashes($mail->message)));
 }else{
  $mailer->Body=strip_tags(str_replace("<br>","\n",stripslashes($mail->message)));
 }
 // sendmail
 $sended=$mailer->send();
 // check result for mailer
 if($mail->id>0){
  // update status
  if(!$sended){$f_status="2";$f_error=$mailer->ErrorInfo;}else{$f_status="1";}
  $GLOBALS['db']->execute("UPDATE `logs_mails` SET `status`='".$f_status."',`sendDate`='".api_now()."',`error`='".$f_error."' WHERE id='".$mail->id."'");
  // return
  return $sended;
 }
 // check result for manual calls
 if($mail->id==0){
  // return ok or error
  if(!$sended){return $mailer->ErrorInfo;}else{return "ok";}
 }
 // in other case return false
 return FALSE;
}


/**
 * Account object
 *
 * @param integer $idAccount account id or null for self
 * @return object account object
 */
function api_account($idAccount=NULL){
 if($idAccount===0||$idAccount==="0"){return FALSE;}
 if($idAccount===NULL){$account=$_SESSION['account'];}
 else{$account=api_accounts_account($idAccount);}
 if($account->id){return $account;}
 return FALSE;
}


/**
 * Company object
 *
 * @param mixed $company company id, object or null for active
 * @return object company object
 */
function api_company($company=NULL){
 if($company===NULL){$company=$_SESSION['company'];}
 if(is_numeric($company)){$company=api_accounts_company($company);}
 if($company->id){return $company;}
 return FALSE;
}


/**
 * Add groups and subgroups into array recursively
 *
 * @param array $groups group array to cycle
 * @param array $array array to implement
 */
function api_walkGroupsRecursively($groups,&$array){
 foreach($groups as $group){
  $array[$group->id]=$group->id;
  api_walkGroupsRecursively($group->groups,$array);
 }
}


/**
 * Load Account Permission
 *
 * @return permissions array
 */
function api_loadAccountPermission(){
 // definitions
 $permissions_array=array();
 // get permissions
 $permissions=$GLOBALS['db']->query("SELECT * FROM settings_permissions ORDER BY module ASC,action ASC");
 while($permission=$GLOBALS['db']->fetchNextObject($permissions)){
  // retrieve required groups
  $query="SELECT settings_permissions_join_accounts_groups.*,
   IFNULL(settings_permissions_join_accounts_groups.idCompany,accounts_groups.idCompany) as idCompany,
   IFNULL(settings_permissions_join_accounts_groups.idGroup,'0') AS idGroup
   FROM settings_permissions_join_accounts_groups
   LEFT JOIN accounts_groups ON accounts_groups.id=settings_permissions_join_accounts_groups.idGroup
   WHERE settings_permissions_join_accounts_groups.idPermission='".$permission->id."'";
  $required_groups=$GLOBALS['db']->query($query);
  while($required_group=$GLOBALS['db']->fetchNextObject($required_groups)){
   // definitions
   $permitted=FALSE;
   $subgroups_array=array();
   // check if account is associated to the company
   if(api_account()->companies[$required_group->idCompany]==NULL){continue;}
   // check groups or company level
   if($required_group->idGroup==0){
    // check if account company level <= required level
    if(api_account()->companies[$required_group->idCompany]->role->level<=$required_group->level){$permitted=TRUE;}
   }else{
    // check if group is in array account company groups
    if(array_key_exists($required_group->idGroup,api_account()->companies[$required_group->idCompany]->groups)){
     // check if account company level <= required level
     if(api_account()->companies[$required_group->idCompany]->role->level<=$required_group->level){$permitted=TRUE;}
    }
   }
   if($permitted){
    $permissions_array[$permission->module][$permission->action]=TRUE;
    continue;
   }
   // retrieve subgroups
   $subgroups=api_accounts_groups($required_group->idCompany,$required_group->idGroup);
   api_walkGroupsRecursively($subgroups->results,$subgroups_array);
   // check subgroups
   foreach($subgroups_array as $subgroup){
    // check if subgroup is in array account company groups
    if(array_key_exists($subgroup,api_account()->companies[$required_group->idCompany]->groups)){
     // check if account company level <= required level
     if(api_account()->companies[$required_group->idCompany]->role->level<=$required_group->level){$permitted=TRUE;}
    }
   }
   if($permitted){
    $permissions_array[$permission->module]["inherited"][$permission->action]=TRUE;
    continue;
   }
  }
 }
 return $permissions_array;
}


/**
 * Check Permission
 *
 * @param string $module coordinator module
 * @param string $action module action to check
 * @param booelan $alert show unauthorized alert box
 * @param booelan $admin permit admin bypass
 * @param booelan $subgroups check in subgroups
 * @param integer $idAccount account to check or self
 * @return boolean
 */
function api_checkPermission($module,$action,$alert=FALSE,$admin=TRUE,$subgroups=TRUE,$idAccount=NULL){
 if(!strlen($module)||!strlen($action)){return NULL;}
 // if account is 0 return null
 if($idAccount===0 || $idAccount==="0"){return NULL;}
 // if account is root return always true
 if($idAccount==1 && $admin==TRUE){return TRUE;}
 if(api_account()->id==1 && $admin==TRUE){return TRUE;}
 // if admin and account is superuser in administrator mode return always true
 if($admin==TRUE && $idAccount===NULL && api_account()->administrator){return TRUE;}
 // if logged account use session permissions
 if($idAccount===NULL){
  if(is_array($_SESSION['permissions'])){
   if(array_key_exists($module,$_SESSION['permissions'])){
    if(array_key_exists($action,$_SESSION['permissions'][$module])){return TRUE;}
    if($subgroups){if(array_key_exists($action,$_SESSION['permissions'][$module]["inherited"])){return TRUE;}}
   }
  }
 }else{
  // retrieve the permission id
  $idPermission=$GLOBALS['db']->queryUniqueValue("SELECT id FROM settings_permissions WHERE module='".$module."' AND action='".$action."'");
  // check permission
  if(!$idPermission){
   echo api_alert_box(api_text("permissionNotFound",array(api_tag("i",$module),api_tag("i",$action))),NULL,"alert-warning");
   return FALSE;
  }
  // ---------------------------------------------------------------------------- vecchio sistema ancora in uso per utente diverso da colui che è loggato (valutare se si può ottimizzare)
  // get account object
  $account=api_accounts_account($idAccount);
  // retrieve required groups
  $query="SELECT settings_permissions_join_accounts_groups.*,
   IFNULL(settings_permissions_join_accounts_groups.idCompany,accounts_groups.idCompany) as idCompany,
   IFNULL(settings_permissions_join_accounts_groups.idGroup,'0') AS idGroup
   FROM settings_permissions_join_accounts_groups
   LEFT JOIN accounts_groups ON accounts_groups.id=settings_permissions_join_accounts_groups.idGroup
   WHERE settings_permissions_join_accounts_groups.idPermission='".$idPermission."'";
  $required_groups=$GLOBALS['db']->query($query);
  while($required_group=$GLOBALS['db']->fetchNextObject($required_groups)){
   // definitions
   $subgroups_array=array();
   // check if account is associated to the company
   if($account->companies[$required_group->idCompany]==NULL){continue;}
   // check groups or company level
   if($required_group->idGroup==0){
    // check if account company level <= required level
    if($account->companies[$required_group->idCompany]->role->level<=$required_group->level){return TRUE;}
   }else{
    // check if group is in array account company groups
    if(array_key_exists($required_group->idGroup,$account->companies[$required_group->idCompany]->groups)){
     // check if account company level <= required level
     if($account->companies[$required_group->idCompany]->role->level<=$required_group->level){return TRUE;}
    }else{
     // check subgroups
     if($subgroups){
      // retrieve subgroups
      $subgroups=api_accounts_groups($required_group->idCompany,$required_group->idGroup);
      api_walkGroupsRecursively($subgroups->results,$subgroups_array);
      // check subgroups
      foreach($subgroups_array as $subgroup){
       // check if subgroup is in array account company groups
       if(array_key_exists($subgroup,$account->companies[$required_group->idCompany]->groups)){
        // check if account company level <= required level
        if($account->companies[$required_group->idCompany]->role->level<=$required_group->level){return TRUE;}
       }
      }
     }
    }
   }
  }
 }
 // ----------------------------------------------------------------------------
 if($alert){echo api_alert_box(api_text("accessDenied-message")." ".api_text("accessDenied-action",api_tag("i",$action)),api_text("accessDenied"),"alert-error");}
 return FALSE;
}

/**
 * Check Permission to Show Module ( any module permission )
 *
 * @param string $module coordinator module
 * @param booelan $admin permit admin bypass
 * @return boolean
 */
function api_checkPermissionShowModule($module,$admin=TRUE){
 if(!strlen($module)){return NULL;}
 // if account is root return always true
 if(api_account()->id==1 && $admin==TRUE){return TRUE;}
 // if admin and account is superuser in administrator mode return always true
 if(api_account()->administrator && $admin==TRUE){return TRUE;}
 // retrieve all module permissions
 $permissions=$GLOBALS['db']->query("SELECT * FROM settings_permissions WHERE module='".$module."'");
 while($permission=$GLOBALS['db']->fetchNextObject($permissions)){
  // check permissions for all actions
  if(api_checkPermission($module,$permission->action,FALSE,$admin,api_account()->id)){return TRUE;}
 }
 return FALSE;
}


/**
 * Check if an account is member of a group or his subgroups
 *
 * @param integer $idGroup group to check
 * @param integer $idAccount account to check or self
 * @param booelan $subGroups check also in his sub groups
 * @return boolean
 */
function api_accountGroupMember($idGroup,$idAccount=NULL,$subGroups=TRUE){
 // definitions
 $subgroups_array=array();
 // get objects
 $group=api_accounts_group($idGroup,$subGroups);
 $account=api_accounts_account($idAccount);
 // checks
 if(!$group->id||!$account->id){return FALSE;}
 // check if group is in array account company groups
 if(is_array($account->companies[$group->idCompany]->groups)){
  if(array_key_exists($group->id,$account->companies[$group->idCompany]->groups)){return TRUE;}
 }
 // subgroups
 if($subGroups){
  // retrieve subgroups
  $subgroups=api_accounts_groups($group->idCompany,$group->id);
  api_walkGroupsRecursively($subgroups->results,$subgroups_array);
  // check subgroups
  foreach($subgroups_array as $subgroup){
   // check if subgroup is in array account company groups
   if(is_array($account->companies[$group->idCompany]->groups)){
    if(array_key_exists($subgroup,$account->companies[$group->idCompany]->groups)){return TRUE;}
   }
  }
 }
 return FALSE;
}

/**
 * Period (from number to text)
 *
 * @param integer $period
 * @return string|boolean Textual period
 */
function api_period($period){
 // check parameters
 if(strlen($period)!=6){return false;}
 // definitions
 $year=(int)substr($period,0,4);
 $month=(int)substr($period,4,2);
 // set locale
 setlocale(LC_TIME,$GLOBALS['session']->user->localization);
 // convert month to text
 $return=ucfirst(strftime("%B",strtotime($year."-".$month."-01")))." ".$year;
 // return
 return $return;
}

/**
 * WSRFC API
 *
 * @param string $wsrfc WSRFC Configuration [ default | development | production ]
 * @param string $function Function Module
 * @param array $input Function Input Parameters
 * @param string $username SAP Username
 * @param string $password SAP Password
 * @return boolean
 */
function api_wsrfc($wsrfc,$function,$input,$username=null,$password=null,$verbose=false){
 // include configuration
 require("../config.inc.php");
 // check for sap token configuration
 if(!is_array($sap_wsrfc[$wsrfc])){return false;}
 // get from token
 $url=$sap_wsrfc[$wsrfc]['url'];
 $token=$sap_wsrfc[$wsrfc]['token'];
 // check for authentication
 if(!$username){$username=$sap_wsrfc[$wsrfc]['username'];}
 if(!$password){$password=$sap_wsrfc[$wsrfc]['password'];}
 // build post data
 $post_data=array(
     "token"=>$token,
     "username"=>$username,
     "password"=>$password,
     "function"=>$function,
     "input"=>$input,
     "verbose"=>($verbose?1:0)
 );
 // build http options
 $options=array(
  'http'=>array(
   'header'=>"Content-type: application/x-www-form-urlencoded\r\n",
   'method'=>'POST',
   'content'=>http_build_query($post_data),
  ),
 );
 // make stram context
 $context=stream_context_create($options);
 // get from http
 $response=file_get_contents($url,false,$context);
 // decode result
 $return=json_decode($response,true);
 // return
 return $return;
}










/*
 * -----------------------------------------------------------------------------
 *                D E P R E C A T E D     F U N C T I O N S
 * -----------------------------------------------------------------------------
 */

function api_accountMail($account_id=NULL){api_deprecatedAlert("api_accountMail","api_account()->mail");}
function api_accountId($account_id=NULL){api_deprecatedAlert("api_accountId","api_account()->id");}
function api_accountName($account_id=NULL){api_deprecatedAlert("api_accountName","api_account()->name");}
function api_accountFirstname($account_id=NULL){api_deprecatedAlert("api_accountFirstname","api_account()->firstname");}



/* -[ Sendmail ]---------- OLD DA NON USARE DA ------------------------------ */
// @string $to_mail : Recipient mail
// @string $message : Content of mail
// @string $subject : Subject of mail
// @booelan $html : Send mail in HTML format
// @string $from_mail : Sender mail
// @string $from_name : Sender name
// @string $cc_mails : Carbon Copy mails
/**
 * Sendmail
 *
 * /!\ ATTENZIONE /!\
 *
 * Vecchia funziona da non usare
 *
 * Sostituita con api_mailer()
 *
 */
function api_sendmail($to_mail,$message,$subject="",$html=FALSE,$from_mail="",$from_name="",$cc_mails=""){
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
 if(strlen($cc_mails)>0){$headers.="CC: ".str_replace(" ","",$cc_mails).$eol;}
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
 // sendmail
 return mail($to_mail,$subject,$message,$headers);
}


?>