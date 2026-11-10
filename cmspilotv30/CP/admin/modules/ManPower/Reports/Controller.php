<?
class CP_Admin_Modules_ManPower_Reports_Controller extends CP_Common_Lib_ModuleControllerAbstract
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

    function getMarketingCallByStaffReport() {
        $wMarketingCallByStaffReport = getCPWidgetObj('manPower_marketingCallByStaffReport');
        return $wMarketingCallByStaffReport->getWidget();
    }

    function getMarketingCallByStaffReportExport() {
        $wMarketingCallByStaffReport = getCPWidgetObj('manPower_marketingCallByStaffReport');
        return $wMarketingCallByStaffReport->model->getExportToExcel();
    }

    function getMarketingCallOverallReport() {
        $wMarketingCallOverallReport = getCPWidgetObj('manPower_marketingCallOverallReport');
        return $wMarketingCallOverallReport->getWidget();
    }

    function getMarketingCallOverallReportExport() {
        $wMarketingCallOverallReport = getCPWidgetObj('manPower_marketingCallOverallReport');
        return $wMarketingCallOverallReport->model->getExportToExcel();
    }

    function getIncomeExpenses() {
        $wIncomeExpenses = getCPWidgetObj('manPower_incomeExpenses');
        return $wIncomeExpenses->getWidget();
    }

    function getIncomeExpensesExport() {
        $wIncomeExpenses = getCPWidgetObj('manPower_incomeExpenses');
        return $wIncomeExpenses->model->getExportToExcel();
    }

    function getStaffAttendanceReport() {
        $wStaffAttendanceReport = getCPWidgetObj('manPower_staffAttendanceReport');
        return $wStaffAttendanceReport->getWidget();
    }

    function getStaffAttendanceReportExport() {
        $wStaffAttendanceReport = getCPWidgetObj('manPower_staffAttendanceReport');
        return $wStaffAttendanceReport->model->getExportToExcel();
    }

    function getStaffAttendanceOverallReport() {
        $wStaffAttendanceOverallReport = getCPWidgetObj('manPower_staffAttendanceOverallReport');
        return $wStaffAttendanceOverallReport->getWidget();
    }

    function getStaffAttendanceOverallReportExport() {
        $wStaffAttendanceOverallReport = getCPWidgetObj('manPower_staffAttendanceOverallReport');
        return $wStaffAttendanceOverallReport->model->getExportToExcel();
    }

    function getOpportunityByMonthReport() {
        $wOpportunityByMonthReport = getCPWidgetObj('manPower_opportunityByMonthReport');
        return $wOpportunityByMonthReport->getWidget();
    }

    function getOpportunityByMonthReportExport() {
        $wOpportunityByMonthReport = getCPWidgetObj('manPower_opportunityByMonthReport');
        return $wOpportunityByMonthReport->model->getExportToExcel();
    }

    function getOpportunityPositionReport() {
        $wOpportunityPositionReport = getCPWidgetObj('manPower_opportunityPositionReport');
        return $wOpportunityPositionReport->getWidget();
    }

    function getOpportunityPositionReportExport() {
        $wOpportunityPositionReport = getCPWidgetObj('manPower_opportunityPositionReport');
        return $wOpportunityPositionReport->model->getExportToExcel();
    }

    function getProjectPositionReport() {
        $wProjectPositionReport = getCPWidgetObj('manPower_projectPositionReport');
        return $wProjectPositionReport->getWidget();
    }

    function getProjectPositionReportExport() {
        $wProjectPositionReport = getCPWidgetObj('manPower_projectPositionReport');
        return $wProjectPositionReport->model->getExportToExcel();
    }

    function getTaxReport() {
        $wTaxReport = getCPWidgetObj('manPower_taxReport');
        return $wTaxReport->getWidget();
    }

    function getTaxReportExport() {
        $wTaxReport = getCPWidgetObj('manPower_taxReport');
        return $wTaxReport->model->getExportToExcel();
    }
}