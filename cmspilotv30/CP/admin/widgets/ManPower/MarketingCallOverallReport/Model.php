<?
class CP_Admin_Widgets_ManPower_MarketingCallOverallReport_Model extends CP_Common_Lib_WidgetModelAbstract
{
    //Status |No of Records| Month
    function getSQL(){

        $SQL = "
        SELECT DISTINCT cr.status
        FROM call_registry cr
        ";
        return $SQL;
    }
    
    /**
     *
     */
    function setSearchVar() {
        $fn = Zend_Registry::get('fn');

        $searchVar = $this->searchVar;
        $searchVar->mainTableAlias = 'cr';
        
        $month = $fn->getReqParam('month');
        $year  = $fn->getReqParam('year');

        $current_year  = date('Y');
        $current_month = date('m');
        
        if ($month == '' && $year == '') {
            $startMonth = $current_year . '-' . $current_month . '-' . '01';
            $endMonth   = $current_year . '-' . $current_month . '-' . '31';
            
            $searchVar->sqlSearchVar[] = "cr.contact_date BETWEEN '{$startMonth}' AND '{$endMonth}'";
        }

        if ($year != ''){
            $startYear = $year . '-' . '01-01';
            $endYear   = $year . '-' . '12-31';

            $searchVar->sqlSearchVar[] = "cr.contact_date BETWEEN '{$startYear}' AND '{$endYear}'";
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
            $searchVar->sqlSearchVar[] = "cr.contact_date BETWEEN '{$startMonth}' AND '{$endMonth}'";
        }

        $searchVar->sqlSearchVar[] = "cr.status != ''";
        $searchVar->sortOrder = 'cr.status ASC';
    }

    /**
     *
     */
    function getDataArray() {
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');

        $modelHelper = Zend_Registry::get('modelHelper');
        $dataArray = $modelHelper->getWidgetDataArray($this->controller, 'manPower_marketingCallOverallReport');

        $this->dataArray = $dataArray ;
        return $dataArray;
    }
    
    /**
     *
     */
    function getCountValueForMonth($year, $month) {
        $sqlAppend = '';
        
        $current_year  = date('Y');
        $current_month = date('m');

        if ($month == '' && $year == '') {
            $startMonth = $current_year . '-' . $current_month . '-' . '01';
            $endMonth   = $current_year . '-' . $current_month . '-' . '31';
            
             $sqlAppend .= "AND cr.contact_date BETWEEN '{$startMonth}' AND '{$endMonth}'";
        }
        
        if ($year != ''){
            if ($month != '') {
                $startMonth = $year . '-' . $month . '-' . '01';
                $endMonth   = $year . '-' . $month . '-' . '31';
            } else {
                $month = date('m');
                $startMonth = $year . '-' . $month . '-' . '01';
                $endMonth   = $year . '-' . $month . '-' . '31';
            }
            $sqlAppend .= "AND cr.contact_date BETWEEN '{$startMonth}' AND '{$endMonth}'";
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
            $sqlAppend .= "AND cr.contact_date BETWEEN '{$startMonth}' AND '{$endMonth}'";
        }
        
        return $sqlAppend;
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

        $file_name = "MarketingCallOverall_" . date("d-m-Y") . ".xls";

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
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Status');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'No of Records');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Month');
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
        $month = $fn->getReqParam('month');
        $year  = $fn->getReqParam('year');

        $current_year  = date('Y');
        $current_month = date('m');
        
        if ($month == '' && $year == '') {
            $startMonth = $current_year . '-' . $current_month . '-' . '01';
            $endMonth   = $current_year . '-' . $current_month . '-' . '31';
            
            $sqlAppend .= "AND cr.contact_date BETWEEN '{$startMonth}' AND '{$endMonth}'";
        }

        if ($year != ''){
            $startYear = $year . '-' . '01-01';
            $endYear   = $year . '-' . '12-31';

            $sqlAppend .= "AND cr.contact_date BETWEEN '{$startYear}' AND '{$endYear}'";
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
            $sqlAppend .= "AND cr.contact_date BETWEEN '{$startMonth}' AND '{$endMonth}'";
        }

        $SQL = "
        SELECT DISTINCT cr.status
        FROM call_registry cr
        LEFT JOIN (site st) ON (cr.site_id = st.site_id)
        WHERE st.site_id = {$_SESSION['cp_site_id']}
          AND cr.status != ''
        {$sqlAppend}
        ORDER BY cr.status ASC 
        ";

        $result = $db->sql_query($SQL);

        $serial_no    = 0;
        $total_amount = 0;
        while ($row = $db->sql_fetchrow($result)) {
            $serial_no += 1;

            $colc = 0;
            $rowc++;

            $sqlAppendVal = $this->getCountValueForMonth($year, $month);
            $sqlCount = "
            SELECT COUNT(*) AS total_count_status FROM call_registry cr
            WHERE status = '{$row['status']}'
                  {$sqlAppendVal}
            ";
            $resultCount = $db->sql_query($sqlCount);
            $rowCount    = $db->sql_fetchrow($resultCount);
            
            if ($month == '') {
                $month_val = $current_month = date('F'); 
            } else {
                switch ($month) {
                    case '01': $month_val = 'January';
                    break;
    
                    case '02': $month_val = 'February';
                    break;
    
                    case '03': $month_val = 'March';
                    break;
    
                    case '04': $month_val = 'April';
                    break;
    
                    case '05': $month_val = 'May';
                    break;
    
                    case '06': $month_val = 'June';
                    break;
    
                    case '07': $month_val = 'July';
                    break;
    
                    case '08': $month_val = 'August';
                    break;
    
                    case '09': $month_val = 'September';
                    break;
    
                    case '10': $month_val = 'October';
                    break;
    
                    case '11': $month_val = 'November';
                    break;
    
                    case '12': $month_val = 'December';
                    break;
                }
            }
            
            if ($rowCount['total_count_status']) {
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $serial_no);
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['status']);
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $rowCount['total_count_status']);
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $month_val); 
            }
        }

        $rowc++;

        $actSheet->getStyle("A{$rowc}:D{$rowc}")->applyFromArray($headStyle);

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
    }   
}