<?
class CP_Admin_Widgets_Labsg_RevenueByMonth_Model extends CP_Common_Lib_WidgetModelAbstract
{
    /**
     *
     */
    function getSQL(){
        $SQL = "
        SELECT DATE_FORMAT(i.invoice_date, '%b %Y') AS invoice_month
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
        
        $searchVar = $this->searchVar;
        $searchVar->mainTableAlias = 'i';

        $year = $fn->getReqParam('year');

        $searchVar->sqlSearchVar[] = "DATE_FORMAT(i.invoice_date, '%Y') = '{$year}'";
        $searchVar->sqlSearchVar[] = "i.status != 'Cancelled'";
        $searchVar->groupBy = "DATE_FORMAT(i.invoice_date, '%Y-%m')";
    }

    /**
     *
     */
    function getDataArray() {
        $modelHelper = Zend_Registry::get('modelHelper');

        $dataArray = $modelHelper->getWidgetDataArray($this->controller, 'labsg_revenueByMonth');
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

        $file_name = "RevenueByMonth" . date("d-m-Y") . ".xls";

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

        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Month');
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

        $site_id    = $fn->getSessionParam('cp_site_id');
        $year       = $fn->getReqParam('year');

        $appendSql = '';
        if ($cpCfg['cp.hasMultiUniqueSites']) {
            $appendSql = "AND i.site_id = {$site_id}";
        }

        $SQL = "
        SELECT DATE_FORMAT(i.invoice_date, '%b %Y') AS invoice_month
              ,SUM(i.invoice_amount) AS total_invoice_amount
              ,SUM(i.discount) AS total_discount
        FROM invoice i
        WHERE i.status != 'Cancelled'
          AND DATE_FORMAT(i.invoice_date, '%Y') = '{$year}'
          {$appendSql}
        GROUP BY DATE_FORMAT(i.invoice_date, '%Y-%m')
        ";
        $result = $db->sql_query($SQL);
        $payment_total = '';
        while ($row = $db->sql_fetchrow($result)) {
            $colc = 0;
            $rowc++;

            $invoice_amount_monthly = $row['total_invoice_amount'] - $row['total_discount'];
            $invoice_amount_monthly_formatted = number_format($invoice_amount_monthly, 2);
            $payment_total += $invoice_amount_monthly;

            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['invoice_month']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $invoice_amount_monthly_formatted);
        }

        $colc = 0;
        $rowc++;

        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Total');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, number_format($payment_total,2));
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
        $year       = $fn->getReqParam('year');

        $appendSql = '';
        if ($cpCfg['cp.hasMultiUniqueSites']) {
            $appendSql = "AND i.site_id = {$site_id}";
        }

        $SQL = "
        SELECT DATE_FORMAT(i.invoice_date, '%b %Y') AS invoice_month
              ,SUM(i.invoice_amount) AS total_invoice_amount
              ,SUM(i.discount) AS total_discount
        FROM invoice i
        WHERE i.status != 'Cancelled'
          AND DATE_FORMAT(i.invoice_date, '%Y') = '{$year}'
          {$appendSql}
        GROUP BY DATE_FORMAT(i.invoice_date, '%Y-%m')
        ";
        $result = $db->sql_query($SQL);
        $tbl3 ='
        <table>
            <tbody>
                <tr>
                    <td><h3><u>Revenue by year for ' . $year .'</u></h3></td>
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
                    <th width="20%"><strong>Month</strong></th>
                    <th width="73%" align="right"><strong>Total Amount</strong></th>
                </tr>
            </thead>
            <tbody>
        ';
        $count = 1;
        $payment_total = 0;
        while ($row = $db->sql_fetchrow($result)) {            
            $invoice_amount_monthly = $row['total_invoice_amount'] - $row['total_discount'];
            $payment_total += $invoice_amount_monthly;

            $tbl3 .= '
            <tr>
                <td width="7%" align="center">' . $count . '</td>
                <td width="20%">' . $row['invoice_month'] . '</td>
                <td width="73%" align="right">' . number_format($invoice_amount_monthly, 2) . '</td>
            </tr>
            ';
            $count++;
        }

        $tbl3 = $tbl3 .'
        <tr bgcolor="#B6E5F9">
            <td width="27%" colspan="2"><strong>Total</strong></td>
            <td width="73%" align="right"><strong>' . number_format($payment_total, 2) . '</strong></td>
        </tr>
        </tbody>
        </table>';

        $pdf->ln(4);
        $pdf->writeHTML($tbl3, true, false, false, false, '');
        $download_title = "Revenue-By-Month" . date("d-m-Y") .'.pdf';
        $pdf->Output($download_title, 'I');
    }
}