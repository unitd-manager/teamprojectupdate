<?
class CP_Admin_Widgets_AceIms_TeacherDeploymentReport_Model extends CP_Common_Lib_WidgetModelAbstract
{
    function getSQL(){

        $SQL = "
        SELECT t.*
        FROM teacher t
        ";

        return $SQL;
    }
    /**
     *
     */
    function setSearchVar() {
        $fn = Zend_Registry::get('fn');
        $searchVar = $this->searchVar;
        $status     = $fn->getReqParam('status');

        $searchVar->sortOrder = '';
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
        $dataArray = $modelHelper->getWidgetDataArray($this->controller, 'aceIms_teacherDeploymentReport');

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

        $file_name = "TeacherDeploymentReport_" . date("d-m-Y") . ".xls";

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

        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'TEACHER NAME');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'NRIC NO');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'APPROVED COURSE');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'APPROVED MODULES');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'INTAKE NO');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'DATE');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'EVALUATION %');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'PASSING RATE');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'REMARKS/FEEDBACKS');
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
        SELECT t.*
        FROM teacher t
        ";

        $result = $db->sql_query($SQL);

        $serial_no = 0;
        while ($row = $db->sql_fetchrow($result)) {
            $serial_no += 1;

            $colc = 0;
            $rowc++;
        
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['first_name']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['id_card_no']);
        }

        $rowc++;
        $actSheet->getStyle("A{$rowc}:D{$rowc}")->applyFromArray($headStyle);

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
    }   

}