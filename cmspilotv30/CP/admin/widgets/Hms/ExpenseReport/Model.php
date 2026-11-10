<?
class CP_Admin_Widgets_Hms_ExpenseReport_Model extends CP_Common_Lib_WidgetModelAbstract
{
    /**
     *
     */
    function getSQL(){
        $fn = Zend_Registry::get('fn');   
        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');
        
        $SQL ="
        SELECT e.*
               ,s.title AS from_location
        FROM expense e
        LEFT JOIN site s ON (s.site_id = e.site_id)
        ";

        return $SQL;
    }
    /**
     *
     */
    function setSearchVar() {
        $fn = Zend_Registry::get('fn');
        $searchVar = $this->searchVar;
        $searchVar->mainTableAlias = 'e';
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
            $searchVar->sqlSearchVar[] = "e.creation_date  >= '{$start_date}' AND e.creation_date  <= '{$current_date}'";
        } else if ($start_date == '' && $end_date != ''){
            $start_date = $year . '-' . $month . '-' . '01';
            $searchVar->sqlSearchVar[] = "e.creation_date  >= '{$start_date}' AND e.creation_date  <= '{$end_date}'";
        } else if ($start_date != '' && $end_date != '') {
            $searchVar->sqlSearchVar[] = "e.creation_date  >= '{$start_date}' AND e.creation_date  <= '{$end_date}'";
        } else if ($monthVal == '' && $yearVal == ''){
            $start_date = $year . '-' . $month . '-' . '01';
            $end_date = $year . '-' . $month . '-' . '31';
            $searchVar->sqlSearchVar[] = "e.creation_date  >= '{$start_date}' AND e.creation_date  <= '{$end_date}'";
        }

        if ($cpCfg['cp.hasMultiUniqueSites']) {
            if($site_id != ''){
                $searchVar->sqlSearchVar[] = "e.site_id = {$site_id}" ;
            }
        }

        if ($monthVal != '') {
            $searchVar->sqlSearchVar[] = "DATE_FORMAT(e.creation_date , '%m') = '{$monthVal}'" ;
        }
        if ($yearVal != '') {
            $searchVar->sqlSearchVar[] = "DATE_FORMAT(e.creation_date , '%Y') = '{$yearVal}'" ;
        }
        $searchVar->sqlSearchVar[] = "e.status != 'Cancelled'";
        $searchVar->groupBy = "e.creation_date ";
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
        $dataArray = $modelHelper->getWidgetDataArray($this->controller, 'hms_expenseReport');

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

        $file_name = "ExpenseReport_" . date("d-m-Y") . ".xls";

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

        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'S.No');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Date');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Title');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Status');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Location');
        if($cpCfg['cp.hasMultiUniqueSites']){
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Location');
         }

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

        $staff_id       = $fn->getReqParam('staff_id');
        $start_date     = $fn->getReqParam('start_date');
        $end_date       = $fn->getReqParam('end_date');
        $location_id    = $fn->getReqParam('location_id');
        $current_month = date('m');
        $current_year  = date('Y');


        $appendStaffSQL = '';

        if ($start_date != '' && $end_date == '') {
            $appendFollowUpDateSQL = "e.creation_date BETWEEN '{$start_date}' AND '{$end_date}'";
        } else if ($start_date == '' && $end_date != ''){
            $appendFollowUpDateSQL = "e.creation_date BETWEEN '{$start_date}' AND '{$end_date}'";
        } else if ($start_date != '' && $end_date != '') {
            $appendFollowUpDateSQL = "e.creation_date BETWEEN '{$start_date}' AND '{$end_date}'";
       } else {
            $start_date     = $current_year . '-' . $current_month . '-' . '01';
            $end_date       = $current_year . '-' . $current_month . '-' . '31';
            $appendFollowUpDateSQL = "e.creation_date BETWEEN '{$start_date}' AND '{$end_date}'";

       }


        $count =1;

        $SQL = "
        SELECT e.*
               ,s.title AS from_location
        FROM expense e
        LEFT JOIN site s ON (s.site_id = e.site_id)
        ";

        $result = $db->sql_query($SQL);
        while ($row = $db->sql_fetchrow($result)) {


            $colc = 0;
            $rowc++;

            //$follow_up_date = $fn->getCPDate($row['follow_up_date'],"d-m-Y");
            $expense_date = $fn->getCPDate($row['creation_date'],"d-m-Y");

            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $count);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $expense_date);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['title']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['status']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['from_location']);

            $count++;

        }

        $rowc++;
        $actSheet->getStyle("A{$rowc}:D{$rowc}")->applyFromArray($headStyle);

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
    }

}