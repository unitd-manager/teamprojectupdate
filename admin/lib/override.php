<?
$cpCfg            = Zend_Registry::get('cpCfg');
$tv               = Zend_Registry::get('tv');
$widgetsArrAccess = Zend_Registry::get('widgetsArrAccess');
$widgetsArr       = Zend_Registry::get('widgetsArr');

$dashboard = getCPModuleObj('common_dashboard')->model;
$themePath = CP_THEMES_PATH_LOCAL_ALIAS . $cpCfg['cp.theme'] . '/';

$arr = array();
if ($cpCfg['cp.hasAccessModule'] && isLoggedInAdmin()) {
    if ($widgetsArrAccess['enggCrm_dashboardTopPanel']['hasAccess']) {
        $arr[] = $dashboard->getDasboardObj('enggCrm_dashboardTopPanel', array('cssClass' => '', 'subClass' => 'subcr p0 mr0'));
    }

     if ($widgetsArrAccess['project_invoiceSummaryChart']['hasAccess']) {
        $arr[] = $dashboard->getDasboardObj('enggCrm_overallSalesSummary', array('cssClass' => '', 'subClass' => 'subcr p0 mr0'));
    }
    
    if ($widgetsArrAccess['enggCrm_projectReport']['hasAccess']) {
        $arr[] = $dashboard->getDasboardObj('enggCrm_projectReport', array('cssClass' => '', 'subClass' => 'subcr p0 mr0'));
    }

    
    if ($widgetsArrAccess['enggCrm_materialRequestedList']['hasAccess']) {
        $arr[] = $dashboard->getDasboardObj('enggCrm_materialRequestedList', array('cssClass' => '', 'subClass' => 'subcr p0 mr0'));
    }

    if ($widgetsArrAccess['enggCrm_materialDelivered']['hasAccess']) {
        $arr[] = $dashboard->getDasboardObj('enggCrm_materialDelivered', array('cssClass' => '', 'subClass' => 'subcr p0 mr0'));
    }

    

    if ($widgetsArrAccess['enggCrm_tenderSummary']['hasAccess']) {
        $arr[] = $dashboard->getDasboardObj('enggCrm_tenderSummary', array('cssClass' => '', 'subClass' => 'subcr p0 mr0'));
    }

    if ($widgetsArrAccess['enggCrm_taskFromAdmin']['hasAccess']) {
        $arr[] = $dashboard->getDasboardObj('enggCrm_taskFromAdmin', array('cssClass' => '', 'subClass' => 'subcr p0 mr0'));
    }

    if ($widgetsArrAccess['enggCrm_invoiceChartByMonth']['hasAccess']) {
        $arr[] = $dashboard->getDasboardObj('enggCrm_invoiceChartByMonth', array('cssClass' => '', 'subClass' => 'subcr p0 mr0'));
    }
     if ($widgetsArrAccess['payroll_employeeSummary']['hasAccess']) {
        $arr[] = $dashboard->getDasboardObj('payroll_employeeSummary', array('cssClass' => 'c50l', 'subClass' => 'subcr p0 mr0'));
    }

    if ($widgetsArrAccess['payroll_passportExpiry']['hasAccess']) {
        $arr[] = $dashboard->getDasboardObj('payroll_passportExpiry', array('cssClass' => 'c50r', 'subClass' => 'subcr p0 mr0'));
    }

    if ($widgetsArrAccess['payroll_workpermitExpiry']['hasAccess']) {
        $arr[] = $dashboard->getDasboardObj('payroll_workpermitExpiry', array('cssClass' => 'c50l', 'subClass' => 'subcr p0 mr0'));
    }
}

//$arr[] = $dashboard->getDasboardObj('project_projectSummary', array('cssClass' => 'c50r', 'subClass' => 'subcr p0 mr0'));
//$arr[] = $dashboard->getDasboardObj('enggCrm_salesByMonthChart', array('cssClass' => 'c50l', 'subClass' => 'subcr p0 mr0'));

$cpCfg['cp.dashboardArr'] = $arr;

CP_Common_Lib_Registry::arrayMerge('cpCfg', $cpCfg);

$tv = Zend_Registry::get('tv');
array_push($tv['protSiteSpActionExceptions'], 'sendTaskUpdatesToPM');
CP_Common_Lib_Registry::arrayMerge('tv', $tv);

$fn = Zend_Registry::get('fn');
$modulesArr = Zend_Registry::get('modulesArr');
$isDeveloper = $fn->getSessionParam('isDeveloper');

$modulesArr['enggCrm_company']['title'] = 'Client';

if ($isDeveloper == 1) {
    $modulesArr['enggCrm_company']['actBtnsList'] = array('new');
    $cpCfg['cp.topRooms']['admin']['modules'] = array(
         'core_staff'
        ,'core_valuelist'
        ,'core_setting'
        ,'core_userGroup'
        ,'webBasic_content'
      
        /*,'enggCrm_employee'
        ,'enggCrm_attendance'
        ,'webBasic_section'
        ,'webBasic_content'
        */
    );
} else {
    $modulesArr['enggCrm_company']['actBtnsList'] = array('new');
    $cpCfg['cp.topRooms']['admin']['modules'] = array(
         'core_staff'
        ,'core_valuelist'
        ,'core_setting'
    );

}

$cssFilesArr   = array();
$cssFilesArr[] = 'https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css';
$cssFilesArr[] = 'https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap-theme.min.css';

$jsFilesArr   = array();
$jsFilesArr[] = "https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js";
$jsFilesArr[] = "https://cdn.datatables.net/1.10.12/js/jquery.dataTables.min.js";

$jssKeys = array('fontAwesome-4.3.0', 'jqUploadify3.2', 'jqForm-3.15', 'ckEditor');

CP_Common_Lib_Registry::arrayMerge('jsFilesArr', $jsFilesArr);
CP_Common_Lib_Registry::arrayMerge('jssKeys', $jssKeys);
CP_Common_Lib_Registry::arrayMerge('cssFilesArr', $cssFilesArr);
CP_Common_Lib_Registry::arrayMerge('cpCfg', $cpCfg);
CP_Common_Lib_Registry::arrayMerge('modulesArr', $modulesArr);
