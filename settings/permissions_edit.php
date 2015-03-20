<?php
/* ------------------------------------------------------------------------- *\
|* -[ Settings - Permissions Edit ]----------------------------------------- *|
\* ------------------------------------------------------------------------- */
$checkPermission="permissions_edit";
include("template.inc.php");
function content(){
 // definitions
 $modules_array=array();
 $permissions_array=array();
 // acquire variables
 $g_module=$_GET['module'];
 $g_idCompany=$_GET['idCompany'];
 // build modules array
 $modules=$GLOBALS['db']->query("SELECT DISTINCT module FROM settings_permissions ORDER BY module ASC");
 while($module=$GLOBALS['db']->fetchNextObject($modules)){$modules_array[]=$module->module;}
 // build permissions array
 if(strlen($g_module)){
  $permissions=$GLOBALS['db']->query("SELECT * FROM settings_permissions WHERE module='".$g_module."' ORDER BY id ASC");
  while($permission=$GLOBALS['db']->fetchNextObject($permissions)){$permissions_array[]=$permission;}
 }
 // build modules sidebar
 $sidebar=new str_sidebar();
 $sidebar->addHeader(api_text("permissions_edit-modules"));
 foreach($modules_array as $module){
  $label=mb_convert_case(mb_strtolower($module,"UTF-8"),MB_CASE_TITLE,"UTF-8");
  if(strlen($label)==3){$label=mb_strtoupper($module,"UTF-8");}
  $sidebar->addItem($label,"permissions_edit.php?module=".$module);
 }

 // build options group    ----------------------------------------------------- da verificare
 function permissions_edit_options_group($array,$level=0){
  foreach($array as $group){
   $pre=NULL;
   for($i=0;$i<$level;$i++){$pre.="&nbsp;&nbsp;&nbsp;";}
   $return.="<option value='".$group->id."'>".$pre.$group->label."</option>\n";
   $return.=permissions_edit_options_group($group->groups,($level+1));
  }
  return $return;
 }

 // build module tabbable
 $tabbable=new str_tabbable("top");
 // cycle companies
 $companies=api_accounts_companies();
 foreach($companies->results as $company){
  // get company groups
  $company_groups=api_accounts_groups($company->id);
  // build permissions table
  $permissions_table=new str_table(api_text("permissions_edit-permission-tr-unvalued"));
  // build permissions table header
  $permissions_table->addHeader("&nbsp;",NULL,16);
  $permissions_table->addHeader(api_text("permissions_edit-permission-th-action",$company->name),"nowarp");
  $permissions_table->addHeader(api_text("permissions_edit-permission-th-group"),NULL,"100%");
  // cycle permissions
  foreach($permissions_array as $permission){
   // make lock field
   if($permission->locked){$locked_td=api_icon("icon-lock");}else{$locked_td=NULL;}
   // make enabled groups
   $enabled_groups=NULL;
   // build permissions table rows
   $permission_groups_array=array();
   $permission_groups=$GLOBALS['db']->query("SELECT settings_permissions_join_accounts_groups.*,accounts_groups.id,accounts_groups.name,accounts_groups.description FROM settings_permissions_join_accounts_groups LEFT JOIN accounts_groups ON accounts_groups.id=settings_permissions_join_accounts_groups.idGroup WHERE settings_permissions_join_accounts_groups.idPermission='".$permission->id."' AND ( settings_permissions_join_accounts_groups.idCompany='".$company->id."' OR accounts_groups.idCompany='".$company->id."' ) ORDER BY name ASC");
   while($permission_group=$GLOBALS['db']->fetchNextObject($permission_groups)){
    if($permission_group->id==NULL){
     $label=api_link("submit.php?act=permission_group_remove&module=".$g_module."&idPermission=".$permission->id."&idCompany=".$company->id."&idGroup=0",api_icon("icon-trash",api_text("permissions_edit-permission-td-group-remove")),NULL,NULL,FALSE,api_text("permissions_edit-permission-td-group-remove-confirm"));
     $label.=" ".api_tag("i",api_text("permissions_edit-permission-td-group-any")." (".$permission_group->level."+)");
    }else{
     $label=api_link("submit.php?act=permission_group_remove&module=".$g_module."&idPermission=".$permission->id."&idCompany=".$company->id."&idGroup=".$permission_group->id,api_icon("icon-trash",api_text("permissions_edit-permission-td-group-remove")),NULL,NULL,FALSE,api_text("permissions_edit-permission-td-group-remove-confirm"));
     $label.=" ".stripslashes($permission_group->name);
     if(strlen($permission_group->description)){$label.=" &minus; ".stripslashes($permission_group->description);}
     $label.=" (".$permission_group->level."+)";
    }
    $permission_groups_array[$permission_group->idGroup]=$label;
    $enabled_groups.="<br>".$permission_groups_array[$permission_group->idGroup];
   }
   // check permissions
   if(!strlen($enabled_groups)){$enabled_groups="<br>".api_tag("i",api_text("permissions_edit-permission-td-group-none"));}
   // build table row
   $permissions_table->addRow();
   $permissions_table->addField($locked_td);
   $permissions_table->addField(api_link("#",$permission->description,$permission->action,"hiddenlink",TRUE),"nowarp");
   $permissions_table->addField(substr($enabled_groups,4));
  }
  // build permissions form
  $permissions_form=new str_form("submit.php?act=permission_group_add&module=".$g_module."&idCompany=".$company->id,"post","permissions_edit_".$company->id);
  $permissions_box=api_link("submit.php?act=permission_group_reset&module=".$g_module."&idCompany=".$company->id,api_icon("icon-repeat",api_text("permissions_edit-fc-reset")),NULL,"btn",FALSE,api_text("permissions_edit-fc-reset-confirm",$g_module))."\n";
  $permissions_box.="<select name='idPermission' class='input-xlarge'>\n";
  $permissions_box.="<option value=''>".api_text("permissions_edit-permission-fo-action-select")."</option>\n";
  $permissions_box.="<option value='0'>".api_text("permissions_edit-permission-fo-action-any")."</option>\n";
  foreach($permissions_array as $permission){
   if($permission->locked&&api_accounts_account()->id>1){continue;}
   $permissions_box.="<option value='".$permission->id."'>".stripslashes($permission->description)."</option>\n";
  }
  $permissions_box.="</select>\n";
  $permissions_box.="<select name='idGroup' class='input-xlarge'>\n";
  $permissions_box.="<option value=''>".api_text("permissions_edit-permission-fo-group-select")."</option>\n";
  $permissions_box.="<option value='0'>".api_text("permissions_edit-permission-fo-group-any")."</option>\n";
  $permissions_box.=permissions_edit_options_group($company_groups->results);
  $permissions_box.="</select>\n";
  $permissions_box.="<select name='level' class='input-medium'>\n";
  $permissions_box.="<option value=''>".api_text("permissions_edit-permission-fo-level-select")."</option>\n";
  foreach(api_accounts_roles()->results as $role){
   $label=$role->level." &minus; ".$role->name;
   if(strlen($role->description)){$label.=" (".$role->description.")";}
   $permissions_box.="<option value='".$role->level."'>".$label."</option>\n";
   // ^------------------------------------------------------------------------ usato level al posto di id
  }
  $permissions_box.="</select>\n";
  $permissions_box.="<input type='submit' name='permissions_edit_".$company->id."_submit' class='btn' value='+'>\n";
  $permissions_form->addCustomField(NULL,$permissions_box);
  // add company element
  $tabbable->addTab($company->name,$permissions_table->render(FALSE).$permissions_form->render(FALSE),NULL,TRUE,($company->id==$g_idCompany?TRUE:FALSE));
 }
 // open split
 $GLOBALS['html']->split_open();
 $GLOBALS['html']->split_span(3);
 // renderize sidebar
 $sidebar->render();
 // split page
 $GLOBALS['html']->split_span(9);
 // renderize permissions tabbable
 $tabbable->render();
 // close split
 $GLOBALS['html']->split_close();
?>
<script type="text/javascript">
 $(document).ready(function(){
<?php
  foreach($companies->results as $company){
   echo "  // validation company ".$company->name."\n";
   echo "  $('form[name=permissions_edit_".$company->id."]').validate({\n";
   echo "   rules:{\n";
   echo "    idPermission:{required:true},\n";
   echo "    idGroup:{required:true},\n";
   echo "    level:{required:true}\n";
   echo "   },submitHandler:function(form){form.submit();}\n";
   echo "  });\n";
  }
?>
 });
</script>
<?php } ?>