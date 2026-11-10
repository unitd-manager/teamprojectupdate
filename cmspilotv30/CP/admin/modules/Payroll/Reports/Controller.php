<?
class CP_Admin_Modules_Payroll_Reports_Controller extends CP_Common_Lib_ModuleControllerAbstract
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

    /**
     * IR8A Report
     */
    function getIr8a() {
        $wIr8aReport = getCPWidgetObj('payroll_ir8aReport');
        return $wIr8aReport->getWidget();
    }

    function getIr8aExport() {
        $wIr8aReport = getCPWidgetObj('payroll_ir8aReport');
        return $wIr8aReport->model->getExportToExcel();
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
        return $this->model->getEmployeeByEmployeeStatus();
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

    /**
     * Employee Training Expiry Report
     */
    function getEmployeeTrainingExpiryReport() {
        $wEmployeeTrainingExpiryReport = getCPWidgetObj('payroll_employeeTrainingExpiryReport');
        return $wEmployeeTrainingExpiryReport->getWidget();
    }

    function getEmployeeTrainingExpiryReportExport() {
        $wEmployeeTrainingExpiryReport = getCPWidgetObj('payroll_employeeTrainingExpiryReport');
        return $wEmployeeTrainingExpiryReport->model->getExportToExcel();
    }

    /**
     * Payslip By Employee Report
     */
    function getPayslipByEmployeeReport() {
        $wPayslipByEmployeeReport = getCPWidgetObj('payroll_payslipByEmployeeReport');
        return $wPayslipByEmployeeReport->getWidget();
    }

    function getPayslipByEmployeeReportExport() {
        $wPayslipByEmployeeReport = getCPWidgetObj('payroll_payslipByEmployeeReport');
        return $wPayslipByEmployeeReport->model->getExportToExcel();
    }
}