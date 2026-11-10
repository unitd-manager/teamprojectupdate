<?
class CP_Admin_Widgets_AceIms_TraineeByMonth_Model extends CP_Common_Lib_WidgetModelAbstract
{
    /**
     *
     */
    function getSQL(){
        $SQL = "
        SELECT DATE_FORMAT(c.creation_date, '%M') AS month
			  ,(SELECT COUNT(*)
				FROM course_contact cc
				WHERE cc.batch_id = b.batch_id
                AND b.status = 'Current'
				) AS attendee_count
        FROM course_contact c
        JOIN batch b ON (c.batch_id = b.batch_id)
        ";

        $SQL = "
        SELECT COUNT(*) AS attendee_count
              ,i.invoice_month
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
        
        $year      = $fn->getReqParam('year');
        /*$start_date = $fn->getReqParam('start_date');
        $end_date   = $fn->getReqParam('end_date');

        $searchVar->sqlSearchVar[] = "b.status = 'Open'";

        if ($start_date != ''){
            $searchVar->sqlSearchVar[] = "b.creation_date >= '{$start_date}'";
        }
        if ($end_date != ''){
            $searchVar->sqlSearchVar[] = "b.creation_date <= '{$end_date}'";
        }*/

        if ($year == '') {
            $year = date('Y');

            $startYear = $year .'-01-01'; 
            $endYear   = $year .'-12-31';
            $searchVar->sqlSearchVar[] = "i.invoice_date BETWEEN '{$startYear}' AND '{$endYear}'";
        } else if ($year != ''){
            $startYear = $year .'-01-01'; 
            $endYear   = $year .'-12-31';
            $searchVar->sqlSearchVar[] = "i.invoice_date BETWEEN '{$startYear}' AND '{$endYear}'";
        }

        $searchVar->groupBy = 'i.invoice_month';
        //$searchVar->sortOrder = 'i.invoice_month';
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
        $dataArray = $modelHelper->getWidgetDataArray($this->controller, 'aceIms_traineeByCourse');

        $this->dataArray = $dataArray ;
        return $dataArray;
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

        $year = $fn->getReqParam('year');

        set_time_limit(50000);
        ini_set('memory_limit', '512M');

        require_once("PHPExcel.php");
        include 'PHPExcel/IOFactory.php';

        $file_name = "TraineeByCourse_" . date("d-m-Y") . ".xls";

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
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Number of Students');
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
        if ($year == '') {
            $year = date('Y');

            $startYear = $year .'-01-01'; 
            $endYear   = $year .'-12-31';
            $sqlAppend = "WHERE i.invoice_date BETWEEN '{$startYear}' AND '{$endYear}'";
        } else if ($year != ''){
            $startYear = $year .'-01-01'; 
            $endYear   = $year .'-12-31';
            $sqlAppend = "WHERE i.invoice_date BETWEEN '{$startYear}' AND '{$endYear}'";
        }

        $SQL = "
        SELECT COUNT(*) AS attendee_count
              ,i.invoice_month
        FROM invoice i
        {$sqlAppend}
        GROUP BY i.invoice_month
        ";

        $result = $db->sql_query($SQL);

        while ($row = $db->sql_fetchrow($result)) {

            switch ($row['invoice_month']) {
                case 1: $month = 'January';
                break;
                case 2: $month = 'February';
                break;
                case 3: $month = 'March';
                break;
                case 4: $month = 'April';
                break;
                case 5: $month = 'May';
                break;
                case 6: $month = 'June';
                break;
                case 7: $month = 'July';
                break;
                case 8: $month = 'August';
                break;
                case 9: $month = 'September';
                break;
                case 10: $month = 'October';
                break;
                case 11: $month = 'November';
                break;
                case 12: $month = 'December';
                break;
            }

            $colc = 0;
            $rowc++;
        
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $month);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['attendee_count']);
        }

        $rowc++;
        $actSheet->getStyle("A{$rowc}:D{$rowc}")->applyFromArray($headStyle);

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
    }   
}