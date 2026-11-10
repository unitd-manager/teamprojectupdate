<?
class CP_Admin_Modules_Tradingsg_Reports_Controller extends CP_Common_Lib_ModuleControllerAbstract
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

    function getSalesByMonth() {
        $wSalesByMonth = getCPWidgetObj('tradingsg_salesByMonth');
        return $wSalesByMonth->getWidget();
    }

    function getSalesByMonthExport() {
        $wSalesByMonth = getCPWidgetObj('tradingsg_salesByMonth');
        return $wSalesByMonth->model->getExportToExcel();
    }

    function getSalesByYear() {
        $wSalesByYear = getCPWidgetObj('tradingsg_salesByYear');
        return $wSalesByYear->getWidget();
    }

    function getSalesByYearExport() {
        $wSalesByYear = getCPWidgetObj('tradingsg_salesByYear');
        return $wSalesByYear->model->getExportToExcel();
    }

    function getInvoiceByMonth() {
        $wInvoiceByMonth = getCPWidgetObj('tradingsg_invoiceByMonth');
        return $wInvoiceByMonth->getWidget();
    }

    function getInvoiceByMonthExport() {
        $wInvoiceByMonth = getCPWidgetObj('tradingsg_invoiceByMonth');
        return $wInvoiceByMonth->model->getExportToExcel();
    }

    function getGstReport() {
        $wGstReport = getCPWidgetObj('tradingsg_gstReport');
        return $wGstReport->getWidget();
    }

    function getGstReportExport() {
        $wGstReport = getCPWidgetObj('tradingsg_gstReport');
        return $wGstReport->model->getExportToExcel();
    }

    function getDetailInvoiceByMonth() {
        $wInvoiceByMonth = getCPWidgetObj('tradingsg_detailInvoiceByMonth');
        return $wInvoiceByMonth->getWidget();
    }

    function getDetailInvoiceByMonthExport() {
        $wInvoiceByMonth = getCPWidgetObj('tradingsg_detailInvoiceByMonth');
        return $wInvoiceByMonth->model->getExportToExcel();
    }

    function getInvoiceByYear() {
        $wInvoiceByYear = getCPWidgetObj('tradingsg_invoiceByYear');
        return $wInvoiceByYear->getWidget();
    }

    function getInvoiceByYearExport() {
        $wInvoiceByYear = getCPWidgetObj('tradingsg_invoiceByYear');
        return $wInvoiceByYear->model->getExportToExcel();
    }

    function getProfitByMonth() {
        $wProfitByMonth = getCPWidgetObj('tradingsg_profitByMonth');
        return $wProfitByMonth->getWidget();
    }

    function getProfitByMonthExport() {
        $wProfitByMonth = getCPWidgetObj('tradingsg_profitByMonth');
        return $wProfitByMonth->model->getExportToExcel();
    }

    function getProfitByYear() {
        $wProfitByYear = getCPWidgetObj('tradingsg_profitByYear');
        return $wProfitByYear->getWidget();
    }

    function getProfitByYearExport() {
        $wProfitByYear = getCPWidgetObj('tradingsg_profitByYear');
        return $wProfitByYear->model->getExportToExcel();
    }

    function getQuoteByMonth() {
        $wQuoteByMonth = getCPWidgetObj('tradingsg_quoteByMonth');
        return $wQuoteByMonth->getWidget();
    }

    function getQuoteByMonthExport() {
        $wQuoteByMonth = getCPWidgetObj('tradingsg_quoteByMonth');
        return $wQuoteByMonth->model->getExportToExcel();
    }

    function getQuoteByYear() {
        $wQuoteByYear = getCPWidgetObj('tradingsg_quoteByYear');
        return $wQuoteByYear->getWidget();
    }

    function getSalesByClient() {
        $wSalesByClient = getCPWidgetObj('tradingsg_salesByClient');
        return $wSalesByClient->getWidget();
    }

    function getSalesByClientExport() {
        $wSalesByClient = getCPWidgetObj('tradingsg_salesByClient');
        return $wSalesByClient->model->getExportToExcel();
    }

    function getInvoiceByClient() {
        $wInvoiceByClient = getCPWidgetObj('tradingsg_invoiceByClient');
        return $wInvoiceByClient->getWidget();
    }

    function getInvoiceByClientExport() {
        $wInvoiceByClient = getCPWidgetObj('tradingsg_invoiceByClient');
        return $wInvoiceByClient->model->getExportToExcel();
    }

    function getEnquiryByMonth() {
        $wEnquiryByMonth = getCPWidgetObj('tradingsg_enquiryByMonth');
        return $wEnquiryByMonth->getWidget();
    }

    function getEnquiryByMonthExport() {
        $wEnquiryByMonth = getCPWidgetObj('tradingsg_enquiryByMonth');
        return $wEnquiryByMonth->model->getExportToExcel();
    }

    function getEnquiryByYear() {
        $wEnquiryByYear = getCPWidgetObj('tradingsg_enquiryByYear');
        return $wEnquiryByYear->getWidget();
    }

    function getLeadByStaff() {
        $wLeadByStaff = getCPWidgetObj('tradingsg_leadByStaff');
        return $wLeadByStaff->getWidget();
    }

    function getLeadByStaffExport() {
        $wLeadByStaff = getCPWidgetObj('tradingsg_leadByStaff');
        return $wLeadByStaff->model->getExportToExcel();
    }

    function getEnquiryByStaff() {
        $wEnquiryByStaff = getCPWidgetObj('tradingsg_enquiryByStaff');
        return $wEnquiryByStaff->getWidget();
    }

    function getEnquiryByStaffExport() {
        $wEnquiryByStaff = getCPWidgetObj('tradingsg_enquiryByStaff');
        return $wEnquiryByStaff->model->getExportToExcel();
    }

    function getEnquiryActivityByStaff() {
        $wEnquiryActivityByStaff = getCPWidgetObj('tradingsg_enquiryActivityByStaff');
        return $wEnquiryActivityByStaff->getWidget();
    }

    function getEnquiryActivityByStaffExport() {
        $wEnquiryActivityByStaff = getCPWidgetObj('tradingsg_enquiryActivityByStaff');
        return $wEnquiryActivityByStaff->model->getExportToExcel();
    }

    function getSalesSummaryByProduct() {
        $wSalesSummaryByProduct = getCPWidgetObj('tradingsg_salesSummaryByProduct');
        return $wSalesSummaryByProduct->getWidget();
    }

    function getSalesSummaryByProductExport() {
        $wSalesSummaryByProduct = getCPWidgetObj('tradingsg_salesSummaryByProduct');
        return $wSalesSummaryByProduct->model->getExportToExcel();
    }

    function getSalesSummaryByProductGroup() {
        $wSalesSummaryByProduct = getCPWidgetObj('tradingsg_salesSummaryByProductGroup');
        return $wSalesSummaryByProduct->getWidget();
    }

    function getSalesSummaryByProductGroupExport() {
        $wSalesSummaryByProduct = getCPWidgetObj('tradingsg_salesSummaryByProductGroup');
        return $wSalesSummaryByProduct->model->getExportToExcel();
    }

    function getInvoicesForVat() {
        $wInvoicesForVat = getCPWidgetObj('tradingsg_invoicesForVat');
        return $wInvoicesForVat->getWidget();
    }

    function getInvoicesForVatExport() {
        $wInvoicesForVat = getCPWidgetObj('tradingsg_invoicesForVat');
        return $wInvoicesForVat->model->getExportToExcel();
    }

    function getInvoicesByVatPercent() {
        $wInvoicesByVatPercent = getCPWidgetObj('tradingsg_invoicesByVatPercent');
        return $wInvoicesByVatPercent->getWidget();
    }

    function getInvoicesByVatPercentExport() {
        $wInvoicesByVatPercent = getCPWidgetObj('tradingsg_invoicesByVatPercent');
        return $wInvoicesByVatPercent->model->getExportToExcel();
    }

    function getDetailVatPercentForInvoice() {
        $wDetailVatPercentForInvoice = getCPWidgetObj('tradingsg_detailVatPercentForInvoice');
        return $wDetailVatPercentForInvoice->getWidget();
    }

    function getDetailVatPercentForInvoiceExport() {
        $wDetailVatPercentForInvoice = getCPWidgetObj('tradingsg_detailVatPercentForInvoice');
        return $wDetailVatPercentForInvoice->model->getExportToExcel();
    }

    function getDailyCollectionReport() {
        $wDailyCollectionReport = getCPWidgetObj('tradingsg_dailyCollectionReport');
        return $wDailyCollectionReport->getWidget();
    }

    function getDailyCollectionReportExport() {
        $wDailyCollectionReport = getCPWidgetObj('tradingsg_dailyCollectionReport');
        return $wDailyCollectionReport->model->getExportToExcel();
    }

    function getDetailCollectionReport() {
        $wDetailCollectionReport = getCPWidgetObj('tradingsg_detailCollectionReport');
        return $wDailyCollectionReport->getWidget();
    }

    function getDetailCollectionReportExport() {
        $wDetailCollectionReport = getCPWidgetObj('tradingsg_detailCollectionReport');
        return $wDetailCollectionReport->model->getExportToExcel();
    }

    function getQuoteByStaff() {
        $wQuoteByStaff = getCPWidgetObj('tradingsg_quoteByStaff');
        return $wQuoteByStaff->getWidget();
    }

    function getQuoteByStaffExport() {
        $wQuoteByStaff = getCPWidgetObj('tradingsg_quoteByStaff');
        return $wQuoteByStaff->model->getExportToExcel();
    }

    function getSummaryByClient() {
        $wSummaryByClient= getCPWidgetObj('tradingsg_summaryByClient');
        return $wSummaryByClient->getWidget();
    }

    function getSummaryByClientExport() {
        $wSummaryByClient = getCPWidgetObj('tradingsg_summaryByClient');
        return $wSummaryByClient->model->getExportToExcel();
    }

    function getDetailSummaryByClient() {
        $wDetailSummaryByClient= getCPWidgetObj('tradingsg_detailSummaryByClient');
        return $wDetailSummaryByClient->getWidget();
    }

    function getDetailSummaryByClientExport() {
        $wDetailSummaryByClient = getCPWidgetObj('tradingsg_detailSummaryByClient');
        return $wDetailSummaryByClient->model->getExportToExcel();
    }






    function getSummaryPurchaseSales() {
        $wSummaryPurchaseSalesReport= getCPWidgetObj('tradingsg_summaryPurchaseSalesReport');
        return $wSummaryPurchaseSalesReport->getWidget();
    }

    function getSummaryPurchaseSalesExport() {
        $wSummaryPurchaseSalesReport = getCPWidgetObj('tradingsg_summaryPurchaseSalesReport');
        return $wSummaryPurchaseSalesReport->model->getExportToExcel();
    }

    function getSummaryPurchase() {
        $wSummaryPurchaseReport= getCPWidgetObj('tradingsg_summaryPurchaseReport');
        return $wSummaryPurchaseReport->getWidget();
    }

    function getSummaryPurchaseExport() {
        $wSummaryPurchaseReport = getCPWidgetObj('tradingsg_summaryPurchaseReport');
        return $wSummaryPurchaseReport->model->getExportToExcel();
    }

    function getSummarySales() {
        $wSummarySalesReport= getCPWidgetObj('tradingsg_summarySalesReport');
        return $wSummarySalesReport->getWidget();
    }

    function getSummarySalesExport() {
        $wSummarySalesReport = getCPWidgetObj('tradingsg_summarySalesReport');
        return $wSummarySalesReport->model->getExportToExcel();
    }

    function getOverallGstSummary() {
        $wOverallGstSummary = getCPWidgetObj('tradingsg_overallGstSummary');
        return $wOverallGstSummary->getWidget();
    }

    function getOverallGstSummaryExport() {
        $wOverallGstSummary = getCPWidgetObj('tradingsg_overallGstSummary');
        return $wOverallGstSummary->model->getExportToExcel();
    }

    function getOverallSalesSummary() {
        $wOverallSalesSummary = getCPWidgetObj('tradingsg_overallSalesSummary');
        return $wOverallSalesSummary->getWidget();
    }

    function getOverallSalesSummaryExport() {
        $wOverallSalesSummary = getCPWidgetObj('tradingsg_overallSalesSummary');
        return $wOverallSalesSummary->model->getExportToExcel();
    }

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
        $modObj = $modules->getModuleObj('payroll_reports');
        return $modObj->model->getEmployeeByEmployeeStatus();
    }
}