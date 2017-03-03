<?php

// ---[ PHP class for MySQL database ]------------------------------------------

class DB{
 var $connector;          // connector of mysql istance
 var $defaultDebug=false; // true if you want ALL queries to be debugged
 var $mtStart;            // Start time in milliseconds
 var $nbQueries;          // Number of executed querys
 var $lastResult;         // The last result ressource of a query()

 // ---[ Connect to a MySQL database ]-----------------------------------------
 function DB($host,$user,$pass,$name){
  $this->mtStart=$this->getMicroTime();
  $this->nbQueries=0;
  $this->lastResult=NULL;
  $this->connector=mysql_connect($host,$user,$pass,TRUE) or die('Server connection not possible.');
  mysql_select_db($name,$this->connector) or die('Database connection not possible.');
  mysql_query("SET NAMES UTF8",$this->connector);
 }

 // ---[ Clear Query ]---------------------------------------------------------
 // @param $query : The query.
 // @return : The query cleared
 function clearQuery($query){
  if(stripos($query,"select")!==FALSE){return $query;}
  $search=array("``","''",'""');
  $query=str_replace($search,"DEFAULT",$query);
  $query=str_replace("\DEFAULT","'",$query); /* @fixme fix last ' bug */
  return $query;
 }

 // ---[ Query the database ]--------------------------------------------------
 // @param $query : The query.
 // @param $clear : If true, clear the query.
 // @param $debug : If true, it output the query and the resulting table.
 // @return : The result of the query, to use with fetchNextObject().
 function query($query,$clear=TRUE,$debug=-1){
  if($query==NULL){return FALSE;}
  if($clear){$query=$this->clearQuery($query);}
  $this->nbQueries++;
  $this->lastResult=mysql_query($query,$this->connector) or $this->debugAndDie($query);
  $this->debug($debug,$query,$this->lastResult);
  return $this->lastResult;
 }

 // ---[ Query the database but do not return nor store result ]---------------
 // @param $query : The query.
 // @param $clear : If true, clear the query.
 // @param $debug : If true, it output the query and the resulting table.
 function execute($query,$clear=TRUE,$debug=-1){
  if($query==NULL){return FALSE;}
  if($clear){$query=$this->clearQuery($query);}
  $this->nbQueries++;
  mysql_query($query,$this->connector) or $this->debugAndDie($query);
  $this->debug($debug, $query);
 }

 // ---[ Query the database but and return instead of die ]--------------------
 // @param $query : The query.
 // @param $debug : If true, it output the query and the resulting table.
 // @return : Return null if executed or error
 function executeAndReturnError($query,$debug=-1){
  if($query==NULL){return FALSE;}
  $query=$this->clearQuery($query);
  $this->nbQueries++;
  if(!mysql_query($query,$this->connector)){return mysql_error();}
  return NULL;
 }

 // ---[ Convenient method for mysql_fetch_object() ]--------------------------
 // @param $result : The ressource returned by query()
 //                  If NULL, the last result returned by query() will be used.
 // @return : An object representing a data row.
 function fetchNextObject($result=NULL){
  if($result==NULL)$result=$this->lastResult;
  if($result==NULL || mysql_num_rows($result)<1){
   return NULL;
  }else{
   return mysql_fetch_object($result);
  }
 }

 // ---[ Convenient method for mysql_fetch_object() ]--------------------------
 // @param $result : The ressource returned by query()
 //                  If NULL, the last result returned by query() will be used.
 // @param $method : MYSQL_BOTH, MYSQL_NUM, MYSQL_ASSOC
 // @return : An object representing a data row.
 function fetchNextArray($result=NULL,$method=MYSQL_BOTH){
  if($result==NULL)$result=$this->lastResult;
  if($result==NULL || mysql_num_rows($result)<1){
   return NULL;
  }else{
   return mysql_fetch_array($result,$method);
  }
 }

 // ---[ Get the number of rows of a query ]-----------------------------------
 // @param $result : The ressource returned by query()
 //                  If NULL, the last result returned by query() will be used.
 // @return : The number of rows of the query (0 or more).
 function numRows($result=NULL){
  if($result==NULL){
   return mysql_num_rows($this->lastResult);
  }else{
   return mysql_num_rows($result);
  }
 }

 // ---[ Query the database and return a unique row ]--------------------------
 // @param $query : The query.
 // @param $debug : If true, it output the query and the resulting row.
 // @return : An object representing a data row (or NULL if result is empty).
 function queryUniqueObject($query,$debug=-1){
  $query=$this->clearQuery($query);
  $query="$query LIMIT 1";
  $this->nbQueries++;
  $result=mysql_query($query,$this->connector) or $this->debugAndDie($query);
  $this->debug($debug,$query,$result);
  return mysql_fetch_object($result);
 }

 // ---[ Query the database and return a unique cell ]-------------------------
 // @param $query : The query.
 // @param $debug : If true, it output the query and the resulting value.
 // @return : A value representing a data cell (or NULL if result is empty).
 function queryUniqueValue($query,$debug=-1){
  $query=$this->clearQuery($query);
  $query="$query LIMIT 1";
  $this->nbQueries++;
  $result=mysql_query($query,$this->connector) or $this->debugAndDie($query);
  $line=mysql_fetch_row($result);
  $this->debug($debug,$query,$result);
  return $line[0];
 }

 // ---[ Query the database and return a unique cell ]-------------------------
 // @param $query : The query.
 // @param $debug : If true, it output the query and the resulting value.
 // @return : A value representing a data cell (or NULL if result is empty).
 function queryUniqueValueNoLimit($query,$debug=-1){
  $query=$this->clearQuery($query);
  $this->nbQueries++;
  $result=mysql_query($query,$this->connector) or $this->debugAndDie($query);
  $line=mysql_fetch_row($result);
  $this->debug($debug,$query,$result);
  return $line[0];
 }

 // ---[ Get the maximum value of a column in a table, with a condition ]------
 // @param $column : The column where to compute the maximum.
 // @param $table : The table where to compute the maximum.
 // @param $where : The condition before to compute the maximum.
 // @return : The maximum value (or NULL if result is empty).
 function maxOf($column,$table,$where){
  return $this->queryUniqueValue("SELECT MAX(`$column`) FROM $table WHERE $where");
 }

 // ---[ Get the maximum value of a column in a table ]------------------------
 // @param $column : The column where to compute the maximum.
 // @param $table : The table where to compute the maximum.
 // @return : The maximum value (or NULL if result is empty).
 function maxOfAll($column,$table){
  return $this->queryUniqueValue("SELECT MAX(`$column`) FROM $table");
 }

 // ---[ Get the count of rows in a table, with a condition ]------------------
 // @param $table : The table where to compute the number of rows.
 // @param $where : The condition before to compute the number or rows.
 // @return : The number of rows (0 or more).
 function countOf($table,$where){
  return $this->queryUniqueValue("SELECT COUNT(*) FROM $table WHERE $where");
 }

 // ---[ Get the count of rows in a table ]---
 // @param $table : The table where to compute the number of rows.
 // @return The number of rows (0 or more).
 function countOfAll($table){
  return $this->queryUniqueValue("SELECT COUNT(*) FROM $table");
 }

 // ---[ Internal function to debug when MySQL encountered an error ]----------
 // @param $query : The SQL query to echo before diying.
 function debugAndDie($query){
  $this->debugQuery($query,"Error");
  die("<p style=\"margin: 2px;\">".mysql_error()."</p></div>\n");
 }

 // --[ Internal function to debug a MySQL query ]---
 // Show the query and output the resulting table if not NULL.
 // @param $debug : The parameter passed to query() functions.
 //                 Can be boolean or -1 (default).
 // @param $query : The SQL query to debug.
 // @param $result : The resulting table of the query, if available.
 function debug($debug,$query,$result=NULL){
  if($debug===-1 && $this->defaultDebug===false)return;
  if($debug===false)return;
  $reason=($debug===-1?"Default Debug":"Debug");
  $this->debugQuery($query,$reason);
  if($result==NULL){
   echo "<p style=\"margin: 2px;\">Number of affected rows: ".mysql_affected_rows()."</p></div>";
  }else{
   $this->debugResult($result);
  }
 }

 // ---[ Internal function to output a query for debug purpose ]---------------
 // @param $query : The SQL query to debug.
 // @param $reason : The reason why this function is called:
 //                  "Default Debug", "Debug" or "Error".
 function debugQuery($query,$reason="Debug"){
  $color=($reason=="Error"?"red":"orange");
  echo "<div style=\"border: solid $color 1px; margin: 2px;\">".
       "<p style=\"margin: 0 0 2px 0; padding: 0; background-color: #DDF;\">".
       "<strong style=\"padding: 0 3px; background-color: $color; color: white;\">$reason:</strong> ".
       "<span style=\"font-family: monospace;\">".htmlentities($query)."</span></p>";
  }

 // ---[ Internal function to output a table representing a query ]------------
 // @param $result : The resulting table of the query.
 function debugResult($result){
  echo "<table border=\"1\" style=\"margin: 2px;\"><thead style=\"font-size: 80%\">";
  $numFields = mysql_num_fields($result);
  // BEGIN HEADER
  $tables=array();
  $nbTables=-1;
  $lastTable="";
  $fields=array();
  $nbFields=-1;
  while($column=mysql_fetch_field($result)){
   if($column->table!=$lastTable){
    $nbTables++;
    $tables[$nbTables] = array("name" => $column->table, "count" => 1);
   }else{
    $tables[$nbTables]["count"]++;
   }
   $lastTable = $column->table;
   $nbFields++;
   $fields[$nbFields] = $column->name;
  }
  for($i=0;$i<=$nbTables;$i++){
   echo "<th colspan=".$tables[$i]["count"].">".$tables[$i]["name"]."</th>";
  }
  echo "</thead>";
  echo "<thead style=\"font-size: 80%\">";
  for($i=0;$i<=$nbFields;$i++){
   echo "<th>".$fields[$i]."</th>";
  }
  echo "</thead>";
  // END HEADER
  while($row=mysql_fetch_array($result)){
   echo "<tr>";
   for($i=0;$i<$numFields;$i++){
    echo "<td>".htmlentities($row[$i])."</td>";
   }
   echo "</tr>";
  }
  echo "</table></div>\n";
  $this->resetFetch($result);
 }

 // ---[ Get how many time the script took ]-----------------------------------
 // @return : The script execution time in seconds
 function getExecTime(){
  return round(($this->getMicroTime()-$this->mtStart)*1000)/1000;
 }

 // ---[ Get the number of queries executed ]----------------------------------
 // @return : The number of queries executed on the database server
 function getQueriesCount(){
  return $this->nbQueries;
 }

 // ---[ Go back to the first element of the result line ]---------------------
 // @param $result : The resssource returned by a query() function.
 function resetFetch($result){
  if(mysql_num_rows($result)>0)mysql_data_seek($result,0);
 }

 // ---[ Get the id of the very last inserted row ]----------------------------
 // @return : The id of the very last inserted row (in any table).
 function lastInsertedId(){
  return mysql_insert_id($this->connector);
 }

 // ---[ Close the connexion with the database server ]------------------------
 // It's usually unneeded since PHP do it automatically at script end.
 function close(){
  mysql_close($this->connector);
 }

 // ---[ Internal method to get the current time ]-----------------------------
 // @return : The current time in seconds with microseconds (in float format).
 function getMicroTime(){
  list($msec,$sec)=explode(' ',microtime());
  return floor($sec/1000)+$msec;
 }

}

/******************************************************************************\
 * A PHP class to access MySQL database with convenient methods in an object  *
 * oriented way, and with a powerful debug system. - License: LGPL            *
 * Author: Sebastien Laout (slaout@linux62.org) - http://slaout.linux62.org   *
\******************************************************************************/

?>
