<?
class CP_Admin_Modules_Account_CounterMaster_Model extends CP_Common_Lib_ModuleModelAbstract
{
    function getSQL() {
        $SQL = "
        SELECT jm.journal_master_id
              ,jm.entry_date
              ,jm.voucher_type
              ,jm.narration
              ,jm.action
              ,j.journal_id
              ,j.credit
              ,j.debit
              ,CASE
               WHEN jm.action = 'buy' THEN j.debit
               ELSE ''
               END AS v_buy_amount

              ,CASE
               WHEN jm.action = 'sell' THEN j.credit
               ELSE ''
               END AS v_sell_amount

              ,CASE
               WHEN jm.action = 'buy' THEN j.exch_rate_to_base
               ELSE ''
               END AS v_buy_rate

              ,CASE
               WHEN jm.action = 'sell' THEN j.exch_rate_to_base
               ELSE ''
               END AS v_sell_rate

              ,CASE
               WHEN jm.action = 'buy' THEN j.debit_base
               ELSE j.credit_base
               END AS amount_base

              ,j.exch_rate_to_base
              ,j.credit_base
              ,j.debit_base
              ,j.currency_id
              ,c.title AS currency
              ,c.code AS currency_code
              ,ah.acc_head_id
              ,ah.title AS acc_head
              ,jm.creation_date
              ,jm.modification_date
              ,CONCAT_WS('-', LEFT(jm.voucher_type, 1), jm.journal_master_id) AS voucher_code
              ,s.short_code AS staff_short_code
              ,s.staff_id
              ,cs.counter_setup_id
              ,cs.title AS counter_name
        FROM journal_master jm
        JOIN journal j ON j.journal_master_id = jm.journal_master_id
        JOIN currency c ON c.currency_id = j.currency_id
        JOIN acc_head ah ON ah.acc_head_id = j.acc_head_id
        LEFT JOIN counter_setup cs ON cs.counter_setup_id = jm.counter_setup_id
        JOIN staff s ON s.staff_id = jm.staff_id
        ";
        return $SQL;
    }

    /**
     *
     */
    function setSearchVar($linkRecType = '') {
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $searchVar = Zend_Registry::get('searchVar');

        $entry_date_from = $fn->getReqParam('entry_date_from');
        $entry_date_to = $fn->getReqParam('entry_date_to');
        $acc_head_id = $fn->getReqParam('acc_head_id');

        if ($tv['record_id'] != '' ) {
            $searchVar->sqlSearchVar[] = "jm.journal_master_id  = {$tv['record_id']}";

        } else {
            $fn->setSearchVarForLinkData($searchVar, $linkRecType, 'jm.journal_master');

            //show only counter records
            $searchVar->sqlSearchVar[] = "jm.voucher_type = 'Counter'";

            //by default only show the foreign currency records
            $base_currency_id = $fn->getSessionParam('base_currency_id');
            $searchVar->sqlSearchVar[] = "c.currency_id != {$base_currency_id}";
            if ($acc_head_id != '') {
                $searchVar->sqlSearchVar[] = "j.acc_head_id = {$acc_head_id}";
            }
            if ($entry_date_from != '' && $entry_date_to != '') {
                $searchVar->sqlSearchVar[] = "vm.entry_date BETWEEN '{$entry_date_from}' AND '{$entry_date_to}'";
            } else if ($entry_date_from != '') {
                $searchVar->sqlSearchVar[] = "cm.entry_date >= '{$entry_date_from}'";
            }
            if ($tv['keyword'] != "") {
                $searchVar->sqlSearchVar[] = "(
                        jm.narration LIKE '%{$tv['keyword']}%'
                     OR j.narration LIKE '%{$tv['keyword']}%'
                     OR c.code = '{$tv['keyword']}'
                )";
            }
            $searchVar->sortOrder = "jm.entry_date DESC, j.journal_master_id DESC";

        }
    }

    function getAmount($fieldName, &$row){
        $fn = Zend_Registry::get('fn');

        $amount = '';
        if ($row['action'] == 'buy') {
            $amount = $row['debit'];
        } else {
            $amount = $row['credit'];
        }
		return $amount;
    }

    function getAmountBase($fieldName, &$row){
        $fn = Zend_Registry::get('fn');

        $amount = '';
        if ($row['action'] == 'buy') {
            $amount = $row['debit_base'];
        } else {
            $amount = $row['credit_base'];
        }
		return $amount;
    }

    function getAmountDebit($debit, $credit){
        $debit = $debit > $credit ? $debit : 0;
		return $debit;
    }

    function getAmountCredit($debit, $credit){
        $credit = $credit > $debit ? $credit : 0;
		return $credit;
    }


    function getSaveCounterValidate(){
        $cpUtil = Zend_Registry::get('cpUtil');

		$error = false;
		if (1 == 2) {
			$error = true;
			return array('error' => $error, 'errorJSON' => $cpUtil->getJsonText('error', '', 'error occurred'));
		}
		return array('error' => $error);
	}

    function getSaveCounter($print = false){
        $fn = Zend_Registry::get('fn');
        $cpUtil = Zend_Registry::get('cpUtil');
        $dbUtil = Zend_Registry::get('dbUtil');
        $db = Zend_Registry::get('db');
        $dbz = Zend_Registry::get('dbz');

        $validation = $this->getSaveCounterValidate();

       if ($validation['error']){
           return $validation['errorJSON'];
       }

		$journal_master_id = $fn->getPostParam('journal_master_id');
        $entry_date     = $fn->getPostParam('entry_date');
        $voucher_type   = 'Counter';
        $narration_main = $fn->getPostParam('narration_main');
        $acc_company_id = $fn->getSessionParam('acc_company_id');
        $staff_id       = $fn->getSessionParam('staff_id');
        $current_date_time = $cpUtil->getISODateTimeStr();

        $c_action = $fn->getPostParam('c_action');

        $counterRows = $this->getCounterRows();
        if ($journal_master_id == '') { //new journal
            $fa = array();
            $fa['entry_date']        = $entry_date;
            $fa['voucher_type']      = $voucher_type;
            $fa['narration']         = $narration_main;
            $fa['acc_company_id']    = $acc_company_id;
            $fa['staff_id']          = $staff_id;
            $fa['action']            = $c_action;
            $fa['creation_date']     = $current_date_time;
            $fa['modification_date'] = $current_date_time;

            $SQL = $dbUtil->getInsertSQLStringFromArray($fa, 'journal_master');
            $db->sql_query($SQL);
            $journal_master_id = $db->sql_nextid();

            foreach ($counterRows as $key => $counterRow) {
                $fa = array();
                $fa['journal_master_id'] = $journal_master_id;
                $fa['acc_head_id']       = $counterRow['acc_head_id'];
                $fa['currency_id']       = $counterRow['currency_id'];
                $fa['exch_rate_to_base'] = $counterRow['exch_rate_to_base'];
                $fa['debit']             = $counterRow['debit'];
                $fa['credit']            = $counterRow['credit'];
                $fa['debit_base']        = $counterRow['debit_base'];
                $fa['credit_base']       = $counterRow['credit_base'];
                $fa['narration']         = $counterRow['narration'];
                $fa['currency_type']     = $counterRow['currency_type'];
                $fa['creation_date']     = $current_date_time;
                $fa['modification_date'] = $current_date_time;

                $SQL = $dbUtil->getInsertSQLStringFromArray($fa, 'journal');
                $db->sql_query($SQL);
                $journal_id = $db->sql_nextid();

                $counterRows[$key]['journal_id'] = $journal_id;

                //update avg rate fields
                getCPModelObj('account_currency')->updateAvgBuyRate($c_action, $journal_id);
                getCPModelObj('account_currency')->updateAvgStockRate($c_action, $journal_id);
            }
            //update journal_id_rel
            foreach ($counterRows as $key => $counterRow) {
                $journal_id = $counterRow['journal_id'];
                $currency_type_alt = $counterRow['currency_type'] == 'base' ? 'foreign' : 'base';
                $SQL = "
                UPDATE journal j
                SET journal_id_rel = ?
                WHERE journal_master_id = ?
                  AND currency_type = ?
                ";
                $arr = array($journal_id, $journal_master_id, $currency_type_alt);
                $dbz->query($SQL, $arr);
            }

        } else { //save journal
            $fa = array();
            $fa['entry_date']        = $entry_date;
            $fa['voucher_type']      = $voucher_type;
            $fa['narration']         = $narration_main;
            $fa['acc_company_id']    = $acc_company_id;
            $fa['staff_id']          = $staff_id;
            $fa['modification_date'] = $current_date_time;

            $whereCondition = "WHERE journal_master_id = {$journal_master_id}";
            $SQL = $dbUtil->getUpdateSQLStringFromArray($fa, 'journal_master', $whereCondition);
            $db->sql_query($SQL);

            foreach ($counterRows as $counterRow) {
                $journal_id = $counterRow['journal_id'];
                $fa = array();
                $fa['journal_master_id'] = $journal_master_id;
                $fa['acc_head_id']       = $counterRow['acc_head_id'];
                $fa['currency_id']       = $counterRow['currency_id'];
                $fa['exch_rate_to_base'] = $counterRow['exch_rate_to_base'];
                $fa['debit']             = $counterRow['debit'];
                $fa['credit']            = $counterRow['credit'];
                $fa['debit_base']        = $counterRow['debit_base'];
                $fa['credit_base']       = $counterRow['credit_base'];
                $fa['narration']         = $counterRow['narration'];
                $fa['currency_type']     = $counterRow['currency_type'];
                $fa['modification_date'] = $current_date_time;

                if ($journal_id != '') {//save row
                    $whereCondition = "WHERE journal_id = {$journal_id}";
                    $SQL = $dbUtil->getUpdateSQLStringFromArray($fa, 'journal', $whereCondition);
                    $db->sql_query($SQL);
                } else { //new row
                    $fa['creation_date']  = date('Y-m-d H:i:s');
                    $SQL = $dbUtil->getInsertSQLStringFromArray($fa, 'journal');
                    $db->sql_query($SQL);
                    $journal_id = $db->sql_nextid();
                }
                //update avg rate fields
                getCPModelObj('account_currency')->updateAvgBuyRate($c_action, $journal_id);
                getCPModelObj('account_currency')->updateAvgStockRate($c_action, $journal_id);

            } //end for
        }

        $arr = array(
            'status' => 'success'
           ,'journal_master_id' => $journal_master_id
        );
        return $cpUtil->getJsonFromArray($arr);

    }

    function getCounterRows(){
        $fn = Zend_Registry::get('fn');
        $c_action = $fn->getReqParam('c_action'); //sell / buy

        $acc_head_id_base_curr = getCPModelObj('account_accHead')->getBaseCurrencyAccHeadIdCounter();
        $rowAccHeadBaseCurr = $fn->getRecordRowByID('acc_head', 'acc_head_id', $acc_head_id_base_curr);

		$arrRows = array();
		foreach ($_POST as $key => $value) {
			$arr = explode('-', $key);
			if (count($arr) < 2) {
				continue;
			}
			//$key ex: acc_head_id-1 or debit-1
			$fieldName = $arr[0];
			$rowInd    = $arr[1];

			if ($fieldName == 'acc_head_id') {
				$arrRow = array();
				$journal_id_fld        = "journal_id-{$rowInd}";
				$acc_head_id_fld       = "acc_head_id-{$rowInd}";
				$amount_fld            = "amount-{$rowInd}";
				$exch_rate_to_base_fld = "exch_rate_to_base-{$rowInd}";
				$amount_base_fld       = "amount_base-{$rowInd}";
				$narration_fld         = "narration-{$rowInd}";

                $db_amount_fld = '';
                $db_amount_base_fld = '';
                if ($c_action == 'sell') {
                    $db_amount_fld = 'credit';
                    $db_amount_base_fld = 'credit_base';
                } else {
                    $db_amount_fld = 'debit';
                    $db_amount_base_fld = 'debit_base';
                }
				$arrRow['debit']       = '';
				$arrRow['credit']      = '';
				$arrRow['debit_base']  = '';
				$arrRow['credit_base'] = '';

				$journal_id = $fn->getPostParam($journal_id_fld);

                $acc_head_id = $fn->getPostParam($acc_head_id_fld);
                $rowAccHead = $fn->getRecordRowByID('acc_head', 'acc_head_id', $acc_head_id);
				$arrRow['journal_id']        = $journal_id;
				$arrRow['acc_head_id']       = $acc_head_id;
                $arrRow['currency_id']       = $rowAccHead['currency_id'];

				$arrRow['exch_rate_to_base'] = $fn->getPostParam($exch_rate_to_base_fld);
				$arrRow[$db_amount_fld]      = $fn->getPostParam($amount_fld);
				$arrRow[$db_amount_base_fld] = $fn->getPostParam($amount_base_fld);
				$arrRow['narration']         = $fn->getPostParam($narration_fld);
                $arrRow['currency_type']     = 'foreign';

                if ($arrRow['acc_head_id'] && $arrRow['exch_rate_to_base']) {
                    $arrRows[] = $arrRow;

                    //create counter history record base currency
                    $db_amount_fld = '';
                    $db_amount_base_fld = '';
                    if ($c_action == 'sell') {
                        $db_amount_fld = 'debit';
                        $db_amount_base_fld = 'debit_base';
                    } else {
                        $db_amount_fld = 'credit';
                        $db_amount_base_fld = 'credit_base';
                    }

                    $arrRow = array();
                    $arrRow['debit']       = '';
                    $arrRow['credit']      = '';
                    $arrRow['debit_base']  = '';
                    $arrRow['credit_base'] = '';
                    $arrRow['narration']   = '';

                    $rowJ = $fn->getRecordRowByID('journal', 'journal_id', $journal_id);
                    $arrRow['journal_id']        = $rowJ['journal_id_rel'];
                    $arrRow['acc_head_id']       = $acc_head_id_base_curr;
                    $arrRow['currency_id']       = $rowAccHeadBaseCurr['currency_id'];
                    $arrRow['exch_rate_to_base'] = 1;
                    $arrRow[$db_amount_fld]      = $fn->getPostParam($amount_base_fld);
                    $arrRow[$db_amount_base_fld] = $fn->getPostParam($amount_base_fld);
                    $arrRow['currency_type']     = 'base';
                    $arrRows[] = $arrRow;
                }
			} //acc_head_id valid row
		} //for

		return $arrRows;
    }


    function getExportData($dataArray, $print = false){
        $fn = Zend_Registry::get('fn');

        $tbsExcel = includeCPClass('Lib', 'TBSExcelExportWrapper', 'TBSExcelExportWrapper');

        $output_file_name = 'counter_' . date('d-m-Y') . '.xlsx';
        $template = __DIR__ . '/assets/counter.xlsx';

        $config = array(
             'output_file_name' => $output_file_name
            ,'dataArray' => $dataArray
            ,'template' => $template
            ,'print' => $print
        );

        return $tbsExcel->exportData($config);

    }

    function getImportData(){
        $cpCfg = Zend_Registry::get('cpCfg');
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');

        $phpExWrap = includeCPClass('Lib', 'PhpExcelImportWrapper', 'PhpExcelImportWrapper');
        $opts = &$phpExWrap->tmpOpts;
        $entry_date = $fn->getPostParam('entry_date');
        $opts['entry_date']       = $entry_date;
        $opts['counter_setup_id'] = $fn->getPostParam('counter_setup_id');

        $entry_date_obj = strtotime($entry_date);
        $sheetName = date('d', $entry_date_obj);

        $fa = array(
              'currency_code' => $phpExWrap->getImportFldObj('Currency Code', 1)
             ,'buy_amount' => $phpExWrap->getImportFldObj('V Buy Amount', 2)
             ,'buy_rate' => $phpExWrap->getImportFldObj('V Buy Rate', 3)
             ,'sell_amount' => $phpExWrap->getImportFldObj('V Sell Amount', 4)
             ,'sell_rate' => $phpExWrap->getImportFldObj('V Sell Rate', 5)
        );

        //delete journal records for the imported day
        $SQL = "
        DELETE j, jm
        FROM journal j
        JOIN journal_master jm ON j.journal_master_id = jm.journal_master_id
        WHERE jm.entry_date = '{$opts['entry_date']}'
          AND jm.voucher_type = 'Counter'
        ";
        $db->sql_query($SQL);

        //---------------------------------//
        $config = array(
             'module' => 'account_counterMaster'
            ,'overrideDefaultProcessCallback' => 'counterImportCallback'
            ,'fldsArr' => $fa
            ,'sheetName' => $sheetName
        );

        $phpExWrap->importData($config);

        $titleMessage = "Please see the log below: ";
        $logMessage = $phpExWrap->getLog($titleMessage);

        return "
        <div class='m10'>
            <script>
               window.opener.location = window.opener.location;
            </script>
            <div class='left'>
                <h1>Import Completed. Please <strong><a href='javascript:window.close();'>close</a></strong> this window</h1>
            </div>

            <div class='left'>
            {$logMessage}
            </div>
        </div>
        ";
    }

    function counterImportCallback($fa, $curRow, $phpExcel) {
        $cpCfg = Zend_Registry::get('cpCfg');
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUtil = Zend_Registry::get('cpUtil');

        $opts = &$phpExcel->tmpOpts;
        $currency_code = $phpExcel->getEFV($fa['currency_code']['excelFld'], $curRow);
        $buy_amount    = $phpExcel->getEFV($fa['buy_amount']['excelFld'], $curRow);
        $buy_rate      = $phpExcel->getEFV($fa['buy_rate']['excelFld'], $curRow);
        $sell_amount   = $phpExcel->getEFV($fa['sell_amount']['excelFld'], $curRow);
        $sell_rate     = $phpExcel->getEFV($fa['sell_rate']['excelFld'], $curRow);
        $current_date  = $cpUtil->getISODateStr();

		$counter_action   = $buy_amount != '' ? 'buy' : 'sell';//sell/buy
        $journal_setup_id = $opts['counter_setup_id'];
        $entry_date       = $opts['entry_date'];

        //do not process if all the columns are empty
        if   ($currency_code == ''
           && $buy_amount == ''
           && $buy_rate == ''
           && $sell_amount == ''
           && $sell_rate == ''
        ) {
            return;
        }

        $acc_head_id = getCPModelObj('account_accHead')
                       ->getCurrencyAccHeadIdCounter($currency_code);
        if (!$acc_head_id) {
            $message = "Currency Code: {$currency_code} is missing in the Chart of Accounts";
            $phpExcel->addLog($message);
            return;
        }
        if   ($currency_code != ''
           && $buy_amount != ''
           && $buy_rate != ''
           && $sell_amount != ''
           && $sell_rate != ''
        ) {
            $message = "Invalid values.";
            $phpExcel->addLog($message);
            return;
        }
        if   (($buy_amount != '' && $buy_rate == '')
           || ($buy_rate != '' && $buy_amount == '')
        ) {
            $message = "Invalid values.";
            $phpExcel->addLog($message);
            return;
        }
        if   (($sell_amount != '' && $sell_rate == '')
           || ($sell_rate != '' && $sell_amount == '')
        ) {
            $message = "Invalid values.";
            $phpExcel->addLog($message);
            return;
        }


        $amount = 0;
        $amount_base = 0;
        if ($counter_action == 'buy') {
            $amount = $buy_amount;
            $exch_rate_to_base = $buy_rate;
        } else {//sell
            $amount = $sell_amount;
            $exch_rate_to_base = $sell_rate;
        }
        $amount_base = $amount * $exch_rate_to_base;

        $narration        = ''; //temporarily it's kept empty
        $acc_company_id   = $fn->getSessionParam('acc_company_id');
        $base_currency_id = $fn->getSessionParam('base_currency_id');
        $staff_id         = $fn->getSessionParam('staff_id');
        $creation_date    = $cpUtil->getISODateStr();

        $acc_head_id_base_curr = getCPModelObj('account_accHead')->getBaseCurrencyAccHeadIdCounter();
        $rowAccHead = $fn->getRecordRowByID('acc_head', 'acc_head_id', $acc_head_id);

        //create journal master record
        $fa = array();
        $fa['counter_setup_id'] = $opts['counter_setup_id'];
        $fa['entry_date']       = $entry_date;
        $fa['narration']        = $narration;
        $fa['acc_company_id']   = $acc_company_id;
        $fa['staff_id']         = $staff_id;
        $fa['creation_date']    = $creation_date;
        $fa['action']           = $counter_action;
        $fa['voucher_type']     = 'Counter';
        $fa['modification_date'] = $current_date;

        $SQL = $dbUtil->getInsertSQLStringFromArray($fa, 'journal_master');
        $db->sql_query($SQL);
        $journal_master_id = $db->sql_nextid();

        //create journal history record foreign currency
        $fa = array();
        $fa['journal_master_id'] = $journal_master_id;
        $fa['acc_head_id']       = $acc_head_id;
        $fa['currency_id']       = $rowAccHead['currency_id'];
        $fa['exch_rate_to_base'] = $exch_rate_to_base;
        $fa['debit']             = $counter_action == 'buy' ? $amount : 0;
        $fa['credit']            = $counter_action == 'sell' ? $amount : 0;
        $fa['debit_base']        = $counter_action == 'buy' ? $amount_base : 0;
        $fa['credit_base']       = $counter_action == 'sell' ? $amount_base : 0;
        $fa['currency_type']     = 'foreign';
        $fa['creation_date']     = $current_date;
        $fa['modification_date'] = $current_date;
        $SQL = $dbUtil->getInsertSQLStringFromArray($fa, 'journal');
        $db->sql_query($SQL);
        $journal_id = $db->sql_nextid();

        $action = $fa['debit'] ? 'buy' : 'sell';
        getCPModelObj('account_currency')->updateAvgBuyRate($action, $journal_id);
        getCPModelObj('account_currency')->updateAvgStockRate($action, $journal_id);


        //create journal history record base currency
        $fa = array();
        $fa['journal_master_id'] = $journal_master_id;
        $fa['acc_head_id']       = $acc_head_id_base_curr;
        $fa['currency_id']       = $base_currency_id;
        $fa['exch_rate_to_base'] = 1;
        $fa['debit']             = $counter_action == 'sell' ? $amount_base : 0;
        $fa['credit']            = $counter_action == 'buy' ? $amount_base : 0;
        $fa['debit_base']        = $fa['debit'];
        $fa['credit_base']       = $fa['credit'];
        $fa['currency_type']     = 'base';
        $fa['creation_date']     = $current_date;
        $fa['modification_date'] = $current_date;
        $SQL = $dbUtil->getInsertSQLStringFromArray($fa, 'journal');
        $db->sql_query($SQL);
        $journal_id = $db->sql_nextid();

        $action = $fa['debit'] ? 'buy' : 'sell';
        getCPModelObj('account_currency')->updateAvgBuyRate($action, $journal_id);
        getCPModelObj('account_currency')->updateAvgStockRate($action, $journal_id);
    }

    function getPrintCounter(){
        $fn = Zend_Registry::get('fn');
        $dbz = Zend_Registry::get('dbz');
        $cpCfg = Zend_Registry::get('cpCfg');

        $journal_master_id = $fn->getReqParam('journal_master_id');
        
        $tbsExcel = includeCPClass('Lib', 'TBSExcelExportWrapper', 'TBSExcelExportWrapper');

        $SQL = "
        SELECT CONCAT_WS('-', LEFT(jm.voucher_type, 1), jm.journal_master_id) AS voucher_code
              ,jm.narration AS narration_main
              ,jm.modification_date
              ,jm.action AS c_action
              ,s.short_code AS staff_code
        FROM journal_master jm
        JOIN staff s ON s.staff_id = jm.staff_id
        WHERE journal_master_id = ?
        ";
        $arr = array($journal_master_id);
        $stmt = $dbz->query($SQL, $arr);
        $rowHeader = $stmt->fetch();
        
        $SQL = "
        SELECT c.code AS currency_code
              ,CASE
               WHEN jm.action = 'buy' THEN j.debit
               ELSE j.credit
               END AS amount
               
              ,CASE
               WHEN jm.action = 'buy' THEN j.debit_base
               ELSE j.credit_base
               END AS amount_base
               
              ,j.exch_rate_to_base
              ,j.narration
        FROM journal j
        JOIN journal_master jm ON jm.journal_master_id = j.journal_master_id
        JOIN acc_head ah ON ah.acc_head_id = j.acc_head_id
        JOIN currency c ON c.currency_id = ah.currency_id
        WHERE jm.journal_master_id = ?
          AND j.currency_type = 'foreign'
        ";
        $arr = array($journal_master_id);
        $stmt = $dbz->query($SQL, $arr);
        
        $dataArray = array();
        $amount_base_sum = 0;
        while ($row = $stmt->fetch()) {
            $currency_code = $row['currency_code'];
            $dataArray[] = $row;
            $amount_base_sum += $row['amount_base'];
        }

        $base_currency_code = getCPModelObj('account_accCompany')->getBaseCurrencyCode();
        
        $amount_base_sum = round($amount_base_sum, $cpCfg['cp.displayDecimalLength']);
        
        $c_action = $rowHeader['c_action'];
        $globalArray = array(
             'base_currency_code' => $base_currency_code
            ,'voucher_code' => $rowHeader['voucher_code']
            ,'c_action' => $c_action
            ,'staff_code' => $rowHeader['staff_code']
            ,'narration_main' => $rowHeader['narration_main']
            ,'amount_base_sum' => $amount_base_sum
            ,'amount_base_sum_words' => $fn->convertNumberToWords($amount_base_sum)
            ,'modification_date' => $rowHeader['modification_date']
            ,'action' => $rowHeader['modification_date']
        );

        $output_file_name = 'Counter_' . $currency_code . date('d-m-Y') . '.xlsx';
        $template = __DIR__ . "/assets/counter_print-{$c_action}.xlsx";

        $config = array(
             'output_file_name' => $output_file_name
            ,'dataArray' => $dataArray
            ,'globalArray' => $globalArray
            ,'template' => $template
        );

        return $tbsExcel->exportData($config);

    }
}
