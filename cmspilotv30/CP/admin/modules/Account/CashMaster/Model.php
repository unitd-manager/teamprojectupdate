<?
class CP_Admin_Modules_Account_CashMaster_Model extends CP_Common_Lib_ModuleModelAbstract
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
               WHEN jm.action = 'receipt' THEN j.debit
               ELSE ''
               END AS receipt_amount

              ,CASE
               WHEN jm.action = 'payment' THEN j.credit
               ELSE ''
               END AS payment_amount

              ,CASE
               WHEN jm.action = 'receipt' THEN j.exch_rate_to_base
               ELSE ''
               END AS receipt_rate

              ,CASE
               WHEN jm.action = 'payment' THEN j.exch_rate_to_base
               ELSE ''
               END AS payment_rate

              ,CASE
               WHEN jm.action = 'receipt' THEN j.debit_base
               ELSE j.credit_base
               END AS amount_base

              ,j.exch_rate_to_base
              ,j.credit_base
              ,j.debit_base
              ,j.currency_id
              ,c.title AS currency
              ,c.code AS currency_code
              ,ah.acc_head_id
              ,ah2.title AS acc_head
              ,jm.creation_date
              ,jm.modification_date
              ,CONCAT_WS('-', LEFT(jm.voucher_type, 1), jm.journal_master_id) AS voucher_code
              ,s.short_code AS staff_short_code
              ,s.staff_id
        FROM journal_master jm
        JOIN journal j ON j.journal_master_id = jm.journal_master_id
        JOIN currency c ON c.currency_id = j.currency_id
        JOIN acc_head ah ON ah.acc_head_id = j.acc_head_id
        JOIN acc_head ah2 ON ah2.acc_head_id = jm.acc_head_id
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

            //show only cash records
            $searchVar->sqlSearchVar[] = "jm.voucher_type = 'Cash'";

            //by default only show the foreign currency records
            $searchVar->sqlSearchVar[] = "j.currency_type = 'foreign'";
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


    function getSaveCashValidate(){
        $cpUtil = Zend_Registry::get('cpUtil');

		$error = false;
		if (1 == 2) {
			$error = true;
			return array('error' => $error, 'errorJSON' => $cpUtil->getJsonText('error', '', 'error occurred'));
		}
		return array('error' => $error);
	}

    function getSaveCash($print = false){
        $fn = Zend_Registry::get('fn');
        $cpUtil = Zend_Registry::get('cpUtil');
        $dbUtil = Zend_Registry::get('dbUtil');
        $db = Zend_Registry::get('db');
        $dbz = Zend_Registry::get('dbz');
        $fnsModGrp = includeCPClass('ModGroup', 'Account', 'Functions');

        $validation = $this->getSaveCashValidate();

       if ($validation['error']){
           return $validation['errorJSON'];
       }

		$journal_master_id = $fn->getPostParam('journal_master_id');
        $entry_date                   = $fn->getPostParam('entry_date');
        $voucher_type                 = 'Cash';
        $narration_main               = $fn->getPostParam('narration_main');
        $acc_company_id               = $fn->getSessionParam('acc_company_id');
        $acc_head_id_customer         = $fn->getPostParam('acc_head_id_customer');
        $exch_rate_cust_curr          = $fn->getPostParam('exch_rate_cust_curr');
        $exch_rate_cust_curr_lc_to_fc = $fn->getPostParam('exch_rate_cust_curr_lc_to_fc');
        $amount_sum_cust_curr         = $fn->getPostParam('amount_sum_cust_curr');
        $amount                       = $fn->getPostParam('amount');
        $staff_id                     = $fn->getSessionParam('staff_id');
        $current_date_time            = $cpUtil->getISODateTimeStr();

        $c_action = $fn->getPostParam('c_action');

        $cashRows = $this->getCashRows();
        if ($journal_master_id == '') { //new journal master record
            $fa = array();
            $fa['entry_date']                   = $entry_date;
            $fa['voucher_type']                 = $voucher_type;
            $fa['narration']                    = $narration_main;
            $fa['acc_company_id']               = $acc_company_id;
            $fa['staff_id']                     = $staff_id;
            $fa['action']                       = $c_action;
            $fa['amount']                       = $amount;
            $fa['acc_head_id']                  = $acc_head_id_customer;
            $fa['exch_rate_cust_curr']          = $exch_rate_cust_curr;
            $fa['exch_rate_cust_curr_lc_to_fc'] = $exch_rate_cust_curr_lc_to_fc;
            $fa['amount_sum_cust_curr']         = $amount_sum_cust_curr;
            $fa['creation_date']                = $current_date_time;
            $fa['modification_date']            = $current_date_time;

            $SQL = $dbUtil->getInsertSQLStringFromArray($fa, 'journal_master');
            $db->sql_query($SQL);
            $journal_master_id = $db->sql_nextid();

            foreach ($cashRows as $key => $cashRow) {
                $fa = array();
                $fa['journal_master_id'] = $journal_master_id;
                $fa['acc_head_id']       = $cashRow['acc_head_id'];
                $fa['currency_id']       = $cashRow['currency_id'];
                $fa['exch_rate_to_base'] = $cashRow['exch_rate_to_base'];
                $fa['debit']             = $cashRow['debit'];
                $fa['credit']            = $cashRow['credit'];
                $fa['debit_base']        = $cashRow['debit_base'];
                $fa['credit_base']       = $cashRow['credit_base'];
                $fa['narration']         = $cashRow['narration'];
                $fa['currency_type']     = $cashRow['currency_type'];
                $fa['creation_date']     = $current_date_time;
                $fa['modification_date'] = $current_date_time;

                $SQL = $dbUtil->getInsertSQLStringFromArray($fa, 'journal');
                $db->sql_query($SQL);
                $journal_id = $db->sql_nextid();

                $cashRows[$key]['journal_id'] = $journal_id;

                $c_action2 = $fnsModGrp->getActualAction($c_action);
                //update avg rate fields
                getCPModelObj('account_currency')->updateAvgBuyRate($c_action2, $journal_id);
                getCPModelObj('account_currency')->updateAvgStockRate($c_action2, $journal_id);
            }
            //update journal_id_rel
            foreach ($cashRows as $key => $cashRow) {
                $journal_id = $cashRow['journal_id'];
                $currency_type_alt = $cashRow['currency_type'] == 'base' ? 'foreign' : 'base';
                $SQL = "
                UPDATE journal j
                SET journal_id_rel = ?
                WHERE journal_master_id = ?
                  AND currency_type = ?
                ";
                $arr = array($journal_id, $journal_master_id, $currency_type_alt);
                $dbz->query($SQL, $arr);
            }

        } else { //save journal master record
            $fa = array();
            $fa['entry_date']                   = $entry_date;
            $fa['voucher_type']                 = $voucher_type;
            $fa['narration']                    = $narration_main;
            $fa['acc_company_id']               = $acc_company_id;
            $fa['amount']                       = $amount;
            $fa['acc_head_id']                  = $acc_head_id_customer;
            $fa['exch_rate_cust_curr']          = $exch_rate_cust_curr;
            $fa['exch_rate_cust_curr_lc_to_fc'] = $exch_rate_cust_curr_lc_to_fc;
            $fa['amount_sum_cust_curr']         = $amount_sum_cust_curr;
            $fa['staff_id']                     = $staff_id;
            $fa['modification_date']            = $current_date_time;

            $whereCondition = "WHERE journal_master_id = {$journal_master_id}";
            $SQL = $dbUtil->getUpdateSQLStringFromArray($fa, 'journal_master', $whereCondition);
            $db->sql_query($SQL);

            foreach ($cashRows as $cashRow) {
                $journal_id = $cashRow['journal_id'];
                $fa = array();
                $fa['journal_master_id'] = $journal_master_id;
                $fa['acc_head_id']       = $cashRow['acc_head_id'];
                $fa['currency_id']       = $cashRow['currency_id'];
                $fa['exch_rate_to_base'] = $cashRow['exch_rate_to_base'];
                $fa['debit']             = $cashRow['debit'];
                $fa['credit']            = $cashRow['credit'];
                $fa['debit_base']        = $cashRow['debit_base'];
                $fa['credit_base']       = $cashRow['credit_base'];
                $fa['narration']         = $cashRow['narration'];
                $fa['currency_type']     = $cashRow['currency_type'];
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

    function getCashRows(){
        $fn = Zend_Registry::get('fn');
        $c_action = $fn->getReqParam('c_action'); //payment/receipt

        $acc_head_id_customer = $fn->getPostParam('acc_head_id_customer');
        $rowAccHeadCust = $fn->getRecordRowByID('acc_head', 'acc_head_id', $acc_head_id_customer);

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
                if ($c_action == 'receipt') {
                    $db_amount_fld = 'debit';
                    $db_amount_base_fld = 'debit_base';
                } else {
                    $db_amount_fld = 'credit';
                    $db_amount_base_fld = 'credit_base';
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

                $arrRows[] = $arrRow;
			} //acc_head_id valid row
		} //for


        //create history record for customer's currency ex: RMB
        //if customer's account is RMB
        $db_amount_fld = '';
        $db_amount_base_fld = '';
        if ($c_action == 'receipt') {
            $db_amount_fld = 'credit';
            $db_amount_base_fld = 'credit_base';
        } else {
            $db_amount_fld = 'debit';
            $db_amount_base_fld = 'debit_base';
        }

        $arrRow = array();
        $arrRow['debit']       = '';
        $arrRow['credit']      = '';
        $arrRow['debit_base']  = '';
        $arrRow['credit_base'] = '';
        $arrRow['narration']   = '';

        $rowJ = $fn->getRecordRowByID('journal', 'journal_id', $journal_id);
        $arrRow['journal_id']        = $rowJ['journal_id_rel'];
        $arrRow['acc_head_id']       = $acc_head_id_customer;
        $arrRow['currency_id']       = $rowAccHeadCust['currency_id'];
        $arrRow['exch_rate_to_base'] = 1;
        $arrRow[$db_amount_fld]      = $fn->getPostParam($amount_base_fld);
        $arrRow[$db_amount_base_fld] = $fn->getPostParam($amount_base_fld);
        $arrRow['currency_type']     = 'base';
        $arrRows[] = $arrRow;


		return $arrRows;
    }


    function getExportData($dataArray, $print = false){
        $fn = Zend_Registry::get('fn');

        $tbsExcel = includeCPClass('Lib', 'TBSExcelExportWrapper', 'TBSExcelExportWrapper');

        $output_file_name = 'cash_' . date('d-m-Y') . '.xlsx';
        $template = __DIR__ . '/assets/cash.xlsx';

        $config = array(
             'output_file_name' => $output_file_name
            ,'dataArray' => $dataArray
            ,'template' => $template
            ,'print' => $print
        );

        return $tbsExcel->exportData($config);

    }

    function getPrintCash(){
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
              ,jm.exch_rate_cust_curr
              ,jm.exch_rate_cust_curr_lc_to_fc
              ,jm.amount_sum_cust_curr
              ,ah.title AS account_title
              ,c.code AS customer_currency_code
              ,s.short_code AS staff_code
        FROM journal_master jm
        JOIN staff s ON s.staff_id = jm.staff_id
        JOIN acc_head ah ON ah.acc_head_id = jm.acc_head_id
        JOIN currency c ON c.currency_id = ah.currency_id
        WHERE journal_master_id = ?
        ";
        $arr = array($journal_master_id);
        $stmt = $dbz->query($SQL, $arr);
        $rowHeader = $stmt->fetch();

        $SQL = "
        SELECT c.code AS currency_code
              ,CASE
               WHEN jm.action = 'receipt' THEN j.debit
               ELSE j.credit
               END AS amount

              ,CASE
               WHEN jm.action = 'receipt' THEN j.debit_base
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
            ,'account_title' => $rowHeader['account_title']
            ,'exch_rate_cust_curr' => $rowHeader['exch_rate_cust_curr']
            ,'amount_sum_cust_curr' => $rowHeader['amount_sum_cust_curr']
            ,'amount_base_sum' => $amount_base_sum
            ,'amount_base_sum_words' => $fn->convertNumberToWords($amount_base_sum)
            ,'modification_date' => $rowHeader['modification_date']
            ,'action' => $rowHeader['modification_date']
        );

        $output_file_name = 'Cash_' . $currency_code . date('d-m-Y') . '.xlsx';
        $template = __DIR__ . "/assets/cash_print-{$c_action}.xlsx";

        $config = array(
             'output_file_name' => $output_file_name
            ,'dataArray' => $dataArray
            ,'globalArray' => $globalArray
            ,'template' => $template
        );

        return $tbsExcel->exportData($config);

    }
}
