<?php
/* -------------------------------------------------------------------------- *\
|* -[ Projects - Projects Resource View ]------------------------------------ *|
\* -------------------------------------------------------------------------- */
include("template.inc.php");
function content(){
 include("../organics/api.inc.php");
?>
<table class="table table-striped table-hover table-condensed">
 <thead>
  <tr>
   <th width='16'>&nbsp;</th>
   <th class='text-center'>!</th>
   <th class='nowarp text-center'>%</th>
   <th width='100%'>Progetto</th>
   <th class='nowarp text-center'>Impegno</th>
   <th class='nowarp text-center'>Incarichi</th>
   <th class='nowarp text-center'>Completati</th>
  </tr>
 </thead>
 <tbody>
<?php
 // resources
 //$projects_resources=$GLOBALS['db']->query("SELECT * FROM projects_projects_join_accounts_accounts WHERE idAccount='".$_SESSION['account']->id."' ORDER BY rate DESC");
 //while($project_resource=$GLOBALS['db']->fetchNextObject($projects_resources)){
  //$project=$GLOBALS['db']->queryUniqueObject("SELECT * FROM projects_projects WHERE id='".$project_resource->idProject."'");
  //if($project->id>0){

 $projects=$GLOBALS['db']->query("SELECT * FROM projects_projects ORDER BY priority ASC,progress DESC,project ASC");
 while($project=$GLOBALS['db']->fetchNextObject($projects)){
  $activities=$GLOBALS['db']->countOf("projects_activities","idProject='".$project->id."' AND idAssigned='".$_SESSION['account']->id."'");
  $activities_completed=$GLOBALS['db']->countOf("projects_activities","idProject='".$project->id."' AND idAssigned='".$_SESSION['account']->id."' AND status='2'");
  if($activities>$activities_completed){
   // title
   $title=api_organics_areaName($GLOBALS['db']->queryUniqueValue("SELECT idArea FROM organics_departments WHERE id='".$project->idDepartment."'"));
   $title.=" / ".api_organics_departmentName($project->idDepartment)." - ".stripslashes($project->project);
   // activities
   $activity="</ul>";
   $projects_activities=$GLOBALS['db']->query("SELECT * FROM projects_activities WHERE idProject='".$project->id."' AND idAssigned='".$_SESSION['account']->id."' AND status<'2' ORDER BY expDate ASC");
   while($projects_activity=$GLOBALS['db']->fetchNextObject($projects_activities)){
    $activity.="<li>".stripslashes($projects_activity->activity)."</li>";
   }
   $activity.="</ul>";
   // resource rate
   $resources_rate=$GLOBALS['db']->queryUniqueValue("SELECT rate FROM projects_projects_join_accounts_accounts WHERE idProject='".$project->id."' AND idAccount='".$_SESSION['account']->id."'");
   if($resources_rate>0){$resources_rate.="%";}else{$resources_rate="";}
   // show project
   echo "<tr>\n";
   echo "<td class='nowarp'><a href='../projects/projects_view.php?id=".$project->id."' target='_blank'><i class='icon-search'></i></a></td>\n";
   echo "<td class='nowarp text-center'>".$project->priority."</td>\n";
   echo "<td class='nowarp text-center'>".$project->progress."%</td>\n";
   echo "<td>".$title.$activity."</td>\n";
   echo "<td class='nowarp text-center'>".$resources_rate."</td>\n";
   echo "<td class='nowarp text-center'>".$activities."</td>\n";
   echo "<td class='nowarp text-center'>".$activities_completed."</td>\n";
   echo "</tr>\n";
  }
 }
?>
 </tbody>
</table>
<?php } ?>