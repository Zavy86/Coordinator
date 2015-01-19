<?php
/* -------------------------------------------------------------------------- *\
|* -[ Settings - Settings Edit ]--------------------------------------------- *|
\* -------------------------------------------------------------------------- */
$checkPermission="settings_edit";
include("template.inc.php");
function content(){
 // reset cron token
 if($_GET['act']=="reset_cron"){
  $cron_token=md5(date("Y-m-d H:i:s"));
 }else{
  $cron_token=api_getOption("cron_token");
 }
 // build form
 $form=new str_form("submit.php?act=settings_save","post","settings");
 $form->splitOpen();
 // owner
 $form->addField("text","owner",api_text("settings-ff-owner"),api_getOption("owner"),"input-large");
 $form->addField("text","owner_url",api_text("settings-ff-owner_url"),api_getOption("owner_url"),"input-xlarge");
 $form->addSeparator();
 // mail
 $form->addField("text","owner_mail",api_text("settings-ff-owner_mail"),api_getOption("owner_mail"),"input-xlarge");
 $form->addField("text","owner_mail_from",api_text("settings-ff-owner_mail_from"),api_getOption("owner_mail_from"),"input-xlarge");
 $form->addField("checkbox","sendmail_asynchronous",api_text("settings-ff-sendmail"));
 $form->addFieldOption(1,api_text("settings-ff-sendmail-label"),(api_getOption("sendmail_asynchronous"))?TRUE:FALSE);
 $form->addSeparator();
 // title and logo
 $form->addField("text","title",api_text("settings-ff-title"),api_getOption("title"),"input-small");
 $form->addField("checkbox","show_logo",api_text("settings-ff-show_logo"));
 if(!file_exists("../uploads/uploads/core/logo.png")){
  $disabled=TRUE;
  $label=api_text("settings-ff-show_logo-label-path").": ".$GLOBALS['dir']."uploads/uploads/core/logo.png";
 }else{
  $disabled=FALSE;
  $label=api_text("settings-ff-show_logo-label")."<br><br>\n";
  $label.="<img src='".$GLOBALS['dir']."uploads/uploads/core/logo.png' alt='Title logo' class='logo'>";
 }
 $form->addFieldOption(1,$label,(api_getOption("show_logo"))?TRUE:FALSE,$disabled);
 $form->addSeparator();
 // maintenance
 $form->addField("checkbox","maintenance",api_text("settings-ff-maintenance"));
 $form->addFieldOption(1,api_text("settings-ff-maintenance-label"),(api_getOption("maintenance"))?TRUE:FALSE);
 $form->addField("text","maintenance_description",api_text("settings-ff-maintenance_description"),api_getOption("maintenance_description"),"input-xlarge");
 $form->splitSpan();
 // tokens
 $form->addField("text","google_analytics",api_text("settings-ff-google_analytics"),api_getOption("google_analytics"),"input-medium");
 $form->addField("text","piwik_analytics",api_text("settings-ff-piwik_analytics"),api_getOption("piwik_analytics"),"input-medium");
 $form->addField("text","cron_token",api_text("settings-ff-cron_token"),$cron_token,"input-xlarge");
 $form->addSeparator();
 // ldap
 $form->addField("checkbox","ldap",api_text("settings-ff-ldap"));
 $form->addFieldOption(1,api_text("settings-ff-ldap-label"),(api_getOption("ldap"))?TRUE:FALSE);
 $form->addField("text","ldap_host",api_text("settings-ff-ldap_host"),api_getOption("ldap_host"),"input-xlarge");
 $form->addField("text","ldap_dn",api_text("settings-ff-ldap_dn"),api_getOption("ldap_dn"),"input-xlarge");
 $form->addField("text","ldap_domain",api_text("settings-ff-ldap_domain"),api_getOption("ldap_domain"),"input-xlarge");
 $form->addField("text","ldap_userfield",api_text("settings-ff-ldap_userfield"),api_getOption("ldap_userfield"),"input-xlarge");
 $form->addField("text","ldap_group",api_text("settings-ff-ldap_group"),api_getOption("ldap_group"),"input-xlarge");
 $form->splitClose();
 // controls
 $form->addControl("submit",api_text("settings-fc-submit"));
 $form->addControl("button",api_text("settings-fc-cron"),NULL,"settings_edit.php?act=reset_cron");
 // show form
 $form->render();
?>
<script type="text/javascript">
 $(document).ready(function(){
  // disable cron reset if form change
  $('form').change(function(){
   $('#settings_control_1').attr("disabled","disabled");
  });
  // validation
  $('form').validate({
   rules:{
    owner:{required:true,minlength:3},
    owner_url:{required:true,url:true},
    owner_mail:{required:true,email:true},
    owner_mail_from:{required:true,minlength:3},
    title:{required:true,minlength:3},
    maintenance_description:{required:true,minlength:3}
   },
   submitHandler:function(form){form.submit();}
  });
 });
</script>
<?php } ?>