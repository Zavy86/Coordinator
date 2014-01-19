<?php
/* -------------------------------------------------------------------------- *\
|* -[ Dashboard - Address List ]--------------------------------------------- *|
\* -------------------------------------------------------------------------- */
include("template.inc.php");
function content(){
 include("../organics/api.inc.php");
 // acquire variables
 $g_page=$_GET['p'];
 if(!$g_page){$g_page=1;}
 $g_limit=$_GET['l'];
 if(!isset($g_limit)){$g_limit=19;}
 $g_letter=$_GET['c'];
 if(!isset($g_letter)){$g_letter=NULL;}
 $g_search=$_GET['s'];
?>
<center>
 <div class="pagination pagination-small">
  <ul>
  <?php
   // all people
   if($g_letter==NULL){echo "<li class='active'><span>Tutti</span></li>";}
    else{echo "<li><a href='addressbook_list.php'>Tutti</a></li>";}
   // alphabetical search
   $letters=array("A","B","C","D","E","F","G","H","I","J","K","L","M","N","O","P","Q","R","S","T","U","V","W","X","Y","Z");
   foreach($letters as $letter){
    if(strtoupper($g_letter)==$letter){echo "<li class='active'><span>".$letter."</span></li>";}
     else{echo "<li><a href='addressbook_list.php?c=".$letter."'>".$letter."</a></li>\n";}
   }
  ?>
   <li><a href="addressbook_update.php">Aggiorna</a></li>
  </ul>
 </div>
</center>
<table class="table table-striped table-hover table-condensed">
 <thead>
  <tr>
   <th width="16">&nbsp;</th>
   <th class='nowarp'>Cognome</th>
   <th class='nowarp'>Nome</th>
   <th class='nowarp'>Ufficio</th>
   <th class='nowarp'>Telefono</th>
   <th class='nowarp'>Cellulare</th>
   <th width='100%'>Mail</th>
  </tr>
 </thead>
 <tbody>
<?php
 // generate query
 $query_where="1";
 if($g_letter<>NULL){$query_where.=" AND firstname LIKE '".$g_letter."%'";}
 if($g_search<>NULL){$query_where.=" AND (firstname LIKE '".$g_search."%' OR lastname LIKE '".$g_search."%' OR office LIKE '".$g_search."%')";}
 // pagination
 if($g_limit>0){
  $recordsLimit=$g_limit;
  $recordsCount=$GLOBALS['db']->countOf("organics_addressbook",$query_where);
  $query_start=($g_page-1)*$recordsLimit;
  $query_limit=" LIMIT ".$query_start.",".$recordsLimit;
 }
 // owner
 $owner=api_getOption('owner');
 $owner_url=api_getOption('owner_url');
 // query
 $people=$GLOBALS['db']->query("SELECT * FROM organics_addressbook WHERE ".$query_where." ORDER BY firstname ASC,lastname ASC".$query_limit);
 while($person=$GLOBALS['db']->fetchNextObject($people)){
  echo "<tr>\n";
  echo "<td class='nowarp'><a href='#modal".$person->id."' data-toggle='modal'><i class='icon-search'></i></a></td>\n";
  echo "<td class='nowarp'>".stripslashes($person->firstname)."</td>\n";
  echo "<td class='nowarp'>".stripslashes($person->lastname)."</td>\n";
  echo "<td class='nowarp'><a href='../maps/map.inc.php?loc=".stripslashes($person->office)."' rel='shadowbox;width=1000;height=625;' data-toggle='popover' data-placement='top' data-content=\"".stripslashes($person->description)."\" title='".stripslashes($person->office)."' style='color:#333333;'>".stripslashes($person->office)."</a></td>\n";
  echo "<td class='nowarp'>".api_organics_phoneFormat($person->phone)."</td>\n";
  echo "<td class='nowarp'>";
  echo api_organics_phoneFormat($person->mobile);
  if(strlen($person->mobile_short)>0){echo " (".$person->mobile_short.")";}
  echo "</td>\n";
  echo "<td class='nowarp'>".stripslashes($person->mail)."</td>\n";
  echo "</tr>\n";
  // vCard
  $vcard="BEGIN:VCARD\r\nVERSION:3.0\r\n".
   "N:".$person->lastname.";".$person->firstname."\r\n".
   "FN:".$person->firstname." ".$person->lastname."\r\n".
   "ORG:".api_getOption('owner')."\r\n".
   "TITLE:".$person->office."\r\n".
   "TEL;TYPE=work,voice,pref:".api_organics_phoneFormat($person->phone)."\r\n".
   "TEL;TYPE=cell,voice:".api_organics_phoneFormat($person->mobile)."\r\n".
   "URL;TYPE=work:".api_getOption('owner_url')."\r\n".
   "EMAIL;TYPE=work,internet,pref:".$person->mail."\r\n".
   "REV:".date('Ymd')."T195243Z\r\n".
   "END:VCARD";
  // modal popup
  echo "<div id='modal".$person->id."' class='modal hide fade' role='dialog' aria-hidden='true'>\n";
  echo "<div class='modal-header'>\n";
  echo "<button type='button' class='close' data-dismiss='modal' aria-hidden='true'>&times;</button>\n";
  echo "<h4>".stripslashes($person->firstname)." ".stripslashes($person->lastname)."</h4>";
  echo "</div>\n";
  echo "<div class='modal-body'>\n";
  echo "<div class='row-fluid'>\n";
  echo "<div class='span6'>\n";
  echo "<dl>\n";
  echo "<dt>Telefono</dt><dd>".api_organics_phoneFormat($person->phone)."&nbsp;</dd>\n";
  echo "<dt>Cellulare</dt><dd>".api_organics_phoneFormat($person->mobile)."&nbsp;</dd>\n";
  echo "</dl>\n";
  echo "</div>\n";
  echo "<div class='span6'>\n";
  echo "<dl>\n";
  echo "<dt>Ufficio</dt><dd>".stripslashes($person->office)." - <a href='../maps/map.inc.php?loc=".stripslashes($person->office)."' rel='shadowbox;width=1000;height=625;'>Mappa</a></dd>\n";
  echo "<dt>Mail</dt><dd><a href='mailto:".stripslashes($person->mail)."'>".stripslashes($person->mail)."</a>&nbsp;</dd>\n";
  echo "</dl>\n";
  echo "</div>\n";
  echo "</div>\n";
  // include the cQRCode class and generate QR Code
  /*require_once('../core/qrcode.class.php');
  $qr=new cQRCode($vcard,ECL_M);
  $qr->getQRImg("PNG");*/
  echo "<img src='http://chart.apis.google.com/chart?chs=250x250&cht=qr&chld=H&chl=".urlencode($vcard)."'>\n";
  echo "</div>\n</div>\n";
 }
?>
 </tbody>
</table>
<?php
 // show the pagination div
 api_pagination($recordsCount,$recordsLimit,$g_page,"addressbook_list.php?c=".$g_letter,"pagination pagination-small pagination-right");
?> 
<script type="text/javascript">
 $(document).ready(function(){
  $("[data-toggle=popover]").popover({trigger:"hover"});
  /*$("[data-toggle=popover]").popover({
   trigger:"hover",
   html:true,
   content:function(){
    return "<img src='../core/images/files/file.png'>";
   }
  });*/
 });
</script>
<?php } ?>