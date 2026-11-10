<?
class CP_Admin_Modules_EnggCrm_Project_Model extends CP_Common_Lib_ModuleModelAbstract
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
            $extraTableNames .= "enggCrm_staff ps_hist,";
        }

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
            LEFT JOIN (`order` o) ON (i.order_id = o.order_id)
            WHERE o.project_id = p.project_id
              AND LOWER(i.status) != 'cancelled'
        )
        ";

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
            ,{$additional_third_party_sql} AS additional_third_party
            ,(p.project_value - (IF(ISNULL({$still_to_bill_sql}),0, {$still_to_bill_sql}))) AS still_to_bill
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
        $yearMonthStart     = $fn->getReqParam('yearMonthStart');
        $yearMonthFinish    = $fn->getReqParam('yearMonthFinish');
        $project_month      = $fn->getReqParam('project_month');
        $start_date1        = $fn->getReqParam('start_date_1');
        $start_date2        = $fn->getReqParam('start_date_2');
        $end_date1          = $fn->getReqParam('end_date_1');
        $end_date2          = $fn->getReqParam('end_date_2');
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

            if ($contact_id != "") {
                $searchVar->sqlSearchVar[] = "p.contact_id   = {$contact_id}";
            }

            if ($service_id != "") {
                $searchVar->sqlSearchVar[] = "p.service_id   = {$service_id}";
            }

            if ($tv['status'] != "") {
                $searchVar->sqlSearchVar[] = "p.status   = '{$tv['status']}'";
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
            if ($cpCfg['m.enggCrm.project.defaultSQL'] != "" && $tv['searchDone'] == 0) {
                $searchVar->sqlSearchVar[] = $cpCfg['m.enggCrm.project.defaultSQL'];
            }

            if ($tv['status'] == '' && $tv['searchDone'] == 0){
                $searchVar->sqlSearchVar[] = $cpCfg['m.enggCrm.project.defaultSQL'];
            }
        }

        $searchVar->sortOrder = "p.project_code DESC";
    }

    /**
     *
     */
    function getNewValidate() {
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();
        $validate->validateData('title', 'Please enter the title');
        $validate->validateData('company_id', 'Please select company name');

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
        $fa['category'] = "Purchase";

        /*if ($cpCfg['m.enggCrm.hasQuotingModule'] == 1) {
            $fa['confirmed_quote_id'] = 0;
        }*/

        //-------------------------------------------------------//
        $fa['project_code'] = $fn->getSettingsValueByKey('projectCodePrefix') . $fn->getSettingsValueByKey('nextProjectCode');
        $SQL = "UPDATE setting SET value = (value+1) WHERE key_text = 'nextProjectCode'";
        $result = $db->sql_query($SQL);

        $fa['currency'] = $cpCfg['m.enggCrm.baseCurrency'];
                    
        $id = $fn->addRecord($fa);
        $fn->returnAfterNewSave($id);
    }

    /**
     *
     */
    function getEditValidate() {
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();
        $validate->validateData('title', 'Please enter the title');
        $validate->validateData('company_id', 'Please select company name');
        $validate->validateData('category', 'Please select category');
        $validate->validateData('start_date', 'Please select start date');
        $validate->validateData('estimated_finish_date', 'Please select estimated finish date');
        
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
        LEFT JOIN (quote q)      ON (p.project_id         = q.project_id)
        ";

        return $SQL;
    }

    /**
     *
     */
    function getEnggCrmProjectEnggCrmInvoiceLinkSQL($id) {

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
    function getEnggCrmProjectEnggCrmScheduleLinkSQL($id) {

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
    function getEnggCrmProjectEnggCrmThirdPartyCostLinkSQL($id) {

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
    function getEnggCrmProjectCoreStaffLinkSQL($id) {

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
    function getEnggCrmProjectEnggCrmEmployeeLinkSQL($id) {


        return "
        SELECT a.employee_id
              ,a.employee_name AS title
              ,a.employee_work_type
              ,IF(a.employee_work_type = 'Part time', add_hourly_rate, salary) AS employee_amt
        FROM `employee` a
        LEFT JOIN (project_employee pe) ON (pe.employee_id = a.employee_id)
        WHERE pe.project_id = {$id}
        ORDER BY title
       ";

    }

    /**
     *
     */
    function getEnggCrmProjectEnggCrmCostingLinkSQL($id) {

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
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['budget_third_party']);

            $colStr = PHPExcel_Cell::stringFromColumnIndex($colc);
            $actSheet->getStyle("{$colStr}{$rowc}")->getNumberFormat()->setFormatCode('#,##');
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['used_third_party']);

            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['per_completed']);

            $colStr = PHPExcel_Cell::stringFromColumnIndex($colc);
            $actSheet->getStyle("{$colStr}{$rowc}")->getNumberFormat()->setFormatCode('#');

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
             ,'contact_name'            => $phpExcel->getFldObj('Client Contact')
             ,'company_name'            => $phpExcel->getFldObj('Client Company')
             ,'staff_name'              => $phpExcel->getFldObj('Staff Name')
             ,'start_date'              => $phpExcel->getFldObj('Start Date')
             ,'estimated_finish_date'   => $phpExcel->getFldObj('Estimated Finish Date')
             ,'actual_finish_date'      => $phpExcel->getFldObj('Actual Finish Date')
             ,'project_value'           => $phpExcel->getFldObj('Project Value')
             ,'per_completed'           => $phpExcel->getFldObj('% Complete')
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
    function getConvertOppToProjectOld() {
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
            $fa['description']        = $row['description'];
            $fa['notes']              = $row['notes'];
            $fa['opportunity_id']     = $row['opportunity_id'];
            $fa['per_completed']      = 0;
            $fa['difficulty']         = $row['difficulty'];
            $fa['client_type']        = $row['client_type'];
            $fa['currency']           = $row['currency'];

            if ($cpCfg['m.enggCrm.hasQuotingModule'] == 0) {
                $fa['project_value'] = is_numeric($row['estimated_value']) ? $row['estimated_value'] : 0;
            }

            if ($cpCfg['m.enggCrm.project.startDateOnConversion'] == 'estimated_start_date') {
                $fa['start_date'] = $row['estimated_start_date'];
            } else {
                $fa['start_date'] = date("Y-m-d");
            }


            $fa['project_code'] = $this->getProjectCodeOnConvFromOpp($row['opportunity_id']);
            $id = $fn->addRecord($this->fieldsArray);

            $SQL = "UPDATE opportunity SET status = 'Win' WHERE opportunity_id = {$opportunity_id}";
            $result = $db->sql_query($SQL);

            //---------------------------------------//
            $SQL = "UPDATE opportunity SET project_id = {$id} WHERE opportunity_id = {$opportunity_id}";
            $result = $db->sql_query($SQL);

            $SQL = "UPDATE quote SET project_id = {$id} WHERE opportunity_id = {$opportunity_id}
            AND quote_status = 'Confirmed'";
            $result = $db->sql_query($SQL);

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

        if ($cpCfg['m.enggCrm.oppurtunity.hasSameCode'] == 1) {
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
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, "Sales {$cpCfg['m.enggCrm.refCurrency']}");

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
        $tv = Zend_Registry::get('tv');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUtil = Zend_Registry::get('cpUtil');

        $project_id = $fn->getReqParam('project_id');

        $projRec  = $fn->getRecordRowByID('project', 'project_id', $project_id);
        $username = $fn->getSessionParam('userName');

        $next_year = date('Y') + 1;

        $fa = array();
        $fa['creation_date']            = date("Y-m-d H:i:s");
        $fa['created_by']               = $fn->getSessionParam('userName');
        $fa['title']       		        = $projRec['title'];
        $fa['category']                 = $projRec['category'];
        $fa['company_id']               = $projRec['company_id'];
        $fa['contact_id']               = $projRec['contact_id'];
        $fa['status']                   = "WIP";
        $fa['start_date']               = $next_year . '-01-01';
        $fa['estimated_finish_date']    = $next_year . '-01-01';
        $fa['description']              = $projRec['description'];
        $fa['project_code']             = $fn->getSettingsValueByKey('projectCodePrefix') . $fn->getSettingsValueByKey('nextProjectCode');
        $SQL = "UPDATE setting SET value = (value+1) WHERE key_text = 'nextProjectCode'";
        $result = $db->sql_query($SQL);

        $id = $fn->addRecord($fa);

        /* CREATION OF QUOTE - START */
        $SQLQuote = "
        SELECT a.* FROM quote a
        WHERE a.quote_id = {$projRec['quote_id']}
          AND a.quote_status = 'Order Raised'
        ";
        $resultQuote = $db->sql_query($SQLQuote);
        $rowQuote = $db->sql_fetchrow($resultQuote);

        $faQuote = array();
        $faQuote['project_id']      = $id;
        $faQuote['quote_code']      = $fn->getSettingsValueByKey('quoteCodePrefix') . $fn->getSettingsValueByKey('nextQuoteCode');

        $cpSiteIdSession  = $fn->getSessionParam('cp_site_id');
        $appendSql = '';
        if ($cpCfg['cp.hasMultiUniqueSites']) {
            $appendSql = " AND site_id = {$cpSiteIdSession}";
        }
        $SQLq = "UPDATE setting SET value = (value+1) WHERE key_text = 'nextQuoteCode' {$appendSql}";
        $resultq = $db->sql_query($SQLq);

        $faQuote['quote_date']      = date("Y-m-d");
        $faQuote['quote_status']    = "Confirmed";
        $faQuote['creation_date']   = date("Y-m-d H:i:s");
        $faQuote['created_by']      = $fn->getSessionParam('userName');
        $faQuote['condition']       = $rowQuote['condition'];
        $faQuote['quote_sequence']  = "1";
        $faQuote['title']           = $rowQuote['title'];
        $faQuote['signatory_name']  = $rowQuote['signatory_name'];
        $faQuote['signatory_position'] = $rowQuote['signatory_position'];

        $id_quote = $fn->addRecord($faQuote, 'quote');
        /* CREATION OF QUOTE - END */

        $SQLProj = "UPDATE project SET quote_id = {$id_quote} WHERE project_id = {$id}";
        $resultProj = $db->sql_query($SQLProj);

        /* CREATION OF QUOTE ITEMS - START */
        $SQLqi = "
        SELECT b.* FROM quote_items b
        WHERE b.quote_id = {$projRec['quote_id']}
        ";
        $resultqi = $db->sql_query($SQLqi);
        while ($rowqi = $db->sql_fetchrow($resultqi)) {
            $faQi = array();
            $faQi['creation_date']  = date("Y-m-d H:i:s");
            $faQi['created_by']     = $fn->getSessionParam('userName');
            $faQi['description']    = $rowqi['description'];
            $faQi['amount']         = $rowqi['amount'];
            $faQi['title']          = $rowqi['title'];
            $faQi['quote_id']       = $id_quote;
            $faQi['quantity']       = $rowqi['quantity'];
            $faQi['project_id']     = $id;
            $faQi['unit']           = $rowqi['unit'];
            $faQi['remarks']        = $rowqi['remarks'];

            $id_quote_items = $fn->addRecord($faQi, 'quote_items');
        }

        /* CREATION OF QUOTE ITEMS - END */
        $cpUtil->redirect("index.php?_topRm={$tv['topRm']}&module={$tv['module']}&_action=edit&record_id={$id}");

        return $text;
    }

    /**
     *This function is working for convert to project
     */
    function getConvertToProject() {
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
            $fa['status']             = "WIP";
            $fa['company_id']         = $row['company_id'];
            $fa['project_manager_id'] = $row['project_manager_id'];
            $fa['category']           = $row['category'];
            $fa['notes']              = $row['notes'];
            $fa['opportunity_id']     = $row['opportunity_id'];
            $fa['per_completed']      = 0;
            $fa['start_date']         = date("Y-m-d");
        }

        $fa['project_code'] = $this->getProjectCodeOnConvFromOpp($row['opportunity_id']);
        $id = $fn->addRecord($this->fieldsArray);
              
        //---------------------------------------//
        $SQL = "UPDATE quote SET project_id = {$id} WHERE opportunity_id = {$opportunity_id} AND quote_status = 'Confirmed'";
        $result = $db->sql_query($SQL);

        $faOpp = array();
        $faOpp['status']   = 'Win';
        $faOpp['modification_date'] = date('Y-m-d H:i:s');
        $faOpp['modified_by'] = $fn->getSessionParam('userName');
        $fn->saveRecord($faOpp, 'opportunity', 'opportunity_id', $opportunity_id);

        $SQlForQuote ="
        SELECT *
        FROM quote
        WHERE opportunity_id = {$opportunity_id}
        AND quote_status = 'Confirmed'
        ";
        $resultForQuote = $db->sql_query($SQlForQuote);
        $rowForQuote = $db->sql_fetchrow($resultForQuote);

        $SQL = "UPDATE project SET quote_id = '{$rowForQuote['quote_id']}' WHERE opportunity_id = {$opportunity_id}";
        $result = $db->sql_query($SQL);

        $cpUtil->redirect("index.php?_topRm=project&module=enggCrm_opportunity&_action=edit&opportunity_id={$opportunity_id}");
    }

    /**
     * Line Item Edit Form Submit
     */
    function getEditForQuoteSubmit() {
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');

        $project_id       = $fn->getReqParam('project_id');
        $quote_id         = $fn->getReqParam('quote_id');
        $title            = $fn->getPostParam('title');
        $quote_date       = $fn->getPostParam('quote_date');
        $quote_status     = $fn->getPostParam('quote_status');
        $condition        = $fn->getPostParam('condition');

        $fa = array();
        $fa['title']             = $title;
        $fa['quote_date']        = $quote_date;
        $fa['quote_status']      = $quote_status;
        $fa['condition']         = $condition;

        $whereCondition = "WHERE quote_id = {$quote_id}";
        $SQL = $dbUtil->getUpdateSQLStringFromArray($fa, "quote", $whereCondition);
        $db->sql_query($SQL);

        // Checking the quote status is confirmed before and changing a new quote record the status will be confirmed old one will be Updated to new
        $sqlQuote = "
        SELECT quote_id FROM quote
        WHERE project_id = {$project_id}
        ";
        $resultQuote  = $db->sql_query($sqlQuote);
        $numRowsQuote = $db->sql_numrows($resultQuote);

        if($quote_status == 'Confirmed') {
            $SQL = "UPDATE project SET quote_id = '{$quote_id}' WHERE project_id = {$project_id}";
            $result = $db->sql_query($SQL);

            /* Checking whether opp has more than one quote to update other quotes to new except confirmed quote */
            if ($numRowsQuote > 1) {
                $sqlQuoteCondition = "
                SELECT * FROM quote 
                WHERE quote_status = 'Confirmed'
                  AND project_id = {$project_id}
                  AND quote_id != {$quote_id}
                ";
                $resultQuoteCondition  = $db->sql_query($sqlQuoteCondition);
                while ($rowQuoteCondition  = $db->sql_fetchrow($resultQuoteCondition)){
                    $sqlUpdateQuote = "
                    UPDATE quote SET quote_status  = 'New' WHERE quote_id = {$rowQuoteCondition['quote_id']}
                    ";
                    $resultQuote  = $db->sql_query($sqlUpdateQuote); 
                }
            }
        }

        return $validate->getSuccessMessageXML();
    }

    /**
     * Purchase Order Edit Form Submit
     */
    function getEditForPoSubmit() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $dbUtil = Zend_Registry::get('dbUtil');
        $validate = Zend_Registry::get('validate');

        $purchase_order_id      = $fn->getReqParam('purchase_order_id');
        $supplier_reference_no  = $fn->getPostParam('supplier_reference_no');
        $our_reference_no       = $fn->getPostParam('our_reference_no');
        $po_date                = $fn->getPostParam('po_date');
        $shipping_method        = $fn->getPostParam('shipping_method');
        $payment_terms          = $fn->getPostParam('payment_terms');
        $delivery_date          = $fn->getPostParam('delivery_date');
        $delivery_terms         = $fn->getPostParam('delivery_terms');

        $fa = array();
        $fa['supplier_reference_no']  = $supplier_reference_no;
        $fa['our_reference_no']       = $our_reference_no;
        $fa['po_date']                = $po_date;
        $fa['shipping_method']        = $shipping_method;
        $fa['payment_terms']          = $payment_terms;
        $fa['delivery_date']          = $delivery_date;
        $fa['delivery_terms']         = $delivery_terms;

        if ($cpCfg['m.enggCrm.project.addShippingAddressInPO'] == 1) {
            $fa['shipping_address_flat']     = $fn->getPostParam('shipping_address_flat');
            $fa['shipping_address_street']   = $fn->getPostParam('shipping_address_street');
            $fa['shipping_address_country']  = $fn->getPostParam('shipping_address_country');
            $fa['shipping_address_po_code']  = $fn->getPostParam('shipping_address_po_code');
        }

        $fa = $fn->addModificationDetailsToFieldsArray($fa, 'purchase_order');

        $whereCondition = "WHERE purchase_order_id = {$purchase_order_id}";
        $SQL = $dbUtil->getUpdateSQLStringFromArray($fa, "purchase_order", $whereCondition);
        $db->sql_query($SQL);

        return $validate->getSuccessMessageXML();
    }

    /**
     * Line Item Edit Form Submit
     */
    function getEditLineItemSubmit() {
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');

       // if (!$this->getTimeSheetEditValidate()){
            //return $validate->getTimeSheetEditValidate();
       // }

        $opportunity_id  = $fn->getReqParam('opportunity_id');
        $quote_items_id  = $fn->getReqParam('quote_items_id');
                         
        $fa = array();

        $fa = $fn->addToFieldsArray($fa, 'title');
        $fa = $fn->addToFieldsArray($fa, 'amount');
        $fa = $fn->addToFieldsArray($fa, 'unit');
        $fa = $fn->addToFieldsArray($fa, 'quantity');
        $fa = $fn->addToFieldsArray($fa, 'description');
        $fa = $fn->addToFieldsArray($fa, 'remarks');
        $fa = $fn->addModificationDetailsToFieldsArray($fa, 'quote_items');

        $whereCondition = "WHERE quote_items_id = {$quote_items_id}";
        $SQL = $dbUtil->getUpdateSQLStringFromArray($fa, "quote_items", $whereCondition);
        $db->sql_query($SQL);

        $quote_item_rec = $fn->getRecordRowByID('quote_items', 'quote_items_id', $quote_items_id);
        $faQuote = array();
        $faQuote = $fn->addModificationDetailsToFieldsArray($faQuote, 'quote');
        $whereCondition = "WHERE quote_id = {$quote_item_rec['quote_id']}";
        $SQL = $dbUtil->getUpdateSQLStringFromArray($faQuote, 'quote', $whereCondition);
        $db->sql_query($SQL);

        return $validate->getSuccessMessageXML();

    }

    /**
     * Employee Item Edit Form Submit
     */
    function getEditEmployeeItemSubmit() {
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');

        $project_id             = $fn->getReqParam('project_id');
        $employee_id            = $fn->getReqParam('employee_id');
        $employee_timesheet_id  = $fn->getReqParam('employee_timesheet_id');


        $fa = array();

        $fa = $fn->addToFieldsArray($fa, 'employee_hours');
        $fa = $fn->addToFieldsArray($fa, 'description');
        $fa = $fn->addToFieldsArray($fa, 'date');

        $whereCondition = "WHERE employee_timesheet_id = {$employee_timesheet_id}";
        $SQL = $dbUtil->getUpdateSQLStringFromArray($fa, "employee_timesheet", $whereCondition);
        $db->sql_query($SQL);

        return $validate->getSuccessMessageXML();

    }

    /**
     *
     */
    function getAddLineItemForQuoteFormSubmit() {
        $fn       = Zend_Registry::get('fn');
        $db       = Zend_Registry::get('db');
        $dbUtil   = Zend_Registry::get('dbUtil');
        $validate = Zend_Registry::get('validate');

        if (!$this->getAddLineItemForQuoteFormValidate()){
            return $validate->getErrorMessageXML();
        }
       
        $opportunity_id = $fn->getReqParam('opportunity_id');
        $project_id     = $fn->getReqParam('project_id');
        $quote_id       = $fn->getPostParam('quote_id');
        $description    = $fn->getPostParam('description');
        $amount         = $fn->getPostParam('amount');

        $fa = array();
       
        $fa['opportunity_id']      = $opportunity_id;
        $fa['project_id']          = $project_id;
        $fa['quote_id']            = $quote_id;
        $fa['description']         = $description;
        $fa['amount']              = $amount;
        $fa['quantity']            = $fn->getPostParam('quantity');

        $fn->addRecord($fa, 'quote_items');

        return $validate->getSuccessMessageXML();

       
    }

    /**
     *
     */
    function getDeleteLineItem(){
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $formObj = Zend_Registry::get('formObj');
        $cpCfg = Zend_Registry::get('cpCfg');
        $quote_items_id    = $fn->getReqParam('quote_items_id');

            $deleteSQL    = "
            DELETE FROM quote_items
            WHERE quote_items_id = '{$quote_items_id}'
            ";
            $result = $db->sql_query($deleteSQL);

    }

    /**
     *
     */
    function getDeleteAddQuote(){
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $formObj = Zend_Registry::get('formObj');
        $cpCfg = Zend_Registry::get('cpCfg');

        $quote_id          = $fn->getReqParam('quote_id');
        $quote_items_id    = $fn->getReqParam('quote_items_id');

        $deleteQuoteSQL    = "
        DELETE FROM quote
        WHERE quote_id = '{$quote_id}'

        ";
        $result = $db->sql_query($deleteQuoteSQL);

        $quoteItemsSQL    = "
        SELECT quote_items_id FROM quote_items
        WHERE quote_id = '{$quote_id}'

        ";
        $resultQuoteItem = $db->sql_query($quoteItemsSQL);

        while ($rowQuoteItem = $db->sql_fetchrow($resultQuoteItem)) {
            $deleteQuoteItemsSQL    = "
            DELETE FROM quote_items
            WHERE quote_items_id = '{$rowQuoteItem['quote_items_id']}'

            ";
            $resultQuoteItemDelete = $db->sql_query($deleteQuoteItemsSQL);
        }

    }

    /**
     *
     */
    function getAddLineItemForQuoteFormValidate() {
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();
        $validate->validateData('description', 'Please enter the description');
        $validate->validateData('amount', 'Please enter the amount');

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     */
    function getAddItemForEmployeeFormValidate() {
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();
        $validate->validateData('date', 'Please select the date');
        $validate->validateData('employee_hours', 'Please enter the hours');

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }


    /**
     *
     */
    function getAddQuoteFormSubmit() {
        $fn       = Zend_Registry::get('fn');
        $db       = Zend_Registry::get('db');
        $dbUtil   = Zend_Registry::get('dbUtil');
        $validate = Zend_Registry::get('validate');
        $cpUtil = Zend_Registry::get('cpUtil');
        
        $project_id  = $fn->getReqParam('project_id');

        $fa = array();
        $fa['project_id']       = $project_id;
        $fa['condition']        = $fn->getSettingsValueByKey("quoteTermsAndCondition");
        $fa['quote_status']     = 'New';
        $fa['quote_date']       = date('Y-m-d');
        $fa['quote_code']       = $this->getUpdateAddQuoteCode();
        $fa['title']            = $fn->getSettingsValueByKey("cp.projectName");
        $fa = $fn->addModificationDetailsToFieldsArray($fa, 'quote');

        $fn->addRecord($fa, 'quote');           
    }


    /**
     *
     */
    function getAddHoursFromEmployeeSubmit() {
        $fn       = Zend_Registry::get('fn');
        $db       = Zend_Registry::get('db');
        $dbUtil   = Zend_Registry::get('dbUtil');
        $validate = Zend_Registry::get('validate');
        $cpUtil = Zend_Registry::get('cpUtil');

        if (!$this->getAddItemForEmployeeFormValidate()){
            return $validate->getErrorMessageXML();
        }

        $employee_id      = $fn->getPostParam('employee_id');
        $project_id       = $fn->getPostParam('project_id');
        $employee_hours   = $fn->getPostParam('employee_hours');
        $date             = $fn->getPostParam('date');
        $description      = $fn->getPostParam('description');


        $fa = array();

        $fa['employee_id']      = $employee_id;
        $fa['project_id']       = $project_id;
        $fa['employee_hours']   = $employee_hours;
        $fa['date']             = $date;
        $fa['description']      = $description;

        $fn->addRecord($fa, 'employee_timesheet');

        return $validate->getSuccessMessageXML();     
           
    }    

    /**
     * QUOTE AND QUOTE ITEMS DUPLICATE
     */
    function getDuplicateQuote() {
        $fn       = Zend_Registry::get('fn');
        $db       = Zend_Registry::get('db');
        $dbUtil   = Zend_Registry::get('dbUtil');
        $validate = Zend_Registry::get('validate');
        $cpUtil = Zend_Registry::get('cpUtil');
        
        $project_id      = $fn->getReqParam('project_id');
        $quote_id        = $fn->getReqParam('quote_id');

        $fa = array();

        $fa['project_id']       = $project_id;
        $fa['quote_status']     = 'New';
        $fa['quote_date']       = date('Y-m-d');
        $fa['quote_code']       = $this->getUpdateAddQuoteCode();

        $id = $fn->addRecord($fa, 'quote');

        $SQLQuoteItem = "
        SELECT *
        FROM quote_items 
        WHERE quote_id = {$quote_id}
        ";
        $resultQuoteItem = $db->sql_query($SQLQuoteItem);

        while ($rowQuoteItem = $db->sql_fetchrow($resultQuoteItem)) {

            $fa1 = array();
            $fa1['quote_id']         = $id;
            //$fa1['project_id']       = $project_id;
            $fa1['description']      = $rowQuoteItem['description'];
            $fa1['amount']           = $rowQuoteItem['amount'];
            $fa1['creation_date']    = date("Y-m-d H:i:s");

            $quote_items_id = $fn->addRecord($fa1, 'quote_items');


        }


    }    


    /**
     *
     */
    function getUpdateAddQuoteCode() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');

        /* Updation of Quote Code */
        $nextQuoteCode = $fn->getSettingsValueByKey("nextQuoteCode");

        if($nextQuoteCode < 10){
            $quoteCode = $fn->getSettingsValueByKey('quoteCodePrefix') . $nextQuoteCode;
        }
        else if($nextQuoteCode < 99){
            $quoteCode = $fn->getSettingsValueByKey('quoteCodePrefix'). $nextQuoteCode;
        }
        else if($nextQuoteCode > 99 || $nextOppCode < 999){
            $quoteCode = $fn->getSettingsValueByKey('quoteCodePrefix') . $nextQuoteCode;
        }
        else{
            $quoteCode = $fn->getSettingsValueByKey('quoteCodePrefix')  . $nextQuoteCode;
        }

        $cpSiteIdSession  = $fn->getSessionParam('cp_site_id');
        $appendSql = '';
        if ($cpCfg['cp.hasMultiUniqueSites']) {
            $appendSql = " AND site_id = {$cpSiteIdSession}";
        }
        $SQL    = "UPDATE setting SET value = (value+1) WHERE key_text = 'nextQuoteCode' {$appendSql}";
        $result = $db->sql_query($SQL);

        return $quoteCode;
    }    

    /**
     *
     */
    function getDeleteEmployeePortal(){
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $formObj = Zend_Registry::get('formObj');
        $cpCfg = Zend_Registry::get('cpCfg');

        $employee_id          = $fn->getReqParam('employee_id');
        $project_id           = $fn->getReqParam('project_id');

        $employeeSQL    = "
        DELETE FROM project_employee
        WHERE employee_id = '{$employee_id}'
        AND project_id = '{$project_id}'
        ";
        $resultEmployee = $db->sql_query($employeeSQL);

        $deleteEmployeeItemsSQL    = "
        DELETE FROM employee_timesheet
        WHERE employee_id = '{$employee_id}' 
        AND project_id = {$project_id}

        ";
        $resultEmployeeItemDelete = $db->sql_query($deleteEmployeeItemsSQL);

    }

    /**
     *Employee Delete Line Item
     */
    function getDeleteEmployeeItem(){
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $formObj = Zend_Registry::get('formObj');
        $cpCfg = Zend_Registry::get('cpCfg');
        $employee_timesheet_id    = $fn->getReqParam('employee_timesheet_id');

            $deleteEmployeeSQL    = "
            DELETE FROM employee_timesheet
            WHERE employee_timesheet_id = '{$employee_timesheet_id}'
            ";
            $result = $db->sql_query($deleteEmployeeSQL);

    }

    /**
     *
     */
    function getAddMultipleLineItemSubmit() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $validate = Zend_Registry::get('validate');

        $project_id      = $fn->getPostParam('project_id');
        $quote_id        = $fn->getPostParam('quote_id');
        $title_arr       = $fn->getPostParam('title', array());
        $description_arr = $fn->getPostParam('description', array());
        $unit_arr        = $fn->getPostParam('unit', array());
        $amount_arr      = $fn->getPostParam('amount', array());
        $quantity_arr    = $fn->getPostParam('quantity', array());
        $remarks_arr     = $fn->getPostParam('remarks', array());

        $count = count($title_arr);
        for ($i= 0; $i < $count; $i++) {
            $title       = $title_arr[$i];
            $description = $description_arr[$i];
            $unit        = $unit_arr[$i];
            $amount      = $amount_arr[$i];
            $quantity    = $quantity_arr[$i];
            $remarks     = $remarks_arr[$i];

            if ($description) {
                $projectRec = $fn->getRecordRowByID('project', 'project_id', $project_id);

                $fa = array();
                $fa['opportunity_id']   = $projectRec['opportunity_id'];
                $fa['project_id']       = $project_id;
                $fa['quote_id']         = $quote_id;
                $fa['title']            = $title;
                $fa['amount']           = $amount;
                $fa['quantity']         = $quantity;
                $fa['description']      = $description;
                $fa['unit']             = $unit;
                $fa['remarks']          = $remarks;
                $fa['creation_date']    = date('Y-m-d H:i:s');
                $fa['created_by']       = $fn->getSessionParam('userName');

                $insert = $dbUtil->getInsertSQLStringFromArray($fa, 'quote_items');
                $result = $db->sql_query($insert);
                $quote_items_id = $db->sql_nextid();

                $faQuote = array();
                $fa = $fn->addModificationDetailsToFieldsArray($faQuote, 'quote');
                $whereCondition = "WHERE quote_id = {$quote_id}";
                $SQL = $dbUtil->getUpdateSQLStringFromArray($fa, 'quote', $whereCondition);
                $db->sql_query($SQL);
            }
        }

        return $validate->getSuccessMessageXML();
    }

    /**
     * Add Materials submit in new window
     */
    function getAddMultipleMaterialsSubmit() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $validate = Zend_Registry::get('validate');

        $project_id      = $fn->getPostParam('project_id');
        $title_arr       = $fn->getPostParam('title', array());
        $amount_arr      = $fn->getPostParam('amount', array());
        $quantity_arr    = $fn->getPostParam('quantity', array());
        $description_arr = $fn->getPostParam('description', array());

        $count = count($title_arr);
        for ($i= 0; $i < $count; $i++) {
            $title       = $title_arr[$i];
            $amount      = $amount_arr[$i];
            $quantity    = $quantity_arr[$i];
            $description = $description_arr[$i];

            if ($title) {
                $fa = array();
                $fa['project_id']       = $project_id;
                $fa['title']            = $title;
                $fa['amount']           = $amount;
                $fa['quantity']         = $quantity;
                $fa['description']      = $description;
                $fa['status']           = 'Used';
                $fa['creation_date']    = date('Y-m-d H:i:s');
                $fa['created_by']       = $fn->getSessionParam('userName');

                $insert = $dbUtil->getInsertSQLStringFromArray($fa, 'project_materials');
                $result = $db->sql_query($insert);
                $project_materials_id = $db->sql_nextid();
            }
        }
        return $validate->getSuccessMessageXML();
    }

    /**
     * Generate order records from Project
     */
    function getGenerateOrderRecords(){
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');

        $project_id = $fn->getReqParam('project_id');
        $quote_id   = $fn->getReqParam('quote_id');

        $current_date = date('Y-m-d H:i:s');
        /* Update quote status */
        $faQuote = array();
        $faQuote['quote_status']   = 'Order Raised';
        $faQuote['modification_date'] = date('Y-m-d H:i:s');
        $faQuote['modified_by'] = $fn->getSessionParam('userName');
        $fn->saveRecord($faQuote, 'quote', 'quote_id', $quote_id);

        /* Creation of Order record */
        $quoteRec   = $fn->getRecordRowByID('quote', 'quote_id', $quote_id);
        $projRec    = $fn->getRecordRowByID('project', 'project_id', $project_id);
        $companyRow = $fn->getRecordRowByID('company', 'company_id', $projRec['company_id']);

        $faOrder = array();
        $faOrder['quote_id']                  = $quote_id;
        $faOrder['project_id']                = $project_id;
        $faOrder['company_id']                = $projRec['company_id'];
        $faOrder['contact_id']                = $projRec['contact_id'];
        $faOrder['project_type']              = $projRec['category'];
        $faOrder['quote_title']               = $quoteRec['title'];
        $faOrder['cust_company_name']         = $companyRow['company_name'];
        $faOrder['cust_address1']             = $companyRow['billing_address_flat'];
        $faOrder['cust_address2']             = $companyRow['billing_address_street'];
        $faOrder['cust_address_country']      = $companyRow['billing_address_country'];
        $faOrder['cust_address_po_code']      = $companyRow['billing_address_po_code'];
        $faOrder['record_type']               = $projRec['category'];
        $faOrder['discount']                  = $quoteRec['discount'];

        if ($companyRow['address_flat'] != '') {
            $faOrder['shipping_first_name']       = $companyRow['company_name'];
            $faOrder['shipping_address1']         = $companyRow['address_flat'];
            $faOrder['shipping_address2']         = $companyRow['address_street'];
            $faOrder['shipping_address_country']  = $companyRow['address_country'];
            $faOrder['shipping_address_po_code']  = $companyRow['address_po_code'];
        } else {
            $faOrder['shipping_first_name']       = $companyRow['company_name'];
            $faOrder['shipping_address1']         = $companyRow['billing_address_flat'];
            $faOrder['shipping_address2']         = $companyRow['billing_address_street'];
            $faOrder['shipping_address_country']  = $companyRow['billing_address_country'];
            $faOrder['shipping_address_po_code']  = $companyRow['billing_address_po_code'];
        }

        $faOrder['creation_date']             = date('Y-m-d H:i:s');
        $faOrder['created_by']                = $fn->getSessionParam('userName');
        $faOrder['order_status']              = 'New';
        $faOrder['order_date']                = date('Y-m-d');

        if ($projRec['category'] == 'Maintenance' || $projRec['category'] == 'Rental') {
            $faOrder['start_date']            = $projRec['start_date'];
            $faOrder['end_date']              = $projRec['estimated_finish_date'];
        }

        //check if the order record already exist or not
        $orderRec = $fn->getRecordByCondition('order', "project_id = '{$project_id}'");
        if(is_array($orderRec)){
            $whereCondition = "WHERE order_id = {$orderRec['order_id']}";
            $sqlUpdate = $dbUtil->getUpdateSQLStringFromArray($faOrder, "order", $whereCondition);
            $resultUpdate = $db->sql_query($sqlUpdate);
            $order_id = $orderRec['order_id'];
        } else {
            $SQLInsert = $dbUtil->getInsertSQLStringFromArray($faOrder, 'order');
            $resultInsert = $db->sql_query($SQLInsert);
            $order_id = $db->sql_nextid();
        }

        /* Creation of Order Item record */
        $SQLSelect = "
        SELECT qi.*
        FROM quote_items qi
        WHERE qi.quote_id = {$quote_id}
        ORDER BY qi.quote_items_id ASC
        ";
        $resultSelect = $db->sql_query($SQLSelect);
        while ($row = $db->sql_fetchrow($resultSelect)) {
            $faOi = array();
            $faOi['item_title']   = $row['title'];
            $faOi['qty']          = $row['quantity'];
            $faOi['unit']         = $row['unit'];
            $faOi['unit_price']   = $row['amount'];
            $faOi['description']  = $row['description'];
            $faOi['remarks']      = $row['remarks'];
            $faOi['record_id']    = $row['quote_items_id'];
            $faOi['order_id']     = $order_id;

            $orderItemRec = $fn->getRecordByCondition('order_item',
                                                      "record_id = '{$row['quote_items_id']}' AND order_id = {$order_id}");
            if(is_array($orderItemRec)){
                $whereCondition = "WHERE order_item_id = {$orderItemRec['order_item_id']}";
                $sqlOiUpdate = $dbUtil->getUpdateSQLStringFromArray($faOi, "order_item", $whereCondition);
                $resultOiUpdate      = $db->sql_query($sqlOiUpdate);
            } else {
                $SQLOI = $dbUtil->getInsertSQLStringFromArray($faOi, 'order_item');
                $resultOI = $db->sql_query($SQLOI);
            }
        }
    }

    /**
     * Update Material status to Cancelled
     */
    function getCancelMaterial() {
        $fn = Zend_Registry::get('fn');

        $project_materials_id = $fn->getReqParam('project_materials_id');        
        /* Update Project material status */
        $faPm = array();
        $faPm['status']   = 'Cancelled';
        $faPm['modification_date'] = date('Y-m-d H:i:s');
        $faPm['modified_by'] = $fn->getSessionParam('userName');
        $fn->saveRecord($faPm, 'project_materials', 'project_materials_id', $project_materials_id);
    }

    /**
     * Update Purchase order item to Cancelled
     */
    function getCancelPoItem() {
        $fn = Zend_Registry::get('fn');

        $po_product_id = $fn->getReqParam('po_product_id');        
        /* Update Purchase order item status */
        $faPo = array();
        $faPo['status']   = 'Cancelled';
        $faPo['modification_date'] = date('Y-m-d H:i:s');
        $faPo['modified_by'] = $fn->getSessionParam('userName');
        $fn->saveRecord($faPo, 'po_product', 'po_product_id', $po_product_id);
    }

    /**
     * Add Purchase order submit in new window
     */
    function getAddMultiplePurchaseOrderSubmit() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $dbUtil = Zend_Registry::get('dbUtil');
        $validate = Zend_Registry::get('validate');

        $project_id      = $fn->getReqParam('project_id');
        $supplier_id_arr = $fn->getPostParam('supplier_id', array());
        $title_arr       = $fn->getPostParam('title', array());
        $quantity_arr    = $fn->getPostParam('quantity', array());
        $unit_arr        = $fn->getPostParam('unit', array());
        $amount_arr      = $fn->getPostParam('amount', array());
        $description_arr = $fn->getPostParam('description', array());

        $count = count($title_arr);
        for ($i= 0; $i < $count; $i++) {
            $supplier_id = $supplier_id_arr[$i];
            $title       = $title_arr[$i];
            $quantity    = $quantity_arr[$i];
            $unit        = $unit_arr[$i];
            $amount      = $amount_arr[$i];
            $description = $description_arr[$i];

            if ($title) {
                /* Checking whether the supplier record is available for the project in purchase_order */
                $purchaseOrderRec = $fn->getRecordByCondition('purchase_order',
                                    "company_id_supplier = '{$supplier_id}' AND project_id = {$project_id}");
                if (is_array($purchaseOrderRec)){
                    $purchase_order_id = $purchaseOrderRec['purchase_order_id'];
                } else {
                    $faPo = array();
                    $faPo['project_id']          = $project_id;
                    $faPo['company_id_supplier'] = $supplier_id;
                    $faPo['creation_date']       = date('Y-m-d H:i:s');
                    $faPo['created_by']          = $fn->getSessionParam('userName');

                    if ($cpCfg['m.enggCrm.project.addShippingAddressInPO'] == 1) {
                        $country_po_code = explode(" ", $cpCfg['cp.addressPdf3']);

                        $faPo['shipping_address_flat']     = $cpCfg['cp.addressPdf1'];
                        $faPo['shipping_address_street']   = $cpCfg['cp.addressPdf2'];
                        $faPo['shipping_address_country']  = $country_po_code[0];
                        $faPo['shipping_address_po_code']  = $country_po_code[1];
                    }
                    //$faPo['po_code'] = $this->getUpdatePOCode();

                    $SQLInsert = $dbUtil->getInsertSQLStringFromArray($faPo, 'purchase_order');
                    $resultInsert = $db->sql_query($SQLInsert);
                    $purchase_order_id = $db->sql_nextid();
                }

                /* Saving Items for the purchase order in po_product */
                $fa = array();
                $fa['purchase_order_id'] = $purchase_order_id;
                $fa['item_title']        = $title;
                $fa['quantity']          = $quantity;
                $fa['unit']              = $unit;
                $fa['amount']            = $amount;
                $fa['description']       = $description;
                $fa['creation_date']     = date('Y-m-d H:i:s');
                $fa['status']            = 'Confirmed';
                $fa['created_by']        = $fn->getSessionParam('userName');

                $insert = $dbUtil->getInsertSQLStringFromArray($fa, 'po_product');
                $result = $db->sql_query($insert);
                $po_product_id = $db->sql_nextid();
            }
        }
        return $validate->getSuccessMessageXML();
    }

    /**
     * Add Timesheet Record submit validate
     */
    function getAddMultipleTimesheetValidate() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $validate = Zend_Registry::get('validate');

        /*$employee_id_arr = $fn->getPostParam('TimesheetEmployee_id', array());
        $count  = count($employee_id_arr);*/
        $TimesheetRatePerHr_arr  = $fn->getPostParam('TimesheetRatePerHr', array());
        $validate->resetErrorArray();

        /*$SQLInvoiceCheck = "
        SELECT i.start_date
              ,i.end_date
        FROM `invoice` i
        LEFT JOIN `order` o ON(o.project_id = {$project_id})
        WHERE i.status != 'Cancelled'
        AND i.order_id = o.order_id
        AND DATE_FORMAT(i.start_date, '%Y-%m') = '{$year}-{$month}'
        AND DATE_FORMAT(i.end_date, '%Y-%m') = '{$year}-{$month}'
        ";
        $resultInvoiceCheck   = $db->sql_query($SQLInvoiceCheck);
        $numRowsInvoiceCheck  = $db->sql_numrows($resultInvoiceCheck);
        if($numRowsInvoiceCheck > 0){
            $disabledInputHrly = "disabled=1";
            $hiddenHrlyRate = "<input type='hidden' value='{$rowTimesheet['hourly_rate']}' name='TimesheetRatePerHr[]'>";
        }*/

        foreach($TimesheetRatePerHr_arr as $key => $value){
            if($value == ''){
                $validate->errorArray['error_box']['name'] = "error_box";
                $validate->errorArray['error_box']['msg']  = "Please Enter Hourly Rate";
            }
        }

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }


    /**
     * Add Timesheet Record submit in new window
     */
    function getAddMultipleTimesheetRecordsSubmit() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $validate = Zend_Registry::get('validate');

        if (!$this->getAddMultipleTimesheetValidate()){
            return $validate->getErrorMessageXML();
        }

        $project_id              = $fn->getPostParam('project_id');
        $employee_id_arr         = $fn->getPostParam('TimesheetEmployee_id', array());
        $TimesheetRatePerHr_arr  = $fn->getPostParam('TimesheetRatePerHr', array());
        $yearSelected            = $fn->getPostParam('project_Time_year');
        $monthSelected           = $fn->getPostParam('project_Time_Month');

        $count = count($employee_id_arr);
        for ($i= 0; $i < $count; $i++) {

            $SQLInvoiceCheck = "
            SELECT i.start_date
                  ,i.end_date
            FROM `invoice` i
            LEFT JOIN `order` o ON(o.project_id = {$project_id})
            WHERE i.status != 'Cancelled'
            AND i.order_id = o.order_id
            AND DATE_FORMAT(i.start_date, '%Y-%m') = '{$yearSelected}-{$monthSelected}'
            AND DATE_FORMAT(i.end_date, '%Y-%m') = '{$yearSelected}-{$monthSelected}'
            ";
            $resultInvoiceCheck   = $db->sql_query($SQLInvoiceCheck);
            $numRowsInvoiceCheck  = $db->sql_numrows($resultInvoiceCheck);
            if($numRowsInvoiceCheck > 0){
                $TimesheetRatePerHr_arr  = $fn->getReqParam('TimesheetRatePerHr', array());
            }

            $employee_id = $employee_id_arr[$i];
            $RatePerHR   = $TimesheetRatePerHr_arr[$i];
            
            $count2 = cal_days_in_month(CAL_GREGORIAN, $monthSelected, $yearSelected);
            $dayCount = 1;
            for ($j= 0; $j < $count2; $j++) {
                $timesheetDate = $yearSelected.'-'.$monthSelected.'-'.$dayCount;
                
                $SQLInvoice = "
                SELECT i.start_date
                      ,i.end_date
                FROM `invoice` i
                LEFT JOIN `order` o ON(o.project_id = {$project_id})
                WHERE i.status != 'Cancelled'
                AND i.order_id = o.order_id
                AND '{$timesheetDate}' BETWEEN i.start_date AND i.end_date
                ";
                $resultInvoice   = $db->sql_query($SQLInvoice);
                $numRowsInvoice  = $db->sql_numrows($resultInvoice);

                $disabledInput = '';
                if($numRowsInvoice > 0){

                }else{
                    ${"day$dayCount"."_arr"} = $fn->getPostParam("TimesheetDaysProject"."$dayCount", array());
                    ${"day$dayCount"} = ${"day$dayCount"."_arr"}[$i];

                    if ($employee_id != '') {
                        if (${"day$dayCount"} != ''){

                            $SQLTimesheetExist ="
                            SELECT employee_hours
                                   ,hourly_rate
                            FROM `employee_timesheet`
                            WHERE project_id = {$project_id}
                            AND employee_id = {$employee_id}
                            AND date= '{$timesheetDate}'
                            "; 
                            $resultTimesheetExist  = $db->sql_query($SQLTimesheetExist);
                            $numRowsTimesheetExist = $db->sql_numrows($resultTimesheetExist);
                            $rowTimesheetExist = $db->sql_fetchrow($resultTimesheetExist);

                            if($numRowsTimesheetExist > 0){

                                if(${"day$dayCount"} != $rowTimesheetExist['employee_hours'] || $RatePerHR != $rowTimesheetExist['hourly_rate']){
                                    $fa1 = array();
                                    $fa1['date']           = $timesheetDate;
                                    $fa1['employee_hours'] = ${"day$dayCount"};
                                    $fa1['employee_id']    = $employee_id;
                                    $fa1['hourly_rate']    = $RatePerHR;
                                    $fa1['modification_date']  = date('Y-m-d H:i:s');
                                     
                                    $whereCondition = "WHERE project_id = {$project_id} AND employee_id = {$employee_id} AND date= '{$timesheetDate}'";
                                    $updateSQL = $dbUtil->getUpdateSQLStringFromArray($fa1, 'employee_timesheet', $whereCondition);
                                    $resultSQL =$db->sql_query($updateSQL);
                                }
                            }else{
                                 $fa = array();
                                 $fa['project_id']     = $project_id;
                                 $fa['employee_id']    = $employee_id;
                                 $fa['employee_hours'] = ${"day$dayCount"};
                                 $fa['date']           = $timesheetDate;
                                 $fa['hourly_rate']    = $RatePerHR;
                                 $fa['month']          = $monthSelected;
                                 $fa['creation_date']  = date('Y-m-d H:i:s');
                                 $fa['year']           = $yearSelected;

                                 $insert = $dbUtil->getInsertSQLStringFromArray($fa, 'employee_timesheet');
                                 $result = $db->sql_query($insert);
                            }
                             
                        }

                    }
                }
                $dayCount++;
            }

        }
        return $validate->getSuccessMessageXML();
    }
}
