<?
class CP_Admin_Widgets_Project_ProjectReport_Model extends CP_Common_Lib_WidgetModelAbstract
{

	/**
     *
     */
    function getSQL(){
    	$cpCfg = Zend_Registry::get('cpCfg');
    	
    	$SQL = "
    	SELECT p.*
              ,p.title AS Project_name
              ,c.company_id
              ,c.company_name AS client_name
              ,c.category
              ,e.employee_name
              ,e.employee_work_type
              ,e.add_hourly_rate
              ,et.employee_hours 
        FROM `project` p
        LEFT JOIN company c ON (c.company_id = p.company_id)
        LEFT JOIN project_employee pe ON (pe.project_id = p.project_id)
        LEFT JOIN employee_timesheet et ON (et.employee_id = pe.employee_id)
        LEFT JOIN employee e ON (e.employee_id = pe.employee_id)
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
        $dataArray = $modelHelper->getWidgetDataArray($this->controller, 'project_EmployeeReport');

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

        $file_name = "ProjectReport__" . date("d-m-Y") . ".xls";

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
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Project Name');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Client Name');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Amount');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Status');
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
              ,c.company_id
              ,c.company_name AS client_name
              ,c.category
              ,e.employee_name
              ,e.employee_work_type
              ,e.add_hourly_rate
              ,et.employee_hours 
        FROM `project` p
        LEFT JOIN company c ON (c.company_id = p.company_id)
        LEFT JOIN project_employee pe ON (pe.project_id = p.project_id)
        LEFT JOIN employee_timesheet et ON (et.employee_id = pe.employee_id)
        LEFT JOIN employee e ON (e.employee_id = pe.employee_id)
        {$appendSql}
        ";

        $result = $db->sql_query($SQL);

            $count = 1;

        while ($row = $db->sql_fetchrow($result)) {
            $colc = 0;
            $rowc++;

	        
            $amount = $row['add_hourly_rate'] * $row['employee_hours'];

            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $count);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['Project_name']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['client_name']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $amount);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['status']);
            
            $count++;
        }

        $rowc++;
        $actSheet->getStyle("A{$rowc}:E{$rowc}")->applyFromArray($headStyle);

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
    }
    /**
     *
     */  

 
}