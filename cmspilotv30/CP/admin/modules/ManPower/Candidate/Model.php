<?
class CP_Admin_Modules_ManPower_Candidate_Model extends CP_Common_Lib_ModuleModelAbstract
{
    function getSQL17102013syed() {
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');

        if ($cpCfg['m.manPower.hasMultipleCompanyAddress'] == 1) {
            $SQL   = "
            SELECT c.*
                   ,CONCAT_WS(' ', c.first_name, c.last_name ) AS candidate_name
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
	               ,gc.name AS country_name
            FROM candidate c
            LEFT JOIN (company b) ON ( c.company_id = b.company_id )
            LEFT JOIN (company_address d) ON ( c.company_address_id = d.company_address_id )
	        LEFT JOIN (geo_country gc) ON (c.address_country = gc.country_code)
                    ";
        } else {
            $SQL   = "
            SELECT c.*,
            CONCAT_WS(' ', c.first_name, c.last_name ) AS candidate_name,
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
            b.category        AS c_category,
            o.opportunity_code AS opportunity_code,
            gc.name AS country_name,
            CONCAT_WS(' ', a.first_name, a.last_name) AS agent_name
            FROM candidate c
            LEFT JOIN (company b) ON ( c.company_id = b.company_id )
            LEFT JOIN (agent a) ON ( c.agent_id = a.agent_id )
	        LEFT JOIN (geo_country gc) ON (c.address_country = gc.country_code)
            LEFT JOIN (opportunity_candidate op ) ON ( c.candidate_id = op.candidate_id )
            LEFT JOIN (opportunity o) ON ( op.opportunity_id = o.opportunity_id )
            ";
        }

        return $SQL;
    }

    function getSQL() {
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');

        if ($cpCfg['m.manPower.hasMultipleCompanyAddress'] == 1) {
            $SQL   = "
            SELECT DISTINCT c.*
                   ,CONCAT_WS(' ', c.first_name, c.last_name ) AS candidate_name
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
	               ,gc.name AS country_name
                   ,pc.position_title
            FROM candidate c
            LEFT JOIN (company b) ON ( c.company_id = b.company_id )
            LEFT JOIN (company_address d) ON ( c.company_address_id = d.company_address_id )
	        LEFT JOIN (geo_country gc) ON (c.address_country = gc.country_code)
            LEFT JOIN (position_candidate pc) ON (pc.candidate_id = c.candidate_id)
                    ";
        } else {
            $SQL   = "
            SELECT  c.*,
            CONCAT_WS(' ', c.first_name, c.last_name ) AS candidate_name,
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
            b.category        AS c_category,
            gc.name AS country_name,
            pc.position_title,
            CONCAT_WS(' ', a.first_name, a.last_name) AS agent_name
            FROM candidate c
            LEFT JOIN (company b) ON ( c.company_id = b.company_id )
            LEFT JOIN (agent a) ON ( c.agent_id = a.agent_id )
	        LEFT JOIN (geo_country gc) ON (c.address_country = gc.country_code)
            LEFT JOIN (position_candidate pc) ON (pc.candidate_id = c.candidate_id)
            ";
        }

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

        $agent_id        = $fn->getReqParam('agent_id');
        $opportunity_id  = $fn->getReqParam('opportunity_id');
        $company_id      = $fn->getReqParam('company_id');
        $position        = $fn->getReqParam('position');
        $candidate_id    = $fn->getReqParam('candidate_id');
        $first_name      = $fn->getReqParam('first_name');
        $last_name       = $fn->getReqParam('last_name');
        $subscribe       = $fn->getReqParam('subscribe');
        $special_search  = $fn->getReqParam('special_search');
        $status          = $fn->getReqParam('status');
        $locked          = $fn->getReqParam('locked');
        $advanced_search = $fn->getReqParam('advanced_search');

        if ($_SESSION['userGroupType'] == 'User') {
            $searchVar->sqlSearchVar[] = "c.staff_id = {$_SESSION['staff_id']}";
        }

        if($position != ''){
            $searchVar->sqlSearchVar[] = "pc.position_title = '{$position}'";
        }

        if ($candidate_id != "") {
            $searchVar->sqlSearchVar[] = "c.candidate_id = '{$candidate_id}'";
        } else if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "c.candidate_id = '{$tv['record_id']}'";
        } else {

            $fn->setSearchVarForLinkData($searchVar, $linkRecType, 'c.candidate_id');

            if ($locked == "Yes") {
                $searchVar->sqlSearchVar[] = "c.edit_locked = 1";
            }

            if ($locked == "No") {
                $searchVar->sqlSearchVar[] = "(c.edit_locked != 1 OR c.edit_locked IS null)";
            }

            //------------------------------------------------------------------------//
            if ($tv['special_search'] == "Subscribed") {
                $searchVar->sqlSearchVar[] = "c.subscribe = 1";
            }

            if ($tv['special_search'] == "Not-Subscribed") {
                $searchVar->sqlSearchVar[] = "(c.subscribe != 1 OR c.subscribe IS null)";
            }

            if ($tv['special_search'] == "Flagged") {
                $searchVar->sqlSearchVar[] = "c.flag = 1";
            }

            if ($tv['special_search'] == "Not-Flagged") {
                $searchVar->sqlSearchVar[] = "(c.flag != 1 OR c.flag IS null)";
            }

            if ($tv['special_search']  == 'Published') {
                $searchVar->sqlSearchVar[] = "c.published = 1";
            }

            if ($tv['special_search'] == 'Not-Published' ) {
                $searchVar->sqlSearchVar[] = "c.published = 0 OR c.published IS NULL OR c.published = ''";
            }

            //------------------------------------------------------------------------//
            if ($agent_id != "") {
                $searchVar->sqlSearchVar[] = "c.agent_id = {$agent_id}";
            }

            if ($company_id != "") {
                $searchVar->sqlSearchVar[] = "c.company_id = {$company_id}";
            }

            if ($tv['keyword'] != "") {
                $searchVar->sqlSearchVar[] = "(
                       c.first_name   LIKE '%{$tv['keyword']}%'
                    OR c.last_name    LIKE '%{$tv['keyword']}%'
                    OR c.company_name LIKE '%{$tv['keyword']}%'
                    OR c.email        LIKE '%{$tv['keyword']}%'
                    OR b.company_name LIKE '%{$tv['keyword']}%'
                )";
                    //OR c.user_name    LIKE '%{$tv['keyword']}%'
                    //OR c.name         LIKE '%{$tv['keyword']}%'

            }

            if($advanced_search != ''){
                $resultRecord_id = $this->view->getConvertDocumentsIntoText($advanced_search);
                if($resultRecord_id == ''){
                    $searchVar->sqlSearchVar[] = "c.candidate_id IS NULL";
                }else{
                    $searchVar->sqlSearchVar[] = "c.candidate_id IN ({$resultRecord_id})";
                }
            }

            if ($first_name != "") {
                $searchVar->sqlSearchVar[] = "c.first_name = '{$first_name}'";
            }

            if ($last_name != "") {
                $searchVar->sqlSearchVar[] = "c.last_name = '{$last_name}'";
            }

            if ($status != "") {
                $searchVar->sqlSearchVar[] = "c.status = '{$status}'";
            }

            if ($tv['spAction'] == 'link' && $tv['module'] == 'broadcast' ){
                $searchVar->sqlSearchVar[] = "c.subscribe = 1";
            }

            if ($_SESSION['userGroupType'] == 'Agent') {
                $rowAgent = $fn->getRecordByCondition('agent', "email = '{$_SESSION['email']}'");
                $searchVar->sqlSearchVar[] = "c.agent_id = '{$rowAgent['agent_id']}'";
            }

            $searchVar->sortOrder = "c.first_name";
            $searchVar->groupBy = "c.candidate_id";
        }
    }

    /**
     *
     */
    function getNewValidate() {
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();

        $validate->validateData('first_name', 'Please enter the first name');
        $validate->validateData('last_name', 'Please enter the last name');

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
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
        $candidate_id    = $fn->getReqParam('candidate_id');

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
        }

        $whereCondition = "WHERE candidate_id = {$candidate_id}";
        $sqlUpdate = $dbUtil->getUpdateSQLStringFromArray($fa1, "candidate", $whereCondition);
        $resultUpdate      = $db->sql_query($sqlUpdate);

        return $validate->getSuccessMessageXML('', $valuelist_value);
    }

    /**
     *
     */
    function getAddPositionCandidate() {
        $fn       = Zend_Registry::get('fn');
        $db       = Zend_Registry::get('db');
        $dbUtil   = Zend_Registry::get('dbUtil');
        $validate = Zend_Registry::get('validate');

        $position     = $fn->getReqParam('position');
        $candidate_id = $fn->getReqParam('candidate_id');

        $fa = array();
        $fa['position_title']  = $position;
        $fa['candidate_id']    = $candidate_id;

        $insert = $dbUtil->getInsertSQLStringFromArray($fa, 'position_candidate');
        $result = $db->sql_query($insert);

    }

    /**
     *
     */
    function getDeletePositionCandidate() {
        $fn       = Zend_Registry::get('fn');
        $db       = Zend_Registry::get('db');
        $dbUtil   = Zend_Registry::get('dbUtil');
        $validate = Zend_Registry::get('validate');

        $position     = $fn->getReqParam('position');
        $candidate_id = $fn->getReqParam('candidate_id');

        $deletePosition = "
        DELETE FROM position_candidate
        WHERE candidate_id ={$candidate_id}
        AND position_title ='{$position}'";

        $result = $db->sql_query($deletePosition);
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

        $rowAgent = $fn->getRecordByCondition('agent', "email = '{$_SESSION['email']}'");

        $agent_name = '';
        $staff_id = '';
        if ($_SESSION['userGroupType'] == 'Agent') {
            $agent_name = $rowAgent['agent_id'];
        } else {
            $staff_id = $_SESSION['staff_id'];
        }

        $fa = $this->getFields();
        $fa['agent_id'] = $agent_name;
        $fa['staff_id'] = $staff_id;
        $fa['candidate_code']   = $this->getUpdateCandidateCode();
        $id = $fn->addRecord($fa);
        $fn->returnAfterNewSave($id);
    }

    /**
     *
     */
    function getEditValidate($fa = array()) {
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');

        $validate->resetErrorArray();

        /*if ($fa['visa_by_another_country'] == 1) {
            $validate->validateData('country_of_issue' , 'Please enter the Country of Issue *');
            $validate->validateData('length_visa' , 'Please enter the Length of Visa *');
        }*/

        $validate->validateData('first_name', 'Please enter the First Name');
        $validate->validateData('last_name', 'Please enter the Last Name');
        //$validate->validateData('salutation' , 'Please select Salutation');
        //$validate->validateData('sex' , 'Please select Sex');
        //$validate->validateData('martial_status' , 'Please select Martial Status');
        //$validate->validateData('date_of_birth' , 'Please enter the Date Of Birth');
        //$validate->validateData('nationality' , 'Please select Nationality');
        //$validate->validateData('address_country' , 'Please select Country of  Birth');
        //$validate->validateData('address_country1' , 'Please select Country of  Origin');
        //$validate->validateData('race' , 'Please select the Race');
        //$validate->validateData('religion' , 'Please select the Religion');
        //$validate->validateData('travel_document_type' , 'Please select Travel Document Type');
        //$validate->validateData('travel_document_no' , 'Please enter the Travel Document No');
        //$validate->validateData('date_of_expiry' , 'Please enter the Date Of Expiry');

        //$validate->validateData('education_country1' , 'Please select the Country');
        //$validate->validateData('degree_name1' , 'Please enter the Name');
        //$validate->validateData('college_name1' , 'Please enter the Main Campus or Affiliating College Attended');
        //$validate->validateData('education_qualification1' , 'Please select Qualification');
        //$validate->validateData('education_faculty1' , 'Please select the Faculty');
        //$validate->validateData('education_specialisation1' , 'Please select Specialisation');
        /*if ($fa['education_specialisation1'] == 'NONE OF THE ABOVE') {
            $validate->validateData('education_none_of_the_above1' , 'Please enter Specialisation');
        }
        $validate->validateData('mode_of_study1' , 'Please select Mode of Study');
        $validate->validateData('period_of_study_from1' , 'Please enter the Period of Study From Date');
        $validate->validateData('period_of_study_to1' , 'Please enter the Period of Study To Date');*/

        /*$validate->validateData('employment_company_name1' , 'Please enter the Name of Company');
        $validate->validateData('employment_occupation1' , 'Please enter occupation');
        $validate->validateData('employment_period_from1' , 'Please enter the Period of Employment From Date');
        $validate->validateData('employment_period_to1' , 'Please enter the Period of Employment To Date');
        $validate->validateData('employment_salary1' , 'Please enter Fixed Monthly  Salary');
        $validate->validateData('job_duties_responsibilities1' , 'Please enter JOB DUTIES & RESPONSIBILITIES');*/
        //$validate->validateData('email_address' , 'Please enter the valid Email');
        //$validate->validateData('purpose' , 'Please enter the Staying Purpose');


        $email_address = $fn->getPostParam('email_address', '', true);
        $record_id     = $fn->getReqParam('candidate_id');

        if ($email_address != ''){
            if(!$validate->isEmail($email_address)){
                $validate->errorArray['email_address']['name'] = "email_address";
                $validate->errorArray['email_address']['msg']  = "Please enter valid email";
            }
            else{
                $recCand = $fn->getRecordByCondition('candidate', "email_address = '{$email_address}' AND candidate_id != {$record_id}");
                if (is_array($recCand)){
                    $expEmail = array('displayText'=> 'Goto Existing Candidate Record');
                    $emailLink = $fn->getRecordDetailLink('manPower_candidate', 'record_id', $recCand['candidate_id'], $expEmail);

                    $validate->errorArray['email_address']['name'] = "email_address";
                    /* Hiding the goto candidate link which is not allowed for agent login */
                    if ($_SESSION['userGroupType'] == 'Agent') {
                        $validate->errorArray['email_address']['msg']  = "Email already exists.";
                    } else {
                        $validate->errorArray['email_address']['msg']  = "Email already exists. '{$emailLink}'";
                    }
                }
            }
        }

        $travel_document_no = $fn->getPostParam('travel_document_no', '', true);
        $recCand = $fn->getRecordByCondition('candidate', "travel_document_no = '{$travel_document_no}' AND candidate_id != {$record_id}");
        if (is_array($recCand)){
            $expTravelNo = array('displayText'=> 'Goto Existing Candidate Record');
            $gotoRecordLink = $fn->getRecordDetailLink('manPower_candidate', 'record_id', $recCand['candidate_id'], $expTravelNo);

            //$validate->errorArray['travel_document_no']['name'] = "travel_document_no";
            /* Hiding the goto candidate link which is not allowed for agent login */
            if ($_SESSION['userGroupType'] == 'Agent') {
                //$validate->errorArray['travel_document_no']['msg']  = "Passport No already exists.";
            } else {
                //$validate->errorArray['travel_document_no']['msg']  = "Passport No already exists. '{$gotoRecordLink}'";
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

        $fa = $this->getFields();
        if (!$this->getEditValidate($fa)){
            return $validate->getErrorMessageXML();
        }

        $id = $fn->saveRecord($fa, $tableName, $keyField, $keyFieldVal);
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

        $fa = $fn->addToFieldsArray($fa, 'candidate_code');
        $fa = $fn->addToFieldsArray($fa, 'name');
        $fa = $fn->addToFieldsArray($fa, 'first_name');
        $fa = $fn->addToFieldsArray($fa, 'last_name');
        $fa = $fn->addToFieldsArray($fa, 'salutation');
        $fa = $fn->addToFieldsArray($fa, 'email');
        $fa = $fn->addToFieldsArray($fa, 'address_flat');
        $fa = $fn->addToFieldsArray($fa, 'address_street');
        $fa = $fn->addToFieldsArray($fa, 'address_town');
        $fa = $fn->addToFieldsArray($fa, 'address_state');
        $fa = $fn->addToFieldsArray($fa, 'address_country');
        $fa = $fn->addToFieldsArray($fa, 'candidate_mobile_no');
        $fa = $fn->addToFieldsArray($fa, 'email_address');
        $fa = $fn->addToFieldsArray($fa, 'residential_address');
        $fa = $fn->addToFieldsArray($fa, 'home_no');
        $fa = $fn->addToFieldsArray($fa, 'father_mother_no');
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
        $fa = $fn->addToFieldsArray($fa, 'agent_id');
        $fa = $fn->addToFieldsArray($fa, 'subscribe');
        $fa = $fn->addToFieldsArray($fa, 'published');
        $fa = $fn->addToFieldsArray($fa, 'chi_name');
        $fa = $fn->addToFieldsArray($fa, 'chi_position');
        $fa = $fn->addToFieldsArray($fa, 'chi_department');
        $fa = $fn->addToFieldsArray($fa, 'category');
        $fa = $fn->addToFieldsArray($fa, 'status');
        $fa = $fn->addToFieldsArray($fa, 'passport_no');
        $fa = $fn->addToFieldsArray($fa, 'name_alias');
        $fa = $fn->addToFieldsArray($fa, 'sex');
        $fa = $fn->addToFieldsArray($fa, 'martial_status');
        $fa = $fn->addToFieldsArray($fa, 'date_of_birth');
        $fa = $fn->addToFieldsArray($fa, 'nationality');
        $fa = $fn->addToFieldsArray($fa, 'address_country1');
        $fa = $fn->addToFieldsArray($fa, 'address_state');
        $fa = $fn->addToFieldsArray($fa, 'race');
        $fa = $fn->addToFieldsArray($fa, 'religion');
        $fa = $fn->addToFieldsArray($fa, 'travel_document_type');
        $fa = $fn->addToFieldsArray($fa, 'travel_document_no');
        $fa = $fn->addToFieldsArray($fa, 'date_of_expiry');
        $fa = $fn->addToFieldsArray($fa, 'no_of_education1');
        $fa = $fn->addToFieldsArray($fa, 'details_of_education1');
        $fa = $fn->addToFieldsArray($fa, 'submit_documents_for_qualification1');
        $fa = $fn->addToFieldsArray($fa, 'university1');
        $fa = $fn->addToFieldsArray($fa, 'education_country1');
        $fa = $fn->addToFieldsArray($fa, 'degree_name1');
        $fa = $fn->addToFieldsArray($fa, 'college_name1');
        $fa = $fn->addToFieldsArray($fa, 'education_qualification1');
        $fa = $fn->addToFieldsArray($fa, 'education_faculty1');
        $fa = $fn->addToFieldsArray($fa, 'education_none_of_the_above1');
        $fa = $fn->addToFieldsArray($fa, 'education_specialisation1');
        $fa = $fn->addToFieldsArray($fa, 'mode_of_study1');
        $fa = $fn->addToFieldsArray($fa, 'period_of_study_from1');
        $fa = $fn->addToFieldsArray($fa, 'period_of_study_to1');
        $fa = $fn->addToFieldsArray($fa, 'degree_name2');
        $fa = $fn->addToFieldsArray($fa, 'college_name2');
        $fa = $fn->addToFieldsArray($fa, 'no_of_education2');
        $fa = $fn->addToFieldsArray($fa, 'details_of_education2');
        $fa = $fn->addToFieldsArray($fa, 'submit_documents_for_qualification2');
        $fa = $fn->addToFieldsArray($fa, 'university2');
        $fa = $fn->addToFieldsArray($fa, 'education_country2');
        $fa = $fn->addToFieldsArray($fa, 'education_qualification2');
        $fa = $fn->addToFieldsArray($fa, 'education_faculty2');
        $fa = $fn->addToFieldsArray($fa, 'education_none_of_the_above2');
        $fa = $fn->addToFieldsArray($fa, 'education_specialisation2');
        $fa = $fn->addToFieldsArray($fa, 'mode_of_study2');
        $fa = $fn->addToFieldsArray($fa, 'period_of_study_from2');
        $fa = $fn->addToFieldsArray($fa, 'period_of_study_to2');
        $fa = $fn->addToFieldsArray($fa, 'degree_name3');
        $fa = $fn->addToFieldsArray($fa, 'college_name3');
        $fa = $fn->addToFieldsArray($fa, 'no_of_education3');
        $fa = $fn->addToFieldsArray($fa, 'details_of_education3');
        $fa = $fn->addToFieldsArray($fa, 'submit_documents_for_qualification3');
        $fa = $fn->addToFieldsArray($fa, 'university3');
        $fa = $fn->addToFieldsArray($fa, 'education_country3');
        $fa = $fn->addToFieldsArray($fa, 'education_qualification3');
        $fa = $fn->addToFieldsArray($fa, 'education_faculty3');
        $fa = $fn->addToFieldsArray($fa, 'education_none_of_the_above3');
        $fa = $fn->addToFieldsArray($fa, 'education_specialisation3');
        $fa = $fn->addToFieldsArray($fa, 'mode_of_study3');
        $fa = $fn->addToFieldsArray($fa, 'period_of_study_from3');
        $fa = $fn->addToFieldsArray($fa, 'period_of_study_to3');
        $fa = $fn->addToFieldsArray($fa, 'degree_name4');
        $fa = $fn->addToFieldsArray($fa, 'college_name4');
        $fa = $fn->addToFieldsArray($fa, 'no_of_education4');
        $fa = $fn->addToFieldsArray($fa, 'details_of_education4');
        $fa = $fn->addToFieldsArray($fa, 'submit_documents_for_qualification4');
        $fa = $fn->addToFieldsArray($fa, 'university4');
        $fa = $fn->addToFieldsArray($fa, 'education_country4');
        $fa = $fn->addToFieldsArray($fa, 'education_qualification4');
        $fa = $fn->addToFieldsArray($fa, 'education_faculty4');
        $fa = $fn->addToFieldsArray($fa, 'education_none_of_the_above4');
        $fa = $fn->addToFieldsArray($fa, 'education_specialisation4');
        $fa = $fn->addToFieldsArray($fa, 'mode_of_study4');
        $fa = $fn->addToFieldsArray($fa, 'period_of_study_from4');
        $fa = $fn->addToFieldsArray($fa, 'period_of_study_to4');
        $fa = $fn->addToFieldsArray($fa, 'degree_name5');
        $fa = $fn->addToFieldsArray($fa, 'college_name5');
        $fa = $fn->addToFieldsArray($fa, 'no_of_education5');
        $fa = $fn->addToFieldsArray($fa, 'details_of_education5');
        $fa = $fn->addToFieldsArray($fa, 'submit_documents_for_qualification5');
        $fa = $fn->addToFieldsArray($fa, 'university5');
        $fa = $fn->addToFieldsArray($fa, 'education_country5');
        $fa = $fn->addToFieldsArray($fa, 'education_qualification5');
        $fa = $fn->addToFieldsArray($fa, 'education_faculty5');
        $fa = $fn->addToFieldsArray($fa, 'education_none_of_the_above5');
        $fa = $fn->addToFieldsArray($fa, 'education_specialisation5');
        $fa = $fn->addToFieldsArray($fa, 'mode_of_study5');
        $fa = $fn->addToFieldsArray($fa, 'period_of_study_from5');
        $fa = $fn->addToFieldsArray($fa, 'period_of_study_to5');
        $fa = $fn->addToFieldsArray($fa, 'period_of_working');
        $fa = $fn->addToFieldsArray($fa, 'period_of_relevant_experience');
        $fa = $fn->addToFieldsArray($fa, 'employment_company_name1');
        $fa = $fn->addToFieldsArray($fa, 'employment_occupation1');
        $fa = $fn->addToFieldsArray($fa, 'employment_country1');
        $fa = $fn->addToFieldsArray($fa, 'employment_period_from1');
        $fa = $fn->addToFieldsArray($fa, 'employment_period_to1');
        $fa = $fn->addToFieldsArray($fa, 'job_duties_responsibilities1');
        $fa = $fn->addToFieldsArray($fa, 'employment_salary1');
        $fa = $fn->addToFieldsArray($fa, 'employment_company_name2');
        $fa = $fn->addToFieldsArray($fa, 'employment_occupation2');
        $fa = $fn->addToFieldsArray($fa, 'employment_country2');
        $fa = $fn->addToFieldsArray($fa, 'employment_period_from2');
        $fa = $fn->addToFieldsArray($fa, 'employment_period_to2');
        $fa = $fn->addToFieldsArray($fa, 'job_duties_responsibilities2');
        $fa = $fn->addToFieldsArray($fa, 'employment_salary2');
        $fa = $fn->addToFieldsArray($fa, 'employment_company_name3');
        $fa = $fn->addToFieldsArray($fa, 'employment_occupation3');
        $fa = $fn->addToFieldsArray($fa, 'employment_country3');
        $fa = $fn->addToFieldsArray($fa, 'employment_period_from3');
        $fa = $fn->addToFieldsArray($fa, 'employment_period_to3');
        $fa = $fn->addToFieldsArray($fa, 'job_duties_responsibilities3');
        $fa = $fn->addToFieldsArray($fa, 'employment_salary3');
        $fa = $fn->addToFieldsArray($fa, 'employment_company_name4');
        $fa = $fn->addToFieldsArray($fa, 'employment_occupation4');
        $fa = $fn->addToFieldsArray($fa, 'employment_country4');
        $fa = $fn->addToFieldsArray($fa, 'employment_period_from4');
        $fa = $fn->addToFieldsArray($fa, 'employment_period_to4');
        $fa = $fn->addToFieldsArray($fa, 'job_duties_responsibilities4');
        $fa = $fn->addToFieldsArray($fa, 'employment_salary4');
        $fa = $fn->addToFieldsArray($fa, 'employment_company_name5');
        $fa = $fn->addToFieldsArray($fa, 'employment_occupation5');
        $fa = $fn->addToFieldsArray($fa, 'employment_country5');
        $fa = $fn->addToFieldsArray($fa, 'employment_period_from5');
        $fa = $fn->addToFieldsArray($fa, 'employment_period_to5');
        $fa = $fn->addToFieldsArray($fa, 'job_duties_responsibilities5');
        $fa = $fn->addToFieldsArray($fa, 'employment_salary5');
        $fa = $fn->addToFieldsArray($fa, 'refused_deport');
        $fa = $fn->addToFieldsArray($fa, 'convicted');
        $fa = $fn->addToFieldsArray($fa, 'prohibited');
        $fa = $fn->addToFieldsArray($fa, 'different_country_passport');
        $fa = $fn->addToFieldsArray($fa, 'different_name');
        $fa = $fn->addToFieldsArray($fa, 'singapore_citizen');
        $fa = $fn->addToFieldsArray($fa, 'stayed_in');
        $fa = $fn->addToFieldsArray($fa, 'purpose');
        $fa = $fn->addToFieldsArray($fa, 'visa_by_another_country');
        $fa = $fn->addToFieldsArray($fa, 'visa_details');
        $fa = $fn->addToFieldsArray($fa, 'country_of_issue');
        $fa = $fn->addToFieldsArray($fa, 'length_visa');
        $fa = $fn->addToFieldsArray($fa, 'address_country');
        $fa = $fn->addToFieldsArray($fa, 'period_of_working_month');
        $fa = $fn->addToFieldsArray($fa, 'period_of_relevant_month');
        $fa = $fn->addToFieldsArray($fa, 'period_of_working_year');
        $fa = $fn->addToFieldsArray($fa, 'period_of_relevant_year');
        $fa = $fn->addToFieldsArray($fa, 'length_of_stay_other_month');
        $fa = $fn->addToFieldsArray($fa, 'length_of_stay_other_year');
        $fa = $fn->addToFieldsArray($fa, 'length_of_stay_study_month');
        $fa = $fn->addToFieldsArray($fa, 'length_of_stay_study_year');
        $fa = $fn->addToFieldsArray($fa, 'length_of_stay_work_month');
        $fa = $fn->addToFieldsArray($fa, 'length_of_stay_work_year');
        $fa = $fn->addToFieldsArray($fa, 'contract_date');
        $fa = $fn->addToFieldsArray($fa, 'edit_locked');
        $fa = $fn->addToFieldsArray($fa, 'address_country_code');
        $fa = $fn->addToFieldsArray($fa, 'address_po_code');
        $fa = $fn->addToFieldsArray($fa, 'ssn');
        $fa = $fn->addToFieldsArray($fa, 'no_of_withholding');
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

        $file_name = "Candidate_" . date("d-m-Y") . ".xls";

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

        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Candidate Id');
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

            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['candidate_id']);
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
    function getCandidateByCompanyJSON(){
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');

        $rows = "";

        $company_id   = $fn->getReqParam('company_id');

        $json  = array();

        if ($company_id == ""){
            return json_encode($json);
        }

        $SQL = "
        SELECT candidate_id
              ,CONCAT_WS(' ', first_name, last_name) AS candidate_name
        FROM candidate
        WHERE company_id = '{$company_id}'
        ORDER BY candidate_name
        ";
        $result   = $db->sql_query($SQL);

        $json[] = array("value" => "", "caption" => "Please Select");
        while ($row = $db->sql_fetchrow($result)) {
            $json[] = array("value" => $row['candidate_id'], "caption" => $row['candidate_name']);
        }

        return json_encode($json);
    }

    /**
     *
     */
    function getCandidateDocumentSubmit(){
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        $dbUtil = Zend_Registry::get('dbUtil');
        $db = Zend_Registry::get('db');

        $candidate_id = $fn->getReqParam('candidate_id');
        $documents_id = $fn->getReqParam('documents_id');
        $documents = $fn->getReqParam('documents');

        $fa = array();
        $fa['candidate_id'] = $candidate_id;
        $fa['documents_id'] = $documents_id;
        $fa['site_id'] = $_SESSION['cp_site_id'];
        if($documents == 1){
            print 'aaaaa';
            $SQL = $dbUtil->getInsertSQLStringFromArray($fa, 'candidate_documents');
            $db->sql_query($SQL);
            $candidate_documents_id = $db->sql_nextid();
            return 'yes';
        } else{
            $sql = "
            DELETE FROM candidate_documents
            WHERE candidate_id = {$candidate_id}
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
        $candidate_id   = $fn->getReqParam('candidate_id');
        $email  = trim($email);
        $append = "";

        if($candidate_id != ""){
            $append = "AND candidate_id != {$candidate_id}";
        }

        $SQL = "
        SELECT email
        FROM   candidate
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

        $fa1 = array(
              'candidate_id'        => $phpExcel->getFldObj('Candidate ID')
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

        $fa = array(
              'candidate_code'      => $phpExcel->getFldObj('Candidate Code')
             ,'first_name'          => $phpExcel->getFldObj('First Name')
             ,'last_name'           => $phpExcel->getFldObj('Last Name')
             ,'ssn'                 => $phpExcel->getFldObj('SSN')
             ,'email_address'       => $phpExcel->getFldObj('Email')
             ,'candidate_mobile_no' => $phpExcel->getFldObj('Mobile')

             ,'address_flat'        => $phpExcel->getFldObj('Address')
             ,'address_state'       => $phpExcel->getFldObj('State')
        );
        //,'address_country_code'=> $phpExcel->getFldObj('Country')
        $config = array(
             'fldsArr'   => $fa
            ,'dataArray' => $dataArray
        );

        return $phpExcel->exportData($config);
    }

    /**
     *
     */
    function getManPowerCandidateManPowerCandidateLinkSQL() {

        return "
        SELECT c.*
        FROM candidate c
        ";

    }

    function getUpdateCandidateCode() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');

        /* Updation of Call Registry Code */

            $sesstion = '';
            if($cpCfg['cp.hasMultiUniqueSites'] == true) {
                $sesstion = $_SESSION['cp_site_id'] == 1;
            }

			/*if($sesstion){
	            $nextCandidateCode = $fn->getSettingsValueByKey("nextCandidateCode");

		        if($nextCandidateCode < 10){
		            $candidateCode = $fn->getSettingsValueByKey('candidatePrefix') . '0000' . $nextCandidateCode;
		        }else if ($nextCandidateCode < 100){
		            $candidateCode = $fn->getSettingsValueByKey('candidatePrefix') . '000' . $nextCandidateCode;
		        }else if ($nextCandidateCode > 99 && $nextCandidateCode < 1000){
		            $candidateCode = $fn->getSettingsValueByKey('candidatePrefix') . '00' . $nextCandidateCode;
		        }else if ($nextCandidateCode > 999 && $nextCandidateCode < 10000){
		            $candidateCode = $fn->getSettingsValueByKey('candidatePrefix') . '0' . $nextCandidateCode;
		        } else{
		            $candidateCode = $fn->getSettingsValueByKey('candidatePrefix') . $nextCandidateCode;
		        }

	            $SQL    = "UPDATE setting SET value = (value+1) WHERE key_text = 'nextCandidateCode'";
	            $result = $db->sql_query($SQL);

	            return $candidateCode;
            } else {
                $nextCandidateCode = $fn->getSettingsValueByKey("nextCandidateCode2");

		        if($nextCandidateCode < 10){
		            $candidateCode = $fn->getSettingsValueByKey('candidatePrefix') . '0000' . $nextCandidateCode;
		        }else if ($nextCandidateCode < 100){
		            $candidateCode = $fn->getSettingsValueByKey('candidatePrefix') . '000' . $nextCandidateCode;
		        }else if ($nextCandidateCode > 99 && $nextCandidateCode < 1000){
		            $candidateCode = $fn->getSettingsValueByKey('candidatePrefix') . '00' . $nextCandidateCode;
		        }else if ($nextCandidateCode > 999 && $nextCandidateCode < 10000){
		            $candidateCode = $fn->getSettingsValueByKey('candidatePrefix') . '0' . $nextCandidateCode;
		        } else{*/

                    $nextCandidateCode = $fn->getSettingsValueByKey("nextCandidateCode");
		            $candidateCode = $fn->getSettingsValueByKey('candidatePrefix') . $nextCandidateCode;
		        //}

            $SQL    = "UPDATE setting SET value = (value+1) WHERE key_text = 'nextCandidateCode'";
            $result = $db->sql_query($SQL);

            return $candidateCode;

    }

    /**
     *
     */
    function getPrintCandidateDocument() {
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

	    $candidate_id = $fn->getReqParam('candidate_id');
	    $candidateRec  = $fn->getRecordRowByID('candidate', 'candidate_id', $candidate_id);


		$candidateName             = strtoupper($candidateRec['first_name'] . ' ' .$candidateRec['last_name']);

	    $template = 'Candidate_Resume_Format.docx';
	    $templatePath = $cpCfg['cp.localPath'].'lib/template/' . $template;
	    $TBS->LoadTemplate($templatePath);
	    $rnd_no = mt_rand();
	    $file_name = 'Westrama_' . $candidateName . '_' . '.docx';
	    $file_name = str_replace('.', '_' . date('Y-m-d') . '.', $file_name);

	    //$path = realpath($cpCfg['cp.mediaFolder']) . '\temp';
	    //$file_name_save = $path . '\\' . $file_name;
	    //$sourceFilePath = $file_name_save;

	    //$contactRec = $fn->getRecordRowByID('contact', 'contact_id', $contact_id);
	    //$countryNameRec = $fn->getRecordRowByID('geo_country', 'country_code', "'{$staffRec['native_address_country']}'");

        $countryNameRecNationality = $fn->getRecordRowByID('geo_country', 'country_code', "'{$candidateRec['nationality']}'");
        $countryNameRecAddCountry  = $fn->getRecordRowByID('geo_country', 'country_code', "'{$candidateRec['address_country']}'");
        $countryNameRecAddCountry1 = $fn->getRecordRowByID('geo_country', 'country_code', "'{$candidateRec['address_country1']}'");

		$dob = $candidateRec['date_of_birth'];
        $DOB = $fn->getCPDate($dob, 'd-m-Y');
		$doe = $candidateRec['date_of_expiry'];
        $DOE = $fn->getCPDate($doe, 'd-m-Y');

        $countryNameRecEduCountry = $fn->getRecordRowByID('geo_country', 'country_code', "'{$candidateRec['education_country1']}'");

		$periodOfStudyFrom = $candidateRec['period_of_study_from1'];
        $POSF              = $fn->getCPDate($periodOfStudyFrom, 'd-m-Y');
		$periodOfStudyTo   = $candidateRec['period_of_study_to1'];
        $POST              = $fn->getCPDate($periodOfStudyTo, 'd-m-Y');

        $countryNameRecEmploymentCountry = $fn->getRecordRowByID('geo_country', 'country_code', "'{$candidateRec['employment_country1']}'");

		$empPeriodfrom = $candidateRec['employment_period_from1'];
        $empPeriodFrom = $fn->getCPDate($empPeriodfrom, 'd-m-Y');
		$empPeriodto   = $candidateRec['employment_period_to1'];
        $empPeriodTo   = $fn->getCPDate($empPeriodto, 'd-m-Y');

	    $valArr = array();
	    /* Staff Details */
        $valArr['candidate_name']                             = $candidateName;
        $valArr['candidate_sex']                              = $candidateRec['sex'];
        $valArr['candidate_marStat']                          = $candidateRec['martial_status'];
        $valArr['candidate_dob']                              = $DOB;
        $valArr['candidate_nation']                           = $countryNameRecNationality['name'];
        $valArr['candidate_addCountry']                       = $countryNameRecAddCountry['name'];
        $valArr['candidate_addCountry1']                      = $countryNameRecAddCountry1['name'];
        $valArr['candidate_race']                             = $candidateRec['race'];
        $valArr['candidate_religion']                         = $candidateRec['religion'];
        $valArr['candidate_travelDoc']                        = $candidateRec['travel_document_type'];
        $valArr['candidate_travelDocNo']                      = $candidateRec['travel_document_no'];
        $valArr['candidate_dateExpiry']                       = $DOE;
        $valArr['candidate_eduCountry']                       = $countryNameRecEduCountry['name'];
        $valArr['candidate_degreeName']                       = $candidateRec['degree_name1'];
        $valArr['candidate_collegeName']                      = $candidateRec['college_name1'];
        $valArr['candidate_qualification']                    = $candidateRec['education_qualification1'];
        $valArr['candidate_faculty']                          = $candidateRec['education_faculty1'];
        $valArr['candidate_specialisation']                   = $candidateRec['education_specialisation1'];
        $valArr['candidate_noneOfTheAbove']                   = $candidateRec['education_none_of_the_above1'];
        $valArr['candidate_modeOfStudy']                      = $candidateRec['mode_of_study1'];
        $valArr['candidate_periodOfStudyFrom']                = $POSF;
        $valArr['candidate_periodOfStudyto']                  = $POST;
        $valArr['candidate_periodWorkingYear']                = $candidateRec['period_of_working_year'];
        $valArr['candidate_periodWorkingMonth']               = $candidateRec['period_of_working_month'];
        $valArr['candidate_periodRelevantYear']               = $candidateRec['period_of_relevant_year'];
        $valArr['candidate_periodRelevantMonth']              = $candidateRec['period_of_relevant_month'];
        $valArr['candidate_employmentCompanyName']            = $candidateRec['employment_company_name1'];
        $valArr['candidate_employmentOccupation']             = $candidateRec['employment_occupation1'];
        $valArr['candidate_employmentCountry']                = $countryNameRecEmploymentCountry['name'];
        $valArr['candidate_employmentPeriodFrom']             = $empPeriodFrom;
        $valArr['candidate_employmentPeriodTo']               = $empPeriodTo;
        $valArr['candidate_employmentSalary']                 = $candidateRec['employment_salary1'];
        $valArr['candidate_employmentjobResponsibilites']     = $candidateRec['job_duties_responsibilities1'];
        $valArr['candidate_refusedDeport']   				  = $this->getYesorNo($candidateRec['refused_deport']);
        $valArr['candidate_convicted']   				  	  = $this->getYesorNo($candidateRec['convicted']);
        $valArr['candidate_prohibited']   				  	  = $this->getYesorNo($candidateRec['prohibited']);
        $valArr['candidate_diffCountryPassport']   			  = $this->getYesorNo($candidateRec['different_country_passport']);
        $valArr['candidate_differentName']   				  = $this->getYesorNo($candidateRec['different_name']);
        $valArr['candidate_singaporeCitizen']   		      = $this->getYesorNo($candidateRec['singapore_citizen']);
        $valArr['candidate_stayedIn']   				  	  = $this->getYesorNo($candidateRec['stayed_in']);
        $valArr['candidate_purpose']   				  		  = $candidateRec['purpose'];
        $valArr['candidate_visaAnotherCountry']   			  = $this->getYesorNo($candidateRec['visa_by_another_country']);
        $valArr['candidate_countryIssued']   				  = $candidateRec['country_of_issue'];
        $valArr['candidate_lengthOfVisa']   				  = $candidateRec['length_visa'];
        $valArr['candidate_mobileNo']   					  = $candidateRec['candidate_mobile_no'];
        $valArr['candidate_email']   				  		  = $candidateRec['email_address'];
        $valArr['candidate_residentAdd']   				      = $candidateRec['residential_address'];
        $valArr['candidate_homeNo']   				   		  = $candidateRec['home_no'];
        $valArr['candidate_fatherNo']   				  	  = $candidateRec['father_mother_no'];

	    $blkMain   = array();
	    $blkMain[] = $valArr;

	    $TBS->MergeBlock('blkMain', $blkMain);

	    $TBS->Show(OPENTBS_DOWNLOAD, $file_name);
    }

    /**
     *
     */
    function getPrintNoDueWord() {
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

	    $candidate_id = $fn->getReqParam('candidate_id');
	    $candidateRec  = $fn->getRecordRowByID('candidate', 'candidate_id', $candidate_id);


		$candidateName             = strtoupper($candidateRec['first_name'] . ' ' .$candidateRec['last_name']);

	    $template = 'Candidate_No_Due.docx';
	    $templatePath = $cpCfg['cp.localPath'].'lib/template/' . $template;
	    $TBS->LoadTemplate($templatePath);
	    $rnd_no = mt_rand();
	    $file_name = 'Westrama_NoDue_' . $candidateName . '_' . '.docx';
	    $file_name = str_replace('.', '_' . date('Y-m-d') . '.', $file_name);

	    $valArr = array();
	    /* Staff Details */
        $valArr['candidate_name']                             = $candidateName;

	    $blkMain   = array();
	    $blkMain[] = $valArr;

	    $TBS->MergeBlock('blkMain', $blkMain);

	    $TBS->Show(OPENTBS_DOWNLOAD, $file_name);
    }

    /**
     *
     */
    function getPrintDeclarationWord() {
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

	    $candidate_id = $fn->getReqParam('candidate_id');
	    $candidateRec  = $fn->getRecordRowByID('candidate', 'candidate_id', $candidate_id);


		$candidateName             = strtoupper($candidateRec['first_name'] . ' ' .$candidateRec['last_name']);

	    $template = 'Candidate_Declaration.docx';
	    $templatePath = $cpCfg['cp.localPath'].'lib/template/' . $template;
	    $TBS->LoadTemplate($templatePath);
	    $rnd_no = mt_rand();
	    $file_name = 'Westrama_Declaration_' . $candidateName . '.docx';
	    //$file_name = str_replace('.', '_' . date('Y-m-d') . '.', $file_name);

		$dob = $candidateRec['date_of_birth'];
        $DOB = $fn->getCPDate($dob, 'd-m-Y');
		$doe = $candidateRec['date_of_expiry'];
        $DOE = $fn->getCPDate($doe, 'd-m-Y');


	    $valArr = array();
	    /* Staff Details */
        $valArr['candidate_name']          = $candidateName;
        $valArr['candidate_dob']           = $DOB;
        $valArr['candidate_dateExpiry']    = $DOE;
        $valArr['candidate_travelDocNo']   = $candidateRec['travel_document_no'];

	    $blkMain   = array();
	    $blkMain[] = $valArr;

	    $TBS->MergeBlock('blkMain', $blkMain);

	    $TBS->Show(OPENTBS_DOWNLOAD, $file_name);
    }

    /**
     *
     */
    function getPrintCandidateResumeAsPdf() {
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $searchVar = Zend_Registry::get('searchVar');
        $media = Zend_Registry::get('media');
        $cpPaths = Zend_Registry::get('cpPaths');
        $dbUtil = Zend_Registry::get('dbUtil');

        ini_set('memory_limit', '512M');

        set_time_limit(50000);

        include_once(CP_LIBRARY_PATH.'lib_php/fpdf/fpdf.php');
        include_once(CP_LIBRARY_PATH.'lib_php/fpdf-extra/html_table1.php');

        $pdf = new MYPDF();

		$pdf->AddPage();
		$pdf->SetFont('Courier','',11);

        $candidate_id = $fn->getReqParam('candidate_id');

        $SQL = "
        SELECT c.*
              ,CONCAT_WS(' ', c.first_name, c.last_name ) AS candidate_name
        FROM candidate c
        WHERE c.candidate_id = {$candidate_id}
        ";

        $result = $db->sql_query($SQL);

        $numRows  = $db->sql_numrows($result);

        $today = date("Y-m-d");
        $dateMonth = $fn->getCPDate($today, 'm');
        $dateYear = $fn->getCPDate($today, 'Y');
		if ($numRows == 0){
            $pdf->SetXY(30,30);
            $pdf->Cell(50, 20, "Please set the values for your Order and print the PDF");
			$pdf->Output();
			return;
		}

        $count = 0;
        $total = 0;
        $rows = "";
        $lineItemNumber = 1;  // To increment the line item in receipt
        $staff_id  = $fn->getSessionParam('staff_id');
        $staffRec  = $fn->getRecordRowByID('staff', 'staff_id', $staff_id);

        $staffName        = $staffRec['first_name'] .' ' . $staffRec['last_name'];
        $staffDesignation = $staffRec['designation'];
        $staffPhone       = $staffRec['phone'];

        //============================================================================= //
        $pdf->SetFont('Courier','',11);
        while ($row = $db->sql_fetchrow($result)) {
            if ($count == 0){
                /* Logo of the institution */
                $pdf->Image('images/logo-print.png',150,10,30);

                $pdf->SetXY(10,5);
                $pdf->SetFont('Courier','B',11);
                $pdf->Cell(50, 25, $staffName);
                $pdf->Ln(5);

                $pdf->SetXY(10,12);
                $pdf->Cell(50, 20, $staffDesignation);
                $pdf->Ln(5);

                $mobile = 'Mobile: ' . $staffPhone;
                $pdf->SetXY(10,15);
                $pdf->Cell(50, 24, $mobile);
                $pdf->Ln(5);

                /* Header */
                /*$pdf->SetFont('Courier','BU',11);
                $pdf->SetXY(15, 35);
                $pdf->Cell(21, 30,$row['candidate_name'], 0, 0, 'C');
                $pdf->Ln(20);*/

                /* Header */
                $pdf->SetFont('Courier','BU',11);
                $pdf->SetXY(100, 35);
                $pdf->Cell(21, 10, "RESUME", 0, 0, 'C');
                $pdf->Ln(20);

                $pdf->SetFont('Courier','B',11);
                $pdf->SetFillColor(221,221,221);
				$pdf->SetTextColor(0,0,0);
                $pdf->Cell(190,8,"Step 1 - Foreign Employee's Personal Information",0,0, 'L', 1);
                $pdf->Ln();
                $pdf->Ln();
                $pdf->SetFillColor(0,0,0);
				$pdf->SetTextColor(0,0,255);
                $pdf->Cell(190,8,"Section A - Foreign Employee's Personal Particulars");
                $pdf->Ln();
                $pdf->SetFillColor(255,255,255);
				$pdf->SetTextColor(0,0,0);
                $pdf->Cell(55,8,"Candidate Name       :",0,0);
                $pdf->SetFillColor(221,221,221);
                $pdf->Cell(135,8,$row['candidate_name'],0,0);
                $pdf->Ln();
                $pdf->Ln();
   				$pdf->SetTextColor(0,0,0);
                $pdf->Cell(55,8,"Sex                  :",0,0);
                $pdf->Cell(135,8,$row['sex'],0,0);
                $pdf->Ln();
				$pdf->SetTextColor(0,0,0);
                $pdf->Cell(55,8,"Marital Status    	  :",0,0);
                $pdf->Cell(135,8,$row['martial_status'],0,0);
                $pdf->Ln();

                $date_of_birth = $fn->getCPDate($row['date_of_birth'], 'd-m-Y');
				$pdf->SetTextColor(0,0,0);
                $pdf->Cell(55,8,"Date Of Birth   	    :",0,0);
                $pdf->Cell(135,8,$date_of_birth,0,0);
                $pdf->Ln();

                $nationality = $this->getCountryName($row['nationality']);
				$pdf->SetTextColor(0,0,0);
                $pdf->Cell(55,8,"Nationality		        :",0,0);
                $pdf->Cell(135,8,$nationality,0,0);
                $pdf->Ln();

                $address_country = $this->getCountryName($row['address_country']);
                $pdf->Cell(55,8,"Country of Birth	    :",0,0);
                $pdf->Cell(135,8,$address_country,0,0);
                $pdf->Ln();

                $address_country1 = $this->getCountryName($row['address_country1']);
                $pdf->Cell(55,8,"Country of Origin    :",0,0);
                $pdf->Cell(135,8,$address_country1,0,0);
                $pdf->Ln();

                $pdf->Cell(55,8,"Race				             :",0,0);
                $pdf->Cell(135,8,$row['race'],0,0);
                $pdf->Ln();
                $pdf->Cell(55,8,"Religion			          :",0,0);
                $pdf->Cell(135,8,$row['religion'],0,0);
                $pdf->Ln();
                $pdf->Ln();

                $pdf->SetFont('Courier','B',11);
                $pdf->SetFillColor(0,0,0);
				$pdf->SetTextColor(0,0,255);
                $pdf->Cell(190,8,"Section B - Foreign Employee's Travel Document Information");
                $pdf->Ln();
				$pdf->SetTextColor(0,0,0);
                $pdf->Cell(55,8,"Travel Document Type	:",0,0);
                $pdf->Cell(135,8,$row['travel_document_type'],0,0);
                $pdf->Ln();
                $pdf->Cell(55,8,"Travel Document No	  :",0,0);
                $pdf->Cell(135,8,$row['travel_document_no'],0,0);
                $pdf->Ln();

                $date_of_expiry = $fn->getCPDate($row['date_of_expiry'], 'd-m-Y');
                $pdf->Cell(55,8,"Date of Expiry	      :",0,0);
                $pdf->Cell(135,8,$date_of_expiry,0,0);
                $pdf->Ln();
                $pdf->Ln();

                $pdf->SetFont('Courier','B',11);
                $pdf->SetFillColor(221,221,221);
				$pdf->SetTextColor(0,0,0);
                $pdf->Cell(190,8,"Step 2 - Foreign Employee's Education / Membership Details",0,0, 'L', 1);
                $pdf->Ln();
                $pdf->SetFont('Courier','B',11);
                $pdf->SetFillColor(0,0,0);
				$pdf->SetTextColor(0,0,255);
                $pdf->Cell(190,8,"Section A - Education Details");
                $pdf->Ln();

				$pdf->SetTextColor(0,0,0);
                $pdf->SetFont('Courier','B',11);
				$pdf->SetTextColor(0,0,0);
                $pdf->Cell(180,20,"Awarding Body / Institution / University");
                $pdf->Ln();

                $education_country1 = $this->getCountryName($row['education_country1']);
                $pdf->Cell(55,8,"Country      :");
                $pdf->Cell(135,8,$education_country1);
                $pdf->Ln();
                $pdf->Cell(55,8,"Name         :");
                $pdf->Cell(135,8,$row['degree_name1']);
                $pdf->Ln();
                $pdf->Cell(110,8,"Main Campus or Affiliating College Attended:");
                $pdf->Cell(135,8,$row['college_name1']);
                $pdf->Ln();
                $pdf->Cell(55,8,"Qualification :");
                $pdf->Cell(135,8,$row['education_qualification1']);
                $pdf->Ln();
                $pdf->Cell(55,8,"Faculty 			:");
                $pdf->Cell(135,8,$row['education_faculty1']);
                $pdf->Ln();
                $pdf->Cell(55,8,"Specialisation 	:");
                $pdf->Cell(135,8,$row['education_specialisation1']);
                $pdf->Ln();
                $pdf->Cell(110,8,"If None of the Above please specify        :");
                $pdf->Cell(135,8,$row['education_none_of_the_above1']);
                $pdf->Ln();
                $pdf->SetFillColor(255,255,255);
                $pdf->Cell(55,8,"Mode of Study 		:");
                $pdf->SetFillColor(221,221,221);
                $pdf->Cell(135,8,$row['mode_of_study1']);
                $pdf->Ln();

                $from_date = $fn->getCPDate($row['period_of_study_from1'], 'd-m-Y');
                $to_date   = $fn->getCPDate($row['period_of_study_to1'], 'd-m-Y');
                $period_of_study = 'From ' . $from_date . ' To ' . $to_date;
                $pdf->SetFillColor(255,255,255);
                $pdf->Cell(55,8,"Period of Study      	:");
                $pdf->SetFillColor(221,221,221);
                $pdf->Cell(135,8,$period_of_study);
                $pdf->Ln();
                $pdf->Ln();

				if ($row['education_country2'] != '' || $row['degree_name2'] != '' || $row['college_name2'] != '' || $row['education_qualification2'] != '' || $row['education_faculty2'] != '' || $row['education_specialisation2'] != '' || $row['education_none_of_the_above2'] != '' || $row['mode_of_study2'] != '' || $row['period_of_study_from2'] != '' || $row['period_of_study_to2'] != '') {
	                $education_country2 = $this->getCountryName($row['education_country2']);
	                $pdf->Cell(55,8,"Country      :");
	                $pdf->Cell(135,8,$education_country2);
	                $pdf->Ln();
	                $pdf->Cell(55,8,"Name         :");
	                $pdf->Cell(135,8,$row['degree_name2']);
	                $pdf->Ln();
	                $pdf->Cell(110,8,"Main Campus or Affiliating College Attended:");
	                $pdf->Cell(135,8,$row['college_name2']);
	                $pdf->Ln();
	                $pdf->Cell(55,8,"Qualification :");
	                $pdf->Cell(135,8,$row['education_qualification2']);
	                $pdf->Ln();
	                $pdf->Cell(55,8,"Faculty 			:");
	                $pdf->Cell(135,8,$row['education_faculty2']);
	                $pdf->Ln();
	                $pdf->Cell(55,8,"Specialisation 	:");
	                $pdf->Cell(135,8,$row['education_specialisation2']);
	                $pdf->Ln();
	                $pdf->Cell(110,8,"If None of the Above please specify        :");
	                $pdf->Cell(135,8,$row['education_none_of_the_above2']);
	                $pdf->Ln();
	                $pdf->SetFillColor(255,255,255);
	                $pdf->Cell(55,8,"Mode of Study 		:");
	                $pdf->SetFillColor(221,221,221);
	                $pdf->Cell(135,8,$row['mode_of_study2']);
	                $pdf->Ln();

	                $from_date = $fn->getCPDate($row['period_of_study_from2'], 'd-m-Y');
	                $to_date   = $fn->getCPDate($row['period_of_study_to2'], 'd-m-Y');
	                $period_of_study = 'From ' . $from_date . ' To ' . $to_date;
	                $pdf->SetFillColor(255,255,255);
	                $pdf->Cell(55,8,"Period of Study      	:");
	                $pdf->SetFillColor(221,221,221);
	                $pdf->Cell(135,8,$period_of_study);
	                $pdf->Ln();
	                $pdf->Ln();
				}

				if ($row['education_country3'] != '' || $row['degree_name3'] != '' || $row['college_name3'] != '' || $row['education_qualification3'] != '' || $row['education_faculty3'] != '' || $row['education_specialisation3'] != '' || $row['education_none_of_the_above3'] != '' || $row['mode_of_study3'] != '' || $row['period_of_study_from3'] != '' || $row['period_of_study_to3'] != '') {
	                $education_country3 = $this->getCountryName($row['education_country3']);
	                $pdf->Cell(55,8,"Country      :");
	                $pdf->Cell(135,8,$education_country3);
	                $pdf->Ln();
	                $pdf->Cell(55,8,"Name         :");
	                $pdf->Cell(135,8,$row['degree_name3']);
	                $pdf->Ln();
	                $pdf->Cell(110,8,"Main Campus or Affiliating College Attended:");
	                $pdf->Cell(135,8,$row['college_name3']);
	                $pdf->Ln();
	                $pdf->Cell(55,8,"Qualification :");
	                $pdf->Cell(135,8,$row['education_qualification3']);
	                $pdf->Ln();
	                $pdf->Cell(55,8,"Faculty 			:");
	                $pdf->Cell(135,8,$row['education_faculty3']);
	                $pdf->Ln();
	                $pdf->Cell(55,8,"Specialisation 	:");
	                $pdf->Cell(135,8,$row['education_specialisation3']);
	                $pdf->Ln();
	                $pdf->Cell(110,8,"If None of the Above please specify        :");
	                $pdf->Cell(135,8,$row['education_none_of_the_above3']);
	                $pdf->Ln();
	                $pdf->SetFillColor(255,255,255);
	                $pdf->Cell(55,8,"Mode of Study 		:");
	                $pdf->SetFillColor(221,221,221);
	                $pdf->Cell(135,8,$row['mode_of_study3']);
	                $pdf->Ln();

	                $from_date = $fn->getCPDate($row['period_of_study_from3'], 'd-m-Y');
	                $to_date   = $fn->getCPDate($row['period_of_study_to3'], 'd-m-Y');
	                $period_of_study = 'From ' . $from_date . ' To ' . $to_date;
	                $pdf->SetFillColor(255,255,255);
	                $pdf->Cell(55,8,"Period of Study      	:");
	                $pdf->SetFillColor(221,221,221);
	                $pdf->Cell(135,8,$period_of_study);
	                $pdf->Ln();
	                $pdf->Ln();
				}

				if ($row['education_country4'] != '' || $row['degree_name4'] != '' || $row['college_name4'] != '' || $row['education_qualification4'] != '' || $row['education_faculty4'] != '' || $row['education_specialisation4'] != '' || $row['education_none_of_the_above4'] != '' || $row['mode_of_study4'] != '' || $row['period_of_study_from4'] != '' || $row['period_of_study_to4'] != '') {
	                $education_country4 = $this->getCountryName($row['education_country4']);
	                $pdf->Cell(55,8,"Country      :");
	                $pdf->Cell(135,8,$education_country4);
	                $pdf->Ln();
	                $pdf->Cell(55,8,"Name         :");
	                $pdf->Cell(135,8,$row['degree_name4']);
	                $pdf->Ln();
	                $pdf->Cell(110,8,"Main Campus or Affiliating College Attended:");
	                $pdf->Cell(135,8,$row['college_name4']);
	                $pdf->Ln();
	                $pdf->Cell(55,8,"Qualification :");
	                $pdf->Cell(135,8,$row['education_qualification4']);
	                $pdf->Ln();
	                $pdf->Cell(55,8,"Faculty 			:");
	                $pdf->Cell(135,8,$row['education_faculty4']);
	                $pdf->Ln();
	                $pdf->Cell(55,8,"Specialisation 	:");
	                $pdf->Cell(135,8,$row['education_specialisation4']);
	                $pdf->Ln();
	                $pdf->Cell(110,8,"If None of the Above please specify        :");
	                $pdf->Cell(135,8,$row['education_none_of_the_above4']);
	                $pdf->Ln();
	                $pdf->SetFillColor(255,255,255);
	                $pdf->Cell(55,8,"Mode of Study 		:");
	                $pdf->SetFillColor(221,221,221);
	                $pdf->Cell(135,8,$row['mode_of_study4']);
	                $pdf->Ln();

	                $from_date = $fn->getCPDate($row['period_of_study_from4'], 'd-m-Y');
	                $to_date   = $fn->getCPDate($row['period_of_study_to4'], 'd-m-Y');
	                $period_of_study = 'From ' . $from_date . ' To ' . $to_date;
	                $pdf->SetFillColor(255,255,255);
	                $pdf->Cell(55,8,"Period of Study      	:");
	                $pdf->SetFillColor(221,221,221);
	                $pdf->Cell(135,8,$period_of_study);
	                $pdf->Ln();
	                $pdf->Ln();
				}

				if ($row['education_country5'] != '' || $row['degree_name5'] != '' || $row['college_name5'] != '' || $row['education_qualification5'] != '' || $row['education_faculty5'] != '' || $row['education_specialisation5'] != '' || $row['education_none_of_the_above5'] != '' || $row['mode_of_study5'] != '' || $row['period_of_study_from5'] != '' || $row['period_of_study_to5'] != '') {
	                $education_country5 = $this->getCountryName($row['education_country5']);
	                $pdf->Cell(55,8,"Country      :");
	                $pdf->Cell(135,8,$education_country5);
	                $pdf->Ln();
	                $pdf->Cell(55,8,"Name         :");
	                $pdf->Cell(135,8,$row['degree_name5']);
	                $pdf->Ln();
	                $pdf->Cell(110,8,"Main Campus or Affiliating College Attended:");
	                $pdf->Cell(135,8,$row['college_name5']);
	                $pdf->Ln();
	                $pdf->Cell(55,8,"Qualification :");
	                $pdf->Cell(135,8,$row['education_qualification5']);
	                $pdf->Ln();
	                $pdf->Cell(55,8,"Faculty 			:");
	                $pdf->Cell(135,8,$row['education_faculty5']);
	                $pdf->Ln();
	                $pdf->Cell(55,8,"Specialisation 	:");
	                $pdf->Cell(135,8,$row['education_specialisation5']);
	                $pdf->Ln();
	                $pdf->Cell(110,8,"If None of the Above please specify        :");
	                $pdf->Cell(135,8,$row['education_none_of_the_above5']);
	                $pdf->Ln();
	                $pdf->SetFillColor(255,255,255);
	                $pdf->Cell(55,8,"Mode of Study 		:");
	                $pdf->SetFillColor(221,221,221);
	                $pdf->Cell(135,8,$row['mode_of_study5']);
	                $pdf->Ln();

	                $from_date = $fn->getCPDate($row['period_of_study_from5'], 'd-m-Y');
	                $to_date   = $fn->getCPDate($row['period_of_study_to5'], 'd-m-Y');
	                $period_of_study = 'From ' . $from_date . ' To ' . $to_date;
	                $pdf->SetFillColor(255,255,255);
	                $pdf->Cell(55,8,"Period of Study      	:");
	                $pdf->SetFillColor(221,221,221);
	                $pdf->Cell(135,8,$period_of_study);
	                $pdf->Ln();
	                $pdf->Ln();
				}

                $pdf->SetFont('Courier','B',11);
                $pdf->SetFillColor(221,221,221);
				$pdf->SetTextColor(0,0,0);
                $pdf->Cell(190,8,"Step 3 - Foreign Employee's Employment Details",0,0, 'L', 1);
                $pdf->Ln();
                $pdf->SetFont('Courier','B',11);
                $pdf->SetFillColor(0,0,0);
				$pdf->SetTextColor(0,0,255);
                $pdf->Cell(190,8,"Section A - Working Experience of Foreign Employee");
                $pdf->Ln();

                $pdf->SetFont('Courier','B',11);
				$pdf->SetTextColor(0,0,0);
                $pdf->SetFillColor(255,255,255);
                $pdf->Cell(95,8,"Name Of Company",1,0, 'L', 0);
				$pdf->Cell(95, 8, $row['employment_company_name1'],1,0, 'TLR', 0, 'L', 0);
                $pdf->Ln();
                $pdf->Cell(95,8,"Occupation",1,0, 'L', 0);
				$pdf->Cell(95, 8, $row['employment_occupation1'],1,0, 'TLR', 0, 'L', 0);
                $pdf->Ln();

                $employment_country1 = $this->getCountryName($row['employment_country1']);
                $pdf->Cell(95,8,"Country",1,0, 'L', 0);
				$pdf->Cell(95, 8, $employment_country1,1,0, 'TLR', 0, 'L', 0);
                $pdf->Ln();

                $from_date = $fn->getCPDate($row['employment_period_from1'], 'd-m-Y');
                $to_date   = $fn->getCPDate($row['employment_period_to1'], 'd-m-Y');
                $employment_period = 'From ' . $from_date . ' To ' . $to_date;
                $pdf->Cell(95,8,"Period",1,0, 'L', 0);
				$pdf->Cell(95, 8, $employment_period,1,0, 'TLR', 0, 'L', 0);
                $pdf->Ln();
                $pdf->Cell(95,8,"Fixed Month Salary",1,0, 'L', 0);
				$pdf->Cell(95, 8, "S$ " . $row['employment_salary1'],1,0, 'TLR', 0, 'L', 0);
                $pdf->Ln();

                $pdf->SetFont('Courier','B',10);
                $pdf->Cell(190,8,"JOB DUTIES & RESPONSIBILITIES",1,2, 'L', 1);
                $pdf->Ln(3);
                $pdf->SetFillColor(255,255,255);
                $pdf->drawTextBox($row['job_duties_responsibilities1'], 194, 50, 'L', 'T', 0);
                $pdf->Ln(10);

				if ($row['employment_company_name2'] != '' || $row['employment_occupation2'] != '' || $row['employment_country2'] != '' || $row['employment_period_from2'] != '' || $row['employment_period_to2'] != '' || $row['employment_salary2'] != '' || $row['job_duties_responsibilities5'] != '') {
	                $pdf->SetFont('Courier','B',11);
					$pdf->SetTextColor(0,0,0);
	                $pdf->SetFillColor(255,255,255);
	                $pdf->Cell(95,8,"Name Of Company",1,0, 'L', 0);
					$pdf->Cell(95, 8, $row['employment_company_name2'],1,0, 'TLR', 0, 'L', 0);
	                $pdf->Ln();
	                $pdf->Cell(95,8,"Occupation",1,0, 'L', 0);
					$pdf->Cell(95, 8, $row['employment_occupation2'],1,0, 'TLR', 0, 'L', 0);
	                $pdf->Ln();

	                $employment_country2 = $this->getCountryName($row['employment_country2']);
	                $pdf->Cell(95,8,"Country",1,0, 'L', 0);
					$pdf->Cell(95, 8, $employment_country2,1,0, 'TLR', 0, 'L', 0);
	                $pdf->Ln();

	                $from_date = $fn->getCPDate($row['employment_period_from2'], 'd-m-Y');
	                $to_date   = $fn->getCPDate($row['employment_period_to2'], 'd-m-Y');
	                $employment_period = 'From ' . $from_date . ' To ' . $to_date;
	                $pdf->Cell(95,8,"Period",1,0, 'L', 0);
					$pdf->Cell(95, 8, $employment_period,1,0, 'TLR', 0, 'L', 0);
	                $pdf->Ln();
	                $pdf->Cell(95,8,"Fixed Month Salary",1,0, 'L', 0);
					$pdf->Cell(95, 8, "S$ " . $row['employment_salary2'],1,0, 'TLR', 0, 'L', 0);
	                $pdf->Ln();

                    $pdf->SetFont('Courier','B',10);
                    $pdf->Cell(190,8,"JOB DUTIES & RESPONSIBILITIES",1,2, 'L', 1);
                    $pdf->Ln(3);
                    $pdf->drawTextBox($row['job_duties_responsibilities2'], 194, 50, 'L', 'T', 0);
                    $pdf->Ln(10);
				}

				if ($row['employment_company_name3'] != '' || $row['employment_occupation3'] != '' || $row['employment_country3'] != '' || $row['employment_period_from3'] != '' || $row['employment_period_to3'] != '' || $row['employment_salary3'] != '' || $row['job_duties_responsibilities5'] != '') {
	                $pdf->SetFont('Courier','B',11);
					$pdf->SetTextColor(0,0,0);
	                $pdf->SetFillColor(255,255,255);
	                $pdf->Cell(95,8,"Name Of Company",1,0, 'L', 0);
					$pdf->Cell(95, 8, $row['employment_company_name3'],1,0, 'TLR', 0, 'L', 0);
	                $pdf->Ln();
	                $pdf->Cell(95,8,"Occupation",1,0, 'L', 0);
					$pdf->Cell(95, 8, $row['employment_occupation3'],1,0, 'TLR', 0, 'L', 0);
	                $pdf->Ln();

	                $employment_country3 = $this->getCountryName($row['employment_country3']);
	                $pdf->Cell(95,8,"Country",1,0, 'L', 0);
					$pdf->Cell(95, 8, $employment_country3,1,0, 'TLR', 0, 'L', 0);
	                $pdf->Ln();

	                $from_date = $fn->getCPDate($row['employment_period_from3'], 'd-m-Y');
	                $to_date   = $fn->getCPDate($row['employment_period_to3'], 'd-m-Y');
	                $employment_period = 'From ' . $from_date . ' To ' . $to_date;
	                $pdf->Cell(95,8,"Period",1,0, 'L', 0);
					$pdf->Cell(95, 8, $employment_period,1,0, 'TLR', 0, 'L', 0);
	                $pdf->Ln();
	                $pdf->Cell(95,8,"Fixed Month Salary",1,0, 'L', 0);
					$pdf->Cell(95, 8, "S$ " . $row['employment_salary3'],1,0, 'TLR', 0, 'L', 0);
	                $pdf->Ln();

                    $pdf->SetFont('Courier','B',10);
                    $pdf->Cell(190,8,"JOB DUTIES & RESPONSIBILITIES",1,2, 'L', 1);
                    $pdf->Ln(3);
                    $pdf->drawTextBox($row['job_duties_responsibilities3'], 194, 50, 'L', 'T', 0);
                    $pdf->Ln(10);
				}

				if ($row['employment_company_name4'] != '' || $row['employment_occupation4'] != '' || $row['employment_country4'] != '' || $row['employment_period_from4'] != '' || $row['employment_period_to4'] != '' || $row['employment_salary4'] != '' || $row['job_duties_responsibilities5'] != '') {
	                $pdf->SetFont('Courier','B',11);
					$pdf->SetTextColor(0,0,0);
	                $pdf->SetFillColor(255,255,255);
	                $pdf->Cell(95,8,"Name Of Company",1,0, 'L', 0);
					$pdf->Cell(95, 8, $row['employment_company_name4'],1,0, 'TLR', 0, 'L', 0);
	                $pdf->Ln();
	                $pdf->Cell(95,8,"Occupation",1,0, 'L', 0);
					$pdf->Cell(95, 8, $row['employment_occupation4'],1,0, 'TLR', 0, 'L', 0);
	                $pdf->Ln();

	                $employment_country4 = $this->getCountryName($row['employment_country4']);
	                $pdf->Cell(95,8,"Country",1,0, 'L', 0);
					$pdf->Cell(95, 8, $employment_country4,1,0, 'TLR', 0, 'L', 0);
	                $pdf->Ln();

	                $from_date = $fn->getCPDate($row['employment_period_from4'], 'd-m-Y');
	                $to_date   = $fn->getCPDate($row['employment_period_to4'], 'd-m-Y');
	                $employment_period = 'From ' . $from_date . ' To ' . $to_date;
	                $pdf->Cell(95,8,"Period",1,0, 'L', 0);
					$pdf->Cell(95, 8, $employment_period,1,0, 'TLR', 0, 'L', 0);
	                $pdf->Ln();
	                $pdf->Cell(95,8,"Fixed Month Salary",1,0, 'L', 0);
					$pdf->Cell(95, 8, "S$ " . $row['employment_salary4'],1,0, 'TLR', 0, 'L', 0);
	                $pdf->Ln();

                    $pdf->SetFont('Courier','B',10);
                    $pdf->Cell(190,8,"JOB DUTIES & RESPONSIBILITIES",1,2, 'L', 1);
                    $pdf->Ln(3);
                    $pdf->drawTextBox($row['job_duties_responsibilities4'], 194, 50, 'L', 'T', 0);
                    $pdf->Ln(10);
				}

				if ($row['employment_company_name5'] != '' || $row['employment_occupation5'] != '' || $row['employment_country5'] != '' || $row['employment_period_from5'] != '' || $row['employment_period_to5'] != '' || $row['employment_salary5'] != '' || $row['job_duties_responsibilities5'] != '') {
	                $pdf->SetFont('Courier','B',11);
					$pdf->SetTextColor(0,0,0);
	                $pdf->SetFillColor(255,255,255);
	                $pdf->Cell(95,8,"Name Of Company",1,0, 'L', 0);
					$pdf->Cell(95, 8, $row['employment_company_name5'],1,0, 'TLR', 0, 'L', 0);
	                $pdf->Ln();
	                $pdf->Cell(95,8,"Occupation",1,0, 'L', 0);
					$pdf->Cell(95, 8, $row['employment_occupation5'],1,0, 'TLR', 0, 'L', 0);
	                $pdf->Ln();

	                $employment_country5 = $this->getCountryName($row['employment_country5']);
	                $pdf->Cell(95,8,"Country",1,0, 'L', 0);
					$pdf->Cell(95, 8, $employment_country5,1,0, 'TLR', 0, 'L', 0);
	                $pdf->Ln();

	                $from_date = $fn->getCPDate($row['employment_period_from5'], 'd-m-Y');
	                $to_date   = $fn->getCPDate($row['employment_period_to5'], 'd-m-Y');
	                $employment_period = 'From ' . $from_date . ' To ' . $to_date;
	                $pdf->Cell(95,8,"Period",1,0, 'L', 0);
					$pdf->Cell(95, 8, $employment_period,1,0, 'TLR', 0, 'L', 0);
	                $pdf->Ln();
	                $pdf->Cell(95,8,"Fixed Month Salary",1,0, 'L', 0);
					$pdf->Cell(95, 8, "S$ " . $row['employment_salary5'],1,0, 'TLR', 0, 'L', 0);
	                $pdf->Ln();

                    $pdf->SetFont('Courier','B',10);
                    $pdf->Cell(190,8,"JOB DUTIES & RESPONSIBILITIES",1,2, 'L', 1);
                    $pdf->Ln(3);
                    $pdf->drawTextBox($row['job_duties_responsibilities5'], 193, 50, 'L', 'T', 0);
                    $pdf->Ln(10);
				}

                $pdf->SetFont('Courier','B',11);
                $pdf->SetFillColor(221,221,221);
				$pdf->SetTextColor(0,0,0);
                $pdf->Cell(190,8,"Step 4 - Declaration by Foreign Employee ",0,0, 'L', 1);
                $pdf->Ln();
                $pdf->Ln();

                $pdf->SetFillColor(0,0,0);
   				$pdf->SetTextColor(0,0,0);
                $pdf->SetFont('Courier','B',8);
                $pdf->Cell(165,8,"*	a) Has the Foreigner ever been refused entry into or deported from any country ?       		   :");
                $pdf->Cell(135,8,$this->getYesorNo($row['refused_deport']),0,0);
                $pdf->Ln();
                $pdf->SetFont('Courier','B',8);
                $pdf->Cell(165,8,"*	b)Has the Foreigner ever been convicted in a court of  law  in any country ?           		   :",0,0);
                $pdf->Cell(150,8,$this->getYesorNo($row['convicted']),0,0);
                $pdf->Ln();
                $pdf->SetFont('Courier','B',8);
                $pdf->Cell(165,8,"*	c)Has the Foreigner ever been prohibited from entering Singapore ?                     		   :",0,0);
                $pdf->Cell(150,8,$this->getYesorNo($row['prohibited']),0,0);
                $pdf->Ln();
                $pdf->SetFont('Courier','B',8);
                $pdf->Cell(165,8,"*	d)Has the Foreigner ever entered Singapore using a passport issued by a different country ? :",0,0);
                $pdf->Cell(135,8,$this->getYesorNo($row['different_country_passport']),0,0);
                $pdf->Ln();
                $pdf->SetFont('Courier','B',8);
                $pdf->Cell(165,8,"*	e)Has the Foreigner ever entered Singapore using a different name ?       						            :",0,0);
                $pdf->Cell(80,8,$this->getYesorNo($row['different_name']),0,0);
                $pdf->Ln();
                $pdf->SetFont('Courier','B',8);
                $pdf->Cell(165,8,"*	f)Has the Foreigner ever been a Singapore Citizen or Singapore Permanent Resident ?         :",0,0);
                $pdf->Cell(80,8,$this->getYesorNo($row['singapore_citizen']),0,0);
                $pdf->Ln();
                $pdf->SetFont('Courier','B',8);
                $pdf->SetFillColor(255,255,255);
                $pdf->Cell(165,8,"*	g)Has the Foreigner ever stayed  in Singapore ? If Yes, please select the                   :");
                $pdf->Cell(80,8,$this->getYesorNo($row['stayed_in']),0,0);
                $pdf->Ln();
                $pdf->Cell(80,8,"	  purpose(s) of  stay below ");
                $pdf->Ln();
                $pdf->SetFont('Courier','B',8);
                $pdf->SetFillColor(255,255,255);
                $pdf->Cell(165,8,"*	h)Has the Foreigner ever been issued a work visa by another country(s)                      :");
                $pdf->Cell(80,8,$this->getYesorNo($row['visa_by_another_country']));
                $pdf->Ln();
                $pdf->Cell(80,8,"	  ? If Yes, please provide the most recent details below");
                $pdf->Ln();

				if ($row['visa_by_another_country'] == 1){
	                $pdf->SetFont('Courier','B',8);
	                $pdf->Cell(80,8,"  i) Country of Issue                :",0,0);
	                $pdf->Cell(80,8,$row['country_of_issue'],0,0);
	                $pdf->Ln();
	                $pdf->SetFont('Courier','B',8);
	                $pdf->Cell(80,8,"  ii) Length of Visa                 :",0,0);
	                $pdf->Cell(80,8,$row['length_visa'],0,0);
	                $pdf->Ln();
				}


                /*
                $pdf->SetFont('Courier','B',11);
                $pdf->SetFillColor(255,255,255);
				$pdf->SetTextColor(0,0,0);
                $pdf->Cell(80,8,"Candidate Mobile Numbers	  :",0,0);
                $pdf->SetFillColor(221,221,221);
                $pdf->Cell(80,8,$row['candidate_mobile_no'],0,0);
                $pdf->Ln();
   				$pdf->SetTextColor(0,0,0);
                $pdf->Cell(80,8,"E-mail Address             :",0,0);
                $pdf->Cell(80,8,$row['email_address'],0,0);
                $pdf->Ln();
				$pdf->SetTextColor(0,0,0);
                $pdf->Cell(80,8,"Residential Address        :",0,0);
                $pdf->Cell(80,8,$row['residential_address'],0,0);
                $pdf->Ln();
				$pdf->SetTextColor(0,0,0);
                $pdf->Cell(80,8,"Home Phone Number  	    		 :",0,0);
                $pdf->Cell(80,8,$row['home_no'],0,0);
                $pdf->Ln();

                $pdf->Cell(90,8,"Father / Mother / Wife Mobile Number: ",0,0);
                $pdf->Cell(80,8,$row['father_mother_no'],0,0);
                $pdf->Ln();
                */




	  }
    }


        $file_name = 'Refund_REF_' . date('Y-m-d') .'.pdf';
        $outputPath = realpath($cpCfg['cp.mediaFolder']) . '/temp';

        $outputFileName = $outputPath . '/' . $file_name;
		$pdf->Output();
    }

	function getYesorNo($value){

		if($value == 0){
			return $text = 'NO';
		} else {
			return $text = 'YES';
		}
	}

    /**
     *
     */
    function getPrintNoDuePdf() {
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $searchVar = Zend_Registry::get('searchVar');
        $media = Zend_Registry::get('media');
        $cpPaths = Zend_Registry::get('cpPaths');
        $dbUtil = Zend_Registry::get('dbUtil');

        ini_set('memory_limit', '512M');

        set_time_limit(50000);

        include_once(CP_LIBRARY_PATH.'lib_php/fpdf/fpdf.php');
        include_once(CP_LIBRARY_PATH.'lib_php/fpdf-extra/html2pdf.php');
        include_once(CP_LIBRARY_PATH.'lib_php/fpdf-extra/html_table1.php');

        $pdf = new MYPDF();
        $pdf = new PDF_HTML();

		$pdf->AddPage();
		$pdf->SetFont('Courier','',11);

        $candidate_id = $fn->getReqParam('candidate_id');

        $SQL = "
        SELECT c.*
              ,CONCAT_WS(' ', c.first_name, c.last_name ) AS candidate_name
        FROM candidate c
        WHERE c.candidate_id = {$candidate_id}
        ";
        $result = $db->sql_query($SQL);
        $numRows  = $db->sql_numrows($result);

		if ($numRows == 0){
            $pdf->SetXY(30,30);
            $pdf->Cell(50, 20, "Please set the values for your Order and print the PDF");
			$pdf->Output();
			return;
		}

        $count = 0;
        $total = 0;
        $rows = "";
        $lineItemNumber = 1;  // To increment the line item in receipt

        //============================================================================= //
        $pdf->SetFont('Courier','',11);
        while ($row = $db->sql_fetchrow($result)) {
            if ($count == 0){

                $pdf->SetFillColor(255,255,255);
				$pdf->SetTextColor(0,0,0);
                $pdf->Cell(55,8,"FROM:",0,0);
                $pdf->SetFillColor(221,221,221);
                $pdf->Ln();
                $pdf->Cell(55,8,$row['candidate_name'],0,0);
                $pdf->Ln(20);
   				$pdf->SetTextColor(0,0,0);

                $pdf->Cell(55,8,"TO:",0,0);
                $pdf->Ln();
                $pdf->Cell(55, 8, $cpCfg['cp.companyName']);
                $pdf->Ln();
                $pdf->Cell(55, 8, $cpCfg['cp.companyAddress1']);
                $pdf->Ln();
                $pdf->Cell(55, 8, $cpCfg['cp.companyAddress2']);
                $pdf->Ln();
                $pdf->Cell(55, 8, $cpCfg['cp.companyAddress3']);
                $pdf->Ln(20);
				$pdf->SetTextColor(0,0,0);
                $pdf->Cell(55,8, $cpCfg['cp.noDuesubject']);
                $pdf->Ln(15);

				$pdf->SetTextColor(0,0,0);
                //$pdf->Cell(55,8, $cpCfg['cp.noDueContent']);
                //$pdf->WordWrap($cpCfg['cp.noDueContent'],900);
                //$pdf->Write(5,$cpCfg['cp.noDueContent']);
                //$pdf->drawTextBox($cpCfg['cp.noDueContent'], 190, 300, 'L', 'T', 0);
                $text='';
                $text=stripslashes($cpCfg['cp.noDueContent']);
                $text = str_replace("[Name]", $row['candidate_name'], $text);
                $text = str_replace("[FIN]", $row['travel_document_no'], $text);
                $pdf->WriteHTML($text);
                $pdf->Ln(30);

				$pdf->SetTextColor(0,0,0);
                $pdf->Cell(55,8,"Yours faithfully",0,0);
                $pdf->Ln();
                $pdf->Cell(55,8,$row['candidate_name'],0,0);
                $pdf->Ln();
                $pdf->Cell(55,8,"FIN : " . $row['travel_document_no'],0,0);
                $pdf->Ln();

	        }
        }

        $file_name = 'No_Due_' . date('Y-m-d') .'.pdf';
        $outputPath = realpath($cpCfg['cp.mediaFolder']) . '/temp';

        $outputFileName = $outputPath . '/' . $file_name;
		$pdf->Output();
 }

    /**
     *
     */
    function getPrintDeclarationPdf() {
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $searchVar = Zend_Registry::get('searchVar');
        $media = Zend_Registry::get('media');
        $cpPaths = Zend_Registry::get('cpPaths');
        $dbUtil = Zend_Registry::get('dbUtil');
        $dateUtil = Zend_Registry::get('dateUtil');

        ini_set('memory_limit', '512M');

        set_time_limit(50000);

        include_once(CP_LIBRARY_PATH.'lib_php/fpdf/fpdf.php');
        include_once(CP_LIBRARY_PATH.'lib_php/fpdf-extra/html2pdf.php');
        include_once(CP_LIBRARY_PATH.'lib_php/fpdf-extra/html_table1.php');

        $pdf = new MYPDF();
        $pdf = new PDF_HTML();

		$pdf->AddPage();
		$pdf->SetFont('Courier','',11);

        $candidate_id = $fn->getReqParam('candidate_id');

        $SQL = "
        SELECT c.*
              ,CONCAT_WS(' ', c.first_name, c.last_name ) AS candidate_name
        FROM candidate c
        WHERE c.candidate_id = {$candidate_id}
        ";
        $result = $db->sql_query($SQL);
        $numRows  = $db->sql_numrows($result);

		if ($numRows == 0){
            $pdf->SetXY(30,30);
            $pdf->Cell(50, 20, "Please set the values for your Order and print the PDF");
			$pdf->Output();
			return;
		}

        $count = 0;
        $total = 0;
        $rows = "";
        $lineItemNumber = 1;  // To increment the line item in receipt

        //============================================================================= //
        $pdf->SetFont('Courier','',11);
        while ($row = $db->sql_fetchrow($result)) {
            if ($count == 0){

				$pdf->SetTextColor(0,0,0);
                $pdf->SetFont('Courier','B',11);
                $pdf->Cell(50,8,"TO",0,0);
                $pdf->SetFillColor(221,221,221);
                $pdf->Ln();

				$pdf->SetTextColor(0,0,0);
                $pdf->SetFont('Courier','B',11);
                $pdf->Cell(50, 8, $cpCfg['cp.companyName']);
                $pdf->Ln();

				$pdf->SetTextColor(0,0,0);
                $pdf->SetFont('Courier','B',11);
                $pdf->Cell(50, 8, $cpCfg['cp.companyAddress1']);
                $pdf->Ln();

				$pdf->SetTextColor(0,0,0);
                $pdf->SetFont('Courier','B',11);
                $pdf->Cell(50, 8, $cpCfg['cp.companyAddress2']);
                $pdf->Ln();

				$pdf->SetTextColor(0,0,0);
                $pdf->SetFont('Courier','B',11);
                $pdf->Cell(50, 8, $cpCfg['cp.companyAddress3']);
                $pdf->Ln();
                $pdf->Ln();

				$pdf->SetTextColor(0,0,0);
                $pdf->SetFont('Courier','B',11);
                $pdf->Cell(50,8,"Dear Sir,",0,0);
                $pdf->Ln();

				$pdf->SetTextColor(0,0,0);
                $pdf->SetFont('Courier','BU',11);
                $pdf->Cell(200,8, $cpCfg['cp.declarationSubject'], 0, 0,'C');
                $pdf->Ln();

				$pdf->SetTextColor(0,0,0);
                $pdf->SetFont('Courier','BU',11);
                $pdf->Cell(180, 10, "DECLARATION FORM", 0, 0, 'C');
                $pdf->Ln();

				$pdf->SetTextColor(0,0,0);
                $pdf->SetFont('Courier','B',11);
                $pdf->Cell(70,8,"Name :",0,0);
                $pdf->Cell(135,8,$row['candidate_name'],0,0);
                $pdf->SetFillColor(221,221,221);
                $pdf->Ln();

				$pdf->SetTextColor(0,0,0);
                $pdf->Cell(70,8,"Date Of Birth :",0,0);
                $pdf->Cell(135,8, $dateUtil->formatDate($row['date_of_birth'], 'DD-MM-YYYY'),0,0);
                $pdf->Ln();

				$pdf->SetTextColor(0,0,0);
                $pdf->Cell(70,8,"Passport No :",0,0);
                $pdf->Cell(135,8,$row['travel_document_no'],0,0);
                $pdf->Ln();

				$pdf->SetTextColor(0,0,0);
                $pdf->Cell(70,8,"Date of Expiry :",0,0);
                $pdf->Cell(135,8, $dateUtil->formatDate($row['date_of_expiry'], 'DD-MM-YYYY'),0,0);
                $pdf->Ln();

				/*$pdf->SetTextColor(0,0,0);
                $pdf->Cell(70,8,"FIN :",0,0);
                $pdf->Cell(135,8, $row['travel_document_no'],0,0);
                $pdf->Ln();*/

   				$pdf->SetTextColor(0,0,0);
                $pdf->Cell(70,8,"Degree(1) :",0,0);
                $pdf->Cell(135,8,$row['education_qualification1'],0,0);
                $pdf->Ln();

   				$pdf->SetTextColor(0,0,0);
                $pdf->Cell(70,8,"College :",0,0);
                $pdf->Cell(135,8,$row['college_name1'],0,0);
                $pdf->Ln();

   				$pdf->SetTextColor(0,0,0);
                $pdf->Cell(70,8,"University :",0,0);
                $pdf->Cell(135,8,$row['degree_name1'],0,0);
                $pdf->Ln();

                $pdf->Cell(70,8,"Year of Passing :",0,0);
                $pdf->Cell(135,8,$dateUtil->formatDate($row['period_of_study_to1'], 'DD-MM-YYYY'),0,0);
                $pdf->Ln();

   				if($row['education_qualification2'] != '' || $row['college_name2'] != ''
   				   || $row['degree_name2'] != '' || $row['period_of_study_to2'] != ''){
       				$pdf->SetTextColor(0,0,0);
                    $pdf->Cell(70,8,"Degree(2):",0,0);
                    $pdf->Cell(135,8,$row['education_qualification2'],0,0);
                    $pdf->Ln();

       				$pdf->SetTextColor(0,0,0);
                    $pdf->Cell(70,8,"College :",0,0);
                    $pdf->Cell(135,8,$row['college_name2'],0,0);
                    $pdf->Ln();

       				$pdf->SetTextColor(0,0,0);
                    $pdf->Cell(70,8,"University :",0,0);
                    $pdf->Cell(135,8,$row['degree_name2'],0,0);
                    $pdf->Ln();

       				//$pdf->SetTextColor(0,0,0);
                    $pdf->Cell(70,8,"Year of Passing :",0,0);
                    $pdf->Cell(135,8,$dateUtil->formatDate($row['period_of_study_to2'], 'DD-MM-YYYY'),0,0);
                    $pdf->Ln();
                }
                    $pdf->Ln();

				$pdf->SetTextColor(0,0,0);
                //$pdf->Cell(70,8, $cpCfg['cp.declarationContent']);
                $pdf->WriteHTML($cpCfg['cp.declarationContent']);
                $pdf->Ln(20);

				$pdf->SetTextColor(0,0,0);
                $pdf->Cell(50,8,"Yours truly",0,0);
                $pdf->Cell(200,8,"Witness:",0,0, 'C');
                $pdf->Ln();
                $pdf->Cell(55,8,$row['candidate_name'],0,0);
                $pdf->Ln();

	        }
        }

        $file_name = 'Declaration_' . date('Y-m-d') .'.pdf';
        $outputPath = realpath($cpCfg['cp.mediaFolder']) . '/temp';

        $outputFileName = $outputPath . '/' . $file_name;
		$pdf->Output();
    }

    /**
     *
     */
    function getCountryName($country_code) {
        $fn = Zend_Registry::get('fn');

        $whereCondition = "country_code = '" . $country_code . "'";

        $row_country = $fn->getRecordByCondition('geo_country', $whereCondition);

        return $row_country['name'];

    }

    /**
     *
     */
    function getAddCommentFormSubmit() {
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        $dbUtil = Zend_Registry::get('dbUtil');
        $db = Zend_Registry::get('db');
        $ln = Zend_Registry::get('ln');
        $cpCfg = Zend_Registry::get('cpCfg');
        $media = Zend_Registry::get('media');
        $cpUrl = Zend_Registry::get('cpUrl');

        if (!$this->getAddCommentFormValidate()){
            return $validate->getErrorMessageXML();
        }

        //-------------------------------------------------------------------------------------//
        $candidate_id       = $fn->getReqParam('candidate_id');
        $agent_id           = $fn->getReqParam('agent_id');
        $comments           = $fn->getPostParam('comments');
        $opportunity_code   = $fn->getReqParam('opportunity_code');

        $fa = array();
        $fa['comments']       = $comments;
        $fa['room_name']      = 'manPower_candidate';
        $fa['contact_id']     = $_SESSION['staff_id'];
        $fa['site_id']        = $_SESSION['cp_site_id'];
        $fa['comment_date']   = date("Y-m-d H:i:s");
        $fa['creation_date']  = date("Y-m-d H:i:s");
        $fa['record_id']      = $candidate_id;
        $fa['user_group_type']= $_SESSION['userGroupType'];

        $insertSQL   = $dbUtil->getInsertSQLStringFromArray($fa, 'comment');
        $resultSQL   = $db->sql_query($insertSQL);
        $comment_id  = $db->sql_nextid();

        //------------------- SEND EMAIL ----------------------------------------------//

        $rowAgent = $fn->getRecordByCondition('agent', "agent_id = '{$agent_id}'");

        $toAgentEmail = '';
        $toAgentName = '';
        $name = '';
        //if you have logged in as Staff then you should send mail to agent
        if ($_SESSION['userGroupType'] != 'Agent'){
            $toAgentName    = $rowAgent['first_name'] . ' ' . $rowAgent['last_name'];
            $toAgentEmail   = $rowAgent['email'];
            $name           = 'Admin';
        //if you have logged in as Staff then you should send mail to staff
        } else if ($_SESSION['userGroupType'] == 'Agent'){

            $sqlStaff = "
            SELECT s.* FROM staff s
            LEFT JOIN (opportunity o) ON (s.staff_id = o.staff_id)
            WHERE o.opportunity_code = '{$opportunity_code}'
            ";
            $resultStaff  = $db->sql_query($sqlStaff);
            $rowStaff     = $db->sql_fetchrow($resultStaff);

            $toAgentName    = $rowStaff['first_name'] . ' ' . $rowStaff['last_name'];
            $toAgentEmail   = $rowStaff['email'];
            $name           = 'Agent';
        }

        $mailComments = nl2br($fa['comments']);
		$urlLink = "{$cpCfg['cp.siteUrl']}admin/index.php?_topRm=opportunity&module=manPower_candidate&_action=edit&candidate_id={$candidate_id}";

        $emailDraft    = $fn->getRecordByCondition('setting', "key_text = 'cp.emailDraftCandidateComment'");
        $message = $emailDraft['value'];
        $message = str_replace("[user]", $toAgentName, $message );
        $message = str_replace("[opp_code]", $opportunity_code, $message );
        $message = str_replace("[remarks]", $comments, $message );
        $message = str_replace("[link]", $urlLink, $message );
        $message = nl2br($message);

        $subject   = $opportunity_code . ' : New Comment from ' . $name;
        //$fromName  = $_SESSION['userFullName'];
        //$fromEmail = $_SESSION['email'];
        $fromName  = 'Westrama Management Company :' . $name;
        $fromEmail = 'autonotification@westrama.com';
        $toName    = $toAgentName;
        $toEmail   = $toAgentEmail;
        //$toEmail = 'syed@usoftsolutions.com';

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
    function getAddCommentFormValidate() {
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
     */
    function getSendMessageToStaffByAgentSubmit(){
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $ln = Zend_Registry::get('ln');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpCfg = Zend_Registry::get('cpCfg');
        $validate = Zend_Registry::get('validate');

        $opportunity_code = $fn->getReqParam('opportunity_code');
        $candidate_id     = $fn->getReqParam('candidate_id');

        if (!$this->getOppCandidateValidate()){
            return $validate->getErrorMessageXML();
        }

        $oppRec     = $fn->getRecordByCondition('opportunity', "opportunity_code = '{$opportunity_code}'");
        $staffRec   = $fn->getRecordRowByID('staff', 'staff_id', $_SESSION['staff_id']);

        $fa = array();
        $fa['opportunity_id']   = $oppRec['opportunity_id'];
        $fa['candidate_id']     = $candidate_id;
        $fa['process_status']  = "Agent linked Candidate";
        $fa['site_id']          = $oppRec['site_id'];
        $fa['agent_id']         = $staffRec['agent_id'];
        $fa                     = $fn->addCreationDetailsToFieldsArray($fa, 'opportunity_candidate');

        $SQL = $dbUtil->getInsertSQLStringFromArray($fa, 'opportunity_candidate');
        $db->sql_query($SQL);
        $opportunity_candidate_id = $db->sql_nextid();

        $sqlStaff = "
        SELECT s.email
             ,CONCAT_WS(' ', s.first_name, s.last_name ) AS staff_name
        FROM staff s
        LEFT JOIN (opportunity o) ON (s.staff_id = o.staff_id)
        WHERE o.opportunity_code = '{$opportunity_code}'
        ";
        $resultStaff = $db->sql_query($sqlStaff);
        $numRows = $db->sql_numrows($resultStaff);
        $rowStaff    = $db->sql_fetchrow($resultStaff);

        $canRec = $fn->getRecordRowById('candidate', 'candidate_id', $candidate_id);

        $sqlOpp = "
        SELECT *
        FROM opportunity
        WHERE opportunity_code = '{$opportunity_code}'
        ";
        $resultOpp = $db->sql_query($sqlOpp);
        $oppRec    = $db->sql_fetchrow($resultOpp);

        if($numRows){
            $toName  = $rowStaff['staff_name'];
            $toEmail = $rowStaff['email'];
        }
        else{
            $toName  = 'Anitha';
            $toEmail = 'anitha@westrama.com';
        }
        //$toEmail = 'syed@usoftsolutions.com';

        $fromName  = 'Westrama Agent';
        $fromEmail = 'autonotification@westrama.com';
        $replyTo   = 'autonotification@westrama.com';

        $subject = $ln->gd('m.manPower.agent.form.confirmToStaff.email.notifySubject');

        $subjectText = $oppRec['opportunity_code'] . " : Candidate Added for Opportunity - " . $oppRec['position'];

        $opportunintyLink = $cpCfg['cp.siteUrl'] . "admin/index.php?_topRm=opportunity&module=manPower_candidate&_action=edit&candidate_id={$candidate_id}";

        $message = $cpCfg['cp.emailDraftToSatffByAgent'];
        $message = str_replace("[staff_name]"     , $toName               , $message);
        $message = str_replace("[opp_position]"   , $oppRec["position"]   , $message);
        $message = str_replace("[agent_name]"     , $fromName             , $message);
        $message = str_replace("[siteUrl]"        , $opportunintyLink  , $message);
        $message = str_replace("[opportunity_code]" , $oppRec['opportunity_code']  , $message);
        $message = str_replace("[title]"          , $oppRec['title']  , $message);
        $message = nl2br($message);

        $args = array(
             'toName'    => $toName
            ,'toEmail'   => $toEmail
            ,'subject'   => $subjectText
            ,'message'   => $message
            ,'fromName'  => $fromName
            ,'fromEmail' => $fromEmail
        );

        $emailMsg= includeCPClass('Lib', 'EmailTemplate', 'EmailTemplate', true, array('args' => $args));
        if($toEmail){
            $emailMsg->sendEmail();
        }

        return $validate->getSuccessMessageXML();
    }

    /**
     *
     */
    function getOppCandidateValidate() {
        $fn       = Zend_Registry::get('fn');
        $db       = Zend_Registry::get('db');
        $validate = Zend_Registry::get('validate');

        $opportunity_code = $fn->getReqParam('opportunity_code');
        $candidate_id     = $fn->getReqParam('candidate_id');

        $oppRec     = $fn->getRecordByCondition('opportunity', "opportunity_code = '{$opportunity_code}'");
        $staffRec   = $fn->getRecordRowByID('staff', 'staff_id', $_SESSION['staff_id']);

        $validate->resetErrorArray();
        $validate->validateData('opportunity_code', 'Please enter opportunity code');

        if ($opportunity_code) {
            $sqlOppCandidate = "
            SELECT opportunity_candidate_id FROM opportunity_candidate
            WHERE opportunity_id = {$oppRec['opportunity_id']}
              AND candidate_id   = {$candidate_id}
              AND site_id        = {$oppRec['site_id']}
            ";
            $resultOppCandidate  = $db->sql_query($sqlOppCandidate);
            $numRowsOppCandidate = $db->sql_numrows($resultOppCandidate);

            if ($numRowsOppCandidate){
                $msg = "Candidate is already linked to Opportunity";
                $validate->validateData('error_box', $msg);
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

    /**
     *
     */
    function getPrintPdfByDropDown() {
        $fn = Zend_Registry::get('fn');
        $cpUrl = Zend_Registry::get('cpUrl');
        $cpUtil = Zend_Registry::get('cpUtil');

        //-------------------------------------------------------------------------------------//
        $actBtn = $fn->getReqParam('actBtn');
        $candidate_id = $fn->getReqParam('candidate_id');

        if ($actBtn =='candidateResumePdf'){
            $cpUtil->redirect("index.php?module=manPower_candidate&_spAction=printCandidateResumeAsPdf&candidate_id={$candidate_id}&showHTML=0");
        }

    }
    
}