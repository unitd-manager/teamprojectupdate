<?
class CPL_Admin_Modules_EnggCrm_Reports_Controller extends CP_Admin_Modules_EnggCrm_Reports_Controller
{
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
     * Dormitory Report
     */
    function getDormitoryReport() {
        $wDormitoryReport = getCPWidgetObj('payroll_dormitoryReport');
        return $wDormitoryReport->getWidget();
    }

    function getDormitoryReportExport() {
        $wDormitoryReport = getCPWidgetObj('payroll_dormitoryReport');
        return $wDormitoryReport->model->getExportToExcel();
    }

    /**
     * Profit & Loss Report
     */
    function getProfitLossReport() {
        $wProfitLossReport = getCPWidgetObj('enggCrm_profitLossReport');
        return $wProfitLossReport->getWidget();
    }

    function getProfitLossReportExport() {
        $wProfitLossReport = getCPWidgetObj('enggCrm_profitLossReport');
        return $wProfitLossReport->model->getExportToExcel();
    }

    function getExpenseSummaryReport() {
        $wExpenseSummaryReport = getCPWidgetObj('enggCrm_expenseSummaryReport');
        return $wExpenseSummaryReport->getWidget();
    }

    function getExpenseSummaryReportExport() {
        $wExpenseSummaryReport = getCPWidgetObj('enggCrm_expenseSummaryReport');
        return $wExpenseSummaryReport->model->getExportToExcel();
    }

    function getOverallSalesSummary() {
        $wOverallSalesSummary = getCPWidgetObj('enggCrm_overallSalesSummary');
        return $wOverallSalesSummary->getWidget();
    }

    function getOverallSalesSummaryExport() {
        $wOverallSalesSummary = getCPWidgetObj('enggCrm_overallSalesSummary');
        return $wOverallSalesSummary->model->getExportToExcel();
    }

    /**
     *
     */
    function getCompanyNameByJSON() {
        return $this->model->getCompanyNameByJSON();
    }

    function getVacationReport() {
        $wVacationReport = getCPWidgetObj('payroll_vacationReport');
        return $wVacationReport->getWidget();
    }

    function getVacationReportExport() {
        $wVacationReport = getCPWidgetObj('payroll_vacationReport');
        return $wVacationReport->model->getExportToExcel();
    }

    function getOperationalFinancialReport() {
        $wOperationalFinancialReport = getCPWidgetObj('project_operationalFinancialReport');
        return $wOperationalFinancialReport->getWidget();
    }

    function getOperationalFinancialReportExport() {
        $wOperationalFinancialReport = getCPWidgetObj('project_operationalFinancialReport');
        return $wOperationalFinancialReport->model->getExportToExcel();
    }

    function getContractReport() {
        $wContractReport = getCPWidgetObj('enggCrm_contractReport');
        return $wContractReport->getWidget();
    }

    function getContractReportExport() {
        $wContractReport = getCPWidgetObj('enggCrm_contractReport');
        return $wContractReport->model->getExportToExcel();
    }
}