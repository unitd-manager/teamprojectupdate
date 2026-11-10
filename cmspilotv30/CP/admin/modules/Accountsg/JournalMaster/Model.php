<?
class CP_Admin_Modules_Accountsg_JournalMaster_Model extends CP_Common_Lib_ModuleModelAbstract
{
    /**
     *
     */
    function getSQL() {
        $SQL = "
        SELECT jm.journal_master_id
              ,jm.entry_date
              ,jm.voucher_type
              ,jm.narration AS narration_main
              ,jm.ledger_authorized
              ,j.journal_id
              ,j.credit
              ,j.debit
              ,j.exch_rate_to_base
              ,j.credit_base
              ,j.debit_base
              ,j.narration
              ,j.pending
              ,j.avg_buy_rate
              ,j.avg_stock_rate
              ,j.margin
              ,j.currency_id
              ,ah.title AS acc_head
              ,jm.creation_date
              ,jm.modification_date
              ,CONCAT_WS('-', LEFT(jm.voucher_type, 1), jm.journal_master_id) AS voucher_code
        FROM journal_master jm
        JOIN journal j ON j.journal_master_id = jm.journal_master_id
        JOIN acc_head ah ON ah.acc_head_id = j.acc_head_id
        JOIN staff s ON s.staff_id = jm.staff_id
        ";
        return $SQL;
    }

    /**
     *
     */
    function getSQLForPager() {
        $SQL = "
        SELECT count(*)
        FROM journal_master jm
        JOIN journal j ON j.journal_master_id = jm.journal_master_id
        JOIN acc_head ah ON ah.acc_head_id = j.acc_head_id
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

        $voucher_type = $fn->getReqParam('voucher_type');
        $entry_date_from = $fn->getReqParam('entry_date_from');
        $entry_date_to = $fn->getReqParam('entry_date_to');
        $show_counter = $fn->getReqParam('show_counter', 0);
        $is_counter = $show_counter;

        if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "jm.journal_master_id  = {$tv['record_id']}";

        } else {
            $fn->setSearchVarForLinkData($searchVar, $linkRecType, 'jm.journal_master');

            if ($is_counter == 0) {
                $searchVar->sqlSearchVar[] = "(jm.is_counter = '{$is_counter}' OR jm.is_counter IS NULL)";
            }

            if ($voucher_type != '') {
                $searchVar->sqlSearchVar[] = "jm.voucher_type = '{$voucher_type}'";
            }
            if ($entry_date_from != '' && $entry_date_to != '') {
                $searchVar->sqlSearchVar[] = "jm.entry_date BETWEEN '{$entry_date_from}' AND '{$entry_date_to}'";
            } else if ($entry_date_from != '') {
                $searchVar->sqlSearchVar[] = "jm.entry_date >= '{$entry_date_from}'";
            }
            if ($tv['keyword'] != "") {
                $searchVar->sqlSearchVar[] = "(
                        jm.narration LIKE '%{$tv['keyword']}%'
                     OR j.narration LIKE '%{$tv['keyword']}%'
                )";
            }

            if ($tv['searchDone'] == 0){
                $SQL = "
                SELECT MAX(entry_date) AS last_entry_date
                FROM journal_master
                ";
                $rec = $fn->getRecordBySQL($SQL);
                //$searchVar->sqlSearchVar[] = "jm.entry_date = '{$rec['last_entry_date']}'";
            }
            $searchVar->sortOrder = "jm.entry_date DESC, j.journal_master_id DESC";
        }
    }

    /**
     *
     */
    function getJournalRows(){
        $fn = Zend_Registry::get('fn');

        $arrRows = array();
        foreach ($_POST as $key => $value) {
            $arr = explode('-', $key);
            if (count($arr) < 2) {
                continue;
            }
            //$key ex: acc_head-1 or debit-1
            $fieldName = $arr[0];
            $rowInd    = $arr[1];

            if ($fieldName == 'acc_head') {
                $arrRow = array();
                $journal_id_fld        = "journal_id-{$rowInd}";
                $acc_head_id_fld       = "acc_head_id-{$rowInd}";
                $debit_fld             = "debit-{$rowInd}";
                $credit_fld            = "credit-{$rowInd}";
                $debit_base_fld        = "debit_base-{$rowInd}";
                $credit_base_fld       = "credit_base-{$rowInd}";
                $narration_fld         = "narration-{$rowInd}";

                $arrRow['journal_id']        = $fn->getPostParam($journal_id_fld);
                $arrRow['acc_head_id']       = $fn->getPostParam($acc_head_id_fld);
                $arrRow['debit']             = $fn->getPostParam($debit_fld);
                $arrRow['credit']            = $fn->getPostParam($credit_fld);
                $arrRow['debit_base']        = $fn->getPostParam($debit_base_fld);
                $arrRow['credit_base']       = $fn->getPostParam($credit_base_fld);
                $arrRow['narration']         = $fn->getPostParam($narration_fld);

                if ($arrRow['acc_head_id']) {
                    $arrRows[] = $arrRow;
                }
            }
        }

        return $arrRows;
    }

    /**
     *
     */
    function getSaveJournalValidate(){
        $cpUtil = Zend_Registry::get('cpUtil');

        $journalRows = $this->getJournalRows();
        $error = false;
        if (1 == 2) {
            $error = true;
            return array('error' => $error, 'errorJSON' => $cpUtil->getJsonText('error', '', 'error occurred'));
        }
        return array('error' => $error);
    }

    /**
     *
     */
    function getSaveJournal(){
        $fn = Zend_Registry::get('fn');
        $cpUtil = Zend_Registry::get('cpUtil');
        $dbUtil = Zend_Registry::get('dbUtil');
        $db = Zend_Registry::get('db');
        $validate = Zend_Registry::get('validate');
        $cpCfg = Zend_Registry::get('cpCfg');

       $validation = $this->getSaveJournalValidate();

       if ($validation['error']){
           return $validation['errorJSON'];
       }

        $journal_master_id = $fn->getPostParam('journal_master_id');
        $entry_date     = $fn->getPostParam('entry_date');
        $voucher_type   = $fn->getPostParam('voucher_type');
        $narration      = $fn->getPostParam('narration_main');
        $acc_company_id = $fn->getSessionParam('acc_company_id');
        $staff_id       = $fn->getSessionParam('staff_id');
        $current_date   = $cpUtil->getISODateStr();

        $journalRows = $this->getJournalRows();
        if ($journal_master_id == '') { //new journal
            $fa = array();
            $fa['entry_date']        = $entry_date;
            $fa['voucher_type']      = $voucher_type;
            $fa['narration']         = $narration;
            $fa['acc_company_id']    = $acc_company_id;
            $fa['staff_id']          = $staff_id;
            $fa['creation_date']     = $current_date;
            $fa['modification_date'] = $current_date;

            $SQL = $dbUtil->getInsertSQLStringFromArray($fa, 'journal_master');
            $result = $db->sql_query($SQL);
            $journal_master_id = $db->sql_nextid();

            foreach ($journalRows as $journalRow) {
                $fa = array();
                $fa['journal_master_id'] = $journal_master_id;
                $fa['acc_head_id']       = $journalRow['acc_head_id'];
                $fa['debit']             = $journalRow['debit'];
                $fa['credit']            = $journalRow['credit'];
                $fa['debit_base']        = $journalRow['debit_base'];
                $fa['credit_base']       = $journalRow['credit_base'];
                $fa['narration']         = $journalRow['narration'];
                $fa['creation_date']     = $current_date;
                $fa['modification_date'] = $current_date;

                $SQL = $dbUtil->getInsertSQLStringFromArray($fa, 'journal');
                $db->sql_query($SQL);
                $journal_id = $db->sql_nextid();

                if ($cpCfg['m.accountsg.journalMaster.hasAvgStock']) {
                    $action = $fa['debit'] ? 'buy' : 'sell';
                    getCPModelObj('accountsg_currency')->updateAvgBuyRate($action, $journal_id);
                    getCPModelObj('accountsg_currency')->updateAvgStockRate($action, $journal_id);
                }
            }
        } else { //save journal
            $fa = array();
            $fa['entry_date']        = $entry_date;
            $fa['voucher_type']      = $voucher_type;
            $fa['narration']         = $narration;
            $fa['acc_company_id']    = $acc_company_id;
            $fa['staff_id']          = $staff_id;
            $fa['modification_date'] = $current_date;

            $whereCondition = "WHERE journal_master_id = {$journal_master_id}";
            $SQL = $dbUtil->getUpdateSQLStringFromArray($fa, 'journal_master', $whereCondition);
            $db->sql_query($SQL);

            $journal_ids_str = '';
            foreach ($journalRows as $journalRow) {
                $journal_id = $journalRow['journal_id'];
                $fa = array();
                $fa['journal_master_id'] = $journal_master_id;
                $fa['acc_head_id']       = $journalRow['acc_head_id'];
                $fa['debit']             = $journalRow['debit'];
                $fa['credit']            = $journalRow['credit'];
                $fa['debit_base']        = $journalRow['debit_base'];
                $fa['credit_base']       = $journalRow['credit_base'];
                $fa['narration']         = $journalRow['narration'];
                $fa['modification_date'] = $current_date;

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
                $journal_ids_str .= "{$journal_id},";

            } //end for
            //delete removed row
            $journal_ids_str = trim($journal_ids_str, ",");
            $SQL = "
            DELETE FROM journal
            WHERE journal_master_id = {$journal_master_id}
              AND journal_id NOT IN ({$journal_ids_str})
            ";
            $db->sql_query($SQL);
        }

        return $cpUtil->getJsonText('success', '');

    }

    /**
     *
     */
    function getFields() {
        $fn = Zend_Registry::get('fn');

        $fa = array();
        $fa = $fn->addToFieldsArray($fa, 'entry_date');

        return $fa;
    }

    /**
     *
     */
    function getExportData1($dataArray, $print = false){
        $fn = Zend_Registry::get('fn');

        $tbsExcel = includeCPClass('Lib', 'TBSExcelExportWrapper', 'TBSExcelExportWrapper');

        $output_file_name = 'Journal_' . date('d-m-Y') . '.xlsx';
        $template = __DIR__ . '/assets/journal.xlsx';

        $config = array(
             'output_file_name' => $output_file_name
            ,'dataArray' => $dataArray
            ,'template' => $template
            ,'print' => $print
        );

        return $tbsExcel->exportData($config);
    }

    /**
     *
     */
    function getExportData($dataArray){
        $phpExcel = includeCPClass('Lib', 'PhpExcelExportWrapper', 'PhpExcelExportWrapper');
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');

        $fa = array(
              'entry_date'      => $phpExcel->getFldObj('Date')
             ,'acc_head'        => $phpExcel->getFldObj('Account')
             ,'narration_main'  => $phpExcel->getFldObj('Narration Main')
             ,'narration'       => $phpExcel->getFldObj('Narration for Item')
             ,'debit'           => $phpExcel->getFldObj('Debit')
             ,'credit'          => $phpExcel->getFldObj('Credit')
        );

        $file_name = "Journal_" . date("d-m-Y") . ".xls";
        $config = array(
             'filename'  => $file_name
            ,'fldsArr'   => $fa
            ,'dataArray' => $dataArray
        );

        return $phpExcel->exportData($config);
    }

    /**
     *
     */
    function getLedgerAuthorize() {
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');
        $db = Zend_Registry::get('db');

        $journal_master_id = $fn->getReqParam('journal_master_id');
        $ledger_authorized = $fn->getReqParam('ledger_authorized');

        $fa = array();
        $fa['ledger_authorized'] = $ledger_authorized;

        $whereCondition = "WHERE journal_master_id = {$journal_master_id}";
        $SQL = $dbUtil->getUpdateSQLStringFromArray($fa, 'journal_master', $whereCondition);
        $db->sql_query($SQL);

        return "{}";
    }

    /**
     *
     */
    function getLedgerPending() {
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');
        $db = Zend_Registry::get('db');

        $journal_id = $fn->getReqParam('journal_id');
        $pending = $fn->getReqParam('pending');

        $fa = array();
        $fa['pending'] = $pending;

        $whereCondition = "WHERE journal_id = {$journal_id}";
        $SQL = $dbUtil->getUpdateSQLStringFromArray($fa, 'journal', $whereCondition);
        $db->sql_query($SQL);

        return "{}";
    }

    /**
     *
     */
    function getJournalMasterRow($journal_master_id) {
        $dbz = Zend_Registry::get('dbz');

        $SQL = "
        SELECT jm.*
              ,ah.title AS acc_head
              ,jm.creation_date
              ,jm.modification_date
              ,ah.title AS acc_head_title
              ,(SELECT SUM(j.debit_base)
                FROM journal j
                WHERE j.journal_master_id = jm.journal_master_id
                  AND j.currency_type = 'foreign'
                ) AS debit_base_sum
              ,(SELECT SUM(j.credit_base)
                FROM journal j
                WHERE j.journal_master_id = jm.journal_master_id
                  AND j.currency_type = 'foreign'
               ) AS credit_base_sum
        FROM journal_master jm
        JOIN acc_head ah ON ah.acc_head_id = jm.acc_head_id
        WHERE jm.journal_master_id = ?
        ";
        $stmt = $dbz->query($SQL, $journal_master_id);

        $row = null;
        if ($stmt) {
            $row = $stmt->fetch();
        }
        return $row;
    }

    /**
     *
     */
    function getUpdateAccountHeadOther() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        set_time_limit(50000);

        $SQL = "
        SELECT *
        FROM journal_master
        ORDER BY journal_master_id
        ";
        $result = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);

        while ($row = $db->sql_fetchrow($result)) {
            $SQL2 = "
            SELECT * FROM journal
            WHERE journal_master_id = {$row['journal_master_id']}
            ";
            $result2 = $db->sql_query($SQL2);

            while ($row2 = $db->sql_fetchrow($result2)) {
                $SQL3 = "
                SELECT * FROM journal
                WHERE journal_master_id = {$row['journal_master_id']}
                AND journal_id != {$row2['journal_id']}
                LIMIT 0,1
                ";
                $result3 = $db->sql_query($SQL3);

                while ($row3 = $db->sql_fetchrow($result3)) {
                    $SQL4 = "
                    UPDATE journal
                    SET acc_head_id_other = {$row3['acc_head_id']}
                    WHERE journal_id = {$row2['journal_id']}
                    ";
                    $result4 = $db->sql_query($SQL4);
                }
            }
        }
    }

    /**
     *
     */
    function getImportData(){
        $phpExcel = includeCPClass('Lib', 'PhpExcelImportWrapper');
        $db = Zend_Registry::get('db');

        $SQLUpdate ="
        UPDATE journal set by_import = 0 WHERE by_import = 1
        ";
        $resultUpdate = $db->sql_query($SQLUpdate);

        $SQLUpdate ="
        UPDATE journal_master set by_import = 0 WHERE by_import = 1
        ";
        $resultUpdate = $db->sql_query($SQLUpdate);

        $fa = array(
              'entry_date'   => $phpExcel->getImportFldObj('Date')
             ,'from_account' => $phpExcel->getImportFldObj('From Account')
             ,'to_account'   => $phpExcel->getImportFldObj('To Account')
             ,'narration'    => $phpExcel->getImportFldObj('Narration Main')
             ,'narration_from_acc' => $phpExcel->getImportFldObj('Narration for from account')
             ,'narration_to_acc' => $phpExcel->getImportFldObj('Narration for To account')
             ,'amount'       => $phpExcel->getImportFldObj('Amount')
             ,'credit'       => $phpExcel->getImportFldObj('Credit')
        );
        $fa['from_account']['refOnly'] = true;
        $fa['to_account']['refOnly'] = true;
        $fa['narration_from_acc']['refOnly'] = true;
        $fa['narration_to_acc']['refOnly'] = true;
        $fa['amount']['refOnly'] = true;
        $fa['credit']['refOnly'] = true;

        $fa['staff_id']['defaultValue'] = $_SESSION['staff_id'];
        $fa['by_import']['defaultValue'] = 1;

        /****************************************/
        $config = array(
             'module'              => 'accountsg_journalMaster'
            ,'fldsArr'             => $fa
            ,'callbackAfterInsert' => 'importDataRowCallback'
        );

        return $phpExcel->importData($config);
    }

    /**
     *
     */
    function importDataRowCallback($journal_master_id, $fa) {
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');
        $db = Zend_Registry::get('db');

        $from_account = $fa['from_account'];
        $to_account = $fa['to_account'];
        $narration_from_acc = $fa['narration_from_acc'];
        $narration_to_acc = $fa['narration_to_acc'];
        $amount = $fa['amount'];
        $credit = $fa['credit'];

        if($from_account != ''){
            $sqlacc = "
            SELECT acc_head_id
                   ,title FROM acc_head
                   WHERE title = '{$from_account}'
            ";
            $resultacc = $db->sql_query($sqlacc);
            $accRec    = $db->sql_fetchrow($resultacc);

            $acc_head_id  = $accRec['acc_head_id'];

            $fa2 = array();
            $fa2['journal_master_id'] = $journal_master_id;
            $fa2['acc_head_id']  = $acc_head_id;
            $fa2['narration']  = $narration_from_acc;
            $fa2['by_import']  = 1;
            if($credit == $from_account) {
                $fa2['credit']  = $amount;
            } else {
                $fa2['debit']  = $amount;
            }
            $fa2 = $fn->addCreationDetailsToFieldsArray($fa2, 'journal');

            $SQL = $dbUtil->getInsertSQLStringFromArray($fa2, 'journal');
            $result = $db->sql_query($SQL);
        }

        if($to_account != ''){
            $sqlacc = "
            SELECT acc_head_id
                   ,title FROM acc_head
                   WHERE title = '{$to_account}'
            ";
            $resultacc = $db->sql_query($sqlacc);
            $accRec    = $db->sql_fetchrow($resultacc);

            $acc_head_id  = $accRec['acc_head_id'];
            $fa3 = array();
            $fa3['journal_master_id'] = $journal_master_id;
            $fa3['acc_head_id']  = $acc_head_id;
            $fa3['by_import']  = 1;
            if($credit == $to_account) {
                $fa3['credit']  = $amount;
            } else {
                $fa3['debit']  = $amount;
            }
            $fa3['narration']  = $narration_to_acc;
            $fa3 = $fn->addCreationDetailsToFieldsArray($fa3, 'journal');

            $SQL1 = $dbUtil->getInsertSQLStringFromArray($fa3, 'journal');
            $result1 = $db->sql_query($SQL1);
        }
    }
}
