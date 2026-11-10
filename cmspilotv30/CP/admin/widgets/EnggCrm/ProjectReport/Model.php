<?
class CP_Admin_Widgets_EnggCrm_ProjectReport_Model extends CP_Common_Lib_WidgetModelAbstract
{
	/**
     *
     */
    function getSQL(){
    	$SQL = "
    	SELECT p.*
              ,p.title AS Project_name
              ,c.company_id
              ,c.company_name 
              ,CONCAT_WS(' ', cont.first_name, cont.last_name) AS contact_name
        FROM `project` p
        LEFT JOIN company c ON (c.company_id = p.company_id)
        LEFT JOIN (contact cont) ON (p.contact_id = cont.contact_id)
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
        $category   = $fn->getReqParam('category');
        
        if ($start_date == '') {
            $start_date = date('Y-m-d', mktime (0,0,0,date("m")-12, date("d"), date("Y")));
        }
        
        if ($end_date == '') {
            $end_date = date('Y-m-d');
        } 

        $searchVar->sqlSearchVar[] = "(p.start_date >= '{$start_date}' OR p.actual_finish_date <= '{$end_date}')";

        if ($status != '') {
            $searchVar->sqlSearchVar[] = "p.status = '{$status}'";
        }
        
        if ($category != '') {
            $searchVar->sqlSearchVar[] = "p.category = '{$category}'";
        }

        $searchVar->sortOrder = 'p.start_date ASC';
    }

    /**
     *
     */

    function getDataArray() {
        $ln = Zend_Registry::get('ln');

        $modelHelper = Zend_Registry::get('modelHelper');
        $dataArray = $modelHelper->getWidgetDataArray($this->controller, 'enggCrm_projectReport');

        $this->dataArray = $dataArray ;
        return $dataArray;
    }


    /**
     */
    function getExportToExcel1(){
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
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Project code');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Project Title');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Category');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Start Date');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'End Date');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Client Company');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Contact');
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
              ,c.company_name 
              ,CONCAT_WS(' ', cont.first_name, cont.last_name) AS contact_name
        FROM `project` p
        LEFT JOIN company c ON (c.company_id = p.company_id)
        LEFT JOIN (contact cont) ON (p.contact_id = cont.contact_id)
        {$appendSql}
        ";

        $result = $db->sql_query($SQL);

            $count = 1;

        while ($row = $db->sql_fetchrow($result)) {
            $colc = 0;
            $rowc++;

            //$amount = $row['add_hourly_rate'] * $row['employee_hours'];

            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $count);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['project_code']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['title']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['category']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['start_date']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['actual_finish_date']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['company_name']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['contact_name']);
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
    function getExportToExcel($dataArray = ''){
        $dbUtil = Zend_Registry::get('dbUtil');
        
        if (!is_array($dataArray)){
            $dataArray = $this->getDataArray();
        }

        $phpExcel = includeCPClass('Lib', 'PhpExcelExportWrapper', 'PhpExcelExportWrapper');         
        $fa = array(
              'project_code'        => $phpExcel->getFldObj('Project code')
             ,'title'               => $phpExcel->getFldObj('Project Title')
             ,'category'            => $phpExcel->getFldObj('Category')
             ,'start_date'          => $phpExcel->getFldObj('Start Date')
             ,'actual_finish_date'  => $phpExcel->getFldObj('End Date')
             ,'company_name'        => $phpExcel->getFldObj('Client Company')
             ,'contact_name'        => $phpExcel->getFldObj('Contact')
             ,'status'              => $phpExcel->getFldObj('Status')
        );

        $file_name = "ProjectReport_" . date("d-m-Y") . ".xls";

        $config = array(
             'filename'  => $file_name
            ,'fldsArr'   => $fa
            ,'dataArray' => $dataArray
        );

        return $phpExcel->exportData($config);
    }

 
}