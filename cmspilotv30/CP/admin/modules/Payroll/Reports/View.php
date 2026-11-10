<?
class CP_Admin_Modules_Payroll_Reports_View extends CP_Common_Lib_ModuleViewAbstract
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
        $cpCfg   = Zend_Registry::get('cpCfg');

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
                        <option value='employeePayslipGeneratedReport'>Payslip Generated Report</option>
                        <option value='employeeSalaryReport'>Employee Salary Report</option>
                        <option value='employeeTrainingExpiryReport'>Employee Training Expiry Report</option>
                        <option value='cPFSummaryReport'>CPF Summary Report</option>
                        <option value='leaveReport'>Leave Report</option>
                        <option value='ir8a'>IR8A Report</option>
                        <option value='allowanceReport'>Allowance Report</option>
                        <option value='sDLReport'>SDL Report</option>
                        <option value='payslipByEmployeeReport'>Payslip by Employee Report</option>
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
        $fn      = Zend_Registry::get('fn');
        $tv      = Zend_Registry::get('tv');
        $dbUtil  = Zend_Registry::get('dbUtil');
        $cpUtil  = Zend_Registry::get('cpUtil');
        $pager   = Zend_Registry::get('pager');
        $db      = Zend_Registry::get('db');
        $cpCfg   = Zend_Registry::get('cpCfg');
        $ln      = Zend_Registry::get('ln');

        $reportsArray = $this->model->reportsArray;

        $url = "";
        $report             = $fn->getReqParam('report');
        $month              = $fn->getReqParam('month');
        $year               = $fn->getReqParam('year');
        $employee_status    = $fn->getReqParam('employee_status');
        $employee_id        = $fn->getReqParam('employee_id');
        $payroll_year       = $fn->getReqParam('payroll_year');

        if ($employee_status == '') {
            $employee_status = 'Current';
        }

        $employeeStatusArr = array(
              "Current"
             ,"Archive"
             ,"Cancel"
        );

        $arr = array (
                '01' => 'January'
               ,'02' => 'February'
               ,'03' => 'March'
               ,'04' => 'April'
               ,'05' => 'May'
               ,'06' => 'June'
               ,'07' => 'July'
               ,'08' => 'August'
               ,'09' => 'September'
               ,'10' => 'October'
               ,'11' => 'November'
               ,'12' => 'December'
               );

        if ($year == '') {
            $year = date('Y');
        }

        if ($month == '') {
            $month = date('m');
        }

        $searchFldsArr = $reportsArray[$report]['searchFlds'];
        $rows = '';
        foreach($searchFldsArr AS $searchFld){
            if ($report == 'employeePayslipGeneratedReport'
            || $report == 'cPFSummaryReport'
            || $report == 'sDLReport'
            || $report == 'allowanceReport'){
                if ($month == '' || $month == date('m')) {
                    $month = date('m') - 1;
                    if ($month <= 9 && $month > 0) {
                        $month = 0 . $month;
                    } else if ($month == 0) {
                        $month = 12;
                    } else {
                        $month = $month;
                    }
                }

                $rows .= "
                <td>
                    <select name='month' class='ml10 mr10 monthFilter'>
                        {$cpUtil->getDropDownFromArr($arr, $month)}
                    </select>
                </td>
                ";

                $cpSiteIdSession = $fn->getSessionParam('cp_site_id');
                $appendSqlSite = "";
                if ($cpCfg['cp.hasMultiUniqueSites']) {
                    $appendSqlSite = "WHERE site_id = {$cpSiteIdSession}";
                }

                $sqlYear = "
                SELECT DISTINCT payroll_year
                FROM payroll_management
                {$appendSqlSite}
                ";

                $rows .= "
                <td>
                    <select name='year' class='yearFilter'>
                        {$dbUtil->getDropDownFromSQLCols1($db, $sqlYear, $year)}
                    </select>
                </td>
                ";
            }

            if ($report == 'leaveReport' || $report == 'payslipByEmployeeReport'){
                $cpSiteIdSession = $fn->getSessionParam('cp_site_id');
                $appendSqlSite  = "";
                $appendSqlSite1 = "";
                if ($cpCfg['cp.hasMultiUniqueSites']) {
                    $appendSqlSite  = "WHERE site_id = {$cpSiteIdSession}";
                    $appendSqlSite1 = "AND e.site_id = {$cpSiteIdSession}";
                }

                $sqlYear = "
                SELECT DISTINCT payroll_year
                FROM payroll_management
                {$appendSqlSite}
                ";

                $sqlEmployee ="
                SELECT DISTINCT e.employee_id, e.first_name
                FROM employee e
                WHERE e.status = '{$employee_status}'
                {$appendSqlSite1}
                ORDER BY e.first_name ASC
                ";

                $rows .= "
                <td>
                    <select name='employee_status' class='ml10 mr10 employeeStatusFilter'>
                        {$cpUtil->getDropDown1($employeeStatusArr, $employee_status)}
                    </select>
                </td>
                <td class='ml5'>
                    <select name='employee_id' class='ml10 mr10 leaveReportEmployee'>
                        {$dbUtil->getDropDownFromSQLCols2($db, $sqlEmployee, $employee_id)}
                    </select>
                </td>
                <td>
                    <select name='year' class='yearFilter'>
                        {$dbUtil->getDropDownFromSQLCols1($db, $sqlYear, $year)}
                    </select>
                </td>
                ";
            }

            if ($report == 'employeeSalaryReport'){
                $rows .= "
                <td>
                    <select name='employee_status' class='ml10 mr10 employeeStatusFilter'>
                        {$cpUtil->getDropDown1($employeeStatusArr, $employee_status)}
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

        if ($report == 'statementofAccountsReport'){
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
}