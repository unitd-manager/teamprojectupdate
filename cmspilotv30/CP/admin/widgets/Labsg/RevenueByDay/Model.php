<?
class CP_Admin_Widgets_Labsg_RevenueByDay_Model extends CP_Common_Lib_WidgetModelAbstract
{
    /**
     *
     */
    function getSQL(){
        $SQL = "
        SELECT i.invoice_date
              ,DATE_FORMAT(i.invoice_date, '%W') AS day
              ,SUM(i.invoice_amount) AS total_invoice_amount
              ,SUM(i.discount) AS total_discount
        FROM invoice i
        ";

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

        $start_date = $fn->getReqParam('start_date');
        $end_date   = $fn->getReqParam('end_date');
        $year       = $fn->getReqParam('year');
        $month      = $fn->getReqParam('month');

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

        $searchVar->sqlSearchVar[] = "i.invoice_date >= '{$start_date}' AND i.invoice_date <= '{$end_date}'";
        $searchVar->sqlSearchVar[] = "i.status != 'Cancelled'";
        $searchVar->groupBy = "i.invoice_date";
    }

    /**
     *
     */
    function getDataArray() {
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');

        $modelHelper = Zend_Registry::get('modelHelper');
        $dataArray = $modelHelper->getWidgetDataArray($this->controller, 'labsg_revenueByDay');

        $this->dataArray = $dataArray ;
        return $dataArray;
    }

    /**
     *
     */
    function getExportToExcel(){
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');

        set_time_limit(50000);
        ini_set('memory_limit', '512M');

        require_once("PHPExcel.php");
        include 'PHPExcel/IOFactory.php';

        $file_name = "RevenueByDay__" . date("d-m-Y") . ".xls";

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
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Day');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Total');
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

        $site_id    = $fn->getSessionParam('cp_site_id');
        $start_date = $fn->getReqParam('start_date');
        $end_date   = $fn->getReqParam('end_date');
        $year       = $fn->getReqParam('year');
        $month      = $fn->getReqParam('month');

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

        $appendSqlInv = '';
        if ($cpCfg['cp.hasMultiUniqueSites']) {
            $appendSqlInv = "AND i.site_id = {$site_id}";
        }

        $SQL = "
        SELECT i.invoice_date
              ,DATE_FORMAT(i.invoice_date, '%W') AS day
              ,SUM(i.invoice_amount) AS total_invoice_amount
              ,SUM(i.discount) AS total_discount
        FROM invoice i
        WHERE i.status != 'Cancelled'
          AND i.invoice_date >= '{$start_date}' AND i.invoice_date <= '{$end_date}'
          {$appendSqlInv}
        GROUP BY i.invoice_date
        ";
        $result = $db->sql_query($SQL);
        $payment_total = 0;

        while ($row = $db->sql_fetchrow($result)) {
            $colc = 0;
            $rowc++;
            $invoice_amount_monthly = $row['total_invoice_amount'] - $row['total_discount'];
            $invoice_amount_monthly_formatted = number_format($invoice_amount_monthly, 2);
            $payment_total += $invoice_amount_monthly;

            $creationDate = $fn->getCPDate($row['invoice_date'],"d-m-Y");

            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $creationDate);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['day']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $invoice_amount_monthly_formatted);
        }

        $colc = 0;
        $rowc++;
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Total');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, number_format($payment_total,2));
        $actSheet->getStyle("A{$rowc}:C{$rowc}")->applyFromArray($headStyle);

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

        $site_id    = $fn->getSessionParam('cp_site_id');
        $start_date = $fn->getReqParam('start_date');
        $end_date   = $fn->getReqParam('end_date');
        $year       = $fn->getReqParam('year');
        $month      = $fn->getReqParam('month');

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

        $appendSql = '';
        if ($cpCfg['cp.hasMultiUniqueSites']) {
            $appendSql = "AND i.site_id = {$site_id}";
        }

        $SQL = "
        SELECT i.invoice_date
              ,DATE_FORMAT(i.invoice_date, '%W') AS day
              ,SUM(i.invoice_amount) AS total_invoice_amount
              ,SUM(i.discount) AS total_discount
        FROM invoice i
        WHERE i.status != 'Cancelled'
          AND i.invoice_date >= '{$start_date}' AND i.invoice_date <= '{$end_date}'
          {$appendSql}
        GROUP BY i.invoice_date
        ";
        $result = $db->sql_query($SQL);

        $start_date_formatted = $dateUtil->formatDate($start_date, 'DD-MM-YYYY');
        $end_date_formatted = $dateUtil->formatDate($end_date, 'DD-MM-YYYY');
        $tbl3 ='
        <table>
            <tbody>
                <tr>
                    <td><h3><u>Revenue by day between ' . $start_date_formatted .' and ' . $end_date_formatted .'</u></h3></td>
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
                    <th width="20%"><strong>Date</strong></th>
                    <th width="20%"><strong>Day</strong></th>
                    <th width="53%" align="right"><strong>Total Amount</strong></th>
                </tr>
            </thead>
            <tbody>
        ';
        $count = 1;
        while ($row = $db->sql_fetchrow($result)) {            
            $date = $dateUtil->formatDate($row['invoice_date'], 'DD-MM-YYYY');
            $invoice_amount_monthly = $row['total_invoice_amount'] - $row['total_discount'];

            $tbl3 .= '
            <tr>
                <td width="7%" align="center">' . $count . '</td>
                <td width="20%">' . $date . '</td>
                <td width="20%">' . $row['day'] . '</td>
                <td width="53%" align="right">' . number_format($invoice_amount_monthly, 2) . '</td>
            </tr>
            ';
            $count++;
        }

        $tbl3 = $tbl3 .'
        </tbody>
        </table>';

        $pdf->ln(4);
        $pdf->writeHTML($tbl3, true, false, false, false, '');
        $download_title = "Revenue-By-Day" . date("d-m-Y") .'.pdf';
        $pdf->Output($download_title, 'I');
    }
}