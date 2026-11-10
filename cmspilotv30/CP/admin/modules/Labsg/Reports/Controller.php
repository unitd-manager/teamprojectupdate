<?
class CP_Admin_Modules_Labsg_Reports_Controller extends CP_Common_Lib_ModuleControllerAbstract
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

    function getPatientVisitSummary() {
        $wPatientVisitSummary = getCPWidgetObj('labsg_patientVisitSummary');
        return $wPatientVisitSummary->getWidget();
    }

    function getPatientVisitSummaryExport() {
        $wPatientVisitSummary = getCPWidgetObj('labsg_patientVisitSummary');
        return $wPatientVisitSummary->model->getExportToExcel();
    }

    function getPatientVisitSummaryExportAsPdf() {
        $wPatientVisitSummary = getCPWidgetObj('labsg_patientVisitSummary');
        return $wPatientVisitSummary->model->getExportToPdf();
    }

    function getPatientVisitDetailReport() {
        $wPatientVisitDetailReport = getCPWidgetObj('labsg_patientVisitDetailReport');
        return $wPatientVisitDetailReport->getWidget();
    }

    function getPatientVisitDetailReportExport() {
        $wPatientVisitDetailReport = getCPWidgetObj('labsg_patientVisitDetailReport');
        return $wPatientVisitDetailReport->model->getExportToExcel();
    }

    function getPatientVisitDetailReportExportAsPdf() {
        $wPatientVisitDetailReport = getCPWidgetObj('labsg_patientVisitDetailReport');
        return $wPatientVisitDetailReport->model->getExportToPdf();
    }

    function getDailyCollectionReport() {
        $wDailyCollectionReport = getCPWidgetObj('labsg_dailyCollectionReport');
        return $wDailyCollectionReport->getWidget();
    }

    function getDailyCollectionReportExport() {
        $wDailyCollectionReport = getCPWidgetObj('labsg_dailyCollectionReport');
        return $wDailyCollectionReport->model->getExportToExcel();
    }

    function getDailyCollectionReportExportAsPdf() {
        $wDailyCollectionReportReport = getCPWidgetObj('labsg_dailyCollectionReport');
        return $wDailyCollectionReportReport->model->getExportToPdf();
    }

    function getMasterFinanceSummaryReport() {
        $wMasterFinanceSummaryReport = getCPWidgetObj('labsg_masterFinanceSummaryReport');
        return $wMasterFinanceSummaryReport->getWidget();
    }

    function getMasterFinanceSummaryReportExportAsPdf() {
        $wMasterFinanceSummaryReport = getCPWidgetObj('labsg_masterFinanceSummaryReport');
        return $wMasterFinanceSummaryReport->model->getExportToPdf();
    }

    function getMasterFinanceSummaryReportExport() {
        $wMasterFinanceSummaryReport = getCPWidgetObj('labsg_masterFinanceSummaryReport');
        return $wMasterFinanceSummaryReport->model->getExportToExcel();
    }

    function getRevenueByDay() {
        $wRevenueByDay = getCPWidgetObj('labsg_revenueByDay');
        return $wRevenueByDay->getWidget();
    }

    function getRevenueByDayExport() {
        $wRevenueByDay = getCPWidgetObj('labsg_revenueByDay');
        return $wRevenueByDay->model->getExportToExcel();
    }

    function getRevenueByDayExportAsPdf() {
        $wRevenueByDay = getCPWidgetObj('labsg_revenueByDay');
        return $wRevenueByDay->model->getExportToPdf();
    }

    function getRevenueByMonth() {
        $wRevenueByMonth = getCPWidgetObj('labsg_revenueByMonth');
        return $wRevenueByMonth->getWidget();
    }

    function getRevenueByMonthExport() {
        $wRevenueByMonth = getCPWidgetObj('labsg_revenueByMonth');
        return $wRevenueByMonth->model->getExportToExcel();
    }

    function getRevenueByMonthExportAsPdf() {
        $wRevenueByMonth = getCPWidgetObj('labsg_revenueByMonth');
        return $wRevenueByMonth->model->getExportToPdf();
    }

    function getTreatmentHistory() {
        $wTreatmentHistory = getCPWidgetObj('labsg_treatmentHistory');
        return $wTreatmentHistory->getWidget();
    }

    /* Not working in treatment History widget # model.php. So written in another file by ARIF */
    function getTreatmentHistoryExport() {
        $wTreatmentHistory = getCPWidgetObj('labsg_revenueByMonth');
        return $wTreatmentHistory->view->getExportToExcelTreatment();
    }

    function getTreatmentHistoryExportAsPdf() {
        $wTreatmentHistory = getCPWidgetObj('labsg_revenueByMonth');
        return $wTreatmentHistory->view->getExportToPdfTreatment();
    }

    function getVisitByDay() {
        $wVisitByDay = getCPWidgetObj('labsg_visitByDay');
        return $wVisitByDay->getWidget();
    }

    function getVisitByDayExport() {
        $wVisitByDay = getCPWidgetObj('labsg_visitByDay');
        return $wVisitByDay->model->getExportToExcel();
    }

    function getVisitByDayExportAsPdf() {
        $wVisitByDay = getCPWidgetObj('labsg_visitByDay');
        return $wVisitByDay->model->getExportToPdf();
    }

    function getInvoiceSummary() {
        $wInvoiceSummary = getCPWidgetObj('labsg_invoiceSummary');
        return $wInvoiceSummary->getWidget();
    }

    function getInvoiceSummaryExport() {
        $wInvoiceSummary = getCPWidgetObj('labsg_invoiceSummary');
        return $wInvoiceSummary->model->getExportToExcel();
    }

    function getInvoiceSummaryExportAsPdf() {
        $wInvoiceSummary = getCPWidgetObj('labsg_invoiceSummary');
        return $wInvoiceSummary->model->getExportToPdf();
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

    function getDailyCollectionReport1() {
        $wDailyCollectionReport = getCPWidgetObj('tradingsg_dailyCollectionReport');
        return $wDailyCollectionReport->getWidget();
    }

    function getDailyCollectionReportExport1() {
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

    function getCompanyPatientSqlByBillType() {
        return $this->model->getCompanyPatientSqlByBillType();
    }

    function getClientSummaryReport() {
        $wClientSummaryReport = getCPWidgetObj('labsg_clientSummaryReport');
        return $wClientSummaryReport->getWidget();
    }

    function getClientSummaryReportExport() {
        $wClientSummaryReport = getCPWidgetObj('labsg_clientSummaryReport');
        return $wClientSummaryReport->model->getExportToExcel();
    }
}