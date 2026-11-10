<?
class CP_Admin_Widgets_Hms_DutyRosterReport_Model extends CP_Common_Lib_WidgetModelAbstract
{
    /**
     *
     */
    function getSQL(){
        $fn = Zend_Registry::get('fn');
        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');

        $SQL ="
        SELECT r.work_from_time
               ,r.work_to_time
               ,r.employment_id
               ,r.site_id
               ,CONCAT_WS(' ', e.first_name, e.middle_name, e.last_name) AS employee_name
        FROM duty_roster r
        LEFT JOIN (employee e) ON (e.employee_id = r.employment_id)
        ";

        return $SQL;
    }
    /**
     *
     */
    function setSearchVar() {
        $fn = Zend_Registry::get('fn');
        $searchVar = $this->searchVar;
        $searchVar->mainTableAlias = 'r';
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
            $searchVar->sqlSearchVar[] = "r.work_date  >= '{$start_date}' AND r.work_date  <= '{$current_date}'";
        } else if ($start_date == '' && $end_date != ''){
            $start_date = $year . '-' . $month . '-' . '01';
            $searchVar->sqlSearchVar[] = "r.work_date  >= '{$start_date}' AND r.work_date  <= '{$end_date}'";
        } else if ($start_date != '' && $end_date != '') {
            $searchVar->sqlSearchVar[] = "r.work_date  >= '{$start_date}' AND r.work_date  <= '{$end_date}'";
        } else if ($monthVal == '' && $yearVal == ''){
            $start_date = $year . '-' . $month . '-' . '01';
            $end_date = $year . '-' . $month . '-' . '31';
            $searchVar->sqlSearchVar[] = "r.work_date  >= '{$start_date}' AND r.work_date  <= '{$end_date}'";
        }

        if ($monthVal != '') {
            $searchVar->sqlSearchVar[] = "DATE_FORMAT(r.work_date , '%m') = '{$monthVal}'" ;
        }
        if ($yearVal != '') {
            $searchVar->sqlSearchVar[] = "DATE_FORMAT(r.work_date , '%Y') = '{$yearVal}'" ;
        }
        $searchVar->groupBy = "r.employment_id";
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
        $dataArray = $modelHelper->getWidgetDataArray($this->controller, 'hms_dutyRosterReport');

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

        $file_name = "DutyRosterReport_" . date("d-m-Y") . ".xls";

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

        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Dr Name');

        $SQLSite = "
        SELECT title
        FROM site
        ";
        $resultSite     = $db->sql_query($SQLSite);
        $LocationTitle = "";
        while ($rowSite = $db->sql_fetchrow($resultSite)) {
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $rowSite['title']);
        }

        /*if($cpCfg['cp.hasMultiUniqueSites']){
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Location');
         }*/

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

        $start_date     = $fn->getReqParam('start_date');
        $end_date       = $fn->getReqParam('end_date');
        $site_id        = $fn->getReqParam('site_id');
        $current_date   = date('Y-m-d');
        $month          = date('m');
        $year           = date('Y');
        $monthVal       = $fn->getReqParam('month');
        $yearVal        = $fn->getReqParam('year');

        if ($start_date != '' && $end_date == '') {
            $startDateAppendSql = "r.work_date >= '{$start_date}' AND r.work_date <= '{$current_date}'";
        } else if ($start_date == '' && $end_date != ''){
            $start_date = $year . '-' . $month . '-' . '01';
            $startDateAppendSql = "r.work_date >= '{$start_date}' AND r.work_date <= '{$end_date}'";
        } else if ($start_date != '' && $end_date != '') {
            $startDateAppendSql = "r.work_date >= '{$start_date}' AND r.work_date <= '{$end_date}'";
        } else {
            $start_date = $year . '-' . $month . '-' . '01';
            $end_date = $year . '-' . $month . '-' . '31';
            $startDateAppendSql = "r.work_date >= '{$start_date}' AND r.work_date <= '{$end_date}'";
        }

        $SQL = "
        SELECT  r.work_from_time
               ,r.work_to_time
               ,r.employment_id
               ,r.site_id
               ,CONCAT_WS(' ', e.first_name, e.middle_name, e.last_name) AS employee_name
        FROM duty_roster r
        LEFT JOIN (employee e) ON (e.employee_id = r.employment_id)
        WHERE {$startDateAppendSql}
        GROUP BY r.employment_id
        ORDER BY r.employment_id ASC
        ";

        $result = $db->sql_query($SQL);
        //$row = $db->sql_fetchrow($result);
        while ($row = $db->sql_fetchrow($result)) {
            $colc = 0;
            $rowc++;

            $SQLSite = "
            SELECT site_id
            FROM site s
            ";
            $resultSite     = $db->sql_query($SQLSite);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['employee_name']);
            while ($rowSite = $db->sql_fetchrow($resultSite)) {

                $SQLTimings ="
                SELECT s.site_id
                      ,CONCAT_WS(' - ', LOWER(DATE_FORMAT(r.work_from_time, '%l:%i %p'))
                      , LOWER(DATE_FORMAT(r.work_to_time, '%l:%i %p'))) AS work_time
                FROM site s
                LEFT JOIN (duty_roster r) ON (r.site_id = s.site_id)
                WHERE r.employment_id = {$row['employment_id']}
                AND r.site_id = {$rowSite['site_id']}
                GROUP BY r.work_from_time, r.work_to_time
                ";

                $resultTimings     = $db->sql_query($SQLTimings);
                $timings = '';
                while($rowTimings = $db->sql_fetchrow($resultTimings)){
                    $timings .= "{$rowTimings['work_time']}\n";
                }

                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $timings);

            }


        }

        $rowc++;
        $actSheet->getStyle("A{$rowc}:D{$rowc}")->applyFromArray($headStyle);

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
    }

}