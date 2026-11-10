<?
$cpCfg = array();

$cpCfg['cp.isAdmin']         = true;
$cpCfg['cp.multiLang']       = 0;
$cpCfg['cp.superAdminUGId']  = 1;

$cpCfg['cp.adminHasRememberLogin'] = true;

//$cpCfg['cp.jqVersion'] = '1.8.2';

//----------------------- THEME RELATED -----------------------//
$cpCfg['cp.defaultPageCssClass']        = 'hideboth';
$cpCfg['cp.fullWidthTemplte']           = false;
$cpCfg['cp.fixMainPanelHeightInList']   = true;
$cpCfg['cp.fixMainPanelHeightInDetail'] = false;
$cpCfg['cp.showActionPanelInHeader']    = false;

$cpCfg['cp.footerCompanyLink']       = "<a href='http://www.cubosale.in' target='_blank'>Usoftsolutions</a>";
$cpCfg['cp.autoLoginToIntranet']     = 0;
$cpCfg['cp.allowCKFinderFileUpload'] = isset($cpCfg['cp.allowCKFinderFileUpload']) ? $cpCfg['cp.allowCKFinderFileUpload'] : true;
$cpCfg['cp.showPagerPanelInFooter']  = false;

//======================= REPORTS ============================//
$cpCfg['cp.repPrintLogoInLeft']      = true;
$cpCfg['cp.repPrintLogoInRight']     = false;
$cpCfg['cp.repPrintAddressInRight']  = false;
$cpCfg['cp.repPrintAddressInFooter'] = true;

//======================= LANGUAGE ============================//
$cpCfg['cp.displayLanguageFromVL'] = 0;
$cpCfg['cp.availableLanguages'] = array(
    'eng' => 'English'
);

//=========== LANGUAGE FOR ADMIN INTERFACE ====================//
$cpCfg['cp.hasAdminInterfaceLangs'] = false;
$cpCfg['cp.adminInterfaceLangs'] = array(
    'eng' => 'English',
);

//======================= ACCESS MODULE ============================//
$cpCfg['cp.hasAccessModule']            = 0;
$cpCfg['cp.hasAccessModuleOtherAction'] = 0;
$cpCfg['cp.hasAccessModuleContent']     = 0;
$cpCfg['cp.hasMultiUsergroupPerStaff']  = 0;
$cpCfg['cp.captureAutoLogin']           = false;
$cpCfg['cp.attendanceTable']            = 'staff_attendance';

$cpCfg['m.core.staff.hasPasswordSalt'] = false;
$cpCfg['m.core.userGroup.userGroupTypeArr'] = array(
     'Super Administrator'
    ,'Administrator'
    ,'User'
);

//*** default set the name of the table for solutions which does not use access module
$cpCfg['cp.modAccessStaffTable']      = "staff";
$cpCfg['cp.modAccessUserGroupTable']  = "user_group";
$cpCfg['cp.modAccessStaffIdLabel']    = "staff_id";
$cpCfg['cp.hasFirstRoomValueInStaff'] = 0;
$cpCfg['cp.hasCountryInStaff']        = 0;

/*** used when the data to be displayed should belong to the country of the logged in staff ref: asurion ***/
$cpCfg['cp.showOnlyDataOfStaffsCountry'] = 0;
$cpCfg['cp.pagetoReturnAfterSave']       = 'list';

$cpCfg['cp.specialSearchArr'] = array(
     'Published'
    ,'Not-Published'
    ,'Flagged'
    ,'Not-Flagged'
);

//======================= MODULE GROUPS ============================//
$cpCfg['cp.hasProjectMg']   = 0;
$cpCfg['cp.hasDirectoryMg'] = 0;

//======================= SITES ============================//
$cpCfg['cp.availableThemes'] = array(
    'Default'
);

$cpCfg['cp.availableSkins'] = array(
    'Default'
);

//============ Password Salting in contact module
$cpCfg['cp.hasPasswordSalt']  = false;

/** for solutions which requires dashboard, redefine this array in local/lib/override.php,
    refer mod/common_dashboard/model.php
***/
$cpCfg['cp.dashboardArr'] = array();

//============ stock = no are exclude from the reports, used in blossoms and vmsj =====//
$cpCfg['cp.excludeStock'] = 0;

$cpCfg['cp.vunitedLoginText'] = 0;

$cpCfg['cp.hospitalManagementLoginText'] = 0;

return $cpCfg;
