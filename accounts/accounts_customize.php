<?php
/* -------------------------------------------------------------------------- *\
|* -[ Accounts - Accounts Customize ]---------------------------------------- *|
\* -------------------------------------------------------------------------- */
include("template.inc.php");
function content(){
 // get objects
 $account=api_accounts_account();
 // build webcam modal
 $webcam_modal=new str_modal("webcam");
 $webcam_modal->header(api_text("accounts_customize-modal-title"));
 $webcam_modal->body("<div id='buttons'><button id='start'>".api_icon("icon-off")."</button><button id='snap'>".api_icon("icon-camera")."</button><button id='upload'>".api_icon("icon-ok")."</button><br><br><button id='discard'>".api_icon("icon-repeat")."</button></div><div id='live'><video id='video' width='320' height='240' autoplay></video><div id='square'></div></div><div id='photo'><canvas id='canvas' width='200' height='200'></canvas></div>");
 // build account description list
 $account_dl=new str_dl("br","dl-horizontal");
 $account_dl->addElement(api_text("accounts_customize-ff-avatar"),api_image($account->avatar,"img-polaroid",125,NULL,TRUE)." ".$webcam_modal->link(api_icon("icon-camera"),"btn"));
 // build account form
 $account_form=new str_form("submit.php?act=account_customize&idAccount=".$account->id,"post","accounts_customize");
 $account_form->addField("file","avatar","&nbsp;",NULL,"input-large",api_text("accounts_customize-ff-avatar-placeholder"));
 $account_form->addField("text","name",api_text("accounts_customize-ff-name"),stripslashes($account->name),"input-large",api_text("accounts_customize-ff-name-placeholder"),FALSE,NULL,NULL,(api_checkPermission("accounts","accounts_customize")?FALSE:TRUE));
 if(strlen($account->ldap)){
  $account_form->addField("text","account",api_text("accounts_customize-ff-mail"),stripslashes($account->account),"input-xlarge",api_text("accounts_customize-ff-account-placeholder"),FALSE,NULL,NULL,(api_checkPermission("accounts","accounts_customize")?FALSE:TRUE));
 }else{
  $account_form->addField("text","account",api_text("accounts_customize-ff-account"),stripslashes($account->account),"input-xlarge",api_text("accounts_customize-ff-account-placeholder"),FALSE,NULL,NULL,(api_checkPermission("accounts","accounts_customize")?FALSE:TRUE));
  $account_form->addField("password","password",api_text("accounts_customize-ff-password"),NULL,"input-large",api_text("accounts_customize-ff-password-placeholder"));
  $account_form->addField("password","confirm",api_text("accounts_customize-ff-confirm"),NULL,"input-large",api_text("accounts_customize-ff-confirm-placeholder"));
 }
 // custom account fields
 if(is_array($GLOBALS['custom_fields']['accounts'])){
  foreach($GLOBALS['custom_fields']['accounts'] as $field){
   if(!$account->{$field}){continue;}
   $account_form->addField("text",$field,strtoupper(str_replace("_"," ",$field)),$account->{$field},"input-large",null,true); /* @todo check for disabled method */
  }
 }
 $account_form->addField("text","phone",api_text("accounts_customize-ff-phone"),$account->phone,"input-medium",api_text("accounts_customize-ff-phone-placeholder"));
 $account_form->addField("select","language",api_text("accounts_customize-ff-language"),NULL,"input-medium");
 foreach(api_language_availables() as $key=>$language){$account_form->addFieldOption($key,$language." (".$key.")",($key==$account->language?TRUE:FALSE));}
 // controls
 $account_form->addControl("submit",api_text("accounts_customize-fc-submit"));
 $account_form->addControl("button",api_text("accounts_customize-fc-cancel"),NULL,"../index.php");
 // build companies table
 $companies_table=new str_table(api_text("accounts_customize-companies-tr-unvalued"));
 // build companies table headers
 $companies_table->addHeader("&nbsp;",NULL,"16");
 $companies_table->addHeader(api_text("accounts_customize-companies-th-name"),NULL,"100%");
 $companies_table->addHeader(api_text("accounts_customize-companies-th-role"),"nowarp text-right");
 // build companies table fields
 foreach($account->companies as $company){
  // make main
  if($company->main){$main_td=api_icon('icon-star',api_text("accounts_customize-companies-td-main"));}
  else{$main_td=api_icon('icon-star-empty');}
  // build company row
  $companies_table->addRow();
  $companies_table->addField($main_td,"text-center");
  $companies_table->addField($company->name,"nowarp");
  $companies_table->addField($company->role->name,"nowarp text-right");
 }
 // companies groups
 $companies_groups_array=array();
 // cycle companies
 foreach($account->companies as $company){
  // build groups table
  $groups_table=new str_table(api_text("accounts_customize-groups-tr-unvalued"));
  // build companies table headers
  $groups_table->addHeader("&nbsp;",NULL,"16");
  $groups_table->addHeader(api_text("accounts_customize-groups-th-name",$company->name),NULL,"100%");
  // build companies table fields
  foreach($company->groups as $group){
   // make del and main
   if($group->main){$main_td=api_icon('icon-star',api_text("accounts_customize-groups-td-main"));}
   else{$main_td=api_icon('icon-star-empty');}
   // build company row
   $groups_table->addRow();
   $groups_table->addField($main_td,"text-center");
   $groups_table->addField($group->path.$group->label);
  }
  // add table to array
  $companies_groups_array[]=$groups_table;
 }
 // open split
 $GLOBALS['html']->split_open();
 $GLOBALS['html']->split_span(6);
 // renderize account description list
 if(is_object($account_dl)){$account_dl->render();}
 // renderize account form
 if(is_object($account_form)){$account_form->render();}
 // split page
 $GLOBALS['html']->split_span(6);
 // renderize companies table
 if(is_object($companies_table)){$companies_table->render();}
 // cycle associated companies
 if(is_array($companies_groups_array)){
  foreach($companies_groups_array as $company_groups){
   // renderize groups table
   if(is_object($company_groups)){$company_groups->render();}
  }
 }
 // close split
 $GLOBALS['html']->split_close();
 // renderize webcam modal
 $webcam_modal->render();
 // debug
 if($_SESSION["account"]->debug){pre_var_dump($account,"print","account");}
?>
<script type="text/javascript">
 $(document).ready(function(){
  // validation account
  $('form[name=accounts_customize]').validate({
   rules:{
<?php
 if(!strlen($account->ldap)){
  echo "    password:{minlength:6},\n";
  echo "    confirm:{minlength:6,equalTo:\"#accounts_customize_input_password\"},\n";
 }
?>
    name:{required:true,minlength:3}
   },
   submitHandler:function(form){form.submit();}
  });
 });
 /* webcam scripts */
 window.addEventListener("DOMContentLoaded",function(){
  // Elements for taking the snapshot
  var video=document.getElementById('video');
  var canvas=document.getElementById('canvas');
  var context=canvas.getContext('2d');
  var video=document.getElementById('video');
  var errBack=function(e){console.log('An error has occurred!',e);};
  $("#snap").hide();
  $("#photo").hide();
  $("#discard").hide();
  $("#upload").hide();
  // trigger start
  document.getElementById("start").addEventListener("click",function(){
   $("#start").hide();
   $("#snap").show();
   // get access to the camera
   if(navigator.mediaDevices && navigator.mediaDevices.getUserMedia){
    //navigator.mediaDevices.getUserMedia({video:true}).then(function(stream){
    navigator.mediaDevices.getUserMedia({video:{width:640,height:480}}).then(function(stream){
     video.src=window.URL.createObjectURL(stream);
     video.play();
    });
   }else if(navigator.getUserMedia){ // Standard
    navigator.getUserMedia({video:{width:640,height:480}},function(stream){
     video.src=stream;
     video.play();
    },errBack);
   }else if(navigator.webkitGetUserMedia){ // WebKit-prefixed
    navigator.webkitGetUserMedia({video:{width:640,height:480}},function(stream){
     video.src=window.webkitURL.createObjectURL(stream);
     video.play();
    },errBack);
   }else if(navigator.mozGetUserMedia){ // Mozilla-prefixed
    navigator.mozGetUserMedia({video:{width:640,height:480}},function(stream){
     video.src=window.URL.createObjectURL(stream);
     video.play();
    },errBack);
   }
  });
  // trigger photo take
  document.getElementById("snap").addEventListener("click",function(){
   context.drawImage(video,120,40,400,400,0,0,200,200);
   $("#photo").show();
   $("#live").hide();
   $("#discard").show();
   $("#snap").hide();
   $("#upload").show();
  });
  // trigger pgoto discard
  document.getElementById("discard").addEventListener("click",function(){
	   $("#photo").hide();
    $("#live").show();
    $("#discard").hide();
    $("#snap").show();
    $("#upload").hide();
  });
  // upload the photo
  document.getElementById("upload").addEventListener("click",function(){
   var dataURL=canvas.toDataURL("image/jpeg",1);
   console.log("save photo");
   $.ajax({
    url:"submit.php?act=account_customize_webcam",
    type:"POST",
    data:{
     imgBase64:dataURL,
     idAccount:<?php echo $account->id?>
    }
   }).done(function(msg){
    console.log("saved");
    window.location.replace("accounts_customize.php");
   });
  });
 },false);
</script>
<style>
 #buttons{
  float:left;
  margin-right:10px;
 }
 #live{
  position:relative;
  float:left;
  width:320px;
  height:240px;
  border:1px solid #333333;
  z-index:1000;
 }
 #live #square{
  position:relative;
  top:-225px;
  left:60px;
  width:200px;
  height:200px;
  border:2px dashed red;
  z-index:1200;
 }
 #video{
  position:relative;
  top:0;
  left:0;
  z-index:1100;
 }
 #photo{
  position:relative;
  float:left;
  width:200px;
  height:200px;
  border:1px solid #333333;
  z-index:1000;
 }
</style>
<?php } ?>