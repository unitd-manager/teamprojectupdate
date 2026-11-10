<?
class CP_Admin_Modules_Project_Reports_Controller extends CP_Common_Lib_ModuleControllerAbstract
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

    function getExportDataPdf(){
        $fn = Zend_Registry::get('fn');

        set_time_limit(50000);
        $report = $fn->getReqParam('report');
        $fnName = 'get' . ucfirst($report) . 'ExportAsPdf';
        return $this->$fnName();
    }

    function getAGMFileStatusReports() {
        $wAGMFileStatusReports = getCPWidgetObj('project_aGMFileStatusReports');
        return $wAGMFileStatusReports->getWidget();
    }

    function getAgmFileStatusReportsExport() {
        $wAgmFileStatusReports = getCPWidgetObj('project_aGMFileStatusReports');
        return $wAgmFileStatusReports->model->getExportToExcel();
    }

    function getSalesByMonthReports() {
        $wSalesByMonthReports = getCPWidgetObj('project_salesByMonthReports');
        return $wSalesByMonthReports->getWidget();
    }

    function getSalesByMonthReportsExport() {
        $wSalesByMonthReports = getCPWidgetObj('project_salesByMonthReports');
        return $wSalesByMonthReports->model->getExportToExcel();
    }

    function getSalesByYearReports() {
        $wSalesByYearReports = getCPWidgetObj('project_salesByYearReports');
        return $wSalesByYearReports->getWidget();
    }

    function getSalesByYearReportsExport() {
        $wSalesByYearReports = getCPWidgetObj('project_salesByYearReports');
        return $wSalesByYearReports->model->getExportToExcel();
    }

    function getInvoiceByMonthReports() {
        $wInvoiceByMonthReports = getCPWidgetObj('project_invoiceByMonthReports');
        return $wInvoiceByMonthReports->getWidget();
    }

    function getInvoiceByMonthReportsExport() {
        $wInvoiceByMonthReports = getCPWidgetObj('project_invoiceByMonthReports');
        return $wInvoiceByMonthReports->model->getExportToExcel();
    }

    function getInvoiceByYearReports() {
        $wInvoiceByYearReports = getCPWidgetObj('project_invoiceByYearReports');
        return $wInvoiceByYearReports->getWidget();
    }

    function getInvoiceByYearReportsExport() {
        $wInvoiceByYearReports = getCPWidgetObj('project_invoiceByYearReports');
        return $wInvoiceByYearReports->model->getExportToExcel();
    }

    function getOfficeTimeReport() {
        $wOfficeTimeReport = getCPWidgetObj('project_officeTimeReport');
        return $wOfficeTimeReport->getWidget();
    }

    function getOfficeTimeReportExport() {
        $wOfficeTimeReport = getCPWidgetObj('project_officeTimeReport');
        return $wOfficeTimeReport->model->getExportToExcel();
    }

    function getAttendanceReport() {
        $wAttendanceReport = getCPWidgetObj('project_attendanceReport');
        return $wAttendanceReport->getWidget();
    }

    function getOpportunityReport() {
        $wOpportunityReport = getCPWidgetObj('project_opportunityReport');
        return $wOpportunityReport->getWidget();
    }

    function getOpportunityReportExport() {
        $wOpportunityReport = getCPWidgetObj('project_opportunityReport');
        return $wOpportunityReport->model->getExportToExcel();
    }
    function getTaskHoursByStaffReport() {
        $wOpportunityReport = getCPWidgetObj('project_taskHoursByStaffReport');
        return $wOpportunityReport->getWidget();
    }
     function getTaskHoursByStaffReportExport() {
        $wTaskHoursByStaffReport = getCPWidgetObj('project_taskHoursByStaffReport');
        return $wTaskHoursByStaffReport->model->getExportToExcel();
    }
    function getDetailTaskSummaryReport() {
        $wDetailTaskSummaryReport = getCPWidgetObj('project_detailTaskSummaryReport');
        return $wDetailTaskSummaryReport->getWidget();
    }
    function getDetailTaskSummaryReportExport() {
        $wDetailTaskSummaryReport = getCPWidgetObj('project_detailTaskSummaryReport');
        return $wDetailTaskSummaryReport->model->getExportToExcel();
    }

    function getOpportunityChart() {
        $wOpportunityChart = getCPWidgetObj('project_opportunityChart');
        return $wOpportunityChart->getWidget();
    }

    function getCPFSummaryReport() {
        $wCPFSummaryReport = getCPWidgetObj('payroll_cPFSummaryReport');
        return $wCPFSummaryReport->getWidget();
    }
     function getCPFSummaryReportExport() {
        $wCPFSummaryReport = getCPWidgetObj('payroll_cPFSummaryReport');
        return $wCPFSummaryReport->model->getExportToExcel();
    }

    function getEmployeeSalaryReport() {
        $wEmployeeSalaryReport = getCPWidgetObj('payroll_employeeSalaryReport');
        return $wEmployeeSalaryReport->getWidget();
    }
     function getEmployeeSalaryReportExport() {
        $wEmployeeSalaryReport = getCPWidgetObj('payroll_employeeSalaryReport');
        return $wEmployeeSalaryReport->model->getExportToExcel();
    }

    function getLeaveReport() {
        $wLeaveReport = getCPWidgetObj('payroll_leaveReport');
        return $wLeaveReport->getWidget();
    }
     function getLeaveReportExport() {
        $wLeaveReport = getCPWidgetObj('payroll_leaveReport');
        return $wLeaveReport->model->getExportToExcel();
    }

    function getLoanReport() {
        $wLoanReport = getCPWidgetObj('payroll_loanReport');
        return $wLoanReport->getWidget();
    }
     function getLoanReportExport() {
        $wLoanReport = getCPWidgetObj('payroll_loanReport');
        return $wLoanReport->model->getExportToExcel();
    }

    function getMarketingDetailReport() {
        $wmarketingDetailReport = getCPWidgetObj('project_marketingDetailReport');
        return $wmarketingDetailReport->getWidget();
    }

    function getMarketingDetailReportExport() {
        $wmarketingDetailReport = getCPWidgetObj('project_marketingDetailReport');
        return $wmarketingDetailReport->model->getExportToExcel();
    }

    function getMarketingSummaryReport() {
        $wMarketingSummaryReport = getCPWidgetObj('project_marketingSummaryReport');
        return $wMarketingSummaryReport->getWidget();
    }

    function getMarketingSummaryReportExport() {
        $wMarketingSummaryReport = getCPWidgetObj('project_marketingSummaryReport');
        return $wMarketingSummaryReport->model->getExportToExcel();
    }

    function getAllowanceReport() {
        $wAllowanceReport = getCPWidgetObj('payroll_allowanceReport');
        return $wAllowanceReport->getWidget();
    }
     function getAllowanceReportExport() {
        $wAllowanceReport = getCPWidgetObj('payroll_allowanceReport');
        return $wAllowanceReport->model->getExportToExcel();
    }

    function getSDLReport() {
        $wSDLReport = getCPWidgetObj('payroll_sDLReport');
        return $wSDLReport->getWidget();
    }
     function getSDLReportExport() {
        $wSDLReport = getCPWidgetObj('payroll_sDLReport');
        return $wSDLReport->model->getExportToExcel();
    }

}