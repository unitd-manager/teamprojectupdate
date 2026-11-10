<?
class CP_Admin_Modules_Hms_Reports_View extends CP_Common_Lib_ModuleViewAbstract
{

    var $jssKeys = array('jqForm-3.15','chosen-1.5.1');

    /**
     *
     */
    function getList() {
        $listObj = Zend_Registry::get('listObj');
        $cpUtil  = Zend_Registry::get('cpUtil');

        $rowCounter = 0;
        $rows = "";

        $text = "
        <div class='homeClass'><a href='index.php?_topRm=home&module=hms_home'>Home</a></div>
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
                        <option value='patientVisitSummary'>Patient Visit Summary</option>
                        <option value='dailyCollectionReport'>Daily Collection Report</option>
                        <option value='revenueByDay'>Revenue By Day</option>
                        <option value='revenueByMonth'>Revenue By Month</option>
                        <option value='treatmentHistory'>Treatment History</option>
                        <option value='visitByDay'>Visit By Day</option>
                        <option value='invoiceSummary'>Invoice Summary</option>
                        <option value='companyInvoiceSummary'>Company Invoice Summary</option>
                        <option value='panelInvoiceSummary'>Panel Invoice Summary</option>
                        <option value='expenseReport'>Expense Report</option>
                        <option value='stockReport'>Stock Report</option>
                        <option value='dutyRosterReport'>Duty Roster Report</option>
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

        $year         	= $fn->getReqParam('year');
        $month        	= $fn->getReqParam('month');
        $sort_order   	= $fn->getReqParam('sort_order');
        $search_by    	= $fn->getReqParam('search_by');
        $active_start 	= $fn->getReqParam('active_start');
        $active_end   	= $fn->getReqParam('active_end');
        $course_id    	= $fn->getReqParam('course_id');
        $subject_id   	= $fn->getReqParam('subject_id');
        $batch_id     	= $fn->getReqParam('batch_id');
        $status       	= $fn->getReqParam('status');
        $staff_id     	= $fn->getReqParam('staff_id');
        $employee_visit = $fn->getReqParam('employee_visit');
        $employee_id    = $fn->getReqParam('employee_id');
        $teacher_id   	= $fn->getReqParam('teacher_id');
        $product_id   	= $fn->getReqParam('product_id');
        $company_id   	= $fn->getReqParam('company_id');
        $site_id        = $fn->getReqParam('site_id');
        $patient_information_id     = $fn->getReqParam('patient_information_id');
        $bill_type    = $fn->getReqParam('bill_type');

        $start_date   	= $fn->getReqParam('start_date');
        $end_date 	  	= $fn->getReqParam('end_date');

        /*if ($start_date == '') {
            $start_date = date('Y-m-d', mktime (0,0,0,date("m")-6, date("d"), date("Y")));
        }

        if ($end_date == '') {
            $end_date = date('Y-m-d');
        }*/

        $location = '';
        if ($cpCfg['cp.hasMultiUniqueSites'] == 1){
            $sqlLocation = "
            SELECT s.site_id
                  ,s.title
            FROM site s
            WHERE s.published = 1
            ORDER BY site_id
            ";
            $location_id    = $fn->getReqParam('location_id');
            $cpSiteIdSession = $fn->getSessionParam('cp_site_id');
            /*if ($location_id == '') {
                $location_id = $cpSiteIdSession;
            }*/

            $location = "
            <td class='fieldValue'>
                <select name='location_id'>
                    <option value=''>Select Location</option>
                    {$dbUtil->getDropDownFromSQLCols2($db, $sqlLocation, $location_id)}
                </select>
            </td>
            ";
        }

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

        $spArrayBillType = array (
             'Individual'
            ,'Company'
            ,'Panel'
        );

        $rows = '';
        foreach($searchFldsArr AS $searchFld){

            if ($report == 'patientVisitSummary' ||
                $report == 'dailyCollectionReport')
            {
                $sqlYear = "
                SELECT DISTINCT DATE_FORMAT(pv.check_up_date, '%Y') AS contact_date
                FROM  patient_visit pv
                ";

                $rows .= "
                <td>
                    <select name='year' class='leadStaffYearFilter'>
                        <option value=''>Choose Year</option>
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

                $sqlemployee_visit = "

                SELECT ev.employee_id
                      ,CONCAT_WS(' ', e.first_name, e.middle_name, e.last_name) AS employee_name
                FROM employee_visit ev
                LEFT JOIN (employee e) ON (e.employee_id = ev.employee_id)
                GROUP BY ev.employee_id
                ";

                $rows .= "
                <td>
                    <select name='employee_id' class='leadStaffFilter'>
                        <option value=''>Doctor/Nurse</option>
                        {$dbUtil->getDropDownFromSQLCols2($db, $sqlemployee_visit, $employee_id)}
                    </select>
                </td>
                ";

                $sqlBranch = "
                SELECT site_id
                      ,title
                FROM site
                WHERE published = 1
                ";

                $rows .= "
                <td>
                    <select name='site_id' class='leadStaffFilter'>
                        <option value=''>Select Branch</option>
                        {$dbUtil->getDropDownFromSQLCols2($db, $sqlBranch, $site_id)}
                    </select>
                </td>
                ";
            }
            if($report == 'invoiceSummary') {

                /*$sqlPI ="
                SELECT p.patient_information_id
                      ,CONCAT_WS(' ', p.first_name, p.middle_name, p.last_name) AS patient_name
                FROM patient_information p
                ORDER BY patient_name ASC
                ";

                $rows .= "
                <td>
                    <select name='patient_information_id' class='invoiceSummaryFilter'>
                        <option value=''>Select Patient</option>
                        {$dbUtil->getDropDownFromSQLCols2($db, $sqlPI, $patient_information_id)}
                    </select>
                </td>
                ";

                $sqlBranch = "
                SELECT site_id
                      ,title
                FROM site
                WHERE published = 1
                ";

                $rows .= "
                <td>
                    <select name='site_id' class='leadStaffFilter'>
                        <option value=''>Select Branch</option>
                        {$dbUtil->getDropDownFromSQLCols2($db, $sqlBranch, $site_id)}
                    </select>
                </td>
                ";*/

                $sqlYear = "
                SELECT DISTINCT DATE_FORMAT(i.invoice_date, '%Y') AS invoice_year
                FROM invoice i
                ";

                $rows .= "
                <td>
                    <select name='year' class='year'>
                        <option value=''>Choose Year</option>
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

                /*
                <td>
                    <input type='text' name='company_patient_search' class='invoiceSummary' />
                    <input type='hidden' name='company_patient_id' value=''/> 
                </td>
                */

                $rows .= "
                <td>
                    <select name='bill_type' class='invoiceSummaryFilter'>
                        <option value=''>Bill Type</option>
                        {$cpUtil->getDropDown1($spArrayBillType, $bill_type)}
                    </select>
                    <input type='hidden' name='bill_type_hidden' value=''/>
                </td>
                ";

                $sqlPI ="
                SELECT p.patient_information_id AS company_patient_id
                      ,CONCAT_WS(' ', p.first_name, p.middle_name, p.last_name) AS patient_name
                FROM patient_information p
                ORDER BY patient_name ASC
                ";
                $resultPI = $db->sql_query($sqlPI);
                $patientNameOption = '';
                while($rowPI    = $db->sql_fetchrow($resultPI)){
                    $patientNameOption .= "<option value='{$rowPI['company_patient_id']}'>{$rowPI['patient_name']}</option>";
                }

                $rows .= "
                <td  class='individualCombobox'>
                <script type='text/javascript'>
                    var config = {
                        '.chosen-select'           : {},
                        '.chosen-select-deselect'  : {allow_single_deselect:true},
                        '.chosen-select-no-single' : {disable_search_threshold:10},
                        '.chosen-select-no-results': {no_results_text:'Oops, nothing found!'},
                        '.chosen-select-width'     : {width:'95%'}
                    }
                    for (var selector in config) {
                        $(selector).chosen(config[selector]);
                    }
                </script>
                    <div>
                      <em>Into This</em>
                      <select name='company_patient_id' data-placeholder='Choose Patient...' class='chosen-select'>
                        <option value=''>Please Select</option>
                        {$patientNameOption}
                      </select>
                   </div>
                </td>
                ";
                //<select name='company_patient_id' class='invoiceSummary'>
                    //</select>
                        //<option value=''>Select Patient / Company</option>
                        //{$dbUtil->getDropDownFromSQLCols2($db, $sqlPI, $patient_information_id)}
                    //<input type='text' name='company_patient_id' class='invoiceSummary' />

            }
            if($report == 'companyInvoiceSummary') {

                $sqlCompany = "
                SELECT company_id
                      ,company_name
                FROM company
                WHERE category = 'Client'
                ";

                $rows .= "
                <td>
                    <select name='company_id' class='companyFilter'>
                        <option value=''>Select Company</option>
                        {$dbUtil->getDropDownFromSQLCols2($db, $sqlCompany, $company_id)}
                    </select>
                </td>
                ";
                $sqlYear = "
                SELECT DISTINCT DATE_FORMAT(i.invoice_date, '%Y') AS invoice_year
                FROM invoice i
                ";

                $rows .= "
                <td>
                    <select name='year' class='year'>
                        <option value=''>Choose Year</option>
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

            if($report == 'panelInvoiceSummary') {
                $sqlCompany = "
                SELECT company_id
                      ,company_name
                FROM company
                WHERE category = 'Panel'
                ";

                $rows .= "
                <td>
                    <select name='company_id' class='companyFilter'>
                        <option value=''>Select Panel</option>
                        {$dbUtil->getDropDownFromSQLCols2($db, $sqlCompany, $company_id)}
                    </select>
                </td>
                {$location}
                ";

                $sqlYear = "
                SELECT DISTINCT DATE_FORMAT(i.invoice_date, '%Y') AS invoice_year
                FROM invoice i
                ";

                $rows .= "
                <td>
                    <select name='year' class='ml10 year'>
                        <option value=''>Choose Year</option>
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
            if($report == 'dutyRosterReport') {

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
            if($report == 'expenseReport') {

                $sqlYear = "
                SELECT DISTINCT DATE_FORMAT(e.creation_date , '%Y') AS expense_year
                FROM expense e
                ";

                $rows .= "
                <td>
                    <select name='year' class='year'>
                        <option value=''>Choose Year</option>
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
                $sqlBranch = "
                SELECT site_id
                      ,title
                FROM site
                WHERE published = 1
                ";

                $rows .= "
                <td>
                    <select name='site_id' class='leadStaffFilter'>
                        <option value=''>Select Branch</option>
                        {$dbUtil->getDropDownFromSQLCols2($db, $sqlBranch, $site_id)}
                    </select>
                </td>
                ";

            }

            if($report == 'stockReport') {
                $previous_year = date('Y') - 1;
                $next_year = date('Y') + 1;
                $sqlYear = array(
                      $previous_year
                     ,date('Y')
                     ,$next_year
                );

                $rows .= "
                <td>
                    <select name='year' class='year'>
                        <option value=''>Choose Year</option>
                        {$cpUtil->getDropDown1($sqlYear, $year, 0)}
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

                $rows .= "
                {$location}
                ";

            }

            if ($report == 'revenueByDay')  {
                $sqlYear = "
                SELECT DISTINCT DATE_FORMAT(pv.check_up_date, '%Y') AS contact_date
                FROM  patient_visit pv
                ";

                $rows .= "
                <td>
                    <select name='year' class='leadStaffYearFilter'>
                        <option value=''>Choose Year</option>
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

                $sqlBranch = "
                SELECT site_id
                      ,title
                FROM site
                WHERE published = 1
                ";

                $rows .= "
                <td>
                    <select name='site_id' class='leadStaffFilter'>
                        <option value=''>Select Branch</option>
                        {$dbUtil->getDropDownFromSQLCols2($db, $sqlBranch, $site_id)}
                    </select>
                </td>
                ";

            }

            if ($report == 'revenueByMonth')  {
                $sqlYear = "
                SELECT DISTINCT DATE_FORMAT(i.invoice_date, '%Y')
                FROM  invoice i
                ";

                $rows .= "
                <td>
                    <select name='year' class='year'>
                        <option value=''>Choose Year</option>
                        {$dbUtil->getDropDownFromSQLCols1($db, $sqlYear, $year)}
                    </select>
                </td>
                ";

                $sqlBranch = "
                SELECT site_id
                      ,title
                FROM site
                WHERE published = 1
                ";

                $rows .= "
                <td>
                    <select name='site_id' class='leadStaffFilter'>
                        <option value=''>Select Branch</option>
                        {$dbUtil->getDropDownFromSQLCols2($db, $sqlBranch, $site_id)}
                    </select>
                </td>
                ";
            }
            else if ($report == 'treatmentHistory' || $report == 'visitByDay') {
                $sqlYear = "
                SELECT DISTINCT DATE_FORMAT(pv.check_up_date, '%Y') AS contact_date
                FROM  patient_visit pv
                ";

                $rows .= "
                <td>
                    <select name='year' class='leadStaffYearFilter'>
                        <option value=''>Choose Year</option>
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

                $sqlBranch = "
                SELECT site_id
                      ,title
                FROM site
                WHERE published = 1
                ";

                $rows .= "
                <td>
                    <select name='site_id' class='leadStaffFilter'>
                        <option value=''>Select Branch</option>
                        {$dbUtil->getDropDownFromSQLCols2($db, $sqlBranch, $site_id)}
                    </select>
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

    function getDisplayReport($text){
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $pager = Zend_Registry::get('pager');
        $report = $fn->getReqParam('report');

        $searchQueryString = $pager->removeQueryString(array("_spAction"));
        $exportLink = "{$searchQueryString}&_spAction=exportData&report={$report}&showHTML=0";
        $exportPDFLink = "{$searchQueryString}&_spAction=exportDataPdf&report={$report}&showHTML=0";
        
        $text = "
        <div>
            <a href='{$exportLink}' class='exportLink button'>
                <u1>Export to Excel</u1>
            </a>
            
            {$text}
        </div>
        ";

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