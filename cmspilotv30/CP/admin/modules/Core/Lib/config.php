<?
$cpCfg = array();

/********************* STAFF ******************/
$cpCfg['m.core.staff.showFldSensitiveDetails'] = 0; // Whether to show budget commission for specific staff in Stepworks
$cpCfg['m.core.hasStaffGroup']                 = 0;
$cpCfg['m.core.staff.showStaffDescription']    = 0;
$cpCfg['m.core.staff.showCountry']             = 0;
$cpCfg['m.core.staff.showUserGroup']           = 0;
$cpCfg['m.core.staff.showShortCode']           = 0;
$cpCfg['m.core.staff.hasZipCode']              = false;
$cpCfg['m.core.staff.hasChangePasswordNextLogin']  = false;
$cpCfg['m.core.staff.hasStaffCommission']      = false;
$cpCfg['m.core.staff.hasCommissionDetails']    = false;

/********************* VALUELIST ******************/
$cpCfg['m.core.valuelist.hasGroupHead']      = 0;
$cpCfg['m.core.valuelist.hasDescription']    = 0;
$cpCfg['m.core.valuelist.hasCode']           = 0;
$cpCfg['m.core.valuelist.recordTypeArr']     = array(
     'enquiryStatus' => 'Enquiry Status'
    ,'enquiryType'   => 'Enquiry Type'
);

return $cpCfg;