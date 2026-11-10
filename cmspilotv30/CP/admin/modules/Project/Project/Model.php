<?
class CP_Admin_Modules_Project_Project_Model extends CP_Common_Lib_ModuleModelAbstract
{
    /**
     *
     */
    function getSQL() {
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');
        $sqlMaster = Zend_Registry::get('sqlMaster');

        $extraTableNames = "";

        if ($tv['staff_id'] != "") {
            $extraTableNames .= "project_staff ps_hist,";
        }

        $used_hrs_sql = "(
            SELECT ROUND(SUM(hours)) AS total_hours
            FROM timesheet
            WHERE project_id = p.project_id
        )";

        $used_inhouse_sql = "(
            SELECT SUM(total_cost) AS total_cost
            FROM timesheet ts
            WHERE ts.project_id = p.project_id
        )";

        $additional_third_party_sql = "(
            SELECT SUM(actual_amount) AS total_additional_cost
            FROM third_party_cost
            WHERE project_id = p.project_id
        )";

        $perc_used_sql = "(
            (IF(ISNULL(p.used_inhouse),0, p.used_inhouse)) /
            (p.project_value - IF(ISNULL(p.budget_third_party),0, p.budget_third_party))
        ) *100
        ";

        $still_to_bill_sql = "(
            SELECT SUM(invoice_amount)
            FROM invoice i
            WHERE i.project_id = p.project_id
              AND LOWER(i.status) != 'cancelled'
        )
        ";

        $joinTbls = '';
        $joinFlds = '';

        if ($cpCfg['m.project.hasMultiBranches'] == 1){
            $joinTbls .= "LEFT JOIN branch b ON(p.branch_id = b.branch_id)";
            $joinFlds .= ",b.title AS branch_name";
        }

        if ($sqlMaster->generateSQLWithOnlyKeyFldGC == 1) {
            $flds = "
            SELECT GROUP_CONCAT(p.project_id SEPARATOR ',') AS record_ids
            ";
        } else {
            $flds = "
            SELECT p.*,
            CONCAT_WS(' ', cont.first_name, cont.last_name) AS contact_name
            ,c.company_name
            ,c.company_size
            ,c.source
            ,c.industry
            ,o.opportunity_code
            ,(
                SELECT GROUP_CONCAT(
                    CONCAT_WS(' ', stf.first_name, stf.last_name)
                    ORDER BY CONCAT_WS(' ', stf.first_name, stf.last_name)
                    SEPARATOR ', '
                )
                FROM staff stf
                    ,project_staff ts
                WHERE ts.project_id = p.project_id
                AND stf.staff_id = ts.staff_id
            ) AS staff_name
            ,ser.title as service_title
            ,CONCAT_WS(' ', s.first_name, s.last_name) AS project_manager_name
            ,IF(ISNULL({$perc_used_sql}), 0, ROUND({$perc_used_sql})) AS percentage_used
            ,(IF(ISNULL({$used_hrs_sql}),0, {$used_hrs_sql}) ) AS used_hours
            ,{$additional_third_party_sql} AS additional_third_party
            ,(p.project_value - (IF(ISNULL({$still_to_bill_sql}),0, {$still_to_bill_sql}))) AS still_to_bill
            {$joinFlds}
            ";
        }

        $SQL = "
        {$flds}
        FROM {$extraTableNames}
        project p
        LEFT JOIN (contact cont)  ON (p.contact_id         = cont.contact_id)
        LEFT JOIN (company c)     ON (p.company_id         = c.company_id)
        LEFT JOIN (service ser)   ON (p.service_id         = ser.service_id)
        LEFT JOIN (staff   s)     ON (p.project_manager_id = s.staff_id)
        LEFT JOIN (opportunity o) ON (p.opportunity_id     = o.opportunity_id)
        {$joinTbls}
        ";

        return $SQL;
    }

    /**
     *
     */
    function setSearchVar($linkRecType = '') {
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $searchVar = Zend_Registry::get('searchVar');
        $cpCfg = Zend_Registry::get('cpCfg');
        $searchVar->mainTableAlias = 'p';

        $title              = $fn->getReqParam('title');
        $category           = $fn->getReqParam('category');
        $company_id         = $fn->getReqParam('company_id');
        $contact_id         = $fn->getReqParam('contact_id');
        $project_id         = $fn->getReqParam('project_id');
        $service_id         = $fn->getReqParam('service_id');
        $staff_id           = $fn->getReqParam('staff_id');
        $project_manager_id = $fn->getReqParam('project_manager_id');
        $yearMonthStart     = $fn->getReqParam('yearMonthStart');
        $yearMonthFinish    = $fn->getReqParam('yearMonthFinish');
        $project_month      = $fn->getReqParam('project_month');
        $start_date1        = $fn->getReqParam('start_date_1');
        $start_date2        = $fn->getReqParam('start_date_2');
        $end_date1          = $fn->getReqParam('end_date_1');
        $end_date2          = $fn->getReqParam('end_date_2');
        $branch_id          = $fn->getReqParam('branch_id');
        $client_type        = $fn->getReqParam('client_type');

        if ($project_id != "") {
            $searchVar->sqlSearchVar[] = "p.project_id = {$project_id}";
        } else if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "p.project_id = {$tv['record_id']}";
        } else {
            $fn->setSearchVarForLinkData($searchVar, $linkRecType, 'p.project_id');

            if ($title != '') {
                $searchVar->sqlSearchVar[] = "p.title LIKE '%{$title}%'";
            }

            if ($category != '') {
                $searchVar->sqlSearchVar[] = "p.category = '{$category}'";
            }

            if ($project_month != "") {
                $searchVar->sqlSearchVar[] = "p.paid_on  = '{$project_month}'";
            }

            if ($project_manager_id != "") {
                $searchVar->sqlSearchVar[] = "p.project_manager_id   = {$project_manager_id}";
            }

            if ($contact_id != "") {
                $searchVar->sqlSearchVar[] = "p.contact_id   = {$contact_id}";
            }

            if ($service_id != "") {
                $searchVar->sqlSearchVar[] = "p.service_id   = {$service_id}";
            }

            if ($tv['status'] != "") {
                $searchVar->sqlSearchVar[] = "p.status   = '{$tv['status']}'";
            }

            if ($branch_id != "") {
                $searchVar->sqlSearchVar[] = "p.branch_id = '{$branch_id}'";
            }

            if ($company_id != "") {
                $searchVar->sqlSearchVar[] = "p.company_id  = {$company_id}";
            }

            if ($client_type != '') {
                $searchVar->sqlSearchVar[] = "p.client_type = '{$client_type}'";
            }

            if ($tv['keyword'] != "") {
                $searchVar->sqlSearchVar[] = "(
                    p.title          LIKE '%{$tv['keyword']}%'  OR
                    p.project_code   LIKE '%{$tv['keyword']}%'  OR
                    p.project_id     LIKE '%{$tv['keyword']}%'  OR
                    p.description    LIKE '%{$tv['keyword']}%'  OR
                    c.company_name   LIKE '%{$tv['keyword']}%'  OR
                    p.notes          LIKE '%{$tv['keyword']}%'  OR
                    p.quote_ref      LIKE '%{$tv['keyword']}%'  OR
                    ser.service_code LIKE '%{$tv['keyword']}%'  OR
                    ser.title        LIKE '%{$tv['keyword']}%'
                )";
            }

            if ($tv['staff_id'] != '' ) {
                $searchVar->sqlSearchVar[] = "p.project_id     =  ps_hist.project_id";
                $searchVar->sqlSearchVar[] = "ps_hist.staff_id = {$tv['staff_id']}";
            }

            if ($yearMonthStart != '') {
                $searchVar->sqlSearchVar[] = "DATE_FORMAT(start_date, '%Y-%m') = '{$yearMonthStart}'";
            }

            if ($yearMonthFinish != '' ) {
                $searchVar->sqlSearchVar[] = "DATE_FORMAT(estimated_finish_date, '%Y-%m') = '{$yearMonthFinish}'";
            }

            if ($start_date1 != "" && $start_date2 != "" ) {
                $searchVar->sqlSearchVar[] = "(p.start_date BETWEEN '{$start_date1}' AND '{$start_date2}')";
            }

            if ($end_date1 != "" && $end_date2 != "" ) {
                $searchVar->sqlSearchVar[] = "(p.estimated_finish_date BETWEEN '{$end_date1}' AND '{$end_date2}')";
            }
            //------------------------------------------------------------------------//
            if ($tv['special_search'] == "Flagged") {
                $searchVar->sqlSearchVar[] = "p.flag = 1";
            }

            if ($tv['special_search'] == "Not-Flagged") {
                $searchVar->sqlSearchVar[] = "(p.flag != 1 OR p.flag IS null)";
            }

            if ($tv['special_search'] == "Overrun") {
                $searchVar->sqlSearchVar[] = "(
                    ROUND(
                     (
                      (IF(ISNULL(p.used_inhouse),0, p.used_inhouse))  / ( p.project_value - IF(ISNULL(p.budget_third_party),0, p.budget_third_party) )
                     ) *100
                    )
                ) > 100
                ";
            }

            //------------------------------------------------------------------------//
            if ($cpCfg['m.project.project.defaultSQL'] != "" && $tv['searchDone'] == 0) {
                $searchVar->sqlSearchVar[] = $cpCfg['m.project.project.defaultSQL'];
            }

            if ($tv['status'] == '' && $tv['searchDone'] == 0){
                $searchVar->sqlSearchVar[] = $cpCfg['m.project.project.defaultSQL'];
            }
        }

        //------------------------------------------------------------------------//
        if ($tv['searchDone'] == 0){
            $searchVar->sortOrder = "c.company_name";
        } else {
            $searchVar->sortOrder = "p.project_code DESC";
        }

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
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');

        if (!$this->getNewValidate()){
            return $validate->getErrorMessageXML();
        }

        $fa = $this->getFields();
        $fa['status'] = "WIP";

        if ($cpCfg['m.project.hasQuotingModule'] == 1) {
            $fa['confirmed_quote_id'] = 0;
        }

        //-------------------------------------------------------//
        $fa['project_code'] = $fn->getSettingsValueByKey('projectCodePrefix') . $fn->getSettingsValueByKey('nextProjectCode');
        $SQL = "UPDATE setting SET value = (value+1) WHERE key_text = 'nextProjectCode'";
        $result = $db->sql_query($SQL);

        if ($cpCfg['m.project.hasMultiBranches'] == 1 && $fa['branch_id'] != ''){
            $branchRec = $fn->getRecordRowByID('branch', 'branch_id', $fa['branch_id']);
            $fa['currency'] = $branchRec['currency'];
        } else {
            $fa['currency'] = $cpCfg['m.project.baseCurrency'];
        }
                    
        $id = $fn->addRecord($fa);
        $fn->returnAfterNewSave($id);
    }

    /**
     *
     */
    function getEditValidate() {
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();
        //$validate->validateData('title', 'Please enter the title');

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     */
    function getSave($tableName = '', $keyField = '', $keyFieldVal = '') {
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $validate = Zend_Registry::get('validate');
        $db = Zend_Registry::get('db');

        if (!$this->getEditValidate()){
            return $validate->getErrorMessageXML();
        }

        $fa = $this->getFields();
        $id = $fn->saveRecord($fa, $tableName, $keyField, $keyFieldVal);
        if (strtolower($fa['status']) == 'cancelled'){
            $SQL = "
            UPDATE opportunity
            SET status = 'Cancelled'
            WHERE project_id = {$id}
            ";

            $db->sql_query($SQL);
        }
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
        $fa = $fn->addToFieldsArray($fa, 'project_code');
        $fa = $fn->addToFieldsArray($fa, 'per_completed');
        $fa = $fn->addToFieldsArray($fa, 'actual_finish_date');
        $fa = $fn->addToFieldsArray($fa, 'description');
        $fa = $fn->addToFieldsArray($fa, 'title');
        $fa = $fn->addToFieldsArray($fa, 'quote_ref');
        $fa = $fn->addToFieldsArray($fa, 'paid_on');
        $fa = $fn->addToFieldsArray($fa, 'deposit_inv_ref');
        $fa = $fn->addToFieldsArray($fa, 'invoice');
        $fa = $fn->addToFieldsArray($fa, 'payment_terms');
        $fa = $fn->addToFieldsArray($fa, 'contact_id');
        $fa = $fn->addToFieldsArray($fa, 'company_id');
        $fa = $fn->addToFieldsArray($fa, 'staff_id');
        $fa = $fn->addToFieldsArray($fa, 'project_manager_id');
        $fa = $fn->addToFieldsArray($fa, 'category');
        $fa = $fn->addToFieldsArray($fa, 'start_date');
        $fa = $fn->addToFieldsArray($fa, 'project_value');
        $fa = $fn->addToFieldsArray($fa, 'project_value_base');
        $fa = $fn->addToFieldsArray($fa, 'project_value_ref');
        $fa = $fn->addToFieldsArray($fa, 'project_commission');
        $fa = $fn->addToFieldsArray($fa, 'notes');
        $fa = $fn->addToFieldsArray($fa, 'client_type');
        $fa = $fn->addToFieldsArray($fa, 'difficulty');
        $fa = $fn->addToFieldsArray($fa, 'target_left');
        $fa = $fn->addToFieldsArray($fa, 'estimated_finish_date');
        $fa = $fn->addToFieldsArray($fa, 'status');
        $fa = $fn->addToFieldsArray($fa, 'stage');
        $fa = $fn->addToFieldsArray($fa, 'branch_id');
        $fa = $fn->addToFieldsArray($fa, 'currency');
        $fa = $fn->addToFieldsArray($fa, 'product_id');

        $fa = $fn->addToFieldsArray($fa, 'date_of_incorporation');
        $fa = $fn->addToFieldsArray($fa, 'last_agm_date');
        $fa = $fn->addToFieldsArray($fa, 'last_ar_date');
        $fa = $fn->addToFieldsArray($fa, 'date_of_accounts_laid');
        $fa = $fn->addToFieldsArray($fa, 'next_agm_due_date');
        $fa = $fn->addToFieldsArray($fa, 'form_c_filed_date');
        $fa = $fn->addToFieldsArray($fa, 'form_cs');
        $fa = $fn->addToFieldsArray($fa, 'eci');
        

        return $fa;
    }

    /**
     *
     */
    function getProjectValueSumSQL($fld_name) {
        $tv = Zend_Registry::get('tv');

        $extraTableNames = "";

        if ($tv['staff_id'] != "") {
            $extraTableNames .= "project_staff ps_hist,";
        }

        if ($fld_name == 'still_to_bill') {
            $invSQL = "(
            SELECT SUM(invoice_amount)
            FROM invoice i
            WHERE i.project_id = p.project_id
            )
            ";

            $fld = "
            SELECT FORMAT(SUM(p.project_value - IF(ISNULL({$invSQL}),0, {$invSQL})), 0)
            ";
        } else {
            $fld = "
            SELECT FORMAT(SUM(p.{$fld_name}), 0)
            ";
        }

        $SQL = "
        {$fld}
        FROM {$extraTableNames}
        project p
        LEFT JOIN (contact cont) ON (p.contact_id         = cont.contact_id )
        LEFT JOIN (company c)    ON (p.company_id         = c.company_id    )
        LEFT JOIN (service ser)  ON (p.service_id         = ser.service_id  )
        LEFT JOIN (staff s)      ON (p.project_manager_id = s.staff_id      )
        ";

        return $SQL;
    }

    /**
     *
     */
    function getProjectProjectProjectTaskLinkSQL($id) {

        return "
        SELECT a.task_id
              ,a.title AS title
              ,(
                    SELECT GROUP_CONCAT(
                        CONCAT_WS(' ', stf.first_name, stf.last_name)
                        ORDER BY CONCAT_WS(' ', stf.first_name, stf.last_name)
                        SEPARATOR ', ')
                    FROM staff stf, task_staff ts
                    WHERE ts.task_id   = a.task_id
                      AND stf.staff_id = ts.staff_id
              ) AS staff_names
              ,a.status
              ,date_format(a.due_date, '%d %b %Y') AS due_date
              ,FORMAT(a.estimated_hours,2)
              ,(SELECT SUM(hours) AS total_hours
                FROM timesheet ts
                WHERE ts.task_id = a.task_id
              )
        FROM project b
            ,task a
        WHERE a.project_id = b.project_id
          AND b.project_id = {$id}
        ORDER BY due_date
        ";

    }

    /**
     *
     */
    function getProjectProjectProjectInvoiceLinkSQL($id) {

        return "
        SELECT a.invoice_id
              ,a.invoice_code
              ,FORMAT(a.invoice_amount,0) AS invoice_amount
              ,a.status
              ,date_format(a.invoice_date, '%d %b %Y') AS invoice_date
              ,date_format(a.invoice_due_date, '%d %b %Y') AS invoice_due_date
              ,date_format(a.invoice_paid_date, '%d %b %Y') AS invoice_paid_date
        FROM project b
            ,invoice a
        WHERE a.project_id = b.project_id
          AND b.project_id = {$id}
        ORDER BY a.invoice_code
        ";

    }

    /**
     *
     */
    function getProjectProjectProjectScheduleLinkSQL($id) {

        return "
        SELECT a.schedule_id
              ,a.title AS title
              ,date_format(a.start_date, '%d %b %Y') AS start_date
              ,date_format(a.end_date, '%d %b %Y')
        FROM project b
            ,schedule a
        WHERE a.project_id = b.project_id
          AND b.project_id = {$id}
        ORDER BY start_date
        ";

    }

    /**
     *
     */
    function getProjectProjectProjectThirdPartyCostLinkSQL($id) {

        return "
        SELECT a.third_party_cost_id
              ,a.item_title AS item_title
              ,FORMAT(a.budget_amount,0)
              ,FORMAT(a.actual_amount,0)
        FROM project b
            ,third_party_cost a
        WHERE a.project_id = b.project_id
          AND b.project_id = {$id}
        ORDER BY item_title
        ";

    }

    /**
     *
     */
    function getProjectProjectCoreStaffLinkSQL($id) {

        return "
        SELECT a.staff_id
              ,CONCAT_WS(' ', a.first_name, a.last_name) AS title
              ,team
              ,staff_type
        FROM `staff` a, `project_staff` b
        WHERE a.staff_id = b.staff_id
          AND b.project_id = '{$id}'
        ORDER BY title
        ";

    }

    /**
     *
     */
    function getProjectProjectProjectCostingLinkSQL($id) {

        return "
        SELECT costing_id
              ,section
              ,category
              ,title
              ,sort_order
              ,hours
        FROM costing
        WHERE project_id = {$id}
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

        $file_name = "Project-" . date("d-m-Y") . ".xls";

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

        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Project ID');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Project Title');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Project Manager');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Project Category');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Client Contact');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Client Company');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Client Type');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Staff Name');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Service Title');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Start Date');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Estimated Finish Date');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Actual Finish Date');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Project Value');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Budget In House');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Used In House');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Budget 3rd Party');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Used 3rd Party');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '% Complete');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '% Used');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Total Man Hours');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Status');

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

            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['project_code']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['title']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['project_manager_name']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['category']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['contact_name']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['company_name']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['client_type']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['staff_name']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['service_title']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['start_date']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['estimated_finish_date']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['actual_finish_date']);

            $colStr = PHPExcel_Cell::stringFromColumnIndex($colc);
            $actSheet->getStyle("{$colStr}{$rowc}")->getNumberFormat()->setFormatCode('#,##');
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['project_value']);

            $colStr = PHPExcel_Cell::stringFromColumnIndex($colc);
            $actSheet->getStyle("{$colStr}{$rowc}")->getNumberFormat()->setFormatCode('#,##');
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['budget_inhouse']);

            $colStr = PHPExcel_Cell::stringFromColumnIndex($colc);
            $actSheet->getStyle("{$colStr}{$rowc}")->getNumberFormat()->setFormatCode('#,##');
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['used_inhouse']);

            $colStr = PHPExcel_Cell::stringFromColumnIndex($colc);
            $actSheet->getStyle("{$colStr}{$rowc}")->getNumberFormat()->setFormatCode('#,##');
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['budget_third_party']);

            $colStr = PHPExcel_Cell::stringFromColumnIndex($colc);
            $actSheet->getStyle("{$colStr}{$rowc}")->getNumberFormat()->setFormatCode('#,##');
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['used_third_party']);

            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['per_completed']);

            $colStr = PHPExcel_Cell::stringFromColumnIndex($colc);
            $actSheet->getStyle("{$colStr}{$rowc}")->getNumberFormat()->setFormatCode('#');
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['percentage_used']);

            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['used_hours']);
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
              'project_code'            => $phpExcel->getFldObj('Project ID')
             ,'title'                   => $phpExcel->getFldObj('Project Title')
             ,'project_manager_name'    => $phpExcel->getFldObj('Project Manager')
             ,'category'                => $phpExcel->getFldObj('Project Category')
             ,'contact_name'            => $phpExcel->getFldObj('Client Contact')
             ,'company_name'            => $phpExcel->getFldObj('Client Company')
             ,'client_type'             => $phpExcel->getFldObj('Client Type')
             ,'staff_name'              => $phpExcel->getFldObj('Staff Name')
             ,'service_title'           => $phpExcel->getFldObj('Service Title')
             ,'start_date'              => $phpExcel->getFldObj('Start Date')
             ,'estimated_finish_date'   => $phpExcel->getFldObj('Estimated Finish Date')
             ,'actual_finish_date'      => $phpExcel->getFldObj('Actual Finish Date')
             ,'project_value'           => $phpExcel->getFldObj('Project Value')
             ,'budget_inhouse'          => $phpExcel->getFldObj('Budget In House')
             ,'used_inhouse'            => $phpExcel->getFldObj('Used In House')
             ,'budget_third_party'      => $phpExcel->getFldObj('Budget 3rd Party')
             ,'used_third_party'        => $phpExcel->getFldObj('Used 3rd Party')
             ,'per_completed'           => $phpExcel->getFldObj('% Complete')
             ,'percentage_used'         => $phpExcel->getFldObj('% Used')
             ,'used_hours'              => $phpExcel->getFldObj('Total Man Hours')
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
    function getConvertOppToProject() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $cpUtil = Zend_Registry::get('cpUtil');
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');
        $media = Zend_Registry::get('media');

        $opportunity_id = $fn->getReqParam('opportunity_id');

        $SQL = "
        SELECT *
        FROM opportunity
        WHERE opportunity_id = {$opportunity_id}
        ";

        $result = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);

        if ($numRows > 0) {
            $row = $db->sql_fetchrow($result);

            $this->fieldsArray = array();
            $fa = & $this->fieldsArray;
            $fa['creation_date']      = date("Y-m-d H:i:s");
            $fa['title']              = $row['title'];
            $fa['contact_id']         = $row['contact_id'];
            $fa['company_id']         = $row['company_id'];
            $fa['staff_id']           = $row['staff_id'];
            $fa['project_manager_id'] = $row['project_manager_id'];
            $fa['category']           = $row['category'];
            $fa['status']             = "WIP";
            $fa['description']        = $row['description'];
            $fa['notes']              = $row['notes'];
            $fa['opportunity_id']     = $row['opportunity_id'];
            $fa['per_completed']      = 0;
            $fa['difficulty']         = $row['difficulty'];
            $fa['client_type']        = $row['client_type'];
            $fa['currency']           = $row['currency'];

            if ($cpCfg['m.project.hasQuotingModule'] == 0) {
                $fa['project_value'] = is_numeric($row['estimated_value']) ? $row['estimated_value'] : 0;
            }

            if ($cpCfg['m.project.project.startDateOnConversion'] == 'estimated_start_date') {
                $fa['start_date'] = $row['estimated_start_date'];
            } else {
                $fa['start_date'] = date("Y-m-d");
            }

            if ($cpCfg['m.project.hasQuotingModule'] == 1) {
                $fa['confirmed_quote_id'] = $row['confirmed_quote_id'] ;
            }

            if ($cpCfg['m.project.hasMultiBranches'] == 1 && $row['branch_id'] != ''){
                $branchRec = $fn->getRecordRowByID('branch', 'branch_id', $fa['branch_id']);
                $fa['branch_id'] = $row['branch_id'];
                $fa['currency'] = $branchRec['currency'];
            } else {
                $fa['currency'] = $cpCfg['m.project.baseCurrency'];
            }

            $fa['project_code'] = $this->getProjectCodeOnConvFromOpp($row['opportunity_id']);
            $id = $fn->addRecord($this->fieldsArray);

            $SQL = "UPDATE opportunity SET status = 'Win' WHERE opportunity_id = {$opportunity_id}";
            $result = $db->sql_query($SQL);

            //---------------------------------------//
            $SQL = "UPDATE opportunity SET project_id = {$id} WHERE opportunity_id = {$opportunity_id}";
            $result = $db->sql_query($SQL);

            if ($cpCfg['m.project.hasQuotingModule'] == 1) {
                $SQL = "UPDATE quote SET project_id = {$id} WHERE opportunity_id = {$opportunity_id}";
                $result = $db->sql_query($SQL);

                if ($row['confirmed_quote_id'] > 0) {
                    $fnMod = includeCPClass('ModuleFns', 'project_quote');
                    $fnMod->refreshValuesBasedOnConfirmedQuote($row['confirmed_quote_id']);
                }
            }

            if ($cpCfg['m.project.project.carryForwardTaskTimeFromOpp'] == 1) {
                /************ update task **********/
                $SQL = "UPDATE task SET project_id = {$id} WHERE opportunity_id = {$opportunity_id}";
                $result = $db->sql_query($SQL);

                /************ update timesheeet **********/
                $SQL = "UPDATE timesheet SET project_id = {$id} WHERE opportunity_id = {$opportunity_id}";
                $result = $db->sql_query($SQL);
            }

            /************ link staff to project **********/
            $SQL1 = "SELECT staff_id FROM opportunity_staff WHERE opportunity_id = {$opportunity_id}";
            $result1 = $db->sql_query($SQL1);

            while ($row1 = $db->sql_fetchrow($result1)) {
                $staff_id = $row1['staff_id'];
                $SQL2 = "INSERT INTO project_staff (project_id, staff_id, creation_date, modification_date) VALUES ($id, $staff_id, NOW(), NULL)";
                $result2 = $db->sql_query($SQL2);

            }

            $media->getDuplicateMedia('opportunity', $opportunity_id, $id, 'project');
            //---------------------------------------//

            $cpUtil->redirect("index.php?_topRm={$tv['topRm']}&module={$tv['module']}&_action=edit&project_id={$id}");
        }
    }

    /**
     *
     */
    function getProjectCodeOnConvFromOpp($opportunity_id) {
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $db = Zend_Registry::get('db');

        if ($cpCfg['m.project.oppurtunity.hasSameCode'] == 1) {
            $oppRec = $fn->getRecordRowByID('opportunity', 'opportunity_id', $opportunity_id);
            $project_code =  "P-" . substr($oppRec['opportunity_code'], 2);
        } else {
            $project_code = $fn->getSettingsValueByKey('projectCodePrefix') . $fn->getSettingsValueByKey('nextProjectCode');
            $SQL = "UPDATE setting SET value = (value+1) WHERE key_text = 'nextProjectCode'";
            $result = $db->sql_query($SQL);
        }

        return $project_code;
    }

    /**
     *
     */
    function getEditFromListValidate() {
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();
        $validate->validateData('per_completed', 'Please choose percentage completed');
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

    /**
     *
     */
    function getTaskSummaryByProject(){
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $result = Zend_Registry::get('result');
        $cpUtil = Zend_Registry::get('cpUtil');

        set_time_limit(50000);
        ini_set('memory_limit', '512M');

        require_once("PHPExcel.php");
        include 'PHPExcel/IOFactory.php';

        $file_name = "Project-Summary-" . date("d-m-Y") . ".xls";

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

        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Project Summary');
        $actSheet->getColumnDimension('A')->setWidth(35);
        $actSheet->getColumnDimension('B')->setWidth(25);
        $actSheet->getColumnDimension('C')->setWidth(15);
        $actSheet->getColumnDimension('D')->setWidth(15);
        $actSheet->getColumnDimension('E')->setWidth(15);
        $actSheet->getColumnDimension('F')->setWidth(50);

        /******************** FORMAT HEADER *******************/
        $reportHeader = array(
            'font' => array(
                'bold' => true
               ,'size' => 20
            )
        );

        $headStyle = array(
            'font' => array(
                'bold' => true
               ,'size' => 12
               ,'color' => array('rgb' => 'ffffff')
            ),
        	'fill' => array(
        		'type' => PHPExcel_Style_Fill::FILL_SOLID,
        		'startcolor' => array(
        			'rgb' => '000000',
        		),
        	),
        );

        $projectTitle = array(
            'font' => array(
                'bold' => true
               ,'size' => 12
               ,'color' => array('rgb' => 'ffffff')
            ),
        	'fill' => array(
        		'type' => PHPExcel_Style_Fill::FILL_SOLID,
        		'startcolor' => array(
        			'rgb' => 'A0A0A0',
        		),
        	),
        );

        $table = array(
        	'borders' => array(
        		'outline' => array(
        			'style' => PHPExcel_Style_Border::BORDER_THIN,
        			'color' => array('argb' => '#000'),
        		)
        	)
        );

        $table2 = array(
        	'fill' => array(
        		'type' => PHPExcel_Style_Fill::FILL_SOLID,
        		'startcolor' => array(
        			'rgb' => 'EFEFEF',
        		),
        	),
        	'borders' => array(
        		'allborders' => array(
        			'style' => PHPExcel_Style_Border::BORDER_THIN,
        			'color' => array('rgb' => 'CFCFCF'),
        		)
        	)
        );

        $lastCol    = $actSheet->getHighestColumn();
        $lastColInd = PHPExcel_Cell::columnIndexFromString($lastCol);

        for ($i=0; $i < $lastColInd; $i++){
            $colAlphabet = PHPExcel_Cell::stringFromColumnIndex($i);
            //$actSheet->getColumnDimension($colAlphabet)->setAutoSize(true);
        }

        $rowc++;
        $colc = 0;
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, "Title");
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, "Page");
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, "Due Date");
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, "Status");
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, "% Completed");
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, "Comments");

        $actSheet->getStyle("A1")->applyFromArray($reportHeader);
        $actSheet->getStyle("A2:F2")->applyFromArray($headStyle);
        $actSheet->mergeCells("A1:F1");

        //============================================================================= //
        while ($row = $db->sql_fetchrow($result)) {
            $colc = 0;
            $rowc++;

            $colStr = PHPExcel_Cell::stringFromColumnIndex($colc);

            $objPHPExcel->getActiveSheet()->getRowDimension($rowc)->setRowHeight(20);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['title']);
            $actSheet->mergeCells("{$colStr}{$rowc}:F{$rowc}");

            //============================================================================= //
            $actSheet->getStyle("A{$rowc}:D{$rowc}")->applyFromArray($projectTitle);

            $SQL2 = "
            SELECT task_id
                  ,title
                  ,due_date
                  ,status
            FROM task
            WHERE project_id = {$row['project_id']}
            ORDER BY due_date
            ";

            $result2 = $db->sql_query($SQL2);
            $numRows2 = $db->sql_numrows($result2);

            if ($numRows2 > 0){
                $startRow = $rowc;
            }

            while ($row2 = $db->sql_fetchrow($result2)) {
                $colc = 0;
                $rowc++;
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row2['title']);
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row2['due_date']);
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row2['status']);

                //============================================================================= //
                $colc = 0;

                $SQL3 = "
                SELECT *
                FROM task_history
                WHERE task_id = {$row2['task_id']}
                ORDER BY sort_order
                ";
                $result3 = $db->sql_query($SQL3);
                $numRows3 = $db->sql_numrows($result3);

                if ($numRows3 > 0){
                    $startRow2 = $rowc+1;
                }

                while ($row3 = $db->sql_fetchrow($result3)) {
                    $colc = 0;
                    $rowc++;
                    $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
                    $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row3['title']);
                    $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
                    $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row3['status']);
                    $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row3['percentage']);
                    $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row3['comments']);

                    $actSheet->getStyle("E{$rowc}")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                }

                if ($numRows3 > 0){
                    $actSheet->getStyle("B{$startRow2}:F{$rowc}")->applyFromArray($table2);
                }
                //============================================================================= //
            }

            if ($numRows2 > 0){
                //$actSheet->getStyle("A{$startRow}:D{$rowc}")->applyFromArray($table);
            }
            //============================================================================= //
        }

        $colStr = PHPExcel_Cell::stringFromColumnIndex($colc);
        $actSheet->getStyle("A1:{$colStr}{$rowc}")->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
    }

    /**
     *
     */
    function getExportSalesByMonth(){
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $cpUtil = Zend_Registry::get('cpUtil');

        set_time_limit(50000);
        ini_set('memory_limit', '512M');


        require_once("PHPExcel.php");
        include 'PHPExcel/IOFactory.php';

        $file_name = "Project-" . date("d-m-Y") . ".xls";

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

        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Month');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, "Sales {$cpCfg['m.project.refCurrency']}");

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
        $SQL = "
        SELECT DATE_FORMAT(start_date, '%M %Y') AS yearMonth
              ,SUM(`project_value_ref`) AS project_value_ref
        FROM `project`
        GROUP BY DATE_FORMAT(start_date, '%Y-%m')
        ";

        $result = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);
        //============================================================================= //
        while ($row = $db->sql_fetchrow($result)) {
            $colc = 0;
            $rowc++;

            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['yearMonth']);

            $colStr = PHPExcel_Cell::stringFromColumnIndex($colc);
            $actSheet->getStyle("{$colStr}{$rowc}")->getNumberFormat()->setFormatCode('#,##');
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['project_value_ref']);
        }

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
    }

    /**
     *
     */
    function getProjectServiceTotal($project_id) {
        $db = Zend_Registry::get('db');

        $SQL = "
        SELECT a.project_id
              ,SUM( (a.quantity * a.sale_price) * (1 - (a.discount /100)))  AS total
        FROM project_service a
        WHERE a.project_id = {$project_id}
        GROUP BY a.project_id
        ";

        $result = $db->sql_query($SQL);
        $row = $db->sql_fetchrow($result);

        return $row['total'];
    }

    /**
     *
     */
    function getUsedInhouseAmount($project_id) {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');

        if ($project_id == '') {
            $project_id = $fn->getReqParam('project_id');
        }

        $SQL = "
        SELECT sum(total_cost) AS total_cost
        FROM timesheet
        WHERE project_id = {$project_id}
        ";
        $result = $db->sql_query($SQL);
        $row = $db->sql_fetchrow($result);

        $total_cost = $row['total_cost'];

        return $total_cost;
    }

    /**
     *
     */
    function getInvoiceAmount($project_id) {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');

        if ($project_id == '') {
            $project_id = $fn->getReqParam('project_id');
        }

        $SQL = "
        SELECT sum(invoice_amount) AS total_invoice_amount
        FROM invoice
        WHERE project_id = {$project_id}
        ";
        $result = $db->sql_query($SQL);
        $row = $db->sql_fetchrow($result);

        $total_invoice_amount = $row['total_invoice_amount'];

        return $total_invoice_amount;
    }
    /**
     *
     */
    function getDuplicateProject(){
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUtil = Zend_Registry::get('cpUtil');
        $tv = Zend_Registry::get('tv');

        $project_id = $fn->getReqParam('project_id');

        $projRec  = $fn->getRecordRowByID('project', 'project_id', $project_id);
        //$compRec  = $fn->getRecordRowByID('company', 'company_id', $projRec['company_id']);
        $username = $fn->getSessionParam('userName');

        $fa = array();

        $fa['creation_date']     = date("Y-m-d H:i:s");
        $fa['title']       		 = $projRec['title'];
        $fa['status']       	 = $projRec['status'];
        $fa['client_type']       = $projRec['client_type'];
        $fa['difficulty']        = $projRec['difficulty'];
        $fa['category']          = $projRec['category'];
        $fa['start_date']        = date("Y-m-d");
        $fa['project_code'] = $fn->getSettingsValueByKey('projectCodePrefix') . $fn->getSettingsValueByKey('nextProjectCode');
        $SQL = "UPDATE setting SET value = (value+1) WHERE key_text = 'nextProjectCode'";
        $result = $db->sql_query($SQL);

        $id = $fn->addRecord($fa);

        $SQL = "
        SELECT a.*
        FROM task a
        WHERE a.project_id = {$project_id}
        ";
        $result  = $db->sql_query($SQL);

        while ($row = $db->sql_fetchrow($result)) {

            $fa = array();
            $fa['project_id']       = $id;
            $fa['title']            = $row['title'];
			$fa['due_date']         = date("Y-m-d");
            $fa['status']           = 'Due';
            $fa['category']   	 	= $row['category_type'];
            $fa['created_by']       = $username;
            $fa['chargeable']       = 1;
            $fa['published']        = 1;
            $fa['creation_date']    = date("Y-m-d H:i:s");

            $SQLInsert              = $dbUtil->getInsertSQLStringFromArray($fa, 'task');
            $resultUpdate           = $db->sql_query($SQLInsert);
		}

        $cpUtil->redirect("index.php?_topRm={$tv['topRm']}&module={$tv['module']}&_action=edit&record_id={$id}");

        return $text;
    }
}
