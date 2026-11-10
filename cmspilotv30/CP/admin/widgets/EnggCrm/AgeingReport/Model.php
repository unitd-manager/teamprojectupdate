<?
class CP_Admin_Widgets_EnggCrm_AgeingReport_Model extends CP_Common_Lib_WidgetModelAbstract
{
    /**
     *
     */
    function getSQL(){
        $SQL = "
        SELECT DISTINCT c.company_id
              ,c.company_name
        FROM `company` c
        ";

        return $SQL;
    }

    /**
     *
     */
    function setSearchVar() {
        $fn = Zend_Registry::get('fn');
        $searchVar = $this->searchVar;

        //$searchVar->sqlSearchVar[] = "c.status = 'Active'";
        //$searchVar->sqlSearchVar[] = "p.parent_id = 1";
        $searchVar->sqlSearchVar[] = "c.category = 'Client'";
        
        $searchVar->sortOrder = 'c.company_name ASC';
    }

    /**
     *
     */
    function getDataArray() {
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');

        $modelHelper = Zend_Registry::get('modelHelper');
        $dataArray = $modelHelper->getWidgetDataArray($this->controller, 'enggCrm_ageingReport');

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

        $file_name = "AgeingReport__" . date("d-m-Y") . ".xls";
        
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Content-Type: application/force-download");
        header("Content-Type: application/octet-stream");
        header("Content-Type: application/download");
        header("Content-Disposition: attachment;filename={$file_name}");
        header("Content-Transfer-Encoding: binary ");

        $objPHPExcel = new PHPExcel();
        $actSheet = &$objPHPExcel->getActiveSheet();
        $headStyle = array(
            'font' => array('bold' => true)
        );
        //--------------------------------------------------//
        $colc = 0;
        $rowc = 1;
        $actSheet->mergeCells("A{$rowc}:E{$rowc}");
        $actSheet->getStyle("A{$rowc}:E{$rowc}")->applyFromArray($headStyle);
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Ageing Summary Report');

        $rowc++;
        $colc = 0;
        $actSheet->getStyle("A{$rowc}:E{$rowc}")->applyFromArray($headStyle);
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'S.No');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Company Name');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '31-60 Days');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '61-90 Days');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Above 90 days');
        /******************** FORMAT HEADER *******************/
        $lastCol    = $actSheet->getHighestColumn();
        $lastColInd = PHPExcel_Cell::columnIndexFromString($lastCol);
        $actSheet->getStyle("A1:{$lastCol}1")->applyFromArray($headStyle);
        
        for ($i=0; $i < $lastColInd; $i++){
            $colAlphabet = PHPExcel_Cell::stringFromColumnIndex($i);
            $actSheet->getColumnDimension($colAlphabet)->setAutoSize(true);
        }

        $current_date = date('Y-m-d');

        $sql = "
        SELECT DISTINCT c.company_id
              ,c.company_name
        FROM `company` c
        WHERE c.category = 'Client'
        ORDER BY c.company_name ASC
        ";
        $result = $db->sql_query($sql);
        $count = 1;
        while ($row = $db->sql_fetchrow($result)) {
            $thirtyOneDays = date('Y-m-d', strtotime("-31 days"));
            $sixtyDays = date('Y-m-d', strtotime("-60 days"));
            $sixtyOneDays = date('Y-m-d', strtotime("-61 days"));
            $nintyDays = date('Y-m-d', strtotime("-90 days"));
            $nintyOneDays = date('Y-m-d', strtotime("-91 days"));
            $days3160 = $this->view->getOverallDueForCompany($row['company_id'], $thirtyOneDays, $sixtyDays);
            $days6190 = $this->view->getOverallDueForCompany($row['company_id'], $sixtyOneDays, $nintyDays);
            $daysAbove90 = $this->view->getOverallDueForCompany($row['company_id'], $nintyOneDays, 91);

            $colc = 0;
            $rowc++;
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $count);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['company_name']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $days3160);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $days6190);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $daysAbove90);
            $count++;
        }

        $rowc++;
        $actSheet->getStyle("A{$rowc}:D{$rowc}")->applyFromArray($headStyle);

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
    }   
}