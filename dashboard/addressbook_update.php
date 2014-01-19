<?php
/* -------------------------------------------------------------------------- *\
|* -[ Address - Address Book Update ]---------------------------------------- *|
\* -------------------------------------------------------------------------- */
$checkSession=FALSE;
include("../core/api.inc.php");
// check token
//api_checkToken();
// acquire variables
$g_submit=$_GET['submit'];
// check if submit from web form or cron
if($g_submit<>"cron"){$html->header("Address Book Update");}
// connect to ldap
$ldap=ldap_connect($ldap_host,3268);
if($ldap){
 // try to bind
 $bind=ldap_bind($ldap,"testsis".$ldap_domain,"Aosta123456");
 if($bind){
  // set options
  ldap_set_option($ldap,LDAP_OPT_REFERRALS,0);
  ldap_set_option($ldap,LDAP_OPT_PROTOCOL_VERSION,3);
  //ldap_set_option($ldap,LDAP_OPT_SIZELIMIT,5000);
  // setup filter to show only people
  $filter="(&(objectClass=user)(objectCategory=person)(!(userAccountControl:1.2.840.113556.1.4.803:=2)))";
  //$filter="samaccountname=mgrosso"; // <- debug
  $get=array("samaccountname","sn","givenname","mail","telephonenumber","mobile","othermobile","physicaldeliveryofficename","description");
  $search=ldap_search($ldap,$ldap_dn,$filter,$get);
  //$search=ldap_search($ldap,"".$ldap_dn,$filter); // <- debug
  $results=ldap_get_entries($ldap,$search);
  // definitions
  $mysql_querys=array();
  // fetch results
  foreach($results as $result){
   //var_dump($result); // <- debug
   if($result["samaccountname"][0]<>"" && $result["sn"][0]<>"" && $result["givenname"][0]<>"" && $result["mail"][0]<>""){
    // phone check
    $phone=$result["telephonenumber"][0];
    $phone=str_replace(array(" ","/","(",")"),"",$phone);
    if(strlen($phone)>5 and substr($phone,0,1)<>"+"){$phone="+39".$phone;}
    if(strlen($phone)==4 and substr($phone,0,1)=="2"){$phone="+39016530".$phone;}
    // mobile phone check
    $mobile=$result["mobile"][0];
    $mobile=str_replace(array(" ","/","(",")"),"",$mobile);
    if(strlen($mobile)>5 && substr($mobile,0,1)<>"+"){$mobile="+39".$mobile;}
    // mysql query
    $mysql_querys[]="INSERT INTO organics_addressbook
     (account,firstname,lastname,mail,phone,mobile,mobile_short,office,description) VALUES
     ('".addslashes(strtolower($result["samaccountname"][0]))."',
      '".addslashes(ucfirst(strtolower($result["sn"][0])))."',
      '".addslashes(ucfirst(strtolower($result["givenname"][0])))."',
      '".addslashes(strtolower($result["mail"][0]))."',
      '".addslashes($phone)."',
      '".addslashes($mobile)."',
      '".addslashes($result["othermobile"][0])."',
      '".addslashes(strtoupper($result["physicaldeliveryofficename"][0]))."',
      '".addslashes($result["description"][0])."');";
   }
  }
  // truncate mysql table
  $mysql_deleted_row=$GLOBALS['db']->countOfAll("organics_addressbook");
  if(count($mysql_querys)>0){
   $GLOBALS['db']->execute("TRUNCATE TABLE organics_addressbook");
  }
  // execute query
  $timerStart=microtime(TRUE);
  foreach($mysql_querys as $mysql_query){
   $GLOBALS['db']->execute($mysql_query);
   //echo $mysql_query."<br>"; // <- debug
  }
  $timerEnd=microtime(TRUE);
  $timer_mysql=number_format($timerEnd-$timerStart,2,".","");
 }
}
// close ldap connection
ldap_close($ldap);
// log of the operation
$log_level=1;
if(!(float)$timer_mysql>0){$log_level=2;}
if(count($mysql_querys)==0){$log_level=3;}
if($log_level==1){$log="ADRESS BOOK - UPDATED SUCCESSFULLY\n";}
if($log_level==2){$log="ADRESS BOOK - WARNING UPDATING\n";}
if($log_level==3){$log="ADRESS BOOK - ERROR UPDATING\n";}
$log.="Query MySQL execute in ".$timer_mysql." seconds\n";
$log.="Number of MySQL row deleted: ".$mysql_deleted_row."\n";
$log.="Number of MySQL query executed: ".count($mysql_querys)."\n";
api_log($log_level,"dashboard",$log);
// show footer
if($g_submit<>"cron"){
 echo nl2br($log);
 $html->footer();
}else{
 echo $log;
}
?>