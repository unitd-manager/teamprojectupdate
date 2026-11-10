<?
class CP_Admin_Modules_AgileIms_Reports_View extends CP_Common_Lib_ModuleViewAbstract
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

        //Attendance Reports : Used in Mass IMS
        if ($cpCfg['m.agileIms.reports.showAttendanceReports']){
            $attendanceReports .= "<option value='attendanceReports'>Attendance Reports Overall</option>";
        }

        //Student Status Reports : Used in Mass IMS
        if ($cpCfg['m.agileIms.reports.showStudentStatusReports']){
            $studentStatusReports .= "<option value='studentStatusReports'>Student Status Reports</option>";
        }

        //Student Progression Reports : Used in Mass IMS
        if ($cpCfg['m.agileIms.reports.showStudentProgressionReports']){
            $studentProgressionReports .= "<option value='studentProgressionReports'>Student Progression Reports</option>";
        }

        //Attendance Reports : Used in Mass Ims
        if ($cpCfg['m.agileIms.reports.showAttendanceReportBySubject']){
            $attendanceReportBySubject .= "<option value='attendanceReportBySubject'>Attendance Report By Batch/Subject</option>";
        }

        //Staff Attendance Reports : Used in Mass Ims
        if ($cpCfg['m.agileIms.reports.showStaffAttendanceReport']){
            $staffAttendanceReport .= "<option value='staffAttendanceReport'>Staff Attendance Report</option>";
        }

        //Staff Attendance Overall Reports : Used in Mass Ims
        if ($cpCfg['m.agileIms.reports.showStaffAttendanceOverallReport']){
            $staffAttendanceOverallReport .= "<option value='staffAttendanceOverallReport'>Staff Attendance Overall Report</option>";
        }

        //Daily Accounts Report : Used in Mass Ims
        if ($cpCfg['m.agileIms.reports.showDailyAccountsReport']){
            $dailyAccountsReport .= "<option value='dailyAccountsReport'>Daily Accounts Report</option>";
        }

        //Marketing Call By Staff Report : Used in Manpower
        if ($cpCfg['m.agileIms.reports.showMarketingCallByStaffReport']){
            $marketingCallByStaffReport .= "<option value='marketingCallByStaffReport'>Marketing Call By Staff Report</option>";
        }

        //Marketing Call Overall Report : Used in Manpower
        if ($cpCfg['m.agileIms.reports.showMarketingCallOverallReport']){
            $marketingCallOverallReport .= "<option value='marketingCallOverallReport'>Marketing Call Overall Report</option>";
        }

        //Used in Enterprise system
        if ($cpCfg['m.agileIms.reports.showPaymentOutstandingReport']){
            $paymentOutstandingReport = "<option value='paymentOutstandingReport'>Financial report of Student</option>";
        }

        //Used in Enterprise system
        if ($cpCfg['m.agileIms.reports.showTeacherAttendanceReport']){
            $teacherAttendanceReport = "<option value='teacherAttendanceReportEnt'>Teacher Attendance Report</option>";
        }

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
                        <option value='incomeByStudent'>Invoice Summary</option>
                        <option value='subsidyPaidHistoryReport'>Subsidy Paid History</option>
                        <option value='statementofAccountsReport'>Statement of Accounts Report</option>
                        <option value='ageingReport'>Ageing Report</option>
                    </optgroup>
                </select>
            </td>
        </tr>
        </table>
        ";

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
        $start_date     = $fn->getReqParam('start_date');
        $end_date       = $fn->getReqParam('end_date');
        $invoice_status = $fn->getReqParam('invoice_status');

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

        $spArrayInvoiceStatus = array (
             'Due'
            ,'Cancelled'
            ,'Partial Payment'
            ,'Paid'
            ,'All'
        );

        if ($payment_mode == '') {
            $payment_mode = 'Cash';
        }

        if ($cpCfg['cp.forAgileIms'] == 0) {
            if ($start_date == '') {
                $start_date = date('Y-m-d');
            }

            if ($end_date == '') {
                $end_date = date('Y-m-d');
            }
        }

        $sqlModeOfPayment = $fn->getValueListSQL('paymentType');

        /* Statement of Accounts Report & Ageing Report */
        $enrollment_type    = $fn->getReqParam('enrollment_type');
        $company_contact_id = $fn->getReqParam('company_contact_id');

        $spArrayEnrollmentType = array (
             'Individual'
            ,'Company'
        );

        $rows = '';
        foreach($searchFldsArr AS $searchFld){

            if ($report == 'ageingReport'){
                $rows .= "
                <td><b>Enrollment Type :</b>
                    <select name='enrollment_type' class='soa_enrollment_type_filter'>
                        {$cpUtil->getDropDown1($spArrayEnrollmentType, $enrollment_type)}
                    </select>
                </td>
                ";
            }

            if ($report == 'statementofAccountsReport'){
                $sqlContact = "
                SELECT DISTINCT o.contact_id
                               ,c.first_name
                FROM `order` o
                LEFT JOIN (contact c) ON (o.contact_id = c.contact_id)
                WHERE o.contact_id != ''
                ORDER BY first_name
                ";

                $rows .= "
                <td><div><b>Enrollment Type</b></div>
                    <select name='enrollment_type' class='soa_enrollment_type_filter'>
                        {$cpUtil->getDropDown1($spArrayEnrollmentType, $enrollment_type)}
                    </select>
                </td>
                <td>&nbsp;&nbsp;&nbsp;</td>
                <td><div><b>Student/Company Name</b></div>
                    <select name='company_contact_id' class='statementofAccountsReportFilter'>
                        {$dbUtil->getDropDownFromSQLCols2($db, $sqlContact, $company_contact_id)}
                    </select>
                </td>
                <td>&nbsp;&nbsp;&nbsp;</td>
                <td class='dateRange'><div><b>Date Range</b></div>
                    <input type='text' allowEdit='1' name='start_date' class='fld_date'
                    id='fld_start_date' value='{$start_date}' />
                    <input type='text' allowEdit='1' name='end_date' class='fld_date'
                    id='fld_end_date' value='{$end_date}' />
                </td>
                ";
            }

            if ($report == 'incomeByStudent') {
                if ($invoice_status == '') {
                    $invoice_status = 'Due';
                }

                $rows .= "
                <td>
                    <select name='invoice_status'>
                        <option value=''>Select Invoice Status</option>
                        {$cpUtil->getDropDown1($spArrayInvoiceStatus, $invoice_status)}
                    </select>
                </td>
                ";
            }

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

            if ($report == 'subsidyPaidHistoryReport') {
                if ($status == '') {
                    $status = 'Paid';
                }

                $sqlStatus = $fn->getValueListSQL('subsidyStatus');
                $rows .= "
                <td>
                    <select name='status'>
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
             || $report == 'subsidyPaidHistoryReport'
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

        if ($report == 'ageingReport'){
            $text = "
            <div>
                {$text}
            </div>
            ";
        } else {
            $text = "
            <div>
                <a href='{$exportLink}' class='exportLink button'>
                    <u>Export to Excel</u>
                </a>
                {$text}
            </div>
            ";
        }

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