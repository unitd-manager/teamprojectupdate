<?
class CP_Admin_Widgets_Labsg_InvoiceSummary_Model extends CP_Common_Lib_WidgetModelAbstract
{
    /**
     *
     */
    function getSQL(){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $bill_type     = $fn->getReqParam('bill_type');

        if($bill_type == 'Company'){
            /*
            $SQL = "
            SELECT DISTINCT o.company_id
            FROM `invoice` i
            LEFT JOIN `order` o ON (o.order_id = i.order_id)
            ";
            */
            $SQL = "
            SELECT DISTINCT i.invoice_id
                  ,o.company_id
            FROM `invoice` i
            LEFT JOIN `order` o ON (i.order_id = o.order_id)
            ";
        } else {
            $SQL = "
            SELECT DISTINCT o.patient_information_id
            FROM `invoice` i
            LEFT JOIN `order` o ON (o.order_id = i.order_id)
            ";
        }
        return $SQL;
    }

    /**
     *
     */
    function setSearchVar() {
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $searchVar = $this->searchVar;
        $searchVar->mainTableAlias = 'i';

        $bill_type           = $fn->getReqParam('bill_type');
        $company_patient_id  = $fn->getReqParam('company_patient_id');
        $start_date          = $fn->getReqParam('start_date');
        $end_date            = $fn->getReqParam('end_date');

        if ($start_date == '' & $end_date != '') {
            $end_date_year = substr($end_date, 0, 8);
            $start_date = $end_date_year . '01';
        } else if ($start_date != '' & $end_date == '') {
            $start_date_year = substr($start_date, 0, 8);
            $end_date = $start_date_year . '31';
        } else if ($start_date == '' & $end_date == ''){
            $month_year = date('Y') . '-' . date('m') . '-';
            $start_date = $month_year . '01';
            $end_date = $month_year . '31';
        }

        $searchVar->sqlSearchVar[] = "i.invoice_date BETWEEN '{$start_date}' AND '{$end_date}'";

		if ($company_patient_id != '') {
            if($bill_type == 'Company'){
                $searchVar->sqlSearchVar[] = "o.company_id = {$company_patient_id}";
            } else {
                $searchVar->sqlSearchVar[] = "o.patient_information_id = {$company_patient_id}";
            }
		}

        $searchVar->sqlSearchVar[] = "o.bill_type = '{$bill_type}'";
        $searchVar->sortOrder 		= "i.invoice_date ASC";
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
        $dataArray = $modelHelper->getWidgetDataArray($this->controller, 'labsg_invoiceSummary');

        $this->dataArray = $dataArray;
        return $this->dataArray;
    }

    /**
     */
    function getExportToExcelOld(){
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $cpUtil = Zend_Registry::get('cpUtil');
        $dateUtil = Zend_Registry::get('dateUtil');
        $fn = Zend_Registry::get('fn');

        $company_patient_id = $fn->getReqParam('company_patient_id');
        $bill_type          = $fn->getReqParam('bill_type');
        $start_date         = $fn->getReqParam('start_date');
        $end_date           = $fn->getReqParam('end_date');

        $rows = '';

        set_time_limit(50000);
        ini_set('memory_limit', '512M');

        require_once("PHPExcel.php");
        include 'PHPExcel/IOFactory.php';

        $file_name = "InvoiceSummary_" . date("d-m-Y") . ".xls";

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
        $appendSql = '';
        $actSheet = &$objPHPExcel->getActiveSheet();

        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Company Name/ Patient Name');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Date');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Invoice Code');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Invoice Amount');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Paid');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Amount Due');
        if($cpCfg['cp.hasMultiUniqueSites']){
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Location');
         }
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

        $appendSql .= "o.bill_type = '{$bill_type}'";

        if($company_patient_id != '') {
            if($bill_type == 'Company'){
                $appendSql .= "AND o.company_id = {$company_patient_id}";
            } else {
                $appendSql .= "AND o.patient_information_id = {$company_patient_id}";
            }
        }

        if($bill_type == 'Company'){
            $SQL = "
            SELECT DISTINCT o.company_id
                  ,o.company_name AS patient_name
            FROM `invoice` i
            LEFT JOIN `order` o ON (o.order_id = i.order_id)
            WHERE {$appendSql}
              AND i.invoice_date BETWEEN '{$start_date}' AND '{$end_date}'
            ORDER BY i.invoice_date ASC, patient_name ASC
            ";
        } else {
            $SQL = "
            SELECT DISTINCT o.patient_information_id
                  ,CONCAT_WS(' ', o.first_name, o.middle_name, o.last_name ) AS patient_name
            FROM `invoice` i
            LEFT JOIN `order` o ON (o.order_id = i.order_id)
            WHERE {$appendSql}
              AND i.invoice_date BETWEEN '{$start_date}' AND '{$end_date}'
            ORDER BY i.invoice_date ASC, patient_name ASC
            ";
        }
        
        $result = $db->sql_query($SQL);

        $totalInvoiceAmount = 0;
        $totalBalanceAmount = 0;
        $totalPaidAmount = 0;

        while ($row = $db->sql_fetchrow($result)) {

            if($bill_type == 'Company'){
                $SQLInv = "
                SELECT inv.*
                ,(SELECT SUM(invh.amount)
                FROM invoice_receipt_history invh
                LEFT JOIN (receipt rcp) ON (invh.receipt_id = rcp.receipt_id)
                WHERE invh.invoice_id = inv.invoice_id
                  AND rcp.receipt_status = 'Paid'
                ) AS total_amount_paid
                FROM invoice inv
                LEFT JOIN `order` o ON (o.order_id = inv.order_id)
                WHERE o.company_id = {$row['company_id']}
                AND inv.status != 'Cancelled'
                ";
            } else {
                $SQLInv = "
                SELECT inv.*
                ,(SELECT SUM(invh.amount)
                FROM invoice_receipt_history invh
                LEFT JOIN (receipt rcp) ON (invh.receipt_id = rcp.receipt_id)
                WHERE invh.invoice_id = inv.invoice_id
                  AND rcp.receipt_status = 'Paid'
                ) AS total_amount_paid
                FROM invoice inv
                LEFT JOIN `order` o ON (o.order_id = inv.order_id)
                WHERE o.patient_information_id = {$row['patient_information_id']}
                AND inv.status != 'Cancelled'
                ";
            }

            $resultInv = $db->sql_query($SQLInv);
            $invoice_amount  = 0;
            while ($rowInv = $db->sql_fetchrow($resultInv)) {

                $colc = 0;
                $rowc++;

                $invoice_amount = $rowInv['invoice_amount'];
                $balance_amount  = $invoice_amount - $rowInv['total_amount_paid'];
                $totalInvoiceAmount += $invoice_amount;
                $totalBalanceAmount += $balance_amount;
                $totalPaidAmount += $rowInv['total_amount_paid'];
                $invoice_amount = number_format($invoice_amount);
                $balance_amount = number_format($balance_amount);
                $rowInv['total_amount_paid'] = number_format($rowInv['total_amount_paid']);
                $invoiceCode = $rowInv['invoice_code'];

                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['patient_name']);
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $fn->getCPDate($rowInv['invoice_date'], 'd-m-Y'));
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $invoiceCode);
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $invoice_amount);
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $rowInv['total_amount_paid']);
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $balance_amount);

            }
        }

        $colc = 0;
        $rowc++;
        $totalInvoiceAmount = number_format($totalInvoiceAmount,2);
        $totalBalanceAmount = number_format($totalBalanceAmount,2);
        $totalPaidAmount    = number_format($totalPaidAmount,2);

        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, ' ');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Total');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $totalInvoiceAmount);
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $totalPaidAmount);
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $totalBalanceAmount);
        $actSheet->getStyle("A{$rowc}:F{$rowc}")->applyFromArray($headStyle);

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
    }

    /**
     */
    function getExportToExcel(){
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $cpUtil = Zend_Registry::get('cpUtil');
        $dateUtil = Zend_Registry::get('dateUtil');
        $fn = Zend_Registry::get('fn');

        $site_id            = $fn->getSessionParam('cp_site_id');
        $company_patient_id = $fn->getReqParam('company_patient_id');
        $bill_type          = $fn->getReqParam('bill_type');
        $start_date         = $fn->getReqParam('start_date');
        $end_date           = $fn->getReqParam('end_date');

        $rows = '';

        set_time_limit(50000);
        ini_set('memory_limit', '512M');

        require_once("PHPExcel.php");
        include 'PHPExcel/IOFactory.php';

        $file_name = "InvoiceSummary_" . date("d-m-Y") . ".xls";

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
        $appendSql = '';
        $actSheet = &$objPHPExcel->getActiveSheet();

        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Company Name/ Patient Name');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Date');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Invoice Code');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Invoice Amount');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Paid');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Discount');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Amount Due');
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

        if ($start_date == '' & $end_date != '') {
            $end_date_year = substr($end_date, 0, 8);
            $start_date = $end_date_year . '01';
        } else if ($start_date != '' & $end_date == '') {
            $start_date_year = substr($start_date, 0, 8);
            $end_date = $start_date_year . '31';
        } else if ($start_date == '' & $end_date == ''){
            $month_year = date('Y') . '-' . date('m') . '-';
            $start_date = $month_year . '01';
            $end_date = $month_year . '31';
        }

        $appendSql .= "o.bill_type = '{$bill_type}'";

        if($company_patient_id != '') {
            if($bill_type == 'Company'){
                $appendSql .= "AND o.company_id = {$company_patient_id}";
            } else {
                $appendSql .= "AND o.patient_information_id = {$company_patient_id}";
            }
        }

        $appendSqlInv = '';
        if ($cpCfg['cp.hasMultiUniqueSites']) {
            $appendSqlInv = "AND i.site_id = {$site_id}";
        }

        if($bill_type == 'Company'){
            $SQL = "
            SELECT DISTINCT i.invoice_id
                  ,o.company_id
            FROM `invoice` i
            LEFT JOIN `order` o ON (i.order_id = o.order_id)
            WHERE {$appendSql}
              AND i.invoice_date BETWEEN '{$start_date}' AND '{$end_date}'
              {$appendSqlInv}
            ORDER BY i.invoice_date ASC
            ";
        } else {
            $SQL = "
            SELECT DISTINCT o.patient_information_id
            FROM `invoice` i
            LEFT JOIN `order` o ON (o.order_id = i.order_id)
            WHERE {$appendSql}
              AND i.invoice_date BETWEEN '{$start_date}' AND '{$end_date}'
              {$appendSqlInv}
            ORDER BY i.invoice_date ASC
            ";
        }
        
        $result = $db->sql_query($SQL);

        $totalInvoiceAmount  = 0;
        $totalDiscountAmount = 0;
        $totalBalanceAmount  = 0;
        $totalPaidAmount     = 0;

        while ($row = $db->sql_fetchrow($result)) {

            if($bill_type == 'Company'){
                $SQLInv = "
                SELECT inv.*
                ,(SELECT SUM(invh.amount)
                FROM invoice_receipt_history invh
                LEFT JOIN (receipt rcp) ON (invh.receipt_id = rcp.receipt_id)
                WHERE invh.invoice_id = inv.invoice_id
                  AND rcp.receipt_status = 'Paid'
                ) AS total_amount_paid
                ,o.company_name AS patient_name
                FROM invoice inv
                LEFT JOIN `order` o ON (o.order_id = inv.order_id)
                WHERE inv.status != 'Cancelled'
                  AND inv.invoice_id = {$row['invoice_id']}
                ";
            } else {
                $SQLInv = "
                SELECT inv.*
                ,(SELECT SUM(invh.amount)
                FROM invoice_receipt_history invh
                LEFT JOIN (receipt rcp) ON (invh.receipt_id = rcp.receipt_id)
                WHERE invh.invoice_id = inv.invoice_id
                  AND rcp.receipt_status = 'Paid'
                ) AS total_amount_paid
                ,CONCAT_WS(' ', o.first_name, o.middle_name, o.last_name ) AS patient_name
                FROM invoice inv
                LEFT JOIN `order` o ON (o.order_id = inv.order_id)
                WHERE o.patient_information_id = {$row['patient_information_id']}
                AND inv.status != 'Cancelled'
                ";
            }

            $resultInv = $db->sql_query($SQLInv);
            $invoice_amount  = 0;
            while ($rowInv = $db->sql_fetchrow($resultInv)) {

                $colc = 0;
                $rowc++;

                $invoice_amount = $rowInv['invoice_amount'];
                $balance_amount  = $invoice_amount - $rowInv['total_amount_paid'] - $rowInv['discount'];
                $totalInvoiceAmount += $invoice_amount;
                $totalDiscountAmount += $rowInv['discount'];
                $totalBalanceAmount += $balance_amount;
                $totalPaidAmount += $rowInv['total_amount_paid'];
                $invoice_amount = number_format($invoice_amount);
                $balance_amount = number_format($balance_amount);
                $rowInv['total_amount_paid'] = number_format($rowInv['total_amount_paid']);
                $invoiceCode = $rowInv['invoice_code'];

                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $rowInv['patient_name']);
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $fn->getCPDate($rowInv['invoice_date'], 'd-m-Y'));
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $invoiceCode);
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $invoice_amount);
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $rowInv['total_amount_paid']);
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $rowInv['discount']);
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $balance_amount);

            }
        }

        $colc = 0;
        $rowc++;
        $totalInvoiceAmount = number_format($totalInvoiceAmount,2);
        $totalBalanceAmount = number_format($totalBalanceAmount,2);
        $totalPaidAmount    = number_format($totalPaidAmount,2);

        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, ' ');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Total');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $totalInvoiceAmount);
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $totalPaidAmount);
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $totalDiscountAmount);
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $totalBalanceAmount);
        $actSheet->getStyle("A{$rowc}:G{$rowc}")->applyFromArray($headStyle);

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
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

        $site_id            = $fn->getSessionParam('cp_site_id');
        $company_patient_id = $fn->getReqParam('company_patient_id');
        $bill_type          = $fn->getReqParam('bill_type');
        $start_date         = $fn->getReqParam('start_date');
        $end_date           = $fn->getReqParam('end_date');

        if ($start_date == '' & $end_date != '') {
            $end_date_year = substr($end_date, 0, 8);
            $start_date = $end_date_year . '01';
        } else if ($start_date != '' & $end_date == '') {
            $start_date_year = substr($start_date, 0, 8);
            $end_date = $start_date_year . '31';
        } else if ($start_date == '' & $end_date == ''){
            $month_year = date('Y') . '-' . date('m') . '-';
            $start_date = $month_year . '01';
            $end_date = $month_year . '31';
        }

        $appendSql          = '';
        $appendSql .= "o.bill_type = '{$bill_type}'";

        if($company_patient_id != '') {
            if($bill_type == 'Company'){
                $appendSql .= "AND o.company_id = {$company_patient_id}";
            } else {
                $appendSql .= "AND o.patient_information_id = {$company_patient_id}";
            }
        }

        $appendSqlInv = '';
        if ($cpCfg['cp.hasMultiUniqueSites']) {
            $appendSqlInv = "AND i.site_id = {$site_id}";
        }

        if($bill_type == 'Company'){
            $SQL = "
            SELECT DISTINCT i.invoice_id
                  ,o.company_id
            FROM `invoice` i
            LEFT JOIN `order` o ON (i.order_id = o.order_id)
            WHERE {$appendSql}
              AND i.invoice_date BETWEEN '{$start_date}' AND '{$end_date}'
              {$appendSqlInv}
            ORDER BY i.invoice_date ASC
            ";
        } else {
            $SQL = "
            SELECT DISTINCT o.patient_information_id
            FROM `invoice` i
            LEFT JOIN `order` o ON (o.order_id = i.order_id)
            WHERE {$appendSql}
              AND i.invoice_date BETWEEN '{$start_date}' AND '{$end_date}'
              {$appendSqlInv}
            ORDER BY i.invoice_date ASC
            ";
        }        
        $result = $db->sql_query($SQL);

        $totalInvoiceAmount  = 0;
        $totalDiscountAmount = 0;
        $totalBalanceAmount  = 0;
        $totalPaidAmount     = 0;

        $company_patient_name = '';
        if ($bill_type == 'Company' && $company_patient_id != '') {
            $sqlOrder = "
            SELECT o.company_name AS company_patient_name
            FROM `order` o
            WHERE o.bill_type = 'Company'
              AND o.company_id = {$company_patient_id}
            ";
            $resultOrder = $db->sql_query($sqlOrder);
            $rowOrder = $db->sql_fetchrow($resultOrder);
            $company_patient_name = $rowOrder['company_patient_name'];
        } else if ($bill_type == 'Individual' && $company_patient_id != '') {
            $sqlOrder = "
            SELECT o.first_name AS company_patient_name
            FROM `order` o
            WHERE o.bill_type = 'Individual'
              AND o.patient_information_id = {$company_patient_id}
            ";
            $resultOrder = $db->sql_query($sqlOrder);
            $rowOrder = $db->sql_fetchrow($resultOrder);
            $company_patient_name = $rowOrder['company_patient_name'];
        }

        $start_date_formatted = $dateUtil->formatDate($start_date, 'DD-MM-YYYY');
        $end_date_formatted   = $dateUtil->formatDate($end_date, 'DD-MM-YYYY');

        $tbl3 ='
        <table>
            <tbody>
                <tr>
                    <td><h3><u>Invoice Summary for '. $bill_type .' (' . $company_patient_name . ') between ' . $start_date_formatted .' and ' . $end_date_formatted .'</u></h3></td>
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
                    <th width="25%"><strong>Company Name/ Patient Name</strong></th>
                    <th width="14%"><strong>Date</strong></th>
                    <th width="12%"><strong>Invoice Code</strong></th>
                    <th width="11%" align="right"><strong>Invoice Amount</strong></th>
                    <th width="10%" align="right"><strong>Paid</strong></th>
                    <th width="11%" align="right"><strong>Discount</strong></th>
                    <th width="10%" align="right"><strong>Due</strong></th>
                </tr>
            </thead>
            <tbody>
        ';
        $count = 1;
        while ($row = $db->sql_fetchrow($result)) {            
            if($bill_type == 'Company'){
                $SQLInv = "
                SELECT inv.*
                ,(SELECT SUM(invh.amount)
                FROM invoice_receipt_history invh
                LEFT JOIN (receipt rcp) ON (invh.receipt_id = rcp.receipt_id)
                WHERE invh.invoice_id = inv.invoice_id
                  AND rcp.receipt_status = 'Paid'
                ) AS total_amount_paid
                ,o.company_name AS patient_name
                FROM invoice inv
                LEFT JOIN `order` o ON (o.order_id = inv.order_id)
                WHERE inv.status != 'Cancelled'
                  AND inv.invoice_id = {$row['invoice_id']}
                ";
            } else {
                $SQLInv = "
                SELECT inv.*
                ,(SELECT SUM(invh.amount)
                FROM invoice_receipt_history invh
                LEFT JOIN (receipt rcp) ON (invh.receipt_id = rcp.receipt_id)
                WHERE invh.invoice_id = inv.invoice_id
                  AND rcp.receipt_status = 'Paid'
                ) AS total_amount_paid
                ,CONCAT_WS(' ', o.first_name, o.middle_name, o.last_name ) AS patient_name
                FROM invoice inv
                LEFT JOIN `order` o ON (o.order_id = inv.order_id)
                WHERE o.patient_information_id = {$row['patient_information_id']}
                AND inv.status != 'Cancelled'
                ";
            }

            $resultInv = $db->sql_query($SQLInv);
            $invoice_amount  = 0;
            while ($rowInv = $db->sql_fetchrow($resultInv)) {

                $invoice_amount = $rowInv['invoice_amount'];
                $balance_amount  = $invoice_amount - $rowInv['total_amount_paid'] - $rowInv['discount'];
                $totalInvoiceAmount += $invoice_amount;
                $totalDiscountAmount += $rowInv['discount'];
                $totalBalanceAmount += $balance_amount;
                $totalPaidAmount += $rowInv['total_amount_paid'];
                $invoice_amount = number_format($invoice_amount, 2);
                $balance_amount = number_format($balance_amount, 2);
                $discount_amount = number_format($rowInv['discount'], 2);
                $total_amount_paid = number_format($rowInv['total_amount_paid'], 2);
                $invoiceCode = $rowInv['invoice_code'];

                $tbl3 .= '
                <tr>
                    <td width="7%" align="center">' . $count . '</td>
                    <td width="25%">' . $rowInv['patient_name'] . '</td>
                    <td width="14%">' . $fn->getCPDate($rowInv['invoice_date'], 'd-m-Y') . '</td>
                    <td width="12%">' . $invoiceCode . '</td>
                    <td width="11%" align="right">' . $invoice_amount . '</td>
                    <td width="10%" align="right">' . $total_amount_paid . '</td>
                    <td width="11%" align="right">' . $discount_amount . '</td>
                    <td width="10%" align="right">' . $balance_amount . '</td>
                </tr>
                ';
                $count++;
            }
        }

        $tbl3 = $tbl3 .'
        <tr bgcolor="#B6E5F9">
            <td colspan="4"><strong>Total</strong></td>
            <td align="right"><strong>' . number_format($totalInvoiceAmount,2) .'</strong></td>
            <td align="right"><strong>' . number_format($totalPaidAmount,2) .'</strong></td>
            <td align="right"><strong>' . number_format($totalDiscountAmount,2) .'</strong></td>
            <td align="right"><strong>' . number_format($totalBalanceAmount,2) .'</strong></td>
        </tr>
        </tbody>
        </table>';

        $pdf->ln(4);
        $pdf->writeHTML($tbl3, true, false, false, false, '');
        $download_title = "Invoice-Summary" . date("d-m-Y") .'.pdf';
        $pdf->Output($download_title, 'I');
    }
}