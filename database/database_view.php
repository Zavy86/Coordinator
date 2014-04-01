<?php
/* -------------------------------------------------------------------------- *\
|* -[ Database - View ]------------------------------------------------------ *|
\* -------------------------------------------------------------------------- */
$checkPermission="database_view";
include("template.inc.php");
function content(){
 // definitions
 $_SESSION['nodes']=array();
 // acquire variables
 $g_module=$_GET['module'];
 //if(!$g_module){$g_module="accounts";}
 $g_table=$_GET['table'];
 $g_field=$_GET['field'];
 // build database object
 include("../config.inc.php");
 $db_schema=new DB($db_host,$db_user,$db_pass,"information_schema");

 // split page
 $GLOBALS['html']->split_open();
 $GLOBALS['html']->split_span(4);

 // show tables
 echo "<h5>".strtoupper(api_text("view-modules"))."</h5>\n";
 $module=NULL;
 $tables=$db_schema->query("SELECT TABLE_NAME FROM TABLES WHERE TABLE_SCHEMA='".$db_name."'");
 while($table=$db_schema->fetchNextObject($tables)){
  $table_module=substr($table->TABLE_NAME,0,strpos($table->TABLE_NAME,"_"));
  if($table_module<>NULL && $table_module<>$module){
   $module=$table_module;
   echo "<strong><a href='database_view.php?module=".$table_module."'>".ucfirst($module)."</a></strong><br>\n";
  }
  if($table_module==$g_module){
   echo "&nbsp; <a href='database_view.php?module=".$table_module."&table=".$table->TABLE_NAME."'>".$table->TABLE_NAME."</a><br>\n";
  }
 }

 // split page
 $GLOBALS['html']->split_span(8);

 // show all modules
 if($g_module==NULL){
  echo "<h5>COORDINATOR</h5>\n";
  $core_module=new stdClass();
  $core_module->name="core";
  $core_module->label="Coordinator";
  $core_module->links=array();
  $_SESSION['nodes'][]=$core_module;
  // build modules array
  $dir="../";
  if(is_dir($dir)){
   if($dh=opendir($dir)){
    while(($file=readdir($dh))!==false){
     if(is_dir($dir.$file)&&$file<>"."&&$file<>".."){
      if(file_exists($dir.$file."/module.inc.php")){
       $module_core=FALSE;
       include($dir.$file."/module.inc.php");
       if(!$module_core){
        $this_module=new stdClass();
        $this_module->name=$file;
        $this_module->label=ucfirst($file);
        $this_module->links="core";
        $_SESSION['nodes'][]=$this_module;
       }
      }
     }
    }
    closedir($dh);
   }
  }
  echo "<img src='flowchart.inc.php'>\n";
 }

 // show module flow chart
 if($g_module<>NULL && $g_table==NULL){
  echo "<h5>".strtoupper($g_module)."</h5>\n";
  $tables=$db_schema->query("SELECT TABLE_NAME FROM TABLES WHERE TABLE_SCHEMA='".$db_name."' AND TABLE_NAME LIKE '".$g_module."%'");
  while($table=$db_schema->fetchNextObject($tables)){
   $current_table=new stdClass();
   $current_table->name=$table->TABLE_NAME;
   $current_table->label=preg_replace("/".$g_module."_/","",$table->TABLE_NAME,1);
   $current_table->links=array();
   // link out
   $results=$db_schema->query("SELECT * FROM KEY_COLUMN_USAGE WHERE CONSTRAINT_SCHEMA='".$db_name."' AND TABLE_NAME='".$table->TABLE_NAME."' AND REFERENCED_TABLE_NAME<>'NULL'");
   while($result=$db_schema->fetchNextObject($results)){
    $current_table->links[$result->REFERENCED_TABLE_NAME]=$result->REFERENCED_TABLE_NAME;
   }
   /*// link in
   $results=$db_schema->query("SELECT * FROM KEY_COLUMN_USAGE WHERE CONSTRAINT_SCHEMA='".$db_name."' AND REFERENCED_TABLE_NAME='".$table->TABLE_NAME."'");
   while($result=$db_schema->fetchNextObject($results)){
    $module_array[$table->TABLE_NAME][$result->TABLE_NAME]="<";
   }*/
   $_SESSION['nodes'][$current_table->name]=$current_table;
  }
  echo "<img src='flowchart.inc.php'>\n";
 }

 // show selected table
 if($g_table<>NULL){
  echo "<h5>".strtoupper($g_module)."</h5>\n";
  $table=$db_schema->queryUniqueObject("SELECT * FROM TABLES WHERE TABLE_SCHEMA='".$db_name."' AND TABLE_NAME='".$g_table."'");
  echo "<h5>".$table->TABLE_NAME."</h5>\n";
  $fields_tab=new str_table();
  $fields_tab->addHeader("Campo","nowarp");
  $fields_tab->addHeader("Tipo","nowarp");
  $fields_tab->addHeader("Default","nowarp");
  $fields_tab->addHeader("Extra","nowarp");
  $fields_tab->addHeader("Commento",NULL,"100%");
  $fields=$db_schema->query("SELECT * FROM COLUMNS WHERE TABLE_SCHEMA='".$db_name."' AND TABLE_NAME='".$table->TABLE_NAME."'");
  while($field=$db_schema->fetchNextObject($fields)){
   if($field->COLUMN_NAME==$g_field){$fields_tab->addRow("info");}else{$fields_tab->addRow();}
   $extra=NULL;
   if($field->COLUMN_KEY=="PRI"){$extra.=" PK";}
   if($field->COLUMN_KEY=="UNI"){$extra.=" UQ";}
   if($field->EXTRA=="auto_increment"){$extra.=" AI";}
   if($field->IS_NULLABLE=="YES"){$field->COLUMN_TYPE.=" null";}
   $fields_tab->addField($field->COLUMN_NAME,"nowarp");
   $fields_tab->addField($field->COLUMN_TYPE,"nowarp");
   $fields_tab->addField($field->COLUMN_DEFAULT,"nowarp");
   $fields_tab->addField($extra,"nowarp");
   $fields_tab->addField($field->COLUMN_COMMENT);
  }
  $fields_tab->render();

  echo "<hr>\n";
  $results=$db_schema->query("SELECT * FROM KEY_COLUMN_USAGE WHERE CONSTRAINT_SCHEMA='".$db_name."' AND TABLE_NAME='".$g_table."' AND REFERENCED_TABLE_NAME<>'NULL'"); // AND REFERENCED_COLUMN_NAME='".$g_field."'
  while($result=$db_schema->fetchNextObject($results)){
   echo $result->COLUMN_NAME." &rarr; ";
   echo "<a href='database_view.php?module=".$g_module."&table=".$result->REFERENCED_TABLE_NAME."&field=".$result->REFERENCED_COLUMN_NAME."'>";
   echo $result->REFERENCED_TABLE_NAME.".".$result->REFERENCED_COLUMN_NAME;
   echo "</a><br>\n";
  }
  $results=$db_schema->query("SELECT * FROM KEY_COLUMN_USAGE WHERE CONSTRAINT_SCHEMA='".$db_name."' AND REFERENCED_TABLE_NAME='".$g_table."'"); // AND REFERENCED_COLUMN_NAME='".$g_field."'
  while($result=$db_schema->fetchNextObject($results)){
   echo $result->REFERENCED_COLUMN_NAME." &larr; ";
   echo "<a href='database_view.php?module=".$g_module."&table=".$result->TABLE_NAME."&field=".$result->COLUMN_NAME."'>";
   echo $result->TABLE_NAME.".".$result->COLUMN_NAME;
   echo "</a><br>\n";
  }
  echo "<hr>\n";

 }

 // close split
 $GLOBALS['html']->split_close();

 // close database connection
 $db_schema->close();
}
?>


