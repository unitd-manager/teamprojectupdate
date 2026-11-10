<?
class CP_Admin_Widgets_Hms_PatientVisitSummary_Model extends CP_Common_Lib_WidgetModelAbstract
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
              ,ev.employee_id
        FROM employee_visit ev
        LEFT JOIN (patient_visit pv) ON (pv.patient_visit_id = ev.patient_visit_id)
        LEFT JOIN (appointment a) ON (a.appointment_id = pv.appointment_id)
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
        $searchVar->mainTableAlias = 'pv';

        $start_date     = $fn->getReqParam('start_date');
        $end_date       = $fn->getReqParam('end_date');
        $current_date   = date('Y-m-d');
        $month          = date('m');
        $year           = date('Y');
        $employee_id    = $fn->getReqParam('employee_id');
        $monthVal       = $fn->getReqParam('month');
        $yearVal        = $fn->getReqParam('year');
        $site_id        = $fn->getReqParam('site_id');
        if($tv['module'] == 'common_dashboard'){
            /*$start_date = date('Y-m-d', mktime (0,0,0,date("m"), date("d"), date("Y")));
            $end_date = $current_date;*/
            $start_date = $year . '-' . $month . '-' . '01';
            $end_date = $year . '-' . $month . '-' . '31';
        }

        if ($start_date != '' && $end_date == '') {
            $searchVar->sqlSearchVar[] = "pv.check_up_date >= '{$start_date}' AND pv.check_up_date <= '{$current_date}'";
        } else if ($start_date == '' && $end_date != ''){
            $start_date = $year . '-' . $month . '-' . '01';
            $searchVar->sqlSearchVar[] = "pv.check_up_date >= '{$start_date}' AND pv.check_up_date <= '{$end_date}'";
        } else if ($start_date != '' && $end_date != '') {
            $searchVar->sqlSearchVar[] = "pv.check_up_date >= '{$start_date}' AND pv.check_up_date <= '{$end_date}'";
        } else if ($monthVal == '' && $yearVal == ''){
            $start_date = $year . '-' . $month . '-' . '01';
            $end_date = $year . '-' . $month . '-' . '31';
            $searchVar->sqlSearchVar[] = "pv.check_up_date >= '{$start_date}' AND pv.check_up_date <= '{$end_date}'";
        }

        if ($employee_id != '') {
            $searchVar->sqlSearchVar[] = "ev.employee_id = {$employee_id}" ;
        }
        if ($monthVal != '') {
            $searchVar->sqlSearchVar[] = "DATE_FORMAT(pv.check_up_date, '%m') = '{$monthVal}'" ;
        }
        if ($yearVal != '') {
            $searchVar->sqlSearchVar[] = "DATE_FORMAT(pv.check_up_date, '%Y') = '{$yearVal}'" ;
        }

        if ($cpCfg['cp.hasMultiUniqueSites']) {
            if($site_id != ''){
                $searchVar->sqlSearchVar[] = "pv.site_id = {$site_id}" ;
            }
        }

        $searchVar->sqlSearchVar[] = "pv.status != 'Cancelled'";
        //$searchVar->sortOrder = "c.company_name ASC";
        $searchVar->groupBy = "pv.check_up_date";
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
        $dataArray = $modelHelper->getWidgetDataArray($this->controller, 'hms_patientVisitSummary');

        $this->dataArray = $dataArray;
        return $this->dataArray;
    }

    /**
     */
    function getExportToExcel(){
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

        $file_name = "PatientVisit__" . date("d-m-Y") . ".xls";

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

        $actSheet = &$objPHPExcel->getActiveSheet();

        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Date');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Day');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Dr In Charge');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Appoinment(Fixed)');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Patient Visited');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Patient Not Visited');

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

        if ($start_date != '' && $end_date == '') {
            $startDateAppendSql = "pv.check_up_date >= '{$start_date}' AND pv.check_up_date <= '{$current_date}'";
        } else if ($start_date == '' && $end_date != ''){
            $start_date = $year . '-' . $month . '-' . '01';
            $startDateAppendSql = "pv.check_up_date >= '{$start_date}' AND pv.check_up_date <= '{$end_date}'";
        } else if ($start_date != '' && $end_date != '') {
            $startDateAppendSql = "pv.check_up_date >= '{$start_date}' AND pv.check_up_date <= '{$end_date}'";
        } else {
            $start_date = $year . '-' . $month . '-' . '01';
            $end_date = $year . '-' . $month . '-' . '31';
            $startDateAppendSql = "pv.check_up_date >= '{$current_date}' AND pv.check_up_date <= '{$current_date}'";
        }

        $SQL = "
        SELECT pv.check_up_date
              ,DATE_FORMAT(pv.check_up_date, '%W') AS day
              ,count(ev.patient_visit_id) AS patients_visited
              ,count(a.appointment_id) AS appointment_fixed
              ,ev.employee_id
        FROM employee_visit ev
        LEFT JOIN (patient_visit pv) ON (pv.patient_visit_id = ev.patient_visit_id)
        LEFT JOIN (appointment a) ON (a.appointment_id = pv.appointment_id)
        WHERE {$startDateAppendSql}
        AND pv.status != 'Cancelled'
        GROUP BY pv.check_up_date
        ";

        $result = $db->sql_query($SQL);

        while ($row = $db->sql_fetchrow($result)) {
            $colc = 0;
            $rowc++;

            $patient_not_visited = $row['appointment_fixed'] - $row['patients_visited'];
            $SQL = "
            SELECT e.employee_name
            FROM employee e
            LEFT JOIN (employee_visit ev) ON (ev.employee_id = e.employee_id)
            LEFT JOIN (patient_visit pv) ON (pv.patient_visit_id = ev.patient_visit_id)
            WHERE pv.check_up_date = '{$row['check_up_date']}'
            AND pv.status != 'Cancelled'
            GROUP BY e.employee_name
            ";
            $result = $db->sql_query($SQL);

            $employee_name = '';

            while ($rowEM = $db->sql_fetchrow($result)) {
                $employee_name .= $rowEM['employee_name'].', ';
            }
            $employee_name = rtrim($employee_name, ', ');

            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['check_up_date']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['day']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $employee_name);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['appointment_fixed']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['patients_visited']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $patient_not_visited);
        }

        $rowc++;
        $actSheet->getStyle("A{$rowc}:D{$rowc}")->applyFromArray($headStyle);

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
    }

}