<?
class CPL_Admin_Widgets_EnggCrm_EmployeeReport_Model extends CP_Admin_Widgets_EnggCrm_EmployeeReport_Model
{

	/**
     *
     */
    function getSQL(){

        $SQL = "
        SELECT e.*
        FROM employee e
        ";

        return $SQL;
    }

    /**
     *
     */
    function setSearchVar() {
        $fn = Zend_Registry::get('fn');
        $searchVar = $this->searchVar;
        $searchVar->mainTableAlias = 'e';

        $status = $fn->getReqParam('status');

        if ($status != '') {
            $searchVar->sqlSearchVar[] = "e.status = '{$status}'";
        }
        
        $searchVar->sortOrder = 'e.employee_name ASC';
    }

    /**
     *
     */
    function getDataArray() {
        $ln = Zend_Registry::get('ln');

        $modelHelper = Zend_Registry::get('modelHelper');
        $dataArray = $modelHelper->getWidgetDataArray($this->controller, 'enggCrm_employeeReport');

        $this->dataArray = $dataArray ;
        return $dataArray;
    }

    /**
     */
    function getExportToExcel11(){
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $cpUtil = Zend_Registry::get('cpUtil');
        $dateUtil = Zend_Registry::get('dateUtil');
        $fn = Zend_Registry::get('fn');

        $status = $fn->getReqParam('status');

        $rows = '';
        $appendSql = '';


        set_time_limit(50000);
        ini_set('memory_limit', '512M');

        require_once("PHPExcel.php");
        include 'PHPExcel/IOFactory.php';

        $file_name = "EmployeeReport__" . date("d-m-Y") . ".xls";

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
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Employee Name');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Project Code');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Project Title');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Start Date');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'End Date');
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

        $appendSql = '';
        if($status != ''){
            $appendSql .= "WHERE e.status = '{$status}'";
        }

        $SQL = "
        SELECT p.*
              ,p.title AS Project_name
              ,e.employee_name
              ,e.employee_id
              ,et.employee_id
        FROM `project` p
        LEFT JOIN project_employee pe ON (pe.project_id = p.project_id)
        LEFT JOIN employee e ON (e.employee_id = pe.employee_id)
        LEFT JOIN employee_timesheet et ON (et.employee_id = pe.employee_id)
        {$appendSql}
        ";
        $result = $db->sql_query($SQL);
        $count = 1;

        while ($row = $db->sql_fetchrow($result)) {
            $colc = 0;
            $rowc++;

            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $count);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['employee_name']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['project_code']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['title']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['start_date']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['actual_finish_date']);

            $count++;
        }

        $rowc++;
        $actSheet->getStyle("A{$rowc}:F{$rowc}")->applyFromArray($headStyle);

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
    }

    /**
     *
     */  
    function getExportToExcel($dataArray = ''){
        $dbUtil = Zend_Registry::get('dbUtil');
        
        if (!is_array($dataArray)){
            $dataArray = $this->getDataArray();
        }

        $phpExcel = includeCPClass('Lib', 'PhpExcelExportWrapper', 'PhpExcelExportWrapper');         
        $fa = array(
              'employee_name'   => $phpExcel->getFldObj('Employee Name')
             ,'passport'        => $phpExcel->getFldObj('Passport No')
             ,'nric_no'         => $phpExcel->getFldObj('FIN No')
             ,'spass_no'        => $phpExcel->getFldObj('S Pass No')
             ,'date_of_birth'   => $phpExcel->getFldObj('Date of birth')
             ,'date_of_expiry'  => $phpExcel->getFldObj('Date of expiry')
             ,'status'          => $phpExcel->getFldObj('Status')
             ,'salary'          => $phpExcel->getFldObj('Salary')
        );

        $file_name = "EmployeeReport_" . date("d-m-Y") . ".xls";

        $config = array(
             'filename'  => $file_name
            ,'fldsArr'   => $fa
            ,'dataArray' => $dataArray
        );

        return $phpExcel->exportData($config);
    } 
}