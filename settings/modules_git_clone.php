<?php
/* -------------------------------------------------------------------------- *\
|* -[ Settings - Modules GIT Clone ]----------------------------------------- *|
\* -------------------------------------------------------------------------- */
$checkPermission="modules_edit";
include("template.inc.php");
function content(){
 // build form
 $form=new str_form("submit.php?act=module_git_clone","post","modules_git_clone");
 $form->addField("text","url",api_text("modules_git_clone-ff-url"),NULL,"input-xxlarge",api_text("modules_git_clone-ff-url-placeholder"));
 $form->addField("text","dir",api_text("modules_git_clone-ff-dir"),NULL,"input-medium");
 $form->addField("text","branch",api_text("modules_git_clone-ff-branch"),"master","input-small");
 $form->addControl("submit",api_text("modules_git_clone-fc-submit"));
 $form->addControl("button",api_text("modules_git_clone-fc-cancel"),NULL,"modules_edit.php");
 // show form
 $form->render();
?>
<script type="text/javascript">
 $(document).ready(function(){
  // get repository name for directory
  $('#modules_git_clone_input_url').change(function(){
   var url=$('#modules_git_clone_input_url').val();
   $('#modules_git_clone_input_dir').val(url.substring(url.lastIndexOf('/')+1,url.indexOf('.git')).toLowerCase())
  });
  // validation
  $('form').validate({
   rules:{
    url:{required:true},
    dir:{required:true},
    branch:{required:true}
   },
   submitHandler:function(form){form.submit();}
  });
 });
</script>
<?php } ?>