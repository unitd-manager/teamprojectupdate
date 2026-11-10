<?
class CP_Admin_Modules_EnggCrm_Timesheet_Model extends CP_Common_Lib_ModuleModelAbstract
{
    /**
     *
     */
    function getSQL() {
        $sqlMaster = Zend_Registry::get('sqlMaster');

        if ($sqlMaster->generateSQLWithOnlyKeyFldGC == 1) {
            $flds = "
            SELECT GROUP_CONCAT(t.timesheet_id SEPARATOR ',') AS record_ids
            ";
        } else {
            $flds = "
            SELECT t.*
            ,IF (t.opportunity_id IS NOT NULL AND t.opportunity_id != '', o.title, p.title) AS project_opp_title
            ,IF (t.opportunity_id IS NOT NULL AND t.opportunity_id != '', c_o.company_name, c_p.company_name) AS project_opp_company
            ,IF (t.opportunity_id IS NOT NULL AND t.opportunity_id != '', o.opportunity_code, p.project_code) AS project_opp_code
            ,IF (t.opportunity_id IS NOT NULL AND t.opportunity_id != '', 'Opportunity', 'Project') AS project_or_opp
            ,s.team as staff_team
            ,CONCAT_WS(' ', s.first_name, s.last_name) AS staff_name
            ,p.title AS project_title
            ,o.title AS opportunity_title
            ,p.project_code as project_code
            ,ta.category
            ,ta.title AS task_title
            ";
        }

        $SQL = "
        {$flds}
        FROM timesheet t
        LEFT JOIN (opportunity o) ON (t.opportunity_id = o.opportunity_id)
        LEFT JOIN (project p)     ON (p.project_id     = t.project_id)
        LEFT JOIN (company c_o)   ON (c_o.company_id   = o.company_id)
        LEFT JOIN (company c_p)   ON (c_p.company_id   = p.company_id)
        LEFT JOIN (staff s)       ON (s.staff_id       = t.staff_id)
        LEFT JOIN (task ta)       ON (ta.task_id       = t.task_id)
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

        $task_id        = $fn->getReqParam('task_id');
        $timesheet_id   = $fn->getReqParam('timesheet_id');
        $company_id     = $fn->getReqParam('company_id');
        $yearMonthStart = $fn->getReqParam('yearMonthStart');
        $project_id     = $fn->getReqParam('project_id');
        $opportunity_id = $fn->getReqParam('opportunity_id');
        $category       = $fn->getReqParam('category');
        $staff_team     = $fn->getReqParam('staff_team');
        $company_id     = $fn->getReqParam('company_id');
        $entry_date     = $fn->getReqParam('entry_date');
        $yearMonthStart = $fn->getReqParam('yearMonthStart');
        $entry_date1    = $fn->getReqParam('entry_date_1');
        $entry_date2    = $fn->getReqParam('entry_date_2');
        $userGroupID    = $fn->getSessionParam('userGroupID');
        $staffIDS       = $fn->getSessionParam('staff_id');

        if ($timesheet_id != "") {
            $searchVar->sqlSearchVar[] = "t.timesheet_id   = '{$timesheet_id}'";
        } else if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "t.timesheet_id   = '{$tv['record_id']}'";
        } else {

            if ($yearMonthStart != '') {
                $searchVar->sqlSearchVar[] = "DATE_FORMAT(start_date, '%Y-%m') >= '{$yearMonthStart}'";
            }

            if ($task_id != "") {
                $searchVar->sqlSearchVar[] = "t.task_id   = '{$task_id}'";
            }

            if ($tv['staff_id'] != "") {
                $searchVar->sqlSearchVar[] = "t.staff_id   = '{$tv['staff_id']}'";
            }

            if ($staff_team != "") {
                $searchVar->sqlSearchVar[] = "s.team  = '{$staff_team}'";
            }

            if ($project_id != "") {
                $searchVar->sqlSearchVar[] = "t.project_id = '{$project_id}'";
            }

            if ($company_id != "") {
                $searchVar->sqlSearchVar[] = "(p.company_id = '{$company_id}' OR o.company_id = '{$company_id}')";
            }

            if ($opportunity_id != "") {
                $searchVar->sqlSearchVar[] = "t.opportunity_id = '{$opportunity_id}'";
            }

            if ($entry_date1 != "" && $entry_date2 != "" ) {
                $searchVar->sqlSearchVar[] = "(t.entry_date BETWEEN '{$entry_date1}' AND '{$entry_date2}')";
            } else if ($entry_date1 != "") {
                $searchVar->sqlSearchVar[] = "t.entry_date = '{$entry_date1}'";
            }

            if ($category != "") {
                $searchVar->sqlSearchVar[] = "ta.category = '{$category}'";
            }

            if ($tv['keyword'] != "") {
                $searchVar->sqlSearchVar[] = "(
                    t.description LIKE '%{$tv['keyword']}%'
                    OR p.title    LIKE '%{$tv['keyword']}%'
                    OR o.title    LIKE '%{$tv['keyword']}%'
                    OR ta.title   LIKE '%{$tv['keyword']}%'
                )";
            }

            //------------------------------------------------------------------------//
            $entry_date1 = $fn->getReqParam('entry_date1');
            $entry_date2 = $fn->getReqParam('entry_date2');

            if ($entry_date1 != "" && $entry_date2 != "" ) {
                $searchVar->sqlSearchVar[] = "(t.entry_date BETWEEN '{$entry_date1}' AND '{$entry_date2}')";
            }

            if ($tv['special_search'] == "Flagged") {
                $searchVar->sqlSearchVar[] = "t.flag = 1";
            }

            if ($tv['special_search'] == "Not-Flagged") {
                $searchVar->sqlSearchVar[] = "(t.flag != 1 OR t.flag IS null)";
            }

            if ($userGroupID != $cpCfg['cp.superAdminUGId']){
                $searchVar->sqlSearchVar[] = "t.staff_id= '{$staffIDS}'";
            }

            $searchVar->sortOrder = "t.entry_date DESC";
        }
    }

    /**
     *
     */
    function getNewValidate() {
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();

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

        if (!$this->getNewValidate()){
            return $validate->getErrorMessageXML();
        }

        $fa = $this->getFields();
        $id = $fn->addRecord($fa);

        $fnMod = includeCPClass('ModuleFns', 'enggCrm_timesheet');
        $fnMod->refreshValuesBasedOnTimeRecord($id);

        $fn->returnAfterNewSave($id);
    }

    /**
     *
     */
    function getEditValidate() {
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();

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
        $cpCfg = Zend_Registry::get('cpCfg');

        if (!$this->getEditValidate()){
            return $validate->getErrorMessageXML();
        }
        $fa = $this->getFields();
        $id = $fn->saveRecord($fa, $tableName, $keyField, $keyFieldVal);

        $fnMod = includeCPClass('ModuleFns', 'enggCrm_timesheet');
        $fnMod->refreshValuesBasedOnTimeRecord($id);

        $fn->returnAfterNewSave($id, $cpCfg['cp.pagetoReturnAfterSave']);
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

        $staff_id = $fn->getPostParam('staff_id');
        $fa['staff_id'] = $staff_id;

        $fa = $fn->addToFieldsArray($fa, 'project_id');
        $fa = $fn->addToFieldsArray($fa, 'opportunity_id');
        $fa = $fn->addToFieldsArray($fa, 'task_id');
        $fa = $fn->addToFieldsArray($fa, 'project_id');
        $fa = $fn->addToFieldsArray($fa, 'entry_date');
        $fa = $fn->addToFieldsArray($fa, 'hours');
        $fa = $fn->addToFieldsArray($fa, 'task_id');
        $fa = $fn->addToFieldsArray($fa, 'description');
        $fa = $fn->addToFieldsArray($fa, 'task_history_id');

        if (isset($fa['hours'])){
            $staff_rate       = $this->getStaffRate($staff_id);
            $fa['total_cost'] = $staff_rate * $fa['hours'];
        }

        return $fa;
    }

    /**
     *
     */
    function getStaffRate($staff_id) {
        $db = Zend_Registry::get('db');

        if ($staff_id == '') {
            return;
        }

        $SQL    = "SELECT staff_rate FROM staff WHERE staff_id = {$staff_id}";
        $result = $db->sql_query($SQL);
        $row    = $db->sql_fetchrow($result);

        $staff_rate = $row['staff_rate'];

        return $staff_rate;
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

        $file_name = "Timesheet-" . date("d-m-Y") . ".xls";

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

        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Task Title');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Code');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Project / Opportunity Name');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Type');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Staff Name');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Entry Date');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Hours');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Description');

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

        //============================================================================= //
        while ($row = $db->sql_fetchrow($result)) {
            $colc = 0;
            $rowc++;

            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['task_title']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['project_opp_code']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['project_opp_title']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['project_or_opp']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['staff_name']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['entry_date']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['hours']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['description']);

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
              'task_title'          => $phpExcel->getFldObj('Task Title')
             ,'project_opp_code'    => $phpExcel->getFldObj('Code')
             ,'project_opp_title'   => $phpExcel->getFldObj('Project / Opportunity Name')
             ,'project_or_opp'      => $phpExcel->getFldObj('Type')
             ,'staff_name'          => $phpExcel->getFldObj('Staff Name')
             ,'entry_date'          => $phpExcel->getFldObj('Entry Date')
             ,'hours'               => $phpExcel->getFldObj('Hours')
             ,'description'         => $phpExcel->getFldObj('Description')
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
    function getNewRecordFromTaskValidate() {
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();

        $validate->validateData('staff_id', 'Please choose the staff');
        $validate->validateData('entry_date', 'Please enter the date');
        $validate->validateData('hours', 'Please enter the hours');

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     */
    function getAddRecordFromTask(){
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');

        if (!$this->getNewRecordFromTaskValidate()){
            return $validate->getErrorMessageXML();
        }

        $fa = $this->getFields();
        $id = $fn->addRecord($fa);

        $fnMod = includeCPClass('ModuleFns', 'enggCrm_timesheet');
        $fnMod->refreshValuesBasedOnTimeRecord($id);

        return $validate->getSuccessMessageXML();
    }

    /**
     *
     */
    function setFields1() {
        $fn = Zend_Registry::get('fn');

        $fa = &$this->fieldsArray;

        $staff_id                = $fn->getPostParam('staff_id');
        $staff_rate              = $this->getStaffRate($staff_id);
        $fa['staff_id']          = $staff_id;
        $fa['project_id']        = $fn->getPostParam('project_id');
        $fa['opportunity_id']    = $fn->getPostParam('opportunity_id');
        $fa['task_id']           = $fn->getPostParam('task_id');
        $fa['entry_date']        = $fn->getPostParam('entry_date');
        $fa['hours']             = $fn->getPostParam('hours');
        $fa['task_id']           = $fn->getPostParam('task_id');
        $fa['total_cost']        = $staff_rate * $fa['hours'] ;
        $fa['description']       = $fn->getPostParam('description');
        $fa['modification_date'] = date('Y-m-d H:i:s');

    }

    /**
     *
     */
    function getTimesheetSummaryByMonth() {
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $cpUtil = Zend_Registry::get('cpUtil');
        $dateUtil = Zend_Registry::get('dateUtil');
        $fn = Zend_Registry::get('fn');

        require_once("PHPExcel.php");
        include 'PHPExcel/IOFactory.php';

        $start_date = $fn->getReqParam('entry_date_1');
        $end_date   = $fn->getReqParam('entry_date_2');
        if ($start_date == '' || $end_date == '') {
            $start_date = date('Y-m-1');
            $end_date = date('Y-m-d');
        }
        $filename = 'timesheetSummaryByMonth_' . date('d-m-Y') . '.xls';

        //$fn->getAddHeaderForExcelReport($filename);

        $objPHPExcel = new PHPExcel();
        $sheet = $objPHPExcel->getActiveSheet();

        $commonCellStyle = array(
            'borders' => array(
                    'outline' => array(
                            'style' => PHPExcel_Style_Border::BORDER_THIN,
                            'color' => array('argb' => '00000000'),
                    ),
            ),
        );

        $boldStyle = array(
            'font' => array(
                'bold' => true,
            )
        );
        $headerRowHeight = 60;
        $rowHeight = 25;
        $colWidth  = 20;

        $SQL = "
        SELECT s.staff_id
              ,ts.entry_date
              ,SUM(ts.hours) AS hours
        FROM staff s
        JOIN timesheet ts  ON (ts.staff_id = s.staff_id)
        WHERE ts.entry_date BETWEEN '{$start_date}' AND '{$end_date}'
        GROUP BY s.staff_id
                ,ts.entry_date
        ORDER BY s.staff_id
        ";
        $result  = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);

        //store timesheet data in array for later processing
        //array('staff_id' => array('yyyy-mm-dd' => hours1, 'yyyy-mm-dd' => hours2 .... ) )
        $tsArr = array();
        while ($row = $db->sql_fetchrow($result)) {
            if (!isset($tsArr[$row['staff_id']])) {
                $tsArr[$row['staff_id']] = array();
                $staffArr = &$tsArr[$row['staff_id']];
            }
            $staffArr[$row['entry_date']] = $row['hours'];
        }

        $start_date_obj      = strtotime($start_date);
        $start_date_obj_temp = $start_date_obj;
        $end_date_obj        = strtotime($end_date);

        $col = 'A';
        $rowCount = 1;

        //write header content
        $sheet->setCellValue($col . $rowCount, 'Staff/Date');
        $sheet->getColumnDimension($col)->setWidth($colWidth);
        $sheet->getRowDimension($rowCount)->setRowHeight($headerRowHeight);
        $col++;

        $startWeekDay = date('d', $start_date_obj_temp);
        $startCol     = $col;
        $startCol2    = $col;
        $endCol       = '';
        while ($start_date_obj_temp <= $end_date_obj) {
            $title = '';
            $dayOfWeek = date('D', $start_date_obj_temp); //ex: Sun
            $dayOfWeek = substr($dayOfWeek, 0, 1);
            $dayOfWeek2 = date('D', $start_date_obj_temp); //ex: Sun
            $day = date('d', $start_date_obj_temp);

            $title2 = date('j/n', $start_date_obj_temp); //ex: 8/3 S -- (Sunday)
            $sheet->getColumnDimension($col)->setWidth(6);
            $sheet->setCellValue($col . $rowCount, $day);
            $sheet->setCellValue(($col . ($rowCount + 1)), $title2);
            $sheet->setCellValue(($col . ($rowCount + 2)), $dayOfWeek);
            if ($dayOfWeek2 == 'Sun' || $start_date_obj_temp == $end_date_obj) {
                $month      = date('M', $start_date_obj_temp);
                $endWeekDay = date('d', $start_date_obj_temp);
                $title      = $month . ' ' . $startWeekDay . ' - ' . $endWeekDay;
                $sheet->mergeCells("{$startCol}{$rowCount}:{$col}{$rowCount}");
                $sheet->getStyle("{$startCol}{$rowCount}")->getAlignment()
                      ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                $sheet->setCellValue($startCol . $rowCount, $title);
                $col++;
                $sheet->setCellValue($col . $rowCount, 'Total no. of hours inputted');
                $sheet->getStyle("{$col}{$rowCount}")->getAlignment()->setWrapText(true);
                $startCol = $col;
                $startCol++;
                $startWeekDay = date('d', strtotime('+1 day', $start_date_obj_temp));
            }

            $start_date_obj_temp = strtotime('+1 day', $start_date_obj_temp);
            $col++;
        }

        //$col = $cpUtil->getSubtractString($col);
        $endCol = $col;
        $sheet->getStyle("{$startCol2}{$rowCount}:{$endCol}{$rowCount}")
              ->getFill()
              ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
              ->getStartColor()
              ->setRGB('DDDD00');

        $rowCount++;
        $sheet->getRowDimension($rowCount)->setRowHeight($rowHeight);
        $rowCount++;
        $sheet->getRowDimension($rowCount)->setRowHeight($rowHeight);
        $rowCount++;


        //write body data

        $SQL = "
        SELECT s.staff_id
              ,CONCAT_WS(' ', s.first_name, s.last_name) AS staff_name
        FROM staff s
        WHERE s.published = 1
        ORDER BY staff_name
        ";
        $result = $db->sql_query($SQL);

        while ($row = $db->sql_fetchrow($result)) {
            $sheet->getRowDimension($rowCount)->setRowHeight($rowHeight);
            $col = 'A';
            $staff_id   = $row['staff_id'];
            $staff_name = $row['staff_name'];
            $sheet->setCellValue($col . $rowCount, $staff_name);
            $col++;

            $start_date_obj_temp = $start_date_obj;
            $total = '';
            while ($start_date_obj_temp <= $end_date_obj) {
                $date = date('Y-m-d', $start_date_obj_temp);
                $dayOfWeek2 = date('D', $start_date_obj_temp); //ex: Sun
                $hour = '';
                if (isset($tsArr[$staff_id][$date])) {
                    $hour = $tsArr[$staff_id][$date];
                }
                $total += $hour;
                $sheet->setCellValue($col . $rowCount, $hour);

                if ($dayOfWeek2 == 'Sun' || $start_date_obj_temp == $end_date_obj) {
                    $col++;
                    $sheet->setCellValue($col . $rowCount, $total);
                    $sheet->getStyle("{$col}{$rowCount}")->applyFromArray($boldStyle);
                    $total = '';
                }
                $start_date_obj_temp = strtotime('+1 day', $start_date_obj_temp);
                $col++;
            }
            $rowCount++;
        }

        $rowCount--;
        $sheet->getStyle("B1:{$endCol}{$rowCount}")->getAlignment()
              ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        //$fn->setRepeatedBorder($sheet, 'A', $endCol, 1, $rowCount, $commonCellStyle);
        $sheet->getStyle("A1:A{$rowCount}")->applyFromArray($boldStyle);

        //$fn->getSaveExcelReport($objPHPExcel);

        exit();

        $objPHPExcel->getActiveSheet()->duplicateStyleArray(
            array(
            'font' => array( 'bold' => true ),
            'alignment' => array( 'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT)
            ), 'A1:F1'
        );

        $rowCount = 1;

        $objPHPExcel->getActiveSheet()->duplicateStyleArray(
                array(
                'alignment' => array( 'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT)
                ), 'A1:F' . $rowCount
        );

        exit();

        $fn->getSaveExcelReport($objPHPExcel, $file_name);

    }
}
