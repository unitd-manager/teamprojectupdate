<?
class CP_Admin_Modules_Pms_Reports_Controller extends CP_Common_Lib_ModuleControllerAbstract
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
        $wTraineeByBatch = getCPWidgetObj('pms_traineeByBatch');
        return $wTraineeByBatch->getWidget();
    }

    function getTraineeByBatchExport() {
        $wTraineeByBatch = getCPWidgetObj('pms_traineeByBatch');
        return $wTraineeByBatch->model->getExportToExcel();
    }

    function getAttendanceReportsExport() {
        $wTraineeByBatch = getCPWidgetObj('pms_attendanceReports');
        return $wTraineeByBatch->model->getExportToExcel();
    }

    function getIncomeByCourse() {
        $wIncomeByCourse = getCPWidgetObj('pms_incomeByCourse');
        return $wIncomeByCourse->getWidget();
    }

    function getIncomeByCourseExport() {
        $wIncomeByCourse = getCPWidgetObj('pms_incomeByCourse');
        return $wIncomeByCourse->model->getExportToExcel();
    }

    function getTraineeByCourse() {
        $wTraineeByCourse = getCPWidgetObj('pms_traineeByCourse');
        return $wTraineeByCourse->getWidget();
    }

    function getTraineeByCourseExport() {
        $wTraineeByCourse = getCPWidgetObj('pms_traineeByCourse');
        return $wTraineeByCourse->model->getExportToExcel();
    }

    function getTraineeByMonth() {
        $wTraineeByMonth = getCPWidgetObj('pms_traineeByMonth');
        return $wTraineeByMonth->getWidget();
    }

    function getTraineeByMonthExport() {
        $wTraineeByMonth = getCPWidgetObj('pms_traineeByMonth');
        return $wTraineeByMonth->model->getExportToExcel();
    }


    function getEnrollmentStatus() {
        $wIncomeByCourse = getCPWidgetObj('pms_enrollmentStatus');
        return $wIncomeByCourse->getWidget();
    }

    function getEnrollmentStatusExport() {
        $wEnrollmentStatus = getCPWidgetObj('pms_enrollmentStatus');
        return $wEnrollmentStatus->model->getExportToExcel();
    }

    function getMonthlyEnrollmentReports() {
        $wMonthlyEnrollmentReports = getCPWidgetObj('pms_monthlyEnrollment');
        return $wMonthlyEnrollmentReports->getWidget();
    }

    function getMonthlyEnrollmentReportsExport() {
        $wMonthlyEnrollmentReports = getCPWidgetObj('pms_monthlyEnrollment');
        return $wMonthlyEnrollmentReports->model->getExportToExcel();
    }


    function getIncomeByStudent() {
        $wIncomeByStudent = getCPWidgetObj('pms_incomeByStudent');
        return $wIncomeByStudent->getWidget();
    }

    function getIncomeByStudentExport() {
        $wIncomeByStudent = getCPWidgetObj('pms_incomeByStudent');
        return $wIncomeByStudent->model->getExportToExcel();
    }

    function getIncomeExpenses() {
        $wIncomeExpenses = getCPWidgetObj('pms_incomeExpenses');
        return $wIncomeExpenses->getWidget();
    }

    function getIncomeExpensesExport() {
        $wIncomeExpenses = getCPWidgetObj('pms_incomeExpenses');
        return $wIncomeExpenses->model->getExportToExcel();
    }

    function getAttendanceReports() {
        $wAttendanceReports = getCPWidgetObj('pms_attendanceReports');
        return $wAttendanceReports->getWidget();
    }


    function getMonthlyEnrollmentByDateReports() {
        $wMonthlyEnrollmentByDateReports = getCPWidgetObj('pms_monthlyEnrollmentByDate');
        return $wMonthlyEnrollmentByDateReports->getWidget();
    }

    function getMonthlyEnrollmentByDateReportsExport() {
        $wMonthlyEnrollmentByDateReports = getCPWidgetObj('pms_monthlyEnrollmentByDate');
        return $wMonthlyEnrollmentByDateReports->model->getExportToExcel();
    }

    function getStudentStatusReports() {
        $wStudentStatusReports = getCPWidgetObj('pms_studentStatusReports');
        return $wStudentStatusReports->getWidget();
    }

    function getStudentStatusReportsExport() {
        $wStudentStatusReports = getCPWidgetObj('pms_studentStatusReports');
        return $wStudentStatusReports->model->getExportToExcel();
    }

    function getStudentProgressionReports() {
        $wStudentProgressionReports = getCPWidgetObj('pms_studentProgressionReports');
        return $wStudentProgressionReports->getWidget();
    }

    function getAttendanceReportBySubject() {
        $wAttendanceReportBySubject = getCPWidgetObj('pms_attendanceReportBySubject');
        return $wAttendanceReportBySubject->getWidget();
    }

    function getAttendanceReportBySubjectExport() {
        $wAttendanceReportBySubject = getCPWidgetObj('pms_attendanceReportBySubject');
        return $wAttendanceReportBySubject->model->getExportToExcel();
    }

    function getDailyAccountsReport() {
        $wDailyAccountsReport = getCPWidgetObj('pms_dailyAccountsReport');
        return $wDailyAccountsReport->getWidget();
    }

    function getDailyAccountsReportExport() {
        $wDailyAccountsReport = getCPWidgetObj('pms_dailyAccountsReport');
        return $wDailyAccountsReport->model->getExportToExcel();
    }

    function getStaffAttendanceReport() {
        $wStaffAttendanceReport = getCPWidgetObj('pms_staffAttendanceReport');
        return $wStaffAttendanceReport->getWidget();
    }

    function getStaffAttendanceReportExport() {
        $wStaffAttendanceReport = getCPWidgetObj('pms_staffAttendanceReport');
        return $wStaffAttendanceReport->model->getExportToExcel();
    }

    function getStaffAttendanceOverallReport() {
        $wStaffAttendanceOverallReport = getCPWidgetObj('pms_staffAttendanceOverallReport');
        return $wStaffAttendanceOverallReport->getWidget();
    }

    function getStaffAttendanceOverallReportExport() {
        $wStaffAttendanceOverallReport = getCPWidgetObj('pms_staffAttendanceOverallReport');
        return $wStaffAttendanceOverallReport->model->getExportToExcel();
    }

    function getResultSubmissionReports() {
        $wAttendanceReports = getCPWidgetObj('pms_resultSubmissionReports');
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
        $wPaymentOutstandingReport = getCPWidgetObj('pms_paymentOutstandingReport');
        return $wPaymentOutstandingReport->getWidget();
    }

    function getIncomeByStudentEntExport() {
        $wIncomeByStudentEnt = getCPWidgetObj('pms_incomeByStudentEnt');
        return $wIncomeByStudentEnt->model->getExportToExcel();
    }

    function getTeacherAttendanceReportEnt() {
        $wTeacherAttendanceReportEnt = getCPWidgetObj('pms_teacherAttendanceReportEnt');
        return $wTeacherAttendanceReportEnt->getWidget();
    }

    function getTeacherAttendanceReportEntExport() {
        $wTeacherAttendanceReportEnt = getCPWidgetObj('pms_teacherAttendanceReportEnt');
        return $wTeacherAttendanceReportEnt->model->getExportToExcel();
    }

    function getReceiptSummary() {
        $wReceiptSummary = getCPWidgetObj('pms_receiptSummary');
        return $wReceiptSummary->getWidget();
    }

    function getReceiptSummaryExport() {
        $wReceiptSummary = getCPWidgetObj('pms_receiptSummary');
        return $wReceiptSummary->model->getExportToExcel();
    }

    function getPaymentOutstandingReportExport() {
        $wPaymentOutstandingReport = getCPWidgetObj('pms_paymentOutstandingReport');
        return $wPaymentOutstandingReport->model->getExportToExcel();
    }

    function getStudentEnrollmentReport() {
        $wStudentEnrollmentReport = getCPWidgetObj('pms_studentEnrollmentReport');
        return $wStudentEnrollmentReport->getWidget();
    }

    function getStudentEnrollmentReportExport() {
        $wStudentEnrollmentReport = getCPWidgetObj('pms_studentEnrollmentReport');
        return $wStudentEnrollmentReport->model->getExportToExcel();
    }

    function getMonthlyFinancialReport() {
        $wMonthlyFinancialReport = getCPWidgetObj('pms_monthlyFinancialReport');
        return $wMonthlyFinancialReport->getWidget();
    }

    function getMonthlyFinancialReportExport() {
        $wStudentEnrollmentReport = getCPWidgetObj('pms_monthlyFinancialReport');
        return $wStudentEnrollmentReport->model->getExportToExcel();
    }

    function getStatementOfAccountReport() {
        $wStatementofAccountReport = getCPWidgetObj('pms_statementOfAccountReport');
        return $wStatementofAccountReport->getWidget();
    }

    function getStatementOfAccountReportExport() {
        $wStatementofAccountReport = getCPWidgetObj('pms_statementOfAccountReport');
        return $wStatementofAccountReport->model->getExportToExcel();
    }

    function getEnrollmentByYearReport() {
        $wEnrollmentByYearReport = getCPWidgetObj('pms_enrollmentByYearReport');
        return $wEnrollmentByYearReport->getWidget();
    }

    function getEnrollmentByYearReportExport() {
        $wEnrollmentByYearReport = getCPWidgetObj('pms_enrollmentByYearReport');
        return $wEnrollmentByYearReport->model->getExportToExcel();
    }

    function getEnrollmentBySummaryReport() {
        $wEnrollmentBySummaryReport = getCPWidgetObj('pms_enrollmentBySummaryReport');
        return $wEnrollmentBySummaryReport->getWidget();
    }

    function getEnrollmentBySummaryReportExport() {
        $wEnrollmentBySummaryReport = getCPWidgetObj('pms_enrollmentBySummaryReport');
        return $wEnrollmentBySummaryReport->model->getExportToExcel();
    }

    function getGiroFailureReport() {
        $wGiroFailureReport = getCPWidgetObj('pms_giroFailureReport');
        return $wGiroFailureReport->getWidget();
    }

    function getGiroFailureReportExport() {
        $wGiroFailureReport = getCPWidgetObj('pms_giroFailureReport');
        return $wGiroFailureReport->model->getExportToExcel();
    }

    function getGiroFailureByMonthReport() {
        $wGiroFailureByMonthReport = getCPWidgetObj('pms_giroFailureByMonthReport');
        return $wGiroFailureByMonthReport->getWidget();
    }

    function getGiroFailureByMonthReportExport() {
        $wGiroFailureByMonthReport = getCPWidgetObj('pms_giroFailureByMonthReport');
        return $wGiroFailureByMonthReport->model->getExportToExcel();
    }

    function getOverdueReport() {
        $wOverdueReport = getCPWidgetObj('pms_overdueReport');
        return $wOverdueReport->getWidget();
    }

    function getOverdueReportExport() {
        $wOverdueReport = getCPWidgetObj('pms_overdueReport');
        return $wOverdueReport->model->getExportToExcel();
    }

    function getStudentParentReport() {
        $wStudentParentReport = getCPWidgetObj('pms_studentParentReport');
        return $wStudentParentReport->getWidget();
    }

    function getStudentParentReportExport() {
        $wStudentParentReport = getCPWidgetObj('pms_studentParentReport');
        return $wStudentParentReport->model->getExportToExcel();
    }

    function getAttendanceSummaryReport() {
        $wAttendanceSummaryReport = getCPWidgetObj('pms_attendanceSummaryReport');
        return $wAttendanceSummaryReport->getWidget();
    }

    function getAttendanceSummaryReportExport() {
        $wAttendanceSummaryReport = getCPWidgetObj('pms_attendanceSummaryReport');
        return $wAttendanceSummaryReport->model->getExportToExcel();
    }

    function getAttendanceDetailReport() {
        $wAttendanceDetailReport = getCPWidgetObj('pms_attendanceDetailReport');
        return $wAttendanceDetailReport->getWidget();
    }

    function getAttendanceDetailReportExport() {
        $wAttendanceDetailReport = getCPWidgetObj('pms_attendanceDetailReport');
        return $wAttendanceDetailReport->model->getExportToExcel();
    }

    function getAttendanceAbsentReport() {
        $wAttendanceAbsentReport = getCPWidgetObj('pms_attendanceAbsentReport');
        return $wAttendanceAbsentReport->getWidget();
    }

    function getAttendanceAbsentReportExport() {
        $wAttendanceAbsentReport = getCPWidgetObj('pms_attendanceAbsentReport');
        return $wAttendanceAbsentReport->model->getExportToExcel();
    }

    function getAssessmentReport() {
        $wAssessmentReport = getCPWidgetObj('pms_assessmentReport');
        return $wAssessmentReport->getWidget();
    }

    function getAssessmentReportExport() {
        $wAssessmentReport = getCPWidgetObj('pms_assessmentReport');
        return $wAssessmentReport->model->getExportToExcel();
    }

    function getSmsBroadcastReport() {
        $wSmsBroadcastReport = getCPWidgetObj('pms_smsBroadcastReport');
        return $wSmsBroadcastReport->getWidget();
    }

    function getSmsBroadcastReportExport() {
        $wSmsBroadcastReport = getCPWidgetObj('pms_smsBroadcastReport');
        return $wSmsBroadcastReport->model->getExportToExcel();
    }
}