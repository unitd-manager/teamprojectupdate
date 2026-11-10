<?
class CP_Admin_Widgets_ManPower_OpportunityPositionReport_Model extends CP_Common_Lib_WidgetModelAbstract
{
    /**
     * Opportunity Position | Month | No. of Oppurtunity
     */
    function getSQL(){
        $SQL = "
        SELECT DISTINCT o.position
			  ,o.creation_date
              ,(SELECT COUNT(*)
                FROM opportunity op
                WHERE o.position = op.position
                ) AS no_of_oppurtunity
              ,(SELECT SUM(no_of_position)
                FROM opportunity op
                WHERE o.position = op.position
                ) AS no_of_positions
        FROM opportunity o
        ";

        return $SQL;
    }

    /**
     *
     */
    function setSearchVar() {
        $fn = Zend_Registry::get('fn');

        $start_date  = $fn->getReqParam('start_date');
        $end_date    = $fn->getReqParam('end_date');
        $month       = $fn->getReqParam('month');
        $year        = $fn->getReqParam('year');
        $status      = $fn->getReqParam('status');

        $searchVar   = $this->searchVar;
        $searchVar->mainTableAlias = 'o';

        $current_year= date('Y');

        $searchVar->sqlSearchVar[] = "o.position != ''";

        $current_year  = date('Y');
        $current_month = date('m');
        $start_month   = '01';

        // If both Year and Month are empty, search from 01 Jan of Current year to Current Date
        if ($month == '' && $year == '') {
            $startDate = $current_year . '-' . $start_month   . '-' . '01';
            $endDate   = date('Y-m-d');

            $searchVar->sqlSearchVar[] = "o.creation_date BETWEEN '{$startDate}' AND '{$endDate}'";
        }

        // If both Year has value and Month is empty, search from 01 Jan of selected year to Last date of selected year and current month
        if ($year != '' && $month == ''){

            $startDate = $year . '-' . $start_month   . '-01';
            if ($year >= $current_year) {
                $endDate   = $year . '-' . $current_month . '-31';
            } else {
                $endDate   = $year . '-12-31';
            }
            $searchVar->sqlSearchVar[] = "o.creation_date BETWEEN '{$startDate}' AND '{$endDate}'";
        }

        if ($month != ''){
            if ($year != '') {
                $startDate = $year . '-' . $month . '-' . '01';
                $endDate   = $year . '-' . $month . '-' . '31';
            } else {
                $year = date('Y');
                $startDate = $year . '-' . $month . '-' . '01';
                $endDate   = $year . '-' . $month . '-' . '31';
            }
            $searchVar->sqlSearchVar[] = "o.creation_date BETWEEN '{$startDate}' AND '{$endDate}'";
        }

       $searchVar->groupBy = 'o.position';
       $searchVar->sortOrder = 'o.creation_date';
    }
    /**
     *
     */


    function getDataArray() {
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');

        $modelHelper = Zend_Registry::get('modelHelper');
        $dataArray = $modelHelper->getWidgetDataArray($this->controller, 'manPower_opportunityPositionReport');

        $this->dataArray = $dataArray ;
        return $dataArray;
    }

    /**
     *
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

        $file_name = "OpportunityByMonth_" . date("d-m-Y") . ".xls";

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

        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Serial No');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Position');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Month');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'No. of Oppurtunity');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'No. of Position');
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

        $sqlAppend = '';
        $start_date  = $fn->getReqParam('start_date');
        $end_date    = $fn->getReqParam('end_date');
        $month       = $fn->getReqParam('month');
        $year        = $fn->getReqParam('year');
        $status      = $fn->getReqParam('status');

        $current_year= date('Y');
        $current_month = date('m');
        $start_month   = '01';

        if ($month == '' && $year == '') {
            $startDate = $current_year . '-' . $start_month   . '-' . '01';
            $endDate   = date('Y-m-d');

            $sqlAppend .= "AND o.creation_date BETWEEN '{$startDate}' AND '{$endDate}'";
        }

        if ($year != '' && $month == ''){
            $startDate = $year . '-' . $start_month   . '-01';
            if ($year >= $current_year) {
                $endDate   = $year . '-' . $current_month . '-31';
            } else {
                $endDate   = $year . '-12-31';
            }

            $sqlAppend .= "AND o.creation_date BETWEEN '{$startDate}' AND '{$endDate}'";
        }

        if ($month != ''){
            if ($year != '') {
                $startDate = $year . '-' . $month . '-' . '01';
                $endDate   = $year . '-' . $month . '-' . '31';
            } else {
                $year = date('Y');
                $startDate = $year . '-' . $month . '-' . '01';
                $endDate   = $year . '-' . $month . '-' . '31';
            }
            $sqlAppend .= "AND o.creation_date BETWEEN '{$startDate}' AND '{$endDate}'";
        }

        $SQL = "
        SELECT DISTINCT o.position
              ,o.creation_date
              ,(SELECT COUNT(*)
                FROM opportunity op
                WHERE o.position = op.position
                ) AS no_of_oppurtunity
              ,(SELECT SUM(no_of_position)
                FROM opportunity op
                WHERE o.position = op.position
                ) AS no_of_positions
        FROM opportunity o
        WHERE o.position != ''
        {$sqlAppend}
        GROUP BY o.position
        ORDER BY o.creation_date
        ";

        $result = $db->sql_query($SQL);

        $serial_no    = 0;
        $total_amount = 0;
        while ($row = $db->sql_fetchrow($result)) {
            $serial_no += 1;
            $creation_date = $fn->getCPDate($row['creation_date'], 'M');

            $colc = 0;
            $rowc++;

            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $serial_no);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['position']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $creation_date);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['no_of_oppurtunity']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['no_of_positions']);
        }

        $colc = 0;
        $rowc++;
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');

        $actSheet->getStyle("A{$rowc}:D{$rowc}")->applyFromArray($headStyle);

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
    }
}