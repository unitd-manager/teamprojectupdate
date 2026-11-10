<?
class CP_Admin_Modules_Project_Reports_Model extends CP_Common_Lib_ModuleModelAbstract
{
    var $reportsArray = array();

    function __construct() {
        $cpUtil  = Zend_Registry::get('cpUtil');

        $this->reportsArray = array(
            'agmFileStatusReports'      => $this->getReportObj('agmFileStatusReports', 'AGM File Status Reports')
           ,'salesByMonthReports'	      => $this->getReportObj('salesByMonthReports', 'Sales by Month')
           ,'salesByYearReports' 	      => $this->getReportObj('salesByYearReports', 'Sales by Year')
           ,'invoiceByMonthReports'	    => $this->getReportObj('invoiceByMonthReports', 'Invoice by Month')
           ,'invoiceByYearReports' 	    => $this->getReportObj('invoiceByYearReports', 'Invoice by Year')
           ,'officeTimeReport'  	      => $this->getReportObj('officeTimeReport', 'Office Time Report')
           ,'opportunityReport'  	      => $this->getReportObj('officeTimeReport', 'Opportunity Report')
           ,'taskHoursByStaffReport'    => $this->getReportObj('taskHoursByStaffReport', 'Task Hours By Staff Report')
           ,'detailTaskSummaryReport'   => $this->getReportObj('detailTaskSummaryReport', 'Detail Task Summary Report')
           ,'opportunityChart'          => $this->getReportObj('opportunityChart', 'Opportunity Chart')
           ,'cPFSummaryReport'          => $this->getReportObj('cPFSummaryReport', 'CPF Summary Report')
           ,'employeeSalaryReport'      => $this->getReportObj('employeeSalaryReport', 'Employee Salary Report')
           ,'leaveReport'               => $this->getReportObj('leaveReport', 'Leave Report')
           ,'loanReport'                => $this->getReportObj('loanReport', 'Loan Report')
           ,'marketingDetailReport'     => $this->getReportObj('marketingDetailReport', 'Marketing Detail Report')
           ,'marketingSummaryReport'    => $this->getReportObj('marketingSummaryReport', 'Marketing Summary Report')
           ,'allowanceReport'           => $this->getReportObj('allowanceReport', 'Allowance Report')
           ,'sDLReport'                 => $this->getReportObj('sDLReport', 'SDL Report')
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
