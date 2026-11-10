<?
class CP_Admin_Widgets_Labsg_MasterFinanceSummaryReport_Model extends CP_Common_Lib_WidgetModelAbstract
{
    /**
     *
     */
    function getSQL(){
        $SQL = "
        SELECT SUM(amount) AS receipt_amount
               ,mode_of_payment
        FROM receipt r
        LEFT JOIN (`order` o) ON (r.order_id = o.order_id)
        ";

        return $SQL;
    }

    /**
     *
     */
    function setSearchVar() {
        $fn = Zend_Registry::get('fn');
        $searchVar = $this->searchVar;

        $searchVar = $this->searchVar;
        $searchVar->mainTableAlias = 'r';

        $start_date = $fn->getReqParam('start_date');
        $end_date   = $fn->getReqParam('end_date');

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

        $searchVar->sqlSearchVar[] = "r.receipt_status = 'Paid'";
        $searchVar->sqlSearchVar[] = "r.date BETWEEN '{$start_date}' AND '{$end_date}'";

        $searchVar->groupBy   = "r.mode_of_payment";
        $searchVar->sortOrder = 'r.mode_of_payment';
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
        $dataArray = $modelHelper->getWidgetDataArray($this->controller, 'tradingsg_dailyCollectionReport');

        $this->dataArray = $dataArray;
        return $this->dataArray;
    }

    /**
     *
     */
    function getExportToExcel(){
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');

        $site_id      = $fn->getSessionParam('cp_site_id');
        $start_date   = $fn->getReqParam('start_date');
        $end_date     = $fn->getReqParam('end_date');

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

        set_time_limit(50000);
        ini_set('memory_limit', '512M');

        require_once("PHPExcel.php");
        include 'PHPExcel/IOFactory.php';

        $file_name = "Finance-Summary_" . date("d-m-Y") . ".xls";

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

        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Mode of Payment');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Amount');
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
        if ($cpCfg['cp.hasMultiUniqueSites']) {
            $appendSql = "AND r.site_id = {$site_id}";
        }
        
        $SQL = "
        SELECT SUM(amount) AS receipt_amount
               ,mode_of_payment
        FROM receipt r
        LEFT JOIN (`order` o) ON (r.order_id = o.order_id)
        WHERE r.receipt_status = 'Paid'
          AND r.date BETWEEN '{$start_date}' AND '{$end_date}'
          {$appendSql}
        GROUP BY r.mode_of_payment
        ORDER BY r.mode_of_payment
        ";
        $result = $db->sql_query($SQL);
        $grand_total = 0;
        $total_payment_mode = 0;
        $total_for_payment_mode = 0;
        $amount = 0;

        while ($row = $db->sql_fetchrow($result)) {
            $colc = 0;
            $rowc++;
            $amount = number_format($row['receipt_amount'], 2);

            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['mode_of_payment']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $amount);

            $total_payment_mode += $row['receipt_amount'];
        }

        $total_for_payment_mode = number_format($total_payment_mode, 2);

        $appendSqlInv = '';
        if ($cpCfg['cp.hasMultiUniqueSites']) {
            $appendSqlInv = "AND inv.site_id = {$site_id}";
        }

        $SQLInv = "
        SELECT inv.*
        ,(SELECT SUM(invh.amount)
        FROM invoice_receipt_history invh
        LEFT JOIN (receipt rcp) ON (invh.receipt_id = rcp.receipt_id)
        WHERE invh.invoice_id = inv.invoice_id
          AND rcp.receipt_status = 'Paid'
        ) AS total_amount_paid
        FROM invoice inv
        WHERE inv.status != 'Cancelled'
        AND inv.invoice_date BETWEEN '{$start_date}' AND '{$end_date}'
        {$appendSqlInv}
        ";

        $resultInv = $db->sql_query($SQLInv);
        $totalBalanceAmount  = 0;
        $balance_amount = 0;
        $discount_amount = 0;
        while ($rowInv = $db->sql_fetchrow($resultInv)) {
            $invoice_amount = $rowInv['invoice_amount'];
            $balance_amount += $invoice_amount - $rowInv['total_amount_paid'] - $rowInv['discount'];
            $discount_amount += $rowInv['discount'];
        }
        
        $totalBalanceAmount  = number_format($balance_amount,2);
        $totalDiscountAmount = number_format($discount_amount,2);

        $grand_total = $balance_amount + $total_payment_mode;
        $grand_total_formatted = number_format($grand_total, 2);
        
        $colc = 0;
        $rowc++;
        $actSheet->getStyle("A{$rowc}:B{$rowc}")->applyFromArray($headStyle);
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Total');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $total_for_payment_mode);

        $colc = 0;
        $rowc++;
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Billing Outstanding');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $totalBalanceAmount);

        $colc = 0;
        $rowc++;
        $actSheet->getStyle("A{$rowc}:B{$rowc}")->applyFromArray($headStyle);
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Total of Payment & Outstanding');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $grand_total_formatted);

        $colc = 0;
        $rowc++;
        $actSheet->mergeCells("A{$rowc}:B{$rowc}");

        $colc = 0;
        $rowc++;
        $actSheet->getStyle("A{$rowc}:B{$rowc}")->applyFromArray($headStyle);
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Total Discount');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $totalDiscountAmount);

        $colc = 0;
        $rowc++;
        $actSheet->mergeCells("A{$rowc}:B{$rowc}");

        $rowc++;
        $actSheet->mergeCells("A{$rowc}:B{$rowc}");
        $actSheet->getStyle("A{$rowc}:B{$rowc}")->applyFromArray($headStyle);
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Total Unbilled Revenue');

        $colc = 0;
        $rowc++;
        $totalUnbilledVisitForCompany = $this->getTotalUnbilledVisitForCompany($start_date, $end_date);
        $formattedTotalUnbilledVisitForCompany = number_format($totalUnbilledVisitForCompany, 2);
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Company');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $formattedTotalUnbilledVisitForCompany);

        $colc = 0;
        $rowc++;
        $totalUnbilledVisitForIndividual = $this->getTotalUnbilledVisitForIndividual($start_date, $end_date);
        $formattedTotalUnbilledVisitForIndividual = number_format($totalUnbilledVisitForIndividual, 2);
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Individual');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $formattedTotalUnbilledVisitForIndividual);

        $colc = 0;
        $rowc++;
        $actSheet->getStyle("A{$rowc}:B{$rowc}")->applyFromArray($headStyle);
        //$totalPatientVisit = $this->getTotalPatientVisit($start_date, $end_date);
        $total_sales = $grand_total - $discount_amount + $totalUnbilledVisitForCompany + $totalUnbilledVisitForIndividual;      
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Total Sales');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $total_sales);

        $rowc++;
        $actSheet->getStyle("A{$rowc}:D{$rowc}")->applyFromArray($headStyle);

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
        $cpCfg = Zend_Registry::get('cpCfg');

        $site_id = $fn->getSessionParam('cp_site_id');

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

        $appendSql = '';
        if ($cpCfg['cp.hasMultiUniqueSites']) {
            $appendSql = "AND r.site_id = {$site_id}";
        }

        $SQL = "
        SELECT SUM(amount) AS receipt_amount
               ,mode_of_payment
        FROM receipt r
        LEFT JOIN (`order` o) ON (r.order_id = o.order_id)
        WHERE r.receipt_status = 'Paid'
          AND r.date BETWEEN '{$start_date}' AND '{$end_date}'
          {$appendSql}
        GROUP BY r.mode_of_payment
        ORDER BY r.mode_of_payment
        ";
        $result = $db->sql_query($SQL);
        $grand_total = 0;
        $total_payment_mode = 0;
        $total_for_payment_mode = 0;
        $amount = 0;

        $start_date_formatted = $dateUtil->formatDate($start_date, 'DD-MM-YYYY');
        $end_date_formatted = $dateUtil->formatDate($end_date, 'DD-MM-YYYY');
        $tbl3 ='
        <table>
            <tbody>
                <tr>
                    <td><h3><u>Master finance summary report between ' . $start_date_formatted .' and ' . $end_date_formatted .'</u></h3></td>
                </tr>
                <tr>
                    <td></td>
                </tr>
            </tbody>
        </table>
        <table border="1" width="100%" cellpadding="4">
            <thead>
                <tr bgcolor="#B6E5F9">
                    <th><strong>Mode of Payment</strong></th>
                    <th align="Right"><strong>Amount</strong></th>
                </tr>
            </thead>
            <tbody>
        ';

        $grand_total = 0;
        $total_payment_mode = 0;
        $total_for_payment_mode = 0;
        $amount = 0;
        while ($row = $db->sql_fetchrow($result)) {
            $amount = number_format($row['receipt_amount'], 2);
            $tbl3 = $tbl3.'
            <tr>
                <td>'.$row['mode_of_payment'].'</td>
                <td align="Right">'.$amount.'</td>
            </tr>
            ';

            $total_payment_mode += $row['receipt_amount'];
        }

        $total_for_payment_mode = number_format($total_payment_mode, 2);

        $appendSqlInv = '';
        if ($cpCfg['cp.hasMultiUniqueSites']) {
            $appendSqlInv = "AND inv.site_id = {$site_id}";
        }

        $SQLInv = "
        SELECT inv.*
        ,(SELECT SUM(invh.amount)
        FROM invoice_receipt_history invh
        LEFT JOIN (receipt rcp) ON (invh.receipt_id = rcp.receipt_id)
        WHERE invh.invoice_id = inv.invoice_id
          AND rcp.receipt_status = 'Paid'
        ) AS total_amount_paid
        FROM invoice inv
        WHERE inv.status != 'Cancelled'
        AND inv.invoice_date BETWEEN '{$start_date}' AND '{$end_date}'
        {$appendSqlInv}
        ";
        $resultInv = $db->sql_query($SQLInv);
        $totalBalanceAmount  = 0;
        $balance_amount = 0;
        $discount_amount = 0;
        while ($rowInv = $db->sql_fetchrow($resultInv)) {
            $invoice_amount = $rowInv['invoice_amount'];
            $balance_amount += $invoice_amount - $rowInv['total_amount_paid'] - $rowInv['discount'];
            $discount_amount += $rowInv['discount'];
        }
        
        $totalBalanceAmount  = number_format($balance_amount,2);
        $totalDiscountAmount = number_format($discount_amount,2);

        $grand_total = $balance_amount + $total_payment_mode;
        $grand_total_formatted = number_format($grand_total, 2);
        $totalUnbilledVisitForCompany = $this->getTotalUnbilledVisitForCompany($start_date, $end_date); 
        $totalUnbilledVisitForIndividual = $this->getTotalUnbilledVisitForIndividual($start_date, $end_date); 
        $formattedTotalUnbilledVisitForCompany    = number_format($totalUnbilledVisitForCompany, 2);
        $formattedTotalUnbilledVisitForIndividual = number_format($totalUnbilledVisitForIndividual, 2);
        //$formattedTotalPatientVisit               = number_format($this->model->getTotalPatientVisit($start_date, $end_date), 2);
        $total_sales = $grand_total - $discount_amount + $totalUnbilledVisitForCompany + $totalUnbilledVisitForIndividual;
        $formattedTotalPatientVisit = number_format($total_sales, 2);

        $tbl3 = $tbl3.'
        <tr bgcolor="#B6E5F9">
            <td><strong>Total</strong></td>
            <td align="Right"><strong>'.$total_for_payment_mode.'</strong></td>
        </tr>
        <tr>
            <td>Billing Outstanding</td>
            <td align="Right"><strong>'.$totalBalanceAmount.'</strong></td>            
        </tr>
        <tr bgcolor="#B6E5F9">
            <td><strong>Total of Payment & Outstanding</strong></td>
            <td align="Right"><strong>'.$grand_total_formatted.'</strong></td>
        </tr>
        <tr><td colspan="2"></td></tr>
        <tr bgcolor="#B6E5F9">
            <td><strong>Total Discount</strong></td>
            <td align="Right"><strong>'.$totalDiscountAmount.'</strong></td>
        </tr>
        <tr><td colspan="2"></td></tr>
        <tr bgcolor="#B6E5F9">
            <td colspan="2"><strong>Total Unbilled Revenue</strong></td>
        </tr>
        <tr>
            <td><strong>Company</strong></td>
            <td align="Right">'.$formattedTotalUnbilledVisitForCompany.'</td>
        </tr>
        <tr>
            <td><strong>Individual</strong></td>
            <td align="Right">'.$formattedTotalUnbilledVisitForIndividual.'</td>
        </tr>
        <tr><td colspan="2"></td></tr>
        <tr bgcolor="#B6E5F9">
            <td><strong>Total Sales</strong></td>
            <td align="Right"><strong>'.$formattedTotalPatientVisit.'</strong></td>
        </tr>
        </tbody>
        </table>
        ';

        $pdf->ln(4);
        $pdf->writeHTML($tbl3, true, false, false, false, '');
        $download_title = "Finance-Summary_" . date("d-m-Y") .'.pdf';
        $pdf->Output($download_title, 'I');
    }


    /**
     *
     */
    function getTotalUnbilledVisitForCompany($start_date, $end_date) {
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
              AND pv.status != 'Cancelled'
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
                    $total_company_unbilled += $row3['total_fees_amount'];
                }
            }

            $overall_company_unbilled += $total_company_unbilled;
        }

        return $overall_company_unbilled;
    }

    /**
     *
     */
    function getTotalUnbilledVisitForIndividual($start_date, $end_date) {
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
            }
        }

        return $total_individual_unbilled;
    }

    /**
     *
     */
    function getTotalPatientVisit($start_date, $end_date) {
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
          AND pv.status != 'Cancelled'
          {$appendSql}
        ";
        $result1  = $db->sql_query($sql1);
        $rows = '';
        $total_patient_visit = 0;
        while ($row1 = $db->sql_fetchrow($result1)) {
            $sql2 = "
            SELECT SUM(fees) AS total_fees_amount FROM treatment_visit
            WHERE patient_visit_id = {$row1['patient_visit_id']}
            ";
            $result2  = $db->sql_query($sql2);
            $row2 = $db->sql_fetchrow($result2);

            if ($row2['total_fees_amount']) {
                $total_patient_visit += $row2['total_fees_amount'];
            }
        }

        return $total_patient_visit;
    }
}