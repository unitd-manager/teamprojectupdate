<?
class CP_Admin_Modules_Account_Reports_Model extends CP_Common_Lib_ModuleModelAbstract
{
    var $reportsArray = array();

    /**
     *
     */
    function __construct() {
        $cpUtil  = Zend_Registry::get('cpUtil');

        $this->reportsArray = array(
            'generalLedger' => $this->getReportObj(
                 'generalLedger'
                ,'General Ledger'
                ,array('dateRange')
            )
            ,'trialBalance' => $this->getReportObj(
                 'trialBalance'
                ,'Trial Balance'
                ,array('dateRange', 'currency', 'accountCategoryGroup', 'accountCategory', 'keyword', 'sortBy')
            )
            ,'trialBalanceBankAccount' => $this->getReportObj(
                 'trialBalanceBankAccount'
                ,'Bank Accounts'
                ,array('dateRange', 'currency', 'sortBy')
            )
            ,'trialBalanceSundryCreditorDebtor' => $this->getReportObj(
                 'trialBalanceSundryCreditorDebtor'
                ,'Sundry Creditors/Debtors'
                ,array('activeRange', 'activeFilter', 'currency', 'sortBy')
            )
            ,'trialBalanceOutstandingReceivable' => $this->getReportObj(
                 'trialBalanceOutstandingReceivable'
                ,'Accounts Receivables'
                ,array('activeRange', 'activeFilter', 'currency', 'sortBy')
            )
            ,'trialBalanceOutstandingPayable' => $this->getReportObj(
                'trialBalanceOutstandingPayable'
                ,'Accounts Payables'
                ,array('activeRange', 'activeFilter', 'currency', 'sortBy')
            )
            ,'tradingProfitLoss' => $this->getReportObj(
                 'tradingProfitLoss'
                ,'Trading Profit & Loss'
            )
            ,'balanceSheet' => $this->getReportObj(
                 'balanceSheet'
                ,'Balance Sheet'
            )
            ,'currencyStock' => $this->getReportObj(
                 'currencyStock'
                ,'Currency Stock'
                ,array('dateRange')
            )
            ,'profitMargin' => $this->getReportObj(
                 'profitMargin'
                ,'Profit Margin'
                ,array('dateRange')
            )
            ,'netWorth' => $this->getReportObj(
                 'netWorth'
                ,'Net Worth'
                ,array()
                ,array('hasExport' => false)
            )
            ,'liquiditySummary' => $this->getReportObj(
                 'liquiditySummary'
                ,'Liquidity Summary'
                ,array('liquidityRange')
                ,array('hasExport' => false)
            )
        );
    }

    /**
     *
     */
    function getReportObj($name, $title, $searchFlds = array(), $exp = array()) {

        $expDefault = array('hasExport' => true);
        $exp = array_merge($expDefault, $exp);

        //searchFldType: uptoDate, dateRange, activeRange
        $arr = array(
             'name' => $name
            ,'title' => $title
            ,'searchFlds' => $searchFlds
        );

        $arr = array_merge($arr, $exp);

        return $arr;
    }

    /**
     *
     */
    function getExportData($dataArray, $print = false){
        $fn = Zend_Registry::get('fn');
        $report = $fn->getReqParam('report');

        $wTrialBalance = getCPWidgetObj('account_trialBalance');
        if ($report == 'generalLedger') {
            $text = $wTrialBalance->model->getExportGeneralLedger($dataArray);
        } else {
            $text = $wTrialBalance->model->getExportData($dataArray);
        }

    }

    /**
     *
     */
    function getSumByCategoryType($category_type, $reportType = '', $end_date = '', $currArr = '') {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');

        if ($end_date != ''){
            $end_date = "AND jm.entry_date <= '{$end_date}'";
        }
        
        $sqlAppend = '';
        if (is_array($currArr)){
            $currArr = join("','", $currArr);
            $sqlAppend .= "AND ah.currency_id IN('{$currArr}')";
        }
        $debitBaseSum = "
        CAST(
           -(SELECT SUM(j.debit)
             FROM journal j
             JOIN journal_master jm ON (j.journal_master_id = jm.journal_master_id)
             WHERE j.acc_head_id = ah.acc_head_id
                 {$end_date}
             )
        AS DECIMAL(20,2)) * cc.exch_rate_sell
        ";

        $creditBaseSum = "
        CAST(
            (SELECT SUM(j.credit)
             FROM journal j
             JOIN journal_master jm ON (j.journal_master_id = jm.journal_master_id)
             WHERE j.acc_head_id = ah.acc_head_id
                 {$end_date}
             )
        AS DECIMAL(20,2)) * cc.exch_rate_sell
        ";

        $openingBalanceBase = "
        CASE
            WHEN ah.opening_balance_debit > 0 THEN CAST( -ah.opening_balance_debit AS DECIMAL(20,2) )
            WHEN ah.opening_balance_credit > 0 THEN ah.opening_balance_credit
            ELSE 0
            END * cc.exch_rate_sell
        ";
    
        $SQL = "
        SELECT ah.acc_head_id
              ,ah.code
              ,ah.title AS account
              ,c.code AS currency_code
              ,{$debitBaseSum} AS debit_base_sum
              ,{$creditBaseSum} AS credit_base_sum
              ,CASE
               WHEN (
                        IF(ISNULL({$debitBaseSum}),0, {$debitBaseSum}) +
                        IF(ISNULL({$creditBaseSum}),0, {$creditBaseSum}) +
                        IF(ISNULL({$openingBalanceBase}),0, {$openingBalanceBase})
                    ) > 0 THEN
                    CAST(
                        IF(ISNULL({$debitBaseSum}),0, {$debitBaseSum}) +
                        IF(ISNULL({$creditBaseSum}),0, {$creditBaseSum}) +
                        IF(ISNULL({$openingBalanceBase}),0, {$openingBalanceBase})
                    AS DECIMAL(20,2))
               END AS credit_base_sum_actual

              ,CASE
               WHEN (
                        IF(ISNULL({$debitBaseSum}),0, {$debitBaseSum}) +
                        IF(ISNULL({$creditBaseSum}),0, {$creditBaseSum}) +
                        IF(ISNULL({$openingBalanceBase}),0, {$openingBalanceBase})
                    ) < 0 THEN
                    CAST(
                    -(
                        IF(ISNULL({$debitBaseSum}),0, {$debitBaseSum}) +
                        IF(ISNULL({$creditBaseSum}),0, {$creditBaseSum}) +
                        IF(ISNULL({$openingBalanceBase}),0, {$openingBalanceBase})
                    )
                    AS DECIMAL(20,2))
               END AS debit_base_sum_actual
        FROM acc_head ah
        JOIN currency c ON c.currency_id = ah.currency_id
        JOIN currency_convert cc ON c.currency_id = cc.from_currency_id
        WHERE ah.acc_category_id IN (
            SELECT acc_category_id
            FROM acc_category
            WHERE category_type = '{$category_type}'
        )
        {$sqlAppend}
        ";
        $resultAcc = $db->sql_query($SQL);

        //print $SQL;
        //fb::log($SQL);
        //print '<hr>';

        $dataArray = array();
        while ($rowAcc = $db->sql_fetchrow($resultAcc)) {
            //this array is for the account row
            $arr2 = array();
            $arr2['debit_base']  = $rowAcc['debit_base_sum_actual'];
            $arr2['credit_base'] = $rowAcc['credit_base_sum_actual'];
            $dataArray[] = $arr2;
        }

        $debit_total = 0;
        $credit_total = 0;
        foreach($dataArray AS $row){
            if ($reportType == 'Receivables') {
                $debit_total += $row['debit_base'];
            } else if ($reportType == 'Payables') {
                $credit_total += $row['credit_base'];
            } else {
                $debit_total += $row['debit_base'];
                $credit_total += $row['credit_base'];
            }
        }

        return array('debit' => $debit_total, 'credit' => $credit_total);
    }

    /**
     *
     */
    function getDatesByQuickFilter(){
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $dateRange = $fn->getReqParam('dateRange');

        $date1 = '';
        $date2 = '';

        if ($dateRange == 'today'){
            $date1 = date('Y-m-d');
            $date2 = date('Y-m-d');
        } else if ($dateRange == 'yesterday'){
            $date1 = date('Y-m-d', mktime (0,0,0,date("m"), date("d")-1, date("Y")));
            $date2 = date('Y-m-d');
        } else if ($dateRange == 'last3Days'){
            $date1 = date('Y-m-d', mktime (0,0,0,date("m"), date("d")-3, date("Y")));
            $date2 = date('Y-m-d');
        } else if ($dateRange == 'last7Days'){
            $date1 = date('Y-m-d', mktime (0,0,0,date("m"), date("d")-7, date("Y")));
            $date2 = date('Y-m-d');
        } else if ($dateRange == 'last30Days'){
            $date1 = date('Y-m-d', mktime (0,0,0,date("m"), date("d")-30, date("Y")));
            $date2 = date('Y-m-d');
        }

        return json_encode(array('date1' => $date1, 'date2' => $date2));
    }
}
