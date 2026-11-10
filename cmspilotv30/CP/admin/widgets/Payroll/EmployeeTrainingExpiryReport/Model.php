<?
class CP_Admin_Widgets_Payroll_EmployeeTrainingExpiryReport_Model extends CP_Common_Lib_WidgetModelAbstract
{
    /**
     *
     */
    function getSQL(){

        $SQL = "
        SELECT ts.*
              ,t.title AS training_title
              ,e.first_name
        FROM training_staff ts
        LEFT JOIN (training t) ON (ts.training_id = t.training_id)
        LEFT JOIN (employee e) ON (ts.staff_id = e.employee_id)
        ";

        return $SQL;
    }
    
    /**
     *
     */
    function setSearchVar() {
        $fn = Zend_Registry::get('fn');
        
        $searchVar = $this->searchVar;        
        $searchVar->mainTableAlias = 't';

        $searchVar->sqlSearchVar[] = "e.status = 'Current'";

        $searchVar->sortOrder = 'ts.from_date ASC, ts.to_date ASC';
    }

    /**
     *
     */
    function getDataArray() {
        $ln = Zend_Registry::get('ln');

        $modelHelper = Zend_Registry::get('modelHelper');
        $dataArray = $modelHelper->getWidgetDataArray($this->controller, 'payroll_employeeTrainingExpiryReport');

        $this->dataArray = $dataArray ;
        return $dataArray;
    }
    
    /**
     */
    function getExportToExcel(){
        $tv       = Zend_Registry::get('tv');
        $db       = Zend_Registry::get('db');
        $fn       = Zend_Registry::get('fn');
        $cpCfg    = Zend_Registry::get('cpCfg');
        $cpUtil   = Zend_Registry::get('cpUtil');
        $dateUtil = Zend_Registry::get('dateUtil');

        $rows = '';

        set_time_limit(50000);
        ini_set('memory_limit', '512M');

        require_once("PHPExcel.php");
        include 'PHPExcel/IOFactory.php';

        $file_name = "EmployeeTrainingExpiryReport_" . date("d-m-Y") . ".xls";

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
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Training Title');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Employee Name');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'From Date');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'To Date');
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
            $appendSqlSite = "AND t.site_id = {$cpSiteIdSession}";
        }

        $SQL = "
        SELECT ts.*
              ,t.title AS training_title
              ,e.first_name
        FROM training_staff ts
        LEFT JOIN (training t) ON (ts.training_id = t.training_id)
        LEFT JOIN (employee e) ON (ts.staff_id = e.employee_id)
        WHERE e.status = 'Current'
        {$appendSqlSite}
        ORDER BY ts.from_date ASC, ts.to_date ASC
        ";
        $result = $db->sql_query($SQL);
        $count = 1;
        while ($row = $db->sql_fetchrow($result)) {
            $colc = 0;
            $rowc++;

            $from_date = $dateUtil->formatDate($row['from_date'], 'DD-MM-YYYY');
            $to_date = $dateUtil->formatDate($row['to_date'], 'DD-MM-YYYY');

            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $count);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['training_title']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['first_name']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $from_date);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $to_date);

            $count++;
        }

        $rowc++;
        $actSheet->getStyle("A{$rowc}:D{$rowc}")->applyFromArray($headStyle);

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
    }
}