<?
class CP_Admin_Modules_AgileIms_Contact_Model extends CP_Common_Modules_AgileIms_Contact_Model
{
    /**
     * Used for both Contact new and Company#contact new
     */
    function getNewValidate() {
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');
        $ln = Zend_Registry::get('ln');
        $db = Zend_Registry::get('db');

        $validate->resetErrorArray();

        $validate->validateData('first_name', 'Please enter the name');
        $validate->validateData('id_card_no' , 'Please enter NRIC / Passport No.');
        
        $id_card_no = $fn->getPostParam('id_card_no', '', true);
        
        if ($id_card_no != ''){
            $rec = $fn->getRecordByCondition('contact', "id_card_no = '{$id_card_no}'");
            $expIdCard = array('displayText' => 'Go to this record', 'target' => '_blank');
            $IdCardlink = $fn->getRecordDetailLink('agileIms_contact', 'record_id', $rec['contact_id'], $expIdCard);
    
            if (is_array($rec)){
                $validate->errorArray['id_card_no']['name'] = "id_card_no";
                $validate->errorArray['id_card_no']['msg']  = "NRIC / Passport No. already exists. '{$IdCardlink}'";
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
    function getContactAddValidate() {
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');
        $ln = Zend_Registry::get('ln');
        $db = Zend_Registry::get('db');

        $validate->resetErrorArray();

        $validate->validateData('first_name', 'Please enter the name');
        $validate->validateData('id_card_no' , 'Please enter NRIC / Passport No.');
        $validate->validateData('nationality' , 'Please select nationality');
        $validate->validateData('date_of_birth' , 'Please enter date of birth');
        
        $id_card_no = $fn->getPostParam('id_card_no', '', true);
        
        if ($id_card_no != ''){
            $rec = $fn->getRecordByCondition('contact', "id_card_no = '{$id_card_no}'");
            $expIdCard = array('displayText' => 'Go to this record', 'target' => '_blank');
            $IdCardlink = $fn->getRecordDetailLink('agileIms_contact', 'record_id', $rec['contact_id'], $expIdCard);
    
            if (is_array($rec)){
                $validate->errorArray['id_card_no']['name'] = "id_card_no";
                $validate->errorArray['id_card_no']['msg']  = "NRIC / Passport No. already exists. '{$IdCardlink}'";
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
    function getAdd(){
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        $cpCfg = Zend_Registry::get('cpCfg');
        $db = Zend_Registry::get('db');

        if (!$this->getNewValidate()){
            return $validate->getErrorMessageXML();
        }

        $fa = $this->getFields();

        $rowPrefix    = $fn->getRecordByCondition('setting', "key_text = 'registrationCodePrefix'");
        $current_year = date('Y');
        $nextRegNo = $fn->getSettingsValueByKey("nextRegistrationNo");

        if ($nextRegNo < 10) {
            $nextRegNo = $rowPrefix['value'] . '-' . $current_year . '-0000' . $nextRegNo;
        } else if ($nextRegNo < 100) {
            $nextRegNo = $rowPrefix['value'] . '-' . $current_year . '-000' . $nextRegNo;
        } else if ($nextRegNo > 99) {
            $nextRegNo = $rowPrefix['value'] . '-' . $current_year . '-00' . $nextRegNo;
        } else {
            $nextRegNo = $rowPrefix['value'] . '-' . $current_year . '-0000' . $nextRegNo;
        }
        $fa['registration_no'] = $nextRegNo;

        $id = $fn->addRecord($fa);

        /* Increment of Reg No */
        $SQLUpdate    = "UPDATE setting SET value = (value+1) WHERE key_text = 'nextRegistrationNo'";
        $resultUpdate = $db->sql_query($SQLUpdate);

        $fn->returnAfterNewSave($id);
    }

    /**
     * Used for both Contact edit and Company#contact Edit
     */
    function getEditValidate($contact_id = '') {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();
        
        $id_card_no = $fn->getPostParam('id_card_no', '', true);

        $validate->validateData('first_name', 'Please enter the name');
        $validate->validateData('id_card_no' , 'Please enter NRIC/FIN/Work Permit No.');
        $validate->validateData('nationality' , 'Please select nationality');
        $validate->validateData('date_of_birth' , 'Please enter date of birth');

        if ($id_card_no != ''){
            $rec = $fn->getRecordByCondition('contact', "id_card_no = '{$id_card_no}' AND contact_id != {$contact_id}");
            $expIdCard = array('displayText' => 'Go to this record', 'target' => '_blank');
            $IdCardlink = $fn->getRecordDetailLink('agileIms_contact', 'record_id', $rec['contact_id'], $expIdCard);
    
            if (is_array($rec)){
                $validate->errorArray['id_card_no']['name'] = "id_card_no";
                $validate->errorArray['id_card_no']['msg']  = "NRIC / Passport No. already exists. '{$IdCardlink}'";
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
        $cpCfg  = Zend_Registry::get('cpCfg');
        $cpUtil = Zend_Registry::get('cpUtil');

        $contact_id = $fn->getReqparam('contact_id');
        $company_id = $fn->getPostparam('company_id');

        $companyRec = $fn->getRecordRowById('company', 'company_id', $company_id);
        $countryRec = $fn->getRecordByCondition('geo_country', "country_code = '{$companyRec['address_country_code']}'");

        if (!$this->getEditValidate($contact_id)){
            return $validate->getErrorMessageXML();
        }

        $fa = $this->getFields();
        $fa['company_contact_person']  = $companyRec['contact_name'];
        $fa['office_no']               = $companyRec['phone'];
        $fa['company_fax']             = $companyRec['fax'];
        $fa['company_address_flat']    = $companyRec['address1'];
        $fa['company_address_street']  = $companyRec['address2'];
        $fa['company_address_po_code'] = $companyRec['address_po_code'];
        $fa['company_address_country'] = $countryRec['name'];

        if ($cpCfg['cp.hasPasswordSalt']) {
            $pass_word = $fa['pass_word'];
            $email = $fa['email'];
            if ($pass_word != '') {
                $arr = $cpUtil->getSaltAndPasswordArray($email, $pass_word);
                $fa['salt'] = $arr['salt'];
                $fa['pass_word'] = $arr['pass_word'];
            } else {
                //remove pass_word field from the fields array
                unset($fa['pass_word']);
            }
        }
        $id = $fn->saveRecord($fa, $tableName, $keyField, $keyFieldVal);
        $fn->returnAfterNewSave($id);
    }

    /**
     * Used for both Contact edit and Company#contact Edit
     */
    function getFields(){
        $fn = Zend_Registry::get('fn');

        $fa = array();

        $fa = $fn->addToFieldsArray($fa, 'registration_no');
        $fa = $fn->addToFieldsArray($fa, 'first_name');
        $fa = $fn->addToFieldsArray($fa, 'gender');
        $fa = $fn->addToFieldsArray($fa, 'date_of_birth');
        $fa = $fn->addToFieldsArray($fa, 'marital_status');
        $fa = $fn->addToFieldsArray($fa, 'id_card_no');
        $fa = $fn->addToFieldsArray($fa, 'nationality');
        $fa = $fn->addToFieldsArray($fa, 'race');
        $fa = $fn->addToFieldsArray($fa, 'address_flat');
        $fa = $fn->addToFieldsArray($fa, 'address_street');
        $fa = $fn->addToFieldsArray($fa, 'address_po_code');
        $fa = $fn->addToFieldsArray($fa, 'address_country');
        $fa = $fn->addToFieldsArray($fa, 'email');
        $fa = $fn->addToFieldsArray($fa, 'pass_word');
        $fa = $fn->addToFieldsArray($fa, 'phone');
        $fa = $fn->addToFieldsArray($fa, 'mobile');
        $fa = $fn->addToFieldsArray($fa, 'qualification');
        $fa = $fn->addToFieldsArray($fa, 'yr_of_exp');
        $fa = $fn->addToFieldsArray($fa, 'is_citizen');
        $fa = $fn->addToFieldsArray($fa, 'eng_read_write');
        $fa = $fn->addToFieldsArray($fa, 'physical_activities');
        $fa = $fn->addToFieldsArray($fa, 'safety_shoe');
        $fa = $fn->addToFieldsArray($fa, 'company_id');

        return $fa;
    }

    /**
     *
     */
    function getExportData($dataArray){
        $phpExcel = includeCPClass('Lib', 'PhpExcelExportWrapper', 'PhpExcelExportWrapper');
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');

        $course_status  = $fn->getReqParam('course_status');
        
        if ($course_status != '' && $cpCfg['m.agileIms.course.hasCourseContactStatus']) {
            $addStatusField = 1;
        } else {
            $addStatusField = '';
        }
        
        if ($addStatusField == ''){        
            $fa = array(
                  'first_name'      => $phpExcel->getFldObj('First Name')
                 ,'last_name'       => $phpExcel->getFldObj('Last Name')
                 ,'email'           => $phpExcel->getFldObj('Email')
                 ,'phone_direct'    => $phpExcel->getFldObj('Phone')
                 ,'mobile'          => $phpExcel->getFldObj('Mobile')
                 ,'address_flat'    => $phpExcel->getFldObj('Address 1')
                 ,'address_street'  => $phpExcel->getFldObj('Address 2')
                 ,'address_city'    => $phpExcel->getFldObj('City')
                 ,'address_state'   => $phpExcel->getFldObj('State')
                 ,'address_po_code' => $phpExcel->getFldObj('Zip Code')
                 ,'country_name'    => $phpExcel->getFldObj('Country')
            );
        } else {
            $fa = array(
                  'first_name'      => $phpExcel->getFldObj('First Name')
                 ,'last_name'       => $phpExcel->getFldObj('Last Name')
                 ,'email'           => $phpExcel->getFldObj('Email')
                 ,'phone_direct'    => $phpExcel->getFldObj('Phone')
                 ,'mobile'          => $phpExcel->getFldObj('Mobile')
                 ,'address_flat'    => $phpExcel->getFldObj('Address 1')
                 ,'address_street'  => $phpExcel->getFldObj('Address 2')
                 ,'address_city'    => $phpExcel->getFldObj('City')
                 ,'address_state'   => $phpExcel->getFldObj('State')
                 ,'address_po_code' => $phpExcel->getFldObj('Zip Code')
                 ,'country_name'    => $phpExcel->getFldObj('Country')
                 ,'course_status'   => $phpExcel->getFldObj('Status')
            );
        }

        $file_name = "Contact_" . date("d-m-Y") . ".xls";

        $config = array(
             'filename'  => $file_name
            ,'fldsArr'   => $fa
            ,'dataArray' => $dataArray
        );

        return $phpExcel->exportData($config);
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
              'first_name'           => $phpExcel->getImportFldObj('First Name')
             ,'last_name'            => $phpExcel->getImportFldObj('Last Name')
             ,'email'                => $phpExcel->getImportFldObj('Email')
             ,'phone_direct'         => $phpExcel->getImportFldObj('Phone')
             ,'mobile'               => $phpExcel->getImportFldObj('Mobile')
             ,'address_flat'         => $phpExcel->getImportFldObj('Address 1')
             ,'address_street'       => $phpExcel->getImportFldObj('Address 2')
             ,'address_city'         => $phpExcel->getImportFldObj('City')
             ,'address_state'        => $phpExcel->getImportFldObj('State')
             ,'address_po_code'      => $phpExcel->getImportFldObj('Zip Code')
             ,'address_country'      => $phpExcel->getImportFldObj('Country')
             ,'subscribe'            => $phpExcel->getImportFldObj('Newsletter')
        );

        $fa['address_country']['specialType'] = 'geo_country';
        $fa['subscribe']['defaultValue'] = 1;

        $config = array(
             'module'          => 'agileIms_contact'
            ,'matchFieldArr'   => array('email')
            ,'fldsArr'         => $fa
            ,'callbackAfterInsert' => 'importDataRowCallback'
        );

        return $phpExcel->importData($config);
    }

    /**
     *
     * @param type $contact_id
     * @param type $fa
     */
    function importDataRowCallback($contact_id, $fa) {
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');
        $db = Zend_Registry::get('db');

        //link to interest
        if($cpCfg['m.common.contact.showInterestInImport']){
            $interest_id = $fn->getPostParam('interest_id', '', true);
            $recCount = $fn->getRecordCount('interest_contact', "interest_id = '{$interest_id}' AND contact_id = '{$contact_id}'");
            if (is_numeric ($interest_id) && $recCount == 0) {
                $fa2 = array();
                $fa2['interest_id'] = $interest_id;
                $fa2['contact_id']  = $contact_id;
                $fa2 = $fn->addCreationDetailsToFieldsArray($fa2, 'interest_contact');

                $SQL = $dbUtil->getInsertSQLStringFromArray($fa2, 'interest_contact');
                $result = $db->sql_query($SQL);
            }
        }
    }

    /**
     *
     */
    function getAgileImsContactAgileImsCourseLinkSQL($id) {
        $cpCfg = Zend_Registry::get('cpCfg');
        $formObj = Zend_Registry::get('formObj');

        $courseFld = ($formObj->mode == 'edit') ? 'c.course_id' : 'c.title AS course_title';
        $batchFld = ($formObj->mode == 'edit') ? 'b.batch_id' : 'b.title AS batch_title';
        $linkStr = "<a href='index.php?module=ecommerce_order&_spAction=printOrder&order_id'>Print</a>";
        
        $SQL = "
        SELECT cc.course_contact_id
              ,c.title AS course_title
              ,sd.title AS subsidy_title
              ,sdis.title AS discount_title
              ,b.title AS batch_title
              ,cc.year_of_enrollment
              ,IF ((cc.order_id IS NOT NULL AND cc.order_id != ''),
                (CONCAT_WS('', '<a href=\'index.php?_topRm=finance&module=agileIms_order&_action=edit&order_id=', cc.order_id, '\' target=\'_blank\'>Goto Finance</a>'))
              , '')
               AS order_link
        FROM course_contact cc 
        LEFT JOIN course c ON (c.course_id = cc.course_id)
        LEFT JOIN batch b ON (b.batch_id = cc.batch_id)
        LEFT JOIN (subsidy_discount sd) ON (cc.subsidy_discount_id = sd.subsidy_discount_id)
        LEFT JOIN (subsidy_discount sdis) ON (cc.subsidy_discount_id = sdis.subsidy_discount_id and sdis.category_type = 'Discount')
        WHERE cc.contact_id = '{$id}' AND company_id is NULL
        ORDER BY cc.course_contact_id
        ";

        return $SQL;
    }

    /**
     *
     */
    function setSearchVar($linkRecType = '') {
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $searchVar = Zend_Registry::get('searchVar');
        $searchVar->mainTableAlias = 'c';

        $interest_id    = $fn->getReqParam('interest_id');
        $contact_id     = $fn->getReqParam('contact_id');
        $course_id      = $fn->getReqParam('course_id');
        $batch_id       = $fn->getReqParam('batch_id');
        $class_id       = $fn->getReqParam('class_id');
        $special_search = $fn->getReqParam('special_search');
        $course_status  = $fn->getReqParam('course_status');

        if ($contact_id != "") {
            $searchVar->sqlSearchVar[] = "c.contact_id = '{$contact_id}'";
        } else if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "c.contact_id = '{$tv['record_id']}'";
        } else {
            $fn->setSearchVarForLinkData($searchVar, $linkRecType, 'c.contact_id');

            //------------------------------------------------------------------------//
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

            if ($tv['special_search']  == 'Batch-Not-Linked') {
                $searchVar->sqlSearchVar[] = "cc.batch_id IS NULL";
            }

            //------------------------------------------------------------------------//
            if ($tv['keyword'] != "") {
                $searchVar->sqlSearchVar[] = "(
                       c.first_name         LIKE '%{$tv['keyword']}%'
                    OR c.company_name       LIKE '%{$tv['keyword']}%'
                    OR c.id_card_no         LIKE '%{$tv['keyword']}%'
                    OR c.registration_no    LIKE '%{$tv['keyword']}%'
                    OR c.email              LIKE '%{$tv['keyword']}%'
                    OR c.phone              LIKE '%{$tv['keyword']}%'
                    OR c.mobile             LIKE '%{$tv['keyword']}%'
                )";
            }

            if ($interest_id != '' ) {
                $searchVar->sqlSearchVar[] = "ic.interest_id = {$interest_id}";
            }

            if ($course_id != '' ) {
                $searchVar->sqlSearchVar[] = "cc.course_id = {$course_id}";
            }

            if ($course_status != '') {
                $searchVar->sqlSearchVar[] = "cc.course_status = '{$course_status}'";
            }

            if ($batch_id != '' ) {
                $searchVar->sqlSearchVar[] = "cc.batch_id = {$batch_id}";
            }

            if ($class_id != '' ) {
                $searchVar->sqlSearchVar[] = "sc.class_id = {$class_id}";
            }

            if ($tv['spAction'] == 'link' && $tv['module'] == 'broadcast' ){
                $searchVar->sqlSearchVar[] = "c.subscribe = 1";
            }

            $searchVar->sortOrder = "c.registration_no DESC";
        }
    }

    /**
    *
    */
    function getContactSave(){
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        
        $contact_id = $fn->getPostParam('contact_id');

        if (!$this->getEditValidate($contact_id)) {
            return $validate->getErrorMessageXML();
        }

        $fa = $this->getFields();
        $fn->saveRecord($fa, 'contact', 'contact_id', $contact_id);
        
        return $validate->getSuccessMessageXML('', '', array('data' => $fa));
    }    

    /**
    */
    function getContactAddSubmit(){
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $validate = Zend_Registry::get('validate');
        
        $company_id  = $fn->getReqParam('company_id');
        
        if (!$this->getContactAddValidate()){
            return $validate->getErrorMessageXML();
        }

        $fa = array();
        $fa = $this->getFields();
        $fa['company_id'] = $company_id;
        
        $rowPrefix    = $fn->getRecordByCondition('setting', "key_text = 'registrationCodePrefix'");
        $current_year = date('Y');
        
        $SQLUpdate = "UPDATE setting SET value = (value+1) WHERE key_text = 'nextRegistrationNo'";
        $resultUpdate = $db->sql_query($SQLUpdate);
        $nextRegNo = $fn->getSettingsValueByKey("nextRegistrationNo");

        if ($nextRegNo < 10) {
            $nextRegNo = $rowPrefix['value'] . '-' . $current_year . '-0000' . $nextRegNo;
        } else if ($nextRegNo < 100) {
            $nextRegNo = $rowPrefix['value'] . '-' . $current_year . '-000' . $nextRegNo;
        } else if ($nextRegNo > 99) {
            $nextRegNo = $rowPrefix['value'] . '-' . $current_year . '-00' . $nextRegNo;
        } else {
            $nextRegNo = $rowPrefix['value'] . '-' . $current_year . '-0000' . $nextRegNo;
        }
        $fa['registration_no'] = $nextRegNo;

        $contact_id = $fn->addRecord($fa, 'contact');

        $_SESSION['selectedContactIds'][] = $contact_id;
        //below code will be used in case if a new trainee is added, in getSelectedTraineeResultRow        
        $_SESSION['newTrainee']           = $contact_id;        

        return $validate->getSuccessMessageXML();

    }    

    /**
    /********************************* PROCESS ************************************/
    /* ACTION: STUDENT MODULE : STUDENT- COURSE LINK - WHEN YOU CLICK 'CANCEL' LINK/BUTTON.
    * STEP 1: UPDATING ENROLLMENT STATUS TO 'CANCELLED' IN COURSE CONTACT TABLE
    * STEP 2: UPDATING ORDER STATUS TO 'CANCELLED' IN ORDER TABLE
    * STEP 3: UPDATING INVOICE STATUS TO 'CANCELLED' IN INVOICE TABLE
    * STEP 4: UPDATING RECEIPT STATUS 'TO CANCELLED' IN RECEIPT TABLE
    /******************************* END PROCESS **********************************/
    function getCancelEnrollmentForStudent(){
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $validate = Zend_Registry::get('validate');
        
        $order_id = $fn->getReqParam('order_id');
        
        /********************************** STEP 1 **************************************/
        $faCc = array();
        $faCc['modification_date'] = date('Y-m-d H:i:s');
        $faCc['modified_by']       = $fn->getSessionParam('userName');
        $faCc['course_status']     = 'Cancelled'; 
        $fn->saveRecord($faCc, 'course_contact', 'order_id', $order_id);
        /********************************** STEP 1 ENDS HERE ****************************/
        /********************************** STEP 2 **************************************/
        $faO = array();
        $faO['modification_date'] = date('Y-m-d H:i:s');
        $faO['modified_by']       = $fn->getSessionParam('userName');
        $faO['order_status']      = 'Cancelled'; 
        $fn->saveRecord($faO, 'order', 'order_id', $order_id);
        /********************************** STEP 2 ENDS HERE ****************************/
        /********************************** STEP 3 **************************************/
        $expInv = array('customWhereCondn' => 'status != "Cancelled"');
        $faInv = array();
        $faInv['modification_date'] = date('Y-m-d H:i:s');
        $faInv['modified_by']       = $fn->getSessionParam('userName');
        $faInv['status']            = 'Cancelled'; 
        $fn->saveRecord($faInv, 'invoice', 'order_id', $order_id, $expInv);
        /********************************** STEP 3 ENDS HERE ****************************/
        /********************************** STEP 4 **************************************/
        $expInv = array('customWhereCondn' => 'status != "Cancelled"');
        $faRec = array();
        $faRec['modification_date'] = date('Y-m-d H:i:s');
        $faRec['modified_by']       = $fn->getSessionParam('userName');
        $faRec['receipt_status']    = 'Cancelled'; 
        $fn->saveRecord($faRec, 'receipt', 'order_id', $order_id, $expRec);
        /********************************** STEP 4 ENDS HERE ****************************/
        return $validate->getSuccessMessageXML();
    }    
}
