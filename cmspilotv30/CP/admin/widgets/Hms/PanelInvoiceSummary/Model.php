<?
class CP_Admin_Widgets_Hms_PanelInvoiceSummary_Model extends CP_Common_Lib_WidgetModelAbstract
{
    /**
     *
     */
    function getSQL(){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $bill_type = $fn->getReqParam('bill_type');

        $SQL = "
        SELECT DISTINCT o.patient_information_id
              ,CONCAT_WS(' ', o.first_name, o.middle_name, o.last_name ) AS patient_name
              ,o.nric
              ,o.worker_id
              ,o.check_up_date
              ,o.patient_visit_id
        FROM `invoice` i
        LEFT JOIN `order` o ON (o.order_id = i.order_id)
        ";

        return $SQL;
    }

    /**
     *
     */
    function setSearchVar() {
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $searchVar = $this->searchVar;
        $searchVar->mainTableAlias = 'i';

        $start_date     = $fn->getReqParam('start_date');
        $end_date       = $fn->getReqParam('end_date');
        $current_date   = date('Y-m-d');
        $month          = date('m');
        $year           = date('Y');
        $monthVal       = $fn->getReqParam('month');
        $yearVal        = $fn->getReqParam('year');
        $site_id        = $fn->getReqParam('location_id');
        /*$location_id    = $fn->getReqParam('location_id');
        if ($location_id != '') {
            $searchVar->sqlSearchVar[] = "o.site_id = {$location_id}";
        }*/


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

        $searchVar->sqlSearchVar[] = "o.patient_visit_id != ''";

        $searchVar->sortOrder       = "patient_name ASC";
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
        //$dataArray = $modelHelper->getWidgetDataArray($this->controller, 'tradingsg_detailSummaryByClient');
        $dataArray = $modelHelper->getWidgetDataArray($this->controller, 'hms_invoiceSummary');

        $this->dataArray = $dataArray;
        return $this->dataArray;
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

        $location_id    = $fn->getReqParam('location_id');
        $company_id     = $fn->getReqParam('company_id');

        $rows = '';


        set_time_limit(50000);
        ini_set('memory_limit', '512M');

        require_once("PHPExcel.php");
        include 'PHPExcel/IOFactory.php';

        $file_name = "Detail_Summary_by_Client__" . date("d-m-Y") . ".xls";

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
        $start_date     = $fn->getReqParam('start_date');
        $end_date       = $fn->getReqParam('end_date');
        $current_date   = date('Y-m-d');
        $month          = date('m');
        $year           = date('Y');
        $company_id      = $fn->getReqParam('company_id');
        $actSheet = &$objPHPExcel->getActiveSheet();

        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Date');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Name');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'IC / No.');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Emp No.');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Treatment');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Fees');
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

        $totalInvoiceAmount = 0;
        $totalBalanceAmount = 0 ;
        $paymentTotalofInvoiceAmount = 0 ;
        $totalPaidAmount = 0 ;

        if ($start_date != '' && $end_date == '') {
            $startDateAppendSql = "i.invoice_date >= '{$start_date}' AND i.invoice_date <= '{$current_date}'";
        } else if ($start_date == '' && $end_date != ''){
            $start_date = $year . '-' . $month . '-' . '01';
            $startDateAppendSql = "i.invoice_date >= '{$start_date}' AND i.invoice_date <= '{$end_date}'";
        } else if ($start_date != '' && $end_date != '') {
            $startDateAppendSql = "i.invoice_date >= '{$start_date}' AND i.invoice_date <= '{$end_date}'";
        } else {
            $start_date = $year . '-' . $month . '-' . '01';
            $end_date = $year . '-' . $month . '-' . '31';
            $startDateAppendSql = "i.invoice_date >= '{$start_date}' AND i.invoice_date <= '{$end_date}'";
        }

        $SQL = "
        SELECT DISTINCT o.patient_information_id
              ,CONCAT_WS(' ', o.first_name, o.middle_name, o.last_name ) AS patient_name
              ,o.nric
              ,p.worker_id
              ,pv.check_up_date
              ,pv.patient_visit_id
        FROM `invoice` i
        LEFT JOIN `order` o ON (o.order_id = i.order_id)
        LEFT JOIN `patient_information` p ON (p.patient_information_id = o.patient_information_id)
        LEFT JOIN `patient_visit` pv ON (pv.patient_visit_id = o.patient_visit_id)
        WHERE {$startDateAppendSql}
        ORDER BY patient_name ASC
        ";

        $result1 = $db->sql_query($SQL);

        while ($row = $db->sql_fetchrow($result1)) {

            $SqlCondition = "WHERE o.company_id = {$company_id}";

            if($company_id != ''){
            $SQLInv = "
            SELECT inv.*
            ,o.order_id
            ,(SELECT SUM(invh.amount)
            FROM invoice_receipt_history invh
            LEFT JOIN (receipt rcp) ON (invh.receipt_id = rcp.receipt_id)
            WHERE invh.related_invoice_id = inv.invoice_id
              AND rcp.receipt_status = 'Paid'
            ) AS total_amount_paid
            ,if(
            (SELECT SUM(srh.qty_return*srh.price)
            FROM sales_return_history srh
            WHERE srh.invoice_id = inv.invoice_id
              AND srh.status IS NULL
            ),
            (SELECT SUM(srh.qty_return*srh.price)
            FROM sales_return_history srh
            WHERE srh.invoice_id = inv.invoice_id
              AND srh.status IS NULL
            )
            ,''
            )as sales_return_amount
            FROM invoice inv
            LEFT JOIN `order` o ON (o.order_id = inv.order_id)
            {$SqlCondition}
            AND inv.status != 'Cancelled'
            AND o.patient_visit_id = {$row['patient_visit_id']}
            {$appendSql}
            ";


            $resultInv = $db->sql_query($SQLInv);
            $invoice_amount  = '';

            while ($rowInv = $db->sql_fetchrow($resultInv)) {

                $colc = 0;
                $rowc++;

                $invoice_amount = $rowInv['invoice_amount'];
                $balance_amount  = $invoice_amount - $rowInv['total_amount_paid'];
                $totalInvoiceAmount += $invoice_amount;
                $totalBalanceAmount += $balance_amount;
                $totalPaidAmount += $rowInv['total_amount_paid'];
                $invoice_amount = number_format($invoice_amount, 2);
                $balance_amount = number_format($balance_amount, 2);
                $rowInv['total_amount_paid'] = number_format($rowInv['total_amount_paid'], 2);

                $todaylink = "<a target = '_blank' href = 'index.php?_topRm=finance&module=hms_order&record_id={$rowInv['order_id']}&_action=edit'>";
                $invoiceCode = $rowInv['invoice_code'];

                $SQL = "
                SELECT it.*
                FROM invoice_item it
                WHERE it.invoice_id = '{$rowInv['invoice_id']}'
                  AND it.record_type = 'Treatment'
                ";
                $result = $db->sql_query($SQL);
                $treatment = '';

                while ($rowIt = $db->sql_fetchrow($result)) {
                    $treatment .= $rowIt['item_title'].', ';
                }
                $treatment = rtrim($treatment, ', ');

                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $fn->getCPDate($row['check_up_date'], 'd-m-Y'));
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['patient_name']);
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['nric']);
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['worker_id']);
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $treatment);
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $invoice_amount);
            }
            }
        }

        $colc = 0;
        $rowc++;

        if($cpCfg['cp.hasMultiUniqueSites'] == 1){
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
            if($company_id == ''){
               $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
            }
        }

        $rowc++;
        $totalInvoiceAmount = number_format($totalInvoiceAmount,2);

        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');


        if($cpCfg['cp.hasMultiUniqueSites'] == 1){
        $actSheet->getStyle("A{$rowc}:F{$rowc}")->applyFromArray($headStyle);
        }

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
    }
    /**
     */
    function getExportToPdfMBPJ(){
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $cpUtil = Zend_Registry::get('cpUtil');
        $dateUtil = Zend_Registry::get('dateUtil');
        $fn = Zend_Registry::get('fn');

        ini_set('memory_limit', '512M');

        set_time_limit(50000);

        include_once(CP_LIBRARY_PATH.'lib_php/tcpdf/tcpdf.php');
        include_once(CP_LOCAL_PATH.'lib/headfoot1.php');

        $pdf = new MYPDF_Local('L', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

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
        $startDateAppendSql = '';
        $company_id     = $fn->getReqParam('company_id');
        $start_date     = $fn->getReqParam('start_date');
        $end_date       = $fn->getReqParam('end_date');
        $current_date   = date('Y-m-d');
        $month          = date('m');
        $year           = date('Y');
        $monthVal       = $fn->getReqParam('monthVal');
        $yearVal        = $fn->getReqParam('yearVal');
        $site_id        = $fn->getReqParam('site_id');

        if ($start_date != '' && $end_date == '') {
            $startDateAppendSql = "AND i.invoice_date >= '{$start_date}' AND i.invoice_date <= '{$current_date}'";
        } else if ($start_date == '' && $end_date != ''){
            $start_date = $year . '-' . $month . '-' . '01';
            $startDateAppendSql = "AND i.invoice_date >= '{$start_date}' AND i.invoice_date <= '{$end_date}'";
        } else if ($start_date != '' && $end_date != '') {
            $startDateAppendSql = "AND i.invoice_date >= '{$start_date}' AND i.invoice_date <= '{$end_date}'";
        } else if ($monthVal == '' && $yearVal == ''){
            $start_date = $year . '-' . $month . '-' . '01';
            $end_date = $year . '-' . $month . '-' . '31';
            $startDateAppendSql = "AND i.invoice_date >= '{$start_date}' AND i.invoice_date <= '{$end_date}'";
        }

        if ($monthVal != '') {
            $startDateAppendSql .= "AND DATE_FORMAT(i.invoice_date, '%m') = '{$monthVal}'" ;
        }
        if ($yearVal != '') {
            $startDateAppendSql .= "AND DATE_FORMAT(i.invoice_date, '%Y') = '{$yearVal}'" ;
        }

        if($site_id != ''){
            $appendSql = "AND inv.site_id = {$site_id}";
        }

        $SQL = "
        SELECT DISTINCT o.patient_information_id
              ,CONCAT_WS(' ', o.first_name, o.middle_name, o.last_name ) AS patient_name
              ,o.nric
              ,o.worker_id
              ,o.check_up_date
              ,o.patient_visit_id
              ,o.no_of_days
              ,i.invoice_date
              ,i.invoice_code
              ,i.invoice_amount
              ,o.serial_no_of_book
              ,o.department
        FROM `invoice` i
        LEFT JOIN `order` o ON (o.order_id = i.order_id)
        WHERE o.patient_visit_id != ''
        {$startDateAppendSql}
        ORDER BY patient_name ASC
        ";
        $result = $db->sql_query($SQL);
        $result2 = $db->sql_query($SQL);
        $Row = $db->sql_fetchrow($result2);

        $today = date("d-m-Y");
        $totalInvoiceAmount = 0;
        $invoice_date = $fn->getCPDate($Row['invoice_date'], 'd F Y');

        $tbl1 = '
        <table border="0" width="100%">
            <tr>
                <td width="65%" style="font-size:12px; font-weight:bold;">Majlis Bandaraya Petalang Jaya</td>
                <td width="21%" align="right" style="font-size:12px; font-weight:bold;"><b>Karikh&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: </b></td>
                <td width="14%" align="center" style="font-size:12px; font-weight:bold;">'.$invoice_date.'</td>
            </tr>
            <tr>
                <td width="65%" style="font-size:12px; font-weight:bold;">Klinik Kesihatan MBPJ</td>
                <td width="21%" align="right" style="font-size:12px; font-weight:bold;"><b>No.Inbois: </b></td>
                <td width="14%" align="center" style="font-size:12px; font-weight:bold;"></td>
            </tr>
        </table>
        ';

        $check_up_date = $fn->getCPDate($Row['check_up_date'], 'F Y');

        $tbl2 = '
        <table border="0" width="100%">
            <tr>
                <td width="27%" style="font-size:12px; font-weight:bold;">TUNTUTAN BAYARAN RAWATAN BULAN :</td>
                <td width="14%" style="font-size:12px; font-weight:bold;">'.$check_up_date.'</td>
            </tr>

        </table>
        ';

        $tbl3 ='<table border="1" cellpadding="2" width="100%">
                    <thead>
                        <tr>
                            <th width="3%" align="center" style="font-size:12px; font-weight:bold;">Bill</th>
                            <th width="7%" align="center" style="font-size:12px; font-weight:bold;">Tarikh</th>
                            <th width="20%" align="center" style="font-size:12px; font-weight:bold;">Nama Pesakit</th>
                            <th width="15%" align="center" style="font-size:12px; font-weight:bold;">No.k/p</th>
                            <th width="6%" align="center" style="font-size:12px; font-weight:bold;">No.Siri</th>
                            <th width="11%" align="center" style="font-size:12px; font-weight:bold;">Jabatan</th>
                            <th width="5%" align="center" style="font-size:12px; font-weight:bold;">Cuti Sakit</th>
                            <th width="14%" align="center" style="font-size:12px; font-weight:bold;">Jenis Rawatan</th>
                            <th width="10%" align="center" style="font-size:12px; font-weight:bold;">Jumlah Rawatan</th>
                            <th width="10%" align="center" style="font-size:12px; font-weight:bold;">Tandatangan Pesakit</th>
                        </tr>
                    </thead>';

        $count = 1;

        while ($row = $db->sql_fetchrow($result)) {
            $SqlCondition = "WHERE o.company_id = {$company_id}";

            if($company_id != ''){
            $SQLInv = "
            SELECT inv.*
            ,o.order_id
            ,(SELECT SUM(invh.amount)
            FROM invoice_receipt_history invh
            LEFT JOIN (receipt rcp) ON (invh.receipt_id = rcp.receipt_id)
            WHERE invh.related_invoice_id = inv.invoice_id
              AND rcp.receipt_status = 'Paid'
            ) AS total_amount_paid
            ,if(
            (SELECT SUM(srh.qty_return*srh.price)
            FROM sales_return_history srh
            WHERE srh.invoice_id = inv.invoice_id
              AND srh.status IS NULL
            ),
            (SELECT SUM(srh.qty_return*srh.price)
            FROM sales_return_history srh
            WHERE srh.invoice_id = inv.invoice_id
              AND srh.status IS NULL
            )
            ,''
            )as sales_return_amount
            FROM invoice inv
            LEFT JOIN `order` o ON (o.order_id = inv.order_id)
            {$SqlCondition}
            AND inv.status != 'Cancelled'
            AND o.patient_visit_id = {$row['patient_visit_id']}
            {$appendSql}
            ";


            $resultInv = $db->sql_query($SQLInv);
            $invoice_amount  = '';

            while ($rowInv = $db->sql_fetchrow($resultInv)) {

                $colc = 0;

                $invoice_amount = $rowInv['invoice_amount'];
                $balance_amount  = $invoice_amount - $rowInv['total_amount_paid'];
                $totalInvoiceAmount += $invoice_amount;
                $invoice_amount = number_format($invoice_amount, 2);

               // $todaylink = "<a target = '_blank' href = 'index.php?_topRm=finance&module=hms_order&record_id={$rowInv['order_id']}&_action=edit'>";
                $invoiceCode = $rowInv['invoice_code'];

                $SQLIt = "
                SELECT it.*
                FROM invoice_item it
                WHERE it.invoice_id = '{$rowInv['invoice_id']}'
                  AND it.record_type = 'Treatment'
                ";
                $resultIt = $db->sql_query($SQLIt);
                $treatment = '';

                while ($rowIt = $db->sql_fetchrow($resultIt)) {
                    $treatment .= $rowIt['item_title'].', ';
                }
                
                $treatment = rtrim($treatment, ', ');

                $date = $fn->getCPDate($row['check_up_date'], 'd-m-Y');


            $tbl3 = $tbl3.'<tr>
                                <td width="3%" align="center" style="font-size:12px;">'.$count.'</td>
                                <td width="7%" style="font-size:12px;">'.$date.'</td>
                                <td width="20%" align="center" style="font-size:12px;">'.$row['patient_name'].'</td>
                                <td width="15%" align="center" style="font-size:12px;">'.$row['nric'].'</td>
                                <td width="6%" align="center" style="font-size:12px;">'.$row['serial_no_of_book'].'</td>
                                <td width="11%" align="center" style="font-size:12px;">'.$row['department'].'</td>
                                <td width="5%" align="center" style="font-size:12px;">'.$row['no_of_days'].'</td>
                                <td width="14%" align="center" style="font-size:12px;">'.$treatment.'</td>
                                <td width="10%" align="right" style="font-size:12px;">'.$invoice_amount.'</td>
                                <td width="10%" align="center" style="font-size:12px;"></td>
                            </tr>';

            $count++;
            }
            }
        }
        $totalInvoiceAmount = number_format($totalInvoiceAmount,2);

        $tbl3 = $tbl3.'<tr>
                    <th colspan="8" align="right" style="font-weight:bold;">TOTAL</th>
                    <th align="right">'.$totalInvoiceAmount.'</th>
                    </tr></table>';

        $tbl4 = '
        <table border="0" width="100%">
            <tr>
                <td width="50%">Nama Bank Akaun :</td>
                <td width="50%">Tandatangan Doktor :</td>
            </tr>
            <tr>
                <td width="50%">Nombor Akaun :</td>
                <td width="50%">Cop Klinik :</td>
            </tr>
            <tr>
                <td width="50%">Tandatangan :</td>
            </tr>
        </table>';



        $pdf->writeHTML($tbl1, true, false, false, false, '');
        $pdf->writeHTML($tbl2, true, false, false, false, '');
        $pdf->writeHTML($tbl3, true, false, false, false, '');
        $pdf->writeHTML($tbl4, true, false, false, false, '');
        //$pdf->writeHTML($tbl6, true, false, false, false, '');
        $pdf->Output('Delivery-Order.pdf', 'I');

    }
    /**
     */
    function getExportToPdfSyabas(){
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $cpUtil = Zend_Registry::get('cpUtil');
        $dateUtil = Zend_Registry::get('dateUtil');
        $fn = Zend_Registry::get('fn');

        ini_set('memory_limit', '512M');

        set_time_limit(50000);

        include_once(CP_LIBRARY_PATH.'lib_php/tcpdf/tcpdf.php');
        include_once(CP_LOCAL_PATH.'lib/headfoot1.php');

        $pdf = new MYPDF_Local('L', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

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
        $startDateAppendSql = '';
        $company_id     = $fn->getReqParam('company_id');
        $start_date     = $fn->getReqParam('start_date');
        $end_date       = $fn->getReqParam('end_date');
        $current_date   = date('Y-m-d');
        $month          = date('m');
        $year           = date('Y');
        $monthVal       = $fn->getReqParam('monthVal');
        $yearVal        = $fn->getReqParam('yearVal');
        $site_id        = $fn->getReqParam('site_id');

        if ($start_date != '' && $end_date == '') {
            $startDateAppendSql = "AND i.invoice_date >= '{$start_date}' AND i.invoice_date <= '{$current_date}'";
        } else if ($start_date == '' && $end_date != ''){
            $start_date = $year . '-' . $month . '-' . '01';
            $startDateAppendSql = "AND i.invoice_date >= '{$start_date}' AND i.invoice_date <= '{$end_date}'";
        } else if ($start_date != '' && $end_date != '') {
            $startDateAppendSql = "AND i.invoice_date >= '{$start_date}' AND i.invoice_date <= '{$end_date}'";
        } else if ($monthVal == '' && $yearVal == ''){
            $start_date = $year . '-' . $month . '-' . '01';
            $end_date = $year . '-' . $month . '-' . '31';
            $startDateAppendSql = "AND i.invoice_date >= '{$start_date}' AND i.invoice_date <= '{$end_date}'";
        }

        if ($monthVal != '') {
            $startDateAppendSql .= "AND DATE_FORMAT(i.invoice_date, '%m') = '{$monthVal}'" ;
        }
        if ($yearVal != '') {
            $startDateAppendSql .= "AND DATE_FORMAT(i.invoice_date, '%Y') = '{$yearVal}'" ;
        }

        if($site_id != ''){
            $appendSql = "AND inv.site_id = {$site_id}";
        }

        $SQL = "
        SELECT DISTINCT o.patient_information_id
              ,CONCAT_WS(' ', o.first_name, o.middle_name, o.last_name ) AS patient_name
              ,o.nric
              ,o.primary_contact
              ,o.relationship
              ,o.worker_id
              ,o.check_up_date
              ,o.patient_visit_id
              ,i.invoice_date
              ,i.invoice_code
              ,i.invoice_amount
              ,CONCAT_WS(' ', e.first_name, e.middle_name, e.last_name ) AS employee_name
        FROM `invoice` i
        LEFT JOIN `order` o ON (o.order_id = i.order_id)
        LEFT JOIN `employee_visit` ev ON (ev.patient_visit_id = o.patient_visit_id)
        LEFT JOIN `employee` e ON (e.employee_id = ev.employee_id)
        WHERE o.patient_visit_id != ''
        {$startDateAppendSql}
        ORDER BY patient_name ASC
        ";
        $result = $db->sql_query($SQL);
        $result2 = $db->sql_query($SQL);
        $Row = $db->sql_fetchrow($result2);
        $company = $db->sql_fetchrow($result2);


        $today = date("d-m-Y");
        $totalInvoiceAmount = 0;
        $invoice_date=$fn->getCPDate($Row['invoice_date'], 'd F Y');
        $check_up_date=$fn->getCPDate($Row['check_up_date'], 'F Y');

        $tbl1 = '
        <table border="0" width="100%">
            <tr>
                <td width="18%" style="font-size:12px; font-weight:bold;">BIL KEPADA &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; :</td>
                <td width="45%" style="font-size:12px; font-weight:bold;">SYARIKAT BEKALAN AIR SELANGOR SDN BHD</td>
                <td width="19%" align="center" style="font-size:12px; font-weight:bold;"><b>&nbsp;&nbsp;&nbsp;&nbsp;EMAIL &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; :  </b></td>
                <td width="18%" align="center" style="font-size:12px; font-weight:bold;">info.kppdentcare@gmail.com</td>
            </tr>
            <tr>
                <td width="18%" style="font-size:12px; font-weight:bold;">BIL RAWATAN BULAN :</td>
                <td width="45%" style="font-size:12px; font-weight:bold;">'.$check_up_date.'</td>
                <td width="19%" align="center" style="font-size:12px; font-weight:bold;"><b>&nbsp;&nbsp;&nbsp;&nbsp;No.INVOIS : </b></td>
                <td width="18%" align="left" style="font-size:12px; font-weight:bold;"></td>
            </tr>
            <tr>
                <td width="18%" style="font-size:12px; font-weight:bold;"></td>
                <td width="45%" style="font-size:12px; font-weight:bold;"></td>
                <td width="19%" align="center" style="font-size:12px; font-weight:bold;"><b>&nbsp;&nbsp;&nbsp;&nbsp;TARIKH &nbsp;&nbsp; &nbsp;&nbsp;: </b></td>
                <td width="18%" align="left" style="font-size:12px; font-weight:bold;">'.$invoice_date.'</td>
            </tr>
        </table>
        ';

        $check_up_date=$fn->getCPDate($Row['check_up_date'], 'F Y');

        /*NAMA KAKITANGAN = Main patient name
          NAMA PESAKIT = Related patient name (primary contact)
          HUBUNGAN = Relationship*/

        $tbl2 ='<table border="1" cellpadding="2" width="100%">
                    <thead>
                        <tr>
                            <th width="3%" align="center" style="font-size:12px; font-weight:bold;">Bill</th>
                            <th width="9%" align="center" style="font-size:12px; font-weight:bold;">No.KAKITANGAN</th>
                            <th width="7%" align="center" style="font-size:12px; font-weight:bold;">TARIKH LAWATAN</th>
                            <th width="18%" align="center" style="font-size:12px; font-weight:bold;">NAMA KAKITANGAN</th>
                            <th width="9%" align="center" style="font-size:12px; font-weight:bold;">No.k/p KAKITANGAN</th>
                            <th width="18%" align="center" style="font-size:12px; font-weight:bold;">NAMA PESAKIT</th>
                            <th width="14%" align="center" style="font-size:12px; font-weight:bold;">HUBUNGAN</th>
                            <th width="13%" align="center" style="font-size:12px; font-weight:bold;">JENIS RAWATAN</th>
                            <th width="9%" align="center" style="font-size:12px; font-weight:bold;">JUMLAH</th>
                        </tr>
                    </thead>';

        $count = 1;

        $result1 = $db->sql_query($SQL);

        while ($row = $db->sql_fetchrow($result1)) {

            $SqlCondition = "WHERE o.company_id = {$company_id}";

            if($company_id != ''){
            $SQLInv = "
            SELECT inv.*
            ,o.order_id
            ,(SELECT SUM(invh.amount)
            FROM invoice_receipt_history invh
            LEFT JOIN (receipt rcp) ON (invh.receipt_id = rcp.receipt_id)
            WHERE invh.related_invoice_id = inv.invoice_id
              AND rcp.receipt_status = 'Paid'
            ) AS total_amount_paid
            ,if(
            (SELECT SUM(srh.qty_return*srh.price)
            FROM sales_return_history srh
            WHERE srh.invoice_id = inv.invoice_id
              AND srh.status IS NULL
            ),
            (SELECT SUM(srh.qty_return*srh.price)
            FROM sales_return_history srh
            WHERE srh.invoice_id = inv.invoice_id
              AND srh.status IS NULL
            )
            ,''
            )as sales_return_amount
            FROM invoice inv
            LEFT JOIN `order` o ON (o.order_id = inv.order_id)
            {$SqlCondition}
            AND inv.status != 'Cancelled'
            AND o.patient_visit_id = {$row['patient_visit_id']}
            {$appendSql}
            ";


            $resultInv = $db->sql_query($SQLInv);
            $invoice_amount  = '';

            while ($rowInv = $db->sql_fetchrow($resultInv)) {

                $colc = 0;

                $invoice_amount = $rowInv['invoice_amount'];
                $balance_amount  = $invoice_amount - $rowInv['total_amount_paid'];
                $totalInvoiceAmount += $invoice_amount;
                $invoice_amount = number_format($invoice_amount, 2);

               // $todaylink = "<a target = '_blank' href = 'index.php?_topRm=finance&module=hms_order&record_id={$rowInv['order_id']}&_action=edit'>";
                $invoiceCode = $rowInv['invoice_code'];

                $SQL = "
                SELECT it.*
                FROM invoice_item it
                WHERE it.invoice_id = '{$rowInv['invoice_id']}'
                  AND it.record_type = 'Treatment'
                ";
                $result = $db->sql_query($SQL);
                $treatment = '';

                while ($rowIt = $db->sql_fetchrow($result)) {
                    $treatment .= $rowIt['item_title'].', ';
                }
                $treatment = rtrim($treatment, ', ');

                $date=$fn->getCPDate($Row['check_up_date'], 'd-m-Y');


            $tbl2 = $tbl2.'<tr>
                                <td width="3%" align="center" style="font-size:12px;">'.$count.'</td>
                                <td width="9%" align="center" style="font-size:12px;">'.$row['worker_id'].'</td>
                                <td width="7%" align="center" style="font-size:12px;">'.$row['check_up_date'].'</td>
                                <td width="18%" align="center" style="font-size:12px;">'.$row['employee_name'].'</td>
                                <td width="9%" align="center" style="font-size:12px;">'.$row['nric'].'</td>
                                <td width="18%" align="center" style="font-size:12px;">'.$row['patient_name'].'</td>
                                <td width="14%" align="center" style="font-size:12px;">'.$row['relationship'].'</td>
                                <td width="13%" align="center" style="font-size:12px;">'.$treatment.'</td>
                                <td width="9%" align="right" style="font-size:12px;">'.$invoice_amount.'</td>
                            </tr>';

            $count++;
            }
            }
        }
        $totalInvoiceAmount = number_format($totalInvoiceAmount,2);

        $tbl2 = $tbl2.'<tr>
                    <th colspan="8" align="right" style="font-weight:bold;">JUMLAH : RM</th>
                    <th align="right">'.$totalInvoiceAmount.'</th>
                    </tr></table>';

        $tbl3 = '
        <table border="0" width="100%">
            <tr>
                <td width="50%">Disediakan oleh :</td>
                <td width="50%">Disahkan oleh :</td>
            </tr>
            <tr>
                <td width="20%">Kerani</td>
                <td width="30%">Klinik Pergigian Dr.M.Rama</td>
                <td width="50%">Doktor :- Dr.sharmila A/P SR Krishnamurthi</td>
            </tr>
        </table>';



        $pdf->writeHTML($tbl1, true, false, false, false, '');
        $pdf->writeHTML($tbl2, true, false, false, false, '');
        $pdf->writeHTML($tbl3, true, false, false, false, '');
        //$pdf->writeHTML($tbl4, true, false, false, false, '');
        $pdf->Output('Delivery-Order.pdf', 'I');

    }
    /**
     */
    function getExportToPdfSDN(){
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $cpUtil = Zend_Registry::get('cpUtil');
        $dateUtil = Zend_Registry::get('dateUtil');
        $fn = Zend_Registry::get('fn');

        ini_set('memory_limit', '512M');

        set_time_limit(50000);

        include_once(CP_LIBRARY_PATH.'lib_php/tcpdf/tcpdf.php');
        include_once(CP_LOCAL_PATH.'lib/headfoot1.php');

        $pdf = new MYPDF_Local('L', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

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
        $startDateAppendSql = '';
        $company_id     = $fn->getReqParam('company_id');
        $start_date     = $fn->getReqParam('start_date');
        $end_date       = $fn->getReqParam('end_date');
        $current_date   = date('Y-m-d');
        $month          = date('m');
        $year           = date('Y');
        $monthVal       = $fn->getReqParam('monthVal');
        $yearVal        = $fn->getReqParam('yearVal');
        $site_id        = $fn->getReqParam('site_id');

        if ($start_date != '' && $end_date == '') {
            $startDateAppendSql = "AND i.invoice_date >= '{$start_date}' AND i.invoice_date <= '{$current_date}'";
        } else if ($start_date == '' && $end_date != ''){
            $start_date = $year . '-' . $month . '-' . '01';
            $startDateAppendSql = "AND i.invoice_date >= '{$start_date}' AND i.invoice_date <= '{$end_date}'";
        } else if ($start_date != '' && $end_date != '') {
            $startDateAppendSql = "AND i.invoice_date >= '{$start_date}' AND i.invoice_date <= '{$end_date}'";
        } else if ($monthVal == '' && $yearVal == ''){
            $start_date = $year . '-' . $month . '-' . '01';
            $end_date = $year . '-' . $month . '-' . '31';
            $startDateAppendSql = "AND i.invoice_date >= '{$start_date}' AND i.invoice_date <= '{$end_date}'";
        }

        if ($monthVal != '') {
            $startDateAppendSql .= "AND DATE_FORMAT(i.invoice_date, '%m') = '{$monthVal}'" ;
        }
        if ($yearVal != '') {
            $startDateAppendSql .= "AND DATE_FORMAT(i.invoice_date, '%Y') = '{$yearVal}'" ;
        }

        if($site_id != ''){
            $appendSql = "AND inv.site_id = {$site_id}";
        }

        $SQL = "
        SELECT DISTINCT o.patient_information_id
              ,o.worker_id
              ,o.check_up_date
              ,o.patient_visit_id
              ,i.invoice_date
              ,i.invoice_code
              ,i.invoice_amount
              ,o.company_id
              ,o.company_name
              ,o.cust_address1
              ,o.cust_address2
              ,o.cust_address_state
              ,o.cust_address_city
              ,CONCAT_WS(' ', o.first_name, o.middle_name, o.last_name ) AS patient_name
              ,CONCAT_WS(' ', e.first_name, e.middle_name, e.last_name ) AS employee_name
        FROM `invoice` i
        LEFT JOIN `order` o ON (o.order_id = i.order_id)
        LEFT JOIN `employee_visit` ev ON (ev.patient_visit_id = o.patient_visit_id)
        LEFT JOIN `employee` e ON (e.employee_id = ev.employee_id)
        WHERE o.patient_visit_id != ''
        {$startDateAppendSql}
        ORDER BY patient_name ASC
        ";
        $result  = $db->sql_query($SQL);
        $result2 = $db->sql_query($SQL);
        $Row     = $db->sql_fetchrow($result2);

        $today = date("d-m-Y");
        $totalInvoiceAmount = 0;
        $invoice_date=$fn->getCPDate($Row['invoice_date'], 'm / Y');


        $tbl1 = '
            <table border="0" width="100%">
                <tr>
                    <td width="70%" style="font-size:12px; font-weight:bold;"></td>
                    <td width="10%" style="font-size:16px; font-weight:bold;">INVOICE</td>
                    <td width="20%" align="right" style="font-size:12px; font-weight:bold;"></td>
                </tr>
                <tr>
                    <td width="5%" style="font-size:12px; font-weight:bold;">To : </td>
                    <td width="65%" style="font-size:12px;font-weight:bold;">'.$Row['company_name'].'</td>
                    <td width="10%" style="font-size:12px; font-weight:bold;"><b>No :-  </b></td>
                    <td width="20%" style="font-size:12px; font-weight:bold;"></td>
                </tr>
                <tr>
                    <td width="70%" style="font-size:12px; font-weight:bold;">'.$Row['cust_address1'].'-'.$Row['cust_address2'].'</td>
                    <td width="10%" style="font-size:12px; font-weight:bold;">Date :-</td>
                    <td width="20%" align="left" style="font-size:12px; font-weight:bold;">'.$invoice_date.'</td>
                </tr>
                <tr>
                    <td width="70%" style="font-size:12px; font-weight:bold;">'.$Row['cust_address_city'].'-'.$Row['cust_address_state'].'</td>
                    <td width="10%" style="font-size:12px; font-weight:bold;">Clinic Code :-</td>
                    <td width="20%" align="left" style="font-size:12px; font-weight:bold;"></td>
                </tr>
            </table>
            ';

        $tbl2 ='<table border="1" cellpadding="2" width="100%">
                    <thead>
                        <tr>
                            <th rowspan="2" width="5%" align="center" style="font-size:12px; font-weight:bold;">No</th>
                            <th rowspan="2" width="15%" align="center" style="font-size:12px; font-weight:bold;">Treatment Date</th>
                            <th rowspan="2" width="17%" align="center" style="font-size:12px; font-weight:bold;">Staff No</th>
                            <th rowspan="2" width="28%" align="center" style="font-size:12px; font-weight:bold;">Name of Staff</th>
                            <th rowspan="2" width="20%" align="center" style="font-size:12px; font-weight:bold;">Treatment Done</th>
                            <th colspan="2" width="15%" align="center" style="font-size:12px; font-weight:bold;">JUMLAH</th>
                        </tr>
                        <tr>
                            <td align="center" width="10%" style="font-weight:bold;">RM</td>
                            <td align="center" width="5%" style="font-weight:bold;">Sen</td>
                        </tr>
                    </thead>';

        $count = 1;

        while ($rowOrder = $db->sql_fetchrow($result)) {

            $SqlCondition = "WHERE o.company_id = {$company_id}";

            if($company_id != ''){
            $SQLInv = "
            SELECT inv.*
            ,o.order_id
            ,(SELECT SUM(invh.amount)
            FROM invoice_receipt_history invh
            LEFT JOIN (receipt rcp) ON (invh.receipt_id = rcp.receipt_id)
            WHERE invh.related_invoice_id = inv.invoice_id
              AND rcp.receipt_status = 'Paid'
            ) AS total_amount_paid
            ,if(
            (SELECT SUM(srh.qty_return*srh.price)
            FROM sales_return_history srh
            WHERE srh.invoice_id = inv.invoice_id
              AND srh.status IS NULL
            ),
            (SELECT SUM(srh.qty_return*srh.price)
            FROM sales_return_history srh
            WHERE srh.invoice_id = inv.invoice_id
              AND srh.status IS NULL
            )
            ,''
            )as sales_return_amount
            FROM invoice inv
            LEFT JOIN `order` o ON (o.order_id = inv.order_id)
            {$SqlCondition}
            AND inv.status != 'Cancelled'
            AND o.patient_visit_id = {$rowOrder['patient_visit_id']}
            {$appendSql}
            ";


            $resultInv = $db->sql_query($SQLInv);
            $invoice_amount  = '';

            while ($rowInv = $db->sql_fetchrow($resultInv)) {

                $colc = 0;

                $invoice_amount = $rowInv['invoice_amount'];
                $balance_amount  = $invoice_amount - $rowInv['total_amount_paid'];
                $totalInvoiceAmount += $invoice_amount;
                $invoice_amountSen = number_format($invoice_amount, 2);
                $decAmount  = $invoice_amountSen - (int) $invoice_amountSen;  // .7
                $decAmount  = sprintf("%.02f",$decAmount);
                $decAmount  = ltrim($decAmount,"0.");
                if($decAmount == ''){
                    $decAmount = "00";
                }

                $invoice_amount = number_format($invoice_amount);
               // $todaylink = "<a target = '_blank' href = 'index.php?_topRm=finance&module=hms_order&record_id={$rowInv['order_id']}&_action=edit'>";
                $invoiceCode = $rowInv['invoice_code'];

                $SQL = "
                SELECT it.*
                FROM invoice_item it
                WHERE it.invoice_id = '{$rowInv['invoice_id']}'
                  AND it.record_type = 'Treatment'
                ";
                $result = $db->sql_query($SQL);
                $treatment = '';
                while ($rowIt = $db->sql_fetchrow($result)) {
                    $treatment .= $rowIt['item_title'].', ';
                }

                $treatment = rtrim($treatment, ', ');

                $date          = $fn->getCPDate($rowOrder['check_up_date'], 'd-m-Y');
                $invoice_date  = $fn->getCPDate($rowOrder['invoice_date'], 'm / Y');
                $check_up_date = $fn->getCPDate($Row['check_up_date'], 'F Y');

            $tbl2 = $tbl2.'<tr>
                                <td width="5%" align="center" style="font-size:12px;">'.$count.'</td>
                                <td width="15%" align="center" style="font-size:12px;">'.$date.'</td>
                                <td width="17%" align="center" style="font-size:12px;">'.$rowOrder['worker_id'].'</td>
                                <td width="28%" align="center" style="font-size:12px;">'.$rowOrder['employee_name'].'</td>
                                <td width="20%" align="center" style="font-size:12px;">'.$treatment.'</td>
                                <td width="10%" align="right" style="font-size:12px;">'.$invoice_amount.'</td>
                                <td width="5%" align="right" style="font-size:12px;">'.$decAmount.'</td>
                            </tr>';

            $count++;
            }
            }
        }
        $totaldecAmount = 0;
        //$totalInvoiceAmount = number_format($totalInvoiceAmount,2);
        $totalInvoiceAmountSen = number_format($totalInvoiceAmount, 2);
        $decAmount  = $totalInvoiceAmountSen - (int) $totalInvoiceAmountSen;  // .7
        $totaldecAmount  = sprintf("%.02f",$totaldecAmount);
        $totaldecAmount  = ltrim($totaldecAmount,"0.");
        if($totaldecAmount == ''){
            $totaldecAmount = "00";
        }

        $tbl2 = $tbl2.'<tr>
                    <th colspan="5" align="right" style="font-weight:bold;">TOTAL : RM</th>
                    <th align="right">'.$totalInvoiceAmount.'</th>
                    <th align="right">'.$totaldecAmount.'</th>
                    </tr></table>';

        $tbl3 = '
        <table border="0" width="100%">
            <tr>
                <td>Singed :-</td>
            </tr>
            <tr>
                <td width="28%" style="border-bottom:2px solid black"></td>
            </tr>
            <tr>
                <td>Dr.Sharmila A/p SR Krishnamurthi</td>
            </tr>
        </table>';



        $pdf->writeHTML($tbl1, true, false, false, false, '');
        $pdf->writeHTML($tbl2, true, false, false, false, '');
        $pdf->writeHTML($tbl3, true, false, false, false, '');
        //$pdf->writeHTML($tbl4, true, false, false, false, '');
        $pdf->Output('Delivery-Order.pdf', 'I');

    }


}