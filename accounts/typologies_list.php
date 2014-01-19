<?php
/* ------------------------------------------------------------------------- *\
|* -[ Accounts - Typoogies List ]------------------------------------------- *|
\* ------------------------------------------------------------------------- */
$checkPermission="typologies_list";
include("template.inc.php");
function content(){
?>

<table class="table table-striped table-hover">
 <thead>
  <tr>
   <th width="22">&nbsp;</th>
   <th width="10">#</th>
   <th>Tipologia</th>
   <th>Descrizione</th>
   <th>Accounts</th>
  </tr>
 </thead>
 <tbody>

<?php

$typologies=$GLOBALS['db']->query("SELECT * FROM accounts_typologies ORDER BY id ASC");
while($typology=$GLOBALS['db']->fetchNextObject($typologies)){
 echo "<tr>\n";
  echo "<td><a href=\"typologies_edit.php?id=".$typology->id."\"><img src=\"images/typology.png\" style=\"margin-top:-6px\"></a></td>\n";
  echo "<td>".$typology->id."</td>\n";
  echo "<td>".$typology->name."</td>\n";
  echo "<td>".$typology->description."</td>\n";
  echo "<td>".$GLOBALS['db']->countOf("accounts_accounts","typology='".$typology->id."'")."</td>\n";
 echo "</tr>\n";
}

?>

 </tbody>
</table>

<?php
}
?>