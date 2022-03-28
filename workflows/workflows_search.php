<?php
/* -------------------------------------------------------------------------- *\
|* -[ Workflows - Search ]--------------------------------------------------- *|
\* -------------------------------------------------------------------------- */
$checkPermission="workflows_add";
require_once("template.inc.php");
function content(){
 // acquire variables
 $g_search=$_GET['q'];
 // get category object
 $selected_category=api_workflows_category($_GET['idCategory']);
 // show main categories
 if(!$selected_category->id){
  $categories=$GLOBALS['db']->query("SELECT * FROM workflows_categories WHERE idCategory='0' ORDER BY name ASC");
  while($category=$GLOBALS['db']->fetchNextObject($categories)){
   echo "<p><a href='workflows_search.php?idCategory=".$category->id."'>".api_icon("icon-search")."</a>\n";
   echo "<strong>".stripslashes($category->name)."</strong>\n";
   if(strlen($category->description)>0){echo " &rarr; <small class='muted'><i>".stripslashes($category->description)."</i></small>\n";}
   echo "</p>\n";
  }
  return TRUE;
 }
 // show categories
 if($selected_category->id>0){
  echo "<h4><a href='workflows_search.php?idCategory=".$selected_category->idCategory."'>&laquo;</a> ".stripslashes($selected_category->name);
  if(strlen($selected_category->description)>0){echo " &rarr; <small class='muted'><i>".stripslashes($selected_category->description)."</i></small>\n";}
  echo "</h4>\n<hr>\n";
  $query_where="id='".$selected_category->id."' OR idCategory='".$selected_category->id."'";
 }
 // show categories and subcategories
 $categories=$GLOBALS['db']->query("SELECT * FROM workflows_categories WHERE ".$query_where." ORDER BY idCategory ASC,name ASC");
 while($category=$GLOBALS['db']->fetchNextObject($categories)){
  if($category->id<>$selected_category->id){
   echo "<p><a href='workflows_search.php?idCategory=".$category->id."'>".api_icon("icon-search")."</a> ";
   echo "<strong>".stripslashes($category->name)."</strong>";
   if(strlen($category->description)>0){echo " &rarr; <small class='muted'><i>".stripslashes($category->description)."</i></small>\n";}
   echo "</p>\n";
   // query category and subcategories
   $query_where="( idCategory='".$category->id."'";
   $subcategories=$GLOBALS['db']->query("SELECT * FROM workflows_categories WHERE idCategory='".$category->id."' ORDER BY name ASC");
   while($subcategory=$GLOBALS['db']->fetchNextObject($subcategories)){
    $query_where.=" OR idCategory='".$subcategory->id."'";
   }
   $query_where.=" )";
  }else{
   // query only category
   $query_where="( idCategory='".$category->id."')";
   $subcategories_count=$GLOBALS['db']->countOf("workflows_categories","idCategory='".$category->id."'");
  }
  // query search
  if(strlen($g_search)>0){
   $query_where.=" AND (";
   $query_where.=" workflows_flows.subject LIKE '%".$g_search."%'";
   $query_where.=" OR workflows_flows.description LIKE '%".$g_search."%'";
   $query_where.=" )";
  }elseif($selected_category->idCategory==0){
   $query_where="0";
  }elseif($subcategories_count && $category->id<>$selected_category->id){
   // query only pinned
   $query_where.=" AND pinned='1'";
  }
  // open list
  echo "<ul>\n";
  // query
  //echo $query_where;
  $workflows=$GLOBALS['db']->query("SELECT workflows_flows.* FROM workflows_flows LEFT JOIN workflows_actions ON workflows_flows.id=workflows_actions.idFlow WHERE workflows_actions.id>'0' AND ".$query_where." GROUP BY workflows_flows.id ORDER BY subject ASC");
  while($workflow=$GLOBALS['db']->fetchNextObject($workflows)){
   if($workflow->pinned==1){$bold="<strong>";$unbold="</strong>";}else{$bold=NULL;$unbold=NULL;}
   echo "<li>".$bold."<a href='workflows_add.php?idFlow=".$workflow->id."&idCategory=".$category->id."'>".stripslashes($workflow->subject)."</a>".$unbold."\n";
   if(strlen($workflow->description)>0){echo "&rarr; <small><i>".stripslashes($workflow->description)."</i></small>";}
   echo "</li>\n";
  }
  if($selected_category->id==$category->id){
   //if(!$subcategories_count){
    echo "</ul>\n<ul>\n";
    echo "<li><a href='workflows_add.php?idCategory=".$category->id."'>".api_text("search-li-other")."</a></li>\n";
   //}
  }else{
   echo "<li><a href='workflows_search.php?idCategory=".$category->id."'>".api_text("search-li-show",stripslashes($category->name))."</a></li>\n";
  }
  // close list
  echo "</ul>\n";
 }
}
?>