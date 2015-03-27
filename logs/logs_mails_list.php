<?php
/* -------------------------------------------------------------------------- *\
|* -[ Logs - Mail List ]----------------------------------------------------- *|
\* -------------------------------------------------------------------------- */
include("template.inc.php");
function content(){
 // definitions
 $modals_array=array();
 // build table
 $table=new str_table(api_text("mails_list-tr-no-results"),TRUE);
 $table->addHeader("&nbsp;",NULL,"16",NULL);
 $table->addHeader(api_text("mails_list-th-timestamp"),"nowarp",NULL,"addDate");
 $table->addHeader(api_text("mails_list-th-recipient"),NULL,NULL,"`to`");
 $table->addHeader(api_text("mails_list-th-subject"),NULL,"100%","subject");
 $table->addHeader(api_text("mails_list-th-sended"),"nowarp",NULL,"sendDate");
 $table->addHeader("&nbsp;","nowarp",NULL,"status");
 // build query
 $query_where="1";
 // pagination
 $pagination=new str_pagination("logs_mails",$query_where,"&s=".$g_status);
 $query_limit=$pagination->queryLimit();
 // query order
 $query_order=api_queryOrder("status=0 DESC,status DESC,addDate DESC");
 // query
 $mails=$GLOBALS['db']->query("SELECT * FROM logs_mails WHERE ".$query_where.$query_order.$query_limit);
 while($mail=$GLOBALS['db']->fetchNextObject($mails)){
  // build modal
  $modal=new str_modal($mail->id);
  $modal->header(stripslashes($mail->subject));
  $modal_body="<p>FROM: ".stripslashes($mail->sender)." - ".stripslashes($mail->from)."</p>\n";
  $modal_body.="<p>TO: ".stripslashes($mail->to)."</p>\n";
  $modal_body.="<p>CC: ".stripslashes($mail->cc)."</p>\n";
  $modal_body.="<p>BCC: ".stripslashes($mail->bcc)."</p>\n";
  if($mail->error){$modal_body.="<p class='text-error'>".stripslashes($mail->error)."</p>\n";}
  $modal_body.="<hr>\n<p>".nl2br(strip_tags(stripslashes($mail->message)))."</p>\n";
  $modal->body($modal_body);
  if($mail->status==1){
   $modal_footer=api_link("submit.php?act=mails_retry&id=".$mail->id,api_text("mails_list-m-resend"),NULL,"btn");
   $modal->footer($modal_footer);
  }
  if($mail->status==2){
   $modal_footer=api_text("mails_list-m-failed")." &nbsp; ";
   $modal_footer.=api_link("submit.php?act=mails_retry&id=".$mail->id,api_text("mails_list-m-retry"),NULL,"btn btn-success");
   $modal_footer.=api_link("submit.php?act=mails_delete&id=".$mail->id,api_text("mails_list-m-delete"),NULL,"btn btn-danger",TRUE,api_text("mails_list-m-delete-confirm"));
   $modal->footer($modal_footer);
  }
  $modals_array[]=$modal;
  // status
  if($mail->status==0){
   $tr_class=NULL;
   $status=api_icon('icon-cog');
  }elseif($mail->status==1){
   $tr_class=NULL;
   $status=api_icon('icon-ok');
  }else{
   $tr_class="warning";
   $status=api_icon('icon-remove');
  }
  // build table row
  $table->addRow($tr_class);
  // build table fields
  $table->addField($modal->link(api_icon("icon-search")));
  $table->addField(api_timestampFormat($mail->addDate,api_text("datetime")),"nowarp");
  $table->addField(stripslashes($mail->to),"nowarp");
  $table->addField(stripslashes($mail->subject));
  $table->addField(api_timestampFormat($mail->sendDate,api_text("datetime")),"nowarp");
  $table->addField($status,"nowarp");
 }
 // show table
 $table->render();
 // show modal windows
 foreach($modals_array as $modal){$modal->render();}
 // show the pagination div
 $pagination->render();
}
?>