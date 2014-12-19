<?php
/* -------------------------------------------------------------------------- *\
|* -[ Uploads - Submit ]----------------------------------------------------- *|
\* -------------------------------------------------------------------------- */
include('../core/api.inc.php');
include('api.inc.php');
$act=$_GET['act'];
//switch($act){
// // contacts
// case "contact_save":contact_save();break;
// case "contact_manage":contact_manage();break;
// case "contact_delete":contact_delete("delete");break;
// case "contact_undelete":contact_delete("undelete");break;
// case "contact_remove":contact_delete("remove");break;
// case "contact_import":contact_import();break;
// case "contact_address_save":contact_address_save();break;
// case "contact_address_delete":contact_address_delete("delete");break;
// case "contact_address_undelete":contact_address_delete("undelete");break;
// case "contact_address_remove":contact_address_delete("remove");break;
// case "contact_referent_save":contact_referent_save();break;
// case "contact_referent_delete":contact_referent_delete("delete");break;
// case "contact_referent_undelete":contact_referent_delete("undelete");break;
// case "contact_referent_remove":contact_referent_delete("remove");break;
// case "contact_attachment_upload":contact_attachment_upload();break;
// case "contact_attachment_download":contact_attachment_download();break;
// case "contact_attachment_delete":contact_attachment_delete();break;
// // roles
// case "role_save":role_save();break;
// case "role_delete":role_delete("delete");break;
// case "role_undelete":role_delete("undelete");break;
// case "role_remove":role_delete("remove");break;
// case "role_txt_save":role_txt_save();break;
// case "role_txt_delete":role_txt_delete();break;
// // areas
// case "area_save":area_save();break;
// case "area_delete":area_delete("delete");break;
// case "area_undelete":area_delete("undelete");break;
// case "area_remove":area_delete("remove");break;
// case "area_txt_save":area_txt_save();break;
// case "area_txt_delete":area_txt_delete();break;
// // default
// default:
  $alert="?alert=submitFunctionNotFound&alert_class=alert-warning&act=".$act;
  exit(header("location: index.php".$alert));
//}
//
//
///**
// * Contact Save
// *
// * @param booelan $return if true don't redirect but return
// * @return boolean if return is true return true if insert query run without errors
// */
//function contact_save($return=FALSE){
// if(!api_checkPermission("contacts","contacts_edit")){api_die("accessDenied");}
// // get objects
// $contact=api_contacts_contact($_GET['idContact'],TRUE);
// // cross variables
// $_GET['idAddress']=$_POST['idAddress'];
// $_POST['typology']=1;
// // acquire contact
// $p_name=addslashes(api_cleanString(ucwords($_POST['name']),"/[^A-Za-zÀ-ÿ0-9-.' ]/"));
// $p_typology=api_cleanNumber($_POST['typology']);
// $p_language=strtoupper(addslashes($_POST['language']));
// $p_vatCode=addslashes(api_cleanString(mb_strtoupper($_POST['vatCode'],'UTF-8'),"/[^A-Z0-9]/")); //"/[^A-Z0-9-.]/"
// $p_fiscalCode=addslashes(api_cleanString(mb_strtoupper($_POST['fiscalCode'],'UTF-8'),"/[^A-Z0-9]/"));
// $p_url=addslashes(api_cleanString(mb_strtolower($_POST['url'],'UTF-8'),"/[^a-z0-9-.:?&=\/]/"));
// $p_iban=addslashes(api_cleanString(mb_strtoupper($_POST['iban'],'UTF-8'),"/[^A-Z0-9]/"));
// $p_bic=addslashes(api_cleanString(mb_strtoupper($_POST['bic'],'UTF-8'),"/[^A-Z0-9]/"));
// //$p_note=addslashes(api_cleanString($_POST['note'],"/[^A-Za-zÀ-ÿ0-9-.' ]/")); // <<<--- problemi con i \n da risolvere
// $p_note=addslashes($_POST['note']);
// $p_assIdAccount=api_cleanNumber($_POST['assIdAccount']);
// // check variables
// if(strlen($p_url)>0){if(substr($p_url,0,7)<>"http://"){$p_url="http://".$p_url;}}
// // lowercase
// $p_name=mb_strtolower($p_name,'UTF-8');
// // build contact query
// if($contact->id){
//  $query="UPDATE contacts_contacts SET
//   name='".$p_name."',
//   typology='".$p_typology."',
//   language='".$p_language."',
//   vatCode='".$p_vatCode."',
//   fiscalCode='".$p_fiscalCode."',
//   url='".$p_url."',
//   iban='".$p_iban."',
//   bic='".$p_bic."',
//   note='".$p_note."',
//   assIdAccount='".$p_assIdAccount."',
//   updDate='".api_now()."',
//   updIdAccount='".api_accountId()."'
//   WHERE id='".$contact->id."'";
//  // execute query
//  $GLOBALS['db']->execute($query);
//  // log event
//  $log=api_log(API_LOG_NOTICE,"contacts","contactUpdated",
//   "{logs_contacts_contactUpdated|".$contact->id."|".$p_name."}", // aggiungere quello che serve
//   $contact->id,"contacts/contacts_view.php?idContact=".$contact->id);
//  // alert
//  $alert="&alert=contactUpdated&alert_class=alert-success&idLog=".$log->id;
// }else{
//  $query="INSERT INTO contacts_contacts
//   (name,typology,language,vatCode,fiscalCode,url,iban,bic,note,assIdAccount,addDate,addIdAccount) VALUES
//   ('".$p_name."','".$p_typology."','".$p_language."','".$p_vatCode."','".$p_fiscalCode."',
//    '".$p_url."','".$p_iban."','".$p_bic."','".$p_note."','".$p_assIdAccount."',
//    '".api_now()."','".api_accountId()."')";
//  // execute query
//  $GLOBALS['db']->execute($query);
//  // build contact from last inserted id
//  $contact=api_contacts_contact($GLOBALS['db']->lastInsertedId(),TRUE);
//  // log event
//  $log=api_log(API_LOG_NOTICE,"contacts","contactCreated",
//   "{logs_contacts_contactCreated|".$contact->id."|".$p_name."}", // aggiungere quello che serve
//   $contact->id,"contacts/contacts_view.php?idContact=".$contact->id);
//  // extend companies
//  if(is_array($_POST['companies'])){
//   foreach($_POST['companies'] as $company){
//    $GLOBALS['db']->execute("INSERT INTO contacts_contacts_ext_companies
//     (idContact,idCompany) VALUES ('".$contact->id."','".$company."')");
//   }
//  }else{
//   // extend contact to current company
//   $GLOBALS['db']->execute("INSERT INTO contacts_contacts_ext_companies
//    (idContact,idCompany) VALUES ('".$contact->id."','".api_accountCompany()->id."')");
//  }
//  // alert
//  $alert="&alert=contactCreated&alert_class=alert-success&idLog=".$log->id;
// }
// // set id
// $_GET['idContact']=$contact->id;
// // check contact
// if(!$contact->id){
//  if($return){return FALSE;}
//  $alert="?alert=contactError&alert_class=alert-error";
//  exit(header("location: contacts_list.php".$alert));
// }
// // save primary address
// if(!contact_address_save(TRUE)&&$return){return FALSE;}
// // extend roles
// $GLOBALS['db']->execute("DELETE FROM contacts_contacts_ext_roles WHERE idContact='".$contact->id."'");
// if(is_array($_POST['roles'])){
//  foreach($_POST['roles'] as $role){
//   if(api_contacts_role($role)<>FALSE){
//    $GLOBALS['db']->execute("INSERT INTO contacts_contacts_ext_roles
//     (idContact,idRole) VALUES ('".$contact->id."','".$role."')");
//   }
//  }
// }
// // extend areas
// $GLOBALS['db']->execute("DELETE FROM contacts_contacts_ext_areas WHERE idContact='".$contact->id."'");
// if(is_array($_POST['areas'])){
//  foreach($_POST['areas'] as $area){
//   if(api_contacts_area($area)<>FALSE){
//    $GLOBALS['db']->execute("INSERT INTO contacts_contacts_ext_areas
//    (idContact,idArea) VALUES ('".$contact->id."','".$area."')");
//   }
//  }
// }
// if($return){return TRUE;}
// // redirect
// exit(header("location: contacts_view.php?idContact=".$contact->id.$alert));
//}
//
///**
// * Contact Manage
// */
//function contact_manage(){
// if(!api_checkPermission("contacts","contacts_manage")){api_die("accessDenied");}
// // get objects
// $contact=api_contacts_contact($_GET['idContact'],TRUE);
// // check objects
// if(!$contact->id){echo api_text("contactNotFound");return FALSE;}
// // companies
// $GLOBALS['db']->execute("DELETE FROM contacts_contacts_ext_companies WHERE idContact='".$contact->id."'");
// if(is_array($_POST['companies'])){
//  foreach($_POST['companies'] as $company){
//   $companies.=api_company($company)->name.", ";
//   $GLOBALS['db']->execute("INSERT INTO contacts_contacts_ext_companies
//    (idContact,idCompany) VALUES ('".$contact->id."','".$company."')");
//  }
// }
// // log event
// $log=api_log(API_LOG_NOTICE,"contacts","contactManaged",
//  "{logs_contacts_contactManaged|".$contact->id."|".$contact->name."|".substr($companies,0,-2)."}",
//  $contact->id,"contacts/contacts_view.php?idContact=".$contact->id);
// // redirect
// $alert="&alert=contactManaged&alert_class=alert-success&idLog=".$log->id;
// exit(header("location: contacts_view.php?idContact=".$contact->id.$alert));
//}
//
///**
// * Contact Delete
// *
// * @param string $action delete | undelete | remove
// */
//function contact_delete($action){
// if(!api_checkPermission("contacts","contacts_edit")){api_die("accessDenied");}
// // get objects
// $contact=api_contacts_contact($_GET['idContact'],TRUE);
// // check objects
// if(!$contact->id){echo api_text("contactNotFound");return FALSE;}
// // check address
// if($contact->id){
//  // build contact query
//  switch($action){
//   case "delete":
//    $query="UPDATE contacts_contacts SET del='1',updDate='".api_now()."',updIdAccount='".api_accountId()."' WHERE id='".$contact->id."'";
//    $log_action="contactDeleted";
//    break;
//   case "undelete":
//    $query="UPDATE contacts_contacts SET del='0',updDate='".api_now()."',updIdAccount='".api_accountId()."' WHERE id='".$contact->id."'";
//    $log_action="contactUndeleted";
//    break;
//   case "remove":
//    $query="DELETE FROM contacts_contacts WHERE id='".$contact->id."'";
//    $log_action="contactRemoved";
//    break;
//   default:$query=NULL;
//  }
//  // execute query
//  if($query){
//   $GLOBALS['db']->execute($query);
//   // log event
//   $log=api_log(API_LOG_WARNING,"contacts",$log_action,
//    "{logs_contacts_".$log_action."|".$contact->id."|".$contact->name."}",
//    $contact->id,"contacts/contacts_view.php?idContact=".$contact->id);
//   // alert
//   $alert="&alert=".$log_action."&alert_class=alert-success&idLog=".$log->id;
//  }else{$alert="&alert=contactError&alert_class=alert-error";}
// }else{$alert="&alert=contactError&alert_class=alert-error";}
// // redirect
// exit(header("location: contacts_view.php?idContact=".$contact->id.$alert));
//}
//
///**
// * Contact Import
// */
//function contact_import(){
// if(!api_checkPermission("contacts","contacts_manage")){api_die("accessDenied");}
// if(!file_exists("../uploads/uploads/contacts/tmp/import.csv")){echo api_text("csvNotFound");return FALSE;}
// // definitions
// $v_success=0;
// $v_errors=0;
// // parse csv file
// $csv_rows=api_parse_csv_file("../uploads/uploads/contacts/tmp/import.csv",',','"');
// // cycle csv contacts
// foreach($csv_rows as $contact){
//  // set server variables
//  $_GET['idContact']=NULL;
//  $contact['companies']=explode(",",$contact['companies']);
//  $contact['roles']=explode(",",$contact['roles']);
//  $contact['areas']=explode(",",$contact['areas']);
//  $_POST=$contact;
//  // call save contact function
//  if(contact_save(TRUE)){$v_success++;}else{$v_errors++;}
//  // call save referent function
//  for($i=1;$i<=3;$i++){
//   if(strlen($contact['r'.$i.'_firstname'])>0&&$_GET['idContact']>0&&$_GET['idAddress']>0){
//    $_POST['idAddress']=$_GET['idAddress'];
//    $_POST['firstname']=$contact['r'.$i.'_firstname'];
//    $_POST['lastname']=$contact['r'.$i.'_lastname'];
//    $_POST['role']=$contact['r'.$i.'_role'];
//    $_POST['mail']=$contact['r'.$i.'_mail'];
//    $_POST['phone']=$contact['r'.$i.'_phone'];
//    $_POST['mobile']=$contact['r'.$i.'_mobile'];
//    if(!contact_referent_save(TRUE)){$v_errors++;}
//   }
//  }
// }
// if($v_errors){
//  $log_typology=API_LOG_WARNING;
//  $alert_class="alert-warning";
// }else{
//  $log_typology=API_LOG_NOTICE;
//  $alert_class="alert-success";
// }
// // log event
// $log=api_log($log_typology,"contacts","contactImported",
//  "{logs_contacts_contactImported|".count($csv_rows)."|".$v_success."|".$v_errors."}",
//  NULL,"contacts/contacts_list.php");
// $alert="?alert=contactImported&alert_class=".$alert_class."&idLog=".$log->id;
// exit(header("location: contacts_list.php".$alert));
//}
//
//
///**
// * Contact Address Save
// *
// * @param booelan $return if true don't redirect but return
// * @return boolean if return is true return true if insert query run without errors
// */
//function contact_address_save($return=FALSE){
// if(!api_checkPermission("contacts","contacts_edit")){api_die("accessDenied");}
// // get objects
// $contact=api_contacts_contact($_GET['idContact'],TRUE);
// $address=api_contacts_address($_GET['idAddress'],TRUE);
// // check objects
// if(!$contact->id){if($return){return FALSE;}echo api_text("contactNotFound");return FALSE;}
// // aquire variables
// $p_typology=api_cleanNumber($_POST['typology']);
// $p_name=addslashes(api_cleanString(ucwords($_POST['name']),"/[^A-Za-zÀ-ÿ0-9-.' ]/"));
// $p_address=addslashes(api_cleanString(ucwords($_POST['address']),"/[^A-Za-zÀ-ÿ0-9-.' ]/"));
// $p_civic=addslashes(api_cleanString(mb_strtoupper($_POST['civic'],'UTF-8'),"/[^A-Z0-9]/"));
// $p_zip=api_cleanNumber($_POST['zip']);
// $p_city=addslashes(api_cleanString(ucwords($_POST['city']),"/[^A-Za-zÀ-ÿ0-9-.' ]/"));
// $p_district=addslashes(api_cleanString(ucwords($_POST['district']),"/[^A-Za-zÀ-ÿ0-9-.' ]/"));
// $p_region=addslashes(api_cleanString(ucwords($_POST['region']),"/[^A-Za-zÀ-ÿ0-9-.' ]/"));
// $p_country=addslashes(api_cleanString(mb_strtoupper($_POST['country'],'UTF-8'),"/[^A-Z]/"));
// $p_gps=addslashes(api_cleanNumber($_POST['gps'],"/[^0-9.,]/"));
// $p_mail=addslashes(api_cleanString(strtolower($_POST['mail']),"/[^a-z0-9-.@]/"));
// $p_phone=api_cleanNumber($_POST['phone_pre'].$_POST['phone'],"/[^0-9+]/");
// $p_fax=api_cleanNumber($_POST['fax_pre'].$_POST['fax'],"/[^0-9+]/");
// // check variables
// if(strlen($p_phone)==3){$p_phone=NULL;}
// if(strlen($p_fax)==3){$p_fax=NULL;}
// // lowercase
// $p_name=mb_strtolower($p_name,'UTF-8');
// $p_address=mb_strtolower($p_address,'UTF-8');
// $p_city=mb_strtolower($p_city,'UTF-8');
// if(strlen($p_district)==2){$p_district=mb_strtoupper($p_district,'UTF-8');}else{$p_district=mb_strtolower($p_district,'UTF-8');}
// if(strlen($p_region)==2){$p_region=mb_strtoupper($p_region,'UTF-8');}else{$p_region=mb_strtolower($p_region,'UTF-8');}
// // build query
// if($address->id){
//  $query="UPDATE contacts_addresses SET
//   idContact='".$contact->id."',
//   typology='".$p_typology."',
//   name='".$p_name."',
//   address='".$p_address."',
//   civic='".$p_civic."',
//   zip='".$p_zip."',
//   city='".$p_city."',
//   district='".$p_district."',
//   region='".$p_region."',
//   country='".$p_country."',
//   gps='".$p_gps."',
//   mail='".$p_mail."',
//   phone='".$p_phone."',
//   fax='".$p_fax."',
//   updDate='".api_now()."',
//   updIdAccount='".api_accountId()."'
//   WHERE id='".$address->id."'";
//  // execute query
//  $GLOBALS['db']->execute($query);
//  // log event
//  $log=api_log(API_LOG_NOTICE,"contacts","contactAddressUpdated",
//   "{logs_contacts_contactAddressUpdated|".$contact->id."|".$address->id."|".$contact->name."|".$p_name."}", // aggiungere quello che serve
//   $contact->id,"contacts/contacts_view.php?idContact=".$contact->id);
//  // return
//  if($return){return TRUE;}
//  // redirect
//  $alert="&alert=contactAddressUpdated&alert_class=alert-success&idLog=".$log->id;
//  exit(header("location: contacts_view.php?idContact=".$contact->id."&idAddress=".$address->id.$alert));
// }else{
//  $query="INSERT INTO contacts_addresses
//   (idContact,typology,name,address,civic,zip,city,district,region,country,gps,mail,phone,fax,addDate,addIdAccount) VALUES
//   ('".$contact->id."','".$p_typology."','".$p_name."','".$p_address."','".$p_civic."','".$p_zip."','".$p_city."',
//    '".$p_district."','".$p_region."','".$p_country."','".$p_gps."','".$p_mail."','".$p_phone."','".$p_fax."',
//    '".api_now()."','".api_accountId()."')";
//  // execute query
//  $GLOBALS['db']->execute($query);
//  // get last inserted id
//  $q_idAddress=$GLOBALS['db']->lastInsertedId();
//  $_GET['idAddress']=$q_idAddress;
//  if(!$q_idAddress&&$return){return FALSE;}
//  // log event
//  $log=api_log(API_LOG_NOTICE,"contacts","contactAddressCreated",
//   "{logs_contacts_contactAddressCreated|".$contact->id."|".$q_idAddress."|".$contact->name."|".$p_name."}", // aggiungere quello che serve
//   $contact->id,"contacts/contacts_view.php?idContact=".$contact->id);
//  // return
//  if($return){return TRUE;}
//  // redirect
//  $alert="&alert=contactAddressCreated&alert_class=alert-success&idLog=".$log->id;
//  exit(header("location: contacts_view.php?idContact=".$contact->id."&idAddress=".$q_idAddress.$alert));
// }
//}
//
///**
// * Contact Address Delete
// *
// * @param string $action delete | undelete | remove
// */
//function contact_address_delete($action){
// if(!api_checkPermission("contacts","contacts_edit")){api_die("accessDenied");}
// // get objects
// $address=api_contacts_address($_GET['idAddress'],TRUE);
// $contact=api_contacts_address($address->idContact,TRUE);
// // check objects
// if(!$address->id){echo api_text("addressNotFound");return FALSE;}
// // check address
// if($address->id){
//  // build contact query
//  switch($action){
//   case "delete":
//    $query="UPDATE contacts_addresses SET del='1',updDate='".api_now()."',updIdAccount='".api_accountId()."' WHERE id='".$address->id."'";
//    $log_action="contactAddressDeleted";
//    break;
//   case "undelete":
//    $query="UPDATE contacts_addresses SET del='0',updDate='".api_now()."',updIdAccount='".api_accountId()."' WHERE id='".$address->id."'";
//    $log_action="contactAddressUndeleted";
//    break;
//   case "remove":
//    $query="DELETE FROM contacts_addresses WHERE id='".$address->id."'";
//    $log_action="contactAddressRemoved";
//    break;
//   default:$query=NULL;
//  }
//  // execute query
//  if($query){
//   $GLOBALS['db']->execute($query);
//   // log event
//   $log=api_log(API_LOG_WARNING,"contacts",$log_action,
//    "{logs_contacts_".$log_action."|".$contact->id."|".$address->id."|".$contact->name."|".$address->name."}",
//    $address->idContact,"contacts/contacts_view.php?idContact=".$address->idContact);
//   // alert
//   $alert="&alert=".$log_action."&alert_class=alert-success&idLog=".$log->id;
//  }else{$alert="&alert=contactAddressError&alert_class=alert-error";}
// }else{$alert="&alert=contactAddressError&alert_class=alert-error";}
// // redirect
// exit(header("location: contacts_view.php?idContact=".$contact->id.$alert));
//}
//
//
///**
// * Contact Address Referent Save
// *
// * @param booelan $return if true don't redirect but return
// * @return boolean if return is true return true if insert query run without errors
// */
//function contact_referent_save($return=FALSE){
// if(!api_checkPermission("contacts","contacts_edit")){api_die("accessDenied");}
// // get objects
// $contact=api_contacts_contact($_GET['idContact'],TRUE);
// $referent=api_contacts_referent($_GET['idReferent'],TRUE);
// $address=api_contacts_address($_POST['idAddress'],TRUE);
// // check objects
// if(!$contact->id){if($return){return FALSE;}echo api_text("contactNotFound");return FALSE;}
// if(!$address->id){if($return){return FALSE;}echo api_text("addressNotFound");return FALSE;}
// // aquire variables
// $p_firstname=addslashes(api_cleanString(ucwords(mb_strtolower($_POST['firstname'],'UTF-8')),"/[^A-Za-zÀ-ÿ0-9-.' ]/"));
// $p_lastname=addslashes(api_cleanString(ucwords(mb_strtolower($_POST['lastname'],'UTF-8')),"/[^A-Za-zÀ-ÿ0-9-.' ]/"));
// $p_role=addslashes(api_cleanString(ucwords($_POST['role']),"/[^A-Za-zÀ-ÿ0-9-.' ]/"));
// $p_mail=addslashes(api_cleanString(mb_strtolower($_POST['mail'],'UTF-8'),"/[^a-z0-9-.@]/"));
// $p_phone=api_cleanNumber($_POST['phone_pre'].$_POST['phone'],"/[^0-9+]/");
// $p_mobile=api_cleanNumber($_POST['mobile_pre'].$_POST['mobile'],"/[^0-9+]/");
// //$p_note=addslashes(api_cleanString($_POST['note'],"/[^A-Za-zÀ-ÿ0-9-.' ]/"));
// $p_note=addslashes($_POST['note']);
// // check variables
// if(strlen($p_phone)==3){$p_phone=NULL;}
// if(strlen($p_mobile)==3){$p_mobile=NULL;}
// // lowercase
// $p_firstname=mb_strtolower($p_firstname,'UTF-8');
// $p_lastname=mb_strtolower($p_lastname,'UTF-8');
// $p_role=mb_strtolower($p_role,'UTF-8');
// // build query
// if($referent->id){
//  $query="UPDATE contacts_referents SET
//   idContact='".$contact->id."',
//   idAddress='".$address->id."',
//   firstname='".$p_firstname."',
//   lastname='".$p_lastname."',
//   role='".$p_role."',
//   mail='".$p_mail."',
//   phone='".$p_phone."',
//   mobile='".$p_mobile."',
//   note='".$p_note."',
//   updDate='".api_now()."',
//   updIdAccount='".api_accountId()."'
//   WHERE id='".$referent->id."'";
//  // execute query
//  $GLOBALS['db']->execute($query);
//  // log event
//  $log=api_log(API_LOG_NOTICE,"contacts","contactReferentUpdated",
//   "{logs_contacts_contactReferentUpdated|".$contact->id."|".$address->id."|".$referent->id."|".$contact->name."|".$p_firstname."|".$p_lastname."}",
//   $contact->id,"contacts/contacts_view.php?idContact=".$contact->id."&idAddress=".$address->id."&idReferent=".$referent->id);
//  if($return){return TRUE;}
//  // redirect
//  $alert="&alert=contactReferentUpdated&alert_class=alert-success&idLog=".$log->id;
//  exit(header("location: contacts_addresses_edit.php?idContact=".$contact->id."&idAddress=".$address->id.$alert));
// }else{
//  $query="INSERT INTO contacts_referents
//   (idContact,idAddress,firstname,lastname,role,mail,phone,mobile,note,addDate,addIdAccount) VALUES
//   ('".$contact->id."','".$address->id."','".$p_firstname."','".$p_lastname."','".$p_role."','".$p_mail."',
//    '".$p_phone."','".$p_mobile."','".$p_note."','".api_now()."','".api_accountId()."')";
//  // execute query
//  $GLOBALS['db']->execute($query);
//  // get last inserted id
//  $q_referent=$GLOBALS['db']->lastInsertedId();
//  // log event
//  $log=api_log(API_LOG_NOTICE,"contacts","contactReferentCreated",
//   "{logs_contacts_contactReferentCreated|".$contact->id."|".$address->id."|".$q_referent."|".$contact->name."|".$p_firstname."|".$p_lastname."}",
//   $contact->id,"contacts/contacts_view.php?idContact=".$contact->id."&idAddress=".$address->id."&idReferent=".$q_referent);
//  if($return){return TRUE;}
//  // redirect
//  $alert="&alert=contactReferentCreated&alert_class=alert-success&idLog=".$log->id;
//  exit(header("location: contacts_addresses_edit.php?idContact=".$contact->id."&idAddress=".$address->id."&idReferent=".$q_referent.$alert));
// }
//}
//
///**
// * Contact Address Referent Delete
// *
// * @param string $action delete | undelete | remove
// */
//function contact_referent_delete($action){
// if(!api_checkPermission("contacts","contacts_edit")){api_die("accessDenied");}
// // get objects
// $contact=api_contacts_contact($_GET['idContact'],TRUE);
// $address=api_contacts_address($_GET['idAddress'],TRUE);
// $referent=api_contacts_referent($_GET['idReferent'],TRUE);
// // check objects
// if(!$contact->id){echo api_text("contactNotFound");return FALSE;}
// if(!$address->id){echo api_text("addressNotFound");return FALSE;}
// // check area
// if($referent->id){
//  // build contact query
//  switch($action){
//   case "delete":
//    $query="UPDATE contacts_referents SET del='1',updDate='".api_now()."',updIdAccount='".api_accountId()."' WHERE id='".$referent->id."'";
//    $log_action="contactReferentDeleted";
//    break;
//   case "undelete":
//    $query="UPDATE contacts_referents SET del='0',updDate='".api_now()."',updIdAccount='".api_accountId()."' WHERE id='".$referent->id."'";
//    $log_action="contactReferentUndeleted";
//    break;
//   case "remove":
//    $query="DELETE FROM contacts_referents WHERE id='".$referent->id."'";
//    $log_action="contactReferentRemoved";
//    break;
//   default:$query=NULL;
//  }
//  // execute query
//  if($query){
//   $GLOBALS['db']->execute($query);
//   // log event
//   $log=api_log(API_LOG_WARNING,"contacts",$log_action,
//    "{logs_contacts_".$log_action."|".$contact->id."|".$address->id."|".$referent->id."|".$contact->name."|".$referent->firstname."|".$referent->lastname."}",
//    $contact->id,"contacts/contacts_view.php?idContact=".$contact->id."&idAddress=".$address->id);
//   // alert
//   $alert="&alert=".$log_action."&alert_class=alert-success&idLog=".$log->id;
//  }else{$alert="&alert=contactReferentError&alert_class=alert-error";}
// }else{$alert="&alert=contactReferentError&alert_class=alert-error";}
// // redirect
// exit(header("location: contacts_addresses_edit.php?idContact=".$contact->id."&idAddress=".$address->id.$alert));
//}
//
///**
// * Contact Attachment Upload
// */
//function contact_attachment_upload(){
// if(!api_checkPermission("contacts","contacts_edit")){api_die("accessDenied");}
// // get objects
// $contact=api_contacts_contact($_GET['idContact']);
// // acquire variables
// $p_description=addslashes($_POST['description']);
// $p_tags=addslashes($_POST['tags']);
// // check
// if($contact->id){
//  $file=api_file_upload($_FILES['file'],"contacts_attachments",NULL,NULL,$p_description,$p_tags,FALSE,NULL,TRUE,"contacts/attachments");
//  if($file->id){
//   $GLOBALS['db']->execute("UPDATE contacts_attachments SET idContact='".$contact->id."' WHERE id='".$file->id."'");
//   // log event
//   $log=api_log(API_LOG_NOTICE,"contacts","contactsAttachmentUploaded",
//    "{logs_contacts_contactsAttachmentUploaded|".$contact->id."|".$file->id."|".$contact->name."|".$file->name."|".$file->description."}",
//    $contact->id,"contacts/contacts_view.php?idContact=".$contact->id);
//   // redirect
//   $alert="&alert=contactsAttachmentUploaded&alert_class=alert-success&idLog=".$log->id;
//   exit(header("location: contacts_view.php?idContact=".$contact->id.$alert));
//  }else{
//   // redirect
//   $alert="&alert=contactsAttachmentError&alert_class=alert-error";
//   exit(header("location: contacts_view.php?idContact=".$contact->id.$alert));
//  }
// }else{
//  // redirect
//  $alert="?alert=contactError&alert_class=alert-error";
//  exit(header("location: contacts_list.php".$alert));
// }
//}
//
///**
// * Contact Attachment Download
// */
//function contact_attachment_download(){
// if(!api_checkPermission("contacts","contacts_view")){api_die("accessDenied");}
// // download file from database
// api_file_download($_GET['idFile'],"contacts_attachments",NULL,FALSE,"contacts/attachments");
//}
//
///**
// * Contact Attachment Delete
// */
//function contact_attachment_delete(){
// if(!api_checkPermission("contacts","contacts_edit")){api_die("accessDenied");}
// // get objects
// $contact=api_contacts_contact($_GET['idContact']);
// $file=api_file($_GET['idFile'],"contacts_attachments");
// // delete file from database
// if($file->id>0){
//  api_file_delete($_GET['idFile'],"contacts_attachments","contacts/attachments");
//  // log event
//  $log=api_log(API_LOG_WARNING,"contacts","contactAttachmentDeleted",
//   "{logs_contacts_contactAttachmentDeleted|".$contact->id."|".$file->id."|".$file->name."|".$file->description."}",
//   $contact->id,"contacts/contacts_view.php?idContact=".$contact->id);
//  // alert
//  $alert="&alert=contactAttachmentDeleted&alert_class=alert-warning&idLog=".$log->id;
// }else{
//  // alert
//  $alert="&alert=contactAttachmentError&alert_class=alert-error";
// }
// // redirect
// exit(header("location: contacts_view.php?idContact=".$contact->id.$alert));
//}
//
//
///**
// * Role Save
// */
//function role_save(){
// if(!api_checkPermission("contacts","roles_manage")){api_die("accessDenied");}
// // get objects
// $role=api_contacts_role($_GET['idRole']);
// // acquire contact
// $p_name=addslashes(api_cleanString($_POST['name'],"/[^A-Za-zÀ-ÿ0-9-' ]/"));
// $p_description=addslashes(api_cleanString($_POST['description'],"/[^A-Za-zÀ-ÿ0-9-' ]/"));
// // build contact query
// if($role->id){
//  $query="UPDATE contacts_roles SET
//   name='".$p_name."',
//   description='".$p_description."',
//   updDate='".api_now()."',
//   updIdAccount='".api_accountId()."'
//   WHERE id='".$role->id."'";
//  // execute query
//  $GLOBALS['db']->execute($query);
//  // log event
//  $log=api_log(API_LOG_NOTICE,"contacts","roleUpdated",
//   "{logs_contacts_roleUpdated|".$role->id."|".$p_name."|".$p_description."}",
//   $role->id,"contacts/roles_list.php?idRole=".$role->id);
//  // redirect
//  $alert="&alert=roleUpdated&alert_class=alert-success&idLog=".$log->id;
//  exit(header("location: roles_list.php?idRole=".$role->id.$alert));
// }else{
//  $query="INSERT INTO contacts_roles
//   (name,description,addDate,addIdAccount) VALUES
//   ('".$p_name."','".$p_description."','".api_now()."','".api_accountId()."')";
//  // execute query
//  $GLOBALS['db']->execute($query);
//  // build contact from last inserted id
//  $q_idRole=$GLOBALS['db']->lastInsertedId();
//  // log event
//  $log=api_log(API_LOG_NOTICE,"contacts","roleCreated",
//   "{logs_contacts_roleCreated|".$q_idRole."|".$p_name."|".$p_description."}",
//   $q_idRole,"contacts/roles_list.php?idRole=".$q_idRole);
//  // redirect
//  $alert="&alert=roleCreated&alert_class=alert-success&idLog=".$log->id;
//  exit(header("location: roles_list.php?idRole=".$q_idRole.$alert));
// }
//}
//
///**
// * Role Delete
// *
// * @param string $action delete | undelete | remove
// */
//function role_delete($action){
// if(!api_checkPermission("contacts","roles_manage")){api_die("accessDenied");}
// // get objects
// $role=api_contacts_role($_GET['idRole']);
// // check area
// if($role->id){
//  // build contact query
//  switch($action){
//   case "delete":
//    $query="UPDATE contacts_roles SET del='1',updDate='".api_now()."',updIdAccount='".api_accountId()."' WHERE id='".$role->id."'";
//    $log_action="roleDeleted";
//    break;
//   case "undelete":
//    $query="UPDATE contacts_roles SET del='0',updDate='".api_now()."',updIdAccount='".api_accountId()."' WHERE id='".$role->id."'";
//    $log_action="roleUndeleted";
//    break;
//   case "remove":
//    $query="DELETE FROM contacts_roles WHERE id='".$role->id."'";
//    $log_action="roleRemoved";
//    break;
//   default:$query=NULL;
//  }
//  // execute query
//  if($query){
//   $GLOBALS['db']->execute($query);
//   // log event
//   $log=api_log(API_LOG_WARNING,"contacts",$log_action,
//    "{logs_contacts_".$log_action."|".$role->id."|".$role->name."|".$role->description."}",
//    $role->id,"contacts/areas_list.php?idRole=".$role->id);
//   // alert
//   $alert="&alert=".$log_action."&alert_class=alert-success&idLog=".$log->id;
//  }else{$alert="&alert=roleError&alert_class=alert-error";}
// }else{$alert="&alert=roleError&alert_class=alert-error";}
// // redirect
// exit(header("location: roles_list.php?idRole=".$role->id.$alert));
//}
//
///**
// * Role Translation Save
// */
//function role_txt_save(){
// if(!api_checkPermission("contacts","roles_manage")){api_die("accessDenied");}
// // get objects
// $role=api_contacts_role($_GET['idRole']);
// $role_txt=$GLOBALS['db']->queryUniqueObject("SELECT * FROM contacts_roles_txt WHERE id='".$_GET['idRoleTxt']."'");
// // check objects
// if(!$role->id){echo api_text("roleNotFound");return FALSE;}
// // acquire contact
// $p_language=strtoupper(addslashes($_POST['language']));
// $p_name=addslashes(api_cleanString($_POST['name'],"/[^A-Za-zÀ-ÿ0-9-' ]/"));
// $p_description=addslashes(api_cleanString($_POST['description'],"/[^A-Za-zÀ-ÿ0-9-' ]/"));
// // build contact query
// if($role_txt->id){
//  $query="UPDATE contacts_roles_txt SET
//   language='".$p_language."',
//   name='".$p_name."',
//   description='".$p_description."'
//   WHERE id='".$role_txt->id."'";
//  // execute query
//  $GLOBALS['db']->execute($query);
//  // update areas
//  $GLOBALS['db']->execute("UPDATE contacts_roles SET updDate='".api_now()."',updIdAccount='".api_accountId()."' WHERE id='".$role->id."'");
//  // log event
//  $log=api_log(API_LOG_NOTICE,"contacts","roleTxtUpdated",
//   "{logs_contacts_roleTxtUpdated|".$role->id."|".$role_txt->id."|".$p_language."|".$p_name."|".$p_description."}",
//   $role->id,"contacts/roles_list.php?idRole=".$role->id);
//  // alert
//  $alert="&alert=roleTxtUpdated&alert_class=alert-success&idLog=".$log->id;
// }else{
//  $query="INSERT INTO contacts_roles_txt
//   (idRole,language,name,description) VALUES
//   ('".$role->id."','".$p_language."','".$p_name."','".$p_description."')";
//  // execute query
//  $GLOBALS['db']->execute($query);
//  // build contact from last inserted id
//  $q_idRoleTxt=$GLOBALS['db']->lastInsertedId();
//  // update areas
//  $GLOBALS['db']->execute("UPDATE contacts_roles SET updDate='".api_now()."',updIdAccount='".api_accountId()."' WHERE id='".$role->id."'");
//  // log event
//  $log=api_log(API_LOG_NOTICE,"contacts","roleTxtCreated",
//   "{logs_contacts_roleTxtCreated|".$role->id."|".$q_idRoleTxt."|".$p_language."|".$p_name."|".$p_description."}",
//   $role->id,"contacts/roles_list.php?idRole=".$role->id);
//  // alert
//  $alert="&alert=roleTxtCreated&alert_class=alert-success&idLog=".$log->id;
// }
// // redirect
// exit(header("location: roles_edit.php?idRole=".$role->id.$alert));
//}
//
///**
// * Role Translation Delete
// */
//function role_txt_delete(){
// if(!api_checkPermission("contacts","roles_manage")){api_die("accessDenied");}
// // get objects
// $role=api_contacts_role($_GET['idRole']);
// $role_txt=$GLOBALS['db']->queryUniqueObject("SELECT * FROM contacts_roles_txt WHERE id='".$_GET['idRoleTxt']."'");
// // check objects
// if(!$role->id){echo api_text("roleaNotFound");return FALSE;}
// // check area
// if($role_txt->id){
//  // execute query
//  $GLOBALS['db']->execute("DELETE FROM contacts_roles_txt WHERE id='".$role_txt->id."'");
//  // log event
//  $log=api_log(API_LOG_NOTICE,"contacts","roleTxtDeleted",
//   "{logs_contacts_roleTxtDeleted|".$role->id."|".$role_txt->id."|".$role_txt->name."|".$role_txt->description."}",
//   $role->id,"contacts/roles_list.php?idRole=".$role->id);
//  // alert
//  $alert="&alert=roleTxtDeleted&alert_class=alert-success&idLog=".$log->id;
// }else{$alert="&alert=roleError&alert_class=alert-error";}
// // redirect
// exit(header("location: roles_edit.php?idRole=".$role->id.$alert));
//}
//
//
///**
// * Area Save
// */
//function area_save(){
// if(!api_checkPermission("contacts","areas_manage")){api_die("accessDenied");}
// // get objects
// $area=api_contacts_area($_GET['idArea']);
// // acquire contact
// $p_name=addslashes(api_cleanString($_POST['name'],"/[^A-Za-zÀ-ÿ0-9-' ]/"));
// $p_description=addslashes(api_cleanString($_POST['description'],"/[^A-Za-zÀ-ÿ0-9-' ]/"));
// // build contact query
// if($area->id){
//  $query="UPDATE contacts_areas SET
//   name='".$p_name."',
//   description='".$p_description."',
//   updDate='".api_now()."',
//   updIdAccount='".api_accountId()."'
//   WHERE id='".$area->id."'";
//  // execute query
//  $GLOBALS['db']->execute($query);
//  // log event
//  $log=api_log(API_LOG_NOTICE,"contacts","areaUpdated",
//   "{logs_contacts_areaUpdated|".$area->id."|".$p_name."|".$p_description."}",
//   $area->id,"contacts/areas_list.php?idArea=".$area->id);
//  // redirect
//  $alert="&alert=areaUpdated&alert_class=alert-success&idLog=".$log->id;
//  exit(header("location: areas_list.php?idArea=".$area->id.$alert));
// }else{
//  $query="INSERT INTO contacts_areas
//   (name,description,addDate,addIdAccount) VALUES
//   ('".$p_name."','".$p_description."','".api_now()."','".api_accountId()."')";
//  // execute query
//  $GLOBALS['db']->execute($query);
//  // build contact from last inserted id
//  $q_idArea=$GLOBALS['db']->lastInsertedId();
//  // log event
//  $log=api_log(API_LOG_NOTICE,"contacts","areaCreated",
//   "{logs_contacts_areaCreated|".$q_idArea."|".$p_name."|".$p_description."}",
//   $q_idArea,"contacts/areas_list.php?idArea=".$q_idArea);
//  // redirect
//  $alert="&alert=areaCreated&alert_class=alert-success&idLog=".$log->id;
//  exit(header("location: areas_list.php?idArea=".$q_idArea.$alert));
// }
//}
//
///**
// * Area Delete
// *
// * @param string $action delete | undelete | remove
// */
//function area_delete($action){
// if(!api_checkPermission("contacts","areas_manage")){api_die("accessDenied");}
// // get objects
// $area=api_contacts_area($_GET['idArea']);
// // check area
// if($area->id){
//  // build contact query
//  switch($action){
//   case "delete":
//    $query="UPDATE contacts_areas SET del='1',updDate='".api_now()."',updIdAccount='".api_accountId()."' WHERE id='".$area->id."'";
//    $log_action="areaDeleted";
//    break;
//   case "undelete":
//    $query="UPDATE contacts_areas SET del='0',updDate='".api_now()."',updIdAccount='".api_accountId()."' WHERE id='".$area->id."'";
//    $log_action="areaUndeleted";
//    break;
//   case "remove":
//    $query="DELETE FROM contacts_areas WHERE id='".$area->id."'";
//    $log_action="areaRemoved";
//    break;
//   default:$query=NULL;
//  }
//  // execute query
//  if($query){
//   $GLOBALS['db']->execute($query);
//   // log event
//   $log=api_log(API_LOG_WARNING,"contacts",$log_action,
//    "{logs_contacts_".$log_action."|".$area->id."|".$area->name."|".$area->description."}",
//    $area->id,"contacts/areas_list.php?idArea=".$area->id);
//   // alert
//   $alert="&alert=".$log_action."&alert_class=alert-success&idLog=".$log->id;
//  }else{$alert="&alert=areaError&alert_class=alert-error";}
// }else{$alert="&alert=areaError&alert_class=alert-error";}
// // redirect
// exit(header("location: areas_list.php?idArea=".$area->id.$alert));
//}
//
///**
// * Area Translation Save
// */
//function area_txt_save(){
// if(!api_checkPermission("contacts","areas_manage")){api_die("accessDenied");}
// // get objects
// $area=api_contacts_area($_GET['idArea']);
// $area_txt=$GLOBALS['db']->queryUniqueObject("SELECT * FROM contacts_areas_txt WHERE id='".$_GET['idAreaTxt']."'");
// // check objects
// if(!$area->id){echo api_text("areaNotFound");return FALSE;}
// // acquire contact
// $p_language=strtoupper(addslashes($_POST['language']));
// $p_name=addslashes(api_cleanString($_POST['name'],"/[^A-Za-zÀ-ÿ0-9-' ]/"));
// $p_description=addslashes(api_cleanString($_POST['description'],"/[^A-Za-zÀ-ÿ0-9-' ]/"));
// // build contact query
// if($area_txt->id){
//  $query="UPDATE contacts_areas_txt SET
//   language='".$p_language."',
//   name='".$p_name."',
//   description='".$p_description."'
//   WHERE id='".$area_txt->id."'";
//  // execute query
//  $GLOBALS['db']->execute($query);
//  // update areas
//  $GLOBALS['db']->execute("UPDATE contacts_areas SET updDate='".api_now()."',updIdAccount='".api_accountId()."' WHERE id='".$area->id."'");
//  // log event
//  $log=api_log(API_LOG_NOTICE,"contacts","areaTxtUpdated",
//   "{logs_contacts_areaTxtUpdated|".$area->id."|".$area_txt->id."|".$p_language."|".$p_name."|".$p_description."}",
//   $area->id,"contacts/areas_list.php?idArea=".$area->id);
//  // alert
//  $alert="&alert=areaTxtUpdated&alert_class=alert-success&idLog=".$log->id;
// }else{
//  $query="INSERT INTO contacts_areas_txt
//   (idArea,language,name,description) VALUES
//   ('".$area->id."','".$p_language."','".$p_name."','".$p_description."')";
//  // execute query
//  $GLOBALS['db']->execute($query);
//  // build contact from last inserted id
//  $q_idAreaTxt=$GLOBALS['db']->lastInsertedId();
//  // update areas
//  $GLOBALS['db']->execute("UPDATE contacts_areas SET updDate='".api_now()."',updIdAccount='".api_accountId()."' WHERE id='".$area->id."'");
//  // log event
//  $log=api_log(API_LOG_NOTICE,"contacts","areaTxtCreated",
//   "{logs_contacts_areaTxtCreated|".$area->id."|".$q_idAreaTxt."|".$p_language."|".$p_name."|".$p_description."}",
//   $area->id,"contacts/areas_list.php?idArea=".$area->id);
//  // alert
//  $alert="&alert=areaTxtCreated&alert_class=alert-success&idLog=".$log->id;
// }
// // redirect
// exit(header("location: areas_edit.php?idArea=".$area->id.$alert));
//}
//
///**
// * Area Translation Delete
// */
//function area_txt_delete(){
// if(!api_checkPermission("contacts","areas_manage")){api_die("accessDenied");}
// // get objects
// $area=api_contacts_area($_GET['idArea']);
// $area_txt=$GLOBALS['db']->queryUniqueObject("SELECT * FROM contacts_areas_txt WHERE id='".$_GET['idAreaTxt']."'");
// // check objects
// if(!$area->id){echo api_text("areaNotFound");return FALSE;}
// // check area
// if($area_txt->id){
//  // execute query
//  $GLOBALS['db']->execute("DELETE FROM contacts_areas_txt WHERE id='".$area_txt->id."'");
//  // log event
//  $log=api_log(API_LOG_NOTICE,"contacts","areaTxtDeleted",
//   "{logs_contacts_areaTxtDeleted|".$area->id."|".$area_txt->id."|".$area_txt->name."|".$area_txt->description."}",
//   $area->id,"contacts/areas_list.php?idArea=".$area->id);
//  // alert
//  $alert="&alert=areaTxtDeleted&alert_class=alert-success&idLog=".$log->id;
// }else{$alert="&alert=areaError&alert_class=alert-error";}
// // redirect
// exit(header("location: areas_edit.php?idArea=".$area->id.$alert));
//}

?>