<?php

/* -[ Global parameter ]----------------------------------------------------- */
session_start();
global $debug;                  // debug variable
global $html;                   // html structure resource
global $db;                     // database resource
global $path;                   // base path of web root directory
global $dir;                    // directory of web application
global $alert;                  // alerts global variable
global $locale;                 // array with translation
include("../config.inc.php");   // include the configuration file
include("html.class.php");      // include the html class
include("structure.class.php"); // include structure classes
include("db.class.php");        // include the database class
// include core api+
include_once("../accounts/api.inc.php");
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
     && api_baseName()<>"accounts_ldap.php"
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
    $GLOBALS['dir']."chats/chat_counter.inc.php",
    $GLOBALS['dir']."logs/logs_notifications_list.inc.php",
    $GLOBALS['dir']."logs/logs_notifications_counter.inc.php");
   if(!in_array($_SERVER['REQUEST_URI'],$url_skip)){
    $_SESSION['external_redirect']="http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
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

/* -[ Boolean Text ]--------------------------------------------------------- */
// @param $boolean : boolean value
function api_text_boolean($boolean){
 if($boolean){return api_text("yes");}else{return api_text("no");}
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


/* -[ Timestamp Format ]----------------------------------------------------- */
// @param $timestamp : MySql timestamp
// @param $format : datetime format
// @param $language : language conversion
function api_timestampFormat($timestamp,$format="Y-m-d H:i",$language=NULL){
 if($timestamp==NULL){return NULL;}
 if(!strlen($language)){$language=$_SESSION['language'];}
 $datetime=new DateTime($timestamp);
 $return=$datetime->format($format);
 // if language not default
 if($language<>"default"){
  if($language<>$_SESSION['language']){api_loadLocaleFile("../",$language);}
  $days=array("Monday","Tuesday","Wednesday","Thursday","Friday","Saturday","Sunday");
  $locale_days=array(api_text("day-monday"),api_text("day-tuesday"),api_text("day-wednesday"),api_text("day-thursday"),api_text("day-friday"),api_text("day-saturday"),api_text("day-sunday"));
  $months=array("January","February","March","April","May","July","August","September","October","November","December");
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
 $group=api_accounts_group($idGroup);
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

/* -[ Load Module API, Languages and Required Modules ]---------------------- */
// @array $modules_required : modules name to be included
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
  if($label<>NULL){$file->label=api_cleanString($label,"/[^A-Za-z0-9- ]/");}else{$file->label=substr($file->name,0,-4);}
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


/* -[ Link ]----------------------------------------------------------------- */
// @string $url : url to link
// @string $label : label for link
// @string $title : title for link
// @string $class : url css class
// @booelan $popup : show popup label
// @string $confirm : show confirm alert
// @string $style : manual styles tag
// @string $target : target window
// @string $id : link id
function api_link($url,$label,$title=NULL,$class=NULL,$popup=FALSE,$confirm=NULL,$style=NULL,$target="_self",$id=NULL){
 if($url==NULL){return FALSE;}
 $return="<a id=\"link_".$id."\" href=\"".$url."\" class='".$class."' style=\"".$style."\"";
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
     if(substr($link,0,4)<>"http"){$mail_link="http://".$_SERVER['SERVER_NAME'].$GLOBALS['dir'].$link;}
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
 $return="<i class='".$icon."' title='".$title."' style='".$style."'></i>";
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
 if(!file_exists("../core/nusoap/wsdl/".$wsdl)){return FALSE;}
 // initialize webservice
 require_once("../core/nusoap/nusoap.php");
 $nusoap_client=new nusoap_client("../core/nusoap/wsdl/".$wsdl,TRUE);
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
 if(!strlen($tag)||!strlen($text)){return FALSE;}
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
function api_image($image,$class=NULL,$width=NULL,$height=NULL,$refresh=FALSE){
 if($refresh){$refresh="?".rand(1000,9999);}
 $image_tag="<img src='".$image.$refresh."' class='".$class."' width='".$width."' height='".$height."'>\n";
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
 if(!$mail->id){return FALSE;}
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
  switch(strtolower(api_getOption("smtp_username"))){
   case "tls":$mailer->SMTPSecure="tls";break;
   case "ssl":$mailer->SMTPSecure="ssl";break;
  }
 }
 // sender
 if(!strlen($mail->from)){$mail->from=api_getOption('owner_mail');}
 if(!strlen($mail->sender)){$mail->sender=api_getOption('owner_mail_from');}
 $mailer->From=stripslashes($mail->from);
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
 // update status
 if(!$sended){$f_status="2";$f_error=$mailer->ErrorInfo;}else{$f_status="1";}
 $GLOBALS['db']->execute("UPDATE `logs_mails` SET `status`='".$f_status."',`sendDate`='".api_now()."',`error`='".$f_error."' WHERE id='".$mail->id."'");
 // return
 return $sended;
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
 * Check Permission
 *
 * @param string $module coordinator module
 * @param string $action module action to check
 * @param booelan $alert show unauthorized alert box
 * @param booelan $admin permit admin bypass
 * @param integer $idAccount account to check or self
 * @return boolean
 */
function api_checkPermission($module,$action,$alert=FALSE,$admin=TRUE,$idAccount=NULL){
 if(!strlen($module)||!strlen($action)){return NULL;}
 // if account is 0 return null
 if($idAccount===0 || $idAccount==="0"){return NULL;}
 // if account is root return always true
 //if(api_account()->id==1 && $admin==TRUE){return TRUE;} // -------------------
 if($idAccount==1 && $admin==TRUE){return TRUE;}
 // if admin and account is superuser return always true
 if($admin==TRUE && $idAccount===NULL && api_account()->administrator){return TRUE;}
 // retrieve the permission id
 $idPermission=$GLOBALS['db']->queryUniqueValue("SELECT id FROM settings_permissions WHERE module='".$module."' AND action='".$action."'");
 // check permission
 if(!$idPermission){
  echo api_alert_box(api_text("permissionNotFound",array(api_tag("i",$module),api_tag("i",$action))),NULL,"alert-warning");
  return FALSE;
 }
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
 // if account typology is administrator return always true
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
 $group=api_accounts_group($idGroup);
 $account=api_accounts_account($idAccount);
 // checks
 if(!$group->id||!$account->id){return FALSE;}
 // check if group is in array account company groups
 if(array_key_exists($group->id,$account->companies[$group->idCompany]->groups)){return TRUE;}
 // subgroups
 if($subGroups){
  // retrieve subgroups
  $subgroups=api_accounts_groups($group->idCompany,$group->id);
  api_walkGroupsRecursively($subgroups->results,$subgroups_array);
  // check subgroups
  foreach($subgroups_array as $subgroup){
   // check if subgroup is in array account company groups
   if(array_key_exists($subgroup,$account->companies[$group->idCompany]->groups)){return TRUE;}
  }
 }
 return FALSE;
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