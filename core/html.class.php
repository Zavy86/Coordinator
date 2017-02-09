<?php

/* -[ HTML Class ]----------------------------------------------------------- */

class HTML{

/**
* HTML Header
* @param string $title the page title
* @param string $nav the active tab in the navigation bar
* @param boolean $navbar TRUE visible, FALSE invisible
*/
public function header($title="",$nav="dashboard",$navbar=TRUE){
 if($title<>""){$title=$title." - ".api_getOption('title');}else{$title=api_getOption('title');}
?>
<!DOCTYPE html>
<html lang="it">
<head>
 <meta charset="utf-8">
 <meta name="viewport" content="width=device-width, initial-scale=1.0">
 <meta name="author" content="Manuel Zavatta">
 <meta name="copyright" content="2009-<?php echo date('Y'); ?> © Coordinator [www.coordinator.it]">
 <meta name="owner" content="<?php echo api_getOption('owner'); ?>">
 <meta name="description" content="Coordinator is an Open Source modular web application">
 <title><?php echo $title; ?></title>

 <!-- Stylesheet -->
 <link href="<?php echo $GLOBALS['dir']."core/pace-loader/pace.flash.css";?>" rel="stylesheet">
 <link href="<?php echo $GLOBALS['dir']."core/codemirror/lib/codemirror.css";?>" rel="stylesheet">
 <link href="<?php echo $GLOBALS['dir']."core/font-awesome/css/font-awesome.min.css";?>" rel="stylesheet">
 <link href="<?php echo $GLOBALS['dir']."core/bootstrap/css/bootstrap.min.css";?>" rel="stylesheet">
 <link href="<?php echo $GLOBALS['dir']."core/bootstrap/css/bootstrap-custom.css";?>" rel="stylesheet">
 <link href="<?php echo $GLOBALS['dir']."core/bootstrap/css/bootstrap-responsive.css";?>" rel="stylesheet">
 <link href="<?php echo $GLOBALS['dir']."core/bootstrap/css/bootstrap-responsive-tables.css";?>" rel="stylesheet">
 <link href="<?php echo $GLOBALS['dir']."core/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css";?>" rel="stylesheet">
 <link href="<?php echo $GLOBALS['dir']."core/bootstrap-markdown/css/bootstrap-markdown.min.css";?>" rel="stylesheet">
 <link href="<?php echo $GLOBALS['dir']."core/bootstrap-select2/select2.css";?>" rel="stylesheet">
 <link href="<?php echo $GLOBALS['dir']."core/bootstrap-select2/select2-bootstrap.css";?>" rel="stylesheet">
 <link href="<?php echo $GLOBALS['dir']."core/bootstrap-slider/css/bootstrap-slider.min.css";?>" rel="stylesheet">
 <link href="<?php echo $GLOBALS['dir']."core/bootstrap-wysihtml5/css/bootstrap-wysihtml5.css";?>" rel="stylesheet">
 <link href="<?php echo $GLOBALS['dir']."core/bootstrap-tagsinput/css/bootstrap-tagsinput.css";?>" rel="stylesheet">
 <link href="<?php echo $GLOBALS['dir']."core/bootstrap-colorpicker/css/bootstrap-colorpicker.css";?>" rel="stylesheet">
 <link href="<?php echo $GLOBALS['dir']."core/bootstrap-fontawesomepicker/css/fontawesome-iconpicker.min.css";?>" rel="stylesheet">
 <link href="<?php echo $GLOBALS['dir']."core/shadowbox/shadowbox.css";?>" rel="stylesheet">
 <link href="<?php echo $GLOBALS['dir']."core/template.css?v=0.0.86";?>" rel="stylesheet">

 <!-- Javascript -->
 <script src="<?php echo $GLOBALS['dir']."core/pace-loader/pace.min.js";?>" type="text/javascript"></script>
 <script src="<?php echo $GLOBALS['dir']."core/codemirror/lib/codemirror.js";?>" type="text/javascript"></script>
 <script src="<?php echo $GLOBALS['dir']."core/jquery/jquery-1.11.1.min.js";?>" type="text/javascript"></script>
 <script src="<?php echo $GLOBALS['dir']."core/jquery/jquery.validate-1.13.0.min.js";?>" type="text/javascript"></script>
 <script src="<?php echo $GLOBALS['dir']."core/jquery/jquery.validate-1.13.0.am.js";?>" type="text/javascript"></script>
 <script src="<?php echo $GLOBALS['dir']."core/jquery/jquery.validate-1.13.0.it.js";?>" type="text/javascript"></script><script src="<?php echo $GLOBALS['dir']."core/jquery/jquery.md5-1.0.0.js";?>" type="text/javascript"></script>
 <script src="<?php echo $GLOBALS['dir']."core/jquery/jquery.markdown.js";?>" type="text/javascript"></script>
 <script src="<?php echo $GLOBALS['dir']."core/jquery/jquery.to-markdown.js";?>" type="text/javascript"></script>
 <script src="<?php echo $GLOBALS['dir']."core/jquery/jquery.wysihtml5-0.3.0.min.js";?>" type="text/javascript"></script>
 <script src="<?php echo $GLOBALS['dir']."core/jquery/jquery.chained-0.9.10.min.js";?>" type="text/javascript"></script><script src="<?php echo $GLOBALS['dir']."core/jquery/jquery.chained-0.9.10.min.js";?>" type="text/javascript"></script>
 <script src="<?php echo $GLOBALS['dir']."core/jquery/jquery.chained.remote-0.9.10.min.js";?>" type="text/javascript"></script>
 <script src="<?php echo $GLOBALS['dir']."core/jquery/jquery.sortable-0.9.13.js";?>" type="text/javascript"></script>
 <script src="<?php echo $GLOBALS['dir']."core/shadowbox/shadowbox.js";?>" type="text/javascript"></script>
 <script type="text/javascript">
  Shadowbox.init();
  //Shadowbox.init({onClose:function(){ window.location.reload();}}); // eseguire reload solo se chiudo la chat
 </script>

 <!-- IE6-8 support of HTML5 elements -->
 <!--[if lt IE 9]>
  <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
 <![endif]-->

 <!-- Favicon -->
 <link rel="shortcut icon" type="image/x-icon" href="<?php echo $GLOBALS['dir']."core/images/favicon.ico";?>">
 <link rel="shortcut icon" href="<?php echo $GLOBALS['dir']."core/images/favicon.png";?>">

 <!-- Web Application Icon -->
 <link rel="icon" sizes="144x144" href="<?php echo $GLOBALS['dir']."core/images/logos/logo_144.png";?>" type="image/png">

 <!-- Apple iOS -->
 <meta name="apple-mobile-web-app-capable" content="yes">
 <link rel="apple-touch-icon-precomposed" sizes="144x144" href="<?php echo $GLOBALS['dir']."core/images/logos/logo_144.png";?>">
 <link rel="apple-touch-icon-precomposed" sizes="114x114" href="<?php echo $GLOBALS['dir']."core/images/logos/logo_114.png";?>">
 <link rel="apple-touch-icon-precomposed" sizes="72x72" href="<?php echo $GLOBALS['dir']."core/images/logos/logo_72.png";?>">
 <link rel="apple-touch-icon-precomposed" href="<?php echo $GLOBALS['dir']."core/images/logos/logo_57.png";?>">

</head>

<body <?php if(api_baseName()=="login.php"){echo "class='login-background'";}?>>

 <!-- Navbar -->
 <div class="navbar navbar-fixed-top">
  <div class="navbar-inner">
   <div class="container">
    <!-- collapse -->
    <a class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
     <span class="icon-bar"></span>
     <span class="icon-bar"></span>
     <span class="icon-bar"></span>
    </a>

    <?php
     if(file_exists("../uploads/uploads/core/logo.png") && api_getOption('show_logo')){
      echo "<a class='brand-logo' href='".$GLOBALS['dir']."index.php'><img src='".$GLOBALS['dir']."uploads/uploads/core/logo.png'></a>\n";
     }else{
      echo "<a class='brand' href='".$GLOBALS['dir']."index.php'>".api_getOption('title')."</a>\n";
     }
    ?>

    <?php if($navbar){ ?>

    <div class="nav-collapse collapse">

     <ul class="nav">

      <li class="<?php if($nav=="dashboard"){echo "active";} ?>"><a href="<?php echo $GLOBALS['dir']."dashboard/index.php";?>"><?php echo api_text("core-menu-dashboard"); ?></a></li>

      <?php
       // get menus
       function html_get_menus($idMenu=1,$level=0){
        $menus_array=array();
        // get menu objects
        $menus=$GLOBALS['db']->query("SELECT * FROM settings_menus WHERE idMenu='".$idMenu."' ORDER BY position ASC");
        while($menu=$GLOBALS['db']->fetchNextObject($menus)){
         // get translated name
         $translation=$GLOBALS['db']->queryUniqueObject("SELECT * FROM settings_menus_languages WHERE idMenu='".$menu->id."' AND language='".$_SESSION['language']."'");
         if($translation->id){$menu->menu=$translation->name;}
         // if menu is module check permissions to show
         if($menu->module){if(!api_checkPermissionShowModule($menu->module)){continue;}}
         // build submenu objects array
         $menu->submenus=array();
         $menu->submenus=html_get_menus($menu->id,($level+1));
         // if menu is not module and submenus is null
         if(!$menu->module && !count($menu->submenus)){continue;}
         // list of modules in menu for active class
         $menu->modules=array();
         if($menu->module){$menu->modules[]=$menu->module;}
         foreach($menu->submenus as $submenu){if($submenu->module){$menu->modules[]=$submenu->module;}}
         // add menu to array
         $menus_array[$menu->id]=$menu;
        }
        return $menus_array;
       }
       // print spaces in html source
       function html_print_menu_spaces($level){for($i=0;$i<=$level;$i++){echo "      ";}}
       // print menus
       function html_print_menu($menus_array,$nav,$level=0){
        // cycle menus
        foreach($menus_array as $menu){
         // open item
         echo "<li class=\"";
         if(in_array($nav,$menu->modules)){echo "active ";}
         // check and open submenus
         if(count($menu->submenus)){
          // check for first level menu
          if($level==0){
           echo "dropdown\">";
           echo "<a href='#' class='dropdown-toggle' data-toggle='dropdown'>";
           echo stripslashes($menu->menu)." <b class='caret'></b></a>\n";
           html_print_menu_spaces($level);
           echo "<ul class='dropdown-menu'>\n";
          }else{
           echo "dropdown-submenu\">";
           echo "<a href='#'>".stripslashes($menu->menu)."</a>\n";
           html_print_menu_spaces($level);
           echo "<ul class='dropdown-menu'>\n";
          }
          // print submenus
          html_print_menu($menu->submenus,$nav,($level+1));
          html_print_menu_spaces($level);
          // close submenu
          echo "</ul>\n";
          html_print_menu_spaces($level);
         }else{
          // print item
          echo "\"><a href='".$GLOBALS['dir'].$menu->module."/".$menu->url."'>".stripslashes($menu->menu)."</a>";
         }
         // close item
         echo "</li>\n";
        }
       }
       // get menus
       $menus_array=html_get_menus();
       // print menus
       html_print_menu($menus_array,$nav);
      ?>

     </ul>

     <ul class="nav pull-right">

      <?php if($GLOBALS['initial_module']<>"index"){ ?><li><a href="<?php echo $GLOBALS['dir']."index/index.php";?>"><i class="icon-home"></i></a></li> <?php } ?>

      <?php if(api_checkPermission("chats","chats_chat")){ ?>

      <li class="dropdown">
       <a href="#" class="dropdown-toggle" data-toggle="dropdown">
        <span id="chat_counter">
        <?php include("../chats/chat_counter.inc.php") ?>
        </span>
        <b class="caret"></b>
       </a>
       <ul class="dropdown-menu" id="chat_list">
        <?php include("../chats/chat_list.inc.php"); ?>
       </ul>
      </li>

      <?php } ?>

      <li class="dropdown">
       <a href="#" class="dropdown-toggle" data-toggle="dropdown">
        <span id="notifications_counter">
        <?php include("../logs/logs_notifications_counter.inc.php") ?>
        </span>
        <b class="caret"></b>
       </a>
       <ul class="dropdown-menu" id="notifications_list">
        <?php include("../logs/logs_notifications_list.inc.php"); ?>
       </ul>
      </li>

      <?php
       // reset module language
       api_loadModule();
      ?>

      <?php
       // show support link in menu bar
       // require module workflows -> https://github.com/Zavy86/Coordinator-Workflows
       if(file_exists("../helpdesk/module.inc.php")){
        echo "<li";
        if(api_baseName()=="tickets_open.php"||api_baseName()=="tickets_add.php"){echo " class=\"active\"";}
        echo "><a href='".$GLOBALS['dir']."helpdesk/tickets_open.php' title='".ucfirst(api_text("support"))."'>";
        echo "<img src='".$GLOBALS['dir']."core/images/icons/support.png' style='margin-top:-2px'></a></li>";
       }
      ?>

      <li class="dropdown">
       <a href="#" class="dropdown-toggle" data-toggle="dropdown">
        <img src="<?php echo api_account()->avatar;?>" class="img-rounded" style="width:22px;margin-top:-4px;padding:0;"> <b class="caret"></b>
       </a>
       <ul class="dropdown-menu">
        <li class="nav-header"><?php echo $_SESSION['account']->name;?></li>
        <li><a href="<?php echo $GLOBALS['dir']."accounts/index.php";?>"><?php echo api_text("core-menu-account"); ?></a></li>
        <?php if(api_checkPermission("accounts","accounts_view")){echo "<li><a href=\"".$GLOBALS['dir']."accounts/index.php\">".api_text("core-menu-accounts")."</a></li>\n";} ?>
        <?php if(api_checkPermission("stats","stats_server")){echo "<li><a href=\"".$GLOBALS['dir']."stats/index.php\">".api_text("core-menu-statistics")."</a></li>\n";} ?>
        <?php if(api_checkPermission("settings","settings_edit")||api_checkPermission("settings","permissions_manage")){echo "<li><a href=\"".$GLOBALS['dir']."settings/index.php\">".api_text("core-menu-settings")."</a></li>\n";} ?>
        <?php if(api_checkPermission("logs","logs_list")){echo "<li><a href=\"".$GLOBALS['dir']."logs/logs_list.php\">".api_text("core-menu-logs")."</a></li>\n";} ?>
        <?php if(api_checkPermission("database","database_view")){echo "<li><a href=\"".$GLOBALS['dir']."database/index.php\">".api_text("core-menu-database")."</a></li>\n";} ?>
        <li><a href="<?php echo $GLOBALS['dir']."dashboard/dashboard_edit.php";?>"><?php echo api_text("core-menu-dashboard-edit"); ?></a></li>

        <?php

         if($_SESSION['account']->interpreter){

         }

         if($_SESSION['account']->superuser){
          echo "<li class='divider'></li>\n";
          if($_SESSION['account']->debug){
           echo "<li><a href='".$GLOBALS['dir']."accounts/submit.php?act=account_debug_disable'>".api_text("core-menu-debugDisable")."</a></li>\n";
          }else{
           echo "<li><a href='".$GLOBALS['dir']."accounts/submit.php?act=account_debug_enable'>".api_text("core-menu-debugEnable")."</a></li>\n";
          }
          if(!$_SESSION['account']->administrator){
           echo "<li><a href='".$GLOBALS['dir']."accounts/submit.php?act=account_switch_to_admin'>".api_text("core-menu-becomeAdministrator")."</a></li>\n";
          }elseif($_SESSION['account']->id>1){
           echo "<li><a href='".$GLOBALS['dir']."accounts/submit.php?act=account_switch_to_user'>".api_text("core-menu-becomeUser")."</a></li>\n";
          }
         }

         echo "<li class='divider'></li>\n";

         if(!$_SESSION['account']->interpreter){
          echo "<li><a href='".$GLOBALS['dir']."accounts/submit.php?act=account_logout'>".api_text("core-menu-logout")."</a></li>\n";
         }else{
          echo "<li><a href='".$GLOBALS['dir']."accounts/submit.php?act=account_interpret_stop'>".api_text("core-menu-interpretStop")."</a></li>\n";
         }
        ?>
       </ul>
      </li>

     </ul>

    </div><!-- /nav-collapse -->

    <?php } ?>

   </div><!-- /container -->
  </div>
 </div><!-- /navbar -->

 <?php
  // show modal notifications windows
  if(count($modals_notifications_array)>0){
   foreach($modals_notifications_array as $modal){$modal->render();}
  }
 ?>

 <?php
  if(api_checkPermission("chats","chats_chat")){
   // modal new message
   echo "<div id='modalNew' class='modal hide fade' role='dialog' aria-hidden='true'>\n";
   echo "<div class='modal-header'>\n";
   echo "<button type='button' class='close' data-dismiss='modal' aria-hidden='true'>&times;</button>\n";
   echo "<h4>Nuovo messaggio</h4>";
   echo "</div>\n";
   echo "<div class='modal-body'>\n";
   echo "<input type='hidden' id='chat_idAccountTo' name='chat_idAccountTo' style='width:520px'>\n";
   echo "</div>\n</div>\n";
  }
 ?>

 <!-- Content -->
 <div class="container">

  <div class="row-fluid"><?php api_alert(); ?></div>

<?php



       //pre_var_dump($menus_array);




}

/* -[ Footer ]--------------------------------------------------------------- */
public function footer($copyright=TRUE){
?>

<?php if($copyright){ ?>

  <div class="row-fluid">
   <hr>
   <!-- Footer -->
   <footer>

    <?php
     if(file_exists("manuals/".api_account()->language.".pdf")){$manual_link="manuals/".api_account()->language.".pdf";}
     elseif(file_exists("manuals/default.pdf")){$manual_link="manuals/default.pdf";}
     if(strlen($manual_link)){echo "<span class='help'>".api_text("core-footer-manual",array("<a href='".$manual_link."' target='_blank'>","</a>"))."</span>";}
    ?>

    <span class="muted credit pull-right">
     Copyright 2009-<?php echo date("Y");?> &copy; <a href="http://www.coordinator.it" target="_blank">Coordinator</a> - All Rights Reserved
     <?php
      if(api_accountGroupMember(1)){
       $script_timer_end=api_getmicrotime();
       $script_timer_execution=number_format($script_timer_end-$GLOBALS['script_timer'],2,",",".");
       echo " - Execution time: ".$script_timer_execution. " secs";
       echo " - Queries: ".$GLOBALS['db']->nbQueries;
      }
     ?>
    </span>

   </footer>
  </div><!-- /row -->

 <?php } ?>

 </div><!-- /container -->

 <!-- Javascript -->
 <script src="<?php echo $GLOBALS['dir']."core/bootstrap/js/bootstrap.min.js";?>" type="text/javascript"></script>
 <script src="<?php echo $GLOBALS['dir']."core/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js";?>" type="text/javascript"></script>
 <script src="<?php echo $GLOBALS['dir']."core/bootstrap-markdown/js/bootstrap-markdown.js";?>" type="text/javascript"></script>
 <script src="<?php echo $GLOBALS['dir']."core/bootstrap-select2/select2.min.js";?>" type="text/javascript"></script>
 <script src="<?php echo $GLOBALS['dir']."core/bootstrap-select2/select2_locale_it.js";?>" type="text/javascript"></script>
 <script src="<?php echo $GLOBALS['dir']."core/bootstrap-wysihtml5/js/bootstrap-wysihtml5.min.js";?>" type="text/javascript"></script>
 <script src="<?php echo $GLOBALS['dir']."core/bootstrap-wysihtml5/js/bootstrap-wysihtml5.it-IT.js";?>" type="text/javascript"></script>
 <script src="<?php echo $GLOBALS['dir']."core/bootstrap-tagsinput/js/bootstrap-tagsinput.min.js";?>" type="text/javascript"></script>
 <script src="<?php echo $GLOBALS['dir']."core/bootstrap-slider/js/bootstrap-slider.min.js";?>" type="text/javascript"></script>
 <script src="<?php echo $GLOBALS['dir']."core/bootstrap-colorpicker/js/bootstrap-colorpicker.min.js";?>" type="text/javascript"></script>
 <script src="<?php echo $GLOBALS['dir']."core/bootstrap-fontawesomepicker/js/fontawesome-iconpicker.min.js";?>" type="text/javascript"></script>

 <script type="text/javascript">
  $(document).ready(function(){
   // active popovers
   $("[data-toggle=popover]").popover({trigger:"hover"});
  });
 </script>

 <?php if(api_checkPermission("chats","chats_chat")){ ?>

 <script type="text/javascript">
  $(document).ready(function(){
   // refresh notifications every 1 minutes
   var refreshNotificationsCounter=setInterval(function(){
    $.get("../logs/logs_notifications_counter.inc.php",function(data){
     $('#notifications_counter').html(data);
     if(data.substr(3,1)>0){
      clearInterval(refreshNotificationsCounter);
      $('#notifications_list').load("../logs/logs_notifications_list.inc.php");
     }
    });
   },60000);
   // refresh chats every 10 sec
   var chat_popup=1;
   var refreshChatCounter=setInterval(function(){
    $.get("../chats/chat_counter.inc.php",function(data){
     $('#chat_counter').html(data);
     if(data.substr(3,1)>0){
      //clearInterval(refreshChatCounter);
      $('#chat_list').load("../chats/chat_list.inc.php",function(){
       Shadowbox.clearCache();
       Shadowbox.setup();
      });
      if(chat_popup===1){alert("Hai ricevuto un nuovo messaggio via Chat");}
      chat_popup=0;
     }
    });
   },10000);
   // select2 chat_idAccountTo
   $("#chat_idAccountTo").select2({
    placeholder:"Cerca un contatto",
    minimumInputLength:2,
    ajax:{
     url:"../accounts/accounts_json.inc.php",
     dataType:"json",
     data:function(term,page){return{q:term};},
     results:function(data,page){return{results:data};}
    }
   });
   // select2 chat_idAccountTo redirect
   $("#chat_idAccountTo").change(function(){
    $('#modalNew').modal('hide');
    Shadowbox.open({
     content:"../chats/chat.inc.php?account="+$("#chat_idAccountTo").val(),
     player:"iframe",
     width:360,
     height:480
    });
   });
  });
 </script>

 <?php } ?>

 <?php
  $piwik=api_getOption("piwik_analytics");
  if($piwik<>""){
   $piwik_server=substr($piwik,0,strpos($piwik,":"));
   $piwik_siteid=substr($piwik,strpos($piwik,":",0)+1);
 ?>

 <!-- Piwik -->
 <script type="text/javascript">
  var _paq=_paq||[];
  _paq.push(['trackPageView']);
  _paq.push(['enableLinkTracking']);
  (function(){
   var u=(("https:"==document.location.protocol)?"https":"http")+"://<?php echo $piwik_server;?>//";
   _paq.push(['setTrackerUrl', u+'piwik.php']);
   _paq.push(['setSiteId',<?php echo $piwik_siteid;?>]);
   var d=document,g=d.createElement('script'),s=d.getElementsByTagName('script')[0];g.type='text/javascript';
   g.defer=true;g.async=true;g.src=u+'piwik.js';s.parentNode.insertBefore(g,s);
  })();
 </script>
 <noscript><p><img src="http://<?php echo $piwik_server;?>/piwik.php?idsite=<?php echo $piwik_siteid;?>" style="border:0" alt="" /></p></noscript>
 <!-- End Piwik Code -->
 <?php } ?>

 <?php
  // debug
  if($_SESSION["account"]->debug){
   $script_timer_end=api_getmicrotime();
   $script_timer_execution=number_format($script_timer_end-$GLOBALS['script_timer'],2,",",".");
   echo "<br><hr><center>";
   echo "Script Execution time: ".$script_timer_execution. " secs";
   echo " - Total executed queries: ".number_format($GLOBALS['db']->nbQueries,0,",",".");
   echo "</center>";
  }
 ?>

</body>
</html>

<?php

}




 //private $current_split;
 //protected $splits;

 protected $split_open;
 protected $split_columns;

 protected $class;

 /* -[ Construct ]----------------------------------------------------------- */
 public function __construct(){
  $this->split_open=FALSE;
  $this->split_columns=0;
 }

 /* -[ Open ]---------------------------------------------------------------- */
 // @string $class : splitting css class
 function split_open($class=NULL){
  echo "<!-- row-fluid -->\n";
  echo "<div class='row-fluid ".$class."'>\n";
 }

 /* -[ Span ]---------------------------------------------------------------- */
 // @string $columns : number of columns
 // @string $class : span css class
 function split_span($columns,$class=NULL){
  if($columns<1 && $columns>12){return FALSE;}
  if($this->split_open){
   echo "\n </div><!-- /span".$this->split_columns." -->\n";
  }else{
   $this->split_open=TRUE;
  }
  $this->split_columns=$columns;
  echo " <div class='span".$columns." ".$class."'>\n\n";
 }

 /* -[ Close ]--------------------------------------------------------------- */
 function split_close(){
  if($this->split_open){
   echo "\n </div><!-- /span".$this->split_columns." -->\n";
   $this->split_open=FALSE;
  }
  echo "</div><!-- /row-fluid -->\n\n";
 }






// /class
}
?>