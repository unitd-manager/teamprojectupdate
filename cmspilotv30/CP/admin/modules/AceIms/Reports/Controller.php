<?
class CP_Admin_Modules_AceIms_Reports_Controller extends CP_Common_Lib_ModuleControllerAbstract
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
        $wTraineeByBatch = getCPWidgetObj('aceIms_traineeByBatch');
        return $wTraineeByBatch->getWidget();
    }

    function getTraineeByBatchExport() {
        $wTraineeByBatch = getCPWidgetObj('aceIms_traineeByBatch');
        return $wTraineeByBatch->model->getExportToExcel();
    }

    function getAttendanceReportsExport() {
        $wTraineeByBatch = getCPWidgetObj('aceIms_attendanceReports');
        return $wTraineeByBatch->model->getExportToExcel();
    }

    function getIncomeByCourse() {
        $wIncomeByCourse = getCPWidgetObj('aceIms_incomeByCourse');
        return $wIncomeByCourse->getWidget();
    }

    function getIncomeByCourseExport() {
        $wIncomeByCourse = getCPWidgetObj('aceIms_incomeByCourse');
        return $wIncomeByCourse->model->getExportToExcel();
    }
    
    function getTraineeByCourse() {
        $wTraineeByCourse = getCPWidgetObj('aceIms_traineeByCourse');
        return $wTraineeByCourse->getWidget();
    }

    function getTraineeByCourseExport() {
        $wTraineeByCourse = getCPWidgetObj('aceIms_traineeByCourse');
        return $wTraineeByCourse->model->getExportToExcel();
    }
    
    function getTraineeByMonth() {
        $wTraineeByMonth = getCPWidgetObj('aceIms_traineeByMonth');
        return $wTraineeByMonth->getWidget();
    }

    function getTraineeByMonthExport() {
        $wTraineeByMonth = getCPWidgetObj('aceIms_traineeByMonth');
        return $wTraineeByMonth->model->getExportToExcel();
    }
    
    function getEnrollmentStatus() {
        $wIncomeByCourse = getCPWidgetObj('aceIms_enrollmentStatus');
        return $wIncomeByCourse->getWidget();
    }

    function getEnrollmentStatusExport() {
        $wEnrollmentStatus = getCPWidgetObj('aceIms_enrollmentStatus');
        return $wEnrollmentStatus->model->getExportToExcel();
    }

    function getMonthlyEnrollmentReports() {
        $wMonthlyEnrollmentReports = getCPWidgetObj('aceIms_monthlyEnrollment');
        return $wMonthlyEnrollmentReports->getWidget();
    }

    function getMonthlyEnrollmentReportsExport() {
        $wMonthlyEnrollmentReports = getCPWidgetObj('aceIms_monthlyEnrollment');
        return $wMonthlyEnrollmentReports->model->getExportToExcel();
    }

    function getIncomeByStudent() {
        $wIncomeByStudent = getCPWidgetObj('aceIms_incomeByStudent');
        return $wIncomeByStudent->getWidget();
    }

    function getIncomeByStudentExport() {
        $wIncomeByStudent = getCPWidgetObj('aceIms_incomeByStudent');
        return $wIncomeByStudent->model->getExportToExcel();
    }

    function getIncomeExpenses() {
        $wIncomeExpenses = getCPWidgetObj('aceIms_incomeExpenses');
        return $wIncomeExpenses->getWidget();
    }

    function getIncomeExpensesExport() {
        $wIncomeExpenses = getCPWidgetObj('aceIms_incomeExpenses');
        return $wIncomeExpenses->model->getExportToExcel();
    }

    function getAttendanceReports() {
        $wAttendanceReports = getCPWidgetObj('aceIms_attendanceReports');
        return $wAttendanceReports->getWidget();
    }


    function getMonthlyEnrollmentByDateReports() {
        $wMonthlyEnrollmentByDateReports = getCPWidgetObj('aceIms_monthlyEnrollmentByDate');
        return $wMonthlyEnrollmentByDateReports->getWidget();
    }

    function getMonthlyEnrollmentByDateReportsExport() {
        $wMonthlyEnrollmentByDateReports = getCPWidgetObj('aceIms_monthlyEnrollmentByDate');
        return $wMonthlyEnrollmentByDateReports->model->getExportToExcel();
    }

    function getStudentStatusReports() {
        $wStudentStatusReports = getCPWidgetObj('aceIms_studentStatusReports');
        return $wStudentStatusReports->getWidget();
    }

    function getStudentStatusReportsExport() {
        $wStudentStatusReports = getCPWidgetObj('aceIms_studentStatusReports');
        return $wStudentStatusReports->model->getExportToExcel();
    }

    function getStudentProgressionReports() {
        $wStudentProgressionReports = getCPWidgetObj('aceIms_studentProgressionReports');
        return $wStudentProgressionReports->getWidget();
    }

    function getStudentProgressionReportsExport() {
        $wStudentProgressionReports = getCPWidgetObj('aceIms_studentProgressionReports');
        return $wStudentProgressionReports->model->getExportToExcel();
    }

    function getAttendanceReportBySubject() {
        $wAttendanceReportBySubject = getCPWidgetObj('aceIms_attendanceReportBySubject');
        return $wAttendanceReportBySubject->getWidget();
    }

    function getAttendanceReportBySubjectExport() {
        $wAttendanceReportBySubject = getCPWidgetObj('aceIms_attendanceReportBySubject');
        return $wAttendanceReportBySubject->model->getExportToExcel();
    }

    function getDailyAccountsReport() {
        $wDailyAccountsReport = getCPWidgetObj('aceIms_dailyAccountsReport');
        return $wDailyAccountsReport->getWidget();
    }
    
    function getDailyAccountsReportExport() {
        $wDailyAccountsReport = getCPWidgetObj('aceIms_dailyAccountsReport');
        return $wDailyAccountsReport->model->getExportToExcel();
    }

    function getStaffAttendanceReport() {
        $wStaffAttendanceReport = getCPWidgetObj('aceIms_staffAttendanceReport');
        return $wStaffAttendanceReport->getWidget();
    }

    function getStaffAttendanceReportExport() {
        $wStaffAttendanceReport = getCPWidgetObj('aceIms_staffAttendanceReport');
        return $wStaffAttendanceReport->model->getExportToExcel();
    }

    function getStaffAttendanceOverallReport() {
        $wStaffAttendanceOverallReport = getCPWidgetObj('aceIms_staffAttendanceOverallReport');
        return $wStaffAttendanceOverallReport->getWidget();
    }

    function getStaffAttendanceOverallReportExport() {
        $wStaffAttendanceOverallReport = getCPWidgetObj('aceIms_staffAttendanceOverallReport');
        return $wStaffAttendanceOverallReport->model->getExportToExcel();
    }

    function getResultSubmissionReports() {
        $wAttendanceReports = getCPWidgetObj('aceIms_resultSubmissionReports');
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
        $wPaymentOutstandingReport = getCPWidgetObj('aceIms_paymentOutstandingReport');
        return $wPaymentOutstandingReport->getWidget();
    }

    function getIncomeByStudentEntExport() {
        $wIncomeByStudentEnt = getCPWidgetObj('aceIms_incomeByStudentEnt');
        return $wIncomeByStudentEnt->model->getExportToExcel();
    }

    function getTeacherAttendanceReportEnt() {
        $wTeacherAttendanceReportEnt = getCPWidgetObj('aceIms_teacherAttendanceReportEnt');
        return $wTeacherAttendanceReportEnt->getWidget();
    }

    function getTeacherAttendanceReportEntExport() {
        $wTeacherAttendanceReportEnt = getCPWidgetObj('aceIms_teacherAttendanceReportEnt');
        return $wTeacherAttendanceReportEnt->model->getExportToExcel();
    }

    function getReceiptSummary() {
        $wReceiptSummary = getCPWidgetObj('aceIms_receiptSummary');
        return $wReceiptSummary->getWidget();
    }

    function getReceiptSummaryExport() {
        $wReceiptSummary = getCPWidgetObj('aceIms_receiptSummary');
        return $wReceiptSummary->model->getExportToExcel();
    }

    function getTeacherAttendanceReport() {
        $wTeacherAttendanceReport = getCPWidgetObj('aceIms_teacherAttendanceReport');
        return $wTeacherAttendanceReport->getWidget();
    }

    function getTeacherAttendanceReportExport() {
        $wTeacherAttendanceReport = getCPWidgetObj('aceIms_teacherAttendanceReport');
        return $wTeacherAttendanceReport->model->getExportToExcel();
    }

    function getInvoiceSummaryReport() {
        $wInvoiceSummaryReport = getCPWidgetObj('aceIms_invoiceSummaryReport');
        return $wInvoiceSummaryReport->getWidget();
    }

    function getInvoiceSummaryReportExport() {
        $wInvoiceSummaryReport = getCPWidgetObj('aceIms_invoiceSummaryReport');
        return $wInvoiceSummaryReport->model->getExportToExcel();
    }

    function getInstallmentSummaryReport() {
        $wInstallmentSummaryReport = getCPWidgetObj('aceIms_installmentSummaryReport');
        return $wInstallmentSummaryReport->getWidget();
    }

    function getInstallmentSummaryReportExport() {
        $wInstallmentSummaryReport = getCPWidgetObj('aceIms_installmentSummaryReport');
        return $wInstallmentSummaryReport->model->getExportToExcel();
    }

    function getBankReconcillationReport() {
        $wBankReconcillationReport = getCPWidgetObj('aceIms_bankReconcillationReport');
        return $wBankReconcillationReport->getWidget();
    }

    function getBankReconcillationReportExport() {
        $wBankReconcillationReport = getCPWidgetObj('aceIms_bankReconcillationReport');
        return $wBankReconcillationReport->model->getExportToExcel();
    }

     function getStatementofAccountReport() {
        $wStatementofAccountReport = getCPWidgetObj('aceIms_statementofAccountReport');
        return $wStatementofAccountReport->getWidget();
    }

    function getStatementofAccountReportExport() {
        $wStatementofAccountReport = getCPWidgetObj('aceIms_statementofAccountReport');
        return $wStatementofAccountReport->model->getExportToExcel();
    }

    function getDailyCollectionReport() {
        $wDailyCollectionReport = getCPWidgetObj('aceIms_dailyCollectionReport');
        return $wDailyCollectionReport->getWidget();
    }

    function getDailyCollectionReportExport() {
        $wDailyCollectionReport = getCPWidgetObj('aceIms_dailyCollectionReport');
        return $wDailyCollectionReport->model->getExportToExcel();
    }

    function getTeacherStatusReport() {
        $wTeacherStatusReport = getCPWidgetObj('aceIms_teacherStatusReport');
        return $wTeacherStatusReport->getWidget();
    }

    function getTeacherStatusReportExport() {
        $wTeacherStatusReport = getCPWidgetObj('aceIms_teacherStatusReport');
        return $wTeacherStatusReport->model->getExportToExcel();
    }

    function getTeacherDeploymentReport() {
        $wTeacherDeploymentReport = getCPWidgetObj('aceIms_teacherDeploymentReport');
        return $wTeacherDeploymentReport->getWidget();
    }

    function getTeacherDeploymentReportExport() {
        $wTeacherDeploymentReport = getCPWidgetObj('aceIms_teacherDeploymentReport');
        return $wTeacherDeploymentReport->model->getExportToExcel();
    }

    function getTeacherPaymentReport() {
        $wTeacherPaymentReport = getCPWidgetObj('aceIms_teacherPaymentReport');
        return $wTeacherPaymentReport->getWidget();
    }

    function getTeacherPaymentReportExport() {
        $wTeacherPaymentReport = getCPWidgetObj('aceIms_teacherPaymentReport');
        return $wTeacherPaymentReport->model->getExportToExcel();
    }

    function getInvoiceListingReport() {
        $wInvoiceListingReport = getCPWidgetObj('aceIms_invoiceListingReport');
        return $wInvoiceListingReport->getWidget();
    }

    function getInvoiceListingReportExport() {
        $wInvoiceListingReport = getCPWidgetObj('aceIms_invoiceListingReport');
        return $wInvoiceListingReport->model->getExportToExcel();
    }

    function getAgeingDetailReport() {
        $wAgeingDetailReport = getCPWidgetObj('aceIms_ageingDetailReport');
        return $wAgeingDetailReport->getWidget();
    }

    function getAgeingDetailReportExport() {
        $wAgeingDetailReport = getCPWidgetObj('aceIms_ageingDetailReport');
        return $wAgeingDetailReport->model->getExportToExcel();
    }

    function getAgeingSummaryReport() {
        $wAgeingSummaryReport = getCPWidgetObj('aceIms_ageingSummaryReport');
        return $wAgeingSummaryReport->getWidget();
    }

    function getAgeingSummaryReportExport() {
        $wAgeingSummaryReport = getCPWidgetObj('aceIms_ageingSummaryReport');
        return $wAgeingSummaryReport->model->getExportToExcel();
    }

}