<?
class CPL_Admin_Widgets_EnggCrm_StatementofAccountsReport_Model extends CP_Common_Lib_WidgetModelAbstract
{
    // Date | Title | Amount | Balance
    function getSQL(){
        $fn = Zend_Registry::get('fn');
        $client_type = $fn->getReqParam('client_type');

        if($client_type == '' || $client_type == 'Client') {
            $SQL = "
            SELECT i.invoice_id
            FROM `invoice` i
            LEFT JOIN (`order` o) ON (i.order_id = o.order_id)
            ";
        } else if($client_type == 'Supplier') {
            $SQL = "
            SELECT e.expense_id
            FROM `expense` e
            ";
        }

        return $SQL;
    }

    /**
     *
     */
    function setSearchVar() {
        $fn = Zend_Registry::get('fn');
        $searchVar = $this->searchVar;

        $client_type  = $fn->getReqParam('client_type');

        if($client_type == 'Client') {
            $searchVar->mainTableAlias = 'i';
        } else if($client_type == 'Supplier') {
            $searchVar->mainTableAlias = 'e';
        }        
        
        $start_date   = $fn->getReqParam('start_date');
        $end_date     = $fn->getReqParam('end_date');
        $company_id   = $fn->getReqParam('company_id');
        $current_date = date('Y-m-d');
        $year  = date('Y');
        $month = date('m');

        if($client_type == 'Client') {
            if ($start_date != '' && $end_date == '') {
                $searchVar->sqlSearchVar[] = "i.invoice_date BETWEEN '{$start_date}' AND '{$current_date}'";
            } else if ($start_date == '' && $end_date != ''){
                $searchVar->sqlSearchVar[] = "i.invoice_date BETWEEN '{$start_date}' AND '{$end_date}'";
            } else if ($start_date != '' && $end_date != '') {
                $searchVar->sqlSearchVar[] = "i.invoice_date BETWEEN '{$start_date}' AND '{$end_date}'";
            } else {
                $start_date = $year . '-' . $month . '-' . '01';
                $end_date = $year . '-' . $month . '-' . '31';
                $searchVar->sqlSearchVar[] = "i.invoice_date BETWEEN '{$start_date}' AND '{$end_date}'";
            }
      
            if ($company_id != '' ) {
                $searchVar->sqlSearchVar[] = "o.company_id = {$company_id}";
            }   
           
            $searchVar->sqlSearchVar[] = "i.status != 'Cancelled'";
            $searchVar->sortOrder = 'i.invoice_date ASC';
        } else if($client_type == 'Supplier') {
            if ($start_date != '' && $end_date == '') {
                $searchVar->sqlSearchVar[] = "e.date BETWEEN '{$start_date}' AND '{$current_date}'";
            } else if ($start_date == '' && $end_date != ''){
                $searchVar->sqlSearchVar[] = "e.date BETWEEN '{$start_date}' AND '{$end_date}'";
            } else if ($start_date != '' && $end_date != '') {
                $searchVar->sqlSearchVar[] = "e.date BETWEEN '{$start_date}' AND '{$end_date}'";
            } else {
                $start_date = $year . '-' . $month . '-' . '01';
                $end_date = $year . '-' . $month . '-' . '31';
                $searchVar->sqlSearchVar[] = "e.date BETWEEN '{$start_date}' AND '{$end_date}'";
            }
      
            if ($company_id != '' ) {
                $searchVar->sqlSearchVar[] = "e.company_id = {$company_id}";
            }   
           
            $searchVar->sortOrder = 'e.date ASC';
        }
        
        #$searchVar->groupBy = 'i.invoice_id ASC';
    }
    
    /**
     *
     */
    function getDataArray() {
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');

        $modelHelper = Zend_Registry::get('modelHelper');
        $dataArray = $modelHelper->getWidgetDataArray($this->controller, 'project_statementofAccountsReport');

        $this->dataArray = $dataArray;
        return $dataArray;
    }
    
    /**
     *
     */
    function getExportToExcel(){
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

        $file_name = "StatementOfAccounts_" . date("d-m-Y") . ".xls";

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

        $actSheet->getStyle("E{$rowc}:G{$rowc}")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Serial No.');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Date');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Description');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Code');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Charges (Invoice Amount)');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Credits (Receipt Amount)');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Account Balance');
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
        
        $appendSql = '';
        
        $start_date  = $fn->getReqParam('start_date');
        $end_date    = $fn->getReqParam('end_date');
        $company_id  = $fn->getReqParam('company_id');
        $client_type = $fn->getReqParam('client_type');

        $current_date = date('Y-m-d');
        $year  = date('Y');
        $month = date('m');

        if ($start_date != '' && $end_date == '') {
            $appendSql .= "BETWEEN '{$start_date}' AND '{$current_date}'";
        } else if ($start_date == '' && $end_date != ''){
            $start_date = $year . '-' . $month . '-' . '01';
            $appendSql .= "BETWEEN '{$start_date}' AND '{$end_date}'";
        } else if ($start_date != '' && $end_date != '') {
            $appendSql .= "BETWEEN '{$start_date}' AND '{$end_date}'";
        } else {
            $start_date = $year . '-' . $month . '-' . '01';
            $end_date = $year . '-' . $month . '-' . '31';
            $appendSql .= "BETWEEN '{$start_date}' AND '{$end_date}'";
        }

        $outstandingAmt = $this->getPreviousOutstandingBalanceAmount($company_id, $start_date, $client_type) + 
                          $this->getTotalOutstandingAmount($start_date, $end_date, $company_id, $client_type);

        if($client_type == 'Client') {
            $SQL = "
            (
            SELECT i.invoice_amount AS debit_amount
                  ,0 AS credit_amount
                  ,i.invoice_date AS date
                  ,i.invoice_code AS code
                  ,0 AS payment_mode
                  ,0 AS bank_cheque_no
                  ,0 AS bank_cheque_date
                  ,p.title AS project_title
                  ,i.gst_percentage AS GSTPercentage
            FROM invoice i
            LEFT JOIN (`order` o) ON (o.order_id = i.order_id)
            LEFT JOIN (project p) ON (p.project_id = o.project_id)
            WHERE i.status != 'Cancelled'
                AND o.company_id = {$company_id}
                AND i.invoice_date {$appendSql}
            ) UNION (
            SELECT 0 AS debit_amount
                  ,r.amount AS credit_amount
                  ,r.date AS date
                  ,r.receipt_code AS code
                  ,r.mode_of_payment AS payment_mode
                  ,r.cheque_no AS bank_cheque_no
                  ,r.cheque_date AS bank_cheque_date
                  ,p.title AS project_title
                  ,0 AS GSTPercentage
            FROM receipt r
            LEFT JOIN (invoice_receipt_history irh) ON (r.receipt_id = irh.receipt_id)
            LEFT JOIN (invoice i) ON (i.invoice_id = irh.invoice_id)
            LEFT JOIN (`order` o) ON (o.order_id   = i.order_id)
            LEFT JOIN (project p) ON (p.project_id = o.project_id)
            WHERE r.receipt_status != 'Cancelled'
                AND o.company_id = {$company_id}
                AND r.date {$appendSql}
            )
            ORDER BY date ASC
            ";
        } else if($client_type == 'Supplier') {
            $SQL = "
            (
            SELECT (IFNULL(e.amount, 0) + IFNULL(e.gst_amount, 0) + IFNULL(e.service_charge, 0)) AS debit_amount
                  ,0 AS credit_amount
                  ,e.date AS date
                  ,e.po_code AS code
                  ,0 AS payment_mode
                  ,0 AS bank_cheque_no
                  ,0 AS bank_cheque_date
                  ,e.description AS project_title
            FROM expense e
            WHERE e.company_id = {$company_id}
                AND e.date {$appendSql}
            ) UNION (
            SELECT 0 AS debit_amount
                  ,p.amount AS credit_amount
                  ,p.date AS date
                  ,p.payment_code AS code
                  ,p.mode_of_payment AS payment_mode
                  ,p.cheque_no AS bank_cheque_no
                  ,p.cheque_date AS bank_cheque_date
                  ,e.description AS project_title
            FROM payment p
            LEFT JOIN (expense e) ON (e.expense_id = p.record_id)
            WHERE e.company_id = {$company_id}
                AND p.payment_status != 'Cancelled'
                AND p.date {$appendSql}
            )
            ORDER BY date ASC
            ";
        }

        $result = $db->sql_query($SQL);
        $serial_no = 0;
        $total_outstanding_amount = $this->getPreviousOutstandingBalanceAmount($company_id, $start_date, $client_type);

        $colc = 0;
        $rowc++;
        
        $colStr = PHPExcel_Cell::stringFromColumnIndex($colc);
        $actSheet->mergeCells("{$colStr}{$rowc}:F{$rowc}");
        $actSheet->getStyle("A{$rowc}:G{$rowc}")->applyFromArray($headStyle);
        $actSheet->getStyle("A{$rowc}:G{$rowc}")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Previous Outstanding Amount');
        $actSheet->setCellValueByColumnAndRow($colc+5, $rowc, number_format($total_outstanding_amount, 2));

        while ($row = $db->sql_fetchrow($result)) {

            if($client_type == "Client") {
                if ($row['GSTPercentage'] > 0) {
                    $debit_amount = $fn->getAmountFractionFormattedForGst($row['debit_amount'], $cpCfg['cp.gstPercentage']);
                } else {
                    $debit_amount = $row['debit_amount'];
                }
            } else {
                    $debit_amount = $row['debit_amount'];
            }

            $total_outstanding_amount += $debit_amount - $row['credit_amount'];

            $serial_no += 1;
            $date = $fn->getCPDate($row['date'],"d-m-Y");
            //$attendance_date = $dateUtil->formatDate($row['record_date'], 'DD-MM-YYYY');

            $debit_amount  = number_format($debit_amount, 2);
            $credit_amount = number_format($row['credit_amount'], 2);
            $total_outstanding_amount_formatted = number_format($total_outstanding_amount, 2);

            $colc = 0;
            $rowc++;
        
            $actSheet->getStyle("E{$rowc}:G{$rowc}")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $serial_no);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $date);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['project_title']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['code']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $debit_amount);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $credit_amount);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $total_outstanding_amount_formatted);
        }

        $colc = 0;
        $rowc++;
        $colStr = PHPExcel_Cell::stringFromColumnIndex($colc);
        $actSheet->mergeCells("{$colStr}{$rowc}:F{$rowc}");
        $actSheet->getStyle("A{$rowc}:G{$rowc}")->applyFromArray($headStyle);
        $actSheet->getStyle("A{$rowc}:G{$rowc}")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Total Outstanding Amount');
        $actSheet->setCellValueByColumnAndRow($colc+5, $rowc, number_format($outstandingAmt, 2));

        $rowc++;
        $actSheet->getStyle("A{$rowc}:G{$rowc}")->applyFromArray($headStyle);

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
    }   

    /**
     *
     */
    function getPreviousOutstandingBalanceAmount($company_id, $start_date, $client_type){
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');

        $total_invoice_amount = 0;
        if($client_type == 'Client') {
            $sqlInvoice = "
            SELECT i.invoice_id
                  ,i.invoice_amount
                  ,i.gst_percentage
            FROM invoice i
            LEFT JOIN (`order` o) ON (o.order_id = i.order_id)
            WHERE o.company_id = {$company_id}
              AND i.invoice_date < '{$start_date}'
              AND i.status != 'Cancelled'
            ";
            $resultInvoice = $db->sql_query($sqlInvoice);
            while ($rowInvoice = $db->sql_fetchrow($resultInvoice)) {
                if ($rowInvoice['gst_percentage'] > 0) {
                    $debit_amount = $fn->getAmountFractionFormattedForGst($rowInvoice['invoice_amount'], $cpCfg['cp.gstPercentage']);
                } else {
                    $debit_amount = $rowInvoice['invoice_amount'];
                }

                $total_invoice_amount += $debit_amount;

                $sqlPayment = "
                SELECT SUM(r.amount) AS receipt_amount
                FROM receipt r
                LEFT JOIN (invoice_receipt_history irh) ON (r.receipt_id = irh.receipt_id)
                WHERE r.receipt_status = 'Paid'
                  AND irh.invoice_id = {$rowInvoice['invoice_id']}
                  AND r.date < '{$start_date}'
                ";
                $resultPayment = $db->sql_query($sqlPayment);
                $rowPayment = $db->sql_fetchrow($resultPayment);
                
                $total_invoice_amount -= $rowPayment['receipt_amount'];
            }
        } else if($client_type == 'Supplier') {
            $sqlExpense = "
            SELECT e.expense_id
                  ,e.amount
                  ,e.gst_amount
                  ,e.service_charge
            FROM expense e
            WHERE e.company_id = {$company_id}
              AND e.date < '{$start_date}'
              AND (e.payment_status = 'Partial Payment'
              OR e.payment_status = 'Due')
            ";
            $resultExpense = $db->sql_query($sqlExpense);
            while ($rowExpense = $db->sql_fetchrow($resultExpense)) {
                $debit_amount = $rowExpense['amount'] + $rowExpense['gst_amount'] + $rowExpense['service_charge'];
                $total_invoice_amount += $debit_amount;
            }

            $sqlPayment = "
            SELECT SUM(p.amount) AS receipt_amount
            FROM payment p
            LEFT JOIN (expense e) ON (e.expense_id = p.record_id)
            WHERE e.company_id = {$company_id}
              AND p.payment_status = 'Paid'
              AND p.date < '{$start_date}'
            ";
            $resultPayment = $db->sql_query($sqlPayment);
            while ($rowPayment = $db->sql_fetchrow($resultPayment)) {
                $total_invoice_amount -= $rowPayment['receipt_amount'];
            }
        }

        return $total_invoice_amount;
    }

    /**
     *
     */
    function getReceiptBalanceAmount($invoice_id) {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $sql = "
        SELECT i.invoice_amount
              ,i.gst_percentage
              ,SUM(r.amount) AS receipt_amount
        FROM receipt r
        LEFT JOIN (invoice i) ON (r.invoice_id = i.invoice_id)
        WHERE r.receipt_status != 'Cancelled'
            AND r.invoice_id = {$invoice_id}
        ";
        $result = $db->sql_query($sql);
        $row    = $db->sql_fetchrow($result);

        if ($row['gst_percentage'] > 0) {
            $debit_amount = $fn->getAmountFractionFormattedForGst($row['invoice_amount'], $cpCfg['cp.gstPercentage']);
        } else {
            $debit_amount = $row['invoice_amount'];
        }
        
        return $previous_balance = $debit_amount - $row['receipt_amount'];
    }

    /**
     *
     */
    function getTotalOutstandingAmount($start_date, $end_date, $company_id, $client_type) {
        $db = Zend_Registry::get('db');

        $year  = date('Y');
        $month = date('m');
        $current_date = date('Y-m-d');
        $total_outstanding_amount = 0;

        if($client_type == 'Client') {
            if ($start_date != '' && $end_date == '') {
                $appendSql = "BETWEEN '{$start_date}' AND '{$current_date}'";
            } else if ($start_date == '' && $end_date != ''){
                $appendSql = "BETWEEN '{$start_date}' AND '{$end_date}'";
            } else if ($start_date != '' && $end_date != '') {
                $appendSql = "BETWEEN '{$start_date}' AND '{$end_date}'";
            } else {
                $start_date = $year . '-' . $month . '-' . '01';
                $end_date   = $year . '-' . $month . '-' . '31';
                $appendSql  = "BETWEEN '{$start_date}' AND '{$end_date}'";
            }

            $sqlInvoice = "
            SELECT  SUM(CASE
                       WHEN (i.gst_percentage > 0) THEN (i.invoice_amount + ((i.invoice_amount * i.gst_percentage) / 100))
                       ELSE i.invoice_amount
                       END) AS total_invoice_amount
            FROM invoice i
            LEFT JOIN (`order` o) ON (o.order_id = i.order_id)
            WHERE o.company_id = {$company_id}
              AND i.status != 'Cancelled'
              AND i.invoice_date {$appendSql}
            ";
            $resultInvoice = $db->sql_query($sqlInvoice);
            $rowInvoice    = $db->sql_fetchrow($resultInvoice);
            
            $sqlReceipt = "
            SELECT SUM(r.amount) AS total_receipt_amount
            FROM receipt r
            LEFT JOIN (invoice_receipt_history irh) ON (r.receipt_id = irh.receipt_id)
            LEFT JOIN (invoice i) ON (irh.invoice_id = i.invoice_id)
            LEFT JOIN (`order` o) ON (o.order_id = i.order_id)
            WHERE o.company_id = {$company_id}
              AND r.receipt_status != 'Cancelled'
              AND r.date {$appendSql}
            ";
            $resultReceipt = $db->sql_query($sqlReceipt);
            $rowReceipt    = $db->sql_fetchrow($resultReceipt);
            
            $total_outstanding_amount = $rowInvoice['total_invoice_amount'] - $rowReceipt['total_receipt_amount'];
        } else if($client_type == 'Supplier') {
            if ($start_date != '' && $end_date == '') {
                $appendSql = "BETWEEN '{$start_date}' AND '{$current_date}'";
            } else if ($start_date == '' && $end_date != ''){
                $appendSql = "BETWEEN '{$start_date}' AND '{$end_date}'";
            } else if ($start_date != '' && $end_date != '') {
                $appendSql = "BETWEEN '{$start_date}' AND '{$end_date}'";
            } else {
                $start_date = $year . '-' . $month . '-' . '01';
                $end_date   = $year . '-' . $month . '-' . '31';
                $appendSql  = "BETWEEN '{$start_date}' AND '{$end_date}'";
            }

            $sqlInvoice = "
            SELECT SUM(IFNULL(e.amount, 0) + IFNULL(e.gst_amount, 0) + IFNULL(e.service_charge, 0)) AS total_invoice_amount
            FROM expense e
            WHERE e.company_id = {$company_id}
              AND e.date {$appendSql}
            ";
            $resultInvoice = $db->sql_query($sqlInvoice);
            $rowInvoice    = $db->sql_fetchrow($resultInvoice);
            
            $sqlReceipt = "
            SELECT SUM(p.amount) AS total_receipt_amount
            FROM payment p
            LEFT JOIN (expense e) ON (e.expense_id = p.record_id)
            WHERE e.company_id = {$company_id}
              AND p.payment_status != 'Cancelled'
              AND p.date {$appendSql}
            ";
            $resultReceipt = $db->sql_query($sqlReceipt);
            $rowReceipt    = $db->sql_fetchrow($resultReceipt);
            
            $total_outstanding_amount = $rowInvoice['total_invoice_amount'] - $rowReceipt['total_receipt_amount'];
        }
        
        return $total_outstanding_amount;
    }
}