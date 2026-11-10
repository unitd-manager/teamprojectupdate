<?
class CP_Admin_Widgets_AgileIms_StatementofAccountsReport_Model extends CP_Common_Lib_WidgetModelAbstract
{
    // Date | Title | Amount | Balance
    function getSQL(){
        $SQL = "
        SELECT i.invoice_id
        FROM invoice i
        LEFT JOIN (`order` o) ON (i.order_id = o.order_id)
        LEFT JOIN (company c) ON (c.company_id = o.company_id)
        ";

        return $SQL;
    }

    /**
     *
     */
    function setSearchVar() {
        $fn = Zend_Registry::get('fn');
        $searchVar = $this->searchVar;
        $searchVar->mainTableAlias = 'i';

        $start_date = $fn->getReqParam('start_date');
        $end_date   = $fn->getReqParam('end_date');
        $company_id = $fn->getReqParam('company_id');
        $current_date = date('Y-m-d');
        $year  = date('Y');
        $month = date('m');

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
            $searchVar->sqlSearchVar[] = "c.company_id = {$company_id}";
        }

        $searchVar->sqlSearchVar[] = "i.status != 'Cancelled'";
        $searchVar->sortOrder = 'i.invoice_date ASC';
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
        $dataArray = $modelHelper->getWidgetDataArray($this->controller, 'agileIms_statementofAccountsReport');

        $this->dataArray = $dataArray ;
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

        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Serial No.');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Date');
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

        $start_date         = $fn->getReqParam('start_date');
        $end_date           = $fn->getReqParam('end_date');
        $enrollment_type    = $fn->getReqParam('enrollment_type');
        $company_contact_id = $fn->getReqParam('company_contact_id');

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

        $outstandingAmt = $this->getPreviousOutstandingBalanceAmount($enrollment_type, $company_contact_id, $start_date) +
                          $this->getTotalOutstandingAmount($start_date, $end_date, $enrollment_type, $company_contact_id);

        if ($enrollment_type == 'Individual') {
            $leftJoin = "LEFT JOIN (contact co) ON (co.contact_id = o.contact_id)";
            $sqlWhere = "AND co.contact_id = {$company_contact_id}";
        } else {
            $leftJoin = "LEFT JOIN (company c) ON (c.company_id = o.company_id)";
            $sqlWhere = "AND c.company_id = {$company_contact_id}";
        }

        $SQL = "
        (
        SELECT i.invoice_amount AS debit_amount
              ,0 AS credit_amount
              ,i.invoice_date AS date
              ,i.invoice_code AS code
              ,i.creation_date AS creation_date
        FROM invoice i
        LEFT JOIN (`order` o) ON (i.order_id = o.order_id)
        {$leftJoin}
        WHERE i.status != 'Cancelled'
            {$sqlWhere}
            AND i.invoice_date {$appendSql}
        ) UNION (
        SELECT 0 AS debit_amount
              ,r.amount AS credit_amount
              ,r.date AS date
              ,r.receipt_code AS code
              ,r.creation_date AS creation_date
        FROM receipt r
        LEFT JOIN (invoice_receipt_history irh) ON (r.receipt_id = irh.receipt_id)
        LEFT JOIN (invoice i) ON (irh.invoice_id = i.invoice_id)
        LEFT JOIN (`order` o) ON (i.order_id = o.order_id)
        {$leftJoin}
        WHERE r.receipt_status != 'Cancelled'
            {$sqlWhere}
            AND r.date {$appendSql}
        )
        ORDER BY date ASC, creation_date ASC
        ";

        $result = $db->sql_query($SQL);
        $serial_no = 0;
        $total_outstanding_amount = $this->getPreviousOutstandingBalanceAmount($enrollment_type, $company_contact_id, $start_date);

        $colc = 0;
        $rowc++;

        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Previous Outstanding Amount');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $this->getPreviousOutstandingBalanceAmount($enrollment_type, $company_contact_id, $start_date));

        $actSheet->getStyle("A{$rowc}:F{$rowc}")->applyFromArray($headStyle);

        while ($row = $db->sql_fetchrow($result)) {

            $total_outstanding_amount += $row['debit_amount'] - $row['credit_amount'];

            $serial_no += 1;
			$date = $fn->getCPDate($row['date'],"d-m-Y");
            //$attendance_date = $dateUtil->formatDate($row['record_date'], 'DD-MM-YYYY');

            $colc = 0;
            $rowc++;

            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $serial_no);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $date);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['code']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['debit_amount']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['credit_amount']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $total_outstanding_amount);
        }

        $colc = 0;
        $rowc++;
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Total Outstanding Amount');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $outstandingAmt);

        $actSheet->getStyle("A{$rowc}:F{$rowc}")->applyFromArray($headStyle);

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
    }

    /**
     *
     */
    function getPreviousOutstandingBalanceAmount($enrollment_type, $company_contact_id, $start_date){
        $db = Zend_Registry::get('db');

        $total_invoice_amount = 0;

        if ($enrollment_type == 'Individual') {
            $sqlWhere = "o.contact_id = {$company_contact_id}";
        } else {
            $sqlWhere = "o.company_id = {$company_contact_id}";
        }

        $sqlInvoice = "
        SELECT invoice_id, invoice_amount
        FROM invoice i
        LEFT JOIN (`order` o) ON (i.order_id = o.order_id)
        WHERE {$sqlWhere}
          AND i.invoice_date < '{$start_date}'
          AND i.status != 'Cancelled'
        ";
        $resultInvoice = $db->sql_query($sqlInvoice);
        while ($rowInvoice = $db->sql_fetchrow($resultInvoice)) {

            $total_invoice_amount += $rowInvoice['invoice_amount'];
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
              ,SUM(r.amount) AS receipt_amount
        FROM receipt r
        LEFT JOIN (invoice i) ON (r.invoice_id = i.invoice_id)
        WHERE r.receipt_status != 'Cancelled'
            AND r.invoice_id = {$invoice_id}
        ";
        $result = $db->sql_query($sql);
        $row    = $db->sql_fetchrow($result);

        return $previous_balance = $row['invoice_amount'] - $row['receipt_amount'];
    }

    /**
     *
     */
    function getTotalOutstandingAmount($start_date, $end_date, $enrollment_type, $company_contact_id) {
        $db = Zend_Registry::get('db');

        $year  = date('Y');
        $month = date('m');
        $current_date = date('Y-m-d');
        $total_outstanding_amount = 0;

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

        if ($enrollment_type == 'Individual') {
            $leftJoin = "LEFT JOIN (contact co) ON (co.contact_id = o.contact_id)";
            $sqlWhere = "co.contact_id = {$company_contact_id}";
        } else {
            $leftJoin = "LEFT JOIN (company c) ON (c.company_id = o.company_id)";
            $sqlWhere = "c.company_id = {$company_contact_id}";
        }

        $sqlInvoice = "
        SELECT SUM(i.invoice_amount) AS total_invoice_amount
        FROM invoice i
        LEFT JOIN (`order` o) ON (i.order_id = o.order_id)
        {$leftJoin}
        WHERE {$sqlWhere}
          AND i.status != 'Cancelled'
          AND i.invoice_date {$appendSql}
        ";
        $resultInvoice = $db->sql_query($sqlInvoice);
        $rowInvoice = $db->sql_fetchrow($resultInvoice);

        $sqlReceipt = "
        SELECT SUM(r.amount) AS total_receipt_amount
        FROM receipt r
        LEFT JOIN (invoice_receipt_history irh) ON (r.receipt_id = irh.receipt_id)
        LEFT JOIN (invoice i) ON (irh.invoice_id = i.invoice_id)
        LEFT JOIN (`order` o) ON (i.order_id = o.order_id)
        {$leftJoin}
        WHERE {$sqlWhere}
          AND r.receipt_status != 'Cancelled'
          AND r.date {$appendSql}
        ";
        $resultReceipt = $db->sql_query($sqlReceipt);
        $rowReceipt    = $db->sql_fetchrow($resultReceipt);

        $total_outstanding_amount = $rowInvoice['total_invoice_amount'] - $rowReceipt['total_receipt_amount'];

        return $total_outstanding_amount;
    }
}