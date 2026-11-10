<?
class CP_Admin_Modules_EnggCrm_Opportunity_Model extends CP_Common_Lib_ModuleModelAbstract
{
    /**
     *
     */
    function getSQL() {
        $tv = Zend_Registry::get('tv');
        $sqlMaster = Zend_Registry::get('sqlMaster');
        $cpCfg = Zend_Registry::get('cpCfg');

        $extraTableNames = '';
        $joinTbls = '';
        $joinFlds = '';

        if ($tv['staff_id'] != "") {
            $extraTableNames .= "opportunity_staff os_hist,";
        }

        if ($sqlMaster->generateSQLWithOnlyKeyFldGC == 1) {
            $flds = "
            SELECT GROUP_CONCAT(o.opportunity_id SEPARATOR ', ') AS record_ids
            ";
        } else {
            $flds = "
            SELECT o.*
                  ,CONCAT_WS(' ', cont.first_name, cont.last_name) AS contact_name
                  ,CONCAT_WS(' ', ref.first_name, ref.last_name) AS ref_contact_name
                  ,c.company_name
                  ,c.company_size
                  ,c.source
                  ,c.industry
                  ,p.project_code
                  ,ser.title AS service_title
                  ,CONCAT_WS(' ', s.first_name, s.last_name) AS project_manager_name
            ";
        }

        $SQL = "
        {$flds}
        FROM {$extraTableNames}
        opportunity o
        LEFT JOIN (contact cont) ON (o.contact_id          = cont.contact_id)
        LEFT JOIN (contact ref)  ON (o.referrer_contact_id = ref.contact_id)
        LEFT JOIN (company c)    ON (o.company_id          = c.company_id)
        LEFT JOIN (service ser)  ON (o.service_id          = ser.service_id)
        LEFT JOIN (staff s)      ON (o.project_manager_id  = s.staff_id)
        LEFT JOIN (valuelist VL) ON (o.chance              = VL.value AND VL.key_text = 'opportunityChance')
        LEFT JOIN (project p)    ON (p.project_id          = o.project_id)
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

        $title              = $fn->getReqParam('title');
        $category           = $fn->getReqParam('category');
        $chance             = $fn->getReqParam('chance');
        $company_id         = $fn->getReqParam('company_id');
        $service_id         = $fn->getReqParam('service_id');
        $opportunity_id     = $fn->getReqParam('opportunity_id');
        $project_manager_id = $fn->getReqParam('project_manager_id');
        $yearMonthStart     = $fn->getReqParam('yearMonthStart');

        if ($opportunity_id != "") {
            $searchVar->sqlSearchVar[] = "o.opportunity_id   = {$opportunity_id}";
        } else if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "o.opportunity_id   = {$tv['record_id']}";
        } else {
            $fn->setSearchVarForLinkData($searchVar, $linkRecType, 'o.opportunity_id');

            if ($title != '') {
                $searchVar->sqlSearchVar[] = "o.title LIKE '%{$title}%'";
            }

            if ($category != '') {
                $searchVar->sqlSearchVar[] = "o.category = '{$category}'";
            }

            if ($project_manager_id != '') {
                $searchVar->sqlSearchVar[] = "o.project_manager_id  = {$project_manager_id}";
            }

            if ($company_id != "") {
                $searchVar->sqlSearchVar[] = "o.company_id   = {$company_id}";
            }

            if ($service_id != "") {
                $searchVar->sqlSearchVar[] = "o.service_id   = {$service_id}";
            }

            if ($chance != "") {
                $searchVar->sqlSearchVar[] = "o.chance   = '{$chance}'";
            }

            if ($tv['status'] != "") {
                $searchVar->sqlSearchVar[] = "o.status   = '{$tv['status']}'";
            }

            if ($tv['keyword'] != "") {
                $searchVar->sqlSearchVar[] = "(
                    o.title            LIKE '%{$tv['keyword']}%'  OR
                    o.description      LIKE '%{$tv['keyword']}%'  OR
                    o.notes            LIKE '%{$tv['keyword']}%'  OR
                    o.opportunity_code LIKE '%{$tv['keyword']}%'  OR
                    c.company_name LIKE '%{$tv['keyword']}%'
                )";
            }

            if ($tv['staff_id'] != '') {
                $searchVar->sqlSearchVar[] = "o.opportunity_id = os_hist.opportunity_id";
                $searchVar->sqlSearchVar[] = "os_hist.staff_id = {$tv['staff_id']}";
            }

            //------------------------------------------------------------------------//
            $enquiry_date1         = $fn->getReqParam('enquiry_date_1');
            $enquiry_date2         = $fn->getReqParam('enquiry_date_2');
            $follow_up_date1       = $fn->getReqParam('follow_up_date_1');
            $follow_up_date2       = $fn->getReqParam('follow_up_date_2');
            $estimated_start_date1 = $fn->getReqParam('estimated_start_date_1');
            $estimated_start_date2 = $fn->getReqParam('estimated_start_date_2');

            if ($enquiry_date1 != "" && $enquiry_date2 != "" ) {
                $searchVar->sqlSearchVar[] = "(o.enquiry_date BETWEEN '{$enquiry_date1}' AND '{$enquiry_date2}')";
            } else if ($enquiry_date1 != "") {
                $searchVar->sqlSearchVar[] = "o.enquiry_date = '{$enquiry_date1}'";
            }

            if ($follow_up_date1 != "" && $follow_up_date2 != "" ){
                $searchVar->sqlSearchVar[] = "(o.follow_up_date BETWEEN '{$follow_up_date1}' AND '{$follow_up_date2}')";
            } else if ($follow_up_date1 != ""){
                $searchVar->sqlSearchVar[] = "o.follow_up_date = '{$follow_up_date1}'";
            }


            if ($estimated_start_date1 != "" && $estimated_start_date2 != "" ) {
                $searchVar->sqlSearchVar[] = "(o.estimated_start_date BETWEEN '{$estimated_start_date1}' AND '{$estimated_start_date2}')";
            } else if ($estimated_start_date1 != ""){
                $searchVar->sqlSearchVar[] = "o.estimated_start_date = '{$estimated_start_date1}'";
            }

            if ($yearMonthStart != '') {
                $searchVar->sqlSearchVar[] = "DATE_FORMAT(o.enquiry_date, '%Y-%m') = '{$yearMonthStart}'";
            }

            if ($tv['special_search'] == "Flagged") {
                $searchVar->sqlSearchVar[] = "o.flag = 1";
            }

            if ($tv['special_search'] == "Not-Flagged") {
                $searchVar->sqlSearchVar[] = "(o.flag != 1 OR o.flag IS null)";
            }

            /*
            if ($tv['status'] == '' && $tv['searchDone'] == 0){
                $searchVar->sqlSearchVar[] = "(
                    LOWER(o.status) != 'cancelled'
                AND LOWER(o.status) != 'win'
                AND LOWER(o.status) != 'lost'
                AND LOWER(o.status) != 'follow up'
                AND o.status != ''
                )";
            }
            */
        }

        $searchVar->sortOrder = "o.opportunity_code DESC, o.enquiry_date DESC, o.status, c.company_name, VL.sort_order DESC";

        //print $searchVar->sortOrder . "<br>";
    }

    /**
     *
     */
    function getNewValidate() {
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();
        $validate->validateData('title', 'Please enter the title');
        $validate->validateData('company_id', 'Please select company name');
        $validate->validateData('category', 'Please select the category');


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

        if (!$this->getNewValidate()){
            return $validate->getErrorMessageXML();
        }

        $fa = $this->getFields();

        if ($cpCfg['m.enggCrm.hasQuotingModule'] == 1) {
            $fa['confirmed_quote_id'] = 0;
        }

        //-------------------------------------------------------//
        $fa['opportunity_code'] = $fn->getSettingsValueByKey('opportunityCodePrefix') . $fn->getSettingsValueByKey('nextOpportunityCode');
        $SQL = "UPDATE setting SET value = (value+1) WHERE key_text = 'nextOpportunityCode'";
        $result = $db->sql_query($SQL);

        if ($cpCfg['m.enggCrm.oppurtunity.hasSameCode'] == 1) {
            $nextOppCode = $fn->getSettingsValueByKey("nextOpportunityCode");
            $SQL = "UPDATE setting SET value = {$nextOppCode} WHERE key_text = 'nextProjectCode'";
            $result = $db->sql_query($SQL);
        }

        $fa['enquiry_date'] = date('Y-m-d');
        $fa['status'] = 'Enquiry';
        $fa['currency'] = 'SG$';


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
        $validate->validateData('status', 'Please select the status');
        $validate->validateData('company_id', 'Please select company name');
        $validate->validateData('category', 'Please select category');

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

        if (!$this->getEditValidate()){
            return $validate->getErrorMessageXML();
        }

        $fa = $this->getFields();
        $id = $fn->saveRecord($fa, $tableName, $keyField, $keyFieldVal);
        $this->getSendFollowUpEmail();

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
        $fa = $fn->addToFieldsArray($fa, 'title');
        $fa = $fn->addToFieldsArray($fa, 'quote_ref');
        $fa = $fn->addToFieldsArray($fa, 'company_id');
        $fa = $fn->addToFieldsArray($fa, 'contact_id');
        $fa = $fn->addToFieldsArray($fa, 'staff_id');
        $fa = $fn->addToFieldsArray($fa, 'project_manager_id');
        $fa = $fn->addToFieldsArray($fa, 'service_id');
        $fa = $fn->addToFieldsArray($fa, 'category');
        $fa = $fn->addToFieldsArray($fa, 'enquiry_date');
        $fa = $fn->addToFieldsArray($fa, 'follow_up_date');
        $fa = $fn->addToFieldsArray($fa, 'follow_up_needed');
        $fa = $fn->addToFieldsArray($fa, 'estimated_start_date');
        $fa = $fn->addToFieldsArray($fa, 'estimated_value');
        $fa = $fn->addToFieldsArray($fa, 'estimated_value_base');
        $fa = $fn->addToFieldsArray($fa, 'opportunity_code');
        $fa = $fn->addToFieldsArray($fa, 'other_cost');
        $fa = $fn->addToFieldsArray($fa, 'chance');
        $fa = $fn->addToFieldsArray($fa, 'status');
        $fa = $fn->addToFieldsArray($fa, 'description');
        $fa = $fn->addToFieldsArray($fa, 'notes');
        $fa = $fn->addToFieldsArray($fa, 'client_type');
        $fa = $fn->addToFieldsArray($fa, 'difficulty');
        $fa = $fn->addToFieldsArray($fa, 'currency');
        $fa = $fn->addToFieldsArray($fa, 'referrer_contact_id');
        $fa = $fn->addToFieldsArray($fa, 'source_channel');
        $fa = $fn->addToFieldsArray($fa, 'rating_1');
        $fa = $fn->addToFieldsArray($fa, 'rating_2');
        $fa = $fn->addToFieldsArray($fa, 'rating_3');
        $fa = $fn->addToFieldsArray($fa, 'rating_4');

        $rating = isset($fa['rating_1']) ? $fa['rating_1'] : 0;
        $rating += isset($fa['rating_2']) ? $fa['rating_2'] : 0;
        $rating += isset($fa['rating_3']) ? $fa['rating_3'] : 0;
        $rating += isset($fa['rating_4']) ? $fa['rating_4'] : 0;
        $rating = round($rating / 4);

        $optionArr = array(
             1 => 'Very Low'
            ,2 => 'Low'
            ,3 => 'Normal'
            ,4 => 'High'
            ,5 => 'Very High'
        );

        if($rating != 0){
            $fa['chance'] = $optionArr[$rating];
        }
        return $fa;
    }

    /**
     *
     */
    function getOpportunityEstValueSQL() {
        $tv = Zend_Registry::get('tv');

        $extraTableNames = "";

        if ($tv['staff_id'] != "") {
            $extraTableNames .= "opportunity_staff os_hist,";
        }

        return "
        SELECT FORMAT(SUM(o.estimated_value), 0) AS est_value_sum
        FROM {$extraTableNames}
        opportunity o
        LEFT JOIN (company c)    ON (o.company_id   = c.company_id)
        LEFT JOIN (valuelist VL) ON (o.chance       = VL.value AND VL.key_text = 'opportunityChance')
        ";

    }

    /**
     *
     */
    function getEnggCrmOpportunityCoreStaffLinkSQL($id) {

        return "
        SELECT a.staff_id
              ,CONCAT_WS(' ', a.first_name, a.last_name) AS title
              ,a.team
              ,a.staff_type
        FROM `staff` a
            ,`opportunity_staff` b
        WHERE a.staff_id = b.staff_id
          AND b.opportunity_id = {$id}
        ORDER BY title
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

        $file_name = "Opportunity-" . date("d-m-Y") . ".xls";

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
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, "Project Title");
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, "Key Contact");
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, "Client Company");
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, "Project Manager");
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, "Enquiry Date");
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, "Follow-up Date");
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, "Estimated Start Date"  );
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, "Estimated Value");
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, "Status");
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, "Chance");

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

            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['opportunity_code']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['title']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['contact_name']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['company_name']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['project_manager_name']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['enquiry_date']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['follow_up_date']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['estimated_start_date']);
            $colStr = PHPExcel_Cell::stringFromColumnIndex($colc);
            $actSheet->getStyle("{$colStr}{$rowc}")->getNumberFormat()->setFormatCode('#,##');
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['estimated_value']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['status']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['chance']);
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
              'opportunity_code'    => $phpExcel->getFldObj('Code')
             ,'title'               => $phpExcel->getFldObj('Project Title')
             ,'contact_name'        => $phpExcel->getFldObj('Key Contact')
             ,'company_name'        => $phpExcel->getFldObj('Client Company')
             ,'project_manager_name'=> $phpExcel->getFldObj('Project Manager')
             ,'enquiry_date'        => $phpExcel->getFldObj('Enquiry Date')
             ,'follow_up_date'      => $phpExcel->getFldObj('Follow-up Date')
             ,'estimated_start_date'=> $phpExcel->getFldObj('Estimated Start Date')
             ,'estimated_value'     => $phpExcel->getFldObj('Estimated Value')
             ,'status'              => $phpExcel->getFldObj('Status')
             ,'chance'              => $phpExcel->getFldObj('Chance')
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

        $validate->validateData('chance', 'Please choose the chance');
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
    function getOpportunityCost($opportunity_id) {
        $db = Zend_Registry::get('db');

        $SQL = "
        SELECT sum(total_cost) as total_cost
        FROM timesheet
        WHERE opportunity_id = {$opportunity_id}
        ";
        $result = $db->sql_query($SQL);
        $row = $db->sql_fetchrow($result);

        $total_cost = $row['total_cost'];

        return $total_cost;
    }

    /**
     *
     */
    function getSendFollowUpEmail() {
        $db = Zend_Registry::get('db');

        $today = date("Y-m-d");

        $SQL = "
        SELECT a.*
            ,c.company_name FROM opportunity a
        LEFT JOIN (company c)    ON (a.company_id   = c.company_id  )
        WHERE a.follow_up_needed = 1
          AND a.follow_up_date = '$today'
        ";

        $result = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);

        $rowCounter = 0;
        while ($row = $db->sql_fetchrow($result)) {
            $project_manager_id = $row['project_manager_id'];

            $SQL1 = "
            SELECT  CONCAT_WS(' ', a.first_name, a.last_name) AS project_manager_name
                    ,a.email
            FROM staff a
            WHERE a.staff_id={$project_manager_id}
            ";
            $result1 = $db->sql_query($SQL1);
            $row1 = $db->sql_fetchrow($result1);

            $opportunity_id = $row['opportunity_id'];
            $opportunity_code = $row['opportunity_code'];
            $company_name = $row['company_name'];
            $title = $row['title'];
            $description = $row['description'];
            $follow_up_date = $row['follow_up_date'];
            $project_manager_name = $row1['project_manager_name'];
            $email = $row1['email'];

            $this->sendNotificationToProjectManager($title, $description, $follow_up_date, $project_manager_name, $email, $opportunity_code, $opportunity_id, $company_name);
            $rowCounter++;
        }
    }

    /**
     *
     */
    function sendNotificationToProjectManager($title, $description, $follow_up_date, $project_manager_name, $email, $opportunity_code, $opportunity_id, $company_name) {
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');

        $subject = "Opportunity Follow up: " . $title;
        $message = "
        <table cellpadding='0' width='400'>
            Dear {$project_manager_name},<br><br>
            Please note that the opportunity below has to be followed up today:<br>
            Opportunity Code : {$opportunity_code} <br>
            Client : {$company_name} <br>
            Opportunity Title : <u><a href='{$cpCfg['cp.siteUrl']}admin/index.php?_topRm=project&module=opportunity&_action=detail&opportunity_id={$opportunity_id}'>{$title}</a></u><br>
            Description : {$description}<br>
            Follow Up Date : {$follow_up_date}<br><br>
            Thank you,<br>
            {$cpCfg['cp.companyName']}
        </table>
        ";

        $SQLUpdate = "UPDATE opportunity set follow_up_needed = 0 WHERE opportunity_id = {$opportunity_id}";
        $result2 = $db->sql_query($SQLUpdate);

        $this->sendMailToProjectManager($project_manager_name, $subject, $message, $email);
    }

    /**
     *
     */
    function sendMailToProjectManager($project_manager_name, $subject, $message, $email) {
        $cpCfg = Zend_Registry::get('cpCfg');

        $toName = $project_manager_name;
        $toEmail = $email;

        $fromName = $cpCfg['cp.companyName'];
        $fromEmail = $cpCfg['companyEmail'];

        $smtpLocal = new SMTPLocal();
        $error = $smtpLocal->sendEmail($toName, $toEmail, $fromName, $fromEmail, $subject, $message);
    }

    /**
     *
     */
    function getDuplicate() {
        $db = Zend_Registry::get('db');
        $tv = Zend_Registry::get('tv');
        $cpUtil = Zend_Registry::get('cpUtil');
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');

        $opportunity_id = $fn->getReqParam('record_id');

        $SQL = "
        SELECT a.*
        FROM opportunity a
        WHERE a.opportunity_id = {$opportunity_id}
        ";
        $result = $db->sql_query($SQL);
        $row = $db->sql_fetchrow($result);

        $this->fieldsArray = array();
        $fa = & $this->fieldsArray;
        $fa['creation_date']        = date("Y-m-d H:i:s");
        $fa['opportunity_code']     = $row['opportunity_code'];
        $fa['title']                = $row['title'];
        $fa['company_id']           = $row['company_id'];
        $fa['contact_id']           = $row['contact_id'];
        $fa['client_type']          = $row['client_type'];
        $fa['difficulty']           = $row['difficulty'];
        $fa['project_manager_id']   = $row['project_manager_id'];
        $fa['category']             = $row['category'];
        $fa['enquiry_date']         = $row['enquiry_date'];
        $fa['follow_up_date']       = $row['follow_up_date'];
        $fa['follow_up_needed']     = $row['follow_up_needed'];
        $fa['estimated_start_date'] = $row['estimated_start_date'];
        $fa['estimated_value']      = $row['estimated_value'];
        $fa['opportunity_cost']     = $row['opportunity_cost'];
        $fa['other_cost']           = $row['other_cost'];
        $fa['chance']               = $row['chance'];
        $fa['status']               = $row['status'];
        $fa['description']          = $row['description'];
        $fa['notes']                = $row['notes'];

        $SQLNew = $dbUtil->getInsertSQLStringFromArray($fa, 'opportunity');
        $resultNew = $db->sql_query($SQLNew);
        $new_opportunity_id = $db->sql_nextid();
        //---------------------------------------------------------------//

        $SQL = "
        SELECT a.*
        FROM opportunity_staff a
        WHERE a.opportunity_id = {$opportunity_id}
        ";

        $result = $db->sql_query($SQL);

        while ($row = $db->sql_fetchrow($result)) {

            $fa = array();
            $fa['opportunity_id'] = $new_opportunity_id;
            $fa['staff_id']       = $row['staff_id'];
            $fa['creation_date']  = date("Y-m-d H:i:s");

            $SQLStaff = $dbUtil->getInsertSQLStringFromArray($fa, 'opportunity_staff');
            $resultStaff = $db->sql_query($SQLStaff);
            $opportunity_staff_id = $db->sql_nextid();
        }
        //---------------------------------------------------------------//

        $SQL = "
        SELECT a.*
        FROM task a
        WHERE a.opportunity_id = {$opportunity_id}
        ";

        $result = $db->sql_query($SQL);

        while ($row = $db->sql_fetchrow($result)) {

            $fa = array();
            $fa['opportunity_id']        = $new_opportunity_id;
            $fa['title']                 = $row['title'];
            $fa['description']           = $row['description'];
            $fa['status']                = $row['status'];
            $fa['due_date']              = $row['due_date'];
            $fa['chargeable']            = $row['chargeable'];
            $fa['project_manager_id']    = $row['project_manager_id'];
            $fa['category']              = $row['category'];
            $fa['estimated_hours']       = $row['estimated_hours'];
            $fa['staff_alert']           = $row['staff_alert'];
            $fa['project_manager_alert'] = $row['project_manager_alert'];

            $SQLTask = $dbUtil->getInsertSQLStringFromArray($fa, 'task');
            $resultTask = $db->sql_query($SQLTask);
            $task_id = $db->sql_nextid();
        }
        //---------------------------------------------------------------//

        $SQL = "
        SELECT a.*
        FROM media a
        WHERE a.opportunity_id = {$opportunity_id}
        ";

        $result = $db->sql_query($SQL);

        while ($row = $db->sql_fetchrow($result)) {

            $fa = array();
            $fa['opportunity_id']   = $new_opportunity_id;
            $fa['media_type']       = $row['media_type'];
            $fa['actual_file_name'] = $row['actual_file_name'];
            $fa['file_name']        = $row['file_name'];
            $fa['content_type']     = $row['content_type'];
            $fa['media_size']       = $row['media_size'];
            $fa['room_name']        = $row['room_name'];
            $fa['record_type']      = $row['record_type'];
            $fa['lang']             = $row['lang'];
            $fa['creation_date']    = date("Y-m-d H:i:s");

            $SQLMedia = $dbUtil->getInsertSQLStringFromArray($fa, 'media');
            $resultMedia = $db->sql_query($SQLMedia);
            $task_id = $db->sql_nextid();
        }
        //---------------------------------------------------------------//

        $SQL = "
        SELECT a.*
        FROM quote a
        WHERE a.opportunity_id = {$opportunity_id}
        ";

        $result = $db->sql_query($SQL);
        while ($row = $db->sql_fetchrow($result)) {

            $quote_sequence = $this->getNextQuoteSeq($new_opportunity_id);
            $fa = array();

            $fa['opportunity_id']   = $new_opportunity_id;
            $fa['quote_sequence']   = $quote_sequence;
            $fa['quote_date']       = date("Y-m-d H:i:s");
            $fa['quote_type']       = $row['quote_type'];
            $fa['currency_item']    = $row['currency_item'];
            $fa['status']           = 'Draft';
            $fa['note']             = $row['note'];
            $fa['condition']        = $row['condition'];
            $fa['creation_date']    = date("Y-m-d H:i:s");

            $SQLUpdate = $dbUtil->getInsertSQLStringFromArray($fa, 'quote');
            $resultUpdate = $db->sql_query($SQLUpdate);
            $new_quote_id = $db->sql_nextid();
        }
        $this->getUpdateQuoteCode($new_quote_id, $new_opportunity_id, $quote_sequence);
        //---------------------------------------------------------------//

        $SQL = "
        SELECT a.*
        FROM quote_category a
        WHERE a.quote_id = {$quote_id}
        ";
        $result = $db->sql_query($SQL);

        while ($row = $db->sql_fetchrow($result)) {

            $fa = array();
            $fa['quote_id']      = $new_quote_id;
            $fa['valuelist_id']  = $row['valuelist_id'];
            $fa['category_type'] = $row['category_type'];
            $fa['sort_order']    = $row['sort_order'];
            $fa['creation_date'] = date("Y-m-d H:i:s");

            $SQLUpdate = $dbUtil->getInsertSQLStringFromArray($fa, 'quote_category');
            $resultUpdate = $db->sql_query($SQLUpdate);
            $quote_category_id = $db->sql_nextid();

            $SQLQuoteItem = "
            SELECT a.*
            FROM quote_items a
            WHERE a.quote_category_id = {$row['quote_category_id']}
            ";
            $resultQuoteItem = $db->sql_query($SQLQuoteItem);

            while ($rowQuoteItem = $db->sql_fetchrow($resultQuoteItem)) {

                $fa = array();
                $fa['quote_category_id'] = $quote_category_id;
                $fa['quote_id']     = $new_quote_id;
                $fa['title']        = $rowQuoteItem['title'];
                $fa['item_type']    = $rowQuoteItem['item_type'];
                $fa['amount']       = $rowQuoteItem['amount'];
                $fa['amount_other'] = $rowQuoteItem['amount_other'];
                $fa['sort_order']   = $rowQuoteItem['sort_order'];
                $fa['creation_date'] = date("Y-m-d H:i:s");

                $SQLUpdate = $dbUtil->getInsertSQLStringFromArray($fa, 'quote_items');
                $resultUpdate = $db->sql_query($SQLUpdate);
                $quote_items_id = $db->sql_nextid();
            }
        }

        $cpUtil->redirect("index.php?_topRm={$tv['topRm']}&module={$tv['module']}" .
                        "&_action=edit&opportunity_id={$new_opportunity_id}");
    }

    /**
     *
     */
    function getNextQuoteSeq($opportunity_id) {
        $db = Zend_Registry::get('db');

        $SQL = "SELECT MAX(quote_sequence) FROM quote WHERE opportunity_id = {$opportunity_id}";
        $result = $db->sql_query($SQL);
        $row = $db->sql_fetchrow($result);

        return $row[0] + 1;
    }

    /**
     *
     */
    function getUpdateQuoteCode($quote_id, $opportunity_id, $sequence) {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');

        if ($quote_id != "") {
            $SQL = "
            SELECT opportunity_code
            FROM   opportunity
            WHERE  opportunity_id = {$opportunity_id}
                    ";
            $result = $db->sql_query($SQL);
            $row = $db->sql_fetchrow($result);

            $quote_prefix = $fn->getSettingsValueByKey("quoteCodePrefix");
            $SQL = "
            UPDATE quote
            SET    quote_code = CONCAT_WS('', '{$quote_prefix}', SUBSTRING('{$row['opportunity_code']}' FROM {$cpCfg['m.enggCrm.quote.CodeStartIndex']}), '-', '{$sequence}')
            WHERE  quote_id = {$quote_id}";
            $result = $db->sql_query($SQL);
        }
    }

    /**
     *
     */
    function getConfirmedQuoteIDJSON() {
        $fn = Zend_Registry::get('fn');

        $opportunity_id = $fn->getReqParam('opportunity_id');

        $arr = array();

        if ($opportunity_id != ""){
            $oppRec = $fn->getRecordRowByID('opportunity', 'opportunity_id', $opportunity_id);
            $arr['quote_id'] = ($oppRec['confirmed_quote_id'] != '') ? $oppRec['confirmed_quote_id'] : 0;
        }

        return json_encode($arr);
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

        $opportunity_id  = $fn->getReqParam('id');

        $fa = array();
        $fa['opportunity_id']   = $opportunity_id;
        $fa['condition']        = $fn->getSettingsValueByKey("quoteTermsAndCondition");
        $fa['quote_status']     = 'New';
        $fa['quote_date']       = date('Y-m-d');
        $fa['quote_code']       = $this->getUpdateAddQuoteCode();
        $fa['title']            = $fn->getSettingsValueByKey("cp.projectName");
        $fa = $fn->addModificationDetailsToFieldsArray($fa, 'quote');

        $fn->addRecord($fa, 'quote');
    }

    /**
     * Line Item Edit Form Submit
     */
    function getEditLineItemSubmit() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $validate = Zend_Registry::get('validate');

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
        $fa = $fn->addModificationDetailsToFieldsArray($faQuote, 'quote');
        $whereCondition = "WHERE quote_id = {$quote_item_rec['quote_id']}";
        $SQL = $dbUtil->getUpdateSQLStringFromArray($fa, 'quote', $whereCondition);
        $db->sql_query($SQL);

        return $validate->getSuccessMessageXML();

    }

    /**
     *
     */
    function getUpdateAddQuoteCode() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

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
    function getAddLineItemForQuoteFormSubmit() {
        $fn       = Zend_Registry::get('fn');
        $db       = Zend_Registry::get('db');
        $dbUtil   = Zend_Registry::get('dbUtil');
        $validate = Zend_Registry::get('validate');

        if (!$this->getAddLineItemForQuoteFormValidate()){
            return $validate->getErrorMessageXML();
        }

        $opportunity_id = $fn->getPostParam('opportunity_id');
        $quote_id       = $fn->getPostParam('quote_id');
        $description    = $fn->getPostParam('description');
        $amount         = $fn->getPostParam('amount');

        $fa = array();

        $fa['opportunity_id']      = $opportunity_id;
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
     * Line Item Edit Form Submit
     */
    function getEditForQuoteSubmit() {
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');

        $opportunity_id     = $fn->getReqParam('opportunity_id');
        $quote_id           = $fn->getReqParam('quote_id');
        $title              = $fn->getPostParam('title');
        $quote_date         = $fn->getPostParam('quote_date');
        $quote_status       = $fn->getPostParam('quote_status');
        $condition          = $fn->getPostParam('condition');

        $fa = array();
        $fa['title']             = $title;
        $fa['quote_date']        = $quote_date;
        $fa['quote_status']      = $quote_status;
        $fa['condition']         = $condition;
        $fa['modification_date'] = date("Y-m-d H:i:s");
        $fa['modified_by']       = $fn->getSessionParam('userName');

        $whereCondition = "WHERE quote_id = {$quote_id}";
        $SQL = $dbUtil->getUpdateSQLStringFromArray($fa, "quote", $whereCondition);
        $db->sql_query($SQL);

        // Checking the quote status is confirmed before and changing a new quote record the status will be confirmed old one will be Updated to new
        if($quote_status == 'Confirmed') {
            $sqlQuoteCondition = "
            SELECT *
            FROM quote
            WHERE quote_status = 'Confirmed'
            AND quote_id != {$quote_id}
            ";
            $resultQuoteCondition  = $db->sql_query($sqlQuoteCondition);

            $SQL = "UPDATE project SET quote_id = '{$quote_id}' WHERE opportunity_id = {$opportunity_id}";
            $result = $db->sql_query($SQL);
            while ($rowQuoteCondition  = $db->sql_fetchrow($resultQuoteCondition)){
                $sqlUpdateQuote = "
                UPDATE quote SET quote_status  = 'New' WHERE quote_id = {$rowQuoteCondition['quote_id']}
                ";
                $resultQuote  = $db->sql_query($sqlUpdateQuote);
            }
        }

        $SQL = "UPDATE project SET quote_id = '{$quote_id}' WHERE opportunity_id = {$opportunity_id}";
        $result = $db->sql_query($SQL);

        /* Update Opportunity value in Opportunity table */
        $quoteRec = $fn->getRecordCount('quote', "opportunity_id = {$opportunity_id} AND quote_status != 'Cancelled'");
        if ($quote_status == 'Cancelled') {
            /* If there is only one quote for Opp and which is also cancelled */
            $faOpp = array();

            if ($quoteRec == 0) {
                $faOpp['estimated_value'] = 0.00;
            } else {
                $sqlQuote = "
                SELECT quote_id
                FROM quote
                WHERE opportunity_id = {$opportunity_id}
                  AND quote_status != 'Cancelled'
                ORDER BY modification_date DESC
                LIMIT 0,1
                ";
                $resultQuote = $db->sql_query($sqlQuote);
                $rowQuote = $db->sql_fetchrow($resultQuote);

                $quote_id = $rowQuote['quote_id'];

                /* Updating Opp value to Opp if there is only one quote available */
                $total_quote_amount = $this->getFindTotalAmountOfQuote($opportunity_id, $quote_id);
                $faOpp['estimated_value'] = $total_quote_amount;
            }

            $faOpp = $fn->addModificationDetailsToFieldsArray($faOpp, 'opportunity');
            $whereCondition = "WHERE opportunity_id = {$opportunity_id}";
            $SQL = $dbUtil->getUpdateSQLStringFromArray($faOpp, 'opportunity', $whereCondition);
            $db->sql_query($SQL);
        } else if ($quote_status != 'Cancelled') {
            /* If quote is confirmed */
            $sqlQuote = "
            SELECT quote_id
            FROM quote
            WHERE opportunity_id = {$opportunity_id}
              AND quote_status != 'Cancelled'
            ORDER BY modification_date DESC
            LIMIT 0,1
            ";
            $resultQuote = $db->sql_query($sqlQuote);
            $rowQuote = $db->sql_fetchrow($resultQuote);

            $quote_id = $rowQuote['quote_id'];
            $total_quote_amount = $this->getFindTotalAmountOfQuote($opportunity_id, $quote_id);

            $faOpp = array();
            $faOpp['estimated_value'] = $total_quote_amount;
            $faOpp = $fn->addModificationDetailsToFieldsArray($faOpp, 'opportunity');
            $whereCondition = "WHERE opportunity_id = {$opportunity_id}";
            $SQL = $dbUtil->getUpdateSQLStringFromArray($faOpp, 'opportunity', $whereCondition);
            $db->sql_query($SQL);
        }

        return $validate->getSuccessMessageXML();

    }

    /**
     * QUOTE AND QUOTE ITEMS DUPLICATE
     */
    function getDuplicateQuote() {
        $fn       = Zend_Registry::get('fn');
        $db       = Zend_Registry::get('db');

        $opportunity_id  = $fn->getReqParam('opportunity_id');
        $quote_id        = $fn->getReqParam('quote_id');
        $add_line_item   = $fn->getReqParam('add_line_item');

        $quoteRec = $fn->getRecordRowByID('quote', 'quote_id', $quote_id);

        $fa = array();
        $fa['opportunity_id']   = $opportunity_id;
        $fa['title']            = $quoteRec['title'];
        $fa['condition']        = $fn->getSettingsValueByKey("quoteTermsAndCondition");
        $fa['quote_status']     = 'New';
        $fa['quote_date']       = date('Y-m-d');
        $fa['quote_code']       = $this->getUpdateAddQuoteCode();
        $id = $fn->addRecord($fa, 'quote');

        if ($add_line_item == 1) {
            $SQLQuoteItem = "
            SELECT *
            FROM quote_items
            WHERE quote_id = {$quote_id}
            ";
            $resultQuoteItem = $db->sql_query($SQLQuoteItem);
            while ($rowQuoteItem = $db->sql_fetchrow($resultQuoteItem)) {

                $fa1 = array();
                $fa1['opportunity_id']   = $opportunity_id;
                $fa1['quote_id']         = $id;
                $fa1['title']            = $rowQuoteItem['title'];
                $fa1['description']      = $rowQuoteItem['description'];
                $fa1['amount']           = $rowQuoteItem['amount'];
                $fa1['quantity']         = $rowQuoteItem['quantity'];
                $fa1['unit']             = $rowQuoteItem['unit'];
                $fa1['remarks']          = $rowQuoteItem['remarks'];
                $fa1['creation_date']    = date("Y-m-d H:i:s");
                $fa1['created_by']       = $fn->getSessionParam('userName');

                $quote_items_id = $fn->addRecord($fa1, 'quote_items');
            }
        }
    }

    /**
     *
     */
    function getAddMultipleLineItemSubmit() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $validate = Zend_Registry::get('validate');

        $opportunity_id  = $fn->getPostParam('opportunity_id');
        $quote_id        = $fn->getPostParam('quote_id');
        $description_arr = $fn->getPostParam('description', array());
        $title_arr       = $fn->getPostParam('title', array());
        $quantity_arr    = $fn->getPostParam('quantity', array());
        $unit_arr        = $fn->getPostParam('unit', array());
        $amount_arr      = $fn->getPostParam('amount', array());
        $remarks_arr     = $fn->getPostParam('remarks', array());

        $count = count($description_arr);
        for ($i= 0; $i < $count; $i++) {
            $description = $description_arr[$i];
            $title       = $title_arr[$i];
            $amount      = $amount_arr[$i];
            $unit        = $unit_arr[$i];
            $quantity    = $quantity_arr[$i];
            $remarks     = $remarks_arr[$i];

            if ($description) {
                $fa = array();
                $fa['opportunity_id']   = $opportunity_id;
                $fa['quote_id']         = $quote_id;
                $fa['description']      = $description;
                $fa['title']            = $title;
                $fa['unit']             = $unit;
                $fa['quantity']         = $quantity;
                $fa['amount']           = $amount;
                $fa['remarks']          = $remarks;
                $fa['creation_date']    = date('Y-m-d H:i:s');
                $fa['created_by']       = $fn->getSessionParam('userName');

                $insert = $dbUtil->getInsertSQLStringFromArray($fa, 'quote_items');
                $result = $db->sql_query($insert);
                $quote_items_id = $db->sql_nextid();

                $faQuote = array();
                $faQuote = $fn->addModificationDetailsToFieldsArray($faQuote, 'quote');
                $whereCondition = "WHERE quote_id = {$quote_id}";
                $SQL = $dbUtil->getUpdateSQLStringFromArray($faQuote, 'quote', $whereCondition);
                $db->sql_query($SQL);
            }
        }

        /* Update Opportunity value in Opportunity table */
        $quoteRec = $fn->getRecordCount('quote', "opportunity_id = {$opportunity_id} AND quote_status != 'Cancelled'");
        if ($quoteRec == 1) {
            /* Updating Opp value to Opp if there is only one quote available */
            $total_quote_amount = $this->getFindTotalAmountOfQuote($opportunity_id, $quote_id = '');

            $faOpp = array();
            $faOpp['estimated_value'] = $total_quote_amount;
            $faOpp = $fn->addModificationDetailsToFieldsArray($faOpp, 'opportunity');
            $whereCondition = "WHERE opportunity_id = {$opportunity_id}";
            $SQL = $dbUtil->getUpdateSQLStringFromArray($faOpp, 'opportunity', $whereCondition);
            $db->sql_query($SQL);
        } else if ($quoteRec > 1) {
            $sqlQuote = "
            SELECT quote_id
            FROM quote
            WHERE opportunity_id = {$opportunity_id}
              AND quote_status != 'Cancelled'
            ORDER BY modification_date DESC
            LIMIT 0,1
            ";
            $resultQuote = $db->sql_query($sqlQuote);
            $rowQuote = $db->sql_fetchrow($resultQuote);

            $quote_id = $rowQuote['quote_id'];

            /* Updating Opp value to Opp if there is only one quote available */
            $total_quote_amount = $this->getFindTotalAmountOfQuote($opportunity_id, $quote_id);

            $faOpp = array();
            $faOpp['estimated_value'] = $total_quote_amount;
            $faOpp = $fn->addModificationDetailsToFieldsArray($faOpp, 'opportunity');
            $whereCondition = "WHERE opportunity_id = {$opportunity_id}";
            $SQL = $dbUtil->getUpdateSQLStringFromArray($faOpp, 'opportunity', $whereCondition);
            $db->sql_query($SQL);
        }

        return $validate->getSuccessMessageXML();
    }

    /**
     *
     */
    function getFindTotalAmountOfQuote($opportunity_id, $quote_id) {
        $db = Zend_Registry::get('db');

        $sqlAppend = '';
        if ($quote_id != '') {
            $sqlAppend .= " AND quote_id = {$quote_id}";
        }

        $sqlQuoteItemsVal = "
        SELECT SUM(quantity * amount) AS total_quote_items_amount
        FROM quote_items
        WHERE opportunity_id = {$opportunity_id}
        {$sqlAppend}
        ";
        $resultQuoteItemsVal = $db->sql_query($sqlQuoteItemsVal);
        $rowQuoteItemsVal = $db->sql_fetchrow($resultQuoteItemsVal);

        $total_quote_amount = round($rowQuoteItemsVal['total_quote_items_amount'], 2);

        return $total_quote_amount;
    }
}
