<?
class CP_Admin_Modules_ManPower_Opportunity_Model extends CP_Common_Lib_ModuleModelAbstract
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

        if ($_SESSION['userGroupType'] == "User") {
            //$extraTableNames .= "opportunity_staff os_hist,";
            //$joinFlds .= ",os_hist.staff_id as history_staff_id";
        }

        if ($sqlMaster->generateSQLWithOnlyKeyFldGC == 1) {
            $flds = "
            SELECT GROUP_CONCAT(o.opportunity_id SEPARATOR ', ') AS record_ids
            ";
        } else {
            $flds = "
            SELECT o.*
                  ,CONCAT_WS(' ', cont.first_name, cont.last_name) AS contact_name
	              ,CONCAT_WS(' ', s.first_name, s.last_name) AS staff_name
                  ,cr.call_registry_code
                  ,c.company_name
                  ,c.company_size
                  ,c.source
                  ,p.project_code
                  ,st.title AS site_title
                  ,gc.name AS opportunity_country_name
                  {$joinFlds}
            ";
            /*$flds = "
            SELECT o.*
                  ,CONCAT_WS(' ', cont.first_name, cont.last_name) AS contact_name
	              ,CONCAT_WS(' ', s.first_name, s.last_name) AS staff_name
                  ,cr.call_registry_code
                  ,c.company_name
                  ,c.company_size
                  ,c.source
                  ,p.project_code
                  ,st.title AS site_title
                  ,gc.name AS opportunity_country_name
                  ,os_hist.staff_id as history_staff_id
            ";*/
        }
        //LEFT JOIN (opportunity_staff os_hist)   ON (o.opportunity_id = os_hist.opportunity_id)

        $SQL = "
        {$flds}
        FROM {$extraTableNames}
        opportunity o
        LEFT JOIN (candidate cont)   ON (o.candidate_id     = cont.candidate_id)
        LEFT JOIN (company c)        ON (o.company_id       = c.company_id)
        LEFT JOIN (project p)        ON (p.project_id       = o.project_id)
        LEFT JOIN (site st)          ON (o.site_id          = st.site_id)
        LEFT JOIN (call_registry cr) ON (o.call_registry_id = cr.call_registry_id)
        LEFT JOIN (staff s)			 ON (o.staff_id 		= s.staff_id)
        LEFT JOIN (geo_country gc)   ON (o.candidate_country = gc.country_code)
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
        $searchVar->mainTableAlias = 'o';

        $today  = date('Y-m-d');
        $title              = $fn->getReqParam('title');
        $category           = $fn->getReqParam('category');
        $chance             = $fn->getReqParam('chance');
        $company_id         = $fn->getReqParam('company_id');
        $service_id         = $fn->getReqParam('service_id');
        $staff_id           = $fn->getReqParam('staff_id');
        $opportunity_id     = $fn->getReqParam('opportunity_id');
        $project_manager_id = $fn->getReqParam('project_manager_id');
        $branch_id          = $fn->getReqParam('branch_id');
        $yearMonthStart     = $fn->getReqParam('yearMonthStart');
		$today_reminder     = $fn->getReqParam('today_reminder');
		$position     		= $fn->getReqParam('position');
        $position_type      = $fn->getReqParam('position_type');

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

            if ($staff_id != "") {
                $searchVar->sqlSearchVar[] = "o.staff_id = '{$staff_id}'";
            }

            if ($tv['status'] != "") {
                $searchVar->sqlSearchVar[] = "o.status   = '{$tv['status']}'";
            }

            if ($branch_id != "") {
                $searchVar->sqlSearchVar[] = "o.branch_id = '{$branch_id}'";
            }

			if($position != "") {
                $searchVar->sqlSearchVar[] = "o.position = '{$position}'";
			}

            if ($tv['keyword'] != "") {
                $searchVar->sqlSearchVar[] = "(
                    o.title            LIKE '%{$tv['keyword']}%'  OR
                    o.description      LIKE '%{$tv['keyword']}%'  OR
                    o.notes            LIKE '%{$tv['keyword']}%'  OR
                    o.opportunity_code LIKE '%{$tv['keyword']}%'  OR
                    o.position 		   LIKE '%{$tv['keyword']}%'  OR
                    c.company_name LIKE '%{$tv['keyword']}%'
                )";
            }

            /*if ($tv['staff_id'] != '') {
                $searchVar->sqlSearchVar[] = "o.opportunity_id = os_hist.opportunity_id";
                $searchVar->sqlSearchVar[] = "os_hist.staff_id = {$tv['staff_id']}";
            }*/

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

            if ($today_reminder == "Follow Up for Today") {
                $searchVar->sqlSearchVar[] = "o.follow_up_date = '{$today}'
                AND o.follow_up_needed =1";
            }

            if ($tv['special_search'] == "Flagged") {
                $searchVar->sqlSearchVar[] = "o.flag = 1";
            }

            if ($tv['special_search'] == "Not-Flagged") {
                $searchVar->sqlSearchVar[] = "(o.flag != 1 OR o.flag IS null)";
            }

            if ($_SESSION['userGroupType'] == "User") {
				$searchVar->sqlSearchVar[] = "o.opportunity_id = os_hist.opportunity_id";
                $searchVar->sqlSearchVar[] = "(s.staff_id  = '{$_SESSION['staff_id']}'
                                               OR
                                               os_hist.staff_id  = '{$_SESSION['staff_id']}')
                                               ";
            } else if ($_SESSION['userGroupType'] == "Agent") {
                $searchVar->sqlSearchVar[] = "o.status != 'Win'";
            }
        }

        $searchVar->sortOrder = "o.opportunity_id DESC, o.status, c.company_name";

        //print $searchVar->sortOrder . "<br>";
    }

    /**
     *
     */
    function getNewValidate() {
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();
        //$validate->validateData('title', 'Please enter the title');
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

        if (!$this->getNewValidate()){
            return $validate->getErrorMessageXML();
        }

        $fa = $this->getFields();

        if ($cpCfg['m.manPower.hasQuotingModule'] == 1) {
            $fa['confirmed_quote_id'] = 0;
        }

        //-------------------------------------------------------//
        if ($cpCfg['m.manPower.oppurtunity.hasSameCode'] == 1) {
            $nextOppCode = $fn->getSettingsValueByKey("nextOpportunityCode");
            $SQL = "UPDATE setting SET value = {$nextOppCode} WHERE key_text = 'nextProjectCode'";
            $result = $db->sql_query($SQL);
        }

        /*
        //This code is to get releant currency from branch table
        if ($cpCfg['m.manPower.hasMultiBranches'] == 1 && $fa['branch_id'] != ''){
            $branchRec = $fn->getRecordRowByID('branch', 'branch_id', $fa['branch_id']);
            $fa['currency'] = $branchRec['currency'];
        } else {
            $fa['currency'] = $cpCfg['m.manPower.baseCurrency'];
        }
        */

        //$fa['currency'] = $cpCfg['m.manPower.baseCurrency'];
        $fa['currency'] = 'US$';
        $fa['staff_id'] = $_SESSION['staff_id'];
        $fa['status']   = 'Candidate Search';
        $fa['candidate_country']   = 'US';
        $fa['no_of_position']   = 1;

        $modObj = getCPModuleObj('manPower_opportunity');
        $fa['opportunity_code'] = $modObj->model->getUpdateOpportunityCode();

        $fa['enquiry_date']     = date('Y-m-d');
        $fa['follow_up_date']   = date('Y-m-d', strtotime("+7 days"));

        $id = $fn->addRecord($fa);

        /* Linking Opportunity to Staff in history table */
        $faHist = array();
        $faHist['opportunity_id'] = $id;
        $faHist['staff_id']       = $_SESSION['staff_id'];
        $faHist['creation_date']  = date("Y-m-d H:i:s");
        $id_hist = $fn->addRecord($faHist, "opportunity_staff");

        $fn->returnAfterNewSave($id);
    }

    /**
     *
     */
    function getAddNewValuelistFormValidate($valuelist_name, $valuelist_value) {
        $db       = Zend_Registry::get('db');
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();
        $validate->validateData('valuelist_value', 'Please enter value');

        if ($valuelist_value) {
            $sql = "
            SELECT value FROM valuelist
            WHERE key_text = '{$valuelist_name}'
              AND value = '{$valuelist_value}'
            ";
            $result  = $db->sql_query($sql);
            $numRows = $db->sql_numrows($result);
            if ($numRows > 0) {
                $validate->errorArray['valuelist_value']['name'] = "valuelist_value";
                $validate->errorArray['valuelist_value']['msg']  = "Entered value already exists";
            }
        }

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     */
    function getAddNewValuelistFormSubmit() {
        $fn       = Zend_Registry::get('fn');
        $db       = Zend_Registry::get('db');
        $dbUtil   = Zend_Registry::get('dbUtil');
        $validate = Zend_Registry::get('validate');

        $valuelist_value = $fn->getPostParam('valuelist_value');
        $valuelist_name  = $fn->getReqParam('valuelist_name');
        $opportunity_id  = $fn->getReqParam('opportunity_id');

        if (!$this->getAddNewValuelistFormValidate($valuelist_name, $valuelist_value)){
            return $validate->getErrorMessageXML();
        }

        $fa = array();
        $fa['key_text']      = $valuelist_name;
        $fa['value']         = $valuelist_value;
        $fa['creation_date'] = date("Y-m-d H:i:s");

        $insert = $dbUtil->getInsertSQLStringFromArray($fa, 'valuelist');
        $result = $db->sql_query($insert);
        $id     = $db->sql_nextid();

        $fa1 = array();
        if($valuelist_name == "opportunityPosition"){
            $fa1['position'] = $valuelist_value;
            $fa1['title']    = $valuelist_value;
        }elseif ($valuelist_name == "callRegistryIndustry"){
            $fa1['industry'] = $valuelist_value;
        }elseif ($valuelist_name == "projectCategory"){
            $fa1['category'] = $valuelist_value;
        }

        $whereCondition = "WHERE opportunity_id = {$opportunity_id}";
        $sqlUpdate = $dbUtil->getUpdateSQLStringFromArray($fa1, "opportunity", $whereCondition);
        $resultUpdate      = $db->sql_query($sqlUpdate);

        return $validate->getSuccessMessageXML('', $valuelist_value);
    }

    /**
     *
     */
    function getEditValidate() {
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');

        $validate->resetErrorArray();

        //$validate->validateData('title', 'Please enter the title');
        $validate->validateData('status', 'Please select the status');
        $validate->validateData('position', 'Please select the position');
        $validate->validateData('position_type', 'Please select the position Type');
        $validate->validateData('no_of_position', 'Please enter no. of position');

        $client_hourly_rate = $fn->getReqParam('client_hourly_rate');
        $candidate_hourly_rate = $fn->getReqParam('candidate_hourly_rate');

        if ($client_hourly_rate < $candidate_hourly_rate) {
            $validate->errorArray['client_hourly_rate']['name'] = 'client_hourly_rate';
            $validate->errorArray['client_hourly_rate']['msg']  = 'Client hourly rate should be equal or greater then candidate hourly rate';
        }

        /*$salary = $fn->getPostParam('salary', '', true);
        if (!ctype_digit($salary)) {
            $validate->errorArray['salary']['name'] = "salary";
            $validate->errorArray['salary']['msg']  = "Please enter only nos eg: 1500";
        }

        $working_hours = $fn->getPostParam('working_hours', '', true);
        if (!ctype_digit($working_hours)) {
            $validate->errorArray['working_hours']['name'] = "working_hours";
            $validate->errorArray['working_hours']['msg']  = "Please enter only nos eg: 8";
        }

        $leave_year = $fn->getPostParam('leave_year', '', true);
        if (!ctype_digit($leave_year)) {
            $validate->errorArray['leave_year']['name'] = "leave_year";
            $validate->errorArray['leave_year']['msg']  = "Please enter only nos eg: 1";
        }

        $required_experience = $fn->getPostParam('required_experience', '', true);
        if (!ctype_digit($required_experience)) {
            $validate->errorArray['required_experience']['name'] = "required_experience";
            $validate->errorArray['required_experience']['msg']  = "Please enter only nos eg: 2";
        }

        /*
        $validate->validateData('salary', 'Please enter Salary');
        $validate->validateData('working_hours', 'Please enter Working Hours');
        $validate->validateData('leave_year', 'Please enter Leave days/Month');
        $validate->validateData('required_experience', 'Please enter Required Experience');
        */
        /*$validate->validateData('pass_type', 'Please select Pass Type');
        $validate->validateData('candidate_country', 'Please select Candidate Country');*/

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
        //$this->getSendFollowUpEmail();

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
        $fa = $fn->addToFieldsArray($fa, 'candidate_id', '', false, 'contact_id');
        $fa = $fn->addToFieldsArray($fa, 'contact_id');
        $fa = $fn->addToFieldsArray($fa, 'staff_id');
        $fa = $fn->addToFieldsArray($fa, 'project_manager_id');
        $fa = $fn->addToFieldsArray($fa, 'service_id');
        $fa = $fn->addToFieldsArray($fa, 'category');
        $fa = $fn->addToFieldsArray($fa, 'enquiry_date');
        $fa = $fn->addToFieldsArray($fa, 'reminder_date');
        $fa = $fn->addToFieldsArray($fa, 'follow_up_date');
        $fa = $fn->addToFieldsArray($fa, 'follow_up_needed');
        $fa = $fn->addToFieldsArray($fa, 'estimated_start_date');
        $fa = $fn->addToFieldsArray($fa, 'estimated_value');
        $fa = $fn->addToFieldsArray($fa, 'estimated_value_base');
        $fa = $fn->addToFieldsArray($fa, 'status');
        $fa = $fn->addToFieldsArray($fa, 'description');
        $fa = $fn->addToFieldsArray($fa, 'notes');
        $fa = $fn->addToFieldsArray($fa, 'client_type');
        $fa = $fn->addToFieldsArray($fa, 'difficulty');
        $fa = $fn->addToFieldsArray($fa, 'branch_id');
        $fa = $fn->addToFieldsArray($fa, 'currency');
        $fa = $fn->addToFieldsArray($fa, 'position');
        $fa = $fn->addToFieldsArray($fa, 'period_of_year');
        $fa = $fn->addToFieldsArray($fa, 'contract_date');
        $fa = $fn->addToFieldsArray($fa, 'candidate_country');
        $fa = $fn->addToFieldsArray($fa, 'working_hours');
        $fa = $fn->addToFieldsArray($fa, 'leave_year');
        $fa = $fn->addToFieldsArray($fa, 'salary');
        $fa = $fn->addToFieldsArray($fa, 'required_experience');
        $fa = $fn->addToFieldsArray($fa, 'pass_type');
        $fa = $fn->addToFieldsArray($fa, 'industry');
        $fa = $fn->addToFieldsArray($fa, 'client_hourly_rate');
        $fa = $fn->addToFieldsArray($fa, 'candidate_hourly_rate');
        $fa = $fn->addToFieldsArray($fa, 'position_type');
        $fa = $fn->addToFieldsArray($fa, 'no_of_position');
        $fa = $fn->addToFieldsArray($fa, 'work_state');
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
    function getProjectOpportunityProjectTaskLinkSQL($id) {

        return "
        SELECT a.task_id
              ,a.title AS title
              ,(
                    SELECT GROUP_CONCAT(
                        CONCAT_WS(' ', stf.first_name, stf.last_name)
                        ORDER BY CONCAT_WS(' ', stf.first_name, stf.last_name)
                        SEPARATOR ', '
                    )
                    FROM staff stf
                        ,task_staff ts
                    WHERE ts.task_id   = a.task_id
                      AND stf.staff_id = ts.staff_id
              ) AS staff_names
              ,a.status
              ,date_format(a.due_date, '%d %b %Y') AS due_date
              ,a.estimated_hours
              ,(SELECT SUM(hours) AS total_hours
                FROM timesheet ts
                WHERE ts.task_id = a.task_id
              )
        FROM opportunity b
            ,task a
        WHERE a.opportunity_id = b.opportunity_id
          AND b.opportunity_id = {$id}
        ORDER BY due_date
        ";

    }

    /**
     *
     */
    function getProjectOpportunityCoreStaffLinkSQL($id) {

        /*
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
        */
        return "
        SELECT a.staff_id
              ,CONCAT_WS(' ', a.first_name, a.last_name) AS title
              ,a.team
              ,a.staff_type
        FROM `opportunity_staff` b
        LEFT JOIN staff a ON (a.staff_id = b.staff_id)
        WHERE b.opportunity_id = {$id}
        ORDER BY title
        ";

    }

    /**
     *
     */
    function getExportData($dataArray){
        $phpExcel = includeCPClass('Lib', 'PhpExcelExportWrapper', 'PhpExcelExportWrapper');

        $fa = array(
              'opportunity_code'    => $phpExcel->getFldObj('Code')
             ,'title'               => $phpExcel->getFldObj('Opp Title')
             ,'contact_name'        => $phpExcel->getFldObj('Key Contact')
             ,'company_name'        => $phpExcel->getFldObj('Client Company')
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

        $opportunity_id = $fn->getReqParam('opportunity_id');
        $opportunityRec = $fn->getRecordRowByID('opportunity', 'opportunity_id', $opportunity_id);

        /* Creation of Opportunity records */
        $this->fieldsArray = array();
        $fa = & $this->fieldsArray;

        $modObj = getCPModuleObj('manPower_opportunity');
        $fa['opportunity_code'] = $modObj->model->getUpdateOpportunityCode();

        $fa['staff_id']               = $_SESSION['staff_id'];
        $fa['industry']               = $opportunityRec['industry'];
        $fa['category']               = $opportunityRec['category'];
        $fa['position']               = $opportunityRec['position'];
        $fa['position_type']          = $opportunityRec['position_type'];
        $fa['title']                  = $opportunityRec['title'];
        $fa['company_id']             = $opportunityRec['company_id'];
        $fa['contact_id']             = $opportunityRec['contact_id'];
        $fa['status']                 = $opportunityRec['status'];
        $fa['salary']                 = $opportunityRec['salary'];
        $fa['working_hours']          = $opportunityRec['working_hours'];
        $fa['leave_year']             = $opportunityRec['leave_year'];
        $fa['required_experience']    = $opportunityRec['required_experience'];
        $fa['pass_type']              = $opportunityRec['pass_type'];
        $fa['candidate_country']      = $opportunityRec['candidate_country'];
        $fa['currency']               = $opportunityRec['currency'];
        $fa['estimated_value']        = $opportunityRec['estimated_value'];
        $fa['description']            = $opportunityRec['description'];
        $fa['site_id']                = $opportunityRec['site_id'];
        $fa['history_opportunity_id'] = $opportunity_id;
        $fa = $fn->addCreationDetailsToFieldsArray($fa, 'opportunity');

        $new_opportunity_id = $fn->addRecord($fa);

        /* Linking Opportunity to Staff in history table */
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

            $SQLStaff               = $dbUtil->getInsertSQLStringFromArray($fa, 'opportunity_staff');
            $resultStaff            = $db->sql_query($SQLStaff);
            $opportunity_staff_id   = $db->sql_nextid();
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
            $fa['title']                = $row['title'];
            $fa['description']          = $row['description'];
            $fa['published']            = $row['published'];
            $fa['member_only']          = $row['member_only'];
            $fa['contact_id']           = $row['contact_id'];
            $fa['project_id']           = $row['project_id'];
            $fa['staff_id']             = $row['staff_id'];
            $fa['total_hours']          = $row['total_hours'];
            $fa['status']               = $row['status'];
            $fa['notes']                = $row['notes'];
            $fa['timesheet_id']         = $row['timesheet_id'];
            $fa['task_code']            = $row['task_code'];
            $fa['due_date']             = $row['due_date'];
            $fa['chargeable']           = $row['chargeable'];
            $fa['opportunity_id']       = $new_opportunity_id;
            $fa['project_manager_id']   = $row['project_manager_id'];
            $fa['category']             = $row['category'];
            $fa['estimated_hours']      = $row['estimated_hours'];
            $fa['staff_alert']          = $row['staff_alert'];
            $fa['project_manager_alert']= $row['project_manager_alert'];
            $fa['flag']                 = $row['flag'];
            $fa['service_id']           = $row['service_id'];
            $fa['release_task_status']  = $row['release_task_status'];
            $fa['site_id']              = $row['site_id'];
            $fa['from_date']            = $row['from_date'];
            $fa = $fn->addCreationDetailsToFieldsArray($fa, 'task');

            $SQLTask = $dbUtil->getInsertSQLStringFromArray($fa, 'task');
            $resultTask = $db->sql_query($SQLTask);
            $task_id = $db->sql_nextid();
        }
        //---------------------------------------------------------------//

        $SQL = "
        SELECT a.*
        FROM media a
        WHERE a.record_id = {$opportunity_id}
          AND room_name = 'manPower_opportunity'
        ";
        $result = $db->sql_query($SQL);
        while ($row = $db->sql_fetchrow($result)) {

            $fa = array();
            $fa['creation_date']    = date("Y-m-d H:i:s");
            $fa['media_type']       = $row['media_type'];
            $fa['actual_file_name'] = $row['actual_file_name'];
            $fa['display_title']    = $row['display_title'];
            $fa['file_name']        = $row['file_name'];
            $fa['content_type']     = $row['content_type'];
            $fa['media_size']       = $row['media_size'];
            $fa['room_name']        = $row['room_name'];
            $fa['record_type']      = $row['record_type'];
            $fa['lang']             = $row['lang'];
            $fa['alt_tag_data']     = $row['alt_tag_data'];
            $fa['external_link']    = $row['external_link'];
            $fa['caption']          = $row['caption'];
            $fa['chi_caption']      = $row['chi_caption'];
            $fa['sort_order']       = $row['sort_order'];
            $fa['record_id']        = $new_opportunity_id;
            $fa['description']      = $row['description'];
            $fa['internal_link']    = $row['internal_link'];
            $fa['site_id']          = $row['site_id'];

            $SQLMedia = $dbUtil->getInsertSQLStringFromArray($fa, 'media');
            $resultMedia = $db->sql_query($SQLMedia);
            $task_id = $db->sql_nextid();
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
            SET    quote_code = CONCAT_WS('', '{$quote_prefix}', SUBSTRING('{$row['opportunity_code']}' FROM {$cpCfg['m.project.quote.CodeStartIndex']}), '-', '{$sequence}')
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
    function getManPowerOpportunityManPowerCandidateLinkSQL($id) {

        return "
        SELECT oc.opportunity_candidate_id
              ,oc.candidate_id
              ,CONCAT_WS(' ', c.first_name, c.last_name) AS candidate_name
              ,oc.process_status
              ,oc.response_status
              ,oc.percent_win
              ,oc.agent_id
              ,a.agent_code
              ,(CONCAT_WS('', '<a href=\'index.php?module=manPower_opportunity&_spAction=convertOppToProject&candidate_id=', oc.candidate_id, '&opportunity_id=', {$id}, '\' target=\'_blank\'>Convert to Project</a>'))
              ,(CONCAT_WS('', '<a href=\'index.php?_topRm=opportunity&module=manPower_opportunityCandidate&_action=edit&opportunity_candidate_id=', oc.opportunity_candidate_id, '\' target=\'_blank\'>Detail</a>'))
              ,(CONCAT_WS('', '<a href=\'index.php?_topRm=opportunity&module=manPower_opportunity&_spAction=printCandidateResumeAsPdf&candidate_id=', oc.candidate_id, '\' target=\'_blank\'>Resume</a>'))
        FROM opportunity_candidate oc
        LEFT JOIN candidate c ON (c.candidate_id = oc.candidate_id)
        LEFT JOIN agent a ON (a.agent_id = oc.agent_id)
        WHERE oc.opportunity_id = '{$id}'
        ";

    }

    /**
     *
     */
    function getManPowerOpportunityManPowerTaskLinkSQL($id) {

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
              ,date_format(a.from_date, '%d %b %Y') AS from_date
              ,date_format(a.due_date, '%d %b %Y') AS due_date
        FROM opportunity b
            ,task a
        WHERE a.opportunity_id = b.opportunity_id
          AND b.opportunity_id = {$id}
        ORDER BY task_id
        ";

              /*,FORMAT(a.estimated_hours,2)
              ,(SELECT SUM(hours) AS total_hours
                FROM timesheet ts
                WHERE ts.task_id = a.task_id
              )*/

    }

    /**
     *
     */
    function getManPowerOpportunityManPowerStaffLinkSQL($id) {

        return "
        SELECT a.staff_id
              ,CONCAT_WS(' ', a.first_name, a.last_name) AS title
              ,team
              ,staff_type
        FROM `staff` a, `opportunity_staff` b
        WHERE a.staff_id = b.staff_id
          AND b.opportunity_id = '{$id}'
        ORDER BY title
        ";

    }

    /**
     *
     */
    function getPrintCandidateContract() {
        $cpCfg = Zend_Registry::get('cpCfg');
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $media = Zend_Registry::get('media');

        //-----------------------------------------------------------------//
        include_once(CP_LIBRARY_PATH.'lib_php/tbs_us/tbs_class.php');
        include_once(CP_LIBRARY_PATH.'lib_php/tbs_us/plugins/tbs_plugin_opentbs.php');
        include_once(CP_LIBRARY_PATH.'lib_php/tbs_us/plugins/tbs_plugin_html.php');

        $TBS = new clsTinyButStrong;
        $TBS->Plugin(TBS_INSTALL, OPENTBS_PLUGIN);

        $opportunity_id    = $fn->getReqParam('opportunity_id');
        $opportunityCandidateRec    = $fn->getRecordRowByID('opportunity_candidate', 'opportunity_id', $opportunity_id);
		$candidate_id = $opportunityCandidateRec['candidate_id'];

        $opportunityRec    = $fn->getRecordRowByID('opportunity', 'opportunity_id', $opportunity_id);
        $candidateRec    = $fn->getRecordRowByID('candidate', 'candidate_id', $candidate_id);

        $template = 'Candidate Agreement.docx';
        $templatePath = $cpCfg['cp.localPath'].'lib/template/' . $template;
        $TBS->LoadTemplate($templatePath);
        $rnd_no = mt_rand();
        $file_name = 'Candidate Agreement_' . '_' . $rnd_no . '.docx';
        $file_name = str_replace('.', '_' . date('Y-m-d') . '.', $file_name);

        $path = realpath($cpCfg['cp.mediaFolder']) . '\temp';
        $file_name_save = $path . '\\' . $file_name;
        $sourceFilePath = $file_name_save;

		$company_name = $opportunityRec['company_id'];
        $companyRec    = $fn->getRecordRowByID('company', 'company_id', $company_name);

		$contract_date = $opportunityRec['contract_date'];
        $contractDay   = $fn->getCPDate($contract_date, 'l');
        $contractDated = $fn->getCPDate($contract_date, 'd/m/Y');
        $contractdated = $fn->getCPDate($contract_date, 'dS F Y');
		$cadidateName  = strtoupper($candidateRec['first_name'] . ' ' .$candidateRec['last_name']);

        $valArr = array();
        /* Candidate Details */
        $valArr['contract_day']         		= $contractDay;
        $valArr['contract_date']        		= $contractDated;
        $valArr['candidate_name']       		= $cadidateName;
        $valArr['contract_dated']               = $contractdated;
        $valArr['candidate_address']            = $candidateRec['residential_address'];
        $valArr['position']            			= $opportunityRec['position'];
        $valArr['position_type']                = $opportunityRec['position_type'];
        $valArr['company_name']            		= $companyRec['company_name'];

        $blkMain   = array();
        $blkMain[] = $valArr;

        $TBS->MergeBlock('blkMain', $blkMain);

        $TBS->Show(OPENTBS_DOWNLOAD, $file_name);
    }
    /**
     *
     */
    function getManpowerOpportunityManpowerExpenseLinkSQL($id) {

        $SQL = "
        SELECT e.expense_id
              ,DATE_FORMAT(e.date, '%d %b %Y') AS date
              ,e.description
              ,e.amount
        FROM expense e
        LEFT JOIN opportunity o ON (o.opportunity_id = e.opportunity_id)
        WHERE e.opportunity_id = {$id}
        ";
        return $SQL;
    }

    /**
     *
     */
    function getConvertOppToProject() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $cpUtil = Zend_Registry::get('cpUtil');
        $dbUtil = Zend_Registry::get('dbUtil');
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');

        $opportunity_id = $fn->getReqParam('opportunity_id');
        $candidate_id   = $fn->getReqParam('candidate_id');

        $SQL = "
        SELECT *
        FROM opportunity
        WHERE opportunity_id = {$opportunity_id}
        ";
        $result  = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);

        if ($numRows > 0 && $opportunity_id != '') {
            $row = $db->sql_fetchrow($result);

            $fa = array();
            $fa['site_id']               = $row['site_id'];
            $fa['opportunity_id']        = $opportunity_id;
            $fa['title']                 = $row['title'];
            //$fa['contact_id']           = $candidate_id;
            $fa['creation_date']         = date("Y-m-d H:i:s");
            $fa['created_by']            = $fn->getSessionParam('userName');
            $fa['project_code']          = $this->getProjectCodeOnConvFromOpp($row['opportunity_id']);
            $fa['company_id']            = $row['company_id'];
            $fa['contact_id']            = $row['contact_id'];
            $fa['staff_id']              = $row['staff_id'];
            $fa['status']                = "WIP";
            $fa['description']           = $row['description'];
            $fa['difficulty']            = $row['difficulty'];
            $fa['currency']              = $row['currency'];
            $fa['project_value']         = $row['estimated_value'];
            $fa['candidate_hourly_rate'] = $row['candidate_hourly_rate'];
            $fa['client_hourly_rate']    = $row['client_hourly_rate'];
            $fa['position']              = $row['position'];
            $fa['position_type']         = $row['position_type'];
            $fa['start_date']            = date("Y-m-d");
            $fa['per_completed']         = 0;
            $fa['category']              = $row['category'];
            $fa['candidate_id']          = $candidate_id;
            $fa['work_state']            = $row['work_state'];

            /*
            $fa['title']                = $row['title'];
            $fa['project_manager_id']   = $row['project_manager_id'];
            $fa['category']             = $row['category'];
            $fa['notes']                = $row['notes'];
            $fa['client_type']          = $row['client_type'];
            $fa['confirmed_quote_id']   = $row['confirmed_quote_id'] ;
            */

            $id = $fn->addRecord($fa, 'project');

            /* Finding related staffs for the opportunity */
            $sqlStaff = "
            SELECT s.staff_id, s.commission_type, s.staff_commission_rate FROM staff s
            LEFT JOIN (opportunity_staff os) ON (s.staff_id = os.staff_id)
            WHERE os.opportunity_id = {$opportunity_id}
            ";
            $resultStaff = $db->sql_query($sqlStaff);
            while ($rowStaff = $db->sql_fetchrow($resultStaff)) {

                $faSc = array();
                $faSc['staff_id']       = $rowStaff['staff_id'];
                $faSc['project_id']     = $id;

                if ($rowStaff['commission_type'] == 'Fixed') {
                    $faSc['amount']     = $rowStaff['staff_commission_rate'];
                } else if ($rowStaff['commission_type'] == '%') {
                    $amount = (($row['estimated_value'] * $rowStaff['staff_commission_rate']) / 100);
                    $faSc['amount']     = $amount;
                }

                $faSc['status']         = 'Due';
                $faSc['date']           = date('Y-m-d');
                $faSc['site_id']        = $row['site_id'];
                $faSc['creation_date']  = date('Y-m-d H:i:s');

                $staff_commission_id    = $fn->addRecord($faSc, 'staff_commission');
            }

            /* Creation of History record in Project#Opportunity */
            /*$fa1 = array();
            $fa1['project_id']          = $id;
            $fa1['candidate_id']        = $candidate_id;
            $fa1['creation_date']       = date("Y-m-d");
            $project_candidate_id       = $fn->addRecord($fa1, 'project_candidate');*/

            $SQL = "UPDATE opportunity SET status = 'Win' WHERE opportunity_id = {$opportunity_id}";
            $result = $db->sql_query($SQL);

            $SQL3 = "UPDATE candidate SET edit_locked = 1 WHERE candidate_id = {$candidate_id}";
            $result3 = $db->sql_query($SQL3);

            /************ link staff to project **********/
            $SQL1 = "SELECT staff_id FROM opportunity_staff WHERE opportunity_id = {$opportunity_id}";
            $result1 = $db->sql_query($SQL1);

            while ($row1 = $db->sql_fetchrow($result1)) {
                $staff_id = $row1['staff_id'];

                $SQL2 = "INSERT INTO project_staff (project_id, staff_id, creation_date, modification_date) VALUES ($id, $staff_id, NOW(), NULL)";
                $result2 = $db->sql_query($SQL2);

            }

            /*$spArray = array(
                 'Payment Received'
                ,'Candidate Reached'
                ,'Medical'
                ,'Thumb Print Scheduled + Card Collection'
                ,'Documents Hand Over To Company'
            );

            $count = 0;
            foreach ($spArray as $row2){
                $title = $spArray[$count];
                $today = date("Y-m-d");
                $today_day = $fn->getCPDate($today, 'D');

                /* Linking of Opportunity and Task */
                /*$fa4 = array();
                $fa4['title']  = $title;
                $fa4['project_id'] = $id;
                $fa4['creation_date']  = date("Y-m-d H:i:s");
                $fa4['site_id']     = $row['site_id'];
                $fa4['status']      = "Due";
                //$fa4['staff_id']    = $callRegistryRec['staff_id'];
                if($title == 'Payment Received'){
                    $fa4['from_date']  = strftime("%Y-%m-%d", strtotime("$today +24 day"));
                    $fa4['due_date']   = strftime("%Y-%m-%d", strtotime("$today +28 day"));
                } else if($title == 'Candidate Reached'){
                    $fa4['from_date']  = strftime("%Y-%m-%d", strtotime("$today +29 day"));
                    $fa4['due_date']   = strftime("%Y-%m-%d", strtotime("$today +29 day"));
                } else if($title == 'Medical'){
                    $fa4['from_date']  = strftime("%Y-%m-%d", strtotime("$today +30 day"));
                    $fa4['due_date']   = strftime("%Y-%m-%d", strtotime("$today +31 day"));
                } else if($title == 'Thumb Print Scheduled + Card Collection'){
                    $fa4['from_date']  = strftime("%Y-%m-%d", strtotime("$today +32 day"));
                    $fa4['due_date']   = strftime("%Y-%m-%d", strtotime("$today +36 day"));
                } else if($title == 'Documents Hand Over To Company'){
                    $fa4['from_date']  = strftime("%Y-%m-%d", strtotime("$today +37 day"));
                    $fa4['due_date']   = strftime("%Y-%m-%d", strtotime("$today +38 day"));
                }

                $SQL            = $dbUtil->getInsertSQLStringFromArray($fa4, 'task');
                $result         = $db->sql_query($SQL);
                $task_id = $db->sql_nextid();

                $count++;

                /* Linking of Task and Staff */
                /*if ($callRegistryRec['staff_id']) {
                    $fa5 = array();
                    $fa5['task_id']  = $task_id;
                    $fa5['staff_id']        = $callRegistryRec['staff_id'];
                    $fa5['creation_date']   = date("Y-m-d H:i:s");

                    $SQL            = $dbUtil->getInsertSQLStringFromArray($fa5, 'task_staff');
                    $result         = $db->sql_query($SQL);
                }
            }*/

            //---------------------------------------//
            //$cpUtil->redirect("index.php?_topRm=project&module=manPower_project&project_id={$id}&_action=edit");
        }
    }

    /**
     *
     */
    function getProjectCodeOnConvFromOpp($opportunity_id) {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        /* Updation of Project Code */
        $nextProjectCode = $fn->getSettingsValueByKey("nextProjectCode");

        /*if($nextOppCode < 10){
            $projectCode = $fn->getSettingsValueByKey('projectCodePrefix') . '00' . $nextProjectCode;
        }
        else if($nextOppCode < 99){
            $projectCode = $fn->getSettingsValueByKey('projectCodePrefix') . '0' . $nextProjectCode;
        }
        else{*/
            $projectCode = $fn->getSettingsValueByKey('projectCodePrefix') . $nextProjectCode;
        //}

        $SQL         = "UPDATE setting SET value = (value+1) WHERE key_text = 'nextProjectCode'";
        $result      = $db->sql_query($SQL);

        return $projectCode;
    }

    /**
     *
     */
    function getPopulateCandidatePassport() {
        $fn = Zend_Registry::get('fn');

        $candidate_id = $fn->getReqParam('candidate_id');

        $passport_no = '';
        if ($candidate_id) {
            $candidateRec = $fn->getRecordByCondition('candidate', "candidate_id = {$candidate_id}");
            $passport_no = $candidateRec['travel_document_no'];
        }

        return $passport_no;
    }

    /**
     *
     */
    function getUpdateOpportunityCode() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        /* Updation of Opportunity Code */
        $site_id    = $fn->getSessionParam('cp_site_id');
		if ( $site_id  == 1) {
            $nextOppCode = $fn->getSettingsValueByKey("nextOpportunityCode");

	        if($nextOppCode < 10){
	            $oppCode = $fn->getSettingsValueByKey('opportunityCodePrefix') . '000' . $nextOppCode;
	        }
	        else if($nextOppCode < 99){
	            $oppCode = $fn->getSettingsValueByKey('opportunityCodePrefix') . '00' . $nextOppCode;
	        }
	        else if($nextOppCode > 99 || $nextOppCode < 999){
	            $oppCode = $fn->getSettingsValueByKey('opportunityCodePrefix') . '0' . $nextOppCode;
	        }
	        else{
	            $oppCode = $fn->getSettingsValueByKey('opportunityCodePrefix') . $nextOppCode;
	        }

            $SQL    = "UPDATE setting SET value = (value+1) WHERE key_text = 'nextOpportunityCode'";
            $result = $db->sql_query($SQL);

            return $oppCode;
        }
        else if ( $site_id  == 2) {
            $nextOppCode = $fn->getSettingsValueByKey("nextOpportunityCode2");

	        if($nextOppCode < 10){
	            $oppCode = $fn->getSettingsValueByKey('opportunityCodePrefix') . '000' . $nextOppCode;
	        }
	        else if($nextOppCode < 99){
	            $oppCode = $fn->getSettingsValueByKey('opportunityCodePrefix') . '00' . $nextOppCode;
	        }
	        else if($nextOppCode > 99 || $nextOppCode < 999){
	            $oppCode = $fn->getSettingsValueByKey('opportunityCodePrefix') . '0' . $nextOppCode;
	        }
	        else{
	            $oppCode = $fn->getSettingsValueByKey('opportunityCodePrefix') . $nextOppCode;
	        }

            $SQL    = "UPDATE setting SET value = (value+1) WHERE key_text = 'nextOpportunityCode2'";
            $result = $db->sql_query($SQL);

            return $oppCode;
		}
        else{
            $nextOppCode = $fn->getSettingsValueByKey("nextOpportunityCode");

            /*if($nextOppCode < 10){
                $oppCode = $fn->getSettingsValueByKey('opportunityCodePrefix') . '000' . $nextOppCode;
            }
            else if($nextOppCode < 99){
                $oppCode = $fn->getSettingsValueByKey('opportunityCodePrefix') . '00' . $nextOppCode;
            }
            else if($nextOppCode > 99 || $nextOppCode < 999){
                $oppCode = $fn->getSettingsValueByKey('opportunityCodePrefix') . '0' . $nextOppCode;
            }
            else{*/
                $oppCode = $fn->getSettingsValueByKey('opportunityCodePrefix') . $nextOppCode;
            //}

            $SQL    = "UPDATE setting SET value = (value+1) WHERE key_text = 'nextOpportunityCode'";
            $result = $db->sql_query($SQL);

            return $oppCode;

        }
    }

    /**
     *
     */
    function getPopulateRelatedAgent() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $candidate_id = $fn->getReqParam('candidate_id');

        $agent_name = '';
        if ($candidate_id) {
            $sql = "
            SELECT a.agent_code
            FROM agent a
            LEFT JOIN (candidate c) ON (a.agent_id = c.agent_id)
            WHERE c.candidate_id = {$candidate_id}
            ";
            $result = $db->sql_query($sql);
            $row = $db->sql_fetchrow($result);

            $agent_name = $row['agent_code'];
        }

        return $agent_name;
    }

    /**
     *
     */
    function getSendMailToAgentFormSubmit() {
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUtil = Zend_Registry::get('cpUtil');
        $cpCfg = Zend_Registry::get('cpCfg');

        $opportunity_id  = $fn->getPostParam('opportunity_id');
        $remarks         = $fn->getPostParam('remarks');
        $site_id    = $fn->getSessionParam('cp_site_id');

        if (!$this->getSendMailToAgentFormValidate()){
            return $validate->getErrorMessageXML();
        }

        $fa = array();
        $fa['comments']     = $remarks;
        $fa['room_name']    = 'manPower_opportunity';
        $fa['contact_id']   = $_SESSION['staff_id'];
        $fa['record_id']    = $opportunity_id;
        $fa['comment_date'] = date("Y-m-d H:i:s");
        $fa['creation_date']= date("Y-m-d H:i:s");
        if($site_id){
            $fa['site_id']      = $site_id;
        }

        $insertSQL   = $dbUtil->getInsertSQLStringFromArray($fa, 'comment');
        $resultSQL   = $db->sql_query($insertSQL);
        $comment_id  = $db->sql_nextid();

        //$cpUtil->redirect("index.php?_topRm=finance&module=pms_order&order_id={$order_id}&_action=edit");
        //$this->getSendMessageInBackground();
        $this->getSendMailToAgent($opportunity_id, $comment_id);
        return $validate->getSuccessMessageXML();
    }
    /**
     *
     */
    function getSendMailToAgent($opportunity_id, $comment_id){
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $ln = Zend_Registry::get('ln');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUtil = Zend_Registry::get('cpUtil');
        $cpUrl = Zend_Registry::get('cpUrl');
        $cpCfg = Zend_Registry::get('cpCfg');

        $staff_id = $_SESSION['staff_id'];

        $oppRec = $fn->getRecordRowByID('opportunity', 'opportunity_id', $opportunity_id);
        $opp_code = $oppRec['opportunity_code'];


        $SQL = "
        SELECT * FROM agent
        WHERE published= 1
        ORDER BY agent_id
        ";
        $result = $db->sql_query($SQL);
        $site_id    = $fn->getSessionParam('cp_site_id');

        //create recipients
        while ($row = $db->sql_fetchrow($result)) {
            $fa = array();
            $fa['opportunity_id'] = $opportunity_id;
            $fa['agent_id']       = $row['agent_id'];
            $fa['send_tag']       = 0;
            if($site_id){
                $fa['site_id']      = $site_id;
            }
            $fa['creation_date']  = $fn->getCurrentTimestamp();
            $SQLInsert = $dbUtil->getInsertSQLStringFromArray($fa, 'opportunity_agent');
            $db->sql_query($SQLInsert);
        }
        //send message in background
        $phpScript = realpath('.') . '/util/send_message.php';
        $params  = 'host=' . $_SERVER['HTTP_HOST']
                 . '&opportunity_id=' . $opportunity_id
                 . '&comment_id=' . $comment_id;
        $cpUtil->callScriptInBackground($phpScript, $params, $cpCfg['cp.phpPath']);
    }

    /**
     *
     */
    function getSendMailToAgentFormValidate() {
        $ln = Zend_Registry::get('ln');
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }
    /**
     *
     * @param type $party_setup_id
     * @param type $message_id
     * @param type $messageType = message|thankyouCard|rescheduleParty|cancelParty
     * @return type
     */
    function getSendMessageInBackground(){
        set_time_limit(500000);

        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $ln = Zend_Registry::get('ln');
        $cpUtil = Zend_Registry::get('cpUtil');
        $cpCfg = Zend_Registry::get('cpCfg');
        $mediaArrayObj = Zend_Registry::get('mediaArrayObj');

        /*
        to check using log if the process is getting in to this function.
        $fp = fopen('D:/Projects/manpower/httpdocs/admin/util/log.txt', 'w');
        fwrite($fp, 'Getting in to the Function');
        fclose($fp);
        */

        //$mediaArrayObj->setMediaArray('party_partySetup');
        $opportunity_id  = $fn->getReqParam('opportunity_id');
        $comment_id      = $fn->getReqParam('comment_id');


        $mod_date   = date('Y-m-d H:i:s');

        $oppRec     = $fn->getRecordRowByID('opportunity', 'opportunity_id', $opportunity_id);
        $sqlCountry = "
        SELECT name FROM geo_country
        WHERE country_code = '{$oppRec['candidate_country']}'
        ";
        $resultCountry = $db->sql_query($sqlCountry);
        $countryRec    = $db->sql_fetchrow($resultCountry);

        $opp_code            = $oppRec['opportunity_code'];
        $position            = $oppRec['position'];

        $salary              = $oppRec['salary'];
        $working_hours       = $oppRec['working_hours'];
        $leave_year          = $oppRec['leave_year'];
        $required_experience = $oppRec['required_experience'];
        $pass_type           = $oppRec['pass_type'];
        $country_name        = $countryRec['name'];

        $cmtRec     = $fn->getRecordRowByID('comment', 'comment_id', $comment_id);
        $comment    = $cmtRec['comments'];
        $emailDraft = $fn->getRecordByCondition('setting', "key_text = 'cp.emailtoagentdraft'");

        //-------------------------------//
        //$subject = $rowMsg['title'];
        $subject = $opp_code . ' New Opportunity from Westrama' . ' : ' .  $position;
        //$message = 'Message for the Email';
        $message = $emailDraft['value'];
        //$message = $comment;
        $message = str_replace("[opp_code]", $opp_code, $message );
        $message = str_replace("[position]", $position, $message );
        $message = str_replace("[salary]", $salary, $message );
        $message = str_replace("[working_hours]", $working_hours, $message );
        $message = str_replace("[leave_year]", $leave_year, $message );
        $message = str_replace("[required_experience]", $required_experience, $message );
        $message = str_replace("[pass_type]", $pass_type, $message );
        $message = str_replace("[country_name]", $country_name, $message );

        $message = str_replace("[remarks]", $comment, $message );
        $message = nl2br($message);

        $fromName  = 'Westrama Management Company';
        $fromEmail = 'autonotification@westrama.com';
        $replyTo   = 'autonotification@westrama.com';

        //send broadcast
        $text = '';
        //---------------------------------------------------------------------//
        //$smtp->SMTPKeepAlive = true;
        // internally calls the following in linux (test site)
        // /usr/bin/php /www-disk/inetpub/Apache/USS/manpower/httpdocs/admin/util/send_message.php "host=manpower.testpilotweb.com&opportunity_id=12&comment_id=54"

        $SQL = "
        SELECT oppag.*
            ,a.first_name as name
            ,a.email
        FROM opportunity_agent oppag
        LEFT JOIN (agent a) ON (a.agent_id   = oppag.agent_id)
        WHERE oppag.opportunity_id = '{$opportunity_id}'
          AND (oppag.status != 'sent' OR oppag.status IS NULL)
        ";
        $result  = $db->sql_query($SQL);
        while ($row = $db->sql_fetchrow($result)) {
        //$row = $db->sql_fetchrow($result);
            $toName  = $row['name'];
            $toEmail = $row['email'];
            //$toEmail = 'arif@usoftsolutions.com';

            $message = str_replace("[user]", $row['name'], $message );

            //for re-schedule party

            //$message = str_replace('[uniqueUrl]', $uniqueUrl, $message);

            $footerType = 'default';

            $error = '';
            $args = array(
                 'toName'    => $toName
                ,'toEmail'   => $toEmail
                ,'subject'   => $subject
                ,'message'   => $message
                ,'fromName'  => $fromName
                ,'fromEmail' => $fromEmail
                ,'replyTo'   => $replyTo
            );

            $emailMsg = includeCPClass('Lib', 'EmailTemplate', 'EmailTemplate',
                                        true, array('args' => $args));

            $exp = array('showHeader' => false, 'css' => '');
            $exp = '';
            $error = $emailMsg->sendEmail($exp);

            if ($error) {
                $SQL1 = "
                UPDATE opportunity_agent
                SET send_tag = 0
                   ,status = 'failed'
                WHERE agent_id = {$row['agent_id']}
                  AND opportunity_id = {$opportunity_id}
                ";
                $text .= $toEmail . ': Error sending the mail.<br>';
            } else {
                $SQL1 = "
                UPDATE opportunity_agent
                SET send_tag = 1
                   ,status = 'sent'
                WHERE agent_id = {$row['agent_id']}
                  AND opportunity_id = {$opportunity_id}
                ";
                $text .= $toEmail . ': Successfully Sent.<br>';
            }
            $db->sql_query($SQL1);
        }

        //-----------------------------------------------------------------------------//
        /*
        $SQL    = "UPDATE message SET message_date = NOW() WHERE message_id = '{$message_id}'";
        $result = $db->sql_query($SQL);

        $retArr = array(
            'html' => $ln->gd('m.party.message.sendMessageResponse')
        );
        */
        return $cpUtil->getJsonFromArray('success');
    }

    /**
     *
     */
    function getDeleteEmptyOpportunityRecords(){
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');

        /* SELECT EMPTY RECORDS OF OPPORTUNITY */
        $site_id    = $fn->getSessionParam('cp_site_id');
         $sqlAppend = '';

        if($site_id){
            $sqlAppend = " AND site_id  = {$site_id}";
        }

        $sqlOppEmpty = "
        SELECT opportunity_id FROM opportunity
        WHERE salary IS NULL
          AND staff_id = {$_SESSION['staff_id']}
        {$sqlAppend}
                ";
        $resultOppEmpty = $db->sql_query($sqlOppEmpty);
        while ($rowOppEmpty = $db->sql_fetchrow($resultOppEmpty)) {
            /* DELETE OPPORTUNITY STAFF RECORD */
            $SqlDelOppStaff = "
            DELETE FROM opportunity_staff
            WHERE opportunity_id = {$rowOppEmpty['opportunity_id']}
            ";
            $resultDelOppStaff = $db->sql_query($SqlDelOppStaff);

            /* SELECT MULTIPLE TASKS FOR OPPORTUNITY RECORD */
            $site_id    = $fn->getSessionParam('cp_site_id');
             $sqlAppend = '';

            if($site_id){
                $sqlAppend = " AND site_id  = {$site_id}";
            }
            $sqlTasksForOpp = "
            SELECT task_id FROM task
            WHERE opportunity_id = {$rowOppEmpty['opportunity_id']}
              {$sqlAppend}
            ";
            $resultTasksForOpp = $db->sql_query($sqlTasksForOpp);
            while ($rowTasksForOpp = $db->sql_fetchrow($resultTasksForOpp)) {

                /* DELETE TASK STAFF RECORD */
                $SqlDelTaskStaff = "
                DELETE FROM task_staff
                WHERE task_id = {$rowTasksForOpp['task_id']}
                ";
                $resultDelTaskStaff = $db->sql_query($SqlDelTaskStaff);

                /* DELETE TASK RECORD */
                $site_id    = $fn->getSessionParam('cp_site_id');
                 $sqlAppend = '';

                if($site_id){
                    $sqlAppend = " AND site_id  = {$site_id}";
                }
                $SqlDelTask = "
                DELETE FROM task
                WHERE opportunity_id = {$rowOppEmpty['opportunity_id']}
                  {$sqlAppend}
                ";
                $resultDelTask = $db->sql_query($SqlDelTask);
            }

            /* DELETE OPPORTUNITY RECORD */
            $SqlDelOpp = "
            DELETE FROM opportunity
            WHERE opportunity_id = {$rowOppEmpty['opportunity_id']}
              {$sqlAppend}
            ";
            $resultDelOpp = $db->sql_query($SqlDelOpp);
        }
    }

    /**
     *
     */
    function getValueByValuelistJSON(){
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');

        $rows = "";

        $valuelist_name = $fn->getReqParam('valuelist_name');

        $json  = array();

        if ($valuelist_name == ""){
            return json_encode($json);
        }

        $SQL = "
        SELECT v.value
              ,v.value
        FROM valuelist v
        WHERE v.key_text = '{$valuelist_name}'
        ORDER BY v.value
        ";
        $result = $db->sql_query($SQL);
        $json[] = array("value" => "", "caption" => "Please Select");
        while ($row = $db->sql_fetchrow($result)) {
            $json[] = array("value" => $row['value'], "caption" => $row['value']);
        }

        return json_encode($json);
    }
}
