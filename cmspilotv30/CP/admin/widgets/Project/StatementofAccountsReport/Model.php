<?
class CP_Admin_Widgets_Project_StatementofAccountsReport_Model extends CP_Common_Lib_WidgetModelAbstract
{
    // Date | Title | Amount | Balance
    function getSQL(){
        $SQL = "
        SELECT i.invoice_id
        FROM invoice i
        LEFT JOIN (project p) ON (i.project_id = p.project_id)
        LEFT JOIN (company c) ON (c.company_id = p.company_id)
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
        $dataArray = $modelHelper->getWidgetDataArray($this->controller, 'project_statementofAccountsReport');

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
        
        $start_date = $fn->getReqParam('start_date');
        $end_date   = $fn->getReqParam('end_date');
        $company_id = $fn->getReqParam('company_id');

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

        $outstandingAmt = $this->getPreviousOutstandingBalanceAmount($company_id, $start_date) + 
                          $this->getTotalOutstandingAmount($start_date, $end_date, $company_id);

        $SQL = "
        (
        SELECT i.invoice_amount AS debit_amount
              ,0 AS credit_amount
              ,i.invoice_date AS date
              ,i.invoice_code AS code
        FROM invoice i
        LEFT JOIN (project p) ON (i.project_id = p.project_id)
        LEFT JOIN (company c) ON (c.company_id = p.company_id)
        WHERE i.status != 'Cancelled'
            AND c.company_id = {$company_id}
            AND i.invoice_date {$appendSql}
        ) UNION (
        SELECT 0 AS debit_amount
              ,r.amount AS credit_amount
              ,r.date AS date
              ,r.receipt_code AS code
        FROM receipt r
        LEFT JOIN (invoice_receipt_history irh) ON (r.receipt_id = irh.receipt_id)
        LEFT JOIN (invoice i) ON (irh.invoice_id = i.invoice_id)
        LEFT JOIN (project p) ON (i.project_id   = p.project_id)
        LEFT JOIN (company c) ON (c.company_id   = p.company_id)
        WHERE r.receipt_status != 'Cancelled'
            AND c.company_id = {$company_id}
            AND r.date {$appendSql}
        )
        ORDER BY date ASC
        ";

        $result = $db->sql_query($SQL);
        $serial_no = 0;
        $total_outstanding_amount = $this->getPreviousOutstandingBalanceAmount($company_id, $start_date);

        $colc = 0;
        $rowc++;
    
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Previous Outstanding Amount');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $this->getPreviousOutstandingBalanceAmount($company_id, $start_date));

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

        $rowc++;
        $actSheet->getStyle("A{$rowc}:D{$rowc}")->applyFromArray($headStyle);

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
    }   

    /**
     *
     */
    function getPreviousOutstandingBalanceAmount($company_id, $start_date){
        $db = Zend_Registry::get('db');
        
        $total_invoice_amount = 0;
        
        $sqlInvoice = "
        SELECT invoice_id, invoice_amount
        FROM invoice i
        LEFT JOIN (project p) ON (i.project_id = p.project_id)
        WHERE p.company_id = {$company_id}
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
    function getTotalOutstandingAmountOld($start_date, $end_date, $company_id) {
        $db = Zend_Registry::get('db');

        $year  = date('Y');
        $month = date('m');
        $current_date = date('Y-m-d');
        $total_outstanding_amount = 0;

        if ($start_date != '' && $end_date == '') {
            $appendSql = "And i.invoice_date BETWEEN '{$start_date}' AND '{$current_date}'";
        } else if ($start_date == '' && $end_date != ''){
            $appendSql = "AND i.invoice_date BETWEEN '{$start_date}' AND '{$end_date}'";
        } else if ($start_date != '' && $end_date != '') {
            $appendSql = "AND i.invoice_date BETWEEN '{$start_date}' AND '{$end_date}'";
        } else {
            $start_date = $year . '-' . $month . '-' . '01';
            $end_date   = $year . '-' . $month . '-' . '31';
            $appendSql  = "AND i.invoice_date BETWEEN '{$start_date}' AND '{$end_date}'";
        }

        $sqlInvoice = "
        SELECT i.invoice_id, i.invoice_amount
        FROM invoice i
        LEFT JOIN (project p) ON (i.project_id = p.project_id)
        LEFT JOIN (company c) ON (c.company_id = p.company_id)
        WHERE c.company_id = {$company_id}
        {$appendSql} 
        AND i.status != 'Cancelled'
        ";
        $resultInvoice = $db->sql_query($sqlInvoice);
        while ($rowInvoice = $db->sql_fetchrow($resultInvoice)) {
            $invoice_amount = $rowInvoice['invoice_amount'];
            
            $sqlReceipt = "
            SELECT SUM(r.amount) AS receipt_amount
            FROM receipt r
            LEFT JOIN (invoice i) ON (r.invoice_id = i.invoice_id)
            WHERE i.invoice_id = {$rowInvoice['invoice_id']} 
            AND r.receipt_status != 'Cancelled'
            ";
            $resultReceipt = $db->sql_query($sqlReceipt);
            $rowReceipt    = $db->sql_fetchrow($resultReceipt);
            $receipt_amount = $rowReceipt['receipt_amount'];
            
            $balance_amount = $invoice_amount - $receipt_amount;
            
            $total_outstanding_amount += $balance_amount;
        }
        
        return $total_outstanding_amount;
    }

    /**
     *
     */
    function getTotalOutstandingAmount($start_date, $end_date, $company_id) {
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

        $sqlInvoice = "
        SELECT SUM(i.invoice_amount) AS total_invoice_amount
        FROM invoice i
        LEFT JOIN (project p) ON (i.project_id = p.project_id)
        LEFT JOIN (company c) ON (c.company_id = p.company_id)
        WHERE c.company_id = {$company_id}
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
        LEFT JOIN (project p) ON (i.project_id = p.project_id)
        LEFT JOIN (company c) ON (c.company_id = p.company_id)
        WHERE c.company_id = {$company_id}
          AND r.receipt_status != 'Cancelled'
          AND r.date {$appendSql}
        ";
        $resultReceipt = $db->sql_query($sqlReceipt);
        $rowReceipt    = $db->sql_fetchrow($resultReceipt);
        
        $total_outstanding_amount = $rowInvoice['total_invoice_amount'] - $rowReceipt['total_receipt_amount'];
            
        return $total_outstanding_amount;
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

        $appendSql = '';        
        $start_date = $fn->getReqParam('start_date');
        $end_date   = $fn->getReqParam('end_date');
        $company_id = $fn->getReqParam('company_id');

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

        $outstandingAmt = $this->getPreviousOutstandingBalanceAmount($company_id, $start_date) + 
                          $this->getTotalOutstandingAmount($start_date, $end_date, $company_id);

        $SQL = "
        (
        SELECT i.invoice_amount AS debit_amount
              ,0 AS credit_amount
              ,i.invoice_date AS date
              ,i.invoice_code AS code
              ,0 AS payment_mode
              ,0 AS bank_cheque_no
              ,0 AS bank_cheque_date
        FROM invoice i
        LEFT JOIN (project p) ON (i.project_id = p.project_id)
        LEFT JOIN (company c) ON (c.company_id = p.company_id)
        WHERE i.status != 'Cancelled'
            AND c.company_id = {$company_id}
            AND i.invoice_date {$appendSql}
        ) UNION (
        SELECT 0 AS debit_amount
              ,r.amount AS credit_amount
              ,r.date AS date
              ,r.receipt_code AS code
              ,r.mode_of_payment AS payment_mode
              ,r.cheque_no AS bank_cheque_no
              ,r.cheque_date AS bank_cheque_date
        FROM receipt r
        LEFT JOIN (invoice_receipt_history irh) ON (r.receipt_id = irh.receipt_id)
        LEFT JOIN (invoice i) ON (irh.invoice_id = i.invoice_id)
        LEFT JOIN (project p) ON (i.project_id   = p.project_id)
        LEFT JOIN (company c) ON (c.company_id   = p.company_id)
        WHERE r.receipt_status != 'Cancelled'
            AND c.company_id = {$company_id}
            AND r.date {$appendSql}
        )
        ORDER BY date ASC
        ";
        $result = $db->sql_query($SQL);

        $start_date_formatted = $dateUtil->formatDate($start_date, 'DD-MM-YYYY');

        $current_date = date('Y-m-d');
        if ($end_date > $current_date || $end_date == '') {
            $end_date = $current_date;
        }
        $end_date_formatted   = $dateUtil->formatDate($end_date, 'DD-MM-YYYY');
        $current_date_formatted = $dateUtil->formatDate($current_date, 'DD MMM YYYY');

        $companyRec = $fn->getRecordRowById('company', 'company_id', $company_id);

        $tbl3 ='
        <table>
            <tbody>
                <tr>
                    <td width="80%">To:<br/>
                        ' . $companyRec['company_name'].',<br/>
                        ' . $companyRec['address_flat'].',<br/>
                        ' . $companyRec['address_street'].',<br/>
                        ' . $companyRec['address_country'].' - ' . $companyRec['address_po_code'] .'
                    </td>
                    <td width="20%">Date: '. $current_date_formatted .'</td>
                </tr>
                <tr>
                    <td colspan="2"></td>
                </tr>
                <tr>
                    <td colspan="2">Statement of Accounts report between ' . $start_date_formatted .' and ' . $end_date_formatted .'</td>
                </tr>
                <tr>
                    <td colspan="2"></td>
                </tr>
            </tbody>
        </table>
        <table border="1" width="100%" cellpadding="4">
            <tr bgcolor="#B6E5F9">
                <td width="14%"><strong>Date</strong></td>
                <td width="18%"><strong>Code</strong></td>
                <td width="17%" align="right"><strong>Charges<br>(Invoice)</strong></td>
                <td width="17%" align="right"><strong>Credits<br>(Receipt)</strong></td>
                <td width="16%"><strong>Payment Mode</strong></td>
                <td width="18%" align="right"><strong>Account Balance</strong></td>
            </tr>
            <tr>
                <td colspan="5" width="82%" align="right"><strong>Previous Outstanding Amount</strong></td>
                <td width="18%" align="right"><strong>' . number_format($this->getPreviousOutstandingBalanceAmount($company_id, $start_date),2) . '</strong></td>
            </tr>
        ';

        $count = 1;
        $total_outstanding_amount = $this->getPreviousOutstandingBalanceAmount($company_id, $start_date);
        while ($row = $db->sql_fetchrow($result)) {
            $total_outstanding_amount += $row['debit_amount'] - $row['credit_amount'];

            $date = $fn->getCPDate($row['date'],"d-m-Y");
            $bank_cheque_date = $fn->getCPDate($row['bank_cheque_date'],"d-m-Y");

            if ($row['payment_mode'] == '0') {
                $payment_mode = '';
            } else {
                if ($row['payment_mode'] == 'Cheque') {
                    $payment_mode = $row['payment_mode'] . ' - ' . $row['bank_cheque_no'] . '<br/> (' . $bank_cheque_date . ')';
                } else {
                    $payment_mode = $row['payment_mode'];
                }
            }

            $tbl3 .= '
            <tr>
                <td width="14%">' . $date . '</td>
                <td width="18%">' . $row['code'] . '</td>
                <td width="17%" align="right">' . number_format($row['debit_amount'],2) . '</td>
                <td width="17%" align="right">' . number_format($row['credit_amount'],2) . '</td>
                <td width="16%">'. $payment_mode .'</td>
                <td width="18%" align="right">' . number_format($total_outstanding_amount,2) . '</td>
            </tr>
            ';
            $count++;
        }

        $tbl3 = $tbl3 .'
            <tr bgcolor="#B6E5F9">
                <td colspan="5" width="82%"><strong>Total Outstanding Amount</strong></td>
                <td width="18%" align="right"><strong>' . number_format($outstandingAmt,2) . '</strong></td>
            </tr>
        </table>
        ';

        $pdf->ln(4);
        $pdf->writeHTML($tbl3, true, false, false, false, '');
        $download_title = "Statement-of-Accounts" . date("d-m-Y") .'.pdf';
        $pdf->Output($download_title, 'I');
    }
}