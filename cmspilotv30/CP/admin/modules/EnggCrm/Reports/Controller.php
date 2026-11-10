<?
class CP_Admin_Modules_EnggCrm_Reports_Controller extends CP_Common_Lib_ModuleControllerAbstract
{
    function getSearch(){
        return $this->view->getSearch();
    }

    function getDisplayReport(){
        $fn = Zend_Registry::get('fn');
        
        set_time_limit(50000);
        $report = $fn->getReqParam('report');
        $fnName = 'get' . ucfirst($report);
        $text = $this->$fnName();
    
        return $this->view->getDisplayReport($text);
    }    

    function getExportData(){
        $fn = Zend_Registry::get('fn');
        
        set_time_limit(50000);
        $report = $fn->getReqParam('report');
        $fnName = 'get' . ucfirst($report) . 'Export';
        return $this->$fnName();
    }

    function getOpportunityReport() {
        $wOpportunityReport = getCPWidgetObj('enggCrm_opportunityReport');
        return $wOpportunityReport->getWidget();
    }

    function getOpportunityReportExport() {
        $wOpportunityReport = getCPWidgetObj('enggCrm_opportunityReport');
        return $wOpportunityReport->model->getExportToExcel();
    }

    function getOpportunityQuotation() {
        $wOpportunityQuotation = getCPWidgetObj('enggCrm_opportunityQuotation');
        return $wOpportunityQuotation->getWidget();
    }

    function getOpportunityQuotationExport() {
        $wOpportunityQuotation = getCPWidgetObj('enggCrm_opportunityQuotation');
        return $wOpportunityQuotation->model->getExportToExcel();
    }

    function getProjectReport() {
        $wProjectReport = getCPWidgetObj('enggCrm_projectReport');
        return $wProjectReport->getWidget();
    }

    function getProjectReportExport() {
        $wProjectReport = getCPWidgetObj('enggCrm_projectReport');
        return $wProjectReport->model->getExportToExcel();
    }

    function getInvoiceSummary() {
        $wInvoiceSummary = getCPWidgetObj('enggCrm_invoiceSummary');
        return $wInvoiceSummary->getWidget();
    }

    function getInvoiceSummaryExport() {
        $wInvoiceSummary = getCPWidgetObj('enggCrm_invoiceSummary');
        return $wInvoiceSummary->model->getExportToExcel();
    }

    function getSalesByMonthReports() {
        $wSalesByMonthReports = getCPWidgetObj('enggCrm_salesByMonthReports');
        return $wSalesByMonthReports->getWidget();
    }

    function getSalesByMonthReportsExport() {
        $wSalesByMonthReports = getCPWidgetObj('enggCrm_salesByMonthReports');
        return $wSalesByMonthReports->model->getExportToExcel();
    }

    function getSalesByYearReports() {
        $wSalesByYearReports = getCPWidgetObj('enggCrm_salesByYearReports');
        return $wSalesByYearReports->getWidget();
    }

    function getSalesByYearReportsExport() {
        $wSalesByYearReports = getCPWidgetObj('enggCrm_salesByYearReports');
        return $wSalesByYearReports->model->getExportToExcel();
    }

    function getInvoiceByMonthReports() {
        $wInvoiceByMonthReports = getCPWidgetObj('enggCrm_invoiceByMonthReports');
        return $wInvoiceByMonthReports->getWidget();
    }

    function getInvoiceByMonthReportsExport() {
        $wInvoiceByMonthReports = getCPWidgetObj('enggCrm_invoiceByMonthReports');
        return $wInvoiceByMonthReports->model->getExportToExcel();
    }

    function getInvoiceByYearReports() {
        $wInvoiceByYearReports = getCPWidgetObj('enggCrm_invoiceByYearReports');
        return $wInvoiceByYearReports->getWidget();
    }

    function getInvoiceByYearReportsExport() {
        $wInvoiceByYearReports = getCPWidgetObj('enggCrm_invoiceByYearReports');
        return $wInvoiceByYearReports->model->getExportToExcel();
    }





    function getEmployeeReport() {
        $wEmployeeReport = getCPWidgetObj('enggCrm_employeeReport');
        return $wEmployeeReport->getWidget();
    }
    function getEmployeeReportExport() {
        $wEmployeeReport = getCPWidgetObj('enggCrm_employeeReport');
        return $wEmployeeReport->model->getExportToExcel();
    }

    /*function getProjectReport() {
        $wProjectReport = getCPWidgetObj('project_projectReport');
        return $wProjectReport->getWidget();
    }
    function getProjectReportExport() {
        $wProjectReport = getCPWidgetObj('project_projectReport');
        return $wProjectReport->model->getExportToExcel();
    }*/

    function getDetailSummaryByClient() {
        $wDetailSummaryByClient = getCPWidgetObj('project_detailSummaryByClient');
        return $wDetailSummaryByClient->getWidget();
    }
    function getDetailSummaryByClientExport() {
        $wDetailSummaryByClient = getCPWidgetObj('project_detailSummaryByClient');
        return $wDetailSummaryByClient->model->getExportToExcel();
    }

    function getStatementofAccountsReport() {
        $wStatementofAccountsReport = getCPWidgetObj('enggCrm_statementofAccountsReport');
        return $wStatementofAccountsReport->getWidget();
    }

    function getStatementofAccountsReportExport() {
        $wStatementofAccountsReport = getCPWidgetObj('enggCrm_statementofAccountsReport');
        return $wStatementofAccountsReport->model->getExportToExcel();
    }
    
    function getAgeingReport() {
        $wAgeingReport = getCPWidgetObj('enggCrm_ageingReport');
        return $wAgeingReport->getWidget();
    }

    function getAgeingReportExport() {
        $wAgeingReportReport = getCPWidgetObj('enggCrm_ageingReport');
        return $wAgeingReportReport->model->getExportToExcel();
    }

    function getIr8a() {
        $wIr8aReport = getCPWidgetObj('payroll_ir8aReport');
        return $wIr8aReport->getWidget();
    }

    function getIr8aExport() {
        $wIr8aReport = getCPWidgetObj('payroll_ir8aReport');
        return $wIr8aReport->model->getExportToExcel();
    }
    
    function getAgeingBreakdownReport() {
        $wAgeingBreakdownReport = getCPWidgetObj('enggCrm_ageingBreakdownReport');
        return $wAgeingBreakdownReport->getWidget();
    }

    function getAgeingBreakdownReportExport() {
        $wAgeingBreakdownReport = getCPWidgetObj('enggCrm_ageingBreakdownReport');
        return $wAgeingBreakdownReport->model->getExportToExcel();
    }

    /**
     * Employee Payslip Generated Report
     */
    function getEmployeePayslipGeneratedReport() {
        $wEmployeePayslipGeneratedReport = getCPWidgetObj('payroll_employeePayslipGeneratedReport');
        return $wEmployeePayslipGeneratedReport->getWidget();
    }

    function getEmployeePayslipGeneratedReportExport() {
        $wEmployeePayslipGeneratedReport = getCPWidgetObj('payroll_employeePayslipGeneratedReport');
        return $wEmployeePayslipGeneratedReport->model->getExportToExcel();
    }

    /**
     * Employee Salary Report
     */
    function getEmployeeSalaryReport() {
        $wEmployeePayslipGeneratedReport = getCPWidgetObj('payroll_employeeSalaryReport');
        return $wEmployeePayslipGeneratedReport->getWidget();
    }

    function getEmployeeSalaryReportExport() {
        $wEmployeePayslipGeneratedReport = getCPWidgetObj('payroll_employeeSalaryReport');
        return $wEmployeePayslipGeneratedReport->model->getExportToExcel();
    }

    /**
     * CPF Summary Report
     */
    function getCPFSummaryReport() {
        $wCPFSummaryReport = getCPWidgetObj('payroll_cPFSummaryReport');
        return $wCPFSummaryReport->getWidget();
    }

    function getCPFSummaryReportExport() {
        $wCPFSummaryReport = getCPWidgetObj('payroll_cPFSummaryReport');
        return $wCPFSummaryReport->model->getExportToExcel();
    }

    /**
     * Leave Report
     */
    function getLeaveReport() {
        $wCPFSummaryReport = getCPWidgetObj('payroll_leaveReport');
        return $wCPFSummaryReport->getWidget();
    }

    function getLeaveReportExport() {
        $wCPFSummaryReport = getCPWidgetObj('payroll_leaveReport');
        return $wCPFSummaryReport->model->getExportToExcel();
    }

    function getEmployeeByEmployeeStatus() {
        $modObj = $modules->getModuleObj('payroll_reports');
        return $modObj->model->getEmployeeByEmployeeStatus();
    }
}