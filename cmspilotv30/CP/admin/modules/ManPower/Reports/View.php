<?
class CP_Admin_Modules_ManPower_Reports_View extends CP_Common_Lib_ModuleViewAbstract
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
        $incomeExpenses = '';

        $repArrSrc = $this->model->reportsArray;

        $repArr = array();
        foreach($repArrSrc AS $key => $val){
            $repArr[$key] = $val['title'];
        }

        $text = "
        <table class='search'>
        <tr>
            <td>
                <select name='report' class='report'>
                    <option value=''>Please Choose the Report</option>
                    <optgroup label='Standard Reports'>
                        <option value='opportunityByMonthReport'>Opportunity By Month Report</option>
                        <!--<option value='marketingCallByStaffReport'>Marketing Call By Staff Report</option>-->
                        <!--<option value='marketingCallOverallReport'>Marketing Call Overall Report</option>
                        <option value='staffAttendanceReport'>Staff Attendance Report</option>
                        <option value='staffAttendanceOverallReport'>Staff Attendance Overall Report</option>-->
                        <option value='opportunityPositionReport'>Opportunity Position Report</option>
                        <option value='projectPositionReport'>Project Position Report</option>
                        <option value='taxReport'>Tax Report</option>
                    </optgroup>
                    <!--<optgroup label='Expense Reports'>
                        <option value='incomeExpenses'>Income Expense</option>
                    </optgroup>-->
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
        $reportsArray = $this->model->reportsArray;
        $cpCfg = Zend_Registry::get('cpCfg');

        $report = $fn->getReqParam('report');
        $url = "";
        $start_date = $fn->getReqParam('start_date');
        $end_date = $fn->getReqParam('end_date');

        $active_start = $fn->getReqParam('active_start');
        $active_end   = $fn->getReqParam('active_end');
        $status       = $fn->getReqParam('status');
        $work_state   = $fn->getReqParam('work_state');
        $year         = $fn->getReqParam('year');
        $staff_id     = $fn->getReqParam('staff_id');
        $year         = $fn->getReqParam('year');
        $month        = $fn->getReqParam('month');
        $staff_id     = $fn->getReqParam('staff_id');
        $candidate_id = $fn->getReqParam('candidate_name');

        $searchFldsArr = $reportsArray[$report]['searchFlds'];

        $rows = '';
        foreach($searchFldsArr AS $searchFld){

            if ($report == 'opportunityByMonthReport'){
                $sqlYear = "
                SELECT DISTINCT DATE_FORMAT(o.enquiry_date, '%Y') AS opportunity_year
                FROM opportunity o
                ";

                $rows .= "
                <td>
                    <select name='year' class='opportunityYearFilter'>
                        <option value=''>Select Year</option>
                        {$dbUtil->getDropDownFromSQLCols1($db, $sqlYear, $year)}
                    </select>
                </td>

                <td>
                    <select name='month' class='opportunityMonthFilter'>
                        {$this->getMonthFilterValues()}
                    </select>
                </td>
                ";

                $appendStaffSql = '';
                if($cpCfg['cp.hasMultiUniqueSites'] == true) {
                    $appendStaffSql = " AND st.site_id = '{$_SESSION['cp_site_id']}'";
                }

                $sqlStaff = "
                SELECT DISTINCT s.staff_id
                      ,CONCAT_WS(' ', s.first_name, s.last_name) AS staff_name
                FROM staff s
                LEFT JOIN (opportunity_staff os) ON (s.staff_id = os.staff_id)
                LEFT JOIN (opportunity o) ON (os.opportunity_id = o.opportunity_id)
                LEFT JOIN (site st)      ON (o.site_id = st.site_id)
                WHERE s.developer = 0
                {$appendStaffSql}
                ORDER BY staff_name
                ";
                $rows .= "
                <td>
                    <select name='staff_id' class='opportunityStaffFilter'>
                        <option value=''>Select Staff</option>
                        {$dbUtil->getDropDownFromSQLCols2($db, $sqlStaff, $staff_id)}
                    </select>
                </td>
                ";

            } else if ($report == 'marketingCallOverallReport'){
                $sqlYear = "
                SELECT DISTINCT DATE_FORMAT(ca.contact_date, '%Y') AS call_registry_year
                FROM call_registry ca
                ";

                $rows .= "
                <td>
                    <select name='year' class='callRegistryYearFilter'>
                        <option value=''>Select Year</option>
                        {$dbUtil->getDropDownFromSQLCols1($db, $sqlYear, $year)}
                    </select>
                </td>

                <td>
                    <select name='month' class='callRegistryMonthFilter'>
                        {$this->getMonthFilterValues()}
                    </select>
                </td>
                ";

            } else if ($report == 'marketingCallByStaffReport'){
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
                        {$this->getMonthFilterValues()}
                    </select>
                </td>
                ";

            } else if ($report == 'opportunityPositionReport'){
                $sqlYear = "
                SELECT DISTINCT DATE_FORMAT(o.creation_date, '%Y') AS opportunity_year
                FROM opportunity o
                ";

                $rows .= "
                <td>
                    <select name='year' class='opportunityYearFilter'>
                        <option value=''>Select Year</option>
                        {$dbUtil->getDropDownFromSQLCols1($db, $sqlYear, $year)}
                    </select>
                </td>
                <td>
                    <select name='month' class='opportunityPositionFilter'>
                        {$this->getMonthFilterValues()}
                    </select>
                </td>
                ";

            } else if ($report == 'projectPositionReport'){
                $sqlYear = "
                SELECT DISTINCT DATE_FORMAT(o.creation_date, '%Y') AS opportunity_year
                FROM opportunity o
                ";

                $rows .= "
                <td>
                    <select name='year' class='projectYearFilter'>
                        <option value=''>Select Year</option>
                        {$dbUtil->getDropDownFromSQLCols1($db, $sqlYear, $year)}
                    </select>
                </td>
                <td>
                    <select name='month' class='projectPositionFilter'>
                        {$this->getMonthFilterValues()}
                    </select>
                </td>
                ";

            } else if ($report == 'taxReport'){

                $sqlYear = "
                SELECT DISTINCT DATE_FORMAT(i.start_date, '%Y') AS invoice_year
                FROM invoice i
                ";

                $candidate_name = "
                SELECT DISTINCT CONCAT_WS(' ', c.first_name, c.last_name) AS candidate_name
                FROM `invoice` i
                LEFT JOIN `order` o ON (o.order_id = i.order_id)
                LEFT JOIN `candidate` c ON (c.candidate_id = o.candidate_id)
                WHERE i.status != 'Cancelled'
                AND i.invoice_type IN('Candidate')
                ";

                $spArray = array(
                    "2013"
                   ,"2014"
                   ,"2015"
                   ,"2016"
                   ,"2017"
                   ,"2018"
                   ,"2019"
                   ,"2020"
                );

                $rows .= "
                <td class='taxWorkstateFilter'>
                    <select name='work_state'>
                        <option value=''>Select State</option>
                        {$cpUtil->getDropDown1($cpCfg['m.manPower.project.stateListArr'], $work_state)}
                    </select>
                </td>
                <td class='candidateFilter'>
                    <select name='candidate_name'>
                        <option value=''>Select Candidate</option>
                        {$dbUtil->getDropDownFromSQLCols1($db, $candidate_name, $candidate_id)}
                    </select>
                </td>
                <td class='taxYearFilter'>
                    <select name='year'>
                        <option value=''>Select Year</option>
                        {$cpUtil->getDropDown1($spArray, $year)}
                    </select>
                </td>
                <td class='taxMonthFilter'>
                    <select name='month'>
                        {$this->getMonthFilterValues()}
                    </select>
                </td>
                <td class='dateRange'>
                        Date Range:
                        <input type='text' allowEdit='1' name='start_date' class='fld_date'
                        id='fld_start_date' value='{$start_date}' />
                        <input type='text' allowEdit='1' name='end_date' class='fld_date'
                        id='fld_end_date' value='{$end_date}' />
                </td>
                ";

            } else {

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
                        {$this->getMonthFilterValues()}
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

        }

        $text = "
        <form id='reportSearch'>
        <table class='search'>
            <tr>
                <td><a href='javascript:void(0);' onClick=\"javascript:$('#reportSearch').clearForm();\">reset</a></td>
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

    /**
     *
     */
    function getMonthFilterValues() {
        return "
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
        ";
    }
}