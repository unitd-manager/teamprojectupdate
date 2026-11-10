<?
class CP_Admin_Modules_Account_Reports_View extends CP_Common_Lib_ModuleViewAbstract
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

        $rowCounter = 0;
        $rows = "";

        $repArrSrc = $this->model->reportsArray;

        $repArr = array();
        foreach($repArrSrc AS $key => $val){
            $repArr[$key] = $val['title'];
        }

        $spReports = '';
        if ($_SESSION['userGroupType'] == 'Super Administrator'){
            $spReports = "
                <optgroup label='Special Reports'>
                <option value='currencyStock'>Currency Stock</option>
                <option value='profitMargin'>Profit Margin</option>
                <option value='netWorth'>Net Worth</option>
                <option value='liquiditySummary'>Liquidity Summary</option>
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
                        <option value='generalLedger'>General Ledger</option>
                        <option value='trialBalance'>Trial Balance</option>
                        <option value='tradingProfitLoss'>Trading Profit & Loss</option>
                        <option value='balanceSheet'>Balance Sheet</option>
                    </optgroup>
                    <optgroup label='Key Reports'>
                        <option value='trialBalanceBankAccount'>Bank Accounts</option>
                        <option value='trialBalanceSundryCreditorDebtor'>Sundry Creditors/Debtors</option>
                        <option value='trialBalanceOutstandingReceivable'>Accounts Receivables</option>
                        <option value='trialBalanceOutstandingPayable'>Accounts Payables</option>
                    </optgroup>
                    {$spReports}
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
        $cpUtil = Zend_Registry::get('cpUtil');
        $formObj = Zend_Registry::get('formObj');
        $cpCfg = Zend_Registry::get('cpCfg');
        $reportsArray = $this->model->reportsArray;

        $report = $fn->getReqParam('report');
        $url = "";

        $start_date = $fn->getReqParam('start_date');
        $end_date = $fn->getReqParam('end_date');
        $sort_order = $fn->getReqParam('sort_order');

        $active_start = $fn->getReqParam('active_start');
        $active_end   = $fn->getReqParam('active_end');

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

            if ($searchFld == 'activeFilter'){
                $rows .= "
                <td>
                    <select class='w150' name='activeFilter'>
                        <option value=''>Active Filter</option>
                        <option value='today'>Today</option>
                        <option value='yesterday'>Yesterday</option>
                        <option value='last3Days'>Last 3 Days</option>
                        <option value='last7Days'>Last 7 Days</option>
                        <option value='last30Days'>Last 30 Days</option>
                    </select>
                </td>
                ";
            }

            if ($searchFld == 'liquidityRange'){
                $rows .= "
                <td>
                    <select name='specialSearch'>
                        <option value=''>Filter</option>
                        <option value='last7Days'>Last 7 Days</option>
                        <option value='last30Days'>Last 30 Days</option>
                        <option value='last12Months'>Last 12 Months</option>
                        <option value='byYear'>By Year</option>
                    </select>
                </td>
                ";
            }

            if ($searchFld == 'currency'){
                $exp = array('fieldCls' => 'w100');
                $rows .= "
                <td>
                    {$formObj->getDropDownBySQL('Currency', 'currency_id', $fn->getDdSql('account_currency'), '', $exp)}
                </td>
                ";
            }

            if ($searchFld == 'accountCategory'){
                $fnsModGrp = includeCPClass('ModGroup', 'Account', 'Functions');

                $rows .= "
                <td>
                    <select name='acc_category_id' class='w100'>
                        <option value=''>Category</option>
                        {$fnsModGrp->getAccCatDropdown()}
                    </select>
                </td>
                ";
            }

            if ($searchFld == 'accountCategoryGroup'){
                $exp = array('fieldCls' => 'w100');
                $categoryTypeArr = $cpCfg['m.account.accCategory.categoryType'];

                $rows .= "
                <td>
                    {$formObj->getDropDownByArray('Group', 'accountCategoryGroup', $categoryTypeArr, '', $exp)}
                </td>
                ";
            }

            if ($searchFld == 'keyword'){
                $rows .= "
                <td>
                    Keyword: <input class='w100' name='keyword' value='' />
                </td>
                ";
            }

            if ($searchFld == 'sortBy'){
                $selAccGroup = "selected='selected'";
                $selDebit = "";
                if ($report == 'trialBalanceSundryCreditorDebtor'){
                    $selAccGroup = '';
                }

                $rows .= "
                <td>
                    <select name='sort_order' class='w150'>
                        <option value=''>Sort By</option>
                        <option value='accountGroup' {$selAccGroup}>Account Group</option>
                        <option value='accountName'>Account Name</option>
                        <option value='currency'>Currency</option>
                        <option value='debit' {$selDebit}>Debit</option>
                        <option value='credit'>Credit</option>
                    </select>
                </td>
                ";
            }
        }

        $text = "
        <form id='reportSearch'>
        <table class='cpSearch'>
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
            cpm.account.reports.setSearchForm();
        </script>
        ";

        return $text;
    }

    function getDisplayReport($text){
        $fn = Zend_Registry::get('fn');
        $pager = Zend_Registry::get('pager');
        $report = $fn->getReqParam('report');
        $reportArr = $this->model->reportsArray[$report];
        $hasExport = $reportArr['hasExport'];

        $exportLink = '';
        if ($hasExport){
            $searchQueryString = $pager->removeQueryString(array("_spAction"));
            $exportLink = "{$searchQueryString}&_spAction=exportData&report={$report}&showHTML=0";
            $exportLink = "
            <a href='{$exportLink}' class='exportLink'>
                <u>Export to Excel</u>
            </a>
            ";
        }

        $text = "
        <div>
            {$exportLink}
            {$text}
        </div>
        ";

        return $text;

        $json = array();
        $json['html'] = $text;

        return json_encode($json);
    }

    function getNetworth() {
        $fn = Zend_Registry::get('fn');

        $cashArr  = 0;
        $bankArr  = 0;
        $recArr  = 0;

        $cashArr = $this->model->getSumByCategoryType('Cash Account');
        $bankArr = $this->model->getSumByCategoryType('Bank Account');
        $payArr  = $this->model->getSumByCategoryType('Sundry Creditor / Debtor', 'Payables');
        $recArr  = $this->model->getSumByCategoryType('Sundry Creditor / Debtor', 'Receivables');

        $cashTotal = abs($cashArr['debit'] + $cashArr['credit']);
        $bankTotal = abs($bankArr['debit'] + $bankArr['credit']);
        $recvTotal = abs($recArr['debit']  + $recArr['credit'] );
        $paybTotal = abs($payArr['debit']  + $payArr['credit'] );

        $netValue = $cashTotal + $bankTotal + $recvTotal + $paybTotal;

        $cashTotalF = $fn->getFormatNumber($cashTotal);
        $bankTotalF = $fn->getFormatNumber($bankTotal);
        $recvTotalF = $fn->getFormatNumber($recvTotal);
        $paybTotalF = $fn->getFormatNumber($paybTotal);
        $netValueF  = $fn->getFormatNumber($cashTotal + $bankTotal + $recvTotal - $paybTotal);

        $text = "
        <div id='networthSummary'>
            <div class='subcolumns'>
                <div class='c50l'>
                    <div class='subcl'>
                        <h1>Breakdown</h1>
                        {$this->getNetworthBreakdown(array('recb' => $recvTotal, 'payb' => $paybTotal))}
                    </div>
                </div>
                <div class='c50r'>
                    <div class='subcr'>
                        <h1>Net Worth of the Company</h1>
                        <table class='thinlist'>
                            <tr>
                                <th>Cash in Hand</th>
                                <td class='txtRight'>{$cashTotalF}</td>
                            </tr>
                            <tr>
                                <th>Cash at Banks</th>
                                <td class='txtRight'>{$bankTotalF}</td>
                            </tr>
                            <tr>
                                <th>Receivables</th>
                                <td class='txtRight'>{$recvTotalF}</td>
                            </tr>
                            <tr>
                                <th>Payables</th>
                                <td class='txtRight'>{$paybTotalF}</td>
                            </tr>
                        </table>
                        <div class='netWorth'>
                            Net Worth: {$netValueF}
                        </div>
                    </div>
                </div>
            </div>
        </div>
        ";

        return $text;
    }

    function getNetworthBreakdown($exp = array()) {
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');

        $currArr = $dbUtil->getSQLResultAsArray("SELECT * FROM currency");

        $mainCurArr = array();
        $otherCurIdArr = array();

        foreach($currArr AS $row){
            if ($row['main_currency'] == 1){
                $mainCurArr[] = $row;
            } else {
                $otherCurIdsArr[] = $row['currency_id'];
            }
        }

        $cashMainCurrText = '';
        $bankMainCurrText = '';

        foreach($mainCurArr AS $row){
            $ccRec = $fn->getRecordByCondition('currency_convert', "from_currency_id = '{$row['currency_id']}'");

            $cashArr = $this->model->getSumByCategoryType('Cash Account', '', '', array($row['currency_id']));
            $bankArr = $this->model->getSumByCategoryType('Bank Account', '', '', array($row['currency_id']));

            $cashTotal = abs($cashArr['debit'] + $cashArr['credit']);
            $bankTotal = abs($bankArr['debit'] + $bankArr['credit']);

            $cashTotalF = $fn->getFormatNumber($cashTotal);
            $bankTotalF = $fn->getFormatNumber($bankTotal);
            
            if (is_array($ccRec)){
                $cashMainCurrText .= "
                <tr>
                    <td>CASH ({$row['code']})</td>
                    <td class='txtRight'>{$fn->getFormatNumber($ccRec['exch_rate_cash'])}</td>
                    <td class='txtRight'>{$cashTotalF}</td>
                </tr>
                ";

                $bankMainCurrText .= "
                <tr>
                    <td>BANK ({$row['code']})</td>
                    <td class='txtRight'>{$fn->getFormatNumber($ccRec['exch_rate_bank'])}</td>
                    <td class='txtRight'>{$bankTotalF}</td>
                </tr>
                ";
            }
        }
        
        $cashArr = $this->model->getSumByCategoryType('Cash Account', '', '', $otherCurIdsArr);
        $cashTotal = abs($cashArr['debit'] + $cashArr['credit']);
        $cashTotalF = $fn->getFormatNumber($cashTotal);

        $recvTotalF = $fn->getFormatNumber($exp['recb']);
        $paybTotalF = $fn->getFormatNumber($exp['payb']);

        $text = "
        <table class='thinlist'>
            <thead>
                <tr>
                    <th>Description</th>
                    <th class='txtRight'>Rate</th>
                    <th class='txtRight'>Total (HK$)</th>
                </tr>
            </thead>
            <tbody>
                {$cashMainCurrText}
                <tr>
                    <td>CASH (other currencies)</td>
                    <td class='txtRight'></td>
                    <td class='txtRight'>{$cashTotalF}</td>
                </tr>
                {$bankMainCurrText}
                <tr>
                    <td>Receivables</td>
                    <td class='txtRight'></td>
                    <td class='txtRight'>{$recvTotalF}</td>
                </tr>
                <tr>
                    <td>Payables</td>
                    <td class='txtRight'></td>
                    <td class='txtRight'>{$paybTotalF}</td>
                </tr>
            </tbody>
        </table>
        ";

        return $text;
    }

    function getLiquiditySummary() {
        $fn = Zend_Registry::get('fn');
        $dateUtil = Zend_Registry::get('dateUtil');
        $specialSearch = $fn->getReqParam('specialSearch');

        $dateArr = array();

        if ($specialSearch == 'last7Days'){
            $date1 = date('Y-m-d', mktime (0,0,0,date("m"), date("d")-7, date("Y")));
            $date2 = date('Y-m-d');
            $daysArr = $dateUtil->getDateArrBetween2Dates($date1, $date2);
            foreach($daysArr AS $date){
                $dateArr[] = array(date('d-m', strtotime($date)), $date);
            }
        } else if ($specialSearch == 'last30Days'){
            $date1 = date('Y-m-d', mktime (0,0,0,date("m"), date("d")-30, date("Y")));
            $date2 = date('Y-m-d');
            $daysArr = $dateUtil->getDateArrBetween2Dates($date1, $date2);
            foreach($daysArr AS $date){
                $dateArr[] = array(date('d-m', strtotime($date)), $date);
            }

        } else {
            $last12Month = date('Y-m-d',mktime (0,0,0,date("m")-11,1, date("Y")));
            $monthsArr = $dateUtil->getMonthArrayBetweenTwoDates($last12Month, date('Y-m-d'));
            foreach($monthsArr AS $monthArr){
                $dateArr[] = array(date('M-y', strtotime($monthArr[0])), $monthArr[1]);
            }
        }

        $rows = '';
        foreach($dateArr AS $val){
            $label = $val[0];
            $date = $val[1];

            $cashArr = $this->model->getSumByCategoryType('Cash Account', '', $date);
            $bankArr = $this->model->getSumByCategoryType('Bank Account', '', $date);
            $payArr  = $this->model->getSumByCategoryType('Sundry Creditor / Debtor', 'Payables', $date);
            $recArr  = $this->model->getSumByCategoryType('Sundry Creditor / Debtor', 'Receivables', $date);

            $cashTotal = abs($cashArr['debit'] + $cashArr['credit']);
            $bankTotal = abs($bankArr['debit'] + $bankArr['credit']);
            $recvTotal = abs($recArr['debit']  + $recArr['credit'] );
            $paybTotal = abs($payArr['debit']  + $payArr['credit'] );

            $netValue = $cashTotal + $bankTotal + $recvTotal - $paybTotal;
            $rows .= "['{$label}', {$netValue}],";
        }

        $text = "
        <div class='' id='chart_div'>
        </div>
        <script type='text/javascript'>
            function drawChart() {
                // Create the data table.
                var data = new google.visualization.DataTable();
                data.addColumn('string', 'Month');
                data.addColumn('number', 'Net Worth');
                data.addRows([
                    {$rows}
                ]);

                var chart = new google.visualization.LineChart(document.getElementById('chart_div'));

                var formatter = new google.visualization.NumberFormat(
                    {pattern:'$#,###', negativeColor: 'red', negativeParens: true});
                formatter.format(data, 1); // Apply formatter to second column

                chart.draw(data, {width: 'auto', height: 'auto', title: '',
                    hAxis: {title: 'Dates', titleTextStyle: {color: 'red'}}
                });
            }
        </script>
        ";

        return $text;
    }
}