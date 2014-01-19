<?php
/* ------------------------------------------------------------------------- *\
|* -[ Accounts - Companies List ]------------------------------------------- *|
\* ------------------------------------------------------------------------- */
$checkPermission="companies_list";
include("template.inc.php");
function content(){
?>

<table class="table table-striped table-hover">
 <thead>
  <tr>
   <th width="22">&nbsp;</th>
   <th width="10">#</th>
   <th>Societ&agrave;</th>
   <th>Ragione sociale</th>
   <th>Accounts</th>
  </tr>
 </thead>
 <tbody>

<?php

$companies=$GLOBALS['db']->query("SELECT * FROM accounts_companies ORDER BY company ASC,division ASC");
while($company=$GLOBALS['db']->fetchNextObject($companies)){
 echo "<tr>\n";
  echo "<td><a href=\"companies_edit.php?id=".$company->id."\"><img src=\"images/company.png\" style=\"margin-top:-6px\"></a></td>\n";
  echo "<td>".$company->id."</td>\n";
  echo "<td>".$company->company." - ".$company->division."</td>\n";
  echo "<td>".$company->name."</td>\n";
  echo "<td>".$GLOBALS['db']->countOf("accounts_accounts","idCompany='".$company->id."'")."</td>\n";
 echo "</tr>\n";
}

?>

 </tbody>
</table>

<?php
}
?>