<?
class CP_Admin_Modules_AceIms_Reports_View extends CP_Common_Lib_ModuleViewAbstract
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
        $paymentOutstandingReport = '';
        $teacherAttendanceReport = '';

        $repArrSrc = $this->model->reportsArray;

        $repArr = array();
        foreach($repArrSrc AS $key => $val){
            $repArr[$key] = $val['title'];
        }

        //Monthly Enrollment Report : Used in Mass IMS
        if ($cpCfg['m.aceIms.reports.showMonthlyEnrollmentForPvt']){
            $enrollmentForPvt .= "
            <!-- <option value='monthlyEnrollmentReports'>Monthly Enrollment Reports</option> -->
            <option value='monthlyEnrollmentByDateReports'>F1 Report</option>
            <option value='monthlyEnrollmentReports'>F2 Report</option>
            ";
        }

        //Income By Student : Used in Mass IMS
        if ($cpCfg['m.aceIms.reports.showIncomeByStudent']){
            $incomeByStudent .= "<option value='incomeByStudent'>Income By Student</option>";
        }

        //Income Expenses : Used in Mass IMS
        if ($cpCfg['m.aceIms.reports.showIncomeExpenses']){
            $incomeExpenses .= "<!--<option value='incomeExpenses'>Income Expenses</option>-->";
        }

        //Attendance Reports : Used in Mass IMS
        if ($cpCfg['m.aceIms.reports.showAttendanceReports']){
            $attendanceReports .= "<option value='attendanceReports'>Attendance Reports Overall</option>";
        }

        //Student Status Reports : Used in Mass IMS
        if ($cpCfg['m.aceIms.reports.showStudentStatusReports']){
            $studentStatusReports .= "<option value='studentStatusReports'>Student Status Reports</option>";
        }

        //Student Progression Reports : Used in Mass IMS
        if ($cpCfg['m.aceIms.reports.showStudentProgressionReports']){
            $studentProgressionReports .= "<option value='studentProgressionReports'>Student Progression Reports</option>";
        }

        //Attendance Reports : Used in Mass Ims
        if ($cpCfg['m.aceIms.reports.showAttendanceReportBySubject']){
            $attendanceReportBySubject .= "<option value='attendanceReportBySubject'>Attendance Report By Batch/Subject</option>";
        }

        //Staff Attendance Reports : Used in Mass Ims
        if ($cpCfg['m.aceIms.reports.showStaffAttendanceReport']){
            $staffAttendanceReport .= "<option value='staffAttendanceReport'>Staff Attendance Report</option>";
        }

        //Staff Attendance Overall Reports : Used in Mass Ims
        if ($cpCfg['m.aceIms.reports.showStaffAttendanceOverallReport']){
            $staffAttendanceOverallReport .= "<option value='staffAttendanceOverallReport'>Staff Attendance Overall Report</option>";
        }

        //Daily Accounts Report : Used in Mass Ims
        if ($cpCfg['m.aceIms.reports.showDailyAccountsReport']){
            $dailyAccountsReport .= "<option value='dailyAccountsReport'>Daily Accounts Report</option>";
        }

        //Marketing Call By Staff Report : Used in Manpower
        if ($cpCfg['m.aceIms.reports.showMarketingCallByStaffReport']){
            $marketingCallByStaffReport .= "<option value='marketingCallByStaffReport'>Marketing Call By Staff Report</option>";
        }

        //Marketing Call Overall Report : Used in Manpower
        if ($cpCfg['m.aceIms.reports.showMarketingCallOverallReport']){
            $marketingCallOverallReport .= "<option value='marketingCallOverallReport'>Marketing Call Overall Report</option>";
        }

        //Used in Enterprise system
        if ($cpCfg['m.aceIms.reports.showPaymentOutstandingReport']){
            $paymentOutstandingReport = "<option value='paymentOutstandingReport'>Financial report of Student</option>";
        }

        //Used in Enterprise system
        if ($cpCfg['m.aceIms.reports.showTeacherAttendanceReport']){
            $teacherAttendanceReport = "<option value='teacherAttendanceReportEnt'>Teacher Attendance Report</option>";
        }

        //This is used to display Reports for Pvt : Used in Mass IMS
        if ($cpCfg['m.aceIms.reports.showReportsForPvt'] == false){

            $specialReport = '';
            if ($cpCfg['m.aceIms.reports.showSpecialReportsForPvt']) {
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
                            <option value='receiptSummary'>Daily Collection in Summary</option>
                        </optgroup>
                        <optgroup label='Financial Reports'>
                            {$paymentOutstandingReport}
                        </optgroup>
                        <!--<optgroup label='Standard Reports'>
                            {$attendanceReports}
                            <option value='enrollmentStatus'>Enrollment Status</option>
                            <option value='traineeByBatch'>Trainee by Batch</option>
                            <option value='traineeByCourse'>Trainee by Course</option>
                            <option value='traineeByMonth'>Trainee by Month</option>
                            {$teacherAttendanceReport}
                        </optgroup>
                        <optgroup label='Financial Reports'>
                            {$incomeExpenses}
                            {$incomeByStudent}
                            <option value='incomeByCourse'>Financial report of Course</option>
                            {$paymentOutstandingReport}
                        </optgroup>-->
                        {$specialReport}
                    </select>
                </td>
            </tr>
            </table>
            ";
        }
        else{

            // Show only in local
            if ($cpCfg['cp.siteUrl'] == "http://greensafe.localhost/") {
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
                            <optgroup label='Extra Reports'>
                                <option value='teacherAttendanceReport'>Teacher Attendance Report</option>
                                <option value='invoiceSummaryReport'>Invoice Summary Report</option>
                                <option value='installmentSummaryReport'>Installment Summary Report</option>
                                <option value='bankReconcillationReport'>Bank Reconcillation Report</option>
                                <option value='statementofAccountReport'>Statement of Account Report</option>
                                <option value='dailyCollectionReport'>Daily Collection Report</option>
                                <option value='teacherStatusReport'>Teacher Status Report</option>
                                <option value='teacherDeploymentReport'>Teacher Deployment Report</option>
                                <option value='teacherPaymentReport'>Teacher Payment Report</option>
                                <option value='invoiceListingReport'>Invoice Listing Report</option>
                                <option value='ageingDetailReport'>Ageing Detail Report</option>
                                <option value='ageingSummaryReport'>Ageing Summary Report</option>
    					    </optgroup>
                        </select>
                    </td>
                </tr>
                </table>
                ";
            } else {
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
                                <!--<option value='incomeByCourse'>Income by Course</option>-->
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
        $cpCfg = Zend_Registry::get('cpCfg');
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
        $payment_mode = $fn->getReqParam('payment_mode');
        $ageing_value = $fn->getReqParam('ageing_value');

        $searchFldsArr = $reportsArray[$report]['searchFlds'];

		$spArrayYear = array (
			 '2010' => '2010'
			,'2011' => '2011'
			,'2012' => '2012'
			,'2013' => '2013'
			,'2014' => '2014'
			,'2015' => '2015'
			,'2016' => '2016'
			,'2017' => '2017'
			,'2018' => '2018'
			,'2019' => '2019'
			,'2020' => '2020'
			,'2021' => '2021'
			,'2022' => '2022'
			,'2023' => '2023'
			,'2024' => '2024'
			,'2025' => '2025'
		);

        if ($payment_mode == '') {
            $payment_mode = 'Cash';
        }

        if ($cpCfg['cp.forAceIms'] == 0) {
            if ($start_date == '') {
                $start_date = date('Y-m-d');
            }

            if ($end_date == '') {
                $end_date = date('Y-m-d');
            }
        }

        $sqlModeOfPayment = $fn->getValueListSQL('paymentType');

        $rows = '';
        foreach($searchFldsArr AS $searchFld){

            if ($report == 'studentStatusReports') {
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

            if ($report == 'paymentOutstandingReport') {
                if ($year == '') {
                    $year = date('Y');
                }

                $sqlYear = "
                SELECT DISTINCT o.year_of_enrollment
                FROM `order` o
                ";

                $rows .= "
                <td class='year_drop_down'>
                    <select name='year' class='attendanceFilter'>
                        <option value=''>Select Year</option>
                        {$dbUtil->getDropDownFromSQLCols1($db, $sqlYear, $year)}
                    </select>
                </td>

                <td class='payment_mode_receipt_summary pl10'>
                    <select name='payment_mode'>
                        <option value=''>Mode of Payment</option>
                        {$dbUtil->getDropDownFromSQLCols1($db, $sqlModeOfPayment, $payment_mode)}
                    </select>
                </td>

                <td class='ageing_values pl10'>
                    <select name='ageing_value'>
                        <option value=''>Ageing Period</option>
                        <option value='1'>Ageing by 30 days</option>
                        <option value='2'>Ageing by 60 days</option>
                        <option value='3'>Ageing by 90 days</option>
                    </select>
                </td>
                ";
            }

			if ($report == 'incomeByStudent'
			 || $report == 'incomeByCourse'
			 || $report == 'attendanceReportBySubject'
			 || $report == 'monthlyEnrollmentByDateReports'
			 || $report == 'monthlyEnrollmentReports'
			 || $report == 'dailyAccountsReport'
			) {
				$rows .="
                <td class='dateRange'>
                    Date Range:
                    <input type='text' allowEdit='1' name='start_date' class='fld_date'
                    id='fld_start_date' value='{$start_date}' />
                    <input type='text' allowEdit='1' name='end_date' class='fld_date'
                    id='fld_end_date' value='{$end_date}' />
                </td>
				";
			}

			if ($report == 'incomeExpenses') {
                $rows .= "
                <td>
                    <select name='year' class='year'>
                        <option value=''>Select Year</option>
                    	{$cpUtil->getDropDownFromArr($spArrayYear, $year)}
                    </select>
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

    		//......... EXTRA REPORTS...........\\ (FOR ONLY GREEN SAFE SITE)
            if  ($report == 'teacherAttendanceReport' || $report == 'invoiceSummaryReport'
            	     || $report == 'installmentSummaryReport'
            	     || $report == 'statementofAccountReport'
            	     || $report == 'dailyCollectionReport'
            	     || $report == 'teacherStatusReport'
            	     || $report == 'ageingDetailReport'
            	     || $report == 'invoiceListingReport'
            	     || $report == 'teacherPaymentReport'
            	     || $report == 'teacherDeploymentReport'
            	     || $report == 'bankReconcillationReport' ){

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

            if ($report == 'receiptSummary') {
                $spArray = array(
                    "Cash"
                   ,"Bank Transfer"
                   ,"Cheque"
                   ,"Nets"
                   ,"All"
                );

                $rows .= "
                <td class='payment_mode_receipt_summary pl10'>
                    <select name='payment_mode'>
                        <option value=''>Mode of Payment</option>
                        {$cpUtil->getDropDown1($spArray, $payment_mode)}
                    </select>
                </td>
                ";
            }

            if ($report == 'attendanceReports'){
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