<?
class CP_Admin_Widgets_Labsg_PatientVisitSummary_Model extends CP_Common_Lib_WidgetModelAbstract
{
    /**
     *
     */
    function getSQL(){
        $cpCfg = Zend_Registry::get('cpCfg');

        $SQL = "
        SELECT pv.check_up_date
              ,DATE_FORMAT(pv.check_up_date, '%W') AS day
              ,count(a.appointment_id) AS appointment_fixed
        FROM patient_visit pv
        LEFT JOIN (appointment a) ON (a.appointment_id = pv.appointment_id)
        ";
        return $SQL;
    }

    /**
     *
     */
    function setSearchVar() {
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');

        $searchVar = $this->searchVar;
        $searchVar->mainTableAlias = 'pv';

        $start_date   = $fn->getReqParam('start_date');
        $end_date     = $fn->getReqParam('end_date');
        $year         = $fn->getReqParam('year');
        $month        = $fn->getReqParam('month');

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

        if($tv['module'] == 'common_dashboard'){
            $monthVal   = date('m');
            $yearVal    = date('Y');
            $start_date = $yearVal . '-' . $monthVal . '-' . '01';
            $end_date   = $yearVal . '-' . $monthVal . '-' . '31';
        }

        $searchVar->sqlSearchVar[] = "pv.status != 'Cancelled'";
        $searchVar->sqlSearchVar[] = "pv.check_up_date >= '{$start_date}' AND pv.check_up_date <= '{$end_date}'";
        $searchVar->groupBy = "pv.check_up_date";
    }

    /**
     *
     * @param <type> $SQL
     * @return <type>
     */
    function getDataArray() {
        $modelHelper = Zend_Registry::get('modelHelper');

        $dataArray = $modelHelper->getWidgetDataArray($this->controller, 'labsg_patientVisitSummary');
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

        $file_name = "Patient_Visit_Summary_" . date("d-m-Y") . ".xls";

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
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'No of Fixed Appoinment');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Turn Up Patient');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Did Not Turn Up');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Walk In Patient');
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

        $totalAppFixed = 0;
        $totalPatVisited = 0;
        $totalPatNotVisited = 0;
        $totalWalkIn = 0;
        $totalOverAll = 0;

        $start_date   = $fn->getReqParam('start_date');
        $end_date     = $fn->getReqParam('end_date');
        $year         = $fn->getReqParam('year');
        $month        = $fn->getReqParam('month');
        $site_id      = $fn->getSessionParam('cp_site_id');

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

        $appendSql = '';
        if ($cpCfg['cp.hasMultiUniqueSites']) {
            $appendSql = "AND pv.site_id = {$site_id}";
        }

        $SQL = "
        SELECT pv.check_up_date
              ,DATE_FORMAT(pv.check_up_date, '%W') AS day
              ,count(a.appointment_id) AS appointment_fixed
        FROM patient_visit pv
        LEFT JOIN (appointment a) ON (a.appointment_id = pv.appointment_id)
        WHERE pv.check_up_date >= '{$start_date}' AND pv.check_up_date <= '{$end_date}'
          AND pv.status != 'Cancelled'
          {$appendSql}
        GROUP BY pv.check_up_date
        ";
        $result = $db->sql_query($SQL);
        while ($row = $db->sql_fetchrow($result)) {
            $colc = 0;
            $rowc++;

            $SqlAppointment ="
            SELECT count(pv.patient_visit_id) AS patients_visited
            FROM patient_visit pv
            WHERE pv.record_type = 'By Appointment'
              AND pv.check_up_date = '{$row['check_up_date']}'
              AND pv.status != 'Cancelled'
              {$appendSql}
            ";
            $resultAppointment = $db->sql_query($SqlAppointment);
            $rowAp = $db->sql_fetchrow($resultAppointment);

            $SqlWalkIn ="
            SELECT count(pv.patient_visit_id) AS patients_walkin
            FROM patient_visit pv
            WHERE pv.record_type = 'Walk In'
              AND pv.check_up_date = '{$row['check_up_date']}'
              AND pv.status != 'Cancelled'
              {$appendSql}
            ";
            $resultWalkIn = $db->sql_query($SqlWalkIn);
            $rowWI = $db->sql_fetchrow($resultWalkIn);

            $patient_not_visited = $row['appointment_fixed'] - $rowAp['patients_visited'];

            $total = $rowAp['patients_visited'] + $rowWI['patients_walkin'];

            $check_up_date = $fn->getCPDate($row['check_up_date'],"d-m-Y");

            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $check_up_date);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['day']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['appointment_fixed']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $rowAp['patients_visited']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $patient_not_visited);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $rowWI['patients_walkin']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $total);

            $totalAppFixed += $row['appointment_fixed'];
            $totalPatVisited += $rowAp['patients_visited'];
            $totalPatNotVisited += $patient_not_visited;
            $totalWalkIn += $rowWI['patients_walkin'];
            $totalOverAll += $total;
        }

        $colc = 0;
        $rowc++;
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Total');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $totalAppFixed);
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $totalPatVisited);
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $totalPatNotVisited);
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $totalWalkIn);
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $totalOverAll);
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

        $totalAppFixed = 0;
        $totalPatVisited = 0;
        $totalPatNotVisited = 0;
        $totalWalkIn = 0;
        $totalOverAll = 0;

        $start_date   = $fn->getReqParam('start_date');
        $end_date     = $fn->getReqParam('end_date');
        $year         = $fn->getReqParam('year');
        $month        = $fn->getReqParam('month');
        $site_id      = $fn->getSessionParam('cp_site_id');

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

        $appendSql = '';
        if ($cpCfg['cp.hasMultiUniqueSites']) {
            $appendSql = "AND pv.site_id = {$site_id}";
        }

        $SQL = "
        SELECT pv.check_up_date
              ,DATE_FORMAT(pv.check_up_date, '%W') AS day
              ,count(a.appointment_id) AS appointment_fixed
        FROM patient_visit pv
        LEFT JOIN (appointment a) ON (a.appointment_id = pv.appointment_id)
        WHERE pv.check_up_date >= '{$start_date}' AND pv.check_up_date <= '{$end_date}'
          AND pv.status != 'Cancelled'
          {$appendSql}
        GROUP BY pv.check_up_date
        ";
        $result = $db->sql_query($SQL);
        $start_date_formatted = $dateUtil->formatDate($start_date, 'DD-MM-YYYY');
        $end_date_formatted = $dateUtil->formatDate($end_date, 'DD-MM-YYYY');

        $tbl3 ='
        <table>
            <tbody>
                <tr>
                    <td><h3><u>Patient visit summary between ' . $start_date_formatted .' and ' . $end_date_formatted .'</u></h3></td>
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
                    <th width="15%"><strong>Date</strong></th>
                    <th width="20%"><strong>Day</strong></th>
                    <th width="15%" align="center"><strong>No of Fixed Appoinment</strong></th>
                    <th width="11%" align="center"><strong>Turn Up Patient</strong></th>
                    <th width="11%" align="center"><strong>Did Not Turn Up</strong></th>
                    <th width="10%" align="center"><strong>Walk In Patient</strong></th>
                    <th width="11%" align="center"><strong>Total</strong></th>
                </tr>
            </thead>
            <tbody>
        ';
        $count = 1;
        while ($row = $db->sql_fetchrow($result)) {            
            $SqlAppointment ="
            SELECT count(pv.patient_visit_id) AS patients_visited
            FROM patient_visit pv
            WHERE pv.record_type = 'By Appointment'
              AND pv.check_up_date = '{$row['check_up_date']}'
              AND pv.status != 'Cancelled'
              {$appendSql}
            ";
            $resultAppointment = $db->sql_query($SqlAppointment);
            $rowAp = $db->sql_fetchrow($resultAppointment);

            $SqlWalkIn ="
            SELECT count(pv.patient_visit_id) AS patients_walkin
            FROM patient_visit pv
            WHERE pv.record_type = 'Walk In'
              AND pv.check_up_date = '{$row['check_up_date']}'
              AND pv.status != 'Cancelled'
              {$appendSql}
            ";
            $resultWalkIn = $db->sql_query($SqlWalkIn);
            $rowWI = $db->sql_fetchrow($resultWalkIn);

            $patient_not_visited = $row['appointment_fixed'] - $rowAp['patients_visited'];
            $total = $rowAp['patients_visited'] + $rowWI['patients_walkin'];
            $check_up_date = $fn->getCPDate($row['check_up_date'],"d-m-Y");

            $tbl3 .= '
            <tr>
                <td width="7%" align="center">' . $count . '</td>
                <td width="15%">' . $check_up_date . '</td>
                <td width="20%">' . $row['day'] . '</td>
                <td width="15%" align="center">' . $row['appointment_fixed'] . '</td>
                <td width="11%" align="center">' . $rowAp['patients_visited'] . '</td>
                <td width="11%" align="center">' . $patient_not_visited . '</td>
                <td width="10%" align="center">' . $rowWI['patients_walkin'] . '</td>
                <td width="11%" align="center">' . $total . '</td>
            </tr>
            ';
            $count++;
            $totalAppFixed += $row['appointment_fixed'];
            $totalPatVisited += $rowAp['patients_visited'];
            $totalPatNotVisited += $patient_not_visited;
            $totalWalkIn += $rowWI['patients_walkin'];
            $totalOverAll += $total;
        }

        $tbl3 = $tbl3 .'
        <tr bgcolor="#B6E5F9">
            <td colspan="3" width="42%" align="right"><strong>Total</strong></td>
            <td width="15%" align="center"><strong>' . $totalAppFixed . '</strong></td>
            <td width="11%" align="center"><strong>' . $totalPatVisited . '</strong></td>
            <td width="11%" align="center"><strong>' . $totalPatNotVisited . '</strong></td>
            <td width="10%" align="center"><strong>' . $totalWalkIn . '</strong></td>
            <td width="11%" align="center"><strong>' . $totalOverAll . '</strong></td>
        </tr>
        </tbody>
        </table>';

        $pdf->ln(4);
        $pdf->writeHTML($tbl3, true, false, false, false, '');
        $download_title = "Patient-visit-summary" . date("d-m-Y") .'.pdf';
        $pdf->Output($download_title, 'I');
    }
}