<?
class CP_Admin_Modules_enterpriseIms_Reports_Controller extends CP_Common_Lib_ModuleControllerAbstract
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
        $wTraineeByBatch = getCPWidgetObj('enterpriseIms_traineeByBatch');
        return $wTraineeByBatch->getWidget();
    }

    function getTraineeByBatchExport() {
        $wTraineeByBatch = getCPWidgetObj('enterpriseIms_traineeByBatch');
        return $wTraineeByBatch->model->getExportToExcel();
    }

    function getAttendanceReportsExport() {
        $wTraineeByBatch = getCPWidgetObj('enterpriseIms_attendanceReports');
        return $wTraineeByBatch->model->getExportToExcel();
    }

    function getIncomeByCourse() {
        $wIncomeByCourse = getCPWidgetObj('enterpriseIms_incomeByCourse');
        return $wIncomeByCourse->getWidget();
    }

    function getIncomeByCourseExport() {
        $wIncomeByCourse = getCPWidgetObj('enterpriseIms_incomeByCourse');
        return $wIncomeByCourse->model->getExportToExcel();
    }
    
    function getTraineeByCourse() {
        $wTraineeByCourse = getCPWidgetObj('enterpriseIms_traineeByCourse');
        return $wTraineeByCourse->getWidget();
    }

    function getTraineeByCourseExport() {
        $wTraineeByCourse = getCPWidgetObj('enterpriseIms_traineeByCourse');
        return $wTraineeByCourse->model->getExportToExcel();
    }
    
    function getTraineeByMonth() {
        $wTraineeByMonth = getCPWidgetObj('enterpriseIms_traineeByMonth');
        return $wTraineeByMonth->getWidget();
    }

    function getTraineeByMonthExport() {
        $wTraineeByMonth = getCPWidgetObj('enterpriseIms_traineeByMonth');
        return $wTraineeByMonth->model->getExportToExcel();
    }    

    function getEnrollmentStatus() {
        $wIncomeByCourse = getCPWidgetObj('enterpriseIms_enrollmentStatus');
        return $wIncomeByCourse->getWidget();
    }

    function getEnrollmentStatusExport() {
        $wEnrollmentStatus = getCPWidgetObj('enterpriseIms_enrollmentStatus');
        return $wEnrollmentStatus->model->getExportToExcel();
    }

    function getMonthlyEnrollmentReports() {
        $wMonthlyEnrollmentReports = getCPWidgetObj('enterpriseIms_monthlyEnrollment');
        return $wMonthlyEnrollmentReports->getWidget();
    }

    function getMonthlyEnrollmentReportsExport() {
        $wMonthlyEnrollmentReports = getCPWidgetObj('enterpriseIms_monthlyEnrollment');
        return $wMonthlyEnrollmentReports->model->getExportToExcel();
    }

    function getIncomeByStudent() {
        $wIncomeByStudent = getCPWidgetObj('enterpriseIms_incomeByStudent');
        return $wIncomeByStudent->getWidget();
    }

    function getIncomeByStudentExport() {
        $wIncomeByStudent = getCPWidgetObj('enterpriseIms_incomeByStudent');
        return $wIncomeByStudent->model->getExportToExcel();
    }

    function getIncomeExpenses() {
        $wIncomeExpenses = getCPWidgetObj('enterpriseIms_incomeExpenses');
        return $wIncomeExpenses->getWidget();
    }

    function getIncomeExpensesExport() {
        $wIncomeExpenses = getCPWidgetObj('enterpriseIms_incomeExpenses');
        return $wIncomeExpenses->model->getExportToExcel();
    }

    function getAttendanceReports() {
        $wAttendanceReports = getCPWidgetObj('enterpriseIms_attendanceReports');
        return $wAttendanceReports->getWidget();
    }

    function getMonthlyEnrollmentByDateReports() {
        $wMonthlyEnrollmentByDateReports = getCPWidgetObj('enterpriseIms_monthlyEnrollmentByDate');
        return $wMonthlyEnrollmentByDateReports->getWidget();
    }

    function getMonthlyEnrollmentByDateReportsExport() {
        $wMonthlyEnrollmentByDateReports = getCPWidgetObj('enterpriseIms_monthlyEnrollmentByDate');
        return $wMonthlyEnrollmentByDateReports->model->getExportToExcel();
    }

    function getStudentStatusReports() {
        $wStudentStatusReports = getCPWidgetObj('enterpriseIms_studentStatusReports');
        return $wStudentStatusReports->getWidget();
    }

    function getStudentStatusReportsExport() {
        $wStudentStatusReports = getCPWidgetObj('enterpriseIms_studentStatusReports');
        return $wStudentStatusReports->model->getExportToExcel();
    }

    function getStudentProgressionReports() {
        $wStudentProgressionReports = getCPWidgetObj('enterpriseIms_studentProgressionReports');
        return $wStudentProgressionReports->getWidget();
    }

    function getAttendanceReportBySubject() {
        $wAttendanceReportBySubject = getCPWidgetObj('enterpriseIms_attendanceReportBySubject');
        return $wAttendanceReportBySubject->getWidget();
    }

    function getAttendanceReportBySubjectExport() {
        $wAttendanceReportBySubject = getCPWidgetObj('enterpriseIms_attendanceReportBySubject');
        return $wAttendanceReportBySubject->model->getExportToExcel();
    }

    function getDailyAccountsReport() {
        $wDailyAccountsReport = getCPWidgetObj('enterpriseIms_dailyAccountsReport');
        return $wDailyAccountsReport->getWidget();
    }
    
    function getDailyAccountsReportExport() {
        $wDailyAccountsReport = getCPWidgetObj('enterpriseIms_dailyAccountsReport');
        return $wDailyAccountsReport->model->getExportToExcel();
    }

    function getStaffAttendanceReport() {
        $wStaffAttendanceReport = getCPWidgetObj('enterpriseIms_staffAttendanceReport');
        return $wStaffAttendanceReport->getWidget();
    }

    function getStaffAttendanceReportExport() {
        $wStaffAttendanceReport = getCPWidgetObj('enterpriseIms_staffAttendanceReport');
        return $wStaffAttendanceReport->model->getExportToExcel();
    }

    function getStaffAttendanceOverallReport() {
        $wStaffAttendanceOverallReport = getCPWidgetObj('enterpriseIms_staffAttendanceOverallReport');
        return $wStaffAttendanceOverallReport->getWidget();
    }

    function getStaffAttendanceOverallReportExport() {
        $wStaffAttendanceOverallReport = getCPWidgetObj('enterpriseIms_staffAttendanceOverallReport');
        return $wStaffAttendanceOverallReport->model->getExportToExcel();
    }

    function getResultSubmissionReports() {
        $wAttendanceReports = getCPWidgetObj('enterpriseIms_resultSubmissionReports');
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

    function getIncomeByStudentEnt() {
        $wIncomeByStudentEnt = getCPWidgetObj('enterpriseIms_incomeByStudentEnt');
        return $wIncomeByStudentEnt->getWidget();
    }

    function getIncomeByStudentEntExport() {
        $wIncomeByStudentEnt = getCPWidgetObj('enterpriseIms_incomeByStudentEnt');
        return $wIncomeByStudentEnt->model->getExportToExcel();
    }

    function getTeacherAttendanceReportEnt() {
        $wTeacherAttendanceReportEnt = getCPWidgetObj('enterpriseIms_teacherAttendanceReportEnt');
        return $wTeacherAttendanceReportEnt->getWidget();
    }

    function getTeacherAttendanceReportEntExport() {
        $wTeacherAttendanceReportEnt = getCPWidgetObj('enterpriseIms_teacherAttendanceReportEnt');
        return $wTeacherAttendanceReportEnt->model->getExportToExcel();
    }

    function getReceiptSummary() {
        $wReceiptSummary = getCPWidgetObj('enterpriseIms_receiptSummary');
        return $wReceiptSummary->getWidget();
    }

    function getReceiptSummaryExport() {
        $wReceiptSummary = getCPWidgetObj('enterpriseIms_receiptSummary');
        return $wReceiptSummary->model->getExportToExcel();
    }

    function getPaymentOutstandingReportExport() {
        $wPaymentOutstandingReport = getCPWidgetObj('enterpriseIms_paymentOutstandingReport');
        return $wPaymentOutstandingReport->model->getExportToExcel();
    }

    function getStudentEnrollmentReport() {
        $wStudentEnrollmentReport = getCPWidgetObj('enterpriseIms_studentEnrollmentReport');
        return $wStudentEnrollmentReport->getWidget();
    }

    function getStudentEnrollmentReportExport() {
        $wStudentEnrollmentReport = getCPWidgetObj('enterpriseIms_studentEnrollmentReport');
        return $wStudentEnrollmentReport->model->getExportToExcel();
    }

    function getMonthlyFinancialReport() {
        $wMonthlyFinancialReport = getCPWidgetObj('enterpriseIms_monthlyFinancialReport');
        return $wMonthlyFinancialReport->getWidget();
    }

    function getMonthlyFinancialReportExport() {
        $wStudentEnrollmentReport = getCPWidgetObj('enterpriseIms_monthlyFinancialReport');
        return $wStudentEnrollmentReport->model->getExportToExcel();
    }
}