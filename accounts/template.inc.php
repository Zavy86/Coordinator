<?php
/* ------------------------------------------------------------------------- *\
|* -[ Accounts - Template ]------------------------------------------------- *|
\* ------------------------------------------------------------------------- */
include("../core/api.inc.php");
$html->header("Accounts",NULL);
?>

<div class="row-fluid">

 <!-- Navigation -->
 <ul class="nav nav-tabs">
  <li<?php if(api_baseName()=="index.php"){echo " class='active'";}?>><a href="index.php">Profilo personale</a></li>
  
  <?php 
   echo "<li";if(api_baseName()=="accounts_list.php"){echo " class='active'";}
   if(!api_checkPermission("accounts","accounts_list")){echo " class='disabled'><a href='#'";}
   else{echo "><a href='accounts_list.php'";}
   echo ">Accounts</a></li>";
  ?>
  
  <?php 
   echo "<li";if(api_baseName()=="groups_list.php"){echo " class='active'";}
   if(!api_checkPermission("accounts","groups_list")){echo " class='disabled'><a href='#'";}
   else{echo "><a href='groups_list.php'";}
   echo ">Gruppi</a></li>";
  ?>
    
  <?php 
   echo "<li";if(api_baseName()=="companies_list.php"){echo " class='active'";}
   if(!api_checkPermission("accounts","companies_list")){echo " class='disabled'><a href='#'";}
   else{echo "><a href='companies_list.php'";}
   echo ">Societ&agrave;</a></li>";
  ?>
  
  <?php 
   /*echo "<li";if(api_baseName()=="typologies_list.php"){echo " class='active'";}
   if(!api_checkPermission("accounts","typologies_list")){echo " class='disabled'><a href='#'";}
   else{echo "><a href='typologies_list.php'";}
   echo ">Tipologie</a></li>";*/
  ?>
  
  <li class="dropdown<?php if(api_baseName()=="accounts_edit.php"||api_baseName()=="groups_edit.php"||api_baseName()=="companies_edit.php"||api_baseName()=="typologies_edit.php"){echo " active";}?>">
   <a class="dropdown-toggle" data-toggle="dropdown" href="#">
    <?php if($_GET['id']>0){echo "Modifica";}else{echo "Aggiungi";} ?>
    <b class="caret"></b>
   </a>
   <ul class="dropdown-menu">
    <li<?php if(!api_checkPermission("accounts","accounts_add")){echo " class='disabled'><a href='#'";}else{echo "><a href='accounts_edit.php'";}?>>Account</a></li>
    <li<?php if(!api_checkPermission("accounts","groups_add")){echo " class='disabled'><a href='#'";}else{echo "><a href='groups_edit.php'";}?>>Gruppo</a></li>
    <li<?php if(!api_checkPermission("accounts","companies_add")){echo " class='disabled'><a href='#'";}else{echo "><a href='companies_edit.php'";}?>>Societ&agrave;</a></li>
    <?php /*<li<?php if(!api_checkPermission("accounts","typologies_add")){echo " class='disabled'><a href='#'";}else{echo "><a href='typologies_edit.php'";}?>>Tipologia</a></li>*/ ?>
   </ul>
  </li>
  
 </ul>

<?php //if(api_checkPermission("accounts",$checkPermission)){content();} ?>
<?php if($checkPermission==NULL){content();}else{if(api_checkPermission("accounts",$checkPermission,TRUE)){content();}} ?>

</div><!-- /row -->

<?php $html->footer(); ?>