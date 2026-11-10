<?
class CP_Admin_Widgets_AgileIms_DailyAccountsReport_Model extends CP_Common_Lib_WidgetModelAbstract
{
    /**
     *
     */
    function getSQL(){
        $SQL = "
        SELECT incExp.*
        FROM expenses incExp
        ";

        return $SQL;
    }
    /**
     *
     */
    function setSearchVar() {
        $fn = Zend_Registry::get('fn');
        $searchVar = $this->searchVar;
        $start_date = $fn->getReqParam('start_date');
        $end_date   = $fn->getReqParam('end_date');

        $today =  date('Y-m-d');
        if($start_date == '' && $end_date == ''){
            $searchVar->sqlSearchVar[] = "incExp.date = '{$today}'"; 
        }
        else{
            if ($start_date != ''){
                $searchVar->sqlSearchVar[] = "incExp.date >= '{$start_date}'";
            }
            if ($end_date != ''){
                $searchVar->sqlSearchVar[] = "incExp.date <= '{$end_date}'";
            }
        }

        //$searchVar->groupBy = 'oi.record_id';
        //$searchVar->sortOrder = 'c.registration_no';
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
        $dataArray = $modelHelper->getWidgetDataArray($this->controller, 'agileIms_dailyAccountsReport');

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

        $start_date = $fn->getReqParam('start_date');
        $end_date   = $fn->getReqParam('end_date');

        set_time_limit(50000);
        ini_set('memory_limit', '512M');

        require_once("PHPExcel.php");
        include 'PHPExcel/IOFactory.php';

        $file_name = "DailyAccountReport" . date("d-m-Y") . ".xls";

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
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Title');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Description');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Income');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Expense');
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
        
        $today =  date('Y-m-d');
        if($start_date == '' && $end_date == ''){
            $sqlAppend = "incExp.date = '{$today}'"; 
        }
        else{
            if ($start_date == "" && $end_date != ''){
                $sqlAppend = "incExp.date <= '{$end_date}'";
            }
            else if ($start_date != "" && $end_date == ''){
                $sqlAppend = "incExp.date >= '{$start_date}'";
            }
            else if ($start_date != "" && $end_date != ''){
                $sqlAppend = "incExp.date >= '{$start_date}' AND incExp.date <= '{$end_date}'";
            }
        }
        
        $SQL = "
        SELECT incExp.*
        FROM expenses incExp
        WHERE {$sqlAppend}
        ";

        $result = $db->sql_query($SQL);

        while ($row = $db->sql_fetchrow($result)) {
            $expAmount = '';
            $incAmount = '';
            
            if($row['type'] == 'Income'){
                $incAmount = $row['amount'];
            }
            else if($row['type'] == 'Expense'){
                $expAmount = $row['amount'];
            }

            $colc = 0;
            $rowc++;
        
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $fn->getCPDate($row['date'], 'd-M-Y'));
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['title']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['description']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $incAmount);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $expAmount);
        }

        $rowc++;
        $actSheet->getStyle("A{$rowc}:D{$rowc}")->applyFromArray($headStyle);

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
    }   
}