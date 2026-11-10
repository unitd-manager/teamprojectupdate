<?
class CP_Admin_Widgets_Account_TrialBalance_Model extends CP_Common_Lib_WidgetModelAbstract
{

    /**
     *
     */
    function getSQL($acc_category_id = '') {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');

        $sort_order = $fn->getReqParam('sort_order');
        $start_date = $fn->getReqParam('start_date');
        $end_date = $fn->getReqParam('end_date');
        $currency_id = $fn->getReqParam('currency_id');

        $active_start = $fn->getReqParam('active_start');
        $active_end   = $fn->getReqParam('active_end');
        $keyword = $fn->getReqParam('keyword');
        $accCategoryIdSrch = $fn->getReqParam('acc_category_id');
        $accountCategoryGroup = $fn->getReqParam('accountCategoryGroup');
        $report = $fn->getReqParam('report');

        $dateRangeAppendSQL = '';
        if ($start_date != '' || $end_date != ''){
            if ($start_date != '' && $end_date != ''){
                //$dateRangeAppendSQL = "(jm.entry_date BETWEEN '{$start_date}' AND '{$end_date}')";
                $dateRangeAppendSQL = "jm.entry_date <= '{$end_date}'";
            } else if ($start_date != ''){
                $dateRangeAppendSQL = "jm.entry_date <= '{$start_date}'";
            } else if ($end_date != ''){
                $dateRangeAppendSQL = "jm.entry_date <= '{$end_date}'";
            }
            $dateRangeAppendSQL = "AND {$dateRangeAppendSQL}";
        }

        $whereArr = array();

        //$whereArr[] = "ah.acc_head_id = 463";

        if ($acc_category_id != ''){
            $whereArr[] = "ah.acc_category_id = {$acc_category_id}";
        } else if ($sort_order != 'accountGroup'){
            $category_type = $this->getAccCategoryType();

            if ($category_type != ''){
                $whereArr[] = "ah.acc_category_id IN (
                    SELECT acc_category_id
                    FROM acc_category
                    WHERE category_type = '{$category_type}'
                )";
            }
        }

        // this is used for searching by account categoy with multiple hierarchy
        if ($accCategoryIdSrch != ''){
            $whereArr[] = "
            (ah.acc_category_id = '{$accCategoryIdSrch}' OR
             ac.parent_id = '{$accCategoryIdSrch}' OR
             ac2.parent_id = '{$accCategoryIdSrch}'
            )
            ";
        }

        if ($currency_id != '' && $currency_id != 'null'){
            $whereArr[] = "ah.currency_id = {$currency_id}";
        }

        if ($keyword != '' && $keyword != 'null'){
            $whereArr[] = "(
                ah.title LIKE '%{$keyword }%'  OR
                ah.code LIKE '%{$keyword}%'
            )";
        }

        if ($accountCategoryGroup != '' && $accountCategoryGroup != 'null'){
            $whereArr[] = "ac.category_type = '{$accountCategoryGroup}'";
        }

        if ($active_start != '' || $active_end != ''){
            if ($active_start != '' && $active_end != ''){
                $appendSQL = "(jm1.entry_date BETWEEN '{$active_start}' AND '{$active_end}')";
            } else if ($active_start != ''){
                $appendSQL = "jm1.entry_date = '{$active_start}'";
            } else if ($active_end != ''){
                $appendSQL = "jm1.entry_date = '{$active_end}'";
            }

            $whereArr[] = "ah.acc_head_id IN (
                SELECT DISTINCT j1.acc_head_id
                FROM journal j1
                JOIN journal_master jm1 ON (j1.journal_master_id = jm1.journal_master_id)
                WHERE {$appendSQL}
            )";
        }
        //$whereArr[] = "ah.acc_head_id  = '245'";

        $whereCondn = join(" AND \n", $whereArr);
        if ($whereCondn != '') {
            $whereCondn = " WHERE {$whereCondn}";
        }

        $orderBy = '';

        if ($sort_order == 'accountName') {
            $orderBy = 'ah.title';
        } else if ($sort_order == 'currency') {
            $orderBy = 'c.code';
        } else if ($sort_order == 'debit') {
            $orderBy = 'debit_base_sum_actual DESC';
        } else if ($sort_order == 'credit') {
            $orderBy = 'credit_base_sum_actual DESC';
        } else {
            $orderBy = 'ah.code';
        }
        
        if ($report == 'generalLedger'){
            $orderBy = 'ah.title';
        }

        if ($report == 'trialBalanceSundryCreditorDebtor' && $sort_order == ''){
            $orderBy = 'debit_base_sum_actual DESC, -credit_base_sum_actual DESC';
        }
        
        $exchRateFldName  = 'cc.exch_rate_bank';
        $exchRateFldName2 = 'cc.exch_rate_bank';

        //======================================================//
        $debitBaseSum = "
        CAST(
           -(SELECT SUM(j.debit * {$exchRateFldName2})
             FROM journal j
             JOIN journal_master jm ON (j.journal_master_id = jm.journal_master_id)
             WHERE j.acc_head_id = ah.acc_head_id
             {$dateRangeAppendSQL}
             )
         AS DECIMAL(20,2))
         ";

        $creditBaseSum = "
        CAST(
            (SELECT SUM(j.credit * {$exchRateFldName2})
             FROM journal j
             JOIN journal_master jm ON (j.journal_master_id = jm.journal_master_id)
             WHERE j.acc_head_id = ah.acc_head_id
             {$dateRangeAppendSQL}
             )
        AS DECIMAL(20,2))
        ";

        $openingBalanceBase = "
        CASE
            WHEN ah.opening_balance_debit > 0 THEN CAST( -ah.opening_balance_debit AS DECIMAL(20,2) )
            WHEN ah.opening_balance_credit > 0 THEN ah.opening_balance_credit
            ELSE 0
            END * {$exchRateFldName}
            ";

        $SQL = "
        SELECT ah.acc_head_id
              ,ah.code
              ,ah.title AS account
              ,c.code AS currency_code
              ,ac.title AS acc_category_title
              ,CAST(
                 -(SELECT SUM(j.debit)
                   FROM journal j
                   JOIN journal_master jm ON (j.journal_master_id = jm.journal_master_id)
                   WHERE j.acc_head_id = ah.acc_head_id
                   {$dateRangeAppendSQL}
                   )
               AS DECIMAL(20,2)) AS debit_sum

              ,CAST(
                   (SELECT SUM(j.credit)
                   FROM journal j
                   JOIN journal_master jm ON (j.journal_master_id = jm.journal_master_id)
                   WHERE j.acc_head_id = ah.acc_head_id
                   {$dateRangeAppendSQL}
                   )
               AS DECIMAL(20,2)) AS credit_sum

              ,{$debitBaseSum} AS debit_base_sum
              ,{$creditBaseSum} AS credit_base_sum

              ,CASE
               WHEN ah.opening_balance_debit > 0 THEN
                    CAST( -ah.opening_balance_debit AS DECIMAL(20,2) )
               WHEN ah.opening_balance_credit > 0 THEN
                    ah.opening_balance_credit
               ELSE 0
               END AS opening_balance

              ,{$openingBalanceBase} AS opening_balance_base

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
        LEFT JOIN acc_category ac ON ac.acc_category_id = ah.acc_category_id
        LEFT JOIN acc_category ac2 ON ac2.acc_category_id = ac.parent_id
        LEFT JOIN acc_category ac3 ON ac3.acc_category_id = ac2.parent_id
        {$whereCondn}
        ORDER BY {$orderBy}
        ";

        //print $SQL;
        //fb::log($SQL);

        return $SQL;
    }

    /**
     *
     */
    function getDataArray($acc_category_id = 0, $level = 0) {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $report = $fn->getReqParam('report');
        $sort_order = $fn->getReqParam('sort_order');
        if ($sort_order != 'accountGroup'){
            $SQL = $this->getSQL($acc_category_id);
            $resultAcc = $db->sql_query($SQL);
            $numRows = $db->sql_numrows($resultAcc);
            return $this->getAccountsArray($resultAcc);
        }

        $SQL = "
        SELECT acc_category_id
              ,title AS acc_category
              ,category_type
              ,parent_id
        FROM acc_category
        WHERE parent_id = {$acc_category_id}
        ORDER BY code
        ";
        $result = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);

        if ($numRows > 0) {
            $level++;
            while ($row = $db->sql_fetchrow($result)) {
                if ($report != 'trialBalance' && $this->skipCurrentAccCategory($row)) {
                   continue;
                }

                //this array is for the account category row (sub groups)
                $arr = array();
                $arr['code']            = '';
                $arr['acc_category']    = $row['acc_category'];
                $arr['acc_category_id'] = $row['acc_category_id'];
                $arr['level']           = $level;
                $arr['title']           = $row['acc_category']; //title is either acc_category or account (for display purpose)
                $arr['account']         = '';
                $arr['currency_code']   = '';
                $arr['debit']           = '';
                $arr['credit']          = '';
                $arr['debit_base']      = '';
                $arr['credit_base']     = '';
                $this->dataArray[] = $arr;

                $acc_category_id = $row['acc_category_id'];
                $SQL = $this->getSQL($acc_category_id);
                $resultAcc = $db->sql_query($SQL);

                $this->getAccountsArray($resultAcc, $arr);
                $this->getDataArray($row['acc_category_id'], $level);
            }
        }

        return $this->dataArray;
    }

    /**
     *
     */
    function getAccountsArray($resultAcc, $arr = array(), $report = '') {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $modelHelper = Zend_Registry::get('modelHelper');

        if ($report == ''){
            $report = $fn->getReqParam('report');
        }
        //print '<hr>';
        $count = 1;
        
        while ($rowAcc = $db->sql_fetchrow($resultAcc)) {
            $debit_sum = $rowAcc['debit_sum']; //-2500
            $credit_sum = $rowAcc['credit_sum']; //5000
            $opening_balance = $rowAcc['opening_balance']; //5000
            $sum = $debit_sum + $credit_sum + $opening_balance;

            if ($sum > 0) { //-2500 + 5000 is true in this ex
                $credit_sum = $sum;
                $debit_sum = '';
            } else {
                $credit_sum = '';
                $debit_sum = $sum;
            }

            $credit_base_sum = $rowAcc['credit_base_sum_actual'];
            $debit_base_sum = $rowAcc['debit_base_sum_actual'];
            if ($report == 'trialBalanceOutstandingReceivable') {
                if ($debit_sum == '') {
                    continue;
                }
            } else if ($report == 'trialBalanceOutstandingPayable') {
                if ($credit_sum == '') {
                    continue;
                }
            }
            //this array is for the account row
            $arr2 = array();
            $arr2['code']            = $rowAcc['code'];
            $arr2['acc_head_id']     = $rowAcc['acc_head_id'];
            $arr2['account']         = $rowAcc['account'];
            $arr2['currency_code']   = $rowAcc['currency_code'];
            $arr2['level']           = (count($arr) > 0) ? $arr['level'] : 1;
            $arr2['acc_category']    = (count($arr) > 0) ? $arr['acc_category'] : '';
            $arr2['acc_category_title'] = $rowAcc['acc_category_title'];
            
            $arr2['title']           = $rowAcc['account'];
            $arr2['acc_category_id'] = (count($arr) > 0) ? $arr['acc_category_id'] : '';
            $arr2['debit']           = $debit_sum;
            $arr2['credit']          = $credit_sum;
            $arr2['debit_base']      = $debit_base_sum;
            $arr2['credit_base']     = $credit_base_sum;
            
            if ($debit_base_sum != ''){
                $arr2['amount_base']  = -($debit_base_sum);
            } else if ($credit_base_sum != ''){
                $arr2['amount_base']  = $credit_base_sum;
            } else {
                $arr2['amount_base']  = 0;
            }

            $count++;
            
            //if ($count == 10) {
            //    break;
            //}
            $this->dataArray[] = $arr2;
        }

        return $this->dataArray;
    }

    /**
     *
     */
    function getAccCategoryType() {
        $fn = Zend_Registry::get('fn');

        $report = $fn->getReqParam('report');

        $category_type = '';
        if ($report == 'trialBalanceBankAccount') {
            $category_type = 'Bank Account';
        } else if ($report == 'trialBalanceSundryCreditorDebtor'
                || $report == 'trialBalanceOutstandingReceivable'
                || $report == 'trialBalanceOutstandingPayable'
                ) {
            $category_type = 'Sundry Creditor / Debtor';

        } else if ($report == 'liquidity') {
            $category_type = 'Bank Account, Cash Account, Sundry Creditor / Debtor';
        }
        return $category_type;
    }

    /**
     *
     */
    function skipCurrentAccCategory($rowCategory) {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');

        $sort_order = $fn->getReqParam('sort_order');
        $report = $fn->getReqParam('report');

        $category_type = $this->getAccCategoryType();
        if ($category_type == '' || $category_type == $rowCategory['category_type']) {
            return false;
        }

        $catTypeArr = explode(', ', $category_type);

        if (count($catTypeArr) == 1){
            $whereCond = "
            WHERE ac.category_type = '{$category_type}'
               OR ac2.category_type = '{$category_type}'
               OR ac3.category_type = '{$category_type}'
               OR ac4.category_type = '{$category_type}'
            ";
        } else {
            $whereCond = "WHERE (ac.category_type = '" . join("' OR ac.category_type = '", $catTypeArr) . "')";
        }

        $SQL = "
        SELECT ac.parent_id AS parent_id_1
              ,ac2.parent_id AS parent_id_2
              ,ac3.parent_id AS parent_id_3
              ,ac4.parent_id AS parent_id_4

        FROM acc_category ac
        LEFT JOIN acc_category ac2 ON ac2.acc_category_id = ac.parent_id
        LEFT JOIN acc_category ac3 ON ac3.acc_category_id = ac2.parent_id
        LEFT JOIN acc_category ac4 ON ac4.acc_category_id = ac3.parent_id
        {$whereCond}
        ";
        $result = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);

        if ($numRows == 0) {
            return true;
        }

        $retVal = true;

        $count = 4;
        while ($row = $db->sql_fetchrow($result)) {
            for ($i = 1; $i <= $count; $i++) {
                $fld_name = "parent_id_{$i}";
                if ($row[$fld_name] == $rowCategory['acc_category_id']) {
                    $retVal = false;
                    break;
                }
            }
        }

        return $retVal;
    }

    /**
     *
     */
    function getSummaryDataArr() {
        $dataArray = $this->dataArray;

        $debit_base_sum = 0;
        $credit_base_sum = 0;
        foreach ($dataArray as $key => $value) {
            $row = $dataArray[$key];

            $debit_base_sum += $row['debit_base'];
            $credit_base_sum += $row['credit_base'];
        }

        $arr = array (
            'debit_base_sum' => $debit_base_sum
           ,'credit_base_sum' => $credit_base_sum
        );
        return $arr;
    }

    /**
     *
     */
    function getExportData(){
        $fn = Zend_Registry::get('fn');

        $tbsExcel = includeCPClass('Lib', 'TBSExcelExportWrapper', 'TBSExcelExportWrapper');

        $this->getDataArray();

        $sumArr = $this->getSummaryDataArr();
        $debit_base_sum = $sumArr['debit_base_sum'];
        $credit_base_sum = $sumArr['credit_base_sum'];

        $currency_code = 'HKD';
        $globalArray = array(
             'debit_base_sum' => $sumArr['debit_base_sum']
            ,'credit_base_sum' => $sumArr['credit_base_sum']
            ,'currency_code' => $currency_code
        );

        $output_file_name = 'Trial_Balance_' . date('d-m-Y') . '.xlsx';
        $template = __DIR__ . '/assets/trial_balance.xlsx';

        $refObjs = array('model' => $this);
        $config = array(
             'output_file_name' => $output_file_name
            ,'dataArray' => $this->dataArray
            ,'globalArray' => $globalArray
            ,'template' => $template
            ,'refObjs' => $refObjs
        );

        return $tbsExcel->exportData($config);
    }

    /**
     *
     */
    function getExportGeneralLedger(){
        $cpCfg = Zend_Registry::get('cpCfg');
        $dbUtil = Zend_Registry::get('dbUtil');
        $fn = Zend_Registry::get('fn');
        $tbsExcel = includeCPClass('Lib', 'TBSExcelExportWrapper', 'TBSExcelExportWrapper');
        $tbs = $tbsExcel->tbs;

        set_time_limit(50000);
        ini_set('memory_limit', '512M');

        //-----------------------------------------------------------------//
        include_once(CP_LIBRARY_PATH.'lib_php/tbs_us/tbs_class.php');
        include_once(CP_LIBRARY_PATH.'lib_php/tbs_us/plugins/tbs_plugin_opentbs.php');
        include_once(CP_LIBRARY_PATH.'lib_php/tbs_us/plugins/tbs_plugin_html.php');

        $TBS = new clsTinyButStrong;
        $TBS->Plugin(TBS_INSTALL, OPENTBS_PLUGIN);
        
        $quote_id  = $fn->getReqParam('id');
        $template = __DIR__ . '/assets/general-ledger.xlsx';
        $TBS->LoadTemplate($template);

        $start_date = $fn->getReqParam('start_date'); // used in reports /general ledger
        $end_date = $fn->getReqParam('end_date'); // used in reports /general ledger

        global $dataArray;
        $dataArray = $this->getDataArray();
        
        $arr = array();
        foreach ($dataArray as $row) {
            $amount_base_int = abs($row['amount_base']);
            if ($amount_base_int <= 100){
                continue;
            }

            $accHead = getCPModelObj('account_accHead');
            $rowAccHead = $accHead->getAccHeadRow($row['acc_head_id'], $start_date, $end_date);
            
            $row['brought_forward'] = $rowAccHead['brought_forward'];
            $arr[] = $row;
        }

        $dataArray = $arr;
                
        //print '<table border=1>';
        foreach ($dataArray as $key => $value) {
            $row = &$dataArray[$key];

            //print "<tr><td>{$row['account']}</td><td>{$row['amount_base']}</td></tr>";

            // used in reports/general ledger
            $sqlSearchVar = array();
            $sqlSearchVar[] = "j.acc_head_id = {$row['acc_head_id']}";
            if ($start_date != '' && $end_date != '') {
                $sqlSearchVar[] = "(jm.entry_date BETWEEN '{$start_date}' AND '{$end_date}')";
            } else if ($start_date != '') {
                $sqlSearchVar[] = "jm.entry_date >= '{$start_date}'";
            }
            
            $sqlSearchVar = join(" AND \n", $sqlSearchVar);

            $SQL = "
            SELECT jm.journal_master_id
                  ,jm.entry_date
                  ,jm.voucher_type
                  ,jm.narration AS narration_main
                  ,ac.title AS acc_other_category_title
                  ,CASE
                   WHEN (j.debit * cc.exch_rate_bank) > 0 THEN -(j.debit * cc.exch_rate_bank)
                   ELSE (j.credit * cc.exch_rate_bank)
                   END AS amount
            FROM journal_master jm
            JOIN journal j ON j.journal_master_id = jm.journal_master_id
            JOIN acc_head ah ON ah.acc_head_id = j.acc_head_id
            JOIN currency c ON c.currency_id = ah.currency_id
            JOIN currency_convert cc ON c.currency_id = cc.from_currency_id
            LEFT JOIN acc_head ah2 ON ah2.acc_head_id = j.acc_head_id_other
            LEFT JOIN acc_category ac ON ac.acc_category_id = ah2.acc_category_id
            WHERE 
            {$sqlSearchVar}
            ORDER BY jm.entry_date ASC, j.journal_id ASC
            ";

            $row['ledgerData'] = $dbUtil->getResultsetAsArray($SQL);
            
            if (abs($row['brought_forward']) > 0){
                $bfRow = array(
                     'entry_date'      => ''
                    ,'voucher_type'    => ''
                    ,'narration_main'  => 'B/F'
                    ,'amount'          => $row['brought_forward']
                    ,'acc_other_category_title' => ''
                );
                array_unshift($row['ledgerData'], $bfRow);
            }
        }
        
        //print '</table>';

        $currency_code = 'HKD';
        $globalArray = array(
            'currency_code' => $currency_code
        );
        $output_file_name = 'General-Ledger-' . date('d-m-Y') . '.xlsx';
        $formatArray['date']     = isset($formatArray['date']) ? $formatArray['date'] : $cpCfg['cp.dateDisplayFormatTBS'];
        $formatArray['dateTime'] = isset($formatArray['dateTime']) ? $formatArray['dateTime'] : $cpCfg['cp.dateTimeDisplayFormatTBS'];
        $formatArray['number']   = isset($formatArray['number']) ? $formatArray['number'] : $cpCfg['cp.numberFormatTBS'];
        
        $TBS->MergeField('f', $formatArray);
        $TBS->MergeField('g', $globalArray);
        $TBS->MergeBlock('data', $dataArray);          
        $TBS->MergeBlock('data','array','dataArray');
        $TBS->MergeBlock('ledg','array','dataArray[%p1%][ledgerData]');
        
        $TBS->Show(OPENTBS_DOWNLOAD, $output_file_name);

        //return $tbsExcel->exportData($config);
    }

    /**
     *
     */
    function onTBSData($blockName, &$row, $recNum) {
        $spaceCat = str_repeat('--->', $row['level'] - 1);
        $space = str_repeat('--->', $row['level']);
        $title = '';
        if ($row['account'] != '') {
            $title = "{$space}[{$row['code']}] {$row['account']}";
        } else {
            $title = "{$spaceCat}{$row['acc_category']}";
        }
        $row['title'] = $title;
    }
}