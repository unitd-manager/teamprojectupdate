<?
class CP_Admin_Modules_Edukloud_Reports_View extends CP_Common_Lib_ModuleViewAbstract
{

    var $jssKeys = array('jqForm-3.15');

    /**
     *
     */
    function getList() {
        $listObj = Zend_Registry::get('listObj');
        $cpUtil  = Zend_Registry::get('cpUtil');

        $rowCounter = 0;
        $rows = "";

        $text = "
        <div class='floatbox'>
            <div class='float_left'>
                <a href='#' class='cpBack'>back</a>
            </div>
            <div class='float_right'>
                {$this->getReportsDropdown()}
            </div>
        </div>
        <div id='reportSearchPanel' class='ui-corner-all'>
        test
        </div>
        <div id='reportContainer' class='ui-corner-all'>
        </div>
        <script type='text/javascript' src='https://www.google.com/jsapi'></script>
        <script type='text/javascript'>
            google.load('visualization', '1.0', {'packages':['corechart']});
        </script>
		";

        return $text;
    }

    /**
     *
     */
    function getReportsDropdown() {
        $listObj = Zend_Registry::get('listObj');
        $cpUtil  = Zend_Registry::get('cpUtil');
        $cpCfg  = Zend_Registry::get('cpCfg');

        $rowCounter = 0;
        $rows = "";
        $enrollmentForPvt = '';
        $incomeByStudent = '';
        $incomeExpenses = '';
        $attendanceReports = '';
        $studentStatusReports = '';
        $studentProgressionReports = '';
        $attendanceReportBySubject = '';
        $dailyAccountsReport = '';
        $staffAttendanceReport = '';
        $staffAttendanceOverallReport = '';
        $marketingCallByStaffReport = '';
        $marketingCallOverallReport = '';
        $incomeByStudentEntReport = '';
        $teacherAttendanceReport = '';

        $repArrSrc = $this->model->reportsArray;

        $repArr = array();
        foreach($repArrSrc AS $key => $val){
            $repArr[$key] = $val['title'];
        }
        
        //Monthly Enrollment Report : Used in Mass IMS
        if ($cpCfg['m.edukloud.reports.showMonthlyEnrollmentForPvt']){
            $enrollmentForPvt .= "
            <!-- <option value='monthlyEnrollmentReports'>Monthly Enrollment Reports</option> -->
            <option value='monthlyEnrollmentByDateReports'>F1 Report</option>
            <option value='monthlyEnrollmentReports'>F2 Report</option>
            ";
        }
        
        //Income By Student : Used in Mass IMS
        if ($cpCfg['m.edukloud.reports.showIncomeByStudent']){
            $incomeByStudent .= "<option value='incomeByStudent'>Income By Student</option>";
        }

        //Income Expenses : Used in Mass IMS
        if ($cpCfg['m.edukloud.reports.showIncomeExpenses']){
            $incomeExpenses .= "<option value='incomeExpenses'>Income Expenses</option>";
        }

        //Attendance Reports : Used in Mass IMS
        if ($cpCfg['m.edukloud.reports.showAttendanceReports']){
            $attendanceReports .= "<option value='attendanceReports'>Attendance Reports Overall</option>";
        }

        //Student Status Reports : Used in Mass IMS
        if ($cpCfg['m.edukloud.reports.showStudentStatusReports']){
            $studentStatusReports .= "<option value='studentStatusReports'>Student Status Reports</option>";
        }

        //Student Progression Reports : Used in Mass IMS
        if ($cpCfg['m.edukloud.reports.showStudentProgressionReports']){
            $studentProgressionReports .= "<option value='studentProgressionReports'>Student Progression Reports</option>";
        }

        //Attendance Reports : Used in Mass Ims
        if ($cpCfg['m.edukloud.reports.showAttendanceReportBySubject']){
            $attendanceReportBySubject .= "<option value='attendanceReportBySubject'>Attendance Report By Batch/Subject</option>";
        }

        //Staff Attendance Reports : Used in Mass Ims
        if ($cpCfg['m.edukloud.reports.showStaffAttendanceReport']){
            $staffAttendanceReport .= "<option value='staffAttendanceReport'>Staff Attendance Report</option>";
        }

        //Staff Attendance Overall Reports : Used in Mass Ims
        if ($cpCfg['m.edukloud.reports.showStaffAttendanceOverallReport']){
            $staffAttendanceOverallReport .= "<option value='staffAttendanceOverallReport'>Staff Attendance Overall Report</option>";
        }

        //Daily Accounts Report : Used in Mass Ims
        if ($cpCfg['m.edukloud.reports.showDailyAccountsReport']){
            $dailyAccountsReport .= "<option value='dailyAccountsReport'>Daily Accounts Report</option>";
        }

        //Marketing Call By Staff Report : Used in Manpower
        if ($cpCfg['m.edukloud.reports.showMarketingCallByStaffReport']){
            $marketingCallByStaffReport .= "<option value='marketingCallByStaffReport'>Marketing Call By Staff Report</option>";
        }

        //Marketing Call Overall Report : Used in Manpower
        if ($cpCfg['m.edukloud.reports.showMarketingCallOverallReport']){
            $marketingCallOverallReport .= "<option value='marketingCallOverallReport'>Marketing Call Overall Report</option>";
        }

        //Used in Enterprise system
        if ($cpCfg['m.edukloud.reports.showIncomeByStudentEntReport']){
            $incomeByStudentEntReport = "<option value='incomeByStudentEnt'>Financial report of Student</option>";
        }

        //Used in Enterprise system
        if ($cpCfg['m.edukloud.reports.showTeacherAttendanceReport']){
            $teacherAttendanceReport = "<option value='teacherAttendanceReportEnt'>Teacher Attendance Report</option>";
        }

        //This is used to display Reports for Pvt : Used in Mass IMS
        if ($cpCfg['m.edukloud.reports.showReportsForPvt'] == false){
            
            $specialReport = '';
            if ($cpCfg['m.edukloud.reports.showSpecialReportsForPvt']) {
                $specialReport = "
                <optgroup label='Special Reports'>
                    {$enrollmentForPvt}
                    <option value='resultSubmissionReports'>Result Submission Reports</option>
                </optgroup>
                ";
            }
            
            $text = "
            <table class='search'> 
            <tr>
                <td>
                    <select name='report' class='report'>
                        <option value=''>Please Choose the Report</option>
                        <optgroup label='Standard Reports'>
                            {$attendanceReports}
                            <!--<option value='enrollmentStatus'>Enrollment Status</option>-->
                            <option value='traineeByBatch'>Trainee by Batch</option>
                            <option value='traineeByCourse'>Trainee by Course</option>
                            <option value='traineeByMonth'>Trainee by Month</option>
                            {$teacherAttendanceReport}
                        </optgroup>
                        <optgroup label='Financial Reports'>
                            {$incomeExpenses}
                            {$incomeByStudent}
                            <option value='incomeByCourse'>Financial report of Course</option>
                            {$incomeByStudentEntReport}
                        </optgroup>
                        {$specialReport}
                    </select>
                </td>
            </tr>
            </table>
            ";
        }
        else{
            $text = "
            <table class='search'> 
            <tr>
                <td>
                    <select name='report' class='report'>
                        <option value=''>Please Choose the Report</option>
                        <optgroup label='Standard Reports'>
                            {$attendanceReports}
                            {$attendanceReportBySubject}
                            {$studentStatusReports}
                            {$studentProgressionReports}
                            {$staffAttendanceReport}
                            {$staffAttendanceOverallReport}
                            {$marketingCallByStaffReport}
                            {$marketingCallOverallReport}
                        </optgroup>
                        <optgroup label='Financial Reports'>
                            {$incomeExpenses}
                            {$incomeByStudent}
                            <option value='incomeByCourse'>Income by Course</option>
                            {$dailyAccountsReport}
                        </optgroup>
                        <optgroup label='Special Reports'>
                            {$enrollmentForPvt}
                        </optgroup>
                    </select>
                </td>
            </tr>
            </table>
            ";
        }

        return $text;
    }
    
    /**
     *
     */
    function getSearch() {
        $formObj = Zend_Registry::get('formObj');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUtil = Zend_Registry::get('cpUtil');
        $pager = Zend_Registry::get('pager');
        $db = Zend_Registry::get('db');
        $reportsArray = $this->model->reportsArray;

        $report = $fn->getReqParam('report');
        $url = "";
        $start_date = $fn->getReqParam('start_date');
        $end_date = $fn->getReqParam('end_date');
        $sort_order = $fn->getReqParam('sort_order');

        $active_start = $fn->getReqParam('active_start');
        $active_end   = $fn->getReqParam('active_end');
        $course_id    = $fn->getReqParam('course_id');
        $subject_id   = $fn->getReqParam('subject_id');
        $batch_id     = $fn->getReqParam('batch_id');
        $status       = $fn->getReqParam('status');
        $year         = $fn->getReqParam('year');
        $staff_id     = $fn->getReqParam('staff_id');
        $teacher_id   = $fn->getReqParam('teacher_id');

        $searchFldsArr = $reportsArray[$report]['searchFlds'];

        $rows = '';
        foreach($searchFldsArr AS $searchFld){
            if ($searchFld == 'dateRange'){
                $rows .= "
                <td class='dateRange'>
                    Date Range:
                    <input type='text' allowEdit='1' name='start_date' class='fld_date'
                    id='fld_start_date' value='{$start_date}' />
                    <input type='text' allowEdit='1' name='end_date' class='fld_date'
                    id='fld_end_date' value='{$end_date}' />
                </td>
                ";
            }

            if ($searchFld == 'activeRange'){
                $rows .= "
                <td class='dateRange'>
                    Active Between:
                    <input type='text' allowEdit='1' name='active_start' class='fld_date'
                    id='fld_active_start' value='{$active_start}' />
                    <input type='text' allowEdit='1' name='active_end' class='fld_date'
                    id='fld_active_end' value='{$active_end}' />
                </td>
                ";
            }

            if ($report == 'attendanceReports'){
                $rows .= "
                <td>
                    <select name='month'>
                        <option value=''>Month Filter</option>
                        <option value='01'>January</option>
                        <option value='02'>February</option>
                        <option value='03'>March</option>
                        <option value='04'>April</option>
                        <option value='05'>May</option>
                        <option value='06'>June</option>
                        <option value='07'>July</option>
                        <option value='08'>August</option>
                        <option value='09'>September</option>
                        <option value='10'>October</option>
                        <option value='11'>November</option>
                        <option value='12'>December</option>
                    </select>
                </td>";

                $sqlCourse = "
                SELECT course_id
                      ,title
                FROM course
                ";

                $rows .= "
                <td>
                    <select name='course_id'>
                        <option value=''>Select Course</option>
                        {$dbUtil->getDropDownFromSQLCols2($db, $sqlCourse, $course_id)}
                    </select>
                </td>
                ";
            }

            if ($report == 'staffAttendanceReport' || $report == 'staffAttendanceOverallReport'){

                $sqlStaff = "
                SELECT s.staff_id
                      ,CONCAT_WS(' ', s.first_name, s.last_name) AS staff_name
                FROM staff s
                WHERE s.developer = 0
                ORDER BY staff_name
                ";
                $rows .= "
                <td>
                    <select name='staff_id' class='attendanceFilter'>
                        <option value=''>Select Staff</option>
                        {$dbUtil->getDropDownFromSQLCols2($db, $sqlStaff, $staff_id)}
                    </select>
                </td>
                ";
                
                $rows .= "
                <td>
                    <select name='month' class='attendanceFilter'>
                        <option value=''>Month Filter</option>
                        <option value='01'>January</option>
                        <option value='02'>February</option>
                        <option value='03'>March</option>
                        <option value='04'>April</option>
                        <option value='05'>May</option>
                        <option value='06'>June</option>
                        <option value='07'>July</option>
                        <option value='08'>August</option>
                        <option value='09'>September</option>
                        <option value='10'>October</option>
                        <option value='11'>November</option>
                        <option value='12'>December</option>
                    </select>
                </td>
                ";

                $sqlYear = "
                SELECT DISTINCT DATE_FORMAT(sa.record_date, '%Y') AS attendance_year
                FROM staff_attendance sa
                ";
                $rows .= "
                <td>
                    <select name='year' class='attendanceFilter'>
                        <option value=''>Select Year</option>
                        {$dbUtil->getDropDownFromSQLCols1($db, $sqlYear, $year)}
                    </select>
                </td>
                ";
            }
            
            if ($report == 'teacherAttendanceReportEnt'){

                $sqlTeacher = "
                SELECT t.teacher_id
                      ,CONCAT_WS(' ', t.first_name, t.last_name) AS teacher_name
                FROM teacher t
                ORDER BY teacher_name
                ";
                $rows .= "
                <td>
                    <select name='teacher_id' class='attendanceFilter'>
                        <option value=''>Select Teacher</option>
                        {$dbUtil->getDropDownFromSQLCols2($db, $sqlTeacher, $teacher_id)}
                    </select>
                </td>
                ";
            }
            
            if ($report == 'incomeByStudentEnt'){
                $sqlYear = "
                SELECT DISTINCT DATE_FORMAT(o.order_date, '%Y') AS order_year
                FROM `order` o
                ";
                $rows .= "
                <td>
                    <select name='year' class='yearFilter'>
                        <option value=''>Select Year</option>
                        {$dbUtil->getDropDownFromSQLCols1($db, $sqlYear, $year)}
                    </select>
                </td>
                ";
            }

            if ($report == 'traineeByMonth'){
                $sqlYear = "
                SELECT DISTINCT DATE_FORMAT(i.invoice_date, '%Y') AS invoice_year
                FROM invoice i
                ";
                $rows .= "
                <td>
                    <select name='year' class='yearFilter'>
                        <option value=''>Select Year</option>
                        {$dbUtil->getDropDownFromSQLCols1($db, $sqlYear, $year)}
                    </select>
                </td>
                ";
            }

            if ($report == 'marketingCallByStaffReport'){
                $sqlStatus = $fn->getValueListSQL('callRegistryStatus');
                $rows .= "
                <td>
                    <select name='status' class='callRegistryFilter'>
                        <option value=''>Select Status</option>
                        {$dbUtil->getDropDownFromSQLCols1($db, $sqlStatus, $status)}
                    </select>
                </td>
                <td>
                    <select name='month' class='callRegistryFilter'>
                        <option value=''>Month Filter</option>
                        <option value='01'>January</option>
                        <option value='02'>February</option>
                        <option value='03'>March</option>
                        <option value='04'>April</option>
                        <option value='05'>May</option>
                        <option value='06'>June</option>
                        <option value='07'>July</option>
                        <option value='08'>August</option>
                        <option value='09'>September</option>
                        <option value='10'>October</option>
                        <option value='11'>November</option>
                        <option value='12'>December</option>
                    </select>
                </td>";
            }

            if ($report == 'attendanceReportBySubject'){
                $rows .= "
                <td>
                    <select name='month'>
                        <option value=''>Month Filter</option>
                        <option value='01'>January</option>
                        <option value='02'>February</option>
                        <option value='03'>March</option>
                        <option value='04'>April</option>
                        <option value='05'>May</option>
                        <option value='06'>June</option>
                        <option value='07'>July</option>
                        <option value='08'>August</option>
                        <option value='09'>September</option>
                        <option value='10'>October</option>
                        <option value='11'>November</option>
                        <option value='12'>December</option>
                    </select>
                </td>";

                /*
                $sqlSubject = "
                SELECT subject_id
                      ,title
                FROM subject
                WHERE title != 'Science Lab'
                ";

                $rows .= "
                <td>
                    <select name='subject_id'>
                        <option value=''>Select Subject</option>
                        {$dbUtil->getDropDownFromSQLCols2($db, $sqlSubject, $subject_id)}
                    </select>
                </td>
                ";
                */
                
                $sqlBatch = "
                SELECT batch_id
                      ,title
                FROM batch
                ";

                $rows .= "
                <td>
                    <select name='batch_id'>
                        <option value=''>Select Batch</option>
                        {$dbUtil->getDropDownFromSQLCols2($db, $sqlBatch, $batch_id)}
                    </select>
                </td>
                ";
            }
            
            if ($report == 'monthlyEnrollmentByDateReports' || $report == 'monthlyEnrollmentReports'){

                $rows .= "
                <td>
                    <select name='month'>
                        <option value=''>Month Filter</option>
                        <option value='01'>January</option>
                        <option value='02'>February</option>
                        <option value='03'>March</option>
                        <option value='04'>April</option>
                        <option value='05'>May</option>
                        <option value='06'>June</option>
                        <option value='07'>July</option>
                        <option value='08'>August</option>
                        <option value='09'>September</option>
                        <option value='10'>October</option>
                        <option value='11'>November</option>
                        <option value='12'>December</option>
                    </select>
                </td>

                <td>
                    <select name='day'>
                        <option value=''>Date Filter</option>
                        <option value='01'>01</option>
                        <option value='15'>15</option>
                    </select>
                </td>
                ";
            }

            if ($report == 'traineeByBatch' || $report == 'traineeByCourse'){
                $rows .= "
                <td>
                    <select name='specialSearch'>
                        <option value=''>Filter</option>
                        <option value='Open'>Open</option>
                        <option value='Closed'>Closed</option>
                    </select>
                </td>
                ";
            }
            else if ($report == 'studentStatusReports'){
                $sqlStatus = $fn->getValueListSQL('courseStatus');
                $rows .= "
                <td>
                    <select name='status'>
                        <option value=''>Select Status</option>
                        {$dbUtil->getDropDownFromSQLCols1($db, $sqlStatus, $status)}
                    </select>
                </td>
                ";
            }
        }

        $text = "
        <form id='reportSearch'>
        <table class='search'>
            <tr>
                <td class='resetLink'><a href='javascript:void(0);' onClick=\"javascript:$('#reportSearch').clearForm();\">reset</a></td>
                {$rows}
                <td>
                    <input type='hidden' name='report' value='{$report}'>
                    <input type='hidden' id='reportName' value='{$report}'>
                    <input type='submit' value='GO' class='button'>
                </td>
            </tr>
        </table>
        </form>
        <script>
        </script>
        ";

        return $text;
    }

    function getDisplayReport($text){
        $fn = Zend_Registry::get('fn');
        $pager = Zend_Registry::get('pager');
        $report = $fn->getReqParam('report');

        $searchQueryString = $pager->removeQueryString(array("_spAction"));
        $exportLink = "{$searchQueryString}&_spAction=exportData&report={$report}&showHTML=0";

        $text = "
        <div>
            <a href='{$exportLink}' class='exportLink button'>
                <u>Export to Excel</u>
            </a>
            {$text}
        </div>
        ";

        return $text;

        $json = array();
        $json['html'] = $text;

        return json_encode($json);
    }

    function getLiquiditySummary() {
        $fn = Zend_Registry::get('fn');

        $arr1 = $this->model->getSumByCategoryType('Cash Account');
        $arr2 = $this->model->getSumByCategoryType('Bank Account');
        $arr3 = $this->model->getSumByCategoryType('Sundry Creditor / Debtor');

        $cashTotal = abs($arr1[0] + $arr1[1]);
        $bankTotal = abs($arr2[0] + $arr2[0]);
        $recvTotal = abs($arr3[0]);
        $paybTotal = abs($arr3[1]);

        $netValue = $cashTotal + $bankTotal + $recvTotal - $paybTotal;

        $cashTotalF = number_format($cashTotal, 0);
        $bankTotalF = number_format($bankTotal, 0);
        $recvTotalF = number_format($recvTotal, 0);
        $paybTotalF = number_format($paybTotal, 0);
        $netValueF  = number_format($cashTotal + $bankTotal + $recvTotal - $paybTotal);

        $text = "
        <div id='liquiditySummary'>
            <div class='subcolumns'>
                <div class='c40l'>
                    <div class='subcl'>
                        <h1>Overall Liquidity Summary</h1>
                        <table class='thinlist'>
                            <tr>
                                <th>Cash in Hand</th>
                                <td>{$cashTotalF}</td>
                            </tr>
                            <tr>
                                <th>Cash at Banks</th>
                                <td>{$bankTotalF}</td>
                            </tr>
                            <tr>
                                <th>Receivables</th>
                                <td>{$recvTotalF}</td>
                            </tr>
                            <tr>
                                <th>Payables</th>
                                <td>{$paybTotalF}</td>
                            </tr>
                        </table>

                        <div class='netWorth'>
                            Net Worth: {$netValueF}
                        </div>
                    </div>
                </div>
                <div class='c60r'>  
                    <div class='subcr'>
                        <div class='reportLoading' id='chart_div'>
                        </div>
                        {$this->getLiquidityChart()}
                    </div>
                </div>
            </div>
        </div>
        ";

        return $text;
    }
}