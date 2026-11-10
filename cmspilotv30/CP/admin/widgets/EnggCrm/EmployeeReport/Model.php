<?
class CP_Admin_Widgets_EnggCrm_EmployeeReport_Model extends CP_Common_Lib_WidgetModelAbstract
{

	/**
     *
     */
    function getSQL(){
    	$cpCfg = Zend_Registry::get('cpCfg');
    	
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

        $status     = $fn->getReqParam('status');
        $start_date = $fn->getReqParam('start_date');
        $end_date   = $fn->getReqParam('end_date');
        
        if ($start_date == '') {
            $start_date = date('Y-m-d', mktime (0,0,0,date("m")-12, date("d"), date("Y")));
        }
        
        if ($end_date == '') {
            $end_date = date('Y-m-d');
        } 


        //$searchVar->sqlSearchVar[] = "pe.date BETWEEN {$start_date} AND {$end_date}";
        //$searchVar->sqlSearchVar[] = "p.enquiry_date BETWEEN '{$start_date}' AND '{$end_date}'";

        if ($status != '') {
            $searchVar->sqlSearchVar[] = "p.status = '{$status}'";
        }
        
        //$searchVar->sortOrder = 'p.enquiry_date DESC';

    }

    /**
     *
     */

    function getDataArray() {
        $ln = Zend_Registry::get('ln');

        $modelHelper = Zend_Registry::get('modelHelper');
        $dataArray = $modelHelper->getWidgetDataArray($this->controller, 'enggCrm_EmployeeReport');

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

        $staff_id  = $fn->getReqParam('staff_id');
        $start_date = $fn->getReqParam('start_date');
        $end_date   = $fn->getReqParam('end_date');
        $month      = $fn->getReqParam('month');
        $year       = $fn->getReqParam('year');
        $current_date = date('Y-m-d');

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
        
        $status     = $fn->getReqParam('status');
        if($status != ''){
            $appendSql .= "WHERE p.status = '{$status}'";
        }

        $start_date = $fn->getReqParam('start_date');
        $end_date   = $fn->getReqParam('end_date');
        
        if ($start_date == '') {
            $start_date = date('Y-m-d', mktime (0,0,0,date("m")-12, date("d"), date("Y")));
        }
        
        if ($end_date == '') {
            $end_date = date('Y-m-d');
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

	        
            //$amount = $row['add_hourly_rate'] * $row['employee_hours'];

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
    function getExportToExcel1($dataArray = ''){
        $dbUtil = Zend_Registry::get('dbUtil');
        
        if (!is_array($dataArray)){
            $dataArray = $this->getDataArray();
        }

        $phpExcel = includeCPClass('Lib', 'PhpExcelExportWrapper', 'PhpExcelExportWrapper');         
        $fa = array(
              'employee_name'     => $phpExcel->getFldObj('Employee Name')
             ,'project_code'      => $phpExcel->getFldObj('Project Code')
             ,'title'             => $phpExcel->getFldObj('Project Title')
             ,'start_date'        => $phpExcel->getFldObj('Start Date')
             ,'actual_finish_date'=> $phpExcel->getFldObj('End Date')
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