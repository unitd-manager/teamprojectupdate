<?
class CP_Admin_Modules_Project_Reports_View extends CP_Common_Lib_ModuleViewAbstract
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
        $reports = '';

        $repArrSrc = $this->model->reportsArray;

        $repArr = array();
        foreach($repArrSrc AS $key => $val){
            $repArr[$key] = $val['title'];
        }

        if ($_SESSION['staff_team'] == 'Admin'){
            $reports = "
            <optgroup label='Sales Reports'>
                <option value='salesByMonthReports'>Sales by Month</option>
                <option value='salesByYearReports'>Sales by Year</option>
            </optgroup>
            <optgroup label='Financial Reports'>
                <option value='invoiceByMonthReports'>Invoice by Month</option>
                <option value='invoiceByYearReports'>Invoice by Year</option>
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
                        <option value='agmFileStatusReports'>AGM File Status Reports</option>
                        <option value='officeTimeReport'>Office Time Report</option>
                        <option value='attendanceReport'>Attendance Report</option>
                        <option value='opportunityReport'>Opportunity Report</option>
                        <option value='taskHoursByStaffReport'>Task Hours By Staff Report</option>
                        <option value='detailTaskSummaryReport'>Detail Task Summary Report</option>
                        <option value='opportunityChart'>Opportunity Chart</option>
                        <option value='cPFSummaryReport'>CPF Summary Report</option>
                        <option value='employeeSalaryReport'>Employee Salary Report</option>
                        <option value='leaveReport'>Leave Report</option>
                        <option value='loanReport'>Loan Report</option>
                        <option value='allowanceReport'>Allowance Report</option>
                        <option value='sDLReport'>SDL Report</option>
                    </optgroup>
                    {$reports}
                    <optgroup label='Marketing Reports'>
                        <option value='marketingDetailReport'>Marketing detail Report</option>
                        <option value='marketingSummaryReport'>Marketing summary Report</option>
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
        $ln = Zend_Registry::get('ln');
        $reportsArray = $this->model->reportsArray;

        $report = $fn->getReqParam('report');
        $url = "";
        $start_date = $fn->getReqParam('start_date');
        $end_date = $fn->getReqParam('end_date');

        $year           = $fn->getReqParam('year');
        $month          = $fn->getReqParam('month');
        $search_by      = $fn->getReqParam('search_by');
        $staff_id       = $fn->getReqParam('staff_id');
        $company_name   = $fn->getReqParam('company_name');
        $project_name   = $fn->getReqParam('project_name');
        $yearMonthStart = $fn->getReqParam('yearMonthStart');
        $status         = $fn->getReqParam('status');
        $source_channel = $fn->getReqParam('source_channel');
        $payroll_year   = $fn->getReqParam('payroll_year');

        $searchFldsArr = $reportsArray[$report]['searchFlds'];

        $rows = '';
        foreach($searchFldsArr AS $searchFld){

            if ($report == 'agmFileStatusReports'){
                $sqlYear = "
                SELECT DISTINCT DATE_FORMAT(p.next_agm_due_date, '%Y') AS next_agm_year
                FROM project p
                ";

                $spArray = array(
                    ""
                   ,"Current Month"
                   ,"Next Month"
                   ,"Late"
                );

                $keyword = $ln->gd('cp.lbl.keywordSearch', 'Keyword Search');

                $rows .= "
                <td>
                    <select name='year' class='agmYearFilter'>
                        <option value=''>Select Year</option>
                        {$dbUtil->getDropDownFromSQLCols1($db, $sqlYear, $year)}
                    </select>
                </td>

                <td>
                    <select name='month' class='agmMonthFilter'>
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
                    <select name='search_by' class='searchBy'>
                        <option value=''>Search By</option
                        {$cpUtil->getDropDown1($spArray, $search_by)}
                   </select>
                </td>

                <td>
                    <input type='text' rel='' class='keyword' name='keyword' value='{$tv['keyword']}'>
                </td>
                ";
            } else if ($report == 'salesByYearReports' || $report == 'salesByMonthReports'
                    || $report == 'invoiceByYearReports' || $report == 'invoiceByMonthReports'){

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

            if ($report == 'officeTimeReport'){

                $sqlStaff = "
                SELECT s.staff_id
                      ,CONCAT_WS(' ', s.first_name, s.last_name) AS staff_name
                FROM staff s
		        WHERE s.team = 'In-house'
                ORDER BY staff_name
                ";
                $rows .= "
                <td>
                    <select name='staff_id' class='attendanceFilter'>
                        <option value=''>Select Staff</option>
                        {$dbUtil->getDropDownFromSQLCols2($db, $sqlStaff, $staff_id)}
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

			}

            if ($report == 'attendanceReport'){

                $sqlStaff = "
                SELECT s.staff_id
                      ,CONCAT_WS(' ', s.first_name, s.last_name) AS staff_name
                FROM staff s
				WHERE s.team = 'In-house'
                ORDER BY staff_name
                ";
                $rows .= "
                <td>
                    <select name='staff_id' class='attendanceFilter'>
                        <option value=''>Select Staff</option>
                        {$dbUtil->getDropDownFromSQLCols2($db, $sqlStaff, $staff_id)}
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

			}

            if ($report == 'opportunityReport'){

                $SQLStatus = $fn->getValueListSQL('opportunityStatus');
                $rows .= "
                <td>
                    <select name='status'>
                        <option value=''>Status</option>
                        {$dbUtil->getDropDownFromSQLCols1($db, $SQLStatus, $status)}
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

			}
            if ($report == 'taskHoursByStaffReport'){

                $SQLStatus = $fn->getValueListSQL('taskHoursByStaffStatus');
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
            if ($report == 'marketingDetailReport'){
                $SQLChannel = $fn->getValueListSQL('opportunitySourceChannel');
                $SQLStatus = $fn->getValueListSQL('opportunityStatus');
               $rows .= "
                <td>
                    <select name='source_channel'>
                        <option value=''>Channel</option>
                        {$dbUtil->getDropDownFromSQLCols1($db, $SQLChannel, $source_channel)}
                    </select>
                </td>
                <td>
                    <select name='status'>
                        <option value=''>Status</option>
                        {$dbUtil->getDropDownFromSQLCols1($db, $SQLStatus, $status)}
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
            }
            if ($report == 'marketingSummaryReport'){
                $SQLStatus = $fn->getValueListSQL('opportunityStatus');
                $sqlYear = "
                SELECT DISTINCT DATE_FORMAT(o.enquiry_date, '%Y') AS opp_year
                FROM opportunity o
                WHERE o.enquiry_date != ''
                ORDER BY opp_year DESC
                ";

                if ($year == '') {
                    $year = date('Y');
                }

                $rows .= "
                <td>
                    <select name='year'>
                        <option value=''>Select Year</option>
                        {$dbUtil->getDropDownFromSQLCols1($db, $sqlYear, $year)}
                    </select>
                </td>
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
                    <select name='status'>
                        <option value=''>Status</option>
                        {$dbUtil->getDropDownFromSQLCols1($db, $SQLStatus, $status)}
                    </select>
                </td>
                ";
            }
            if ($report == 'detailTaskSummaryReport'){

                /*$sqlmonth = "
                SELECT DISTINCT DATE_FORMAT(start_date, '%Y-%m') AS yearMonthStart
                ,DATE_FORMAT(start_date, '%b - %Y') AS monthYear
                FROM project
                WHERE DATE_FORMAT( start_date, '%b - %Y') IS NOT NULL
                ORDER BY yearMonthStart DESC
                ";
                */
                $sqlproject = "
                SELECT p.project_id as project_id,p.title as name
                FROM project p
                ORDER BY name
                ";

                $sqlcompany = "
                SELECT c.company_id as company_id,c.company_name as company
                FROM company c
                ORDER BY c.company_name
                ";

                $sqlStaff = "
                SELECT s.staff_id
                      ,CONCAT_WS(' ', s.first_name, s.last_name) AS staff_name
                FROM staff s
                WHERE s.team = 'In-house'
                ORDER BY staff_name
                ";
                $rows .= "

                <td>
                    <select name='company_name' class='attendanceFilter'>
                        <option value=''>Select Company</option>
                        {$dbUtil->getDropDownFromSQLCols2($db, $sqlcompany, $company_name)}
                    </select>
                </td>
                <td>
                    <select name='project_name' class='attendanceFilter'>
                        <option value=''>Select project</option>
                        {$dbUtil->getDropDownFromSQLCols2($db, $sqlproject, $project_name)}
                    </select>
                </td>

                <td>
                    <select name='staff_id' class='attendanceFilter'>
                        <option value=''>Select Staff</option>
                        {$dbUtil->getDropDownFromSQLCols2($db, $sqlStaff, $staff_id)}
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

            }

            if ($report == 'cPFSummaryReport' || $report == 'employeeSalaryReport'){
                $sqlYear = "
                SELECT payroll_year
                FROM payroll_management pm
                ";

                $rows .= "

                <td class='dateRange'>
                    Date Range:
                    <input type='text' allowEdit='1' name='start_date' class='fld_date'
                    id='fld_start_date' value='{$start_date}' />
                    <input type='text' allowEdit='1' name='end_date' class='fld_date'
                    id='fld_end_date' value='{$end_date}' />
                </td>
                <td>
                    <select name='year' class='YearFilter'>
                        <option value=''>Select Year</option>
                        {$dbUtil->getDropDownFromSQLCols1($db, $sqlYear, $year)}
                    </select>
                </td>

                <td>
                    <select name='month' class='MonthFilter'>
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

            }

            if ($report == 'leaveReport'){
                $sqlYear = "
                SELECT DISTINCT DATE_FORMAT(l.from_date, '%Y') AS year
                FROM `leave` l
                ";

                $rows .= "

                <td class='dateRange'>
                    Date Range:
                    <input type='text' allowEdit='1' name='start_date' class='fld_date'
                    id='fld_start_date' value='{$start_date}' />
                    <input type='text' allowEdit='1' name='end_date' class='fld_date'
                    id='fld_end_date' value='{$end_date}' />
                </td>
                <td>
                    <select name='year' class='YearFilter'>
                        <option value=''>Select Year</option>
                        {$dbUtil->getDropDownFromSQLCols1($db, $sqlYear, $year)}
                    </select>
                </td>

                <td>
                    <select name='month' class='MonthFilter'>
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

            }

            if ($report == 'loanReport'){
                $sqlYear = "
                SELECT DISTINCT DATE_FORMAT(l.date, '%Y') AS year
                FROM `loan` l
                ";

                $rows .= "

                <td class='dateRange'>
                    Date Range:
                    <input type='text' allowEdit='1' name='start_date' class='fld_date'
                    id='fld_start_date' value='{$start_date}' />
                    <input type='text' allowEdit='1' name='end_date' class='fld_date'
                    id='fld_end_date' value='{$end_date}' />
                </td>
                <td>
                    <select name='year' class='YearFilter'>
                        <option value=''>Select Year</option>
                        {$dbUtil->getDropDownFromSQLCols1($db, $sqlYear, $year)}
                    </select>
                </td>

                <td>
                    <select name='month' class='MonthFilter'>
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

            }

            if ($report == 'allowanceReport'){
                $sqlYear = "
                SELECT DISTINCT p.payroll_year
                FROM `payroll_management` p
                ";

                $rows .= "

                <td>
                    <select name='payroll_year' class='YearFilter'>
                        <option value=''>Select Year</option>
                        {$dbUtil->getDropDownFromSQLCols1($db, $sqlYear, $payroll_year)}
                    </select>
                </td>

                <td>
                    <select name='month' class='MonthFilter'>
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

            }

            if ($report == 'sDLReport'){
                $sqlYear = "
                SELECT DISTINCT p.payroll_year
                FROM `payroll_management` p
                ";

                $rows .= "

                <td>
                    <select name='payroll_year' class='YearFilter'>
                        <option value=''>Select Year</option>
                        {$dbUtil->getDropDownFromSQLCols1($db, $sqlYear, $payroll_year)}
                    </select>
                </td>

                <td>
                    <select name='month' class='MonthFilter'>
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

            }

        }

        $text = "
        <form id='reportSearch'>
        <table class='search'>
            <tr>
                <td><a href='javascript:void(0);' style='padding-right:5px;' onClick=\"javascript:$('#reportSearch').clearForm();\">reset</a></td>
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