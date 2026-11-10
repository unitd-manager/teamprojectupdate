<?
class CP_Admin_Modules_AgileIms_Reports_Controller extends CP_Common_Lib_ModuleControllerAbstract
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
    
    function getTraineeByBatch() {
        $wTraineeByBatch = getCPWidgetObj('agileIms_traineeByBatch');
        return $wTraineeByBatch->getWidget();
    }

    function getTraineeByBatchExport() {
        $wTraineeByBatch = getCPWidgetObj('agileIms_traineeByBatch');
        return $wTraineeByBatch->model->getExportToExcel();
    }

    function getAttendanceReportsExport() {
        $wTraineeByBatch = getCPWidgetObj('agileIms_attendanceReports');
        return $wTraineeByBatch->model->getExportToExcel();
    }

    function getIncomeByCourse() {
        $wIncomeByCourse = getCPWidgetObj('agileIms_incomeByCourse');
        return $wIncomeByCourse->getWidget();
    }

    function getIncomeByCourseExport() {
        $wIncomeByCourse = getCPWidgetObj('agileIms_incomeByCourse');
        return $wIncomeByCourse->model->getExportToExcel();
    }
    
    function getTraineeByCourse() {
        $wTraineeByCourse = getCPWidgetObj('agileIms_traineeByCourse');
        return $wTraineeByCourse->getWidget();
    }

    function getTraineeByCourseExport() {
        $wTraineeByCourse = getCPWidgetObj('agileIms_traineeByCourse');
        return $wTraineeByCourse->model->getExportToExcel();
    }
    
    function getTraineeByMonth() {
        $wTraineeByMonth = getCPWidgetObj('agileIms_traineeByMonth');
        return $wTraineeByMonth->getWidget();
    }

    function getTraineeByMonthExport() {
        $wTraineeByMonth = getCPWidgetObj('agileIms_traineeByMonth');
        return $wTraineeByMonth->model->getExportToExcel();
    }
    
    function getEnrollmentStatus() {
        $wIncomeByCourse = getCPWidgetObj('agileIms_enrollmentStatus');
        return $wIncomeByCourse->getWidget();
    }

    function getEnrollmentStatusExport() {
        $wEnrollmentStatus = getCPWidgetObj('agileIms_enrollmentStatus');
        return $wEnrollmentStatus->model->getExportToExcel();
    }

    function getMonthlyEnrollmentReports() {
        $wMonthlyEnrollmentReports = getCPWidgetObj('agileIms_monthlyEnrollment');
        return $wMonthlyEnrollmentReports->getWidget();
    }

    function getMonthlyEnrollmentReportsExport() {
        $wMonthlyEnrollmentReports = getCPWidgetObj('agileIms_monthlyEnrollment');
        return $wMonthlyEnrollmentReports->model->getExportToExcel();
    }

    function getIncomeByStudent() {
        $wIncomeByStudent = getCPWidgetObj('agileIms_incomeByStudent');
        return $wIncomeByStudent->getWidget();
    }

    function getIncomeByStudentExport() {
        $wIncomeByStudent = getCPWidgetObj('agileIms_incomeByStudent');
        return $wIncomeByStudent->model->getExportToExcel();
    }

    function getIncomeExpenses() {
        $wIncomeExpenses = getCPWidgetObj('agileIms_incomeExpenses');
        return $wIncomeExpenses->getWidget();
    }

    function getIncomeExpensesExport() {
        $wIncomeExpenses = getCPWidgetObj('agileIms_incomeExpenses');
        return $wIncomeExpenses->model->getExportToExcel();
    }

    function getAttendanceReports() {
        $wAttendanceReports = getCPWidgetObj('agileIms_attendanceReports');
        return $wAttendanceReports->getWidget();
    }


    function getMonthlyEnrollmentByDateReports() {
        $wMonthlyEnrollmentByDateReports = getCPWidgetObj('agileIms_monthlyEnrollmentByDate');
        return $wMonthlyEnrollmentByDateReports->getWidget();
    }

    function getMonthlyEnrollmentByDateReportsExport() {
        $wMonthlyEnrollmentByDateReports = getCPWidgetObj('agileIms_monthlyEnrollmentByDate');
        return $wMonthlyEnrollmentByDateReports->model->getExportToExcel();
    }

    function getStudentStatusReports() {
        $wStudentStatusReports = getCPWidgetObj('agileIms_studentStatusReports');
        return $wStudentStatusReports->getWidget();
    }

    function getStudentStatusReportsExport() {
        $wStudentStatusReports = getCPWidgetObj('agileIms_studentStatusReports');
        return $wStudentStatusReports->model->getExportToExcel();
    }

    function getStudentProgressionReports() {
        $wStudentProgressionReports = getCPWidgetObj('agileIms_studentProgressionReports');
        return $wStudentProgressionReports->getWidget();
    }

    function getStudentProgressionReportsExport() {
        $wStudentProgressionReports = getCPWidgetObj('agileIms_studentProgressionReports');
        return $wStudentProgressionReports->model->getExportToExcel();
    }

    function getAttendanceReportBySubject() {
        $wAttendanceReportBySubject = getCPWidgetObj('agileIms_attendanceReportBySubject');
        return $wAttendanceReportBySubject->getWidget();
    }

    function getAttendanceReportBySubjectExport() {
        $wAttendanceReportBySubject = getCPWidgetObj('agileIms_attendanceReportBySubject');
        return $wAttendanceReportBySubject->model->getExportToExcel();
    }

    function getDailyAccountsReport() {
        $wDailyAccountsReport = getCPWidgetObj('agileIms_dailyAccountsReport');
        return $wDailyAccountsReport->getWidget();
    }
    
    function getDailyAccountsReportExport() {
        $wDailyAccountsReport = getCPWidgetObj('agileIms_dailyAccountsReport');
        return $wDailyAccountsReport->model->getExportToExcel();
    }

    function getStaffAttendanceReport() {
        $wStaffAttendanceReport = getCPWidgetObj('agileIms_staffAttendanceReport');
        return $wStaffAttendanceReport->getWidget();
    }

    function getStaffAttendanceReportExport() {
        $wStaffAttendanceReport = getCPWidgetObj('agileIms_staffAttendanceReport');
        return $wStaffAttendanceReport->model->getExportToExcel();
    }

    function getStaffAttendanceOverallReport() {
        $wStaffAttendanceOverallReport = getCPWidgetObj('agileIms_staffAttendanceOverallReport');
        return $wStaffAttendanceOverallReport->getWidget();
    }

    function getStaffAttendanceOverallReportExport() {
        $wStaffAttendanceOverallReport = getCPWidgetObj('agileIms_staffAttendanceOverallReport');
        return $wStaffAttendanceOverallReport->model->getExportToExcel();
    }

    function getResultSubmissionReports() {
        $wAttendanceReports = getCPWidgetObj('agileIms_resultSubmissionReports');
        return $wAttendanceReports->getWidget();
    }

    function getMarketingCallByStaffReport() {
        $wMarketingCallByStaffReport = getCPWidgetObj('manPower_marketingCallByStaffReport');
        return $wMarketingCallByStaffReport->getWidget();
    }

    function getMarketingCallOverallReport() {
        $wMarketingCallOverallReport = getCPWidgetObj('manPower_marketingCallOverallReport');
        return $wMarketingCallOverallReport->getWidget();
    }

    function getPaymentOutstandingReport() {
        $wPaymentOutstandingReport = getCPWidgetObj('agileIms_paymentOutstandingReport');
        return $wPaymentOutstandingReport->getWidget();
    }

    function getIncomeByStudentEntExport() {
        $wIncomeByStudentEnt = getCPWidgetObj('agileIms_incomeByStudentEnt');
        return $wIncomeByStudentEnt->model->getExportToExcel();
    }

    function getTeacherAttendanceReportEnt() {
        $wTeacherAttendanceReportEnt = getCPWidgetObj('agileIms_teacherAttendanceReportEnt');
        return $wTeacherAttendanceReportEnt->getWidget();
    }

    function getTeacherAttendanceReportEntExport() {
        $wTeacherAttendanceReportEnt = getCPWidgetObj('agileIms_teacherAttendanceReportEnt');
        return $wTeacherAttendanceReportEnt->model->getExportToExcel();
    }

    function getReceiptSummary() {
        $wReceiptSummary = getCPWidgetObj('agileIms_receiptSummary');
        return $wReceiptSummary->getWidget();
    }

    function getReceiptSummaryExport() {
        $wReceiptSummary = getCPWidgetObj('agileIms_receiptSummary');
        return $wReceiptSummary->model->getExportToExcel();
    }

    function getTeacherAttendanceReport() {
        $wTeacherAttendanceReport = getCPWidgetObj('agileIms_teacherAttendanceReport');
        return $wTeacherAttendanceReport->getWidget();
    }

    function getTeacherAttendanceReportExport() {
        $wTeacherAttendanceReport = getCPWidgetObj('agileIms_teacherAttendanceReport');
        return $wTeacherAttendanceReport->model->getExportToExcel();
    }

    function getInvoiceSummaryReport() {
        $wInvoiceSummaryReport = getCPWidgetObj('agileIms_invoiceSummaryReport');
        return $wInvoiceSummaryReport->getWidget();
    }

    function getInvoiceSummaryReportExport() {
        $wInvoiceSummaryReport = getCPWidgetObj('agileIms_invoiceSummaryReport');
        return $wInvoiceSummaryReport->model->getExportToExcel();
    }

    function getInstallmentSummaryReport() {
        $wInstallmentSummaryReport = getCPWidgetObj('agileIms_installmentSummaryReport');
        return $wInstallmentSummaryReport->getWidget();
    }

    function getInstallmentSummaryReportExport() {
        $wInstallmentSummaryReport = getCPWidgetObj('agileIms_installmentSummaryReport');
        return $wInstallmentSummaryReport->model->getExportToExcel();
    }

    function getBankReconcillationReport() {
        $wBankReconcillationReport = getCPWidgetObj('agileIms_bankReconcillationReport');
        return $wBankReconcillationReport->getWidget();
    }

    function getBankReconcillationReportExport() {
        $wBankReconcillationReport = getCPWidgetObj('agileIms_bankReconcillationReport');
        return $wBankReconcillationReport->model->getExportToExcel();
    }

     function getStatementofAccountReport() {
        $wStatementofAccountReport = getCPWidgetObj('agileIms_statementofAccountReport');
        return $wStatementofAccountReport->getWidget();
    }

    function getStatementofAccountReportExport() {
        $wStatementofAccountReport = getCPWidgetObj('agileIms_statementofAccountReport');
        return $wStatementofAccountReport->model->getExportToExcel();
    }

    function getDailyCollectionReport() {
        $wDailyCollectionReport = getCPWidgetObj('agileIms_dailyCollectionReport');
        return $wDailyCollectionReport->getWidget();
    }

    function getDailyCollectionReportExport() {
        $wDailyCollectionReport = getCPWidgetObj('agileIms_dailyCollectionReport');
        return $wDailyCollectionReport->model->getExportToExcel();
    }

    function getTeacherStatusReport() {
        $wTeacherStatusReport = getCPWidgetObj('agileIms_teacherStatusReport');
        return $wTeacherStatusReport->getWidget();
    }

    function getTeacherStatusReportExport() {
        $wTeacherStatusReport = getCPWidgetObj('agileIms_teacherStatusReport');
        return $wTeacherStatusReport->model->getExportToExcel();
    }

    function getTeacherDeploymentReport() {
        $wTeacherDeploymentReport = getCPWidgetObj('agileIms_teacherDeploymentReport');
        return $wTeacherDeploymentReport->getWidget();
    }

    function getTeacherDeploymentReportExport() {
        $wTeacherDeploymentReport = getCPWidgetObj('agileIms_teacherDeploymentReport');
        return $wTeacherDeploymentReport->model->getExportToExcel();
    }

    function getTeacherPaymentReport() {
        $wTeacherPaymentReport = getCPWidgetObj('agileIms_teacherPaymentReport');
        return $wTeacherPaymentReport->getWidget();
    }

    function getTeacherPaymentReportExport() {
        $wTeacherPaymentReport = getCPWidgetObj('agileIms_teacherPaymentReport');
        return $wTeacherPaymentReport->model->getExportToExcel();
    }

    function getInvoiceListingReport() {
        $wInvoiceListingReport = getCPWidgetObj('agileIms_invoiceListingReport');
        return $wInvoiceListingReport->getWidget();
    }

    function getInvoiceListingReportExport() {
        $wInvoiceListingReport = getCPWidgetObj('agileIms_invoiceListingReport');
        return $wInvoiceListingReport->model->getExportToExcel();
    }

    function getAgeingDetailReport() {
        $wAgeingDetailReport = getCPWidgetObj('agileIms_ageingDetailReport');
        return $wAgeingDetailReport->getWidget();
    }

    function getAgeingDetailReportExport() {
        $wAgeingDetailReport = getCPWidgetObj('agileIms_ageingDetailReport');
        return $wAgeingDetailReport->model->getExportToExcel();
    }

    function getAgeingSummaryReport() {
        $wAgeingSummaryReport = getCPWidgetObj('agileIms_ageingSummaryReport');
        return $wAgeingSummaryReport->getWidget();
    }

    function getAgeingSummaryReportExport() {
        $wAgeingSummaryReport = getCPWidgetObj('agileIms_ageingSummaryReport');
        return $wAgeingSummaryReport->model->getExportToExcel();
    }

    function getSubsidyPaidHistoryReport() {
        $wSubsidyPaidHistoryReport = getCPWidgetObj('agileIms_subsidyPaidHistoryReport');
        return $wSubsidyPaidHistoryReport->getWidget();
    }

    function getSubsidyPaidHistoryReportExport() {
        $wSubsidyPaidHistoryReport = getCPWidgetObj('agileIms_subsidyPaidHistoryReport');
        return $wSubsidyPaidHistoryReport->model->getExportToExcel();
    }

    function getStatementofAccountsReport() {
        $wStatementofAccountsReport = getCPWidgetObj('agileIms_statementofAccountsReport');
        return $wStatementofAccountsReport->getWidget();
    }

    function getStatementofAccountsReportExport() {
        $wStatementofAccountsReport = getCPWidgetObj('agileIms_statementofAccountsReport');
        return $wStatementofAccountsReport->model->getExportToExcel();
    }

    function getCompanyContactSqlByEnrollmentType() {
        return $this->model->getCompanyContactSqlByEnrollmentType();
    }

    function getAgeingReport() {
        $wAgeingReport = getCPWidgetObj('agileIms_ageingReport');
        return $wAgeingReport->getWidget();
    }

    function getAgeingReportExport() {
        $wAgeingReportReport = getCPWidgetObj('agileIms_ageingReport');
        return $wAgeingReportReport->model->getExportToExcel();
    }
}