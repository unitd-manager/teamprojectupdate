<?
class CP_Admin_Modules_Labsg_Reports_View extends CP_Common_Lib_ModuleViewAbstract
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
                    <option value=''>Report List</option>
                        <option value='dailyCollectionReport'>Daily Collection Report</option>
                        <option value='masterFinanceSummaryReport'>Master Finance Summary Report</option>
                        <option value='revenueByDay'>Revenue By Day</option>
                        <option value='revenueByMonth'>Revenue By Month</option>
                        <option value='patientVisitSummary'>Patient Visit Summary</option>
                        <option value='patientVisitDetailReport'>Patient Visit Detail</option>
                        <option value='treatmentHistory'>Treatment History</option>
                        <option value='visitByDay'>Visit By Day</option>
                        <option value='invoiceSummary'>Invoice Summary</option>
                        <!--<option value='ageingReport'>Ageing Report</option>-->
                </select>
            </td>
        </tr>
        </table>
        ";

        /*$text = "
        <table class='search'>
        <tr>
            <td>
                <select name='report' class='report'>
                    <option value=''>Please Choose the Report</option>
                    <optgroup label='Financial Reports'>
                        <option value='summaryPurchaseSales'>Summary Purchase Sales</option>
                        <option value='summaryPurchase'>Summary Purchase</option>
                        <option value='summarySales'>Summary Sales</option>
                    </optgroup>
                </select>
            </td>
        </tr>
        </table>
        ";*/

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

        $payment_mode            = $fn->getReqParam('payment_mode');
        $year         	         = $fn->getReqParam('year');
        $month        	         = $fn->getReqParam('month');
        $sort_order   	         = $fn->getReqParam('sort_order');
        $search_by    	         = $fn->getReqParam('search_by');
        $active_start 	         = $fn->getReqParam('active_start');
        $active_end   	         = $fn->getReqParam('active_end');
        $course_id    	         = $fn->getReqParam('course_id');
        $subject_id   	         = $fn->getReqParam('subject_id');
        $batch_id     	         = $fn->getReqParam('batch_id');
        $status       	         = $fn->getReqParam('status');
        $staff_id     	         = $fn->getReqParam('staff_id');
        $employee_visit          = $fn->getReqParam('employee_visit');
        $employee_id             = $fn->getReqParam('employee_id');
        $teacher_id   	         = $fn->getReqParam('teacher_id');
        $product_id   	         = $fn->getReqParam('product_id');
        $company_id   	         = $fn->getReqParam('company_id');
        $site_id                 = $fn->getReqParam('site_id');
        $patient_information_id  = $fn->getReqParam('patient_information_id');
        $bill_type               = $fn->getReqParam('bill_type');
        $start_date   	         = $fn->getReqParam('start_date');
        $end_date 	  	         = $fn->getReqParam('end_date');
        $invoiced                = $fn->getReqParam('invoiced');

        $searchFldsArr = $reportsArray[$report]['searchFlds'];

        if ($year == '') {
            $year = date('Y');
        }

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

        if ($month == '') {
            $month = date('m');
        }

        if ($payment_mode == '') {
            $payment_mode = 'Cash';
        }

        $spArrayBillType = array (
             'Individual'
            ,'Company'
        );

        $invoicedArray = array(
            "Yes"
           ,"No"
        );

        $rows = '';
        foreach($searchFldsArr AS $searchFld){

            if ($report == 'patientVisitSummary' ||
                $report == 'patientVisitDetailReport' ||
                $report == 'dailyCollectionReport' ||
                $report == 'revenueByDay') {

                if ($report == 'patientVisitDetailReport') {
                    $rows .= "
                    <td>
                        <select name='invoiced' class='invoiceSummaryFilter'>
                            <option>Invoiced</option>
                            {$cpUtil->getDropDown1($invoicedArray, $invoiced)}
                        </select>
                    </td>
                    ";
                }

                if ($report == 'dailyCollectionReport') {
                    $sqlModeOfPayment = $fn->getValueListSQL('paymentType');
                    $rows .= "
                    <td class='payment_mode_receipt_summary pl10'>
                        <select name='payment_mode'>
                            {$dbUtil->getDropDownFromSQLCols1($db, $sqlModeOfPayment, $payment_mode)}
                            <option value='All'>All</option>
                        </select>
                    </td>
                    ";
                }

                $sqlYear = "
                SELECT DISTINCT DATE_FORMAT(r.date, '%Y') AS contact_date
                FROM receipt r
                ";
                $resultYear  = $db->sql_query($sqlYear);
                $numRowsYear = $db->sql_numrows($resultYear);

                $current_year = '';
                if ($numRowsYear == 0) {
                    $current_year = "<option value=''2018>2018</option>";
                }
                $rows .= "
                <td>
                    <select name='year' class='leadStaffYearFilter'>
                        {$current_year}
                        {$dbUtil->getDropDownFromSQLCols1($db, $sqlYear, $year)}
                    </select>
                </td>
                ";

                $rows .= "
                <td>
                    <select name='month' class='ml10 mr10 month'>
                        {$cpUtil->getDropDownFromArr($arr, $month)}
                    </select>
                </td>
                ";

                $rows .= "
                <td class='dateRange'>
                    From Date:
                    <input type='text' allowEdit='1' name='start_date' class='fld_date'
                    id='fld_start_date' value='{$start_date}' />
                    To Date:
                    <input type='text' allowEdit='1' name='end_date' class='fld_date'
                    id='fld_end_date' value='{$end_date}' />
                </td>
                ";

                $sqlemployee_visit = "
                SELECT ev.employee_id
                FROM employee_visit ev
                ";
            }

            if ($report == 'treatmentHistory') {
                $rows .= "
                <td class='dateRange'>
                    From Date:
                    <input type='text' allowEdit='1' name='start_date' class='fld_date'
                    id='fld_start_date' value='{$start_date}' />
                    To Date:
                    <input type='text' allowEdit='1' name='end_date' class='fld_date'
                    id='fld_end_date' value='{$end_date}' />
                </td>
                ";

            }

            if($report == 'invoiceSummary') {
                $sqlPI ="
                SELECT DISTINCT o.patient_information_id AS company_patient_id
                               ,o.first_name AS company_patient_name
                FROM `order` o
                WHERE o.patient_information_id != ''
                ORDER BY company_patient_name ASC
                ";

                $rows .= "
                <td>
                    <select name='bill_type' class='invoiceSummaryFilter'>
                        {$cpUtil->getDropDown1($spArrayBillType, $bill_type)}
                    </select>
                </td>
                <td class='ml5'>
                    <select name='company_patient_id' class='invoiceSummaryCompany'>
                        <option value=''>Select Patient / Company</option>
                        {$dbUtil->getDropDownFromSQLCols2($db, $sqlPI, $patient_information_id)}
                    </select>
                </td>
                <td class='dateRange'>
                    From Date:
                    <input type='text' allowEdit='1' name='start_date' class='fld_date'
                    id='fld_start_date' value='{$start_date}' />
                    To Date:
                    <input type='text' allowEdit='1' name='end_date' class='fld_date'
                    id='fld_end_date' value='{$end_date}' />
                </td>
                ";
            }

            if ($report == 'ageingReport'){
                $rows .= "";
            }
            if ($report == 'revenueByMonth')  {
                $sqlYear = "SELECT DISTINCT DATE_FORMAT(i.invoice_date, '%Y') FROM  invoice i";
                $resultYear  = $db->sql_query($sqlYear);
                $numRowsYear = $db->sql_numrows($resultYear);

                $current_year = '';
                if ($numRowsYear == 0) {
                    $current_year = "<option value=''2018>2018</option>";
                }

                $rows .= "
                <td>
                    <select name='year' class='year'>
                        {$current_year}
                        {$dbUtil->getDropDownFromSQLCols1($db, $sqlYear, $year)}
                    </select>
                </td>
                ";
            }
            else if ($report == 'visitByDay') {
                $sqlYear = "
                SELECT DISTINCT DATE_FORMAT(pv.check_up_date, '%Y') AS contact_date
                FROM  patient_visit pv
                ";
                $resultYear  = $db->sql_query($sqlYear);
                $numRowsYear = $db->sql_numrows($resultYear);

                $current_year = '';
                if ($numRowsYear == 0) {
                    $current_year = "<option value=''2018>2018</option>";
                }

                $rows .= "
                <td>
                    <select name='year' class='leadStaffYearFilter'>
                        {$current_year}
                        {$dbUtil->getDropDownFromSQLCols1($db, $sqlYear, $year)}
                    </select>
                </td>
                ";

                $rows .= "
                <td>
                    <select name='month' class='ml10 mr10 month'>
                        <option value=''>Choose Month</option>
                        {$cpUtil->getDropDownFromArr($arr, $month)}
                    </select>
                </td>
                ";

                $rows .= "
                <td class='dateRange'>
                    From Date:
                    <input type='text' allowEdit='1' name='start_date' class='fld_date'
                    id='fld_start_date' value='{$start_date}' />
                    To Date:
                    <input type='text' allowEdit='1' name='end_date' class='fld_date'
                    id='fld_end_date' value='{$end_date}' />
                </td>
                ";

            }

            elseif ($report == 'masterFinanceSummaryReport') {
                $rows .= "
                <td class='dateRange'>
                    From Date:
                    <input type='text' allowEdit='1' name='start_date' class='fld_date'
                    id='fld_start_date' value='{$start_date}' />
                    To Date:
                    <input type='text' allowEdit='1' name='end_date' class='fld_date'
                    id='fld_end_date' value='{$end_date}' />
                </td>
                ";
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
        }
        return $text;
    }

    /**
     *
     */
    function getDisplayReport($text){
        $fn = Zend_Registry::get('fn');
        $pager = Zend_Registry::get('pager');
        $report = $fn->getReqParam('report');

        $searchQueryString = $pager->removeQueryString(array("_spAction"));
        $exportLink = "{$searchQueryString}&_spAction=exportData&report={$report}&showHTML=0";
        $exportPDFLink = "{$searchQueryString}&_spAction=exportDataPdf&report={$report}&showHTML=0";

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
                <a href='{$exportPDFLink}' target='blank' class='exportLinkPDF button'>
                    <u>Export to PDF</u>
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