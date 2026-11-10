<?
class CP_Admin_Widgets_Payroll_AllowanceReport_Model extends CP_Common_Lib_WidgetModelAbstract
{
    /**
     *
     */
    function getSQL(){
        $SQL = "
        SELECT p.*
              ,e.first_name
              ,e.nric_no
              ,e.fin_no
              ,e.citizen
        FROM `payroll_management` p
        LEFT JOIN (employee e) ON (e.employee_id = p.employee_id)
        ";

        return $SQL;
    }
    
    /**
     *
     */
    function setSearchVar() {
        $fn = Zend_Registry::get('fn');
        $searchVar = $this->searchVar;
        $searchVar->mainTableAlias = 'p';

        $month = $fn->getReqParam('month');
        $year  = $fn->getReqParam('year');
        
        $searchVar->sqlSearchVar[] = "p.payroll_month = '{$month}'";
        $searchVar->sqlSearchVar[] = "p.payroll_year = '{$year}'";

        $searchVar->sqlSearchVar[] = "e.first_name != ''";
        $searchVar->sqlSearchVar[] = "p.status != 'Cancelled'";
        $searchVar->sortOrder = 'e.first_name ASC';
    }

    /**
     *
     */
    function getDataArray() {
        $ln = Zend_Registry::get('ln');

        $modelHelper = Zend_Registry::get('modelHelper');
        $dataArray = $modelHelper->getWidgetDataArray($this->controller, 'payroll_allowanceReport');

        $this->dataArray = $dataArray ;
        return $dataArray;
    }
    
    /**
     */
    function getExportToExcel(){
        $fn       = Zend_Registry::get('fn');
        $db       = Zend_Registry::get('db');
        $tv       = Zend_Registry::get('tv');
        $cpCfg    = Zend_Registry::get('cpCfg');
        $cpUtil   = Zend_Registry::get('cpUtil');
        $dateUtil = Zend_Registry::get('dateUtil');

        $month = $fn->getReqParam('month');
        $year  = $fn->getReqParam('year');

        $rows = '';

        set_time_limit(50000);
        ini_set('memory_limit', '512M');

        require_once("PHPExcel.php");
        include 'PHPExcel/IOFactory.php';

        $file_name = "AllowanceReport_" . date("d-m-Y") . ".xls";

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
        $row  = 1;
        $actSheet = &$objPHPExcel->getActiveSheet();
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'S.No');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Name');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'NRIC/Fin No');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Payroll Month');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Payroll Year');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $cpCfg['m.jobInformation.allowance1Lbl']);
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $cpCfg['m.jobInformation.allowance2Lbl']);
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $cpCfg['m.jobInformation.allowance3Lbl']);
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $cpCfg['m.jobInformation.allowance4Lbl']);
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $cpCfg['m.jobInformation.allowance5Lbl']);
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Total Allowance');
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

        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');
        $appendSqlSite = "";
        if ($cpCfg['cp.hasMultiUniqueSites']) {
            $appendSqlSite = "AND p.site_id = {$cpSiteIdSession}";
        }

        $SQL = "
        SELECT p.*
              ,e.first_name
              ,e.nric_no
              ,e.fin_no
              ,e.citizen
        FROM `payroll_management` p
        LEFT JOIN (employee e) ON (e.employee_id = p.employee_id)
        WHERE e.first_name != ''
          AND p.payroll_month = '{$month}'
          AND p.payroll_year = '{$year}'
          AND p.status != 'Cancelled'
          {$appendSqlSite}
        ORDER BY e.first_name ASC
        ";
        $result = $db->sql_query($SQL);
        $count = 1;
        $allowance1Total = 0;
        $allowance2Total = 0;
        $allowance3Total = 0;
        $allowance4Total = 0;
        $allowance5Total = 0;
        $overallTotal = 0;

        while ($row = $db->sql_fetchrow($result)) {
            $colc = 0;
            $rowc++;

            if($row['citizen'] == 'PR' || $row['citizen'] == 'Citizen'){
                $finNo = $row['nric_no'];
            }else {
                $finNo = $row['fin_no'] ;
            }

            $total_allowance = $row['allowance1'] + $row['allowance2'] +$row['allowance3'] +$row['allowance4'] +$row['allowance5'];
            $allowance1Total += $row['allowance1'];
            $allowance2Total += $row['allowance2'];
            $allowance3Total += $row['allowance3'];
            $allowance4Total += $row['allowance4'];
            $allowance5Total += $row['allowance5'];
            $overallTotal += $total_allowance;

            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $count);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['first_name']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $finNo);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $month);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $year);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['allowance1']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['allowance2']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['allowance3']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['allowance4']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['allowance5']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $total_allowance);

            $count++;
        }

        $colc = 0;
        $rowc++;

        $colc = 0;
        $rowc++;
        $actSheet->mergeCells("A{$rowc}:E{$rowc}");
        $actSheet->getStyle("A{$rowc}:K{$rowc}")->applyFromArray($headStyle);
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'TOTAL');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $allowance1Total);
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $allowance2Total);
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $allowance3Total);
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $allowance4Total);
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $allowance5Total);
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $overallTotal);

        $rowc++;
        $actSheet->getStyle("A{$rowc}:F{$rowc}")->applyFromArray($headStyle);

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
    }
}