<?
class CP_Admin_Widgets_Hms_VisitByDay_Model extends CP_Common_Lib_WidgetModelAbstract
{
    /**
     *
     */
    function getSQL(){
        $cpCfg = Zend_Registry::get('cpCfg');

        $SQL = "
        SELECT pv.check_up_date
              ,DATE_FORMAT(pv.check_up_date, '%W') AS day
              ,count(pv.patient_visit_id) AS patients_visited
        FROM patient_visit pv
        ";

        return $SQL;
    }

    /**
     *
     */
    function setSearchVar() {
        $fn = Zend_Registry::get('fn');
        $searchVar = $this->searchVar;
        $searchVar->mainTableAlias = 'pv';
        $cpCfg = Zend_Registry::get('cpCfg');

        $start_date     = $fn->getReqParam('start_date');
        $end_date       = $fn->getReqParam('end_date');
        $site_id        = $fn->getReqParam('site_id');
        $current_date   = date('Y-m-d');
        $month          = date('m');
        $year           = date('Y');
        $monthVal       = $fn->getReqParam('month');
        $yearVal        = $fn->getReqParam('year');

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

        if ($cpCfg['cp.hasMultiUniqueSites']) {
            if($site_id != ''){
                $searchVar->sqlSearchVar[] = "pv.site_id = {$site_id}" ;
            }
        }

        if ($monthVal != '') {
            $searchVar->sqlSearchVar[] = "DATE_FORMAT(pv.check_up_date, '%m') = '{$monthVal}'" ;
        }
        if ($yearVal != '') {
            $searchVar->sqlSearchVar[] = "DATE_FORMAT(pv.check_up_date, '%Y') = '{$yearVal}'" ;
        }
        $searchVar->sqlSearchVar[] = "pv.status != 'Cancelled'";
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
        $dataArray = $modelHelper->getWidgetDataArray($this->controller, 'hms_visitByDay');

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

        set_time_limit(50000);
        ini_set('memory_limit', '512M');

        require_once("PHPExcel.php");
        include 'PHPExcel/IOFactory.php';

        $file_name = "VisitbyDay_" . date("d-m-Y") . ".xls";

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
        $headStyle = array(
            'font' => array('bold' => true)
        );

        $actSheet->mergeCells("A{$rowc}:C{$rowc}");
        $actSheet->getStyle("A{$rowc}:C{$rowc}")->applyFromArray($headStyle);
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Visit by Day');

        $colc = 0;
        $rowc++;
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Date');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Day');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Total');
        /******************** FORMAT HEADER *******************/
        $lastCol    = $actSheet->getHighestColumn();
        $lastColInd = PHPExcel_Cell::columnIndexFromString($lastCol);
        $actSheet->getStyle("A2:{$lastCol}2")->applyFromArray($headStyle);

        for ($i=0; $i < $lastColInd; $i++){
            $colAlphabet = PHPExcel_Cell::stringFromColumnIndex($i);
            $actSheet->getColumnDimension($colAlphabet)->setAutoSize(true);
        }

        $start_date     = $fn->getReqParam('start_date');
        $end_date       = $fn->getReqParam('end_date');
        $site_id        = $fn->getReqParam('site_id');
        $current_date   = date('Y-m-d');
        $month          = date('m');
        $year           = date('Y');
        $monthVal       = $fn->getReqParam('month');
        $yearVal        = $fn->getReqParam('year');

        $appendSql = '';
        if ($start_date != '' && $end_date == '') {
            $appendSql .= " AND pv.check_up_date >= '{$start_date}' AND pv.check_up_date <= '{$current_date}'";
        } else if ($start_date == '' && $end_date != ''){
            $start_date = $year . '-' . $month . '-' . '01';
            $appendSql .= " AND pv.check_up_date >= '{$start_date}' AND pv.check_up_date <= '{$end_date}'";
        } else if ($start_date != '' && $end_date != '') {
            $appendSql .= " AND pv.check_up_date >= '{$start_date}' AND pv.check_up_date <= '{$end_date}'";
        } else if ($start_date == '' && $end_date == ''){
            $appendSql .= " AND DATE_FORMAT(pv.check_up_date, '%m') = '{$monthVal}' AND DATE_FORMAT(pv.check_up_date, '%Y') = '{$yearVal}'";
        }

        if ($cpCfg['cp.hasMultiUniqueSites']) {
            if($site_id != ''){
                $appendSql .= " AND pv.site_id = {$site_id}" ;
            }
        }

        $SQL = "
        SELECT pv.check_up_date
              ,DATE_FORMAT(pv.check_up_date, '%W') AS day
              ,count(pv.patient_visit_id) AS patients_visited
        FROM patient_visit pv
        WHERE pv.status != 'Cancelled'
        {$appendSql}
        GROUP BY pv.check_up_date
        ";
        $result = $db->sql_query($SQL);
        while ($row = $db->sql_fetchrow($result)) {
            $colc = 0;
            $rowc++;

            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['check_up_date']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['day']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['patients_visited']);
        }

        $rowc++;
        $actSheet->getStyle("A{$rowc}:C{$rowc}")->applyFromArray($headStyle);

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
    }
}