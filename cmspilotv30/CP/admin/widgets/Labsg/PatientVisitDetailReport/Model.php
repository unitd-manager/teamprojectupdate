<?
class CP_Admin_Widgets_Labsg_PatientVisitDetailReport_Model extends CP_Common_Lib_WidgetModelAbstract
{
    /**
     *
     */
    function getSQL(){
        $SQL = "
        SELECT pv.*
              ,pi.name
              ,pi.gender
              ,pi.registration_no
              ,pi.dob
              ,c.company_name
        FROM patient_visit pv
        LEFT JOIN (patient_information pi) ON (pv.patient_information_id = pi.patient_information_id)
        LEFT JOIN (company c) ON (pi.company_id = c.company_id)
        ";

        return $SQL;
    }

    /**
     *
     */
    function setSearchVar() {
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        
        $searchVar = $this->searchVar;
        $searchVar->mainTableAlias = 'pv';

        $start_date   = $fn->getReqParam('start_date');
        $end_date     = $fn->getReqParam('end_date');
        $year         = $fn->getReqParam('year');
        $month        = $fn->getReqParam('month');
        $invoiced     = $fn->getReqParam('invoiced');

        if ($year == '') {
            $year = date('Y');
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

        if ($invoiced != "") {
            if ($invoiced == 'Yes'){
                $searchVar->sqlSearchVar[] = "pv.order_id != ''";
            } else if ($invoiced == 'No') {
                $searchVar->sqlSearchVar[] = "(pv.order_id = '' OR pv.order_id IS NULL)";
            }
        }

        $searchVar->sqlSearchVar[] = "pi.name != ''";
        $searchVar->sqlSearchVar[] = "pv.status != 'Cancelled'";
        $searchVar->sqlSearchVar[] = "pv.check_up_date >= '{$start_date}' AND pv.check_up_date <= '{$end_date}'";
        $searchVar->sortOrder = "pv.check_up_date DESC, pv.patient_visit_id DESC";
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
        $dataArray = $modelHelper->getWidgetDataArray($this->controller, 'labsg_patientVisitDetailReport');

        $this->dataArray = $dataArray;
        return $this->dataArray;
    }

    /**
     */
    function getExportToExcel(){
        $db       = Zend_Registry::get('db');
        $fn       = Zend_Registry::get('fn');
        $tv       = Zend_Registry::get('tv');
        $cpCfg    = Zend_Registry::get('cpCfg');
        $cpUtil   = Zend_Registry::get('cpUtil');
        $dateUtil = Zend_Registry::get('dateUtil');

        $rows = '';

        set_time_limit(50000);
        ini_set('memory_limit', '512M');

        require_once("PHPExcel.php");
        include 'PHPExcel/IOFactory.php';

        $file_name = "Patient_Visit_Detail_" . date("d-m-Y") . ".xls";

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
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Visit Code');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Date');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Time');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Patient Name');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Company');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Gender');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Passport/ID');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'DOB');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Invoiced');

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

        $site_id      = $fn->getSessionParam('cp_site_id');
        $start_date   = $fn->getReqParam('start_date');
        $end_date     = $fn->getReqParam('end_date');
        $year         = $fn->getReqParam('year');
        $month        = $fn->getReqParam('month');
        $invoiced     = $fn->getReqParam('invoiced');

        if ($year == '') {
            $year = date('Y');
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
        if ($invoiced != "") {
            if ($invoiced == 'Yes'){
                $sqlAppend .= " AND pv.order_id != ''";
            } else if ($invoiced == 'No') {
                $sqlAppend .= " AND (pv.order_id = '' OR pv.order_id IS NULL)";
            }
        }

        $appendSqlPv = '';
        if ($cpCfg['cp.hasMultiUniqueSites']) {
            $appendSqlPv = "AND pv.site_id = {$site_id}";
        }

        $SQL = "
        SELECT pv.*
              ,pi.name
              ,pi.gender
              ,pi.registration_no
              ,pi.dob
              ,c.company_name
        FROM patient_visit pv
        LEFT JOIN (patient_information pi) ON (pv.patient_information_id = pi.patient_information_id)
        LEFT JOIN (company c) ON (pi.company_id = c.company_id)
        WHERE pi.name != ''
          AND pv.status != 'Cancelled'
          AND pv.check_up_date >= '{$start_date}' AND pv.check_up_date <= '{$end_date}'
          {$sqlAppend}
          {$appendSqlPv}
        ORDER BY pv.check_up_date DESC, pv.patient_visit_id DESC
        ";
        $result = $db->sql_query($SQL);
        while ($row = $db->sql_fetchrow($result)) {
            $colc = 0;
            $rowc++;

            $company = $row['company_name'];
            if ($row['company_name'] == '') {
                $company = 'Individual';
            }

            if($row['order_id']){
                $invoiced = "Yes";
            } else {
                $invoiced = "No";
            }

            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['visit_code']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['check_up_date']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['check_up_time']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['name']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $company);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['gender']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['registration_no']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['dob']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $invoiced);
        }
        
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

        $site_id      = $fn->getSessionParam('cp_site_id');
        $start_date   = $fn->getReqParam('start_date');
        $end_date     = $fn->getReqParam('end_date');
        $year         = $fn->getReqParam('year');
        $month        = $fn->getReqParam('month');
        $invoiced     = $fn->getReqParam('invoiced');

        if ($year == '') {
            $year = date('Y');
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
        if ($invoiced != "") {
            if ($invoiced == 'Yes'){
                $sqlAppend .= " AND pv.order_id != ''";
            } else if ($invoiced == 'No') {
                $sqlAppend .= " AND (pv.order_id = '' OR pv.order_id IS NULL)";
            }
        }

        $appendSqlPv = '';
        if ($cpCfg['cp.hasMultiUniqueSites']) {
            $appendSqlPv = "AND pv.site_id = {$site_id}";
        }

        $SQL = "
        SELECT pv.*
              ,pi.name
              ,pi.gender
              ,pi.registration_no
              ,pi.dob
              ,c.company_name
        FROM patient_visit pv
        LEFT JOIN (patient_information pi) ON (pv.patient_information_id = pi.patient_information_id)
        LEFT JOIN (company c) ON (pi.company_id = c.company_id)
        WHERE pi.name != ''
          AND pv.status != 'Cancelled'
          AND pv.check_up_date >= '{$start_date}' AND pv.check_up_date <= '{$end_date}'
          {$sqlAppend}
          {$appendSqlPv}
        ORDER BY pv.check_up_date DESC, pv.patient_visit_id DESC
        ";
        $result = $db->sql_query($SQL);
        $start_date_formatted = $dateUtil->formatDate($start_date, 'DD-MM-YYYY');
        $end_date_formatted   = $dateUtil->formatDate($end_date, 'DD-MM-YYYY');

        $tbl3 ='
        <table>
            <tbody>
                <tr>
                    <td><h3><u>Patient visit detail report between ' . $start_date_formatted .' and ' . $end_date_formatted .'</u></h3></td>
                </tr>
                <tr>
                    <td></td>
                </tr>
            </tbody>
        </table>
        <table border="1" width="100%" cellpadding="4">
            <thead>
                <tr bgcolor="#B6E5F9">
                    <th width="10%"><strong>Visit Code</strong></th>
                    <th width="14%"><strong>Date</strong></th>
                    <th width="11%"><strong>Time</strong></th>
                    <th width="18%"><strong>Patient Name</strong></th>
                    <th width="13%"><strong>Company</strong></th>
                    <th width="12%"><strong>Passport/ID</strong></th>
                    <th width="14%"><strong>DOB</strong></th>
                    <th width="8%"><strong>Invoiced</strong></th>
                </tr>
            </thead>
            <tbody>
        ';

        $count = 1;
        while ($row = $db->sql_fetchrow($result)) {
            $check_up_date = $dateUtil->formatDate($row['check_up_date'], 'DD-MM-YYYY');
            $dob = $dateUtil->formatDate($row['dob'], 'DD-MM-YYYY');

            $company = $row['company_name'];
            if ($row['company_name'] == '') {
                $company = 'Individual';
            }

            if($row['order_id']){
                $invoiced = "Yes";
            } else {
                $invoiced = "No";
            }

            $tbl3 = $tbl3.'
            <tr>
                <td width="10%">'.$row['visit_code'].'</td>
                <td width="14%">'.$check_up_date.'</td>
                <td width="11%">'.$row['check_up_time'].'</td>
                <td width="18%">'.$row['name'].'</td>
                <td width="13%">'.$company.'</td>
                <td width="12%">'.$row['registration_no'].'</td>
                <td width="14%">'.$dob.'</td>
                <td width="8%">'.$invoiced.'</td>
            </tr>
            ';
            $count++;
        }

        $tbl3 = $tbl3.'</tbody></table>';

        $pdf->ln(4);
        $pdf->writeHTML($tbl3, true, false, false, false, '');
        $download_title = "Patient_Visit_Detail_" . date("d-m-Y") .'.pdf';
        $pdf->Output($download_title, 'I');
    }
}