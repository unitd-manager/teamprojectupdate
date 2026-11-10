<?
class CP_Admin_Widgets_ManPower_OpportunityByMonthReport_Model extends CP_Common_Lib_WidgetModelAbstract
{
    /**
     *
     */
    //Opportunity Title | Value | Staff Name
    function getSQL(){
        $SQL = "
        SELECT o.title
              ,o.estimated_value
              ,o.position
              ,o.no_of_position
              ,CONCAT_WS(' ', s.first_name, s.last_name) AS staff_name
        FROM opportunity o
        LEFT JOIN (opportunity_staff os) ON (o.opportunity_id = os.opportunity_id)
        LEFT JOIN (staff s) ON (os.staff_id = s.staff_id)
        ";

        return $SQL;
    }

    /**
     *
     */
    function setSearchVar() {
        $fn = Zend_Registry::get('fn');

        $month      = $fn->getReqParam('month');
        $year       = $fn->getReqParam('year');
        $staff_id   = $fn->getReqParam('staff_id');

        $searchVar     = $this->searchVar;
        $searchVar->mainTableAlias = 'o';

        $current_year  = date('Y');
        $current_month = date('m');


        if ($month == '' && $year == '') {
            $startMonth = $current_year . '-' . $current_month . '-' . '01';
            $endMonth   = $current_year . '-' . $current_month . '-' . '31';

            $searchVar->sqlSearchVar[] = "o.enquiry_date BETWEEN '{$startMonth}' AND '{$endMonth}'";
        }

        if ($year != ''){
            $startYear = $year . '-' . '01-01';
            $endYear   = $year . '-' . '12-31';

            $searchVar->sqlSearchVar[] = "o.enquiry_date BETWEEN '{$startYear}' AND '{$endYear}'";
        }

        if ($month != ''){
            if ($year != '') {
                $startMonth = $year . '-' . $month . '-' . '01';
                $endMonth   = $year . '-' . $month . '-' . '31';
            } else {
                $year = date('Y');
                $startMonth = $year . '-' . $month . '-' . '01';
                $endMonth   = $year . '-' . $month . '-' . '31';
            }
            $searchVar->sqlSearchVar[] = "o.enquiry_date BETWEEN '{$startMonth}' AND '{$endMonth}'";
        }

        if ($staff_id != ''){
            $searchVar->sqlSearchVar[] = "s.staff_id = '{$staff_id}'";
        }

        $searchVar->sortOrder = 'o.title ASC';
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
        $dataArray = $modelHelper->getWidgetDataArray($this->controller, 'manPower_opportunityByMonthReport');

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
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Opportunity Title');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Position');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'No. of Position');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Staff Name');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Value');
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
        $month      = $fn->getReqParam('month');
        $year       = $fn->getReqParam('year');
        $staff_id   = $fn->getReqParam('staff_id');

        $current_year  = date('Y');
        $current_month = date('m');

        if ($month == '' && $year == '') {
            $startMonth = $current_year . '-' . $current_month . '-' . '01';
            $endMonth   = $current_year . '-' . $current_month . '-' . '31';

            $sqlAppend .= "AND o.enquiry_date BETWEEN '{$startMonth}' AND '{$endMonth}'";
        }

        if ($year != ''){
            $startYear = $year . '-' . '01-01';
            $endYear   = $year . '-' . '12-31';

            $sqlAppend .= "AND o.enquiry_date BETWEEN '{$startYear}' AND '{$endYear}'";
        }

        if ($month != ''){
            if ($year != '') {
                $startMonth = $year . '-' . $month . '-' . '01';
                $endMonth   = $year . '-' . $month . '-' . '31';
            } else {
                $year = date('Y');
                $startMonth = $year . '-' . $month . '-' . '01';
                $endMonth   = $year . '-' . $month . '-' . '31';
            }
            $sqlAppend .= "AND o.enquiry_date BETWEEN '{$startMonth}' AND '{$endMonth}'";
        }

        if ($staff_id != ''){
            $sqlAppend .= "AND s.staff_id = '{$staff_id}'";
        }

        $SQL = "
        SELECT o.title
              ,o.estimated_value
              ,o.position
              ,o.no_of_position
              ,CONCAT_WS(' ', s.first_name, s.last_name) AS staff_name
        FROM opportunity o
        LEFT JOIN (opportunity_staff os) ON (o.opportunity_id = os.opportunity_id)
        LEFT JOIN (staff s) ON (os.staff_id = s.staff_id)
        WHERE o.opportunity_id != ''
        {$sqlAppend}
        ORDER BY o.title ASC
        ";

        $result = $db->sql_query($SQL);

        $serial_no    = 0;
        $total_amount = 0;
        while ($row = $db->sql_fetchrow($result)) {
            $serial_no += 1;

            $colc = 0;
            $rowc++;

            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $serial_no);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['title']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['position']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['no_of_position']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['staff_name']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['estimated_value']);

            $total_amount += $row['estimated_value'];
        }

        $colc = 0;
        $rowc++;
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Total');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $total_amount);

        $actSheet->getStyle("A{$rowc}:F{$rowc}")->applyFromArray($headStyle);

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
    }
}