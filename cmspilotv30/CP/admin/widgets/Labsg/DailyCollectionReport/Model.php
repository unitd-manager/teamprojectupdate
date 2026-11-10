<?
class CP_Admin_Widgets_Labsg_DailyCollectionReport_Model extends CP_Common_Lib_WidgetModelAbstract
{
    /**
     *
     */
    function getSQL(){
        /*
        $SQL = "
        SELECT i.*
              ,(SELECT SUM(invHist.amount) AS prev_sum
                FROM invoice_receipt_history invHist
                LEFT JOIN receipt r ON (r.receipt_id = invHist.receipt_id)
                WHERE invHist.invoice_id =  i.invoice_id
                AND r.receipt_status != 'Cancelled'
                AND i.status != 'Cancelled'
                ) AS receipt_amount
              ,CONCAT_WS(' ', o.first_name, o.middle_name, o.last_name ) AS patient_name
              ,o.company_name
              ,o.bill_type
        FROM invoice i
        LEFT JOIN (`order` o) ON (i.order_id = o.order_id)
        ";
        */

        $SQL = "
        SELECT r.*
              ,CONCAT_WS(' ', o.first_name, o.middle_name, o.last_name ) AS patient_name
              ,o.company_name
              ,o.bill_type
        FROM receipt r
        LEFT JOIN (`order` o) ON (r.order_id = o.order_id)
        ";

        return $SQL;
    }

    /**
     *
     */
    function setSearchVar1() {
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $searchVar = $this->searchVar;
        $searchVar->mainTableAlias = 'i';

        $start_date 	= $fn->getReqParam('start_date');
        $end_date   	= $fn->getReqParam('end_date');
        $monthVal       = $fn->getReqParam('month');
        $yearVal        = $fn->getReqParam('year');
        $site_id        = $fn->getReqParam('site_id');
        $employee_id    = $fn->getReqParam('employee_id');

        $current_date 	= date('Y-m-d');
        $month        	= date('m');
        $year		  	= date('Y');

        if($tv['module'] == 'common_dashboard'){
            $start_date = $year . '-' . $month . '-' . '01';
            $end_date = $year . '-' . $month . '-' . '31';
        }

        if ($start_date != '' && $end_date == '') {
  	        $searchVar->sqlSearchVar[] = "i.invoice_date >= '{$start_date}' AND i.invoice_date <= '{$current_date}'";
        } else if ($start_date == '' && $end_date != ''){
            $start_date = $year . '-' . $month . '-' . '01';
  	        $searchVar->sqlSearchVar[] = "i.invoice_date >= '{$start_date}' AND i.invoice_date <= '{$end_date}'";
        } else if ($start_date != '' && $end_date != '') {
  	        $searchVar->sqlSearchVar[] = "i.invoice_date >= '{$start_date}' AND i.invoice_date <= '{$end_date}'";
        } else if ($monthVal == '' && $yearVal == ''){
            $start_date = $year . '-' . $month . '-' . '01';
            $end_date = $year . '-' . $month . '-' . '31';
            $searchVar->sqlSearchVar[] = "i.invoice_date >= '{$start_date}' AND i.invoice_date <= '{$end_date}'";
        }

        if ($monthVal != '') {
            $searchVar->sqlSearchVar[] = "DATE_FORMAT(i.invoice_date, '%m') = '{$monthVal}'" ;
        }

        if ($yearVal != '') {
            $searchVar->sqlSearchVar[] = "DATE_FORMAT(i.invoice_date, '%Y') = '{$yearVal}'" ;
        }

        $searchVar->sqlSearchVar[] = "i.status != 'Cancelled'";

        $searchVar->sortOrder = 'i.invoice_date DESC, i.invoice_id DESC';
    }

    /**
     *
     */
    function setSearchVar() {
        $fn = Zend_Registry::get('fn');
        $searchVar = $this->searchVar;

        $start_date   = $fn->getReqParam('start_date');
        $end_date     = $fn->getReqParam('end_date');
        $year         = $fn->getReqParam('year');
        $month        = $fn->getReqParam('month');
        $payment_mode = $fn->getReqParam('payment_mode');
        
        if ($payment_mode == '') {
            $payment_mode = 'Cash';
        }

        if ($start_date == '' & $end_date != '') {
            $end_date_year = substr($end_date, 0, 8);
            $start_date = $end_date_year . '01';
        } else if ($start_date != '' & $end_date == '') {
            $start_date_year = substr($start_date, 0, 8);
            $end_date = $start_date_year . '31';
        } else if ($start_date == '' & $end_date == ''){
            $month_year = $year . '-' . $month . '-';
            $start_date = $month_year . '01';
            $end_date = $month_year . '31';
        }

        $searchVar = $this->searchVar;
        $searchVar->mainTableAlias = 'r';

        $searchVar->sqlSearchVar[] = "r.receipt_status = 'Paid'";
        $searchVar->sqlSearchVar[] = "r.date BETWEEN '{$start_date}' AND '{$end_date}'";

        if ($payment_mode == 'All') {
        } else {
            $searchVar->sqlSearchVar[] = "r.mode_of_payment = '{$payment_mode}'";
        }

        if ($payment_mode == 'All') {
            $searchVar->sortOrder = 'FIELD(r.mode_of_payment, "Cash", "Nets", "Cheque"), r.receipt_id';
        } else {
            $searchVar->sortOrder = 'r.mode_of_payment, r.receipt_id';
        }
    }

    /**
     *
     * @param <type> $SQL
     * @return <type>
     */
    function getDataArray() {
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');

        $modelHelper = Zend_Registry::get('modelHelper');
        $dataArray = $modelHelper->getWidgetDataArray($this->controller, 'labsg_dailyCollectionReport');

        $this->dataArray = $dataArray;
        return $this->dataArray;
    }

    /**
     */
    function getExportToExcel1(){
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

        $file_name = "DailyCollectionReport_" . date("d-m-Y") . ".xls";

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
        $start_date     = $fn->getReqParam('start_date');
        $end_date       = $fn->getReqParam('end_date');
        $month          = $fn->getReqParam('month');
        $year           = $fn->getReqParam('year');
        $current_date   = date('Y-m-d');
        $month          = date('m');
        $year           = date('Y');
        $monthVal       = $fn->getReqParam('month');
        $yearVal        = $fn->getReqParam('year');
        $actSheet = &$objPHPExcel->getActiveSheet();

        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Date');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Patient Name');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Treatment Type');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Invoice Amount');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Amount Paid');
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


        $monthAppendSql = '';
        $yearAppendSql = '';
        $startDateAppendSql = '';
        if ($start_date != '' && $end_date == '') {
            $startDateAppendSql = "i.invoice_date >= '{$start_date}' AND i.invoice_date <= '{$current_date}'";
        } else if ($start_date == '' && $end_date != ''){
            $start_date = $year . '-' . $month . '-' . '01';
            $startDateAppendSql = "i.invoice_date >= '{$start_date}' AND i.invoice_date <= '{$end_date}'";
        } else if ($start_date != '' && $end_date != '') {
            $startDateAppendSql = "i.invoice_date >= '{$start_date}' AND i.invoice_date <= '{$end_date}'";
        } else if ($monthVal == '' && $yearVal == ''){
            $start_date = $year . '-' . $month . '-' . '01';
            $end_date = $year . '-' . $month . '-' . '31';
            $startDateAppendSql = "i.invoice_date >= '{$start_date}' AND i.invoice_date <= '{$end_date}'";
        }

        if ($monthVal != '') {
            if($startDateAppendSql != ''){
                $monthAppendSql = "AND DATE_FORMAT(i.invoice_date, '%m') = '{$monthVal}'" ;
            }else{
                $monthAppendSql = "DATE_FORMAT(i.invoice_date, '%m') = '{$monthVal}'" ;
            }
        }

        if ($yearVal != '') {
            $yearAppendSql = "AND DATE_FORMAT(i.invoice_date, '%Y') = '{$yearVal}'" ;
        }

        $SQL = "
        SELECT i.*
              ,(SELECT SUM(invHist.amount) AS prev_sum
                FROM invoice_receipt_history invHist
                LEFT JOIN receipt r ON (r.receipt_id = invHist.receipt_id)
                WHERE invHist.invoice_id =  i.invoice_id
                AND r.receipt_status != 'Cancelled'
                AND i.status != 'Cancelled'
                ) AS receipt_amount
              ,CONCAT_WS(' ', o.first_name, o.middle_name, o.last_name ) AS patient_name
              ,o.company_name
        FROM invoice i
        LEFT JOIN (`order` o) ON (i.order_id = o.order_id)
        WHERE
        {$startDateAppendSql}
        {$monthAppendSql}
        {$yearAppendSql}
        AND i.status != 'Cancelled'
        ORDER BY i.invoice_date DESC, i.invoice_id DESC
        ";

        $result = $db->sql_query($SQL);

        $totalAmount = 0;
        $totalBalance = 0;
        $totalInvoiceAmount = 0;
        while ($row = $db->sql_fetchrow($result)) {
            $colc = 0;
            $rowc++;

            $creationDate = $fn->getCPDate($row['invoice_date'],"d-m-Y");
            $amount = number_format($row['receipt_amount'], 2);
            $balance = ($row['invoice_amount'] - $row['discount']) - $row['receipt_amount'];

            $invoice_amountDis = $row['invoice_amount'] - $row['discount'];
            $totalInvoiceAmount += $invoice_amountDis;
            $invoice_amountDis = number_format($invoice_amountDis, 2);

            $totalAmount += $row['receipt_amount'];
            $totalBalance += $balance;

            $balance = number_format($balance, 2);

            $SQLIt = "
            SELECT it.*
            FROM invoice_item it
            WHERE it.invoice_id = '{$row['invoice_id']}'
              AND it.record_type = 'Treatment'
            ";
            $resultIt = $db->sql_query($SQLIt);
            $treatment = '';

            while ($rowIt = $db->sql_fetchrow($resultIt)) {
                $treatment .= $rowIt['item_title'].', ';
            }
            $treatment = rtrim($treatment, ', ');

            if($row['patient_name'] != ''){
                $name = $row['patient_name'];
            }else{
                $name = $row['company_name'];
            }

            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $creationDate);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $name);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $treatment);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $invoice_amountDis);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $amount);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $balance);
        }

        $totalAmount  = number_format($totalAmount, 2);
        $totalBalance = number_format($totalBalance, 2);

        $colc = 0;
        $rowc++;
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Total');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $totalInvoiceAmount);
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $totalAmount);
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $totalBalance);
        $actSheet->getStyle("A{$rowc}:F{$rowc}")->applyFromArray($headStyle);

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
    }

    /**
     *
     */
    function getExportToExcel(){
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        
        $start_date   = $fn->getReqParam('start_date');
        $end_date     = $fn->getReqParam('end_date');
        $year         = $fn->getReqParam('year');
        $month        = $fn->getReqParam('month');
        $payment_mode = $fn->getReqParam('payment_mode');
        $site_id      = $fn->getSessionParam('cp_site_id');

        if ($year == '') {
            $year = date('Y');
        }

        if ($payment_mode == '') {
            $payment_mode = 'Cash';
        }

        if ($start_date == '' & $end_date != '') {
            $end_date_year = substr($end_date, 0, 8);
            $start_date = $end_date_year . '01';
        } else if ($start_date != '' & $end_date == '') {
            $start_date_year = substr($start_date, 0, 8);
            $end_date = $start_date_year . '31';
        } else if ($start_date == '' & $end_date == ''){
            $month_year = $year . '-' . $month . '-';
            $start_date = $month_year . '01';
            $end_date = $month_year . '31';
        }

        set_time_limit(50000);
        ini_set('memory_limit', '512M');

        require_once("PHPExcel.php");
        include 'PHPExcel/IOFactory.php';

        $file_name = "Daily-Collection_" . date("d-m-Y") . ".xls";

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

        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Payment Date');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Invoice Code');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Receipt Code');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Bill To');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Client Name');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Mode of Payment');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Amount');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Remarks');
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
        
        $sqlAppend = '';
        if ($payment_mode == 'All') {
            $sortOrder = "ORDER BY FIELD(r.mode_of_payment, 'Cash', 'Nets', 'Cheque'), r.receipt_id";
        } else {
            $sqlAppend = "AND r.mode_of_payment = '{$payment_mode}'";
            $sortOrder = "ORDER BY r.mode_of_payment, r.receipt_id";
        }

        if ($cpCfg['cp.hasMultiUniqueSites']) {
            $sqlAppend .= "AND r.site_id = {$site_id}";
        }

        $SQL = "
        SELECT r.*
              ,CONCAT_WS(' ', o.first_name, o.middle_name, o.last_name ) AS patient_name
              ,o.company_name
              ,o.bill_type
        FROM receipt r
        LEFT JOIN (`order` o) ON (r.order_id = o.order_id)
        WHERE r.date BETWEEN '{$start_date}' AND '{$end_date}'
          AND r.receipt_status = 'Paid'
        {$sqlAppend}
        {$sortOrder}
        ";
        $result = $db->sql_query($SQL);
        $print_total = '';
        $grand_total = 0;
        $mode_of_payment = '';
        $previous_mode_of_payment = '';
        $total_for_payment_mode = 0;

        while ($row = $db->sql_fetchrow($result)) {

            if ($row['amount'] > 0 || $print_total == 1) {
                $colc = 0;
                $rowc++;
            }
        
            // Printing total amount for each mode of payment. Eg: Cash, Nets, Giro etc
            if ($mode_of_payment == '') {
                $print_total = 0;
                $mode_of_payment = $row['mode_of_payment'];
                $previous_mode_of_payment = $row['mode_of_payment'];
                $total_for_payment_mode = $row['amount'];
            } else if ($mode_of_payment == $row['mode_of_payment']) {
                $print_total = 0;
                $total_for_payment_mode += $row['amount'];
                $mode_of_payment = $row['mode_of_payment'];
                $previous_mode_of_payment = $row['mode_of_payment'];
            } else if ($mode_of_payment != $row['mode_of_payment']) {
                $print_total = 1;
                $mode_of_payment = $row['mode_of_payment'];
                $payment_total = $total_for_payment_mode;
                $total_for_payment_mode = $row['amount'];
            }

            // If Mode of payment changes, printing the total amount for the specific payment mode.
            // Eg: If earlier payment mode was Cash, and for next receipt it is Giro, then printing the total.
            if ($print_total == 1) {
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $previous_mode_of_payment . ' Total');
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $payment_total);
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');

                $previous_mode_of_payment = $row['mode_of_payment'];
                $colc = 0;
                $rowc++;
            } else {
                $print_total = "";
            }
            
            $grand_total += $row['amount'];
            if($row['patient_name'] != ''){
                $name = $row['patient_name'];
            }else{
                $name = $row['company_name'];
            }

            $invoice_code = $this->getInvoiceCodeForReceipt($row['receipt_id']);

            if ($row['amount'] > 0) {
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['date']);
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $invoice_code);
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['receipt_code']);
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['bill_type']);
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $name);
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['mode_of_payment']);
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['amount']);
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['remarks']);
            } else {
                $print_rows = "";
            }

            $print_total;
            $print_rows;
        }

        $colc = 0;
        $rowc++;
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $previous_mode_of_payment . ' Total');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $total_for_payment_mode);
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');

        if ($payment_mode == 'All') {
            $grand_total = number_format($grand_total, 2);

            $colc = 0;
            $rowc++;
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Grand Total');
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $grand_total);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
        }

        $colc = 0;
        $rowc++;

        $colc = 0;
        $rowc++;
        $actSheet->mergeCells("A{$rowc}:H{$rowc}");
        $actSheet->getStyle("A{$rowc}:H{$rowc}")->applyFromArray($headStyle);
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Unbilled visit revenue for Individual');

        $appendSqlIndividual = '';
        if ($cpCfg['cp.hasMultiUniqueSites']) {
            $appendSqlIndividual = "AND pv.site_id = {$site_id}";
        }

        $sql1 = "
        SELECT pv.patient_visit_id
              ,pi.name
        FROM patient_visit pv
        LEFT JOIN (patient_information pi) ON (pv.patient_information_id = pi.patient_information_id)
        WHERE pv.check_up_date BETWEEN '{$start_date}' AND '{$end_date}'
          AND (pv.order_id IS NULL OR pv.order_id = '')
          AND (pi.company_id IS NULL OR pi.company_id = '')
          AND pv.status != 'Cancelled'
          {$appendSqlIndividual}
        ";
        $result1  = $db->sql_query($sql1);
        $total_individual_unbilled = 0;
        while ($row1 = $db->sql_fetchrow($result1)) {
            $sql2 = "
            SELECT SUM(fees) AS total_fees_amount FROM treatment_visit
            WHERE patient_visit_id = {$row1['patient_visit_id']}
            ";
            $result2  = $db->sql_query($sql2);
            $row2 = $db->sql_fetchrow($result2);

            if ($row2['total_fees_amount']) {
                $total_fees_amount = number_format($row2['total_fees_amount'],2);
                $total_individual_unbilled += $row2['total_fees_amount'];

                $colc = 0;
                $rowc++;
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Individual');
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row1['name']);
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $total_fees_amount);
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
            }
        }

        $colc = 0;
        $rowc++;
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Total');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $total_individual_unbilled);
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');

        $colc = 0;
        $rowc++;
        $actSheet->mergeCells("A{$rowc}:H{$rowc}");
        $actSheet->getStyle("A{$rowc}:H{$rowc}")->applyFromArray($headStyle);
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Unbilled visit revenue for Company');

        $sql1 = "
        SELECT DISTINCT c.company_id
              ,c.company_name
        FROM company c
        LEFT JOIN (patient_information pi) ON (c.company_id = pi.company_id)
        LEFT JOIN (patient_visit pv) ON (pi.patient_information_id = pv.patient_information_id)
        WHERE pv.check_up_date BETWEEN '{$start_date}' AND '{$end_date}'
          AND (pv.order_id IS NULL OR pv.order_id = '')
          AND pi.company_id != ''
          AND pv.status != 'Cancelled'
          {$appendSqlIndividual}
        ";
        $result1  = $db->sql_query($sql1);
        $overall_company_unbilled = 0;
        while ($row1 = $db->sql_fetchrow($result1)) {
            $total_company_unbilled = 0;
            $sql2 = "
            SELECT pv.patient_visit_id
            FROM patient_visit pv
            LEFT JOIN (patient_information pi) ON (pv.patient_information_id = pi.patient_information_id)
            LEFT JOIN (company c) ON (pi.company_id = c.company_id)
            WHERE pv.check_up_date BETWEEN '{$start_date}' AND '{$end_date}'
              AND (pv.order_id IS NULL OR pv.order_id = '')
              AND pi.company_id = '{$row1['company_id']}'
              {$appendSqlIndividual}
            ";
            $result2  = $db->sql_query($sql2);        
            while ($row2 = $db->sql_fetchrow($result2)) {
                $sql3 = "
                SELECT SUM(fees) AS total_fees_amount FROM treatment_visit
                WHERE patient_visit_id = {$row2['patient_visit_id']}
                ";
                $result3  = $db->sql_query($sql3);
                $row3 = $db->sql_fetchrow($result3);

                if ($row3['total_fees_amount']) {
                    $total_fees_amount = number_format($row3['total_fees_amount'],2);
                    $total_company_unbilled += $row3['total_fees_amount'];
                }
            }

            if ($total_company_unbilled > 0) {
                $overall_company_unbilled += $total_company_unbilled;
                $total_company_unbilled = number_format($total_company_unbilled, 2);

                $colc = 0;
                $rowc++;
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Company');
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row1['company_name']);
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $total_company_unbilled);
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
            }
        }

        $colc = 0;
        $rowc++;
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Total');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $overall_company_unbilled);
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');

        $rowc++;
        $actSheet->getStyle("A{$rowc}:D{$rowc}")->applyFromArray($headStyle);

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
    }   

    /**
     *
     */
    function getSqlForCount() {
        $db = Zend_Registry::get('db');
        
        $serial_no   = 0;
        $grand_total = 0;

        foreach ($this->dataArray as $row) {
            $serial_no += 1;
            $grand_total += $row['amount'];
        }

        $row = array(
                     'total_count' => $serial_no
                    ,'grand_total' => $grand_total
                    );

        return $row;
    }

    /**
     *
     */
    function getExportToPdf(){
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $dateUtil = Zend_Registry::get('dateUtil');

        ini_set('memory_limit', '512M');
        set_time_limit(50000);

        include_once(CP_LIBRARY_PATH.'lib_php/tcpdf/tcpdf.php');
        include_once(CP_LOCAL_PATH.'lib/headfoot.php');

        $pdf = new MYPDF_Local(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('USS');
        $pdf->SetSubject('Print Link');
        $pdf->SetTitle('Print Link');

        // set default header data
        $pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.' 04', PDF_HEADER_STRING);
        // set header and footer fonts
        $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
        $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

        // set default monospaced font
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

        // set margins
        $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER,10);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
        // set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

        // set image scale factor
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

        // set some language-dependent strings (optional)
        if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
            require_once(dirname(__FILE__).'/lang/eng.php');
            $pdf->setLanguageArray($l);
        }

        /*HEADER PART AND FOOTER PART FUNCTIONS HAS BEEN ADDED IN (headfoot.php) PATH INCLUDE: (admin/lib/headfoot.php)*/
        $pdf->AddPage();

        $start_date   = $fn->getReqParam('start_date');
        $end_date     = $fn->getReqParam('end_date');
        $year         = $fn->getReqParam('year');
        $month        = $fn->getReqParam('month');
        $payment_mode = $fn->getReqParam('payment_mode');
        $site_id      = $fn->getSessionParam('cp_site_id');
        
        if ($year == '') {
            $year = date('Y');
        }

        if ($payment_mode == '') {
            $payment_mode = 'Cash';
        }

        if ($start_date == '' & $end_date != '') {
            $end_date_year = substr($end_date, 0, 8);
            $start_date = $end_date_year . '01';
        } else if ($start_date != '' & $end_date == '') {
            $start_date_year = substr($start_date, 0, 8);
            $end_date = $start_date_year . '31';
        } else if ($start_date == '' & $end_date == ''){
            $month_year = $year . '-' . $month . '-';
            $start_date = $month_year . '01';
            $end_date = $month_year . '31';
        }

        $sqlAppend = '';
        if ($payment_mode == 'All') {
            $sortOrder = "ORDER BY FIELD(r.mode_of_payment, 'Cash', 'Nets', 'Cheque'), r.receipt_id";
        } else {
            $sqlAppend = "AND r.mode_of_payment = '{$payment_mode}'";
            $sortOrder = "ORDER BY r.mode_of_payment, r.receipt_id";
        }

        if ($cpCfg['cp.hasMultiUniqueSites']) {
            $sqlAppend .= "AND r.site_id = {$site_id}";
        }

        $SQL = "
        SELECT r.*
              ,CONCAT_WS(' ', o.first_name, o.middle_name, o.last_name ) AS patient_name
              ,o.company_name
              ,o.bill_type
        FROM receipt r
        LEFT JOIN (`order` o) ON (r.order_id = o.order_id)
        WHERE r.date BETWEEN '{$start_date}' AND '{$end_date}'
          AND r.receipt_status = 'Paid'
        {$sqlAppend}
        {$sortOrder}
        ";
        $result = $db->sql_query($SQL);
        $print_total = '';
        $grand_total = 0;
        $mode_of_payment = '';
        $previous_mode_of_payment = '';
        $total_for_payment_mode = 0;

        $start_date_formatted = $dateUtil->formatDate($start_date, 'DD-MM-YYYY');
        $end_date_formatted   = $dateUtil->formatDate($end_date, 'DD-MM-YYYY');
        $tbl3 ='
        <table>
            <tbody>
                <tr>
                    <td><h3><u>Daily collection report between ' . $start_date_formatted .' and ' . $end_date_formatted .'</u></h3></td>
                </tr>
                <tr>
                    <td></td>
                </tr>
            </tbody>
        </table>
        <table border="1" width="100%" cellpadding="4">
            <thead>
                <tr bgcolor="#B6E5F9">
                    <th width="7%"><strong>S.No</strong></th>
                    <th width="17%"><strong>Payment Date</strong></th>
                    <th width="18%"><strong>Invoice Code</strong></th>
                    <th width="12%"><strong>Bill To</strong></th>
                    <th width="22%"><strong>Client Name</strong></th>
                    <th width="12%"><strong>Mode of Payment</strong></th>
                    <th width="12%" align="right"><strong>Amount</strong></th>
                </tr>
            </thead>
            <tbody>
        ';

        $grand_total = 0;
        $total_payment_mode = 0;
        $total_for_payment_mode = 0;
        $amount = 0;
        $rows = '';
        $count = 1;
        while ($row = $db->sql_fetchrow($result)) {
            // Printing total amount for each mode of payment. Eg: Cash, Nets, Giro etc
            if ($mode_of_payment == '') {
                $print_total = 0;
                $mode_of_payment = $row['mode_of_payment'];
                $previous_mode_of_payment = $row['mode_of_payment'];
                $total_for_payment_mode = $row['amount'];
            } else if ($mode_of_payment == $row['mode_of_payment']) {
                $print_total = 0;
                $total_for_payment_mode += $row['amount'];
                $mode_of_payment = $row['mode_of_payment'];
                $previous_mode_of_payment = $row['mode_of_payment'];
            } else if ($mode_of_payment != $row['mode_of_payment']) {
                $print_total = 1;
                $mode_of_payment = $row['mode_of_payment'];
                $payment_total = $total_for_payment_mode;
                $total_for_payment_mode = $row['amount'];
            }

            // If Mode of payment changes, printing the total amount for the specific payment mode.
            // Eg: If earlier payment mode was Cash, and for next receipt it is Giro, then printing the total.
            if ($print_total == 1) {
                $payment_total = number_format($payment_total, 2);
                $print_total_rows .= '
                <tr bgcolor="#B6E5F9">
                    <td colspan="6"><strong>'. $previous_mode_of_payment .' Total</strong></td>
                    <td align="right"><strong>' . $payment_total . '</strong></td>
                </tr>
                ';
                $previous_mode_of_payment = $row['mode_of_payment'];
            } else {
                $print_total_rows = "";
            }
            
            $grand_total += $row['amount'];
            if($row['patient_name'] != ''){
                $name = $row['patient_name'];
            }else{
                $name = $row['company_name'];
            }

            $tbl3 = $tbl3. $print_total_rows;
            $date = $dateUtil->formatDate($row['date'], 'DD-MM-YYYY');
            $invoice_code = $this->getInvoiceCodeForReceipt($row['receipt_id']);

            if ($row['amount'] > 0) {
                $tbl3 .= '
                <tr>
                    <td width="7%" align="center">' . $count . '</td>
                    <td width="17%">' . $date . '</td>
                    <td width="18%">' . $invoice_code . '</td>
                    <td width="12%">' . $row['bill_type'] . '</td>
                    <td width="22%">' . $name . '</td>
                    <td width="12%">' . $row['mode_of_payment'] . '</td>
                    <td width="12%" align="right">' . $row['amount'] . '</td>
                </tr>
                ';
                $count++;
            }
        }

        $tbl3 = $tbl3 .'
        <tr bgcolor="#B6E5F9">
            <td colspan="6" width="88%"><strong>'. $previous_mode_of_payment .' Total</strong></td>
            <td width="12%" align="right"><strong>' . number_format($total_for_payment_mode,2) . '</strong></td>
        </tr>
        ';

        if ($payment_mode == 'All') {
            $grand_total = number_format($grand_total, 2);
            $tbl3 = $tbl3.'
            <tr bgcolor="#B6E5F9">
                <td width="88%" colspan="6"><strong>Grand Total</strong></td>
                <td width="12%" align="right"><strong>'.$grand_total.'</strong></td>
            </tr>
            ';
        }
        $tbl3 = $tbl3 .'
        <tr bgcolor="#B6E5F9">
            <td colspan="7"><strong>Unbilled visit revenue for Individual</strong></td>
        </tr> '. $this->getUnbilledVisitForIndividualPdf($start_date, $end_date) . '
        <tr bgcolor="#B6E5F9">
            <td colspan="7"><strong>Unbilled visit revenue for Company</strong></td>
        </tr> '. $this->getUnbilledVisitForCompanyPdf($start_date, $end_date) . '
        </tbody>
        </table>
        ';

        $pdf->ln(4);
        $pdf->writeHTML($tbl3, true, false, false, false, '');
        $download_title = "Daily-Collection_" . date("d-m-Y") .'.pdf';
        $pdf->Output($download_title, 'I');
    }

    /**
     *
     */
    function getUnbilledVisitForIndividualPdf($start_date, $end_date) {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');

        $site_id = $fn->getSessionParam('cp_site_id');

        $appendSql = '';
        if ($cpCfg['cp.hasMultiUniqueSites']) {
            $appendSql = "AND pv.site_id = {$site_id}";
        }

        $sql1 = "
        SELECT pv.patient_visit_id
              ,pi.name
        FROM patient_visit pv
        LEFT JOIN (patient_information pi) ON (pv.patient_information_id = pi.patient_information_id)
        WHERE pv.check_up_date BETWEEN '{$start_date}' AND '{$end_date}'
          AND (pv.order_id IS NULL OR pv.order_id = '')
          AND (pi.company_id IS NULL OR pi.company_id = '')
          AND pv.status != 'Cancelled'
          {$appendSql}
        ";
        $result1  = $db->sql_query($sql1);
        $rows = '';
        $total_individual_unbilled = 0;
        while ($row1 = $db->sql_fetchrow($result1)) {
            $sql2 = "
            SELECT SUM(fees) AS total_fees_amount FROM treatment_visit
            WHERE patient_visit_id = {$row1['patient_visit_id']}
            ";
            $result2  = $db->sql_query($sql2);
            $row2 = $db->sql_fetchrow($result2);

            if ($row2['total_fees_amount']) {
                $total_fees_amount = number_format($row2['total_fees_amount'],2);
                $total_individual_unbilled += $row2['total_fees_amount'];

                $rows .= '
                <tr>
                    <td width="42%" colspan="3"></td>
                    <td width="12%">Individual</td>
                    <td width="34%" colspan="2">'. $row1['name'] .'</td>
                    <td width="12%" align="right">'. $total_fees_amount .'</td>
                </tr>
                ';
            }
        }

        $total_individual_unbilled = number_format($total_individual_unbilled, 2);
        $text = $rows . '
        <tr bgcolor="#B6E5F9">
            <td width="88%" colspan="6"><strong>Total</strong></td>
            <td width="12%" align="right"><strong>'.$total_individual_unbilled .'</strong></td>
        </tr>
        ';

        return $text;
    }

    /**
     *
     */
    function getUnbilledVisitForCompanyPdf($start_date, $end_date) {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');

        $site_id = $fn->getSessionParam('cp_site_id');

        $appendSql = '';
        if ($cpCfg['cp.hasMultiUniqueSites']) {
            $appendSql = "AND pv.site_id = {$site_id}";
        }

        /* Finding list of unbilled companies - START */
        $sql1 = "
        SELECT DISTINCT c.company_id
              ,c.company_name
        FROM company c
        LEFT JOIN (patient_information pi) ON (c.company_id = pi.company_id)
        LEFT JOIN (patient_visit pv) ON (pi.patient_information_id = pv.patient_information_id)
        WHERE pv.check_up_date BETWEEN '{$start_date}' AND '{$end_date}'
          AND (pv.order_id IS NULL OR pv.order_id = '')
          AND pi.company_id != ''
          AND pv.status != 'Cancelled'
          {$appendSql}
        ";
        $result1  = $db->sql_query($sql1);
        $rows = '';
        $overall_company_unbilled = 0;
        while ($row1 = $db->sql_fetchrow($result1)) {
            $total_company_unbilled = 0;
            $sql2 = "
            SELECT pv.patient_visit_id
            FROM patient_visit pv
            LEFT JOIN (patient_information pi) ON (pv.patient_information_id = pi.patient_information_id)
            LEFT JOIN (company c) ON (pi.company_id = c.company_id)
            WHERE pv.check_up_date BETWEEN '{$start_date}' AND '{$end_date}'
              AND (pv.order_id IS NULL OR pv.order_id = '')
              AND pi.company_id = '{$row1['company_id']}'
              {$appendSql}
            ";
            $result2  = $db->sql_query($sql2);        
            while ($row2 = $db->sql_fetchrow($result2)) {
                $sql3 = "
                SELECT SUM(fees) AS total_fees_amount FROM treatment_visit
                WHERE patient_visit_id = {$row2['patient_visit_id']}
                ";
                $result3  = $db->sql_query($sql3);
                $row3 = $db->sql_fetchrow($result3);

                if ($row3['total_fees_amount']) {
                    $total_fees_amount = number_format($row3['total_fees_amount'],2);
                    $total_company_unbilled += $row3['total_fees_amount'];
                }
            }

            if ($total_company_unbilled > 0) {
                $overall_company_unbilled += $total_company_unbilled;
                $total_company_unbilled = number_format($total_company_unbilled, 2);
                $rows .= '
                <tr>
                    <td width="42%" colspan="3"></td>
                    <td width="12%">Company</td>
                    <td width="34%" colspan="2">'. $row1['company_name'] .'</td>
                    <td width="12%" align="right">'. $total_company_unbilled .'</td>
                </tr>
                ';
            }
        }

        $overall_company_unbilled = number_format($overall_company_unbilled, 2);
        $text = $rows . '
        <tr bgcolor="#B6E5F9">
            <td width="88%" colspan="6"><strong>Total</strong></td>
            <td width="12%" align="right"><strong>'.$overall_company_unbilled .'</strong></td>
        </tr>
        ';

        return $text;
    }

    /**
     *
     */
    function getInvoiceCodeForReceipt($receipt_id) {
        $db = Zend_Registry::get('db');

        $sql = "
        SELECT i.invoice_code
        FROM invoice i
        LEFT JOIN (invoice_receipt_history irh) ON (i.invoice_id = irh.invoice_id)
        WHERE irh.receipt_id = {$receipt_id}
        ";
        $result  = $db->sql_query($sql);
        $numRows = $db->sql_numrows($result);

        $count = 1;
        $invoice_code = '';
        while ($row = $db->sql_fetchrow($result)) {
            if ($count == $numRows) {
                $invoice_code .= $row['invoice_code'];
            } else {
                $invoice_code .= $row['invoice_code'] . ', ';
            }
            $count++;
        }
        
        return $invoice_code;
    }

    /**
     *
     */
    function getTotalCollectionForPaymentMode($payment_mode, $start_date, $end_date) {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');

        $site_id = $fn->getSessionParam('cp_site_id');

        $appendSql = '';
        if ($cpCfg['cp.hasMultiUniqueSites']) {
            $appendSql = "AND r.site_id = {$site_id}";
        }

        $sql = "
        SELECT SUM(r.amount) AS total_paid_amount
        FROM receipt r
        WHERE r.receipt_status = 'Paid'
          AND r.date BETWEEN '{$start_date}' AND '{$end_date}'
          AND r.mode_of_payment = '{$payment_mode}'
          {$appendSql}
        ";
        $result = $db->sql_query($sql);
        $row = $db->sql_fetchrow($result);
        
        return $row['total_paid_amount'];
    }
}