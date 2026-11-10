<?
$cpCfg = array();

$cpCfg['m.labsg.currentYear'] = date('Y'); 
$cpCfg['cp.forlabsg']      = 0; // Universal config only for Edutrust Institutions

//------------ CONTACT -------------//
$cpCfg['m.labsg.contact.showEvent']        = 0;
$cpCfg['m.labsg.contact.showInterest']     = 1;
$cpCfg['m.labsg.contact.showAttachment']   = 0;
$cpCfg['m.labsg.contact.showOrders']       = false;
$cpCfg['m.labsg.contact.hasWebLogin']      = 1;
$cpCfg['m.labsg.contact.showUsername']     = 0;
$cpCfg['m.labsg.contact.showStaffDetail']  = 0;
$cpCfg['m.labsg.contact.hasCompanyTable']  = 0;
$cpCfg['m.labsg.contact.showContentLink']  = false;
$cpCfg['m.labsg.contact.hasSalutation']    = false;
$cpCfg['m.labsg.contact.showInterestInImport'] = false;
$cpCfg['m.labsg.contact.showLangPrefernce'] = false;
$cpCfg['m.labsg.contact.flagInvalidEmails'] = false;
$cpCfg['m.labsg.contact.specialSearchArr'] = array(
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
$cpCfg['m.labsg.purchaseOrder.statusArr'] = array (
     'new'
    ,'confirmed'
    ,'closed'
    ,'on hold'
    ,'cancelled'
);

$cpCfg['m.labsg.purchaseOrder.poProductStatusArr'] = array (
     'print'
    ,'inprogress'
    ,'delivered'
    ,'paid'
);

$cpCfg['m.labsg.purchaseOrder.statusprodArr'] = array (
     'New'
    ,'Due'
    ,'Paid'
    ,'Cancelled'
    ,'Pending'
);

/************** Labs *************/
$cpCfg['m.labsg.labs.statusArr'] = array (
     'new'
    ,'confirmed'
    ,'closed'
    ,'on hold'
    ,'cancelled'
);

$cpCfg['m.labsg.labs.statusprodArr'] = array (
     'New'
    ,'Due'
    ,'Paid'
    ,'Cancelled'
    ,'Pending'
);

$cpCfg['m.labsg.labs.labsProductStatusArr'] = array (
     'print'
    ,'inprogress'
    ,'delivered'
    ,'paid'
);

return $cpCfg;
