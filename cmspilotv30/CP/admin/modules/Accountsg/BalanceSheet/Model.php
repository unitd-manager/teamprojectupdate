<?
class CP_Admin_Modules_Accountsg_BalanceSheet_Model extends CP_Common_Lib_ModuleModelAbstract
{
    function getSQL() {
        $fn = Zend_Registry::get('fn');

        $acc_head_id = $fn->getReqParam('acc_head_id');
        if ($acc_head_id == '') {
            return '';
        }

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
              ,CONCAT_WS(' \r\n', jm.narration, j.narration) AS narration_full
              ,j.currency_id
              ,j.pending
              ,ah.title AS acc_head
              ,jm.creation_date
              ,jm.modification_date
        FROM journal_master jm
        JOIN journal j ON j.journal_master_id = jm.journal_master_id
        JOIN acc_head ah ON ah.acc_head_id = j.acc_head_id
        LEFT JOIN acc_category ac ON ah.acc_category_id = ac.acc_category_id
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

        $acc_head_id = $fn->getReqParam('acc_head_id');
        $entry_date_from = $fn->getReqParam('entry_date_from');
        $entry_date_to = $fn->getReqParam('entry_date_to');

        if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "j.journal_id  = '{$tv['record_id']}'";

        } else {
            if ($acc_head_id != '') {
                $searchVar->sqlSearchVar[] = "j.acc_head_id = {$acc_head_id}";
            }
            if ($entry_date_from != '' && $entry_date_to != '') {
                $searchVar->sqlSearchVar[] = "jm.entry_date BETWEEN '{$entry_date_from}' AND '{$entry_date_to}'";
            } else if ($entry_date_from != '') {
                $searchVar->sqlSearchVar[] = "jm.entry_date >= '{$entry_date_from}'";
            }

            if ($tv['keyword'] != "") {
                $searchVar->sqlSearchVar[] = "(
                       j.narration LIKE '%{$tv['keyword']}%'
                    OR jm.narration LIKE '%{$tv['keyword']}%'
                )";
            }
            $searchVar->sortOrder = "jm.entry_date ASC, j.journal_id ASC";
        }
    }

    function getRunningBalancePrevious($rowAccHead) {
        $fn = Zend_Registry::get('fn');
        $searchVar = Zend_Registry::get('searchVar');
        $pager = Zend_Registry::get('pager');

        $this->setSearchVar();
        $sortOrder = strtolower($searchVar->sortOrder);

        //if date_entry is not sorted ascending or sorted by any other field then
        //do not display the running balance
        if (strpos($sortOrder, 'jm.entry_date asc') === false) {
            return '';
        }
        $acc_head_id = $fn->getReqParam('acc_head_id');
        $brough_forward = $rowAccHead['brought_forward'];

        $running_bal_prev = $brough_forward;
        if ($pager->page > 1) {
            $pgrNumRecordsPerPage = $pager->getNumRecordsPerPage();
            $pgrRecordOffset = $pager->recordOffset;

            $recordOffset = 0;
            $numRecs = ($pager->page - 1) * $pgrNumRecordsPerPage;

            $limitStr = " LIMIT {$recordOffset}, {$numRecs}" ;

            $SQL = "
            SELECT SUM(credit) +
                   CAST(-SUM(debit) AS DECIMAL(20,2)) AS balance
            FROM (SELECT j.credit
                        ,j.debit
                  FROM journal j
                  JOIN journal_master jm ON jm.journal_master_id = j.journal_master_id
                  WHERE j.acc_head_id = {$acc_head_id}
                  ORDER BY {$searchVar->sortOrder}
                  {$limitStr}
            ) s
            ";

            $row = $fn->getRecordBySQL($SQL, MYSQL_ASSOC);
            $running_bal_prev += $row['balance'];
        }


        return $running_bal_prev;
    }

    function addRunningBalanceToDataArray(&$dataArray, $runningBalPrev = 0){
        $fn = Zend_Registry::get('fn');
        $searchVar = Zend_Registry::get('searchVar');
        $pager = Zend_Registry::get('pager');

        $this->setSearchVar();
        $sortOrder = strtolower($searchVar->sortOrder);

        //if date_entry is not sorted ascending or sorted by any other field then
        //do not display the running balance
        if (strpos($sortOrder, 'jm.entry_date asc') === false) {
            $running_bal = $runningBalPrev;
            foreach ($dataArray as $key => $row){
                $dataArray[$key]['running_bal'] = '';
            }
        } else {
            $running_bal = $runningBalPrev;
            foreach ($dataArray as $key => $row){
                $debit  = $row['debit'];
                $credit = $row['credit'];
                $running_bal = $credit - $debit + $running_bal;
                $dataArray[$key]['running_bal'] = $running_bal;
            }
        }

        return $dataArray;
    }

    function getPrintData($dataArray){
        $this->getExportData($dataArray, true);
    }

    function getExportData1($dataArray, $print = false, $exp = array()){
        $fn = Zend_Registry::get('fn');

        $acc_head_id      = $fn->getIssetParam($exp, 'acc_head_id');
        $entry_date_from  = $fn->getIssetParam($exp, 'entry_date_from');
        $entry_date_to    = $fn->getIssetParam($exp, 'entry_date_to');
        $output_file_name = $fn->getIssetParam($exp, 'output_file_name');

        if ($entry_date_from == '') {
            $entry_date_from = $fn->getReqParam('entry_date_from');
            $entry_date_to = $fn->getReqParam('entry_date_to');
        }

        $tbsExcel = includeCPClass('Lib', 'TBSExcelExportWrapper', 'TBSExcelExportWrapper');

        $accHead = getCPModelObj('accountsg_accHead');
        $rowAccHead = $accHead->getAccHeadRow($acc_head_id, $entry_date_from, $entry_date_to);
        $debit_sum  = $rowAccHead['debit_sum'];
        $credit_sum  = $rowAccHead['credit_sum'];
        $debit_base_sum  = $rowAccHead['debit_base_sum'];
        $credit_base_sum  = $rowAccHead['credit_base_sum'];

        $running_bal_prev = $this->getRunningBalancePrevious($rowAccHead);
        $this->addRunningBalanceToDataArray($dataArray, $running_bal_prev);
        $running_bal_prev = $fn->getFormatNumber($running_bal_prev);

        $brough_forward = $rowAccHead['brought_forward'] ? $rowAccHead['brought_forward'] : 0;
        $ledger_balance = $fn->getFormatNumber($rowAccHead['ledger_balance']);
        $available_balance = $fn->getFormatNumber($rowAccHead['available_balance']);

        $dateFormat = 'd-M-Y';
        $brough_forward_text = '';
        $closing_balance_text = $rowAccHead['currency_code'] . ' ' . $ledger_balance . ' on ' . date($dateFormat); //current date
        if ($entry_date_from != '') {
            $brough_forward_text = date($dateFormat, strtotime($entry_date_from) );
            $brough_forward_text = $rowAccHead['currency_code'] . ' ' .  number_format($brough_forward, 2) . ' on ' .
                                    date($dateFormat, strtotime($entry_date_from));
        }
        if ($entry_date_to != '') {
            $closing_balance_text = $rowAccHead['currency_code'] . ' ' . $ledger_balance . ' on ' .
                                    date($dateFormat, strtotime($entry_date_to));
        }

        $globalArray = array(
             'account' => $rowAccHead['account']
            ,'brough_forward_text' => $brough_forward_text
            ,'closing_balance_text' => $closing_balance_text
            ,'currency_code' => $rowAccHead['currency_code']
            ,'brough_forward' => $brough_forward
            ,'ledger_balance' => $ledger_balance
            ,'available_balance' => $available_balance
            ,'running_bal_prev' => $running_bal_prev
            ,'debit_sum' => $debit_sum
            ,'credit_sum' => $credit_sum
        );

        if ($output_file_name == ''){
            $output_file_name = 'Ledger_' . date('d-m-Y') . '.xlsx';
        }
        $template = __DIR__ . '/assets/ledger.xlsx';

        $config = array(
             'output_file_name' => $output_file_name
            ,'dataArray' => $dataArray
            ,'globalArray' => $globalArray
            ,'template' => $template
            ,'print' => false
        );

        return $tbsExcel->exportData($config);
    }

    /**
     *
     */
    function getExportData($dataArray){
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $cpUtil = Zend_Registry::get('cpUtil');
        $dateUtil = Zend_Registry::get('dateUtil');
        $fn = Zend_Registry::get('fn');

        set_time_limit(50000);
        ini_set('memory_limit', '512M');

        require_once("PHPExcel.php");
        include 'PHPExcel/IOFactory.php';

        $file_name = "Ledger_" . date("d-m-Y") . ".xls";

        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Content-Type: application/force-download");
        header("Content-Type: application/octet-stream");
        header("Content-Type: application/download");
        header("Content-Disposition: attachment;filename={$file_name}");
        header("Content-Transfer-Encoding: binary ");

        $objPHPExcel = new PHPExcel();
        //--------------------------------------------------//
        $rowc = 1;
        $colc = 0;
        $actSheet = &$objPHPExcel->getActiveSheet();
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Date');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Narration Main');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Narration for Item');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Debit');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Credit');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Balance');

        /******************** FORMAT HEADER *******************/
        $headStyle = array(
            'font' => array('bold' => true)
        );

        $lastCol    = $actSheet->getHighestColumn();
        $lastColInd = PHPExcel_Cell::columnIndexFromString($lastCol);
        $actSheet->getStyle("A1:{$lastCol}1")->applyFromArray($headStyle);

        for ($i=0; $i < $lastColInd; $i++){
            $colAlphabet = PHPExcel_Cell::stringFromColumnIndex($i);
            $actSheet->getColumnDimension($colAlphabet)->setAutoSize(true);
        }

        $acc_head_id = $fn->getReqParam('acc_head_id');

        $sql = "
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
              ,CONCAT_WS(' \r\n', jm.narration, j.narration) AS narration_full
              ,j.currency_id
              ,j.pending
              ,ah.title AS acc_head
              ,jm.creation_date
              ,jm.modification_date
        FROM journal_master jm
        JOIN journal j ON j.journal_master_id = jm.journal_master_id
        JOIN acc_head ah ON ah.acc_head_id = j.acc_head_id
        LEFT JOIN acc_category ac ON ah.acc_category_id = ac.acc_category_id
        WHERE j.acc_head_id = {$acc_head_id}
        ORDER BY jm.entry_date ASC, j.journal_id ASC
        ";
        $result = $db->sql_query($sql);
        $running_bal_prev = 0;
        $total_debit_amount = 0;
        $total_credit_amount = 0;
        //============================================================================= //
        while ($row = $db->sql_fetchrow($result)) {
            $entry_date = $fn->getCPDate($row['entry_date'], 'd-m-Y');
            $running_bal_prev += $row['credit'] - $row['debit'];
            $total_debit_amount += $row['debit'];
            $total_credit_amount += $row['credit'];

            $colc = 0;
            $rowc++;

            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $entry_date);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['narration_main']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['narration']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['debit']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['credit']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $running_bal_prev);
        }

        $colc = 0;
        $rowc++;
        if ($total_debit_amount > 0) {
            $total_debit_amount = '-' . $total_debit_amount;
        }
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Total');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $total_debit_amount);
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $total_credit_amount);

        $actSheet->getStyle("C{$rowc}:E{$rowc}")->applyFromArray($headStyle);

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
    }

    function getExportDataSpecial(){
        set_time_limit(50000);
        ini_set('memory_limit', '512M');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $wTrialBal = getCPWidgetObj('account_trialBalance');

        $SQL = $wTrialBal->model->getSQL();
        $resultAcc = $db->sql_query($SQL);
        $accArr = $wTrialBal->model->getAccountsArray($resultAcc);

        $entry_date_from = '2011-04-01';
        $entry_date_to = '2012-03-31';

        $counter = 0;
        foreach($accArr AS $accRow){
            $this->getExportDataSpecial2($accRow, $entry_date_from, $entry_date_to);
            $counter++;
        }
    }

    function getExportDataSpecial2($accRow, $entry_date_from, $entry_date_to){
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUtil = Zend_Registry::get('cpUtil');

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
              ,CONCAT_WS('
        ', jm.narration, j.narration) AS narration_full
                      ,j.currency_id
                      ,j.pending
                      ,ah.title AS acc_head
                      ,jm.creation_date
                      ,jm.modification_date
                FROM journal_master jm
                JOIN journal j ON j.journal_master_id = jm.journal_master_id
                JOIN acc_head ah ON ah.acc_head_id = j.acc_head_id
                 WHERE j.acc_head_id = '{$accRow['acc_head_id']}'
         AND jm.entry_date BETWEEN '{$entry_date_from}' AND '{$entry_date_to}'
         ORDER BY jm.entry_date ASC, j.journal_id ASC
        ";

        $result = $db->sql_query($SQL);
        //fb::log($SQL);
        //print "<pre class='sql'>" . $SQL . '</pre>';

        $dataArray = $dbUtil->getResultsetAsArray($result);

        $fileName = $cpUtil->fixFileName($accRow['account']);

        $exp = array(
             'acc_head_id' => $accRow['acc_head_id']
            ,'entry_date_from' => $entry_date_from
            ,'entry_date_to' => $entry_date_to
            ,'output_file_name' => realpath('../media/temp') . '/' . $fileName . '.xlsx'
        );
        $this->getExportData($dataArray, false, $exp);
    }
}

