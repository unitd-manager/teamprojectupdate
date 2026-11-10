<?
class CP_Admin_Modules_ManPower_CallRegistry_Model extends CP_Common_Lib_ModuleModelAbstract
{
    function getSQL() {
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');

        $joinTable = '';
        $leftJoin = '';

        $callRegistryDate1 = $fn->getReqParam('callRegistryDate1');
        $callRegistryDate2 = $fn->getReqParam('callRegistryDate2');

        if (($callRegistryDate1 != "" && $callRegistryDate1 != "From"  && $callRegistryDate1 != "To" )
        || ($callRegistryDate2 != "" && $callRegistryDate2 != "From"  && $callRegistryDate2 != "To" )
        ) {
            $leftJoin = "LEFT JOIN (comment cmt)ON (cmt.record_id = c.call_registry_id)";
        }

        if ($cpCfg['m.manPower.callRegistry.hasCandidate']) {
            //This is used for the candidate right panel link
            //$joinTable = "LEFT JOIN (candidate cont) ON (c.candidate_id = cont.candidate_id)";
        } else {
            //$joinTable = "LEFT JOIN (contact cont) ON (c.contact_id = cont.contact_id)";
        }

        $SQL = "
        SELECT DISTINCT c.call_registry_id
              ,c.name
              ,c.phone
              ,c.fax
              ,c.mobile
              ,c.email
              ,c.company_address
              ,c.industry
              ,c.status
              ,c.contact_date
              ,c.contact_time
              ,c.follow_up_date
              ,c.description
              ,c.staff_id
              ,c.company_id
              ,c.published
              ,c.flag
              ,c.creation_date
              ,c.modification_date
              ,c.created_by
              ,c.modified_by
              ,c.candidate_id
              ,c.site_id
              ,c.enquiry_type
              ,c.reminder
              ,c.reffer
              ,c.requirements
              ,c.no_of_candidates
              ,c.call_registry_code
              ,c.company_name
              ,c.contact_name
              ,c.address
              ,c.title
              ,c.category
              ,c.other_industry
              ,c.job_title
              ,c.alternate_phone
              ,CONCAT_WS(' ', s.first_name, s.last_name) AS staff_name
        FROM `call_registry` c
        {$joinTable}
        LEFT JOIN (company co)ON (c.company_id = co.company_id)
        LEFT JOIN (staff s)ON (c.staff_id = s.staff_id)
        {$leftJoin}
        ";

        return $SQL;
    }

    /**
     *
     */
    function getSQLForPager() {
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');

        $joinTable = '';
        $leftJoin = '';

        $callRegistryDate1 = $fn->getReqParam('callRegistryDate1');
        $callRegistryDate2 = $fn->getReqParam('callRegistryDate2');

        if (($callRegistryDate1 != "" && $callRegistryDate1 != "From"  && $callRegistryDate1 != "To" )
        || ($callRegistryDate2 != "" && $callRegistryDate2 != "From"  && $callRegistryDate2 != "To" )
        ) {
            $leftJoin = "LEFT JOIN (comment cmt)ON (cmt.record_id = c.call_registry_id)";
        }

        if ($cpCfg['m.manPower.callRegistry.hasCandidate']) {
            //This is used for the candidate right panel link
            //$joinTable = "LEFT JOIN (candidate cont) ON (c.candidate_id = cont.candidate_id)";
        } else {
            //$joinTable = "LEFT JOIN (contact cont) ON (c.contact_id = cont.contact_id)";
        }

        $SQL = "
        SELECT count(*)
        FROM call_registry c
        {$joinTable}
        LEFT JOIN (company co)ON (c.company_id = co.company_id)
        LEFT JOIN (staff s)ON (c.staff_id = s.staff_id)
        {$leftJoin}
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
        $searchVar->mainTableAlias = 'c';

        $today  = date('Y-m-d');
        $call_registry_id  = $fn->getReqParam('call_registry_id');
        $status      	   = $fn->getReqParam('status');
        $category          = $fn->getReqParam('category');
        $company_id        = $fn->getReqParam('company_id');
        $company_name      = $fn->getReqParam('company_name');
        $month             = $fn->getReqParam('month');
        $year              = $fn->getReqParam('year');
        $user_group_id     = $fn->getReqParam('user_group_id');
        $staff_id          = $fn->getReqParam('staff_id');
        $today_reminder    = $fn->getReqParam('today_reminder');
        $call_date         = $fn->getReqParam('contact_date');
        //$reminder_date     = $fn->getReqParam('follow_up_date');
        $callRegistryDate1 = $fn->getReqParam('callRegistryDate1');
        $callRegistryDate2 = $fn->getReqParam('callRegistryDate2');
        $followUpDate1 	   = $fn->getReqParam('followUpDate1');
        $followUpDate2     = $fn->getReqParam('followUpDate2');

        //$user_group_id = isset($_SESSION['userGroupID']) ? $_SESSION['userGroupID'] : 0;

        if ($call_registry_id != "") {
            $searchVar->sqlSearchVar[] = "c.call_registry_id = '{$call_registry_id}'";
        } else if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "c.call_registry_id = '{$tv['record_id']}'";
        } else {

            $fn->setSearchVarForLinkData($searchVar, $linkRecType, 'c.call_registry_id');

            if ($status != "") {
                $searchVar->sqlSearchVar[] = "c.status = '{$status}'";
            }

            if ($category != "") {
                $searchVar->sqlSearchVar[] = "c.category = '{$category}'";
            }

            if ($company_id != "") {
                $searchVar->sqlSearchVar[] = "co.company_id = '{$company_id}'";
            }

            if ($company_name != "") {
                $searchVar->sqlSearchVar[] = "c.company_name = '{$company_name}'";
            }

            //from date and to-date populated for call registry
            $start_time = "00-00-00";
            $end_time   = "23-59-59";
            if ($callRegistryDate1 != "" && $callRegistryDate1 != "From"
            && $callRegistryDate2 != "" && $callRegistryDate2 != "To" ) {
                $appendSql = '';
                if ($staff_id != '') {
                    $appendSql = "AND cmt.contact_id = {$staff_id}";
                }

                $start_date_time = $callRegistryDate1 . ' ' . $start_time;
                $end_date_time   = $callRegistryDate2 . ' ' . $end_time;

                $searchVar->sqlSearchVar[] = "((c.contact_date BETWEEN '{$callRegistryDate1}' AND '{$callRegistryDate2}')
                OR
                (cmt.comment_date >= '{$start_date_time}' AND comment_date <= '{$end_date_time}')
                {$appendSql})
                ";
            }

            //from date populated
            if ($callRegistryDate1 != "" && $callRegistryDate1 != "From"
                && ($callRegistryDate2 == "To" || $callRegistryDate2 == "")) {
                $callRegistryDate2 = date('Y-m-d');
                $appendSql = '';
                if ($staff_id != '') {
                    $appendSql = "AND cmt.contact_id = {$staff_id}";
                }

                $start_date_time = $callRegistryDate1 . ' ' . $start_time;
                $end_date_time   = $callRegistryDate2 . ' ' . $end_time;

                $searchVar->sqlSearchVar[] = "((c.contact_date BETWEEN '{$callRegistryDate1}' AND '{$callRegistryDate2}')
                OR
                (cmt.comment_date >= '{$start_date_time}' AND comment_date <= '{$end_date_time}')
                {$appendSql})
                ";
            }


            //to date populated
            if (($callRegistryDate1 == "From" || $callRegistryDate1 == "")
                && $callRegistryDate2 != "" && $callRegistryDate2 != "To") {
                $current_year = date('Y');
                $callRegistryDate1 = $current_year . '-01-01';
                $appendSql = '';
                if ($staff_id != '') {
                    $appendSql = "AND cmt.contact_id = {$staff_id}";
                }

                $start_date_time = $callRegistryDate1 . ' ' . $start_time;
                $end_date_time   = $callRegistryDate2 . ' ' . $end_time;

                $searchVar->sqlSearchVar[] = "((c.contact_date BETWEEN '{$callRegistryDate1}' AND '{$callRegistryDate2}')
                OR
                (cmt.comment_date >= '{$start_date_time}' AND comment_date <= '{$end_date_time}')
                {$appendSql})
                ";
            }

            /*
            if ($callRegistryDate1 != "" && $callRegistryDate1 != "From" && $callRegistryDate2 == "To") {
                $searchVar->sqlSearchVar[] = "(c.contact_date >= '{$callRegistryDate1}')
                AND
                (DATE_FORMAT(cmt.comment_date, '%Y-%m-%d') >= '{$callRegistryDate1}')
                ";
            }
            */


            /*
            if ($callRegistryDate2 != "" && ($callRegistryDate1 == "From"
            || $callRegistryDate1 == "") && $callRegistryDate2 != "To") {
                $searchVar->sqlSearchVar[] = "(c.contact_date <= '{$callRegistryDate2}')
                AND
                (DATE_FORMAT(cmt.comment_date, '%Y-%m-%d') <= '{$callRegistryDate2}')
                ";
            }
            */

            //from date and to-date populated
            if ($followUpDate1 != "" && $followUpDate1 != "From"
            && $followUpDate2 != "" && $followUpDate2 != "To" ) {
                $searchVar->sqlSearchVar[] = "(c.follow_up_date BETWEEN '{$followUpDate1}' AND '{$followUpDate2}')";
                $searchVar->sqlSearchVar[] = "c.status = 'Follow up'";
            }

            //from date populated
            if ($followUpDate1 != "" && $followUpDate1 != "From"
            && ($followUpDate2 == "" || $followUpDate2 == "To")) {
                $followUpDate2 = date('Y-m-d');
                $searchVar->sqlSearchVar[] = "(c.follow_up_date >= '{$followUpDate1}')";
                $searchVar->sqlSearchVar[] = "c.status = 'Follow up'";
            }

            //to date populated ( records populated from the start of the year till the mentioned date)
            if (($followUpDate1 == "" || $followUpDate1 == "From")
            && $followUpDate2 != "" && $followUpDate2 != "To") {
                $current_year = date('Y');
                $followUpDate1 = $current_year . '-01-01';
                $searchVar->sqlSearchVar[] = "(c.follow_up_date BETWEEN '{$followUpDate1}' AND '{$followUpDate2}')";
                $searchVar->sqlSearchVar[] = "c.status = 'Follow up'";
            }

            /*
            if (($followUpDate1 == "" || $followUpDate1 == "From")
            && $followUpDate2 != "" && $followUpDate2 != "To" ) {
                $followUpDate2 = date('Y-m-d');
                $searchVar->sqlSearchVar[] = "(c.follow_up_date BETWEEN '{$followUpDate1}' AND '{$followUpDate2}')";
                $searchVar->sqlSearchVar[] = "c.status = 'Follow up'";
            }
            */

            /*
            if ($followUpDate1 != "" && $followUpDate1 != "From" && $followUpDate2 == "To") {
                $searchVar->sqlSearchVar[] = "(c.follow_up_date >= '{$followUpDate1}')";
                $searchVar->sqlSearchVar[] = "c.status = 'Follow up'";
            }
            */


            /*
            if ($followUpDate2 != "" && ($followUpDate1 == "From"
            || $followUpDate1 == "") && $followUpDate2 != "To") {
                $searchVar->sqlSearchVar[] = "(c.follow_up_date <= '{$followUpDate2}')";
                $searchVar->sqlSearchVar[] = "c.status = 'Follow up'";
            }
            */

            if ($staff_id != "") {
                $searchVar->sqlSearchVar[] = "s.staff_id = {$staff_id}";
            }

            /*
            if ($reminder_date != "") {
                $searchVar->sqlSearchVar[] = "c.follow_up_date = '{$reminder_date}' AND c.status = 'Follow up'";
            }
            */

            if ($call_date != "") {
                $searchVar->sqlSearchVar[] = "c.contact_date = '{$call_date}'";
            }

            if ($month != ''){
                if ($year != '') {
                    $startMonth = $year . '-' . $month . '-' . '01';
                    $endMonth   = $year . '-' . $month . '-' . '31';
                } else {
                    $year = date('Y');
                    $startMonth = $year . '-' . $month . '-' . '01';
                    $endMonth   = $year . '-' . $month . '-' . '31';
                }
                //$searchVar->sqlSearchVar[] = "c.contact_date BETWEEN '{$startMonth}' AND '{$endMonth}'";
            }


            if ($tv['keyword'] != '') {
                $searchVar->sqlSearchVar[] = "(
                 c.email  LIKE '%{$tv['keyword']}%'
                 OR c.company_name  LIKE '%{$tv['keyword']}%'
                 OR c.name  LIKE '%{$tv['keyword']}%'
                )";
            }
                    //co.company_name LIKE '%{$tv['keyword']}%'

            if ($_SESSION['userGroupType'] != "Super Administrator") {
                $searchVar->sqlSearchVar[] = "c.staff_id  = '{$_SESSION['staff_id']}'";
            }

            if ($today_reminder == "Reminders for Today") {
                $searchVar->sqlSearchVar[] = "c.follow_up_date = '{$today}'";
                $searchVar->sqlSearchVar[] = "c.status = 'Follow up'";
            }

        }

        $searchVar->sortOrder = "c.creation_date DESC";
    }

    /**
     *
     */
    function getNewValidate() {
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();
        $validate->validateData('company_name', 'Please select the company');

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
        $fa['staff_id']             = $_SESSION['staff_id'];
        $fa['contact_date']         = date('Y-m-d');
        $fa['contact_time']         = date('H:i:s');
        $fa['follow_up_date']       = date('Y-m-d', strtotime("+7 days"));
        $fa['call_registry_code']   = $this->getUpdateCallRegistryCode();

        $id = $fn->addRecord($fa);
        $fn->returnAfterNewSave($id);
    }

    /**
     *
     */
    function getEditValidate() {
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $record_id  = $fn->getReqParam('call_registry_id');
        $site_id    = $fn->getSessionParam('cp_site_id');
        $staff_id   = $fn->getSessionParam('staff_id');

        $validate->resetErrorArray();
        $validate->validateData('status', 'Please select the status');
        $validate->validateData('title', 'Please enter the title');
        $validate->validateData('category', 'Please enter Category');
        $validate->validateData('company_name', 'Please enter Company Name');

        $status = $fn->getPostParam('status', '', true);
		if($status == 'High Win Ratio'){
            $validate->validateData('contact_name', 'Please enter Contact Name');
		}

        $email = $fn->getPostParam('email', '', true);
        if($email != ''){
            if(!$validate->isEmail($email)){
                $validate->errorArray['email']['name'] = "email";
                $validate->errorArray['email']['msg']  = "Please enter valid email";
            }
        }

        $phone = $fn->getPostParam('phone', '', true);
        if (!ctype_digit($phone)) {
            $validate->errorArray['phone']['name'] = "phone";
            $validate->errorArray['phone']['msg']  = "Please enter valid phone no only eg: 654";
        }

        $appendSql = "";
        if ($site_id) {
            $appendSql = " AND site_id = {$site_id}";
        }

        $today      = date('Y-m-d');
        $start_time = "00-00-00";
        $end_time   = "23-59-59";

        $from = $today . ' ' . $start_time;
        $to   = $today . ' ' . $end_time;


        /*
        $sqlComment = "
        SELECT comment_id FROM comment
        WHERE room_name = 'manPower_callRegistry'
          AND record_id = {$record_id}
          AND comment_date >= '{$from}'
          AND comment_date <= '{$to}'
          AND contact_id = {$staff_id}
          {$appendSql}
        ";
        $resultComment  = $db->sql_query($sqlComment);
        $numRowsComment = $db->sql_numrows($resultComment);

        if ($numRowsComment == 0 && ($status != 'Not in Use' && $status != 'Not Interested') ) {
            $msg = "Please enter your activity by clicking on 'Add Activity' in the right side";
            $validate->validateData('error_box', $msg);
        }
        */

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

        $fa = $this->getFields();
        $id = $fn->saveRecord($fa, $tableName, $keyField, $keyFieldVal);

        $fn->returnAfterNewSave($id);
    }

    /**
     *
     */
    function getFields() {
        $fn = Zend_Registry::get('fn');

        $fa = array();

        $fa = $fn->addToFieldsArray($fa, 'name');
        $fa = $fn->addToFieldsArray($fa, 'fax');
        $fa = $fn->addToFieldsArray($fa, 'company_name');
        $fa = $fn->addToFieldsArray($fa, 'contact_name');
        $fa = $fn->addToFieldsArray($fa, 'phone');
        $fa = $fn->addToFieldsArray($fa, 'address');
        $fa = $fn->addToFieldsArray($fa, 'email');
        $fa = $fn->addToFieldsArray($fa, 'mobile');
        $fa = $fn->addToFieldsArray($fa, 'company_name');
        $fa = $fn->addToFieldsArray($fa, 'company_address');
        $fa = $fn->addToFieldsArray($fa, 'industry');
        $fa = $fn->addToFieldsArray($fa, 'description');
        $fa = $fn->addToFieldsArray($fa, 'status');
        $fa = $fn->addToFieldsArray($fa, 'contact_date');
        $fa = $fn->addToFieldsArray($fa, 'contact_time');
        $fa = $fn->addToFieldsArray($fa, 'follow_up_date');
        $fa = $fn->addToFieldsArray($fa, 'staff_id');
        $fa = $fn->addToFieldsArray($fa, 'candidate_id');
        $fa = $fn->addToFieldsArray($fa, 'contact_id');
        $fa = $fn->addToFieldsArray($fa, 'company_id');
        $fa = $fn->addToFieldsArray($fa, 'category');
        $fa = $fn->addToFieldsArray($fa, 'description');
        $fa = $fn->addToFieldsArray($fa, 'call_registry_id');
        $fa = $fn->addToFieldsArray($fa, 'reminder');
        $fa = $fn->addToFieldsArray($fa, 'reffer');
        $fa = $fn->addToFieldsArray($fa, 'requirements');
        $fa = $fn->addToFieldsArray($fa, 'no_of_candidates');
        $fa = $fn->addToFieldsArray($fa, 'title');
        $fa = $fn->addToFieldsArray($fa, 'other_industry');
        $fa = $fn->addToFieldsArray($fa, 'job_title');
        $fa = $fn->addToFieldsArray($fa, 'alternate_phone');

        return $fa;
    }

    /**
     *
     */
    function getManPowerCallRegistryManPowerOpportunityLinkSQL($id) {

        return $SQL = "
        SELECT o.opportunity_id
              ,o.opportunity_code
              ,o.title
              ,o.status
        FROM opportunity o
        WHERE o.call_registry_id = '{$id}'
        ORDER BY o.opportunity_id
        ";

    }

    /**
     *
     */
    function getManPowerCallRegistryManPowerContactLinkSQL($id) {

        $SQL = "
        SELECT cr.call_registry_contact_id
              ,CONCAT_WS(' ', c.first_name, c.last_name) AS contact_name
        FROM call_registry_contact cr
        LEFT JOIN contact c ON (c.contact_id = cr.contact_id)
        WHERE cr.call_registry_id = '{$id}'
        ORDER BY cr.call_registry_contact_id
        ";

        return $SQL;
    }

    /**
     *
     */
    function getManPowerCallRegistryManPowerCompanyLinkSQL($id) {

        $SQL = "
        SELECT cr.call_registry_company_id
              ,c.company_name
        FROM call_registry_company cr
        LEFT JOIN company c ON (c.company_id = cr.company_id)
        WHERE cr.call_registry_id = '{$id}'
        ORDER BY cr.call_registry_company_id
        ";

        return $SQL;
    }

    /**
     *
     */
    function getExportData($dataArray){
        $phpExcel = includeCPClass('Lib', 'PhpExcelExportWrapper', 'PhpExcelExportWrapper');

        $fa = array(
              'contact_name'        => $phpExcel->getFldObj('Contact Name')
             ,'phone'               => $phpExcel->getFldObj('Contact No')
             ,'contact_date'        => $phpExcel->getFldObj('Call Date')
             ,'contact_time'        => $phpExcel->getFldObj('Call Time')
             ,'email'               => $phpExcel->getFldObj('Email')
             ,'company_name'        => $phpExcel->getFldObj('Company Name')
             ,'status'              => $phpExcel->getFldObj('Status')
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
    function getCompanyDetailsJSON() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $cpUtil = Zend_Registry::get('cpUtil');

        $company_id = $fn->getReqParam('company_id');

        $SQL    = "
        SELECT *
        FROM company
        WHERE company_id = '{$company_id}'
        ";
        $result = $db->sql_query($SQL);
        $row = $db->sql_fetchrow($result);

        $company_address = $row['address_flat'] . ' ' . $row['address_street'] . ' ' . $row['address_town'] . ' ' . $row['address_state'] . ' ' . $row['address_country'];

        $arr['fax'] = $row['fax'];
        $arr['mobile'] = $row['mobile'];
        $arr['email'] = $row['email'];
        $arr['industry'] = $row['industry'];
        $arr['companyAddress'] = $company_address;

        return $cpUtil->getJsonFromArray($arr);
    }

    /**
     *
     */
    function getConvertToOpportunity() {
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');
        $db = Zend_Registry::get('db');
        $cpUtil = Zend_Registry::get('cpUtil');

        $modObj = getCPModuleObj('manPower_company');
        $client_code = $modObj->model->getUpdateClientCode();

        $call_registry_id = $fn->getReqParam('call_registry_id');
        $callRegistryRec  = $fn->getRecordRowById('call_registry', 'call_registry_id', $call_registry_id);

        $sql1 = "SELECT * FROM opportunity WHERE call_registry_id = {$call_registry_id}";
        $result1 = $db->sql_query($sql1);
        $row1 = $db->sql_fetchrow($result1);
        $numRows = $db->sql_numrows($result1);

        if ($numRows == 0){
            if ($callRegistryRec['no_of_candidates'] > 0) {
                //to check if company is already created
                $sqlComp = "SELECT company_name
                                  ,company_id
                            FROM company
                            WHERE company_name = '{$callRegistryRec['company_name']}'
                            AND site_id = {$callRegistryRec['site_id']}
                            ";
                $resultComp = $db->sql_query($sqlComp);
                $rowComp = $db->sql_fetchrow($resultComp);
                $numRows = $db->sql_numrows($resultComp);
                if($numRows == 0){
                    $fa2 = array();
                    $fa2['site_id']          = $callRegistryRec['site_id'];
                    $fa2['company_name']     = $callRegistryRec['company_name'];
                    $fa2['client_code']      = $client_code;
                    $fa2['contact_person']   = $callRegistryRec['contact_name'];
                    $fa2['phone']            = $callRegistryRec['phone'];
                    $fa2['email']            = $callRegistryRec['email'];
                    $fa2['creation_date']    = date("Y-m-d H:i:s");
                    $fa2['created_by']       = $fn->getSessionParam('userName');
                    $fa2['staff_id']         = $_SESSION['staff_id'];

                    $SQLCompany    = $dbUtil->getInsertSQLStringFromArray($fa2, 'company');
                    $resultCompany = $db->sql_query($SQLCompany);
                    $company_id    = $db->sql_nextid();
                }
                else{
                    $company_id = $rowComp['company_id'];
                }

                //to check if contact is already created
                $sqlComp = "SELECT name
                                  ,contact_id
                            FROM contact
                            WHERE name = '{$callRegistryRec['contact_name']}'
                            AND site_id = {$callRegistryRec['site_id']}
                            ";
                $resultComp = $db->sql_query($sqlComp);
                $rowComp = $db->sql_fetchrow($resultComp);
                $numRows = $db->sql_numrows($resultComp);
                if($numRows == 0){
                    $fa3 = array();
                    $fa3['site_id']      = $callRegistryRec['site_id'];
                    $fa3['name']         = $callRegistryRec['contact_name'];
                    $fa3['company_id']   = $company_id;
                    $fa3['creation_date']= date("Y-m-d H:i:s");

                    $SQLContact    = $dbUtil->getInsertSQLStringFromArray($fa3, 'contact');
                    $resultContact = $db->sql_query($SQLContact);
                    $contact_id = $db->sql_nextid();
                }
                else{
                    $contact_id = $rowComp['contact_id'];
                }

                $opportunity_id = '';

                for ($i = 1; $i <= $callRegistryRec['no_of_candidates']; $i++) {
                    $fa = array();
                    $fa['site_id']          = $callRegistryRec['site_id'];
                    $fa['call_registry_id'] = $call_registry_id;
                    $fa['company_id']       = $company_id;
                    //$fa['description']      = $callRegistryRec['description'];
                    $fa['title']            = $callRegistryRec['title'];

                    $fa['industry']         = $callRegistryRec['title'];
                    $fa['status']           = 'Candidate Search';
                    $fa['chance']           = '10%';
                    $fa['difficulty']       = '1';
                    $fa['currency']         = 'SG$';
                    $fa['enquiry_date']     = date('Y-m-d');
                    $fa['follow_up_date']   = date('Y-m-d', strtotime("+7 days"));
                    $fa['staff_id']         = $callRegistryRec['staff_id'];

                    $fa['creation_date']    = date("Y-m-d H:i:s");
                    $fa['created_by']       = $fn->getSessionParam('userName');

                    $modObj = getCPModuleObj('manPower_opportunity');
                    $fa['opportunity_code'] = $modObj->model->getUpdateOpportunityCode();

                    $SQL            = $dbUtil->getInsertSQLStringFromArray($fa, 'opportunity');
                    $result         = $db->sql_query($SQL);
                    $opportunity_id = $db->sql_nextid();

                    /* Linking of Opportunity and Staff */
                    if ($callRegistryRec['staff_id']) {
                        $fa1 = array();
                        $fa1['opportunity_id']  = $opportunity_id;
                        $fa1['staff_id']        = $callRegistryRec['staff_id'];
                        $fa1['creation_date']   = date("Y-m-d H:i:s");

                        $SQL            = $dbUtil->getInsertSQLStringFromArray($fa1, 'opportunity_staff');
                        $result         = $db->sql_query($SQL);
                    }

                    $spArray = array(
                         'Candidate Sourcing'
                        ,'Interview'
                        ,'Authorisation From Client'
                        ,'Application Launching'
                        ,'IPA'
                        ,'Documents To Agent'
                        ,'Air Ticket Confirmation'
                    );

                    $count = 0;
                    foreach ($spArray as $row){
                        $title = $spArray[$count];
                        $today = date("Y-m-d");
                        $today_day = $fn->getCPDate($today, 'D');

                        /* Linking of Opportunity and Task */
                        $fa4 = array();
                        $fa4['title']  = $title;
                        $fa4['opportunity_id'] = $opportunity_id;
                        $fa4['creation_date']  = date("Y-m-d H:i:s");
                        $fa4['site_id']     = $callRegistryRec['site_id'];
                        //$fa4['staff_id']    = $callRegistryRec['staff_id'];
                        if($title == 'Candidate Sourcing'){
                            $fa4['from_date']  = $today;
                            $fa4['due_date']   = strftime("%Y-%m-%d", strtotime("$today +1 day"));
                        } else if($title == 'Interview'){
                            $fa4['from_date']  = strftime("%Y-%m-%d", strtotime("$today +2 day"));
                            $fa4['due_date']   = strftime("%Y-%m-%d", strtotime("$today +3 day"));
                        } else if($title == 'Authorisation From Client'){
                            $fa4['from_date']  = strftime("%Y-%m-%d", strtotime("$today +4 day"));
                            $fa4['due_date']   = strftime("%Y-%m-%d", strtotime("$today +5 day"));
                        } else if($title == 'Application Launching'){
                            $fa4['from_date']  = strftime("%Y-%m-%d", strtotime("$today +6 day"));
                            $fa4['due_date']   = strftime("%Y-%m-%d", strtotime("$today +7 day"));
                        } else if($title == 'IPA'){
                            $fa4['from_date']  = strftime("%Y-%m-%d", strtotime("$today +8 day"));
                            $fa4['due_date']   = strftime("%Y-%m-%d", strtotime("$today +14 day"));
                        } else if($title == 'Documents To Agent'){
                            $fa4['from_date']  = strftime("%Y-%m-%d", strtotime("$today +15 day"));
                            $fa4['due_date']   = strftime("%Y-%m-%d", strtotime("$today +16 day"));
                        } else if($title == 'Air Ticket Confirmation'){
                            $fa4['from_date']  = strftime("%Y-%m-%d", strtotime("$today +17 day"));
                            $fa4['due_date']   = strftime("%Y-%m-%d", strtotime("$today +23 day"));
                        }
                        $fa4['status']          = 'Due';

                        $SQL            = $dbUtil->getInsertSQLStringFromArray($fa4, 'task');
                        $result         = $db->sql_query($SQL);
                        $task_id = $db->sql_nextid();

                        $count++;

                        /* Linking of Task and Staff */
                        if ($callRegistryRec['staff_id']) {
                            $fa5 = array();
                            $fa5['task_id']         = $task_id;
                            $fa5['staff_id']        = $callRegistryRec['staff_id'];
                            $fa5['creation_date']   = date("Y-m-d H:i:s");

                            $SQL            = $dbUtil->getInsertSQLStringFromArray($fa5, 'task_staff');
                            $result         = $db->sql_query($SQL);
                        }
                    }
                }
                $cpUtil->redirect("index.php?_topRm=opportunity&module=manPower_opportunity&_action=edit&opportunity_id={$opportunity_id}");
            }
        } else {
            //This url will run when the no. of candidate is 0
            $cpUtil->redirect("index.php?_topRm=marketing&module=manPower_callRegistry&_action=edit&call_registry_id={$call_registry_id}");
        }
    }

    /**
     *
     */
    function getDuplicateSubmit() {
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');
        $db = Zend_Registry::get('db');
        $validate = Zend_Registry::get('validate');
        $cpUtil = Zend_Registry::get('cpUtil');

        $today  = date('Y-m-d');
        $call_date     = $fn->getReqParam('contact_date');

        if (!$this->getDuplicateValidate()){
            return $validate->getErrorMessageXML();
        }

        $call_registry_id = $fn->getReqParam('call_registry_id');
        $callRegistryRec  = $fn->getRecordRowById('call_registry', 'call_registry_id', $call_registry_id);

        $fa = array();

        $fa['status']           = 'Not Called';

        $fa['site_id']          = $callRegistryRec['site_id'];
        $fa['company_name']     = $callRegistryRec['company_name'];
        $fa['contact_name']     = $callRegistryRec['contact_name'];
        $fa['reffer']           = $callRegistryRec['reffer'];
        $fa['reminder']         = $callRegistryRec['reminder'];
        $fa['phone']            = $callRegistryRec['phone'];
        $fa['email']            = $callRegistryRec['email'];
        $fa['no_of_candidates'] = $callRegistryRec['no_of_candidates'];
        $fa['requirements']     = $callRegistryRec['requirements'];
        $fa['description']      = $callRegistryRec['description'];
        $fa['staff_id']         = $callRegistryRec['staff_id'];
        $fa['follow_up_date']   = $callRegistryRec['follow_up_date'];
        $fa['address']          = $callRegistryRec['address'];
        $fa['title']            = $callRegistryRec['title'];

        $fa['contact_date']     = $call_date;
        $fa['contact_time']     = date('H:i:s');

        $fa['creation_date']    = date("Y-m-d H:i:s");
        $fa['created_by']       = $fn->getSessionParam('userName');

        $fa['call_registry_code'] = $this->getUpdateCallRegistryCode();


        $SQL              = $dbUtil->getInsertSQLStringFromArray($fa, 'call_registry');
        $result           = $db->sql_query($SQL);
        $call_registry_id = $db->sql_nextid();

		$retUrl = "index.php?_topRm=marketing&module=manPower_callRegistry&_action=edit&call_registry_id={$call_registry_id}";
        return $validate->getSuccessMessageXML($retUrl);

    }
    /**
     *
     */
    function getDuplicateValidate() {
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');
        $ln = Zend_Registry::get('ln');
        $db = Zend_Registry::get('db');

        $today       = date('Y-m-d');
        $call_date   = $fn->getReqParam('contact_date');


        $validate->resetErrorArray();
        $validate->validateData('contact_date', 'Please select the date');

		if($call_date < $today){
            $validate->errorArray['contact_date']['name'] = "contact_date";
            $validate->errorArray['contact_date']['msg']  = "Please enter the future date.";
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
    function getUpdateCallRegistryCode() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');

        /* Updation of Call Registry Code */
		if($cpCfg['cp.hasMultiUniqueSites'] == 1 && $_SESSION['cp_site_id'] == 1){
            $nextCallRegCode = $fn->getSettingsValueByKey("nextCallRegistryCode");

	        if($nextCallRegCode < 10){
	            $callRegCode = $fn->getSettingsValueByKey('callRegistryPrefix') . '00' . $nextCallRegCode;
	        }
	        else if($nextCallRegCode < 99){
	            $callRegCode = $fn->getSettingsValueByKey('callRegistryPrefix') . '0' . $nextCallRegCode;
	        }
	        else{
	            $callRegCode = $fn->getSettingsValueByKey('callRegistryPrefix') . $nextCallRegCode;
	        }

            $SQL    = "UPDATE setting SET value = (value+1) WHERE key_text = 'nextCallRegistryCode'";
            $result = $db->sql_query($SQL);

            return $callRegCode;
        } else {
            $nextCallRegCode = $fn->getSettingsValueByKey("nextCallRegistryCode2");

	        if($nextCallRegCode < 10){
	            $callRegCode = $fn->getSettingsValueByKey('callRegistryPrefix') . '000' . $nextCallRegCode;
	        } else if($nextCallRegCode < 99) {
	            $callRegCode = $fn->getSettingsValueByKey('callRegistryPrefix') . '00' . $nextCallRegCode;
	        } else if($nextCallRegCode < 999){
	            $callRegCode = $fn->getSettingsValueByKey('callRegistryPrefix') . '0' . $nextCallRegCode;
	        } else {
	            $callRegCode = $fn->getSettingsValueByKey('callRegistryPrefix') . $nextCallRegCode;
	        }

            $SQL    = "UPDATE setting SET value = (value+1) WHERE key_text = 'nextCallRegistryCode2'";
            $result = $db->sql_query($SQL);

            return $callRegCode;

      	}
    }

    /**
     *
     */
    function getSearchCompanyName() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $cpUtil = Zend_Registry::get('cpUtil');
        $dbUtil = Zend_Registry::get('dbUtil');

        $title = $fn->getReqParam('term', '', true);
        $extractor = explode(" **** ", $title);

        $companyName = $extractor[0];
        $site_id = $_SESSION['cp_site_id'];
        $appendSQL = '';

        if ($_SESSION['userGroupType'] == 'User') {
            $appendSQL = " AND c.staff_id = {$_SESSION['staff_id']}";
        }

        $SQL = "
        SELECT c.company_name AS value
              ,c.company_name AS label
        	  ,c.company_id AS id
        FROM company c
        WHERE c.company_name LIKE '%{$companyName}%'
          AND c.site_id = {$site_id}
          {$appendSQL}
        ";
        $result = $db->sql_query($SQL);

        $dataArray = $dbUtil->getResultsetAsArray($result);
        $arr = json_encode($dataArray);
        return $arr;
    }

    /**
     *
     */
    function getUpdateCompanyDetails() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $cpUtil = Zend_Registry::get('cpUtil');
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');

        $company_id = $fn->getReqParam('company_id');
        $arr = array('company_name' => '', 'contact_person' => '', 'phone' => '', 'email' => '');

        $SQL    = "
        SELECT c.*
        FROM company c
        WHERE c.company_id = '{$company_id}'
        ";
        $result = $db->sql_query($SQL);
        $row = $db->sql_fetchrow($result);

        $arr['company_name'] = $row['company_name'];
        $arr['contact_person'] = $row['contact_person'];
        $arr['phone'] = $row['phone'];
        $arr['email']   = $row['email'];

        return $cpUtil->getJsonFromArray($arr);
    }

    function getCreateClientRec() {
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');
        $db = Zend_Registry::get('db');
        $cpUtil = Zend_Registry::get('cpUtil');

        $call_registry_id = $fn->getReqParam('call_registry_id');
        $callRegistryRec  = $fn->getRecordRowById('call_registry', 'call_registry_id', $call_registry_id);


        $company_name = $fn->getReqParam('company_name');
        $contact_person = $fn->getReqParam('contact_person');
        $phone = $fn->getReqParam('phone');
        $email = $fn->getReqParam('email');

				$companyNameSql = $fn->getRecordByCondition('company', "company_name = '{$company_name}'");

			  $arr['message'] = '';
        if(is_array($companyNameSql)){
            $arr['message'] = "Please note the company is already added";
            return $cpUtil->getJsonFromArray($arr);
        } else {

            $fa2 = array();
            $fa2['site_id']             = $_SESSION['cp_site_id'];
            $fa2['company_name']        = $company_name;
            $fa2['contact_person']      = $contact_person;
            $fa2['phone']               = $phone;
            $fa2['email']               = $email;
            $fa2['category']            = 'Marketing';

            $fa2['creation_date']    = date("Y-m-d H:i:s");
            $fa2['created_by']       = $fn->getSessionParam('userName');

            $SQLCompany    = $dbUtil->getInsertSQLStringFromArray($fa2, 'company');
            $resultCompany = $db->sql_query($SQLCompany);
            $company_id = $db->sql_nextid();

            $arr['message'] = "Company added successfully in client.";
            return $cpUtil->getJsonFromArray($arr);
        }
    }

    /**
     *
     */
    function getStatusByCategoryJSON(){
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');

        $rows = "";
        $appendSql = "";
        $append1Sql = "";

        $category = $fn->getReqParam('category');
        $site_id  = $_SESSION['cp_site_id'];

        if ($site_id) {
            $appendSql .= "AND site_id = {$site_id}";
        }

        $json  = array();

        if ($category == ""){
            return json_encode($json);
        }

        if ($category == 'Direct Marketing'){
            $append1Sql .= "AND value != 'Not in Use'";
        } else if ($category == 'Internet Marketing') {
            $append1Sql .= "AND value != 'Not in Use' AND value != 'Not Interested'";
        }

        $SQL = "
        SELECT value
        FROM valuelist
        WHERE key_text = 'callRegistryStatus'
          {$append1Sql}
          {$appendSql}
        ORDER BY value
        ";

        $result   = $db->sql_query($SQL);
        $json[] = array("value" => "", "caption" => "Please Select");
        while ($row = $db->sql_fetchrow($result)) {
            $json[] = array("value" => $row['value'], "caption" => $row['value']);
        }

        return json_encode($json);
    }

    /**
     *
     */
    function getSendProfileToClientFormSubmit() {
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');
        $db = Zend_Registry::get('db');
	    $cpCfg = Zend_Registry::get('cpCfg');
        $media = Zend_Registry::get('media');
        $localPath = CP_LOCAL_PATH_ALIAS;

        $profile_message = $fn->getPostParam('profile_message');
        $call_registry_id  = $fn->getReqParam('call_registry_id');

        if($_SESSION['cp_site_id'] == 1){
            $profile_message = $cpCfg['profileEmailDraftForSingapore'];
        } else {
            $profile_message = $cpCfg['profileEmailDraftForAbudhabi'];
        }

        if (!$this->getSendProfileToClientFormValidate()){
            return $validate->getErrorMessageXML();
        }

        $rowCallRegistry = $fn->getRecordRowByID('call_registry', 'call_registry_id', $call_registry_id);
        $rowStaff  = $fn->getRecordRowByID('staff', 'staff_id', $rowCallRegistry['staff_id']);
        $exp = array('appendSiteUrl' => 1);
        $pic = $media->getMediaPicture('manPower_staff', 'signature', $rowCallRegistry['staff_id'], $exp);
        $staffName = $rowStaff['first_name'] . ' ' . $rowStaff['last_name'];
        $link = $cpCfg['cp.siteUrl'] . 'Westrama Profile.pdf';
        $logo = "<img src='{$cpCfg['cp.siteUrl']}{$localPath}images/email_logo.png' border='0'>";
        $qrCode = "<img src='{$cpCfg['cp.siteUrl']}{$localPath}images/qr-code.jpg' border='0'>";
        $facebook = "<a href='https://www.facebook.com/WestramaManagementCompany'><img src='{$cpCfg['cp.siteUrl']}{$localPath}images/facebook.png' border='0'></a>";
        $google = "<a href='https://plus.google.com/103869950261474615388/'><img src='{$cpCfg['cp.siteUrl']}{$localPath}images/google.png' border='0'></a>";
        $linkedin = "<a href='http://sg.linkedin.com/company/westrama-management-company'><img src='{$cpCfg['cp.siteUrl']}{$localPath}images/linkedin.png' border='0'></a>";
        $twitter = "<a href='https://twitter.com/westrama'><img src='{$cpCfg['cp.siteUrl']}{$localPath}images/twitter.png' border='0'></a>";

        //$message = nl2br($profile_message) . "<a href= '{$link}'> Click here for the PDF </a>";
        $message = $profile_message;
        $message = str_replace('[[staff_sign]]', $pic, $message);
        $message = str_replace('[[staff_name]]', $staffName, $message);
        $message = str_replace('[[designation]]', $rowStaff['designation'], $message);
        $message = str_replace('[[phone]]', $rowStaff['phone'], $message);
        $message = str_replace('[[logo]]', $logo, $message);
        $message = str_replace('[[qr_code]]', $qrCode, $message);
        $message = str_replace('[[facebook]]', $facebook, $message);
        $message = str_replace('[[google]]', $google, $message);
        $message = str_replace('[[linkedin]]', $linkedin, $message);
        $message = str_replace('[[twitter]]', $twitter, $message);
        $message = str_replace('[[link]]', $link, $message);

        $subject   = 'Westrama - Contact for all HR Requirements and Services';
        $fromName  = $_SESSION['userFullName'];
        $fromEmail = $_SESSION['email'];
        $toName    = $rowCallRegistry['company_name'];
        $toEmail   = $rowCallRegistry['email'];
        //$toEmail   = 'moin@usoftsolutions.com';

        $args = array(
             'toName'    => $toName
            ,'toEmail'   => $toEmail
            ,'subject'   => $subject
            ,'message'   => $message
            ,'fromName'  => $fromName
            ,'fromEmail' => $fromEmail
        );

        $emailMsg = includeCPClass('Lib', 'EmailTemplate', 'EmailTemplate', true, array('args' => $args));
        if($toEmail){
            $emailMsg->sendEmail();
        }

        return $validate->getSuccessMessageXML();
    }

    /**
     *
     */
    function getSendProfileToClientFormValidate() {
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }
}
