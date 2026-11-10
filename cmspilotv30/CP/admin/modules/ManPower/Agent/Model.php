<?
class CP_Admin_Modules_ManPower_Agent_Model extends CP_Common_Lib_ModuleModelAbstract
{
    function getSQL() {
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');

        $opportunity_id = $fn->getReqParam('opportunity_id');

        if ($opportunity_id != "") {
            $extraTableNames .= "opportunity opp,";
        }

        if ($cpCfg['m.manPower.hasMultipleCompanyAddress'] == 1) {
            $SQL   = "
            SELECT a.*
                   ,CONCAT_WS(' ', a.first_name, a.last_name ) AS agent_name
                   ,b.company_name    AS c_company_name
                   ,b.email           AS c_email
                   ,b.address_flat    AS c_address_flat
                   ,b.address_street  AS c_address_street
                   ,b.address_town    AS c_address_town
                   ,b.address_state   AS c_address_state
                   ,b.address_country_code AS c_address_country
                   ,b.address_po_code AS c_address_po_code
                   ,b.phone           AS c_phone
                   ,b.fax             AS c_fax
                   ,b.status          AS c_status
                   ,b.website         AS c_website
                   ,b.category        AS c_category
                   ,d.address_flat    AS comp_mul_address_flat
                   ,d.address_street  AS comp_mul_address_street
                   ,d.address_town    AS comp_mul_address_town
                   ,d.address_state   AS comp_mul_address_state
                   ,d.address_country AS comp_mul_address_country

            FROM agent a
            LEFT JOIN (company b) ON ( a.company_id = b.company_id )
            LEFT JOIN (company_address d) ON ( a.company_address_id = d.company_address_id )
                    ";
        } else {
            $SQL   = "
            SELECT a.*,
            gc.name AS company_country_name,
            CONCAT_WS(' ', a.first_name, a.last_name ) AS agent_name,
            b.company_name    AS c_company_name,
            b.email           AS c_email,
            b.address_flat    AS c_address_flat,
            b.address_street  AS c_address_street,
            b.address_town    AS c_address_town,
            b.address_state   AS c_address_state,
            b.address_country_code AS c_address_country,
            b.address_po_code AS c_address_po_code,
            b.phone           AS c_phone,
            b.fax             AS c_fax,
            b.status          AS c_status,
            b.website         AS c_website,
            b.category        AS c_category
            FROM agent a
            LEFT JOIN (company b) ON ( a.company_id = b.company_id )
            LEFT JOIN (geo_country gc) ON (a.company_address_country = gc.country_code)
            ";
        }

        return $SQL;
    }

    /**
     *
     */
    function getManPowerAgentManPowerContactLinkSQL($id) {

        return "
        SELECT c.contact_id
              ,c.first_name
              ,c.last_name
              ,c.email
              ,c.phone_direct
              ,c.mobile
              ,c.position
              ,c.contact_priority
        FROM agent a, contact c
        WHERE c.agent_id = a.agent_id
          AND a.agent_id = {$id}
        ORDER BY c.contact_priority ASC
        ";

    }

    /**
     *
     */
    function getUpdateAgentCode() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');

        $site_id = $fn->getSessionParam('cp_site_id');
        $nextAgentCode = $fn->getSettingsValueByKey("nextAgentCode");

        /*if($nextAgentCode < 10){
            $agentCode = $fn->getSettingsValueByKey('agentPrefix') . '0' . $nextAgentCode;
        } else {*/
			$agentCode = $fn->getSettingsValueByKey('agentPrefix') . $nextAgentCode;
        //}

        $SQL    = "UPDATE setting SET value = (value+1) WHERE key_text = 'nextAgentCode'";
        $result = $db->sql_query($SQL);

        return $agentCode;

    }

    /**
     *
     */
    function setSearchVar($linkRecType = '') {
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $searchVar = Zend_Registry::get('searchVar');
        $searchVar->mainTableAlias = 'a';

        $opportunity_id = $fn->getReqParam('opportunity_id');
        $company_id     = $fn->getReqParam('company_id');
        $agent_id     = $fn->getReqParam('agent_id');
        $first_name     = $fn->getReqParam('first_name');
        $last_name      = $fn->getReqParam('last_name');
        $subscribe      = $fn->getReqParam('subscribe');
        $category       = $fn->getReqParam('category');
        $special_search = $fn->getReqParam('special_search');

        if ($tv['searchDone'] == 0){
            $status = 'Current';
        }

        if ($_SESSION['userGroupType'] == 'User') {
            $searchVar->sqlSearchVar[] = "a.staff_id = {$_SESSION['staff_id']}";
        }

        if ($agent_id != "") {
            $searchVar->sqlSearchVar[] = "a.agent_id = '{$agent_id}'";
        } else if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "a.agent_id = '{$tv['record_id']}'";
        } else {

            $fn->setSearchVarForLinkData($searchVar, $linkRecType, 'a.agent_id');

            //------------------------------------------------------------------------//
            if ($tv['special_search'] == "Subscribed") {
                $searchVar->sqlSearchVar[] = "a.subscribe = 1";
            }

            if ($tv['special_search'] == "Not-Subscribed") {
                $searchVar->sqlSearchVar[] = "(a.subscribe != 1 OR a.subscribe IS null)";
            }

            if ($tv['special_search'] == "Flagged") {
                $searchVar->sqlSearchVar[] = "a.flag = 1";
            }

            if ($tv['special_search'] == "Not-Flagged") {
                $searchVar->sqlSearchVar[] = "(a.flag != 1 OR a.flag IS null)";
            }

            if ($tv['special_search']  == 'Published') {
                $searchVar->sqlSearchVar[] = "a.published = 1";
            }

            if ($tv['special_search'] == 'Not-Published' ) {
                $searchVar->sqlSearchVar[] = "a.published = 0 OR a.published IS NULL OR a.published = ''";
            }

            //------------------------------------------------------------------------//
            if ($company_id != "") {
                $searchVar->sqlSearchVar[] = "b.company_id = {$company_id}";
            }

            if ($tv['keyword'] != "") {
                $searchVar->sqlSearchVar[] = "(
                       a.first_name   LIKE '%{$tv['keyword']}%'
                    OR a.last_name    LIKE '%{$tv['keyword']}%'
                    OR b.company_name LIKE '%{$tv['keyword']}%'
                    OR a.email        LIKE '%{$tv['keyword']}%'
                )";
                    //OR a.user_name    LIKE '%{$tv['keyword']}%'
                    //OR a.name         LIKE '%{$tv['keyword']}%'
            }

            if ($first_name != "") {
                $searchVar->sqlSearchVar[] = "a.first_name = '{$first_name}'";
            }

            if ($last_name != "") {
                $searchVar->sqlSearchVar[] = "a.last_name = '{$last_name}'";
            }

            if ($category != "") {
                $searchVar->sqlSearchVar[] = "a.category = '{$category}'";
            }

            if ($tv['spAction'] == 'link' && $tv['module'] == 'broadcast' ){
                $searchVar->sqlSearchVar[] = "a.subscribe = 1";
            }

            $searchVar->sortOrder = "a.last_name, a.first_name";
        }
    }

    /**
     *
     */
    function getNewValidate() {
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();

        $validate->validateData('first_name', 'Please enter the company name');

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
        $fa['staff_id']     = $_SESSION['staff_id'];
        $fa['status'] 		= 'Current';
        $fa['agent_code']   = $this->getUpdateAgentCode();
        $id = $fn->addRecord($fa);
        $fn->returnAfterNewSave($id);
    }

    /**
     *
     */
    function getEditValidate() {
        $validate = Zend_Registry::get('validate');
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');

        $validate->resetErrorArray();

        $validate->validateData('first_name', 'Please enter the Company Name');
        //$validate->validateData('category', 'Please enter the Category');
        //$validate->validateData('pass_word', 'Please enter the Password');
        //$validate->validateData('contract_date', 'Please enter the Contract Date');
        //$validate->validateData('phone_direct', 'Please enter the Phone');
        //$validate->validateData('email',  'Please enter the valid Email');

        $email      = $fn->getPostParam('email', '', true);
        $record_id  = $fn->getReqParam('agent_id');

        if ($email != ''){
            if(!$validate->isEmail($email)){
                $validate->errorArray['email']['name'] = "email";
                $validate->errorArray['email']['msg']  = "Please enter valid email";
            }
            else{
                $rec = $fn->getRecordByCondition('agent', "email = '{$email}' AND agent_id != {$record_id}");
                if (is_array($rec)){
                    $expEmail = array('displayText'=> 'Goto the Existing Agent Record', 'target' => 'blank');
                    $emailLink = $fn->getRecordDetailLink('manPower_agent', 'record_id', $rec['agent_id'], $expEmail);

                    $validate->errorArray['email']['name'] = "email";
                    $validate->errorArray['email']['msg']  = "Email already exists. '{$emailLink}'";
                }
            }
        }

        $phone_direct = $fn->getPostParam('phone_direct', '', true);
        if (!ctype_digit($phone_direct)) {
            //$validate->errorArray['phone_direct']['name'] = "phone_direct";
            //$validate->errorArray['phone_direct']['msg']  = "Please enter only nos eg: 1500";
        }

        $company_address_country = $fn->getReqParam('company_address_country');
        $nric_no                 = $fn->getReqParam('nric_no');
        $passport_no             = $fn->getReqParam('passport_no');

        if ($company_address_country == 'IN') {
            if ($passport_no == '') {
                //$validate->validateData('passport_no', 'Please enter Passport no');
            } else if ($passport_no != '') {
                $rec = $fn->getRecordByCondition('agent', "passport_no = '{$passport_no}' AND agent_id != {$record_id}");
                if (is_array($rec)){
                    $expAgent  = array('displayText'=> 'Goto the Existing Agent Record', 'target' => 'blank');
                    $agentLink = $fn->getRecordDetailLink('manPower_agent', 'record_id', $rec['agent_id'], $expAgent);

                    $validate->errorArray['passport_no']['name'] = "passport_no";
                    $validate->errorArray['passport_no']['msg']  = "Passport No already exists. '{$agentLink}'";
                }
            }
        } else {
            if ($nric_no == '') {
                //$validate->validateData('nric_no', 'Please enter NRIC no');
            } else if ($nric_no != '') {
                $rec = $fn->getRecordByCondition('agent', "nric_no = '{$nric_no}' AND agent_id != {$record_id}");
                if (is_array($rec)){
                    $expAgent  = array('displayText'=> 'Goto the Existing Agent Record', 'target' => 'blank');
                    $agentLink = $fn->getRecordDetailLink('manPower_agent', 'record_id', $rec['agent_id'], $expAgent);

                    $validate->errorArray['nric_no']['name'] = "nric_no";
                    $validate->errorArray['nric_no']['msg']  = "NRIC No already exists. '{$agentLink}'";
                }
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
    function getSave($tableName = '', $keyField = '', $keyFieldVal = ''){
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        $cpCfg = Zend_Registry::get('cpCfg');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');


        if (!$this->getEditValidate()){
            return $validate->getErrorMessageXML();
        }

        $fa = $this->getFields();
        $id = $fn->saveRecord($fa, $tableName, $keyField, $keyFieldVal);

        $first_name = $fn->getReqParam('first_name');
        $last_name = $fn->getReqParam('last_name');
        $email = $fn->getReqParam('email');
        $pass_word = $fn->getReqParam('pass_word');
        $published = $fn->getReqParam('published');

        $SQL = "
        SELECT user_group_id
        FROM user_group
        WHERE user_group_type = 'Agent'
        ";
        $result  = $db->sql_query($SQL);
        $row = $db->sql_fetchrow($result);

        $site_id = 0;
        if($cpCfg['cp.hasMultiUniqueSites'] == true) {
            $site_id = $_SESSION['cp_site_id'];
        }

        $fa2 = array();
        $fa2['first_name'] = $first_name;
        $fa2['last_name']  = $last_name;
        $fa2['email']      = $email;
        $fa2['pass_word']  = $pass_word;
        $fa2['staff_login_type'] = 'Agent';
        $fa2['site_id']  = $site_id;
        $fa2['user_group_id']  = $row['user_group_id'];
        $fa2['status']  = 'Current';
        $fa2['published']  = $published;
        $fa2['agent_id']  = $id;

        $SQL = "
        SELECT agent_id
        FROM staff
        WHERE agent_id = '{$id}'
        ";
        $result  = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);

        if ($numRows == 0){
            $SQL = $dbUtil->getInsertSQLStringFromArray($fa2, 'staff');
            $db->sql_query($SQL);
        } else {
            $whereCondition = "WHERE agent_id = '{$id}'";
            $sqlUpdate = $dbUtil->getUpdateSQLStringFromArray($fa2, "staff", $whereCondition);
            $resultUpdate      = $db->sql_query($sqlUpdate);
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
    function getFields(){
        $fn = Zend_Registry::get('fn');

        $fa = array();

        $fa = $fn->addToFieldsArray($fa, 'agent_code');
        $fa = $fn->addToFieldsArray($fa, 'name');
        $fa = $fn->addToFieldsArray($fa, 'first_name');
        $fa = $fn->addToFieldsArray($fa, 'last_name');
        $fa = $fn->addToFieldsArray($fa, 'salutation');
        $fa = $fn->addToFieldsArray($fa, 'email');
        $fa = $fn->addToFieldsArray($fa, 'company_name');
        $fa = $fn->addToFieldsArray($fa, 'address_flat');
        $fa = $fn->addToFieldsArray($fa, 'address_street');
        $fa = $fn->addToFieldsArray($fa, 'address_town');
        $fa = $fn->addToFieldsArray($fa, 'address_state');
        $fa = $fn->addToFieldsArray($fa, 'address_country');
        $fa = $fn->addToFieldsArray($fa, 'address_po_code');
        $fa = $fn->addToFieldsArray($fa, 'phone');
        $fa = $fn->addToFieldsArray($fa, 'phone_direct');
        $fa = $fn->addToFieldsArray($fa, 'fax');
        $fa = $fn->addToFieldsArray($fa, 'mobile');
        $fa = $fn->addToFieldsArray($fa, 'position');
        $fa = $fn->addToFieldsArray($fa, 'company_address_id');
        $fa = $fn->addToFieldsArray($fa, 'company_id');
        $fa = $fn->addToFieldsArray($fa, 'position');
        $fa = $fn->addToFieldsArray($fa, 'department');
        $fa = $fn->addToFieldsArray($fa, 'site_id');
        $fa = $fn->addToFieldsArray($fa, 'staff_id');
        $fa = $fn->addToFieldsArray($fa, 'subscribe');
        $fa = $fn->addToFieldsArray($fa, 'published');
        $fa = $fn->addToFieldsArray($fa, 'chi_name');
        $fa = $fn->addToFieldsArray($fa, 'chi_position');
        $fa = $fn->addToFieldsArray($fa, 'chi_department');
        $fa = $fn->addToFieldsArray($fa, 'category');
        $fa = $fn->addToFieldsArray($fa, 'status');
        $fa = $fn->addToFieldsArray($fa, 'pass_word');
        $fa = $fn->addToFieldsArray($fa, 'website');
        $fa = $fn->addToFieldsArray($fa, 'company_address_flat');
        $fa = $fn->addToFieldsArray($fa, 'company_address_street');
        $fa = $fn->addToFieldsArray($fa, 'company_address_town');
        $fa = $fn->addToFieldsArray($fa, 'company_address_state');
        $fa = $fn->addToFieldsArray($fa, 'company_address_country');
        $fa = $fn->addToFieldsArray($fa, 'contract_date');
        $fa = $fn->addToFieldsArray($fa, 'nric_no');
        $fa = $fn->addToFieldsArray($fa, 'passport_no');
        $fa = $fn->addToFieldsArray($fa, 'address_country_code');

        return $fa;
    }

    /**
     *
     */
    function getExportData1($dataArray){
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');

        set_time_limit(50000);
        ini_set('memory_limit', '512M');


        require_once("PHPExcel.php");
        include 'PHPExcel/IOFactory.php';

        $file_name = "Agent_" . date("d-m-Y") . ".xls";

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

        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Agent Id');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Salutation');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'First Name');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Last Name');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Email');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Position');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Phone');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Fax');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Mobile');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Subscribed');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Company Name');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Company Website');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Company Phone');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Company Fax');

        if($cpCfg['m.project.hasMultipleCompanyAddress'] == 1) {
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Flat');
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Street');
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Town');
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'State');
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Country');

            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Home Flat');
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Home Street');
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Home Town');
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Home State');
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Home Country');

        } else {
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Flat');
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Street');
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Town');
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'State');
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Country');
        }

        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Category');

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
        //============================================================================= //
        foreach ($dataArray as $row){
            $colc = 0;
            $rowc++;

            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['agent_id']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['salutation']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['first_name']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['last_name']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['email']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['position']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['phone_direct']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['fax']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['mobile']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['subscribe']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['c_company_name']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['c_website']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['c_phone']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['c_fax']);

            if($cpCfg['m.project.hasMultipleCompanyAddress'] == 1) {
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['comp_mul_address_flat']);
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['comp_mul_address_street']);
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['comp_mul_address_town']);
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['comp_mul_address_state']);
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['comp_mul_address_country']);

                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['address_flat']);
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['address_street']);
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['address_town']);
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['address_state']);
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['address_country']);
            } else {
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['c_address_flat']);
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['c_address_street']);
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['c_address_town']);
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['c_address_state']);
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['c_address_country']);
            }
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['c_category']);
        }

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
    }

    /**
     *
     */
    function getAgentByCompanyJSON(){
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');

        $rows = "";

        $company_id   = $fn->getReqParam('company_id');

        $json  = array();

        if ($company_id == ""){
            return json_encode($json);
        }

        $SQL = "
        SELECT agent_id
              ,CONCAT_WS(' ', first_name, last_name) AS agent_name
        FROM agent
        WHERE company_id = '{$company_id}'
        ORDER BY agent_name
        ";
        $result   = $db->sql_query($SQL);

        $json[] = array("value" => "", "caption" => "Please Select");
        while ($row = $db->sql_fetchrow($result)) {
            $json[] = array("value" => $row['agent_id'], "caption" => $row['agent_name']);
        }

        return json_encode($json);
    }

    /**
     *
     */

    function getAgentDocumentSubmit(){
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        $dbUtil = Zend_Registry::get('dbUtil');
        $db = Zend_Registry::get('db');

        $agent_id = $fn->getReqParam('agent_id');
        $documents_id = $fn->getReqParam('documents_id');
        $documents = $fn->getReqParam('documents');

        $fa = array();
        $fa['agent_id'] = $agent_id;
        $fa['documents_id'] = $documents_id;
        $fa['site_id'] = $_SESSION['cp_site_id'];
        if($documents == 1){
            print 'aaaaa';
            $SQL = $dbUtil->getInsertSQLStringFromArray($fa, 'agent_documents');
            $db->sql_query($SQL);
            $agent_documents_id = $db->sql_nextid();
            return 'yes';
        } else{
            $sql = "
            DELETE FROM agent_documents
            WHERE agent_id = {$agent_id}
              AND documents_id = {$documents_id}
            ";
            $result = $db->sql_query($sql);
        }
    }

    /**
     *
     */
    function getMultipleAddress(){
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');

        $company_id   = $fn->getReqParam('company_id');
        $json  = array();

        if($company_id == ""){
            return json_encode($json);
        }


        $SQL    = "
        SELECT   company_address_id
                 , CONCAT_WS(', ', address_flat, address_street, address_town, address_country) AS address
        FROM     company_address a
        WHERE    company_id = {$company_id}
        ORDER BY company_address_id
        ";

        $result   = $db->sql_query($SQL);

        $json[] = array("value" => "", "caption" => "Please Select");
        while ($row = $db->sql_fetchrow($result)) {
            $json[] = array("value" => $row['company_address_id'], "caption" => $row['address']);
        }

        return json_encode($json);
    }

    /**
     *
     */
    function getCompanyAddress(){
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');

        $company_id   = $fn->getReqParam('company_id');

        $SQL = "
        SELECT *
        FROM company
        WHERE company_id = {$company_id}";
        $result = $db->sql_query($SQL);
        $row = $db->sql_fetchrow($result);

        $json = array("address_street" => $row['address_street'], "address_flat" => $row['address_flat'],
                "address_town" => $row['address_town'], "address_state" => $row['address_state'],
                "address_country" => $row['address_country']
        );

        return json_encode($json);
    }

    /**
     *
     */
    function getEmailValidation(){
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');

        $email   = $fn->getReqParam('email');
        $agent_id   = $fn->getReqParam('agent_id');
        $email  = trim($email);
        $append = "";

        if($agent_id != ""){
            $append = "AND agent_id != {$agent_id}";
        }

        $SQL = "
        SELECT email
        FROM   agent
        WHERE  email = '{$email}'
               AND email != ''
               {$append}
        ";
        $result  = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);
        $check = ($numRows >= 1) ? 1 : 0;

        return $check;

    }

    /**
     *
     */
    function getExportData($dataArray){
        $phpExcel = includeCPClass('Lib', 'PhpExcelExportWrapper', 'PhpExcelExportWrapper');

        $fa = array(
              'agent_id'            => $phpExcel->getFldObj('Agent ID')
             ,'salutation'          => $phpExcel->getFldObj('Salutation')
             ,'first_name'          => $phpExcel->getFldObj('First Name')
             ,'last_name'           => $phpExcel->getFldObj('Last Name')
             ,'email'               => $phpExcel->getFldObj('Email')
             ,'position'            => $phpExcel->getFldObj('Position')
             ,'phone_direct'        => $phpExcel->getFldObj('Phone')
             ,'fax'                 => $phpExcel->getFldObj('Fax')
             ,'mobile'              => $phpExcel->getFldObj('Mobile')
             ,'subscribe'           => $phpExcel->getFldObj('Subscribed')
             ,'c_company_name'      => $phpExcel->getFldObj('Company Name')
             ,'c_website'           => $phpExcel->getFldObj('Company Website')
             ,'c_phone'             => $phpExcel->getFldObj('Company Phone')
             ,'c_fax'               => $phpExcel->getFldObj('Company Fax')

             ,'c_address_flat'      => $phpExcel->getFldObj('Flat')
             ,'c_address_street'    => $phpExcel->getFldObj('Street')
             ,'c_address_town'      => $phpExcel->getFldObj('Town')
             ,'c_address_state'     => $phpExcel->getFldObj('State')
             ,'c_address_country'   => $phpExcel->getFldObj('Country')

             ,'c_category'           => $phpExcel->getFldObj('Category')
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
    function getManPowerAgentManPowerCandidateLinkSQL($id) {

        return "
        SELECT c.candidate_id
              ,CONCAT_WS(' ', c.first_name, c.last_name) AS title
        FROM `candidate` c
            ,`agent_candidate` ac
        WHERE c.candidate_id = ac.candidate_id
          AND ac.agent_id = {$id}
        ORDER BY title
        ";

    }

    /**
     *
     */
    function getCandidateCountrySubmit(){
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        $dbUtil = Zend_Registry::get('dbUtil');
        $db = Zend_Registry::get('db');

        $agent_id = $fn->getReqParam('agent_id');
        $candidate_country_id = $fn->getReqParam('candidate_country_id');
        $country = $fn->getReqParam('country');

        $fa = array();
        $fa['agent_id'] = $agent_id;
        $fa['candidate_country_id'] = $candidate_country_id;
        $fa['site_id'] = $_SESSION['cp_site_id'];
        if($country == 1){
            $SQL = $dbUtil->getInsertSQLStringFromArray($fa, 'agent_country');
            $db->sql_query($SQL);
            $agent_country_id = $db->sql_nextid();
            return 'Yes';
        } else{
            $sql = "
            DELETE FROM agent_country
            WHERE agent_id = {$agent_id}
              AND candidate_country_id = {$candidate_country_id}
            ";
            $result = $db->sql_query($sql);
            return 'No';
        }
    }

    /**
     *
     */
    function getCandidatePassSubmit(){
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        $dbUtil = Zend_Registry::get('dbUtil');
        $db = Zend_Registry::get('db');

        $agent_id = $fn->getReqParam('agent_id');
        $candidate_pass_id = $fn->getReqParam('candidate_pass_id');
        $country = $fn->getReqParam('country');

        $fa = array();
        $fa['agent_id'] = $agent_id;
        $fa['candidate_pass_id'] = $candidate_pass_id;
        $fa['site_id'] = $_SESSION['cp_site_id'];
        if($country == 1){
            $SQL = $dbUtil->getInsertSQLStringFromArray($fa, 'agent_pass');
            $db->sql_query($SQL);
            $agent_pass_id = $db->sql_nextid();
            return 'Yes';
        } else{
            $sql = "
            DELETE FROM agent_pass
            WHERE agent_id = {$agent_id}
              AND candidate_pass_id = {$candidate_pass_id}
            ";
            $result = $db->sql_query($sql);
            return 'No';
        }
    }
    /**
     *
     */
    function getImportData(){
        $cpCfg = Zend_Registry::get('cpCfg');
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');

        $phpExcel = includeCPClass('Lib', 'PhpExcelImportWrapper', 'PhpExcelImportWrapper');
        $fa = array(
              'category'            => $phpExcel->getFldObj('Category')
             ,'salutation'          => $phpExcel->getFldObj('Salutation')
             ,'first_name'          => $phpExcel->getFldObj('First Name')
             ,'last_name'           => $phpExcel->getFldObj('Last Name')
             ,'email'               => $phpExcel->getFldObj('Email')
             ,'company_name'        => $phpExcel->getFldObj('Company Name')
             ,'position'            => $phpExcel->getFldObj('Position')
             ,'phone_direct'        => $phpExcel->getFldObj('Phone')
             ,'fax'                 => $phpExcel->getFldObj('Fax')
             ,'mobile'              => $phpExcel->getFldObj('Mobile')
             ,'department'          => $phpExcel->getFldObj('department')
             ,'subscribe'           => $phpExcel->getFldObj('Subscribed')
             ,'status'              => $phpExcel->getFldObj('Status')
             ,'pass_word'           => $phpExcel->getFldObj('Password')

        );

        $config = array(
             'module'        => 'common_geoRegion'
            ,'matchFieldArr' => array('region_code')
            ,'fldsArr'       => $fa
        );

        return $phpExcel->importData($config);
    }

    function getPrintAgentContract() {
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

        $agent_id    = $fn->getReqParam('agent_id');
        $agentRec    = $fn->getRecordRowByID('agent', 'agent_id', $agent_id);
        $contactRec  = $fn->getRecordRowByID('contact', 'agent_id', $agent_id);


        $template = 'Agency Agreement.docx';
        $templatePath = $cpCfg['cp.localPath'].'lib/template/' . $template;
        $TBS->LoadTemplate($templatePath);
        $rnd_no = mt_rand();
        $file_name = 'Agency Agreement_' . '_' . $rnd_no . '.docx';
        $file_name = str_replace('.', '_' . date('Y-m-d') . '.', $file_name);

        $path = realpath($cpCfg['cp.mediaFolder']) . '\temp';
        $file_name_save = $path . '\\' . $file_name;
        $sourceFilePath = $file_name_save;

		$contract_date = $agentRec['contract_date'];
        $contractDay   = $fn->getCPDate($contract_date, 'l');
        $contractDated = $fn->getCPDate($contract_date, 'd/m/Y');
        $contractdated = $fn->getCPDate($contract_date, 'dS F Y');
        $valArr['contract_dated']             = $contractdated;

        $valArr = array();
        /* Agent Details */
        $valArr['contract_day']              = $contractDay;
        $valArr['contract_date']             = $contractDated;
        $valArr['contract_dated']             = $contractdated;
        $valArr['comapany_name']             = $agentRec['first_name'];
        $valArr['company_address_flat']      = $agentRec['company_address_flat'];
        $valArr['company_address_street']    = $agentRec['company_address_street'];
        $valArr['company_address_town']      = $agentRec['company_address_town'];
        $valArr['company_address_state']     = $agentRec['company_address_state'];
        $valArr['company_address_country']   = $agentRec['company_address_country'];
        $valArr['employee_name']            = $contactRec['name'];

        $blkMain   = array();
        $blkMain[] = $valArr;

        $TBS->MergeBlock('blkMain', $blkMain);

        $TBS->Show(OPENTBS_DOWNLOAD, $file_name);
    }

    /**
     *
     */
    /*function getSendAgentLoginDetailsEmail() {
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');

        $text = '';

        set_time_limit(500000);

        $SQL = "
        SELECT a.* FROM agent a
        WHERE a.email != ''
        ";
        $result  = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);

        //-----------------------------------------------------------------------------//
        $smtp = includeCPClass('Lib', 'smtp', 'CPSMTP');
        //$smtp->SMTPKeepAlive = true;
        //-----------------------------------------------------------------------------//
        $SERVER = $_SERVER['HTTP_HOST'];

        while ($row = $db->sql_fetchrow($result)) {
            $toName     = $row['first_name'] . ' ' . $row['last_name'];
            $toEmail    = $row['email'];

            $fromName   = "Admin Westrama";
            $fromEmail  = "enquiry@westrama.com";

            $subject    = $cpCfg['m.manPower.agent.email.agentNotifySubject'];
            $message    = $cpCfg['m.manPower.agent.email.agentNotifyBody'];

            $subject    = "Westrama - Agent Login";
            $message    = $this->getAgentEmailBody($row);

            $message = str_replace("[[email]]"      , $row["email"], $message );
            $message = str_replace("[[pass_word]]"  , $row["pass_word"], $message );
            $message = str_replace("[[company_name]]"  , $fromName, $message );

            $error = '';
            $error = $smtp->sendEmail($toName, $toEmail, $fromName, $fromEmail, $subject, $message);
        }

        return $text;
    }

    function getAgentEmailBody($row) {

        $company_name = $row['first_name'] . ' ' . $row['last_name'];

        return "
        Dear {$company_name},<br/><br/>

        We would like to inform that we have developed an online software named Man Power Management System(MMS)  for our all business process.<br/><br/>

        Hereafter we would like. you to make use of this software, to enter all the details of the Candidate in this software.<br/><br/>

        Below are the details of the software link and the username/pass.<br/>
        Link : http://westrama.usscrm.com/admin<br/>
        Username: {$row['email']}<br/>
        Password: {$row['pass_word']}<br/><br/>

        Please login with the details and upload all your candidate details.<br/><br/>

        We will be informing the new opportunities through email, below are the steps to be followed when you get a new opportunity.<br/><br/>

        + Please goto the MMS tool and populate the Candidate details. Fields marked mandatory in Candidate section should be populated, else we cannot progress the Candidate further.<br/>
        + Once you populated please click the 'Message to Staff' button in the top right of the Candidate<br/>
        + Enter the Opportunity Code which you got in your email and Submit.<br/>
        + By doing this, you are intimating us that a related Candidate has been populated.<br/>
        + We will evaluate the Candidate and communicate with you.<br/><br/>

        For any clarifications please contact us.<br/><br/>

        Thanks<br/>
        Westrama Management Company
        ";
    }*/
}
