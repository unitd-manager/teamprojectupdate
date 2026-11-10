<?
class CP_Admin_Modules_EnggCrm_Reports_Model extends CP_Common_Lib_ModuleModelAbstract
{
    var $reportsArray = array();

    function __construct() {
        $cpUtil  = Zend_Registry::get('cpUtil');

        $this->reportsArray = array( 
            'agmFileStatusReports'      => $this->getReportObj('agmFileStatusReports', 'AGM File Status Reports')
           ,'salesByMonthReports'	      => $this->getReportObj('salesByMonthReports', 'Sales by Month')
           ,'salesByYearReports' 	      => $this->getReportObj('salesByYearReports', 'Sales by Year')
           ,'salesByMonthReports'	      => $this->getReportObj('salesByMonthReports', 'Sales by Month')
           ,'salesByYearReports' 	      => $this->getReportObj('salesByYearReports', 'Sales by Year')
           ,'invoiceByMonthReports'	    => $this->getReportObj('invoiceByMonthReports', 'Invoice by Month')
           ,'invoiceByYearReports' 	    => $this->getReportObj('invoiceByYearReports', 'Invoice by Year')
           ,'officeTimeReport'  	      => $this->getReportObj('officeTimeReport', 'Office Time Report')
           ,'opportunityReport'  	      => $this->getReportObj('officeTimeReport', 'Opportunity Report')
           ,'taskHoursByStaffReport'    => $this->getReportObj('taskHoursByStaffReport', 'Task Hours By Staff Report')
           ,'detailTaskSummaryReport'   => $this->getReportObj('detailTaskSummaryReport', 'Detail Task Summary Report')
           ,'employeeReport'            => $this->getReportObj('employeeReport', 'Employee Report')
           ,'projectReport'             => $this->getReportObj('projectReport', 'Project Report')
           ,'detailSummaryByClient'     => $this->getReportObj('detailSummaryByClient', 'Detail Summary By Client')
           ,'opportunityReport'  	      => $this->getReportObj('opportunityReport', 'Opportunity Report')
           ,'opportunityQuotation'      => $this->getReportObj('opportunityQuotation', 'Invoice Summary')
           ,'invoiceSummary'            => $this->getReportObj('invoiceSummary', 'Opportunity Quotation')
           ,'statementofAccountsReport' => $this->getReportObj('statementofAccountsReport', 'Statement of Accounts Report')
           ,'ageingReport'              => $this->getReportObj('ageingReport', 'Ageing Report')
           ,'ageingBreakdownReport'     => $this->getReportObj('ageingBreakdownReport', 'Ageing Breakdown Report')
           ,'ir8a'                           => $this->getReportObj('ir8a', 'IR8A Report')
           ,'employeePayslipGeneratedReport' => $this->getReportObj('employeePayslipGeneratedReport', 'Payslip Generated Report')
           ,'employeeSalaryReport'           => $this->getReportObj('employeeSalaryReport', 'Employee Salary Report')
           ,'cPFSummaryReport'               => $this->getReportObj('cPFSummaryReport', 'CPF Summary Report')
           ,'leaveReport'                    => $this->getReportObj('leaveReport', 'Leave Report')
        );
    }

    function getReportObj($name, $title, $searchFlds = array('dateRange')) {

        //searchFldType: uptoDate, dateRange, activeRange
        $arr = array(
             'name' => $name
            ,'title' => $title
            ,'searchFlds' => $searchFlds
        );

        return $arr;
    }

}
