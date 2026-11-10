<?
class CP_Admin_Widgets_Labsg_RevenueByMonth_View extends CP_Common_Lib_WidgetViewAbstract
{
    /**
     *
     */
    function getWidget() {
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');

        $text = "
		<div class = 'tableOuter scroll-pane'>
		<table class='thinlist'>
			<thead>
				<tr>
					<th>Month</th>
					<th class='txtRight'>Total</th>
				</tr>
			</thead>
			<tbody>
				{$this->getRowsHTML()}
			</tbody>
		</table>
		</div>
        ";
        return $text;
    }

    /**
     *
     */
    function getRowsHTML() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');

        $rows = '';
        $total = 0;
        $payment_total = 0;

        foreach($this->model->dataArray as $row){
            $invoice_amount_monthly = number_format(($row['total_invoice_amount'] - $row['total_discount']), 2);
            $payment_total += $invoice_amount_monthly;

            $rows .= "
			<tr>
				<td>{$row['invoice_month']}</td>
				<td class='txtRight'>{$invoice_amount_monthly}</td>
			</tr>
			";
        }
        $payment_total = number_format($payment_total, 2);

        $text = "
        {$rows}
        <tr>
            <td class='highlight'><strong>Total</strong></td>
            <td class='highlight' align='right'><strong>{$payment_total}</strong></td>
        </tr>
        ";

        return $text;
    }

    /**
     * Not working in treatment History widget # model.php. So written in another file by ARIF
     */
    function getExportToExcelTreatment(){
        $db       = Zend_Registry::get('db');
        $cpCfg    = Zend_Registry::get('cpCfg');
        $tv       = Zend_Registry::get('tv');
        $cpUtil   = Zend_Registry::get('cpUtil');
        $dateUtil = Zend_Registry::get('dateUtil');
        $fn       = Zend_Registry::get('fn');

        $rows = '';

        set_time_limit(50000);
        ini_set('memory_limit', '512M');

        require_once("PHPExcel.php");
        include 'PHPExcel/IOFactory.php';

        $file_name = "TreatmentHistory_" . date("d-m-Y") . ".xls";

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
        $site_id        = $fn->getSessionParam('cp_site_id');
        $start_date     = $fn->getReqParam('start_date');
        $end_date       = $fn->getReqParam('end_date');
       
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

        $actSheet = &$objPHPExcel->getActiveSheet();
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Treatment Name');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Visits');
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

        $SQL = "
        SELECT  t.*
        FROM `treatment` t
        ORDER BY t.treatment_id ASC
        ";
        $result = $db->sql_query($SQL);
        while ($row = $db->sql_fetchrow($result)) {
            $colc = 0;
            $rowc++;

            $recCount = $fn->getRecordCount('treatment_visit', "", array('includeSiteId' => false));
            
            $appendSqlPv = '';
            if ($cpCfg['cp.hasMultiUniqueSites']) {
                $appendSqlPv = "AND pv.site_id = {$site_id}";
            }
            $SQLTreatmentVisit = "
            SELECT t.*
            FROM treatment_visit t
            LEFT JOIN patient_visit pv ON(pv.patient_visit_id = t.patient_visit_id)
            WHERE t.treatment_id = '{$row['treatment_id']}'
              AND pv.status != 'Cancelled'
              AND pv.check_up_date BETWEEN '{$start_date}' AND '{$end_date}'
              {$appendSqlPv}
            ";
            $resultCountTreat = $db->sql_query($SQLTreatmentVisit);
            $recCountTreat    = $db->sql_numrows($resultCountTreat);

            $used = $recCountTreat/$recCount * 100;
            $used = number_format($used, 2);

            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['title']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $recCountTreat);
        }

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
    }

    /**
     *
     */
    function getExportToPdfTreatment(){
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

        $SQL = "
        SELECT t.*
        FROM `treatment` t
        ORDER BY t.treatment_id ASC
        ";
        $result = $db->sql_query($SQL);
        $tbl3 ='
        <table>
            <tbody>
                <tr>
                    <td><h3><u>Treatment History</u></h3></td>
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
                    <th width="80%"><strong>Treatment Name</strong></th>
                    <th width="13%" align="right"><strong>Visits</strong></th>
                </tr>
            </thead>
            <tbody>
        ';
        $count = 1;
        while ($row = $db->sql_fetchrow($result)) {            
            $recCount = $fn->getRecordCount('treatment_visit', "", array('includeSiteId' => false));
            
            $appendSqlPv = '';
            if ($cpCfg['cp.hasMultiUniqueSites']) {
                $appendSqlPv = "AND pv.site_id = {$site_id}";
            }
            $SQLTreatmentVisit = "
            SELECT t.*
            FROM treatment_visit t
            LEFT JOIN patient_visit pv ON(pv.patient_visit_id = t.patient_visit_id)
            WHERE t.treatment_id = '{$row['treatment_id']}'
              AND pv.status != 'Cancelled'
              AND pv.check_up_date BETWEEN '{$start_date}' AND '{$end_date}'
              {$appendSqlPv}
            ";
            $resultCountTreat = $db->sql_query($SQLTreatmentVisit);
            $recCountTreat    = $db->sql_numrows($resultCountTreat);

            $used = $recCountTreat/$recCount * 100;
            $used = number_format($used, 2);

            $tbl3 .= '
            <tr>
                <td width="7%" align="center">' . $count . '</td>
                <td width="80%">' . $row['title'] . '</td>
                <td width="13%" align="right">' . $recCountTreat . '</td>
            </tr>
            ';
            $count++;
        }

        $tbl3 = $tbl3 .'
        </tbody>
        </table>';

        $pdf->ln(4);
        $pdf->writeHTML($tbl3, true, false, false, false, '');
        $download_title = "Treatment-History" . date("d-m-Y") .'.pdf';
        $pdf->Output($download_title, 'I');
    }
}