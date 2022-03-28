<?php
/* -------------------------------------------------------------------------- *\
|* -[ Workflows - Flow List ]------------------------------------------------ *|
\* -------------------------------------------------------------------------- */
$checkPermission="workflows_admin";
require_once("template.inc.php");
function content(){
 // acquire variables
 $g_search=$_GET['q'];
 // show filters
 echo $GLOBALS['navigation']->filtersText();
 // conditions
 $query_where=$GLOBALS['navigation']->filtersQuery("1");
 // sorting
 $query_order=api_queryOrder("idCategory ASC,subject ASC");
 // pagination
 $pagination=new str_pagination("workflows_flows",$query_where,$GLOBALS['navigation']->filtersGet());
 $query_limit=$pagination->queryLimit();
 // search
 if(strlen($g_search)>0){
  $query_where.=" AND (";
  $query_where.=" subject LIKE '%".$g_search."%'";
  $query_where.=" OR description LIKE '%".$g_search."%'";
  $query_where.=" )";
 }
 // build table
 $table=new str_table(api_text("flows_list-tr-unvalued"));
 // build table headers
 $table->addHeader("&nbsp;",NULL,16);
 $table->addHeader(api_text("flows_list-th-category"),"nowarp");
 $table->addHeader("&nbsp;",NULL,16);
 $table->addHeader("!","text-center",16);
 $table->addHeader(api_text("flows_list-th-flow"),NULL,"40%");
 $table->addHeader(api_text("flows_list-th-description"),NULL,"60%");
 $table->addHeader(api_text("flows_list-th-counter"),"nowarp text-center");
 // build table rows
 $flows=$GLOBALS['db']->query("SELECT * FROM workflows_flows WHERE ".$query_where.$query_order.$query_limit);
 while($flow=$GLOBALS['db']->fetchNextObject($flows)){
  $table->addRow();
  // pinned
  if($flow->pinned){$bold="<b>";$unbold="</b>";}
  else{$bold=NULL;$unbold=NULL;}
  // count workflows opened
  $workflows_count=$GLOBALS['db']->countOf("workflows_workflows","idFlow='".$flow->id."'");
  // build table fields
  $table->addField("<a href='workflows_flows_view.php?idFlow=".$flow->id."&idCategory=".$flow->idCategory."'>".api_icon("icon-search")."</a>");
  $table->addField(api_workflows_categoryName($flow->idCategory,TRUE,TRUE,TRUE),"nowarp");
  $table->addField(api_workflows_typology($flow->typology,TRUE));
  $table->addField($flow->priority,"nowarp text-center");
  $table->addField($bold.stripslashes($flow->subject).$unbold);
  $table->addField("<small><i>".stripslashes($flow->description)."</i></small>");
  $table->addField($workflows_count,"nowarp text-center");
 }
 // show table
 $table->render();
 // show pagination
 $pagination->render();
}
?>