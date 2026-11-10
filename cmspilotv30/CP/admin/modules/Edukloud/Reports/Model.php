<?
class CP_Admin_Modules_Edukloud_Reports_Model extends CP_Common_Lib_ModuleModelAbstract
{
    var $reportsArray = array();

    function __construct() {
        $cpUtil  = Zend_Registry::get('cpUtil');

        $this->reportsArray = array( 
            'traineeByBatch'                => $this->getReportObj('traineeByBatch', 'Trainee by Batch')
           ,'traineeByCourse'               => $this->getReportObj('traineeByCourse', 'Trainee by Course')
           ,'traineeByMonth'                => $this->getReportObj('traineeByMonth', 'Trainee by Month')
           ,'incomeByCourse'                => $this->getReportObj('incomeByCourse', 'Income by Course')
           ,'enrollmentStatus'              => $this->getReportObj('enrollmentStatus', 'Enrollment Status')
           ,'monthlyEnrollmentReports'      => $this->getReportObj('monthlyEnrollmentReports', 'Monthly Enrollment Reports')
           ,'monthlyEnrollmentByDateReports'=> $this->getReportObj('monthlyEnrollmentByDateReports', 'Monthly Enrollment By Date Reports')
           ,'incomeByStudent'               => $this->getReportObj('incomeByStudent', 'Income By Student')
           ,'incomeExpenses'                => $this->getReportObj('incomeExpenses', 'Income Expenses')
           ,'attendanceReports'             => $this->getReportObj('attendanceReports', 'Attendance Reports')
           ,'studentStatusReports'          => $this->getReportObj('studentStatusReports', 'Student Status Reports')
           ,'studentProgressionReports'     => $this->getReportObj('studentProgressionReports', 'Student Progression Reports')
           ,'attendanceReportBySubject'     => $this->getReportObj('attendanceReportBySubject', 'Attendance Report By Batch/Subject')
           ,'staffAttendanceReport'         => $this->getReportObj('staffAttendanceReport', 'Staff Attendance Report')
           ,'dailyAccountsReport'           => $this->getReportObj('dailyAccountsReport', 'Daily Accounts Report')
           ,'staffAttendanceOverallReport'  => $this->getReportObj('staffAttendanceOverallReport', 'Staff Attendance Overall Report')
           ,'resultSubmissionReports'       => $this->getReportObj('resultSubmissionReports', 'Result Submission Reports')
           ,'marketingCallByStaffReport'    => $this->getReportObj('marketingCallByStaffReport', 'Marketing Call By Staff Report')
           ,'marketingCallOverallReport'    => $this->getReportObj('marketingCallOverallReport', 'Marketing Call Overall Report')
           ,'incomeByStudentEnt'            => $this->getReportObj('incomeByStudentEnt', 'Income By Student')
           ,'teacherAttendanceReportEnt'    => $this->getReportObj('teacherAttendanceReportEnt', 'Teacher Attendance Report')
        );

    }

    function getReportObj($name, $title, $searchFlds = array('dateRange')) {

        //searchFldType: uptoDate, dateRange, activeRange
        $arr = array(
             'name' => $name
            ,'title' => $title
            ,'searchFlds' => $searchFlds
        );

        return $arr;
    }
    /**
     *
     */
     function getIncomeByCourse($SQLNeeded = '') {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $text = "";
        $rows = "";
        $sqlStartDate = "";
        $sqlEndDate = "";

        $start_date = $fn->getReqParam('start_date');
        $end_date   = $fn->getReqParam('end_date');
        $status     = $fn->getReqParam('specialSearch');

        if ($status == ''){
            $status = 'Due';
        }
        
        if ($start_date != ''){
            $sqlStartDate = " AND o.creation_date >= '{$start_date}'";
        }

        if ($end_date != ''){
            $sqlEndDate = " AND o.creation_date <= '{$end_date}'";
        }
        
        //$SQL =  $this->getTraineeByCourseSQL();
        
        $SQL = "
        SELECT ABS( ABS( SUM( oi.unit_price ) ) ) AS total
              ,c.title as course_title
        FROM `order` o
        JOIN order_item oi ON oi.order_id = o.order_id
        LEFT JOIN course c ON c.course_id = oi.record_id
        WHERE o.order_status = '{$status}'
        {$sqlStartDate}
        {$sqlEndDate}
        GROUP BY oi.record_id
        ORDER BY course_title
        ";
        
        if ($SQLNeeded == 1){
            return $SQL;
        }
        
        $result = $db->sql_query($SQL);
        $resultTable = $db->sql_query($SQL);

        $rows = array(
         'course_title' 
        ,'total'
        );
        
        $columns = array(
        'Course' 
        ,'Total'
        );
        
        $text .= $fn->getTableRowsColumns($resultTable, $rows, $columns);
        
        return $text;
    }

}
