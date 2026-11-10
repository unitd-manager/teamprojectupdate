<?
$cpCfg = array();
$cpCfg['cp.theme']         = 'MaterialTim';
$cpCfg['cp.hasAdminOnly']  = true;
$cpCfg['cp.frameworkName'] = 'CRM Pilot';
$cpCfg['cp.version']       = '3.0';
$cpCfg['cp.hasMultiUniqueSites'] = true;
$cpCfg['w.common_multiUniqueSite.ignoreModules'] = array(
     'common_site'
    ,'core_valuelist'
    ,'core_setting'
    ,'core_userGroup'
    ,'webBasic_content'
    ,'webBasic_section'
    ,'common_comment'
    , 'common_dashboard'
    , 'enggCrm_company'
    , 'tradingsg_product'
    , 'enggCrm_renewal'
    , 'webBasic_help'
    , 'enggCrm_employee'
    , 'tradingsg_supplier'
    , 'tradingsg_subCon'
    , 'tradingin_inventory'
    , 'tradingsg_purchaseOrder'
    ,'enggCrm_reports'
    , 'core_staff'
);
$cpCfg['cp.jqVersion']     = '1.9.1';
$cpCfg['cp.jqUiVersion']   = '1.9.2';

$cpCfg['cp.topRooms'] = array(
    'project' => array(
         'title' => 'Tender / Project'
        ,'modules' => array(
             'enggCrm_home'
            ,'common_dashboard'
            ,'enggCrm_opportunity'
            ,'enggCrm_project'
            ,'enggCrm_company'
            //,'enggCrm_contact'
            //,'tradingsg_booking'
            //,'enggCrm_attendance'
            ,'tradingsg_product'
            ,'enggCrm_renewal'
            ,'webBasic_help'
            ,'enggCrm_employee'
        )
        ,'default' => 'enggCrm_home'
    )

    ,'finance' => array(
         'title' => 'Finance / Admin / Purchaser'
        ,'modules' => array(
            'tradingsg_supplier'
           ,'tradingsg_subCon'
           ,'enggCrm_order'
           ,'enggCrm_invoice'
           ,'tradingin_inventory'
           ,'tradingsg_purchaseOrder'
           ,'enggCrm_reports'
          // ,'enggCrm_vehicle'
        )
        ,'default' => 'enggCrm_order'
    )

    // ,'payroll' => array(
    //      'title' => 'Payroll / HR'
    //     ,'modules' => array(
    //           //'payroll_dashboard'
    //           'payroll_leave'
    //          ,'payroll_loan'
    //          ,'payroll_training'
    //          ,'payroll_employee'
    //          ,'payroll_jobInformation'
    //          //,'payroll_leavePolicy'
    //          ,'payroll_payrollManagement'
    //          ,'payroll_cPFCalculator'
    //          ,'payroll_expense'
    //          ,'payroll_expenseHead'
    //     )
    //     ,'default' => 'payroll_employee'
    // )

    /*
    ,'accounts' => array(
         'title' => 'Accounts'
        ,'modules' => array(
              'payroll_expense'
             ,'payroll_expenseHead'
             //,'payroll_incomeHead'
             //,'payroll_supplier'
        )
        ,'default' => 'payroll_expense'
    )
    */

    ,'admin' => array(
         'title' => 'Admin'
        ,'modules' => array(
              'core_staff'
             ,'core_valuelist'
             ,'core_setting'
             ,'webBasic_section'
             ,'webBasic_content'
             
             ,'core_userGroup'
        )
        ,'default' => 'core_valuelist'
    )
);

$hiddenModules = array(
     'common_contactLink'
    ,'common_testRecipientLink'
    ,'common_interestLink'
    ,'enggCrm_contactLink'
    ,'enggCrm_projectLink'
    ,'enggCrm_invoiceLink'
    ,'enggCrm_opportunityLink'
    ,'enggCrm_companyAddressLink'
    ,'enggCrm_companyLink'
    ,'core_staffLink'
    ,'enggCrm_taskLink'
    ,'enggCrm_scheduleLink'
    ,'enggCrm_thirdPartyCostLink'
    ,'enggCrm_timesheetLink'
    ,'ecommerce_orderItemLink'
    ,'enggCrm_taskHistoryLink'
    ,'enggCrm_employeeLink'
    ,'ecommerce_product'
    ,'enggCrm_receipt'
    ,'enggCrm_opportunity'
    ,'enggCrm_contact'
);

$tmpName = &$cpCfg['cp.topRooms'];
$cpCfg['cp.availableModules'] = array_merge(
     $tmpName['project']['modules']
    ,$tmpName['finance']['modules']
   // ,$tmpName['payroll']['modules']
    //,$tmpName['accounts']['modules']
    //,$tmpName['reports']['modules']
    ,$tmpName['admin']['modules']
    ,$hiddenModules
);

$cpCfg['cp.availableModGroups'] = array(
     'core'
    ,'common'
    ,'enggCrm'
    ,'payroll'
    ,'tradingin'
    ,'tradingsg'
);

$cpCfg['cp.availableWidgets'] = array(
    'common_multiUniqueSite'
    ,'project_projectSummary'
    ,'project_invoiceSummary'
    ,'project_invoiceSummaryChart'
    ,'project_opportunity'
    ,'enggCrm_salesByMonthChart'
    ,'enggCrm_invoiceChartByMonth'
    ,'enggCrm_opportunityReport'
    ,'enggCrm_salesByMonthReports'
    ,'enggCrm_salesByYearReports'
    ,'enggCrm_invoiceByMonthReports'
    ,'enggCrm_invoiceByYearReports'
    ,'enggCrm_opportunityQuotation'
    ,'enggCrm_employeeReport'
    ,'enggCrm_projectReport'
    ,'enggCrm_invoiceSummary'
    ,'project_detailSummaryByClient'
    ,'enggCrm_statementofAccountsReport'
    ,'enggCrm_ageingReport'
    ,'payroll_employeePayslipGeneratedReport'
    ,'payroll_employeeSalaryReport'
    ,'payroll_cPFSummaryReport'
    ,'payroll_ir8aReport'
    ,'payroll_employeeTrainingExpiryReport'
    ,'payroll_dormitoryReport'
    ,'enggCrm_profitLossReport'
    ,'enggCrm_expenseSummaryReport'
    ,'enggCrm_overallSalesSummary'
    ,'project_statementofAccountsReport'
    ,'payroll_vacationReport'
    ,'project_operationalFinancialReport'
    ,'project_projectCostingSummary'
    ,'enggCrm_projectQuote'
    ,'enggCrm_projectJobCompletion'
    ,'enggCrm_projectQuoteRenewal'
    ,'enggCrm_projectMaterialsUsed'
    ,'enggCrm_projectWarranty'
    ,'enggCrm_projectMaintenanace'
    ,'enggCrm_projectPurchaseOrder'
    ,'enggCrm_projectWorkOrder'
    ,'enggCrm_projectClaim'
    ,'enggCrm_projectMaterialTransferred'
    ,'enggCrm_projectFinance'
    ,'enggCrm_projectFinanceRenewal'
    ,'enggCrm_projectDeliveryOrderNote'
    ,'enggCrm_projectWarrantyRenewal'
    ,'enggCrm_projectDeliveryOrder'
    ,'enggCrm_taskFromAdmin'
    ,'enggCrm_dashboardTopPanel'
    ,'enggCrm_projectDuctDelivery'
    ,'payroll_passportExpiry'
    ,'payroll_workpermitExpiry'
    ,'payroll_employeeSummary'
    ,'payroll_sDLReport'
    ,'enggCrm_materialRequestedList'
    ,'enggCrm_materialDelivered'
    ,'enggCrm_tenderSummary'
    ,'enggCrm_opportunityCostingSummary'
    ,'enggCrm_projectTimesheet'
    ,'enggCrm_contractReport'
);

$cpCfg['cp.availablePlugins'] = array(
     'common_comment'
    ,'common_media'
    ,'common_login'
    ,'member_forgotPassword'
);

$cpCfg['cp.repPrintLogoInRight'] = true;
$cpCfg['cp.repPrintLogoInLeft'] = false;

$cpCfg['cp.assetVersion'] = '20130115';

return $cpCfg;
 

