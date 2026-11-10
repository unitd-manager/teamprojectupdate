<?
class CP_Admin_Modules_Edukloud_Reports_Controller extends CP_Common_Lib_ModuleControllerAbstract
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
        $wTraineeByBatch = getCPWidgetObj('edukloud_traineeByBatch');
        return $wTraineeByBatch->getWidget();
    }

    function getTraineeByBatchExport() {
        $wTraineeByBatch = getCPWidgetObj('edukloud_traineeByBatch');
        return $wTraineeByBatch->model->getExportToExcel();
    }

    function getAttendanceReportsExport() {
        $wTraineeByBatch = getCPWidgetObj('edukloud_attendanceReports');
        return $wTraineeByBatch->model->getExportToExcel();
    }

    function getIncomeByCourse() {
        $wIncomeByCourse = getCPWidgetObj('edukloud_incomeByCourse');
        return $wIncomeByCourse->getWidget();
    }

    function getIncomeByCourseExport() {
        $wIncomeByCourse = getCPWidgetObj('edukloud_incomeByCourse');
        return $wIncomeByCourse->model->getExportToExcel();
    }
    
    function getTraineeByCourse() {
        $wTraineeByCourse = getCPWidgetObj('edukloud_traineeByCourse');
        return $wTraineeByCourse->getWidget();
    }

    function getTraineeByCourseExport() {
        $wTraineeByCourse = getCPWidgetObj('edukloud_traineeByCourse');
        return $wTraineeByCourse->model->getExportToExcel();
    }
    
    function getTraineeByMonth() {
        $wTraineeByMonth = getCPWidgetObj('edukloud_traineeByMonth');
        return $wTraineeByMonth->getWidget();
    }

    function getTraineeByMonthExport() {
        $wTraineeByMonth = getCPWidgetObj('edukloud_traineeByMonth');
        return $wTraineeByMonth->model->getExportToExcel();
    }
    

    function getEnrollmentStatus() {
        $wIncomeByCourse = getCPWidgetObj('edukloud_enrollmentStatus');
        return $wIncomeByCourse->getWidget();
    }

    function getEnrollmentStatusExport() {
        $wEnrollmentStatus = getCPWidgetObj('edukloud_enrollmentStatus');
        return $wEnrollmentStatus->model->getExportToExcel();
    }

    function getMonthlyEnrollmentReports() {
        $wMonthlyEnrollmentReports = getCPWidgetObj('edukloud_monthlyEnrollment');
        return $wMonthlyEnrollmentReports->getWidget();
    }

    function getMonthlyEnrollmentReportsExport() {
        $wMonthlyEnrollmentReports = getCPWidgetObj('edukloud_monthlyEnrollment');
        return $wMonthlyEnrollmentReports->model->getExportToExcel();
    }


    function getIncomeByStudent() {
        $wIncomeByStudent = getCPWidgetObj('edukloud_incomeByStudent');
        return $wIncomeByStudent->getWidget();
    }

    function getIncomeByStudentExport() {
        $wIncomeByStudent = getCPWidgetObj('edukloud_incomeByStudent');
        return $wIncomeByStudent->model->getExportToExcel();
    }

    function getIncomeExpenses() {
        $wIncomeExpenses = getCPWidgetObj('edukloud_incomeExpenses');
        return $wIncomeExpenses->getWidget();
    }

    function getIncomeExpensesExport() {
        $wIncomeExpenses = getCPWidgetObj('edukloud_incomeExpenses');
        return $wIncomeExpenses->model->getExportToExcel();
    }

    function getAttendanceReports() {
        $wAttendanceReports = getCPWidgetObj('edukloud_attendanceReports');
        return $wAttendanceReports->getWidget();
    }


    function getMonthlyEnrollmentByDateReports() {
        $wMonthlyEnrollmentByDateReports = getCPWidgetObj('edukloud_monthlyEnrollmentByDate');
        return $wMonthlyEnrollmentByDateReports->getWidget();
    }

    function getMonthlyEnrollmentByDateReportsExport() {
        $wMonthlyEnrollmentByDateReports = getCPWidgetObj('edukloud_monthlyEnrollmentByDate');
        return $wMonthlyEnrollmentByDateReports->model->getExportToExcel();
    }

    function getStudentStatusReports() {
        $wStudentStatusReports = getCPWidgetObj('edukloud_studentStatusReports');
        return $wStudentStatusReports->getWidget();
    }

    function getStudentStatusReportsExport() {
        $wStudentStatusReports = getCPWidgetObj('edukloud_studentStatusReports');
        return $wStudentStatusReports->model->getExportToExcel();
    }

    function getStudentProgressionReports() {
        $wStudentProgressionReports = getCPWidgetObj('edukloud_studentProgressionReports');
        return $wStudentProgressionReports->getWidget();
    }

    function getAttendanceReportBySubject() {
        $wAttendanceReportBySubject = getCPWidgetObj('edukloud_attendanceReportBySubject');
        return $wAttendanceReportBySubject->getWidget();
    }

    function getAttendanceReportBySubjectExport() {
        $wAttendanceReportBySubject = getCPWidgetObj('edukloud_attendanceReportBySubject');
        return $wAttendanceReportBySubject->model->getExportToExcel();
    }

    function getDailyAccountsReport() {
        $wDailyAccountsReport = getCPWidgetObj('edukloud_dailyAccountsReport');
        return $wDailyAccountsReport->getWidget();
    }
    
    function getDailyAccountsReportExport() {
        $wDailyAccountsReport = getCPWidgetObj('edukloud_dailyAccountsReport');
        return $wDailyAccountsReport->model->getExportToExcel();
    }

    function getStaffAttendanceReport() {
        $wStaffAttendanceReport = getCPWidgetObj('edukloud_staffAttendanceReport');
        return $wStaffAttendanceReport->getWidget();
    }

    function getStaffAttendanceReportExport() {
        $wStaffAttendanceReport = getCPWidgetObj('edukloud_staffAttendanceReport');
        return $wStaffAttendanceReport->model->getExportToExcel();
    }

    function getStaffAttendanceOverallReport() {
        $wStaffAttendanceOverallReport = getCPWidgetObj('edukloud_staffAttendanceOverallReport');
        return $wStaffAttendanceOverallReport->getWidget();
    }

    function getStaffAttendanceOverallReportExport() {
        $wStaffAttendanceOverallReport = getCPWidgetObj('edukloud_staffAttendanceOverallReport');
        return $wStaffAttendanceOverallReport->model->getExportToExcel();
    }

    function getResultSubmissionReports() {
        $wAttendanceReports = getCPWidgetObj('edukloud_resultSubmissionReports');
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
        $wIncomeByStudentEnt = getCPWidgetObj('edukloud_incomeByStudentEnt');
        return $wIncomeByStudentEnt->getWidget();
    }

    function getIncomeByStudentEntExport() {
        $wIncomeByStudentEnt = getCPWidgetObj('edukloud_incomeByStudentEnt');
        return $wIncomeByStudentEnt->model->getExportToExcel();
    }


    function getTeacherAttendanceReportEnt() {
        $wTeacherAttendanceReportEnt = getCPWidgetObj('edukloud_teacherAttendanceReportEnt');
        return $wTeacherAttendanceReportEnt->getWidget();
    }

    function getTeacherAttendanceReportEntExport() {
        $wTeacherAttendanceReportEnt = getCPWidgetObj('edukloud_teacherAttendanceReportEnt');
        return $wTeacherAttendanceReportEnt->model->getExportToExcel();
    }
}