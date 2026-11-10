<?
class CP_Admin_Modules_Project_Task_Model extends CP_Common_Lib_ModuleModelAbstract
{
    /**
     *
     */
    function getSQL() {
        $tv = Zend_Registry::get('tv');
        $sqlMaster = Zend_Registry::get('sqlMaster');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');

        $staff_team  = $fn->getReqParam('staff_team');
        $userGroupID = $fn->getSessionParam('userGroupID');
        $staffIDS    = $fn->getSessionParam('staff_id');

        $extraTableNames = "";

        if ($tv['staff_id'] != "" || $staff_team != "" || $userGroupID != $cpCfg['cp.superAdminUGId']) {
            $extraTableNames .= "JOIN task_staff ts ON(ts.task_id = t.task_id)";
        }

        if ($staff_team != "") {
            $extraTableNames .= "JOIN staff st ON(ts.staff_id = st.staff_id)";
        }

        if ($sqlMaster->generateSQLWithOnlyKeyFldGC == 1) {
            $flds = "
            SELECT GROUP_CONCAT(t.task_id SEPARATOR ',') AS record_ids
            ";
        } else {
            $flds = "
            SELECT t.*
            ,IF (t.opportunity_id IS NOT NULL AND t.opportunity_id != '', o.title, p.title) AS project_opp_title
            ,IF (t.opportunity_id IS NOT NULL AND t.opportunity_id != '', c_o.company_name, c_p.company_name) AS project_opp_company
            ,IF (t.opportunity_id IS NOT NULL AND t.opportunity_id != '', o.opportunity_code, p.project_code) AS project_opp_code
            ,IF (t.opportunity_id IS NOT NULL AND t.opportunity_id != '', 'Opportunity', 'Project') AS project_or_opp
            ,o.title AS opportunity_title
            ,o.opportunity_code
            ,p.title AS project_title
            ,p.project_code as project_code
            ,c_p.company_name
            ,c_o.company_name as opp_company_name
            ,CONCAT_WS(' ', s.first_name, s.last_name) AS project_manager_name
            ,(
                SELECT GROUP_CONCAT(
                    CONCAT_WS(' ', stf.first_name, stf.last_name) 
                    ORDER BY CONCAT_WS(' ', stf.first_name, stf.last_name) 
                    SEPARATOR ', '
                )
                FROM staff stf
                    ,task_staff ts
                WHERE ts.task_id = t.task_id 
                  AND stf.staff_id = ts.staff_id
            ) AS staff_names
            ,(
                SELECT GROUP_CONCAT(DISTINCT stf.team SEPARATOR ', ')
                FROM staff stf
                    ,task_staff ts
                WHERE ts.task_id = t.task_id 
                  AND stf.staff_id = ts.staff_id 
                  AND stf.team != ''
            ) AS staff_team
            ,(SELECT SUM(hours)
              FROM timesheet ts
              WHERE ts.task_id = t.task_id
            ) AS total_ts_hours
            ";
        }

        $SQL = "
        {$flds}
        FROM
        task t
        {$extraTableNames}
        LEFT JOIN (opportunity o) ON (t.opportunity_id     = o.opportunity_id)
        LEFT JOIN (project p)     ON (t.project_id         = p.project_id)
        LEFT JOIN (staff s)       ON (t.project_manager_id = s.staff_id)
        LEFT JOIN (company c_p)   ON (p.company_id         = c_p.company_id)
        LEFT JOIN (company c_o)   ON (o.company_id         = c_o.company_id)
        ";

        
        return $SQL;
    }

    /**
     *
     */
    function setSearchVar() {
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $searchVar = Zend_Registry::get('searchVar');
        $searchVar->mainTableAlias = 't';
        $cpCfg = Zend_Registry::get('cpCfg');

        $title              = $fn->getReqParam('title');
        $task_id            = $fn->getReqParam('task_id');
        $status             = $fn->getReqParam('status');
        $chance             = $fn->getReqParam('chance');
        $project_id         = $fn->getReqParam('project_id');
        $opportunity_id     = $fn->getReqParam('opportunity_id');
        $project_manager_id = $fn->getReqParam('project_manager_id');
        $staff_team         = $fn->getReqParam('staff_team');
        $company_id         = $fn->getReqParam('company_id');
        $staff_team         = $fn->getReqParam('staff_team');
        $due_date1          = $fn->getReqParam('due_date_1');
        $due_date2          = $fn->getReqParam('due_date_2');
        $userGroupID        = $fn->getSessionParam('userGroupID');
        $staffIDS           = $fn->getSessionParam('staff_id');

        if ($status == "" && $tv['searchDone'] == 0) {
            $status = "Due";
        }

        if ($task_id != "") {
            $searchVar->sqlSearchVar[] = "t.task_id = '{$task_id}'";

        } else if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "t.task_id = {$tv['record_id']}";

        } else {

            if ($title != '') {
                $searchVar->sqlSearchVar[] = "t.title LIKE '%{$title}%'";
            }

            if ($opportunity_id != "") {
                $searchVar->sqlSearchVar[] = "t.opportunity_id = '{$opportunity_id}'";
            }

            if ($project_manager_id != "") {
                $searchVar->sqlSearchVar[] = "t.project_manager_id = '{$project_manager_id}'";
            }

            if ($project_id != "") {
                $searchVar->sqlSearchVar[] = "t.project_id   = '{$project_id}'";
            }

            if ($company_id != "") {
                $searchVar->sqlSearchVar[] = "(p.company_id   = '{$company_id}' OR o.company_id   = '{$company_id}')";
            }

            if ($status != "") {
                if ($status == "Due" ) {
                    $searchVar->sqlSearchVar[] = "(t.status =  'Due' || t.status  =  'Late')" ;
                } else {
                    $searchVar->sqlSearchVar[] = "t.status   = '{$status}'";
                }
            }

            if ($tv['keyword'] != "") {
                $searchVar->sqlSearchVar[] = "(
                    t.title               LIKE '%{$tv['keyword']}%'
                    OR p.title            LIKE '%{$tv['keyword']}%'
                    OR p.project_id       LIKE '%{$tv['keyword']}%'
                    OR p.project_code     LIKE '%{$tv['keyword']}%'
                    OR o.opportunity_code LIKE '%{$tv['keyword']}%'
                    OR o.title            LIKE '%{$tv['keyword']}%'
                    OR t.description      LIKE '%{$tv['keyword']}%'
                    OR t.task_id          LIKE '%{$tv['keyword']}%'
                    OR t.notes            LIKE '%{$tv['keyword']}%'
                    OR t.status           LIKE '%{$tv['keyword']}%'
                    OR c_o.company_name   LIKE '%{$tv['keyword']}%'
                    OR c_p.company_name   LIKE '%{$tv['keyword']}%'
                )";
            }

            if ($tv['staff_id'] != '' ) {
                $searchVar->sqlSearchVar[] = "ts.staff_id = {$tv['staff_id']}";
            }

            if ($staff_team != '') {
                $searchVar->sqlSearchVar[] = "st.team = '{$staff_team}'";
            }

            if ($due_date1 != "" && $due_date2 != "" ) {
                $searchVar->sqlSearchVar[] = "(t.due_date BETWEEN '{$due_date1}' AND '{$due_date2}')";
            }

            if ($tv['special_search'] == "Flagged") {
                $searchVar->sqlSearchVar[] = "t.flag = 1";
            }

            if ($tv['special_search'] == "Not-Flagged") {
                $searchVar->sqlSearchVar[] = "(t.flag != 1 OR t.flag IS null)";
            }

            if ($userGroupID != $cpCfg['cp.superAdminUGId']){
                $searchVar->sqlSearchVar[] = "ts.staff_id = {$staffIDS}";
            }
            //------------------------------------------------------------------------//
        }

        $searchVar->sortOrder = "
        CASE
        WHEN (t.status = 'Late' ) THEN 1
        WHEN (t.due_date != '' AND t.due_date IS NOT NULL AND t.due_date != '0000-00-00' ) THEN 2
        ELSE 3
        END, t.due_date, p.title, o.title
        ";
    }

    /**
     *
     */
    function getNewValidate() {
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();
        $validate->validateData('title', 'Please enter the title');

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     */
    function getAdd(){
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        $cpCfg = Zend_Registry::get('cpCfg');

        if (!$this->getNewValidate()){
            return $validate->getErrorMessageXML();
        }

        $fa = $this->getFields();
        $id = $fn->addRecord($fa);

        $fn->returnAfterNewSave($id, $cpCfg['cp.pagetoReturnAfterSave']);
    }

    /**
     *
     */
    function getEditValidate() {
        $validate = Zend_Registry::get('validate');
        
        $validate->resetErrorArray();

        $validate->validateData('title', 'Please enter the title');
        $validate->validateData('due_date', 'Please enter the due date');
        $validate->validateData('category', 'Please select the category');
        $validate->validateData('status', 'Please choose the status');

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     */
    function getSave($tableName = '', $keyField = '', $keyFieldVal = ''){
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');

        if (!$this->getEditValidate()){
            return $validate->getErrorMessageXML();
        }

        $task_id = $fn->getPostParam('task_id');
        $taskRec = $fn->getRecordRowByID('task', 'task_id', $task_id);
        $status_prev = $taskRec['status'];

        $fa = $this->getFields();
        $id = $fn->saveRecord($fa, $tableName, $keyField, $keyFieldVal);

        if ($fa['project_manager_alert'] == 1) {
            //$this->Sendnotificationtoprojectmanager($id, $status_prev);
        }

        $fn->returnAfterNewSave($id);
    }

    /**
     *
     */
    function getSaveList(){
        $fn = Zend_Registry::get('fn');
        $fn->getSaveList();
    }

    /**
     *
     */
    function getFields() {
        $fn = Zend_Registry::get('fn');

        $fa = array();

        $fa = $fn->addToFieldsArray($fa, 'title');
        $fa = $fn->addToFieldsArray($fa, 'contact_id');
        $fa = $fn->addToFieldsArray($fa, 'staff_alert');
        $fa = $fn->addToFieldsArray($fa, 'staff_id');
        $fa = $fn->addToFieldsArray($fa, 'opportunity_id');
        $fa = $fn->addToFieldsArray($fa, 'project_id');
        $fa = $fn->addToFieldsArray($fa, 'project_manager_id');
        $fa = $fn->addToFieldsArray($fa, 'due_date');
        $fa = $fn->addToFieldsArray($fa, 'category');
        $fa = $fn->addToFieldsArray($fa, 'total_hours');
        $fa = $fn->addToFieldsArray($fa, 'chargeable');
        $fa = $fn->addToFieldsArray($fa, 'estimated_hours');
        $fa = $fn->addToFieldsArray($fa, 'description');
        $fa = $fn->addToFieldsArray($fa, 'notes');
        $fa = $fn->addToFieldsArray($fa, 'status');
        $fa = $fn->addToFieldsArray($fa, 'project_manager_alert');
        
        return $fa;
    }

    /**
     *
     */
    function getProjectTaskProjectTimesheetLinkSQL($id) {

        return "
        SELECT a.timesheet_id
              ,CONCAT_WS(' ', c.first_name, c.last_name) AS staff_name
              ,date_format(a.entry_date, '%d %b %Y') AS entry_date
              ,a.hours
        FROM task b
            ,timesheet a
        LEFT JOIN (staff c) ON (a.staff_id = c.staff_id)
        WHERE a.task_id = b.task_id
          AND a.task_id = {$id}
        ORDER BY entry_date
        ";

    }

    /**
     *
     */
    function getProjectTaskProjectTaskHistoryLinkSQL($id) {

        return "
        SELECT task_history_id
              ,title AS title
              ,status
              ,percentage
              ,sort_order
              ,comments
        FROM task_history
        WHERE task_id = {$id}
        ORDER BY sort_order
        ";

    }

    /**
     *
     */
    function getExportData1(){
        $db = Zend_Registry::get('db');

        set_time_limit(50000);
        ini_set('memory_limit', '512M');

        require_once("PHPExcel.php");
        include 'PHPExcel/IOFactory.php';

        $file_name = "Task-" . date("d-m-Y") . ".xls";

        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Content-Type: application/force-download");
        header("Content-Type: application/octet-stream");
        header("Content-Type: application/download");;
        header("Content-Disposition: attachment;filename={$file_name}");
        header("Content-Transfer-Encoding: binary ");

        $objPHPExcel = new PHPExcel();

        //--------------------------------------------------//
        $rowc = 1;
        $colc = 0;

        $actSheet = &$objPHPExcel->getActiveSheet();

        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, "Code");
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, "Title");
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, "Project Title");
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, "Record Type");
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, "Staff Name");
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, "Due Date");
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, "Description");
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, "Status");

        /******************** FORMAT HEADER *******************/
        $headStyle = array(
            'font' => array( 'bold' => true)
        );

        $lastCol    = $actSheet->getHighestColumn();
        $lastColInd = PHPExcel_Cell::columnIndexFromString($lastCol);
        $actSheet->getStyle("A1:{$lastCol}1")->applyFromArray($headStyle);

        for ($i=0; $i < $lastColInd; $i++){
            $colAlphabet = PHPExcel_Cell::stringFromColumnIndex($i);
            $actSheet->getColumnDimension($colAlphabet)->setAutoSize(true);
        }

        $actSheet->getColumnDimension('B')->setAutoSize(false);
        $actSheet->getColumnDimension('B')->setWidth(50);
        //============================================================================= //
        while ($row = $db->sql_fetchrow($result)) {
            $colc = 0;
            $rowc++;

            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['project_opp_code']);

            $colStr = PHPExcel_Cell::stringFromColumnIndex($colc);
            $actSheet->getStyle("{$colStr}{$rowc}")->getAlignment()->setWrapText(true);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['title']);

            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['project_opp_title']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['project_or_opp']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['staff_names']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['due_date']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['description']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['status']);
        }

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
    }

    /**
     *
     */
    function getExportData($dataArray){
        $phpExcel = includeCPClass('Lib', 'PhpExcelExportWrapper', 'PhpExcelExportWrapper');

        $fa = array(
              'project_opp_code'        => $phpExcel->getFldObj('Code')
             ,'title'                   => $phpExcel->getFldObj('Title')
             ,'project_opp_title'       => $phpExcel->getFldObj('Project Title')
             ,'project_or_opp'          => $phpExcel->getFldObj('Record Type')
             ,'staff_names'             => $phpExcel->getFldObj('Staff Name')
             ,'due_date'                => $phpExcel->getFldObj('Due Date')
             ,'description'             => $phpExcel->getFldObj('Description')
             ,'status'                  => $phpExcel->getFldObj('Status')
        );
        
        $config = array(
             'fldsArr'   => $fa
            ,'dataArray' => $dataArray
        );

        return $phpExcel->exportData($config);
    }       

    /**
     *
     */
    function getEditFromListValidate() {
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();

        $validate->validateData('status', 'Please choose the status');
        $validate->validateData('due_date', 'Please enter the due date');

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     */
    function getSaveFromList(){
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');

        if (!$this->getEditFromListValidate()){
            return $validate->getErrorMessageXML();
        }

        $fa = $this->getFields();
        $id = $fn->saveRecord($fa);
        return $validate->getSuccessMessageXML();
    }

    function getAddNewValuelistFormSubmit() {
        $fn       = Zend_Registry::get('fn');
        $db       = Zend_Registry::get('db');
        $dbUtil   = Zend_Registry::get('dbUtil');
        $validate = Zend_Registry::get('validate');
        
        $valuelist_value = $fn->getPostParam('valuelist_value');
        $valuelist_name  = $fn->getReqParam('valuelist_name');

        /*if (!$this->getAddNewValuelistFormValidate($valuelist_name, $valuelist_value)){
            return $validate->getErrorMessageXML();
        }*/
        
        $fa = array();
        $fa['key_text']      = $valuelist_name;
        $fa['value']         = $valuelist_value;
        $fa['creation_date'] = date("Y-m-d H:i:s");
        
        $insert = $dbUtil->getInsertSQLStringFromArray($fa, 'valuelist');
        $result = $db->sql_query($insert);
        $id     = $db->sql_nextid();
            
        return $validate->getSuccessMessageXML('', $valuelist_value);
    }
}
