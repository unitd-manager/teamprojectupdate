<?
$cpCfg = array();

$cpCfg['m.hms.currentYear'] = date('Y'); 
$cpCfg['cp.forhms']      = 0; // Universal config only for Edutrust Institutions

//------------ CONTACT -------------//
$cpCfg['m.hms.contact.showEvent']        = 0;
$cpCfg['m.hms.contact.showInterest']     = 1;
$cpCfg['m.hms.contact.showAttachment']   = 0;
$cpCfg['m.hms.contact.showOrders']       = false;
$cpCfg['m.hms.contact.hasWebLogin']      = 1;
$cpCfg['m.hms.contact.showUsername']     = 0;
$cpCfg['m.hms.contact.showStaffDetail']  = 0;
$cpCfg['m.hms.contact.hasCompanyTable']  = 0;
$cpCfg['m.hms.contact.showContentLink']  = false;
$cpCfg['m.hms.contact.hasSalutation']    = false;
$cpCfg['m.hms.contact.showInterestInImport'] = false;
$cpCfg['m.hms.contact.showLangPrefernce'] = false;
$cpCfg['m.hms.contact.flagInvalidEmails'] = false;
$cpCfg['m.hms.contact.specialSearchArr'] = array(
     "Subscribed"
    ,"Not-Subscribed"
    ,"Flagged"
    ,"Not-Flagged"
);

//------------ ORDER -------------//
$cpCfg['m.ecommerce.order.statusArr'] = array (
     'New'
    ,'Due'
    ,'Paid'
    ,'Cancelled'
    ,'Pending'
);

/************** PURCHASE ORDER *************/
/*$cpCfg['m.hms.purchaseOrder.statusArr'] = array (
     'new'
    ,'confirmed'
    ,'closed'
    ,'on hold'
    ,'cancelled'
);*/

/************** PAYROLL *************/
$cpCfg['m.payroll.jobInformation.showCategory'] = 0;
$cpCfg['m.project.hasMultipleCompanyAddress']   = 0;
$cpCfg['m.project.employee.showEvent']          = 0;

$cpCfg['m.hms.purchaseOrder.statusArr'] = array (
     'In progress'
    ,'sent to supplier'
    ,'partially recieved'
    ,'closed'
    ,'on hold'
    ,'cancelled'
);

$cpCfg['m.hms.purchaseOrder.poProductStatusArr'] = array (
     'print'
    ,'inprogress'
    ,'delivered'
    ,'paid'
);

$cpCfg['m.hms.purchaseOrder.statusprodArr'] = array (
     'New'
    ,'Due'
    ,'Paid'
    ,'Cancelled'
    ,'Pending'
);

/************** Labs *************/
$cpCfg['m.hms.labs.statusArr'] = array (
     'new'
    ,'confirmed'
    ,'closed'
    ,'on hold'
    ,'cancelled'
);

$cpCfg['m.hms.labs.statusprodArr'] = array (
     'New'
    ,'Due'
    ,'Paid'
    ,'Cancelled'
    ,'Pending'
);

$cpCfg['m.hms.labs.labsProductStatusArr'] = array (
     'print'
    ,'inprogress'
    ,'delivered'
    ,'paid'
);

//------------ CATEGORY TYPE--------------//
$cpCfg['m.webBasic.category.recordTypeArr'] = array (
     'Content'
    ,'Enquiry Form'
    ,'Product'
);

return $cpCfg;
