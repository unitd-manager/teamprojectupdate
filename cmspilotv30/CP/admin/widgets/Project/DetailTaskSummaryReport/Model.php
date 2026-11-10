<?
class CP_Admin_Widgets_Project_DetailTaskSummaryReport_Model extends CP_Common_Lib_WidgetModelAbstract
{
    /**
     *
     */
    function getSQL(){

        $SQL = "
       SELECT c.company_name as Company_name
                ,p.title as Project_name
                ,t.title as Task_title
                ,th.title as Task_history_title
                ,th.comments as Description
                ,th.start_date as Start_date
                ,th.status as Status
                ,ts.hours as Hours
                ,ts.entry_date
                ,ts.description AS timesheet_description
                ,CONCAT_WS(' ', s.first_name, s.last_name ) AS staff_name
        FROM task_history as th
              LEFT JOIN (task t) on (th.task_id = t.task_id)
              LEFT JOIN (project p) on (th.project_id = p.project_id)
              LEFT JOIN (company c) on (p.company_id = c.company_id)
              LEFT JOIN (timesheet ts) on (th.task_history_id = ts.task_history_id)
              LEFT JOIN (staff s) on (s.staff_id = ts.staff_id)
        ";

        return $SQL;
    }

    /**
     *
     */
    function setSearchVar() {
        $fn = Zend_Registry::get('fn');
        $searchVar = $this->searchVar;
        $searchVar->mainTableAlias = 'o';


        $start_date = $fn->getReqParam('start_date');
        $end_date   = $fn->getReqParam('end_date');
        $month      = $fn->getReqParam('month');
        $year       = $fn->getReqParam('year');
        $staff_id   = $fn->getReqParam('staff_id');
        $company_name = $fn->getReqParam('company_name');
        $project_name = $fn->getReqParam('project_name');
        $yearMonthStart = $fn->getReqParam('yearMonthStart');
        $current_date = date('Y-m-d');
        $current_year = date('Y');
        $current_month = date('m');

        if ($start_date != '' && $end_date == '') {
            $searchVar->sqlSearchVar[] = "ts.entry_date BETWEEN '{$start_date}' AND '{$current_date}'";
        }
        else if ($start_date == '' && $end_date != ''){
            $searchVar->sqlSearchVar[] = "ts.entry_date BETWEEN '{$start_date}' AND '{$end_date}'";
        }
        else if ($start_date != '' && $end_date != '') {
            $searchVar->sqlSearchVar[] = "ts.entry_date BETWEEN '{$start_date}' AND '{$end_date}'";
        }
        else {
            $start_date = $current_year . '-' . $current_month . '-' . '01';
            $end_date = $current_year . '-' . $current_month . '-' . '31';
            $searchVar->sqlSearchVar[] = "ts.entry_date BETWEEN '{$start_date}' AND '{$end_date}'";
        }
        if ($staff_id != '' ) {
            $searchVar->sqlSearchVar[] = "th.staff_id = {$staff_id}";
        }
        if ($company_name != '' ) {
            $searchVar->sqlSearchVar[] = "c.company_id = {$company_name}";
        }
        if ($project_name != '' ) {
            $searchVar->sqlSearchVar[] = "p.project_id = {$project_name}";
        }

            $searchVar->sortOrder = 'ts.entry_date DESC';
    }

    /**
     *
     */
    function getDataArray() {
        $ln = Zend_Registry::get('ln');

        $modelHelper = Zend_Registry::get('modelHelper');
        $dataArray = $modelHelper->getWidgetDataArray($this->controller, 'project_detailTaskSummaryReport');

        $this->dataArray = $dataArray ;
        return $dataArray;
    }

    /**
     *
     */
    function getExportToExcel($dataArray = ''){
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $cpUtil = Zend_Registry::get('cpUtil');
        $dateUtil = Zend_Registry::get('dateUtil');
        $fn = Zend_Registry::get('fn');

        $rows = '';

        set_time_limit(50000);
        ini_set('memory_limit', '512M');

        require_once("PHPExcel.php");
        include 'PHPExcel/IOFactory.php';

        $file_name = "DetailTaskSummaryReport_" . date("d-m-Y") . ".xls";

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

        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Company Name');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Project Name');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Task Title');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Task History Title');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Staff Name');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Description');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Start Date');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Status');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Total Hours');
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
        $datesql = '';
        $staffsql = '';
        $companysql = '';
        $projectsql = '';
        $monthsql = '';
        $start_date     = $fn->getReqParam('start_date');
        $end_date       = $fn->getReqParam('end_date');
        $staff_id       = $fn->getReqParam('staff_id');
        $company_name   = $fn->getReqParam('company_name');
        $project_name   = $fn->getReqParam('project_name');
        $yearMonthStart = $fn->getReqParam('yearMonthStart');
        $current_date   = date('Y-m-d');
        $current_year   = date('Y');
        $current_month  = date('m');

        if ($start_date != '' && $end_date == '') {
             $datesql = " th.start_date BETWEEN '{$start_date}' AND '{$current_date}'";
        }
        else if ($start_date == '' && $end_date != ''){
             $datesql = " th.start_date BETWEEN '{$start_date}' AND '{$end_date}'";
        }
        else if ($start_date != '' && $end_date != ''){
             $datesql = " th.start_date BETWEEN '{$start_date}' AND '{$end_date}'";
        }
        else {
            $start_date = $current_year . '-' . $current_month . '-' . '01';
            $end_date = $current_year . '-' . $current_month . '-' . '31';
            $datesql = " th.start_date BETWEEN '{$start_date}' AND '{$end_date}'";
        }
        if ($staff_id != '' ) {
             $staffsql = "AND th.staff_id = {$staff_id}";
        }
        if ($company_name != ''){
             $companysql = "AND c.company_id = {$company_name}";
        }
        if ($project_name != '' ) {
             $projectsql = "AND p.project_id = {$project_name}";
        }

        $SQL = "
        SELECT c.company_name as Company_name
                ,p.title as Project_name
                ,t.title as Task_title
                ,th.title as Task_history_title
                ,th.comments as Description
                ,th.start_date as Start_date
                ,th.status as Status
                ,ts.hours as Hours
                ,CONCAT_WS(' ', s.first_name, s.last_name ) AS staff_name
        FROM task_history as th
              LEFT JOIN (task t) on (th.task_id = t.task_id)
              LEFT JOIN (project p) on (th.project_id = p.project_id)
              LEFT JOIN (company c) on (p.company_id = c.company_id)
              LEFT JOIN (staff s) on (th.staff_id = s.staff_id)
              LEFT JOIN (timesheet ts) on (th.task_history_id = ts.task_history_id)
              WHERE
              {$datesql}
              {$staffsql}
              {$companysql}
              {$projectsql}
              ORDER BY Start_date DESC
        ";

        $result = $db->sql_query($SQL);

        while ($row = $db->sql_fetchrow($result)) {

        $start_date = $fn->getCPDate($row['Start_date'],"d-m-Y");
        $company = ($row['Company_name']);
        $project = ($row['Project_name']);
        $tasktitle = ($row['Task_title']);
        $taskhistitle = ($row['Task_history_title']);
        $desc = ($row['Description']);
        $status = ($row['Status']);
        $hrs = ($row['Hours']);
        $name = ($row['staff_name']);
        $colc = 0;
        $rowc++;

            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $company);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $project);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $tasktitle);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $taskhistitle);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $name);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $desc);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $start_date);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $status);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $hrs);

        }
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');

    }
}