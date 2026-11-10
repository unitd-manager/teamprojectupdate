<?
class CP_Admin_Widgets_Pms_EnrollmentByYearReport_Model extends CP_Common_Lib_WidgetModelAbstract
{
    /**
     *
     */
    function getSQL(){

        $SQL = "
        SELECT COUNT(*) AS no_of_students
              ,cc.year_of_enrollment
        FROM course_contact cc
        LEFT JOIN (contact c) ON (cc.contact_id = c.contact_id)
        ";

        return $SQL;
    }

    /**
     *
     */
    function setSearchVar() {
        $fn = Zend_Registry::get('fn');

        $searchVar = $this->searchVar;

        $site_id = $fn->getReqParam('site_id');
        
        if ($site_id) {
            if(is_numeric($site_id)) {
                $searchVar->sqlSearchVar[] = "cc.site_id = '{$site_id}'";
            }
        }

        $searchVar->sqlSearchVar[] = "c.status = 'Active'";
        $searchVar->groupBy = "cc.year_of_enrollment ASC";
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
        $dataArray = $modelHelper->getWidgetDataArray($this->controller, 'pms_enrollmentByYearReport');

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

        $sqlAppend = '';
        
        $site_id = $fn->getReqParam('site_id');
        
        if (is_numeric($site_id)) {
            $sqlAppend .= "AND cc.site_id = {$site_id}";
        }
        
        set_time_limit(50000);
        ini_set('memory_limit', '512M');

        require_once("PHPExcel.php");
        include 'PHPExcel/IOFactory.php';

        $file_name = "StudentEnrollmentSummaryByYearReport_" . date("d-m-Y") . ".xls";

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

        if (is_numeric($site_id)) {
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Branch');
        }

        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Year');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'No of Students');

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
        
        $SQL = "
        SELECT COUNT(*) AS no_of_students
              ,cc.year_of_enrollment
        FROM course_contact cc
        LEFT JOIN (contact c) ON (cc.contact_id = c.contact_id)
        WHERE c.status = 'Active'
          {$sqlAppend}
        GROUP BY cc.year_of_enrollment ASC
        ";
        $result = $db->sql_query($SQL);
        while ($row = $db->sql_fetchrow($result)) {
            $colc = 0;
            $rowc++;

            if (is_numeric($site_id)) {
                $siteRec = $fn->getRecordRowById('site', 'site_id', $site_id);
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $siteRec['title']);
            }

            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['year_of_enrollment']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['no_of_students']);
        }

        $rowc++;
        $actSheet->getStyle("A{$rowc}:D{$rowc}")->applyFromArray($headStyle);

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
    }   
}